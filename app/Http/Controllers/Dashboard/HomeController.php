<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\PublishStatus;
use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\Media;
use App\Models\Page;
use App\Models\Recruitment\Application;
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
    public function __invoke(): View
    {
        $live = Page::query()->whereIn('key', array_keys(PageEditor::PAGES))->where('status', PublishStatus::Published)->count();
        $tiles = [
            ['label' => __('dashboard.home.new_job_applications'), 'value' => Application::query()->where('type', 'JOB')->where('status', 'received')->count(), 'route' => 'dashboard.careers.index'],
            ['label' => __('dashboard.home.new_partnership_applications'), 'value' => Application::query()->where('type', 'FR')->where('status', 'received')->count(), 'route' => 'dashboard.partnerships.index'],
            ['label' => __('dashboard.home.feedback_week'), 'value' => Feedback::query()->where('submitted_at', '>=', now()->subDays(7))->count(), 'route' => 'dashboard.feedback.index'],
            ['label' => __('dashboard.home.media_pending'), 'value' => Media::query()->where('approval_status', 'PENDING OWNER APPROVAL')->whereNull('archived_at')->count(), 'route' => 'dashboard.media.index'],
            ['label' => __('dashboard.home.pages_not_live'), 'value' => count(PageEditor::PAGES) - $live, 'route' => 'dashboard.pages.index'],
        ];

        return view('dashboard.home', [
            'tiles' => array_map(fn (array $t): array => $t + ['href' => Route::has($t['route']) ? route($t['route']) : null], $tiles),
        ]);
    }
}
