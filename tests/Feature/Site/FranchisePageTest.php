<?php

namespace Tests\Feature\Site;

use App\Enums\FactSource;
use App\Enums\FactStatus;
use App\Enums\PublishStatus;
use App\Models\Page;
use App\Models\Recruitment\Application;
use App\Models\Recruitment\ConsentVersion;
use App\Models\Recruitment\PartnershipApplication;
use App\Models\User;
use App\Services\Content\Search\SearchIndexer;
use App\Services\Core\Settings;
use App\Services\MasterData\FactRegistry;
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
 * SI-B12 franchise & partnerships (docs/franchise/02–05): hidden until the Owner publishes its content (PO-030); the FR
 * form opens only with the approved consent (PF-03) and disclaimer (PF-02). Test texts below are fixtures, not content.
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

    private function publishPage(): Page
    {
        $page = Page::query()->create([
            'key' => 'franchise', 'type' => 'landing', 'title_ar' => 'عنوان تجريبي', 'title_en' => 'Test title',
            'description_ar' => 'وصف تجريبي.', 'description_en' => 'Test description.',
            'status' => PublishStatus::Published, 'published_at' => now()->subMinute(),
        ]);
        $page->sections()->createMany([
            ['type' => 'text', 'heading_ar' => 'قسم', 'heading_en' => 'Section', 'body_ar' => 'فقرة.', 'body_en' => 'Paragraph.', 'sort_order' => 1],
            ['type' => 'list', 'heading_ar' => 'قائمة', 'heading_en' => 'List', 'body_ar' => "أ\nب", 'body_en' => "First point\nSecond point", 'sort_order' => 2],
            ['type' => 'steps', 'heading_ar' => 'خطوات', 'heading_en' => 'Steps', 'body_ar' => "١\n٢", 'body_en' => "Apply\nMeet", 'sort_order' => 3],
            ['type' => 'faq', 'heading_ar' => 'سؤال؟', 'heading_en' => 'Is there a fee question?', 'body_ar' => 'جواب.', 'body_en' => 'Fixture answer.', 'sort_order' => 4],
        ]);
        $this->app->forgetScopedInstances();

        return $page;
    }

    private function openForm(): ConsentVersion
    {
        $owner = User::factory()->create();
        $settings = app(Settings::class);
        $facts = app(FactRegistry::class);
        foreach (['ar' => 'نص إخلاء مسؤولية تجريبي.', 'en' => 'Fixture disclaimer text.'] as $locale => $text) {
            $key = 'franchise.disclaimer_'.$locale;
            $settings->set($key, $text, $owner, 'test');
            $facts->register($key, 'franchise', $text, FactStatus::Approved, FactSource::OwnerDecision, decisionRef: 'D-TEST');
        }
        $this->app->forgetScopedInstances();

        return ConsentVersion::query()->create([
            'scope' => 'partnerships', 'version' => 'partnerships-test-v1', 'text_ar' => 'نص موافقة تجريبي.',
            'text_en' => 'Fixture consent text.', 'active_from' => now()->subDay(), 'is_active' => true,
        ]);
    }

    /**
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function validInput(array $overrides = []): array
    {
        return $overrides + [
            'full_name' => 'Test Partner', 'phone' => '0791234567', 'email' => 'Partner.Test@Example.com', 'country' => 'jo',
            'city' => 'Irbid', 'market' => 'North', 'interest' => 'Single unit', 'experience_band' => 'y3_5', 'experience_text' => '',
            'owns_business' => 'yes', 'location_status' => 'searching', 'introduction' => "Hello.\nA short note.", 'consent' => '1',
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

    public function test_the_page_does_not_exist_until_published_and_nothing_links_to_it(): void
    {
        $this->get('/ar/franchise/')->assertNotFound();
        $this->get('/en/franchise/')->assertNotFound();
        $this->post('/en/franchise/', $this->validInput())->assertNotFound();

        $contact = (string) $this->fresh('/ar/contact/')->assertOk()->getContent();
        $this->assertStringNotContainsString('/ar/franchise/', $contact, 'the inquiries card shows its number only');
        $this->assertStringContainsString('href="tel:+962799338445"', $contact);
        $footer = (string) $this->fresh('/en/')->getContent();
        $this->assertStringNotContainsString('/en/franchise/', $footer);
        $this->assertStringContainsString('href="http://localhost/en/careers/"', $footer);

        // A draft never shows.
        $this->publishPage()->update(['status' => PublishStatus::Draft]);
        $this->fresh('/en/franchise/')->assertNotFound();
    }

    public function test_published_without_approved_texts_shows_the_content_and_the_inquiries_contact_not_the_form(): void
    {
        $this->publishPage();
        $html = (string) $this->get('/en/franchise/')->assertOk()->getContent();

        $this->assertStringContainsString('<h1 class="ui-franchise__title">Test title</h1>', $html);
        $this->assertStringContainsString('FRANCHISE &amp; PARTNERSHIPS', $html);
        $this->assertStringContainsString('<li class="ui-steps__item">Apply</li>', $html);
        $this->assertMatchesRegularExpression('#<li class="ui-franchise__list-item">.*?<span>Second point</span></li>#s', $html);
        $this->assertStringContainsString('id="q-4"', $html, 'FAQ anchors follow the section position (search links)');
        $this->assertStringNotContainsString('data-franchise-form', $html);
        $this->assertStringContainsString('href="#franchise-contact"', $html);
        $this->assertStringContainsString('id="franchise-contact"', $html);
        $this->assertStringContainsString('href="tel:+962799338445"', $html);

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        $types = array_map(fn (string $json): string => (string) (json_decode($json, true)['@type'] ?? ''), $matches[1]);
        $this->assertContains('FAQPage', $types);
        $this->assertContains('WebPage', $types);
        foreach (['Offer', 'Product', 'AggregateRating', 'Review'] as $forbidden) {
            $this->assertStringNotContainsString('"@type":"'.$forbidden.'"', $html);
        }

        // Linked once published: footer (short label), the contact card, search.
        $footer = (string) $this->fresh('/en/')->getContent();
        $this->assertMatchesRegularExpression('#href="http://localhost/en/franchise/"\s*>Franchise</a>#', $footer);
        $this->assertStringContainsString('href="http://localhost/ar/franchise/"', (string) $this->fresh('/ar/contact/')->getContent());
        app(SearchIndexer::class)->rebuild();
        $search = (string) $this->fresh('/en/search/?q=fee')->assertOk()->getContent();
        $this->assertStringContainsString('href="/en/franchise/#q-4"', $search);
    }

    public function test_with_approved_consent_and_disclaimer_a_partnership_application_is_received(): void
    {
        $this->publishPage();
        $consent = $this->openForm();

        $html = (string) $this->fresh('/en/franchise/')->assertOk()->getContent();
        $this->assertStringContainsString('data-franchise-form', $html);
        $this->assertStringContainsString('href="#apply"', $html);
        $this->assertStringContainsString('Fixture disclaimer text.', $html);
        $this->assertStringContainsString('Fixture consent text.', $html);
        $this->assertStringNotContainsString('نص موافقة تجريبي', $html, 'the consent follows the page language');
        $this->assertMatchesRegularExpression('#<option value="JO"\s*>Jordan</option>#', $html, 'Jordan first');
        $this->assertStringNotContainsString('investment', strtolower(strip_tags($html)), 'no investment question (field 14)');

        $this->post('/en/franchise/', $this->validInput())->assertRedirect('http://localhost/en/franchise/submitted/');
        $application = Application::query()->sole();
        $partner = PartnershipApplication::query()->sole();
        $this->assertSame('FR', $application->type);
        $this->assertMatchesRegularExpression('/^FR-\d{4}-00101$/', $application->reference_number, 'D-316');
        $this->assertSame('+962791234567', $partner->phone_normalized);
        $this->assertSame('partner.test@example.com', $partner->email_normalized);
        $this->assertSame('JO', $partner->country_code);
        $this->assertSame('instagram', $partner->utm_source);
        $this->assertSame('l.instagram.com', $partner->referrer_domain);
        $this->assertNull($partner->experience_text);
        $this->assertSame($consent->id, (int) DB::table('application_consents')->where('application_id', $application->id)->value('consent_version_id'));
        $this->assertNotContains('ip', array_keys((array) DB::table('partnership_applications')->first()), 'attribution never keeps an IP');

        $done = (string) $this->get('/en/franchise/submitted/')->assertOk()->getContent();
        $this->assertStringContainsString($application->reference_number, $done);
        $this->assertStringContainsString('Thank you for your interest in partnering with SHELTER COFFEE', $done);
        $this->assertStringContainsString('<meta name="robots" content="noindex', $done);
        $this->get('/en/franchise/submitted/')->assertRedirect('http://localhost/en/franchise/');
    }

    public function test_validation_keeps_answers_and_names_each_field(): void
    {
        $this->publishPage();
        $this->openForm();

        $this->post('/ar/franchise/', $this->validInput(['email' => 'not-an-email', 'country' => 'XX', 'experience_band' => 'lots', 'introduction' => '', 'consent' => '']))
            ->assertRedirect('http://localhost/ar/franchise/');
        $html = (string) $this->fresh('/ar/franchise/')->getContent();
        foreach (['البريد الإلكتروني: أدخل بريدًا إلكترونيًا صحيحًا.', 'الدولة: اختر قيمة من القائمة.', 'الخبرة في الأعمال: اختر قيمة من القائمة.', 'رسالة تعريفية قصيرة: هذا الحقل مطلوب.', 'يجب الموافقة لإرسال الطلب.'] as $line) {
            $this->assertStringContainsString($line, $html);
        }
        $this->assertStringContainsString('value="Test Partner"', $html);
        $this->assertSame(0, Application::query()->count());
    }

    public function test_the_same_submission_is_received_once_and_bots_are_turned_away(): void
    {
        $this->publishPage();
        $this->openForm();
        $input = $this->validInput();

        $this->post('/en/franchise/', $input)->assertRedirect('http://localhost/en/franchise/submitted/');
        $this->post('/en/franchise/', $input)->assertRedirect('http://localhost/en/franchise/submitted/');
        $this->assertSame(1, Application::query()->count(), 'idempotency key');

        $this->post('/en/franchise/', $this->validInput(['website' => 'http://spam.example']))->assertRedirect('http://localhost/en/franchise/');
        $this->post('/en/franchise/', $this->validInput(['form_token' => Crypt::encryptString((string) now()->getTimestamp())]))->assertRedirect('http://localhost/en/franchise/');
        $this->post('/en/franchise/', $this->validInput(['idempotency_key' => 'not-a-uuid']))->assertRedirect('http://localhost/en/franchise/');
        $this->assertSame(1, Application::query()->count());
    }
}
