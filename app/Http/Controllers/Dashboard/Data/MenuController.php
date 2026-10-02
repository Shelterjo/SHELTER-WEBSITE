<?php

namespace App\Http\Controllers\Dashboard\Data;

use App\Enums\NameStatus;
use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Dashboard\Content\AwardsController;
use App\Models\Branch;
use App\Models\ContentVersion;
use App\Models\MenuCategory;
use App\Models\MenuSourceRow;
use App\Models\Product;
use App\Models\SearchAlias;
use App\Models\User;
use App\Services\Content\Search\Normalizer;
use App\Services\Content\Search\Search;
use App\Services\Content\Search\SearchLog;
use App\Services\Core\FeatureFlags;
use App\Services\Dashboard\MenuManager;
use App\Services\MasterData\MasterData;
use App\Services\Menu\MenuCatalog;
use App\Services\Menu\MenuEditor;
use App\Services\Menu\MenuSeason;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Business data → Menu (dashboard, M50, M33 §5, §16–§18): the products by category with their price and branch
 * differences, one screen per product (names, base price with its history, each branch's price and availability) and
 * the review of the Arabic names a category at a time (D-137). Changing a price needs a fresh re-confirmation.
 */
final class MenuController extends Controller
{
    public function index(Request $request, MenuCatalog $catalog): View
    {
        $categories = MenuCategory::query()->withCount([
            'products as shown_count' => fn ($q) => $q->where('status', Product::STATUS_ACTIVE)->whereNull('merged_into_id'),
        ])->orderBy('sort')->get();
        $q = trim($request->string('q')->toString());
        $code = $request->string('category')->toString();
        $category = $q === '' ? ($categories->firstWhere('code', $code) ?? $categories->first()) : null;

        $query = Product::query()->where('status', Product::STATUS_ACTIVE)->whereNull('merged_into_id')->with(['prices', 'branchOverrides', 'category'])->orderBy('sort');
        if ($category !== null) {
            $query->where('menu_category_id', $category->id);
        }
        $products = $query->get();
        if ($q !== '') {
            $needle = Normalizer::normalize($q);
            $products = $products->filter(fn (Product $p): bool => str_contains(Normalizer::normalize(implode(' ', array_filter([
                $p->code, $p->display_name_en, $p->display_name_ar, $p->normalized_name_en, $p->normalized_name_ar,
            ]))), $needle))->values();
        }
        $pending = Product::query()->where('status', Product::STATUS_ACTIVE)->whereNull('merged_into_id')->get(['menu_category_id', 'name_ar_status'])
            ->filter(fn (Product $p): bool => NameStatus::fromInventory($p->name_ar_status) !== NameStatus::Approved)
            ->countBy('menu_category_id')->all();

        return view('dashboard.menu.index', [
            'categories' => $categories,
            'category' => $category,
            'q' => $q,
            'pending' => $pending,
            'rows' => $products->map(fn (Product $p): array => [
                'product' => $p,
                'arabic' => NameStatus::fromInventory($p->name_ar_status) === NameStatus::Approved ? $p->display_name_ar : null,
                'price' => $catalog->basePrice($p)?->price_fils,
                'branches' => $p->branchOverrides->count(),
                'hidden' => $p->publish_status === PublishStatus::Archived,
            ])->all(),
            'branchNames' => $this->branchNames(),
        ]);
    }

    public function show(Product $product, MenuCatalog $catalog): View
    {
        $product->load(['category', 'branchOverrides']);
        $branches = [];
        $names = $this->branchNames();
        $byCode = [];
        foreach (Branch::query()->whereNull('archived_at')->orderBy('sort')->get() as $branch) {
            $byCode[$branch->code] = $names[$branch->id] ?? $branch->code;
            $override = $product->branchOverrides->firstWhere('branch_id', $branch->id);
            $branches[] = [
                'branch' => $branch,
                'price' => $override?->price_fils,
                'state' => $override?->availability->value ?? 'inherit',
                'shown' => $catalog->availability($product, $branch)->value,
                'resolved' => $catalog->price($product, $branch)?->fils,
            ];
        }

        $source = MenuSourceRow::query()->where('product_id', $product->id)->orderBy('id')->value('source_name_ar');
        $words = $product->searchAliases()->get();
        $taken = array_map(Normalizer::normalize(...), array_filter([$product->display_name_en, $product->normalized_name_en, $product->display_name_ar, ...$words->pluck('value')->all()]));

        return view('dashboard.menu.show', [
            'product' => $product,
            'words' => $words,
            // The name in the source menu file, offered as a search word when the site does not show it (PO-042 stays
            // open: nothing is added without the Owner pressing the button for this item).
            'suggestion' => is_string($source) && ! in_array(Normalizer::normalize($source), $taken, true) ? $source : null,
            'categories' => self::categoryOptions(),
            'current' => $catalog->basePrice($product),
            'history' => $product->prices()->orderByDesc('valid_from')->orderByDesc('id')->limit(8)->get(),
            'source' => $source,
            'approved' => NameStatus::fromInventory($product->name_ar_status) === NameStatus::Approved,
            'images' => AwardsController::usableImages(),
            'branches' => $branches,
            'branchNames' => $names,
            'branchByCode' => $byCode,
            'versions' => ContentVersion::query()->where('versionable_type', $product->getMorphClass())->where('versionable_id', $product->id)
                ->orderByDesc('version')->limit(5)->get(),
        ]);
    }

    public function names(Request $request, Product $product, MenuManager $menu): RedirectResponse
    {
        $errors = $menu->saveNames($product, $request->all(), $this->owner($request));

        return $this->back($product, 'names', $errors, 'names');
    }

    public function details(Request $request, Product $product, MenuManager $menu): RedirectResponse
    {
        $errors = $menu->saveDetails($product, $request->all(), $this->owner($request));

        return $this->back($product, 'details', $errors, 'details');
    }

    public function categoryName(Request $request, MenuCategory $category, MenuManager $menu): RedirectResponse
    {
        $errors = $menu->saveCategoryName($category, $request->all(), $this->owner($request));
        if ($errors !== []) {
            return redirect()->route('dashboard.menu.review', $category)->withInput()->withErrors($errors, 'category');
        }

        return redirect()->route('dashboard.menu.review', $category)->with('status', __('dashboard.saved'));
    }

    public function price(Request $request, Product $product, MenuManager $menu): RedirectResponse
    {
        $errors = $menu->changePrice($product, $request->all(), $this->owner($request));

        return $this->back($product, 'base-price', $errors, 'price');
    }

    public function branch(Request $request, Product $product, Branch $branch, MenuManager $menu): RedirectResponse
    {
        $errors = $menu->saveBranch($product, $branch, $request->all(), $this->owner($request));

        return $this->back($product, 'b'.$branch->id, $errors, 'b'.$branch->id);
    }

    public function review(MenuCategory $category): View
    {
        /** @var Collection<int, Product> $products */
        $products = Product::query()->where('menu_category_id', $category->id)->where('status', Product::STATUS_ACTIVE)->whereNull('merged_into_id')->orderBy('sort')->get();
        $sources = MenuSourceRow::query()->whereIn('product_id', $products->modelKeys())->orderBy('id')->get(['product_id', 'source_name_ar'])
            ->groupBy('product_id')->map(fn ($rows) => $rows->first()?->source_name_ar)->all();

        return view('dashboard.menu.review', [
            'category' => $category,
            'categoryApproved' => NameStatus::fromInventory($category->name_ar_status) === NameStatus::Approved,
            'rows' => $products->map(fn (Product $p): array => [
                'product' => $p,
                'approved' => NameStatus::fromInventory($p->name_ar_status) === NameStatus::Approved,
                'source' => $sources[$p->id] ?? null,
            ])->all(),
        ]);
    }

    public function saveReview(Request $request, MenuCategory $category, MenuManager $menu): RedirectResponse
    {
        $result = $menu->reviewCategory($category, $request->all(), $this->owner($request));
        if ($result['errors'] !== []) {
            return back()->withInput()->withErrors($result['errors']);
        }

        return redirect()->route('dashboard.menu.review', $category)
            ->with('status', trans_choice('dashboard.menu.review_done', $result['approved'], ['count' => $result['approved']]));
    }

    public function create(Request $request): View
    {
        return view('dashboard.menu.create', ['categories' => self::categoryOptions(), 'selected' => $request->integer('category') ?: null]);
    }

    public function store(Request $request, MenuManager $menu): RedirectResponse
    {
        $result = $menu->createProduct($request->all(), $this->owner($request));
        if ($result['product'] === null) {
            return redirect()->route('dashboard.menu.create')->withInput()->withErrors($result['errors'], 'create');
        }

        return redirect()->route('dashboard.menu.show', $result['product'])->with('status', __('dashboard.menu.created'));
    }

    public function searchWord(Request $request, Product $product, MenuManager $menu): RedirectResponse
    {
        $result = $menu->addSearchWord($product, $request->all(), $this->owner($request));
        if ($result['errors'] !== []) {
            return $this->back($product, 'search-words', $result['errors'], 'words');
        }

        return redirect()->to(route('dashboard.menu.show', $product).'#search-words')->with('status', $result['others'] === []
            ? __('dashboard.saved') : __('dashboard.menu.word_shared', ['items' => implode(' · ', $result['others'])]));
    }

    public function archiveWord(Request $request, Product $product, SearchAlias $word, MenuEditor $editor): RedirectResponse
    {
        abort_unless($word->product_id === $product->id, 404);
        $editor->archiveSearchWord($word, $this->owner($request));

        return redirect()->to(route('dashboard.menu.show', $product).'#search-words')->with('status', __('dashboard.menu.word_removed', ['word' => $word->value]));
    }

    /**
     * The search words in one place (CMS-018): a box to try what customers would find, every word by item, and — once
     * anonymous search counting is switched on (PO-019) — what people searched for and did not find.
     */
    public function words(Request $request, Search $search, FeatureFlags $flags): View
    {
        $q = mb_substr(trim($request->string('q')->toString()), 0, 60);
        $logging = $flags->enabled(SearchLog::FLAG);

        return view('dashboard.menu.words', [
            'q' => $q,
            'hits' => $q === '' ? null : ($search->search($q, app()->getLocale())->groups['menu'] ?? []),
            'items' => SearchAlias::query()->where('status', SearchAlias::STATUS_APPROVED)->with('product')->orderBy('product_id')->orderBy('id')->get()
                ->groupBy('product_id')->map(fn ($words) => ['product' => $words->first()?->product, 'words' => $words])->values(),
            'logging' => $logging,
            'missed' => ! $logging ? collect() : DB::table('search_query_daily')->where('day', '>=', now()->subDays(30)->toDateString())->where('zero_results', '>', 0)
                ->where('query_norm', '!=', '[redacted]')->groupBy('query_norm')->selectRaw('query_norm, sum(zero_results) as times')->orderByDesc('times')->limit(20)->get(),
        ]);
    }

    /** The seasonal section (CMS-009, MENU-044, F-17): what customers see now, the Owner's choice, its dates and items. */
    public function season(): View
    {
        $now = CarbonImmutable::now('Asia/Amman');

        return view('dashboard.menu.season', [
            'seasons' => MenuCategory::query()->where('type', 'seasonal')->orderBy('sort')->get()->map(fn (MenuCategory $c): array => [
                'category' => $c,
                'state' => MenuSeason::state($c, $now),
                'mode' => MenuSeason::mode($c),
                'approved' => NameStatus::fromInventory($c->name_ar_status) === NameStatus::Approved,
                'products' => Product::query()->where('menu_category_id', $c->id)->where('status', Product::STATUS_ACTIVE)->whereNull('merged_into_id')->orderBy('sort')->get(),
            ])->all(),
        ]);
    }

    public function saveSeason(Request $request, MenuCategory $category, MenuManager $menu): RedirectResponse
    {
        abort_unless($category->type === 'seasonal', 404);
        $errors = $menu->saveSeason($category, $request->all(), $this->owner($request));
        $url = route('dashboard.menu.season').'#season-'.$category->id;
        if ($errors !== []) {
            return redirect()->to($url)->withInput()->withErrors($errors, 'season'.$category->id);
        }

        return redirect()->to($url)->with('status', __('dashboard.saved'));
    }

    /** @return array<int, string> every section by its shown name; the season says so */
    public static function categoryOptions(): array
    {
        $ar = app()->getLocale() === 'ar';
        $options = [];
        foreach (MenuCategory::query()->orderBy('sort')->get() as $c) {
            $name = $ar && NameStatus::fromInventory($c->name_ar_status) === NameStatus::Approved && filled($c->name_ar) ? (string) $c->name_ar : (string) $c->name_en;
            $options[$c->id] = $c->type === 'seasonal' ? $name.' — '.__('dashboard.menu.season.label') : $name;
        }

        return $options;
    }

    /** @param  array<string, string>  $errors */
    private function back(Product $product, string $anchor, array $errors, string $bag): RedirectResponse
    {
        $url = route('dashboard.menu.show', $product).'#'.$anchor;
        if ($errors !== []) {
            return redirect()->to($url)->withInput()->withErrors($errors, $bag);
        }

        return redirect()->to($url)->with('status', __('dashboard.saved'));
    }

    /** @return array<int, string> */
    private function branchNames(): array
    {
        $data = app(MasterData::class);
        $names = [];
        foreach (Branch::query()->whereNull('archived_at')->orderBy('sort')->get() as $branch) {
            $name = $data->branchField($branch, app()->getLocale() === 'ar' ? 'name_ar' : 'name_en');
            $names[$branch->id] = is_string($name) ? $name : $branch->code;
        }

        return $names;
    }

    private function owner(Request $request): User
    {
        /** @var User $owner */
        $owner = $request->user();

        return $owner;
    }
}
