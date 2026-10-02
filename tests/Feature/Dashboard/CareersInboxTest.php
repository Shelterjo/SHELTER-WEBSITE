<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\Recruitment\Application;
use App\Models\Recruitment\ApplicationAttachment;
use App\Models\Recruitment\ApplicationNote;
use App\Models\Recruitment\InterviewLocation;
use App\Models\Recruitment\UploadSession;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Recruitment\ApplicationSubmitter;
use App\Services\Recruitment\ApplicationTracker;
use App\Services\Recruitment\ApplicationValidator;
use App\Services\Recruitment\AttachmentStore;
use App\Services\Recruitment\CareersForm;
use Carbon\CarbonImmutable;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\Careers\CareersTestHelpers;
use Tests\TestCase;

/** Dashboard → Job applications (CAREERS-052…070, RECRUITMENT-SECURITY, M50): the Owner's inbox, without leaks. */
class CareersInboxTest extends TestCase
{
    use CareersTestHelpers;
    use RefreshDatabase;

    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->bootCareers();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create(['name' => 'Owner']);
        $this->actingAs($this->owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp()]);
    }

    /** @param  array<string, mixed>  $overrides */
    private function apply(array $overrides = []): Application
    {
        $result = app(ApplicationValidator::class)->validate($this->validInput($overrides), [$this->cityId()], CarbonImmutable::parse('2026-10-02'));
        $this->assertSame([], $result['errors']);
        $session = UploadSession::query()->create(['expires_at' => now()->addDay()]);
        $cv = app(AttachmentStore::class)->store($session, $this->pdf());
        $this->assertInstanceOf(ApplicationAttachment::class, $cv);
        $consent = app(CareersForm::class)->consent();
        $this->assertNotNull($consent);

        return app(ApplicationSubmitter::class)->submit($result['data'], $session, $cv->id, (string) Str::uuid(), $consent);
    }

    private function confirmed(): void
    {
        $this->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
    }

    public function test_the_list_searches_and_filters_but_never_by_or_with_the_identity_number(): void
    {
        $first = $this->apply();
        $second = $this->apply(['full_name' => 'Second Applicant', 'phone' => '0781112233', 'email' => 'second@example.com', 'national_id' => '9997654321', 'job_title' => 'Cashier', 'gender' => 'male']);

        $html = (string) $this->get('/dashboard/requests/careers?period=all')->assertOk()->getContent();
        $this->assertSame(2, substr_count($html, 'role="rowheader"'));
        $this->assertSame(2, preg_match_all('/>\s*جديد\s*<\/span>/u', $html), 'both are new (not opened yet)');
        $this->assertStringNotContainsString('9991234567', $html);
        $this->assertStringNotContainsString('4567</', $html, 'not even the last digits in the list');
        $this->assertStringContainsString('طلبات جديدة', $html);

        $count = fn (string $query): int => substr_count((string) $this->get('/dashboard/requests/careers?period=all&'.$query)->getContent(), 'role="rowheader"');
        $this->assertSame(1, $count('q='.urlencode('078 111 2233')), 'phone in any format');
        $this->assertSame(1, $count('q='.$first->reference_number));
        $this->assertSame(1, $count('q=second'));
        $this->assertSame(1, $count('q=cashier'));
        $this->assertSame(0, $count('q=9997654321'), 'the identity number is not searchable (CAREERS-058)');
        $this->assertSame(1, $count('gender=male'));
        $this->assertSame(1, $count('status=new&q=second'));
        $this->assertSame('Second Applicant', $second->job?->full_name);

        $home = (string) $this->get('/dashboard')->getContent();
        $this->assertMatchesRegularExpression('/status=new[^"]*">طلبات توظيف جديدة<\/a>\s*<\/p>\s*<p class="ui-stat-tile__value"><bdi>2<\/bdi>/u', $home);
    }

    public function test_opening_an_application_marks_it_seen_and_shows_the_identity_masked(): void
    {
        $application = $this->apply();
        $html = (string) $this->get('/dashboard/requests/careers/'.$application->id)->assertOk()->getContent();
        $this->assertNotNull($application->refresh()->first_viewed_at);
        $this->assertSame('received', $application->status, 'seeing is not a status change');
        $this->assertStringContainsString('********4567', $html);
        $this->assertStringNotContainsString('9991234567', $html);
        $this->assertStringContainsString('tel:+962791234567', $html);
        $this->assertStringContainsString('السيرة الذاتية', $html);
        $this->assertStringContainsString('careers-consent-v1', $html);
        $this->assertSame(1, AuditLog::query()->where('action', 'application.first_viewed')->count());
        $this->get('/dashboard/requests/careers/'.$application->id);
        $this->assertSame(1, AuditLog::query()->where('action', 'application.first_viewed')->count(), 'only the first time');
    }

    public function test_status_changes_keep_a_history_and_the_applicant_sees_only_the_public_status(): void
    {
        $application = $this->apply();
        $url = '/dashboard/requests/careers/'.$application->id;
        $this->from($url)->post($url.'/status', ['status' => 'accepted', 'note' => 'Strong interview'])->assertRedirect($url)->assertSessionHas('status');
        $this->assertSame('accepted', $application->refresh()->status);
        $this->assertSame('under_review', app(ApplicationTracker::class)->publicStatus($application->reference_number, '0791234567'), 'never "accepted" (TD-PS-02)');
        $html = (string) $this->get($url)->getContent();
        $this->assertStringContainsString('أرشفة طلب «مقبول»', $html);
        $this->assertStringContainsString('Strong interview', $html);

        $this->post($url.'/status', ['status' => 'archived']);
        $this->assertSame(['archived', 'accepted'], [$application->refresh()->status, $application->status_before_archive]);
        $this->post($url.'/restore');
        $this->assertSame(['accepted', null], [$application->refresh()->status, $application->archived_at]);
        $this->from($url)->post($url.'/status', ['status' => 'hired'])->assertSessionHasErrors(['status']);

        $this->assertSame(['received', 'accepted', 'archived', 'accepted'], $application->statusHistory()->reorder()->orderBy('id')->pluck('new_status')->all());
        $audit = AuditLog::query()->where('action', 'application.status_changed')->get()->toJson(JSON_UNESCAPED_UNICODE);
        $this->assertStringNotContainsString('متقدم تجريبي', $audit, 'no names in the audit');
        $this->assertStringNotContainsString('Strong interview', $audit, 'nor the note');
    }

    public function test_notes_and_interviews(): void
    {
        $application = $this->apply();
        $url = '/dashboard/requests/careers/'.$application->id;
        $this->post($url.'/notes', ['body' => ''])->assertSessionHasErrors(['body']);
        $this->post($url.'/notes', ['body' => 'Call back on Sunday'])->assertRedirect($url.'#notes');
        $note = ApplicationNote::query()->firstOrFail();
        $this->put($url.'/notes/'.$note->id, ['body' => 'Call back on Monday']);
        $this->assertSame('Call back on Monday', $note->refresh()->body);
        $edit = AuditLog::query()->where('action', 'note.edited')->firstOrFail();
        $this->assertSame('Call back on Sunday', $edit->changes['before']['body'] ?? null, 'what it said before is kept');
        $this->put($url.'/notes/'.$note->id, ['body' => 'x', 'remove' => '1']);
        $this->assertSoftDeleted($note);
        $this->assertStringNotContainsString('Call back on Monday', (string) $this->get($url)->getContent());

        $location = InterviewLocation::query()->firstOrFail();
        $this->post($url.'/interview', ['date' => '2026-10-10', 'time' => '', 'location' => ''])->assertSessionHasErrors(['date', 'location']);
        $this->post($url.'/interview', ['date' => '2026-10-10', 'time' => '10:30', 'location' => (string) $location->id, 'notes' => 'Bring ID'])->assertRedirect($url.'#interview');
        $this->post($url.'/interview', ['date' => '2026-10-12', 'time' => '12:00', 'location' => (string) $location->id]);
        $html = (string) $this->get($url)->getContent();
        $this->assertStringContainsString('2026-10-12 · 12:00', $html);
        $this->assertStringContainsString('مواعيد سابقة', $html);
        $this->assertSame(1, $application->interviews()->where('is_current', true)->count());
        $this->assertSame('received', app(ApplicationTracker::class)->publicStatus($application->reference_number, '0791234567'));
    }

    public function test_the_identity_number_needs_a_fresh_confirmation_and_is_logged_without_the_number(): void
    {
        $application = $this->apply();
        $this->get('/dashboard/requests/careers/'.$application->id.'/identity')->assertRedirect('/dashboard/confirm');

        $this->confirmed();
        $response = $this->get('/dashboard/requests/careers/'.$application->id.'/identity')->assertOk();
        $response->assertSee('9991234567');
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $log = AuditLog::query()->where('action', 'identity.revealed')->firstOrFail();
        $this->assertStringNotContainsString('9991234567', (string) json_encode($log->toArray()));
        $this->assertStringNotContainsString('1234567', AuditLog::query()->get()->toJson(), 'the number is nowhere in the audit');
    }

    public function test_files_download_safely_and_only_for_the_owner(): void
    {
        $application = $this->apply();
        $file = $application->attachments()->firstOrFail();
        $response = $this->get('/dashboard/requests/attachments/'.$file->id)->assertOk();
        $this->assertStringContainsString('attachment;', (string) $response->headers->get('Content-Disposition'));
        $this->assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        $this->assertStringContainsString('sandbox', (string) $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertSame(1, AuditLog::query()->where('action', 'attachments.downloaded')->count());
        $this->get('/dashboard/requests/attachments/999999')->assertNotFound();

        auth()->logout();
        $this->get('/dashboard/requests/attachments/'.$file->id)->assertRedirect('/dashboard/login');
        $this->get('/dashboard/requests/careers')->assertRedirect('/dashboard/login');
    }

    public function test_permanent_deletion_only_from_the_archive_with_the_number_typed(): void
    {
        $application = $this->apply();
        $paths = $application->attachments()->pluck('storage_path')->all();
        $url = '/dashboard/requests/careers/'.$application->id;
        $this->confirmed();
        $this->get($url.'/delete')->assertNotFound();

        $this->post($url.'/status', ['status' => 'archived']);
        $this->get($url.'/delete')->assertOk()->assertSee($application->reference_number);
        $this->from($url.'/delete')->delete($url, ['reference' => 'JOB-2026-00000', 'understood' => '1'])->assertSessionHasErrors(['reference']);
        $this->from($url.'/delete')->delete($url, ['reference' => $application->reference_number])->assertSessionHasErrors(['reference']);
        $this->assertNotNull(Application::query()->find($application->id));

        $this->delete($url, ['reference' => strtolower($application->reference_number), 'understood' => '1'])->assertRedirect('/dashboard/requests/careers?status=archived');
        $this->assertNull(Application::query()->find($application->id));
        foreach ($paths as $path) {
            Storage::disk('careers')->assertMissing($path);
        }
        $tombstone = AuditLog::query()->where('action', 'application.permanently_deleted')->firstOrFail();
        $this->assertSame($application->reference_number, $tombstone->meta['reference'] ?? null);
        $this->assertStringNotContainsString('متقدم', (string) json_encode($tombstone->toArray(), JSON_UNESCAPED_UNICODE));
    }
}
