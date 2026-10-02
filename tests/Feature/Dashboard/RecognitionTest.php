<?php

namespace Tests\Feature\Dashboard;

use App\Enums\PublishStatus;
use App\Models\AuditLog;
use App\Models\ContentVersion;
use App\Models\Experience;
use App\Models\Media;
use App\Models\Page;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Core\FeatureFlags;
use App\Services\Experiences\Recognitions;
use App\Services\Media\MediaLibrary;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Media\MediaLibraryTest;
use Tests\TestCase;

/**
 * Employee of the Month (DX-007…009, M66 §40): managed in the dashboard without code; public only for someone shown on
 * SHELTER Family with their recorded consent and with a photo the website may use — otherwise nothing at all.
 */
class RecognitionTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media');
        Storage::fake('media_public');
        $this->seed(MasterDataSeeder::class);
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->travelTo(CarbonImmutable::parse('2026-10-10 12:00:00', 'Asia/Amman'));
        $this->owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($this->owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp()]);
    }

    /**
     * A sample profile shown on SHELTER Family with its recorded consent (test data, not a real person).
     *
     * @param  array<string, mixed>  $changes
     */
    private function member(array $changes = []): TeamMember
    {
        return TeamMember::query()->create($changes + [
            'display_name_ar' => 'ليلى', 'display_name_en' => 'Layla', 'job_title_ar' => 'باريستا', 'job_title_en' => 'Barista',
            'is_published' => true, 'publish_consent_at' => now()->subMonth(), 'publish_consent_version' => 'Form 7',
        ]);
    }

    /** A sample image the website may use: approved, and the person in it consented for the website. */
    private function photo(): Media
    {
        $photo = app(MediaLibrary::class)->import(MediaLibraryTest::imageFile(seed: 5), ['source' => 'shelter', 'people_consent' => 'recorded']);
        $photo->forceFill(['approval_status' => Media::APPROVED, 'ok_website' => true, 'alt_ar' => 'صورة تجريبية', 'alt_en' => 'Sample photo',
            'people_consents' => [['person' => 'P-1', 'consented_at' => '2026-09-01', 'scopes' => ['website']]]])->save();
        app(MediaLibrary::class)->generateVariants($photo);

        return $photo;
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<string, mixed>
     */
    private function form(TeamMember $member, ?Media $photo, array $changes = []): array
    {
        return $changes + [
            'status' => 'published', 'team_member_id' => (string) $member->id, 'month' => '10', 'year' => '2026',
            'title_ar' => 'شكرًا على كل صباح', 'title_en' => 'Thank you for every morning',
            'body_ar' => 'نص تكريم تجريبي.', 'body_en' => 'A sample recognition text.',
            'media_id' => $photo === null ? '' : (string) $photo->id, 'placements' => ['family', 'home'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return TestResponse<Response>
     */
    private function save(?Experience $item, array $data): TestResponse
    {
        return $item === null
            ? $this->from('/dashboard/content/recognition/new')->post('/dashboard/content/recognition', $data)
            : $this->from('/dashboard/content/recognition/'.$item->id)->put('/dashboard/content/recognition/'.$item->id, $data);
    }

    /** Moves the clock (Amman time) and keeps the Owner signed in. */
    private function at(string $moment): void
    {
        $this->travelTo(CarbonImmutable::parse($moment, 'Asia/Amman'));
        $this->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp()]);
    }

    private function site(string $url): string
    {
        $this->app->forgetScopedInstances();

        return (string) $this->get($url)->getContent();
    }

    private function shown(string $html): bool
    {
        return str_contains($html, 'class="ui-recognition"');
    }

    public function test_nothing_exists_or_shows_by_default_and_the_seeders_create_none(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->assertSame(0, Experience::query()->where('type', Recognitions::TYPE)->count(), 'seeders create no recognition');
        $this->assertSame(0, TeamMember::query()->count(), 'and no people');
        $this->get('/dashboard/content/recognition')->assertOk()->assertSee('لا يوجد تكريم هنا.');
        $this->get('/dashboard/content/recognition/new')->assertOk()->assertSee('موظف شهر جديد');
        $this->assertFalse($this->shown($this->site('/ar/')));
        $this->assertStringContainsString('href="'.route('dashboard.recognition.index').'"', (string) $this->get('/dashboard')->getContent(), 'in the navigation');
    }

    public function test_publishing_needs_a_consenting_profile_an_approved_photo_both_titles_a_place_and_an_open_month(): void
    {
        $hidden = $this->member(['is_published' => false]);
        $live = $this->member(['display_name_ar' => 'سامي', 'display_name_en' => 'Sami']);
        $photo = $this->photo();
        $unapproved = app(MediaLibrary::class)->import(MediaLibraryTest::imageFile(seed: 6), ['source' => 'shelter', 'people_consent' => 'none']);

        $this->save(null, $this->form($hidden, $photo))->assertSessionHasErrors(['team_member_id']);
        $this->save(null, $this->form($live, null))->assertSessionHasErrors(['media_id']); // no profile photo and none chosen
        $this->save(null, $this->form($live, $unapproved))->assertSessionHasErrors(['media_id']);
        $this->save(null, $this->form($live, $photo, ['title_en' => '', 'placements' => [], 'body_en' => '']))->assertSessionHasErrors(['title_en', 'placements', 'body_en']);
        $this->save(null, $this->form($live, $photo, ['month' => '9']))->assertSessionHasErrors(['month']);
        $this->save(null, $this->form($live, $photo, ['month' => '13']))->assertSessionHasErrors(['month']);
        $this->assertSame(0, Experience::query()->count());

        // A draft keeps whatever is ready — nobody sees it.
        $this->save(null, ['status' => 'draft', 'title_ar' => 'مسودة'])->assertSessionHasNoErrors();
        $draft = Experience::query()->firstOrFail();
        $this->assertSame(['recognition', 'draft'], [$draft->type, $draft->status]);
        $this->assertFalse($this->shown($this->site('/ar/')));

        $this->save($draft, $this->form($live, $photo))->assertSessionHasNoErrors();
        $draft->refresh();
        $this->assertSame('active', $draft->status);
        $this->assertSame(['team_member_id' => $live->id, 'month' => '2026-10'], $draft->details);
        $this->assertSame('2026-10-01 00:00', $draft->starts_at?->setTimezone('Asia/Amman')->format('Y-m-d H:i'));
        $this->assertSame('2026-11-01 00:00', $draft->ends_at?->setTimezone('Asia/Amman')->format('Y-m-d H:i'));

        // One published recognition per month.
        $this->save(null, $this->form($live, $photo))->assertSessionHasErrors(['month']);
    }

    public function test_it_shows_where_the_owner_placed_it_and_leaves_with_the_consent_or_the_photo(): void
    {
        $member = $this->member();
        $photo = $this->photo();
        $this->save(null, $this->form($member, $photo))->assertSessionHasNoErrors();
        $item = Experience::query()->firstOrFail();

        $family = $this->site('/en/family/');
        $this->assertTrue($this->shown($family));
        foreach (['Employee of the Month · October 2026', 'Thank you for every morning', 'Layla', 'Barista', 'A sample recognition text.', '<picture'] as $part) {
            $this->assertStringContainsString($part, $family);
        }
        $home = $this->site('/ar/');
        $this->assertStringContainsString('موظف الشهر · تشرين الأول 2026', $home);
        $this->assertStringContainsString('شكرًا على كل صباح', $home);

        // Media Center was not chosen: not there.
        $page = Page::query()->create(['key' => 'media', 'type' => 'brand', 'title_ar' => 'المركز الإعلامي', 'title_en' => 'Media Center',
            'status' => PublishStatus::Published, 'published_at' => now()->subMinute()]);
        $page->sections()->create(['type' => 'text', 'heading_ar' => 'عنّا', 'heading_en' => 'About', 'body_ar' => 'نص.', 'body_en' => 'Text.']);
        $this->assertStringContainsString('Media Center', $this->site('/en/media/'));
        $this->assertFalse($this->shown($this->site('/en/media/')));
        $this->save($item, $this->form($member, $photo, ['placements' => ['media']]))->assertSessionHasNoErrors();
        $this->assertTrue($this->shown($this->site('/en/media/')));
        $this->assertFalse($this->shown($this->site('/en/')));
        $this->save($item, $this->form($member, $photo))->assertSessionHasNoErrors();
        $this->assertStringContainsString('ظاهر على الموقع هذا الشهر', (string) $this->get('/dashboard/content/recognition')->getContent());

        // The photo is no longer approved: nothing shows (no empty frame), and the dashboard says why.
        $photo->forceFill(['approval_status' => 'PENDING OWNER APPROVAL'])->save();
        $this->assertFalse($this->shown($this->site('/en/family/')));
        $this->get('/dashboard/content/recognition/'.$item->id)->assertOk()->assertSee('شهره الآن لكنه لا يظهر')->assertSee('لا توجد صورة معتمدة للموقع');
        $photo->forceFill(['approval_status' => Media::APPROVED])->save();
        $this->assertTrue($this->shown($this->site('/en/family/')));

        // The person withdraws their consent on SHELTER Family: it leaves every place at once.
        $member->forceFill(['consent_withdrawn_at' => now(), 'is_published' => false])->save();
        $this->assertFalse($this->shown($this->site('/ar/')));
        $this->get('/dashboard/content/recognition/'.$item->id)->assertOk()->assertSee('الشخص غير ظاهر في SHELTER Family');

        // Safe Mode stops the dynamic layer, this included.
        $member->forceFill(['consent_withdrawn_at' => null, 'is_published' => true])->save();
        $this->assertTrue($this->shown($this->site('/ar/')));
        app(FeatureFlags::class)->set(FeatureFlags::SAFE_MODE, true, $this->owner, 'test');
        $this->assertFalse($this->shown($this->site('/ar/')));
    }

    public function test_a_coming_month_waits_and_an_ended_month_moves_to_the_history_without_deleting(): void
    {
        $member = $this->member();
        $photo = $this->photo();
        $this->save(null, $this->form($member, $photo, ['month' => '11']))->assertSessionHasNoErrors();
        $item = Experience::query()->firstOrFail();
        $this->assertSame('scheduled', $item->status);
        $this->assertFalse($this->shown($this->site('/en/family/')));
        $this->assertStringContainsString('منشور — لشهر قادم', (string) $this->get('/dashboard/content/recognition')->getContent());

        $this->at('2026-11-01 00:00:30');
        $this->assertTrue($this->shown($this->site('/en/family/')), 'starts by itself on the first day (Amman time)');

        $this->at('2026-12-01 00:00:30');
        $this->assertFalse($this->shown($this->site('/en/family/')), 'ends by itself after the last day');
        $this->assertStringNotContainsString('شكرًا على كل صباح', (string) $this->get('/dashboard/content/recognition')->getContent());
        $this->get('/dashboard/content/recognition?show=past')->assertOk()->assertSee('شكرًا على كل صباح')->assertSee('انتهى شهره');

        // History can be corrected; it cannot be newly published into.
        $this->save($item, $this->form($member, $photo, ['month' => '11', 'title_ar' => 'شكرًا مرة أخرى']))->assertSessionHasNoErrors();
        $this->save(null, $this->form($member, $photo, ['month' => '11', 'title_ar' => 'آخر']))->assertSessionHasErrors(['month']);

        $this->post('/dashboard/content/recognition/'.$item->id.'/archive')->assertSessionHas('status');
        $this->get('/dashboard/content/recognition?show=archived')->assertOk()->assertSee('شكرًا مرة أخرى');
        $this->post('/dashboard/content/recognition/'.$item->id.'/restore')->assertSessionHas('status');
        $this->post('/dashboard/content/recognition/'.$item->id.'/delete')->assertNotFound();
        $this->assertSame(1, Experience::query()->count(), 'nothing is deleted');
    }

    public function test_every_change_keeps_a_version_and_an_audit_entry_without_names(): void
    {
        $member = $this->member();
        $photo = $this->photo();
        $this->save(null, $this->form($member, $photo))->assertSessionHasNoErrors();
        $item = Experience::query()->firstOrFail();
        $this->save($item, $this->form($member, $photo, ['status' => 'draft', 'reason' => 'Wait for the photo shoot']))->assertSessionHasNoErrors();
        $this->post('/dashboard/content/recognition/'.$item->id.'/archive');

        $versions = ContentVersion::query()->where('versionable_type', $item->getMorphClass())->where('versionable_id', $item->id)->orderBy('version')->get();
        $this->assertSame(['active', 'draft', 'draft'], $versions->pluck('status')->all());
        $this->assertSame('Wait for the photo shoot', $versions->get(1)?->reason);
        $actions = AuditLog::query()->where('action', 'like', 'recognition.%')->orderBy('id')->pluck('action')->all();
        $this->assertSame(['recognition.created', 'recognition.saved', 'recognition.archive'], $actions);
        $audit = AuditLog::query()->where('action', 'like', 'recognition.%')->get()->toJson(JSON_UNESCAPED_UNICODE);
        $this->assertStringNotContainsString('Layla', $audit, 'no names in the audit');
        $this->assertStringNotContainsString('ليلى', $audit);
        $this->get('/dashboard/content/recognition/'.$item->id)->assertOk()->assertSee('Wait for the photo shoot');
    }

    public function test_the_dashboard_screens_are_owner_only_and_reject_other_experiences(): void
    {
        $event = Experience::query()->create(['type' => 'event', 'title_ar' => 'فعالية', 'title_en' => 'Event', 'status' => 'draft']);
        $this->get('/dashboard/content/recognition/'.$event->id)->assertNotFound();
        $this->put('/dashboard/content/recognition/'.$event->id, ['status' => 'draft'])->assertNotFound();
        $this->post('/dashboard/content/recognition/'.$event->id.'/archive')->assertNotFound();

        auth()->logout();
        $this->get('/dashboard/content/recognition')->assertRedirect(route('login'));
        $this->post('/dashboard/content/recognition', ['status' => 'draft'])->assertRedirect(route('login'));
        $this->assertSame(1, Experience::query()->count());
    }
}
