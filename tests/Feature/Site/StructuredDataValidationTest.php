<?php

namespace Tests\Feature\Site;

use App\Enums\FactSource;
use App\Enums\FactStatus;
use App\Enums\PublishStatus;
use App\Models\Award;
use App\Models\Branch;
use App\Models\Experience;
use App\Models\Market;
use App\Models\Media;
use App\Models\Page;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\Dashboard\BranchEditor;
use App\Services\MasterData\FactRegistry;
use App\Services\Media\MediaLibrary;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Media\MediaLibraryTest;
use Tests\Support\RichResults;
use Tests\TestCase;

/**
 * SCHEMA-009 — every page type that emits JSON-LD is rendered and each block is validated locally (Tests\Support\
 * RichResults) against its schema.org / Google rich-result rules: 0 errors on published content, and every warning
 * (a recommended property left out) is one reviewed below with its reason — none is filled with an invented value.
 * All texts and records created here are test data, not business facts.
 */
class StructuredDataValidationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The warnings reviewed (GEP §41: "warnings are evaluated"): recommended properties the site leaves out because
     * no approved value exists — they are never invented. A new warning fails the test until it is reviewed here.
     */
    private const REVIEWED = [
        'Organization.sameAs' => 'Recommended only. The approved social links could be listed here — a later improvement.',
        'CafeOrCoffeeShop.address.streetAddress' => 'MISSING — OWNER INPUT REQUIRED (PO-010): shown once the Owner publishes it.',
        'CafeOrCoffeeShop.address.postalCode' => 'No approved postal code in the Master Data.',
        'CafeOrCoffeeShop.address.addressRegion' => 'No approved region in the Master Data.',
        'CafeOrCoffeeShop.geo' => 'MISSING — OWNER INPUT REQUIRED (PO-010): coordinates are added once the Owner publishes them.',
        'CafeOrCoffeeShop.priceRange' => 'No approved price range (a public claim — the Owner\'s to state).',
        'CafeOrCoffeeShop.servesCuisine' => 'No approved cuisine wording.',
        'Event.location.address.streetAddress' => 'Same as the branch: PO-010.',
        'Event.location.address.postalCode' => 'No approved postal code in the Master Data.',
        'Event.location.address.addressRegion' => 'No approved region in the Master Data.',
        'Event.offers' => 'No approved price or ticket data for events.',
        'Event.performer' => 'No approved performer data for events.',
        'Event.image' => 'Only an event with an approved image carries one (MEDIA-RIGHTS).',
    ];

    private Branch $drive;

    private Branch $house;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        Storage::fake('media');
        Storage::fake('media_public');
        $this->travelTo(CarbonImmutable::parse('2026-10-10 12:00:00', 'Asia/Amman'));
        $this->drive = Branch::query()->where('slug', 'drive')->firstOrFail();
        $this->house = Branch::query()->where('slug', 'house')->firstOrFail();
    }

    private function html(string $url): string
    {
        $this->app->forgetScopedInstances();

        return (string) $this->get($url)->assertOk()->getContent();
    }

    /** @return list<array<mixed>> */
    private function jsonLd(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

        return array_map(fn (string $json): array => (array) json_decode($json, true, flags: JSON_THROW_ON_ERROR), $m[1]);
    }

    /** @param  array<string, mixed>  $attributes */
    private function event(string $slug, array $attributes = []): Experience
    {
        return Experience::query()->create($attributes + [
            'type' => 'event', 'market_id' => Market::query()->value('id'), 'slug' => $slug,
            'title_ar' => 'فعالية تجريبية '.$slug, 'title_en' => 'Test event '.$slug, 'body_ar' => 'وصف تجريبي.', 'body_en' => 'A test description.',
            'status' => 'scheduled',
            'starts_at' => CarbonImmutable::parse('2026-10-12 16:00', 'Asia/Amman')->utc(),
            'ends_at' => CarbonImmutable::parse('2026-10-12 21:00', 'Asia/Amman')->utc(),
        ]);
    }

    /** The content that makes the pages exist (test data only). */
    private function publishTestContent(): void
    {
        foreach (['about' => 'brand', 'faq' => 'faq', 'privacy' => 'legal', 'terms' => 'legal', 'media' => 'brand'] as $key => $type) {
            $page = Page::query()->updateOrCreate(['key' => $key], ['type' => $type, 'title_ar' => 'صفحة تجريبية '.$key, 'title_en' => 'Test page '.$key,
                'status' => PublishStatus::Published, 'published_at' => now()->subDay()]);
            $page->sections()->create(['type' => $type === 'faq' ? 'faq' : 'text', 'heading_ar' => 'سؤال تجريبي؟', 'heading_en' => 'A test question?',
                'body_ar' => 'جواب تجريبي.', 'body_en' => 'A test answer.']);
        }
        $award = Award::query()->create(['title_ar' => 'جائزة تجريبية', 'title_en' => 'Test award', 'issuer_ar' => 'جهة', 'issuer_en' => 'Issuer', 'year' => 2025,
            'evidence_url' => 'https://awards.test/1', 'status' => 'published']);
        app(FactRegistry::class)->register($award->factKey(), 'awards', $award->factValue(), FactStatus::Approved, FactSource::OwnerDecision, decisionRef: 'D-TEST');
        TeamMember::query()->create(['display_name_ar' => 'اسم تجريبي', 'display_name_en' => 'Test name', 'job_title_ar' => 'باريستا', 'job_title_en' => 'Barista',
            'branch_id' => $this->drive->id, 'is_published' => true, 'publish_consent_at' => now(), 'publish_consent_version' => 'family-consent-v1']);

        $library = app(MediaLibrary::class);
        $image = $library->import(MediaLibraryTest::imageFile(seed: 9), ['source' => 'shelter', 'people_consent' => 'none', 'alt_ar' => 'صورة تجريبية', 'alt_en' => 'Test photo']);
        $image->forceFill(['approval_status' => Media::APPROVED, 'approved_at' => now(), 'ok_website' => true])->save();
        $library->generateVariants($image->refresh());

        $this->event('at-drive', ['branch_ids' => [$this->drive->id], 'media_id' => $image->id]);
        $this->event('at-both', ['branch_ids' => [$this->drive->id, $this->house->id]]);
        $this->event('at-venue', ['details' => ['venue_ar' => 'قاعة تجريبية', 'venue_en' => 'A test hall']]);
        $this->event('nowhere');
    }

    /**
     * Every page type that emits JSON-LD, in both languages, with the types each must carry.
     *
     * @return array<string, list<string>>
     */
    private function pages(): array
    {
        $pages = ['/' => ['Organization', 'WebSite']];
        foreach (['ar', 'en'] as $l) {
            $pages += [
                "/{$l}/" => ['Organization', 'WebSite'],
                "/{$l}/contact/" => ['BreadcrumbList', 'ContactPage'],
                "/{$l}/about/" => ['BreadcrumbList', 'WebPage'],
                "/{$l}/faq/" => ['BreadcrumbList', 'FAQPage'],
                "/{$l}/privacy/" => ['BreadcrumbList', 'WebPage'],
                "/{$l}/terms/" => ['BreadcrumbList', 'WebPage'],
                "/{$l}/careers/" => ['BreadcrumbList'],
                "/{$l}/franchise/" => ['BreadcrumbList', 'WebPage', 'FAQPage'],
                "/{$l}/media/" => ['BreadcrumbList', 'WebPage'],
                "/{$l}/awards/" => ['BreadcrumbList', 'WebPage'],
                "/{$l}/family/" => ['BreadcrumbList', 'WebPage'],
                "/{$l}/jo/menu/" => ['BreadcrumbList', 'Menu'],
                "/{$l}/jo/locations/" => ['BreadcrumbList'],
                "/{$l}/jo/locations/irbid/drive/" => ['BreadcrumbList', 'CafeOrCoffeeShop'],
                "/{$l}/jo/locations/irbid/house/" => ['BreadcrumbList', 'CafeOrCoffeeShop'],
                "/{$l}/jo/events/" => ['BreadcrumbList'],
                "/{$l}/jo/events/at-drive/" => ['BreadcrumbList', 'Event'],
                "/{$l}/jo/events/at-both/" => ['BreadcrumbList', 'Event'],
                "/{$l}/jo/events/at-venue/" => ['BreadcrumbList'],
                "/{$l}/jo/events/nowhere/" => ['BreadcrumbList'],
            ];
        }

        return $pages;
    }

    /**
     * Renders every page, validates every block; returns the warnings seen.
     *
     * @return array<string, array<string, mixed>> the blocks by "url type"
     */
    private function validateAll(): array
    {
        $blocks = [];
        $warnings = [];
        foreach ($this->pages() as $url => $types) {
            $found = $this->jsonLd($this->html($url));
            $this->assertSame($types, array_values(array_map(fn (array $b): string => (string) ($b['@type'] ?? '?'), $found)), $url.': the blocks it emits');
            foreach ($found as $block) {
                $result = RichResults::check($block);
                $this->assertSame([], $result['errors'], $url.' '.json_encode($block['@type'] ?? null).": errors\n".implode("\n", $result['errors']));
                foreach ($result['warnings'] as $warning) {
                    $warnings[$warning] = $url;
                }
                $blocks[$url.' '.$block['@type']] = $block;
            }
        }
        $unreviewed = array_diff_key($warnings, self::REVIEWED);
        $this->assertSame([], $unreviewed, 'warnings nobody reviewed (add them to REVIEWED with the reason, or fix them)');

        return $blocks;
    }

    public function test_every_json_ld_block_on_every_page_type_is_valid_and_its_warnings_are_reviewed(): void
    {
        $this->publishTestContent();
        $blocks = $this->validateAll();

        // The Event place is the branch from the Master Data: its approved name and address — city and country always.
        $event = $blocks['/en/jo/events/at-drive/ Event'];
        $this->assertSame(['@type' => 'Place', 'name' => 'SHELTER COFFEE DRIVE', 'url' => 'http://localhost/en/jo/locations/irbid/drive/',
            'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Irbid', 'addressCountry' => 'JO']], $event['location']);
        $this->assertStringStartsWith('http://localhost/storage/', (string) $event['image'], 'the approved image, absolute');
        $this->assertSame('http://localhost/#organization', $event['organizer']['@id']);
        $both = $blocks['/ar/jo/events/at-both/ Event'];
        $this->assertSame(['شلتر كوفي درايف', 'شلتر كوفي هاوس'], array_column($both['location'], 'name'), 'one Place per branch, approved Arabic names');
        $this->assertArrayNotHasKey('/en/jo/events/at-venue/ Event', $blocks, 'a venue without an address in the Master Data: no Event data');
        $this->assertArrayNotHasKey('/en/jo/events/nowhere/ Event', $blocks);
        $this->assertSame([], array_filter(array_keys($blocks), fn (string $key): bool => str_ends_with($key, ' JobPosting')), 'no job data until the Owner publishes jobs');
    }

    public function test_a_published_street_address_and_coordinates_reach_the_branch_and_its_events_and_stay_valid(): void
    {
        $this->publishTestContent();
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $editor = app(BranchEditor::class);
        $details = $editor->parse($this->drive, ['name_ar' => (string) $this->drive->name_ar, 'name_en' => (string) $this->drive->name_en, 'is_public' => '1',
            'address_ar' => 'شارع تجريبي', 'address_en' => 'Test street', 'maps_url' => (string) $this->drive->maps_url, 'landmark_ar' => (string) $this->drive->landmark_ar,
            'latitude' => '32.5', 'longitude' => '35.9']);
        $this->assertSame([], $editor->publish($this->drive, $details, BranchEditor::fingerprint($this->drive, $details), $owner));

        $blocks = $this->validateAll();
        $shop = $blocks['/en/jo/locations/irbid/drive/ CafeOrCoffeeShop'];
        $this->assertSame('Test street', $shop['address']['streetAddress']);
        $this->assertSame(['@type' => 'GeoCoordinates', 'latitude' => 32.5, 'longitude' => 35.9], $shop['geo']);
        $this->assertSame('شارع تجريبي', $blocks['/ar/jo/events/at-drive/ Event']['location']['address']['streetAddress'], 'the event uses the same approved address');
        $this->assertArrayNotHasKey('streetAddress', $blocks['/ar/jo/locations/irbid/house/ CafeOrCoffeeShop']['address'], 'HOUSE has none approved yet');
    }

    public function test_an_event_without_an_address_in_the_master_data_carries_no_event_data_and_says_why(): void
    {
        $this->publishTestContent();
        $logged = [];
        Event::listen(MessageLogged::class, function (MessageLogged $entry) use (&$logged): void {
            if (str_starts_with($entry->message, 'schema.')) {
                $logged[] = [$entry->level, $entry->message, $entry->context];
            }
        });
        $venue = $this->html('/en/jo/events/at-venue/');
        $this->html('/ar/jo/events/nowhere/');
        $this->html('/en/jo/events/at-drive/');
        $this->assertSame([
            ['info', 'schema.event_omitted', ['event' => 'at-venue', 'reason' => 'venue_without_address']],
            ['info', 'schema.event_omitted', ['event' => 'nowhere', 'reason' => 'no_location']],
        ], $logged, 'one line per page without Event data, none for a valid one — no personal data');
        // The visitor still sees the venue as typed by the Owner.
        $this->assertStringContainsString('A test hall', $venue);
    }

    public function test_the_validator_catches_what_google_rejects(): void
    {
        $context = ['@context' => 'https://schema.org'];
        $cases = [
            'event without a location' => $context + ['@type' => 'Event', 'name' => 'x', 'startDate' => '2026-10-12T16:00:00+03:00'],
            'event place without an address' => $context + ['@type' => 'Event', 'name' => 'x', 'startDate' => '2026-10-12T16:00:00+03:00', 'location' => ['@type' => 'Place', 'name' => 'Hall']],
            'event time without a zone' => $context + ['@type' => 'Event', 'name' => 'x', 'startDate' => '2026-10-12T16:00:00', 'location' => ['@type' => 'Place', 'address' => 'Irbid']],
            'event ending before it starts' => $context + ['@type' => 'Event', 'name' => 'x', 'startDate' => '2026-10-12T16:00:00+03:00', 'endDate' => '2026-10-11T16:00:00+03:00', 'location' => ['@type' => 'Place', 'address' => 'Irbid']],
            'business without an address' => $context + ['@type' => 'CafeOrCoffeeShop', 'name' => 'x'],
            'business with a bad day' => $context + ['@type' => 'CafeOrCoffeeShop', 'name' => 'x', 'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Irbid', 'addressCountry' => 'JO'],
                'openingHoursSpecification' => [['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => ['Funday'], 'opens' => '09:00', 'closes' => '22:00']]],
            'breadcrumb out of order' => $context + ['@type' => 'BreadcrumbList', 'itemListElement' => [['@type' => 'ListItem', 'position' => 2, 'name' => 'x', 'item' => 'https://a.test/']]],
            'breadcrumb with a relative link' => $context + ['@type' => 'BreadcrumbList', 'itemListElement' => [['@type' => 'ListItem', 'position' => 1, 'name' => 'x', 'item' => '/ar/'], ['@type' => 'ListItem', 'position' => 2, 'name' => 'y']]],
            'faq without an answer' => $context + ['@type' => 'FAQPage', 'mainEntity' => [['@type' => 'Question', 'name' => 'x']]],
            'a placeholder instead of data' => $context + ['@type' => 'WebPage', 'name' => 'MISSING — OWNER INPUT REQUIRED', 'url' => 'https://a.test/'],
            'a type with no rules' => $context + ['@type' => 'Product', 'name' => 'x'],
            'job without a place' => $context + ['@type' => 'JobPosting', 'title' => 'x', 'description' => 'y', 'datePosted' => '2026-10-01', 'hiringOrganization' => ['name' => 'z']],
            'no context' => ['@type' => 'WebPage', 'name' => 'x', 'url' => 'https://a.test/'],
        ];
        foreach ($cases as $case => $block) {
            $this->assertNotSame([], RichResults::check($block)['errors'], $case);
        }
        $valid = $context + ['@type' => 'Event', 'name' => 'x', 'startDate' => '2026-10-12T16:00:00+03:00', 'endDate' => '2026-10-12T21:00:00+03:00',
            'location' => ['@type' => 'Place', 'name' => 'Hall', 'address' => ['@type' => 'PostalAddress', 'addressLocality' => 'Irbid', 'addressCountry' => 'JO']]];
        $this->assertSame([], RichResults::check($valid)['errors'], 'a place known by its city and country is valid');
    }
}
