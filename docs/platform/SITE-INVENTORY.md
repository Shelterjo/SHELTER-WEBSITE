# SITE INVENTORY — جرد كل مسارات الموقع والـDashboard

| البند | القيمة |
|---|---|
| **الغرض** | ملف واحد لكل Route: الغرض، اللغة، مصدر البيانات، من أين يُدار، مرحلة البناء، الفهرسة، الـSchema، القياس، الحالة. **لا صفحة خارج هذا الجرد** |
| **الحالة** | `SPEC — READY FOR BUILD` · كل المسارات: `NOT STARTED` (الموجود Wireframes فقط) |
| **مرحلة البناء** | لكل مسار مرحلته: **PH1…PH7 = M36 §6 PHASE 1…7** |
| **المتطلبات** | M35 §34 · M35 §17 §18 §19 · IA-005 · IA-006 · IA-008 · IA-015 · IA-016 · D-015 · D-031 · D-052 · D-053 · D-054 · D-069 · D-182 · SEO-019 · SEO-021 · SEO-023 · SEO-024 · SEO-028 · SEO-029 · SEO-032 · CAREERS-006 · CAREERS-008 · CAREERS-042 · DX-002 · DX-009 · OPS-032 · OPS-044 · G14-CF-06 · G14-TF-16 · WEB-017 |
| **المراجع** | [`10-url-architecture-draft`](../phase-01-discovery/10-url-architecture-draft.md) · [`SHELTER-MENU-IA-SPEC`](../menu-ia/SHELTER-MENU-IA-SPEC.md) §1 · [`CAREERS-REQUIREMENTS`](../CAREERS-REQUIREMENTS.md) §2 · [`franchise/02`](../franchise/02-PAGE-IA-AND-CONTENT.md) · [`franchise/05`](../franchise/05-SEO-ANALYTICS-PERFORMANCE.md) · [`FINAL-ARCHITECTURE-REVIEW`](../FINAL-ARCHITECTURE-REVIEW.md) §10 · [`CONTENT-SOURCE-OF-TRUTH`](CONTENT-SOURCE-OF-TRUTH.md) |
| **قرارات مفتوحة** | **PO-004** (الـSitemap والتنقل) · **PO-005** (تجميد الروابط) · PO-030 · PO-032 · PO-034 · PO-035 · G13-PO-01 · G14-PO-01 · G14-PO-02 · PO-028 · PO-014 |

**المفاتيح:**
- `*` = **Slug تقني مقترح**. الصفحة نفسها مطلوبة من الـOwner، والـSlug يُحسم في PO-004/PO-005.
- 🔒 = موجودة في البنية، **غير منشورة** حتى البند المذكور. لا تظهر في التنقل أو الـSitemap أو البحث.
- **الفهرسة:** ✅ قابلة للفهرسة عند النشر · ❌ `noindex` وخارج الـSitemap.
- **Schema:** الأنواع المسموحة فقط: Organization · CafeOrCoffeeShop · Menu · Event · Article · BreadcrumbList · FAQPage · WebPage (M32 §34، MASTER-DATA-HUB §9، franchise/05).
- **القياس:** أسماء من خطط القياس الحالية، والقاموس النهائي في PO-043. **لا PII أبدًا.**
- `{coffee}` = اسم مسار Knowledge Hub، معلّق (DB-09 / URL-03).

## A. طبقة العلامة (Brand layer)
| # | الصفحة · الغرض | AR | EN | النوع | البيانات ← يُدار من | المرحلة | فهرسة | Schema | القياس |
|---|---|---|---|---|---|---|---|---|---|
| SI-B01 | البوابة: بوابة العلامة، بلا تحويل تلقائي (D-052، WEB-017) | `/` | (نفسه) | Gateway | `settings` (`brand.*`) + `pages` ← المحتوى ← الصفحات | PH2 | ✅ x-default | Organization | page_view |
| SI-B02 | الرئيسية | `/ar/` | `/en/` | Home | `pages` · `page_sections` + `experiences` + `branches` · `branch_hours` ← المحتوى ← الصفحات · التجارب | PH2 | ✅ | Organization | page_view · announcement_view/click · campaign_view/click |
| SI-B03 | من نحن (القصة PENDING OWNER INPUT — PO-017) | `/ar/about/` | `/en/about/` | Brand | `pages` + `settings` (`brand.*`) ← المحتوى ← الصفحات | PH2 | ✅ | WebPage · Organization | page_view |
| SI-B04 | التواصل حسب النية (D-059) + نموذج الاستفسار INQ (M35 §9) | `/ar/contact/` | `/en/contact/` | Contact + Form | `contact_points` + `pages` · الطلبات `applications`/`inquiries` ← الأعمال ← الاستفسارات | PH2 (الصفحة) · PH4 (النموذج) | ✅ | WebPage · Organization | phone_click · whatsapp_click |
| SI-B05 | الأسئلة الشائعة (الأسئلة PO-034) | `/ar/faq/` | `/en/faq/` | FAQ | `pages` · `page_sections` ← المحتوى ← الصفحات | PH2 | ✅ | FAQPage (المعتمد فقط) | page_view |
| SI-B06 | سياسة الخصوصية (النص PO-019) | `/ar/privacy/` | `/en/privacy/` | Legal | `pages` | PH2 | ✅ | WebPage | — |
| SI-B07 | الشروط، عند الحاجة القانونية (D-015، PO-019) | `/ar/terms/` | `/en/terms/` | Legal | `pages` | PH2 | ✅ | WebPage | — |
| SI-B08 | نتائج البحث العام ([`GLOBAL-SEARCH`](GLOBAL-SEARCH.md)) | `/ar/search/`* | `/en/search/`* | Search | `search_index` | PH2 | ❌ (SEO-023) | — | site_search |
| SI-B09 | Coffee Knowledge Hub (النشر بعد PO-035) | `/ar/{coffee}/` | `/en/{coffee}/` | Hub | `articles` ← المحتوى ← المعرفة | PH3 | ✅ | BreadcrumbList | blog_view |
| SI-B10 | مقال (رابط مسطح بلا الموضوع) | `/ar/{coffee}/{slug}/` | `/en/{coffee}/{slug}/` | Article | `articles` | PH3 | ✅ | Article · BreadcrumbList | article_view |
| SI-B11 | التوظيف: المحتوى + **النموذج العربي الوحيد** (CAREERS-006) | `/ar/careers/` | `/en/careers/` (محتوى، وApply ← النموذج العربي) | Content + Form (JOB) | `pages` + `applications`/`job_applications` ← الأعمال ← التوظيف | PH4 | ✅ | WebPage | careers_page_view · application_started/submitted/error |
| SI-B12 | الفرنشايز والشراكات + نموذج FR · 🔒 حتى PO-030 | `/ar/franchise/` | `/en/franchise/` | Landing + Form (FR) | `pages` + `facts` + `partnership_applications` ← الأعمال ← الشراكات | PH4 | ✅ عند النشر فقط | Organization · WebPage · BreadcrumbList · FAQPage | franchise_page_view · franchise_cta_click · franchise_form_* |
| SI-B13 | الوسائط والصحافة (Media Center + Press Kit) · 🔒 حتى G14-PO-01 | `/ar/media/`* | `/en/media/`* | Media | `media` + `facts` ← المحتوى ← مركز الوسائط | PH4 | ✅ عند النشر | WebPage · BreadcrumbList | — |
| SI-B14 | الجوائز (ضمن Media Center — M35 §35) · 🔒 حتى PO-032 | `/ar/awards/`* | `/en/awards/`* | Awards | `awards` + `media` ← المحتوى ← الجوائز | PH4 | ✅ عند النشر | WebPage | — |
| SI-B15 | SHELTER Family (DX-002) · 🔒 حتى G13-PO-01 (موافقة الموظفين) | `/ar/family/`* | `/en/family/`* | Team | `experiences` (ملفات بموافقة) ← التجارب ← SHELTER Family | PH4 | ✅ عند النشر | WebPage | — |
| SI-B16 | رأي العميل (M32 §15). نقاط الدخول G14-PO-02، وقد يكون قسمًا داخل التواصل أو الفرع | `/ar/feedback/`* | `/en/feedback/`* | Form (VoC) | `feedback` ← الأعمال ← آراء العملاء | PH4 | ❌ | — | — |

**الموظف المثالي:** ليس مسارًا. يظهر في الرئيسية وSHELTER Family وMedia Center حسب اختيار الـOwner (DX-009).

## B. طبقة السوق — الأردن (`jo`)
| # | الصفحة · الغرض | AR | EN | النوع | البيانات ← يُدار من | المرحلة | فهرسة | Schema | القياس |
|---|---|---|---|---|---|---|---|---|---|
| SI-M01 | صفحة السوق · 🔒 محجوزة (URL-07). سلوكها قبل التفعيل في PO-005 | `/ar/jo/` | `/en/jo/` | Market hub | — | — | ❌ | — | — |
| SI-M02 | المنيو: **صفحة واحدة بلا صفحات أصناف** (IA-015، IA-016، SEO-024) | `/ar/jo/menu/` | `/en/jo/menu/` | Menu | جداول المنيو + `branches` · `branch_hours` · `product_branch_overrides` + `experiences` ← المحتوى ← المنيو | PH2 | ✅ · `?branch=` يحمل Canonical نظيفًا | Menu · BreadcrumbList | menu_view · menu_search · zero_result_search · menu_category_click · product_view · branch_filter_change |
| SI-M03 | الفروع | `/ar/jo/locations/` | `/en/jo/locations/` | Locations | `branches` + `branch_hours` + `hours_exceptions` ← النظام ← البيانات العامة ← الفروع والساعات | PH2 | ✅ | BreadcrumbList | branch_view · directions_click |
| SI-M04 | المدينة (إربد) · 🔒 محجوزة (URL-07) | `/ar/jo/locations/irbid/` | `/en/jo/locations/irbid/` | City | — | — | ❌ | — | — |
| SI-M05 | SHELTER COFFEE DRIVE (`BR-DRIVE`، Slug ثابت — D-053) | `/ar/jo/locations/irbid/drive/` | `/en/jo/locations/irbid/drive/` | Branch | `branches` + الساعات + `contact_points` + `external_references` ← النظام ← البيانات العامة | PH2 | ✅ | CafeOrCoffeeShop · BreadcrumbList | branch_view · directions_click · phone_click · whatsapp_click |
| SI-M06 | SHELTER COFFEE HOUSE (`BR-HOUSE`) | `/ar/jo/locations/irbid/house/` | `/en/jo/locations/irbid/house/` | Branch | نفسه | PH2 | ✅ | CafeOrCoffeeShop · BreadcrumbList | نفسه |
| SI-M07 | الفعاليات والحملات | `/ar/jo/events/` | `/en/jo/events/` | Listing | `experiences` ← المحتوى ← الفعاليات | PH3 | ✅ إن وُجد منشور · ❌ إن كانت فارغة | BreadcrumbList | event_view |
| SI-M08 | فعالية أو حملة | `/ar/jo/events/{slug}/` | `/en/jo/events/{slug}/` | Event | `experiences` + `media` | PH3 | ✅ | Event (الصالحة فقط) · BreadcrumbList | event_view · event_cta_click |

## C. حالات النماذج (كلها ❌ `noindex`، وخارج الـSitemap، وبلا بيانات حساسة في الرابط)
| # | الحالة | AR | EN | المرحلة | ملاحظات |
|---|---|---|---|---|---|
| SI-C01 | نجاح طلب التوظيف (عربي فقط — CAREERS-041) | `/ar/careers/submitted/`* | — | PH4 | يعرض `JOB-YYYY-NNNNN` |
| SI-C02 | متابعة طلب التوظيف: رقم الطلب + الهاتف (CAREERS-042) | `/ar/careers/track/`* | — | PH4 | Rate limit وحماية Enumeration (CAREERS-082). الحالة العامة فقط |
| SI-C03 | نجاح طلب الشراكة | `/ar/franchise/submitted/`* | `/en/franchise/submitted/`* | PH4 | `FR-YYYY-NNNNN`، بلا وعود (franchise/03) |
| SI-C04 | نجاح الاستفسار | `/ar/contact/submitted/`* | `/en/contact/submitted/`* | PH4 | `INQ-YYYY-NNNNN` |
| SI-C05 | نجاح رأي العميل | `/ar/feedback/submitted/`* | `/en/feedback/submitted/`* | PH4 | بلا رقم مرجعي (بلا PII) |

## D. صفحات وملفات النظام
| # | المسار | ماذا | المرحلة | ملاحظات |
|---|---|---|---|---|
| SI-S01 | 404 | لغة الصفحة من بادئة المسار، وثنائية على الجذر | PH2 | الحالة 404. روابط: الرئيسية · المنيو · الفروع · البحث (SEO-019، M35 §17). ❌ |
| SI-S02 | 500 | ثابتة، **لا تعتمد على قاعدة البيانات** | PH2 | إعادة المحاولة · الرئيسية · المنيو · التواصل، بلا Stack trace (M35 §18). ❌ |
| SI-S03 | الصيانة | 503 + `Retry-After` من `feature_flags` | PH2 (القالب) | **آخر حل**، بموافقة الـOwner. Safe Mode أولًا (AC-5، M35 §19). ❌ |
| SI-S04 | `/robots.txt` | مولّد | PH2 | Staging: منع + مصادقة (SEO-028) |
| SI-S05 | `/sitemap.xml` (فهرس + لكل لغة) | مولّد | PH2 | Canonical · عام · مفهرس · معتمد فقط (SEO-029) |
| SI-S06 | `/llms.txt` | مولّد من الحقائق المعتمدة | PH2 | KEEP بمحتوى جديد (`02-url-inventory`) |
| SI-S07 | — (Storybook) | مرجع المكونات: Storybook يُبنى كملفات ثابتة (`storybook-static`)، **ليس مسارًا في التطبيق** | PH1 | لا يُنشر على Production (M37، ADR-001) |

## E. تحويلات فقط (ليست صفحات — جدول `redirects`)
| المصدر | الوجهة | متى |
|---|---|---|
| `/menu` | 301 ← `/ar/jo/menu/` | **وقت الإطلاق فقط** بعد شروط D-054 وD-182 وموافقة الـOwner (PH7) |
| روابط الموقع القديم (`02-url-inventory`) | أقرب بديل، **لا تحويل جماعي للرئيسية** | PH7 ([`LEGACY-URL-MIGRATION`](LEGACY-URL-MIGRATION.md)) |
| `/drive` · `/house` (روابط QR قصيرة) | صفحة الفرع | **مقترح فقط** (URL-09، PO-014) |

## F. مسارات الـDashboard (Owner فقط)
**كلها:** `auth` + `owner` + `2fa` من الخادم · `X-Robots-Tag: noindex` · خارج الـSitemap · بلا Cache. **المسارات تقنية مقترحة**، والأقسام من FINAL-ARCHITECTURE-REVIEW §10.

| المجموعة | المسارات | المرحلة |
|---|---|---|
| الدخول | `/dashboard/login` (Passkey + TOTP) | PH1 |
| الرئيسية | `/dashboard/` (Command Center) | PH3 |
| الشريط العلوي | `/dashboard/search` · `/dashboard/notifications` · مفتاح Safe Mode | PH3 · PH6 · PH6 |
| المحتوى | `/dashboard/content/` `pages` · `menu` · `knowledge` · `events` · `awards` · `media` | PH3 (الجوائز ومركز الوسائط الكامل PH4) |
| التجارب | `/dashboard/experiences/` `active` · `campaigns` · `seasons` · `calendar` · `family` | PH3 (SHELTER Family PH4) |
| الأعمال | `/dashboard/business/` `inquiries` · `careers` · `partnerships` · `feedback` · `reputation` | PH4 (السمعة PH5) |
| النمو | `/dashboard/growth/` `analytics` · `search` · `seo` · `opportunities` | PH3 (بيانات Google PH5 · الفرص PH6) |
| الجودة | `/dashboard/quality/` `health` · `performance` · `accessibility` · `security` · `privacy` · `backups` | PH3 (صحة الموقع) · PH6 |
| النظام | `/dashboard/system/` `master-data` (العلامة، التواصل، الفروع والساعات، السوشال، الموقع العام، الاتساق) · `facts` · `integrations` · `releases` · `audit` · `settings` | PH3 (التكاملات PH5 · الإصدارات PH7) |

**الـAPI الداخلي (ليس صفحات):** `/api/forms/{type}` (Idempotency) · `/api/search` (نطاق عام فقط) · `/api/rum` (كتابة فقط) · `/dashboard/api/*` (Owner). **لا API إداري عام.**

## G. قواعد تنطبق على كل مسار (أعمدة M35 §34)
| عمود M35 | أين في هذا الجرد |
|---|---|
| Purpose · Language | عمود الصفحة + AR/EN |
| Data Source · CMS source · Owner editable? | عمود "البيانات ← يُدار من". كل محتوى عام يُدار من الـDashboard، **عدا نص صفحة 500** (ثابت ليعمل بلا قاعدة) |
| SEO status · Indexable? | الفهرسة + الـSchema. وكل صفحة مفهرسة: Canonical لنفسها، وhreflang متبادل **عند نشر اللغتين فقط** (SEO-032). المعاملات (`?branch=` · `?q=`) لا تُفهرس أبدًا (SEO-023) |
| Analytics | عمود القياس. الـDashboard بلا GA4 |
| Authentication? | العام: بلا. النماذج: CSRF + Rate limit + Honeypot. الـDashboard: §F. Staging: مصادقة + `noindex` |
| Current status | كلها `NOT STARTED` |

## H. مقترحات غير معتمدة (لا تُبنى قبل قرار)
| المقترح | المصدر | القرار |
|---|---|---|
| صفحة "بيان إمكانية الوصول" | اقتراح Claude (R11-06) | PO-004 |
| صفحات مواضيع المعرفة `/ar/{coffee}/{topic}/` | مسودة الروابط §3 | PO-035 · PO-005 |
| صفحة Catering / B2B / فعاليات خاصة | **PAGE RESERVED** (IA-008، D-069) | PO-028 — لا مسار قبل المحتوى |
| روابط QR القصيرة لكل فرع | URL-09 | PO-014 |

## I. V1 مقابل لاحقًا
| V1 | لاحقًا |
|---|---|
| كل المسارات في §A–§F لسوق واحد (`jo`) ولغتين | تفعيل صفحة السوق والمدينة عند وجود أكثر من سوق أو مدينة (Progressive Activation — URL-07) |
| | سوق ثانٍ `/ar/{xx}/…` ولغة ثالثة **بقرار الـOwner فقط** (I18N-002) |
| | صفحات أصناف مستقلة: **غير مخططة** (IA-016) |

## J. اختبارات القبول
| # | الاختبار | المتوقع |
|---|---|---|
| SI-T1 | سكربت CI يقارن مسارات GET العامة في `route:list` بهذا الجرد | تطابق تام. أي مسار عام غير موثق = فشل |
| SI-T2 | المرور على كل مسار `/dashboard/*` | كل مسار عليه `auth` + `owner` + `2fa`، وبلا جلسة يعيد توجيهًا أو 403 |
| SI-T3 | توليد `sitemap.xml` | فقط المسارات ✅ والمنشورة. لا `/dashboard` ولا البحث ولا المحجوز ولا المسودات |
| SI-T4 | كل صفحة مفهرسة | Canonical لنفسها، وhreflang متبادل عند وجود اللغتين |
| SI-T5 | 404 و500 | 404 بحالتها وفيها الروابط الأربعة باللغتين. 500 تُعرض **وقاعدة البيانات متوقفة** |
| SI-T6 | المسارات 🔒 قبل اعتمادها | لا تُخدم علنًا (السلوك الافتراضي 404 حتى PO-005)، وغائبة عن التنقل والـSitemap والبحث |
| SI-T7 | كل مسار عام موجود × 20 عرضًا (`tooling/viewports.mjs`) × AR/EN | بلا Overflow أو قص أو تداخل، وaxe بلا Serious/Critical |

## K. ما لا يُفعل
- لا صفحات أصناف مستقلة في V1 (IA-016، SEO-024).
- لا صفحة خارج قائمة D-015 وإضافات M31/M32/M35/M36 **بلا موافقة الـOwner** (IA-005).
- لا تحويل تلقائي حسب الدولة أو اللغة أو الـIP (WEB-017).
- لا تنفيذ لأي تحويل قبل الإطلاق.
- لا بناء للمقترحات في §H قبل قرارها.
