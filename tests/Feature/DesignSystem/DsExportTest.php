<?php

namespace Tests\Feature\DesignSystem;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

class DsExportTest extends TestCase
{
    private string $out;

    protected function setUp(): void
    {
        parent::setUp();
        $this->out = storage_path('framework/testing/ds-export-'.bin2hex(random_bytes(4)));
    }

    protected function tearDown(): void
    {
        (new Filesystem)->deleteDirectory($this->out);
        parent::tearDown();
    }

    /** @param  array<string, string>  $options */
    private function export(array $options = []): PendingCommand
    {
        $pending = $this->artisan('ds:export', ['--out' => $this->out] + $options);
        $this->assertInstanceOf(PendingCommand::class, $pending);

        return $pending;
    }

    /** @return list<array{story: string, state: string, html_ar: string, html_en: string}> */
    private function stories(string $component): array
    {
        $json = json_decode((string) file_get_contents("{$this->out}/{$component}.json"), true);
        $this->assertIsArray($json);

        /** @var list<array{story: string, state: string, html_ar: string, html_en: string}> $json */
        return $json;
    }

    public function test_it_renders_every_component_in_arabic_and_english_without_a_database(): void
    {
        $queries = 0;
        DB::listen(function () use (&$queries): void {
            $queries++;
        });

        $this->export()->assertSuccessful();

        $this->assertSame(0, $queries, 'ds:export must not touch the database');
        $components = array_map(fn (string $file) => basename($file, '.blade.php'), glob(resource_path('views/components/ui/*.blade.php')) ?: []);
        $exported = array_map(fn (string $file) => basename($file, '.json'), glob("{$this->out}/*.json") ?: []);
        $this->assertSame([], array_values(array_diff($components, $exported, ['tab-panel', 'dialog'])), 'Every x-ui component has stories');

        foreach ($exported as $component) {
            foreach ($this->stories($component) as $story) {
                $this->assertSame(['story', 'state', 'html_ar', 'html_en'], array_keys($story));
                $this->assertMatchesRegularExpression('/^[A-Z][A-Za-z0-9]*$/', $story['story']);
                if ($story['state'] !== 'empty') {
                    $this->assertNotSame('', $story['html_ar'], "{$component}/{$story['story']}");
                }
                $this->assertStringNotContainsString('style=', $story['html_ar'].$story['html_en'], "{$component}/{$story['story']} has an inline style");
                // An unclosed slot leaks Blade source (e.g. "@endslotTry" when </x-slot> touches a Latin word).
                $this->assertDoesNotMatchRegularExpression('/@(end)?(slot|if|isset|foreach|php)/', $story['html_ar'].$story['html_en'], "{$component}/{$story['story']} leaked Blade source");
            }
        }

        $primary = collect($this->stories('button'))->firstWhere('story', 'Primary');
        $this->assertIsArray($primary);
        $this->assertStringContainsString('حفظ', $primary['html_ar']);
        $this->assertStringContainsString('Save', $primary['html_en']);
        $close = collect($this->stories('button'))->firstWhere('story', 'IconOnly');
        $this->assertIsArray($close);
        $this->assertStringContainsString('aria-label="إغلاق"', $close['html_ar']);
        $this->assertStringContainsString('aria-label="Close"', $close['html_en']);
        $this->assertSame(app()->getLocale(), config('app.locale'), 'The locale is restored after the export');
    }

    public function test_product_cards_only_show_approved_menu_data(): void
    {
        $this->export()->assertSuccessful();
        $cards = collect($this->stories('product-card'))->keyBy('story');

        // PRD-00014: the Arabic source name is pending owner review (D-091) → English is primary, no secondary (CF-03).
        $pending = $cards->get('ArabicNamePending');
        $this->assertIsArray($pending);
        $this->assertStringNotContainsString('كابتشينو', $pending['html_ar']);
        $this->assertStringContainsString('<span lang="en">CAPPUCCINO</span>', $pending['html_ar']);
        $this->assertStringContainsString('2.50', $pending['html_ar']);
        // PRD-00001: approved in both languages, price 1.500 JOD.
        $default = $cards->get('Default');
        $this->assertIsArray($default);
        $this->assertStringContainsString('قهوة تركية سينجل', $default['html_ar']);
        $this->assertStringContainsString('TURKISH COFFEE SINGLE', $default['html_ar']);
        $this->assertStringContainsString('1.50 دينار أردني', $default['html_ar']);
        $this->assertStringContainsString('1.50 Jordanian dinars', $default['html_en']);
        foreach ($cards as $story) {
            $this->assertStringNotContainsString('<img', $story['html_ar'], 'No images: only internal placeholders');
        }
    }

    public function test_it_fails_when_a_catalog_story_has_no_storybook_export(): void
    {
        $empty = "{$this->out}-stories";
        (new Filesystem)->ensureDirectoryExists($empty);
        try {
            $this->export(['--stories' => $empty])
                ->expectsOutputToContain('is not exported by button.stories.ts')
                ->assertFailed();
        } finally {
            (new Filesystem)->deleteDirectory($empty);
        }
    }
}
