<?php

// Voice of Customer (VOICE-OF-CUSTOMER §2). The questions follow the M32 §15 example; their final AR/EN wording and the
// entry points are the Owner's pending decision (PO-063) — until then the form is closed in production. Functional UI
// text only; the privacy hint is the spec's own wording.
return [
    'title' => 'كيف كانت تجربتك؟',
    'lead' => 'رأيك يصلنا دون اسم أو رقم هاتف.',
    'required_note' => 'الحقول المعلّمة بـ* مطلوبة.',
    'details' => 'تفاصيل (اختياري)',
    'scale_hint' => '1 الأدنى · 5 الأعلى',
    'fields' => [
        'branch' => 'الفرع',
        'rating_overall' => 'التجربة العامة',
        'rating_coffee' => 'القهوة',
        'rating_service' => 'الخدمة',
        'rating_cleanliness' => 'النظافة',
        'rating_speed' => 'السرعة',
        'comment' => 'تعليق (اختياري)',
    ],
    'comment_hint' => 'لا تكتب بيانات شخصية في التعليق.',
    'submit' => 'إرسال',
    'submitting' => 'جارٍ الإرسال…',
    'errors' => [
        'summary' => 'يرجى تصحيح الأخطاء التالية:',
        'required' => 'هذا الحقل مطلوب.',
        'choose' => 'اختر قيمة من القائمة.',
        'too_long' => 'عدد الأحرف أكبر من الحد المسموح (:max).',
        'generic' => 'تعذر الإرسال، حاول مجددًا.',
        'expired' => 'انتهت صلاحية الصفحة. حدّثها وأعد المحاولة؛ إجاباتك محفوظة.',
        'rate' => 'تم إرسال آراء كثيرة خلال وقت قصير. حاول لاحقًا.',
    ],
    'success' => [
        'title' => 'شكرًا لك',
        'text' => 'وصلنا رأيك.',
        'back' => 'العودة إلى الموقع',
    ],
];
