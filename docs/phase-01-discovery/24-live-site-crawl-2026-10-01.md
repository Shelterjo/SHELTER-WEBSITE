# 24 — فحص مباشر للموقع الحالي (Live crawl)

| البند | القيمة |
|---|---|
| **التاريخ** | 2026-10-01 |
| **الوصول** | **AC-01 فعّال.** الـOwner عدّل Network access للبيئة. تحقق في 2026-10-01 أن `shelterjo.com` و`www.shelterjo.com` و`shop.shelterjo.com` و`api.cloudflare.com` كلها متاحة، فأُغلق PO-008 |
| **الطريقة** | **قراءة فقط** بطلب واحد في الثانية:<br>- `robots.txt`، ثم الـSitemaps (6)، ثم كل رابط فيها (34).<br>- 13 رابطًا قديمًا من البذرة [`02`](02-url-inventory-and-migration-seed.md).<br><br>لا نماذج، ولا تسجيل دخول، ولا مسارات إدارة، ولا تنزيل صور. **لم يتغير أي شيء** في الموقع أو Cloudflare أو Cloudways |
| **البيانات** | [`data/live-site-urls-2026-10-01.csv`](data/live-site-urls-2026-10-01.csv): 47 رابطًا مع الحالة والتحويل والـCanonical والـRobots والعنوان وعدد H1 والـSchema والصور |
| **القاعدة** | الموقع القديم **مصدر معلومات فقط** (D-041). أي معلومة عمل فيه (أسماء الفروع، العناوين، الادعاءات) **لا تُعتمد** بدون موافقة الـOwner |

## 1. الخلاصة
| البند | النتيجة |
|---|---|
| **المنصة** | - WordPress 7.1.2 + Elementor 4.3.3 (+ Pro).<br>- قالب Rise (UiCore).<br>- Yoast (Sitemaps)، وSite Kit 1.188.0، وJetpack، وBreeze (Cache)، وPojo Accessibility، وElement Pack |
| **الـCDN** | Cloudflare أمام `www` (`server: cloudflare`):<br>- HTML: `cf-cache-status: DYNAMIC`.<br>- الملفات الثابتة: `HIT` |
| **الـHost الواحد** | `https://shelterjo.com/` يحوّل بـ**301** إلى `https://www.shelterjo.com/` (قفزة واحدة) |
| **الـSitemaps** | `sitemap_index.xml` (Yoast) فيه 6 خرائط و**34 رابطًا، كلها 200**.<br>`/sitemap.xml` يحوّل بـ301 إلى الفهرس |
| **صفحة 404** | ترجع حالة **404** فعلية مع `noindex` (سليم) |
| **التحويلات القائمة** | تحوّل بـ301 إلى الروابط العربية، بقفزة واحدة:<br>- `/menu`<br>- `/hiring`<br>- `/موقعنا` |
| **`/ar/` و`/en/`** | **404**. المسارات فارغة، فلا تعارض مع بنية الموقع الجديد |
| **صفحات الـDemo في البذرة** | **أصبحت 404:**<br>- `/the-biggest-mental-and-physical-benefits-of-working-out/`<br>- `/mental-physical-challenges-to-overcome/`<br><br>`/category/uncategorized/` ترجع 200 مع `noindex` |
| **الساعات في الـSchema** | **مطابقة تمامًا لـD-020** للفرعين:<br>- DRIVE: السبت–الخميس 07:00–02:00، والجمعة 08:00–02:00.<br>- HOUSE: السبت–الأربعاء 09:00–22:00، والخميس–الجمعة 09:00–23:00.<br><br>**لا تعارض** |

## 2. التتبع الموجود (ANL-001 · ANL-002 · GOOGLE-009 · INT-009)
> **القاعدة:** لا ازدواج. الموقع الجديد لا يضيف أي Tag قبل قرار GOOGLE-010.

| الأداة | المعرّف | الانتشار | الملاحظة |
|---|---|---|---|
| **Google tag عبر Site Kit** | `GT-NNZXZLP5` | كل الصفحات | معرّف GA4 لا يظهر في HTML لأنه داخل إعداد الـGoogle tag. يُؤكد بوصول GA4 (PO-011/012) |
| **Google Ads** | `AW-454452815` | 43/43 صفحة | `gtag('config', 'AW-…')` |
| **Meta Pixel** | `2425046304590717` | 43/43 صفحة | — |
| **Google Tag Manager** | — | **غير موجود** | — |
| **Universal Analytics** | — | **غير موجود** | — |
| **TikTok** | — | **لا Pixel** | الموجود روابط حسابات فقط |
| **Search Console** | Meta tag `google-site-verification` | الرئيسية | التحقق بـHTML tag. المرجّح أن الـProperty من نوع URL-prefix، ويُؤكد بالوصول (PO-011) |

## 3. ملاحظات SEO تقنية
هذه الملاحظات للنقل وللبناء الجديد. **لا تعديل على الموقع القديم** (D-037).

| # | الملاحظة | الأثر على البناء الجديد |
|---|---|---|
| 1 | **Schema مكرر على كل الصفحات:**<br>- FAQPage وMenu وCafeOrCoffeeShop وOpeningHours على **43/43** صفحة.<br>- يشمل ذلك صفحات 404 والأرشيفات | Schema حسب نوع الصفحة فقط (المعمارية المعتمدة) |
| 2 | **H1:**<br>- **بلا H1 (9 صفحات):** `/blog/`، `/newsletter/`، `/drive-thru/`، `/faq/`، و`/locations/` مع صفحتيه الفرعيتين، والتصنيف، والكاتب.<br>- **فيها H1 مكرر (5 صفحات):** `/privacy/`، `/terms/`، `/data-deletion/`، صفحة التوظيف، صفحة القائمة | H1 واحد لكل صفحة (اختبار آلي) |
| 3 | **Meta description فارغ في 5 صفحات أرشيف:** `/locations/`، وصفحتا الفروع تحته، والتصنيف، والكاتب | — |
| 4 | **`/author/info/` قابل للفهرسة** ويكشف اسم مستخدم WordPress | تحويل موجود في البذرة (REDIRECT) |
| 5 | **ازدواج صفحات الفروع:**<br>- `/locations/…` (Yoast Local)<br>- `/qasr-al-nakheel-branch/` و`/city-centre-branch/`<br>- `/موقع-شلتر-كافية-محافظة-اربد/` | دمج في خريطة التحويل (DB-07) |
| 6 | **وزن HTML:**<br>- 100–300 KB للصفحة.<br>- صفحة القائمة 297 KB وفيها 118 صورة | ميزانية الأداء |
| 7 | **صورة بلا `alt`** في كل صفحة (عنصر مشترك)، و9 من 16 صورة في مقالة واحدة | `alt` إلزامي في الـMedia Library |

## 4. روابط يجب حمايتها عند الإطلاق (إضافة إلى LEGACY-URL-MIGRATION)
| الرابط | لماذا |
|---|---|
| `/privacy/` · `/terms/` · `/data-deletion/` | **مرجّح أن تطبيق Meta يستخدمها** كـPrivacy Policy URL وData Deletion URL.<br>إذا انكسرت قد يتوقف التطبيق أو يُرفض في المراجعة.<br>**KEEP أو 301**، مع التحقق من إعدادات تطبيق Meta قبل الإطلاق |
| **مقالات جديدة منذ البذرة (7):**<br>- `/shelter-menu-irbid-2026/`<br>- `/shelter-spring-drinks-2026-irbid/`<br>- `/specialty-vs-commercial-coffee/`<br>- `/best-drive-thru-coffee-irbid/`<br>- `/specialty-coffee-brewing-methods/`<br>- `/specialty-coffee-beans-origins/`<br>- `/قهوة-مختصة-اربد-دليل-شلتر/` | منشورة ومفهرسة. تدخل جرد التحويل، وقيمتها تُقاس من GSC (PO-011) |
| **صفحات جديدة:** `/faq/` · `/qasr-al-nakheel-branch/` · `/locations/` (+2) · `/category/specialty-coffee/` | تدخل الجرد |

> **معلومات الفروع في الموقع القديم (مصدر فقط، غير معتمدة — PO-010):**
> - "الفرع الرئيسي — بجانب قصر النخيل".
> - "إربد ستي سنتر، الطابق الأول".
>
> تُعرض على الـOwner عند سؤال PO-010، ولا تُنسخ إلى Master Data قبل الموافقة.

## 5. `shop.shelterjo.com` — ملاحظة أمنية
| البند | النتيجة |
|---|---|
| **الـDNS** | يشير **مباشرة إلى عنوان IP لسيرفر** (نطاق DigitalOcean)، **ولا يمر عبر Cloudflare**. العنوان لا يُكتب هنا عمدًا |
| **الـHTTPS** | شهادة `*.cloudwaysapps.com` **لا تطابق** `shop.shelterjo.com`، فيظهر للزائر **تحذير أمان** |
| **الخطر** | إذا كان نفس سيرفر `www`، فالسجل **يكشف عنوان الـOrigin**. هذا يسمح بتجاوز حماية Cloudflare (WAF/DDoS) |
| **الغرض** | غير معروف (VQ-03 · D-016) |
| **ما فُعل** | **لا شيء.** تعديل الـDNS تغيير Production يحتاج موافقتك (M38 §10) |
| **التالي** | بعد وصول Cloudflare للقراءة:<br>1. نتأكد هل هو نفس سيرفر `www`.<br>2. تُعرض عليك الخيارات بقرار واحد:<br>- **A:** حذف السجل إن لم يُستخدم.<br>- **B:** تمريره عبر Cloudflare مع شهادة صحيحة.<br>- **C:** إبقاؤه |

## 6. ما لم يُفحص من هذه البيئة
| البند | السبب | البديل |
|---|---|---|
| تحويل `http://` إلى `https://` | الـProxy في بيئة التطوير يقبل HTTPS فقط | قراءة قواعد Cloudflare (PO-013) |
| Lighthouse / Core Web Vitals / Playwright flows | لم تُشغّل في هذه الجولة | تشغيل محلي مجاني (`npm run lighthouse`) في جولة الـBaseline |
| مسارات الإدارة وأمان WordPress | خارج نطاق جرد القراءة هذا | ZAP على الـStaging (TOOLCHAIN) |
