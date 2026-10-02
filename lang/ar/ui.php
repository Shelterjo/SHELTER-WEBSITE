<?php

// Design-system component strings (x-ui.*). Interface wording only: business content (names, prices, hours…)
// always comes from Master Data, never from here. Arabic is the primary language; lang/en/ui.php mirrors every key.
return [
    'skip_to_content' => 'انتقل إلى المحتوى',
    'close' => 'إغلاق',
    'loading' => 'جارٍ التحميل…',
    'optional' => 'اختياري',
    'error_prefix' => 'خطأ:',
    'announcement' => 'إعلان',
    'error_summary_title' => 'يوجد خطأ. راجع الحقول التالية.',
    'select_placeholder' => 'اختر…',
    'breadcrumb' => 'مسار التنقل',
    'main_navigation' => 'التنقل الرئيسي',
    'media_pending' => 'صورة بانتظار اعتماد المالك',
    'alert' => [
        'info' => 'معلومة',
        'success' => 'تم بنجاح',
        'warning' => 'تنبيه',
        'danger' => 'خطأ',
    ],
    'pagination' => [
        'label' => 'التنقل بين الصفحات',
        'previous' => 'السابق',
        'next' => 'التالي',
        'page' => 'الصفحة :page',
        'page_of' => 'الصفحة :page من :total',
    ],
    'search' => [
        'clear' => 'مسح البحث',
        'suggestions' => 'اقتراحات البحث',
    ],
    'event' => [
        'date' => 'التاريخ:',
        'place' => 'المكان:',
    ],
    'currency' => [
        'JOD' => ['symbol' => 'د.أ', 'name' => 'دينار أردني'],
    ],
    'status' => [
        'synced' => 'متزامن',
        'pending' => 'قيد الانتظار',
        'failed' => 'فشل',
        'not_supported' => 'غير مدعوم',
        'manual_action_required' => 'يتطلب إجراءً يدويًا',
        'out_of_sync' => 'غير متزامن',
    ],
    'trend' => [
        'up' => 'ارتفاع:',
        'down' => 'انخفاض:',
    ],
    // Site chrome and branch components (PHASE 2).
    'navigation' => [
        'open' => 'فتح التنقل',
        'title' => 'التنقل',
        'language' => 'اللغة',
    ],
    'hours' => [
        'day' => 'اليوم',
        'time' => 'الساعات',
        'today' => 'اليوم',
        'closed' => 'مغلق',
        'next_day' => 'اليوم التالي',
    ],
    'contact' => [
        'call' => 'اتصال',
        'whatsapp' => 'راسلنا على واتساب', // D-063 (provisional)
    ],
    'footer' => [
        'label' => 'تذييل الموقع',
        'explore' => 'روابط سريعة',
        'contact' => 'تواصل معنا',
        'all_contact' => 'كل قنوات التواصل',
        'follow' => 'تابعنا',
        'legal' => 'الصفحات القانونية',
    ],
];
