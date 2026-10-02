<?php

namespace Tests\Feature\Dashboard;

use App\Models\AuditLog;
use App\Models\Recruitment\Application;
use App\Models\Recruitment\ApplicationAttachment;
use App\Models\Recruitment\UploadSession;
use App\Models\User;
use App\Services\Auth\OwnerSession;
use App\Services\Recruitment\ApplicationSubmitter;
use App\Services\Recruitment\ApplicationValidator;
use App\Services\Recruitment\AttachmentStore;
use App\Services\Recruitment\CareersForm;
use App\Services\Requests\CareersExport;
use Carbon\CarbonImmutable;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\Feature\Careers\CareersTestHelpers;
use Tests\TestCase;
use ZipArchive;

/** Dashboard → Job applications, several at once (CAREERS-068/069/072/073): one confirmation, safe exports, one ZIP. */
class CareersBulkTest extends TestCase
{
    use CareersTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->bootCareers();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $owner = User::factory()->withTwoFactor('JBSWY3DPEHPK3PXPJBSWY3DPEHPK3PXP')->create();
        $this->actingAs($owner)->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->getTimestamp()]);
    }

    /** @param  array<string, mixed>  $overrides */
    private function apply(array $overrides = []): Application
    {
        $result = app(ApplicationValidator::class)->validate($this->validInput($overrides), [$this->cityId()], CarbonImmutable::parse('2026-10-02'));
        $session = UploadSession::query()->create(['expires_at' => now()->addDay()]);
        $cv = app(AttachmentStore::class)->store($session, $this->pdf());
        $this->assertInstanceOf(ApplicationAttachment::class, $cv);

        return app(ApplicationSubmitter::class)->submit($result['data'], $session, $cv->id, (string) Str::uuid(), app(CareersForm::class)->consent() ?? throw new \RuntimeException);
    }

    /** @param  TestResponse<Response>  $response */
    private function file(TestResponse $response): string
    {
        $base = $response->baseResponse;
        if (! $base instanceof BinaryFileResponse) {
            $this->fail('Expected a file download.');
        }

        return $base->getFile()->getPathname();
    }

    public function test_a_bulk_status_change_asks_once_and_keeps_each_history(): void
    {
        $a = $this->apply();
        $b = $this->apply(['full_name' => 'Second Applicant', 'phone' => '0781112233', 'email' => 'second@example.com', 'national_id' => '9997654321']);
        $this->get('/dashboard/requests/careers?period=all')->assertSee('data-select-item', false);

        $this->post('/dashboard/requests/careers/bulk', ['action' => 'status:under_review'])->assertSessionHas('warning');
        $this->post('/dashboard/requests/careers/bulk', ['action' => 'status:under_review', 'ids' => [$a->id, $b->id]])
            ->assertOk()->assertSee('أنت على وشك تغيير حالة طلبين إلى «قيد المراجعة».');
        $this->assertSame('received', $a->refresh()->status, 'nothing changes before the confirmation');

        $this->post('/dashboard/requests/careers/bulk/apply', ['status' => 'under_review', 'ids' => [$a->id, $b->id, 999999]])
            ->assertRedirect('/dashboard/requests/careers')->assertSessionHas('status', 'تغيّرت حالة طلبين.');
        $this->assertSame(['under_review', 'under_review'], [$a->refresh()->status, $b->refresh()->status]);
        $this->assertSame(2, AuditLog::query()->where('action', 'application.status_changed')->count(), 'each keeps its own history');
        $bulk = AuditLog::query()->where('action', 'applications.bulk_status_changed')->firstOrFail();
        $this->assertSame(['selected' => 2, 'changed' => 2], $bulk->meta);
        $this->post('/dashboard/requests/careers/bulk', ['action' => 'delete', 'ids' => [$a->id]])->assertSessionHas('warning');
        $this->assertSame(2, Application::query()->count(), 'there is no bulk permanent delete');
    }

    public function test_exports_mask_the_identity_and_never_start_a_formula(): void
    {
        $this->apply(['full_name' => '=HYPERLINK("http://evil")']);
        $this->get('/dashboard/requests/careers/export?scope=filtered&period=all')->assertOk()->assertSee('أظهر أرقام الهوية كاملة');

        $csv = $this->post('/dashboard/requests/careers/export', ['scope' => 'all', 'format' => 'csv'])->assertOk();
        $this->assertStringContainsString('no-store', (string) $csv->headers->get('Cache-Control'));
        $body = (string) $csv->getContent();
        $this->assertStringStartsWith("\u{FEFF}", $body, 'UTF-8 for Arabic in Excel');
        $this->assertStringContainsString("'=HYPERLINK", $body, 'formula injection neutralised');
        $this->assertStringNotContainsString('9991234567', $body, 'identity masked by default');
        $this->assertStringContainsString('4567', $body);

        $full = (string) $this->post('/dashboard/requests/careers/export', ['scope' => 'all', 'format' => 'csv', 'full_identity' => '1'])->getContent();
        $this->assertStringContainsString('9991234567', $full, 'only when asked explicitly');
        $this->assertSame(['masked', 'full'], AuditLog::query()->where('action', 'applications.exported')->orderBy('id')->get()->map(fn ($l) => $l->meta['identity'] ?? null)->all());
        $this->assertStringNotContainsString('HYPERLINK', (string) json_encode(AuditLog::query()->where('action', 'applications.exported')->pluck('meta')), 'no personal data in the log');

        $xlsx = $this->post('/dashboard/requests/careers/export', ['scope' => 'all', 'format' => 'xlsx'])->assertOk();
        $path = $this->file($xlsx);
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path) === true);
        $sheet = (string) $zip->getFromName('xl/worksheets/sheet1.xml');
        $this->assertStringContainsString('rightToLeft="1"', $sheet);
        $this->assertStringContainsString('&apos;=HYPERLINK', $sheet);
        $zip->close();

        $this->post('/dashboard/requests/careers/export', ['scope' => 'all', 'format' => 'print'])->assertOk()->assertSee('اطبع أو احفظ PDF')
            ->assertDontSee('dashboard.requests.', false);
        $this->assertStringNotContainsString('dashboard.requests.', $body, 'every column has its name');
    }

    public function test_attachments_come_as_one_zip_by_application_number(): void
    {
        $a = $this->apply();
        $response = $this->post('/dashboard/requests/careers/attachments', ['ids' => [$a->id]])->assertOk();
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($this->file($response)) === true);
        $this->assertSame(1, $zip->numFiles);
        $this->assertStringStartsWith($a->reference_number.'/', (string) $zip->getNameIndex(0), 'a folder per application number, never a name');
        $zip->close();
        $this->assertSame(['applications' => 1, 'files' => 1], AuditLog::query()->where('action', 'attachments.zip_downloaded')->firstOrFail()->meta);
        $this->post('/dashboard/requests/careers/attachments', ['ids' => range(1, CareersExport::ZIP_MAX_APPLICATIONS + 1)])->assertSessionHas('warning');
    }

    public function test_exports_need_a_fresh_confirmation(): void
    {
        $this->withSession([OwnerSession::LOGIN_AT => now()->getTimestamp(), OwnerSession::CONFIRMED_AT => now()->subMinutes(30)->getTimestamp()]);
        $this->get('/dashboard/requests/careers/export')->assertRedirect('/dashboard/confirm');
        $this->post('/dashboard/requests/careers/export', ['scope' => 'all', 'format' => 'csv'])->assertRedirect('/dashboard/confirm');
    }
}
