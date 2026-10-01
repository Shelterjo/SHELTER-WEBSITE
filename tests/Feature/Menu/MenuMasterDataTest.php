<?php

namespace Tests\Feature\Menu;

use App\Enums\Availability;
use App\Enums\FactSource;
use App\Enums\FactStatus;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\MenuCategory;
use App\Models\MenuSourceRow;
use App\Models\MenuSubcategory;
use App\Models\Product;
use App\Models\User;
use App\Services\MasterData\FactRegistry;
use App\Services\Menu\MenuCatalog;
use App\Services\Menu\MenuEditor;
use App\Services\Menu\ProductCodes;
use Carbon\CarbonImmutable;
use Database\Seeders\MasterDataSeeder;
use Database\Seeders\MenuSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Menu master data from the frozen inventory v1.0 (D-135) + one product identity with branch overrides (M33 §4–§5). */
class MenuMasterDataTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([MasterDataSeeder::class, MenuSeeder::class]);
        $this->owner = User::factory()->create();
    }

    private function product(string $code): Product
    {
        return Product::query()->where('code', $code)->firstOrFail();
    }

    private function branch(string $code): Branch
    {
        return Branch::query()->where('code', $code)->firstOrFail();
    }

    public function test_import_matches_the_frozen_inventory(): void
    {
        $this->assertSame(192, Product::query()->count());
        $this->assertSame(191, Product::query()->active()->count());
        $this->assertSame(192, MenuSourceRow::query()->count());
        $this->assertSame(11, MenuCategory::query()->count());
        $this->assertSame($this->product('PRD-00115')->id, $this->product('PRD-00120')->merged_into_id);
    }

    public function test_import_is_idempotent_and_never_overwrites_owner_edits(): void
    {
        $cappuccino = $this->product('PRD-00014');
        app(MenuEditor::class)->changeBasePrice($cappuccino, 2750, $this->owner, 'test', CarbonImmutable::parse('2026-11-01'));
        $this->seed(MenuSeeder::class);

        $this->assertSame(192, Product::query()->count());
        $this->assertSame(2, $cappuccino->prices()->count());
    }

    public function test_retired_ids_are_never_reused(): void
    {
        $this->assertSame('PRD-00193', app(ProductCodes::class)->next());
    }

    public function test_source_rows_are_immutable(): void
    {
        $row = MenuSourceRow::query()->firstOrFail();
        $row->source_name_en = 'CHANGED';
        $row->save();

        $this->assertNotSame('CHANGED', $row->fresh()?->source_name_en);
    }

    public function test_only_approved_arabic_names_are_returned(): void
    {
        $catalog = app(MenuCatalog::class);

        $this->assertSame('قهوة تركية سينجل', $catalog->name($this->product('PRD-00001'), 'ar')); // D-127
        $this->assertNull($catalog->name($this->product('PRD-00014'), 'ar')); // source-provided, pending (D-091)
        $this->assertSame('CAPPUCCINO', $catalog->name($this->product('PRD-00014'), 'en'));
        $this->assertSame(39, Product::query()->whereNotNull('display_name_ar')->count());
    }

    public function test_category_arabic_names_are_published_only_when_approved(): void
    {
        $this->assertSame('كيك', MenuCategory::query()->where('code', 'CAT-010')->value('name_ar')); // D-132
        $this->assertNull(MenuCategory::query()->where('code', 'CAT-001')->value('name_ar')); // P-01 pending
        $this->assertSame(0, MenuSubcategory::query()->where('status', '!=', MenuSubcategory::STATUS_PROPOSED)->count()); // P-04
    }

    public function test_price_is_effective_from_the_menu_version_date(): void
    {
        $catalog = app(MenuCatalog::class);
        $cappuccino = $this->product('PRD-00014');

        $this->assertSame('2.50', $catalog->price($cappuccino, null, CarbonImmutable::parse('2026-10-01'))?->amount());
        $this->assertNull($catalog->price($cappuccino, null, CarbonImmutable::parse('2026-09-30')));
    }

    public function test_branch_availability_is_unknown_until_confirmed(): void
    {
        $catalog = app(MenuCatalog::class);
        $drive = $this->branch('BR-DRIVE');
        $cappuccino = $this->product('PRD-00014');
        $this->assertSame(Availability::Unknown, $catalog->availability($cappuccino, $drive));

        $facts = app(FactRegistry::class);
        $fact = $facts->current(MenuCatalog::availabilityFactKey($drive));
        $this->assertNotNull($fact);
        $this->assertSame(FactStatus::Missing, $fact->status);
        $facts->approve($fact, $this->owner, 'D-TEST', replaceValue: true, value: true);

        $this->assertSame(Availability::Available, $catalog->availability($cappuccino, $drive));
    }

    public function test_override_inherit_and_reset_to_master(): void
    {
        $catalog = app(MenuCatalog::class);
        $editor = app(MenuEditor::class);
        $cappuccino = $this->product('PRD-00014');
        $house = $this->branch('BR-HOUSE');
        $drive = $this->branch('BR-DRIVE');

        $editor->overrideForBranch($cappuccino, $house, $this->owner, 'test override', priceFils: 3000, availability: Availability::UnavailableShow);

        $this->assertEquals(['3.00', true], [$catalog->price($cappuccino, $house)?->amount(), $catalog->price($cappuccino, $house)?->overridden]);
        $this->assertEquals(['2.50', false], [$catalog->price($cappuccino, $drive)?->amount(), $catalog->price($cappuccino, $drive)?->overridden]);
        $this->assertSame(Availability::UnavailableShow, $catalog->availability($cappuccino, $house));

        $editor->resetToMaster($cappuccino, $house, $this->owner, 'back to master');
        $this->assertEquals(['2.50', false], [$catalog->price($cappuccino, $house)?->amount(), $catalog->price($cappuccino, $house)?->overridden]);
        $this->assertSame(Availability::Unknown, $catalog->availability($cappuccino, $house));

        $actions = AuditLog::query()->where('subject_type', $cappuccino->getMorphClass())->where('subject_id', $cappuccino->id)->pluck('action')->all();
        $this->assertSame(['menu.branch_override_set', 'menu.branch_override_reset'], $actions);
        $this->assertSame(['website.menu', 'schema.menu'], AuditLog::query()->where('action', 'menu.branch_override_set')->value('channels_affected'));
    }

    public function test_price_change_keeps_history_and_takes_effect_on_its_date(): void
    {
        $catalog = app(MenuCatalog::class);
        $cappuccino = $this->product('PRD-00014');

        app(MenuEditor::class)->changeBasePrice($cappuccino, 2750, $this->owner, 'new menu', CarbonImmutable::parse('2026-11-01'));

        $this->assertSame('2.50', $catalog->price($cappuccino, null, CarbonImmutable::parse('2026-10-31'))?->amount());
        $this->assertSame('2.75', $catalog->price($cappuccino, null, CarbonImmutable::parse('2026-11-01'))?->amount());
        $this->assertSame(['before' => ['price_fils' => 2500], 'after' => ['price_fils' => 2750]], AuditLog::query()->where('action', 'menu.price_changed')->value('changes'));
    }

    public function test_only_the_owner_can_edit_menu_master_data(): void
    {
        $this->expectException(AuthorizationException::class);
        app(MenuEditor::class)->changeBasePrice($this->product('PRD-00014'), 2750, User::factory()->inactive()->create(), 'x');
    }

    public function test_menu_availability_facts_are_registered_as_missing(): void
    {
        $facts = app(FactRegistry::class);
        foreach (['BR-DRIVE', 'BR-HOUSE'] as $code) {
            $fact = $facts->current("menu.availability_confirmed.{$code}");
            $this->assertNotNull($fact);
            $this->assertSame(FactSource::Document, $fact->source_type);
        }
    }
}
