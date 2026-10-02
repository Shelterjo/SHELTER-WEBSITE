<?php

namespace Tests\Feature\Careers;

use App\Models\Recruitment\ApplicationAttachment;
use App\Models\Recruitment\JobApplication;
use App\Models\Recruitment\UploadSession;
use App\Services\Recruitment\ApplicantInput;
use App\Services\Recruitment\ApplicationSubmitter;
use App\Services\Recruitment\ApplicationTracker;
use App\Services\Recruitment\ApplicationValidator;
use App\Services\Recruitment\AttachmentStore;
use App\Services\Recruitment\CareersForm;
use App\Services\Recruitment\CvDetector;
use App\Services\Recruitment\FileInspector;
use App\Services\Recruitment\IdentityVault;
use Carbon\CarbonImmutable;
use Database\Seeders\MasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/** RECRUITMENT-TEST-PLAN §1 (U-01 … U-13) on the server-side services. */
class CareersServicesTest extends TestCase
{
    use CareersTestHelpers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(MasterDataSeeder::class);
        $this->bootCareers();
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{data: array<string, mixed>, errors: array<string, string>}
     */
    private function check(array $input): array
    {
        return app(ApplicationValidator::class)->validate($input, [$this->cityId()], CarbonImmutable::parse('2026-10-02'));
    }

    public function test_u01_free_text_is_kept_as_written_in_any_script(): void
    {
        $result = $this->check($this->validInput(['full_name' => "  Ahmad   أحمد\u{200F} Mix  ", 'job_title' => 'مدير  Shift']));

        $this->assertSame([], $result['errors']);
        $this->assertSame('Ahmad أحمد Mix', $result['data']['full_name']);
        $this->assertSame('مدير Shift', $result['data']['job_title']);
        $this->assertSame("سطر أول\nSecond line", $result['data']['notes']);
    }

    public function test_u02_phone_formats_are_accepted_and_normalised(): void
    {
        $cases = [
            '0791234567' => '+962791234567',
            '٠٧٩١٢٣٤٥٦٧' => '+962791234567',
            '+962 79 123 4567' => '+962791234567',
            '00962791234567' => '+962791234567',
            '+44 20 7946 0958' => '+442079460958',
        ];
        foreach ($cases as $raw => $expected) {
            $this->assertSame($expected, ApplicantInput::phone($raw), $raw);
        }
        $this->assertNull(ApplicantInput::phone('12'));
        $this->assertArrayHasKey('phone', $this->check($this->validInput(['phone' => 'abc']))['errors']);
    }

    public function test_u03_email_format(): void
    {
        $this->assertArrayHasKey('email', $this->check($this->validInput(['email' => 'not-an-email']))['errors']);
        $this->assertArrayNotHasKey('email', $this->check($this->validInput(['email' => 'a.b@example.co']))['errors']);
    }

    public function test_u04_birth_dates_follow_the_calendar(): void
    {
        $date = fn (string $d, string $m, string $y): array => $this->check($this->validInput(['birth_day' => $d, 'birth_month' => $m, 'birth_year' => $y]));

        $this->assertArrayHasKey('birth_date', $date('31', '4', '2000')['errors']);
        $this->assertArrayHasKey('birth_date', $date('30', '2', '2000')['errors']);
        $this->assertArrayHasKey('birth_date', $date('29', '2', '2003')['errors']);
        $this->assertSame('2004-02-29', $date('29', '2', '2004')['data']['birth_date']);
        $this->assertSame('2000-12-31', $date('31', '12', '2000')['data']['birth_date']);
    }

    public function test_u05_conditional_fields_are_required_when_shown(): void
    {
        $this->assertArrayHasKey('national_id', $this->check($this->validInput(['national_id' => '']))['errors']);

        $errors = $this->check($this->validInput(['nationality_type' => 'non_jordanian', 'national_id' => '']))['errors'];
        $this->assertArrayHasKey('nationality_text', $errors);
        $this->assertArrayHasKey('document_number', $errors);
        $this->assertArrayNotHasKey('national_id', $errors);

        $ok = $this->check($this->validInput(['nationality_type' => 'non_jordanian', 'nationality_text' => 'سوري', 'document_number' => 'n 12-34567']));
        $this->assertSame([], $ok['errors']);
        $this->assertSame('N1234567', $ok['data']['identity']);
        $this->assertSame('passport_or_document', $ok['data']['identity_type']);
    }

    public function test_u06_salary_is_a_positive_number_only(): void
    {
        $this->assertSame('450.00', $this->check($this->validInput(['expected_salary' => '450']))['data']['expected_salary']);
        $this->assertSame('450.00', $this->check($this->validInput(['expected_salary' => '٤٥٠']))['data']['expected_salary']);
        foreach (['-5', '0', 'abc', '1e9', '100000'] as $bad) {
            $this->assertArrayHasKey('expected_salary', $this->check($this->validInput(['expected_salary' => $bad]))['errors'], $bad);
        }
    }

    public function test_u07_u08_only_approved_options_and_consent_is_required(): void
    {
        $this->assertArrayHasKey('education_level', $this->check($this->validInput(['education_level' => 'diploma']))['errors']);
        $this->assertArrayHasKey('city_id', $this->check($this->validInput(['city_id' => '99999']))['errors']);
        $this->assertArrayHasKey('same_field_experience', $this->check($this->validInput(['same_field_experience' => 'maybe']))['errors']);
        $this->assertArrayHasKey('consent', $this->check($this->validInput(['consent' => null]))['errors']);
        // An empty form: every field is reported (the CV is checked by the form controller, with the files).
        $this->assertSame([
            'full_name', 'phone', 'email', 'gender', 'birth_date', 'marital_status', 'nationality_type', 'city_id', 'area', 'job_title',
            'education_level', 'experience_band', 'same_field_experience', 'currently_employed', 'expected_salary', 'has_driving_license',
            'notes', 'consent',
        ], array_keys($this->check([])['errors']));
    }

    public function test_u09_application_numbers_are_unique_and_sequential(): void
    {
        $numbers = [];
        for ($i = 0; $i < 25; $i++) {
            $numbers[] = $this->submitSample(['phone' => '07912345'.str_pad((string) $i, 2, '0', STR_PAD_LEFT), 'email' => "p{$i}@example.com", 'national_id' => '99912345'.str_pad((string) $i, 2, '0', STR_PAD_LEFT)])->application_number;
        }
        $this->assertSame(array_unique($numbers), $numbers);
        $year = now('Asia/Amman')->year;
        $this->assertSame("JOB-{$year}-00001", $numbers[0]);
        $this->assertSame("JOB-{$year}-00025", $numbers[24]);
    }

    public function test_u10_earlier_applications_are_linked_never_merged(): void
    {
        $first = $this->submitSample();
        $second = $this->submitSample(['phone' => '0781111111', 'email' => 'other@example.com']); // same national ID only
        $third = $this->submitSample(['national_id' => '1112223334', 'email' => 'other@example.com', 'phone' => '0772222222']);

        $this->assertSame(3, JobApplication::query()->count());
        $this->assertTrue(DB::table('application_links')->where(['application_id' => $second->id, 'linked_application_id' => $first->id, 'signal' => 'id_blind_index'])->exists());
        $this->assertTrue(DB::table('application_links')->where(['application_id' => $third->id, 'linked_application_id' => $second->id, 'signal' => 'email'])->exists());
        $this->assertSame(2, $second->fresh()?->applicant_group_size);
        $this->assertSame('متقدم تجريبي Test', $first->fresh()?->full_name, 'nothing on the earlier application changes');
    }

    public function test_u11_identity_is_encrypted_with_a_fresh_nonce_and_survives_key_rotation(): void
    {
        $vault = app(IdentityVault::class);
        $a = $vault->encrypt('9991234567');
        $b = $vault->encrypt('9991234567');
        $this->assertNotSame($a['ciphertext'], $b['ciphertext']);
        $this->assertSame('9991234567', $vault->decrypt($a['ciphertext'], $a['nonce'], 1));
        $this->assertSame($vault->blindIndex('9991234567'), $vault->blindIndex('9991234567'));

        $application = $this->submitSample();
        $row = DB::table('application_identity_secure')->where('application_id', $application->id)->first();
        $this->assertNotNull($row);
        $this->assertStringNotContainsString('9991234567', (string) $row->id_ciphertext);
        $this->assertSame('4567', $row->id_last4);
        $this->assertSame('********4567', IdentityVault::mask($row->id_last4));

        // Rotation: the old key moves to the previous keys and still decrypts.
        $old = config('careers.identity.encryption_key');
        config(['careers.identity.key_version' => 2, 'careers.identity.encryption_key' => base64_encode(random_bytes(32)), 'careers.identity.previous_keys' => '1:'.$old]);
        $this->assertSame('9991234567', app(IdentityVault::class)->decrypt((string) $row->id_ciphertext, (string) $row->id_nonce, 1));
    }

    public function test_u12_public_status_follows_the_mapping(): void
    {
        $application = $this->submitSample();
        $expected = [
            'received' => 'received', 'under_review' => 'under_review', 'interview_shortlisted' => 'shortlisted',
            'interviewed' => 'under_review', 'accepted' => 'under_review', 'rejected' => 'closed', 'archived' => 'closed',
        ];
        foreach ($expected as $internal => $public) {
            $application->update(['status' => $internal]);
            $this->assertSame($public, app(ApplicationTracker::class)->publicStatus($application->application_number, '+962 79 123 4567'), $internal);
        }
        $this->assertNull(app(ApplicationTracker::class)->publicStatus($application->application_number, '0790000000'));
        $this->assertNull(app(ApplicationTracker::class)->publicStatus('JOB-2026-99999', '0791234567'));
    }

    public function test_u13_cv_detection_never_guesses(): void
    {
        $session = UploadSession::query()->create(['expires_at' => now()->addDay()]);
        $store = app(AttachmentStore::class);

        $only = $store->store($session, $this->png('scan.png'));
        $this->assertInstanceOf(ApplicationAttachment::class, $only);
        $this->assertSame('auto_single', CvDetector::decide(ApplicationSubmitter::drafts($session))['state']);

        $cv = $store->store($session, $this->pdf('My CV 2026.pdf'));
        $this->assertInstanceOf(ApplicationAttachment::class, $cv);
        $decision = CvDetector::decide(ApplicationSubmitter::drafts($session));
        $this->assertSame('auto_confident', $decision['state']);
        $this->assertSame($cv->id, $decision['primary']);

        $other = UploadSession::query()->create(['expires_at' => now()->addDay()]);
        $store->store($other, $this->png('a.png'));
        $store->store($other, $this->png('b.png'));
        $this->assertSame('needs_choice', CvDetector::decide(ApplicationSubmitter::drafts($other))['state']);
    }

    public function test_the_form_stays_closed_without_a_verified_city_list(): void
    {
        $this->assertTrue(app(CareersForm::class)->isOpen());
        DB::table('jordan_cities')->update(['is_active' => false]);
        $this->assertFalse(app(CareersForm::class)->isOpen());
    }

    public function test_file_names_are_cleaned_and_never_build_a_path(): void
    {
        $this->assertSame('etcpasswd.pdf', FileInspector::cleanName("../../etc\x01/passwd.pdf"));
        $session = UploadSession::query()->create(['expires_at' => now()->addDay()]);
        $stored = app(AttachmentStore::class)->store($session, $this->pdf('../../evil cv.pdf'));
        $this->assertInstanceOf(ApplicationAttachment::class, $stored);
        $this->assertMatchesRegularExpression('#^tmp/[0-9a-f]{32}$#', $stored->storage_path);
    }

    public function test_expired_drafts_are_pruned_and_submitted_files_never_are(): void
    {
        $submitted = $this->submitSample();
        $draftSession = UploadSession::query()->create(['expires_at' => now()->subHour()]);
        $draft = app(AttachmentStore::class)->store($draftSession, $this->pdf('left behind.pdf'));
        $this->assertInstanceOf(ApplicationAttachment::class, $draft);
        $this->travel(2)->days();

        $this->assertSame(0, Artisan::call('careers:prune-drafts'));
        $this->assertNull(ApplicationAttachment::query()->find($draft->id));
        Storage::disk('careers')->assertMissing($draft->storage_path);
        foreach ($submitted->attachments as $file) {
            Storage::disk('careers')->assertExists($file->storage_path);
        }
        $this->assertSame(1, JobApplication::query()->count());
    }

    /** @param  array<string, mixed>  $overrides */
    private function submitSample(array $overrides = []): JobApplication
    {
        $result = $this->check($this->validInput($overrides));
        $this->assertSame([], $result['errors']);
        $session = UploadSession::query()->create(['expires_at' => now()->addDay()]);
        $cv = app(AttachmentStore::class)->store($session, $this->pdf());
        $this->assertInstanceOf(ApplicationAttachment::class, $cv);
        $consent = app(CareersForm::class)->consent();
        $this->assertNotNull($consent);

        return app(ApplicationSubmitter::class)->submit($result['data'], $session, $cv->id, (string) Str::uuid(), $consent);
    }
}
