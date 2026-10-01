<?php

namespace Tests\Feature\Site;

use App\Enums\ContactKind;
use App\Models\ContactPoint;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** PHASE 2: contact by intent (SI-B04 — D-057, D-059, D-065, D-071, CT-06). */
class ContactPageTest extends TestCase
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

    public function test_arabic_page_shows_one_card_per_intent_with_its_approved_number(): void
    {
        $html = (string) $this->get('/ar/contact/')->assertOk()->getContent();

        $this->assertStringContainsString('<html lang="ar" dir="rtl">', $html);
        $this->assertStringContainsString('<link rel="canonical" href="http://localhost/ar/contact/">', $html);
        $this->assertStringContainsString('hreflang="en" href="http://localhost/en/contact/"', $html);
        $this->assertSame(1, substr_count($html, '<h1'));

        foreach (['التواصل العام والفروع', 'الشكاوى والاقتراحات', 'الكيترنج والأعمال والفعاليات', 'استفسارات الفرنشايز'] as $intent) {
            $this->assertStringContainsString($intent, $html);
        }
        // Real-text numbers in the Arabic display form (D-065), links always international.
        $this->assertStringContainsString('href="tel:+962799009436"', $html);
        $this->assertStringContainsString('<span dir="ltr">0799009436</span>', $html);
        $this->assertStringContainsString('href="https://wa.me/962799009436"', $html);
        $this->assertSame(2, substr_count($html, 'href="tel:+962799338445"'), 'complaints & feedback + franchise inquiries (D-057, D-071)');
        $this->assertStringContainsString('<span dir="ltr">0799338445</span>', $html);
        $this->assertStringContainsString('href="tel:+962799530383"', $html);
        $this->assertStringContainsString('<span dir="ltr">0799530383</span>', $html);

        // The General card carries each branch's page and live state; no directions until PO-010.
        $this->assertStringContainsString('href="http://localhost/ar/jo/locations/irbid/drive/"', $html);
        $this->assertStringContainsString('href="http://localhost/ar/jo/locations/irbid/house/"', $html);
        $this->assertStringNotContainsString('google.com/maps', $html);
    }

    public function test_english_page_uses_the_international_display_form(): void
    {
        $html = (string) $this->get('/en/contact/')->assertOk()->getContent();

        $this->assertStringContainsString('<html lang="en" dir="ltr">', $html);
        $this->assertStringContainsString('+962 79 900 9436', $html);
        $this->assertStringContainsString('+962 79 933 8445', $html);
        $this->assertStringContainsString('+962 79 953 0383', $html);
        $this->assertStringContainsString('Catering, B2B &amp; Events', $html);
    }

    public function test_email_stays_hidden_until_approved_and_a_withdrawn_number_hides_its_card(): void
    {
        $html = (string) $this->get('/ar/contact/')->assertOk()->getContent();
        $this->assertStringNotContainsString('mailto:', $html);
        $this->assertStringNotContainsString('info@shelterjo.com', $html, 'D-035: email not approved for publication');

        ContactPoint::query()->where('kind', ContactKind::CateringB2bEvents->value)->update(['is_public' => false]);
        $html = (string) $this->get('/ar/contact/')->assertOk()->getContent();
        $this->assertStringNotContainsString('tel:+962799530383', $html);
        $this->assertStringNotContainsString('الكيترنج والأعمال والفعاليات', $html);
    }

    public function test_structured_data_carries_only_the_page_and_breadcrumbs(): void
    {
        $html = (string) $this->get('/ar/contact/')->assertOk()->getContent();
        $data = $this->jsonLd($html);

        $this->assertNotNull(collect($data)->firstWhere('@type', 'ContactPage'));
        $this->assertNotNull(collect($data)->firstWhere('@type', 'BreadcrumbList'));
        $encoded = json_encode($data, JSON_THROW_ON_ERROR);
        $this->assertStringNotContainsString('799338445', $encoded, 'CT-06: intent numbers stay out of structured data');
        $this->assertStringNotContainsString('799530383', $encoded);
    }

    public function test_footer_links_to_the_page_and_the_header_keeps_menu_and_locations_only(): void
    {
        $html = (string) $this->get('/ar/')->assertOk()->getContent();

        $this->assertStringContainsString('href="http://localhost/ar/contact/"', $html);
        $this->assertSame(1, preg_match('#<header class="ui-site-header.*?</header>#s', $html, $header));
        $this->assertStringNotContainsString('/ar/contact/', $header[0] ?? '', 'D-027: the header is for Menu and Locations');
    }
}
