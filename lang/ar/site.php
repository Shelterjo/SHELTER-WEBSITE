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
        'feature' => ['announcement' => 'إعلان', 'campaign' => 'عرض', 'event' => 'فعالية'],
        // M57: the approved positioning line (D-332) in the Google title and the opening line; one short sentence.
        'title' => 'شلتر كوفي — قهوة مختصة ودرايف ثرو في إربد',
        'lead' => 'قهوة مختصة ودرايف ثرو في إربد. تصفّح المنيو، واعرف أيّ فرع مفتوح الآن ومتى يُغلق.',
        'cta_menu' => 'تصفّح المنيو',
        'cta_locations' => 'الفروع والمواعيد',
        'branches_title' => 'فروعنا في إربد',
        'branches_lead' => 'الحالة تتحدث تلقائيًا بحسب ساعات الدوام.',
        'branches_link' => 'كل التفاصيل',
    ],

    'locations' => [
        'title' => 'الفروع',
        'lead' => 'فروع شلتر كوفي في إربد، مع ساعات الدوام وحالة كل فرع الآن.',
        'details' => 'الساعات والتفاصيل',
        'empty' => 'لا توجد فروع للعرض حاليًا.',
    ],

    'branch' => [
        'place' => 'العنوان والخدمات',
        'directions' => 'الاتجاهات على خرائط Google',
        'directions_short' => 'الاتجاهات',
        'services' => 'الخدمات',
        'payments' => 'طرق الدفع',
        'title' => ':name — :kind في إربد | ساعات الدوام',
        'contact' => 'تواصل مع الفرع',
        'call' => 'اتصال',
        'whatsapp' => 'راسلنا على واتساب', // D-063 (provisional)
        'whatsapp_short' => 'واتساب',
        'actions' => 'إجراءات سريعة',
        'menu' => 'منيو هذا الفرع',
        'back' => 'كل الفروع',
        // What kind of branch (master-data type, D-008) and where: «درايف ثرو في إربد».
        'kinds' => ['drive_thru' => 'درايف ثرو', 'coffee_house' => 'كوفي هاوس'],
        'kind_in_city' => ':kind في :city',
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
        '403_title' => 'لا يمكنك فتح هذه الصفحة',
        '403_text' => 'هذه الصفحة غير متاحة لك. ارجع إلى الصفحة الرئيسية.',
        '419_title' => 'انتهت صلاحية الصفحة',
        '419_text' => 'بقيت الصفحة مفتوحة مدة طويلة. حدّثها ثم حاول مرة أخرى.',
        '429_title' => 'طلبات كثيرة خلال وقت قصير',
        '429_text' => 'انتظر دقيقة ثم حاول مرة أخرى.',
        '4xx_title' => 'تعذّر فتح هذه الصفحة',
        '4xx_text' => 'تحقق من الرابط أو ارجع إلى الصفحة الرئيسية.',
        'form_expired' => 'انتهت صلاحية الصفحة لأنها بقيت مفتوحة مدة طويلة. راجع ما كتبته ثم أرسل مرة أخرى.',
        'retry' => 'حاول مرة أخرى',
        'links' => 'روابط مفيدة',
    ],
    // Google descriptions (meta description) per page and the root gateway line: Owner-approved wording, batch 01 (D-332,
    // docs/copy/META-DESCRIPTIONS-DRAFT-01.md). The Owner may reword any of them (dashboard → Site texts); empty = this text.
    // Google titles (M57 §15) for pages whose on-page heading stays short; Owner-editable in Site texts.
    'titles' => [
        'locations' => 'فروع شلتر كوفي في إربد — ساعات الدوام',
        'contact' => 'تواصل مع شلتر كوفي — إربد',
        'events' => 'فعاليات شلتر كوفي في إربد',
        'careers' => 'وظائف شلتر كوفي في إربد',
    ],
    'meta' => [
        'home' => 'شلتر كوفي في إربد: قهوة مختصة وV60 ومشروبات ساخنة وباردة وحلويات، في فرع درايف ثرو وفرع كوفي هاوس. تصفّح المنيو وساعات الدوام.',
        'menu' => 'منيو شلتر كوفي في إربد: قهوة مختصة وV60، إسبريسو ولاتيه، مشروبات باردة، فرابيه، سموذي، شاي، كيك وكوكيز، مع الأسعار لكل فرع.',
        'locations' => 'فرعا شلتر كوفي في إربد، الدرايف والهاوس: ساعات الدوام وأيّ فرع مفتوح الآن.',
        'branch' => ':name في إربد: ساعات الدوام، وهل الفرع مفتوح الآن، والمنيو بأسعار الفرع، وطرق التواصل.',
        'contact' => 'أرقام التواصل مع شلتر كوفي في إربد: التواصل العام والفروع، الشكاوى والاقتراحات، والكيترنج وطلبات الشركات والفعاليات.',
        'events' => 'فعاليات وحملات شلتر كوفي في إربد: ما يجري الآن وما هو قادم، بالتواريخ والفروع المشاركة.',
        'careers' => 'انضم إلى فريق شلتر كوفي في إربد. قدّم طلب التوظيف وأرفق سيرتك الذاتية، وتابع طلبك برقمه.',
        'awards' => 'جوائز شلتر كوفي في إربد والتقدير الذي نالته، مع الجهة المانحة وسنة كل جائزة.',
        'family' => 'الناس وراء قهوتك: تعرّف على فريق شلتر كوفي في SHELTER Family.',
    ],
    'gateway' => ['lead' => 'قهوة مختصة ودرايف ثرو في إربد. اختر لغتك لتصفّح المنيو وساعات الدوام.'],
    'attributes' => [
        'service' => ['indoor_seating' => 'جلسات داخلية', 'outdoor_seating' => 'جلسات خارجية', 'takeaway' => 'طلبات خارجية', 'wifi' => 'واي فاي', 'parking' => 'مواقف سيارات', 'wheelchair_accessible' => 'مناسب للكراسي المتحركة'],
        'payment' => ['cash' => 'نقدًا', 'card' => 'بطاقة', 'cliq' => 'CliQ', 'mobile_wallet' => 'محفظة إلكترونية'],
    ],
];
