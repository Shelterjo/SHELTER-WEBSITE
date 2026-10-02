<?php

/*
| شلتور / SHALTOOR — the website assistant (M69, D-346, docs/SHALTOOR-ASSISTANT-SPEC.md).
| Intents are matched on normalized words (App\Services\Content\Search\Normalizer: أ/إ/آ → ا, ة → ه, ى → ي, lower case),
| so each list is written once in plain spelling; Jordanian everyday words are included. Answers come from Master Data,
| the menu catalogue, the hours resolver, events and campaigns — never from text written here.
*/

return [
    'max_question_length' => 500,
    'retention_days' => 90,
    'rate_limit' => ['per_minute' => 12, 'per_day' => 150],
    // A question asked this many times in 7 days without an answer becomes a "needs attention" item.
    'unanswered_alert_threshold' => 3,

    'intents' => [
        // Never answered from data: money and private matters → a polite "no confirmed information" + the right contact.
        'private' => ['راتب', 'رواتب', 'معاش', 'salary', 'wage', 'wages', 'باسورد', 'كلمه السر', 'كلمه المرور', 'password', 'admin', 'dashboard', 'لوحه التحكم', 'system prompt', 'prompt', 'تعليمات النظام', 'ignore previous', 'ignore all', 'تجاهل', 'api key', 'token', 'database', 'قاعده البيانات', 'cv', 'سيره ذاتيه لمتقدم'],
        'franchise_money' => ['ارباح', 'ربح', 'رسوم', 'رسوم الفرنشايز', 'عائد', 'نسبه', 'كلفه', 'تكلفه', 'تكاليف', 'استثمار', 'راس مال', 'royalty', 'royalties', 'fee', 'fees', 'roi', 'profit', 'profits', 'revenue', 'investment', 'cost', 'costs', 'territory', 'حصريه'],
        'greeting' => ['مرحبا', 'مرحبتين', 'هلا', 'هلو', 'اهلا', 'السلام', 'مساء الخير', 'صباح الخير', 'hi', 'hello', 'hey', 'salam', 'good morning', 'good evening'],
        'thanks' => ['شكرا', 'يسلمو', 'يعطيك العافيه', 'مشكور', 'thanks', 'thank you', 'thx'],
        'compare' => ['الفرق', 'فرق', 'شو الفرق', 'ايش الفرق', 'difference', 'compare', 'vs', 'versus'],
        'hours' => ['ساعات', 'دوام', 'الدوام', 'مفتوح', 'مفتوحين', 'فاتح', 'فاتحين', 'بتفتح', 'بتفتحو', 'بتفتحوا', 'تفتح', 'يفتح', 'بتسكر', 'بسكر', 'بتسكرو', 'بتسكروا', 'تسكر', 'يسكر', 'سكر', 'يغلق', 'مغلق', 'مسكر', 'متى', 'لاي ساعه', 'لاي وقت', 'open', 'opening', 'close', 'closing', 'closed', 'hours', 'until', 'when'],
        'location' => ['وين', 'فين', 'موقع', 'موقعكم', 'مكان', 'عنوان', 'العنوان', 'لوكيشن', 'اللوكيشن', 'اتجاهات', 'الطريق', 'خريطه', 'جوجل ماب', 'where', 'location', 'address', 'directions', 'map', 'maps', 'find you'],
        'branches' => ['فروع', 'الفروع', 'فرعين', 'فرع', 'branches', 'branch', 'locations'],
        'complaint' => ['شكوى', 'شكوي', 'شكاوي', 'اشتكي', 'مشكله', 'اقتراح', 'اقتراحات', 'ملاحظه', 'ملاحظات', 'complaint', 'complaints', 'complain', 'suggestion', 'suggestions', 'feedback'],
        'catering' => ['كيترنج', 'كاترينج', 'تموين', 'شركات', 'شركه', 'طلبيه كبيره', 'طلبيات', 'مناسبه', 'مناسبات', 'حفله', 'b2b', 'catering', 'corporate', 'bulk', 'company'],
        'contact' => ['تواصل', 'اتواصل', 'نتواصل', 'رقم', 'الرقم', 'تلفون', 'تليفون', 'هاتف', 'اتصال', 'اتصل', 'واتس', 'واتساب', 'وتس', 'ايميل', 'بريد', 'contact', 'phone', 'call', 'number', 'whatsapp', 'email', 'reach'],
        'careers' => ['وظيفه', 'وظائف', 'وظايف', 'شغل', 'توظيف', 'اشتغل', 'اقدم', 'تقديم', 'فرصه عمل', 'سيره', 'job', 'jobs', 'career', 'careers', 'hiring', 'work with', 'apply', 'vacancy', 'vacancies'],
        'franchise' => ['فرنشايز', 'فرانشايز', 'امتياز', 'شراكه', 'شريك', 'شركاء', 'وكاله', 'franchise', 'partner', 'partnership', 'partners'],
        'events' => ['فعاليه', 'فعاليات', 'ايفنت', 'ايفنتات', 'event', 'events'],
        'offers' => ['عرض', 'عروض', 'خصم', 'خصومات', 'اوفر', 'اوفرات', 'offer', 'offers', 'promo', 'promotion', 'deal', 'deals', 'discount'],
        'menu' => ['منيو', 'المنيو', 'قائمه', 'مشروبات', 'مشروب', 'اكل', 'بتبيعو', 'شو عندكم', 'menu', 'drinks', 'drink', 'food'],
        'about' => ['مين انتو', 'من انتم', 'عن شلتر', 'مين شلتر', 'شو شلتر', 'قصتكم', 'تاسست', 'about', 'who are you', 'your story', 'founded'],
    ],

    // Words that are not part of a product name when looking a product up.
    'fillers' => ['عندكم', 'عندكو', 'عندك', 'في', 'فيه', 'فيها', 'كم', 'بكم', 'قديش', 'سعر', 'اسعار', 'شو', 'ايش', 'بدي', 'اريد', 'ابغى', 'ممكن', 'هل', 'يوجد', 'موجود', 'متوفر', 'متوفره', 'عن', 'من', 'على', 'ال', 'لو', 'سمحت', 'do', 'you', 'have', 'how', 'much', 'is', 'the', 'a', 'an', 'price', 'prices', 'of', 'for', 'please', 'any', 'can', 'i', 'get', 'want', 'what', 'about', 'cost', 'available',
        // The brand name is in several product names; alone it asks about SHELTER, not a product.
        'shelter', 'شلتر'],

    // Words that name a menu section (by its page id), besides the section's own approved name.
    'categories' => [
        'cold-drinks' => ['بارد', 'بارده', 'مثلج', 'ايس', 'cold', 'iced', 'ice'],
        'hot-drinks' => ['ساخن', 'سخن', 'حار', 'hot'],
        'sweets' => ['حلويات', 'حلو', 'حلا', 'كيك', 'كوكيز', 'sweets', 'dessert', 'desserts', 'cake', 'cookies'],
        'tea' => ['شاي', 'tea'],
        'smoothies' => ['سموذي', 'سموثي', 'smoothie', 'smoothies'],
        'frappe' => ['فرابيه', 'فرابي', 'فرابتشينو', 'frappe', 'frappuccino'],
        'milkshake' => ['ميلك شيك', 'ميلكشيك', 'milkshake', 'milkshakes'],
        'fizzy-drinks' => ['غازي', 'غازيه', 'فوار', 'fizzy', 'soda'],
        'speciality-coffee' => ['مختصه', 'قهوه مختصه', 'v60', 'في 60', 'specialty', 'speciality', 'filter'],
    ],

    // Words that point at one branch (the slug), besides its approved names.
    'branches' => [
        'drive' => ['درايف', 'الدرايف', 'drive', 'drive-thru', 'drive thru', 'قصر النخيل', 'ارابيلا', 'السياره', 'سياره'],
        'house' => ['هاوس', 'الهاوس', 'house', 'سيتي سنتر', 'ستي سنتر', 'city center', 'city centre', 'مول', 'mall', 'جلسه', 'قعده'],
    ],
];
