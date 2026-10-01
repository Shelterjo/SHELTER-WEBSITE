<?php

// Design-system component strings (x-ui.*). Interface wording only: business content (names, prices, hours…)
// always comes from Master Data, never from here. Arabic is the primary language; lang/en/ui.php mirrors every key.
return [
    'skip_to_content' => 'انتقل إلى المحتوى',
    'close' => 'إغلاق',
    'loading' => 'جارٍ التحميل…',
    'optional' => 'اختياري',
    'error_prefix' => 'خطأ:',
    'error_summary_title' => 'يوجد خطأ، راجع الحقول التالية',
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
];
