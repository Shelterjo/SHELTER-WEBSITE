<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Market;
use App\Services\Experiences\Events;
use App\Support\PageUrl;
use App\Support\StructuredData;
use Illuminate\Contracts\View\View;

/**
 * Events and campaigns of a market (SI-M07 /ar/jo/events/, SI-M08 /ar/jo/events/{slug}/ — DX-014). The listing is
 * indexable only while it lists something; an event page carries Event data only while the event is valid (not ended).
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
        if (! $event->isEnded()) {
            $jsonLd[] = array_filter([
                '@context' => 'https://schema.org',
                '@type' => 'Event',
                'name' => $event->title,
                'startDate' => $event->startsAt->toIso8601String(),
                'endDate' => $event->endsAt->toIso8601String(),
                'eventStatus' => 'https://schema.org/EventScheduled',
                'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
                'location' => $event->place !== null ? ['@type' => 'Place', 'name' => $event->place] : null,
                'description' => $event->paragraphs[0] ?? null,
                'organizer' => ['@type' => 'Organization', 'name' => 'SHELTER COFFEE', 'url' => PageUrl::route('gateway')],
                'url' => $event->url,
                'inLanguage' => $locale,
            ], fn (mixed $value): bool => $value !== null);
        }

        return view('site.event', [
            'canonical' => $event->url,
            'alternates' => PageUrl::alternates('events.show', ['market' => $market->code, 'slug' => $slug]),
            'crumbs' => $crumbs,
            'event' => $event,
            'listing' => $listing,
            'noindex' => $event->isEnded(),
            'description' => $event->paragraphs[0] ?? null,
            'jsonLd' => $jsonLd,
        ]);
    }
}
