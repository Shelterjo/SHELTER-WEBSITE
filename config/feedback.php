<?php

/*
| Voice of Customer (docs/platform/VOICE-OF-CUSTOMER.md). Technical behaviour only. The questions follow the M32 §15
| example; their final wording, the entry points (QR / branch page / contact page) and the launch are the Owner's
| pending decision (PO-063) — until then the form is closed in production and linked from nowhere.
*/
return [
    'form_version' => 'feedback-form-v1',

    'form_enabled_in_production' => (bool) env('FEEDBACK_FORM_ENABLED', false),

    // FORM-RELIABILITY §4: 3 s minimum, 24 h maximum; 5 per 10 minutes and 60 per day per (hashed) address — branch
    // customers may share one network.
    'abuse' => [
        'min_fill_seconds' => 3,
        'max_form_age_hours' => 24,
        'per_ten_minutes' => 5,
        'per_day' => 60,
        // Every send that passes the bot check, accepted or not (FINAL-QA QA-004).
        'attempts_per_hour' => 30,
    ],

    'limits' => [
        'comment' => 1000,
    ],
];
