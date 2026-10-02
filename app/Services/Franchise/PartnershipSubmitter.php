<?php

namespace App\Services\Franchise;

use App\Models\Recruitment\Application;
use App\Models\Recruitment\ConsentVersion;
use App\Models\Recruitment\PartnershipApplication;
use App\Services\Core\AuditLogger;
use App\Services\Core\ReferenceNumbers;
use App\Services\Recruitment\ApplicantInput;
use Illuminate\Support\Facades\DB;

/**
 * Turns a validated partnership form into one FR application (franchise field matrix, PLATFORM-ARCHITECTURE §3.4), in
 * one transaction: the Applications Core row with its server-generated FR-YYYY-NNNNN number, the partnership record
 * with privacy-safe attribution, the consent (version + time), the first status, links to earlier partnership
 * applications by exact phone / email — never merged — and an audit entry labelled by the number only. The same
 * idempotency key always returns the same application.
 */
final class PartnershipSubmitter
{
    public function __construct(private readonly ReferenceNumbers $numbers, private readonly AuditLogger $audit) {}

    /**
     * @param  array<string, mixed>  $data  PartnershipValidator output
     * @param  array<string, ?string>  $attribution  utm_source, utm_medium, utm_campaign, landing_path, referrer_domain
     */
    public function submit(array $data, array $attribution, string $locale, string $idempotencyKey, ConsentVersion $consent): Application
    {
        $existing = Application::query()->where('idempotency_key', $idempotencyKey)->first();
        if ($existing !== null) {
            return $existing;
        }

        return DB::transaction(function () use ($data, $attribution, $locale, $idempotencyKey, $consent): Application {
            $application = Application::query()->create([
                'type' => 'FR',
                'reference_number' => $this->numbers->next('FR'),
                'status' => 'received',
                'submitted_at' => now(),
                'idempotency_key' => $idempotencyKey,
                'form_version' => (string) config('franchise.form_version'),
                'locale' => $locale,
            ]);
            $email = ApplicantInput::email((string) $data['email']);
            PartnershipApplication::query()->create([
                'application_id' => $application->id,
                'full_name' => $data['full_name'],
                'phone_raw' => $data['phone'],
                'phone_normalized' => $data['phone_normalized'],
                'email' => $data['email'],
                'email_normalized' => $email,
                'country_code' => $data['country'],
                'city_text' => $data['city'],
                'market_interest' => $data['market'],
                'partnership_interest' => $data['interest'],
                'experience_band' => $data['experience_band'],
                'experience_text' => $data['experience_text'] !== '' ? $data['experience_text'] : null,
                'owns_business' => $data['owns_business'],
                'location_status' => $data['location_status'],
                'introduction' => $data['introduction'],
            ] + $attribution);

            DB::table('application_consents')->insert([
                'application_id' => $application->id, 'consent_version_id' => $consent->id, 'accepted' => true,
                'accepted_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('application_status_history')->insert([
                'application_id' => $application->id, 'old_status' => null, 'new_status' => 'received',
                'actor_id' => null, 'changed_at' => now(), 'created_at' => now(), 'updated_at' => now(),
            ]);

            $linked = [];
            foreach (['phone' => ['phone_normalized', (string) $data['phone_normalized']], 'email' => ['email_normalized', $email]] as $signal => [$column, $value]) {
                foreach (PartnershipApplication::query()->where($column, $value)->where('application_id', '!=', $application->id)->pluck('application_id') as $id) {
                    DB::table('application_links')->insertOrIgnore([
                        'application_id' => $application->id, 'linked_application_id' => (int) $id, 'signal' => $signal, 'created_at' => now(),
                    ]);
                    $linked[(int) $id] = true;
                }
            }
            if ($linked !== []) {
                $size = count($linked) + 1;
                $application->update(['applicant_group_size' => $size]);
                Application::query()->whereIn('id', array_keys($linked))->where('applicant_group_size', '<', $size)->update(['applicant_group_size' => $size]);
            }

            $this->audit->record('application.created', $application, meta: [
                'target_label' => $application->reference_number,
                'actor_type' => 'applicant',
                'type' => 'FR',
            ]);

            return $application;
        });
    }
}
