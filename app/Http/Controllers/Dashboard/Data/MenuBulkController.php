<?php

namespace App\Http\Controllers\Dashboard\Data;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\Dashboard\MenuManager;
use App\Services\MasterData\MasterData;
use App\Support\DashboardBack;
use App\Support\Input;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Menu items, several at once (MENU-062): tick items on the menu list, choose what to do, confirm once on a page that
 * lists them (and asks for the section, or the branch, its availability and the reason), then each item changes with
 * its own history. Prices are never changed in bulk.
 */
final class MenuBulkController extends Controller
{
    public const ACTIONS = ['show', 'hide', 'move', 'branch'];

    public function confirm(Request $request, MasterData $data): View|RedirectResponse
    {
        $products = MenuManager::bulkProducts($request->all());
        $action = Input::text($request, 'action');
        if ($products->isEmpty()) {
            return redirect()->to(DashboardBack::to(route('dashboard.menu.index')))->with('warning', __('dashboard.menu.bulk.none'));
        }
        if (! in_array($action, self::ACTIONS, true)) {
            return redirect()->to(DashboardBack::to(route('dashboard.menu.index')))->with('warning', __('dashboard.menu.bulk.choose'));
        }
        $branches = [];
        foreach (Branch::query()->whereNull('archived_at')->orderBy('sort')->get() as $branch) {
            $name = $data->branchField($branch, app()->getLocale() === 'ar' ? 'name_ar' : 'name_en');
            $branches[$branch->id] = is_string($name) ? $name : $branch->code;
        }

        return view('dashboard.menu.bulk', [
            'action' => $action,
            'products' => $products,
            'categories' => MenuController::categoryOptions(),
            'branches' => $branches,
            'back' => DashboardBack::to(route('dashboard.menu.index')),
        ]);
    }

    public function apply(Request $request, MenuManager $menu, AuditLogger $audit): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $result = $menu->bulk($request->all(), $owner);
        if ($result['errors'] !== []) {
            return redirect()->route('dashboard.menu.bulk', $request->only(['action', 'ids']))->withInput()->withErrors($result['errors'], 'bulk');
        }
        $selected = MenuManager::bulkProducts($request->all())->count();
        $audit->record('menu.bulk_changed', null, [], ['action' => Input::text($request, 'action'), 'selected' => $selected, 'changed' => $result['changed']], actor: $owner);

        return redirect()->route('dashboard.menu.index')->with('status', trans_choice('dashboard.menu.bulk.done', $result['changed'], ['count' => $result['changed']]));
    }
}
