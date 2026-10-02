<?php

namespace App\Services\Content;

use App\Enums\PublishStatus;
use App\Models\Page;
use App\Models\PageSection;
use App\Support\SiteLinks;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;

/**
 * The one public read path for brand content pages (CONTENT-SOURCE-OF-TRUTH). A page is public only when it is
 * published (and its publish time has come), not archived, has both languages for its title and every visible
 * section (G-02/G-03, LANGUAGE-PARITY), uses approved section types only, and carries no unconfirmed AI text (G-20).
 * A page that contains a phrase blocked for it (config content.blocked_phrases — e.g. guaranteed profit on the franchise
 * page, D-323) is kept offline too. Otherwise it does not exist for visitors (404, no link) — never an empty shell or
 * invented text.
 */
final class Pages
{
    /** Fixed page keys = route names, in footer order (SITE-INVENTORY SI-B03, SI-B05, SI-B06, SI-B07). */
    public const EXPLORE = ['about', 'faq'];

    public const LEGAL = ['privacy', 'terms'];

    /** Business pages with their own controller and a published `pages` row (SI-B12 franchise — PO-030). */
    public const BUSINESS = ['franchise'];

    /** @var array<string, ContentPage|null> */
    private array $cache = [];

    public function published(string $key, string $locale, ?CarbonImmutable $now = null): ?ContentPage
    {
        $cacheKey = $key.'|'.$locale;
        if (! array_key_exists($cacheKey, $this->cache)) {
            $this->cache[$cacheKey] = $this->load($key, $locale, $now ?? CarbonImmutable::now());
        }

        return $this->cache[$cacheKey];
    }

    /**
     * Footer links to the published pages of a group, labelled with each page's approved title.
     *
     * @param  list<string>  $keys
     * @return list<array{label: string, href: string, key: string}>
     */
    public function links(array $keys, string $locale): array
    {
        $this->warm($keys, $locale, CarbonImmutable::now());
        $links = [];
        foreach ($keys as $key) {
            $page = $this->published($key, $locale);
            $href = $page !== null ? SiteLinks::to($key, ['locale' => $locale]) : null;
            if ($page !== null && $href !== null) {
                $links[] = ['label' => $page->name, 'href' => $href, 'key' => $key];
            }
        }

        return $links;
    }

    /**
     * Paragraphs are separated by a blank line; single line breaks stay inside the paragraph (shown as written).
     *
     * @return list<string>
     */
    public static function paragraphs(string $text): array
    {
        $parts = preg_split('/\R[ \t]*\R/u', trim($text)) ?: [];

        return array_values(array_filter(array_map('trim', $parts), fn (string $part): bool => $part !== ''));
    }

    /**
     * List and step sections: one item per line.
     *
     * @return list<string>
     */
    public static function items(string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/u', trim($text)) ?: []), fn (string $item): bool => $item !== ''));
    }

    /**
     * One query for every key not read yet in this request (the footer asks for four pages on every page view).
     *
     * @param  list<string>  $keys
     */
    private function warm(array $keys, string $locale, CarbonImmutable $now): void
    {
        $missing = array_values(array_filter($keys, fn (string $key): bool => ! array_key_exists($key.'|'.$locale, $this->cache)));
        if ($missing === []) {
            return;
        }
        $pages = Page::query()
            ->with(['sections' => fn ($query) => $query->where('is_visible', true)])
            ->whereIn('key', $missing)
            ->get()
            ->keyBy('key');
        foreach ($missing as $key) {
            $page = $pages->get($key);
            $this->cache[$key.'|'.$locale] = $page instanceof Page ? $this->build($page, $locale, $now) : null;
        }
    }

    private function load(string $key, string $locale, CarbonImmutable $now): ?ContentPage
    {
        $this->warm([$key], $locale, $now);

        return $this->cache[$key.'|'.$locale];
    }

    private function build(Page $page, string $locale, CarbonImmutable $now): ?ContentPage
    {
        if (! $this->isLive($page, $now) || blank($page->title_ar) || blank($page->title_en) || $this->isBlocked($page)) {
            return null;
        }

        $sections = [];
        foreach ($page->sections as $section) {
            if (! $this->isComplete($section)) {
                return null; // a visible section that is not ready keeps the whole page offline (BLOCKING, not partial)
            }
            $body = (string) $section->body($locale);
            $sections[] = new ContentSection($section->type, $section->heading($locale), in_array($section->type, ['list', 'steps'], true) ? self::items($body) : self::paragraphs($body));
        }
        if ($sections === []) {
            return null;
        }

        $description = $locale === 'ar' ? $page->description_ar : $page->description_en;
        $titleLines = self::items((string) ($locale === 'ar' ? $page->title_ar : $page->title_en));
        $title = implode(' ', $titleLines);
        $name = $locale === 'ar' ? $page->name_ar : $page->name_en;

        return new ContentPage(
            $page->key,
            $page->type,
            $title,
            $titleLines,
            filled($name) ? trim((string) $name) : $title,
            filled($description) ? (string) $description : null,
            $sections,
            $page->content_updated_at ?? $page->published_at,
        );
    }

    /** Both languages are checked: one blocked phrase in either keeps the whole page offline. */
    private function isBlocked(Page $page): bool
    {
        /** @var list<string> $phrases */
        $phrases = config('content.blocked_phrases.'.$page->key, []);
        if ($phrases === []) {
            return false;
        }
        $text = implode("\n", [$page->title_ar, $page->title_en, $page->name_ar, $page->name_en, $page->description_ar, $page->description_en,
            ...$page->sections->flatMap(fn (PageSection $s): array => [$s->heading_ar, $s->heading_en, $s->body_ar, $s->body_en])->all()]);
        foreach ($phrases as $phrase) {
            if (mb_stripos($text, $phrase) !== false) {
                Log::warning('content.blocked_phrase', ['page' => $page->key]);

                return true;
            }
        }

        return false;
    }

    private function isLive(Page $page, CarbonImmutable $now): bool
    {
        return $page->status === PublishStatus::Published
            && $page->archived_at === null
            && ($page->published_at === null || $page->published_at->lessThanOrEqualTo($now))
            && $page->origin !== 'ai';
    }

    private function isComplete(PageSection $section): bool
    {
        if ($section->origin === 'ai' || ! in_array($section->type, PageSection::TYPES, true)) {
            return false;
        }
        $headings = [filled($section->heading_ar), filled($section->heading_en)];

        return filled($section->body_ar) && filled($section->body_en)
            && $headings[0] === $headings[1]
            && ($section->type !== 'faq' || $headings[0]);
    }
}
