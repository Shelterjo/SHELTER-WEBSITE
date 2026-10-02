<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\Experience;
use App\Models\Market;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Dashboard\AnnouncementEditor;
use App\Services\Experiences\Placements;
use Carbon\CarbonImmutable;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Announcements and campaigns (DX-010…013, DX-020/021, DX-037, M50): one per place, by priority; nothing when empty. */
class AnnouncementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-10 12:00:00', 'Asia/Amman'));
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp()]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function form(array $overrides = []): array
    {
        return $overrides + [
            'status' => 'published', 'type' => 'announcement', 'placement' => 'top_bar',
            'title_ar' => 'افتتاح تجريبي', 'title_en' => 'Sample opening',
            'starts_date' => '2026-10-10', 'starts_time' => '08:00', 'ends_date' => '2026-10-12', 'ends_time' => '23:59', 'level' => 'auto',
        ];
    }

    private function page(string $url): string
    {
        $this->app->forgetScopedInstances();

        return (string) $this->get($url)->assertOk()->getContent();
    }

    private function bar(string $html): ?string
    {
        return preg_match('#<aside[^>]*\bclass="ui-announcement[^"]*"[^>]*>.*?</aside>#s', $html, $m) === 1 ? $m[0] : null;
    }

    public function test_nothing_live_draws_nothing_and_a_published_one_shows_in_its_time(): void
    {
        $this->assertNull($this->bar($this->page('/ar/contact/')), 'no empty bar (DX-012)');
        $this->assertStringNotContainsString('home-feature', $this->page('/ar/'));

        $this->post('/dashboard/content/announcements', ['status' => 'published', 'title_ar' => 'نص'])
            ->assertSessionHasErrors(['title_en', 'starts_date', 'ends_date']);
        $this->post('/dashboard/content/announcements', $this->form(['body_ar' => 'سطر']))->assertSessionHasErrors(['body_en']);
        $this->post('/dashboard/content/announcements', $this->form(['status' => 'draft']))->assertSessionHasNoErrors();
        $this->assertNull($this->bar($this->page('/ar/contact/')), 'a draft never shows');

        $draft = Experience::query()->firstOrFail();
        $this->put('/dashboard/content/announcements/'.$draft->id, $this->form(['cta_label_ar' => 'التفاصيل', 'cta_label_en' => 'Details', 'cta_url' => '/ar/jo/menu/']))->assertSessionHasNoErrors();
        $ar = (string) $this->bar($this->page('/ar/contact/'));
        $this->assertStringContainsString('افتتاح تجريبي', $ar);
        $this->assertStringContainsString('href="/ar/jo/menu/"', $ar);
        $this->assertStringContainsString('href="/en/jo/menu/"', (string) $this->bar($this->page('/en/contact/')), 'a site path opens in the page language');
        $this->assertSame('live', app(AnnouncementEditor::class)->state($draft->refresh(), Market::query()->firstOrFail()));

        $this->travelTo(CarbonImmutable::parse('2026-10-13 09:00', 'Asia/Amman'));
        $this->assertNull($this->bar($this->page('/ar/contact/')), 'it leaves by itself at its end');
    }

    public function test_one_per_place_by_priority_and_the_owner_is_told_who_wins(): void
    {
        $this->post('/dashboard/content/announcements', $this->form());
        $this->post('/dashboard/content/announcements', $this->form(['type' => 'campaign', 'title_ar' => 'عرض تجريبي', 'title_en' => 'Sample offer']))
            ->assertSessionHas('conflicts', fn (array $lines): bool => count($lines) === 1 && str_contains($lines[0], 'افتتاح تجريبي'));
        $bar = (string) $this->bar($this->page('/ar/contact/'));
        $this->assertStringContainsString('عرض تجريبي', $bar, 'a campaign outranks an announcement');
        $this->assertStringNotContainsString('افتتاح تجريبي', $bar, 'never two bars');

        $this->post('/dashboard/content/announcements', $this->form(['title_ar' => 'إغلاق طارئ تجريبي', 'title_en' => 'Sample closure', 'urgent' => '1']));
        $bar = (string) $this->bar($this->page('/ar/contact/'));
        $this->assertStringContainsString('إغلاق طارئ تجريبي', $bar);
        $this->assertStringContainsString('ui-announcement--urgent', $bar);

        $urgent = Experience::query()->where('title_en', 'Sample closure')->firstOrFail();
        $this->get('/dashboard/live')->assertOk()->assertSee('إغلاق طارئ تجريبي')->assertSee('ينتظر');
        $this->post('/dashboard/live/'.$urgent->id.'/disable')->assertSessionHas('status');
        $this->assertStringContainsString('عرض تجريبي', (string) $this->bar($this->page('/ar/contact/')), 'stopped at once — the next one takes the place');
        $this->assertSame(1, AuditLog::query()->where('action', 'announcements.disable')->count());

        $offer = Experience::query()->where('title_en', 'Sample offer')->firstOrFail();
        $this->put('/dashboard/content/announcements/'.$offer->id, $this->form(['type' => 'campaign', 'title_ar' => 'عرض تجريبي', 'title_en' => 'Sample offer', 'level' => 'low']));
        $this->assertStringContainsString('افتتاح تجريبي', (string) $this->bar($this->page('/ar/contact/')), "the Owner's priority wins over the default");

        $this->post('/dashboard/content/announcements/'.$urgent->id.'/resume')->assertSessionHas('status');
        $this->assertStringContainsString('إغلاق طارئ تجريبي', (string) $this->bar($this->page('/ar/contact/')));
    }

    public function test_the_home_feature_is_its_own_place(): void
    {
        $this->post('/dashboard/content/announcements', $this->form(['type' => 'campaign', 'placement' => 'home_feature', 'title_ar' => 'عرض الصفحة الرئيسية', 'title_en' => 'Home offer',
            'body_ar' => 'سطر تجريبي.', 'body_en' => 'A sample line.']))->assertSessionHasNoErrors();
        $home = $this->page('/ar/');
        $this->assertStringContainsString('id="home-feature"', $home);
        $this->assertStringContainsString('عرض الصفحة الرئيسية', $home);
        $this->assertNull($this->bar($home), 'not in the top bar too');
    }

    public function test_a_failing_engine_never_breaks_the_page(): void
    {
        Schema::rename('experiences', 'experiences_broken');
        try {
            $this->assertNull(app(Placements::class)->current(Market::query()->firstOrFail(), Placements::TOP_BAR, 'ar'));
        } finally {
            Schema::rename('experiences_broken', 'experiences');
        }
    }
}
