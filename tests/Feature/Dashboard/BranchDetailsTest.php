<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Dashboard\BranchEditor;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Dashboard → Branches → details (M50, PO-010): names, address, Maps link, coordinates, shown or hidden, services. */
class BranchDetailsTest extends TestCase
{
    use RefreshDatabase;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
        $this->branch = Branch::query()->where('slug', 'drive')->firstOrFail();
    }

    private function page(string $url): string
    {
        $this->app->forgetScopedInstances();

        return (string) $this->get($url)->assertOk()->getContent();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function form(array $overrides = []): array
    {
        return $overrides + ['name_ar' => (string) $this->branch->name_ar, 'name_en' => (string) $this->branch->name_en, 'is_public' => '1'];
    }

    public function test_only_google_maps_links_are_accepted(): void
    {
        $this->assertTrue(BranchEditor::mapsLink('https://maps.app.goo.gl/AbC123'));
        $this->assertTrue(BranchEditor::mapsLink('https://www.google.com/maps/place/SHELTER'));
        $this->assertTrue(BranchEditor::mapsLink('https://maps.app.goo.gl/zNfDbkxcT1aMdQiWA'), 'Google\'s newer Share link');
        $this->assertFalse(BranchEditor::mapsLink('https://share.google/a/b?x=1'), 'only the bare share code');
        $this->assertFalse(BranchEditor::mapsLink('http://maps.app.goo.gl/AbC123'), 'https only');
        $this->assertFalse(BranchEditor::mapsLink('https://www.google.com/search?q=x'));
        $this->assertFalse(BranchEditor::mapsLink('https://evil.example/maps'));
    }

    public function test_the_address_map_and_services_reach_the_branch_page_once_saved(): void
    {
        $url = '/dashboard/data/branches/'.$this->branch->id;
        $this->get($url)->assertOk()->assertSee('بيانات الفرع')->assertSee('رابط خرائط Google');
        $ar = $this->page('/ar/jo/locations/irbid/drive/');
        $this->assertStringContainsString('href="https://maps.app.goo.gl/zNfDbkxcT1aMdQiWA"', $ar, 'the approved Maps link (D-336)');
        $this->assertStringNotContainsString('شارع تجريبي', $ar, 'no street address before the Owner saves one (PO-010)');

        $this->put($url.'/details', $this->form(['name_en' => '', 'maps_url' => 'https://example.com/x', 'latitude' => '32.5']))
            ->assertSessionHasErrors(['name_en', 'maps_url', 'longitude'], null, 'details');
        $this->put($url.'/details', $this->form([
            'address_ar' => 'إربد، شارع تجريبي، قرب معلم تجريبي', 'maps_url' => 'https://maps.app.goo.gl/Sample123',
            'latitude' => '32.5556', 'longitude' => '35.85', 'attributes' => ['service' => ['wifi' => 'yes', 'parking' => 'no'], 'payment' => ['cash' => 'yes']],
        ]))->assertSessionHasNoErrors();

        $ar = $this->page('/ar/jo/locations/irbid/drive/');
        $this->assertStringContainsString('إربد، شارع تجريبي، قرب معلم تجريبي', $ar);
        $this->assertStringContainsString('href="https://maps.app.goo.gl/Sample123"', $ar);
        $this->assertStringNotContainsString('zNfDbkxcT1aMdQiWA', $ar, 'the saved link replaces the old one');
        $this->assertStringContainsString('واي فاي', $ar);
        $this->assertStringContainsString('نقدًا', $ar);
        $this->assertStringNotContainsString('مواقف سيارات', $ar, '"no" is not shown');
        $this->assertStringContainsString('"@type":"PostalAddress"', $ar);
        $this->assertStringContainsString('"@type":"GeoCoordinates"', $ar);
        $en = $this->page('/en/jo/locations/irbid/drive/');
        $this->assertStringNotContainsString('شارع تجريبي', $en, 'each language its own address');
        $this->assertStringContainsString('Directions', $en);
        $this->assertSame(1, AuditLog::query()->where('action', 'branch.details_saved')->count());
    }

    public function test_a_hidden_branch_leaves_the_site_and_comes_back(): void
    {
        $this->put('/dashboard/data/branches/'.$this->branch->id.'/details', $this->form(['is_public' => '0', 'reason' => 'إغلاق تجريبي']))->assertSessionHasNoErrors();
        $this->assertStringNotContainsString('/ar/jo/locations/irbid/drive/', $this->page('/ar/jo/locations/'));
        $this->app->forgetScopedInstances();
        $this->get('/ar/jo/locations/irbid/drive/')->assertNotFound();
        $this->get('/dashboard/data/branches')->assertSee('مخفي من الموقع');

        $this->put('/dashboard/data/branches/'.$this->branch->id.'/details', $this->form(['is_public' => '1']));
        $this->assertStringContainsString('/ar/jo/locations/irbid/drive/', $this->page('/ar/jo/locations/'));
    }
}
