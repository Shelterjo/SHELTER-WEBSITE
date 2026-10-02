<?php

// Franchise / partnerships (docs/franchise/02 §2, §5 and 03). Page content comes from the published page (PO-030);
// here: field labels from the field matrix, the Owner's CTA and after-submit wording, and functional UI text only.
return [
    'eyebrow' => 'FRANCHISE & PARTNERSHIPS',
    'cta' => 'ابدأ طلب الشراكة',
    'faq_title' => 'الأسئلة الشائعة',
    'form_title' => 'طلب الشراكة',
    'form_note' => 'الحقول المعلّمة بـ* مطلوبة.',
    'closed_title' => 'استفسارات الفرنشايز',
    'closed_text' => 'للاستفسار عن الشراكة، تواصل معنا مباشرة.',
    'groups' => [
        'about' => 'عنك',
        'market' => 'السوق',
        'experience' => 'الخبرة',
        'opportunity' => 'الفرصة',
        'consent' => 'الإقرار',
    ],
    'fields' => [
        'full_name' => 'الاسم الكامل',
        'phone' => 'الهاتف',
        'email' => 'البريد الإلكتروني',
        'country' => 'الدولة',
        'city' => 'المدينة',
        'market' => 'السوق / المنطقة المهتم بها',
        'interest' => 'نوع الاهتمام بالشراكة',
        'experience_band' => 'الخبرة في الأعمال',
        'experience_text' => 'نبذة قصيرة عن خبرتك (اختياري)',
        'owns_business' => 'هل تملك أو تدير عملًا حاليًا؟',
        'location_status' => 'حالة الموقع المقترح',
        'introduction' => 'رسالة تعريفية قصيرة',
        'consent' => 'الموافقة على معالجة البيانات',
    ],
    'options' => [
        'experience_band' => [
            'none' => 'بدون خبرة', 'lt1' => 'أقل من سنة', 'y1_2' => '1–2 سنة', 'y3_5' => '3–5 سنوات', 'y6_10' => '6–10 سنوات', 'gt10' => 'أكثر من 10 سنوات',
        ],
        'yes_no' => ['yes' => 'نعم', 'no' => 'لا'],
        'location_status' => ['has_site' => 'لدي موقع محدد', 'searching' => 'أبحث عن موقع', 'not_started' => 'لم أبدأ البحث بعد'],
    ],
    'choose' => 'اختر',
    'consent_label' => 'أوافق على ما سبق',
    'submit' => 'إرسال طلب الشراكة',
    'submitting' => 'جارٍ الإرسال…',
    'errors' => [
        'summary' => 'يرجى تصحيح الأخطاء التالية:',
        'required' => 'هذا الحقل مطلوب.',
        'choose' => 'اختر قيمة من القائمة.',
        'too_long' => 'النص أطول من المسموح (:max حرفًا).',
        'phone' => 'أدخل رقم هاتف صحيحًا.',
        'email' => 'أدخل بريدًا إلكترونيًا صحيحًا.',
        'consent' => 'يجب الموافقة لإرسال الطلب.',
        'generic' => 'تعذر إرسال الطلب الآن. حاول مرة أخرى بعد قليل.',
        'expired' => 'انتهت صلاحية النموذج. حدّث الصفحة وأعد المحاولة؛ بياناتك محفوظة.',
        'rate' => 'تم إرسال طلبات كثيرة خلال وقت قصير. حاول لاحقًا.',
    ],
    'success' => [
        'title' => 'شكرًا لاهتمامك بالشراكة مع SHELTER COFFEE',
        'number' => 'رقم الطلب',
        'text' => 'سيقوم فريق SHELTER بمراجعة الطلب والتواصل عند الانتقال إلى المرحلة التالية.',
        'back' => 'العودة إلى الموقع',
    ],
];
