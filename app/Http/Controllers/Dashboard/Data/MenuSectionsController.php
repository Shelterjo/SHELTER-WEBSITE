<?php

namespace App\Http\Controllers\Dashboard\Data;

use App\Enums\NameStatus;
use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\User;
use App\Services\Menu\MenuSections;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Menu → Sections (M50): names, shown or hidden, order, and new sections. */
final class MenuSectionsController extends Controller
{
    public function index(): View
    {
        $categories = MenuCategory::query()->with('group')->orderBy('sort')->orderBy('id')->get();

        return view('dashboard.menu.sections', [
            'rows' => $categories->map(fn (MenuCategory $c): array => [
                'category' => $c,
                'items' => MenuSections::items($c),
                'approved' => NameStatus::fromInventory($c->name_ar_status) === NameStatus::Approved,
            ])->all(),
            'regular' => $categories->where('type', '!=', 'seasonal')->pluck('id')->values()->all(),
        ]);
    }

    public function update(Request $request, MenuCategory $category, MenuSections $sections): RedirectResponse
    {
        $errors = $sections->save($category, $request->all(), $this->owner($request));
        $url = route('dashboard.menu.sections').'#section-'.$category->id;

        return $errors !== [] ? redirect()->to($url)->withInput()->withErrors($errors, 'section'.$category->id)
            : redirect()->to($url)->with('status', __('dashboard.saved'));
    }

    public function store(Request $request, MenuSections $sections): RedirectResponse
    {
        $result = $sections->create($request->all(), $this->owner($request));
        if ($result['category'] === null) {
            return redirect()->to(route('dashboard.menu.sections').'#new-section')->withInput()->withErrors($result['errors'], 'new');
        }

        return redirect()->to(route('dashboard.menu.sections').'#section-'.$result['category']->id)->with('status', __('dashboard.menu.sections.created'));
    }

    public function move(Request $request, MenuCategory $category, string $direction, MenuSections $sections): RedirectResponse
    {
        $sections->move($category, $direction, $this->owner($request));

        return redirect()->to(route('dashboard.menu.sections').'#section-'.$category->id)->with('status', __('dashboard.saved'));
    }

    private function owner(Request $request): User
    {
        /** @var User $owner */
        $owner = $request->user();

        return $owner;
    }
}
