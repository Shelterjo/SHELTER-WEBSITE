<?php

namespace App\Support;

use App\Services\Experiences\EventView;
use App\Services\Site\BranchSummary;

/**
 * JSON-LD for public pages (SITE-INVENTORY schema column; allowed types only). Approved values only, all read from the
 * Master Data Hub (M57 §18–§19): a branch has name, url, logo, telephone (D-060), its city and country (D-008), the
 * menu and its opening hours — regular weeks plus the published exception days ahead; the street address, geo and
 * Maps link appear only once the Owner approves them (PO-010). An event's place is the same branch data. The entities
 * are linked by @id: the website, each branch and each event's organizer point to the one Organization. Past-midnight
 * hours keep closes < opens in one specification (MDH-022). Every block is checked against its rich-result rules by
 * tests/Feature/Site/StructuredDataValidationTest (SCHEMA-009).
 */
final class StructuredData
{
    private const DAYS = [0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday'];

    /**
     * @param  list<array{label: string, href: string}>  $crumbs
     * @return array<string, mixed>
     */
    public static function breadcrumbs(array $crumbs): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn (array $crumb, int $i): array => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb['label'],
                'item' => $crumb['href'],
            ], $crumbs, array_keys($crumbs)),
        ];
    }

    /**
     * @param  string  $site  the site root (the gateway) that owns the Organization @id
     * @return array<string, mixed>
     */
    public static function cafe(BranchSummary $branch, ?string $telephone, string $logo, string $site, ?string $menuUrl = null): array
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'CafeOrCoffeeShop',
            '@id' => $branch->url.'#branch',
            'name' => $branch->name,
            'url' => $branch->url,
            'logo' => $logo,
            'image' => $logo,
            'parentOrganization' => ['@id' => self::organizationId($site)],
        ];
        if ($telephone !== null) {
            $data['telephone'] = $telephone;
        }
        $data['address'] = self::address($branch);
        if ($branch->latitude !== null && $branch->longitude !== null) {
            $data['geo'] = ['@type' => 'GeoCoordinates', 'latitude' => (float) $branch->latitude, 'longitude' => (float) $branch->longitude];
        }
        if ($branch->mapsUrl !== null) {
            $data['hasMap'] = $branch->mapsUrl;
        }
        if ($menuUrl !== null) {
            $data['hasMenu'] = $menuUrl;
        }
        $hours = [...self::openingHours($branch), ...self::specialHours($branch)];
        if ($hours !== []) {
            $data['openingHoursSpecification'] = $hours;
        }

        return $data;
    }

    /**
     * Event (SCHEMA-009 — Google's Event rich result requires name, startDate and, for an event at a venue,
     * location.address). The location is each of our branches the event is at, by its approved name and address. An
     * event whose place has no address in the Master Data — another venue typed as text — or no place at all gets no
     * Event data (null; the reason is self::eventGap()): nothing is invented to make it valid.
     *
     * @param  string  $site  the site root (the gateway) that owns the Organization @id
     * @param  string|null  $image  the event's approved image, absolute
     * @return array<string, mixed>|null
     */
    public static function event(EventView $event, string $site, string $locale, ?string $image): ?array
    {
        if (self::eventGap($event) !== null) {
            return null;
        }
        $places = array_map(fn (BranchSummary $branch): array => self::place($branch), $event->branches);

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => $event->title,
            'startDate' => $event->startsAt->toIso8601String(),
            'endDate' => $event->endsAt->toIso8601String(),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'location' => count($places) === 1 ? $places[0] : $places,
            'description' => $event->paragraphs[0] ?? null,
            'image' => $image,
            'organizer' => ['@type' => 'Organization', '@id' => self::organizationId($site), 'name' => 'SHELTER COFFEE', 'url' => $site],
            'url' => $event->url,
            'inLanguage' => $locale,
        ], fn (mixed $value): bool => $value !== null);
    }

    /** Why an event carries no Event data (null = it does): `venue_without_address` · `no_location`. */
    public static function eventGap(EventView $event): ?string
    {
        return match (true) {
            $event->atVenue => 'venue_without_address',
            $event->branches === [] => 'no_location',
            default => null,
        };
    }

    /** The one Organization every other entity points to. */
    public static function organizationId(string $site): string
    {
        return rtrim($site, '/').'/#organization';
    }

    /**
     * The website itself, for the site name in search results (M57 §28): one name, the Arabic name as an alternate.
     *
     * @param  list<string>  $languages
     * @return array<string, mixed>
     */
    public static function website(string $name, string $alternateName, string $site, array $languages): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => rtrim($site, '/').'/#website',
            'name' => $name,
            'alternateName' => $alternateName,
            'url' => $site,
            'inLanguage' => $languages,
            'publisher' => ['@id' => self::organizationId($site)],
        ];
    }

    /**
     * @param  array<string, mixed>  $extra  approved optional fields (e.g. foundingDate)
     * @return array<string, mixed>
     */
    public static function organization(string $name, string $alternateName, string $url, string $logo, array $extra = []): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => self::organizationId($url),
            'name' => $name,
            'alternateName' => $alternateName,
            'url' => $url,
            'logo' => $logo,
        ] + $extra;
    }

    /**
     * JSON for a <script type="application/ld+json"> block: "<", ">" and "&" are escaped, so it can never close the tag.
     *
     * @param  array<string, mixed>  $data
     */
    public static function encode(array $data): string
    {
        return (string) json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR);
    }

    /**
     * A branch's address: the street once the Owner approved it (PO-010), the city of the fixed page address (D-008,
     * D-053) and its country — the last two written in English / ISO as machine values.
     *
     * @return array<string, string>
     */
    private static function address(BranchSummary $branch): array
    {
        $address = ['@type' => 'PostalAddress'];
        if ($branch->address !== null) {
            $address['streetAddress'] = $branch->address;
        }
        $address['addressLocality'] = $branch->branch->city->name_en;
        $address['addressCountry'] = $branch->branch->city->country->iso2;

        return $address;
    }

    /**
     * A branch as an event's place: its approved name, its page and its address.
     *
     * @return array<string, mixed>
     */
    private static function place(BranchSummary $branch): array
    {
        return ['@type' => 'Place', 'name' => $branch->name, 'url' => $branch->url, 'address' => self::address($branch)];
    }

    /** @return list<array<string, mixed>> */
    private static function openingHours(BranchSummary $branch): array
    {
        $groups = [];
        foreach ($branch->week ?? [] as $day) {
            foreach ($day['intervals'] as $interval) {
                $key = $interval['opens_at'].'-'.$interval['closes_at'];
                $groups[$key] ??= ['opens' => $interval['opens_at'], 'closes' => $interval['closes_at'], 'days' => []];
                $groups[$key]['days'][] = 'https://schema.org/'.self::DAYS[$day['weekday']];
            }
        }

        return array_values(array_map(fn (array $g): array => [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => $g['days'],
            'opens' => $g['opens'],
            'closes' => $g['closes'],
        ], $groups));
    }

    /**
     * Exception days ahead (holiday, special hours, emergency, temporary closure), each valid for its own date only.
     * A closed day is opens = closes = 00:00, as search engines read it.
     *
     * @return list<array<string, mixed>>
     */
    private static function specialHours(BranchSummary $branch): array
    {
        $out = [];
        foreach ($branch->special as $day) {
            $intervals = $day['intervals'] === [] ? [['opens' => '00:00', 'closes' => '00:00']] : $day['intervals'];
            foreach ($intervals as $interval) {
                $out[] = [
                    '@type' => 'OpeningHoursSpecification',
                    'opens' => $interval['opens'],
                    'closes' => $interval['closes'],
                    'validFrom' => $day['date'],
                    'validThrough' => $day['date'],
                ];
            }
        }

        return $out;
    }
}
