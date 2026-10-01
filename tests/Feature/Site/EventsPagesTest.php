<?php

namespace Tests\Feature\Site;

use App\Models\Branch;
use App\Models\Experience;
use App\Models\Market;
use App\Services\Content\Search\SearchIndexer;
use Carbon\CarbonImmutable;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/** PHASE 2: events and campaigns (SI-M07 listing, SI-M08 detail — DX-014) from the one `experiences` table. */
class EventsPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-10 12:00:00', 'Asia/Amman'));
    }

    /** @param  array<string, mixed>  $attributes */
    private function event(string $slug, array $attributes = []): Experience
    {
        return Experience::query()->create($attributes + [
            'type' => 'event',
            'market_id' => Market::query()->where('code', 'jo')->value('id'),
            'slug' => $slug,
            'title_ar' => 'فعالية '.$slug,
            'title_en' => 'Event '.$slug,
            'body_ar' => 'وصف الفعالية.',
            'body_en' => 'About the event.',
            'status' => 'scheduled',
            'starts_at' => CarbonImmutable::parse('2026-10-12 16:00', 'Asia/Amman')->utc(),
            'ends_at' => CarbonImmutable::parse('2026-10-12 21:00', 'Asia/Amman')->utc(),
        ]);
    }

    /**
     * Services cache per request (scoped); a real request starts fresh, so the test does too.
     *
     * @return TestResponse<Response>
     */
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

    public function test_an_empty_listing_is_calm_noindexed_and_not_linked(): void
    {
        $html = (string) $this->get('/ar/jo/events/')->assertOk()->getContent();

        $this->assertStringContainsString('لا توجد فعاليات حاليًا', $html);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
        $this->assertStringNotContainsString('ui-event-card', $html);
        $this->assertStringNotContainsString('href="http://localhost/ar/jo/events/"', (string) $this->fresh('/ar/')->getContent());
    }

    public function test_listing_shows_on_now_first_then_upcoming_and_joins_the_footer(): void
    {
        $this->event('later', ['starts_at' => CarbonImmutable::parse('2026-10-20 10:00', 'Asia/Amman')->utc(), 'ends_at' => CarbonImmutable::parse('2026-10-22 18:00', 'Asia/Amman')->utc()]);
        $this->event('today', ['status' => 'active', 'starts_at' => CarbonImmutable::parse('2026-10-10 09:00', 'Asia/Amman')->utc(), 'ends_at' => CarbonImmutable::parse('2026-10-10 23:00', 'Asia/Amman')->utc()]);
        $this->event('soon');

        $html = (string) $this->get('/en/jo/events/')->assertOk()->getContent();
        $positions = array_map(fn (string $slug): int|false => strpos($html, '/en/jo/events/'.$slug.'/'), ['today', 'soon', 'later']);
        $this->assertNotContains(false, $positions);
        $this->assertSame($positions, array_values(array_unique($positions)));
        $sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions, 'on now first, then by start');
        $this->assertStringContainsString('On now', $html);
        $this->assertStringContainsString('Coming up', $html);
        $this->assertStringContainsString('20 – 22 October 2026</time>', $html, 'several days: the range only, no ambiguous time span');
        $later = (string) $this->fresh('/en/jo/events/later/')->assertOk()->getContent();
        $this->assertStringContainsString('Starts: Tuesday 20 October, 10:00 AM', $later);
        $this->assertStringContainsString('Ends: Thursday 22 October, 6:00 PM', $later);
        $html = (string) $this->fresh('/en/jo/events/')->getContent();
        $this->assertMatchesRegularExpression('#<a class="ui-site-footer__link" href="http://localhost/en/jo/events/"\s+aria-current="page"\s*>Events</a>#', $html);
    }

    public function test_detail_page_carries_valid_event_data_in_the_market_time_zone(): void
    {
        $drive = Branch::query()->where('slug', 'drive')->value('id');
        $this->event('tasting', ['branch_ids' => [$drive], 'cta_label_ar' => 'سجّل الآن', 'cta_label_en' => 'Register', 'cta_url' => 'https://example.com/register', 'terms_ar' => 'شرط.', 'terms_en' => 'A term.']);

        $html = (string) $this->get('/ar/jo/events/tasting/')->assertOk()->getContent();
        $this->assertStringContainsString('<h1 class="ui-page-intro__title">فعالية tasting</h1>', $html);
        $this->assertStringContainsString('الاثنين 12 تشرين الأول 2026', $html);
        $this->assertStringContainsString('شلتر كوفي درايف', $html);
        $this->assertStringContainsString('href="https://example.com/register"', $html);
        $this->assertStringContainsString('class="ui-disclosure ui-event-detail__terms"', $html);

        $event = collect($this->jsonLd($html))->firstWhere('@type', 'Event');
        $this->assertNotNull($event);
        $this->assertSame('2026-10-12T16:00:00+03:00', $event['startDate']);
        $this->assertSame('2026-10-12T21:00:00+03:00', $event['endDate']);
        $this->assertSame('شلتر كوفي درايف', $event['location']['name']);
    }

    public function test_hidden_states_keep_an_event_off_the_site(): void
    {
        $this->event('one-language', ['title_en' => null]);
        $this->event('emergency', ['emergency_disabled' => true]);
        $this->event('switched-off', ['manual_state' => 'off']);
        $this->event('paused', ['status' => 'paused']);
        $this->event('draft', ['status' => 'draft']);
        $this->event('ai-text', ['origin' => 'ai']);
        $this->event('archived', ['archived_at' => now()]);
        $this->event('bad-dates', ['ends_at' => CarbonImmutable::parse('2026-10-12 10:00', 'Asia/Amman')->utc()]);
        $this->event('unsafe-cta', ['cta_label_en' => 'Go', 'cta_label_ar' => 'اذهب', 'cta_url' => 'javascript:alert(1)']);

        $html = (string) $this->get('/en/jo/events/')->assertOk()->getContent();
        foreach (['one-language', 'emergency', 'switched-off', 'paused', 'draft', 'ai-text', 'archived', 'bad-dates'] as $slug) {
            $this->assertStringNotContainsString('/events/'.$slug.'/', $html, $slug);
            $this->fresh('/en/jo/events/'.$slug.'/')->assertNotFound();
        }
        $unsafe = (string) $this->fresh('/en/jo/events/unsafe-cta/')->assertOk()->getContent();
        $this->assertStringNotContainsString('javascript:', $unsafe, 'G-08: only https:// or site-relative links');
    }

    public function test_an_ended_event_keeps_its_page_marked_ended_without_event_data(): void
    {
        $this->event('past', ['status' => 'active', 'starts_at' => CarbonImmutable::parse('2026-10-01 18:00', 'Asia/Amman')->utc(), 'ends_at' => CarbonImmutable::parse('2026-10-02 01:00', 'Asia/Amman')->utc(), 'cta_label_en' => 'Register', 'cta_label_ar' => 'سجّل', 'cta_url' => '/ar/']);

        $this->assertStringNotContainsString('/events/past/', (string) $this->get('/en/jo/events/')->getContent());
        $html = (string) $this->fresh('/en/jo/events/past/')->assertOk()->getContent();
        $this->assertStringContainsString('This event has ended.', $html);
        $this->assertStringContainsString('Thursday 1 October 2026', $html, 'an evening that runs past midnight is still one day');
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
        $this->assertNull(collect($this->jsonLd($html))->firstWhere('@type', 'Event'));
        $this->assertStringNotContainsString('>Register<', $html);
    }

    public function test_events_on_now_or_coming_up_are_searchable(): void
    {
        $this->event('latte-art', ['title_en' => 'Latte art evening', 'title_ar' => 'أمسية فن اللاتيه']);
        app(SearchIndexer::class)->rebuild();

        $html = (string) $this->fresh('/en/search/?q=evening')->assertOk()->getContent();
        $this->assertStringContainsString('id="search-group-event"', $html);
        $this->assertStringContainsString('href="/en/jo/events/latte-art/"', $html);
    }
}
