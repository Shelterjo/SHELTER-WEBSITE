<?php

namespace App\Services\Dashboard;

use App\Enums\ContactKind;
use App\Http\Middleware\Indexing;
use App\Models\Branch;
use App\Models\MenuCategory;
use App\Models\Product;
use App\Services\Content\SiteTexts;
use App\Services\MasterData\MasterData;

/**
 * Local SEO health for the Owner (M57 §48): what search engines and map apps can already read about SHELTER and what
 * is still missing, in plain words, each gap linked to the screen that fixes it. Read-only — it only looks at the
 * master data, the fact registry and the site texts; it changes nothing and calls no outside service (Search Console
 * is not connected).
 */
final class SeoHealth
{
    /** Google shows about this many characters of a title and a description. */
    public const TITLE_MAX = 60;

    public const DESCRIPTION_MAX = 160;

    /** page => [title key, description key, Site texts group]; :name / :kind / :brand are filled for the preview. */
    public const PAGES = [
        'home' => ['site.home.title', 'site.meta.home', 'home'],
        'menu' => ['menu.page_title', 'site.meta.menu', 'menu'],
        'locations' => ['site.titles.locations', 'site.meta.locations', 'locations'],
        'branch' => ['site.branch.title', 'site.meta.branch', 'locations'],
        'contact' => ['site.titles.contact', 'site.meta.contact', 'contact'],
        'events' => ['site.titles.events', 'site.meta.events', 'events'],
        'careers' => ['site.titles.careers', 'site.meta.careers', 'forms'],
    ];

    public function __construct(private readonly MasterData $data) {}

    /**
     * Per branch: each fact a map or search result uses, and whether the site can show it (approved).
     *
     * @return list<array{branch: Branch, name: string, checks: array<string, bool>}>
     */
    public function branches(string $locale): array
    {
        $phone = $this->data->contact(ContactKind::PhoneMain);
        $out = [];
        foreach (Branch::query()->with('city')->orderBy('sort')->get() as $branch) {
            $name = $this->data->branchField($branch, 'name_'.$locale);
            $answered = $branch->branchAttributes()->whereNotNull('value')->exists();
            $out[] = [
                'branch' => $branch,
                'name' => is_string($name) ? $name : $branch->code,
                'checks' => [
                    'names' => $this->data->branchField($branch, 'name_ar') !== null && $this->data->branchField($branch, 'name_en') !== null,
                    'hours' => $this->data->regularHours($branch) !== null,
                    'phone' => $phone !== null && $phone->show_on_branch_cards,
                    'city_ar' => $this->data->cityName($branch->city, 'ar') !== null,
                    'landmark' => $this->data->branchField($branch, 'landmark_ar') !== null && $this->data->branchField($branch, 'landmark_en') !== null,
                    'address' => $this->data->branchField($branch, 'address_ar') !== null && $this->data->branchField($branch, 'address_en') !== null,
                    'maps' => $this->data->branchField($branch, 'maps_url') !== null,
                    'coordinates' => $this->data->branchField($branch, 'latitude') !== null && $this->data->branchField($branch, 'longitude') !== null,
                    'services' => $answered,
                ],
            ];
        }

        return $out;
    }

    /**
     * Each page's Google title and description as they show now, with what is wrong (empty, too long, repeated).
     *
     * @return list<array{page: string, group: string, locale: string, title: string, description: string, issues: list<string>}>
     */
    public function texts(): array
    {
        $rows = [];
        $seen = ['title' => [], 'description' => []];
        foreach (['ar', 'en'] as $locale) {
            foreach (self::PAGES as $page => [$titleKey, $descriptionKey, $group]) {
                $fill = ['name' => (string) __('dashboard.seo.sample_branch', [], $locale), 'kind' => (string) __('dashboard.seo.sample_kind', [], $locale), 'brand' => (string) __('site.brand', [], $locale)];
                $title = self::fill(SiteTexts::current($titleKey, $locale), $fill);
                $description = self::fill(SiteTexts::current($descriptionKey, $locale), $fill);
                $issues = [];
                if ($description === '') {
                    $issues[] = 'no_description';
                } elseif (mb_strlen($description) > self::DESCRIPTION_MAX) {
                    $issues[] = 'description_long';
                }
                if (mb_strlen($title) > self::TITLE_MAX) {
                    $issues[] = 'title_long';
                }
                foreach (['title' => $title, 'description' => $description] as $kind => $text) {
                    if ($text !== '' && isset($seen[$kind][$locale."\0".$text])) {
                        $issues[] = $kind.'_repeated';
                    }
                    $seen[$kind][$locale."\0".$text] = true;
                }
                $rows[] = ['page' => $page, 'group' => $group, 'locale' => $locale, 'title' => $title, 'description' => $description, 'issues' => $issues];
            }
        }

        return $rows;
    }

    /**
     * Arabic searches read the Arabic menu: sections and items still shown with their English names only.
     *
     * @return array{sections_missing: int, sections: int, items_missing: int, items: int}
     */
    public function arabicMenu(): array
    {
        return [
            'sections_missing' => MenuCategory::query()->whereNull('name_ar')->count(),
            'sections' => MenuCategory::query()->count(),
            'items_missing' => Product::query()->whereNull('merged_into_id')->whereNull('display_name_ar')->count(),
            'items' => Product::query()->whereNull('merged_into_id')->count(),
        ];
    }

    public function indexable(): bool
    {
        return Indexing::siteIndexable();
    }

    /** @param array<string, string> $fill */
    private static function fill(string $text, array $fill): string
    {
        foreach ($fill as $key => $value) {
            $text = str_replace(':'.$key, $value, $text);
        }

        return trim($text);
    }
}
