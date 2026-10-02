<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ContentVersion;
use App\Models\User;
use App\Services\Dashboard\ChangeHistory;
use App\Services\Dashboard\VersionRestore;
use App\Support\Input;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Change history (AUDIT-001…004, AUDIT-008/009, ROLLBACK.md §7 "restore an earlier version"): every audited change
 * with who, what, when and from → to, filtered by area, period or one item; an item's versions; and the restore of an
 * earlier version — previewed first, then saved as a new version through the item's own rules. Owner only (route
 * group); the restore needs a fresh re-confirmation (route middleware `confirmed`).
 */
final class HistoryController extends Controller
{
    public function index(Request $request, ChangeHistory $history): View
    {
        $filters = ChangeHistory::filters($request->query());
        $locale = app()->getLocale();
        $page = $history->page($filters, max(1, (int) $request->query('page', '1')));
        $rows = ChangeHistory::textRows($page->items());
        $item = null;
        if ($filters['item'] !== null) {
            $class = ChangeHistory::TYPES[$filters['item']['type']];
            $model = $class::query()->find($filters['item']['id']);
            $item = [
                'name' => $model instanceof Model ? ($history->itemName($model, null, $locale) ?? '#'.$filters['item']['id']) : __('dashboard.history.removed_item', ['id' => $filters['item']['id']]),
                'versions' => $model instanceof Model && ContentVersion::query()->where('versionable_type', $model->getMorphClass())->where('versionable_id', $model->getKey())->exists()
                    ? route('dashboard.history.versions', [$filters['item']['type'], $filters['item']['id']]) : null,
            ];
        }

        return view('dashboard.history.index', [
            'filters' => $filters,
            'item' => $item,
            'entries' => array_map(fn ($log) => $history->describe($log, $locale, $rows), $page->items()),
            'current' => $page->currentPage(),
            'last' => $page->lastPage(),
            'total' => $page->total(),
        ]);
    }

    /** An item's saved versions, newest first; the earlier ones that can come back have a preview. */
    public function versions(Request $request, string $type, int $id, ChangeHistory $history, VersionRestore $restore): View
    {
        $item = $this->item($type, $id);
        $locale = app()->getLocale();
        $versions = $restore->list($item, max(1, (int) $request->query('page', '1')));
        $latest = ContentVersion::query()->where('versionable_type', $item->getMorphClass())->where('versionable_id', $item->getKey())->max('version');

        return view('dashboard.history.versions', [
            'type' => $type,
            'id' => $id,
            'name' => $history->itemName($item, null, $locale) ?? '#'.$id,
            'versions' => $versions->items(),
            'latest' => (int) $latest,
            'blockers' => collect($versions->items())->mapWithKeys(fn (ContentVersion $v): array => [$v->id => $restore->blocker($v)])->all(),
            'current' => $versions->currentPage(),
            'last' => $versions->lastPage(),
        ]);
    }

    /** What the restore would change, field by field — nothing is saved here. */
    public function preview(ContentVersion $version, ChangeHistory $history, VersionRestore $restore): View
    {
        $item = $version->versionable;
        abort_unless($item instanceof Model && ChangeHistory::alias($item) !== null, 404);
        $locale = app()->getLocale();
        ['now' => $now, 'then' => $then] = $restore->state($version);
        $lines = [];
        foreach (VersionRestore::diff($now, $then) as $field) {
            $lines[] = [
                'field' => ChangeHistory::fieldLabel($field),
                'now' => ChangeHistory::value($now[$field] ?? null, $field, $locale, 20000),
                'then' => ChangeHistory::value($then[$field] ?? null, $field, $locale, 20000),
            ];
        }

        return view('dashboard.history.restore', [
            'version' => $version,
            'type' => (string) ChangeHistory::alias($item),
            'name' => $history->itemName($item, null, $locale) ?? '#'.$version->versionable_id,
            'kind' => $item::class,
            'state' => $item->getAttribute('status'),
            'lines' => $lines,
            'blocker' => $restore->blocker($version),
            'fingerprint' => $restore->fingerprint($version),
        ]);
    }

    public function restore(Request $request, ContentVersion $version, VersionRestore $restore): RedirectResponse
    {
        $item = $version->versionable;
        abort_unless($item instanceof Model && ChangeHistory::alias($item) !== null, 404);
        /** @var User $owner */
        $owner = $request->user();
        $result = $restore->restore($version, $owner, Input::text($request, 'fingerprint'), Input::text($request, 'note'));
        if ($result['errors'] !== []) {
            return redirect()->route('dashboard.history.restore', $version)->withErrors($result['errors'], 'restore')->withInput();
        }

        return redirect()->route('dashboard.history.versions', [ChangeHistory::alias($item), $item->getKey()])
            ->with('status', __('dashboard.history.restored', ['from' => $version->version, 'to' => $result['version']]));
    }

    private function item(string $type, int $id): Model
    {
        abort_unless(isset(ChangeHistory::TYPES[$type]), 404);
        $class = ChangeHistory::TYPES[$type];
        $item = $class::query()->find($id);
        abort_unless($item instanceof Model && ChangeHistory::alias($item) === $type, 404);

        return $item;
    }
}
