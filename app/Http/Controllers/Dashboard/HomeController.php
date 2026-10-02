<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Media;
use App\Models\Page;
use App\Models\Recruitment\Application;
use App\Services\Core\Attention;
use App\Services\Dashboard\PageEditor;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

/**
 * Command Center (FINAL-ARCHITECTURE-REVIEW §10, kept simple for the Owner — M50): the few numbers that need a look —
 * new job and partnership applications, feedback this week, images waiting for approval, pages not live yet — each a
 * link to its screen once that screen exists. Counts only: no personal data on this screen.
 */
final class HomeController extends Controller
{
    public function __invoke(Attention $attention): View
    {
        $live = Page::query()->whereIn('key', array_keys(PageEditor::PAGES))->where('status', PublishStatus::Published)->count();
        $tiles = [
            // MON-007: open issues first — the one number that always deserves a look.
            ['label' => __('dashboard.attention.tile'), 'value' => $attention->count(), 'route' => 'dashboard.attention'],
            // "New" = not opened yet — a read state, not a status (CAREERS-054, FRAN-055).
            ['label' => __('dashboard.home.new_job_applications'), 'value' => $this->unseen('JOB'), 'route' => 'dashboard.careers.index', 'params' => ['status' => 'new', 'period' => 'all']],
            ['label' => __('dashboard.home.new_partnership_applications'), 'value' => $this->unseen('FR'), 'route' => 'dashboard.partnerships.index', 'params' => ['status' => 'new']],
            ['label' => __('dashboard.home.feedback_week'), 'value' => Feedback::query()->where('submitted_at', '>=', now()->subDays(7))->count(), 'route' => 'dashboard.feedback.index'],
            ['label' => __('dashboard.home.media_pending'), 'value' => Media::query()->where('approval_status', 'PENDING OWNER APPROVAL')->whereNull('archived_at')->count(), 'route' => 'dashboard.media.index', 'params' => ['show' => 'pending']],
            ['label' => __('dashboard.home.pages_not_live'), 'value' => count(PageEditor::PAGES) - $live, 'route' => 'dashboard.pages.index'],
        ];

        return view('dashboard.home', [
            'attention' => $attention->open(app()->getLocale(), 3),
            'tiles' => array_map(fn (array $t): array => $t + ['href' => Route::has($t['route']) ? route($t['route'], $t['params'] ?? []) : null], $tiles),
        ]);
    }

    private function unseen(string $type): int
    {
        return Application::query()->where('type', $type)->whereNull('first_viewed_at')->where('status', '!=', 'archived')->count();
    }
}
