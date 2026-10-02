<?php

/*
| Content safety (CONTENT-SOURCE-OF-TRUTH). A published page that contains a phrase listed for its key stays offline
| (App\Services\Content\Pages) — a safety net behind the Owner's review, never a replacement for it.
*/
return [
    'blocked_phrases' => [
        // Franchise commercial safety (Owner, M47 — FRAN-003/008/022): no guarantee of profit, return or success, no
        // "best investment" claim. Answers that say no guarantee is given ("No profit … is guaranteed") stay allowed.
        'franchise' => [
            'أرباح مضمونة', 'ربح مضمون', 'عائد مضمون', 'نجاح مضمون', 'مضمون النجاح', 'أفضل فرصة استثمار', 'أفضل فرصة استثمارية',
            'guaranteed roi', 'guaranteed profit', 'guaranteed return', 'guaranteed success', 'guaranteed income', 'best investment opportunity',
        ],
    ],
];
