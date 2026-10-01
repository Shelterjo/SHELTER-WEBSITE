<?php

namespace Tests\Feature\Site;

use App\Enums\PublishStatus;
use App\Models\Page;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/** PHASE 2: brand content pages (SI-B03 About · B05 FAQ · B06 Privacy · B07 Terms) — public only when complete. */
class ContentPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
    }

    /** @param  array<string, mixed>  $attributes */
    private function page(string $key, string $type, array $attributes = []): Page
    {
        return Page::query()->create($attributes + [
            'key' => $key,
            'type' => $type,
            'title_ar' => 'عنوان '.$key,
            'title_en' => 'Title '.$key,
            'status' => PublishStatus::Published,
            'published_at' => Carbon::parse('2026-09-30 10:00:00'),
        ]);
    }

    /** @param  array<string, mixed>  $attributes */
    private function section(Page $page, array $attributes = []): void
    {
        $page->sections()->create($attributes + [
            'type' => 'text',
            'heading_ar' => 'قسم',
            'heading_en' => 'Section',
            'body_ar' => "فقرة أولى.\n\nفقرة ثانية.",
            'body_en' => "First paragraph.\n\nSecond paragraph.",
        ]);
    }

    /**
     * Pages caches per request (scoped); a real request starts fresh, so the test does too after changing data.
     *
     * @return TestResponse<Response>
     */
    private function getFresh(string $url): TestResponse
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

    public function test_pages_without_published_text_do_not_exist_and_are_not_linked(): void
    {
        foreach (['about', 'faq', 'privacy', 'terms'] as $key) {
            $this->get("/ar/{$key}/")->assertNotFound();
            $this->get("/en/{$key}/")->assertNotFound();
        }
        $this->page('about', 'brand', ['status' => PublishStatus::Draft]);
        $this->getFresh('/ar/about/')->assertNotFound();

        $html = (string) $this->getFresh('/ar/')->assertOk()->getContent();
        foreach (['/ar/about/', '/ar/faq/', '/ar/privacy/', '/ar/terms/'] as $path) {
            $this->assertStringNotContainsString($path, $html);
        }
    }

    public function test_a_published_page_renders_escaped_paragraphs_in_both_languages_and_joins_the_footer(): void
    {
        $page = $this->page('about', 'brand', ['description_ar' => 'وصف الصفحة', 'description_en' => 'Page description']);
        $this->section($page, ['body_en' => "First <script>alert(1)</script> paragraph.\n\nSecond paragraph."]);

        $html = (string) $this->get('/ar/about/')->assertOk()->getContent();
        $this->assertStringContainsString('<h1 class="ui-page-intro__title">عنوان about</h1>', $html);
        $this->assertStringContainsString('<p>فقرة أولى.</p>', $html);
        $this->assertStringContainsString('<p>فقرة ثانية.</p>', $html);
        $this->assertStringContainsString('<meta name="description" content="وصف الصفحة">', $html);
        $this->assertStringContainsString('<link rel="canonical" href="http://localhost/ar/about/">', $html);
        $this->assertNotNull(collect($this->jsonLd($html))->firstWhere('@type', 'WebPage'));
        $this->assertMatchesRegularExpression('#<a class="ui-site-footer__link" href="http://localhost/ar/about/"\s+aria-current="page"\s*>عنوان about</a>#u', $html);

        $html = (string) $this->get('/en/about/')->assertOk()->getContent();
        $this->assertStringContainsString('First &lt;script&gt;alert(1)&lt;/script&gt; paragraph.', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);

        $home = (string) $this->get('/en/')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#<a class="ui-site-footer__link" href="http://localhost/en/about/"\s*>Title about</a>#', $home);
    }

    public function test_a_missing_language_unconfirmed_ai_text_a_future_date_or_archive_keeps_the_page_offline(): void
    {
        $page = $this->page('about', 'brand');
        $this->section($page, ['body_en' => null]);
        $this->getFresh('/ar/about/')->assertNotFound(); // LANGUAGE-PARITY: never one language only

        $page->sections()->update(['body_en' => 'Text.', 'origin' => 'ai']);
        $this->getFresh('/ar/about/')->assertNotFound(); // G-20: AI text waits for the owner

        $page->sections()->update(['origin' => 'owner']);
        $this->getFresh('/ar/about/')->assertOk();

        $page->update(['published_at' => now()->addDay()]);
        $this->getFresh('/ar/about/')->assertNotFound();

        $page->update(['published_at' => now()->subDay(), 'archived_at' => now()]);
        $this->getFresh('/ar/about/')->assertNotFound();
    }

    public function test_faq_lists_only_published_answers_as_disclosures_and_faqpage_data(): void
    {
        $page = $this->page('faq', 'faq');
        $this->section($page, ['type' => 'faq', 'heading_ar' => 'سؤال أول؟', 'heading_en' => 'First question?', 'body_ar' => 'جواب.', 'body_en' => 'Answer.']);
        $this->section($page, ['type' => 'faq', 'heading_ar' => 'سؤال مخفي؟', 'heading_en' => 'Hidden question?', 'body_ar' => 'جواب.', 'body_en' => 'Answer.', 'is_visible' => false]);

        $html = (string) $this->get('/en/faq/')->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'class="ui-disclosure ui-prose__faq"'));
        $this->assertStringNotContainsString('Hidden question?', $html);

        $faq = collect($this->jsonLd($html))->firstWhere('@type', 'FAQPage');
        $this->assertNotNull($faq);
        $this->assertCount(1, $faq['mainEntity']);
        $this->assertSame('First question?', $faq['mainEntity'][0]['name']);
        $this->assertSame('Answer.', $faq['mainEntity'][0]['acceptedAnswer']['text']);
    }

    public function test_legal_pages_show_the_update_date_and_sit_next_to_the_copyright(): void
    {
        $page = $this->page('privacy', 'legal', ['content_updated_at' => Carbon::parse('2026-09-15 08:00:00')]);
        $this->section($page);

        $html = (string) $this->get('/ar/privacy/')->assertOk()->getContent();
        $this->assertStringContainsString('آخر تحديث: 15 أيلول 2026', $html);
        $this->assertStringContainsString('class="ui-site-footer__legal-links"', $html);

        $html = (string) $this->get('/en/privacy/')->assertOk()->getContent();
        $this->assertStringContainsString('Last updated: September 15, 2026', $html);
    }
}
