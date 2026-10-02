<?php

/*
| Master data seed — OWNER-APPROVED VALUES ONLY (M38: never invent business facts).
| Every value carries the decision that approved it (docs/governance/DECISION-LOG.md).
| Anything not approved is null here and registered as MISSING / PENDING in the fact registry.
| Weekdays follow Carbon: 0 = Sunday … 5 = Friday, 6 = Saturday.
*/

$satToThu = [6, 0, 1, 2, 3, 4];

return [
    'market' => [
        // D-031: market layer /ar/jo/ · /en/jo/. Timezone/currency/phone code are technical market settings.
        'code' => 'jo', 'name_ar' => 'الأردن', 'name_en' => 'Jordan', 'default_locale' => 'ar', 'locales' => ['ar', 'en'],
        'currency' => 'JOD', 'timezone' => 'Asia/Amman', 'phone_country_code' => '962', 'is_active' => true,
    ],
    'country' => ['iso2' => 'JO', 'name_ar' => 'الأردن', 'name_en' => 'Jordan'],
    'city' => [
        // D-008: both public branches are in Irbid. Latin slug per D-014/D-031.
        // Arabic spelling «إربد» approved by the Owner (D-334, PO-079 → «أ»; CF-M-036 resolved).
        'slug' => 'irbid', 'name_ar' => 'إربد', 'name_en' => 'Irbid',
        'facts' => ['name_ar' => ['status' => 'APPROVED', 'ref' => 'D-334']],
    ],
    'branches' => [
        [
            'code' => 'BR-DRIVE', 'slug' => 'drive', 'type' => 'drive_thru', 'sort' => 1, 'is_public' => true, // D-008, D-053
            'name_ar' => 'شلتر كوفي درايف', 'name_en' => 'SHELTER COFFEE DRIVE', 'names_ref' => 'D-020',
            // D-020: Sat–Thu 07:00–02:00, Fri 08:00–02:00.
            'hours' => array_merge(
                array_map(fn (int $d): array => [$d, '07:00', '02:00'], $satToThu),
                [[5, '08:00', '02:00']],
            ),
            'hours_ref' => 'D-020',
            // D-020: the detailed address and the coordinates are NOT approved yet (PO-010; the Maps link is, D-335).
            'missing' => ['address_ar' => 'PO-010', 'address_en' => 'PO-010', 'latitude' => 'PO-010', 'longitude' => 'PO-010'],
            // Owner-approved values the seeder writes once (never over an Owner edit):
            // D-334 (Owner «أ» to PO-079): the location description as written in M57 §1 — a landmark, not the street
            // address (PO-010); its English wording was not given → PO-081.
            // D-335 (Owner «ج» to PO-010): the Google Maps link, the "main branch" link of the Owner's own site (موقعنا).
            'approved' => [
                'landmark_ar' => ['value' => 'بجانب منطقة قصر النخيل / أرابيلا', 'ref' => 'D-334'],
                'landmark_en' => ['value' => null, 'ref' => 'PO-081'],
                'maps_url' => ['value' => 'https://share.google/Cko3RPFoBGY21bco4', 'ref' => 'D-335'],
            ],
        ],
        [
            'code' => 'BR-HOUSE', 'slug' => 'house', 'type' => 'coffee_house', 'sort' => 2, 'is_public' => true,
            'name_ar' => 'شلتر كوفي هاوس', 'name_en' => 'SHELTER COFFEE HOUSE', 'names_ref' => 'D-020',
            // D-020: Sat–Wed 09:00–22:00, Thu–Fri 09:00–23:00.
            'hours' => array_merge(
                array_map(fn (int $d): array => [$d, '09:00', '22:00'], [6, 0, 1, 2, 3]),
                array_map(fn (int $d): array => [$d, '09:00', '23:00'], [4, 5]),
            ),
            'hours_ref' => 'D-020',
            'missing' => ['address_ar' => 'PO-010', 'address_en' => 'PO-010', 'latitude' => 'PO-010', 'longitude' => 'PO-010'],
            // D-334 (Owner «أ» to PO-079; M57 §1, D-020): Irbid City Center, first floor, next to Jordan Islamic Bank.
            // D-335 (Owner «ج» to PO-010): the Google Maps link, the "City Centre" link of the Owner's own site (موقعنا).
            'approved' => [
                'landmark_ar' => ['value' => 'إربد سيتي سنتر، الطابق الأول، بجانب البنك الإسلامي الأردني', 'ref' => 'D-334'],
                'landmark_en' => ['value' => 'Irbid City Center, First Floor, next to Jordan Islamic Bank', 'ref' => 'D-334'],
                'maps_url' => ['value' => 'https://share.google/d7T2jt7BKhidMHG4A', 'ref' => 'D-335'],
            ],
        ],
    ],
    // D-033 services and D-034 payment methods: data model ready, every value MISSING until the owner confirms.
    'branch_attributes' => [
        'service' => ['indoor_seating', 'outdoor_seating', 'takeaway', 'wifi', 'parking', 'wheelchair_accessible'],
        'payment' => ['cash', 'card', 'cliq', 'mobile_wallet'],
    ],
    'contacts' => [
        // D-057: main public number for brand and all branches (display D-065: 0799009436 / +962 79 900 9436).
        ['kind' => 'phone_main', 'value' => '+962799009436', 'is_public' => true, 'cards' => true, 'status' => 'APPROVED', 'ref' => 'D-057'],
        // D-058: official WhatsApp = main number (CTA text/pre-fill pending D-063/D-064).
        ['kind' => 'whatsapp', 'value' => '+962799009436', 'is_public' => true, 'cards' => true, 'status' => 'APPROVED', 'ref' => 'D-058'],
        // D-057: complaints, feedback & franchise — not a general number, never on branch cards; shown only by intent (D-059).
        ['kind' => 'complaints_feedback_franchise', 'value' => '+962799338445', 'is_public' => true, 'cards' => false, 'status' => 'APPROVED', 'ref' => 'D-057'],
        // D-057: catering, B2B & events — not a general number, never on branch cards; shown only by intent (D-059).
        ['kind' => 'catering_b2b_events', 'value' => '+962799530383', 'is_public' => true, 'cards' => false, 'status' => 'APPROVED', 'ref' => 'D-057'],
        // D-024 / D-035: candidate public email, NOT approved for publication yet.
        ['kind' => 'email', 'value' => 'info@shelterjo.com', 'is_public' => false, 'cards' => false, 'status' => 'PENDING OWNER APPROVAL', 'ref' => 'D-035'],
    ],
    'brand' => [
        // D-018: founding year 2019 and anniversary 20/04. 2018 and "since 2022" are OLD OR INCORRECT.
        'brand.founded_year' => ['value' => 2019, 'ref' => 'D-018'],
        'brand.anniversary' => ['value' => '04-20', 'ref' => 'D-018'],
    ],
    'rejected' => [
        ['key' => 'brand.founded_year.legacy', 'value' => 2018, 'ref' => 'D-018',
            'blocked' => ['منذ 2018', 'تأسست عام 2018', 'since 2018', 'founded in 2018', 'منذ 2022', 'since 2022']],
    ],
];
