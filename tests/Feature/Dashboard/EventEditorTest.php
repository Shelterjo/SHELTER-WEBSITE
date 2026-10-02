<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\ContentVersion;
use App\Models\Experience;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use Carbon\CarbonImmutable;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/** Dashboard → Events (DX-014, M50): draft → publish with both languages, the commands, fixed address, versions. */
class EventEditorTest extends TestCase
{
    use RefreshDatabase;

    private Branch $drive;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-10 12:00:00', 'Asia/Amman'));
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
        $this->drive = Branch::query()->where('code', 'BR-DRIVE')->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function form(array $overrides = []): array
    {
        return $overrides + [
            'status' => 'published',
            'title_ar' => 'يوم القهوة', 'title_en' => 'Coffee Day',
            'body_ar' => "تذوق مجاني.\n\nفقرة ثانية.", 'body_en' => "Free tasting.\n\nA second paragraph.",
            'starts_date' => '2026-10-15', 'starts_time' => '18:00', 'ends_date' => '2026-10-16', 'ends_time' => '01:00',
            'branches' => [(string) $this->drive->id],
        ];
    }

    /** @return TestResponse<Response> */
    private function site(string $url): TestResponse
    {
        $this->app->forgetScopedInstances();

        return $this->get($url);
    }

    public function test_a_draft_stays_off_the_site_and_publishing_needs_both_languages(): void
    {
        $this->get('/dashboard/content/events')->assertOk()->assertSee('لا توجد فعاليات حالية أو قادمة.');
        $this->post('/dashboard/content/events', ['status' => 'draft', 'title_ar' => 'فكرة فعالية'])->assertSessionHasNoErrors();
        $draft = Experience::query()->firstOrFail();
        $this->assertSame(['event', 'draft', 'owner', null], [$draft->type, $draft->status, $draft->origin, $draft->slug]);
        $this->assertStringNotContainsString('فكرة فعالية', (string) $this->site('/ar/jo/events/')->getContent());

        $this->put('/dashboard/content/events/'.$draft->id, ['status' => 'published', 'title_ar' => 'فكرة فعالية'])
            ->assertSessionHasErrors(['title_en', 'body_ar', 'body_en', 'starts_date', 'ends_date']);
        $this->put('/dashboard/content/events/'.$draft->id, $this->form(['ends_date' => '2026-10-15', 'ends_time' => '17:00']))->assertSessionHasErrors(['ends_date']);
        $this->put('/dashboard/content/events/'.$draft->id, $this->form(['cta_label_ar' => 'سجّل', 'cta_url' => 'javascript:alert(1)']))->assertSessionHasErrors(['cta_url', 'cta_label_en']);
        $this->put('/dashboard/content/events/'.$draft->id, $this->form(['venue_ar' => 'جامعة اليرموك']))->assertSessionHasErrors(['venue_en']);
        $this->put('/dashboard/content/events/'.$draft->id, $this->form(['starts_date' => '2026-10-01', 'ends_date' => '2026-10-02']))->assertSessionHasErrors(['ends_date']);
        $this->assertSame('draft', $draft->refresh()->status, 'nothing saved while something is wrong');

        $this->put('/dashboard/content/events/'.$draft->id, $this->form(['reason' => 'Ready', 'cta_label_ar' => 'المنيو', 'cta_label_en' => 'Menu', 'cta_url' => '/ar/jo/menu/']))->assertSessionHasNoErrors();
        $event = $draft->refresh();
        $this->assertSame(['scheduled', 'coffee-day'], [$event->status, $event->slug]);
        $this->assertSame('2026-10-15 15:00', $event->starts_at?->format('Y-m-d H:i'), 'Amman time stored in UTC');

        $listing = (string) $this->site('/ar/jo/events/')->getContent();
        $this->assertStringContainsString('يوم القهوة', $listing);
        $page = (string) $this->site('/en/jo/events/coffee-day/')->assertOk()->getContent();
        $this->assertStringContainsString('Coffee Day', $page);
        $this->assertStringContainsString('SHELTER COFFEE DRIVE', $page, 'the branch is the place');
        $this->assertStringContainsString('href="/en/jo/menu/"', $page, 'a page of this site opens in the page language');

        $edit = (string) $this->get('/dashboard/content/events/'.$event->id)->assertOk()->getContent();
        $this->assertStringContainsString('هكذا يراها الزوار', $edit);
        $this->assertStringContainsString('منشورة — قادمة', $edit);
        $this->assertSame(2, ContentVersion::query()->where('versionable_type', $event->getMorphClass())->where('versionable_id', $event->id)->count(), 'one version per save');
        $this->assertSame('Ready', AuditLog::query()->where('action', 'events.saved')->latest('id')->firstOrFail()->meta['reason'] ?? null);
    }

    public function test_the_address_stays_fixed_once_published_and_duplicates_get_a_free_one(): void
    {
        $this->post('/dashboard/content/events', $this->form())->assertSessionHasNoErrors();
        $this->post('/dashboard/content/events', $this->form())->assertSessionHasNoErrors();
        $this->assertSame(['coffee-day', 'coffee-day-2'], Experience::query()->orderBy('id')->pluck('slug')->all());

        $first = Experience::query()->orderBy('id')->firstOrFail();
        $this->put('/dashboard/content/events/'.$first->id, $this->form(['slug' => 'another-address', 'title_en' => 'Coffee Day 2026']))->assertSessionHasNoErrors();
        $this->assertSame('coffee-day', $first->refresh()->slug, 'shared links keep working');
        $this->post('/dashboard/content/events', $this->form(['slug' => 'coffee-day']))->assertSessionHasErrors(['slug']);
        $this->post('/dashboard/content/events', $this->form(['slug' => 'Bad Address!']))->assertSessionHasErrors(['slug']);
    }

    public function test_the_commands_change_what_visitors_see_at_once(): void
    {
        $this->post('/dashboard/content/events', $this->form(['starts_date' => '2026-10-10', 'starts_time' => '10:00', 'ends_date' => '2026-10-10', 'ends_time' => '22:00']));
        $event = Experience::query()->firstOrFail();
        $url = '/dashboard/content/events/'.$event->id;
        $this->assertSame('active', $event->status, 'started already → active');
        $this->get($url)->assertSee('جارية الآن على الموقع');

        $this->post($url.'/pause')->assertSessionHas('status');
        $this->site('/ar/jo/events/coffee-day/')->assertNotFound();
        $this->post($url.'/pause')->assertSessionHas('warning', 'هذا الأمر لا يناسب حالة الفعالية الآن.');
        $this->post($url.'/resume');
        $this->site('/ar/jo/events/coffee-day/')->assertOk();

        $this->post($url.'/end');
        $this->assertSame('ended', $event->refresh()->status);
        $this->assertStringNotContainsString('يوم القهوة', (string) $this->site('/ar/jo/events/')->getContent(), 'out of the list');
        $this->site('/ar/jo/events/coffee-day/')->assertOk()->assertSee('انتهت');

        $this->post($url.'/archive');
        $this->get('/dashboard/content/events?show=archived')->assertSee('يوم القهوة');
        $this->post($url.'/restore');
        $this->assertNull($event->refresh()->archived_at);
        $this->post($url.'/delete')->assertNotFound();
        $this->assertSame(1, Experience::query()->count(), 'nothing is deleted');
        $this->assertSame(['events.created', 'events.pause', 'events.resume', 'events.end', 'events.archive', 'events.restore'],
            AuditLog::query()->where('action', 'like', 'events.%')->orderBy('id')->pluck('action')->all());
    }

    public function test_cancelling_hides_the_page_and_publishing_again_brings_it_back(): void
    {
        $this->post('/dashboard/content/events', $this->form());
        $event = Experience::query()->firstOrFail();
        $this->post('/dashboard/content/events/'.$event->id.'/cancel');
        $this->site('/ar/jo/events/coffee-day/')->assertNotFound();
        $this->get('/dashboard/content/events?show=past')->assertSee('يوم القهوة');

        $this->put('/dashboard/content/events/'.$event->id, $this->form())->assertSessionHasNoErrors();
        $this->assertSame('scheduled', $event->refresh()->status);
        $this->site('/ar/jo/events/coffee-day/')->assertOk();
    }
}
