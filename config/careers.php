<?php

$str = static function (string $key, string $default = ''): string {
    $value = env($key, $default);

    return is_string($value) ? $value : $default;
};
$int = static function (string $key, int $default): int {
    $value = env($key);

    return is_numeric($value) ? (int) $value : $default;
};

/*
| Careers & recruitment (docs/CAREERS-REQUIREMENTS.md, RECRUITMENT-SECURITY.md, FORM-RELIABILITY.md).
| Technical behaviour only. Every number here is a technical safeguard, never a business rule: the owner set no
| business limit on files (M28 §16–§18). Upload limits follow the server (CLOUDWAYS-RECRUITMENT-ARCHITECTURE §4) and are
| re-tuned after the Cloudways audit (A-07); rate limits are initial values to be tuned after monitoring.
*/
return [

    // Bumped whenever the set of form fields changes (job_applications.form_version, RECRUITMENT-DATA-MODEL §2.1).
    'form_version' => 'careers-form-v1',

    // The form takes real applicant data only in production with this switched on (after the production gates and
    // PO-019); outside production it opens as soon as its data is ready (cities, consent text, identity keys).
    'form_enabled_in_production' => (bool) env('CAREERS_FORM_ENABLED', false),

    // Identity numbers (national ID / passport or document): AES-256-GCM + HMAC-SHA256 blind index (RECRUITMENT-SECURITY
    // §5). Keys are 32 random bytes, base64, set in the server environment only — never in git or the frontend.
    // Losing a key means losing the ability to read the numbers it encrypted: the owner keeps an offline copy.
    'identity' => [
        'key_version' => $int('RECRUITMENT_ID_KEY_VERSION', 1),
        'encryption_key' => $str('RECRUITMENT_ID_ENC_KEY'),
        // Older keys stay readable after a rotation: "2:base64…,1:base64…".
        'previous_keys' => $str('RECRUITMENT_ID_ENC_KEYS_PREVIOUS'),
        'hmac_key' => $str('RECRUITMENT_ID_HMAC_KEY'),
    ],

    // Private attachment storage lives on the `careers` disk (config/filesystems.php), outside the public web root.
    'uploads' => [
        // null = derived from PHP's upload_max_filesize / post_max_size minus a 10% margin (Cloudways audit A-07 may
        // set an explicit number, e.g. when Cloudflare's request limit is lower than PHP's).
        'max_file_bytes' => is_numeric(env('CAREERS_MAX_FILE_BYTES')) ? (int) env('CAREERS_MAX_FILE_BYTES') : null,
        // Flood guards per application, shown only when reached (never announced as a rule).
        'max_files' => $int('CAREERS_MAX_FILES', 20),
        'max_total_bytes' => $int('CAREERS_MAX_TOTAL_BYTES', 100 * 1024 * 1024),
        // Office files are ZIP containers: refuse archives that expand beyond these (zip-bomb guard).
        'max_zip_entries' => 2000,
        'max_zip_expanded_bytes' => 200 * 1024 * 1024,
        // Unsubmitted drafts (never applications) are removed after this many hours.
        'draft_ttl_hours' => 24,
        // Local text extraction for CV detection (pdftotext when the server has it). Never blocks longer than this.
        'text_extraction_timeout' => 3,
    ],

    'cv_detection' => [
        // RECRUITMENT-SECURITY §8: the top file needs this score and this lead over the next one.
        'confident_score' => 60,
        'confident_margin' => 20,
    ],

    // Anti-abuse (RECRUITMENT-SECURITY §6, FORM-RELIABILITY §4). No CAPTCHA, no third-party script.
    'abuse' => [
        'min_fill_seconds' => 8,
        'max_form_age_hours' => 24,
        'submit_per_hour' => 5,
        'submit_per_day' => 20,
        'submit_per_phone_per_day' => 3,
        'uploads_per_hour' => 60,
        // A burst of bot rejections raises one LOW signal (input for a later CAPTCHA decision, never automatic).
        'spam_signal_threshold' => 50,
        // Idempotency: how long a second identical submit waits for the first one to finish (ms).
        'idempotency_wait_ms' => 5000,
    ],

    // Applicant tracking (RECRUITMENT-SECURITY §7, CAREERS-082).
    'tracking' => [
        'attempts_per_window' => 5,
        'window_seconds' => 15 * 60,
        'attempts_per_reference_per_day' => 10,
        // Progressive cooldown after the window is exhausted: 1 → 5 → 30 minutes.
        'cooldown_seconds' => [60, 300, 1800],
    ],

    // Birth-year list in the form (CAREERS-014): a technical display range, not an age rule (G10-TF-12).
    'birth_year' => [
        'newest_offset' => 16,
        'oldest_offset' => 80,
    ],
];
