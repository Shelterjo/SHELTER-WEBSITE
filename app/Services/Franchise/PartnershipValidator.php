<?php

namespace App\Services\Franchise;

use App\Services\Recruitment\ApplicantInput;
use App\Support\Countries;

/**
 * Server-side validation of the partnership form (docs/franchise/03-APPLICATION-FIELD-MATRIX.md fields 1–11 + consent).
 * Free text keeps any script; phone and email follow the careers rules; the country is an ISO 3166 code; the interest
 * type stays free short text until its options are approved (PF-06). No investment question (field 14).
 */
final class PartnershipValidator
{
    public const EXPERIENCE = ['none', 'lt1', 'y1_2', 'y3_5', 'y6_10', 'gt10'];

    public const LOCATION = ['has_site', 'searching', 'not_started'];

    /**
     * @param  array<string, mixed>  $input
     * @return array{data: array<string, mixed>, errors: array<string, string>}
     */
    public function validate(array $input): array
    {
        /** @var array<string, int> $max */
        $max = config('franchise.limits');
        $errors = [];
        $text = fn (string $key, bool $multiline = false): string => ApplicantInput::text(is_string($input[$key] ?? null) ? $input[$key] : '', $multiline);
        $required = function (string $key, string $value, int $limit) use (&$errors): void {
            if ($value === '') {
                $errors[$key] = (string) __('franchise.errors.required');
            } elseif (mb_strlen($value) > $limit) {
                $errors[$key] = (string) __('franchise.errors.too_long', ['max' => $limit]);
            }
        };

        $data = [
            'full_name' => $text('full_name'),
            'phone' => $text('phone'),
            'email' => $text('email'),
            'city' => $text('city'),
            'market' => $text('market'),
            'interest' => $text('interest'),
            'experience_text' => $text('experience_text'),
            'introduction' => $text('introduction', multiline: true),
        ];
        $required('full_name', $data['full_name'], $max['full_name']);

        $data['phone_normalized'] = mb_strlen($data['phone']) <= $max['phone'] ? ApplicantInput::phone($data['phone']) : null;
        if ($data['phone'] === '') {
            $errors['phone'] = (string) __('franchise.errors.required');
        } elseif ($data['phone_normalized'] === null) {
            $errors['phone'] = (string) __('franchise.errors.phone');
        }
        if ($data['email'] === '') {
            $errors['email'] = (string) __('franchise.errors.required');
        } elseif (mb_strlen($data['email']) > $max['email'] || filter_var($data['email'], FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE) === false) {
            $errors['email'] = (string) __('franchise.errors.email');
        }

        $country = is_string($input['country'] ?? null) ? strtoupper($input['country']) : '';
        $data['country'] = Countries::valid($country) ? $country : null;
        if ($data['country'] === null) {
            $errors['country'] = (string) __($country === '' ? 'franchise.errors.required' : 'franchise.errors.choose');
        }

        $required('city', $data['city'], $max['city']);
        $required('market', $data['market'], $max['market']);
        $required('interest', $data['interest'], $max['interest']);

        $data['experience_band'] = in_array($input['experience_band'] ?? null, self::EXPERIENCE, true) ? $input['experience_band'] : null;
        if ($data['experience_band'] === null) {
            $errors['experience_band'] = (string) __(($input['experience_band'] ?? '') === '' ? 'franchise.errors.required' : 'franchise.errors.choose');
        }
        if (mb_strlen($data['experience_text']) > $max['experience_text']) {
            $errors['experience_text'] = (string) __('franchise.errors.too_long', ['max' => $max['experience_text']]);
        }

        $data['owns_business'] = match ($input['owns_business'] ?? null) {
            'yes' => true,
            'no' => false,
            default => null,
        };
        if ($data['owns_business'] === null) {
            $errors['owns_business'] = (string) __('franchise.errors.required');
        }

        $data['location_status'] = in_array($input['location_status'] ?? null, self::LOCATION, true) ? $input['location_status'] : null;
        if ($data['location_status'] === null) {
            $errors['location_status'] = (string) __(($input['location_status'] ?? '') === '' ? 'franchise.errors.required' : 'franchise.errors.choose');
        }

        $required('introduction', $data['introduction'], $max['introduction']);

        if (! in_array($input['consent'] ?? null, ['1', 'on', 'yes', true], true)) {
            $errors['consent'] = (string) __('franchise.errors.consent');
        }

        return ['data' => $data, 'errors' => $errors];
    }
}
