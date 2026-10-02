<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\Media;
use App\Models\TeamMember;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Media\MediaLibrary;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Media\MediaLibraryTest;
use Tests\TestCase;

/** Dashboard → SHELTER Family (M50, OPS-034): a profile is public only with the person's recorded consent. */
class TeamEditorTest extends TestCase
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
        $this->owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($this->owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp()]);
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return array<string, mixed>
     */
    private function profile(array $changes = []): array
    {
        return $changes + [
            'is_published' => '1', 'display_name_ar' => 'ليلى', 'display_name_en' => 'Layla', 'job_title_ar' => 'باريستا', 'job_title_en' => 'Barista',
            'publish_consent_at' => '2026-09-01', 'publish_consent_version' => 'Form 7', 'sort_order' => '1',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return TestResponse<Response>
     */
    private function save(?TeamMember $member, array $data): TestResponse
    {
        return $member === null
            ? $this->from('/dashboard/content/team/new')->post('/dashboard/content/team', $data)
            : $this->from('/dashboard/content/team/'.$member->id)->put('/dashboard/content/team/'.$member->id, $data);
    }

    private function site(): string
    {
        $this->app->forgetScopedInstances();

        return (string) $this->get('/en/family/')->getContent();
    }

    public function test_a_profile_is_shown_only_with_the_persons_recorded_consent(): void
    {
        $this->get('/dashboard/content/team')->assertOk()->assertSee('لا يوجد أحد بعد.');
        $this->get('/dashboard/content/team/new')->assertOk()->assertSee('شلتر كوفي هاوس');

        $this->save(null, $this->profile(['publish_consent_version' => '']))->assertSessionHasErrors(['publish_consent_version']);
        $this->save(null, $this->profile(['job_title_en' => '', 'show_bio' => '1', 'bio_ar' => 'نبذة']))->assertSessionHasErrors(['job_title_en', 'bio_en']);
        $this->save(null, $this->profile(['publish_consent_at' => now()->addDays(3)->format('Y-m-d')]))->assertSessionHasErrors(['publish_consent_at']);
        $this->save(null, $this->profile(['show_join_date' => '1']))->assertSessionHasErrors(['join_date']);
        $this->assertSame(0, TeamMember::query()->count());

        $this->save(null, $this->profile(['branch_id' => '2', 'join_date' => '2023-05-10', 'show_join_date' => '1']))->assertSessionHasNoErrors();
        $member = TeamMember::query()->firstOrFail();
        $site = $this->site();
        $this->assertStringContainsString('Layla', $site);
        $this->assertStringContainsString('May 2023', $site);
        $this->assertStringContainsString('ظاهر على الموقع', (string) $this->get('/dashboard/content/team')->getContent());

        // The person withdraws: recording the date is enough — the profile leaves the site at once.
        $this->save($member, $this->profile(['consent_withdrawn_at' => now()->format('Y-m-d')]))->assertSessionHasNoErrors();
        $this->assertFalse($member->refresh()->is_published);
        $this->app->forgetScopedInstances();
        $this->get('/en/family/')->assertNotFound();
        $this->assertStringContainsString('سحب موافقته', (string) $this->get('/dashboard/content/team')->getContent());

        $audit = AuditLog::query()->where('action', 'like', 'team.%')->get()->toJson(JSON_UNESCAPED_UNICODE);
        $this->assertStringNotContainsString('Layla', $audit, 'no names in the audit');
        $this->assertStringNotContainsString('ليلى', $audit);
    }

    public function test_the_photo_needs_the_images_own_approval_and_archiving_hides_the_profile(): void
    {
        $photo = app(MediaLibrary::class)->import(MediaLibraryTest::imageFile(seed: 3), ['source' => 'shelter', 'people_consent' => 'not_recorded']);
        $photo->forceFill(['approval_status' => Media::APPROVED, 'ok_website' => true, 'alt_ar' => 'ليلى', 'alt_en' => 'Layla'])->save();
        $this->save(null, $this->profile(['photo_media_id' => (string) $photo->id]))->assertSessionHasErrors(['photo_media_id']);
        $this->assertStringNotContainsString('/media/'.$photo->id.'/preview', (string) $this->get('/dashboard/content/team/new')->getContent(), 'not offered without the person’s consent');

        $photo->forceFill(['people_consent' => 'recorded', 'people_consents' => [['person' => 'P-1', 'consented_at' => '2026-09-01', 'scopes' => ['website']]]])->save();
        app(MediaLibrary::class)->generateVariants($photo);
        $this->save(null, $this->profile(['photo_media_id' => (string) $photo->id]))->assertSessionHasNoErrors();
        $member = TeamMember::query()->firstOrFail();
        $this->assertStringContainsString('<picture', $this->site());

        $this->post('/dashboard/content/team/'.$member->id.'/archive')->assertSessionHas('status');
        $this->app->forgetScopedInstances();
        $this->get('/en/family/')->assertNotFound();
        $this->post('/dashboard/content/team/'.$member->id.'/restore');
        $this->assertStringContainsString('Layla', $this->site());
        $this->assertSame(1, TeamMember::query()->count(), 'nothing is deleted');
    }
}
