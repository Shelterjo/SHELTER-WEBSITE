<?php

namespace App\Support;

use Closure;
use Illuminate\Contracts\Translation\Loader;
use Illuminate\Support\Arr;

/**
 * The language files, with the Owner's own wording laid on top for the listed site texts (SiteTexts). Everything else
 * — namespaces, JSON files, every unlisted key — comes from the files unchanged.
 */
final class OverridingTranslationLoader implements Loader
{
    /** @param  Closure(): array<string, array<string, array<string, string>>>  $overrides  locale → group → key path → text */
    public function __construct(private readonly Loader $files, private readonly Closure $overrides) {}

    /** @return array<array-key, mixed> */
    public function load($locale, $group, $namespace = null): array
    {
        $lines = $this->files->load($locale, $group, $namespace);
        if ($namespace !== null && $namespace !== '*') {
            return $lines;
        }
        foreach ((($this->overrides)()[$locale][$group] ?? []) as $path => $text) {
            Arr::set($lines, $path, $text);
        }

        return $lines;
    }

    public function addNamespace($namespace, $hint): void
    {
        $this->files->addNamespace($namespace, $hint);
    }

    public function addJsonPath($path): void
    {
        $this->files->addJsonPath($path);
    }

    /** @return array<string, string> */
    public function namespaces(): array
    {
        return $this->files->namespaces();
    }
}
