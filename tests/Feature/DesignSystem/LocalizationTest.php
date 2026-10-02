<?php

namespace Tests\Feature\DesignSystem;

use Illuminate\Support\Arr;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    /** @return list<string> */
    private function keys(string $locale): array
    {
        $strings = require lang_path("{$locale}/ui.php");
        $this->assertIsArray($strings);

        return array_keys(Arr::dot($strings));
    }

    public function test_arabic_and_english_component_strings_have_the_same_keys(): void
    {
        $ar = $this->keys('ar');
        $en = $this->keys('en');
        sort($ar);
        sort($en);

        $this->assertSame($ar, $en);
    }

    /**
     * Every language file, not only ui.php (FINAL-QA QA-033): a key in one language only is a page that shows the raw
     * key in the other. Allowed on purpose: the Arabic-only careers form (CAREERS-001 — English shows the page and an
     * "apply in Arabic" link) and the extra Arabic plural forms (zero, two, few, many).
     */
    public function test_every_language_file_has_the_same_keys_in_both_languages(): void
    {
        $flatten = function (array $strings, string $prefix = '') use (&$flatten): array {
            $out = [];
            foreach ($strings as $key => $value) {
                if (is_array($value) && $value !== [] && ! array_is_list($value)) {
                    $out = array_merge($out, $flatten($value, $prefix.$key.'.'));
                } else {
                    $out[] = $prefix.$key;
                }
            }

            return $out;
        };
        $files = array_map('basename', glob(lang_path('ar/*.php')) ?: []);
        $this->assertSame($files, array_map('basename', glob(lang_path('en/*.php')) ?: []), 'the same files in both languages');
        foreach ($files as $file) {
            /** @var array<string, mixed> $ar */
            $ar = require lang_path('ar/'.$file);
            /** @var array<string, mixed> $en */
            $en = require lang_path('en/'.$file);
            $arKeys = $flatten($ar);
            $enKeys = $flatten($en);
            $plural = fn (string $key): bool => preg_match('/\.(zero|two|few|many)$/', $key) === 1;
            $arOnly = array_values(array_filter(array_diff($arKeys, $enKeys), fn (string $key): bool => ! $plural($key) && $file !== 'careers.php'));
            $enOnly = array_values(array_filter(array_diff($enKeys, $arKeys), fn (string $key): bool => ! ($file === 'careers.php' && $key === 'apply')));
            $this->assertSame([], $arOnly, "lang/en/{$file} is missing keys");
            $this->assertSame([], $enOnly, "lang/ar/{$file} is missing keys");
        }
    }

    public function test_every_ui_string_used_by_a_view_exists_in_both_languages(): void
    {
        $views = array_merge(
            glob(resource_path('views/components/ui/*.blade.php')) ?: [],
            glob(resource_path('views/layouts/*.blade.php')) ?: [],
        );
        $used = [];
        foreach ($views as $view) {
            preg_match_all("/__\\('ui\\.([a-z_.]+[a-z_])'/", (string) file_get_contents($view), $found);
            $used = array_merge($used, $found[1]);
        }
        // Keys built at runtime by the components (status pill, alert, trend, currency).
        foreach (['synced', 'pending', 'failed', 'not_supported', 'manual_action_required', 'out_of_sync'] as $status) {
            $used[] = "status.{$status}";
        }
        foreach (['info', 'success', 'warning', 'danger'] as $variant) {
            $used[] = "alert.{$variant}";
        }
        array_push($used, 'trend.up', 'trend.down', 'currency.JOD.symbol', 'currency.JOD.name');

        $this->assertGreaterThan(15, count(array_unique($used)));
        foreach (['ar', 'en'] as $locale) {
            $keys = $this->keys($locale);
            foreach (array_unique($used) as $key) {
                $this->assertContains($key, $keys, "lang/{$locale}/ui.php is missing {$key}");
            }
        }
    }

    public function test_components_contain_no_hard_coded_interface_words(): void
    {
        // Visible component text comes from __('ui.…') or from the caller; Arabic or English words in a component
        // template (outside comments and PHP) would be untranslated interface copy.
        foreach (glob(resource_path('views/components/ui/*.blade.php')) ?: [] as $view) {
            $source = (string) file_get_contents($view);
            foreach (['/\{\{--.*?--\}\}/s', '/@php.*?@endphp/s', '/\{\{.*?\}\}/s', '/\{!!.*?!!\}/s', '/@\w+\s*\((?:[^()]|\((?:[^()]|\([^()]*\))*\))*\)/s', '/@\w+/', '/<[^>]*>/s'] as $construct) {
                $source = (string) preg_replace($construct, ' ', $source);
            }
            $this->assertDoesNotMatchRegularExpression('/[\x{0600}-\x{06FF}]|\b[A-Za-z]{3,}\b/u', $source, basename($view).' contains literal interface text');
        }
    }
}
