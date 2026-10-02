<?php

namespace Tests\Feature\Site;

use App\Enums\HoursExceptionKind;
use App\Enums\PublishStatus;
use App\Models\Branch;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\MasterData\FactRegistry;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * M57 local SEO: every value a search engine reads about a branch comes from the master data and shows only once
 * approved — the kind of branch and its city, the location description, the hours including exception days — and
 * each indexable page has its own title naming the brand and the city. Measurement hooks are markup only.
 */
class LocalSeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function page(string $url): string
    {
        $this->app->forgetScopedInstances();

        return (string) $this->get($url)->assertOk()->getContent();
    }

    /** @return list<array<string, mixed>> */
    private function jsonLd(string $html): array
    {
        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $m);

        return array_map(fn (string $json): array => json_decode($json, true, flags: JSON_THROW_ON_ERROR), $m[1]);
    }

    private function owner(): User
    {
        return User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
    }

    public function test_the_kind_of_branch_and_its_city_show_and_the_arabic_city_waits_for_its_spelling(): void
    {
        $en = $this->page('/en/jo/locations/irbid/house/');
        $this->assertStringContainsString('<p class="ui-page-intro__lead">Coffee house in Irbid</p>', $en);
        $this->assertStringContainsString('<title>SHELTER COFFEE HOUSE — Coffee house in Irbid | Opening Hours</title>', $en);

        // CF-M-036: the Arabic spelling of the city is the Owner's to approve; until then only the kind shows.
        $ar = $this->page('/ar/jo/locations/irbid/drive/');
        $this->assertStringContainsString('<p class="ui-page-intro__lead">درايف ثرو</p>', $ar);
        $this->assertStringNotContainsString('<p class="ui-page-intro__lead">درايف ثرو في', $ar);

        $facts = app(FactRegistry::class);
        $fact = $facts->current('city.irbid.name_ar');
        $this->assertNotNull($fact);
        $facts->approve($fact, $this->owner(), 'D-TEST');
        $this->assertStringContainsString('<p class="ui-page-intro__lead">درايف ثرو في إربد</p>', $this->page('/ar/jo/locations/irbid/drive/'));
        $this->assertStringContainsString('درايف ثرو في إربد', $this->page('/ar/jo/locations/'), 'the card says it too');
    }

    public function test_the_location_description_is_missing_until_the_owner_saves_it(): void
    {
        $branch = Branch::query()->where('slug', 'house')->firstOrFail();
        $facts = app(FactRegistry::class);
        foreach (['landmark_ar', 'landmark_en'] as $field) {
            $this->assertSame('MISSING', $facts->current($branch->factKey($field))?->status->value, $field.' is recorded as missing, not invented');
        }
        $this->assertStringNotContainsString('Test landmark', $this->page('/en/jo/locations/irbid/house/'));

        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->actingAs($this->owner())->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
        $this->get('/dashboard/data/branches/'.$branch->id)->assertOk()->assertSee('وصف الموقع بالعربي');
        $this->put('/dashboard/data/branches/'.$branch->id.'/details', [
            'name_ar' => $branch->name_ar, 'name_en' => $branch->name_en, 'is_public' => '1',
            'landmark_ar' => 'معلم تجريبي، الطابق التجريبي', 'landmark_en' => 'Test landmark, test floor',
        ])->assertSessionHasNoErrors();

        $en = $this->page('/en/jo/locations/irbid/house/');
        $this->assertStringContainsString('<p>Test landmark, test floor</p>', $en);
        $this->assertStringContainsString('Test landmark, test floor', $this->page('/en/jo/locations/'));
        $this->assertStringContainsString('معلم تجريبي، الطابق التجريبي', $this->page('/ar/jo/locations/irbid/house/'));
        // The landmark never pretends to be a street address.
        $shop = collect($this->jsonLd($en))->firstWhere('@type', 'CafeOrCoffeeShop');
        $this->assertNotNull($shop);
        $this->assertArrayNotHasKey('streetAddress', $shop['address']);
    }

    public function test_branch_schema_links_the_organization_and_carries_the_exception_days_ahead(): void
    {
        $branch = Branch::query()->where('slug', 'drive')->firstOrFail();
        $tomorrow = CarbonImmutable::now('Asia/Amman')->addDay()->format('Y-m-d');
        $later = CarbonImmutable::now('Asia/Amman')->addDays(3)->format('Y-m-d');
        $branch->hoursExceptions()->create(['kind' => HoursExceptionKind::Holiday, 'starts_on' => $tomorrow, 'ends_on' => $tomorrow, 'is_closed' => true, 'status' => PublishStatus::Published]);
        $branch->hoursExceptions()->create(['kind' => HoursExceptionKind::Special, 'starts_on' => $later, 'ends_on' => $later, 'is_closed' => false,
            'opens_at' => '10:00', 'closes_at' => '23:00', 'status' => PublishStatus::Published]);
        $branch->hoursExceptions()->create(['kind' => HoursExceptionKind::Emergency, 'starts_on' => $later, 'ends_on' => $later, 'is_closed' => true, 'status' => PublishStatus::Draft]);

        $shop = collect($this->jsonLd($this->page('/en/jo/locations/irbid/drive/')))->firstWhere('@type', 'CafeOrCoffeeShop');
        $this->assertNotNull($shop);
        $this->assertSame('http://localhost/en/jo/locations/irbid/drive/#branch', $shop['@id']);
        $this->assertSame('http://localhost/#organization', $shop['parentOrganization']['@id']);
        $special = array_values(array_filter($shop['openingHoursSpecification'], fn (array $s): bool => isset($s['validFrom'])));
        $this->assertSame([
            ['@type' => 'OpeningHoursSpecification', 'opens' => '00:00', 'closes' => '00:00', 'validFrom' => $tomorrow, 'validThrough' => $tomorrow],
            ['@type' => 'OpeningHoursSpecification', 'opens' => '10:00', 'closes' => '23:00', 'validFrom' => $later, 'validThrough' => $later],
        ], $special, 'closed holiday, then special hours; a draft emergency changes nothing');
    }

    public function test_home_and_gateway_carry_the_website_and_the_one_organization(): void
    {
        foreach (['/', '/ar/', '/en/'] as $url) {
            $ld = collect($this->jsonLd($this->page($url)));
            $site = $ld->firstWhere('@type', 'WebSite');
            $this->assertNotNull($site, $url);
            $this->assertSame('SHELTER COFFEE', $site['name']);
            $this->assertSame('شلتر كوفي', $site['alternateName']);
            $this->assertSame('http://localhost/#organization', $site['publisher']['@id']);
            $organization = $ld->firstWhere('@type', 'Organization');
            $this->assertNotNull($organization);
            $this->assertSame('http://localhost/#organization', $organization['@id']);
        }
    }

    public function test_every_indexable_page_has_its_own_title_naming_the_brand_and_the_city(): void
    {
        $titles = [];
        foreach (['ar', 'en'] as $locale) {
            foreach (['/', '/jo/menu/', '/jo/locations/', '/jo/locations/irbid/drive/', '/jo/locations/irbid/house/', '/contact/', '/careers/'] as $path) {
                $html = $this->page('/'.$locale.$path);
                preg_match('#<title>(.*?)</title>#u', $html, $m);
                $title = html_entity_decode($m[1] ?? '');
                $this->assertMatchesRegularExpression('/شلتر كوفي|SHELTER COFFEE/u', $title, $locale.$path);
                $this->assertMatchesRegularExpression('/إربد|Irbid/u', $title, $locale.$path.': the city that the searches name');
                $this->assertLessThanOrEqual(65, mb_strlen($title), $locale.$path.' stays readable in a result');
                $titles[] = $title;
            }
        }
        $this->assertSame($titles, array_values(array_unique($titles)), 'no two pages share a title');
    }

    public function test_measurement_hooks_are_markup_only(): void
    {
        $branch = $this->page('/ar/jo/locations/irbid/drive/');
        $this->assertStringContainsString('data-track-view="branch_view" data-track-branch="drive" data-track-placement="branch_page"', $branch);
        $this->assertStringContainsString('data-track-placement="action_bar"', $branch);
        $this->assertStringContainsString('data-track-view="menu_view"', $this->page('/ar/jo/menu/'));
        $contact = $this->page('/ar/contact/');
        $this->assertStringContainsString('data-track-purpose="complaints_feedback_franchise"', $contact);
        $this->assertStringContainsString('data-track-purpose="catering_b2b_events"', $contact);
        foreach ([$branch, $contact] as $html) {
            $this->assertDoesNotMatchRegularExpression('/googletagmanager|gtag\(|google-analytics|connect\.facebook/i', $html, 'no tag is loaded by the site itself');
        }
    }
}
