<?php

namespace App\Http\Controllers\Site;

use App\Enums\ContactKind;
use App\Http\Controllers\Controller;
use App\Models\Recruitment\Application;
use App\Models\Recruitment\PartnershipApplication;
use App\Services\Content\ContentSection;
use App\Services\Content\Pages;
use App\Services\Forms\FormGuard;
use App\Services\Franchise\FranchiseForm;
use App\Services\Franchise\PartnershipSubmitter;
use App\Services\Franchise\PartnershipValidator;
use App\Services\Site\ContactActions;
use App\Support\Countries;
use App\Support\Input;
use App\Support\PageUrl;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

/**
 * Franchise & partnerships (SI-B12, docs/franchise/*, M29). The page exists only once its content is published by the
 * Owner (content V1 approved in M47 — unpublished: 404, never an empty or invented page). The partnership form (FR)
 * opens only with its approved acknowledgement (PF-02) and consent (PF-03); before that the page offers the franchise
 * inquiries contact (D-071).
 * Same protections as careers: CSRF, honeypot, minimum time, idempotency, rate limits; nothing personal in a URL.
 * Structured data: WebPage, BreadcrumbList and FAQPage for published answers only — never Offer, Price or Rating.
 */
final class FranchiseController extends Controller
{
    private const SESSION_SUBMITTED = 'franchise.submitted';

    public function __construct(private readonly Pages $pages, private readonly FranchiseForm $form) {}

    public function show(Request $request, ContactActions $contacts): View
    {
        $locale = app()->getLocale();
        $page = $this->pages->published('franchise', $locale);
        abort_if($page === null, 404);

        $canonical = PageUrl::route('franchise');
        $crumbs = [
            ['label' => (string) __('site.nav.home'), 'href' => PageUrl::route('home')],
            ['label' => $page->name, 'href' => $canonical],
        ];
        $faq = array_values(array_filter($page->sections, fn (ContentSection $s): bool => $s->type === 'faq'));
        $jsonLd = [
            StructuredData::breadcrumbs($crumbs),
            ['@context' => 'https://schema.org', '@type' => 'WebPage', 'name' => $page->name, 'url' => $canonical, 'inLanguage' => $locale],
        ];
        if ($faq !== []) {
            $jsonLd[] = ['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => array_map(fn (ContentSection $item): array => [
                '@type' => 'Question', 'name' => (string) $item->heading,
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => implode("\n\n", $item->paragraphs)],
            ], $faq)];
        }
        $open = $this->form->isOpen();

        return view('site.franchise', [
            'page' => $page,
            'canonical' => $canonical,
            'alternates' => PageUrl::alternates('franchise'),
            'description' => $page->description,
            'crumbs' => $crumbs,
            'open' => $open,
            'acknowledgement' => $open ? $this->form->acknowledgement() : null,
            'consent' => $open ? $this->form->consent() : null,
            'countries' => $open ? Countries::options($locale) : [],
            'inquiries' => $contacts->intent(ContactKind::ComplaintsFeedbackFranchise, $locale),
            'formToken' => FormGuard::token($request, $this->maxFormAge()),
            'idempotencyKey' => FormGuard::idempotencyKey($request),
            'attribution' => $this->attribution($request),
            'jsonLd' => $jsonLd,
        ]);
    }

    public function submit(Request $request, PartnershipValidator $validator, PartnershipSubmitter $submitter): RedirectResponse
    {
        $locale = app()->getLocale();
        abort_unless($this->pages->published('franchise', $locale) !== null && $this->form->isOpen(), 404);
        // No #fragment: browsers skip `autofocus` when the URL targets an element, and the error summary must take focus.
        $back = PageUrl::route('franchise');
        $input = $request->except([FormGuard::HONEYPOT, '_token']);

        $guard = FormGuard::check($request, (int) config('franchise.abuse.min_fill_seconds'), $this->maxFormAge());
        if ($guard !== 'ok') {
            return redirect()->to($back)->withInput($input)->withErrors(['form' => __($guard === 'bot' ? 'franchise.errors.generic' : 'franchise.errors.expired')]);
        }
        $client = FormGuard::clientKey($request);
        foreach ([['franchise-submit-hour:'.$client, 'submit_per_hour'], ['franchise-submit-day:'.$client, 'submit_per_day']] as [$key, $limit]) {
            if (RateLimiter::tooManyAttempts($key, (int) config('franchise.abuse.'.$limit))) {
                return redirect()->to($back)->withInput($input)->withErrors(['form' => __('franchise.errors.rate')]);
            }
        }
        if (! FormGuard::attempt('franchise', $request, (int) config('franchise.abuse.attempts_per_hour'))) {
            return redirect()->to($back)->withInput($input)->withErrors(['form' => __('franchise.errors.rate')]);
        }
        $idempotencyKey = Input::text($request, 'idempotency_key');
        if (! Str::isUuid($idempotencyKey)) {
            return redirect()->to($back)->withInput($input)->withErrors(['form' => __('franchise.errors.expired')]);
        }
        $done = Application::query()->where('idempotency_key', $idempotencyKey)->first();
        if ($done !== null) {
            return $this->success($done);
        }

        $result = $validator->validate($request->all());
        if ($result['errors'] !== []) {
            return redirect()->to($back)->withInput($input)->withErrors($result['errors']);
        }
        $recent = PartnershipApplication::query()->where('phone_normalized', (string) $result['data']['phone_normalized'])
            ->whereHas('application', fn ($q) => $q->where('submitted_at', '>=', now()->subDay()))->count();
        if ($recent >= (int) config('franchise.abuse.submit_per_phone_per_day')) {
            return redirect()->to($back)->withInput($input)->withErrors(['form' => __('franchise.errors.rate')]);
        }

        $acknowledgement = $this->form->acknowledgement();
        $consent = $this->form->consent();
        abort_if($acknowledgement === null || $consent === null, 404);
        try {
            $application = $submitter->submit($result['data'], $this->attribution($request, fromForm: true), $locale, $idempotencyKey, $acknowledgement, $consent);
        } catch (Throwable $e) {
            report($e);

            return redirect()->to($back)->withInput($input)->withErrors(['form' => __('franchise.errors.generic')]);
        }
        RateLimiter::hit('franchise-submit-hour:'.$client, 3600);
        RateLimiter::hit('franchise-submit-day:'.$client, 86400);

        return $this->success($application);
    }

    public function submitted(Request $request): View|RedirectResponse
    {
        $number = $request->session()->get(self::SESSION_SUBMITTED);
        if (! is_string($number)) {
            return redirect()->to(PageUrl::route('franchise'));
        }

        return view('site.franchise-submitted', ['number' => $number, 'noindex' => true, 'canonical' => PageUrl::route('franchise')]);
    }

    private function success(Application $application): RedirectResponse
    {
        session()->flash(self::SESSION_SUBMITTED, $application->reference_number);

        return redirect()->to(PageUrl::route('franchise.submitted'));
    }

    /**
     * Privacy-safe attribution (franchise field matrix): UTM values and the landing path from the visit, the referrer's
     * DOMAIN only — never an IP, a full referrer URL or a precise location. Captured on the page, carried by the form.
     *
     * @return array{utm_source: ?string, utm_medium: ?string, utm_campaign: ?string, landing_path: ?string, referrer_domain: ?string}
     */
    private function attribution(Request $request, bool $fromForm = false): array
    {
        $value = fn (string $key, int $max): ?string => ($v = $fromForm ? $request->input('attr_'.$key) : $request->query($key)) !== null && is_string($v) && trim($v) !== ''
            ? mb_substr(trim(strip_tags($v)), 0, $max) : null;
        $referrer = $fromForm ? $request->input('attr_referrer_domain') : parse_url((string) $request->headers->get('referer'), PHP_URL_HOST);

        return [
            'utm_source' => $value('utm_source', 100),
            'utm_medium' => $value('utm_medium', 100),
            'utm_campaign' => $value('utm_campaign', 150),
            'landing_path' => $fromForm ? $value('landing_path', 255) : mb_substr($request->getPathInfo(), 0, 255),
            'referrer_domain' => is_string($referrer) && preg_match('/^[a-z0-9.-]{1,255}$/i', $referrer) === 1 ? strtolower($referrer) : null,
        ];
    }

    private function maxFormAge(): int
    {
        return (int) config('franchise.abuse.max_form_age_hours') * 3600;
    }
}
