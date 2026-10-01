<?php

return [
    'brand' => 'SHELTER COFFEE',
    'brand_ar' => 'شلتر كوفي', // D-007
    'skip_to_content' => 'Skip to content',
    'choose_language' => 'Choose language',
    'switch_language' => 'العربية',

    'nav' => [
        'label' => 'Main navigation',
        'home' => 'Home',
        'menu' => 'Menu',
        'locations' => 'Locations',
    ],
    'language' => [
        'label' => 'Language',
        'ar' => 'العربية',
        'en' => 'English',
    ],

    'time' => ['am' => 'AM', 'pm' => 'PM'],
    'weekdays' => [
        0 => 'Sunday', 1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday',
    ],

    // HOURS-010 SPEC wording (English forms are DRAFT until content approval).
    'status' => [
        'open_until' => 'Open now — until :time',
        'closes_in' => [
            'one' => 'Closes in 1 min',
            'other' => 'Closes in :n min',
        ],
        'closed' => 'Closed now',
        'closed_opens' => 'Closed now — opens :time',
        'closed_opens_tomorrow' => 'Closed now — opens tomorrow :time',
        'closed_opens_day' => 'Closed now — opens :day :time',
        'special' => 'Today’s hours differ from the usual',
    ],

    'hours' => [
        'title' => 'Opening hours',
        'caption' => 'Regular opening hours for each day of the week',
        'today_hours' => 'Today: :hours',
        'timezone' => 'Times are in :market time.',
    ],

    'home' => [
        'title' => 'SHELTER COFFEE',
        'lead' => 'Browse the menu and see which branch is open right now — and until when.',
        'cta_menu' => 'View the menu',
        'cta_locations' => 'Locations & hours',
        'branches_title' => 'Locations',
        'branches_lead' => 'Status updates automatically from the opening hours.',
        'branches_link' => 'All details',
    ],

    'locations' => [
        'title' => 'Locations',
        'lead' => 'Opening hours and live status for each branch.',
        'details' => 'Hours & details',
        'empty' => 'No branches to show right now.',
    ],

    'branch' => [
        'title' => ':name — Opening hours',
        'contact' => 'Contact the branch',
        'call' => 'Call',
        'whatsapp' => 'Message us on WhatsApp', // D-063 (provisional)
        'whatsapp_short' => 'WhatsApp',
        'actions' => 'Quick actions',
        'menu' => 'This branch’s menu',
        'back' => 'All locations',
    ],

    // Contact by intent (D-059; titles from the approved intent map, docs/phase-01-discovery/14 §1 / §4).
    'contact' => [
        'title' => 'Contact',
        'lead' => 'Choose why you are getting in touch to reach the right number directly.',
        'general' => [
            'title' => 'General & Branches',
            'lead' => 'General questions, branches, opening hours and orders.',
            'branches' => 'Branch status now',
            'link' => 'Locations and opening hours',
        ],
        'complaints' => [
            'title' => 'Complaints & Feedback',
            'lead' => 'For a complaint or a suggestion about your visit.',
        ],
        'catering' => [
            'title' => 'Catering, B2B & Events',
            'lead' => 'Catering, business orders and events.',
        ],
        'franchise' => [
            'title' => 'Franchise inquiries',
            'lead' => 'For questions about the franchise.',
            'link' => 'Franchise page',
        ],
        'status_note' => 'Branch status updates automatically from the opening hours.',
    ],

    'page' => [
        'updated' => 'Last updated: :date',
    ],

    'errors' => [
        '404_title' => 'Page not found',
        '404_text' => 'The link may have changed or the page was removed. Here are some good places to start:',
        '500_title' => 'Something went wrong',
        '500_text' => 'We couldn’t show this page right now. Please try again in a moment.',
        'retry' => 'Try again',
        'links' => 'Useful links',
    ],
];
