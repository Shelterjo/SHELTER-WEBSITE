<?php

/*
| Menu structure metadata (owner decisions only). Products, names and prices come from the frozen
| inventory file docs/phase-01-discovery/menu/menu-inventory-v1.0.csv (D-135) — never typed here.
*/

return [
    'inventory_csv' => 'docs/phase-01-discovery/menu/menu-inventory-v1.0.csv',
    'subcategory_csv' => 'docs/menu-ia/subcategory-mapping.csv',
    'version' => ['code' => 'MV-2026-10-01', 'status' => 'approved', 'effective_from' => '2026-10-01'], // D-122, D-135

    // F-07 / D-143: SWEETS / حلويات groups CAKE + COOKIES visually; categories keep their identity.
    'groups' => [
        ['code' => 'sweets', 'name_en' => 'SWEETS', 'name_ar' => 'حلويات', 'sort' => 9, 'ref' => 'D-143'],
    ],

    // F-06 display order (SPRING is the seasonal section above the menu, D-117). Arabic category names are
    // PENDING (P-01) except CAKE / COOKIES (D-132); suggestions are stored but never published.
    'categories' => [
        'CAT-009' => ['sort' => 0, 'type' => 'seasonal', 'suggested_ar' => 'سبرينغ'],
        'CAT-008' => ['sort' => 1, 'suggested_ar' => 'قهوة مختصة'],
        'CAT-001' => ['sort' => 2, 'suggested_ar' => 'مشروبات ساخنة'],
        'CAT-002' => ['sort' => 3, 'suggested_ar' => 'مشروبات باردة'],
        'CAT-006' => ['sort' => 4, 'suggested_ar' => 'فرابيه'],
        'CAT-004' => ['sort' => 5, 'suggested_ar' => 'ميلك شيك'],
        'CAT-005' => ['sort' => 6, 'suggested_ar' => 'سموذي'],
        'CAT-003' => ['sort' => 7, 'suggested_ar' => 'مشروبات غازية'],
        'CAT-007' => ['sort' => 8, 'suggested_ar' => 'شاي'],
        'CAT-010' => ['sort' => 9, 'group' => 'sweets', 'name_ar' => 'كيك', 'ref' => 'D-132'],
        'CAT-011' => ['sort' => 10, 'group' => 'sweets', 'name_ar' => 'كوكيز', 'ref' => 'D-132'],
    ],
];
