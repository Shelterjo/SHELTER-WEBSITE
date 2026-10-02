<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\Recruitment\Application;
use App\Models\Recruitment\ApplicationMeeting;
use App\Models\Recruitment\PipelineStage;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Franchise\FranchiseForm;
use App\Services\Franchise\PartnershipSubmitter;
use App\Services\Franchise\PartnershipValidator;
use Database\Seeders\FranchiseSeeder;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/** Dashboard → Partnerships (docs/franchise/04, FRAN-054…065, M50): stages as settings, human decisions, no delete. */
class PartnershipsInboxTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        app(FranchiseSeeder::class)->run();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create(['name' => 'Owner']);
        $this->actingAs($this->owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp()]);
    }

    /** @param  array<string, mixed>  $overrides */
    private function apply(array $overrides = []): Application
    {
        $result = app(PartnershipValidator::class)->validate($overrides + [
            'full_name' => 'Test Partner', 'phone' => '0791234567', 'email' => 'partner@example.com', 'country' => 'JO', 'city' => 'Irbid',
            'market' => 'North', 'partnership_interest_type' => 'single_location', 'experience_band' => 'y3_5', 'experience_text' => '',
            'owns_business' => 'yes', 'location_status' => 'searching', 'introduction' => "Hello.\nA short note.",
            'non_binding_acknowledgement' => '1', 'data_processing_consent' => '1',
        ]);
        $this->assertSame([], $result['errors']);
        $form = app(FranchiseForm::class);
        $ack = $form->acknowledgement();
        $consent = $form->consent();
        $this->assertNotNull($ack);
        $this->assertNotNull($consent);

        return app(PartnershipSubmitter::class)->submit($result['data'], ['utm_source' => 'instagram'], 'en', (string) Str::uuid(), $ack, $consent);
    }

    public function test_the_overview_list_and_search(): void
    {
        $this->apply();
        $this->apply(['full_name' => 'Gulf Investor', 'phone' => '+971501234567', 'email' => 'gulf@example.com', 'country' => 'AE', 'city' => 'Dubai', 'market' => 'UAE']);

        $html = (string) $this->get('/dashboard/requests/partnerships')->assertOk()->getContent();
        $this->assertSame(2, substr_count($html, 'role="rowheader"'));
        $this->assertStringContainsString('أين الطلب؟', $html);
        $this->assertStringContainsString('Dubai', $html);
        $this->assertStringContainsString('جديد / تم الاستلام <bdi>(2)</bdi>', $html, 'stage labels come from the settings table');

        $count = fn (string $query): int => substr_count((string) $this->get('/dashboard/requests/partnerships?'.$query)->getContent(), 'role="rowheader"');
        $this->assertSame(1, $count('q=gulf'));
        $this->assertSame(1, $count('q=dubai'));
        $this->assertSame(1, $count('country=AE'));
        $this->assertSame(1, $count('q='.urlencode('079 123 4567')));
        $this->assertSame(2, $count('status=new'));
    }

    public function test_stage_changes_meetings_notes_and_archive_without_delete(): void
    {
        $application = $this->apply();
        $url = '/dashboard/requests/partnerships/'.$application->id;
        $html = (string) $this->get($url)->assertOk()->getContent();
        $this->assertNotNull($application->refresh()->first_viewed_at);
        $this->assertStringContainsString('instagram', $html, 'where they came from');
        $this->assertStringContainsString('partnership-ack-v1', $html);
        $this->assertStringContainsString('partnership-consent-v1', $html);

        $this->from($url)->post($url.'/status', ['status' => 'qualified', 'note' => 'Good market'])->assertSessionHas('status');
        $this->assertSame('qualified', $application->refresh()->status);
        $this->from($url)->post($url.'/status', ['status' => 'interview_shortlisted'])->assertSessionHasErrors(['status']);

        $this->post($url.'/meetings', ['date' => '2026-10-20', 'time' => '', 'channel' => 'fax'])->assertSessionHasErrors(['date', 'channel']);
        $this->post($url.'/meetings', ['date' => '2026-10-20', 'time' => '11:00', 'channel' => 'video', 'place' => 'Online'])->assertRedirect($url.'#meetings');
        $meeting = ApplicationMeeting::query()->firstOrFail();
        $this->put($url.'/meetings/'.$meeting->id, ['state' => 'done']);
        $this->assertSame('done', $meeting->refresh()->state);
        $this->assertStringContainsString('مكالمة فيديو', (string) $this->get($url)->getContent());

        $this->post($url.'/notes', ['body' => 'Send the overview deck'])->assertRedirect($url.'#notes');
        $this->assertStringContainsString('Send the overview deck', (string) $this->get($url)->getContent());

        $this->post($url.'/status', ['status' => 'archived']);
        $this->post($url.'/restore');
        $this->assertSame('qualified', $application->refresh()->status);
        $this->delete('/dashboard/requests/careers/'.$application->id)->assertRedirect('/dashboard/confirm');
        $this->assertNotNull(Application::query()->find($application->id), 'partnerships are never deleted');
        $this->assertStringNotContainsString('Test Partner', AuditLog::query()->get()->toJson(), 'no names in the audit');
    }

    public function test_the_stages_are_settings(): void
    {
        PipelineStage::query()->where('module', 'FR')->where('code', 'site_review')->update(['is_active' => false]);
        PipelineStage::query()->create(['module' => 'FR', 'code' => 'due_diligence', 'label_ar' => 'تدقيق', 'label_en' => 'Due diligence', 'sort' => 6]);
        $application = $this->apply();
        $html = (string) $this->get('/dashboard/requests/partnerships/'.$application->id)->getContent();
        $this->assertStringContainsString('تدقيق', $html);
        $this->assertStringNotContainsString('مراجعة الموقع', $html);
        $this->from('/x')->post('/dashboard/requests/partnerships/'.$application->id.'/status', ['status' => 'due_diligence'])->assertSessionHas('status');
        $this->assertSame('due_diligence', $application->refresh()->status);
        $this->get('/dashboard/requests/careers/'.$application->id)->assertNotFound();
    }
}
