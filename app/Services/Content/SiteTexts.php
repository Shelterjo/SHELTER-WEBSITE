<?php

namespace App\Services\Content;

use App\Models\SiteText;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\Core\Versions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * The site's fixed texts the Owner may reword without code (M50): page titles and Google descriptions, the home page
 * lines and buttons, page intros, navigation and footer headings, the menu note, form page intros, search and error
 * pages. The language files keep the original wording; the Owner's wording (site_texts) is laid on top by
 * OverridingTranslationLoader. Only the keys listed here can change — form questions, legal consents, labels tied to
 * a form version and the brand name stay in the files. Placeholders (:name, :brand) must stay in the new wording.
 */
final class SiteTexts
{
    private const CACHE_KEY = 'shelter:site-texts:v1';

    public const MAX = 300;

    /** Page → the texts the Owner may change there, in the order they appear. */
    public const GROUPS = [
        'home' => ['site.home.title', 'site.meta.home', 'site.home.lead', 'site.home.cta_menu', 'site.home.cta_locations', 'site.home.branches_title', 'site.home.branches_lead', 'site.gateway.lead'],
        'navigation' => ['site.nav.menu', 'site.nav.locations', 'site.nav.careers', 'ui.footer.explore', 'ui.footer.contact', 'ui.footer.all_contact', 'ui.footer.follow', 'ui.footer.legal'],
        'menu' => ['menu.page_title', 'site.meta.menu', 'menu.title', 'menu.prices_note'],
        'locations' => ['site.titles.locations', 'site.locations.title', 'site.meta.locations', 'site.locations.lead', 'site.branch.title', 'site.meta.branch'],
        'contact' => ['site.titles.contact', 'site.contact.title', 'site.meta.contact', 'site.contact.lead', 'site.contact.general.title', 'site.contact.general.lead', 'site.contact.complaints.title',
            'site.contact.complaints.lead', 'site.contact.catering.title', 'site.contact.catering.lead', 'site.contact.franchise.title', 'site.contact.franchise.lead'],
        'events' => ['site.titles.events', 'site.events.title', 'site.meta.events', 'site.events.lead', 'site.events.empty_title', 'site.events.empty_text'],
        'forms' => ['site.titles.careers', 'careers.title', 'site.meta.careers', 'careers.lead', 'careers.closed', 'feedback.title', 'feedback.lead'],
        'people' => ['awards.title', 'site.meta.awards', 'awards.lead', 'family.title', 'site.meta.family', 'family.lead'],
        'search' => ['site.search.title', 'site.search.lead', 'site.search.none_text', 'site.errors.404_title', 'site.errors.404_text', 'site.errors.500_title',
            'site.errors.500_text', 'site.errors.503_title', 'site.errors.503_text'],
    ];

    /** @var array<string, array<array-key, mixed>> */
    private static array $files = [];

    private static bool $originalOnly = false;

    public function __construct(private readonly Versions $versions, private readonly AuditLogger $audit) {}

    public static function listed(string $key): bool
    {
        foreach (self::GROUPS as $keys) {
            if (in_array($key, $keys, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The Owner's wording by language and file (cached; any failure = no overrides, the files' wording shows).
     *
     * @return array<string, array<string, array<string, string>>>
     */
    public static function overrides(): array
    {
        if (self::$originalOnly) {
            return [];
        }
        try {
            /** @var array<string, array<string, array<string, string>>> $all */
            $all = Cache::rememberForever(self::CACHE_KEY, function (): array {
                $out = [];
                foreach (SiteText::query()->whereNotNull('value')->get(['key', 'locale', 'value']) as $row) {
                    if (self::listed($row->key)) {
                        [$group, $path] = explode('.', $row->key, 2);
                        $out[$row->locale][$group][$path] = (string) $row->value;
                    }
                }

                return $out;
            });

            return $all;
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * Runs $work with the language files' wording only: no database read at all (the design-system export renders the
     * components the same on any machine, whatever the Owner has reworded on a site).
     *
     * @template T
     *
     * @param  callable(): T  $work
     * @return T
     */
    public static function originalOnly(callable $work): mixed
    {
        $before = self::$originalOnly;
        self::$originalOnly = true;
        try {
            return $work();
        } finally {
            self::$originalOnly = $before;
        }
    }

    /** The original wording from the language file (never the Owner's). */
    public static function original(string $key, string $locale): string
    {
        [$group, $path] = explode('.', $key, 2);
        $file = lang_path($locale.'/'.$group.'.php');
        $cacheKey = $locale.'/'.$group;
        if (! isset(self::$files[$cacheKey])) {
            $lines = is_file($file) ? require $file : [];
            self::$files[$cacheKey] = is_array($lines) ? $lines : [];
        }
        $value = Arr::get(self::$files[$cacheKey], $path);

        return is_string($value) ? $value : '';
    }

    /** The wording the site shows now. */
    public static function current(string $key, string $locale): string
    {
        [$group, $path] = explode('.', $key, 2);

        return self::overrides()[$locale][$group][$path] ?? self::original($key, $locale);
    }

    /**
     * Saves one page's texts. Empty = back to the original wording; each placeholder of the original must stay.
     *
     * @param  array<string, mixed>  $input  texts[key][locale] => text
     * @return array{errors: array<string, string>, changed: int}
     */
    public function save(string $group, array $input, User $owner): array
    {
        $keys = self::GROUPS[$group] ?? [];
        $texts = is_array($input['texts'] ?? null) ? $input['texts'] : [];
        $errors = [];
        $values = [];
        foreach ($keys as $key) {
            foreach (['ar', 'en'] as $locale) {
                $raw = $texts[$key][$locale] ?? null;
                $value = is_string($raw) ? trim((string) preg_replace('/[ \t]+/u', ' ', str_replace(["\r\n", "\r"], "\n", $raw))) : '';
                $original = self::original($key, $locale);
                $field = self::fieldId($key, $locale);
                if (mb_strlen($value) > self::MAX) {
                    $errors[$field] = (string) __('dashboard.pages.errors.too_long', ['max' => self::MAX]);

                    continue;
                }
                preg_match_all('/:[a-z_]+/', $original, $needed);
                $missing = $value === '' ? [] : array_values(array_filter($needed[0], fn (string $p): bool => ! str_contains($value, $p)));
                if ($missing !== []) {
                    $errors[$field] = (string) __('dashboard.texts.errors.placeholder', ['placeholders' => implode(' ', $missing)]);

                    continue;
                }
                $phrase = $value === '' ? null : ContentGuard::find($value);
                if ($phrase !== null) {
                    $errors[$field] = (string) __('dashboard.blocked_phrase', ['phrase' => $phrase]);

                    continue;
                }
                $values[$key][$locale] = $value === '' || $value === $original ? null : $value;
            }
        }
        if ($errors !== []) {
            return ['errors' => $errors, 'changed' => 0];
        }
        $changed = [];
        DB::transaction(function () use ($values, $owner, &$changed): void {
            foreach ($values as $key => $byLocale) {
                foreach ($byLocale as $locale => $value) {
                    $row = SiteText::query()->firstOrNew(['key' => $key, 'locale' => $locale]);
                    if (($row->exists ? $row->value : null) === $value) {
                        continue;
                    }
                    $before = $row->exists ? $row->value : null;
                    $row->forceFill(['value' => $value, 'updated_by' => $owner->id])->save();
                    $this->versions->record($row, 'published', ['key' => $key, 'locale' => $locale, 'value' => $value], null, $owner);
                    $changed[] = ['key' => $key, 'locale' => $locale, 'before' => $before, 'after' => $value];
                }
            }
        });
        if ($changed !== []) {
            $this->audit->record('texts.saved', null, ['before' => array_map(fn ($c) => [$c['key'].'.'.$c['locale'] => $c['before']], $changed),
                'after' => array_map(fn ($c) => [$c['key'].'.'.$c['locale'] => $c['after']], $changed)], ['count' => count($changed)], actor: $owner);
            self::flush();
        }

        return ['errors' => [], 'changed' => count($changed)];
    }

    public static function fieldId(string $key, string $locale): string
    {
        return 't-'.str_replace(['.', '_'], '-', $key).'-'.$locale;
    }

    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
        app('translator')->setLoaded([]); // this request's translator re-reads the files and the new wording
    }
}
