<?php

// Interface strings only. Business content comes from the database (Master Data / pages), never from here.
// Approved wording is marked with its decision; everything else is functional UI text. Home copy is DRAFT until the
// homepage copy options are approved (D-068).
return [
    'brand' => 'SHELTER COFFEE',
    'brand_ar' => 'شلتر كوفي', // D-007
    'skip_to_content' => 'انتقل إلى المحتوى',
    'choose_language' => 'اختر اللغة',
    'switch_language' => 'English',

    'nav' => [
        'label' => 'التنقل الرئيسي',
        'home' => 'الرئيسية',
        'menu' => 'المنيو',
        'careers' => 'التوظيف',
        'locations' => 'الفروع',
    ],
    'language' => [
        'label' => 'اللغة',
        'ar' => 'العربية',
        'en' => 'English',
    ],

    'time' => ['am' => 'ص', 'pm' => 'م'],
    'weekdays' => [
        0 => 'الأحد', 1 => 'الاثنين', 2 => 'الثلاثاء', 3 => 'الأربعاء', 4 => 'الخميس', 5 => 'الجمعة', 6 => 'السبت',
    ],

    // Open-state wording (HOURS-010 / HOURS-011). closes_in follows CLDR plural categories (App\Support\PluralCategory).
    'status' => [
        'open_until' => 'مفتوح الآن — حتى :time',
        'closes_in' => [
            'zero' => 'يغلق الآن',
            'one' => 'يغلق بعد دقيقة',
            'two' => 'يغلق بعد دقيقتين',
            'few' => 'يغلق بعد :n دقائق',
            'many' => 'يغلق بعد :n دقيقة',
            'other' => 'يغلق بعد :n دقيقة',
        ],
        'closed' => 'مغلق الآن',
        'closed_opens' => 'مغلق الآن — يفتح :time',
        'closed_opens_tomorrow' => 'مغلق الآن — يفتح غدًا :time',
        'closed_opens_day' => 'مغلق الآن — يفتح :day :time',
        'special' => 'ساعات اليوم معدّلة عن المعتاد',
    ],

    'hours' => [
        'title' => 'ساعات الدوام',
        'caption' => 'ساعات الدوام المعتادة لكل يوم من أيام الأسبوع',
        'today_hours' => 'اليوم: :hours',
        'timezone' => 'الأوقات بتوقيت :market.',
    ],

    'home' => [
        'title' => 'شلتر كوفي — SHELTER COFFEE',
        'lead' => 'تصفّح المنيو، واعرف أيّ فرع مفتوح الآن ومتى يُغلق.',
        'cta_menu' => 'تصفّح المنيو',
        'cta_locations' => 'الفروع والمواعيد',
        'branches_title' => 'الفروع',
        'branches_lead' => 'الحالة تتحدث تلقائيًا بحسب ساعات الدوام.',
        'branches_link' => 'كل التفاصيل',
    ],

    'locations' => [
        'title' => 'الفروع',
        'lead' => 'ساعات الدوام وحالة كل فرع الآن.',
        'details' => 'الساعات والتفاصيل',
        'empty' => 'لا توجد فروع للعرض حاليًا.',
    ],

    'branch' => [
        'title' => ':name — ساعات الدوام',
        'contact' => 'تواصل مع الفرع',
        'call' => 'اتصال',
        'whatsapp' => 'راسلنا على واتساب', // D-063 (provisional)
        'whatsapp_short' => 'واتساب',
        'actions' => 'إجراءات سريعة',
        'menu' => 'منيو هذا الفرع',
        'back' => 'كل الفروع',
    ],

    // Contact by intent (D-059; titles from the approved intent map, docs/phase-01-discovery/14 §1 / §4).
    'contact' => [
        'title' => 'تواصل معنا',
        'lead' => 'اختر سبب تواصلك لتصل إلى الرقم المناسب مباشرة.',
        'general' => [
            'title' => 'التواصل العام والفروع',
            'lead' => 'أسئلة عامة، الفروع، ساعات الدوام والطلبات.',
            'branches' => 'حالة الفروع الآن',
            'link' => 'الفروع وساعات الدوام',
        ],
        'complaints' => [
            'title' => 'الشكاوى والاقتراحات',
            'lead' => 'لشكوى أو اقتراح يخصّ زيارتك.',
        ],
        'catering' => [
            'title' => 'الكيترنج والأعمال والفعاليات',
            'lead' => 'الكيترنج وطلبات الشركات والفعاليات.',
        ],
        'franchise' => [
            'title' => 'استفسارات الفرنشايز',
            'lead' => 'للاستفسار عن الامتياز التجاري.',
            'link' => 'صفحة الفرنشايز',
        ],
        'status_note' => 'حالة الفروع تتحدث تلقائيًا بحسب ساعات الدوام.',
    ],

    // Site search (SI-B08, GLOBAL-SEARCH §4). results follows CLDR plural categories (App\Support\PluralCategory).
    'search' => [
        'title' => 'البحث',
        'lead' => 'ابحث في المنيو والفروع وصفحات الموقع.',
        'label' => 'ابحث في الموقع',
        'submit' => 'بحث',
        'open' => 'البحث',
        'results' => [
            'zero' => 'لا توجد نتائج',
            'one' => 'نتيجة واحدة',
            'two' => 'نتيجتان',
            'few' => ':count نتائج',
            'many' => ':count نتيجة',
            'other' => ':count نتيجة',
        ],
        'for' => 'نتائج البحث عن «:query»',
        'none_title' => 'لا توجد نتائج لـ «:query»',
        'none_text' => 'جرّب كلمة أخرى، أو ابدأ من هنا:',
        'groups' => [
            'menu' => 'المنيو',
            'branch' => 'الفروع',
            'page' => 'الصفحات',
            'event' => 'الفعاليات',
            'faq' => 'الأسئلة الشائعة',
        ],
    ],

    // Events and campaigns (SI-M07 / SI-M08 — DX-014). The event text itself comes from the Owner (experiences).
    'events' => [
        'title' => 'الفعاليات',
        'lead' => 'الفعاليات والحملات الجارية والقادمة.',
        'empty_title' => 'لا توجد فعاليات حاليًا',
        'empty_text' => 'عند الإعلان عن فعالية جديدة ستظهر هنا.',
        'state' => [
            'now' => 'جارية الآن',
            'upcoming' => 'قريبًا',
            'ended' => 'انتهت',
        ],
        'when' => 'الموعد',
        'where' => 'المكان',
        'terms' => 'الشروط',
        'starts' => 'يبدأ: :when',
        'ends' => 'ينتهي: :when',
        'ended_notice' => 'انتهت هذه الفعالية.',
        'back' => 'كل الفعاليات',
    ],

    'page' => [
        'updated' => 'آخر تحديث: :date',
    ],

    'errors' => [
        '404_title' => 'الصفحة غير موجودة',
        '404_text' => 'ربما تغيّر الرابط أو أُزيلت الصفحة. هذه أماكن مفيدة للبدء:',
        '500_title' => 'حدث خطأ غير متوقع',
        '500_text' => 'لم نتمكن من عرض الصفحة الآن. حاول مرة أخرى بعد قليل.',
        '503_title' => 'نعمل على تحسين الموقع',
        '503_text' => 'سنعود خلال وقت قصير. شكرًا لصبرك.',
        'retry' => 'حاول مرة أخرى',
        'links' => 'روابط مفيدة',
    ],
];
