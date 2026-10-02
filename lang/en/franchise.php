<?php

// Franchise / partnerships (docs/franchise/02–03). Page content comes from the published page (`pages` — Owner V1, M47).
// Here: the Owner's CTA labels, field labels and options (PF-06), after-submit wording (M47 Part 5) and functional UI
// text only. The acknowledgement (PF-02) and consent (PF-03) texts are versioned in consent_versions.
return [
    'eyebrow' => 'FRANCHISE & PARTNERSHIPS',
    'cta' => 'Start Your Partnership Application',
    'secondary_cta' => 'Discover SHELTER',
    'final_cta' => 'Start Your Application',
    'faq_title' => 'Frequently asked questions',
    'form_title' => 'Partnership application',
    'form_note' => 'Fields marked * are required.',
    'closed_title' => 'Franchise Inquiries', // D-071
    'closed_text' => 'For partnership questions, contact us directly.',
    'groups' => [
        'about' => 'About you',
        'market' => 'Market',
        'experience' => 'Experience',
        'opportunity' => 'Opportunity',
        'consent' => 'Consent',
    ],
    'fields' => [
        'full_name' => 'Full name',
        'phone' => 'Phone',
        'email' => 'Email',
        'country' => 'Country',
        'city' => 'City',
        'market' => 'Market or area of interest',
        'partnership_interest_type' => 'Type of Partnership Interest',
        'partnership_interest_other' => 'Please describe your partnership interest',
        'experience_band' => 'Business experience',
        'experience_text' => 'A short note on your experience (optional)',
        'owns_business' => 'Do you currently own or manage a business?',
        'location_status' => 'Proposed location status',
        'introduction' => 'Short introduction',
        'non_binding_acknowledgement' => 'Non-binding application acknowledgement',
        'data_processing_consent' => 'Data processing consent',
    ],
    'options' => [
        'partnership_interest_type' => [
            'single_location' => 'Single-Location Franchise Interest',
            'multi_location' => 'Multi-Location Development Interest',
            'market_development' => 'Market / City Development Interest',
            'proposed_location' => 'I Have a Proposed Location for Evaluation',
            'general_interest' => 'General Partnership Interest',
            'other' => 'Other',
        ],
        'experience_band' => [
            'none' => 'No experience', 'lt1' => 'Less than a year', 'y1_2' => '1–2 years', 'y3_5' => '3–5 years', 'y6_10' => '6–10 years', 'gt10' => 'More than 10 years',
        ],
        'yes_no' => ['yes' => 'Yes', 'no' => 'No'],
        'location_status' => ['has_site' => 'I have a specific site', 'searching' => "I'm looking for a site", 'not_started' => "I haven't started looking"],
    ],
    'choose' => 'Choose',
    'submit' => 'Send partnership application',
    'submitting' => 'Sending…',
    'errors' => [
        'summary' => 'Please correct the following:',
        'required' => 'This field is required.',
        'choose' => 'Choose a value from the list.',
        'too_long' => 'The text is longer than allowed (:max characters).',
        'phone' => 'Enter a valid phone number.',
        'email' => 'Enter a valid email address.',
        'acknowledgement' => 'Please confirm this acknowledgement to send the application.',
        'consent' => 'Please agree to send the application.',
        'generic' => 'We could not send the application right now. Please try again shortly.',
        'expired' => 'The form has expired. Refresh the page and try again; your answers are kept.',
        'rate' => 'Too many applications in a short time. Please try again later.',
    ],
    'success' => [
        'title' => 'Thank You for Your Interest in Partnering With SHELTER COFFEE',
        'received' => 'Your application has been received successfully.',
        'number' => 'Application Number:',
        'text' => 'The submitted information will be reviewed, and you will be contacted if the application proceeds to a further stage.',
        'keep' => 'Please keep your application number for future reference.',
        'back' => 'Back to the site',
    ],
];
