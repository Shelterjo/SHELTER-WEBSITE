<?php

namespace App\Services\Site;

use App\Models\Branch;

/**
 * What a public page may show about one branch: approved values only (FACT-REGISTRY §6). A null field is not
 * rendered at all — no empty box, no "MISSING" text. Hours rows: ['weekday', 'day', 'today', 'intervals' =>
 * [['opens', 'closes', 'overnight', 'opens_at', 'closes_at']]] with display times and raw HH:MM.
 *
 * @phpstan-type Interval array{opens: string, closes: string, overnight: bool, opens_at: string, closes_at: string}
 * @phpstan-type DayRow array{weekday: int, day: string, today: bool, intervals: list<Interval>}
 */
final readonly class BranchSummary
{
    /**
     * @param  list<DayRow>|null  $week  regular weekly hours, week order (null = hours not approved)
     * @param  list<Interval>|null  $today  effective intervals starting today (exceptions applied)
     */
    public function __construct(
        public Branch $branch,
        public string $name,
        public string $locale,
        public ?string $altName,
        public string $altLocale,
        public string $url,
        public string $citySlug,
        public ?array $week,
        public ?array $today,
        public ?StatusTimeline $status,
        public ?ContactAction $phone,
        public ?ContactAction $whatsapp,
        public ?string $address = null,
        public ?string $mapsUrl = null,
        public ?string $latitude = null,
        public ?string $longitude = null,
        /** @var list<string> the services the Owner said yes to (D-033) */
        public array $services = [],
        /** @var list<string> the payment methods the Owner said yes to (D-034) */
        public array $payments = [],
        /** what kind of branch it is, from its master-data type (D-008): «درايف ثرو» / «Drive-thru» … */
        public ?string $kind = null,
        /** the city in the page's language (null while its spelling is not approved) */
        public ?string $city = null,
        /** the approved location description — a nearby landmark (M57), never the street address */
        public ?string $landmark = null,
        /** @var list<array{date: string, intervals: list<array{opens: string, closes: string}>}> exception days ahead */
        public array $special = [],
        /** the kind as the Google title words it (D-338: «كافيه»); falls back to the page's kind */
        public ?string $titleKind = null,
    ) {}

    /**
     * The card's one line: what and where, then the location description — the city only once (M57 §28: never
     * «… في إربد · إربد سيتي سنتر…»). Null when nothing is known.
     */
    public function placeLine(): ?string
    {
        if ($this->landmark === null) {
            return $this->kindInCity();
        }
        $cityInLandmark = $this->city !== null && mb_stripos($this->landmark, $this->city) !== false;
        $lead = $cityInLandmark ? $this->kind : $this->kindInCity();

        return $lead !== null ? $lead.' · '.$this->landmark : $this->landmark;
    }

    /** «درايف ثرو في إربد» / “Drive-thru in Irbid”, or whichever half is known; null when neither is. */
    public function kindInCity(): ?string
    {
        if ($this->kind !== null && $this->city !== null) {
            return (string) __('site.branch.kind_in_city', ['kind' => $this->kind, 'city' => $this->city], $this->locale);
        }

        return $this->kind ?? $this->city;
    }
}
