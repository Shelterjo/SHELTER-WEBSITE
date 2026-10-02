# TRACKING & COOKIES INVENTORY — جرد التتبع والكوكيز وقاموس أحداث GA4

| البند | القيمة |
|---|---|
| **الحالة** | `DRAFT — PENDING OWNER APPROVAL`. الجرد «اليوم» مأخوذ من الكود؛ «المخطط» اقتراح |
| **آخر تحديث** | 2026-10-02 |
| **الغرض** | جواب واحد على: ماذا يضع الموقع في متصفح الزائر اليوم؟ ماذا سيضع لاحقًا، ومتى، وبأي موافقة؟ وما أسماء أحداث القياس النهائية؟ |
| **المصادر** | `config/session.php` · `.env.example` · `bootstrap/app.php` · `resources/js/ui/track.ts` · `resources/js/menu/page.ts` · `app/Http/Middleware/SecurityHeaders.php` · [`PRIVACY-CENTER`](platform/PRIVACY-CENTER.md) · [`GA4-MEASUREMENT-PLAN`](google/GA4-MEASUREMENT-PLAN.md) · [`MENU-MEASUREMENT-PLAN`](menu-ia/MENU-MEASUREMENT-PLAN.md) · [`24-live-site-crawl`](phase-01-discovery/24-live-site-crawl-2026-10-01.md) |
| **قرارات حاكمة** | D-149 · D-204 · D-246 · D-259 · D-194 · PRIV-007 · CF-M-219 · PO-019 · PO-036 · PO-043 |

## 1. القواعد
- **لا Cookie غير ضرورية قبل موافقة الزائر.** GA4 والـPixels لا تُحمّل قبل الموافقة.
- **لا PII في أي أداة تحليلات:** لا اسم، ولا هاتف، ولا بريد، ولا رقم طلب، ولا نص يكتبه الزائر (بحث، رسالة).
- **لا تتبع إعلاني بلا موافقتك** (PRIV-007)، **ولا قبل سياسة الخصوصية** (PO-019).
- **حدث واحد لكل فعل.** لا اسمان لنفس الفعل، ولا حدث يكرره حدث تلقائي من GA4.

## 2. اليوم — ما يضعه الموقع الجديد في المتصفح
> الموقع الجديد **غير منشور بعد**. هذا ما يفعله الكود عند تشغيله (محليًا، ثم على Staging).
> **لا Analytics ولا Pixels ولا أي سكربت خارجي:** الـCSP الحالي يسمح بـ`'self'` فقط.

| الاسم | Provider | Purpose | Category | Data collected | Trigger | Consent requirement | Retention | Owner / account | Status |
|---|---|---|---|---|---|---|---|---|---|
| `shelter-coffee-session` (Cookie) | SHELTER (Laravel) | جلسة الزائر: حماية النماذج (CSRF)، رسائل أخطاء النموذج، ملفات مسودة التوظيف، دخول الـOwner | Essential | معرّف عشوائي مشفّر. في الخادم (جدول `sessions`): IP والمتصفح بحكم مشغّل الجلسات | **اليوم: كل صفحة** (المسارات العامة تعمل داخل مجموعة `web` الافتراضية — قراءة كود، لم يُختبر بالتشغيل). **المواصفة:** النماذج واللوحة فقط — انظر §8 | لا تلزم (ضرورية). التأكيد القانوني في PO-019 | 30 دقيقة خمول (`SESSION_LIFETIME=30`). `HttpOnly` · `SameSite=Lax` · `Secure` على Staging/Production | SHELTER | live (في الكود) |
| `XSRF-TOKEN` (Cookie) | SHELTER (Laravel) | حماية الطلبات من التزوير (CSRF) | Essential | رمز مشفّر، بلا هوية | مع كل استجابة `web` (نفس ملاحظة الجلسة) | لا تلزم | نفس مدة الجلسة | SHELTER | live (في الكود) |
| `shelter.menu.branch` (localStorage — ليست Cookie) | SHELTER | تذكّر الفرع الذي اختاره الزائر في المنيو | Essential (تفضيل طلبه الزائر) | `drive` أو `house` أو `all` فقط | ضغط الزائر على زر الفرع | لا تلزم. التأكيد في PO-019 | حتى يمسحها الزائر | SHELTER | live |
| أحداث القياس (`dataLayer`) | SHELTER (`track.ts`) | قياس الأفعال المهمة | Analytics | اسم الحدث ومعاملات ثابتة، بلا PII | **لا شيء يُرسل:** تُدفع فقط إن وُجد `dataLayer`، ولا GTM مثبت | Analytics (عند تشغيل GTM) | — | — | hooks ready (غير فعّالة) |
| مصادقة الـStaging (HTTP Basic auth) | SHELTER | قفل نسخة الفحص | Essential | المتصفح يحفظ اسم المستخدم مؤقتًا (ليست Cookie) | Staging فقط | لا | جلسة المتصفح | SHELTER | جاهز (يُفعّل على Staging) |

**النماذج (التوظيف · الشراكات · الآراء):** تستخدم الجلسة فقط. موافقة معالجة البيانات تُحفظ **في الخادم** (النص + النسخة + الوقت)، وليست Cookie. لا يُرسل شيء من محتواها إلى أي أداة تحليلات (D-246، D-259).

## 3. المخطط — ما سيُضاف لاحقًا (بالموافقة فقط)
| الاسم | Provider | Purpose | Category | Data collected | Trigger | Consent requirement | Retention | Owner / account | Status |
|---|---|---|---|---|---|---|---|---|---|
| `shelter_consent` | SHELTER | حفظ اختيار الزائر في البانر | Essential | الفئات المقبولة + نسخة السياسة + التاريخ. **بلا معرّف** | اختيار الزائر | لا (تحفظ الاختيار نفسه) | `PENDING LEGAL REVIEW` (PO-019) | SHELTER | planned |
| `__cf_bm` · `cf_clearance` | Cloudflare | حماية من الروبوتات والتحديات الأمنية | Essential | معرّف أمني مؤقت | فقط عند تفعيل حماية الروبوتات أو ظهور تحدٍّ | لا | `__cf_bm`: 30 دقيقة. `cf_clearance`: حسب إعداد Challenge Passage | حساب Cloudflare للمنشأة | planned — يُتحقق بعد الربط |
| GTM (`gtm.js`) | Google | تحميل الوسوم المعتمدة | تابع للوسم الذي يحمّله | لا يضع Cookies بنفسه (يُتحقق عند الإعداد) | بعد موافقة Analytics | Analytics | — | Container ID `PENDING OWNER INPUT` (PO-012) | planned |
| `_ga` | Google Analytics 4 | تمييز المتصفح (معرّف عشوائي) | Analytics | معرّف عشوائي + أوقات | وسم GA4 بعد الموافقة | **نعم** (`analytics_storage`) | سنتان افتراضيًا (قابل للتعديل). احتفاظ التقارير في GA4: اقتراح 14 شهرًا | Property الحالي خلف `GT-NNZXZLP5` — يُؤكد (PO-012) | planned |
| `_ga_<ID>` | Google Analytics 4 | حالة الجلسة | Analytics | رقم الجلسة وتوقيتها | وسم GA4 بعد الموافقة | **نعم** | سنتان افتراضيًا | نفس الـProperty | planned |
| RUM (`/api/rum`) | SHELTER | سرعة الصفحات عند الزوار | `PENDING` (G14-CF-02: إفصاح أم موافقة؟) | أرقام مجمعة: LCP · INP · CLS، بلا معرّف ولا IP ولا Cookie | تحميل الصفحة | حسب قرار PO-019 | تُجمع يوميًا | SHELTER | planned (PHASE 6) |
| أخطاء المتصفح (`/api/client-errors`) | SHELTER | اكتشاف أعطال الواجهة | Essential (تشغيل) | رسالة مقصوصة ومنظفة، بلا PII ولا Cookie | خطأ JavaScript | لا | حسب سياسة السجلات | SHELTER | planned (PHASE 6) |
| `_gcl_au` · `_gcl_aw` | Google Ads | ربط نقرة الإعلان بالتحويل | Marketing | معرّف نقرة الإعلان | وسم Ads بعد الموافقة | **نعم** (`ad_storage` · `ad_user_data`) | 90 يومًا | حساب Ads — الموقع القديم فيه `AW-454452815` (PO-012) | FUTURE — بموافقتك فقط |
| `_fbp` · `_fbc` | Meta Pixel | قياس إعلانات Meta | Marketing | معرّف متصفح + معرّف نقرة الإعلان | وسم Pixel بعد الموافقة | **نعم** | 90 يومًا | الـPixel القائم `2425046304590717` (PO-012) | OPTIONAL — بحاجة حملة |
| Meta Conversions API | Meta (من الخادم) | قياس من الخادم مع منع التكرار | Marketing | اسم الحدث + معرّفات مشفّرة (Hashed) بأقل حد | حدث من الخادم بعد موافقة Marketing | **نعم** | حسب Meta | حساب Meta للمنشأة | FUTURE |
| `_scid` | Snap Pixel | قياس إعلانات Snapchat | Marketing | معرّف متصفح | وسم Snap بعد الموافقة | **نعم** | to confirm at setup | حساب Snap — `PENDING OWNER INPUT` | OPTIONAL |
| خريطة Google مضمّنة | Google | عرض الخريطة | Marketing (طرف ثالث) | Cookies من Google داخل الإطار | **اضغط لعرض الخريطة** فقط | **نعم** | حسب Google | — | غير مخطط (الروابط تكفي) |

## 4. الموقع القديم (للعلم عند الانتقال)
ما يحمّله WordPress اليوم على **كل الصفحات** (جرد 2026-10-01). **كلها تختفي يوم الانتقال** إلا ما تقرر إعادته عبر GTM خلف الموافقة:

| الأداة | المعرّف | القرار المطلوب |
|---|---|---|
| Google tag عبر Site Kit (GA4) | `GT-NNZXZLP5` | إعادة استخدام نفس الـProperty في GA4 الجديد (PO-012) |
| Google Ads | `AW-454452815` | هل توجد حملة نشطة؟ (PO-012) |
| Meta Pixel | `2425046304590717` | هل توجد حملة أو جمهور يعتمد عليه؟ (PO-012) |
| HubSpot (ذكره Semrush سابقًا — [`GTM-TAG-REGISTER`](google/GTM-TAG-REGISTER.md)) | Portal `148229555` | لا يُنقل (الموقع الجديد بلا HubSpot — D-228، D-254). يُؤكد في التدقيق |

## 5. فئات الموافقة وGoogle Consent Mode v2
| الفئة | تشمل | الافتراضي | إشارات Consent Mode |
|---|---|---|---|
| **Essential** | الجلسة · `XSRF-TOKEN` · `shelter_consent` · تفضيل الفرع · أمان Cloudflare | دائمًا (لا تُطفأ) | — |
| **Analytics** | GA4 | `denied` | `analytics_storage` |
| **Marketing** | Google Ads · Meta Pixel/CAPI · Snap Pixel | `denied` | `ad_storage` · `ad_user_data` · `ad_personalization` |

- **البانر يظهر فقط عند وجود أول وسم غير ضروري.** لا بانر بلا حاجة.
- «رفض» و«قبول» بنفس الوزن. رابط «تفضيلات الكوكيز» في الـFooter يعيد فتح الاختيار.
- **الوضع الأساسي:** لا شيء يُحمّل من Google قبل الموافقة. الوضع المتقدم (إشارات بلا Cookies قبل الموافقة) قرار ضمن PO-019.

---

## 6. قاموس أحداث GA4 الموحد
### 6.1 المبدأ
- **قائمتك تحدد ماذا نقيس. الاسم التقني الدقيق قرار تقني** (M38، وسابقة CF-M-219).
- **اسم مبني ومعتمد يبقى** (لا نكسر ما اختُبر). **اسمك الجديد يُعتمد** حيث لا شيء مبني، ويجعل القاموس أكثر اتساقًا.
- **حدث واحد لكل فعل.** إعدادات GA4 التلقائية التي تكرر أحداثنا تُطفأ: Outbound clicks (يكرر الاتجاهات وواتساب) · Site search (يلتقط نص البحث) · Form interactions (يكرر أحداث النماذج — G11-TF-04). Page views تبقى.
- **المعاملات من قوائم ثابتة فقط.** لا نص يكتبه الزائر.

### 6.2 مطابقة قائمتك مع الأسماء المعتمدة
| الاسم في قائمتك | الاسم الموحد | المصدر | ملاحظة |
|---|---|---|---|
| `menu_view` · `menu_search` · `menu_category_click` · `product_view` · `branch_filter_change` · `zero_result_search` | **نفسها** | D-149 | — |
| `directions_click` · `whatsapp_click` · `campaign_click` · `site_search` | **نفسها** | D-204 | — |
| `franchise_form_start` · `franchise_form_submit` | **نفسها** | D-259 | — |
| `branch_view` · `event_view` | **نفسها** | كانت مرشحة (CF-M-103، PO-043) | قائمتك تحسم إدراجهما |
| `call_click` | **`phone_click`** | D-204 · CF-M-219 (RESOLVED) | ⚠️ **CONFLICT:** `phone_click` معتمد ومبني ومختبر. «اتصال» يبقى الاسم الظاهر لك في التقارير |
| `language_change` | **`language_switch`** | D-204 | ⚠️ **CONFLICT:** `language_switch` معتمد ومبني |
| `career_form_start` · `career_form_submit` | **`career_form_start` · `career_form_submit`** | قائمتك (الأحدث) | ⚠️ **CONFLICT مع D-246** (`application_started` · `application_submitted`). **التوصية: اسمك.** لا شيء مبني، ويطابق نمط `franchise_form_*`، فيُغلق CF-M-136. `application_error` يصبح `career_form_error` |
| `contact_submit` | **`contact_submit`** | قائمتك | ⚠️ **CONFLICT مع** [`INQUIRIES`](platform/INQUIRIES.md) (`inquiry_submitted` — اقتراح Claude، ليس قرارك). النموذج لم يُبنَ بعد |

### 6.3 القاموس النهائي
**«مبني اليوم» = الحدث موجود في الكود ويُدفع إلى `dataLayer`. لا شيء يصل إلى GA4 قبل تثبيت GTM.**
**Key event = اقتراح فقط. القرار لك (PO-036).**

| Event | Trigger | Parameters (بلا PII ولا نص حر) | Key event (اقتراح) | مبني اليوم؟ |
|---|---|---|---|---|
| `menu_view` | فتح صفحة المنيو | `language` (لاحقًا: `branch_context` · `market`) | ❌ (مؤشر اهتمام — D-149) | ✅ |
| `menu_search` | انتهاء كتابة بحث في المنيو (مرة لكل بحث، لا لكل حرف) | `results_count` · `search_input_language` · `branch_context` · `menu_language` — **بلا نص البحث** | ❌ | ✅ |
| `zero_result_search` | بحث بلا نتائج | نفس معاملات البحث + `search_scope` (`menu` · `site`) | ❌ | ✅ للمنيو · ❌ للبحث العام |
| `menu_category_click` | ضغط فئة من الشريط (ليس عند التمرير) | `category_id` · `subcategory_id` · `nav_source` · `branch_context` · `menu_language` | ❌ | ❌ |
| `product_view` | فتح تفاصيل صنف (ليس عند ظهور البطاقة) | `menu_item_id` (`PRD-…`) · `item_name` (من Master Data، ليس نص زائر) · `category_id` · `detail_source` · `branch_context` · `menu_language` | ❌ | ❌ |
| `branch_view` | فتح صفحة فرع | `branch_id` (`drive` · `house`) · `placement` · `language` | ❌ | ✅ |
| `branch_filter_change` | تغيير الفرع **من الزائر** في المنيو (ليس عند التحميل) | `branch_from` · `branch_to` · `menu_language` | ❌ | ❌ |
| `directions_click` | ضغط «الاتجاهات» | `branch_id` · `placement` · `language` | ✅ | ✅ |
| `phone_click` | ضغط رقم هاتف | `branch_id` (إن وُجد) · `phone_purpose` (`general` · `complaints_feedback_franchise` · `catering_b2b_events`) · `placement` · `language` — **بلا الرقم** | ✅ | ✅ |
| `whatsapp_click` | ضغط واتساب | `branch_id` (إن وُجد) · `placement` · `language` — **بلا نص الرسالة** | ✅ | ✅ |
| `contact_submit` | نجاح إرسال نموذج التواصل **بعد رد الخادم الناجح** | `inquiry_type` (قائمة ثابتة) · `language` | ✅ | ❌ (النموذج PHASE 4) |
| `career_form_start` | أول تفاعل مع نموذج التوظيف | `language` | ❌ | ❌ |
| `career_form_submit` | نجاح إرسال طلب التوظيف بعد رد الخادم | `language` — **بلا رقم الطلب** | ❌ (اقتراح: يُقاس دون أن يُحسب تحويلًا تجاريًا) | ❌ |
| `franchise_form_start` | أول تفاعل مع نموذج الشراكة | `language` | ❌ | ❌ |
| `franchise_form_submit` | نجاح إرسال طلب الشراكة بعد رد الخادم | `language` — **بلا رقم الطلب ولا الاستثمار** | ✅ | ❌ |
| `event_view` | فتح صفحة فعالية | `event_id` (الـslug) · `language` | ❌ | ❌ |
| `campaign_click` | ضغط زر حملة | `campaign_id` · `placement` · `language` | ✅ | ❌ |
| `language_switch` | تبديل اللغة | `from` · `to` | ❌ | ✅ |
| `site_search` | بحث في صفحة البحث العام | `results_count` · `search_input_language` · `language` — **بلا نص البحث** | ❌ | ❌ |

**معتمدة سابقًا ولم ترد في قائمتك — تبقى** (السكوت ليس إلغاءً):
`campaign_view` · `article_view` (D-204) · `careers_page_view` · `career_form_error` (كان `application_error` — D-246) · `franchise_page_view` · `franchise_cta_click` · `franchise_form_step` · `franchise_form_error` · `franchise_faq_open` (D-259).

**مرشحة بانتظار قرارك (PO-043):** `social_click` · `announcement_view` · `announcement_click` · `event_cta_click` · `seasonal_experience_view`.

### 6.4 حماية نص البحث (مهم)
- صفحة البحث العام تحمل النص في الرابط: `/ar/search/?q=…`. GA4 يرسل الرابط الكامل مع كل `page_view`.
- **الحل في GTM:** حذف `q` من `page_location` قبل الإرسال، وإطفاء Site search في Enhanced Measurement.
- الكلمات التي لم تجد نتائج تظهر **في اللوحة فقط**، منظفة ومجمعة، بعد قرار PO-019.

## 7. التحقق قبل أي نشر
| الفحص | الأداة | المتوقع |
|---|---|---|
| لا طلب لنطاقات Google أو Meta قبل الموافقة | Playwright + سجل الشبكة | 0 طلبات |
| بعد «رفض» وتصفح 3 صفحات | Playwright | لا Cookie غير Essential |
| كل حدث يصل مرة واحدة بمعاملاته | GTM Preview + GA4 DebugView | لا تكرار |
| لا PII في أي حدث أو رابط مرسل | فحص آلي لحمولات الأحداث (أرقام طويلة، بريد، `q=`) | 0 |
| Staging لا يرسل لبيانات Production | إعدادات منفصلة | معرّفات مختلفة |

## 8. ملاحظات للسجلات (لا حل صامت)
| # | الملاحظة | الأثر | الاقتراح |
|---|---|---|---|
| 1 | **الكود يضع Cookie الجلسة و`XSRF-TOKEN` على كل الصفحات العامة** (المسارات العامة في مجموعة `web` الافتراضية). المواصفات تقول: الصفحات العامة بلا Cookies ([`PRIVACY-CENTER`](platform/PRIVACY-CENTER.md) §2، [`SECURITY-CENTER`](platform/SECURITY-CENTER.md)، [`CACHE-CDN`](platform/CACHE-CDN.md)) | Cache الحافة لا يعمل للصفحات العامة، وسطر في جدول `sessions` لكل زائر | إصلاح تقني: الصفحات العامة بلا جلسة، والجلسة للنماذج واللوحة فقط، مع اختبار PRV-T01 وCC-T3. **قراءة كود، لم يُختبر بالتشغيل** |
| 2 | اسم Cookie الجلسة الافتراضي `shelter-coffee-session`. المواصفة (SECURITY-CENTER) تطلب البادئة `__Host-` | بادئة أمان ناقصة | ضبط `SESSION_COOKIE` على السيرفر (تقني) |
| 3 | [`GA4-MEASUREMENT-PLAN`](google/GA4-MEASUREMENT-PLAN.md) (`site_search`) و[`MENU-MEASUREMENT-PLAN`](menu-ia/MENU-MEASUREMENT-PLAN.md) (`menu_search`) يسمحان بـ`search_term` منظّف. الكود وهذه الوثيقة: بلا نص | تعارض وثائق | `DOC FIX`: لا نص بحث إلى GA4 |
| 4 | `call_click` → `phone_click` · `language_change` → `language_switch` | اسمان لنفس الفعل | الاسم المعتمد المبني. يُسجّل في CONFLICT-REGISTER |
| 5 | `application_started` / `application_submitted` / `application_error` (D-246) → `career_form_*` | تغيير اسم معتمد | يُسجّل كـUPDATE على D-246، والقديم `SUPERSEDED` |
| 6 | `inquiry_submitted` (INQUIRIES) → `contact_submit` | تعارض وثيقة | `DOC FIX` في INQUIRIES |
| 7 | `branch_view` · `event_view` من «مرشحة» إلى «معتمدة» | يحسم جزءًا من CF-M-103 وPO-043 وANL-026 | يُسجّل إذا كانت القائمة رسالة صريحة منك |
| 8 | [`GTM-TAG-REGISTER`](google/GTM-TAG-REGISTER.md) يقول «GTM / GA4 / Pixel غير معروف»، والجرد المباشر وجدها (§4) | وثيقة قديمة | `DOC FIX` |
| 9 | معاملات المنيو المبنية (`menu_language`) تختلف عن مسودة الخطة (`language`) | أسماء معاملات غير موحدة | توحيدها في القاموس النهائي (PO-043) |
