<?php

namespace App\Http\Controllers\Dashboard\Content;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\ContentVersion;
use App\Models\Experience;
use App\Models\Market;
use App\Models\User;
use App\Services\Dashboard\EventEditor;
use App\Services\Experiences\Events;
use App\Services\MasterData\MasterData;
use App\Support\Input;
use App\Support\PageUrl;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Content → Events (dashboard, M50, DX-014): every event with its state for visitors in plain words; one form to add or
 * change one with a live preview of what visitors will see in both languages; the commands (pause, resume, cancel,
 * end now, archive) and the last versions. Owner only (routes/dashboard.php).
 */
final class EventsController extends Controller
{
    private const TABS = ['current' => ['live', 'upcoming', 'paused', 'draft', 'incomplete'], 'past' => ['ended', 'cancelled'], 'archived' => ['archived']];

    public function index(Request $request, EventEditor $editor): View
    {
        $market = $this->market();
        $tab = array_key_exists(Input::query($request, 'show'), self::TABS) ? Input::query($request, 'show') : 'current';
        $now = CarbonImmutable::now();
        $rows = [];
        $counts = array_fill_keys(array_keys(self::TABS), 0);
        foreach (Experience::query()->where('type', 'event')->orderBy('starts_at')->orderBy('id')->get() as $event) {
            $state = $editor->state($event, $market, $now);
            foreach (self::TABS as $key => $states) {
                if (in_array($state, $states, true)) {
                    $counts[$key]++;
                    if ($key === $tab) {
                        $rows[] = ['event' => $event, 'state' => $state, 'when' => $this->when($event, $market)];
                    }
                }
            }
        }
        // Now and next first; the past newest first.
        $order = ['live' => 0, 'upcoming' => 1, 'paused' => 2, 'incomplete' => 3, 'draft' => 4];
        usort($rows, fn (array $a, array $b): int => $tab === 'current'
            ? [$order[$a['state']] ?? 9, $a['event']->starts_at?->getTimestamp() ?? PHP_INT_MAX] <=> [$order[$b['state']] ?? 9, $b['event']->starts_at?->getTimestamp() ?? PHP_INT_MAX]
            : ($b['event']->ends_at?->getTimestamp() ?? 0) <=> ($a['event']->ends_at?->getTimestamp() ?? 0));

        return view('dashboard.events.index', ['tab' => $tab, 'counts' => $counts, 'rows' => $rows]);
    }

    public function create(MasterData $data): View
    {
        return $this->form(new Experience(['type' => 'event', 'status' => 'draft']), $data);
    }

    public function store(Request $request, EventEditor $editor): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $result = $editor->save(null, $request->all(), $this->market(), $owner);
        if ($result['errors'] !== [] || $result['event'] === null) {
            return back()->withInput()->withErrors($result['errors']);
        }

        return redirect()->route('dashboard.events.edit', $result['event'])->with('status', __('dashboard.saved'));
    }

    public function edit(Experience $event, MasterData $data): View
    {
        abort_unless($event->type === 'event', 404);

        return $this->form($event, $data);
    }

    public function update(Request $request, Experience $event, EventEditor $editor): RedirectResponse
    {
        abort_unless($event->type === 'event', 404);
        /** @var User $owner */
        $owner = $request->user();
        $result = $editor->save($event, $request->all(), $this->market(), $owner);
        if ($result['errors'] !== []) {
            return back()->withInput()->withErrors($result['errors']);
        }

        return redirect()->route('dashboard.events.edit', $event)->with('status', __('dashboard.saved'));
    }

    public function command(Request $request, Experience $event, string $command, EventEditor $editor): RedirectResponse
    {
        abort_unless($event->type === 'event' && in_array($command, EventEditor::COMMANDS, true), 404);
        /** @var User $owner */
        $owner = $request->user();
        $done = $editor->command($event, $command, $owner);

        return redirect()->route('dashboard.events.edit', $event)
            ->with($done ? 'status' : 'warning', $done ? __('dashboard.events.done.'.$command) : __('dashboard.events.not_now'));
    }

    private function form(Experience $event, MasterData $data): View
    {
        $market = $this->market();
        $editor = app(EventEditor::class);
        $events = app(Events::class);
        $timezone = $market->timezone !== '' ? $market->timezone : 'Asia/Amman';

        return view('dashboard.events.form', [
            'event' => $event,
            'images' => AwardsController::usableImages(),
            'state' => $event->exists ? $editor->state($event, $market) : 'draft',
            'fixed' => $editor->addressFixed($event),
            'branches' => self::branchChoices($data),
            'starts' => $event->starts_at === null ? null : CarbonImmutable::instance($event->starts_at)->setTimezone($timezone),
            'ends' => $event->ends_at === null ? null : CarbonImmutable::instance($event->ends_at)->setTimezone($timezone),
            'preview' => $event->exists ? ['ar' => $events->preview($event, $market, 'ar'), 'en' => $events->preview($event, $market, 'en')] : null,
            'publicUrl' => $event->exists && in_array($editor->state($event, $market), ['live', 'upcoming', 'ended'], true)
                ? PageUrl::route('events.show', ['locale' => app()->getLocale() === 'ar' ? 'ar' : 'en', 'market' => $market->code, 'slug' => $event->slug]) : null,
            'versions' => $event->exists ? ContentVersion::query()->where('versionable_type', $event->getMorphClass())->where('versionable_id', $event->id)
                ->orderByDesc('version')->limit(5)->get() : collect(),
            'timezone' => $timezone,
            'market' => $market,
        ]);
    }

    /**
     * The branches an event or a campaign may name, each by its approved name in the dashboard language (else its code).
     *
     * @return array<int, string> id => name
     */
    public static function branchChoices(MasterData $data): array
    {
        $branches = [];
        foreach (Branch::query()->whereNull('archived_at')->orderBy('sort')->get() as $branch) {
            $name = $data->branchField($branch, app()->getLocale() === 'ar' ? 'name_ar' : 'name_en');
            $branches[$branch->id] = is_string($name) ? $name : $branch->code;
        }

        return $branches;
    }

    /** One line for the list: the day (and time) in the market's time zone. */
    private function when(Experience $event, Market $market): ?string
    {
        if ($event->starts_at === null) {
            return null;
        }
        $timezone = $market->timezone !== '' ? $market->timezone : 'Asia/Amman';

        return CarbonImmutable::instance($event->starts_at)->setTimezone($timezone)->format('Y-m-d H:i');
    }

    private function market(): Market
    {
        return Market::query()->firstOrFail();
    }
}
