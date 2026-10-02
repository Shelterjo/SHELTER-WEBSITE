<?php

// Voice of Customer (VOICE-OF-CUSTOMER §2). The questions follow the M32 §15 example; their final AR/EN wording and the
// entry points are the Owner's pending decision (PO-063) — until then the form is closed in production. Functional UI
// text only; the privacy hint is the spec's own wording.
return [
    'title' => 'How was your experience?',
    'lead' => 'Your answer reaches us without your name or phone number.',
    'required_note' => 'Fields marked * are required.',
    'details' => 'Details (optional)',
    'scale_hint' => '1 lowest · 5 highest',
    'fields' => [
        'branch' => 'Branch',
        'rating_overall' => 'Overall experience',
        'rating_coffee' => 'Coffee',
        'rating_service' => 'Service',
        'rating_cleanliness' => 'Cleanliness',
        'rating_speed' => 'Speed',
        'comment' => 'Comment (optional)',
    ],
    'comment_hint' => 'Please do not include personal details in your comment.',
    'submit' => 'Send',
    'submitting' => 'Sending…',
    'errors' => [
        'summary' => 'Please correct the following:',
        'required' => 'This field is required.',
        'choose' => 'Choose a value from the list.',
        'too_long' => 'The text is longer than allowed (:max characters).',
        'generic' => 'We could not send this. Please try again.',
        'expired' => 'The page has expired. Refresh it and try again; your answers are kept.',
        'rate' => 'Too many answers in a short time. Please try again later.',
    ],
    'success' => [
        'title' => 'Thank you',
        'text' => 'We have received your feedback.',
        'back' => 'Back to the site',
    ],
];
