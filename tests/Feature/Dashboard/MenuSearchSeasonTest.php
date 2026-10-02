<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\MenuCategory;
use App\Models\Product;
use App\Models\SearchAlias;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * Dashboard → Menu D6 (M50): the search words (CMS-018 — only the Owner's, archived not deleted, live at once in the
 * menu search and the site search), the seasonal section (CMS-009, MENU-044 — by its dates, shown or hidden; no date
 * invented), new items (next PRD number) and moving an item to another section.
 */
class MenuSearchSeasonTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private MenuCategory $season;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-10 12:00:00', 'Asia/Amman'));
        $this->login();
        $this->product = Product::query()->where('code', 'PRD-00003')->firstOrFail(); // AMERICAN COFFEE
        $this->season = MenuCategory::query()->where('type', 'seasonal')->firstOrFail(); // SPRING (CAT-009)
    }

    private function login(): void
    {
        $owner = User::query()->first() ?? User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
    }

    /** @return TestResponse<Response> */
    private function fresh(string $url): TestResponse
    {
        $this->app->forgetScopedInstances();

        return $this->get($url);
    }

    /** The search the menu page runs in the browser: the terms of one item in its JSON index. */
    private function menuTerms(string $code): string
    {
        $html = (string) $this->fresh('/ar/jo/menu/')->assertOk()->getContent();
        preg_match('#<script type="application/json" id="menu-index">(.*?)</script>#s', $html, $m);
        $index = json_decode($m[1] ?? '[]', true);
        foreach (is_array($index) ? $index : [] as $row) {
            if (is_array($row) && str_contains((string) json_encode($row), strtolower(substr($code, -3)))) {
                return (string) json_encode($row['terms'] ?? [], JSON_UNESCAPED_UNICODE);
            }
        }

        return '';
    }

    public function test_a_search_word_finds_the_item_at_once_and_leaves_without_being_deleted(): void
    {
        $url = '/dashboard/data/menu/'.$this->product->id;
        $this->assertStringNotContainsString('AMERICAN COFFEE', (string) $this->fresh('/en/search/?q=amerikano')->getContent());

        $this->post($url.'/words', ['word' => ''])->assertSessionHasErrors(['word'], null, 'words');
        $this->post($url.'/words', ['word' => 'american coffee'])->assertSessionHasErrors(['word'], null, 'words'); // its own name
        $this->post($url.'/words', ['word' => 'Amerikano'])->assertSessionHasNoErrors();
        $this->post($url.'/words', ['word' => 'AMERIKANO'])->assertSessionHasErrors(['word'], null, 'words'); // the same word
        $this->post($url.'/words', ['word' => 'أمريكانو'])->assertSessionHasNoErrors();
        $this->assertSame(['en', 'ar'], SearchAlias::query()->orderBy('id')->pluck('locale')->all(), 'the language comes from the word');

        $this->get($url)->assertOk()->assertSee('Amerikano')->assertSee('أمريكانو');
        $this->assertStringContainsString('AMERICAN COFFEE', (string) $this->fresh('/en/search/?q=amerikano')->getContent(), 'the site search, without a manual rebuild');
        $this->assertStringContainsString('AMERICAN COFFEE', (string) $this->fresh('/ar/search/?q=امريكانو')->getContent(), 'normalised: أ = ا');
        $this->assertStringContainsString('Amerikano', $this->menuTerms('PRD-00003'), 'the menu page search');
        $this->get('/dashboard/data/menu/words?q=amerikano')->assertOk()->assertSee('AMERICAN COFFEE')->assertSee('أمريكانو');

        $word = SearchAlias::query()->where('value', 'Amerikano')->firstOrFail();
        $this->post($url.'/words/'.$word->id.'/archive')->assertSessionHas('status');
        $this->assertStringNotContainsString('Amerikano', $this->menuTerms('PRD-00003'));
        $this->assertSame(SearchAlias::STATUS_ARCHIVED, $word->refresh()->status, 'archived, never deleted');
        $this->assertSame(2, SearchAlias::query()->count());
        $this->assertSame(['menu.search_word_added', 'menu.search_word_added', 'menu.search_word_archived'],
            AuditLog::query()->where('action', 'like', 'menu.search_word%')->orderBy('id')->pluck('action')->all());

        $other = Product::query()->where('code', 'PRD-00004')->firstOrFail();
        $this->post('/dashboard/data/menu/'.$other->id.'/words', ['word' => 'أمريكانو'])
            ->assertSessionHas('status', fn (string $text): bool => str_contains($text, 'AMERICAN COFFEE'));
    }

    public function test_the_season_follows_the_owners_choice_and_its_dates(): void
    {
        $url = '/dashboard/data/menu/season/'.$this->season->id;
        $this->get('/dashboard/data/menu/season')->assertOk()->assertSee('ظاهر (بقرارك)')->assertSee('بلا تواريخ');
        $this->assertStringContainsString('ui-menu__section--season', (string) $this->fresh('/ar/jo/menu/')->getContent(), 'F-17: shown today without dates');

        $this->put($url, ['mode' => 'dates', 'name_en' => 'SPRING'])->assertSessionHasErrors(['starts_on', 'ends_on'], null, 'season'.$this->season->id);
        $this->put($url, ['mode' => 'dates', 'name_en' => 'SPRING', 'starts_on' => '2026-10-20', 'ends_on' => '2026-10-15'])->assertSessionHasErrors(['ends_on'], null, 'season'.$this->season->id);
        $this->put($url, ['mode' => 'dates', 'name_en' => 'SPRING', 'starts_on' => '2026-10-12', 'ends_on' => '2026-10-31', 'reason' => 'trial'])->assertSessionHasNoErrors();
        $this->assertStringNotContainsString('ui-menu__section--season', (string) $this->fresh('/ar/jo/menu/')->getContent(), 'not before its first day');
        $this->assertStringNotContainsString('ROZY BASIL', (string) $this->fresh('/en/search/?q=rozy')->getContent());
        $this->get('/dashboard/live')->assertOk()->assertSee('يبدأ حسب التواريخ');

        $this->travelTo(CarbonImmutable::parse('2026-10-12 00:30', 'Asia/Amman'));
        $this->login();
        $this->assertStringContainsString('ui-menu__section--season', (string) $this->fresh('/ar/jo/menu/')->getContent(), 'its first day (Amman)');
        $this->assertStringContainsString('ROZY BASIL', (string) $this->fresh('/en/search/?q=rozy')->getContent(), 'the search follows the season by itself');

        $this->travelTo(CarbonImmutable::parse('2026-11-01 00:10', 'Asia/Amman'));
        $this->login();
        $this->assertStringNotContainsString('ui-menu__section--season', (string) $this->fresh('/ar/jo/menu/')->getContent(), 'it leaves after its last day');
        $this->assertStringNotContainsString('ROZY BASIL', (string) $this->fresh('/en/search/?q=rozy')->getContent());
        $this->assertSame(5, Product::query()->where('menu_category_id', $this->season->id)->count(), 'nothing is deleted');

        $this->put($url, ['mode' => 'on', 'name_en' => 'SPRING', 'starts_on' => '2026-10-12', 'ends_on' => '2026-10-31'])->assertSessionHasNoErrors();
        $this->assertStringContainsString('ui-menu__section--season', (string) $this->fresh('/ar/jo/menu/')->getContent(), 'shown by hand, dates kept');
        $this->assertSame('2026-10-31', $this->season->refresh()->season_ends_on?->toDateString());
        $this->put($url, ['mode' => 'off', 'name_en' => 'SPRING'])->assertSessionHasNoErrors();
        $this->assertStringNotContainsString('ui-menu__section--season', (string) $this->fresh('/ar/jo/menu/')->getContent());
        $this->assertSame(3, AuditLog::query()->where('action', 'menu.season_saved')->count());
    }

    public function test_a_new_item_takes_the_next_number_and_can_move_into_the_season(): void
    {
        $this->get('/dashboard/data/menu/new')->assertOk()->assertSee('صنف جديد');
        $this->post('/dashboard/data/menu', ['category' => (string) $this->product->menu_category_id, 'name_en' => '', 'price' => '2.375'])
            ->assertSessionHasErrors(['name_en', 'price'], null, 'create');
        $this->post('/dashboard/data/menu', ['category' => (string) $this->product->menu_category_id, 'name_en' => 'american coffee', 'price' => '2.5'])
            ->assertSessionHasErrors(['name_en'], null, 'create'); // already in this section
        $this->post('/dashboard/data/menu', ['category' => (string) $this->product->menu_category_id, 'name_en' => 'CORTADO TRIAL', 'name_ar' => 'كورتادو تجريبي', 'price' => '2.5', 'visible' => '1'])
            ->assertSessionHasNoErrors();
        $item = Product::query()->where('display_name_en', 'CORTADO TRIAL')->firstOrFail();
        $this->assertSame('PRD-00193', $item->code, 'the next number; a retired one is never reused');
        $menu = (string) $this->fresh('/ar/jo/menu/')->getContent();
        $this->assertStringContainsString('كورتادو تجريبي', $menu, "the Owner's own Arabic name shows");
        $this->assertSame(1, AuditLog::query()->where('action', 'menu.product_created')->count());

        $this->put('/dashboard/data/menu/'.$item->id.'/details', ['category' => (string) $this->season->id, 'visible' => '1'])->assertSessionHasNoErrors();
        $this->assertTrue($item->refresh()->is_seasonal);
        $html = (string) $this->fresh('/ar/jo/menu/')->getContent();
        preg_match('#<section class="ui-menu__section ui-menu__section--season".*?</section>#s', $html, $m);
        $this->assertStringContainsString('كورتادو تجريبي', $m[0] ?? '', 'now in the season section');
        $this->assertSame(1, AuditLog::query()->where('action', 'menu.product_moved')->count());
        $this->get('/dashboard/data/menu/season')->assertSee('CORTADO TRIAL');
    }
}
