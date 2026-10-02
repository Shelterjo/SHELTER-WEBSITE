<?php

return [
    'brand' => 'SHELTER COFFEE',
    'brand_ar' => 'شلتر كوفي', // D-007
    'title_brand' => 'SHELTER COFFEE', // the brand after “ — ” in tab titles
    'skip_to_content' => 'Skip to content',
    'choose_language' => 'Choose language',
    'switch_language' => 'العربية',

    'nav' => [
        'label' => 'Main navigation',
        'home' => 'Home',
        'menu' => 'Menu',
        'careers' => 'Careers',
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
        'timezone' => 'All times are :market time.',
    ],

    'home' => [
        'feature' => ['announcement' => 'Announcement', 'campaign' => 'Offer', 'event' => 'Event'],
        // M57: the approved positioning line (D-332) in the Google title and the opening line; one short sentence.
        'title' => 'SHELTER COFFEE — Specialty Coffee & Drive-Thru in Irbid',
        'lead' => 'Specialty coffee and a drive-thru in Irbid. Browse the menu and see which branch is open right now — and until when.',
        'cta_menu' => 'Browse the menu',
        'cta_locations' => 'Locations & hours',
        'branches_title' => 'Our branches in Irbid',
        'branches_lead' => 'Each branch’s status updates automatically from its opening hours.',
        'branches_link' => 'All details',
    ],

    'locations' => [
        'title' => 'Locations',
        'lead' => 'SHELTER COFFEE branches in Irbid, with opening hours and each branch’s live status.',
        'details' => 'Hours & details',
        'empty' => 'No branches to show right now.',
    ],

    'branch' => [
        'place' => 'Location',
        'directions' => 'Directions on Google Maps',
        'directions_short' => 'Directions',
        'services' => 'Services',
        'payments' => 'Payment methods',
        'title' => ':name — :kind in Irbid | Opening Hours',
        'contact' => 'Contact the branch',
        'call' => 'Call',
        'whatsapp' => 'Message us on WhatsApp', // D-063 (provisional)
        'whatsapp_short' => 'WhatsApp',
        'actions' => 'Quick actions',
        'menu' => 'This branch’s menu',
        'back' => 'All locations',
        // What kind of branch (master-data type, D-008) and where: “Drive-thru in Irbid”.
        'kinds' => ['drive_thru' => 'Drive-thru', 'coffee_house' => 'Coffee house'],
        // Title Case, as in the other Google titles (“Specialty Coffee & Drive-Thru”); the page keeps sentence case.
        'title_kinds' => ['drive_thru' => 'Drive-Thru', 'coffee_house' => 'Coffee House'],
        'kind_in_city' => ':kind in :city',
    ],

    // Contact by intent (D-059; titles from the approved intent map, docs/phase-01-discovery/14 §1 / §4).
    'contact' => [
        'title' => 'Contact',
        'lead' => 'Choose the reason you’re getting in touch to reach the right number.',
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
            'title' => 'Franchise Inquiries', // D-071
            'lead' => 'For franchise and partnership inquiries.',
            'link' => 'Franchise page',
        ],
        'status_note' => 'Branch status updates automatically from the opening hours.',
    ],

    // Site search (SI-B08, GLOBAL-SEARCH §4). results follows CLDR plural categories (App\Support\PluralCategory).
    'search' => [
        'title' => 'Search',
        'lead' => 'Search the menu, branches and site pages.',
        'label' => 'Search the site',
        'submit' => 'Search',
        'open' => 'Search',
        'results' => [
            'zero' => 'No results',
            'one' => '1 result',
            'two' => ':count results',
            'few' => ':count results',
            'many' => ':count results',
            'other' => ':count results',
        ],
        'for' => 'Results for “:query”',
        'none_title' => 'No results for “:query”',
        'none_text' => 'Try another word, or start here:',
        'groups' => [
            'menu' => 'Menu',
            'branch' => 'Locations',
            'page' => 'Pages',
            'event' => 'Events',
            'faq' => 'FAQs',
        ],
    ],

    // Events and campaigns (SI-M07 / SI-M08 — DX-014). The event text itself comes from the Owner (experiences).
    'events' => [
        'title' => 'Events',
        'lead' => 'Events and campaigns, on now and coming up.',
        'empty_title' => 'No events right now',
        'empty_text' => 'New events will appear here as soon as they are announced.',
        'state' => [
            'now' => 'On now',
            'upcoming' => 'Coming up',
            'ended' => 'Ended',
        ],
        'when' => 'When',
        'where' => 'Where',
        'terms' => 'Terms',
        'starts' => 'Starts: :when',
        'ends' => 'Ends: :when',
        'ended_notice' => 'This event has ended.',
        'back' => 'All events',
    ],

    'page' => [
        'updated' => 'Last updated: :date',
    ],

    'errors' => [
        '404_title' => 'Page not found',
        '404_text' => 'The link may have changed or the page was removed. Here are some good places to start:',
        '500_title' => 'Something went wrong',
        '500_text' => 'We couldn’t show this page right now. Please try again in a moment.',
        '503_title' => 'We are improving the site',
        '503_text' => 'We will be back shortly. Thank you for your patience.',
        '403_title' => 'You can’t open this page',
        '403_text' => 'This page isn’t available to you. Go back to the home page.',
        '419_title' => 'This page has expired',
        '419_text' => 'The page was open for a long time. Refresh it and try again.',
        '429_title' => 'Too many requests',
        '429_text' => 'Please wait a minute, then try again.',
        '4xx_title' => 'This page couldn’t be opened',
        '4xx_text' => 'Check the link or go back to the home page.',
        'form_expired' => 'The page expired because it was open for a long time. Check what you typed, then send it again.',
        'retry' => 'Try again',
        'links' => 'Useful links',
    ],
    // Google descriptions (meta description) per page and the root gateway line: Owner-approved wording, batch 01 (D-332,
    // docs/copy/META-DESCRIPTIONS-DRAFT-01.md). The Owner may reword any of them (dashboard → Site texts); empty = this text.
    // Google titles (M57 §15) for pages whose on-page heading stays short; Owner-editable in Site texts.
    'titles' => [
        'locations' => 'SHELTER COFFEE Locations in Irbid — Opening Hours',
        'contact' => 'Contact SHELTER COFFEE — Irbid',
        'events' => 'SHELTER COFFEE Events in Irbid',
        'careers' => 'Careers at SHELTER COFFEE, Irbid',
    ],
    'meta' => [
        'home' => 'SHELTER COFFEE in Irbid: specialty coffee, V60, hot and cold drinks and desserts, at a drive-thru and a coffee house. See the menu and opening hours.',
        'menu' => 'SHELTER COFFEE menu in Irbid: specialty coffee and V60, espresso and lattes, cold drinks, frappés, smoothies, tea, cake and cookies, with prices per branch.',
        'locations' => 'SHELTER COFFEE\'s two branches in Irbid, DRIVE and HOUSE: opening hours and which one is open right now.',
        'branch' => ':name in Irbid: opening hours, whether it\'s open right now, the menu with this branch\'s prices, and how to get in touch.',
        'contact' => 'SHELTER COFFEE contact numbers in Irbid: general and branch enquiries, complaints and suggestions, and catering, corporate orders and events.',
        'events' => 'SHELTER COFFEE events and campaigns in Irbid: what\'s on now and what\'s coming, with dates and participating branches.',
        'careers' => 'Join the SHELTER COFFEE team in Irbid. Apply with your CV through our Arabic application form and follow your application with its number.',
        'awards' => 'SHELTER COFFEE awards and recognition in Irbid, with the awarding body and year of each.',
        'family' => 'The people behind your coffee: meet the SHELTER COFFEE team in SHELTER Family.',
    ],
    'gateway' => ['lead' => 'Specialty coffee and a drive-thru in Irbid. Choose your language to browse the menu and opening hours.'],
    'attributes' => [
        'service' => ['indoor_seating' => 'Indoor seating', 'outdoor_seating' => 'Outdoor seating', 'takeaway' => 'Takeaway', 'wifi' => 'Wi-Fi', 'parking' => 'Parking', 'wheelchair_accessible' => 'Wheelchair accessible'],
        'payment' => ['cash' => 'Cash', 'card' => 'Card', 'cliq' => 'CliQ', 'mobile_wallet' => 'Mobile wallet'],
    ],
];
