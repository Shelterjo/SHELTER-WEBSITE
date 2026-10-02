<?php

namespace Tests\Support;

use Closure;

/**
 * A local structured-data validator (SCHEMA-009 — no external service): every JSON-LD block a page emits is checked
 * against the required and recommended properties of its schema.org type and, where Google has a rich result for it,
 * Google's documented rules (Organization · WebSite site names · LocalBusiness / CafeOrCoffeeShop · BreadcrumbList ·
 * FAQPage · Event · JobPosting), plus schema.org validity for the other types the site emits (Menu, WebPage,
 * ContactPage). An error = the rich result is invalid (or the block is malformed, or carries a placeholder instead of
 * an approved value); a warning = a recommended property is missing — each one is reviewed in the test that uses this.
 * A type without rules here is an error, so a new block cannot slip past unchecked.
 */
final class RichResults
{
    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    private const EVENT_STATUS = ['EventScheduled', 'EventCancelled', 'EventMovedOnline', 'EventPostponed', 'EventRescheduled'];

    private const ATTENDANCE = ['OfflineEventAttendanceMode', 'OnlineEventAttendanceMode', 'MixedEventAttendanceMode'];

    private const LOCAL_BUSINESS = ['LocalBusiness', 'FoodEstablishment', 'CafeOrCoffeeShop', 'Restaurant', 'Bakery'];

    private const FOOD = ['FoodEstablishment', 'CafeOrCoffeeShop', 'Restaurant', 'Bakery'];

    /** @var list<string> */
    private array $errors = [];

    /** @var list<string> */
    private array $warnings = [];

    /**
     * @param  array<mixed>  $block  one decoded <script type="application/ld+json">
     * @return array{errors: list<string>, warnings: list<string>}
     */
    public static function check(array $block): array
    {
        $validator = new self;
        $type = is_string($block['@type'] ?? null) ? $block['@type'] : '?';
        if (($block['@context'] ?? null) !== 'https://schema.org') {
            $validator->error($type, '@context must be "https://schema.org"');
        }
        $validator->noPlaceholders($block, $type);
        $validator->entity($block, $type);

        return ['errors' => $validator->errors, 'warnings' => $validator->warnings];
    }

    /** @param  array<mixed>  $node */
    private function entity(array $node, string $path): void
    {
        $type = $node['@type'] ?? null;
        $rules = match (true) {
            $type === 'Organization' => $this->organization(...),
            $type === 'WebSite' => $this->website(...),
            in_array($type, self::LOCAL_BUSINESS, true) => $this->localBusiness(...),
            $type === 'BreadcrumbList' => $this->breadcrumbs(...),
            $type === 'FAQPage' => $this->faq(...),
            $type === 'Event' => $this->event(...),
            $type === 'JobPosting' => $this->jobPosting(...),
            $type === 'Menu' => $this->menu(...),
            in_array($type, ['WebPage', 'ContactPage', 'AboutPage', 'CollectionPage'], true) => $this->webPage(...),
            default => null,
        };
        if ($rules === null) {
            $this->error($path, 'no validation rules for @type '.json_encode($type));

            return;
        }
        $rules($node, $path);
    }

    /**
     * Google Organization: no required property; name and url identify it; logo must be a crawlable URL.
     *
     * @param  array<mixed>  $node
     */
    private function organization(array $node, string $path): void
    {
        $this->text($node, 'name', $path);
        $this->url($node, 'url', $path);
        $this->recommended($node, 'logo', $path, fn () => $this->absolute($node['logo'], $path.'.logo'));
        if (isset($node['@id'])) {
            $this->absolute($node['@id'], $path.'.@id');
        }
        if (isset($node['foundingDate']) && (! is_string($node['foundingDate']) || preg_match('/^\d{4}(-\d{2}(-\d{2})?)?$/', $node['foundingDate']) !== 1)) {
            $this->error($path, 'foundingDate must be an ISO 8601 date (YYYY, YYYY-MM or YYYY-MM-DD)');
        }
        $this->recommended($node, 'sameAs', $path, fn () => $this->urlList($node['sameAs'], $path.'.sameAs'));
    }

    /**
     * Google site names: WebSite with its name and the home page url.
     *
     * @param  array<mixed>  $node
     */
    private function website(array $node, string $path): void
    {
        $this->text($node, 'name', $path);
        $this->url($node, 'url', $path);
        if (is_string($node['url'] ?? null) && preg_match('#^https?://[^/]+/?$#', $node['url']) !== 1) {
            $this->error($path, 'url must be the site root (site names read the home page)');
        }
        if (isset($node['alternateName'])) {
            $this->text($node, 'alternateName', $path);
        }
    }

    /**
     * Google LocalBusiness: required name + address (PostalAddress); geo, hours, telephone, url, priceRange… recommended.
     *
     * @param  array<mixed>  $node
     */
    private function localBusiness(array $node, string $path): void
    {
        $this->text($node, 'name', $path);
        if (! is_array($node['address'] ?? null)) {
            $this->error($path, 'address (PostalAddress) is required');
        } else {
            $this->postalAddress($node['address'], $path.'.address');
        }
        $this->recommended($node, 'url', $path, fn () => $this->absolute($node['url'], $path.'.url'));
        $this->recommended($node, 'telephone', $path, fn () => $this->telephone($node['telephone'], $path.'.telephone'));
        $this->recommended($node, 'geo', $path, fn () => $this->geo($node['geo'], $path.'.geo'));
        $this->recommended($node, 'priceRange', $path, fn () => $this->text($node, 'priceRange', $path));
        if (in_array($node['@type'], self::FOOD, true)) {
            $this->recommended($node, 'servesCuisine', $path);
            // schema.org hasMenu (Google still names the older `menu`): either one, a URL.
            $menu = isset($node['hasMenu']) ? 'hasMenu' : 'menu';
            $this->recommended($node, $menu, $path, fn () => $this->absolute($node[$menu], $path.'.'.$menu));
        }
        foreach (['logo', 'image', 'hasMap'] as $field) {
            if (isset($node[$field])) {
                $this->absolute($node[$field], $path.'.'.$field);
            }
        }
        $hours = $node['openingHoursSpecification'] ?? null;
        if ($hours === null) {
            $this->warn($path, 'openingHoursSpecification');
        } elseif (! is_array($hours) || ! array_is_list($hours)) {
            $this->error($path, 'openingHoursSpecification must be a list');
        } else {
            foreach ($hours as $i => $spec) {
                $this->openingHours($spec, $path.'.openingHoursSpecification['.$i.']');
            }
        }
    }

    /**
     * Google Breadcrumb: itemListElement of ListItem — position (1, 2, 3…), name, item (a URL; only the last may omit it).
     *
     * @param  array<mixed>  $node
     */
    private function breadcrumbs(array $node, string $path): void
    {
        $items = $node['itemListElement'] ?? null;
        if (! is_array($items) || $items === [] || ! array_is_list($items)) {
            $this->error($path, 'itemListElement must be a non-empty list');

            return;
        }
        foreach ($items as $i => $item) {
            $at = $path.'.itemListElement['.$i.']';
            if (! is_array($item) || ($item['@type'] ?? null) !== 'ListItem') {
                $this->error($at, '@type must be ListItem');

                continue;
            }
            if (($item['position'] ?? null) !== $i + 1) {
                $this->error($at, 'position must be '.($i + 1));
            }
            $this->text($item, 'name', $at);
            if ($i < count($items) - 1 || isset($item['item'])) {
                $this->url($item, 'item', $at);
            }
        }
    }

    /**
     * Google FAQ: mainEntity of Question, each with a name and an acceptedAnswer (Answer with text).
     *
     * @param  array<mixed>  $node
     */
    private function faq(array $node, string $path): void
    {
        $questions = $node['mainEntity'] ?? null;
        if (! is_array($questions) || $questions === [] || ! array_is_list($questions)) {
            $this->error($path, 'mainEntity must be a non-empty list of Question');

            return;
        }
        foreach ($questions as $i => $question) {
            $at = $path.'.mainEntity['.$i.']';
            if (! is_array($question) || ($question['@type'] ?? null) !== 'Question') {
                $this->error($at, '@type must be Question');

                continue;
            }
            $this->text($question, 'name', $at);
            $answer = $question['acceptedAnswer'] ?? null;
            if (! is_array($answer) || ($answer['@type'] ?? null) !== 'Answer') {
                $this->error($at, 'acceptedAnswer (Answer) is required');
            } else {
                $this->text($answer, 'text', $at.'.acceptedAnswer');
            }
        }
    }

    /**
     * Google Event: required name, startDate, location — a Place with an address for an event at a venue; recommended
     * description, endDate, eventStatus, eventAttendanceMode, image, offers, organizer, performer.
     *
     * @param  array<mixed>  $node
     */
    private function event(array $node, string $path): void
    {
        $this->text($node, 'name', $path);
        $starts = $this->dateTime($node, 'startDate', $path, required: true);
        $ends = $this->dateTime($node, 'endDate', $path, required: false);
        if ($starts !== null && $ends !== null && $ends < $starts) {
            $this->error($path, 'endDate is before startDate');
        }
        $location = $node['location'] ?? null;
        if ($location === null) {
            $this->error($path, 'location is required');
        }
        $places = is_array($location) && array_is_list($location) ? $location : ($location === null ? [] : [$location]);
        foreach ($places as $i => $place) {
            $at = $path.'.location'.(count($places) > 1 ? '['.$i.']' : '');
            if (! is_array($place) || ($place['@type'] ?? null) !== 'Place') {
                $this->error($at, 'must be a Place');

                continue;
            }
            $this->recommended($place, 'name', $at, fn () => $this->text($place, 'name', $at));
            $address = $place['address'] ?? null;
            if ($address === null) {
                $this->error($at, 'address is required for an event at a venue');
            } elseif (is_array($address)) {
                $this->postalAddress($address, $at.'.address');
            } elseif (! is_string($address) || trim($address) === '') {
                $this->error($at, 'address must be a PostalAddress or a non-empty text');
            }
            if (isset($place['url'])) {
                $this->absolute($place['url'], $at.'.url');
            }
        }
        $this->recommended($node, 'description', $path, fn () => $this->text($node, 'description', $path));
        $this->enum($node, 'eventStatus', self::EVENT_STATUS, $path);
        $this->enum($node, 'eventAttendanceMode', self::ATTENDANCE, $path);
        $this->recommended($node, 'image', $path, fn () => $this->urlList($node['image'], $path.'.image'));
        if (isset($node['url'])) {
            $this->absolute($node['url'], $path.'.url');
        }
        $this->recommended($node, 'offers', $path);
        $this->recommended($node, 'performer', $path);
        $organizer = $node['organizer'] ?? null;
        if (! is_array($organizer)) {
            $this->warn($path, 'organizer');
        } else {
            $this->text($organizer, 'name', $path.'.organizer');
            $this->url($organizer, 'url', $path.'.organizer');
        }
    }

    /**
     * Google JobPosting: datePosted, description, hiringOrganization, jobLocation (unless remote), title; validThrough,
     * employmentType and baseSalary recommended.
     *
     * @param  array<mixed>  $node
     */
    private function jobPosting(array $node, string $path): void
    {
        foreach (['title', 'description'] as $field) {
            $this->text($node, $field, $path);
        }
        $this->dateTime($node, 'datePosted', $path, required: true);
        $organization = $node['hiringOrganization'] ?? null;
        if (! is_array($organization)) {
            $this->error($path, 'hiringOrganization is required');
        } else {
            $this->text($organization, 'name', $path.'.hiringOrganization');
        }
        if (($node['jobLocationType'] ?? null) !== 'TELECOMMUTE') {
            $location = $node['jobLocation'] ?? null;
            if (! is_array($location) || ! is_array($location['address'] ?? null)) {
                $this->error($path, 'jobLocation (Place with a PostalAddress) is required unless the job is remote');
            } else {
                $this->postalAddress($location['address'], $path.'.jobLocation.address');
            }
        }
        $this->recommended($node, 'validThrough', $path, fn () => $this->dateTime($node, 'validThrough', $path, required: true));
        $this->recommended($node, 'employmentType', $path);
        $this->recommended($node, 'baseSalary', $path);
    }

    /**
     * schema.org Menu → MenuSection → MenuItem → Offer (a price, an ISO 4217 currency). No Google rich result.
     *
     * @param  array<mixed>  $node
     */
    private function menu(array $node, string $path): void
    {
        $this->text($node, 'name', $path);
        if (isset($node['url'])) {
            $this->absolute($node['url'], $path.'.url');
        }
        $sections = $node['hasMenuSection'] ?? null;
        if (! is_array($sections) || $sections === [] || ! array_is_list($sections)) {
            $this->error($path, 'hasMenuSection must be a non-empty list');

            return;
        }
        foreach ($sections as $i => $section) {
            $at = $path.'.hasMenuSection['.$i.']';
            if (! is_array($section) || ($section['@type'] ?? null) !== 'MenuSection') {
                $this->error($at, '@type must be MenuSection');

                continue;
            }
            $this->text($section, 'name', $at);
            foreach (is_array($section['hasMenuItem'] ?? null) ? $section['hasMenuItem'] : [] as $j => $item) {
                $itemAt = $at.'.hasMenuItem['.$j.']';
                if (! is_array($item) || ($item['@type'] ?? null) !== 'MenuItem') {
                    $this->error($itemAt, '@type must be MenuItem');

                    continue;
                }
                $this->text($item, 'name', $itemAt);
                $offer = $item['offers'] ?? null;
                if ($offer !== null && (! is_array($offer) || ($offer['@type'] ?? null) !== 'Offer'
                    || ! is_string($offer['price'] ?? null) || preg_match('/^\d+(\.\d+)?$/', $offer['price']) !== 1
                    || ! is_string($offer['priceCurrency'] ?? null) || preg_match('/^[A-Z]{3}$/', $offer['priceCurrency']) !== 1)) {
                    $this->error($itemAt.'.offers', 'an Offer needs a numeric price and an ISO 4217 priceCurrency');
                }
            }
        }
    }

    /**
     * schema.org WebPage (and its kinds): a name and its absolute url.
     *
     * @param  array<mixed>  $node
     */
    private function webPage(array $node, string $path): void
    {
        $this->text($node, 'name', $path);
        $this->url($node, 'url', $path);
    }

    /**
     * PostalAddress: a locality and a country place it when there is no street; the street, the postal code and the
     * region are recommended.
     *
     * @param  array<mixed>  $address
     */
    private function postalAddress(array $address, string $path): void
    {
        if (($address['@type'] ?? null) !== 'PostalAddress') {
            $this->error($path, '@type must be PostalAddress');
        }
        foreach (['streetAddress', 'addressLocality', 'postalCode', 'addressRegion', 'addressCountry'] as $field) {
            if (isset($address[$field])) {
                $this->text($address, $field, $path);
            } elseif (in_array($field, ['addressLocality', 'addressCountry'], true) && ! isset($address['streetAddress'])) {
                $this->error($path, $field.' is needed when there is no streetAddress (Google cannot place it otherwise)');
            } else {
                $this->warn($path, $field);
            }
        }
        if (is_string($address['addressCountry'] ?? null) && preg_match('/^[A-Z]{2}$/', $address['addressCountry']) !== 1) {
            $this->error($path, 'addressCountry must be an ISO 3166-1 alpha-2 code');
        }
    }

    private function openingHours(mixed $spec, string $path): void
    {
        if (! is_array($spec) || ($spec['@type'] ?? null) !== 'OpeningHoursSpecification') {
            $this->error($path, '@type must be OpeningHoursSpecification');

            return;
        }
        foreach (['opens', 'closes'] as $field) {
            if (! is_string($spec[$field] ?? null) || preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $spec[$field]) !== 1) {
                $this->error($path, $field.' must be hh:mm[:ss]');
            }
        }
        $special = isset($spec['validFrom']) || isset($spec['validThrough']);
        foreach (['validFrom', 'validThrough'] as $field) {
            if (isset($spec[$field]) && (! is_string($spec[$field]) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $spec[$field]) !== 1)) {
                $this->error($path, $field.' must be a YYYY-MM-DD date');
            }
        }
        if ($special && (! isset($spec['validFrom'], $spec['validThrough']) || $spec['validFrom'] > $spec['validThrough'])) {
            $this->error($path, 'an exception day needs validFrom ≤ validThrough');
        }
        $days = $spec['dayOfWeek'] ?? null;
        if ($days === null) {
            if (! $special) {
                $this->error($path, 'dayOfWeek is required for regular hours');
            }

            return;
        }
        foreach (is_array($days) ? $days : [$days] as $day) {
            $name = is_string($day) ? (string) preg_replace('#^https?://schema\.org/#', '', $day) : '';
            if (! in_array($name, self::DAYS, true)) {
                $this->error($path, 'dayOfWeek '.json_encode($day).' is not a schema.org DayOfWeek');
            }
        }
    }

    private function geo(mixed $geo, string $path): void
    {
        if (! is_array($geo) || ($geo['@type'] ?? null) !== 'GeoCoordinates'
            || ! is_numeric($geo['latitude'] ?? null) || abs((float) $geo['latitude']) > 90
            || ! is_numeric($geo['longitude'] ?? null) || abs((float) $geo['longitude']) > 180) {
            $this->error($path, 'GeoCoordinates need a latitude (±90) and a longitude (±180) as numbers');
        }
    }

    private function telephone(mixed $phone, string $path): void
    {
        if (! is_string($phone) || preg_match('/^\+\d{8,15}$/', $phone) !== 1) {
            $this->error($path, 'telephone must be an international number (+ and digits)');
        }
    }

    /**
     * @param  array<mixed>  $node
     * @param  list<string>  $allowed
     */
    private function enum(array $node, string $field, array $allowed, string $path): void
    {
        if (! isset($node[$field])) {
            $this->warn($path, $field);

            return;
        }
        $value = is_string($node[$field]) ? (string) preg_replace('#^https?://schema\.org/#', '', $node[$field]) : '';
        if (! in_array($value, $allowed, true)) {
            $this->error($path, $field.' '.json_encode($node[$field]).' is not a schema.org value');
        }
    }

    /**
     * ISO 8601: a date, or a date and time with its time-zone offset (Google: a time without a zone is ambiguous).
     *
     * @param  array<mixed>  $node
     * @return int|null the timestamp
     */
    private function dateTime(array $node, string $field, string $path, bool $required): ?int
    {
        $value = $node[$field] ?? null;
        if ($value === null) {
            if ($required) {
                $this->error($path, $field.' is required');
            } else {
                $this->warn($path, $field);
            }

            return null;
        }
        if (! is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}(T\d{2}:\d{2}(:\d{2}(\.\d+)?)?(Z|[+-]\d{2}:\d{2}))?$/', $value) !== 1) {
            $this->error($path, $field.' must be ISO 8601 with a time zone');

            return null;
        }
        $time = strtotime($value);

        return $time === false ? null : $time;
    }

    /** @param  array<mixed>  $node */
    private function text(array $node, string $field, string $path): void
    {
        if (! is_string($node[$field] ?? null) || trim($node[$field]) === '') {
            $this->error($path, $field.' must be a non-empty text');
        }
    }

    /** @param  array<mixed>  $node */
    private function url(array $node, string $field, string $path): void
    {
        if (! isset($node[$field])) {
            $this->error($path, $field.' is required');

            return;
        }
        $this->absolute($node[$field], $path.'.'.$field);
    }

    private function urlList(mixed $value, string $path): void
    {
        foreach (is_array($value) ? $value : [$value] as $url) {
            $this->absolute($url, $path);
        }
    }

    private function absolute(mixed $url, string $path): void
    {
        if (! is_string($url) || filter_var($url, FILTER_VALIDATE_URL) === false || preg_match('#^https?://#', $url) !== 1) {
            $this->error($path, 'must be an absolute http(s) URL, got '.json_encode($url));
        }
    }

    /**
     * A recommended property: checked by $check when present, else a warning the test reviews.
     *
     * @param  array<mixed>  $node
     */
    private function recommended(array $node, string $field, string $path, ?Closure $check = null): void
    {
        if (! isset($node[$field])) {
            $this->warn($path, $field);
        } elseif ($check !== null) {
            $check();
        }
    }

    /** No empty value, and never a placeholder where an approved value belongs (MISSING, PENDING, TODO…). */
    private function noPlaceholders(mixed $value, string $path): void
    {
        if (is_array($value) && $value !== []) {
            foreach ($value as $key => $inner) {
                $this->noPlaceholders($inner, $path.'.'.$key);
            }

            return;
        }
        if ($value === null || $value === '' || $value === []) {
            $this->error($path, 'empty value');
        } elseif (is_string($value) && preg_match('/MISSING|OWNER INPUT|PENDING|TODO|lorem ipsum|placeholder/i', $value) === 1) {
            $this->error($path, 'placeholder instead of an approved value: '.json_encode($value));
        }
    }

    private function error(string $path, string $message): void
    {
        $this->errors[] = $path.': '.$message;
    }

    /** A recommended property is missing — named "Type.path.property" (list positions dropped) so a test can review it. */
    private function warn(string $path, string $property): void
    {
        $this->warnings[] = (string) preg_replace('/\[\d+\]/', '', $path).'.'.$property;
    }
}
