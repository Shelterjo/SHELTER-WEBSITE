<?php

/*
| Franchise / partnerships (docs/franchise/*, M29, M47). Technical behaviour only — the page content (`pages`), the
| non-binding acknowledgement (PF-02) and the data-processing consent (PF-03) are versioned approved data, never here.
*/
return [
    'form_version' => 'partnership-form-v1',

    // The page is public only once its content is published; the form only once both approved texts are active (the
    // acknowledgement — consent_versions scope partnership_ack — and the consent — scope partnerships) and, in
    // production, only once switched on after the release gates.
    'form_enabled_in_production' => (bool) env('FRANCHISE_FORM_ENABLED', false),

    'abuse' => [
        'min_fill_seconds' => 8,
        'max_form_age_hours' => 24,
        'submit_per_hour' => 5,
        'submit_per_day' => 20,
        'submit_per_phone_per_day' => 3,
    ],

    'limits' => [
        'full_name' => 150, 'city' => 120, 'market' => 200, 'partnership_interest_other' => 300, 'experience_text' => 300,
        'introduction' => 2000, 'email' => 254, 'phone' => 40,
    ],
];
