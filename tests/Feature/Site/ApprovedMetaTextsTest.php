<?php

namespace Tests\Feature\Site;

use App\Enums\FactSource;
use App\Enums\FactStatus;
use App\Models\Award;
use App\Models\Branch;
use App\Models\TeamMember;
use App\Services\Content\SiteTexts;
use App\Services\MasterData\FactRegistry;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * D-332: the Owner-approved Google descriptions (site.meta.*) and the root gateway line ship as the default wording,
 * show on their pages in both languages and stay inside the rules they were approved under: no founding year, counts,
 * prices, phone numbers or superlatives (D-018, D-038, PO-032), and short enough for Google to show whole.
 */
class ApprovedMetaTextsTest extends TestCase
{
    use RefreshDatabase;

    private const KEYS = ['home', 'menu', 'locations', 'branch', 'contact', 'events', 'careers', 'awards', 'family'];

    private function page(string $url): string
    {
        $this->app->forgetScopedInstances();

        return (string) $this->get($url)->assertOk()->getContent();
    }

    /** A noindex page (an empty events list) keeps its description but carries no share preview at all. */
    private function assertDescription(string $html, string $text, string $url, bool $shared = true): void
    {
        $this->assertStringContainsString('<meta name="description" content="'.e($text).'">', $html, $url);
        if ($shared) {
            $this->assertStringContainsString('<meta property="og:description" content="'.e($text).'">', $html, $url.' (share preview)');
        }
    }

    public function test_every_approved_text_keeps_the_approval_rules(): void
    {
        $texts = [];
        foreach (['ar', 'en'] as $locale) {
            foreach (self::KEYS as $key) {
                $texts["site.meta.$key.$locale"] = SiteTexts::original("site.meta.$key", $locale);
            }
            $texts["site.gateway.lead.$locale"] = SiteTexts::original('site.gateway.lead', $locale);
        }

        foreach ($texts as $id => $text) {
            $this->assertNotSame('', $text, $id.' ships with the code');
            $this->assertLessThanOrEqual(160, mb_strlen($text), $id.' fits a Google result');
            $this->assertDoesNotMatchRegularExpression('/\d/u', str_replace('V60', '', $text), $id.': no year, count, price or phone');
            $this->assertDoesNotMatchRegularExpression('/أفضل|الأول|أول |الأكبر|best|first|leader|largest|number one|#1/iu', $text, $id.': no superlatives');
            if (! str_starts_with($id, 'site.meta.branch') && ! str_starts_with($id, 'site.gateway')) {
                $this->assertMatchesRegularExpression('/شلتر كوفي|SHELTER COFFEE/u', $text, $id.': the full brand name (D-007)');
            }
        }
        $this->assertStringContainsString(':name', SiteTexts::original('site.meta.branch', 'ar'));
        $this->assertStringContainsString(':name', SiteTexts::original('site.meta.branch', 'en'));
    }

    public function test_each_page_shows_its_approved_description_in_both_languages(): void
    {
        $this->seed(DatabaseSeeder::class);
        $pages = [
            'home' => ['/ar/', '/en/'],
            'menu' => ['/ar/jo/menu/', '/en/jo/menu/'],
            'locations' => ['/ar/jo/locations/', '/en/jo/locations/'],
            'contact' => ['/ar/contact/', '/en/contact/'],
            'events' => ['/ar/jo/events/', '/en/jo/events/'],
            'careers' => ['/ar/careers/', '/en/careers/'],
        ];
        foreach ($pages as $key => [$ar, $en]) {
            $this->assertDescription($this->page($ar), SiteTexts::original("site.meta.$key", 'ar'), $ar, $key !== 'events');
            $this->assertDescription($this->page($en), SiteTexts::original("site.meta.$key", 'en'), $en, $key !== 'events');
        }

        // The branch line names the branch it describes, in the page's language.
        foreach (Branch::query()->get() as $branch) {
            foreach (['ar', 'en'] as $locale) {
                $url = "/$locale/jo/locations/irbid/{$branch->slug}/";
                $name = (string) $branch->getAttribute('name_'.$locale);
                $html = $this->page($url);
                $this->assertDescription($html, str_replace(':name', $name, SiteTexts::original('site.meta.branch', $locale)), $url);
                $this->assertStringNotContainsString(':name', $html);
            }
        }

        // The root gateway shows the approved Arabic line under the brand.
        $this->assertStringContainsString('<p class="ui-page-intro__lead">'.e(SiteTexts::original('site.gateway.lead', 'ar')).'</p>', $this->page('/'));
    }

    public function test_awards_and_family_carry_theirs_once_they_have_content(): void
    {
        $this->seed(DatabaseSeeder::class);
        $award = Award::query()->create(['title_ar' => 'جائزة', 'title_en' => 'Award', 'issuer_ar' => 'جهة', 'issuer_en' => 'Issuer', 'year' => 2025,
            'evidence_url' => 'https://example.com/award', 'status' => 'published']);
        app(FactRegistry::class)->register($award->factKey(), 'awards', $award->factValue(), FactStatus::Approved, FactSource::OwnerDecision, decisionRef: 'D-TEST');
        TeamMember::query()->create(['display_name_ar' => 'اسم', 'display_name_en' => 'Name', 'job_title_ar' => 'باريستا', 'job_title_en' => 'Barista',
            'branch_id' => Branch::query()->where('slug', 'drive')->value('id'), 'is_published' => true,
            'publish_consent_at' => now(), 'publish_consent_version' => 'family-consent-v1']);

        foreach (['awards', 'family'] as $key) {
            foreach (['ar', 'en'] as $locale) {
                $this->assertDescription($this->page("/$locale/$key/"), SiteTexts::original("site.meta.$key", $locale), "/$locale/$key/");
            }
        }
    }
}
