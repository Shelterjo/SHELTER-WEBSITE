# GA4 MEASUREMENT PLAN (مسودة — لا تنفيذ)

> **الحالة:** `DRAFT` — ~~أسماء الأحداث النهائية تُعرض على الـOwner قبل الاعتماد (§26 من السياسة)~~ **الأسماء حُسمت:** أحداث المنيو (D-149) + قائمة الموقع (D-204 — M27 §18)؛ أي تعديل تقني على اسم يُوثّق ويحفظ المعنى (D-185). `branch_view` · `event_view` · `social_click` = مرشحات اختيارية. **المفتوح:** Key Events (PO-036). لا تنفيذ قبل اعتماد الخطة (§25).
> **مصدر واحد (D-227 — M25 §71):** أحداث المنيو مفصّلة في [`../menu-ia/MENU-MEASUREMENT-PLAN.md`](../menu-ia/MENU-MEASUREMENT-PLAN.md). الخطتان تُدمجان لاحقًا في وثيقة واحدة `ANALYTICS-MEASUREMENT-PLAN.md`، ولا تُكرر الأحداث في مكانين متعارضين.
> **قواعد:** لا أحداث عشوائية · لا تكرار (Double counting — §31) · اختبار بـGTM Preview وGA4 DebugView قبل أي نشر (§30) · أي حدث يعتمد على ميزة غير معتمدة لا يُفعّل.
> **آخر تحديث:** 2026-10-01

## الأحداث المرشحة (من السياسة §26) — ~~كلها `PROPOSED`~~ الأسماء حُسمت (D-149، D-204)؛ الـTriggers والـParameters هنا مسودة

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
| ~~`blog_view`~~ | فتح مقال | `article_id`, `topic` | Topic authority | الـHub | GA4 | متوسطة | `SUPERSEDED` → `article_view` (M12 §9، D-204) |
| `language_switch` | تغيير اللغة | `from`, `to` | حجم جمهور الإنجليزية | كل مكان | GA4 | متوسطة | — |
| ~~`search_use`~~ | استخدام بحث المنيو | `query_length` (بدون نص البحث الكامل حفاظًا على الخصوصية — للنقاش) | تحسين المنيو | المنيو | GA4 | متوسطة | `SUPERSEDED` → `menu_search` + `zero_result_search` (CF-09، D-149)؛ نص البحث يُسجّل Privacy-conscious (CF-10) |

## إضافات من ملحق التنفيذ (D-051 §9–§11)

| Event Name (مقترح) | Trigger | Parameters | ملاحظة |
|---|---|---|---|
| `menu_search` | بحث داخل المنيو | `language`, `market`, `results_count` | ~~قد يغني عن `search_use` — نختار اسمًا واحدًا~~ ✅ معتمد (D-149) ويحل محل `search_use`. الـParameters النهائية في `MENU-MEASUREMENT-PLAN` |
| `article_view` | فتح مقال | `content_type`, `article_id`, `language` | ~~بديل أوضح لـ`blog_view` — نختار اسمًا واحدًا~~ ✅ الاسم المعتمد (D-204 — M27 §18) ويحل محل `blog_view` |
| `site_search` | بحث عام في الموقع (إن وُجد) | `search_term` — ~~للنقاش — خصوصية~~ يُسجّل Privacy-conscious بنفس قواعد التنظيف في `MENU-MEASUREMENT-PLAN` §3 (حجب البريد والأرقام الطويلة، حد 50 حرفًا — D-149، CF-10) | ✅ الاسم من قائمة الـOwner (D-204). علاقته بـ`search` / `view_search_results` في Enhanced Measurement تفصيل تقني يُحسم عند الإعداد بلا ازدواج (D-050، D-185) |
| `zero_result_search` · `branch_filter_change` | بحث بلا نتائج · تغيير الفرع من المستخدم | انظر `MENU-MEASUREMENT-PLAN` | ✅ معتمدة (D-149) |

### معجم الـParameters المقترح (ثابت التسمية — §10)
`branch_id` · `branch_name` · `product_name` · `product_category` · `campaign_name` · `language` · `market` · `destination` · `content_type`
⚠️ **ملاحظة على `event_name`:** في GA4 يوجد بُعد مدمج اسمه "Event name" (اسم الحدث نفسه). استخدام Parameter بنفس الاسم قد يسبب التباسًا في التقارير والتصدير. **اقتراح:** `shelter_event_name` أو `event_title` لاسم الفعالية (Events & Campaigns). يُعتمد مع الأسماء النهائية.

### مبدأ: أحداث ذات معنى تجاري فقط
لا أحداث Scroll ولا "كل ضغطة" (§9). مراجعة إعدادات Enhanced Measurement الافتراضية وإيقاف ما لا يخدم (§8).

## Key Events (تحويلات) — مقترح، بانتظار قرار الـOwner (PO-036)
`directions_click` · `phone_click` (عند تفعيله) · `whatsapp_click` (~~إذا اعتُمد واتساب — D-023~~ واتساب معتمد: `0799009436` — D-058) · `campaign_click` (CTA الحملة). **لا تُعتبر كل ضغطة Conversion** (§11). `menu_view` مؤشر اهتمام وليس Conversion — **توصية Claude:** لا Key Events في أحداث المنيو (D-149، CF-M-104)؛ القرار للـOwner (PO-036).

## مفتوح
- ~~الأسماء النهائية (اعتماد الـOwner).~~ ✅ حُسمت (D-149، D-204). المفتوح: اختيار Key Events (PO-036).
- الـConsent (D-xx لاحقًا — R11-04).
- طريقة التنفيذ (GA-01: Site Kit / Native / GTM).
