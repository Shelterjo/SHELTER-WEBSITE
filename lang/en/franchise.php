<?php

// Franchise / partnerships (docs/franchise/02 §3, §5 and 03). Page content comes from the published page (PO-030);
// here: field labels from the field matrix, the Owner's CTA and after-submit wording, and functional UI text only.
return [
    'eyebrow' => 'FRANCHISE & PARTNERSHIPS',
    'cta' => 'Start your partnership application',
    'faq_title' => 'Questions',
    'form_title' => 'Partnership application',
    'form_note' => 'Fields marked * are required.',
    'closed_title' => 'Franchise inquiries',
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
        'interest' => 'Partnership interest',
        'experience_band' => 'Business experience',
        'experience_text' => 'A short note on your experience (optional)',
        'owns_business' => 'Do you currently own or manage a business?',
        'location_status' => 'Proposed location status',
        'introduction' => 'Short introduction',
        'consent' => 'Consent to data processing',
    ],
    'options' => [
        'experience_band' => [
            'none' => 'No experience', 'lt1' => 'Less than a year', 'y1_2' => '1–2 years', 'y3_5' => '3–5 years', 'y6_10' => '6–10 years', 'gt10' => 'More than 10 years',
        ],
        'yes_no' => ['yes' => 'Yes', 'no' => 'No'],
        'location_status' => ['has_site' => 'I have a specific site', 'searching' => "I'm looking for a site", 'not_started' => "I haven't started looking"],
    ],
    'choose' => 'Choose',
    'consent_label' => 'I agree to the above',
    'submit' => 'Send partnership application',
    'submitting' => 'Sending…',
    'errors' => [
        'summary' => 'Please correct the following:',
        'required' => 'This field is required.',
        'choose' => 'Choose a value from the list.',
        'too_long' => 'The text is longer than allowed (:max characters).',
        'phone' => 'Enter a valid phone number.',
        'email' => 'Enter a valid email address.',
        'consent' => 'Please agree to send the application.',
        'generic' => 'We could not send the application right now. Please try again shortly.',
        'expired' => 'The form has expired. Refresh the page and try again; your answers are kept.',
        'rate' => 'Too many applications in a short time. Please try later.',
    ],
    'success' => [
        'title' => 'Thank you for your interest in partnering with SHELTER COFFEE',
        'number' => 'Application number',
        'text' => 'The SHELTER team will review your application and get in touch when it moves to the next stage.',
        'back' => 'Back to the site',
    ],
];
