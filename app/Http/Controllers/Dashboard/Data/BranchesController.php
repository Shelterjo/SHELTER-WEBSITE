<?php

namespace App\Http\Controllers\Dashboard\Data;

use App\Http\Controllers\Controller;
use App\Http\Middleware\PreventStaleEdits;
use App\Models\Branch;
use App\Models\HoursException;
use App\Models\Market;
use App\Models\User;
use App\Services\Dashboard\BranchEditor;
use App\Services\Dashboard\HoursEditor;
use App\Services\MasterData\MasterData;
use App\Services\Site\BranchDirectory;
use App\Support\Input;
use App\Support\LocalTime;
use App\Support\PageUrl;
use App\Support\SiteLinks;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;

/**
 * Business data → Branches and hours (dashboard, M50, MDH-005…007, BRANCH-010, HOURS-008): each branch with its state
 * now, its details and the weekly hours (each: preview, then publish) and the exceptions — special hours, holidays,
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
        $errors = $editor->publishWeek($branch, $parsed['rows'], Input::text($request, 'reason'), Input::text($request, 'previewed'), $owner);
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
     * The branch's details: names, address, map, coordinates, shown on the site, services and payments (BranchEditor).
     * Preview first — the card and the page as visitors will see them, both languages, nothing saved (BRANCH-010) —
     * then publish exactly what was previewed.
     */
    public function details(Request $request, Branch $branch, BranchEditor $editor, HoursEditor $hours, MasterData $data, BranchDirectory $directory): View|RedirectResponse
    {
        $details = $editor->parse($branch, $request->all());
        $url = route('dashboard.branches.show', $branch).'#details';
        if ($details['errors'] !== []) {
            return redirect()->to($url)->withInput()->withErrors($details['errors'], 'details');
        }
        if ($request->input('action') !== 'publish') {
            return $this->screen($branch, $hours, $data, $hours->current($branch), null, [], $this->detailsPreview($request, $branch, $details, $editor, $directory));
        }
        /** @var User $owner */
        $owner = $request->user();
        $errors = $editor->publish($branch, $details, Input::text($request, 'previewed'), $owner);
        if ($errors !== []) {
            return $this->screen($branch, $hours, $data, $hours->current($branch), null, [], ['errors' => $errors] + $this->detailsPreview($request, $branch, $details, $editor, $directory));
        }

        return redirect()->to($url)->with('status', __('dashboard.branch.published'));
    }

    /**
     * The branch page as visitors will see it with the details typed (BRANCH-010), in one language, in a new tab: the
     * site's own page and layout, nothing saved, noindex, no structured data, Owner only (the dashboard routes).
     */
    public function previewPage(Request $request, Branch $branch, string $locale, BranchEditor $editor, BranchDirectory $directory): View|RedirectResponse
    {
        $details = $editor->parse($branch, $request->all());
        if ($details['errors'] !== []) {
            return redirect()->to(route('dashboard.branches.show', $branch).'#details')->withInput()->withErrors($details['errors'], 'details');
        }
        $market = Market::query()->firstOrFail();
        app()->setLocale($locale);
        $summary = $directory->preview($branch, $market, $locale, $details['values'], BranchEditor::said($details));
        abort_if($summary === null, 404);
        $parameters = ['locale' => $locale, 'market' => $market->code];
        $menuUrl = SiteLinks::to('menu', $parameters);

        return view('site.branch', [
            'dir' => $locale === 'ar' ? 'rtl' : 'ltr',
            'noindex' => true,
            'preview' => ['hidden' => ! $details['public']],
            'market' => $market,
            'branch' => $summary,
            'crumbs' => [
                ['label' => (string) __('site.nav.home'), 'href' => PageUrl::route('home', ['locale' => $locale])],
                ['label' => (string) __('site.locations.title'), 'href' => PageUrl::route('locations', $parameters)],
                ['label' => $summary->name, 'href' => $summary->url],
            ],
            'menuUrl' => $menuUrl !== null ? $menuUrl.'?branch='.rawurlencode($branch->slug) : null,
        ]);
    }

    /**
     * What the details preview shows: the card and the page's address-and-services part in each language — rendered
     * in that language with the site's own component — what changes, and the fingerprint publishing must carry.
     *
     * @param  array{values: array<string, string|null>, public: bool, attributes: array<string, array<string, string>>, reason: string|null, errors: array<string, string>}  $details
     * @return array{input: array<string, mixed>, views: array<string, HtmlString>, public: bool, changed: list<string>, fingerprint: string, errors: array<string, string>}
     */
    private function detailsPreview(Request $request, Branch $branch, array $details, BranchEditor $editor, BranchDirectory $directory): array
    {
        $market = Market::query()->firstOrFail();
        $said = BranchEditor::said($details);
        $dashboard = app()->getLocale();
        $views = [];
        foreach (['ar', 'en'] as $locale) {
            app()->setLocale($locale);
            try {
                $summary = $directory->preview($branch, $market, $locale, $details['values'], $said);
                $views[$locale] = new HtmlString($summary === null ? '' : view('dashboard.branches._preview', ['summary' => $summary])->render());
            } finally {
                app()->setLocale($dashboard);
            }
        }

        return [
            'input' => $request->except(['_token', '_method', 'action', 'previewed', PreventStaleEdits::FIELD]),
            'views' => $views,
            'public' => $details['public'],
            'changed' => $editor->changes($branch, $details),
            'fingerprint' => BranchEditor::fingerprint($branch, $details),
            'errors' => [],
        ];
    }

    /**
     * @param  array<int, array{open: bool, opens: string, closes: string}>  $days
     * @param  list<array{0: int, 1: string, 2: string}>|null  $preview
     * @param  array<string, string>  $errors
     * @param  array{input: array<string, mixed>, views: array<string, HtmlString>, public: bool, changed: list<string>, fingerprint: string, errors: array<string, string>}|null  $details  the details preview
     */
    private function screen(Branch $branch, HoursEditor $editor, MasterData $data, array $days, ?array $preview, array $errors = [], ?array $details = null): View
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
            'attributes' => $branch->branchAttributes()->orderBy('id')->get()->groupBy('group'),
            'changed' => $changed,
            'branch' => $branch,
            'name' => $this->name($branch, $data),
            'state' => $market !== null ? $data->openState($branch, $market) : null,
            'days' => $days,
            'preview' => $preview === null ? null : BranchDirectory::weekRows($preview, null, app()->getLocale()),
            'current' => BranchDirectory::weekRows($now, null, app()->getLocale()),
            'fingerprint' => $preview === null ? null : HoursEditor::fingerprint($branch, $preview),
            'hoursErrors' => $errors,
            'detailsPreview' => $details,
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
