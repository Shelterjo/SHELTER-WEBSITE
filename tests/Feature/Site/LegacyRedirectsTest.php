<?php

namespace Tests\Feature\Site;

use App\Models\AuditLog;
use App\Models\Redirect;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Seo\LegacyRedirects;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Old links (SEO-011/012/014/018/020): seeded from the approved migration map as switched-off drafts; the Owner
 * switches them on; one hop to a real page, the query kept, hits counted; a live page always wins; 410 for what is
 * gone on purpose; no external target, loop or chain.
 */
class LegacyRedirectsTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
    }

    private function asOwner(): self
    {
        return $this->actingAs($this->owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
    }

    public function test_the_approved_map_is_seeded_switched_off_and_every_target_is_a_real_page(): void
    {
        $rows = Redirect::query()->get();
        $this->assertGreaterThanOrEqual(15, $rows->count());
        $this->assertSame(['draft'], $rows->pluck('state')->unique()->values()->all(), 'nothing redirects until the Owner switches it on (SEO-013)');
        $this->get('/menu')->assertNotFound();

        foreach ($rows->whereNotNull('target') as $row) {
            if (str_ends_with((string) $row->target, '.xml')) {
                continue; // the sitemap answers only where the site is indexable (production)
            }
            $this->get((string) $row->target)->assertOk();
        }
        $this->assertNull(Redirect::query()->where('source_path', '/blog')->first(), 'no "everything to the home page": undecided pages stay out (PO-015, PO-035)');
    }

    public function test_switched_on_it_is_one_hop_keeps_the_query_and_counts_the_visit(): void
    {
        $menu = Redirect::query()->where('source_path', '/menu')->firstOrFail();
        $this->asOwner()->post("/dashboard/seo/redirects/{$menu->id}/activate")->assertSessionHasNoErrors();
        $this->assertSame(1, AuditLog::query()->where('action', 'redirects.activate')->count());

        $this->get('/menu')->assertStatus(301)->assertRedirect('http://localhost/ar/jo/menu/');
        $this->get('/MENU/?utm_source=qr')->assertStatus(301)->assertRedirect('http://localhost/ar/jo/menu/?utm_source=qr');
        $this->get('/'.rawurlencode('القائمة-شلتر-كافية-محافظة-اربد').'/')->assertNotFound(); // still off
        $this->assertSame(2, $menu->fresh()?->hits);

        $this->asOwner()->post("/dashboard/seo/redirects/{$menu->id}/deactivate");
        $this->get('/menu')->assertNotFound();
    }

    public function test_a_live_page_always_wins_and_gone_pages_answer_410(): void
    {
        Redirect::query()->create(['source_path' => '/ar/jo/locations', 'target' => '/ar/jo/menu/', 'status_code' => 301, 'state' => 'active']);
        Redirect::query()->where('source_path', '/locations.kml')->update(['state' => 'active']);
        app(LegacyRedirects::class)->flush();

        $this->get('/ar/jo/locations/')->assertOk();
        $this->get('/locations.kml')->assertStatus(410)->assertSee('lang="ar"', false);
        $this->post('/locations.kml')->assertNotFound(); // only GET/HEAD are redirected
    }

    public function test_the_owner_cannot_point_outside_loop_or_chain(): void
    {
        $owner = $this->asOwner();
        $owner->post('/dashboard/seo/redirects', ['source_path' => '/old-offer', 'target' => 'https://evil.example/x', 'status_code' => 301])
            ->assertSessionHasErrors(['target'], null, 'new');
        $owner->post('/dashboard/seo/redirects', ['source_path' => '/old-offer', 'target' => '/old-offer/', 'status_code' => 301])
            ->assertSessionHasErrors(['target'], null, 'new');
        $owner->post('/dashboard/seo/redirects', ['source_path' => '/old-offer', 'target' => '/menu', 'status_code' => 301])
            ->assertSessionHasErrors(['target' => __('dashboard.redirects.errors.chain')], null, 'new');
        $owner->post('/dashboard/seo/redirects', ['source_path' => '/old-offer', 'target' => '/ar/no-such-page/', 'status_code' => 301])
            ->assertSessionHasErrors(['target'], null, 'new');
        $owner->post('/dashboard/seo/redirects', ['source_path' => '/dashboard/x', 'target' => '/ar/', 'status_code' => 301])
            ->assertSessionHasErrors(['source_path'], null, 'new');
        $owner->post('/dashboard/seo/redirects', ['source_path' => '/menu', 'target' => '/ar/', 'status_code' => 301])
            ->assertSessionHasErrors(['source_path'], null, 'new');

        $owner->post('/dashboard/seo/redirects', ['source_path' => 'https://www.shelterjo.com/Old-Offer/', 'target' => 'http://localhost/en/jo/menu/', 'status_code' => 302])
            ->assertSessionHasNoErrors();
        $row = Redirect::query()->where('source_path', '/old-offer')->firstOrFail();
        $this->assertSame(['/en/jo/menu/', 302, 'draft', 'owner'], [$row->target, $row->status_code, $row->state, $row->origin]);
        $this->assertSame(1, AuditLog::query()->where('action', 'redirects.created')->count());
    }

    public function test_only_the_owner_sees_and_changes_old_links(): void
    {
        $this->get('/dashboard/seo/redirects')->assertRedirect('/dashboard/login');
        $this->post('/dashboard/seo/redirects', ['source_path' => '/x', 'target' => '/ar/'])->assertRedirect('/dashboard/login');
        $this->asOwner()->get('/dashboard/seo/redirects')->assertOk()->assertSee('/city-centre-branch')->assertSee(__('dashboard.redirects.title'));
    }
}
