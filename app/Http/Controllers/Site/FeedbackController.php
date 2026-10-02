<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Services\Feedback\FeedbackForm;
use App\Services\Forms\FormGuard;
use App\Support\PageUrl;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

/**
 * "How was your visit?" (SI-B16, VOICE-OF-CUSTOMER): anonymous ratings per branch. noindex, out of the sitemap, no
 * structured data, linked from nowhere until the Owner picks its entry points (PO-063); closed in production until then.
 * A QR or branch link may preset the branch (?branch=drive) — no other parameter is read. The next screen is the same
 * for every rating (no review gating) and carries no reference number. Protection: CSRF, honeypot, minimum fill time,
 * idempotency, rate limits on a hashed address — nothing identifying is stored.
 */
final class FeedbackController extends Controller
{
    private const SESSION_SUBMITTED = 'feedback.submitted';

    public function __construct(private readonly FeedbackForm $form) {}

    public function show(Request $request): View
    {
        $locale = app()->getLocale();
        abort_unless($this->form->isOpen($locale), 404);
        $branches = $this->form->branches($locale);
        $preset = $request->query('branch');

        return view('site.feedback', [
            'branches' => $branches,
            'preset' => is_string($preset) ? $preset : null,
            'formToken' => FormGuard::token($request, $this->maxFormAge()),
            'idempotencyKey' => FormGuard::idempotencyKey($request),
            'noindex' => true,
            'canonical' => PageUrl::route('feedback'),
            'alternates' => PageUrl::alternates('feedback'),
        ]);
    }

    public function submit(Request $request): RedirectResponse
    {
        $locale = app()->getLocale();
        abort_unless($this->form->isOpen($locale), 404);
        $back = PageUrl::route('feedback');
        $input = $request->except([FormGuard::HONEYPOT, '_token']);

        $guard = FormGuard::check($request, (int) config('feedback.abuse.min_fill_seconds'), $this->maxFormAge());
        if ($guard !== 'ok') {
            return redirect()->to($back)->withInput($input)->withErrors(['form' => __($guard === 'bot' ? 'feedback.errors.generic' : 'feedback.errors.expired')]);
        }
        $client = FormGuard::clientKey($request);
        foreach ([['feedback-10m:'.$client, 'per_ten_minutes'], ['feedback-day:'.$client, 'per_day']] as [$key, $limit]) {
            if (RateLimiter::tooManyAttempts($key, (int) config('feedback.abuse.'.$limit))) {
                return redirect()->to($back)->withInput($input)->withErrors(['form' => __('feedback.errors.rate')]);
            }
        }
        $idempotencyKey = (string) $request->input('idempotency_key');
        if (! Str::isUuid($idempotencyKey)) {
            return redirect()->to($back)->withInput($input)->withErrors(['form' => __('feedback.errors.expired')]);
        }
        if (Feedback::query()->where('idempotency_key', $idempotencyKey)->exists()) {
            return $this->thanks();
        }

        $result = $this->form->validate($request->all(), $this->form->branches($locale));
        if ($result['errors'] !== []) {
            return redirect()->to($back)->withInput($input)->withErrors($result['errors']);
        }
        $entryPoint = $request->input('entry') === 'branch_link' ? 'branch_link' : 'direct';
        try {
            $this->form->record($result['data'], $locale, $entryPoint, $idempotencyKey);
        } catch (Throwable $e) {
            report($e);

            return redirect()->to($back)->withInput($input)->withErrors(['form' => __('feedback.errors.generic')]);
        }
        RateLimiter::hit('feedback-10m:'.$client, 600);
        RateLimiter::hit('feedback-day:'.$client, 86400);

        return $this->thanks();
    }

    public function submitted(Request $request): View|RedirectResponse
    {
        if ($request->session()->get(self::SESSION_SUBMITTED) !== true) {
            return redirect()->to(PageUrl::route('feedback'));
        }

        return view('site.feedback-submitted', ['noindex' => true, 'canonical' => PageUrl::route('feedback'), 'alternates' => []]);
    }

    private function thanks(): RedirectResponse
    {
        session()->flash(self::SESSION_SUBMITTED, true);

        return redirect()->to(PageUrl::route('feedback.submitted'));
    }

    private function maxFormAge(): int
    {
        return (int) config('feedback.abuse.max_form_age_hours') * 3600;
    }
}
