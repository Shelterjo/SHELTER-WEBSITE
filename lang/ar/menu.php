<?php

// Menu page interface strings (Menu IA spec). Business content — product and category names, prices, hours — comes
// from Master Data, never from here. Texts marked P-05 / P-07 are proposed wording PENDING OWNER APPROVAL.
return [
    'title' => 'المنيو',
    'page_title' => 'المنيو — :brand', // P-07: title / meta wording pending owner approval
    'prices_note' => 'الأسعار بالدينار الأردني وشاملة الضريبة', // P-05 (fact approved in F-01; wording pending)
    'search_label' => 'بحث',
    'search_placeholder' => 'ابحث في المنيو',
    'search_open' => 'البحث في المنيو',
    'branch_label' => 'الفرع',
    'all_branches' => 'كل الفروع',
    'categories_label' => 'فئات المنيو',
    'all_categories' => 'كل الفئات',
    'items_count' => '{1} صنف واحد|{2} صنفان|[3,10] :count أصناف|[11,99] :count صنفًا|[100,*] :count صنف',
    'results_count' => '{0} لا توجد نتائج|{1} نتيجة واحدة|{2} نتيجتان|[3,10] :count نتائج|[11,99] :count نتيجة|[100,*] :count نتيجة',
    'results_forms' => [ // Intl plural categories for the live result count (spec §8)
        'zero' => 'لا توجد نتائج',
        'one' => 'نتيجة واحدة',
        'two' => 'نتيجتان',
        'few' => ':count نتائج',
        'many' => ':count نتيجة',
        'other' => ':count نتيجة',
    ],
    'no_results_title' => 'لا توجد نتائج لـ «:query»',
    'no_results_text' => 'جرّب كلمة أخرى أو تصفّح الفئات.',
    'clear_search' => 'مسح البحث',
    'browse_categories' => 'تصفّح الفئات',
    'go_to_category' => 'انتقل إلى :category',
    'more' => 'المزيد',
    'seasonal' => 'موسمي',
    'new' => 'جديد',
    'unavailable' => 'غير متوفر حاليًا',
    'unavailable_at' => 'غير متوفر حاليًا في :branch',
    'only_at' => 'متوفر في :branches فقط',
    'list_separator' => '، ',
];
