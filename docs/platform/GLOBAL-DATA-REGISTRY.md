# GLOBAL DATA REGISTRY — خريطة البيانات العامة (جزء من Master Data Hub)

| البند | القيمة |
|---|---|
| **الغرض** | اسم واحد لكل بيانات تتكرر في الموقع (العلامة، التواصل، الفروع، السوشال، الموقع العام). **ليس نظامًا ثانيًا:** هو جزء "العلامة والتواصل والإعدادات العامة" من [`MASTER-DATA-HUB`](../MASTER-DATA-HUB.md). هذه الوثيقة **خريطة فقط** |
| **الحالة** | `SPEC — READY FOR BUILD` · التنفيذ: `NOT STARTED` |
| **مرحلة البناء (M36 §6)** | **PHASE 1** (Master Data Hub · global configuration · Country/City/Branch) · شاشة الـDashboard في **PHASE 3** (Master Data) |
| **الأولوية** | P0 (OPS-010) |
| **المتطلبات** | OPS-011 · OPS-012 · OPS-002 · CMS-012 · CMS-030 · CMS-032 · CONTENT-007 · SCHEMA-005 · CONTACT-021 · G14-CF-11 · M32 §02 §47 §48 · M33 · M36 §3 §9 |
| **المراجع الملزمة** | [`PLATFORM-ARCHITECTURE`](../architecture/PLATFORM-ARCHITECTURE.md) §3.2 · [`FINAL-ARCHITECTURE-REVIEW`](../FINAL-ARCHITECTURE-REVIEW.md) §5 §10 · [`FACT-REGISTRY`](FACT-REGISTRY.md) · [`CHANGE-IMPACT`](CHANGE-IMPACT.md) |
| **اختبار القبول (OPS-003)** | اتساق البيانات + تحكم الـOwner: تعديل واحد في مكان واحد يحدّث كل الأماكن |

## 1. القرار: دمج لا تكرار
- M32 §02 طلب "Global Data Registry"، وM33 طلب "Master Data Hub". **النظام واحد** (FINAL-ARCHITECTURE-REVIEW §5، G14-CF-11، M36 §3).
- القواعد والمزامنة والتعارض مع Google ونموذج الأمان: **في `MASTER-DATA-HUB` فقط**. لا تُكرر هنا.
- **لا جدول `global_values`.** اقتراح `GAP-CLOSURE-PLAN` §D **مستبدل** بجداول PLATFORM-ARCHITECTURE §3.2: كيانا Brand وGlobal Website = مجموعتا `brand.*` و`website.*` في `settings`، **وكل قيمة مربوطة بـ`facts`**.

## 2. الخريطة: نوع البيانات ← الجدول المالك ← الوثيقة
| نوع البيانات العامة (M32 §02) | الجدول المالك (اسم ملزم) | التحقق | التفاصيل في |
|---|---|---|---|
| اسم العلامة EN/AR | `settings` (`brand.name_en` · `brand.name_ar`) | `facts` | FACT-REGISTRY |
| سنة التأسيس | `settings` (`brand.founded_year`). **"عدد السنوات" يُحسب** ولا يُخزن (CONTENT-007) | `facts` | FACT-REGISTRY |
| الذكرى السنوية | `settings` (`brand.anniversary`) | `facts` | GLOBAL-CONTENT-CALENDAR |
| الجملة التعريفية والأوصاف الرسمية AR/EN | `settings` (`brand.tagline_*` · `brand.description_*`) — PENDING OWNER INPUT (PO-016) | `facts` | FACT-REGISTRY |
| الشعار | `media` (مرجعه في `settings`: `brand.logo_media_id`) — PENDING OWNER INPUT (PO-002) | اعتماد الوسيط | [`MEDIA-RIGHTS`](MEDIA-RIGHTS.md) |
| البريد الرسمي | `settings` (`website.email`) — PENDING VERIFICATION (PO-026) | `facts` | — |
| السوق: الدولة، اللغات، العملة، المنطقة الزمنية، صيغ الهاتف والتاريخ | `markets` (`JO`) | إعداد تقني | INTERNATIONALIZATION |
| الدولة ← المدينة ← الفرع | `countries` · `cities` · `branches` | `facts` لكل حقل | MASTER-DATA-HUB §2 §8 |
| اسم الفرع، النوع، الحالة، الـSlug | `branches` (`BR-DRIVE` · `BR-HOUSE`؛ الـSlug لا يتغير بعد النشر — D-053) | `facts` | MASTER-DATA-HUB §8 |
| العنوان AR/EN · الإحداثيات · رابط الخرائط | `branches` — PENDING OWNER INPUT (PO-010) | `facts` | MASTER-DATA-HUB §8 |
| معرّف الفرع في GBP والأنظمة الخارجية | `external_references` | — | MASTER-DATA-HUB §6 §8 |
| الساعات العادية | `branch_hours` | `facts` | MASTER-DATA-HUB §7 |
| الساعات الخاصة والإغلاق المؤقت والطارئ | `hours_exceptions` | `facts` | MASTER-DATA-HUB §7 · GLOBAL-CONTENT-CALENDAR |
| أرقام التواصل حسب النية: عام · واتساب · شكاوى واقتراحات · فرنشايز · كيترنج/B2B | `contact_points` (`scope` · `kind` · `is_public`) | `facts` | MASTER-DATA-HUB §2 · D-057 · D-059 |
| السوشال | `social_links` | `facts` (التحقق CONTACT-026) | MASTER-DATA-HUB §2 |
| حقوق النشر في الـFooter | **محسوبة:** © + السنة الحالية + `brand.name_en` | — | — |
| بيانات Organization وLocalBusiness للـSchema | **مشتقة** بلا إدخال مستقل | — | MASTER-DATA-HUB §9 |
| الروابط العامة والقانونية | `settings` (`website.*`) تشير إلى `pages` (الخصوصية، الشروط) — النص PENDING OWNER INPUT (PO-019) | محتوى منشور | [`SITE-INVENTORY`](SITE-INVENTORY.md) |
| قيمة خاصة بقناة واحدة (مثل وصف مختلف على GBP) | `channel_overrides` **بموافقة وسبب**، والأصل يبقى في الـHub (M33 §24) | — | MASTER-DATA-HUB |
| الـCTA العام (M32 §04) | `settings` (`website.cta.*`) | — | CHANGE-IMPACT |
| شريط الإعلان · إشعار الطوارئ | `experiences` (**ليس `settings`** — تعارض G13 محسوم) | — | [`DYNAMIC-EXPERIENCE-ENGINE`](../DYNAMIC-EXPERIENCE-ENGINE.md) |
| Safe Mode · الصيانة | `feature_flags` | — | SAFE-MODE |

## 3. قواعد الاستخدام (ملخص)
- **القراءة بالمفتاح فقط** عبر `app/Services/MasterData`. ممنوع أي هاتف أو ساعة أو عنوان مكتوب في Blade أو JSON-LD أو `lang/*` (OPS-012).
- **النص الغني يستخدم Tokens:** `{{contact.main}}` · `{{brand.founded_year}}`، وتُحل عند العرض. المراجع تُستخرج عند الحفظ إلى `page_sections.refs` لتغذية CHANGE-IMPACT.
- **قيمة بلا اعتماد = NULL مخفي** + Fact غير قابل للنشر ← إشارة (PLATFORM-ARCHITECTURE §4.4).
- **كل تعديل** يمر بـ CHANGE-IMPACT ← [`PUBLISH-GUARD`](PUBLISH-GUARD.md) ← نسخة ← Audit ← إبطال بالوسوم `global:{group}` ([`CACHE-CDN`](CACHE-CDN.md) §3).

## 4. V1 مقابل لاحقًا
| V1 | لاحقًا |
|---|---|
| كل الصفوف أعلاه لسوق واحد (`JO`) | سوق ثانٍ بإضافة صف في `markets` بلا إعادة بناء (M35 §31–§33) |
| التواصل لكل العلامة ولكل فرع (`scope`) | نقاط تواصل لكل سوق |

## 5. مكانه في الـDashboard
- **النظام ← البيانات العامة** (FINAL-ARCHITECTURE-REVIEW §10). تبويبات: العلامة · التواصل · الفروع والساعات · السوشال · الموقع العام · الاتساق (حالة المزامنة).
- لكل قيمة: الحالة من `facts` · "مستخدمة في N أماكن" (من CHANGE-IMPACT) · تعديل.
- **الفروع والساعات مهمة يومية** (DASH-028): اختصار من الرئيسية ومن شريط الموبايل (OPS-049).
- Owner فقط، والتحقق في الخادم (OPS-050). بلا كود (OPS-051).

## 6. اختبارات القبول
| # | الاختبار | المتوقع |
|---|---|---|
| GD-T1 | فحص CI للنصوص الثابتة: إضافة رقم بصيغة `07XXXXXXXX` أو `+9627…` في قالب Blade (فرع اختبار) | فشل البناء (OPS-012) |
| GD-T2 | تعديل `contact_points` (العام) ونشره | كل الأسطح في قائمة الأثر تعرض القيمة الجديدة، والقديمة غير موجودة في أي HTML أو JSON-LD |
| GD-T3 | ساعة مجمّدة على 2027-01-01 | سنة حقوق النشر وعدد السنوات يتغيران بلا أي تعديل |
| GD-T4 | عنوان فرع حقيقته `PENDING VERIFICATION` | صفحة الفرع بلا عنصر العنوان، وJSON-LD بلا `address` |
| GD-T5 | طلب كتابة على `/dashboard/system/master-data/*` بلا جلسة أو بمستخدم غير Owner | إعادة توجيه / 403 |
| GD-T6 | الشاشة على مصفوفة `tooling/viewports.mjs` بالعربي والإنجليزي | بلا Overflow أو قص، وaxe بلا Serious/Critical |

## 7. ما لا يُفعل
- لا شاشة ولا جدول "سجل عام" منفصل عن الـHub.
- لا نسخ للقيم داخل الصفحات أو القوالب.
- لا قيم في هذه الوثيقة: كل شيء بالمفاتيح. القيم في جداولها المالكة، وحالة اعتمادها في `facts` وقرارات DECISION-LOG.
- لا كتابة على Google بلا موافقة، والاسم والعنوان يدويان (MASTER-DATA-HUB §5 §10).
