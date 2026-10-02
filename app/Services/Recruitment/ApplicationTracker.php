<?php

namespace App\Services\Recruitment;

use App\Models\Recruitment\Application;

/**
 * Applicant tracking without an account (CAREERS-REQUIREMENTS §5.4, RECRUITMENT-SECURITY §7): application number +
 * the phone it was submitted with (any equivalent format). Only the PUBLIC status is returned, computed from the
 * internal one on every request and never stored (RECRUITMENT-PUBLIC-STATUS-MAPPING, TD-PS-03). No email, identity,
 * CV, notes or interview details, ever.
 */
final class ApplicationTracker
{
    /** Internal status → public status (TD-PS-01: interviewed and TD-PS-02: accepted read "under review"). */
    public const PUBLIC = [
        'received' => 'received',
        'under_review' => 'under_review',
        'interview_shortlisted' => 'shortlisted',
        'interviewed' => 'under_review',
        'accepted' => 'under_review',
        'rejected' => 'closed',
        'archived' => 'closed',
    ];

    public function publicStatus(string $number, string $phone): ?string
    {
        $normalizedPhone = ApplicantInput::phone($phone);
        $number = strtoupper(trim(ApplicantInput::digits($number)));
        $application = Application::query()->with('job')->where('type', 'JOB')->where('reference_number', $number)->first();
        $phone = $application?->job?->phone_normalized;
        // Same answer whether the number or the phone is wrong; compare in constant time.
        if ($application === null || $phone === null || $normalizedPhone === null || ! hash_equals($phone, $normalizedPhone)) {
            return null;
        }

        return self::PUBLIC[$application->status] ?? 'under_review';
    }
}
