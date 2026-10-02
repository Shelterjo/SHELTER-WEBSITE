<?php

namespace App\Http\Controllers\Dashboard\Requests;

use App\Http\Controllers\Controller;
use App\Models\Recruitment\Application;
use App\Models\Recruitment\ApplicationAttachment;
use App\Models\Recruitment\ApplicationNote;
use App\Models\Recruitment\InterviewLocation;
use App\Models\Recruitment\JordanCity;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\Recruitment\IdentityVault;
use App\Services\Requests\ApplicationInbox;
use App\Services\Requests\CareersQuery;
use App\Services\Requests\CareersView;
use App\Services\Requests\RecruitmentSettings;
use App\Support\Input;
use App\Support\OwnNavigation;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Requests → Job applications (dashboard, M28 / CAREERS-052…073, M50): the list with search, filters and the overview
 * cards; one application in a clear order; status, notes, interview, archive. Revealing an identity number and
 * permanent deletion need a fresh re-confirmation (`confirmed`). Owner only; nothing here is cached by the browser.
 */
final class CareersController extends Controller
{
    /** Saved searches per Owner (a short list stays readable). */
    private const SAVED_MAX = 20;

    public function index(Request $request, CareersQuery $query, CareersView $view): View
    {
        $owner = $this->owner($request);
        $filters = CareersQuery::filters($request->query());
        if ($request->query('per') !== null) {
            if (OwnNavigation::is($request)) {
                $view->rememberPerPage($owner, $filters['per']); // the last choice is remembered (CAREERS-063)
            }
        } else {
            $filters['per'] = $view->perPage($owner);
        }
        $page = $query->paginate($filters, max(1, (int) $request->query('page', '1')));

        return view('dashboard.requests.careers.index', [
            'filters' => $filters,
            'counts' => $query->counts($filters['period']),
            'items' => $page->items(),
            'total' => $page->total(),
            'current' => $page->currentPage(),
            'last' => $page->lastPage(),
            'cities' => JordanCity::query()->where('is_active', true)->orderBy('name_ar')->get(),
            'advanced' => array_filter(array_intersect_key($filters, array_flip(['city', 'gender', 'nationality', 'education', 'experience', 'job', 'from', 'to']))) !== [],
            'columns' => $view->columns($owner),
            'ordered' => $view->ordered($owner),
            'density' => $view->density($owner),
            'saved' => DB::table('recruitment_saved_filters')->where('owner_id', $owner->id)->orderBy('sort')->orderBy('id')->get()
                ->map(fn ($row): array => ['id' => (int) $row->id, 'name' => (string) $row->name, 'query' => (array) json_decode((string) $row->filter_json, true)])->all(),
            'staleBefore' => now()->subDays(RecruitmentSettings::staleDays()),
        ]);
    }

    /** The Owner's table view: columns shown and their order, density; or back to the default (CAREERS-061/062). */
    public function view(Request $request, CareersView $view): RedirectResponse
    {
        $view->update($this->owner($request), $request->all());
        $back = Input::text($request, 'back');

        return redirect()->to(str_starts_with($back, route('dashboard.careers.index')) ? $back : route('dashboard.careers.index'))->with('view_open', true);
    }

    /** Saves the current search under a name (CAREERS-060). */
    public function saveFilter(Request $request): RedirectResponse
    {
        $owner = $this->owner($request);
        $name = trim(Input::text($request, 'name'));
        if ($name === '' || mb_strlen($name) > 120) {
            return back()->withErrors(['saved_name' => __('dashboard.requests.saved.errors.name')]);
        }
        if (DB::table('recruitment_saved_filters')->where('owner_id', $owner->id)->count() >= self::SAVED_MAX) {
            return back()->withErrors(['saved_name' => __('dashboard.requests.saved.errors.max', ['max' => self::SAVED_MAX])]);
        }
        $query = array_filter(CareersQuery::filters((array) $request->input('filters', [])), fn ($v, string $k): bool => $v !== '' && $v !== null && $k !== 'per', ARRAY_FILTER_USE_BOTH);
        DB::table('recruitment_saved_filters')->insert(['owner_id' => $owner->id, 'name' => $name, 'filter_json' => json_encode($query, JSON_UNESCAPED_UNICODE),
            'sort' => 0, 'created_at' => now(), 'updated_at' => now()]);

        return redirect()->route('dashboard.careers.index', $query)->with('status', __('dashboard.requests.saved.done'));
    }

    public function destroyFilter(Request $request, int $filter): RedirectResponse
    {
        DB::table('recruitment_saved_filters')->where('owner_id', $this->owner($request)->id)->where('id', $filter)->delete();

        return back()->with('status', __('dashboard.requests.saved.removed'));
    }

    /** Quick view (CAREERS-065): the essentials in a side panel; opening it counts as seeing the application. */
    public function quick(Request $request, Application $application, ApplicationInbox $inbox): View|RedirectResponse
    {
        $this->job($application);
        if ($request->header('X-Quick-View') !== '1') {
            return redirect()->route('dashboard.careers.show', $application); // opened directly: the full page
        }
        $inbox->markViewed($application, $this->owner($request));
        $application->load(['job.city', 'attachments']);

        return view('dashboard.requests.careers._quick', ['application' => $application, 'statuses' => ApplicationInbox::statuses('JOB')]);
    }

    public function show(Request $request, Application $application, ApplicationInbox $inbox): View
    {
        $this->job($application);
        /** @var User $owner */
        $owner = $request->user();
        $wasNew = $application->isNew();
        if (OwnNavigation::is($request)) {
            $inbox->markViewed($application, $owner);
        }
        $application->load(['job.city', 'attachments', 'identity', 'notes.author', 'statusHistory.actor', 'interviews.location']);

        return view('dashboard.requests.careers.show', [
            'application' => $application,
            'wasNew' => $wasNew,
            'locations' => InterviewLocation::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'consents' => DB::table('application_consents')->join('consent_versions', 'consent_versions.id', '=', 'application_consents.consent_version_id')
                ->where('application_id', $application->id)->get(['consent_versions.version', 'application_consents.accepted', 'application_consents.accepted_at'])->all(),
            'previous' => Application::query()->whereIn('id', DB::table('application_links')->where('application_id', $application->id)->pluck('linked_application_id'))
                ->orderByDesc('submitted_at')->get(['id', 'type', 'reference_number', 'status', 'submitted_at'])->all(),
            'statuses' => ApplicationInbox::statuses('JOB'),
            'masked' => $application->identity !== null ? IdentityVault::mask($application->identity->id_last4) : null,
        ]);
    }

    public function status(Request $request, Application $application, ApplicationInbox $inbox): RedirectResponse
    {
        $this->job($application);
        $status = Input::text($request, 'status');
        if (! in_array($status, ApplicationInbox::statuses('JOB'), true)) {
            return back()->withErrors(['status' => __('dashboard.requests.errors.status')]);
        }
        $inbox->changeStatus($application, $status, Input::text($request, 'note'), $this->owner($request));

        return back()->with('status', __('dashboard.requests.status_saved'));
    }

    public function restore(Request $request, Application $application, ApplicationInbox $inbox): RedirectResponse
    {
        $this->job($application);
        $inbox->restore($application, $this->owner($request));

        return back()->with('status', __('dashboard.requests.restored'));
    }

    public function addNote(Request $request, Application $application, ApplicationInbox $inbox): RedirectResponse
    {
        $this->job($application);
        if (trim(Input::text($request, 'body')) === '') {
            return back()->withErrors(['body' => __('dashboard.requests.errors.note')]);
        }
        $inbox->addNote($application, Input::text($request, 'body'), $this->owner($request));

        return redirect()->to(route('dashboard.careers.show', $application).'#notes')->with('status', __('dashboard.requests.note_saved'));
    }

    public function editNote(Request $request, Application $application, ApplicationNote $note, ApplicationInbox $inbox): RedirectResponse
    {
        $this->job($application);
        abort_unless($note->application_id === $application->id, 404);
        if ($request->boolean('remove')) {
            $inbox->deleteNote($note, $this->owner($request));
        } elseif (trim(Input::text($request, 'body')) !== '') {
            $inbox->editNote($note, Input::text($request, 'body'), $this->owner($request));
        }

        return redirect()->to(route('dashboard.careers.show', $application).'#notes')->with('status', __('dashboard.requests.note_saved'));
    }

    public function interview(Request $request, Application $application, ApplicationInbox $inbox): RedirectResponse
    {
        $this->job($application);
        $date = Input::text($request, 'date');
        $time = Input::text($request, 'time');
        $location = InterviewLocation::query()->where('is_active', true)->find($request->integer('location'));
        $at = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 && preg_match('/^\d{2}:\d{2}$/', $time) === 1
            ? CarbonImmutable::createFromFormat('Y-m-d H:i', $date.' '.$time, 'Asia/Amman') : null;
        $errors = array_filter([
            'date' => $at instanceof CarbonImmutable ? null : __('dashboard.requests.errors.interview_when'),
            'location' => $location !== null ? null : __('dashboard.requests.errors.interview_where'),
        ]);
        if ($errors !== [] || ! $at instanceof CarbonImmutable || $location === null) {
            return redirect()->to(route('dashboard.careers.show', $application).'#interview')->withInput()->withErrors($errors);
        }
        $inbox->scheduleInterview($application, $at, $location, Input::text($request, 'notes'), $this->owner($request));

        return redirect()->to(route('dashboard.careers.show', $application).'#interview')->with('status', __('dashboard.requests.interview_saved'));
    }

    /** The full identity number on its own page, after a fresh re-confirmation (route middleware `confirmed`). */
    public function identity(Request $request, Application $application, ApplicationInbox $inbox): Response
    {
        $this->job($application);
        $number = $inbox->revealIdentity($application, $this->owner($request));
        abort_if($number === null, 404);

        return response()->view('dashboard.requests.careers.identity', ['application' => $application->load('identity'), 'number' => $number])
            ->header('Cache-Control', 'no-store, private');
    }

    /** A file, downloaded — never shown inline, never cached, never sniffed (RECRUITMENT-SECURITY §4.4). */
    public function attachment(Request $request, ApplicationAttachment $attachment, AuditLogger $audit): StreamedResponse
    {
        $application = $attachment->application;
        abort_unless($application !== null && in_array($application->type, ['JOB', 'FR'], true), 404);
        abort_unless(Storage::disk('careers')->exists($attachment->storage_path), 404);
        $audit->record('attachments.downloaded', $attachment, [], ['reference' => $application->reference_number], actor: $this->owner($request));

        return Storage::disk('careers')->download($attachment->storage_path, $attachment->original_filename, [
            'Content-Type' => $attachment->detected_mime,
            'X-Content-Type-Options' => 'nosniff', // the global layer would add it too — set here so the rule lives with the route
            'Content-Security-Policy' => "sandbox; default-src 'none'",
            'Cache-Control' => 'no-store, private',
        ]);
    }

    /** Permanent deletion: from the archive only, after re-confirmation, typing the application number (CAREERS-070). */
    public function confirmDelete(Application $application): View
    {
        $this->job($application);
        abort_unless($application->status === 'archived', 404);

        return view('dashboard.requests.careers.delete', ['application' => $application]);
    }

    public function destroy(Request $request, Application $application, ApplicationInbox $inbox): RedirectResponse
    {
        $this->job($application);
        abort_unless($application->status === 'archived', 404);
        if (strtoupper(trim(Input::text($request, 'reference'))) !== $application->reference_number || ! $request->boolean('understood')) {
            return back()->withErrors(['reference' => __('dashboard.requests.errors.delete_confirm')]);
        }
        $reference = $application->reference_number;
        $inbox->permanentlyDelete($application, $this->owner($request));

        return redirect()->route('dashboard.careers.index', ['status' => 'archived'])->with('status', __('dashboard.requests.deleted', ['reference' => $reference]));
    }

    private function job(Application $application): void
    {
        abort_unless($application->type === 'JOB', 404);
    }

    private function owner(Request $request): User
    {
        /** @var User $owner */
        $owner = $request->user();

        return $owner;
    }
}
