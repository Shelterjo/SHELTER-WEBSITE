<?php

namespace App\Http\Controllers\Dashboard\Requests;

use App\Http\Controllers\Controller;
use App\Models\Recruitment\Application;
use App\Models\User;
use App\Services\Core\AuditLogger;
use App\Services\Requests\ApplicationInbox;
use App\Services\Requests\CareersExport;
use App\Services\Requests\CareersQuery;
use App\Support\DashboardBack;
use App\Support\Input;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Requests → Job applications, several at once (CAREERS-068/069/072/073): change the status or archive with one clear
 * confirmation (each application keeps its own history, plus one bulk entry in the audit), export (CSV · Excel · PDF
 * print view — identity masked unless asked) and download the attachments as one ZIP. No bulk permanent delete.
 * Exports and the ZIP sit behind a fresh re-confirmation (`confirmed`); every one is audited without personal data.
 */
final class CareersBulkController extends Controller
{
    private const FORMATS = ['csv', 'xlsx', 'print'];

    private const SCOPES = ['filtered', 'selected', 'all'];

    /** The list's bulk bar: a status change shows its confirmation; export opens the export screen; ZIP downloads. */
    public function bulk(Request $request): View|RedirectResponse
    {
        $ids = CareersQuery::jobIds((array) $request->input('ids', []));
        $action = Input::text($request, 'action');
        if ($ids === []) {
            return redirect()->to(DashboardBack::to(route('dashboard.careers.index')))->with('warning', __('dashboard.requests.bulk.none'));
        }
        if ($action === 'export') {
            return redirect()->route('dashboard.careers.export', ['scope' => 'selected', 'ids' => implode(',', $ids)]);
        }
        if ($action === 'zip') {
            return redirect()->route('dashboard.careers.export', ['scope' => 'selected', 'ids' => implode(',', $ids), 'zip' => 1]);
        }
        $status = str_starts_with($action, 'status:') ? substr($action, 7) : '';
        if (! in_array($status, ApplicationInbox::statuses('JOB'), true)) {
            return redirect()->to(DashboardBack::to(route('dashboard.careers.index')))->with('warning', __('dashboard.requests.bulk.choose'));
        }

        return view('dashboard.requests.careers.bulk', [
            'ids' => $ids,
            'status' => $status,
            'applications' => Application::query()->whereIn('id', $ids)->with('job')->orderByDesc('submitted_at')->get(),
            'back' => DashboardBack::to(route('dashboard.careers.index')),
        ]);
    }

    public function apply(Request $request, ApplicationInbox $inbox, AuditLogger $audit): RedirectResponse
    {
        $ids = CareersQuery::jobIds((array) $request->input('ids', []));
        $status = Input::text($request, 'status');
        abort_unless($ids !== [] && in_array($status, ApplicationInbox::statuses('JOB'), true), 422);
        $owner = $this->owner($request);
        $changed = 0;
        DB::transaction(function () use ($ids, $status, $request, $inbox, $owner, $audit, &$changed): void {
            foreach (Application::query()->whereIn('id', $ids)->get() as $application) {
                if ($application->status !== $status) {
                    $inbox->changeStatus($application, $status, Input::text($request, 'note'), $owner);
                    $changed++;
                }
            }
            $audit->record('applications.bulk_status_changed', null, ['after' => ['status' => $status]], ['selected' => count($ids), 'changed' => $changed], actor: $owner);
        });

        return redirect()->route('dashboard.careers.index')->with('status', trans_choice('dashboard.requests.bulk.done', $changed, ['count' => $changed]));
    }

    /** The export screen: what (the filtered list, the selection or everything), the format, identity masked or full. */
    public function form(Request $request): View
    {
        $ids = CareersQuery::jobIds(explode(',', Input::text($request, 'ids')));
        $scope = in_array($request->query('scope'), self::SCOPES, true) ? Input::query($request, 'scope') : 'filtered';
        $filters = CareersQuery::filters($request->query());

        return view('dashboard.requests.careers.export', [
            'scope' => $scope === 'selected' && $ids === [] ? 'filtered' : $scope,
            'ids' => $ids,
            'filters' => array_filter($filters, fn ($v, string $k): bool => $v !== '' && $v !== null && ! in_array($k, ['per', 'sort'], true), ARRAY_FILTER_USE_BOTH),
            'counts' => [
                'filtered' => app(CareersQuery::class)->filtered($filters)->count(),
                'selected' => count($ids),
                'all' => Application::query()->where('type', 'JOB')->count(),
            ],
            'zip' => $request->boolean('zip'),
            'zipMax' => CareersExport::ZIP_MAX_APPLICATIONS,
        ]);
    }

    public function export(Request $request, CareersQuery $query, CareersExport $export, AuditLogger $audit): Response|View|RedirectResponse
    {
        $format = Input::text($request, 'format');
        $scope = Input::text($request, 'scope');
        abort_unless(in_array($format, self::FORMATS, true) && in_array($scope, self::SCOPES, true), 422);
        $full = $request->boolean('full_identity');
        $applications = $this->chosen($request, $scope, $query);
        $rows = $export->rows($applications, $full);
        $audit->record('applications.exported', null, [], ['format' => $format, 'scope' => $scope, 'count' => count($rows) - 1, 'identity' => $full ? 'full' : 'masked'], actor: $this->owner($request));
        $name = 'shelter-applications-'.now('Asia/Amman')->format('Y-m-d-His');
        $headers = ['Cache-Control' => 'no-store, private', 'X-Content-Type-Options' => 'nosniff'];

        return match ($format) {
            'csv' => response($export->csv($rows), 200, $headers + ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename="'.$name.'.csv"']),
            'xlsx' => response()->download($export->xlsx($rows, app()->getLocale() === 'ar'), $name.'.xlsx',
                $headers + ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])->deleteFileAfterSend(),
            default => view('dashboard.requests.careers.print', ['rows' => $rows, 'full' => $full]),
        };
    }

    /** The attachments of the selected applications as one ZIP (CAREERS-073). */
    public function zip(Request $request, CareersExport $export, AuditLogger $audit): BinaryFileResponse|RedirectResponse
    {
        $requested = (array) $request->input('ids', []);
        $ids = CareersQuery::jobIds($requested);
        if ($ids === [] || count($requested) > CareersExport::ZIP_MAX_APPLICATIONS) {
            return back()->with('warning', __('dashboard.requests.export.zip_limit', ['max' => CareersExport::ZIP_MAX_APPLICATIONS]));
        }
        $result = $export->attachmentsZip(Application::query()->whereIn('id', $ids)->with('attachments')->orderBy('id')->get());
        $audit->record('attachments.zip_downloaded', null, [], ['applications' => count($ids), 'files' => $result['files']], actor: $this->owner($request));

        return response()->download($result['path'], 'shelter-attachments-'.now('Asia/Amman')->format('Y-m-d-His').'.zip', [
            'Content-Type' => 'application/zip', 'Cache-Control' => 'no-store, private', 'X-Content-Type-Options' => 'nosniff',
        ])->deleteFileAfterSend();
    }

    /** @return Collection<int, Application> */
    private function chosen(Request $request, string $scope, CareersQuery $query): Collection
    {
        $with = ['job.city', 'identity'];

        return match ($scope) {
            'selected' => Application::query()->whereIn('id', CareersQuery::jobIds((array) $request->input('ids', [])))->with($with)->orderByDesc('submitted_at')->get(),
            'all' => Application::query()->where('type', 'JOB')->with($with)->orderByDesc('submitted_at')->get(),
            default => $query->filtered(CareersQuery::filters((array) $request->input('filters', [])))->with($with)->get(),
        };
    }

    private function owner(Request $request): User
    {
        /** @var User $owner */
        $owner = $request->user();

        return $owner;
    }
}
