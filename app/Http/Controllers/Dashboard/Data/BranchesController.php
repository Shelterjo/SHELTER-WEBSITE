<?php

namespace App\Http\Controllers\Dashboard\Data;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\HoursException;
use App\Models\Market;
use App\Models\User;
use App\Services\Dashboard\HoursEditor;
use App\Services\MasterData\MasterData;
use App\Services\Site\BranchDirectory;
use App\Support\LocalTime;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Business data → Branches and hours (dashboard, M50, MDH-005…007, BRANCH-010, HOURS-008): each branch with its state
 * now, the weekly hours (preview, then publish with a reason) and the exceptions — special hours, holidays,
 * temporary and emergency closures. Editing central facts needs a fresh re-confirmation (route middleware `confirmed`).
 */
final class BranchesController extends Controller
{
    public function index(MasterData $data): View
    {
        $market = Market::query()->first();
        $rows = [];
        foreach (Branch::query()->whereNull('archived_at')->orderBy('sort')->get() as $branch) {
            $rows[] = ['branch' => $branch, 'name' => $this->name($branch, $data), 'state' => $market !== null ? $data->openState($branch, $market) : null,
                'exceptions' => $branch->hoursExceptions()->where('status', 'published')->whereDate('ends_on', '>=', now('Asia/Amman')->toDateString())->count()];
        }

        return view('dashboard.branches.index', ['rows' => $rows]);
    }

    public function show(Branch $branch, HoursEditor $editor, MasterData $data): View
    {
        return $this->screen($branch, $editor, $data, $editor->current($branch), null);
    }

    /** Preview (nothing saved) or publish — publishing needs the preview of the same week and a reason. */
    public function hours(Request $request, Branch $branch, HoursEditor $editor, MasterData $data): View|RedirectResponse
    {
        $parsed = $editor->parseWeek($request->all());
        $days = $this->formDays($request);
        if ($parsed['errors'] !== []) {
            return $this->screen($branch, $editor, $data, $days, null, $parsed['errors']);
        }
        if ($request->input('action') !== 'publish') {
            return $this->screen($branch, $editor, $data, $days, $parsed['rows']);
        }
        /** @var User $owner */
        $owner = $request->user();
        $errors = $editor->publishWeek($branch, $parsed['rows'], $request->string('reason')->toString(), $request->string('previewed')->toString(), $owner);
        if ($errors !== []) {
            return $this->screen($branch, $editor, $data, $days, $parsed['rows'], $errors);
        }

        return redirect()->route('dashboard.branches.show', $branch)->with('status', __('dashboard.hours.published'));
    }

    public function storeException(Request $request, Branch $branch, HoursEditor $editor): RedirectResponse
    {
        /** @var User $owner */
        $owner = $request->user();
        $result = $editor->saveException($branch, null, $request->all(), $owner);
        if ($result['errors'] !== []) {
            return redirect()->to(route('dashboard.branches.show', $branch).'#exceptions')->withInput()->withErrors($result['errors'], 'exception');
        }

        return redirect()->to(route('dashboard.branches.show', $branch).'#exceptions')->with('status', __('dashboard.hours.exception_saved'));
    }

    public function updateException(Request $request, Branch $branch, HoursException $exception, HoursEditor $editor): RedirectResponse
    {
        abort_unless($exception->branch_id === $branch->id, 404);
        /** @var User $owner */
        $owner = $request->user();
        $result = $editor->saveException($branch, $exception, $request->all(), $owner);
        if ($result['errors'] !== []) {
            return redirect()->to(route('dashboard.branches.show', $branch).'#x'.$exception->id)->withErrors($result['errors'], 'x'.$exception->id);
        }

        return redirect()->to(route('dashboard.branches.show', $branch).'#exceptions')->with('status', __('dashboard.hours.exception_saved'));
    }

    public function archiveException(Request $request, Branch $branch, HoursException $exception, HoursEditor $editor): RedirectResponse
    {
        abort_unless($exception->branch_id === $branch->id, 404);
        /** @var User $owner */
        $owner = $request->user();
        $editor->archiveException($exception, $owner);

        return redirect()->to(route('dashboard.branches.show', $branch).'#exceptions')->with('status', __('dashboard.hours.exception_archived'));
    }

    /**
     * @param  array<int, array{open: bool, opens: string, closes: string}>  $days
     * @param  list<array{0: int, 1: string, 2: string}>|null  $preview
     * @param  array<string, string>  $errors
     */
    private function screen(Branch $branch, HoursEditor $editor, MasterData $data, array $days, ?array $preview, array $errors = []): View
    {
        $market = Market::query()->first();
        $today = CarbonImmutable::now('Asia/Amman')->toDateString();
        $exceptions = $branch->hoursExceptions()->where('status', '!=', 'archived')->orderBy('starts_on')->get();

        // The days the preview changes, by name — so the Owner checks exactly those.
        $now = MasterData::normalizeHours($branch->hours()->get());
        $changed = [];
        if ($preview !== null) {
            foreach ([6, 0, 1, 2, 3, 4, 5] as $day) {
                $before = array_values(array_filter($now, fn (array $r): bool => $r[0] === $day));
                $after = array_values(array_filter($preview, fn (array $r): bool => $r[0] === $day));
                if ($before != $after) {
                    $changed[] = LocalTime::weekday($day, app()->getLocale());
                }
            }
        }

        return view('dashboard.branches.show', [
            'changed' => $changed,
            'branch' => $branch,
            'name' => $this->name($branch, $data),
            'state' => $market !== null ? $data->openState($branch, $market) : null,
            'days' => $days,
            'preview' => $preview === null ? null : BranchDirectory::weekRows($preview, null, app()->getLocale()),
            'current' => BranchDirectory::weekRows($now, null, app()->getLocale()),
            'fingerprint' => $preview === null ? null : HoursEditor::fingerprint($branch, $preview),
            'hoursErrors' => $errors,
            'upcoming' => $exceptions->filter(fn (HoursException $e): bool => $e->ends_on->toDateString() >= $today)->values(),
            'past' => $exceptions->filter(fn (HoursException $e): bool => $e->ends_on->toDateString() < $today)->sortByDesc('starts_on')->values(),
        ]);
    }

    /** @return array<int, array{open: bool, opens: string, closes: string}> */
    private function formDays(Request $request): array
    {
        $input = is_array($request->input('days')) ? $request->input('days') : [];
        $days = [];
        foreach ([6, 0, 1, 2, 3, 4, 5] as $day) {
            $row = is_array($input[$day] ?? null) ? $input[$day] : [];
            $days[$day] = ['open' => ! empty($row['open']), 'opens' => is_string($row['opens'] ?? null) ? $row['opens'] : '', 'closes' => is_string($row['closes'] ?? null) ? $row['closes'] : ''];
        }

        return $days;
    }

    private function name(Branch $branch, MasterData $data): string
    {
        $name = $data->branchField($branch, app()->getLocale() === 'ar' ? 'name_ar' : 'name_en');

        return is_string($name) ? $name : $branch->code;
    }
}
