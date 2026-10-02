<?php

namespace App\Services\Menu\Page;

use App\Enums\Availability;
use App\Enums\NameStatus;
use App\Models\Branch;
use App\Models\Market;
use App\Models\MenuCategory;
use App\Models\Product;
use App\Services\Menu\MenuCatalog;
use App\Services\Site\BranchDirectory;
use App\Services\Site\BranchSummary;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Builds the public menu page (Menu IA spec §2–§10) from the frozen inventory v1.0:
 * - sections in the approved order (F-06), SWEETS = CAKE + COOKIES as one display group (F-07), SPRING on top while
 *   its category is active and inside its dates (F-17);
 * - only approved names: an Arabic name appears only when approved, otherwise the English name leads (CF-03);
 *   category names likewise (P-01);
 * - subcategories are proposals (P-04), so they are not shown as headings yet — every product stays visible;
 * - per branch: the Owner's price and availability for that branch (dashboard → Menu); availability stays UNKNOWN —
 *   and nothing is said about it — until the Owner sets it or confirms the branch (CF-02, §9.6).
 */
final class MenuPage
{
    /** @var array<string, array{0: Branch, 1: bool}> branch slug => [branch, availability confirmed] */
    private array $branchModels = [];

    public function __construct(
        private readonly MenuCatalog $catalog,
        private readonly BranchDirectory $directory,
    ) {}

    public function build(Market $market, string $locale, ?string $branchParameter, ?CarbonImmutable $now = null): MenuView
    {
        $now ??= CarbonImmutable::now($market->timezone);

        $summaries = $this->directory->forMarket($market, $locale, $now);
        // Each branch's availability is a claim only once confirmed or set explicitly (D-094): one fact lookup per branch.
        $this->branchModels = [];
        foreach ($summaries as $summary) {
            $this->branchModels[$summary->branch->slug] = [$summary->branch, $this->catalog->availabilityConfirmed($summary->branch)];
        }
        $branches = array_map(
            fn (BranchSummary $branch): BranchOption => new BranchOption(
                $branch->branch->slug,
                strtoupper($branch->branch->slug),
                $branch->status === null ? null : ['state' => $branch->status->state(), 'text' => $branch->status->text()],
                [],
                $branch->status,
            ),
            $summaries,
        );
        $slugs = array_map(fn (BranchOption $b): string => $b->slug, $branches);
        $selected = in_array($branchParameter, $slugs, true) ? (string) $branchParameter : 'all';

        /** @var Collection<int, MenuCategory> $categories */
        $categories = MenuCategory::query()
            ->with(['group', 'products' => fn ($q) => $this->visibleProducts($q)->with(['prices', 'branchOverrides'])])
            ->orderBy('sort')
            ->get();

        $season = null;
        $sections = [];
        $grouped = [];
        foreach ($categories as $category) {
            if ($category->type === 'seasonal') {
                if ($this->seasonActive($category, $now)) {
                    $season = $this->section($category->slug ?? Str::slug((string) $category->name_en), (string) $category->code, $this->categoryName($category, $locale), [
                        new MenuGroup(null, null, null, null, $this->items($category, $locale, $now, seasonal: true)),
                    ]);
                }

                continue;
            }
            if ($category->group !== null) {
                $grouped[$category->group->code][] = $category;

                continue;
            }
            $sections[] = $this->section($category->slug ?? Str::slug((string) $category->name_en), (string) $category->code, $this->categoryName($category, $locale), [
                new MenuGroup(null, null, null, null, $this->items($category, $locale, $now)),
            ]);
        }

        // Display groups (SWEETS) take the place of their first category in the approved order.
        foreach ($grouped as $members) {
            $group = $members[0]->group;
            $groups = array_map(function (MenuCategory $category) use ($locale, $now): MenuGroup {
                [$name, $lang] = $this->categoryName($category, $locale);

                return new MenuGroup(Str::slug((string) $category->name_en), $name, $lang, (string) $category->code, $this->items($category, $locale, $now));
            }, $members);
            $nameAr = $group?->name_ar;
            $name = $locale === 'ar' && filled($nameAr) ? [(string) $nameAr, null] : [(string) $group?->name_en, $locale === 'ar' ? 'en' : null];
            $sections[] = $this->section((string) $group?->code, (string) $group?->code, $name, $groups);
        }

        $sections = array_values(array_filter($sections, fn (MenuSection $section): bool => $section->count() > 0));

        return new MenuView($locale, 'MV-2026-10-01', $market->timezone, $season, $sections, $branches, $selected);
    }

    /**
     * @param  Builder<Product>|HasMany<Product, MenuCategory>  $query
     * @return Builder<Product>|HasMany<Product, MenuCategory>
     */
    private function visibleProducts($query)
    {
        return $query->where('status', 'active')->whereNull('merged_into_id')->orderBy('sort');
    }

    private function seasonActive(MenuCategory $category, CarbonImmutable $now): bool
    {
        if (! in_array($category->status, ['active', 'published'], true)) {
            return false;
        }
        $today = $now->toDateString();

        return ($category->season_starts_on === null || $category->season_starts_on->toDateString() <= $today)
            && ($category->season_ends_on === null || $category->season_ends_on->toDateString() >= $today);
    }

    /** @return array{0: string, 1: ?string} name and its language when it differs from the page */
    private function categoryName(MenuCategory $category, string $locale): array
    {
        if ($locale === 'ar') {
            $approved = NameStatus::fromInventory((string) $category->name_ar_status) === NameStatus::Approved && filled($category->name_ar);

            return $approved ? [(string) $category->name_ar, null] : [(string) $category->name_en, 'en'];
        }

        return [(string) $category->name_en, null];
    }

    /**
     * @param  array{0: string, 1: ?string}  $name
     * @param  list<MenuGroup>  $groups
     */
    private function section(string $id, string $code, array $name, array $groups): MenuSection
    {
        return new MenuSection($id, $code, $name[0], $name[1], $groups, false);
    }

    /** @return list<MenuItem> */
    private function items(MenuCategory $category, string $locale, CarbonImmutable $now, bool $seasonal = false): array
    {
        [$location] = $this->categoryName($category, $locale);
        $items = [];
        foreach ($category->products as $product) {
            $price = $this->catalog->price($product, null, $now);
            $nameEn = (string) $product->display_name_en;
            if ($price === null || $nameEn === '') {
                continue; // no approved price or name → not shown (never invented)
            }
            $nameAr = $this->catalog->name($product, 'ar');
            [$name, $nameLang, $secondary, $secondaryLang] = $locale === 'ar'
                ? ($nameAr !== null ? [$nameAr, null, $nameEn, 'en'] : [$nameEn, 'en', null, null])
                : [$nameEn, null, $nameAr, $nameAr !== null ? 'ar' : null];

            $availability = [];
            $branchPrices = [];
            foreach ($this->branchModels as $slug => [$branch, $confirmed]) {
                $availability[$slug] = $this->catalog->availability($product, $branch, $confirmed);
                $branchPrice = $this->catalog->price($product, $branch, $now);
                if ($branchPrice !== null && $branchPrice->fils !== $price->fils) {
                    $branchPrices[$slug] = $branchPrice->fils;
                }
            }

            $items[] = new MenuItem(
                code: (string) $product->code,
                slug: Str::slug($nameEn).'-'.strtolower(substr((string) $product->code, -3)),
                name: $name,
                nameLang: $nameLang,
                secondary: $secondary,
                secondaryLang: $secondaryLang,
                nameEn: $nameEn,
                priceFils: $price->fils,
                currency: $price->currency,
                seasonal: $seasonal || (bool) $product->is_seasonal,
                categoryCode: (string) $category->code,
                subcategoryCode: null,
                sectionId: $category->slug ?? Str::slug((string) $category->name_en),
                location: $location,
                availability: $availability,
                branchPrices: $branchPrices,
                searchTerms: array_values(array_filter([
                    $nameEn,
                    $nameAr,
                    $product->normalized_name_en,
                    $product->normalized_name_ar,
                ])),
            );
        }

        return $items;
    }

    /** Spec §9.6: UNKNOWN is never shown as a claim. */
    public static function showsAvailability(Availability $state): bool
    {
        return $state !== Availability::Unknown;
    }
}
