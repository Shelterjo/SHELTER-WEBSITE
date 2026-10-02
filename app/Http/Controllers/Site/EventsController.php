<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Services\Experiences\Events;
use App\Support\PageUrl;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;

/**
 * Events and campaigns of a market (SI-M07 /ar/jo/events/, SI-M08 /ar/jo/events/{slug}/ — DX-014). The listing is
 * indexable only while it lists something; an event page carries Event data only while the event is valid (not ended)
 * and its place is one of our branches (an address from the Master Data — SCHEMA-009); otherwise the reason is logged.
 */
final class EventsController extends Controller
{
    public function index(Market $market, Events $events): View
    {
        $locale = app()->getLocale();
        $canonical = PageUrl::route('events');
        $crumbs = [
            ['label' => (string) __('site.nav.home'), 'href' => PageUrl::route('home')],
            ['label' => (string) __('site.events.title'), 'href' => $canonical],
        ];
        $list = $events->listed($market, $locale);

        return view('site.events', [
            'canonical' => $canonical,
            'alternates' => PageUrl::alternates('events'),
            'crumbs' => $crumbs,
            'events' => $list,
            'market' => $market,
            'noindex' => $list === [],
            'jsonLd' => [StructuredData::breadcrumbs($crumbs)],
        ]);
    }

    public function show(Market $market, string $slug, Events $events): View
    {
        $locale = app()->getLocale();
        $event = $events->find($market, $slug, $locale);
        abort_if($event === null, 404);

        $listing = PageUrl::route('events');
        $crumbs = [
            ['label' => (string) __('site.nav.home'), 'href' => PageUrl::route('home')],
            ['label' => (string) __('site.events.title'), 'href' => $listing],
            ['label' => $event->title, 'href' => $event->url],
        ];
        $jsonLd = [StructuredData::breadcrumbs($crumbs)];
        // The event's approved image, as an absolute address for schema.org and share previews (FINAL-QA QA-041).
        $image = $event->image !== null ? url($event->image->src) : null;
        if (! $event->isEnded()) {
            // SCHEMA-009: Event data only with a place whose address is in the Master Data — never an invented one.
            $schema = StructuredData::event($event, PageUrl::route('gateway'), $locale, $image);
            if ($schema !== null) {
                $jsonLd[] = $schema;
            } else {
                Log::info('schema.event_omitted', ['event' => $event->slug, 'reason' => StructuredData::eventGap($event)]);
            }
        }

        return view('site.event', [
            'canonical' => $event->url,
            'alternates' => PageUrl::alternates('events.show', ['market' => $market->code, 'slug' => $slug]),
            'crumbs' => $crumbs,
            'event' => $event,
            'listing' => $listing,
            'noindex' => $event->isEnded(),
            'description' => $event->paragraphs[0] ?? null,
            'ogType' => 'article',
            'ogImage' => $image,
            'jsonLd' => $jsonLd,
        ]);
    }
}
