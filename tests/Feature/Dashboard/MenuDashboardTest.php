<?php

namespace Tests\Feature\Dashboard;

use App\Enums\Availability;
use App\Enums\NameStatus;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Media;
use App\Models\MenuSourceRow;
use App\Models\Product;
use App\Models\ProductBranchOverride;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Dashboard\MediaEditor;
use App\Services\Dashboard\MenuManager;
use App\Services\Media\MediaLibrary;
use App\Services\Menu\MenuCatalog;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Media\MediaLibraryTest;
use Tests\TestCase;

/** Dashboard → Menu (M33 §5, §16–§18, D-091/D-137, M50): prices with history, branch values, Arabic name approval. */
class MenuDashboardTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-10 12:00:00', 'Asia/Amman'));
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
        $this->product = Product::query()->where('code', 'PRD-00003')->firstOrFail(); // AMERICAN COFFEE, Arabic name pending
    }

    private function catalog(): MenuCatalog
    {
        $this->app->forgetScopedInstances();

        return app(MenuCatalog::class);
    }

    public function test_prices_are_typed_in_dinars_with_two_decimals_at_most(): void
    {
        $this->assertSame(2750, MenuManager::fils('2.75'));
        $this->assertSame(2500, MenuManager::fils('٢٫٥'));
        $this->assertSame(3000, MenuManager::fils('3'));
        $this->assertNull(MenuManager::fils('2.375'), 'the site shows two decimals: what is typed is what shows');
        $this->assertNull(MenuManager::fils('0'));
        $this->assertNull(MenuManager::fils('abc'));
    }

    public function test_a_new_price_starts_on_its_date_and_the_history_stays(): void
    {
        $url = '/dashboard/data/menu/'.$this->product->id;
        $base = $this->catalog()->basePrice($this->product)?->price_fils;
        $this->get('/dashboard/data/menu?q='.$this->product->code)->assertOk()->assertSee('AMERICAN COFFEE');
        $this->get($url)->assertOk()->assertSee('السعر الأساسي');

        $this->post($url.'/price', ['price' => '2.375', 'reason' => ''])->assertSessionHasErrors(['price', 'reason'], null, 'price');
        $this->post($url.'/price', ['price' => MenuManager::dinars((int) $base), 'reason' => 'x'])->assertSessionHasErrors(['price'], null, 'price');
        $this->post($url.'/price', ['price' => '2.75', 'starts_on' => '2026-10-01', 'reason' => 'x'])->assertSessionHasErrors(['starts_on'], null, 'price');

        $this->post($url.'/price', ['price' => '2.75', 'reason' => 'New beans'])->assertSessionHasNoErrors();
        $this->assertSame(2750, $this->catalog()->price($this->product->refresh())?->fils);
        $this->post($url.'/price', ['price' => '2.80', 'reason' => 'Typo'])->assertSessionHasNoErrors();
        $this->assertSame(2800, $this->catalog()->price($this->product->refresh())?->fils, 'a same-day correction replaces it');
        $this->assertSame(3, $this->product->prices()->count(), 'nothing is overwritten');
        $this->get($url)->assertSee('استُبدل في نفس اليوم');

        $this->post($url.'/price', ['price' => '3.00', 'starts_on' => '2026-11-01', 'reason' => 'November'])->assertSessionHasNoErrors();
        $this->assertSame(2800, $this->catalog()->price($this->product->refresh())?->fils, 'a future price waits for its date');
        $this->assertSame(3000, $this->catalog()->price($this->product, null, CarbonImmutable::parse('2026-11-02'))?->fils);
        $this->assertSame('New beans', AuditLog::query()->where('action', 'menu.price_changed')->orderBy('id')->firstOrFail()->meta['reason'] ?? null);
    }

    public function test_each_branch_has_its_own_price_and_availability_or_follows_the_base(): void
    {
        $house = Branch::query()->where('code', 'BR-HOUSE')->firstOrFail();
        $url = '/dashboard/data/menu/'.$this->product->id.'/branches/'.$house->id;
        $base = (int) $this->catalog()->basePrice($this->product)?->price_fils;

        $this->put($url, ['price' => '3.00', 'availability' => 'sold_out', 'reason' => ''])->assertSessionHasErrors(['availability', 'reason'], null, 'b'.$house->id);
        $this->put($url, ['price' => '3.00', 'availability' => 'unavailable_show', 'reason' => 'Machine'])->assertSessionHasNoErrors();
        $this->assertSame(3000, $this->catalog()->price($this->product->refresh(), $house)?->fils);
        $this->assertSame(Availability::UnavailableShow, $this->catalog()->availability($this->product, $house));
        $this->get('/dashboard/data/menu/'.$this->product->id)->assertSee('الموقع: «غير متوفر حاليًا».', false);

        $this->put($url, ['price' => MenuManager::dinars($base), 'availability' => 'inherit', 'reason' => 'Fixed'])->assertSessionHasNoErrors();
        $this->assertSame(0, ProductBranchOverride::query()->count(), 'same as the base = follows the base');
        $this->assertSame(Availability::Unknown, $this->catalog()->availability($this->product->refresh(), $house), 'unknown again: nothing claimed');
        $this->assertSame(['menu.branch_override_set', 'menu.branch_override_reset'], AuditLog::query()->where('action', 'like', 'menu.branch%')->orderBy('id')->pluck('action')->all());
    }

    public function test_an_arabic_name_shows_only_once_the_owner_approves_it(): void
    {
        $url = '/dashboard/data/menu/'.$this->product->id;
        $source = MenuSourceRow::query()->where('product_id', $this->product->id)->value('source_name_ar');
        $this->get($url)->assertSee((string) $source);
        $this->assertNull($this->catalog()->name($this->product, 'ar'));

        $this->put($url.'/names', ['name_en' => 'AMERICAN COFFEE', 'name_ar' => '', 'approve_ar' => '1'])->assertSessionHasErrors(['name_ar'], null, 'names');
        $this->put($url.'/names', ['name_en' => 'AMERICAN COFFEE', 'name_ar' => 'قهوة أمريكية', 'approve_ar' => '1'])->assertSessionHasNoErrors();
        $this->assertSame('قهوة أمريكية', $this->catalog()->name($this->product->refresh(), 'ar'));
        $this->assertStringContainsString('قهوة أمريكية', (string) $this->get('/ar/jo/menu/')->getContent());
        $this->assertSame($source, MenuSourceRow::query()->where('product_id', $this->product->id)->value('source_name_ar'), 'the source file never changes');

        $this->put($url.'/names', ['name_en' => 'AMERICAN COFFEE', 'name_ar' => 'قهوة أمريكية']);
        $this->assertNull($this->catalog()->name($this->product->refresh(), 'ar'), 'approval withdrawn → English only');
    }

    public function test_a_whole_category_is_reviewed_at_once(): void
    {
        $category = $this->product->category;
        $pending = Product::query()->where('menu_category_id', $category->id)->where('status', 'active')->get()
            ->filter(fn (Product $p): bool => NameStatus::fromInventory($p->name_ar_status) !== NameStatus::Approved)->take(2)->values();
        $this->assertCount(2, $pending);
        [$first, $second] = $pending->all();
        $this->get('/dashboard/data/menu/review/'.$category->id)->assertOk()->assertSee('اعتمد');

        $this->put('/dashboard/data/menu/review/'.$category->id, ['names' => [$first->id => ''], 'approve' => [$first->id => '1']])
            ->assertSessionHasErrors(['names.'.$first->id]);
        $this->put('/dashboard/data/menu/review/'.$category->id, [
            'names' => [$first->id => 'اسم أول', $second->id => 'اسم ثانٍ'],
            'approve' => [$first->id => '1', $second->id => '1'],
        ])->assertSessionHas('status', 'اعتُمدت 2 أسماء.');
        $this->assertSame('اسم أول', $this->catalog()->name($first->refresh(), 'ar'));
        $this->assertSame('اسم ثانٍ', $this->catalog()->name($second->refresh(), 'ar'));
    }

    private function card(string $locale, string $anchor): ?string
    {
        $this->app->forgetScopedInstances();
        $html = (string) $this->get('/'.$locale.'/jo/menu/')->assertOk()->getContent();

        return preg_match('#<li data-ui-menu-item="'.$anchor.'".*?</li>#s', $html, $m) === 1 ? $m[0] : null;
    }

    public function test_item_details_reach_the_card_and_the_item_window(): void
    {
        Storage::fake('media');
        Storage::fake('media_public');
        $url = '/dashboard/data/menu/'.$this->product->id.'/details';
        $anchor = 'p-american-coffee-003';

        $this->put($url, ['visible' => '1', 'description_ar' => 'قهوة سوداء.'])->assertSessionHasErrors(['description_en'], null, 'details');
        $this->put($url, ['visible' => '1', 'is_new' => '1', 'new_until' => '2026-10-01'])->assertSessionHasErrors(['new_until'], null, 'details');
        $image = app(MediaLibrary::class)->import(MediaLibraryTest::imageFile(seed: 3), ['source' => 'shelter', 'people_consent' => 'none']);
        $this->put($url, ['visible' => '1', 'media_id' => (string) $image->id])->assertSessionHasErrors(['media_id'], null, 'details');

        $image->forceFill(['approval_status' => Media::APPROVED, 'ok_website' => true, 'alt_ar' => 'فنجان قهوة', 'alt_en' => 'A cup of coffee'])->save();
        app(MediaLibrary::class)->generateVariants($image);
        $this->put($url, [
            'visible' => '1', 'description_ar' => 'قهوة سوداء.', 'description_en' => 'Black coffee.', 'media_id' => (string) $image->id,
            'is_new' => '1', 'new_until' => '2026-10-20', 'is_featured' => '1', 'sort' => '1', 'aria_label_en' => 'American coffee',
        ])->assertSessionHasNoErrors();
        $card = (string) $this->card('en', $anchor);
        $this->assertStringContainsString('data-description="Black coffee."', $card);
        $this->assertStringContainsString('<picture', $card);
        $this->assertStringContainsString('aria-label="American coffee"', $card);
        $this->assertMatchesRegularExpression('#<span class="ui-badge">\s*New\s*</span>#', $card, 'normal case; the badge style shows it in capitals (copy audit F56)');
        $this->assertStringNotContainsStringIgnoringCase('featured', $card, 'internal only');
        $used = app(MediaEditor::class)->usedIn($image, 'en');
        $this->assertCount(1, array_filter($used, fn (string $line): bool => str_contains($line, 'AMERICAN COFFEE')), 'the media library says where the image is used');

        $this->travelTo(CarbonImmutable::parse('2026-10-21 09:00', 'Asia/Amman'));
        $this->assertDoesNotMatchRegularExpression('#<span class="ui-badge">\s*New\s*</span>#', (string) $this->card('en', $anchor), 'the badge ends by itself');

        $this->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]); // 11 days later
        $this->put($url, ['visible' => '0'])->assertRedirect('/dashboard/data/menu/'.$this->product->id.'#details');
        $this->assertNull($this->card('ar', $anchor), 'hidden from the menu');
        $this->get('/dashboard/data/menu?q=AMERICAN')->assertSee('مخفي من المنيو');
        $this->put($url, ['visible' => '1']);
        $this->assertNotNull($this->card('ar', $anchor));
    }

    public function test_a_category_arabic_name_shows_only_once_approved(): void
    {
        $category = $this->product->category;
        $url = '/dashboard/data/menu/review/'.$category->id.'/name';
        $this->assertStringNotContainsString('مشروبات ساخنة', (string) $this->get('/ar/jo/menu/')->getContent());
        $this->put($url, ['category_name_ar' => '', 'approve_category' => '1'])->assertSessionHasErrors(['category_name_ar'], null, 'category');
        $this->put($url, ['category_name_ar' => 'مشروبات ساخنة', 'approve_category' => '1'])->assertSessionHasNoErrors();
        $this->app->forgetScopedInstances();
        $this->assertStringContainsString('مشروبات ساخنة', (string) $this->get('/ar/jo/menu/')->getContent());
        $this->put($url, ['category_name_ar' => 'مشروبات ساخنة']);
        $this->assertNull($category->refresh()->name_ar, 'approval withdrawn → nothing in the display column');
    }

    public function test_changing_prices_needs_a_fresh_confirmation(): void
    {
        $this->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->subMinutes(30)->getTimestamp()]);
        $this->get('/dashboard/data/menu')->assertOk();
        $this->get('/dashboard/data/menu/'.$this->product->id)->assertRedirect('/dashboard/confirm');
        $this->post('/dashboard/data/menu/'.$this->product->id.'/price', ['price' => '9.00', 'reason' => 'x'])->assertRedirect('/dashboard/confirm');
    }
}
