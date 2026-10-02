<?php

namespace App\Http\Controllers\Dashboard\Requests;

use App\Http\Controllers\Controller;
use App\Models\Recruitment\Application;
use App\Models\Recruitment\ApplicationMeeting;
use App\Models\Recruitment\ApplicationNote;
use App\Models\User;
use App\Services\Requests\ApplicationInbox;
use App\Services\Requests\PartnershipsQuery;
use App\Support\Countries;
use App\Support\Input;
use App\Support\OwnNavigation;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Requests → Partnerships (dashboard, docs/franchise/04, FRAN-054…065, M50): the overview (new, total, per stage,
 * where the demand is), the list with search and filters, and one application — stage with its history, notes,
 * meetings, consents, attribution, earlier applications. No automatic decision, no delete (archive only). Owner only.
 */
final class PartnershipsController extends Controller
{
    public function index(Request $request, PartnershipsQuery $query): View
    {
        $filters = PartnershipsQuery::filters($request->query());
        $page = $query->paginate($filters, max(1, (int) $request->query('page', '1')));

        return view('dashboard.requests.partnerships.index', [
            'filters' => $filters,
            'counts' => $query->counts(),
            'demand' => $query->demand(),
            'labels' => ApplicationInbox::labels('FR', app()->getLocale()),
            'countries' => Countries::options(app()->getLocale()),
            'items' => $page->items(),
            'total' => $page->total(),
            'current' => $page->currentPage(),
            'last' => $page->lastPage(),
        ]);
    }

    public function show(Request $request, Application $application, ApplicationInbox $inbox): View
    {
        $this->partnership($application);
        if (OwnNavigation::is($request)) {
            $inbox->markViewed($application, $this->owner($request));
        }
        $application->load(['partnership', 'notes.author', 'statusHistory.actor', 'meetings']);

        return view('dashboard.requests.partnerships.show', [
            'application' => $application,
            'labels' => ApplicationInbox::labels('FR', app()->getLocale()),
            'countries' => Countries::options(app()->getLocale()),
            'consents' => DB::table('application_consents')->join('consent_versions', 'consent_versions.id', '=', 'application_consents.consent_version_id')
                ->where('application_id', $application->id)->get(['consent_versions.scope', 'consent_versions.version', 'application_consents.accepted_at'])->all(),
            'previous' => Application::query()->whereIn('id', DB::table('application_links')->where('application_id', $application->id)->pluck('linked_application_id'))
                ->orderByDesc('submitted_at')->get(['id', 'type', 'reference_number', 'status', 'submitted_at'])->all(),
        ]);
    }

    public function status(Request $request, Application $application, ApplicationInbox $inbox): RedirectResponse
    {
        $this->partnership($application);
        $status = Input::text($request, 'status');
        if (! in_array($status, ApplicationInbox::statuses('FR'), true)) {
            return back()->withErrors(['status' => __('dashboard.requests.errors.status')]);
        }
        $inbox->changeStatus($application, $status, Input::text($request, 'note'), $this->owner($request));

        return back()->with('status', __('dashboard.requests.fr.stage_saved'));
    }

    public function restore(Request $request, Application $application, ApplicationInbox $inbox): RedirectResponse
    {
        $this->partnership($application);
        $inbox->restore($application, $this->owner($request));

        return back()->with('status', __('dashboard.requests.restored'));
    }

    public function addNote(Request $request, Application $application, ApplicationInbox $inbox): RedirectResponse
    {
        $this->partnership($application);
        if (trim(Input::text($request, 'body')) === '') {
            return back()->withErrors(['body' => __('dashboard.requests.errors.note')]);
        }
        $inbox->addNote($application, Input::text($request, 'body'), $this->owner($request));

        return redirect()->to(route('dashboard.partnerships.show', $application).'#notes')->with('status', __('dashboard.requests.note_saved'));
    }

    public function editNote(Request $request, Application $application, ApplicationNote $note, ApplicationInbox $inbox): RedirectResponse
    {
        $this->partnership($application);
        abort_unless($note->application_id === $application->id, 404);
        if ($request->boolean('remove')) {
            $inbox->deleteNote($note, $this->owner($request));
        } elseif (trim(Input::text($request, 'body')) !== '') {
            $inbox->editNote($note, Input::text($request, 'body'), $this->owner($request));
        }

        return redirect()->to(route('dashboard.partnerships.show', $application).'#notes')->with('status', __('dashboard.requests.note_saved'));
    }

    public function meeting(Request $request, Application $application, ApplicationInbox $inbox): RedirectResponse
    {
        $this->partnership($application);
        $date = Input::text($request, 'date');
        $time = Input::text($request, 'time');
        $channel = Input::text($request, 'channel');
        $at = preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 && preg_match('/^\d{2}:\d{2}$/', $time) === 1
            ? CarbonImmutable::createFromFormat('Y-m-d H:i', $date.' '.$time, 'Asia/Amman') : null;
        $errors = array_filter([
            'date' => $at instanceof CarbonImmutable ? null : __('dashboard.requests.errors.interview_when'),
            'channel' => in_array($channel, ApplicationMeeting::CHANNELS, true) ? null : __('dashboard.requests.errors.channel'),
        ]);
        if ($errors !== [] || ! $at instanceof CarbonImmutable) {
            return redirect()->to(route('dashboard.partnerships.show', $application).'#meetings')->withInput()->withErrors($errors);
        }
        $inbox->scheduleMeeting($application, $at, $channel, Input::text($request, 'place'), Input::text($request, 'notes'), $this->owner($request));

        return redirect()->to(route('dashboard.partnerships.show', $application).'#meetings')->with('status', __('dashboard.requests.meeting_saved'));
    }

    public function meetingState(Request $request, Application $application, ApplicationMeeting $meeting, ApplicationInbox $inbox): RedirectResponse
    {
        $this->partnership($application);
        abort_unless($meeting->application_id === $application->id, 404);
        $inbox->setMeetingState($meeting, Input::text($request, 'state'), $this->owner($request));

        return redirect()->to(route('dashboard.partnerships.show', $application).'#meetings')->with('status', __('dashboard.requests.meeting_saved'));
    }

    private function partnership(Application $application): void
    {
        abort_unless($application->type === 'FR', 404);
    }

    private function owner(Request $request): User
    {
        /** @var User $owner */
        $owner = $request->user();

        return $owner;
    }
}
