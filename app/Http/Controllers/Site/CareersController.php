<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Recruitment\Application;
use App\Models\Recruitment\ApplicationAttachment;
use App\Models\Recruitment\JobApplication;
use App\Models\Recruitment\UploadSession;
use App\Services\Forms\FormGuard;
use App\Services\Recruitment\ApplicationSubmitter;
use App\Services\Recruitment\ApplicationTracker;
use App\Services\Recruitment\ApplicationValidator;
use App\Services\Recruitment\AttachmentStore;
use App\Services\Recruitment\CareersForm;
use App\Services\Recruitment\CvDetector;
use App\Support\PageUrl;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

/**
 * Careers (SI-B11, CAREERS-REQUIREMENTS): /ar/careers/ holds the content and THE application form — Arabic only, one
 * form, one submission path; /en/careers/ is content with an Apply link to the Arabic form. Every rule is checked on the
 * server (RECRUITMENT-SECURITY §6): CSRF, honeypot, minimum fill time, form expiry, idempotency, rate limits. Files are
 * uploaded one by one into a draft session (progressive, with a no-JavaScript fallback in the same submit). Nothing
 * sensitive ever goes into a URL; the success page reads the number from the session.
 */
final class CareersController extends Controller
{
    private const SESSION_UPLOADS = 'careers.upload_session';

    private const SESSION_SUBMITTED = 'careers.submitted';

    public function __construct(private readonly CareersForm $form) {}

    public function show(Request $request): View
    {
        $locale = app()->getLocale();
        $canonical = PageUrl::route('careers');
        $crumbs = [
            ['label' => (string) __('site.nav.home'), 'href' => PageUrl::route('home')],
            ['label' => (string) __('careers.title', [], $locale), 'href' => $canonical],
        ];
        $open = $this->form->isOpen();
        $session = $locale === 'ar' && $open ? $this->currentUploadSession($request) : null;
        $drafts = $session !== null ? ApplicationSubmitter::drafts($session) : [];

        return view($locale === 'ar' ? 'site.careers' : 'site.careers-en', [
            'canonical' => $canonical,
            'alternates' => PageUrl::alternates('careers'),
            'crumbs' => $crumbs,
            'open' => $open,
            'cities' => $open ? $this->form->cities() : collect(),
            'consent' => $this->form->consent(),
            'years' => ApplicationValidator::birthYears(),
            'drafts' => $drafts,
            'cv' => CvDetector::decide($drafts),
            'formToken' => FormGuard::token($request, $this->maxFormAge()),
            'idempotencyKey' => FormGuard::idempotencyKey($request),
            'applyUrl' => PageUrl::route('careers', ['locale' => 'ar']).'#apply',
            'jsonLd' => [StructuredData::breadcrumbs($crumbs)],
        ]);
    }

    public function submit(Request $request, ApplicationValidator $validator, AttachmentStore $store, ApplicationSubmitter $submitter): RedirectResponse
    {
        abort_unless(app()->getLocale() === 'ar' && $this->form->isOpen(), 404);
        // No #fragment: browsers skip `autofocus` when the URL targets an element, and the error summary must take focus.
        $back = PageUrl::route('careers');
        // Kept for the form after an error — except the identity number, which never sits in session storage (it is
        // re-typed; RECRUITMENT-SECURITY §5). Files stay in the draft session on the server.
        // The form token travels back too: the minimum-time check counts from the FIRST time the form was opened, so a
        // person who fixes one field and resends quickly is never taken for a bot.
        $input = $request->except(['files', FormGuard::HONEYPOT, '_token', 'national_id', 'document_number']);

        // Bots: a filled honeypot or a form sent faster than a person can fill it → a generic refusal (F-09).
        $guard = FormGuard::check($request, (int) config('careers.abuse.min_fill_seconds'), $this->maxFormAge());
        if ($guard !== 'ok') {
            return redirect()->to($back)->withInput($input)->withErrors(['form' => $guard === 'bot' ? __('careers.errors.generic') : __('careers.errors.expired')]);
        }
        $client = FormGuard::clientKey($request);
        foreach ([['careers-submit-hour:'.$client, 'submit_per_hour', 3600], ['careers-submit-day:'.$client, 'submit_per_day', 86400]] as [$key, $limit, $decay]) {
            if (RateLimiter::tooManyAttempts($key, (int) config('careers.abuse.'.$limit))) {
                return redirect()->to($back)->withInput($input)->withErrors(['form' => __('careers.errors.rate')]);
            }
        }

        $idempotencyKey = (string) $request->input('idempotency_key');
        if (! Str::isUuid($idempotencyKey)) {
            return redirect()->to($back)->withInput($input)->withErrors(['form' => __('careers.errors.expired')]);
        }
        $done = Application::query()->where('idempotency_key', $idempotencyKey)->first();
        if ($done !== null) {
            return $this->success($done); // a double click or a replay returns the same application
        }

        // Files sent with the form itself (no JavaScript): same pipeline as the progressive upload.
        $session = $this->uploadSession($request);
        $errors = [];
        foreach ((array) $request->file('files', []) as $file) {
            if ($file instanceof UploadedFile) {
                $stored = $store->store($session, $file);
                if (is_string($stored)) {
                    $errors['files'] = __('careers.upload.errors.'.$stored);
                }
            }
        }

        $result = $validator->validate($request->all(), array_values($this->form->cities()->map(fn ($city): int => $city->id)->all()));
        $errors = $result['errors'] + $errors;

        $drafts = ApplicationSubmitter::drafts($session);
        $chosen = filter_var($request->input('primary_attachment'), FILTER_VALIDATE_INT);
        $chosenFile = $chosen === false ? null : collect($drafts)->first(fn (ApplicationAttachment $a): bool => $a->id === $chosen);
        $decision = CvDetector::decide($drafts);
        if ($drafts === []) {
            $errors['files'] ??= __('careers.errors.cv_required');
        } elseif ($chosenFile === null && $decision['primary'] === null) {
            $errors['files'] ??= __('careers.errors.cv_choice');
        }

        if ($errors !== []) {
            return redirect()->to($back)->withInput($input)->withErrors($this->ordered($errors));
        }

        $phone = (string) $result['data']['phone_normalized'];
        $recent = JobApplication::query()->where('phone_normalized', $phone)
            ->whereHas('application', fn ($q) => $q->where('submitted_at', '>=', now()->subDay()))->count();
        if ($recent >= (int) config('careers.abuse.submit_per_phone_per_day')) {
            return redirect()->to($back)->withInput($input)->withErrors(['form' => __('careers.errors.rate')]);
        }

        $primary = $chosenFile ?? collect($drafts)->first(fn (ApplicationAttachment $a): bool => $a->id === $decision['primary']);
        if (! $primary instanceof ApplicationAttachment) {
            return redirect()->to($back)->withInput($input)->withErrors(['files' => __('careers.errors.cv_choice')]);
        }
        $primary->update(['cv_detection' => $chosenFile !== null && $decision['primary'] !== $chosenFile->id ? 'applicant_selected' : $decision['state']]);

        $consent = $this->form->consent();
        abort_if($consent === null, 404);
        try {
            $application = $submitter->submit($result['data'], $session, $primary->id, $idempotencyKey, $consent);
        } catch (Throwable $e) {
            report($e);

            return redirect()->to($back)->withInput($input)->withErrors(['form' => __('careers.errors.generic')]);
        }
        RateLimiter::hit('careers-submit-hour:'.$client, 3600);
        RateLimiter::hit('careers-submit-day:'.$client, 86400);
        $request->session()->forget(self::SESSION_UPLOADS);

        return $this->success($application);
    }

    public function upload(Request $request, AttachmentStore $store): JsonResponse
    {
        abort_unless(app()->getLocale() === 'ar' && $this->form->isOpen(), 404);
        $key = 'careers-upload:'.FormGuard::clientKey($request);
        if (RateLimiter::tooManyAttempts($key, (int) config('careers.abuse.uploads_per_hour'))) {
            return response()->json(['error' => 'rate', 'message' => __('careers.upload.errors.rate')], 429);
        }
        RateLimiter::hit($key, 3600);
        $file = $request->file('file');
        if (! $file instanceof UploadedFile) {
            return response()->json(['error' => 'too_large', 'message' => __('careers.upload.errors.too_large')], 422);
        }
        $session = $this->uploadSession($request);
        $stored = $store->store($session, $file);
        if (is_string($stored)) {
            return response()->json(['error' => $stored, 'message' => __('careers.upload.errors.'.$stored)], 422);
        }

        return response()->json(['file' => $this->fileJson($stored), 'cv' => CvDetector::decide(ApplicationSubmitter::drafts($session))], 201);
    }

    public function removeUpload(Request $request, int $attachment, AttachmentStore $store): JsonResponse
    {
        abort_unless(app()->getLocale() === 'ar', 404);
        $session = $this->currentUploadSession($request);
        $file = $session === null ? null : ApplicationAttachment::query()
            ->where('upload_session_id', $session->id)->whereNull('application_id')->find($attachment);
        abort_if($file === null || $session === null, 404);
        $store->remove($file);

        return response()->json(['cv' => CvDetector::decide(ApplicationSubmitter::drafts($session))]);
    }

    public function submitted(Request $request): View|RedirectResponse
    {
        abort_unless(app()->getLocale() === 'ar', 404);
        $number = $request->session()->get(self::SESSION_SUBMITTED);
        if (! is_string($number)) {
            return redirect()->to(PageUrl::route('careers'));
        }

        return view('site.careers-submitted', ['number' => $number, 'noindex' => true, 'canonical' => PageUrl::route('careers')]);
    }

    public function track(Request $request, ApplicationTracker $tracker): View
    {
        abort_unless(app()->getLocale() === 'ar', 404);
        $status = null;
        $error = null;
        if ($request->isMethod('post')) {
            $number = (string) $request->input('number', '');
            [$allowed, $error] = $this->trackingAllowed(FormGuard::clientKey($request), strtoupper(trim($number)));
            if ($allowed) {
                $status = $tracker->publicStatus($number, (string) $request->input('phone', ''));
                if ($status === null) {
                    usleep(random_int(150_000, 300_000)); // same answer, about the same time, whatever was wrong
                    $error = __('careers.track.not_found');
                }
            }
        }

        return view('site.careers-track', [
            'status' => $status,
            'error' => $error,
            'noindex' => true,
            'canonical' => PageUrl::route('careers.track'),
            'alternates' => [],
        ]);
    }

    /**
     * RECRUITMENT-SECURITY §7: 5 tries / 15 min per address, 10 / day per number, cooldown 1 → 5 → 30 min.
     * `$client` = FormGuard::clientKey — a keyed hash, never the address.
     *
     * @return array{0: bool, 1: ?string}
     */
    private function trackingAllowed(string $client, string $number): array
    {
        $cooldownKey = 'careers-track-cooldown:'.$client;
        if (RateLimiter::tooManyAttempts($cooldownKey, 1)) {
            return [false, (string) __('careers.track.cooldown')];
        }
        $windowKey = 'careers-track:'.$client;
        $numberKey = 'careers-track-number:'.hash('sha256', $number);
        if (RateLimiter::tooManyAttempts($windowKey, (int) config('careers.tracking.attempts_per_window'))
            || RateLimiter::tooManyAttempts($numberKey, (int) config('careers.tracking.attempts_per_reference_per_day'))) {
            $stageKey = 'careers-track-stage:'.$client;
            $stage = min(RateLimiter::attempts($stageKey), 2);
            /** @var list<int> $cooldowns */
            $cooldowns = config('careers.tracking.cooldown_seconds');
            RateLimiter::hit($cooldownKey, $cooldowns[$stage] ?? 1800);
            RateLimiter::hit($stageKey, 86400);
            RateLimiter::clear($windowKey);

            return [false, (string) __('careers.track.cooldown')];
        }
        RateLimiter::hit($windowKey, (int) config('careers.tracking.window_seconds'));
        RateLimiter::hit($numberKey, 86400);

        return [true, null];
    }

    private function success(Application $application): RedirectResponse
    {
        session()->flash(self::SESSION_SUBMITTED, $application->reference_number);

        return redirect()->to(PageUrl::route('careers.submitted'));
    }

    /** The form's draft upload session, kept server-side in the visitor's session (bound to the CSRF session). */
    private function currentUploadSession(Request $request): ?UploadSession
    {
        $id = $request->session()->get(self::SESSION_UPLOADS);

        return is_string($id) ? UploadSession::query()->where('id', $id)->where('expires_at', '>', now())->first() : null;
    }

    private function uploadSession(Request $request): UploadSession
    {
        $session = $this->currentUploadSession($request);
        if ($session === null) {
            $session = UploadSession::query()->create([
                'expires_at' => now()->addHours((int) config('careers.uploads.draft_ttl_hours')),
                'ip_hash' => hash_hmac('sha256', (string) $request->ip(), (string) config('app.key')),
            ]);
            $request->session()->put(self::SESSION_UPLOADS, $session->id);
        }

        return $session;
    }

    private function maxFormAge(): int
    {
        return (int) config('careers.abuse.max_form_age_hours') * 3600;
    }

    /**
     * Errors in the order of the form, so the summary reads top to bottom.
     *
     * @param  array<string, string>  $errors
     * @return array<string, string>
     */
    private function ordered(array $errors): array
    {
        $order = ['full_name', 'phone', 'email', 'gender', 'birth_date', 'marital_status', 'nationality_type', 'national_id', 'nationality_text',
            'document_number', 'city_id', 'area', 'job_title', 'education_level', 'experience_band', 'same_field_experience',
            'currently_employed', 'expected_salary', 'has_driving_license', 'notes', 'files', 'consent'];

        $sorted = [];
        foreach ($order as $key) {
            if (isset($errors[$key])) {
                $sorted[$key] = $errors[$key];
            }
        }

        return $sorted + $errors;
    }

    /** @return array{id: int, name: string, size: int, family: string} */
    private function fileJson(ApplicationAttachment $file): array
    {
        return ['id' => $file->id, 'name' => $file->original_filename, 'size' => $file->size_bytes, 'family' => $file->file_family];
    }
}
