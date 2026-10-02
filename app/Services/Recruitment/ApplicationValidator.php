<?php

namespace App\Services\Recruitment;

use Carbon\CarbonImmutable;

/**
 * Server-side validation of the careers form — every rule lives here; the browser only helps (RECRUITMENT-SECURITY §6).
 * All 19 fields are required; a conditional field is required when shown (CAREERS-REQUIREMENTS §3). Choices must be one
 * of the approved options; the birth date must be a real date (month length, leap years); free text keeps any script.
 * Returns the normalised record and the errors keyed by field (Arabic messages, ordered as on the form).
 */
final class ApplicationValidator
{
    public const OPTIONS = [
        'gender' => ['male', 'female'],
        'marital_status' => ['single', 'married', 'other'],
        'nationality_type' => ['jordanian', 'non_jordanian'],
        'education_level' => ['tawjihi_pass', 'tawjihi_fail', 'bachelor', 'master', 'doctorate', 'student'],
        'experience_band' => ['none', 'lt1', 'y1_2', 'y3_5', 'y6_10', 'gt10'],
    ];

    public const YES_NO = ['same_field_experience', 'currently_employed', 'has_driving_license'];

    private const MAX = ['full_name' => 150, 'area' => 120, 'job_title' => 150, 'nationality_text' => 80, 'notes' => 3000, 'email' => 254, 'phone' => 40];

    /**
     * @param  array<string, mixed>  $input
     * @param  list<int>  $cityIds
     * @return array{data: array<string, mixed>, errors: array<string, string>}
     */
    public function validate(array $input, array $cityIds, ?CarbonImmutable $today = null): array
    {
        $today ??= CarbonImmutable::now('Asia/Amman');
        $errors = [];
        $data = [];
        $text = fn (string $key, bool $multiline = false): string => ApplicantInput::text(is_string($input[$key] ?? null) ? $input[$key] : '', $multiline);

        foreach (['full_name', 'phone', 'email'] as $key) {
            $data[$key] = $text($key);
        }
        if ($data['full_name'] === '') {
            $errors['full_name'] = __('careers.errors.required');
        } elseif (mb_strlen($data['full_name']) > self::MAX['full_name']) {
            $errors['full_name'] = __('careers.errors.too_long', ['max' => self::MAX['full_name']]);
        }

        $phone = mb_strlen($data['phone']) <= self::MAX['phone'] ? ApplicantInput::phone($data['phone']) : null;
        if ($data['phone'] === '') {
            $errors['phone'] = __('careers.errors.required');
        } elseif ($phone === null) {
            $errors['phone'] = __('careers.errors.phone');
        }
        $data['phone_normalized'] = $phone;

        if ($data['email'] === '') {
            $errors['email'] = __('careers.errors.required');
        } elseif (mb_strlen($data['email']) > self::MAX['email'] || filter_var($data['email'], FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE) === false) {
            $errors['email'] = __('careers.errors.email');
        }

        $this->choice('gender', $input, $data, $errors);
        $this->birthDate($input, $data, $errors, $today);
        $this->choice('marital_status', $input, $data, $errors);
        $this->choice('nationality_type', $input, $data, $errors);

        // Conditional fields: required exactly when shown (Jordanian → national ID; otherwise nationality + document).
        $data['identity_type'] = null;
        $data['identity'] = null;
        $data['nationality_text'] = null;
        if (($data['nationality_type'] ?? null) === 'jordanian') {
            $this->identity('national_id', $input, $data, $errors, 'national_id');
        } elseif (($data['nationality_type'] ?? null) === 'non_jordanian') {
            $data['nationality_text'] = $text('nationality_text');
            if ($data['nationality_text'] === '') {
                $errors['nationality_text'] = __('careers.errors.required');
            } elseif (mb_strlen($data['nationality_text']) > self::MAX['nationality_text']) {
                $errors['nationality_text'] = __('careers.errors.too_long', ['max' => self::MAX['nationality_text']]);
            }
            $this->identity('document_number', $input, $data, $errors, 'passport_or_document');
        }

        $city = filter_var($input['city_id'] ?? null, FILTER_VALIDATE_INT);
        if ($city === false || ! in_array($city, $cityIds, true)) {
            $errors['city_id'] = ($input['city_id'] ?? '') === '' ? __('careers.errors.required') : __('careers.errors.choose');
        }
        $data['city_id'] = $city === false ? null : $city;

        foreach (['area', 'job_title'] as $key) {
            $data[$key] = $text($key);
            if ($data[$key] === '') {
                $errors[$key] = __('careers.errors.required');
            } elseif (mb_strlen($data[$key]) > self::MAX[$key]) {
                $errors[$key] = __('careers.errors.too_long', ['max' => self::MAX[$key]]);
            }
        }

        $this->choice('education_level', $input, $data, $errors);
        $this->choice('experience_band', $input, $data, $errors);
        $this->yesNo('same_field_experience', $input, $data, $errors);
        $this->yesNo('currently_employed', $input, $data, $errors);

        $salaryRaw = is_string($input['expected_salary'] ?? null) ? trim($input['expected_salary']) : '';
        $data['expected_salary'] = $salaryRaw === '' ? null : ApplicantInput::salary($salaryRaw);
        if ($salaryRaw === '') {
            $errors['expected_salary'] = __('careers.errors.required');
        } elseif ($data['expected_salary'] === null) {
            $errors['expected_salary'] = __('careers.errors.salary');
        }

        $this->yesNo('has_driving_license', $input, $data, $errors);

        $data['notes'] = $text('notes', multiline: true);
        if ($data['notes'] === '') {
            $errors['notes'] = __('careers.errors.required');
        } elseif (mb_strlen($data['notes']) > self::MAX['notes']) {
            $errors['notes'] = __('careers.errors.too_long', ['max' => self::MAX['notes']]);
        }

        if (! in_array($input['consent'] ?? null, ['1', 'on', 'yes', true], true)) {
            $errors['consent'] = __('careers.errors.consent');
        }

        return ['data' => $data, 'errors' => array_map('strval', $errors)];
    }

    /**
     * Birth-year options: a technical display range, not an age rule (G10-TF-12).
     *
     * @return list<int>
     */
    public static function birthYears(?CarbonImmutable $today = null): array
    {
        $year = ($today ?? CarbonImmutable::now('Asia/Amman'))->year;

        return range($year - (int) config('careers.birth_year.newest_offset'), $year - (int) config('careers.birth_year.oldest_offset'));
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $errors
     */
    private function choice(string $key, array $input, array &$data, array &$errors): void
    {
        $value = $input[$key] ?? null;
        $data[$key] = is_string($value) && in_array($value, self::OPTIONS[$key], true) ? $value : null;
        if ($data[$key] === null) {
            $errors[$key] = ($value === null || $value === '') ? (string) __('careers.errors.required') : (string) __('careers.errors.choose');
        }
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $errors
     */
    private function yesNo(string $key, array $input, array &$data, array &$errors): void
    {
        $value = $input[$key] ?? null;
        $data[$key] = match ($value) {
            'yes' => true,
            'no' => false,
            default => null,
        };
        if ($data[$key] === null) {
            $errors[$key] = ($value === null || $value === '') ? (string) __('careers.errors.required') : (string) __('careers.errors.choose');
        }
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $errors
     */
    private function identity(string $key, array $input, array &$data, array &$errors, string $type): void
    {
        $raw = is_string($input[$key] ?? null) ? $input[$key] : '';
        $normalized = ApplicantInput::identity($raw);
        if ($normalized === '') {
            $errors[$key] = (string) __('careers.errors.required');
        } elseif (preg_match('/^[A-Z0-9]{4,30}$/', $normalized) !== 1) {
            $errors[$key] = (string) __('careers.errors.identity');
        } else {
            $data['identity_type'] = $type;
            $data['identity'] = $normalized;
        }
    }

    /**
     * @param  array<string, mixed>  $input
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $errors
     */
    private function birthDate(array $input, array &$data, array &$errors, CarbonImmutable $today): void
    {
        $parts = [];
        foreach (['birth_day', 'birth_month', 'birth_year'] as $key) {
            $value = filter_var(ApplicantInput::digits((string) ($input[$key] ?? '')), FILTER_VALIDATE_INT);
            $parts[] = $value === false ? null : $value;
        }
        [$day, $month, $year] = $parts;
        $data['birth_date'] = null;
        if ($day === null || $month === null || $year === null) {
            $errors['birth_date'] = (string) __('careers.errors.required');

            return;
        }
        if (! in_array($year, self::birthYears($today), true) || ! checkdate($month, $day, $year)) {
            $errors['birth_date'] = (string) __('careers.errors.birth_date');

            return;
        }
        $data['birth_date'] = sprintf('%04d-%02d-%02d', $year, $month, $day);
    }
}
