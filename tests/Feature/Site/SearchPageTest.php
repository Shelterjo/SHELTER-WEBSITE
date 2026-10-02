<?php

namespace Tests\Feature\Site;

use App\Enums\PublishStatus;
use App\Models\FeatureFlag;
use App\Models\Page;
use App\Services\Content\Search\SearchIndexer;
use App\Services\Content\Search\SearchLog;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\MenuSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** PHASE 2: site search (SI-B08, GLOBAL-SEARCH §2–§6) over the published menu, branches and pages. */
class SearchPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([MasterDataSeeder::class, MenuSeeder::class]);
    }

    public function test_the_language_switch_keeps_the_query_and_hreflang_stays_clean(): void
    {
        // FINAL-QA QA-016.
        $html = (string) $this->get('/en/search/?q=latte')->assertOk()->getContent();
        $this->assertStringContainsString('<link rel="alternate" hreflang="ar" href="http://localhost/ar/search/">', $html);
        $this->assertStringContainsString('href="http://localhost/ar/search/?q=latte"', $html);
    }

    public function test_empty_search_shows_the_form_only_and_stays_out_of_the_index(): void
    {
        $html = (string) $this->get('/ar/search/')->assertOk()->getContent();

        $this->assertStringContainsString('<form class="ui-search-form ui-search-page__form" action="http://localhost/ar/search/" method="get" role="search">', $html);
        $this->assertStringContainsString('name="q"', $html);
        $this->assertStringNotContainsString('ui-search-page__groups', $html);
        $this->assertStringContainsString('<meta name="robots" content="noindex', $html);
    }

    public function test_menu_results_link_to_the_item_in_its_place_and_follow_the_approved_names(): void
    {
        $html = (string) $this->get('/en/search/?q=spanish')->assertOk()->getContent();
        $this->assertStringContainsString('id="search-group-menu"', $html);
        $this->assertMatchesRegularExpression('#href="/en/jo/menu/\#p-spanish-latte-\d{3}"#', $html);

        // Arabic page: an approved Arabic name leads; an item without one shows its English name marked lang="en".
        $html = (string) $this->get('/ar/search/?q='.urlencode('لاتيه'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#<span class="ui-search-page__title"\s*>[^<]*لاتيه[^<]*</span>#u', $html);

        $html = (string) $this->get('/ar/search/?q=v60')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#<span class="ui-search-page__title"\s+lang="en"\s*>V60 #', $html);
    }

    public function test_unapproved_arabic_source_names_are_not_searchable(): void
    {
        // PO-042 default: "قهوة أمريكية" is the source name of AMERICAN COFFEE, still pending the owner's review.
        $html = (string) $this->get('/ar/search/?q='.urlencode('قهوة أمريكية'))->assertOk()->getContent();
        $this->assertStringNotContainsString('american-coffee', $html);

        $html = (string) $this->get('/ar/search/?q=american')->assertOk()->getContent();
        $this->assertStringContainsString('american-coffee', $html);
    }

    public function test_branches_and_fixed_pages_are_found_in_both_languages(): void
    {
        $html = (string) $this->get('/en/search/?q=drive')->assertOk()->getContent();
        $this->assertStringContainsString('id="search-group-branch"', $html);
        $this->assertStringContainsString('href="/en/jo/locations/irbid/drive/"', $html);

        $html = (string) $this->get('/ar/search/?q='.urlencode('فرنشايز'))->assertOk()->getContent();
        $this->assertStringContainsString('href="/ar/contact/"', $html, 'the franchise inquiries card lives on the contact page (D-071)');
    }

    public function test_no_results_explain_and_offer_the_main_pages(): void
    {
        $html = (string) $this->get('/en/search/?q=zzqx')->assertOk()->getContent();

        $this->assertStringContainsString('No results for “zzqx”', $html);
        $this->assertStringContainsString('aria-live="polite"', $html);
        $this->assertStringContainsString('href="http://localhost/en/jo/menu/"', $html);
        $this->assertStringContainsString('href="http://localhost/en/jo/locations/"', $html);
    }

    public function test_published_faq_answers_join_the_index_on_rebuild_and_drafts_never_do(): void
    {
        $page = Page::query()->create(['key' => 'faq', 'type' => 'faq', 'title_ar' => 'الأسئلة', 'title_en' => 'Questions', 'status' => PublishStatus::Draft]);
        $page->sections()->create(['type' => 'faq', 'heading_ar' => 'هل يوجد مواقف؟', 'heading_en' => 'Is there parking?', 'body_ar' => 'جواب.', 'body_en' => 'Answer.']);
        app(SearchIndexer::class)->rebuild();
        $this->assertStringNotContainsString('Is there parking?', (string) $this->get('/en/search/?q=parking')->getContent());

        $page->update(['status' => PublishStatus::Published, 'published_at' => now()->subMinute()]);
        $this->app->forgetScopedInstances();
        app(SearchIndexer::class)->rebuild();
        $this->app->forgetScopedInstances();
        $html = (string) $this->get('/en/search/?q=parking')->assertOk()->getContent();
        $this->assertStringContainsString('href="/en/faq/#q-1"', $html);
        $this->assertStringContainsString('Is there parking?', $html);
    }

    public function test_the_search_log_is_off_until_po_019_and_never_keeps_identifying_text(): void
    {
        $this->get('/en/search/?q=latte')->assertOk();
        $this->assertSame(0, DB::table('search_query_daily')->count());

        FeatureFlag::query()->create(['key' => SearchLog::FLAG, 'enabled' => true]);
        $this->app->forgetScopedInstances();
        $this->get('/en/search/?q=Latte')->assertOk();
        $this->get('/en/search/?q=latte')->assertOk();
        $this->get('/en/search/?q=zzqx')->assertOk();
        $this->get('/en/search/?q='.urlencode('me@example.com'))->assertOk();
        $this->get('/en/search/?q=0799123456')->assertOk();

        $count = fn (string $query, string $column): int => (int) DB::table('search_query_daily')->where('query_norm', $query)->value($column);
        $this->assertSame(2, $count('latte', 'searches'));
        $this->assertSame(0, $count('latte', 'zero_results'));
        $this->assertSame(1, $count('zzqx', 'zero_results'));
        $this->assertSame(2, $count('[redacted]', 'searches'));
        $this->assertFalse(DB::table('search_query_daily')->where('query_norm', 'like', '%example%')->exists());
        $this->assertSame(
            ['id', 'day', 'scope', 'locale', 'query_norm', 'searches', 'zero_results', 'selected_type', 'selected_count'],
            array_keys((array) DB::table('search_query_daily')->first()),
            'counters only: no session, user, address or user agent',
        );
    }

    public function test_entry_points_long_queries_and_the_rate_limit(): void
    {
        $home = (string) $this->get('/ar/')->assertOk()->getContent();
        $this->assertStringContainsString('class="ui-search-form ui-search-form--compact ui-nav-drawer__search" action="http://localhost/ar/search/"', $home);
        $this->assertMatchesRegularExpression('#href="http://localhost/ar/search/"[^>]*class="[^"]*ui-site-header__search#', $home);

        $notFound = (string) $this->get('/en/missing-page/')->assertNotFound()->getContent();
        $this->assertStringContainsString('action="http://localhost/en/search/"', $notFound);

        $long = str_repeat('a', 150);
        $html = (string) $this->get('/en/search/?q='.$long)->assertOk()->getContent();
        $this->assertStringContainsString('value="'.str_repeat('a', 100).'"', $html);

        for ($i = 0; $i < 30; $i++) {
            $this->get('/en/search/?q=tea');
        }
        $this->get('/en/search/?q=tea')->assertStatus(429);
    }
}
