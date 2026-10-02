<?php

namespace Tests\Feature\Site;

use App\Enums\PublishStatus;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\Recruitment\Application;
use App\Models\Recruitment\ConsentVersion;
use App\Models\Recruitment\PartnershipApplication;
use App\Services\Content\Search\SearchIndexer;
use Database\Seeders\FranchiseSeeder;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * SI-B12 franchise & partnerships with the Owner's content V1 (M47): the page, its two required form texts (PF-02
 * acknowledgement, PF-03 consent), the interest types (PF-06), the success wording and the commercial-safety guard.
 */
class FranchisePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    private function seedFranchise(): void
    {
        $this->seed(FranchiseSeeder::class);
        $this->app->forgetScopedInstances();
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function validInput(array $overrides = []): array
    {
        return $overrides + [
            'full_name' => 'Test Partner', 'phone' => '0791234567', 'email' => 'Partner.Test@Example.com', 'country' => 'jo',
            'city' => 'Irbid', 'market' => 'North', 'partnership_interest_type' => 'single_location', 'experience_band' => 'y3_5',
            'experience_text' => '', 'owns_business' => 'yes', 'location_status' => 'searching', 'introduction' => "Hello.\nA short note.",
            'non_binding_acknowledgement' => '1', 'data_processing_consent' => '1',
            'attr_utm_source' => 'instagram', 'attr_utm_medium' => 'social', 'attr_utm_campaign' => 'launch',
            'attr_landing_path' => '/en/franchise/', 'attr_referrer_domain' => 'l.instagram.com',
            'form_token' => Crypt::encryptString((string) (now()->getTimestamp() - 30)), 'idempotency_key' => (string) Str::uuid(),
        ];
    }

    /** @return TestResponse<Response> */
    private function fresh(string $url): TestResponse
    {
        $this->app->forgetScopedInstances();

        return $this->get($url);
    }

    /** @return array<int, array<string, mixed>> */
    private function jsonLd(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

        return array_map(fn (string $json): array => json_decode($json, true, flags: JSON_THROW_ON_ERROR), $matches[1]);
    }

    public function test_without_published_content_the_page_does_not_exist_and_nothing_links_to_it(): void
    {
        $this->get('/ar/franchise/')->assertNotFound();
        $this->post('/en/franchise/', $this->validInput())->assertNotFound();

        $contact = (string) $this->fresh('/ar/contact/')->assertOk()->getContent();
        $this->assertStringNotContainsString('/ar/franchise/', $contact, 'the inquiries card shows its number only');
        $this->assertStringContainsString('href="tel:+962799338445"', $contact);
        $home = (string) $this->fresh('/en/')->getContent();
        $this->assertStringNotContainsString('/en/franchise/', $home);
        $this->assertStringContainsString('href="http://localhost/en/careers/"', $home);
    }

    public function test_the_owner_content_v1_renders_in_arabic(): void
    {
        $this->seedFranchise();
        $html = (string) $this->get('/ar/franchise/')->assertOk()->getContent();

        $this->assertStringContainsString('<title>كن شريكًا مع SHELTER COFFEE</title>', $html);
        $this->assertMatchesRegularExpression('#<h1 class="ui-franchise__title">\s*<span class="ui-franchise__title-line">كن شريكًا في نمو</span>\s*<span class="ui-franchise__title-line">SHELTER COFFEE</span>\s*</h1>#u', $html);
        $this->assertStringContainsString('انطلقت SHELTER COFFEE من إربد، الأردن عام 2019.', $html);
        $this->assertStringContainsString('ابدأ طلب الشراكة', $html);
        $this->assertMatchesRegularExpression('#href="\#s-1"[^>]*>\s*تعرّف على SHELTER#u', $html);
        foreach (['من هي SHELTER؟', 'نماذج تجربة SHELTER الحالية', 'لماذا تصبح شريكًا مع SHELTER؟', 'أكثر من مجرد اسم على الواجهة', 'ما الذي نبحث عنه في الشريك؟',
            'رحلة الشراكة', 'أسواق النمو', 'مهتم ببناء SHELTER في سوقك؟'] as $heading) {
            $this->assertStringContainsString($heading, $html);
        }
        $this->assertSame(2, substr_count($html, 'class="ui-franchise__card"'), 'DRIVE and HOUSE as current experiences');
        $this->assertStringContainsString('SHELTER COFFEE DRIVE</h3>', $html);
        $this->assertSame(9, substr_count($html, 'class="ui-steps__item"'));
        $this->assertSame(7, substr_count($html, 'ui-prose__faq'));
        $this->assertStringContainsString('تقديم الطلب لا يعني أن السوق أو المنطقة المطلوبة متاحة أو محجوزة.', $html);

        // The seven public criteria continue "Who we look for"; the nine pillars stay hidden (PENDING FRANCHISE MASTER APPROVAL).
        $this->assertMatchesRegularExpression('#class="ui-franchise__section ui-franchise__section--continued"[^>]*>\s*<ul class="ui-franchise__list"#', $html);
        $this->assertSame(7, substr_count($html, 'class="ui-franchise__list-item"'));
        $this->assertStringContainsString('الجدية في الاستثمار والتشغيل', $html);
        foreach (['التوريد والمشتريات', 'الأنظمة والتقنية', 'نظام القهوة والمنيو'] as $pillar) {
            $this->assertStringNotContainsString($pillar, $html);
        }

        $ld = collect($this->jsonLd($html));
        $this->assertSame('كن شريكًا مع SHELTER COFFEE', $ld->firstWhere('@type', 'WebPage')['name'] ?? null);
        $this->assertCount(7, $ld->firstWhere('@type', 'FAQPage')['mainEntity'] ?? []);
        foreach (['Offer', 'Product', 'AggregateRating', 'Review', 'PriceSpecification'] as $forbidden) {
            $this->assertNull($ld->firstWhere('@type', $forbidden));
        }

        // Linked once published: the footer under the page name, the contact card, search.
        $this->assertMatchesRegularExpression('#href="http://localhost/ar/franchise/"\s*>كن شريكًا مع SHELTER COFFEE</a>#u', (string) $this->fresh('/ar/')->getContent());
        $this->assertStringContainsString('href="http://localhost/ar/franchise/"', (string) $this->fresh('/ar/contact/')->getContent());
    }

    public function test_the_owner_content_v1_renders_in_english_and_is_searchable(): void
    {
        $this->seedFranchise();
        $html = (string) $this->get('/en/franchise/')->assertOk()->getContent();

        $this->assertStringContainsString('<span class="ui-franchise__title-line">Grow With</span>', $html);
        $this->assertStringContainsString('Start Your Partnership Application', $html);
        $this->assertStringContainsString('Discover SHELTER', $html);
        $this->assertStringContainsString('Start Your Application', $html);
        $this->assertStringContainsString('Interested in Bringing SHELTER to Your Market?', $html);
        $this->assertStringContainsString('No profit or specific return is guaranteed.', $html);
        foreach (['Why Partner With SHELTER?', 'We see partnership as more than simply using a brand name.', 'Who We Look For',
            'Willingness to operate within the approved operating system', 'Interest in building a long-term relationship'] as $text) {
            $this->assertStringContainsString(e($text), $html);
        }
        foreach (['Supply &amp; Procurement', 'Technology &amp; Systems'] as $pillar) {
            $this->assertStringNotContainsString($pillar, $html, 'pillars stay hidden');
        }

        app(SearchIndexer::class)->rebuild();
        $search = (string) $this->fresh('/en/search/?q=cost')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#href="/en/franchise/\#q-\d+"#', $search);
        $this->assertStringContainsString('How much does a SHELTER franchise cost?', $search);
    }

    public function test_a_blocked_commercial_claim_keeps_the_page_offline(): void
    {
        $this->seedFranchise();
        PageSection::query()->where('type', 'cta')->update(['body_ar' => 'فرصة بأرباح مضمونة.']);

        $this->fresh('/ar/franchise/')->assertNotFound();
        $this->fresh('/en/franchise/')->assertNotFound();
    }

    public function test_without_both_approved_texts_the_page_offers_the_inquiries_contact(): void
    {
        $this->seedFranchise();
        ConsentVersion::query()->where('scope', 'partnership_ack')->update(['is_active' => false]);

        $html = (string) $this->fresh('/en/franchise/')->assertOk()->getContent();
        $this->assertStringNotContainsString('data-franchise-form', $html);
        $this->assertStringContainsString('href="#franchise-contact"', $html);
        $this->assertStringContainsString('href="tel:+962799338445"', $html);
        $this->post('/en/franchise/', $this->validInput())->assertNotFound();
    }

    public function test_the_form_shows_the_six_interest_types_and_two_unchecked_boxes(): void
    {
        $this->seedFranchise();
        $html = (string) $this->get('/ar/franchise/')->assertOk()->getContent();

        $this->assertStringContainsString('نوع اهتمامك بالشراكة', $html);
        foreach (['single_location', 'multi_location', 'market_development', 'proposed_location', 'general_interest', 'other'] as $value) {
            $this->assertStringContainsString('id="partnership_interest_type-'.$value.'"', $html);
        }
        $this->assertStringContainsString('لدي موقع مقترح وأرغب بدراسة ملاءمته', $html);
        $this->assertStringContainsString('وضح نوع اهتمامك بالشراكة', $html);
        $this->assertStringContainsString(e(FranchiseSeeder::ACKNOWLEDGEMENT_AR), $html);
        $this->assertStringContainsString('لا تعتبر هذه الموافقة اشتراكًا في الرسائل التسويقية.', $html);
        preg_match_all('#<input[^>]*name="(non_binding_acknowledgement|data_processing_consent)"[^>]*>#', $html, $boxes);
        $this->assertCount(2, $boxes[0]);
        foreach ($boxes[0] as $box) {
            $this->assertStringNotContainsString('checked', $box, 'never pre-checked');
        }
        $this->assertStringNotContainsString('investment', strtolower(strip_tags((string) preg_replace('#<main.*?<form#s', '<form', $html))), 'no investment question in the form');
    }

    public function test_a_partnership_application_is_received_with_both_accepted_texts(): void
    {
        $this->seedFranchise();

        $this->post('/en/franchise/', $this->validInput(['partnership_interest_other' => 'stale text from an earlier choice']))
            ->assertRedirect('http://localhost/en/franchise/submitted/');
        $application = Application::query()->sole();
        $partner = PartnershipApplication::query()->sole();
        $this->assertSame('FR', $application->type);
        $this->assertMatchesRegularExpression('/^FR-\d{4}-00101$/', $application->reference_number, 'D-316');
        $this->assertSame('single_location', $partner->partnership_interest_type);
        $this->assertNull($partner->partnership_interest_other, 'a hidden stale description is never stored');
        $this->assertSame('+962791234567', $partner->phone_normalized);
        $this->assertSame('JO', $partner->country_code);
        $this->assertSame('instagram', $partner->utm_source);
        $this->assertSame('l.instagram.com', $partner->referrer_domain);

        $accepted = DB::table('application_consents')->join('consent_versions', 'consent_versions.id', '=', 'application_consents.consent_version_id')
            ->where('application_id', $application->id)->orderBy('consent_versions.scope')->get(['scope', 'version', 'accepted', 'accepted_at']);
        $this->assertSame(['partnership_ack', 'partnerships'], $accepted->pluck('scope')->all());
        $this->assertSame(['partnership-ack-v1', 'partnership-consent-v1'], $accepted->pluck('version')->all());
        $this->assertNotContains(null, $accepted->pluck('accepted_at')->all(), 'each text keeps the time it was accepted');
        $this->assertNotContains('ip', array_keys((array) DB::table('partnership_applications')->first()), 'attribution never keeps an IP');

        $done = (string) $this->get('/en/franchise/submitted/')->assertOk()->getContent();
        foreach (['Thank You for Your Interest in Partnering With SHELTER COFFEE', 'Your application has been received successfully.', 'Application Number:',
            $application->reference_number, 'you will be contacted if the application proceeds to a further stage.', 'Please keep your application number for future reference.'] as $text) {
            $this->assertStringContainsString($text, $done);
        }
        $this->assertStringContainsString('<meta name="robots" content="noindex', $done);
        $this->get('/en/franchise/submitted/')->assertRedirect('http://localhost/en/franchise/');
    }

    public function test_other_needs_a_description_and_each_box_is_required_on_its_own(): void
    {
        $this->seedFranchise();

        $this->post('/ar/franchise/', $this->validInput(['partnership_interest_type' => 'other', 'non_binding_acknowledgement' => '', 'email' => 'not-an-email']))
            ->assertRedirect('http://localhost/ar/franchise/');
        $html = (string) $this->fresh('/ar/franchise/')->getContent();
        foreach (['وضح نوع اهتمامك بالشراكة: هذا الحقل مطلوب.', 'إقرار طبيعة الطلب: يجب تأكيد هذا الإقرار لإرسال الطلب.', 'البريد الإلكتروني: أدخل بريدًا إلكترونيًا صحيحًا.'] as $line) {
            $this->assertStringContainsString($line, $html);
        }
        $this->assertMatchesRegularExpression('#id="partnership_interest_type-other"[^>]*checked#', $html, 'the choice survives the error');
        $this->assertStringContainsString('value="Test Partner"', $html);
        $this->assertSame(0, Application::query()->count());

        $this->post('/en/franchise/', $this->validInput(['data_processing_consent' => '']))->assertRedirect('http://localhost/en/franchise/');
        $this->post('/en/franchise/', $this->validInput(['partnership_interest_type' => 'franchise_rights']))->assertRedirect('http://localhost/en/franchise/');
        $this->assertSame(0, DB::table('applications')->count(), 'only the six approved values');

        $this->post('/en/franchise/', $this->validInput(['partnership_interest_type' => 'other', 'partnership_interest_other' => 'A kiosk partnership']))
            ->assertRedirect('http://localhost/en/franchise/submitted/');
        $this->assertSame('A kiosk partnership', PartnershipApplication::query()->sole()->partnership_interest_other);
    }

    public function test_the_same_submission_is_received_once_and_bots_are_turned_away(): void
    {
        $this->seedFranchise();
        $input = $this->validInput();

        $this->post('/en/franchise/', $input)->assertRedirect('http://localhost/en/franchise/submitted/');
        $this->post('/en/franchise/', $input)->assertRedirect('http://localhost/en/franchise/submitted/');
        $this->assertSame(1, Application::query()->count(), 'idempotency key');

        $this->post('/en/franchise/', $this->validInput(['website' => 'http://spam.example']))->assertRedirect('http://localhost/en/franchise/');
        $this->post('/en/franchise/', $this->validInput(['form_token' => Crypt::encryptString((string) now()->getTimestamp())]))->assertRedirect('http://localhost/en/franchise/');
        $this->post('/en/franchise/', $this->validInput(['idempotency_key' => 'not-a-uuid']))->assertRedirect('http://localhost/en/franchise/');
        $this->assertSame(1, Application::query()->count());
    }

    public function test_english_approved_later_fills_an_existing_page_without_touching_owner_edits(): void
    {
        $this->seedFranchise();
        // The page as first seeded: the two sections in Arabic only, hidden; and an Owner edit elsewhere.
        foreach (['لماذا تصبح شريكًا مع SHELTER؟', 'ما الذي نبحث عنه في الشريك؟'] as $heading) {
            PageSection::query()->where('heading_ar', $heading)->update(['heading_en' => null, 'body_en' => null, 'is_visible' => false]);
        }
        PageSection::query()->where('body_ar', 'like', 'الالتزام بهوية%')->update(['body_en' => null, 'is_visible' => false]);
        PageSection::query()->where('heading_ar', 'أسواق النمو')->update(['body_en' => 'Owner edited text.']);

        $this->seed(FranchiseSeeder::class);

        $why = PageSection::query()->where('heading_ar', 'لماذا تصبح شريكًا مع SHELTER؟')->sole();
        $this->assertSame(['Why Partner With SHELTER?', true], [$why->heading_en, $why->is_visible]);
        $this->assertTrue(PageSection::query()->where('body_ar', 'like', 'الالتزام بهوية%')->sole()->is_visible);
        $this->assertSame('Owner edited text.', PageSection::query()->where('heading_ar', 'أسواق النمو')->sole()->body_en);
        $this->assertFalse(PageSection::query()->where('body_ar', 'like', 'هوية وتجربة العلامة%')->sole()->is_visible, 'pillars stay hidden');
    }

    public function test_the_seeder_never_overwrites_owner_edits(): void
    {
        $this->seedFranchise();
        Page::query()->where('key', 'franchise')->update(['status' => PublishStatus::Draft]);
        $this->seed(FranchiseSeeder::class);

        $this->assertSame(PublishStatus::Draft, Page::query()->where('key', 'franchise')->sole()->status);
        $this->assertSame(17, PageSection::query()->count());
        $this->assertSame(2, ConsentVersion::query()->whereIn('scope', ['partnerships', 'partnership_ack'])->count());
    }
}
