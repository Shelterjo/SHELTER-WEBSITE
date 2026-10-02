# PUBLIC ROUTE MAP — خريطة المسارات العامة كما هي فعلًا

| البند | القيمة |
|---|---|
| **الغرض** | جرد **مُقاس** لكل مسار GET عام في التطبيق: الحالة، الـcanonical، الـH1، الـtitle، الفهرسة، الـsitemap، مصدر التنقل، التحويلات. مرجع للانتقال إلى Cloudways ولمدير التحويلات |
| **الحالة** | `SNAPSHOT — MEASURED 2026-10-02` · ليس قرارًا ولا مواصفة. المواصفة المعتمدة تبقى [`platform/SITE-INVENTORY.md`](platform/SITE-INVENTORY.md) |
| **الكود المقاس** | Commit `976d587`، ثم أُعيد فحص الروابط القديمة والأخطاء الإملائية والصفحات الأساسية بعد Commit `1544454` (مدير التحويلات `LegacyRedirects` + جدول `redirects`): **النتائج نفسها** لأن كل صفوف التحويل `draft` |
| **الأساس المعتمد** | D-031 (P3) · D-052 (ROOT-01 = D) · D-053 (`drive`/`house`) · D-054 + D-182 (`/menu` وقت الإطلاق فقط) · D-067 (لا Geo/IP Redirect) · [`13-root-and-international-seo-plan`](phase-01-discovery/13-root-and-international-seo-plan.md) · [`SEO-MIGRATION-MAP`](google/SEO-MIGRATION-MAP.md) · [`LEGACY-URL-MIGRATION`](platform/LEGACY-URL-MIGRATION.md) · `routes/web.php` |
| **طريقة القياس** | `curl` GET فقط (بلا كتابة):<br>- **Dev** `http://127.0.0.1:8000` (`APP_ENV=local` ← كل شيء `noindex`): الحالة، H1، title، canonical، `lang`/`dir`.<br>- **Production-mode** `http://127.0.0.1:8092` (`APP_ENV=production` · `SHELTER_INDEXING=true` · `APP_DEBUG=false`): الفهرسة، `sitemap.xml`، `robots.txt`، `llms.txt`، الروابط الداخلية.<br>- نفس قاعدة البيانات المحلية (SQLite) للخادمين.<br>- `php artisan route:list --json`. |
| **القرارات المفتوحة المؤثرة** | PO-004 (التنقل والـsitemap) · PO-005 (تجميد الروابط، ROOT-02، hreflang للسوق) · PO-063 (مداخل رأي العميل) · R7-01 (QR) |

> كل ما في هذا الملف **مُقاس** على البناء الحالي. أي صف يختلف عن المواصفة مذكور في §9، ولا يُعدَّل شيء دون قرار مسجّل.

---

## 1. المعمارية المعتمدة (ملخص)
| الطبقة | النمط | المصدر | الحالة في الكود |
|---|---|---|---|
| بوابة العلامة | `/` — صفحة ثنائية اللغة، **بلا تحويل تلقائي**، `x-default` | D-052 · D-067 | ✅ 200، لا تحويل، `x-default` على مجموعة الرئيسية فقط |
| طبقة العلامة | `/{ar\|en}/…` | D-031 | ✅ `routes/web.php` (`locale` = `ar\|en`) |
| طبقة السوق | `/{ar\|en}/{market}/…` (`jo`) | D-031 | ✅ `{market}` = `[a-z]{2}`، فقط الأسواق النشطة (`ResolveMarket`) |
| الفروع | `/{ar\|en}/jo/locations/irbid/{drive\|house}/` | D-053 | ✅ نفس الـslug في اللغتين |
| صفحة السوق والمدينة | `/ar/jo/` · `/ar/jo/locations/irbid/` | URL-07 (محجوزة) | ✅ 404 (لا Route) |
| الشرطة النهائية | كل صفحة عامة تنتهي بـ`/` | `CanonicalTrailingSlash` | ✅ 301 بقفزة واحدة، والـquery يُحفظ |
| `/menu` | 301 → `/ar/jo/menu/` **وقت الإطلاق فقط** | D-054 · D-182 | ✅ غير مفعّل الآن (404) — هذا هو المطلوب |
| hreflang للسوق | الخطة `13` §4 تقترح `ar-JO`/`en-JO` و`x-default` للعربية | ROOT-02 (توصية، ليست قرارًا) | الكود يستخدم `ar`/`en` وبلا `x-default` خارج الرئيسية — بانتظار PO-005 |

---

## 2. مفتاح الأعمدة
| العمود | المعنى |
|---|---|
| **CANONICAL** | الـpath كما يُطبع في `<link rel="canonical">`. يُطبع **مطلقًا** بالـHost المضبوط في `APP_URL` على Production/Staging (`App\Support\CanonicalHost`). تحقق على 8092: `http://127.0.0.1:8092/ar/jo/menu/` إلخ. "self" = الصفحة نفسها |
| **STATUS** | من Dev لكل لغة: `AR / EN`. الاختلاف على Production-mode مذكور صراحة |
| **INDEXABLE** | من 8092: ✅ = لا `<meta name="robots">` ولا `X-Robots-Tag` · ❌ `noindex` |
| **SITEMAP** | موجود في `http://127.0.0.1:8092/sitemap.xml` (17 رابطًا، §6) |
| **NAV** | `H` شريط الهيدر · `D` الـdrawer · `F` الفوتر · `Home` أزرار الرئيسية · `G` البوابة · `404` روابط صفحة الخطأ · `—` لا شيء |
| **LINKED** | روابط داخلية فعلية وُجدت بالزحف على 8092 (header + drawer + footer + main) |
| **REDIRECTS** | `TS-301` = تحويل الشرطة النهائية (`/ar/jo/menu` → `/ar/jo/menu/`). **لا يوجد أي تحويل Legacy فعّال** (§5) |

---

## 3. الجدول الرئيسي — كل مسار GET عام

### 3.1 البوابة والرئيسية
| PAGE | AR PATH | EN PATH | CANONICAL | STATUS | H1 (AR / EN) | TITLE (AR / EN) | INDEXABLE | SITEMAP | NAV | LINKED | REDIRECTS | NOTES |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| بوابة العلامة (SI-B01) | `/` | (نفسه) | `/` self | 200 | `SHELTER COFFEE` | `SHELTER COFFEE · شلتر كوفي` | ✅ | ✅ | — (هيدر مصغّر بلا تنقل) | ❌ لا رابط داخلي إليها (الشعار يذهب إلى `/ar/` أو `/en/`) | لا تحويل (D-052) | `<html lang="ar" dir="rtl">` مع كتل `lang="en"`. hreflang: `ar`→`/ar/` · `en`→`/en/` · `x-default`→`/`. روابطها: `/ar/` `/en/` + المنيو والفروع باللغتين. Schema: Organization + WebSite |
| الرئيسية (SI-B02) | `/ar/` | `/en/` | self | 200 / 200 | `شلتر كوفي` / `SHELTER COFFEE` | `شلتر كوفي — كافيه قهوة مختصة ودرايف ثرو في إربد` / `SHELTER COFFEE — Specialty Coffee & Drive-Thru in Irbid` | ✅ | ✅ | الشعار (كل الصفحات) · G · مسار التنقل (Home) · مبدّل اللغة · 404 | ✅ كل الصفحات | `/ar` → TS-301 → `/ar/` | hreflang + `x-default`→`/` (نفس مجموعة البوابة) |

### 3.2 طبقة السوق (`jo`)
| PAGE | AR PATH | EN PATH | CANONICAL | STATUS | H1 (AR / EN) | TITLE (AR / EN) | INDEXABLE | SITEMAP | NAV | LINKED | REDIRECTS | NOTES |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| المنيو (SI-M02) | `/ar/jo/menu/` | `/en/jo/menu/` | self — نظيف حتى مع `?branch=` و`?utm_*` | 200 / 200 | `المنيو` / `Menu` | `منيو شلتر كوفي في إربد — القهوة والمشروبات والأسعار` / `SHELTER COFFEE Menu in Irbid — Coffee, Drinks & Prices` | ✅ | ✅ | H · D · F · Home · G · 404 | ✅ كل الصفحات + صفحة الفرع (`?branch=drive\|house`) | TS-301 | صفحة واحدة بلا صفحات أصناف. مبدّل اللغة يحفظ `?branch` وhreflang نظيف |
| الفروع (SI-M03) | `/ar/jo/locations/` | `/en/jo/locations/` | self | 200 / 200 | `الفروع` / `Locations` | `فروع شلتر كوفي في إربد — ساعات الدوام` / `SHELTER COFFEE Locations in Irbid — Opening Hours` | ✅ | ✅ | H · D · F · Home · G · 404 | ✅ كل الصفحات + التواصل + مسار التنقل في صفحة الفرع | TS-301 | — |
| SHELTER COFFEE DRIVE (SI-M05) | `/ar/jo/locations/irbid/drive/` | `/en/jo/locations/irbid/drive/` | self | 200 / 200 | `شلتر كوفي درايف` / `SHELTER COFFEE DRIVE` | `شلتر كوفي درايف — درايف ثرو في إربد \| ساعات الدوام` / `SHELTER COFFEE DRIVE — Drive-thru in Irbid \| Opening Hours` | ✅ | ✅ | بطاقات الفروع في Home والفروع والتواصل (ليست في H/F) | ✅ `/ar/` `/ar/jo/locations/` `/ar/contact/` (ونفسها EN) | TS-301 | Slug ثابت (D-053). مسار التنقل: الرئيسية › الفروع › الفرع (بلا مستوى المدينة). Schema: CafeOrCoffeeShop + BreadcrumbList |
| SHELTER COFFEE HOUSE (SI-M06) | `/ar/jo/locations/irbid/house/` | `/en/jo/locations/irbid/house/` | self | 200 / 200 | `شلتر كوفي هاوس` / `SHELTER COFFEE HOUSE` | `شلتر كوفي هاوس — كافيه في إربد \| ساعات الدوام` / `SHELTER COFFEE HOUSE — Coffee house in Irbid \| Opening Hours` | ✅ | ✅ | نفس DRIVE | ✅ نفس DRIVE | TS-301 | نفس DRIVE |
| الفعاليات (SI-M07) | `/ar/jo/events/` | `/en/jo/events/` | self | 200 / 200 | `الفعاليات` / `Events` | `فعاليات شلتر كوفي في إربد` / `SHELTER COFFEE Events in Irbid` | ❌ `noindex` لأنها فارغة الآن (بالتصميم) | ❌ (تدخل تلقائيًا عند وجود فعالية) | F **فقط عند وجود فعالية** — الآن لا شيء | ❌ يتيمة الآن (بالتصميم) | TS-301 | لا توجد أي فعالية في البيانات (`experiences` فارغ) |
| فعالية/حملة (SI-M08) | `/ar/jo/events/{slug}/` | `/en/jo/events/{slug}/` | `$event->url` (self) | 404 / 404 لأي slug الآن | عنوان الفعالية | عنوان الفعالية | ✅ ما دامت صالحة · ❌ بعد انتهائها | ✅ عند الإدراج | بطاقات الفعاليات · Home feature · شريط الإعلان | — (لا فعاليات) | TS-301 | `slug` = `[a-z0-9-]+`. Event schema فقط قبل الانتهاء. **لم يُختبر مثال حي** (لا بيانات) |

### 3.3 صفحات العلامة والنماذج
| PAGE | AR PATH | EN PATH | CANONICAL | STATUS | H1 (AR / EN) | TITLE (AR / EN) | INDEXABLE | SITEMAP | NAV | LINKED | REDIRECTS | NOTES |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| التواصل (SI-B04) | `/ar/contact/` | `/en/contact/` | self | 200 / 200 | `تواصل معنا` / `Contact` | `تواصل مع شلتر كوفي — إربد` / `Contact SHELTER COFFEE — Irbid` | ✅ | ✅ | F ("كل قنوات التواصل" / "All contact channels") — ليست في الهيدر (D-027) | ✅ كل الصفحات (الفوتر) | TS-301 | تربط بالفرنشايز (منشورة) والفروع |
| التوظيف (SI-B11) | `/ar/careers/` | `/en/careers/` | self | 200 / 200 | `التوظيف` / `Careers` | `وظائف شلتر كوفي في إربد` / `Careers at SHELTER COFFEE, Irbid` | ✅ | ✅ | F | ✅ كل الصفحات (الفوتر) | TS-301 | النموذج **عربي فقط**. على Production النموذج مغلق (`CAREERS_FORM_ENABLED=false`) فيختفي رابط Apply من `/en/careers/`؛ يبقى رابط المتابعة |
| متابعة الطلب (SI-C02) | `/ar/careers/track/` | — | self | 200 / **404 بالتصميم** | `متابعة طلب التوظيف` | `متابعة طلب التوظيف — SHELTER COFFEE` | ❌ `noindex` | ❌ | — | ✅ من `/ar/careers/` و`/en/careers/` | TS-301 | GET+POST. بلا hreflang. مبدّل اللغة يذهب إلى `/en/` (لا مكافئ إنجليزي). `Cache-Control: no-store` |
| نجاح التوظيف (SI-C01) | `/ar/careers/submitted/` | — | `/ar/careers/` | **302** → `/ar/careers/` بلا جلسة · 404 EN | `تم استلام طلب التوظيف بنجاح` (من ملف اللغة — يحتاج إرسالًا فعليًا ليُعرض) | — | ❌ `noindex` | ❌ | — | ❌ (وجهة بعد الإرسال فقط) | TS-301 | الرقم من الجلسة، لا شيء في الرابط |
| الفرنشايز والشراكات (SI-B12) | `/ar/franchise/` | `/en/franchise/` | self | 200 / 200 | `كن شريكًا في نمو SHELTER COFFEE` / `Grow With SHELTER COFFEE` | `كن شريكًا مع SHELTER COFFEE` / `Partner With SHELTER COFFEE` | ✅ | ✅ | F (باسم الصفحة) + بطاقة في التواصل | ✅ كل الصفحات (الفوتر) + `/ar/contact/` | TS-301 | منشورة بمحتوى الـOwner V1 (M47، `FranchiseSeeder`). نموذج FR مغلق على Production (`FRANCHISE_FORM_ENABLED`) |
| نجاح الشراكة (SI-C03) | `/ar/franchise/submitted/` | `/en/franchise/submitted/` | `/{locale}/franchise/` | **302** → صفحة الفرنشايز بلا جلسة | `شكراً لاهتمامك بالشراكة مع SHELTER COFFEE` / `Thank You for Your Interest in Partnering With SHELTER COFFEE` (من ملف اللغة) | — | ❌ | ❌ | — | ❌ | TS-301 | — |
| رأي العميل (SI-B16) | `/ar/feedback/` | `/en/feedback/` | self | Dev 200 / 200 · **Production 404 / 404** (مغلق بالتصميم) | `كيف كانت تجربتك؟` / `How was your experience?` | `كيف كانت تجربتك؟ — SHELTER COFFEE` / `How was your experience? — SHELTER COFFEE` | ❌ `noindex` | ❌ | — (PO-063) | ❌ (بالتصميم) | TS-301 | يُفتح على Production فقط بـ`FEEDBACK_FORM_ENABLED=true` بعد PO-063 |
| نجاح رأي العميل (SI-C05) | `/ar/feedback/submitted/` | `/en/feedback/submitted/` | `/{locale}/feedback/` | **302** → `/…/feedback/` (وعلى Production الوجهة 404) | `شكرًا لك` / `Thank you` (من ملف اللغة) | — | ❌ | ❌ | — | ❌ | TS-301 | — |
| البحث (SI-B08) | `/ar/search/` | `/en/search/` | self (بلا `?q=`) | 200 / 200 | `البحث` / `Search` | `البحث — SHELTER COFFEE` / `Search — SHELTER COFFEE` · مع `?q=`: `نتائج البحث عن «قهوة» — SHELTER COFFEE` / `Results for “latte” — SHELTER COFFEE` | ❌ `noindex, follow` (SEO-023) | ❌ | أيقونة البحث في الهيدر (≥1024px) · نموذج البحث في D · نموذج البحث في 404 | ✅ كل الصفحات | TS-301 (يحفظ `?q=`) | Rate limit `throttle:search` |

### 3.4 صفحات تعطي 404 بالتصميم حتى ينشر الـOwner محتواها
| PAGE | AR PATH | EN PATH | STATUS (AR / EN) | شرط الظهور (200) | عند النشر: NAV · SITEMAP · INDEXABLE | NOTES |
|---|---|---|---|---|---|---|
| من نحن (SI-B03) | `/ar/about/` | `/en/about/` | 404 / 404 | صف `pages` (`about`) منشور **باللغتين** (PO-017) | F (Explore) · ✅ · ✅ | `ContentPageController` |
| الأسئلة الشائعة (SI-B05) | `/ar/faq/` | `/en/faq/` | 404 / 404 | `pages` (`faq`) منشور باللغتين (PO-034) | F (Explore) · ✅ · ✅ + FAQPage | — |
| الخصوصية (SI-B06) | `/ar/privacy/` | `/en/privacy/` | 404 / 404 | `pages` (`privacy`) منشور باللغتين (PO-019) | F (السطر القانوني) · ✅ · ✅ | انظر §5: رابط `/privacy/` القديم قد يستخدمه تطبيق Meta |
| الشروط (SI-B07) | `/ar/terms/` | `/en/terms/` | 404 / 404 | `pages` (`terms`) منشور باللغتين (PO-019) | F (السطر القانوني) · ✅ · ✅ | — |
| مركز الوسائط (SI-B13) | `/ar/media/` | `/en/media/` | 404 / 404 | `pages` (`media`) منشور باللغتين (G14-PO-01 / PO-062) | F (Business) · ✅ · ✅ | `MediaCenterController` · نصوص `lang/*/press.php` |
| الجوائز (SI-B14) | `/ar/awards/` | `/en/awards/` | 404 / 404 | جائزة واحدة على الأقل منشورة باللغتين ومُتحقق منها (PO-032) | F · ✅ · ✅ | `AwardsController` |
| SHELTER Family (SI-B15) | `/ar/family/` | `/en/family/` | 404 / 404 | عضو واحد على الأقل بموافقة نشر سارية (G13-PO-01) | F · ✅ · ✅ | `FamilyController` |

> كل صفحات هذا القسم تعطي **صفحة 404 مصممة** بلغة البادئة (`lang`/`dir` صحيحان)، بلا Stack trace. بدون الشرطة النهائية تعطي **301 ثم 404** (§9 RM-01).

### 3.5 ملفات وصفحات النظام
| PAGE | PATH | STATUS (Dev / Production-mode) | المحتوى المقاس | INDEXABLE | SITEMAP | NOTES |
|---|---|---|---|---|---|---|
| `robots.txt` (SI-S04) | `/robots.txt` | 200 / 200 | Dev: `User-agent: *` + `Disallow: /`. Production-mode: `Allow: /` + `Disallow: /dashboard` + `Sitemap: {APP_URL}/sitemap.xml` | — | — | مولّد (`RobotsController`) |
| `sitemap.xml` (SI-S05) | `/sitemap.xml` | **404** / 200 | Dev 404 بالتصميم (غير قابل للفهرسة). Production-mode: 17 رابطًا (§6) مع `xhtml:link` alternates | — | — | يظهر فقط عندما `APP_ENV=production` و`SHELTER_INDEXING=true` |
| `llms.txt` (SI-S06) | `/llms.txt` | 200 / 200 | العلامة · `Founded: 2019` · Pages (Home, Menu, Locations, Contact, الفرنشايز) باللغتين · Branches · Phone | — | — | لا يذكر Careers (RM-10). Events فقط عند وجود فعالية |
| Health | `/up` | 200 / 200 | صفحة Laravel الافتراضية (title `Laravel`) | — | ❌ | ليست صفحة موقع. تفاصيلها في ملف النشر |
| صفحة 404 (SI-S01) | أي مسار غير موجود | 404 | `/ar/…` → عربي `rtl` · `/en/…` → إنجليزي `ltr` · غير ذلك → ثنائية (H1 عربي + H2 إنجليزي) | ❌ `noindex` | ❌ | روابط: الرئيسية · المنيو · الفروع + نموذج بحث. بلا canonical وبلا hreflang |
| 405 | مثال: GET `/ar/careers/uploads/` | 405 | صفحة خطأ مصممة `تعذّر فتح هذه الصفحة` | ❌ | ❌ | المسار POST فقط |

---

## 4. الشرطة النهائية والتحويلات الحالية
| الطلب | النتيجة (Dev = Production-mode) |
|---|---|
| `/ar` · `/en` · `/ar/jo/menu` · `/ar/contact` · `/ar/jo/events` · `/ar/careers/track` | **301** بقفزة واحدة إلى نفس المسار + `/` |
| `/ar/careers?x=1` · `/ar/search?q=x` | **301** إلى `/ar/careers/?x=1` · `/ar/search/?q=x` (الـquery محفوظ) |
| مسار غير موجود بلا شرطة (`/menus`، `/career`، `/ar/careers/x`) | **404 مباشرة** (لا 301 قبلها — FINAL-QA QA-051) |
| مسار له Route لكن محتواه غير منشور، بلا شرطة (`/ar/about`، `/ar/media`، `/en/careers/track`، `/en/careers/submitted`، `/ar/jo/events/x`، `/ar/jo/locations/irbid/x`، و`/ar/feedback` على Production) | **301 ثم 404** (RM-01) |
| `/dashboard` · `/dashboard/` | 302 → `/dashboard/login` (خارج هذا الجرد) |

**قيد مهم للنشر:** الـ`Location` في الـ301 يُبنى من الطلب نفسه (scheme + host من `X-Forwarded-*` القادمة من proxy موثوق)، **وليس** من `APP_URL`. تحقق: بدون `X-Forwarded-Proto` يخرج `http://…`؛ ومع `X-Forwarded-Proto: https` يخرج `https://…`. على Staging يجب أن يعطي `curl -sI https://<host>/ar` الوجهة `https://<host>/ar/` بقفزة واحدة.

## 5. الروابط القديمة (Legacy) — الحالة الآن
- **لا يوجد أي تحويل Legacy فعّال.** مدير التحويلات (`App\Services\Seo\LegacyRedirects`، Commit `1544454`) يبحث في جدول `redirects` **فقط** عندما لا تجيب أي صفحة (قبل الـ404)، لـGET/HEAD، بقفزة واحدة ويحفظ الـquery. `LegacyRedirectSeeder` كتب **20 صفًا كلها `draft`**، و**0 `active`**. التفعيل خطوة إطلاق بموافقة الـOwner (D-054، D-182، SEO-013).
- التطبيع قبل المطابقة: فك الترميز، أحرف صغيرة، شرطة واحدة، بلا شرطة نهائية (`/menu/` = `/menu`).
- كل الروابط القديمة المختبرة تعطي الآن **404 مصممة** (ثنائية، `lang="ar"`)، بلا Stack trace.

| الرابط القديم | الآن | صف `redirects` (كله `draft`) | المرجع |
|---|---|---|---|
| `/menu` · `/menu/` | 404 | 301 → `/ar/jo/menu/` | D-054 (معتمد، التفعيل وقت الإطلاق) |
| `/القائمة-شلتر-كافية-محافظة-اربد` | 404 | 301 → `/ar/jo/menu/` | SEO-MIGRATION-MAP |
| `/hiring` | 404 | 301 → `/ar/careers/` | LIVE-CRAWL-24 |
| `/موقعنا` · `/locations` · `/موقع-شلتر-كافية-محافظة-اربد` | 404 | 301 → `/ar/jo/locations/` | LIVE-CRAWL-24 |
| `/city-centre-branch` | 404 | 301 → `/ar/jo/locations/irbid/house/` | URL-02 |
| `/qasr-al-nakheel-branch` · `/drive-thru` · `/drive-thru-shelter-irbid-how-it-works` · `/أول-خدمة-سيارات-في-الشمال-كافيه-شلتر` | 404 | 301 → `/ar/jo/locations/irbid/drive/` | URL-02 · DB-07 · SEED-02 |
| `/franchise-shelter-coffee` | 404 | 301 → `/ar/franchise/` | FRAN-093 |
| `/sitemap_index.xml` · `/post-sitemap.xml` · `/page-sitemap.xml` | 404 | 301 → `/sitemap.xml` | SEED-02 |
| `/locations.kml` · `/geo-sitemap.xml` · `/uicore-tb-sitemap.xml` · `/author-sitemap.xml` · `/category-sitemap.xml` | 404 | 410 | SEED-02 |
| `/best-cafes-irbid-2026/` | 404 | **لا صف** | R7-03 (REVIEW) |
| `/coffee-house-vs-cafe/` · `/blog/` | 404 | **لا صف** | PO-015 · PO-035 |
| `/privacy/` · `/terms/` · `/data-deletion/` | 404 | **لا صف** — قد يستخدمها تطبيق Meta (G21-TF-02) | PO-019 — يُحسم قبل الإطلاق |
| `/drive/` · `/house/` (QR قصير) | 404 | **لا صف** | URL-09 · PO-014 (غير معتمد) |
| `/careers/` · `/contact/` · `/franchise/` (بلا لغة) | 404 | **لا صف** | لا قرار |

## 6. محتوى `sitemap.xml` (Production-mode)
17 رابطًا، كلها مطلقة بالـHost، ولكل رابط alternates `ar`/`en` (والرئيسية والبوابة `x-default` → `/`):
`/ar/` · `/en/` · `/` · `/ar/jo/menu/` · `/en/jo/menu/` · `/ar/jo/locations/` · `/en/jo/locations/` · `/ar/jo/locations/irbid/drive/` · `/en/jo/locations/irbid/drive/` · `/ar/jo/locations/irbid/house/` · `/en/jo/locations/irbid/house/` · `/ar/franchise/` · `/en/franchise/` · `/ar/contact/` · `/en/contact/` · `/ar/careers/` · `/en/careers/`.

**المطابقة:** كل صفحة قابلة للفهرسة على 8092 موجودة في الـsitemap، ولا شيء `noindex` فيه (الفعاليات الفارغة، البحث، المتابعة، النجاح، رأي العميل خارجه). لا رابط ميت داخلي: كل الروابط الداخلية التي وُجدت بالزحف (54 على Dev، 42 على Production-mode) أعطت 200.

## 7. اللغة: رابط عربي ← صفحة عربية، رابط إنجليزي ← صفحة إنجليزية
فُحص `<html lang dir>` في **124 استجابة HTML** (كل الصفحات أعلاه + صفحات 404 + روابط الأخطاء الإملائية):

| النمط | المتوقع | النتيجة |
|---|---|---|
| كل `/ar/…` (200 أو 404 أو 405) | `lang="ar" dir="rtl"` | ✅ بلا استثناء |
| كل `/en/…` (200 أو 404) | `lang="en" dir="ltr"` | ✅ بلا استثناء |
| `/` (البوابة) | ثنائية: `lang="ar" dir="rtl"` + كتل `lang="en"` | ✅ |
| مسار بلا بادئة لغة (`/menus`، `/fr/`، `/AR/`) | 404 ثنائية، العربية أولًا | ✅ |
| `Content-Language` header | `ar` / `en` حسب البادئة | ✅ على كل الصفحات التي لها Route (ومنها 404 المحتوى غير المنشور مثل `/en/about/`). غائب عن 404 المسارات غير الموجودة (`/ar/does-not-exist/`) وعن البوابة `/` — الـ`lang` في الـHTML صحيح فيها |
| مبدّل اللغة | يذهب للصفحة المكافئة نفسها (وليس للرئيسية) | ✅ عدا `/ar/careers/track/` ← `/en/` (لا مكافئ، بالتصميم) |

## 8. اتساق التسمية
| المفهوم | Route name | URL | الكود (Controller / View) | العربية (Nav · Breadcrumb · H1) | English (Nav · Breadcrumb · H1) | الحكم |
|---|---|---|---|---|---|---|
| الفروع | `locations` · `locations.branch` | `locations/` · `locations/{city}/{branch}/` | `LocationsController` · `BranchController` · `site.locations` · `site.branch` | `الفروع` · `الفروع` · `الفروع` · رابط الرجوع `كل الفروع` | `Locations` · `Locations` · `Locations` · `All locations` | ✅ متسق داخل كل لغة. العربية تقول "الفروع" والرابط `locations` (ترجمة مقصودة). `/branches/` و`/location/` = 404 (لا Alias) |
| التوظيف | `careers` · `careers.track` · `careers.submitted` | `careers/` | `CareersController` | `التوظيف` (Nav · Breadcrumb · H1) · title `وظائف…` | `Careers` | ✅ · `/career/` = 404 |
| الفعاليات | `events` · `events.show` | `events/` · `events/{slug}/` | `EventsController` · `site.events` · `site.event` | `الفعاليات` | `Events` | ✅ · `/event/` = 404. في الـDashboard الاسم الداخلي `experiences` (حملات + فعاليات) |
| الوسائط | `media` | `/{locale}/media/` | `MediaCenterController` · `site.media-center` · `lang/*/press.php` | (لا Nav الآن) | — | ⚠️ ملاحظة: الكلمة نفسها لشيئين: صفحة `/ar/media/` وملفات الصور العامة `/media/<hash>/…` (`public/media`). لا تعارض فعلي. لا `gallery` في أي مكان (`/ar/gallery/` = 404) |
| الفرنشايز / الشراكات | `franchise` · `franchise.submitted` | `franchise/` | `FranchiseController` · الـDashboard: `partnerships` · `PartnershipApplication` · `FR-…` | Nav/Breadcrumb/title: `كن شريكًا مع SHELTER COFFEE` · H1: `كن شريكًا في نمو SHELTER COFFEE` · بطاقة التواصل: `استفسارات الفرنشايز` + `صفحة الفرنشايز` | `Partner With SHELTER COFFEE` · H1 `Grow With SHELTER COFFEE` · بطاقة التواصل `Franchise inquiries` + `Franchise page` | ⚠️ RM-07: الرابط وبطاقة التواصل يقولان "Franchise"، والتنقل والعنوان يقولان "Partner". اسم الصفحة محتوى معتمد من الـOwner (M47) — **لا تعديل بلا قرار** |
| Canonical ↔ Breadcrumb | — | — | `PageUrl::route` للاثنين | عناصر BreadcrumbList = نفس روابط الـcanonical | نفسه | ✅ |

## 9. المشاكل والملاحظات (مقاسة)
| # | الخطورة | المشكلة | الدليل | ملاحظة |
|---|---|---|---|---|
| RM-01 | LOW | **301 ثم 404** لمسارات لها Route ومحتواها غير منشور، عند طلبها بلا شرطة | `/ar/about` → 301 → `/ar/about/` → 404 (كذلك `/ar/media` · `/en/careers/track` · `/en/careers/submitted` · `/ar/jo/events/x` · `/ar/jo/locations/irbid/x` · `/ar/feedback` على Production) | QA-051 يمنع ذلك لما لا Route له فقط. لا رابط داخلي يقود لهذه الحالة |
| RM-02 | LOW | **رابط مكرر:** `/index.php` و`/index.php/{path}` تعطي 200 | `/index.php/ar/jo/menu/` = 200 | على Production الـcanonical نظيف (`/ar/jo/menu/`). على Local يصبح الـcanonical والـhreflang `/index.php/…` (CanonicalHost يعمل على Production/Staging فقط). علاجه قاعدة 301 على الخادم — تحويل Production يحتاج موافقة |
| RM-03 | LOW | **رابط مكرر:** `/ar/jo/menu//` (شرطتان في النهاية) = 200 | canonical = `/ar/jo/menu/` | `/ar//jo/menu/` = 404 |
| RM-04 | INFO | الفعاليات `/ar/jo/events/` 200 لكنها **يتيمة** الآن | لا رابط داخلي، `noindex`، خارج الـsitemap | بالتصميم (تظهر في الفوتر عند وجود فعالية) |
| RM-05 | INFO | رأي العميل بلا مدخل، و404 على Production، و`/…/feedback/submitted/` يعطي 302 إلى 404 على Production | 8092 | بالتصميم (PO-063) |
| RM-06 | LOW | البوابة تكتب السوق `jo` ثابتًا في روابط المنيو والفروع | `resources/views/site/gateway.blade.php` (أسطر 29–31) | بقية الصفحات تستخدم السوق النشط (`Markets::current`). لا خلل الآن؛ يصبح رابطًا ميتًا فقط إذا عُطّل السوق |
| RM-07 | LOW (محتوى) | خلط "Franchise" / "Partner" | §8 | قرار تسمية للـOwner، يُسجَّل ولا يُعدَّل |
| RM-08 | INFO | hreflang للسوق `ar`/`en` بلا `x-default` | §1 | بانتظار PO-005 (ROOT-02) |
| RM-09 | INFO | مستوى المدينة `/irbid/` في رابط الفرع بلا صفحة | `/ar/jo/locations/irbid/` = 404 | محجوز (URL-07). مسار التنقل يتخطاه |
| RM-10 | LOW | `llms.txt` لا يذكر Careers بينما الـsitemap يذكرها | 8092 | — |
| RM-11 | HIGH (قبل الإطلاق) | كل الروابط القديمة ذات القيمة 404 الآن | §5 | متوقع حتى بوابة PHASE 7 (20 صفًا `draft` جاهزة، 0 مفعّل). يجب حسمها قبل تحويل الـDNS، خاصة `/menu` (42 رابطًا خارجيًا) و`/privacy/` `/terms/` `/data-deletion/` (تطبيق Meta — بلا صف بعد) |
| RM-12 | INFO | `/up` تعرض صفحة Laravel الافتراضية (title `Laravel`، تطلب `fonts.bunny.net` و`cdn.jsdelivr.net` التي يمنعها الـCSP) | 8092 | شكلية؛ ليست مرتبطة ولا في الـsitemap |
| RM-13 | INFO | `/favicon.ico` ملف فارغ (0 bytes، 200) | `public/favicon.ico` | الأيقونات الفعلية `/brand/favicon-*.png` |
| RM-14 | INFO | الـHost: التطبيق يقبل `www` والنطاق المجرد وأي subdomain (`trustHosts`)، وأي Host آخر = **400** | Host مزوّر → 400 | تحويل `http`→`https` وبدون-`www`→`www` (DB-11) **ليس في التطبيق**: يُضبط على Cloudways/Cloudflare بموافقة |

**ما لم يُعثر عليه:** لا رابط داخلي ميت · لا صفحة بلغة خاطئة · لا Stack trace · لا Soft 404 · لا صفحة مفهرسة خارج الـsitemap · لا رابط `noindex` داخل الـsitemap · لا صفحة يصل إليها رابطان بلا canonical موحّد.

## 10. الروابط الخاطئة إملائيًا (Typo URLs)
كلها **404 مصممة** (`ui-error`)، بلا Stack trace، بلغة البادئة:

| الرابط | الحالة | اللغة |
|---|---|---|
| `/menus` · `/menus/` | 404 | ثنائية (ar أولًا) |
| `/locatoin` · `/locatoin/` | 404 | ثنائية |
| `/career` · `/career/` | 404 | ثنائية |
| `/franchises` · `/franchises/` | 404 | ثنائية |
| `/ar/jo/menus/` | 404 | ar |
| `/en/jo/menus/` | 404 | en |
| `/ar/careers/x` · `/ar/careers/x/` | 404 | ar |
| `/en/careers/x` | 404 | en |
| `/ar/locatoin/` · `/ar/jo/locatoin/` · `/ar/career/` · `/ar/franchises/` | 404 | ar |
| `/en/career/` · `/en/franchises/` · `/en/menu/` · `/en/locations/` | 404 | en |
| `/ar/jo/branches/` · `/ar/jo/location/` · `/ar/jo/event/` · `/ar/gallery/` · `/ar/partnerships/` · `/ar/menu/` · `/ar/locations/` · `/ar/events/` | 404 | ar |
| حالة الأحرف: `/AR/` · `/Ar/jo/menu/` · `/ar/JO/menu/` · `/ar/jo/Menu/` · `/ar/jo/locations/irbid/DRIVE/` | 404 (الروابط حساسة لحالة الأحرف — لا تكرار) | ثنائية / ar |
| لغة أو سوق غير موجود: `/fr/` · `/ar/xx/menu/` · `/ar/us/menu/` | 404 | ثنائية / ar |
| `/.env` · `/storage/` · `/ar/index.php` · `/ar/jo/menu/index.html` | 404 | ثنائية / ar |
| `/ar/careers/uploads/` (GET على مسار POST) | 405 مصممة | ar |

## 11. إعادة القياس
- المسارات: `php artisan route:list --json` (32 مسارًا خارج الـDashboard: 27 GET منها `/up`، و5 POST/DELETE للنماذج).
- الحالة/اللغة/العناوين: `curl -s -o /dev/null -w '%{http_code}' <url>` على Dev، ثم `<html lang dir>` و`<title>` و`<h1>` و`rel="canonical"`.
- الفهرسة: نفس الطلبات على خادم بـ`APP_ENV=production SHELTER_INDEXING=true`، و`/sitemap.xml`.
- **عند أي إضافة Route عامة:** يُحدَّث هذا الملف و`SITE-INVENTORY.md` معًا (SI-T1).
