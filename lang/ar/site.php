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

    'errors' => [
        '404_title' => 'الصفحة غير موجودة',
        '404_text' => 'ربما تغيّر الرابط أو أُزيلت الصفحة. هذه أماكن مفيدة للبدء:',
        '500_title' => 'حدث خطأ غير متوقع',
        '500_text' => 'لم نتمكن من عرض الصفحة الآن. حاول مرة أخرى بعد قليل.',
        'retry' => 'حاول مرة أخرى',
        'links' => 'روابط مفيدة',
    ],
];
