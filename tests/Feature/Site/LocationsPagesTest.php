<?php

namespace Tests\Feature\Site;

use App\Models\Market;
use Carbon\CarbonImmutable;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** PHASE 2: locations (SI-M03), branch pages (SI-M05/M06), home branch discovery and localized 404 (SI-S01). */
class LocationsPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
    }

    /** @return array<int, array<string, mixed>> */
    private function jsonLd(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

        return array_map(fn (string $json): array => json_decode($json, true, flags: JSON_THROW_ON_ERROR), $matches[1]);
    }

    public function test_locations_page_lists_both_branches_with_canonical_and_hreflang(): void
    {
        $html = (string) $this->get('/ar/jo/locations/')->assertOk()->getContent();

        $this->assertStringContainsString('<html lang="ar" dir="rtl">', $html);
        $this->assertStringContainsString('شلتر كوفي درايف', $html);
        $this->assertStringContainsString('شلتر كوفي هاوس', $html);
        $this->assertStringContainsString('<link rel="canonical" href="http://localhost/ar/jo/locations/">', $html);
        $this->assertStringContainsString('hreflang="en" href="http://localhost/en/jo/locations/"', $html);
        $this->assertStringContainsString('/ar/jo/locations/irbid/drive/', $html);
        $this->assertStringContainsString('/ar/jo/locations/irbid/house/', $html);
    }

    public function test_branch_page_shows_the_week_and_only_approved_structured_data(): void
    {
        $html = (string) $this->get('/ar/jo/locations/irbid/drive/')->assertOk()->getContent();

        $this->assertSame(7, substr_count($html, 'class="ui-hours__row'));
        $this->assertStringContainsString('href="tel:+962799009436"', $html);

        $shop = collect($this->jsonLd($html))->firstWhere('@type', 'CafeOrCoffeeShop');
        $this->assertNotNull($shop);
        $this->assertSame('+962799009436', $shop['telephone']);
        // M57 §18: the city and country are fixed facts (D-008); the street address stays out until PO-010 is approved.
        $this->assertSame(['@type' => 'PostalAddress', 'addressLocality' => 'Irbid', 'addressCountry' => 'JO'], $shop['address']);
        $this->assertArrayNotHasKey('geo', $shop);
        $this->assertSame('https://maps.app.goo.gl/zNfDbkxcT1aMdQiWA', $shop['hasMap'], 'the approved Maps link (D-336)');
        $this->assertCount(2, $shop['openingHoursSpecification']);
        $this->assertSame('http://localhost/#organization', $shop['parentOrganization']['@id']);
        $this->assertSame('http://localhost/ar/jo/menu/', $shop['hasMenu']);
        $this->assertNotNull(collect($this->jsonLd($html))->firstWhere('@type', 'BreadcrumbList'));
    }

    public function test_english_branch_page_is_ltr(): void
    {
        $html = (string) $this->get('/en/jo/locations/irbid/house/')->assertOk()->getContent();

        $this->assertStringContainsString('<html lang="en" dir="ltr">', $html);
        $this->assertStringContainsString('SHELTER COFFEE HOUSE', $html);
    }

    public function test_unknown_branch_city_or_market_is_a_404(): void
    {
        $this->get('/ar/jo/locations/irbid/nope/')->assertNotFound();
        $this->get('/ar/jo/locations/amman/drive/')->assertNotFound();
        $this->get('/ar/xx/locations/')->assertNotFound();

        Market::query()->where('code', 'jo')->update(['is_active' => false]);
        $this->get('/ar/jo/locations/')->assertNotFound();
    }

    public function test_open_state_follows_amman_time_across_midnight(): void
    {
        // Friday 01:40 in Amman: DRIVE is still inside Thursday's 07:00–02:00 interval; HOUSE is closed.
        $this->travelTo(CarbonImmutable::parse('2026-10-02 01:40', 'Asia/Amman'));
        $html = (string) $this->get('/en/jo/locations/irbid/drive/')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/data-state="(open|closing)"/', $html);

        // 02:30: past the overnight close.
        $this->travelTo(CarbonImmutable::parse('2026-10-02 02:30', 'Asia/Amman'));
        $html = (string) $this->get('/en/jo/locations/irbid/drive/')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/data-state="closed"/', $html);
        $this->assertDoesNotMatchRegularExpression('/data-state="(open|closing)"/', $html);
    }

    public function test_home_lists_branches_with_live_status_and_links(): void
    {
        $html = (string) $this->get('/ar/')->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, 'data-ui-open-status='));
        $this->assertStringContainsString('/ar/jo/locations/irbid/drive/', $html);
        $this->assertStringContainsString('class="ui-hero"', $html);
    }

    public function test_404_takes_its_language_from_the_url_and_is_bilingual_elsewhere(): void
    {
        $en = (string) $this->get('/en/does-not-exist/')->assertNotFound()->getContent();
        $this->assertStringContainsString('<html lang="en" dir="ltr">', $en);
        $this->assertStringContainsString('<meta name="robots" content="noindex', $en);

        $root = (string) $this->get('/does-not-exist/')->assertNotFound()->getContent();
        $this->assertStringContainsString('lang="ar"', $root);
        $this->assertStringContainsString('lang="en"', $root);
    }

    public function test_sitemap_is_absent_where_the_site_is_not_indexable(): void
    {
        $this->get('/sitemap.xml')->assertNotFound();
    }
}
