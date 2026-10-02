<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\ContentVersion;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Dashboard\BranchEditor;
use App\Services\MasterData\FactRegistry;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\PublishesBranchDetails;
use Tests\TestCase;

/**
 * Dashboard → Branches → details (M50, PO-010, BRANCH-010): names, address, Maps link, coordinates, shown or hidden,
 * services — previewed as visitors will see them (card and page, both languages, nothing saved), then published.
 */
class BranchDetailsTest extends TestCase
{
    use PublishesBranchDetails;
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
        return $overrides + ['name_ar' => (string) $this->branch->name_ar, 'name_en' => (string) $this->branch->name_en, 'is_public' => '1',
            'maps_url' => (string) $this->branch->maps_url, 'landmark_ar' => (string) $this->branch->landmark_ar];
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

    public function test_the_address_map_and_services_reach_the_branch_page_once_published(): void
    {
        $url = '/dashboard/data/branches/'.$this->branch->id;
        $this->get($url)->assertOk()->assertSee('بيانات الفرع')->assertSee('رابط خرائط Google')->assertSee('معاينة');
        $ar = $this->page('/ar/jo/locations/irbid/drive/');
        $this->assertStringContainsString('href="https://maps.app.goo.gl/zNfDbkxcT1aMdQiWA"', $ar, 'the approved Maps link (D-336)');
        $this->assertStringNotContainsString('شارع تجريبي', $ar, 'no street address before the Owner saves one (PO-010)');

        $this->put($url.'/details', $this->form(['name_en' => '', 'maps_url' => 'https://example.com/x', 'latitude' => '32.5']))
            ->assertSessionHasErrors(['name_en', 'maps_url', 'longitude'], null, 'details');
        $this->publishBranchDetails($this->branch, $this->form([
            'address_ar' => 'إربد، شارع تجريبي، قرب معلم تجريبي', 'maps_url' => 'https://maps.app.goo.gl/Sample123',
            'latitude' => '32.5556', 'longitude' => '35.85', 'attributes' => ['service' => ['wifi' => 'yes', 'parking' => 'no'], 'payment' => ['cash' => 'yes']],
        ]))->assertRedirect($url.'#details')->assertSessionHas('status', 'نُشرت بيانات الفرع.');

        $ar = $this->page('/ar/jo/locations/irbid/drive/');
        $this->assertStringContainsString('إربد، شارع تجريبي، قرب معلم تجريبي', $ar);
        $this->assertStringContainsString('href="https://maps.app.goo.gl/Sample123"', $ar);
        $this->assertStringNotContainsString('zNfDbkxcT1aMdQiWA', $ar, 'the published link replaces the old one');
        $this->assertStringContainsString('واي فاي', $ar);
        $this->assertStringContainsString('نقدًا', $ar);
        $this->assertStringNotContainsString('مواقف سيارات', $ar, '"no" is not shown');
        $this->assertStringContainsString('"@type":"PostalAddress"', $ar);
        $this->assertStringContainsString('"@type":"GeoCoordinates"', $ar);
        $en = $this->page('/en/jo/locations/irbid/drive/');
        $this->assertStringNotContainsString('شارع تجريبي', $en, 'each language its own address');
        $this->assertStringContainsString('Directions', $en);
        $this->assertSame(1, AuditLog::query()->where('action', 'branch.details_saved')->count());
        $this->assertSame('APPROVED', app(FactRegistry::class)->current($this->branch->factKey('address_ar'))?->status->value, 'publishing approves it');
    }

    public function test_the_preview_shows_the_card_and_the_page_in_both_languages_and_saves_nothing(): void
    {
        $before = $this->branch->only(BranchEditor::FIELDS);
        $html = (string) $this->put('/dashboard/data/branches/'.$this->branch->id.'/details', $this->form([
            'name_en' => 'SHELTER COFFEE DRIVE PREVIEW', 'address_en' => 'Sample street, Irbid', 'landmark_en' => 'Next to a sample landmark',
            'attributes' => ['service' => ['wifi' => 'yes']], 'action' => 'preview',
        ]))->assertOk()->getContent();

        $this->assertStringContainsString('هكذا سيراها الزبائن', $html);
        // The site's own card in each language, each in its own frame with its language and direction.
        $this->assertStringContainsString('lang="ar" dir="rtl"', $html);
        $this->assertStringContainsString('lang="en" dir="ltr"', $html);
        $this->assertSame(2, substr_count($html, 'class="ui-branch ui-branch--panel"'), 'the locations-page card, once per language');
        $this->assertStringContainsString('SHELTER COFFEE DRIVE PREVIEW', $html);
        $this->assertStringContainsString('Drive-thru in Irbid · Next to a sample landmark', $html, 'the card line as the English card draws it');
        $this->assertStringContainsString('Sample street, Irbid', $html);
        $this->assertStringContainsString('Address and services', $html, 'the page part in English, in English');
        $this->assertStringContainsString('Wi-Fi', $html);
        $this->assertStringContainsString('واي فاي', $html);
        // What changes, by field name, so the Owner checks exactly those.
        $this->assertStringContainsString('الاسم بالإنجليزي', $html);
        $this->assertStringContainsString('العنوان بالإنجليزي', $html);
        $this->assertStringContainsString('name="previewed"', $html);
        $this->assertStringContainsString('انشر بيانات الفرع', $html);
        $this->assertStringContainsString('value="SHELTER COFFEE DRIVE PREVIEW"', $html, 'the form keeps what was typed');
        $this->assertStringContainsString('/details/preview/en', $html, 'the full page, in a new tab');

        $this->assertSame($before, $this->branch->refresh()->only(BranchEditor::FIELDS), 'a preview saves nothing');
        $this->assertSame(0, AuditLog::query()->where('action', 'branch.details_saved')->count());
        $this->assertSame(0, ContentVersion::query()->where('versionable_type', $this->branch->getMorphClass())->count());
        $this->assertStringNotContainsString('PREVIEW', $this->page('/en/jo/locations/irbid/drive/'), 'visitors still see the published name');
    }

    public function test_publishing_needs_the_preview_of_the_same_details(): void
    {
        $url = '/dashboard/data/branches/'.$this->branch->id.'/details';
        $form = $this->form(['address_ar' => 'عنوان تجريبي']);
        // No preview at all: the request is a preview, nothing is published.
        $this->put($url, $form + ['action' => 'publish'])->assertOk()->assertSee('تغيّرت البيانات بعد المعاينة');
        $this->assertNull($this->branch->refresh()->address_ar);

        $preview = (string) $this->put($url, $form + ['action' => 'preview'])->getContent();
        preg_match('/name="previewed" value="([a-f0-9]{64})"/', $preview, $m);
        $fingerprint = $m[1] ?? '';
        $this->assertNotSame('', $fingerprint);
        // Something changed after the preview (another value, another answer, hidden): refused, nothing saved.
        foreach ([['address_ar' => 'عنوان آخر'], ['attributes' => ['payment' => ['card' => 'yes']]], ['is_public' => '0']] as $change) {
            $this->put($url, $change + $form + ['action' => 'publish', 'previewed' => $fingerprint])->assertOk()->assertSee('تغيّرت البيانات بعد المعاينة');
            $this->assertNull($this->branch->refresh()->address_ar);
            $this->assertTrue($this->branch->is_public);
        }

        $this->put($url, $form + ['action' => 'publish', 'previewed' => $fingerprint, 'reason' => 'عنوان جديد'])->assertRedirect();
        $this->assertSame('عنوان تجريبي', $this->branch->refresh()->address_ar);
        $this->assertSame(['reason' => 'عنوان جديد'], AuditLog::query()->where('action', 'branch.details_saved')->sole()->meta);
    }

    public function test_the_full_page_preview_is_the_site_page_noindexed_unmeasured_and_owner_only(): void
    {
        $url = '/dashboard/data/branches/'.$this->branch->id.'/details/preview/en';
        $form = $this->form(['name_en' => 'SHELTER COFFEE DRIVE PREVIEW', 'address_en' => 'Sample street, Irbid']);
        $response = $this->put($url, $form)->assertOk();
        $html = (string) $response->getContent();
        $this->assertStringContainsString('<html lang="en" dir="ltr">', $html, 'the English page, left to right');
        $this->assertStringContainsString('<h1 class="ui-page-intro__title">SHELTER COFFEE DRIVE PREVIEW</h1>', $html);
        $this->assertStringContainsString('<p>Sample street, Irbid</p>', $html);
        $this->assertStringContainsString('Preview — not published yet', $html);
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $html);
        $this->assertStringContainsString('noindex', (string) $response->headers->get('X-Robots-Tag'));
        $this->assertStringNotContainsString('application/ld+json', $html, 'no structured data for values nobody published');
        $this->assertStringNotContainsString('rel="canonical"', $html);
        $this->assertStringNotContainsString('data-track-view', $html, 'a preview is never measured as a visit');
        $this->assertNull($this->branch->refresh()->address_en, 'nothing saved');

        $hidden = (string) $this->put('/dashboard/data/branches/'.$this->branch->id.'/details/preview/ar', $this->form(['is_public' => '0']))->assertOk()->getContent();
        $this->assertStringContainsString('<html lang="ar" dir="rtl">', $hidden);
        $this->assertStringContainsString('عند النشر لا تفتح هذه الصفحة للزوار', $hidden);
        $this->put($url, $this->form(['name_en' => '']))->assertSessionHasErrors(['name_en'], null, 'details');
        $this->put('/dashboard/data/branches/'.$this->branch->id.'/details/preview/fr', $form)->assertNotFound();

        // Owner only, with a fresh confirmation, like every branch edit.
        $this->withSession([OwnerSession::CONFIRMED_AT => now()->subDay()->getTimestamp()]);
        $this->put($url, $form)->assertRedirect('/dashboard/confirm');
        auth()->logout();
        $this->put($url, $form)->assertRedirect('/dashboard/login');
    }

    public function test_a_hidden_branch_leaves_the_site_and_comes_back(): void
    {
        $preview = (string) $this->put('/dashboard/data/branches/'.$this->branch->id.'/details', $this->form(['is_public' => '0', 'action' => 'preview']))->getContent();
        $this->assertStringContainsString('عند النشر تختفي البطاقة من صفحة الفروع', $preview, 'the preview says so before publishing');
        $this->publishBranchDetails($this->branch, $this->form(['is_public' => '0', 'reason' => 'إغلاق تجريبي']))->assertSessionHasNoErrors();
        $this->assertStringNotContainsString('/ar/jo/locations/irbid/drive/', $this->page('/ar/jo/locations/'));
        $this->app->forgetScopedInstances();
        $this->get('/ar/jo/locations/irbid/drive/')->assertNotFound();
        $this->get('/dashboard/data/branches')->assertSee('مخفي من الموقع');

        $this->publishBranchDetails($this->branch, $this->form(['is_public' => '1']));
        $this->assertStringContainsString('/ar/jo/locations/irbid/drive/', $this->page('/ar/jo/locations/'));
    }
}
