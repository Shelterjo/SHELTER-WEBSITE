<?php

namespace App\Http\Middleware;

use App\Models\Page;
use App\Support\DashboardBack;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * Two tabs, one record (FINAL-QA QA-044): a dashboard save carries the moment its page was rendered (`_seen_at`, set by
 * the dashboard script on submit). When the record it changes was saved after that moment — from another tab or device
 * — the save is refused and the Owner sees why, with what they typed kept, instead of the newer change being silently
 * overwritten. Saves without `_seen_at` (no script) pass as before.
 */
final class PreventStaleEdits
{
    public const FIELD = '_seen_at';

    public function handle(Request $request, Closure $next): Response
    {
        $seen = $request->input(self::FIELD);
        if ($request->isMethodSafe() || ! is_string($seen) || preg_match('/^\d{9,11}$/', $seen) !== 1) {
            return $next($request);
        }
        foreach ($this->records($request) as $record) {
            $updated = $record->getAttribute($record->getUpdatedAtColumn() ?? 'updated_at');
            if ($updated instanceof Carbon && $updated->getTimestamp() > (int) $seen) {
                return redirect()->to(DashboardBack::to(route('dashboard.home')))
                    ->withInput($request->except(['_token', '_method', self::FIELD, 'password', 'code', 'files']))
                    ->with('warning', __('dashboard.stale_edit'));
            }
        }

        return $next($request);
    }

    /** @return list<Model> the records this request changes: bound route models, and a brand page addressed by key. */
    private function records(Request $request): array
    {
        $route = $request->route();
        if ($route === null || is_string($route)) {
            return [];
        }
        $records = [];
        foreach ($route->parameters() as $name => $value) {
            if ($value instanceof Model && $value->usesTimestamps()) {
                $records[] = $value;
            } elseif ($name === 'key' && is_string($value) && $route->getName() === 'dashboard.pages.update') {
                $page = Page::query()->where('key', $value)->first();
                if ($page !== null) {
                    $records[] = $page;
                }
            }
        }

        return $records;
    }
}
