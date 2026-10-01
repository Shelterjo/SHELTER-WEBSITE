# GA4 MEASUREMENT PLAN (مسودة — لا تنفيذ)

> **الحالة:** `DRAFT` — **أسماء الأحداث النهائية تُعرض على الـOwner قبل الاعتماد** (§26 من السياسة). لا تنفيذ قبل اعتماد الخطة (§25).
> **قواعد:** لا أحداث عشوائية · لا تكرار (Double counting — §31) · اختبار بـGTM Preview وGA4 DebugView قبل أي نشر (§30) · أي حدث يعتمد على ميزة غير معتمدة لا يُفعّل.
> **آخر تحديث:** 2026-10-01

## الأحداث المرشحة (من السياسة §26) — كلها `PROPOSED`

| Event Name (مقترح) | Trigger | Parameters (مقترحة) | Purpose | Page | Destination | Business Value | ملاحظة |
|---|---|---|---|---|---|---|---|
| `menu_view` | فتح صفحة المنيو | `market`, `language` | قياس الاهتمام بالمنيو (الفعل رقم 1 — D-013) | المنيو | GA4 | عالية | — |
| `menu_category_click` | ضغط فئة في الشريط اللاصق | `category_id`, `market` | أي فئات تهم الزوار | المنيو | GA4 | متوسطة | — |
| `product_view` | فتح تفاصيل صنف (Drawer أو صفحة صنف) | `item_id`, `category_id` | الأصناف الأكثر اهتمامًا | المنيو | GA4 | متوسطة | يعتمد على DB-06 |
| `branch_view` | فتح صفحة فرع | `branch_id` (`drive`/`house`) | الاهتمام بكل فرع | صفحة الفرع | GA4 | عالية | — |
| `directions_click` | ضغط "الاتجاهات" | `branch_id`, `location` (header / card / branch_page) | **أقرب مؤشر لزيارة الفرع** (الفعل رقم 2) | كل مكان | GA4 | **أعلى** | بدون معاملات تكسر رابط Maps (§33) |
| `phone_click` | ضغط رقم هاتف | `branch_id`, `phone_purpose` (`general` · `complaints_feedback_franchise` · `catering_b2b_events`), `placement` | الاتصال حسب النية (D-059) | الفروع، التواصل، Franchise، B2B | GA4 | عالية | ✅ الأرقام معتمدة (D-057) |
| `whatsapp_click` | ضغط زر واتساب | `branch_id` (إن وُجد), `placement` — **بدون نص الرسالة** | قناة تواصل رئيسية | حسب مقترح `14` | GA4 | عالية | ✅ الرقم معتمد (D-058) · ⏳ مكان/نص الزر |
| `social_click` | ضغط حساب سوشال | `platform` | — | الـFooter | GA4 | منخفضة | ⏸ الحسابات غير مؤكدة (D-036) |
| `campaign_view` | ظهور حملة في منطقة الحملات | `campaign_id` | فعالية الحملات | الرئيسية، الحملات | GA4 | متوسطة | — |
| `campaign_click` | ضغط CTA حملة | `campaign_id`, `cta` | — | — | GA4 | متوسطة | — |
| `event_view` | فتح صفحة فعالية | `event_id` | — | الفعاليات | GA4 | متوسطة | — |
| `blog_view` | فتح مقال | `article_id`, `topic` | Topic authority | الـHub | GA4 | متوسطة | — |
| `language_switch` | تغيير اللغة | `from`, `to` | حجم جمهور الإنجليزية | كل مكان | GA4 | متوسطة | — |
| `search_use` | استخدام بحث المنيو | `query_length` (بدون نص البحث الكامل حفاظًا على الخصوصية — للنقاش) | تحسين المنيو | المنيو | GA4 | متوسطة | — |

## إضافات من ملحق التنفيذ (D-051 §9–§11)

| Event Name (مقترح) | Trigger | Parameters | ملاحظة |
|---|---|---|---|
| `menu_search` | بحث داخل المنيو | `language`, `market`, `results_count` | قد يغني عن `search_use` — نختار اسمًا واحدًا |
| `article_view` | فتح مقال | `content_type`, `article_id`, `language` | بديل أوضح لـ`blog_view` — نختار اسمًا واحدًا |
| `site_search` | بحث عام في الموقع (إن وُجد) | `search_term` (للنقاش — خصوصية) | GA4 لديه حدث موصى به اسمه `search` وحدث Enhanced Measurement `view_search_results` — **قرار:** نستخدم الأسماء الموصى بها من Google أم أسماء مخصصة؟ |

### معجم الـParameters المقترح (ثابت التسمية — §10)
`branch_id` · `branch_name` · `product_name` · `product_category` · `campaign_name` · `language` · `market` · `destination` · `content_type`
⚠️ **ملاحظة على `event_name`:** في GA4 يوجد بُعد مدمج اسمه "Event name" (اسم الحدث نفسه). استخدام Parameter بنفس الاسم قد يسبب التباسًا في التقارير والتصدير. **اقتراح:** `shelter_event_name` أو `event_title` لاسم الفعالية (Events & Campaigns). يُعتمد مع الأسماء النهائية.

### مبدأ: أحداث ذات معنى تجاري فقط
لا أحداث Scroll ولا "كل ضغطة" (§9). مراجعة إعدادات Enhanced Measurement الافتراضية وإيقاف ما لا يخدم (§8).

## Key Events (تحويلات) — مقترح للنقاش
`directions_click` · `phone_click` (عند تفعيله) · `whatsapp_click` (إذا اعتُمد واتساب — D-023) · `campaign_click` (CTA الحملة). **لا تُعتبر كل ضغطة Conversion** (§11). `menu_view` مؤشر اهتمام وليس Conversion — للنقاش.

## مفتوح
- الأسماء النهائية (اعتماد الـOwner).
- الـConsent (D-xx لاحقًا — R11-04).
- طريقة التنفيذ (GA-01: Site Kit / Native / GTM).
