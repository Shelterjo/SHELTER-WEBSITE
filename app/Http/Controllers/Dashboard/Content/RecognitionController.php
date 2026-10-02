<?php

namespace App\Http\Controllers\Dashboard\Content;

use App\Http\Controllers\Controller;
use App\Models\ContentVersion;
use App\Models\Experience;
use App\Models\Market;
use App\Models\User;
use App\Services\Dashboard\ExperienceCommands;
use App\Services\Dashboard\RecognitionEditor;
use App\Services\Experiences\Recognitions;
use App\Support\Input;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Content → Employee of the Month (dashboard, DX-007…009, M66 §40): every month's recognition with its state on the
 * site in plain words (current · history · archived), one form to add or change one (RecognitionEditor), archive and
 * bring back (ExperienceCommands — nothing is deleted) and the last versions. Owner only (routes/dashboard.php).
 */
final class RecognitionController extends Controller
{
    /** Tabs → the states they list (DX-008: an ended month moves to the history, it is never deleted). */
    private const TABS = ['current' => ['active', 'not_shown', 'scheduled', 'draft'], 'past' => ['expired'], 'archived' => ['archived']];

    /** Archive and bring back are the only commands here: draft / published is chosen in the form. */
    public const COMMANDS = ['archive', 'restore'];

    public function index(Request $request, RecognitionEditor $editor): View
    {
        $tab = array_key_exists(Input::query($request, 'show'), self::TABS) ? Input::query($request, 'show') : 'current';
        $now = CarbonImmutable::now();
        $members = $editor->members(app()->getLocale(), null);
        $rows = [];
        $counts = array_fill_keys(array_keys(self::TABS), 0);
        foreach (Experience::query()->where('type', Recognitions::TYPE)->orderByDesc('starts_at')->orderByDesc('id')->get() as $item) {
            $state = $editor->state($item, $now);
            foreach (self::TABS as $key => $states) {
                if (in_array($state, $states, true)) {
                    $counts[$key]++;
                    if ($key === $tab) {
                        $member = Recognitions::memberId($item);
                        $rows[] = ['item' => $item, 'state' => $state, 'member' => $member === null ? null : ($members[$member] ?? '#'.$member),
                            'period' => Recognitions::period(is_string($item->details['month'] ?? null) ? $item->details['month'] : null, app()->getLocale())];
                    }
                }
            }
        }

        return view('dashboard.recognition.index', ['tab' => $tab, 'counts' => $counts, 'rows' => $rows]);
    }

    public function create(RecognitionEditor $editor): View
    {
        return $this->form(new Experience(['type' => Recognitions::TYPE, 'status' => 'draft']), $editor);
    }

    public function store(Request $request, RecognitionEditor $editor): RedirectResponse
    {
        $result = $editor->save(null, $request->all(), $this->market(), $this->owner($request));
        if ($result['errors'] !== [] || $result['item'] === null) {
            return back()->withInput()->withErrors($result['errors']);
        }

        return redirect()->route('dashboard.recognition.edit', $result['item'])->with('status', __('dashboard.saved'));
    }

    public function edit(Experience $recognition, RecognitionEditor $editor): View
    {
        abort_unless($recognition->type === Recognitions::TYPE, 404);

        return $this->form($recognition, $editor);
    }

    public function update(Request $request, Experience $recognition, RecognitionEditor $editor): RedirectResponse
    {
        abort_unless($recognition->type === Recognitions::TYPE, 404);
        $result = $editor->save($recognition, $request->all(), $this->market(), $this->owner($request));
        if ($result['errors'] !== []) {
            return back()->withInput()->withErrors($result['errors']);
        }

        return redirect()->route('dashboard.recognition.edit', $recognition)->with('status', __('dashboard.saved'));
    }

    public function command(Request $request, Experience $recognition, string $command, ExperienceCommands $commands): RedirectResponse
    {
        abort_unless($recognition->type === Recognitions::TYPE && in_array($command, self::COMMANDS, true), 404);
        $done = $commands->run($recognition, $command, $this->owner($request), 'recognition');

        return redirect()->route('dashboard.recognition.edit', $recognition)
            ->with($done ? 'status' : 'warning', $done ? __('dashboard.recognition.done.'.$command) : __('dashboard.recognition.not_now'));
    }

    private function form(Experience $item, RecognitionEditor $editor): View
    {
        $locale = app()->getLocale();
        $month = Recognitions::month(is_string($item->details['month'] ?? null) ? $item->details['month'] : null);
        $year = (int) CarbonImmutable::now('Asia/Amman')->format('Y');
        $years = range($year - 1, $year + 1);
        if ($month !== null && ! in_array((int) $month->format('Y'), $years, true)) {
            $years[] = (int) $month->format('Y');
            sort($years);
        }
        $months = [];
        foreach (range(1, 12) as $number) {
            $first = Recognitions::month(sprintf('%04d-%02d', $year, $number));
            $months[$number] = sprintf('%02d', $number).($first === null ? '' : ' — '.Recognitions::monthName($first, $locale));
        }

        return view('dashboard.recognition.form', [
            'item' => $item,
            'state' => $item->exists ? $editor->state($item) : 'draft',
            'reasons' => $item->exists ? $editor->reasons($item) : [],
            'members' => $editor->members($locale, Recognitions::memberId($item)),
            'memberId' => Recognitions::memberId($item),
            'month' => $month,
            'months' => $months,
            'years' => array_combine(array_map('strval', $years), array_map('strval', $years)),
            'images' => AwardsController::usableImages(),
            'versions' => $item->exists ? ContentVersion::query()->where('versionable_type', $item->getMorphClass())->where('versionable_id', $item->id)
                ->orderByDesc('version')->limit(5)->get() : collect(),
        ]);
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
