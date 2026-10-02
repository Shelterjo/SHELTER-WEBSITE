<?php

$bool = static fn (string $key, bool $default): bool => filter_var(env($key, $default), FILTER_VALIDATE_BOOLEAN);
$str = static function (string $key, string $default = ''): string {
    $value = env($key, $default);

    return is_string($value) ? $value : $default;
};

/*
| SHELTER application configuration. Business data never lives here (Master Data Hub / facts);
| this file only holds technical behaviour. See docs/architecture/PLATFORM-ARCHITECTURE.md.
*/
return [

    // Only production with SHELTER_INDEXING=true may be indexed (ENVIRONMENTS.md). Everything else is noindex.
    'indexing' => $bool('SHELTER_INDEXING', false),

    // Public search rate limit per address and minute (SEC-007). 30 everywhere; a local QA run may raise it in its own .env.
    'search_per_minute' => (int) env('SHELTER_SEARCH_PER_MINUTE', 30),

    // Release channel for build/brand guards: local | staging | production.
    'release' => $str('SHELTER_RELEASE', 'local'),

    // HTTP basic auth in front of the whole app (staging). Empty user = disabled.
    'basic_auth' => [
        'user' => $str('SHELTER_BASIC_AUTH_USER'),
        'password' => $str('SHELTER_BASIC_AUTH_PASSWORD'),
    ],

    // Supported interface languages. Arabic first (D-014).
    'locales' => ['ar', 'en'],

    // The Owner dashboard's language (DASH-034: Arabic first).
    'dashboard_locale' => env('DASHBOARD_LOCALE', 'ar'),
    'default_locale' => 'ar',

    'auth' => [
        // Absolute session limit in minutes, on top of the idle SESSION_LIFETIME (SECURITY-CENTER.md).
        'absolute_lifetime' => 12 * 60,
        // How long a password + TOTP re-confirmation stays valid for sensitive actions.
        'confirm_window' => 10,
        // Pending (password OK, second factor not yet given) login lifetime in minutes.
        'pending_lifetime' => 5,
        'max_attempts_per_minute' => 5,
        // Second-factor failures per account per hour before the code step waits out the hour (FINAL-QA QA-008). The
        // password step has no per-account lock on purpose: anyone who knows the address could lock the Owner out, and
        // a password alone opens nothing (the second factor is mandatory).
        'max_second_factor_failures_per_hour' => 10,
        'totp_issuer' => 'SHELTER COFFEE',
        'totp_window' => 1,
        'recovery_codes' => 10,
        'password_min_length' => 12,
    ],

    'audit' => [
        // Keys whose values are never written to audit_logs (masked).
        'masked_keys' => [
            'password', 'password_confirmation', 'two_factor_secret', 'two_factor_recovery_codes',
            'remember_token', 'national_id', 'passport_number', 'token', 'secret', 'api_key',
        ],
    ],
];
