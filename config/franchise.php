<?php

/*
| Franchise / partnerships (docs/franchise/*, M29). Technical behaviour only — every business text (page content,
| consent PF-03, disclaimer PF-02, interest options PF-06) comes from approved data, never from here.
*/
return [
    'form_version' => 'partnership-form-v1',

    // The page is public only once its content is published (PO-030); the form only once its consent and disclaimer
    // texts are approved — and, in production, only once switched on after the release gates.
    'form_enabled_in_production' => (bool) env('FRANCHISE_FORM_ENABLED', false),

    // Settings keys (approved through the Fact Registry) holding the non-commitment disclaimer shown before submitting.
    'disclaimer_keys' => ['ar' => 'franchise.disclaimer_ar', 'en' => 'franchise.disclaimer_en'],

    'abuse' => [
        'min_fill_seconds' => 8,
        'max_form_age_hours' => 24,
        'submit_per_hour' => 5,
        'submit_per_day' => 20,
        'submit_per_phone_per_day' => 3,
    ],

    'limits' => [
        'full_name' => 150, 'city' => 120, 'market' => 200, 'interest' => 200, 'experience_text' => 300,
        'introduction' => 2000, 'email' => 254, 'phone' => 40,
    ],
];
