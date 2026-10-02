<?php

namespace App\Services\Recruitment;

use App\Models\Recruitment\ApplicationAttachment;
use App\Models\Recruitment\ApplicationIdentity;
use App\Models\Recruitment\ConsentVersion;
use App\Models\Recruitment\JobApplication;
use App\Models\Recruitment\UploadSession;
use App\Services\Core\AuditLogger;
use App\Services\Core\ReferenceNumbers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Turns a validated form into one application (CAREERS-REQUIREMENTS §5, RECRUITMENT-DATA-MODEL §2–§3), in one
 * transaction: server-generated JOB-YYYY-NNNNN number, the record, the encrypted identity, the consent (version + time,
 * no IP or user agent), the first status, the attachments (copied from quarantine to YYYY/MM first, quarantine removed
 * after commit), links to earlier applications by exact phone / email / identity match — never merged — and an audit
 * entry labelled by the application number only. The same idempotency key always returns the same application.
 */
final class ApplicationSubmitter
{
    public function __construct(
        private readonly ReferenceNumbers $numbers,
        private readonly IdentityVault $vault,
        private readonly AuditLogger $audit,
    ) {}

    /** @param  array<string, mixed>  $data  ApplicationValidator output */
    public function submit(array $data, UploadSession $session, int $primaryAttachmentId, string $idempotencyKey, ConsentVersion $consent): JobApplication
    {
        $existing = JobApplication::query()->where('idempotency_key', $idempotencyKey)->first();
        if ($existing !== null) {
            return $existing;
        }

        $disk = Storage::disk('careers');
        $attachments = $session->attachments()->whereNull('application_id')->get();
        $folder = now('Asia/Amman')->format('Y/m');
        $moved = [];
        try {
            foreach ($attachments as $attachment) {
                $target = $folder.'/'.$attachment->storage_key;
                $disk->copy($attachment->storage_path, $target);
                $moved[$attachment->id] = $target;
            }

            $application = DB::transaction(function () use ($data, $attachments, $moved, $primaryAttachmentId, $idempotencyKey, $consent): JobApplication {
                $blindIndex = null;
                $application = JobApplication::query()->create([
                    'application_number' => $this->numbers->next('JOB'),
                    'full_name' => $data['full_name'],
                    'phone_raw' => $data['phone'],
                    'phone_normalized' => $data['phone_normalized'],
                    'email' => $data['email'],
                    'email_normalized' => ApplicantInput::email((string) $data['email']),
                    'gender' => $data['gender'],
                    'birth_date' => $data['birth_date'],
                    'marital_status' => $data['marital_status'],
                    'nationality_type' => $data['nationality_type'],
                    'nationality_text' => $data['nationality_text'],
                    'city_id' => $data['city_id'],
                    'area_text' => $data['area'],
                    'job_title_text' => $data['job_title'],
                    'education_level' => $data['education_level'],
                    'experience_band' => $data['experience_band'],
                    'same_field_experience' => $data['same_field_experience'],
                    'currently_employed' => $data['currently_employed'],
                    'expected_salary_jod' => $data['expected_salary'],
                    'has_driving_license' => $data['has_driving_license'],
                    'notes_text' => $data['notes'],
                    'status' => 'received',
                    'submitted_at' => now(),
                    'idempotency_key' => $idempotencyKey,
                    'form_version' => (string) config('careers.form_version'),
                ]);

                $identity = (string) $data['identity'];
                $encrypted = $this->vault->encrypt($identity);
                $blindIndex = $this->vault->blindIndex($identity);
                ApplicationIdentity::query()->create([
                    'application_id' => $application->id,
                    'id_type' => $data['identity_type'],
                    'id_ciphertext' => $encrypted['ciphertext'],
                    'id_nonce' => $encrypted['nonce'],
                    'id_key_version' => $encrypted['key_version'],
                    'id_last4' => substr($identity, -4),
                    'id_blind_index' => $blindIndex,
                ]);

                DB::table('application_consents')->insert([
                    'application_id' => $application->id, 'consent_version_id' => $consent->id, 'accepted' => true,
                    'accepted_at' => now(), 'created_at' => now(), 'updated_at' => now(),
                ]);
                DB::table('application_status_history')->insert([
                    'application_id' => $application->id, 'old_status' => null, 'new_status' => 'received',
                    'actor_id' => null, 'changed_at' => now(), 'created_at' => now(), 'updated_at' => now(),
                ]);

                foreach ($attachments as $attachment) {
                    $isPrimary = $attachment->id === $primaryAttachmentId;
                    $attachment->update([
                        'application_id' => $application->id,
                        'storage_path' => $moved[$attachment->id],
                        'cv_detection' => $isPrimary ? $attachment->cv_detection : ($attachment->cv_detection === 'pending' ? 'not_cv' : $attachment->cv_detection),
                    ]);
                }
                $application->update(['primary_attachment_id' => $primaryAttachmentId]);

                $this->link($application, $blindIndex);
                $this->audit->record('application.created', $application, meta: [
                    'target_label' => $application->application_number,
                    'actor_type' => 'applicant',
                    'attachments' => $attachments->count(),
                ]);

                return $application;
            });
        } catch (Throwable $e) {
            foreach ($moved as $path) {
                $disk->delete($path);
            }
            throw $e;
        }

        // The quarantine copies go only once the application is safely stored.
        foreach ($attachments as $attachment) {
            $disk->delete('tmp/'.$attachment->storage_key);
        }
        $session->update(['expires_at' => now()]);

        return $application;
    }

    /** Earlier applications by the same phone, email or identity are linked — never merged, edited or removed. */
    private function link(JobApplication $application, ?string $blindIndex): void
    {
        $signals = [
            'phone' => JobApplication::query()->where('phone_normalized', $application->phone_normalized)->pluck('id'),
            'email' => JobApplication::query()->where('email_normalized', $application->email_normalized)->pluck('id'),
            'id_blind_index' => $blindIndex === null ? collect() : ApplicationIdentity::query()->where('id_blind_index', $blindIndex)->pluck('application_id'),
        ];
        $linked = [];
        foreach ($signals as $signal => $ids) {
            foreach ($ids as $id) {
                if ((int) $id === $application->id) {
                    continue;
                }
                DB::table('application_links')->insertOrIgnore([
                    'application_id' => $application->id, 'linked_application_id' => (int) $id, 'signal' => $signal, 'created_at' => now(),
                ]);
                $linked[(int) $id] = true;
            }
        }
        if ($linked !== []) {
            $group = array_keys($linked);
            $size = count($group) + 1;
            $application->update(['applicant_group_size' => $size]);
            JobApplication::query()->whereIn('id', $group)->where('applicant_group_size', '<', $size)->update(['applicant_group_size' => $size]);
        }
    }

    /**
     * The attachments still waiting in a draft session, in upload order.
     *
     * @return list<ApplicationAttachment>
     */
    public static function drafts(UploadSession $session): array
    {
        return array_values($session->attachments()->whereNull('application_id')->get()->all());
    }
}
