<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Experience;
use App\Models\HoursException;
use App\Models\Market;
use App\Models\MenuCategory;
use App\Models\User;
use App\Services\Core\FeatureFlags;
use App\Services\Dashboard\ExperienceCommands;
use App\Services\Experiences\Placements;
use App\Services\MasterData\MasterData;
use App\Services\Menu\MenuSeason;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Active now (DX-020, OPS-021/024): everything that changes the site at this moment — what each place shows and what
 * waits behind it (DX-021), live events, special hours and closures today, the menu's seasonal section — each with its end time and a "Stop now"
 * button; then what starts in the next 7 days. One source: the same resolver the site uses.
 */
final class LiveController extends Controller
{
    public function __invoke(Placements $placements, MasterData $data): View
    {
        $market = Market::query()->firstOrFail();
        $timezone = $market->timezone !== '' ? $market->timezone : 'Asia/Amman';
        $now = CarbonImmutable::now();
        $live = $placements->live($market, $now);
        $places = [];
        foreach (Placements::ALL as $placement) {
            $here = $live->filter(fn (Experience $e): bool => in_array($placement, $e->placements ?? [], true))->values();
            $places[$placement] = ['winner' => $here->first(), 'waiting' => $here->slice(1)->values()->all()];
        }
        $events = Experience::query()->where('type', 'event')->whereIn('status', ['scheduled', 'active'])->whereNull('archived_at')->where('emergency_disabled', false)
            ->where('starts_at', '<=', $now->utc())->where('ends_at', '>', $now->utc())->orderBy('ends_at')->get();
        $today = $now->setTimezone($timezone)->toDateString();
        $names = [];
        foreach (Branch::query()->get() as $branch) {
            $name = $data->branchField($branch, app()->getLocale() === 'ar' ? 'name_ar' : 'name_en');
            $names[$branch->id] = is_string($name) ? $name : $branch->code;
        }

        return view('dashboard.live', [
            'places' => $places,
            'events' => $events,
            'exceptions' => HoursException::query()->where('status', 'published')->whereDate('starts_on', '<=', $today)->whereDate('ends_on', '>=', $today)->orderBy('ends_on')->get(),
            'upcoming' => Experience::query()->whereIn('type', Placements::TYPES)->whereIn('status', ['scheduled', 'active'])->whereNull('archived_at')
                ->where('starts_at', '>', $now->utc())->where('starts_at', '<=', $now->addDays(7)->utc())->orderBy('starts_at')->get(),
            'branchNames' => $names,
            'timezone' => $timezone,
            'safe' => app(FeatureFlags::class)->enabled(FeatureFlags::SAFE_MODE),
            'seasons' => MenuCategory::query()->where('type', 'seasonal')->orderBy('sort')->get()
                ->map(fn (MenuCategory $c): array => ['category' => $c, 'state' => MenuSeason::state($c, $now->setTimezone($timezone))])->all(),
        ]);
    }

    /** "Stop now" (DX-020): takes it off the site at once; it comes back only with "Resume". */
    public function disable(Request $request, Experience $experience, ExperienceCommands $commands): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $done = $commands->run($experience, 'disable', $owner, $experience->type === 'event' ? 'events' : 'announcements');

        return redirect()->route('dashboard.live')->with($done ? 'status' : 'warning', $done ? __('dashboard.live.stopped') : __('dashboard.events.not_now'));
    }
}
