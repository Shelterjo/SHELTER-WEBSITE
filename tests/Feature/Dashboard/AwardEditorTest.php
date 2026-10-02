<?php

namespace Tests\Feature\Dashboard;

use App\Enums\FactStatus;
use App\Models\Award;
use App\Models\Fact;
use App\Models\Media;
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

/** Dashboard → Awards (M50, PO-032): an award is a public claim — published only with the Owner's confirmation. */
class AwardEditorTest extends TestCase
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
    private function award(array $changes = []): array
    {
        return $changes + [
            'status' => 'published', 'title_ar' => 'أفضل قهوة مختصة', 'title_en' => 'Best Specialty Coffee', 'issuer_ar' => 'جهة تجريبية',
            'issuer_en' => 'Test Issuer', 'year' => '2025', 'evidence_url' => 'https://example.test/award', 'confirm' => '1',
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return TestResponse<Response>
     */
    private function save(?Award $award, array $data): TestResponse
    {
        $url = $award === null ? '/dashboard/content/awards' : '/dashboard/content/awards/'.$award->id;
        $from = $award === null ? '/dashboard/content/awards/new' : $url;

        return $award === null ? $this->from($from)->post($url, $data) : $this->from($from)->put($url, $data);
    }

    private function site(): string
    {
        $this->app->forgetScopedInstances();

        return (string) $this->get('/en/awards/')->getContent();
    }

    public function test_publishing_needs_both_languages_and_the_owners_confirmation(): void
    {
        $this->get('/dashboard/content/awards')->assertOk()->assertSee('لا توجد جوائز بعد.');
        $this->get('/dashboard/content/awards/new')->assertOk()->assertSee('أؤكد أن هذه الجائزة حقيقية');

        $this->save(null, $this->award(['confirm' => null]))->assertSessionHasErrors(['confirm']);
        $this->save(null, $this->award(['issuer_en' => '', 'description_ar' => 'وصف فقط بالعربية']))->assertSessionHasErrors(['issuer_en', 'description_en']);
        $this->save(null, $this->award(['year' => '1999', 'evidence_url' => 'http://example.test']))->assertSessionHasErrors(['year', 'evidence_url']);
        $this->assertSame(0, Award::query()->count(), 'nothing saved');

        $this->save(null, $this->award(['status' => 'draft', 'title_en' => '', 'confirm' => null]))->assertSessionHasNoErrors();
        $draft = Award::query()->firstOrFail();
        $this->assertSame('draft', $draft->status, 'a draft may stay incomplete');
        $this->get('/en/awards/')->assertNotFound();

        $this->save($draft, $this->award())->assertRedirect('/dashboard/content/awards/'.$draft->id)->assertSessionHas('status');
        $fact = Fact::query()->where('key', $draft->factKey())->firstOrFail();
        $this->assertSame([FactStatus::Approved, $this->owner->id, 'OWNER-DASHBOARD'], [$fact->status, $fact->approved_by, $fact->decision_ref]);
        $this->assertStringContainsString('Best Specialty Coffee', $this->site());
        $this->assertStringContainsString('منشورة على الموقع', (string) $this->get('/dashboard/content/awards')->getContent());
        $this->assertStringContainsString('أكّدت هذه المعلومات', (string) $this->get('/dashboard/content/awards/'.$draft->id)->getContent());
    }

    public function test_changing_a_confirmed_award_needs_a_new_confirmation(): void
    {
        $this->save(null, $this->award());
        $award = Award::query()->firstOrFail();

        $this->save($award, $this->award(['year' => '2024', 'confirm' => null]))->assertSessionHasErrors(['confirm']);
        $this->assertSame(2025, $award->refresh()->year, 'refused → unchanged and still live');
        $this->assertStringContainsString('Best Specialty Coffee', $this->site());

        $this->save($award, $this->award(['description_ar' => 'وصف', 'description_en' => 'A description', 'confirm' => null]))->assertSessionHasNoErrors();
        $this->assertStringContainsString('A description', $this->site(), 'the description is not part of the confirmed claim');

        $this->save($award, $this->award(['title_en' => 'Best Coffee House', 'year' => '2024']))->assertSessionHasNoErrors();
        $this->assertSame(['SUPERSEDED', 'APPROVED'], Fact::query()->where('key', $award->factKey())->orderBy('id')->pluck('status')->map(fn ($s) => $s->value)->all());
        $this->assertStringContainsString('Best Coffee House', $this->site());
    }

    public function test_the_image_must_be_approved_and_archiving_takes_the_award_down(): void
    {
        $pending = app(MediaLibrary::class)->import(MediaLibraryTest::imageFile(seed: 1), ['source' => 'shelter', 'people_consent' => 'none']);
        $this->save(null, $this->award(['media_id' => (string) $pending->id]))->assertSessionHasErrors(['media_id']);

        $pending->forceFill(['approval_status' => Media::APPROVED, 'ok_website' => true, 'alt_ar' => 'كأس', 'alt_en' => 'A trophy'])->save();
        app(MediaLibrary::class)->generateVariants($pending);
        $this->get('/dashboard/content/awards/new')->assertSee('dashboard/content/media/'.$pending->id.'/preview', false);
        $this->save(null, $this->award(['media_id' => (string) $pending->id]))->assertSessionHasNoErrors();
        $award = Award::query()->firstOrFail();
        $this->assertSame($pending->id, $award->media_id);
        $this->assertStringContainsString('A trophy', $this->site());

        $this->post('/dashboard/content/awards/'.$award->id.'/archive')->assertSessionHas('status');
        $this->app->forgetScopedInstances();
        $this->get('/en/awards/')->assertNotFound();
        $this->assertStringContainsString('مؤرشفة', (string) $this->get('/dashboard/content/awards?show=archived')->getContent());
        $this->post('/dashboard/content/awards/'.$award->id.'/restore');
        $this->assertStringContainsString('Best Specialty Coffee', $this->site());
        $this->assertSame(1, Award::query()->count(), 'nothing is deleted');
    }
}
