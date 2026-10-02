<?php

namespace Tests\Feature\Careers;

use App\Models\Recruitment\Application;
use App\Models\Recruitment\ApplicationAttachment;
use App\Models\Recruitment\JobApplication;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/** RECRUITMENT-TEST-PLAN §2 (F-01 … F-10) and §3 (T-01 … T-04), through the public routes. */
class CareersFormTest extends TestCase
{
    use CareersTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->bootCareers();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    /** @return array<string, string> anti-bot fields of a form rendered 30 seconds ago */
    private function formFields(?string $key = null): array
    {
        return ['form_token' => Crypt::encryptString((string) (now()->getTimestamp() - 30)), 'idempotency_key' => $key ?? (string) Str::uuid()];
    }

    /** @return TestResponse<Response> */
    private function uploadFile(UploadedFile $file): TestResponse
    {
        return $this->postJson('/ar/careers/uploads/', ['file' => $file]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return TestResponse<Response>
     */
    private function submitForm(array $overrides = [], ?string $key = null): TestResponse
    {
        return $this->post('/ar/careers/', $this->validInput($overrides) + $this->formFields($key));
    }

    public function test_page_shows_the_six_groups_and_the_approved_options_in_arabic_only(): void
    {
        $html = (string) $this->get('/ar/careers/')->assertOk()->getContent();

        foreach (['البيانات الشخصية', 'السكن', 'المؤهل والخبرة', 'معلومات العمل', 'المرفقات', 'الإقرار'] as $group) {
            $this->assertStringContainsString($group, $html);
        }
        foreach (['توجيهي ناجح', 'على مقاعد الدراسة', 'أكثر من 10 سنوات', 'كانون الثاني (1)', 'اسحب الملفات هنا أو اضغط للرفع'] as $text) {
            $this->assertStringContainsString($text, $html);
        }
        $this->assertStringNotContainsString('دبلوم', $html);
        // D-315: the Owner's 12 entries, in his order.
        preg_match('#<select[^>]*id="city_id".*?</select>#su', $html, $select);
        preg_match_all('#<option value="\d+"\s*>([^<]+)</option>#u', $select[0] ?? '', $cities);
        $this->assertSame(['عمان', 'إربد', 'الزرقاء', 'البلقاء', 'المفرق', 'جرش', 'عجلون', 'مادبا', 'الكرك', 'الطفيلة', 'معان', 'العقبة'], array_map('trim', $cities[1]));
        $this->assertStringContainsString('أقر بأن جميع المعلومات المدخلة في طلب التوظيف صحيحة', $html);
        $this->assertStringContainsString('name="website"', $html, 'honeypot');

        $en = (string) $this->get('/en/careers/')->assertOk()->getContent();
        $this->assertStringContainsString('href="http://localhost/ar/careers/#apply"', $en);
        $this->assertStringNotContainsString('<form class="ui-apply__form"', $en, 'no English form');
    }

    public function test_the_form_is_closed_without_verified_cities(): void
    {
        DB::table('jordan_cities')->update(['is_active' => false]);
        $html = (string) $this->get('/ar/careers/')->assertOk()->getContent();
        $this->assertStringContainsString('التقديم عبر الموقع غير متاح حاليًا.', $html);
        $this->assertStringNotContainsString('data-careers-form', $html);
        $this->post('/ar/careers/', $this->validInput() + $this->formFields())->assertNotFound();
        $this->uploadFile($this->pdf())->assertNotFound();
    }

    public function test_f01_a_complete_application_with_several_safe_files_is_received(): void
    {
        foreach ([$this->pdf('Ahmad CV.pdf'), $this->docx(), $this->png()] as $file) {
            $this->uploadFile($file)->assertCreated()->assertJsonStructure(['file' => ['id', 'name', 'size', 'family'], 'cv' => ['primary', 'state']]);
        }

        $response = $this->submitForm();
        $response->assertRedirect('http://localhost/ar/careers/submitted/');
        $application = Application::query()->sole();
        $job = JobApplication::query()->sole();
        $this->assertSame('JOB', $application->type);
        $this->assertMatchesRegularExpression('/^JOB-\d{4}-00101$/', $application->reference_number, 'D-324');
        $this->assertSame('+962791234567', $job->phone_normalized);
        $this->assertSame('applicant.test@example.com', DB::table('job_applications')->value('email_normalized'));
        $this->assertSame('2000-02-29', $job->birth_date->toDateString());
        $this->assertSame('450.00', (string) $job->expected_salary_jod);
        $this->assertSame(3, $application->attachments()->count());
        $primary = ApplicationAttachment::query()->find($job->primary_attachment_id);
        $this->assertSame('Ahmad CV.pdf', $primary?->original_filename);
        foreach ($application->attachments as $attachment) {
            Storage::disk('careers')->assertExists($attachment->storage_path);
            Storage::disk('careers')->assertMissing('tmp/'.$attachment->storage_key);
        }
        $this->assertTrue(DB::table('application_consents')->where('application_id', $application->id)->where('accepted', true)->exists());
        $this->assertSame('received', DB::table('application_status_history')->where('application_id', $application->id)->value('new_status'));

        // Success page: the number from the session, nothing sensitive in the URL; the page cannot be revisited.
        $html = (string) $this->get('/ar/careers/submitted/')->assertOk()->getContent();
        $this->assertStringContainsString($application->reference_number, $html);
        $this->assertStringContainsString('تم استلام طلب التوظيف بنجاح', $html);
        $this->get('/ar/careers/submitted/')->assertRedirect('http://localhost/ar/careers/');
    }

    public function test_f02_f03_dangerous_files_are_refused_by_their_content(): void
    {
        $cases = [
            UploadedFile::fake()->createWithContent('setup.exe', "MZ\x90\x00binary"),
            UploadedFile::fake()->createWithContent('shell.php', '<?php echo 1;'),
            UploadedFile::fake()->createWithContent('app.js', 'alert(1)'),
            UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
            UploadedFile::fake()->createWithContent('page.html', '<html></html>'),
            $this->docx('macro.docm', macro: true),
            $this->docx('hidden-macro.docx', macro: true),
            UploadedFile::fake()->createWithContent('files.zip', "PK\x03\x04archive"),
            UploadedFile::fake()->createWithContent('cv.pdf', "MZ\x90\x00not a pdf"),
            UploadedFile::fake()->createWithContent('cv.pdf.php', '%PDF-1.4 fake'),
            UploadedFile::fake()->createWithContent('cv.pdf', "%PDF-1.4\n/OpenAction << /S /JavaScript /JS (app.alert(1)) >>"),
        ];
        foreach ($cases as $file) {
            $response = $this->uploadFile($file)->assertUnprocessable();
            $this->assertNotSame('', (string) $response->json('message'), $file->getClientOriginalName());
        }
        $this->assertSame(0, ApplicationAttachment::query()->count());
        $this->assertSame([], Storage::disk('careers')->allFiles());
    }

    public function test_f04_a_file_over_the_technical_limit_is_refused_and_nothing_is_lost(): void
    {
        config(['careers.uploads.max_file_bytes' => 1024]);
        $this->uploadFile(UploadedFile::fake()->createWithContent('big.txt', str_repeat('a', 4096)))
            ->assertUnprocessable()->assertJson(['error' => 'too_large']);

        // The same refusal through the no-JavaScript path keeps every typed value (except the identity number).
        $response = $this->post('/ar/careers/', $this->validInput() + $this->formFields() + ['files' => [UploadedFile::fake()->createWithContent('big.txt', str_repeat('a', 4096))]]);
        $response->assertRedirect('http://localhost/ar/careers/');
        $response->assertSessionHasErrors('files');
        $this->assertSame('متقدم تجريبي Test', session()->getOldInput('full_name'));
        $this->assertNull(session()->getOldInput('national_id'));
    }

    public function test_f06_stored_files_are_not_reachable_from_the_web(): void
    {
        $id = (int) $this->uploadFile($this->pdf())->assertCreated()->json('file.id');
        $attachment = ApplicationAttachment::query()->findOrFail($id);
        $this->assertTrue(str_starts_with((string) config('filesystems.disks.careers.root'), storage_path()), 'outside public/');
        $this->get('/storage/'.$attachment->storage_path)->assertNotFound();
        $this->get('/'.$attachment->storage_path)->assertNotFound();
    }

    public function test_f08_a_double_submit_creates_one_application_with_the_same_number(): void
    {
        $this->uploadFile($this->pdf())->assertCreated();
        $key = (string) Str::uuid();
        $this->submitForm([], $key)->assertRedirect('http://localhost/ar/careers/submitted/');
        $first = (string) $this->get('/ar/careers/submitted/')->getContent();

        $this->submitForm([], $key)->assertRedirect('http://localhost/ar/careers/submitted/');
        $this->assertSame(1, JobApplication::query()->count());
        $number = Application::query()->value('reference_number');
        $this->assertStringContainsString((string) $number, $first);
        $this->assertStringContainsString((string) $number, (string) $this->get('/ar/careers/submitted/')->getContent());
    }

    public function test_f09_bots_and_forged_requests_are_refused(): void
    {
        $this->uploadFile($this->pdf())->assertCreated();
        $this->submitForm(['website' => 'http://spam.example'])->assertSessionHasErrors('form');
        $this->post('/ar/careers/', $this->validInput() + ['form_token' => Crypt::encryptString((string) now()->getTimestamp()), 'idempotency_key' => (string) Str::uuid()])
            ->assertSessionHasErrors('form'); // faster than a person can fill it
        $this->post('/ar/careers/', $this->validInput() + ['form_token' => 'forged', 'idempotency_key' => (string) Str::uuid()])
            ->assertSessionHasErrors('form');
        $this->assertSame(0, JobApplication::query()->count());

        // CSRF is enforced on the real stack (Laravel skips it only while running tests, so leave "testing" briefly).
        $this->withMiddleware(ValidateCsrfToken::class);
        $this->app->detectEnvironment(fn (): string => 'local');
        try {
            $this->post('/ar/careers/', $this->validInput() + $this->formFields())->assertStatus(419);
        } finally {
            $this->app->detectEnvironment(fn (): string => 'testing');
        }
    }

    public function test_f10_submissions_and_uploads_are_rate_limited(): void
    {
        config(['careers.abuse.uploads_per_hour' => 2]);
        $this->uploadFile($this->pdf())->assertCreated();
        $this->uploadFile($this->png())->assertCreated();
        $this->uploadFile($this->png('c.png'))->assertStatus(429);

        // The phone flood guard (not a ban on re-applying): a 4th application in 24 hours waits.
        config(['careers.abuse.submit_per_hour' => 100, 'careers.abuse.submit_per_day' => 100, 'careers.abuse.uploads_per_hour' => 100]);
        $this->flushSession(); // a fresh form: the files above stay behind as an unsubmitted draft
        for ($i = 0; $i < 3; $i++) {
            $this->uploadFile($this->pdf())->assertCreated();
            $this->submitForm(['email' => "p{$i}@example.com"])->assertRedirect('http://localhost/ar/careers/submitted/');
        }
        $this->uploadFile($this->pdf())->assertCreated();
        $this->submitForm(['email' => 'p9@example.com'])->assertSessionHasErrors('form');
        $this->assertSame(3, JobApplication::query()->count());
    }

    public function test_server_side_validation_reports_every_problem_in_form_order(): void
    {
        $response = $this->post('/ar/careers/', $this->formFields());
        $response->assertRedirect('http://localhost/ar/careers/');
        // The summary lists the problems top to bottom, each linked to its field.
        $html = (string) $this->get('/ar/careers/')->getContent();
        $this->assertStringContainsString('class="ui-error-summary ui-apply__summary"', $html);
        $positions = array_map(fn (string $id): int|false => strpos($html, '<a href="#'.$id.'">'), ['full_name', 'birth_date', 'city_id', 'notes', 'files', 'consent']);
        $this->assertNotContains(false, $positions);
        $sorted = $positions;
        sort($sorted);
        $this->assertSame($sorted, $positions);
        $this->assertStringContainsString('<a href="#full_name">الاسم الكامل: هذا الحقل مطلوب.</a>', $html, 'each line names its field');
        $this->assertStringContainsString('<legend class="ui-fieldset__legend">', $html);
        $this->assertMatchesRegularExpression('#الجنس\s*<span class="ui-field__required" aria-hidden="true">\*</span>#u', $html);
    }

    public function test_fixing_one_field_and_resending_quickly_is_not_taken_for_a_bot(): void
    {
        $this->uploadFile($this->pdf())->assertCreated();
        $fields = $this->formFields();
        $this->post('/ar/careers/', $this->validInput(['area' => '']) + $fields)->assertRedirect('http://localhost/ar/careers/');

        // The page after the error carries the ORIGINAL token, so an immediate resend passes the minimum-time check.
        $html = (string) $this->get('/ar/careers/')->getContent();
        $this->assertSame(1, preg_match('/name="form_token" value="([^"]+)"/', $html, $token));
        $this->assertSame($fields['form_token'], html_entity_decode($token[1] ?? ''));
        $this->post('/ar/careers/', $this->validInput() + ['form_token' => html_entity_decode($token[1] ?? ''), 'idempotency_key' => $fields['idempotency_key']])
            ->assertRedirect('http://localhost/ar/careers/submitted/');
    }

    public function test_an_unsure_cv_is_chosen_by_the_applicant_never_guessed(): void
    {
        $a = (int) $this->uploadFile($this->png('a.png'))->json('file.id');
        $response = $this->uploadFile($this->png('b.png'))->assertCreated();
        $this->assertSame('needs_choice', $response->json('cv.state'));

        $this->submitForm()->assertSessionHasErrors('files');
        $this->submitForm(['primary_attachment' => (string) $a])->assertRedirect('http://localhost/ar/careers/submitted/');
        $this->assertSame($a, JobApplication::query()->sole()->primary_attachment_id);
        $this->assertSame('applicant_selected', ApplicationAttachment::query()->find($a)?->cv_detection);
    }

    public function test_a_draft_file_can_be_removed_only_from_its_own_session(): void
    {
        $id = (int) $this->uploadFile($this->pdf())->json('file.id');
        $this->deleteJson("/ar/careers/uploads/{$id}/")->assertOk()->assertJson(['cv' => ['state' => 'none']]);
        $this->assertNull(ApplicationAttachment::query()->find($id));

        $other = (int) $this->uploadFile($this->pdf())->json('file.id');
        $this->flushSession();
        $this->deleteJson("/ar/careers/uploads/{$other}/")->assertNotFound();
    }

    public function test_t01_to_t04_tracking_shows_the_public_status_only(): void
    {
        $this->uploadFile($this->pdf())->assertCreated();
        $this->submitForm()->assertRedirect();
        $application = Application::query()->sole();
        $application->update(['status' => 'interviewed']);

        $html = (string) $this->post('/ar/careers/track/', ['number' => strtolower($application->reference_number), 'phone' => '+962 79 123 4567'])->assertOk()->getContent();
        $this->assertStringContainsString('قيد المراجعة', $html);
        foreach (['applicant.test@example.com', '9991234567', 'cv.pdf', 'سطر أول', 'تمت المقابلة'] as $private) {
            $this->assertStringNotContainsString($private, $html);
        }

        $wrongPhone = (string) $this->post('/ar/careers/track/', ['number' => $application->reference_number, 'phone' => '0790000000'])->getContent();
        $wrongNumber = (string) $this->post('/ar/careers/track/', ['number' => 'JOB-2026-99999', 'phone' => '0791234567'])->getContent();
        $this->assertStringContainsString('تعذر العثور على الطلب. تحقق من البيانات وحاول مجددًا.', $wrongPhone);
        $this->assertStringContainsString('تعذر العثور على الطلب. تحقق من البيانات وحاول مجددًا.', $wrongNumber);

        // 5 tries per 15 minutes per address, then a cooldown — even for the right answer.
        for ($i = 0; $i < 3; $i++) {
            $this->post('/ar/careers/track/', ['number' => 'JOB-2026-99999', 'phone' => '0791234567']);
        }
        $html = (string) $this->post('/ar/careers/track/', ['number' => $application->reference_number, 'phone' => '0791234567'])->getContent();
        $this->assertStringContainsString('محاولات كثيرة. حاول بعد قليل.', $html);
    }
}
