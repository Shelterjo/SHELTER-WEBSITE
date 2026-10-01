<?php

namespace Tests\Feature\Site;

use App\Models\MenuCategory;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The public menu (SI-M02, Menu IA spec): approved names only, VAT-inclusive prices, order, season, schema. */
class MenuPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function card(string $html, string $code): string
    {
        $suffix = strtolower(substr($code, -3));
        $this->assertSame(1, preg_match('#<article[^>]*id="p-[a-z0-9-]+-'.$suffix.'".*?</article>#s', $html, $match), "card {$code}");

        return $match[0];
    }

    public function test_menu_renders_every_active_product_once_with_clean_canonical(): void
    {
        $html = (string) $this->get('/ar/jo/menu/?branch=drive')->assertOk()->getContent();

        $this->assertStringContainsString('<html lang="ar" dir="rtl">', $html);
        $this->assertStringContainsString('<link rel="canonical" href="http://localhost/ar/jo/menu/">', $html);
        $this->assertStringContainsString('hreflang="en" href="http://localhost/en/jo/menu/"', $html);
        $active = Product::query()->where('status', 'active')->whereNull('merged_into_id')->count();
        $this->assertSame($active, substr_count($html, 'data-ui-menu-item="'));
        $this->assertMatchesRegularExpression('/data-value="drive" aria-pressed="true"/', $html);
    }

    public function test_arabic_page_shows_an_arabic_name_only_when_it_is_approved(): void
    {
        $html = (string) $this->get('/ar/jo/menu/')->assertOk()->getContent();

        // PRD-00001: approved Arabic name first, English as the secondary name.
        $approved = $this->card($html, 'PRD-00001');
        $this->assertStringContainsString('قهوة تركية سينجل', $approved);
        $this->assertStringContainsString('TURKISH COFFEE SINGLE', $approved);

        // PRD-00014 CAPPUCCINO: Arabic name pending (D-091) → English only, no secondary, no source name (CF-03).
        $pending = $this->card($html, 'PRD-00014');
        $this->assertStringContainsString('CAPPUCCINO', $pending);
        $this->assertStringNotContainsString('ui-product-card__secondary', $pending);
        $withoutPrice = (string) preg_replace('#<p class="ui-product-card__price".*?</p>#s', '', $pending);
        $this->assertDoesNotMatchRegularExpression('/[\x{0600}-\x{06FF}]{2,}/u', strip_tags($withoutPrice), 'no Arabic product name');
    }

    public function test_prices_are_vat_inclusive_jod_with_two_decimals(): void
    {
        $html = (string) $this->get('/en/jo/menu/')->assertOk()->getContent();

        $this->assertStringContainsString('1.50', $this->card($html, 'PRD-00001'));
        $this->assertStringContainsString('2.50', $this->card($html, 'PRD-00014'));
        $this->assertStringNotContainsString('+ tax', $html);
    }

    public function test_sections_follow_the_approved_order_with_spring_on_top_and_sweets_grouped(): void
    {
        $html = (string) $this->get('/en/jo/menu/')->assertOk()->getContent();

        preg_match_all('/<section class="ui-menu__section[^"]*" id="([a-z-]+)"/', $html, $matches);
        $this->assertSame(
            ['spring', 'speciality-coffee', 'hot-drinks', 'cold-drinks', 'frappe', 'milkshake', 'smoothies', 'fizzy-drinks', 'tea', 'sweets'],
            $matches[1],
        );
        $this->assertStringContainsString('id="cake"', $html);
        $this->assertStringContainsString('id="cookies"', $html);
    }

    public function test_spring_disappears_when_its_season_is_not_active(): void
    {
        MenuCategory::query()->where('type', 'seasonal')->update(['status' => 'draft']);
        $html = (string) $this->get('/en/jo/menu/')->assertOk()->getContent();

        $this->assertStringNotContainsString('id="spring"', $html);
    }

    public function test_nothing_about_availability_is_claimed_while_it_is_unknown(): void
    {
        $html = (string) $this->get('/ar/jo/menu/')->assertOk()->getContent();

        $this->assertStringNotContainsString((string) __('menu.unavailable', [], 'ar'), $html);
        $this->assertStringNotContainsString('ui-product-card--unavailable', $html);
    }

    public function test_menu_structured_data_lists_sections_with_jod_offers(): void
    {
        $html = (string) $this->get('/en/jo/menu/')->assertOk()->getContent();

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $blocks);
        $menu = collect($blocks[1])->map(fn (string $json): array => json_decode($json, true, flags: JSON_THROW_ON_ERROR))->firstWhere('@type', 'Menu');
        $this->assertNotNull($menu);
        $this->assertCount(10, $menu['hasMenuSection']);
        $this->assertSame('JOD', $menu['hasMenuSection'][1]['hasMenuItem'][0]['offers']['priceCurrency']);
    }

    public function test_unknown_branch_parameter_falls_back_to_all_branches(): void
    {
        $html = (string) $this->get('/en/jo/menu/?branch=nope')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/data-value="all" aria-pressed="true"/', $html);
    }
}
