<?php

namespace Tests\Feature\Dashboard;

use App\Models\Branch;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Dashboard\SeoHealth;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Dashboard → Google visibility (M57 §48): read-only health from the master data and the site texts. */
class SeoHealthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_it_is_for_the_owner_only(): void
    {
        $this->get('/dashboard/seo')->assertRedirect();
    }

    public function test_it_names_each_branch_gap_and_links_to_the_fix(): void
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
        $drive = Branch::query()->where('slug', 'drive')->firstOrFail();

        $html = (string) $this->get('/dashboard/seo')->assertOk()->getContent();
        $this->assertStringContainsString('الظهور في Google', $html);
        $this->assertStringContainsString('رابط خرائط Google (زر «الاتجاهات»)', $html);
        $this->assertStringContainsString('href="http://localhost/dashboard/data/branches/'.$drive->id.'"', $html);
        $this->assertStringContainsString('غير مربوط بعد', $html, 'Search Console is said to be not connected, not faked');

        $row = collect(app(SeoHealth::class)->branches('ar'))->firstWhere('branch.slug', 'drive');
        $this->assertNotNull($row);
        $checks = $row['checks'];
        $this->assertTrue($checks['names'] && $checks['hours'] && $checks['phone']);
        $this->assertTrue($checks['maps'] && $checks['city_ar'], 'D-335 Maps link, D-334 spelling');
        $this->assertFalse($checks['address'] || $checks['landmark'] || $checks['coordinates'], 'street address and coordinates (PO-010), English location line (PO-081)');

        $this->put('/dashboard/data/branches/'.$drive->id.'/details', [
            'name_ar' => $drive->name_ar, 'name_en' => $drive->name_en, 'is_public' => '1', 'maps_url' => (string) $drive->maps_url,
            'landmark_ar' => (string) $drive->landmark_ar, 'landmark_en' => 'Test landmark wording',
        ])->assertSessionHasNoErrors();
        $this->app->forgetScopedInstances();
        $row = collect(app(SeoHealth::class)->branches('ar'))->firstWhere('branch.slug', 'drive');
        $this->assertNotNull($row);
        $checks = $row['checks'];
        $this->assertTrue($checks['landmark'], 'saving the English line clears the gap');
    }

    public function test_titles_and_descriptions_are_checked_for_length_and_repeats(): void
    {
        $rows = collect(app(SeoHealth::class)->texts());
        $this->assertCount(count(SeoHealth::PAGES) * 2, $rows);
        $this->assertSame([], $rows->pluck('issues')->flatten()->all(), 'the shipped titles and descriptions are short enough and unique');
        $branchRow = $rows->firstWhere(fn ($r) => $r['page'] === 'branch' && $r['locale'] === 'ar');
        $this->assertNotNull($branchRow);
        $this->assertSame('[اسم الفرع] — [نوع الفرع] في إربد | ساعات الدوام', $branchRow['title'], 'every placeholder is filled in the preview');
    }
}
