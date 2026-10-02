<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\MenuCategory;
use App\Models\Product;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Menu → Sections (M50, MENU-039, P-01): names, shown or hidden, order, new sections — nothing deleted. */
class MenuSectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
    }

    private function menu(string $locale = 'en'): string
    {
        $this->app->forgetScopedInstances();

        return (string) $this->get('/'.$locale.'/jo/menu/')->assertOk()->getContent();
    }

    private function category(string $code): MenuCategory
    {
        return MenuCategory::query()->where('code', $code)->firstOrFail();
    }

    public function test_names_and_hiding_a_section(): void
    {
        $tea = $this->category('CAT-007');
        $item = (string) Product::query()->where('menu_category_id', $tea->id)->where('status', 'active')->orderBy('sort')->value('display_name_en');
        $this->get('/dashboard/data/menu/sections')->assertOk()->assertSee('أقسام المنيو')->assertSee('TEA');

        $this->put('/dashboard/data/menu/sections/'.$tea->id, ['name_en' => 'hot drinks'])->assertSessionHasErrors(['name_en'], null, 'section'.$tea->id);
        $this->put('/dashboard/data/menu/sections/'.$tea->id, ['name_en' => 'TEA', 'name_ar' => 'شاي', 'approve_ar' => '1', 'visible' => '1'])->assertSessionHasNoErrors();
        $this->assertStringContainsString('شاي', $this->menu('ar'), 'the approved Arabic name');

        $this->put('/dashboard/data/menu/sections/'.$tea->id, ['name_en' => 'TEA', 'name_ar' => 'شاي', 'approve_ar' => '1', 'visible' => '0'])->assertSessionHasNoErrors();
        $this->assertStringNotContainsString($item, $this->menu());
        $this->app->forgetScopedInstances();
        $this->assertStringContainsString(e(__('site.search.none_title', ['query' => $item], 'en')), (string) $this->get('/en/search/?q='.urlencode($item))->getContent(), 'not in the search either');
        $this->assertGreaterThan(0, Product::query()->where('menu_category_id', $tea->id)->count(), 'nothing deleted');

        $this->put('/dashboard/data/menu/sections/'.$tea->id, ['name_en' => 'TEA', 'name_ar' => 'شاي', 'approve_ar' => '1', 'visible' => '1']);
        $this->assertStringContainsString($item, $this->menu());
        $this->assertSame(3, AuditLog::query()->where('action', 'menu.section_saved')->count());
    }

    public function test_order_and_a_new_section(): void
    {
        $menu = $this->menu();
        $this->assertLessThan(strpos($menu, 'id="tea"'), strpos($menu, 'id="fizzy-drinks"'), 'the approved order (MENU-039)');
        $this->post('/dashboard/data/menu/sections/'.$this->category('CAT-007')->id.'/up')->assertSessionHas('status');
        $menu = $this->menu();
        $this->assertLessThan(strpos($menu, 'id="fizzy-drinks"'), strpos($menu, 'id="tea"'), 'moved up one place');
        $this->assertSame(0, $this->category('CAT-009')->sort, 'the season stays on top');

        $this->post('/dashboard/data/menu/sections', ['name_en' => 'BREAKFAST TRIAL', 'name_ar' => 'فطور تجريبي'])->assertSessionHasNoErrors();
        $new = MenuCategory::query()->where('name_en', 'BREAKFAST TRIAL')->firstOrFail();
        $this->assertSame('CAT-012', $new->code);
        $item = Product::query()->where('code', 'PRD-00003')->firstOrFail();
        $this->put('/dashboard/data/menu/'.$item->id.'/details', ['category' => (string) $new->id, 'visible' => '1'])->assertSessionHasNoErrors();
        $ar = $this->menu('ar');
        $this->assertStringContainsString('فطور تجريبي', $ar);
        $this->assertGreaterThan(strpos($ar, 'id="tea"'), strpos($ar, 'id="breakfast-trial"'), 'a new section goes to the end');
        $this->assertSame(1, AuditLog::query()->where('action', 'menu.section_created')->count());
    }
}
