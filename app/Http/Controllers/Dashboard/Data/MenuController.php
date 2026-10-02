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
use App\Models\User;
use App\Services\Content\Search\Normalizer;
use App\Services\Dashboard\MenuManager;
use App\Services\MasterData\MasterData;
use App\Services\Menu\MenuCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        return view('dashboard.menu.show', [
            'product' => $product,
            'current' => $catalog->basePrice($product),
            'history' => $product->prices()->orderByDesc('valid_from')->orderByDesc('id')->limit(8)->get(),
            'source' => MenuSourceRow::query()->where('product_id', $product->id)->orderBy('id')->value('source_name_ar'),
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
