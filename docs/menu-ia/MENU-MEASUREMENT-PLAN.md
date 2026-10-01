# MENU MEASUREMENT PLAN (Phase I)

> **الحالة:** `DRAFT — PENDING OWNER APPROVAL` · **لا تنفيذ على Google الآن** (الموجز §69) · **آخر تحديث:** 2026-10-01
>
> **الإطار:** خطة GA4 العامة [`../google/GA4-MEASUREMENT-PLAN.md`](../google/GA4-MEASUREMENT-PLAN.md).
> - هذه الخطة تفصّل أحداث المنيو.
> - تحلّ اختيار الاسم المفتوح هناك: `menu_search` ويُحذف `search_use` (CF-09).
>
> **المبدأ:** أحداث ذات معنى فقط. **لا Key Event في المنيو.** القرار التجاري الأقرب للزيارة (`directions_click`، `phone_click`) في صفحات الفروع.

## 1. الأحداث

| Event | متى يُرسل | Parameters | Key Event؟ |
|---|---|---|---|
| `menu_view` | مرة لكل فتح صفحة منيو، بعد تهيئة الموافقة | `menu_language` · `market` · `branch_context` · `entry_context` · `menu_version` | ❌ — مقياس اهتمام |
| `menu_search` | **مرة لكل نص بحث مكتمل:** ضغط اقتراح، أو Enter، أو توقف عن الكتابة ثانية واحدة. **لا يُرسل مع كل حرف** | `search_term` (منظّف) · `search_input_language` · `results_count` · `search_source` · `branch_context` · `menu_language` | ❌ |
| `zero_result_search` | عندما يكون `results_count = 0` في `menu_search`. حدث مستقل لتقارير وتنبيهات سهلة | `search_term` (منظّف) · `search_input_language` · `branch_context` · `menu_language` | ❌ |
| `menu_category_click` | ضغط فئة أو قسم فرعي من الشريط أو القائمة الجانبية أو "كل الفئات". **لا يُرسل عند تغيّر الزر النشط بالتمرير** | `category_id` · `subcategory_id` · `nav_source` · `branch_context` · `menu_language` | ❌ |
| `product_view` | فتح تفاصيل صنف (Sheet أو Modal). **لا يُرسل عند ظهور البطاقة** | `menu_item_id` · `item_name` · `category_id` · `subcategory_id` · `detail_source` · `branch_context` · `menu_language` | ❌ |
| `branch_filter_change` | تغيير الفرع **من المستخدم** فقط، وليس عند التحميل | `branch_from` · `branch_to` · `branch_status` · `menu_language` | ❌ |

**عدّ البحث بلا ازدواج:**
- إجمالي عمليات البحث = `menu_search`.
- `zero_result_search` مجموعة فرعية منها لها حدث مستقل.
- لا يُجمع الحدثان معًا في تقرير "عدد البحث".

## 2. معجم الـParameters

| Parameter | القيم | ملاحظة |
|---|---|---|
| `menu_language` | `ar` · `en` | لغة الصفحة |
| `market` | `jo` | |
| `branch_context` | `all` · `drive` · `house` | |
| `entry_context` | `direct` · `branch_link` (وصل بـ`?branch`) · `internal` | من الرابط وموقع الإحالة |
| `menu_version` | `MV-2026-10-01` | يربط القياس بإصدار المنيو (D-107) |
| `search_term` | نص منظّف ≤ 50 حرفًا | §3 |
| `search_input_language` | `ar` · `en` · `mixed` | بنوع الحروف المكتوبة |
| `results_count` | عدد صحيح | يُسجّل أيضًا كـCustom Metric |
| `search_source` | `suggestion` · `submit` · `pause` | |
| `category_id` | `CAT-001` … · `sweets` | المعرّفات المجمّدة |
| `subcategory_id` | `DSC-…` أو فارغ | |
| `nav_source` | `chip` · `subchip` · `sidebar` · `all_categories` | |
| `menu_item_id` | `PRD-00058` | ⚠️ **تعديل تسمية موثّق:** `menu_item_id` بدل `item_id` حتى لا يتعارض مع `item_id` الخاص بالتجارة الإلكترونية في GA4. **المعنى نفسه** |
| `item_name` | الاسم الإنجليزي المعتمد | ثابت بين اللغتين لسهولة التقارير |
| `detail_source` | `card` · `search_jump` · `deep_link` | |
| `branch_from` · `branch_to` | `all` · `drive` · `house` | |
| `branch_status` | `open` · `closing_soon` · `closed` | حالة الفرع الجديد لحظة الاختيار |

## 3. الخصوصية (نص البحث)
1. **قبل الإرسال:** Trim، Lowercase، توحيد المسافات، قص إلى 50 حرفًا.
2. **الحجب:** إذا احتوى النص على `@`، أو 5 أرقام متتالية فأكثر (هاتف)، أو `http`، أو `www` ← يُرسل `[redacted]`.
3. **ما لا يُرسل أبدًا:**
   - اسم أو هاتف أو بريد أو معرّف مستخدم.
   - الفرع المحفوظ كـUser Property.
4. **Consent Mode v2:** قبل الموافقة إشارات بلا Cookies حسب الإعداد العام (`docs/google`). لا تجاوز.
5. **الاحتفاظ بالبيانات** حسب إعداد GA4 العام للحساب.

## 4. أمثلة Payload (dataLayer)

```js
dataLayer.push({ event: 'menu_view', menu_language: 'ar', market: 'jo', branch_context: 'drive', entry_context: 'branch_link', menu_version: 'MV-2026-10-01' });
dataLayer.push({ event: 'menu_search', search_term: 'سبانش', search_input_language: 'ar', results_count: 2, search_source: 'suggestion', branch_context: 'all', menu_language: 'ar' });
dataLayer.push({ event: 'zero_result_search', search_term: 'cold brew', search_input_language: 'en', branch_context: 'house', menu_language: 'en' });
dataLayer.push({ event: 'menu_category_click', category_id: 'CAT-002', subcategory_id: 'DSC-COLD-SHK', nav_source: 'subchip', branch_context: 'all', menu_language: 'ar' });
dataLayer.push({ event: 'product_view', menu_item_id: 'PRD-00058', item_name: 'ICED SPANISH LATTE', category_id: 'CAT-002', subcategory_id: 'DSC-COLD-LAT', detail_source: 'card', branch_context: 'all', menu_language: 'en' });
dataLayer.push({ event: 'branch_filter_change', branch_from: 'all', branch_to: 'house', branch_status: 'closed', menu_language: 'ar' });
```

> مثال `zero_result_search` بـ"cold brew" واقعي: Cold Brew يُباع لكنه ليس في الملف (`ACTIVE — DATA INCOMPLETE`). هذا بالضبط ما يكشفه القياس.

## 5. GTM ← GA4

| GTM | التفاصيل |
|---|---|
| Triggers | Custom Event واحد لكل حدث: `menu_view` · `menu_search` · `zero_result_search` · `menu_category_click` · `product_view` · `branch_filter_change` |
| Variables | Data Layer Variable لكل Parameter أعلاه |
| Tags | GA4 Event Tag لكل حدث بنفس الاسم والـParameters · Consent: يتطلب `analytics_storage` حسب الإعداد العام |
| GA4 Custom Dimensions (Event scope) | `menu_language` · `branch_context` · `entry_context` · `search_input_language` · `search_source` · `category_id` · `subcategory_id` · `nav_source` · `menu_item_id` · `detail_source` · `branch_from` · `branch_to` · `branch_status` · `menu_version` (14 من 50 متاحة) |
| GA4 Custom Metric | `results_count` |
| Search term | `search_term` يُقرأ في بُعد GA4 المدمج "Search term" |
| Enhanced Measurement | "Site search" يراقب Parameters في الرابط، وبحثنا لا يغيّر الرابط فلا تعارض. يُراجع ضمن إعداد EM العام |

## 6. التحقق (Debug)
1. **أداة التحقق:** GTM Preview + GA4 DebugView على نسخة Preview، وليس على Production.
2. **حالات الاختبار:**
   1. فتح المنيو بلا Parameter.
   2. فتحه بـ`?branch=house`.
   3. بحث بالعربي ينتهي باقتراح.
   4. بحث بالإنجليزي بلا نتائج.
   5. نص فيه رقم هاتف ← `[redacted]`.
   6. ضغط فئة وقسم فرعي.
   7. التمرير وحده ← **لا** حدث.
   8. فتح صنف ثم Back.
   9. تغيير الفرع.
   10. تحميل صفحة فيها `?branch` ← **لا** `branch_filter_change`.
3. **لا ازدواج:** حدث واحد لكل فعل. `menu_search` مرة لكل نص، و`product_view` مرة لكل فتح.
4. **الاعتماد:** عرض النتائج على الـOwner، ثم النشر حسب سياسة Google (ملخص ← موافقة ← تنفيذ).
