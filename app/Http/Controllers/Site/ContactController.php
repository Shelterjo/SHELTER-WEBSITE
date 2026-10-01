<?php

namespace App\Http\Controllers\Site;

use App\Enums\ContactKind;
use App\Http\Controllers\Controller;
use App\Services\Site\BranchDirectory;
use App\Services\Site\ContactActions;
use App\Services\Site\Markets;
use App\Support\PageUrl;
use App\Support\SiteLinks;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;

/**
 * Contact by intent (/ar/contact/ — SI-B04, D-059, docs/phase-01-discovery/14 §4): every card starts with the reason
 * for getting in touch, then its approved number. General & Branches (main number + WhatsApp + each branch's live
 * state), Complaints & Feedback, Catering/B2B & Events, Franchise inquiries (shown from launch — D-071). The email
 * appears only once it is approved for publication (D-035). No directions until the map links are approved (PO-010).
 * Only the main number goes into structured data (CT-06). The inquiry form joins in PHASE 4 (INQ).
 */
final class ContactController extends Controller
{
    public function __invoke(Markets $markets, BranchDirectory $directory, ContactActions $contacts): View
    {
        $locale = app()->getLocale();
        $market = $markets->current();
        $canonical = PageUrl::route('contact');
        $crumbs = [
            ['label' => (string) __('site.nav.home'), 'href' => PageUrl::route('home')],
            ['label' => (string) __('site.contact.title'), 'href' => $canonical],
        ];
        $parameters = ['locale' => $locale, 'market' => $market?->code];

        return view('site.contact', [
            'canonical' => $canonical,
            'alternates' => PageUrl::alternates('contact'),
            'crumbs' => $crumbs,
            'branches' => $market !== null ? $directory->forMarket($market, $locale) : [],
            'phone' => $contacts->intent(ContactKind::PhoneMain, $locale),
            'whatsapp' => $contacts->intent(ContactKind::Whatsapp, $locale),
            'complaints' => $contacts->intent(ContactKind::ComplaintsFeedbackFranchise, $locale),
            'catering' => $contacts->intent(ContactKind::CateringB2bEvents, $locale),
            'email' => $contacts->intent(ContactKind::Email, $locale),
            'franchiseUrl' => SiteLinks::to('franchise', $parameters),
            'locationsUrl' => $market !== null ? SiteLinks::to('locations', $parameters) : null,
            'jsonLd' => [
                StructuredData::breadcrumbs($crumbs),
                ['@context' => 'https://schema.org', '@type' => 'ContactPage', 'name' => (string) __('site.contact.title'), 'url' => $canonical, 'inLanguage' => $locale],
            ],
        ]);
    }
}
