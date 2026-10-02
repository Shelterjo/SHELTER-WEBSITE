<?php

namespace App\Http\Controllers\Dashboard\Content;

use App\Http\Controllers\Controller;
use App\Models\ContentVersion;
use App\Models\Experience;
use App\Models\Market;
use App\Models\User;
use App\Services\Dashboard\AnnouncementEditor;
use App\Services\Dashboard\ExperienceCommands;
use App\Services\MasterData\MasterData;
use App\Services\Media\MediaLibrary;
use App\Support\Input;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Content → Announcements and campaigns (dashboard, M50, DX-010…021, CAMP-004): the list with each one's state for
 * visitors (by the Owner's own name for it), one form — with the image from the approved media library and the
 * branches it is for — and a preview of the bar / home block in both languages, the warnings about the same place at
 * the same time, the commands and the last versions. Owner only (routes/dashboard.php).
 */
final class AnnouncementsController extends Controller
{
    private const TABS = ['current' => ['live', 'outranked', 'upcoming', 'paused', 'draft', 'incomplete'], 'past' => ['ended', 'cancelled'], 'archived' => ['archived']];

    public function index(Request $request, AnnouncementEditor $editor): View
    {
        $market = $this->market();
        $tab = array_key_exists(Input::query($request, 'show'), self::TABS) ? Input::query($request, 'show') : 'current';
        $now = CarbonImmutable::now();
        $rows = [];
        $counts = array_fill_keys(array_keys(self::TABS), 0);
        foreach (Experience::query()->whereIn('type', AnnouncementEditor::TYPES)->orderByDesc('starts_at')->orderByDesc('id')->get() as $item) {
            $state = $editor->state($item, $market, $now);
            foreach (self::TABS as $key => $states) {
                if (in_array($state, $states, true)) {
                    $counts[$key]++;
                    if ($key === $tab) {
                        $rows[] = ['item' => $item, 'state' => $state, 'when' => $this->moment($item->starts_at, $market), 'until' => $this->moment($item->ends_at, $market)];
                    }
                }
            }
        }

        return view('dashboard.announcements.index', ['tab' => $tab, 'counts' => $counts, 'rows' => $rows, 'branches' => EventsController::branchChoices(app(MasterData::class))]);
    }

    public function create(): View
    {
        return $this->form(new Experience(['type' => 'announcement', 'status' => 'draft']));
    }

    public function store(Request $request, AnnouncementEditor $editor): RedirectResponse
    {
        $result = $editor->save(null, $request->all(), $this->market(), $this->owner($request));
        if ($result['errors'] !== [] || $result['item'] === null) {
            return back()->withInput()->withErrors($result['errors']);
        }

        return redirect()->route('dashboard.announcements.edit', $result['item'])->with('status', __('dashboard.saved'))->with('conflicts', $result['warnings']);
    }

    public function edit(Experience $announcement): View
    {
        abort_unless(in_array($announcement->type, AnnouncementEditor::TYPES, true), 404);

        return $this->form($announcement);
    }

    public function update(Request $request, Experience $announcement, AnnouncementEditor $editor): RedirectResponse
    {
        abort_unless(in_array($announcement->type, AnnouncementEditor::TYPES, true), 404);
        $result = $editor->save($announcement, $request->all(), $this->market(), $this->owner($request));
        if ($result['errors'] !== []) {
            return back()->withInput()->withErrors($result['errors']);
        }

        return redirect()->route('dashboard.announcements.edit', $announcement)->with('status', __('dashboard.saved'))->with('conflicts', $result['warnings']);
    }

    public function command(Request $request, Experience $announcement, string $command, ExperienceCommands $commands): RedirectResponse
    {
        abort_unless(in_array($announcement->type, AnnouncementEditor::TYPES, true) && in_array($command, ExperienceCommands::COMMANDS, true), 404);
        $done = $commands->run($announcement, $command, $this->owner($request), 'announcements');

        return redirect()->route('dashboard.announcements.edit', $announcement)
            ->with($done ? 'status' : 'warning', $done ? __('dashboard.announcements.done.'.$command) : __('dashboard.events.not_now'));
    }

    private function form(Experience $item): View
    {
        $market = $this->market();
        $timezone = $market->timezone !== '' ? $market->timezone : 'Asia/Amman';
        $editor = app(AnnouncementEditor::class);
        $media = app(MediaLibrary::class);

        return view('dashboard.announcements.form', [
            'item' => $item,
            // CAMP-004: only images the website may show now are offered; the branches by their approved names.
            'images' => AwardsController::usableImages(),
            'branches' => EventsController::branchChoices(app(MasterData::class)),
            'previewImages' => ['ar' => $media->image($item->media, 'ar', $item->title_ar), 'en' => $media->image($item->media, 'en', $item->title_en)],
            'state' => $item->exists ? $editor->state($item, $market) : 'draft',
            'conflicts' => (array) (session('conflicts') ?? ($item->exists && in_array($item->status, ['scheduled', 'active'], true) ? $editor->conflicts($item, $market) : [])),
            'starts' => $item->starts_at === null ? null : CarbonImmutable::instance($item->starts_at)->setTimezone($timezone),
            'ends' => $item->ends_at === null ? null : CarbonImmutable::instance($item->ends_at)->setTimezone($timezone),
            'level' => array_search($item->priority, AnnouncementEditor::LEVELS, true) ?: 'auto',
            'versions' => $item->exists ? ContentVersion::query()->where('versionable_type', $item->getMorphClass())->where('versionable_id', $item->id)
                ->orderByDesc('version')->limit(5)->get() : collect(),
            'timezone' => $timezone,
            'market' => $market,
        ]);
    }

    private function moment(?\DateTimeInterface $at, Market $market): ?string
    {
        return $at === null ? null : CarbonImmutable::instance($at)->setTimezone($market->timezone !== '' ? $market->timezone : 'Asia/Amman')->format('Y-m-d H:i');
    }

    private function market(): Market
    {
        return Market::query()->firstOrFail();
    }

    private function owner(Request $request): User
    {
        /** @var User $owner */
        $owner = $request->user();

        return $owner;
    }
}
