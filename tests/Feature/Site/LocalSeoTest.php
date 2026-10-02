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

    public function test_the_kind_of_branch_and_its_city_show_only_while_the_city_spelling_is_approved(): void
    {
        $en = $this->page('/en/jo/locations/irbid/house/');
        $this->assertStringContainsString('<p class="ui-page-intro__lead">Coffee house in Irbid</p>', $en);
        $this->assertStringContainsString('<title>SHELTER COFFEE HOUSE — Coffee house in Irbid | Opening Hours</title>', $en);
        // D-334: «إربد» is the approved spelling (CF-M-036).
        $this->assertStringContainsString('<p class="ui-page-intro__lead">درايف ثرو في إربد</p>', $this->page('/ar/jo/locations/irbid/drive/'));

        // The fact gates it: when the live name no longer matches the approved spelling, only the kind shows.
        $facts = app(FactRegistry::class);
        $fact = $facts->current('city.irbid.name_ar');
        $this->assertNotNull($fact);
        $facts->supersede($fact, 'اربد', $this->owner(), 'D-TEST');
        $ar = $this->page('/ar/jo/locations/irbid/drive/');
        $this->assertStringContainsString('<p class="ui-page-intro__lead">درايف ثرو</p>', $ar);
        $this->assertStringNotContainsString('<p class="ui-page-intro__lead">درايف ثرو في', $ar);
    }

    public function test_the_approved_location_descriptions_show_and_a_missing_one_waits_for_the_owner(): void
    {
        // D-334: HOUSE in both languages, DRIVE in Arabic; DRIVE's English wording is still missing (PO-081).
        $house = $this->page('/ar/jo/locations/irbid/house/');
        $this->assertStringContainsString('<p>إربد سيتي سنتر، الطابق الأول، بجانب البنك الإسلامي الأردني</p>', $house);
        $this->assertStringContainsString('<p>Irbid City Center, First Floor, next to Jordan Islamic Bank</p>', $this->page('/en/jo/locations/irbid/house/'));
        $this->assertStringContainsString('<p>بجانب منطقة قصر النخيل / أرابيلا</p>', $this->page('/ar/jo/locations/irbid/drive/'));
        $drive = Branch::query()->where('slug', 'drive')->firstOrFail();
        $this->assertSame('MISSING', app(FactRegistry::class)->current($drive->factKey('landmark_en'))?->status->value, 'not invented');
        $this->assertStringNotContainsString('id="branch-place"', $this->page('/en/jo/locations/irbid/drive/'));

        // The card names the city once: «كوفي هاوس · إربد سيتي سنتر…», not «… في إربد · إربد سيتي سنتر…».
        $cards = $this->page('/ar/jo/locations/');
        $this->assertStringContainsString('كوفي هاوس · إربد سيتي سنتر، الطابق الأول', $cards);
        $this->assertStringContainsString('درايف ثرو في إربد · بجانب منطقة قصر النخيل / أرابيلا', $cards);

        // The Owner saves the missing wording in the branch editor (saving = approval) and it shows.
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->actingAs($this->owner())->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
        $this->get('/dashboard/data/branches/'.$drive->id)->assertOk()->assertSee('وصف الموقع بالإنجليزي');
        $this->put('/dashboard/data/branches/'.$drive->id.'/details', [
            'name_ar' => $drive->name_ar, 'name_en' => $drive->name_en, 'is_public' => '1',
            'landmark_ar' => (string) $drive->landmark_ar, 'landmark_en' => 'Test landmark wording',
        ])->assertSessionHasNoErrors();
        $en = $this->page('/en/jo/locations/irbid/drive/');
        $this->assertStringContainsString('<p>Test landmark wording</p>', $en);
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
