<?php

namespace App\Console\Commands;

use App\Services\Content\SiteTexts;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;
use InvalidArgumentException;
use JsonException;

/**
 * Renders the design-system catalog (design-system/catalog.php) through the real Blade x-ui components, once in
 * Arabic (RTL) and once in English (LTR), and writes design-system/stories/generated/{component}.json as
 * [{story, state, html_ar, html_en}] for Storybook (ADR-001, TOOLCHAIN.md: stories come from Blade itself, never a
 * second copy of the components). Needs no database.
 *
 * Governance (DS-022/DS-024): it fails when a component has no stories, or when a catalog story has no matching
 * export in its design-system/stories/{component}.stories.ts file.
 */
final class DsExport extends Command
{
    protected $signature = 'ds:export
        {--catalog=design-system/catalog.php : Catalog file (component → stories)}
        {--out=design-system/stories/generated : Output folder for the JSON files}
        {--stories=design-system/stories : Folder of the *.stories.ts files checked against the catalog}';

    protected $description = 'Render the x-ui component catalog to HTML (ar + en) for Storybook';

    /** @var list<string> */
    private const LOCALES = ['ar', 'en'];

    /** @var list<string> */
    private const STATES = ['default', 'hover', 'focus', 'active', 'disabled', 'loading', 'error', 'empty', 'open'];

    public function handle(Filesystem $files): int
    {
        // The language files' wording only — the export never reads a database (Site texts are a site's own).
        return SiteTexts::originalOnly(fn (): int => $this->export($files));
    }

    private function export(Filesystem $files): int
    {
        $catalog = $this->loadCatalog($this->pathOption('catalog'));
        $out = $this->pathOption('out');
        $storiesDir = $this->pathOption('stories');

        $problems = $this->coverageProblems($catalog, $files, $storiesDir);
        if ($problems !== []) {
            foreach ($problems as $problem) {
                $this->error($problem);
            }

            return self::FAILURE;
        }

        $files->ensureDirectoryExists($out);
        foreach ($files->glob($out.'/*.json') as $stale) {
            $files->delete($stale);
        }

        $original = app()->getLocale();
        $count = 0;
        try {
            foreach ($catalog['components'] as $component => $definition) {
                $entries = [];
                foreach ($definition['stories'] as $story) {
                    $entry = ['story' => $story['story'], 'state' => $story['state']];
                    foreach (self::LOCALES as $locale) {
                        app()->setLocale($locale);
                        $entry['html_'.$locale] = $this->render($component, $story, $locale);
                    }
                    $entries[] = $entry;
                    $count++;
                }
                $files->put("{$out}/{$component}.json", json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
            }
        } catch (JsonException $e) {
            $this->error('ds:export: '.$e->getMessage());

            return self::FAILURE;
        } finally {
            app()->setLocale($original);
        }

        $this->info(sprintf('ds:export: %d components · %d stories · %s', count($catalog['components']), $count, implode(' + ', self::LOCALES)));

        return self::SUCCESS;
    }

    /**
     * @param  array{story: string, state: string, props: array<string, mixed>, slot: mixed, slots: array<string, mixed>, blade: mixed}  $story
     */
    private function render(string $component, array $story, string $locale): string
    {
        $blade = $this->localise($story['blade'], $locale);
        if (is_string($blade)) {
            return trim(Blade::render($blade, [], deleteCachedView: true));
        }

        /** @var array<string, mixed> $props */
        $props = $this->localise($story['props'], $locale);
        $attributes = implode(' ', array_map(
            fn (string $key): string => ':'.Str::kebab($key).'="$__props['.var_export($key, true).']"',
            array_keys($props),
        ));
        $slots = '';
        foreach ($story['slots'] as $name => $source) {
            // The newline matters: Blade compiles </x-slot> to @endslot, which must not touch the next word.
            $slots .= "<x-slot:{$name}>".$this->stringValue($this->localise($source, $locale))."</x-slot:{$name}>\n";
        }
        $slot = $this->stringValue($this->localise($story['slot'], $locale));
        $source = "<x-ui.{$component} {$attributes}>{$slots}{$slot}</x-ui.{$component}>";

        return trim(Blade::render($source, ['__props' => $props], deleteCachedView: true));
    }

    /** Resolves ['@ar' => …, '@en' => …] pairs (at any depth) to the value for $locale. */
    private function localise(mixed $value, string $locale): mixed
    {
        if (! is_array($value)) {
            return $value;
        }
        if (array_key_exists('@ar', $value) && array_key_exists('@en', $value) && count($value) === 2) {
            return $value['@'.$locale];
        }

        return array_map(fn (mixed $item): mixed => $this->localise($item, $locale), $value);
    }

    private function stringValue(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    /** Option value as a path: absolute paths are kept, relative ones resolve from the project root. */
    private function pathOption(string $name): string
    {
        $value = $this->option($name);
        $path = is_string($value) ? $value : '';

        return str_starts_with($path, '/') ? $path : base_path($path);
    }

    /**
     * @return array{components: array<string, array{stories: list<array{story: string, state: string, props: array<string, mixed>, slot: mixed, slots: array<string, mixed>, blade: mixed}>}>, covered: array<string, string>}
     */
    private function loadCatalog(string $path): array
    {
        if (! is_file($path)) {
            throw new InvalidArgumentException("ds:export: catalog not found at {$path}");
        }
        $raw = require $path;
        if (! is_array($raw) || ! isset($raw['components']) || ! is_array($raw['components'])) {
            throw new InvalidArgumentException('ds:export: the catalog must return [\'components\' => [...]]');
        }

        $components = [];
        foreach ($raw['components'] as $component => $definition) {
            if (! is_string($component) || ! is_array($definition) || ! isset($definition['stories']) || ! is_array($definition['stories'])) {
                throw new InvalidArgumentException("ds:export: component [{$component}] needs a 'stories' list");
            }
            $stories = [];
            foreach ($definition['stories'] as $story) {
                if (! is_array($story) || ! isset($story['story']) || ! is_string($story['story']) || preg_match('/^[A-Z][A-Za-z0-9]*$/', $story['story']) !== 1) {
                    throw new InvalidArgumentException("ds:export: every story of [{$component}] needs a PascalCase 'story' name (it is the TypeScript export)");
                }
                $state = $story['state'] ?? 'default';
                if (! is_string($state) || ! in_array($state, self::STATES, true)) {
                    throw new InvalidArgumentException("ds:export: {$component}/{$story['story']} has an unknown state");
                }
                /** @var array<string, mixed> $props */
                $props = is_array($story['props'] ?? null) ? $story['props'] : [];
                /** @var array<string, mixed> $slots */
                $slots = is_array($story['slots'] ?? null) ? $story['slots'] : [];
                $stories[] = [
                    'story' => $story['story'],
                    'state' => $state,
                    'props' => $props,
                    'slot' => $story['slot'] ?? '',
                    'slots' => $slots,
                    'blade' => $story['blade'] ?? null,
                ];
            }
            $components[$component] = ['stories' => $stories];
        }

        $covered = [];
        foreach ((array) ($raw['covered'] ?? []) as $component => $by) {
            if (is_string($component) && is_string($by)) {
                $covered[$component] = $by;
            }
        }

        return ['components' => $components, 'covered' => $covered];
    }

    /**
     * Every x-ui component has stories (directly, or through the component listed in 'covered'), and every catalog
     * story is exported by its *.stories.ts file.
     *
     * @param  array{components: array<string, array{stories: list<array{story: string, state: string, props: array<string, mixed>, slot: mixed, slots: array<string, mixed>, blade: mixed}>}>, covered: array<string, string>}  $catalog
     * @return list<string>
     */
    private function coverageProblems(array $catalog, Filesystem $files, string $storiesDir): array
    {
        $problems = [];
        foreach ($files->glob(resource_path('views/components/ui/*.blade.php')) as $view) {
            $component = basename($view, '.blade.php');
            if (! isset($catalog['components'][$component]) && ! isset($catalog['covered'][$component])) {
                $problems[] = "ds:export: x-ui.{$component} has no stories in the catalog (add them, or list it under 'covered').";
            }
        }
        foreach ($catalog['components'] as $component => $definition) {
            $storyFile = "{$storiesDir}/{$component}.stories.ts";
            $source = $files->exists($storyFile) ? $files->get($storyFile) : '';
            foreach ($definition['stories'] as $story) {
                if (preg_match('/export const '.$story['story'].'\b/', $source) !== 1) {
                    $problems[] = "ds:export: {$component}/{$story['story']} is not exported by ".basename($storyFile).'.';
                }
            }
        }

        return $problems;
    }
}
