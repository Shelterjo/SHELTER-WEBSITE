<?php

namespace Tests\Feature\Http;

use App\Models\FeatureFlag;
use App\Models\Redirect;
use App\Services\Content\Search\SearchLog;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * One URL form per page (CanonicalTrailingSlash, docs/PUBLIC-ROUTE-MAP.md §9): one 301 hop for a missing slash,
 * repeated slashes (RM-03) and a typed /index.php (RM-02), the query kept; and no 301 in front of a 404 — an
 * unpublished page asked for without its slash answers 404 at once (RM-01).
 */
class CanonicalUrlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
    }

    public function test_a_page_without_its_slash_still_gets_one_301_with_the_query(): void
    {
        $this->get('/ar/jo/menu')->assertStatus(301)->assertRedirect('http://localhost/ar/jo/menu/');
        $this->get('/en/jo/menu?branch=drive')->assertStatus(301)->assertRedirect('http://localhost/en/jo/menu/?branch=drive');
        $this->get('/ar/jo/locations/irbid/drive')->assertStatus(301)->assertRedirect('http://localhost/ar/jo/locations/irbid/drive/');
    }

    public function test_an_unpublished_or_unknown_routed_page_without_its_slash_is_a_404_at_once(): void
    {
        // RM-01: these have a route, but nothing is published there (or the slug is unknown).
        foreach (['/ar/about', '/en/faq', '/ar/media', '/en/careers/track', '/ar/jo/events/nothing-here', '/ar/jo/locations/irbid/nope'] as $path) {
            $response = $this->get($path);
            $response->assertNotFound();
            $this->assertNull($response->headers->get('Location'), $path.' must not redirect first');
            $this->assertStringContainsString('ui-error', (string) $response->getContent(), 'the designed 404 page');
        }
        $this->get('/ar/about/')->assertNotFound();
    }

    public function test_a_page_that_answers_with_its_own_redirect_is_one_hop(): void
    {
        // No application in the session: the thank-you page sends the visitor to the careers page — directly.
        $this->get('/ar/careers/submitted')->assertStatus(302)->assertRedirect('http://localhost/ar/careers/');
    }

    public function test_the_owners_redirect_for_an_unpublished_page_is_one_hop(): void
    {
        Redirect::query()->create(['source_path' => '/ar/about', 'target' => '/ar/contact/', 'status_code' => 301, 'state' => 'active', 'origin' => 'owner']);

        $this->get('/ar/about?ref=old')->assertStatus(301)->assertRedirect('http://localhost/ar/contact/?ref=old');
        $this->assertSame(1, (int) Redirect::query()->where('source_path', '/ar/about')->value('hits'), 'counted once, not twice');
    }

    public function test_the_search_is_not_asked_twice_before_its_redirect(): void
    {
        FeatureFlag::query()->create(['key' => SearchLog::FLAG, 'enabled' => true]);

        $this->get('/ar/search?q=latte')->assertStatus(301)->assertRedirect('http://localhost/ar/search/?q=latte');
        $this->assertSame(0, DB::table('search_query_daily')->count(), 'the redirect itself logs nothing');
        $this->get('/ar/search/?q=latte')->assertOk();
        $this->assertSame(1, (int) DB::table('search_query_daily')->sum('searches'));
    }

    public function test_repeated_slashes_collapse_in_one_hop_with_the_query(): void
    {
        // RM-03
        $this->get('/ar//jo/menu/')->assertStatus(301)->assertRedirect('http://localhost/ar/jo/menu/');
        $this->get('/en//jo///locations?x=1')->assertStatus(301)->assertRedirect('http://localhost/en/jo/locations/?x=1');
        $this->get('/dashboard//login')->assertStatus(301)->assertRedirect('http://localhost/dashboard/login');
        // Nothing answers the collapsed address: a 404 at once.
        $this->get('/ar//no-such-page/')->assertNotFound();

        // A doubled final slash (the test client would trim it, so the request is built by hand).
        $response = $this->app->make(Kernel::class)->handle(Request::create('http://localhost/ar/jo/menu//?branch=house'));
        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame('http://localhost/ar/jo/menu/?branch=house', $response->headers->get('Location'));
    }

    public function test_a_typed_index_php_goes_to_the_clean_address(): void
    {
        // RM-02: only when the script name is in the address the visitor typed. Absolute addresses: the test client
        // would otherwise build the next address from the previous request's root (which then holds /index.php).
        $script = ['SCRIPT_NAME' => '/index.php', 'SCRIPT_FILENAME' => public_path('index.php')];

        $this->call('GET', 'http://localhost/index.php/ar/jo/menu/', [], [], [], $script)->assertStatus(301)->assertRedirect('http://localhost/ar/jo/menu/');
        $this->call('GET', 'http://localhost/index.php/en/jo/menu', ['branch' => 'drive'], [], [], $script)->assertStatus(301)->assertRedirect('http://localhost/en/jo/menu/?branch=drive');
        $this->call('GET', 'http://localhost/index.php', [], [], [], $script)->assertStatus(301)->assertRedirect('http://localhost/');
        // The normal front-controller request (script name not in the address) is the page itself.
        $this->call('GET', 'http://localhost/ar/jo/menu/', [], [], [], $script)->assertOk();
    }

    public function test_only_get_and_head_are_redirected(): void
    {
        $this->call('HEAD', '/ar/jo/menu')->assertStatus(301);
        $this->call('TRACE', '/ar/jo/menu')->assertStatus(405);
        $this->call('TRACE', '/ar//jo/menu/')->assertNotFound();
    }

    public function test_files_and_the_dashboard_keep_their_own_form(): void
    {
        $this->get('/robots.txt')->assertOk();
        $this->get('/llms.txt')->assertOk();
        $this->get('/dashboard/login')->assertOk();
        $this->get('/up')->assertOk();
    }
}
