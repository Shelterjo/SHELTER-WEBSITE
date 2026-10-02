<?php

namespace App\Services\Dashboard;

use App\Enums\NameStatus;
use App\Models\Award;
use App\Models\Branch;
use App\Models\Experience;
use App\Models\Media;
use App\Models\MenuCategory;
use App\Models\Page;
use App\Models\Product;
use App\Models\Recruitment\Application;
use App\Models\TeamMember;
use App\Services\Content\Search\Normalizer;
use App\Services\Content\SiteTexts;
use App\Services\Experiences\Recognitions;
use App\Services\MasterData\MasterData;
use App\Services\Requests\ApplicationInbox;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

/**
 * The Owner's dashboard search (DASH-019, M35 §47): one box over the Owner's own data — menu items (names in both
 * languages and the PRD code), menu sections, branches, pages and site texts, events, announcements and campaigns,
 * Employee of the Month, SHELTER Family, awards, images, and requests by their reference number (and by the
 * applicant's name, as the requests lists already allow — never email, phone or identity number). Results are grouped
 * by area, each one opens its screen. Server-side and Owner only (routes/dashboard.php); separate from the visitors'
 * search and its index (M32 §27: no private content there). The words searched for are never stored or logged — no
 * audit entry, no search log.
 */
final class OwnerSearch
{
    public const MIN = 2;

    public const MAX = 100;

    /** Results shown per area; the rest is behind "open the full list". */
    public const PER_GROUP = 8;

    /** Areas, in the order the results page shows them. */
    public const GROUPS = ['menu', 'sections', 'branches', 'pages', 'texts', 'events', 'announcements', 'recognition', 'team', 'awards', 'media', 'careers', 'partnerships'];

    public function __construct(private readonly MasterData $data) {}

    /** The query as searched: trimmed, one space between words, at most MAX characters. */
    public static function clean(string $query): string
    {
        return mb_substr(trim((string) preg_replace('/\s+/u', ' ', $query)), 0, self::MAX);
    }

    /**
     * Every area with at least one hit: its first PER_GROUP results, the total and, when there are more, the screen
     * that lists them all.
     *
     * @return array<string, array{hits: list<array{title: string, meta: ?string, url: string, lang: ?string}>, total: int, more: ?string}>
     */
    public function search(string $query, string $locale): array
    {
        $query = self::clean($query);
        $words = Normalizer::words($query);
        if (mb_strlen($query) < self::MIN || $words === []) {
            return [];
        }
        $ar = $locale === 'ar';
        $found = [
            'menu' => $this->menu($words, $ar),
            'sections' => $this->sections($words, $ar),
            'branches' => $this->branches($words, $locale),
            'pages' => $this->pages($words),
            'texts' => $this->texts($words, $locale),
            'events' => $this->experiences(['event'], 'dashboard.events.edit', $words, $ar),
            'announcements' => $this->experiences(['announcement', 'campaign'], 'dashboard.announcements.edit', $words, $ar),
            'recognition' => $this->recognitions($words, $locale),
            'team' => $this->team($words, $ar),
            'awards' => $this->awards($words, $ar),
            'media' => $this->media($words, $locale),
            'careers' => $this->requests('JOB', $query, $locale),
            'partnerships' => $this->requests('FR', $query, $locale),
        ];
        $lists = [
            'menu' => route('dashboard.menu.index', ['q' => $query]), 'sections' => route('dashboard.menu.index'), 'branches' => route('dashboard.branches.index'),
            'pages' => route('dashboard.pages.index'), 'texts' => route('dashboard.texts.index'), 'events' => route('dashboard.events.index'),
            'announcements' => route('dashboard.announcements.index'), 'recognition' => route('dashboard.recognition.index'), 'team' => route('dashboard.team.index'),
            'awards' => route('dashboard.awards.index'), 'media' => route('dashboard.media.index'),
            'careers' => route('dashboard.careers.index', ['q' => $query]), 'partnerships' => route('dashboard.partnerships.index', ['q' => $query]),
        ];
        $groups = [];
        foreach ($found as $group => $hits) {
            if ($hits !== []) {
                $groups[$group] = ['hits' => array_slice($hits, 0, self::PER_GROUP), 'total' => count($hits), 'more' => count($hits) > self::PER_GROUP ? $lists[$group] : null];
            }
        }

        return $groups;
    }

    /**
     * True when every searched word appears in the text (both normalised: Arabic letter forms, diacritics, digits, case).
     *
     * @param  list<string>  $words
     * @param  list<string|null>  $fields
     */
    public static function matches(array $words, array $fields): bool
    {
        $haystack = ' '.Normalizer::normalize(implode(' ', array_filter($fields, fn (?string $f): bool => $f !== null && $f !== ''))).' ';
        foreach ($words as $word) {
            if (! str_contains($haystack, $word)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param  list<string>  $words
     * @return list<array{title: string, meta: ?string, url: string, lang: ?string}>
     */
    private function menu(array $words, bool $ar): array
    {
        $hits = [];
        foreach (Product::query()->where('status', Product::STATUS_ACTIVE)->whereNull('merged_into_id')->with('category')->orderBy('sort')->get() as $product) {
            if (! self::matches($words, [$product->code, $product->display_name_en, $product->display_name_ar, $product->normalized_name_en, $product->normalized_name_ar])) {
                continue;
            }
            // The Arabic name shows only once approved (D-137), as on the menu screen.
            $arabic = NameStatus::fromInventory($product->name_ar_status) === NameStatus::Approved ? $product->display_name_ar : null;
            $title = $ar && filled($arabic) ? (string) $arabic : (string) ($product->display_name_en ?? $product->code);
            $hits[] = ['title' => $title, 'meta' => $product->code.' · '.$this->categoryName($product->category, $ar), 'url' => route('dashboard.menu.show', $product),
                'lang' => $ar && filled($arabic) ? null : 'en'];
        }

        return $hits;
    }

    /**
     * @param  list<string>  $words
     * @return list<array{title: string, meta: ?string, url: string, lang: ?string}>
     */
    private function sections(array $words, bool $ar): array
    {
        $hits = [];
        foreach (MenuCategory::query()->orderBy('sort')->get() as $category) {
            if (self::matches($words, [$category->code, $category->name_en, $category->name_ar, $category->source_name])) {
                $hits[] = ['title' => $this->categoryName($category, $ar), 'meta' => $category->code, 'url' => route('dashboard.menu.index', ['category' => $category->code]), 'lang' => null];
            }
        }

        return $hits;
    }

    /**
     * @param  list<string>  $words
     * @return list<array{title: string, meta: ?string, url: string, lang: ?string}>
     */
    private function branches(array $words, string $locale): array
    {
        $hits = [];
        foreach (Branch::query()->whereNull('archived_at')->orderBy('sort')->get() as $branch) {
            if (self::matches($words, [$branch->code, $branch->slug, $branch->name_ar, $branch->name_en, $branch->address_ar, $branch->address_en, $branch->landmark_ar, $branch->landmark_en])) {
                $name = $this->data->branchField($branch, $locale === 'ar' ? 'name_ar' : 'name_en');
                $hits[] = ['title' => is_string($name) ? $name : $branch->code, 'meta' => $branch->code, 'url' => route('dashboard.branches.show', $branch), 'lang' => null];
            }
        }

        return $hits;
    }

    /**
     * @param  list<string>  $words
     * @return list<array{title: string, meta: ?string, url: string, lang: ?string}>
     */
    private function pages(array $words): array
    {
        $stored = Page::query()->whereIn('key', array_keys(PageEditor::PAGES))->get()->keyBy('key');
        $hits = [];
        foreach (array_keys(PageEditor::PAGES) as $key) {
            /** @var Page|null $page */
            $page = $stored->get($key);
            if (self::matches($words, [$key, __('dashboard.pages.keys.'.$key, [], 'ar'), __('dashboard.pages.keys.'.$key, [], 'en'),
                $page?->title_ar, $page?->title_en, $page?->name_ar, $page?->name_en])) {
                $hits[] = ['title' => (string) __('dashboard.pages.keys.'.$key), 'meta' => null, 'url' => route('dashboard.pages.edit', $key), 'lang' => null];
            }
        }

        return $hits;
    }

    /**
     * Site texts by their label or by the wording the site shows now, in either language.
     *
     * @param  list<string>  $words
     * @return list<array{title: string, meta: ?string, url: string, lang: ?string}>
     */
    private function texts(array $words, string $locale): array
    {
        $hits = [];
        foreach (SiteTexts::GROUPS as $group => $keys) {
            foreach ($keys as $key) {
                $label = 'dashboard.texts.labels.'.str_replace('.', '_', $key);
                if (! self::matches($words, [__($label, [], 'ar'), __($label, [], 'en'), SiteTexts::current($key, 'ar'), SiteTexts::current($key, 'en')])) {
                    continue;
                }
                $wording = SiteTexts::current($key, $locale);
                $hits[] = ['title' => (string) __($label), 'meta' => __('dashboard.texts.groups.'.$group).($wording !== '' ? ' · '.mb_strimwidth($wording, 0, 80, '…') : ''),
                    'url' => route('dashboard.texts.index', ['page' => $group]).'#f-'.str_replace(['.', '_'], '-', $key), 'lang' => null];
            }
        }

        return $hits;
    }

    /**
     * @param  list<string>  $types
     * @param  list<string>  $words
     * @return list<array{title: string, meta: ?string, url: string, lang: ?string}>
     */
    private function experiences(array $types, string $route, array $words, bool $ar): array
    {
        $hits = [];
        foreach (Experience::query()->whereIn('type', $types)->orderByDesc('starts_at')->orderByDesc('id')->get() as $item) {
            if (self::matches($words, [$item->title_ar, $item->title_en, $item->slug, $item->body_ar, $item->body_en])) {
                $hits[] = ['title' => $this->title($item, $ar), 'meta' => $this->moment($item->starts_at), 'url' => route($route, $item), 'lang' => null];
            }
        }

        return $hits;
    }

    /**
     * Employee of the Month by its title, the month, or the person's name.
     *
     * @param  list<string>  $words
     * @return list<array{title: string, meta: ?string, url: string, lang: ?string}>
     */
    private function recognitions(array $words, string $locale): array
    {
        $items = Experience::query()->where('type', Recognitions::TYPE)->orderByDesc('starts_at')->orderByDesc('id')->get();
        $members = TeamMember::query()->whereIn('id', $items->map(fn (Experience $e): ?int => Recognitions::memberId($e))->filter()->all())->get()->keyBy('id');
        $hits = [];
        foreach ($items as $item) {
            $month = is_string($item->details['month'] ?? null) ? $item->details['month'] : null;
            /** @var TeamMember|null $member */
            $member = $members->get(Recognitions::memberId($item) ?? 0);
            if (self::matches($words, [$item->title_ar, $item->title_en, $month, Recognitions::period($month, 'ar'), Recognitions::period($month, 'en'),
                $member?->display_name_ar, $member?->display_name_en])) {
                $hits[] = ['title' => $this->title($item, $locale === 'ar'), 'meta' => Recognitions::period($month, $locale), 'url' => route('dashboard.recognition.edit', $item), 'lang' => null];
            }
        }

        return $hits;
    }

    /**
     * @param  list<string>  $words
     * @return list<array{title: string, meta: ?string, url: string, lang: ?string}>
     */
    private function team(array $words, bool $ar): array
    {
        $hits = [];
        foreach (TeamMember::query()->orderBy('sort_order')->orderBy('id')->get() as $member) {
            if (self::matches($words, [$member->display_name_ar, $member->display_name_en, $member->job_title_ar, $member->job_title_en, $member->department])) {
                $name = ($ar ? $member->display_name_ar : $member->display_name_en) ?? $member->display_name_ar ?? $member->display_name_en ?? '#'.$member->id;
                $hits[] = ['title' => $name, 'meta' => ($ar ? $member->job_title_ar : $member->job_title_en) ?? $member->job_title_ar ?? $member->job_title_en,
                    'url' => route('dashboard.team.edit', $member), 'lang' => null];
            }
        }

        return $hits;
    }

    /**
     * @param  list<string>  $words
     * @return list<array{title: string, meta: ?string, url: string, lang: ?string}>
     */
    private function awards(array $words, bool $ar): array
    {
        $hits = [];
        foreach (Award::query()->orderByDesc('year')->orderBy('id')->get() as $award) {
            if (self::matches($words, [$award->title_ar, $award->title_en, $award->issuer_ar, $award->issuer_en, (string) $award->year])) {
                $title = ($ar ? $award->title_ar : $award->title_en) ?? $award->title_ar ?? $award->title_en ?? '#'.$award->id;
                $hits[] = ['title' => $title, 'meta' => (string) $award->year, 'url' => route('dashboard.awards.edit', $award), 'lang' => null];
            }
        }

        return $hits;
    }

    /**
     * Images by their code or their description (alternative text) in either language.
     *
     * @param  list<string>  $words
     * @return list<array{title: string, meta: ?string, url: string, lang: ?string}>
     */
    private function media(array $words, string $locale): array
    {
        $hits = [];
        foreach (Media::query()->orderByDesc('id')->get(['id', 'code', 'alt_ar', 'alt_en']) as $media) {
            if (self::matches($words, [$media->code, $media->alt_ar, $media->alt_en])) {
                $hits[] = ['title' => $media->alt($locale) ?? $media->code, 'meta' => $media->code, 'url' => route('dashboard.media.edit', $media), 'lang' => null];
            }
        }

        return $hits;
    }

    /**
     * Requests by reference number, or by the applicant's name (the same two fields the requests lists search).
     *
     * @return list<array{title: string, meta: ?string, url: string, lang: ?string}>
     */
    private function requests(string $type, string $query, string $locale): array
    {
        [$table, $route] = $type === 'JOB' ? ['job_applications', 'dashboard.careers.show'] : ['partnership_applications', 'dashboard.partnerships.show'];
        $like = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query).'%';
        $reference = '%'.str_replace(['\\', '%', '_', ' '], ['\\\\', '\\%', '\\_', ''], mb_strtoupper($query)).'%';
        $rows = Application::query()->where('applications.type', $type)->join($table, $table.'.application_id', '=', 'applications.id')
            ->where(fn (Builder $w) => $w->where('applications.reference_number', 'like', $reference)->orWhere($table.'.full_name', 'like', $like))
            ->orderByDesc('applications.submitted_at')->orderByDesc('applications.id')
            ->get(['applications.id', 'applications.reference_number', 'applications.status', $table.'.full_name']);
        $labels = ApplicationInbox::labels($type, $locale);
        $hits = [];
        foreach ($rows as $row) {
            $status = (string) $row->status;
            $hits[] = ['title' => $row->reference_number.' · '.$row->getAttribute('full_name'), 'meta' => $labels[$status] ?? $status, 'url' => route($route, $row->id), 'lang' => null];
        }

        return $hits;
    }

    private function title(Experience $item, bool $ar): string
    {
        return ($ar ? $item->title_ar : $item->title_en) ?? $item->title_ar ?? $item->title_en ?? '#'.$item->id;
    }

    private function categoryName(?MenuCategory $category, bool $ar): string
    {
        if ($category === null) {
            return '';
        }

        // As on the menu screen: the Arabic name only once approved, otherwise the official English one.
        $approved = NameStatus::fromInventory($category->name_ar_status) === NameStatus::Approved && filled($category->name_ar);

        return (string) (($ar && $approved ? $category->name_ar : null) ?? $category->name_en ?? $category->source_name);
    }

    private function moment(?\DateTimeInterface $at): ?string
    {
        return $at === null ? null : CarbonImmutable::instance($at)->setTimezone('Asia/Amman')->format('Y-m-d');
    }
}
