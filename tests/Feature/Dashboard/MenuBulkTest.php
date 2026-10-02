<?php

namespace Tests\Feature\Dashboard;

use App\Enums\Availability;
use App\Enums\PublishStatus;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\MenuCategory;
use App\Models\Product;
use App\Models\ProductBranchOverride;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Dashboard → Menu, several items at once (MENU-062): one confirmation, each item keeps its history, no bulk prices. */
class MenuBulkTest extends TestCase
{
    use RefreshDatabase;

    /** @var list<int> */
    private array $ids;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-10 12:00:00', 'Asia/Amman'));
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
        $this->ids = array_values(array_map('intval', Product::query()->whereIn('code', ['PRD-00003', 'PRD-00004'])->orderBy('code')->pluck('id')->all()));
    }

    private function menu(): string
    {
        $this->app->forgetScopedInstances();

        return (string) $this->get('/en/jo/menu/')->assertOk()->getContent();
    }

    public function test_hide_and_show_several_items_after_one_confirmation(): void
    {
        $this->get('/dashboard/data/menu')->assertOk()->assertSee('data-select-item', false)->assertSee('مع الأصناف المحددة');
        $this->post('/dashboard/data/menu/bulk', ['action' => 'hide'])->assertSessionHas('warning');
        $this->post('/dashboard/data/menu/bulk', ['ids' => $this->ids])->assertSessionHas('warning');
        $this->post('/dashboard/data/menu/bulk', ['action' => 'hide', 'ids' => $this->ids])->assertOk()
            ->assertSee('سيُخفى صنفان من المنيو والبحث (لا يُحذفان):')->assertSee('AMERICAN COFFEE');
        $this->assertStringContainsString('AMERICAN COFFEE', $this->menu(), 'nothing changes before the confirmation');

        $this->post('/dashboard/data/menu/bulk/apply', ['action' => 'hide', 'ids' => [...$this->ids, 999999]])
            ->assertRedirect('/dashboard/data/menu')->assertSessionHas('status', 'تغيّر صنفان.');
        $this->assertStringNotContainsString('AMERICAN COFFEE', $this->menu());
        $this->assertSame(2, Product::query()->whereIn('id', $this->ids)->where('publish_status', PublishStatus::Archived->value)->count(), 'hidden, not deleted');
        $this->post('/dashboard/data/menu/bulk/apply', ['action' => 'hide', 'ids' => $this->ids])->assertSessionHas('status', 'لم يتغير شيء — الأصناف كانت كذلك أصلًا.');

        $this->post('/dashboard/data/menu/bulk/apply', ['action' => 'show', 'ids' => $this->ids])->assertSessionHas('status', 'تغيّر صنفان.');
        $this->assertStringContainsString('AMERICAN COFFEE', $this->menu());
        $this->assertSame(4, AuditLog::query()->where('action', 'menu.details_saved')->count(), 'each item keeps its own history');
        $this->assertSame(['selected' => 2, 'changed' => 2], array_intersect_key(
            AuditLog::query()->where('action', 'menu.bulk_changed')->orderByDesc('id')->firstOrFail()->meta ?? [], ['selected' => 1, 'changed' => 1]));
    }

    public function test_move_into_the_season_and_set_a_branch_availability_keeping_its_price(): void
    {
        $season = MenuCategory::query()->where('type', 'seasonal')->firstOrFail();
        $this->post('/dashboard/data/menu/bulk/apply', ['action' => 'move', 'ids' => $this->ids])->assertSessionHasErrors(['category'], null, 'bulk');
        $this->post('/dashboard/data/menu/bulk/apply', ['action' => 'move', 'ids' => $this->ids, 'category' => (string) $season->id])->assertSessionHas('status');
        $this->assertSame(2, Product::query()->whereIn('id', $this->ids)->where('menu_category_id', $season->id)->where('is_seasonal', true)->count());

        $branch = Branch::query()->whereNull('archived_at')->orderBy('sort')->firstOrFail();
        ProductBranchOverride::query()->create(['product_id' => $this->ids[0], 'branch_id' => $branch->id, 'price_fils' => 2250, 'reason' => 'seed']);
        $this->post('/dashboard/data/menu/bulk', ['action' => 'branch', 'ids' => $this->ids])->assertOk()->assertSee('سعر الفرع يبقى');
        $this->post('/dashboard/data/menu/bulk/apply', ['action' => 'branch', 'ids' => $this->ids, 'branch' => (string) $branch->id, 'state' => 'unavailable_show'])
            ->assertSessionHasErrors(['reason'], null, 'bulk');
        $this->post('/dashboard/data/menu/bulk/apply', ['action' => 'branch', 'ids' => $this->ids, 'branch' => (string) $branch->id, 'state' => 'unavailable_show', 'reason' => 'نفد مؤقتًا'])
            ->assertSessionHas('status', 'تغيّر صنفان.');
        $first = ProductBranchOverride::query()->where(['product_id' => $this->ids[0], 'branch_id' => $branch->id])->firstOrFail();
        $this->assertSame([2250, Availability::UnavailableShow], [$first->price_fils, $first->availability], 'its branch price stays');
        $this->assertSame(2, ProductBranchOverride::query()->where('branch_id', $branch->id)->whereIn('product_id', $this->ids)->where('availability', 'unavailable_show')->count());
    }
}
