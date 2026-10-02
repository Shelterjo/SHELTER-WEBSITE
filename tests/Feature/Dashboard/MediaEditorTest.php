<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\Media;
use App\Models\MediaUsage;
use App\Models\Page;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Media\MediaRights;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Media\MediaLibraryTest;
use Tests\TestCase;

/** Dashboard → Images (M50, MEDIA-RIGHTS): upload, the Owner's decision with its conditions, people's consent, use. */
class MediaEditorTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    private int $seed = 0;

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

    private function file(string $name = 'photo.png'): UploadedFile
    {
        return new UploadedFile(MediaLibraryTest::imageFile(900, 600, ++$this->seed), $name, 'image/png', null, true);
    }

    private function uploaded(string $source = 'shelter', string $people = 'none'): Media
    {
        $this->post('/dashboard/content/media', ['files' => [$this->file()], 'source' => $source, 'people_consent' => $people]);

        return Media::query()->latest('id')->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $changes
     * @return TestResponse<Response>
     */
    private function decide(Media $media, array $changes): TestResponse
    {
        return $this->from('/dashboard/content/media/'.$media->id)->put('/dashboard/content/media/'.$media->id, $changes + [
            'approval' => 'approved', 'ok_website' => '1', 'source' => $media->source, 'license' => 'full',
            'people_consent' => $media->people_consent, 'alt_ar' => 'كوب قهوة على الطاولة', 'alt_en' => 'A cup of coffee on the table',
            'focal_x' => '50', 'focal_y' => '50',
        ]);
    }

    public function test_uploaded_images_wait_for_approval_and_a_known_file_is_not_stored_twice(): void
    {
        $this->get('/dashboard/content/media')->assertOk()->assertSee('الصور')->assertSee('لا توجد صور هنا بعد.');
        $this->get('/dashboard/content/media/upload')->assertOk()->assertSee('enctype="multipart/form-data"', false);

        $first = $this->file('a.png');
        $this->post('/dashboard/content/media', ['files' => [$first, $this->file('b.png')], 'source' => 'contracted', 'people_consent' => 'none', 'photographer' => 'Studio'])
            ->assertRedirect('/dashboard/content/media?show=pending')->assertSessionHas('status', 'تم رفع صورتين — بانتظار اعتمادك.'); // the Arabic dual (copy audit F43)
        $this->assertSame(2, Media::query()->where('approval_status', Media::PENDING)->where('source', 'contracted')->count());
        $this->assertSame([], Storage::disk('media_public')->allFiles(), 'nothing public before approval');

        $again = new UploadedFile((string) $first->getRealPath(), 'a-again.png', 'image/png', null, true);
        $this->post('/dashboard/content/media', ['files' => [$again], 'source' => 'shelter', 'people_consent' => 'none'])
            ->assertSessionHas('status', fn (string $s): bool => str_contains($s, 'MED-00001'));
        $this->assertSame(2, Media::query()->count());

        $html = (string) $this->get('/dashboard/content/media?show=pending')->getContent();
        $this->assertStringContainsString('بانتظار اعتمادك <bdi>(2)</bdi>', $html);
        $this->assertSame(2, substr_count($html, 'class="ui-media-tile"'));
    }

    public function test_an_upload_without_its_rights_or_with_a_non_image_is_refused(): void
    {
        $this->from('/dashboard/content/media/upload')->post('/dashboard/content/media', ['files' => [$this->file()]])
            ->assertRedirect('/dashboard/content/media/upload')->assertSessionHasErrors(['source', 'people_consent']);
        $this->post('/dashboard/content/media', ['files' => [UploadedFile::fake()->createWithContent('menu.pdf', '%PDF-1.4 x')], 'source' => 'shelter', 'people_consent' => 'none'])
            ->assertSessionHasErrors(['files']);
        $this->post('/dashboard/content/media', ['source' => 'shelter', 'people_consent' => 'none'])->assertSessionHasErrors(['files']);
        $this->assertSame(0, Media::query()->count());
    }

    public function test_approving_needs_the_description_and_the_rights_then_makes_the_web_copies(): void
    {
        $media = $this->uploaded();
        $html = (string) $this->get('/dashboard/content/media/'.$media->id)->assertOk()->getContent();
        $this->assertStringContainsString('لم تعتمدها بعد.', $html);
        $this->assertStringContainsString('ينقصها الوصف باللغتين.', $html);

        $this->decide($media, ['alt_en' => ''])->assertSessionHasErrors(['alt_en']);
        $this->decide($media, ['license' => 'time_limited'])->assertSessionHasErrors(['rights_expires_at']);
        $this->assertSame(Media::PENDING, $media->refresh()->approval_status, 'a refused save changes nothing');

        $this->decide($media, ['rights_expires_at' => now()->addYear()->format('Y-m-d'), 'license' => 'time_limited'])
            ->assertRedirect('/dashboard/content/media/'.$media->id)->assertSessionHas('status');
        $media->refresh();
        $this->assertSame([Media::APPROVED, $this->owner->id, 'OWNER-DASHBOARD'], [$media->approval_status, $media->approved_by, $media->approval_ref]);
        $this->assertTrue(MediaRights::canUse($media));
        $this->assertNotEmpty($media->variants);
        $this->assertNotSame([], Storage::disk('media_public')->allFiles());
        $this->assertStringContainsString('نعم — تظهر حيث تُستخدم.', (string) $this->get('/dashboard/content/media/'.$media->id)->getContent());

        $this->decide($media, ['approval' => 'rejected']);
        $this->assertNull($media->refresh()->variants, 'rejecting takes the public copies down');
        $this->assertSame([], Storage::disk('media_public')->allFiles());
    }

    public function test_a_restricted_source_needs_the_owners_explicit_approval(): void
    {
        $media = $this->uploaded('stock');
        $this->assertStringContainsString('هذا المصدر ممنوع على الموقع', (string) $this->get('/dashboard/content/media/'.$media->id)->getContent());
        $this->decide($media, [])->assertSessionHasErrors(['source_explicitly_approved']);
        $this->decide($media, ['source_explicitly_approved' => '1'])->assertSessionHasNoErrors();
        $this->assertTrue(MediaRights::canUse($media->refresh()));
    }

    public function test_people_in_an_image_need_their_consent_and_a_withdrawal_takes_it_down(): void
    {
        $media = $this->uploaded(people: 'not_recorded');
        $this->decide($media, ['people_consent' => 'recorded'])->assertSessionHasErrors(['people']);
        $html = (string) $this->followingRedirects()->decide($media, ['people_consent' => 'recorded', 'people' => [['person' => 'Rana Haddad', 'scopes' => ['website']]]])->getContent();
        $this->followRedirects = false;
        $this->assertStringContainsString('value="Rana Haddad"', $html, 'a refused save keeps the person typed');
        $this->assertStringContainsString('href="#p0-consented_at"', $html);

        $person = ['person' => 'Rana Haddad', 'consented_at' => '2026-09-30', 'scopes' => ['website', 'social'], 'document_ref' => 'Form 12'];
        $this->decide($media, ['people_consent' => 'recorded', 'people' => [$person, ['person' => '', 'consented_at' => '']]])->assertSessionHasNoErrors();
        $media->refresh();
        $this->assertCount(1, $media->people_consents ?? [], 'the blank row is ignored');
        $this->assertTrue(MediaRights::canUse($media));
        $this->assertFalse(MediaRights::canUse($media, 'ads'), 'consent covers the website only');

        $this->decide($media, ['people_consent' => 'recorded', 'ok_ads' => '1', 'people' => [$person + ['withdrawn_at' => '2026-10-02']]]);
        $this->assertFalse(MediaRights::canUse($media->refresh()));
        $this->assertNull($media->variants, 'withdrawn → taken down at once');
        $this->assertStringContainsString('أحد الأشخاص سحب موافقته.', (string) $this->get('/dashboard/content/media/'.$media->id)->getContent());

        $audit = AuditLog::query()->where('action', 'like', 'media.%')->get()->toJson();
        $this->assertStringNotContainsString('Rana', $audit, 'no names of people in the audit');
    }

    public function test_the_press_kit_and_archiving(): void
    {
        $media = $this->uploaded();
        $this->decide($media, ['press_kit' => '1'])->assertSessionHasNoErrors();
        $page = Page::query()->where('key', 'media')->firstOrFail();
        $this->assertSame('draft', $page->status->value, 'the press kit waits on a draft Media Center page');
        $this->assertTrue(MediaUsage::query()->where('media_id', $media->id)->where('slot', 'press_kit')->exists());
        $this->assertStringContainsString('صور الصحافة (Press Kit)', (string) $this->get('/dashboard/content/media/'.$media->id)->getContent());

        $this->decide($media, []);
        $this->assertFalse(MediaUsage::query()->where('media_id', $media->id)->exists(), 'unticked → removed from the press kit');

        $this->post('/dashboard/content/media/'.$media->id.'/archive')->assertRedirect()->assertSessionHas('status');
        $this->assertNotNull($media->refresh()->archived_at);
        $this->assertNull($media->variants);
        $this->assertStringContainsString('class="ui-media-tile"', (string) $this->get('/dashboard/content/media?show=archived')->getContent());
        $this->assertStringNotContainsString('class="ui-media-tile"', (string) $this->get('/dashboard/content/media')->getContent());

        $this->post('/dashboard/content/media/'.$media->id.'/restore');
        $this->assertNull($media->refresh()->archived_at);
        $this->assertNotEmpty($media->variants, 'brought back and still approved → on the site again');
        $this->assertSame(1, Media::query()->count(), 'nothing is ever deleted');
    }

    public function test_the_preview_is_private(): void
    {
        $media = $this->uploaded();
        $response = $this->get('/dashboard/content/media/'.$media->id.'/preview')->assertOk();
        $this->assertSame('image/webp', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('private', (string) $response->headers->get('Cache-Control'));
        $this->assertSame([], Storage::disk('media_public')->allFiles(), 'the preview is not a public file');

        auth()->logout();
        $this->get('/dashboard/content/media/'.$media->id.'/preview')->assertRedirect('/dashboard/login');
        $this->get('/dashboard/content/media')->assertRedirect('/dashboard/login');
    }
}
