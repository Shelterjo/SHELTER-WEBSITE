<?php

namespace Tests\Feature\Site;

use App\Enums\Availability;
use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use App\Services\Menu\MenuEditor;
use App\Services\Menu\Page\MenuItem;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Menu IA §9.6: what the menu says per branch — UNKNOWN says nothing; the Owner's branch values show (M50). */
class MenuBranchDisplayTest extends TestCase
{
    use RefreshDatabase;

    private const LABELS = ['drive' => 'DRIVE', 'house' => 'HOUSE'];

    /**
     * @param  array<string, Availability>  $availability
     * @param  array<string, int>  $branchPrices
     */
    private function item(array $availability, array $branchPrices = []): MenuItem
    {
        return new MenuItem('PRD-00001', 'x-001', 'X', null, null, null, 'X', 2000, 'JOD', false, 'CAT-001', null, 's', 'S', $availability, $branchPrices, []);
    }

    public function test_the_rules_of_section_9_6(): void
    {
        app()->setLocale('ar');
        $unknown = $this->item(['drive' => Availability::Unknown, 'house' => Availability::Unknown]);
        $this->assertFalse($unknown->variesByBranch());
        $this->assertSame(['hidden' => false, 'status' => null, 'unavailable' => false, 'price' => 2000], $unknown->display('house', self::LABELS), 'UNKNOWN: nothing');

        $only = $this->item(['drive' => Availability::Available, 'house' => Availability::UnavailableShow]);
        $this->assertSame('متوفر في DRIVE فقط', $only->display('all', self::LABELS)['status']);
        $this->assertNull($only->display('drive', self::LABELS)['status']);
        $this->assertSame(['hidden' => false, 'status' => 'غير متوفر حاليًا', 'unavailable' => true, 'price' => 2000], $only->display('house', self::LABELS));

        $halfKnown = $this->item(['drive' => Availability::Unknown, 'house' => Availability::UnavailableHide]);
        $this->assertTrue($halfKnown->display('house', self::LABELS)['hidden']);
        $this->assertSame(['hidden' => false, 'status' => null, 'unavailable' => false, 'price' => 2000], $halfKnown->display('all', self::LABELS), 'one branch unknown: no claim on "all"');

        $gone = $this->item(['drive' => Availability::UnavailableHide, 'house' => Availability::UnavailableHide]);
        $this->assertTrue($gone->display('all', self::LABELS)['hidden']);

        $priced = $this->item(['drive' => Availability::Unknown, 'house' => Availability::Unknown], ['house' => 2500]);
        $this->assertTrue($priced->variesByBranch());
        $this->assertSame(2500, $priced->display('house', self::LABELS)['price']);
        $this->assertSame(2000, $priced->display('all', self::LABELS)['price']);
    }

    public function test_the_menu_page_follows_the_owner_s_branch_values(): void
    {
        $this->seed(DatabaseSeeder::class);
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $latte = Product::query()->where('code', 'PRD-00015')->firstOrFail();
        $house = Branch::query()->where('slug', 'house')->firstOrFail();
        $drive = Branch::query()->where('slug', 'drive')->firstOrFail();
        $editor = app(MenuEditor::class);
        $editor->setBranchValues($latte, $house, $owner, 'Machine', null, Availability::UnavailableHide);
        $editor->setBranchValues($latte, $drive, $owner, 'Drive price', 2750, null);

        $all = (string) $this->get('/ar/jo/menu/')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('#<li data-ui-menu-item="p-latte-015"\s+data-ui-menu-branches="[^"]+"\s*>#', $all, 'visible on "all", with its per-branch views');
        $this->assertSame(1, substr_count($all, 'data-ui-menu-branches='), 'items that do not differ carry no extra data');

        $this->app->forgetScopedInstances();
        $atHouse = (string) $this->get('/ar/jo/menu/?branch=house')->getContent();
        $this->assertMatchesRegularExpression('#<li data-ui-menu-item="p-latte-015"[^>]*\shidden\s+data-branch-hidden="true"\s*>#', $atHouse, 'hidden at HOUSE');

        $this->app->forgetScopedInstances();
        $atDrive = (string) $this->get('/ar/jo/menu/?branch=drive')->getContent();
        preg_match('#data-ui-menu-item="p-latte-015".*?</li>#s', $atDrive, $card);
        $this->assertStringContainsString('2.75', $card[0] ?? '', 'the DRIVE price');
    }
}
