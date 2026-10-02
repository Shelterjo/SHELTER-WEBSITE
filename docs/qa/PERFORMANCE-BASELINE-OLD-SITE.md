# PERF-004 — Baseline أداء الموقع القديم قبل الـMigration

> **التاريخ:** 2026-10-02 · **المتطلب:** `PERF-004` (APPROVED · P1) · **السياسة:** GEP-§36/§39/§40 · D-041 · D-042 · D-044
> **الحالة:** `PARTIAL`
> - **الموقع القديم (Lab / Lighthouse):** `BLOCKED` في هذه البيئة. السبب مسجّل حرفيًا في §2.
> - **الموقع القديم (HTTP-level):** مقاس. طلب واحد لكل رابط عبر `curl` (§3). **ليس بديلًا عن Lighthouse.**
> - **الموقع الجديد (Lab محلي):** مقاس على 5 صفحات × Mobile/Desktop × 3 تشغيلات (§4).
> - **Field data (CrUX / Search Console):** غير متاحة، وتحتاج وصول Google (PO-011).
>
> لا شيء هنا قرار. هذه أرقام مختبر للمقارنة. **أرقام المختبر المحلية ليست Field data**، ولا تُعرض على أنها أداء الزوار الحقيقي.

## 1. المنهج
| البند | القيمة |
|---|---|
| الأداة | Lighthouse **13.5.0** (`tooling/node_modules`) عبر Node API، وChromium **141.0.7390.37** (`/opt/pw-browsers/chromium`)، Headless |
| Mobile | الإعداد الافتراضي: `formFactor: mobile`، شاشة 412px، `throttlingMethod: simulate` (RTT 150ms، 1.6 Mbps، CPU ×4) |
| Desktop | `desktop-config` الرسمي: شاشة 1350px، RTT 40ms، 10 Mbps، CPU ×1 |
| الفئات | Performance · Accessibility · Best Practices · SEO |
| ما يُسجّل لكل رابط | الدرجات الأربع · LCP · CLS · TBT · FCP · Total transfer size (`total-byte-weight`) · عدد الطلبات (`network-requests`) · أهم 5 Audits سببية |
| ترتيب الأسباب | الـAudits الفاشلة من فئة Performance (score < 0.9) مرتبة حسب التوفير المقدّر بالـms، ثم بالـbytes. المقاييس نفسها (LCP…) مستثناة من القائمة |
| التكرار | **الجديد:** 3 تشغيلات لكل صفحة ولكل Preset، ونأخذ الوسيط (Median). **القديم:** كان المخطط تشغيلًا واحدًا لكل رابط ولكل Preset (10 تحميلات صفحة فقط) احترامًا لـD-044: لا Load testing ولا Crawl على Production |
| الخادم الجديد | `http://127.0.0.1:8092`: Proxy يضغط gzip ويضع Cache طويلًا للأصول ذات الـHash (يحاكي nginx على Cloudways)، أمام خادم PHP بوضع Production على 8002. **محلي:** لا شبكة حقيقية ولا Cloudflare ولا CDN |
| السكربت | سكربت مؤقت في مجلد العمل (scratch) يستدعي نفس مكتبة `tooling/scripts/lighthouse.mjs`. أُضيف Desktop preset وFCP وحجم النقل وعدد الطلبات. لم يُغيّر أي ملف في المستودع |

## 2. الموقع القديم — Lighthouse: `BLOCKED` (الخطأ كما هو)
**الشبكة نفسها ليست محجوبة:** `curl` عبر الـProxy يصل إلى `https://www.shelterjo.com/` ويعيد `200` (§3).

**المتصفح هو الذي يفشل:** Chromium داخل الـSandbox لا يثق بشهادة الـProxy الذي يعيد إنهاء TLS، فيتوقف التحميل قبل أي قياس. سُجّل الخطأ في المحاولة الأولى على الرئيسية، Mobile وDesktop، في `2026-10-02T16:49Z`:

| المصدر | الخطأ الحرفي |
|---|---|
| Lighthouse `runtimeError` | `INSECURE_DOCUMENT_REQUEST` — "The URL you have provided does not have a valid security certificate. net::ERR_CERT_AUTHORITY_INVALID" |
| Lighthouse `runWarnings` | "The page may not be loading as expected because your test URL (https://www.shelterjo.com/) was redirected to chrome-error://chromewebdata/." |
| Playwright (Chromium) | `page.goto: net::ERR_CERT_AUTHORITY_INVALID at https://www.shelterjo.com/` |

- **ما فُعل بعدها:** أوقفنا هذا الجزء كما طُلب. **لم يُعطَّل التحقق من TLS، ولم تُتجاوز سياسة البيئة.**
- **محاولة سابقة (2026-10-01):** طلب PageSpeed Insights API أعاد `429 RESOURCE_EXHAUSTED` (حصة الطلبات اليومية). لم يُكرر.
- **الروابط الأربعة الأخرى:** لم تُشغَّل بـLighthouse للسبب نفسه.
- **حالة الروابط** (من `curl`): كلها تعمل. لا يوجد 404:
  - `/menu` يعيد **301** إلى `/القائمة-شلتر-كافية-محافظة-اربد/` ثم 200.
  - الروابط الأخرى تعيد 200 مباشرة.

**لإكمال هذا الجزء** يلزم تشغيل Lighthouse من بيئة متصفحها يثق بالشبكة، مثل CI على GitHub Actions أو جهاز محلي بإنترنت مباشر. **هذا قرار بيئة يعود للمستخدم.** المنهج في §1 جاهز ليُعاد كما هو.

## 3. الموقع القديم — قياس HTTP (طلب واحد لكل رابط، ليس Lighthouse)
**طريقة القياس:**
- `curl` عبر الـProxy المعتمد للبيئة.
- User-Agent موبايل.
- `Accept-Encoding: br`.
- الـHTML حُلل محليًا.
- طلب قياس واحد لكل رابط (D-044).
- **زمن أول بايت (TTFB) هنا يشمل الـProxy**، فلا يُقارن بأرقام المختبر.

| الرابط | HTTP | HTML مضغوط (br) | HTML خام | TTFB عبر الـProxy (عينتان) | سكربتات خارجية / Inline | Stylesheets (منها في `<head>`) | سكربتات Blocking في `<head>` | `<img>` (منها lazy) | H1 |
|---|---|---|---|---|---|---|---|---|---|
| `/` | 200 | 38 KB | 200 KB | 1.29s · 3.19s | 27 / 36 | 35 (32) | 3 | 30 (20) | 1 |
| `/menu` | **301** ← 200 | 35 KB | 297 KB | 2.77s (مع الـ301) | 25 / 36 | 23 (19) | 3 | **118** (103) | **2** |
| `/best-cafes-irbid-2026/` | 200 | 31 KB | 133 KB | 1.41s · 1.48s | 24 / 37 | 19 (13) | 3 | 7 (0) | 1 |
| `/city-centre-branch/` | 200 | 27 KB | 109 KB | 2.59s · 1.21s | 24 / 37 | 18 (12) | 3 | 7 (0) | 1 |
| `/drive-thru/` | 200 | 27 KB | 108 KB | 3.33s · 1.39s | 24 / 36 | 18 (12) | 3 | 7 (0) | **0** |

**ترويسات مشتركة لكل الصفحات:**
- `server: cloudflare` · HTTP/2 · `content-encoding: br`.
- **`cf-cache-status: DYNAMIC`** و**`cache-control: max-age=0`**: الـHTML لا يُخزَّن على Cloudflare، فكل زيارة تصل إلى WordPress.

**المنصة** (من وسوم `generator`):
- WordPress 7.1.2 + Elementor 4.3.3 + Site Kit 1.188.0.
- Breeze وPojo Accessibility.
- مطابق للجرد في [`24-live-site-crawl`](../phase-01-discovery/24-live-site-crawl-2026-10-01.md).

**أطراف خارجية في الـHTML (كل الصفحات):**
- `www.googletagmanager.com`: gtag لـGoogle Ads `AW-…`.
- Meta Pixel: محمّل `fbevents` عبر سكربت Inline.
- HubSpot: `js-eu1.hs-scripts.com`.
- Jetpack Stats: `stats.wp.com`.
- `cdn.elementor.com`.
- jQuery محمّل في `<head>`.

### أهم 5 مؤشرات سببية للموقع القديم (من الـHTML والترويسات، ليست Lighthouse Audits)
1. **الـHTML غير مخزّن على الـEdge** (`DYNAMIC` و`max-age=0`). زمن أول بايت متذبذب: **1.2–3.3s** عبر الـProxy. هذا يضر LCP مباشرة.
2. **CSS ثقيل يحجب العرض:** 12–32 ملف Stylesheet في `<head>` (Elementor والإضافات)، و3 سكربتات Blocking في `<head>`.
3. **JavaScript كثير:** 24–27 سكربت خارجي و~36 سكربت Inline في كل صفحة، منها 4–5 أطراف خارجية للتتبع والتسويق.
4. **HTML ضخم:** 108–297 KB خام لكل صفحة.
   - صفحة القائمة فيها **118 صورة** و**1,143 مرجع PNG** داخل `srcset`.
   - الرئيسية فيها 91 مرجع PNG و54 JPG، مقابل 29 WebP.
5. **تحويل 301 إضافي** على `/menu` قبل وصول المحتوى (رحلة شبكة كاملة إضافية).

## 4. الموقع الجديد — Lab محلي (Lighthouse، الوسيط من 3 تشغيلات)
`http://127.0.0.1:8092`. تشغيل واحد استُبعد: `/ar/jo/menu/` Desktop رقم 3 فشل بـ`NO_NAVSTART` (خلل في الـTrace)، فالوسيط هنا من تشغيلين.

### Mobile
| الصفحة | Perf | A11y | BP | SEO | LCP | CLS | TBT | FCP | النقل | الطلبات | عنصر الـLCP |
|---|---|---|---|---|---|---|---|---|---|---|---|
| `/ar/` | 98 | 100 | 100 | 100 | 2.14s | 0.0001 | 0ms | 1.67s | 209 KB | 13 | نص عنوان الفرع (`p.ui-branch__place`) |
| `/ar/jo/menu/` | 97 | 100 | 100 | 100 | 2.27s | 0.0011 | 0ms | 1.82s | 215 KB | 13 | مقدمة الصفحة (`p.ui-page-intro__lead`) |
| `/ar/jo/locations/` | 98 | 100 | 100 | 100 | 2.18s | 0.0003 | 0ms | 1.66s | 208 KB | 13 | مقدمة الصفحة |
| `/ar/jo/locations/irbid/house/` | 98 | 100 | 100 | 100 | 2.20s | 0.0008 | 0ms | 1.67s | 228 KB | 14 | H1 الفرع |
| `/ar/jo/locations/irbid/drive/` | 98 | 100 | 100 | 100 | 2.19s | 0.0008 | 0ms | 1.66s | 228 KB | 14 | H1 الفرع |

### Desktop
| الصفحة | Perf | A11y | BP | SEO | LCP | CLS | TBT | FCP | النقل | الطلبات |
|---|---|---|---|---|---|---|---|---|---|---|
| `/ar/` | 100 | 100 | 100 | 100 | 0.45s | 0.0000 | 0ms | 0.37s | 186 KB | 12 |
| `/ar/jo/menu/` | 100 | 100 | 100 | 100 | 0.51s | 0.0010 | 0ms | 0.43s | 209 KB | 13 |
| `/ar/jo/locations/` | 100 | 100 | 100 | 100 | 0.46s | 0.0002 | 0ms | 0.38s | 186 KB | 12 |
| `/ar/jo/locations/irbid/house/` | 100 | 100 | 100 | 100 | 0.46s | 0.0003 | 0ms | 0.37s | 194 KB | 13 |
| `/ar/jo/locations/irbid/drive/` | 100 | 100 | 100 | 100 | 0.52s | 0.0003 | 0ms | 0.39s | 213 KB | 14 |

**التذبذب بين التشغيلات صغير:**
- LCP Mobile: ±80ms.
- LCP Desktop: ±40ms.

**تركيب النقل (Mobile `/ar/`):**

| النوع | الحجم | الملفات |
|---|---|---|
| خطوط | **158 KB** | 6 |
| CSS | 13 KB | 1 |
| JS | 4 KB | 2 |
| صور | 26 KB | 2 (الشعار مرتين) |
| HTML | 6 KB | 1 |

**أطراف خارجية:** صفر. **تحويلات:** صفر.

### أهم الأسباب الجذرية للموقع الجديد (Lighthouse + ميزانية الأداء)
| # | السبب | الدليل | المقترح التقني |
|---|---|---|---|
| 1 | `render-blocking-insight`: ملف `site.css` يحجب العرض الأول (13.3 KB) | توفير مقدّر **690–750ms** على Mobile في كل الصفحات | CSS الحرج Inline (≤ 14 KB حسب [`PERFORMANCE-BUDGET`](../menu-ia/PERFORMANCE-BUDGET.md)) والباقي غير حاجب |
| 2 | **الخطوط فوق الميزانية** (ليست Audit فاشلًا في Lighthouse) | 6 ملفات WOFF2 وزنها **150–158 KB**. الميزانية **≤ 120 KB و3–4 ملفات**. Noto Kufi Arabic بثلاثة أوزان (300 · 500 · 700 ≈ 134 KB) + Poppins بثلاثة أوزان. عنصر الـLCP في كل الصفحات **نص**، فالخط يحدد LCP | تقليص الأوزان (وزنان للعربي)، وSubset أدق، وPreload لخط النص بلغة الصفحة فقط |
| 3 | `image-delivery-insight`: توفير مقدّر 13 KiB | الشعار يُحمَّل **مرتين بصيغتين**: `logo-white-480.webp` في الـHeader (10.7 KB) و`logo-white-480.png` في الـFooter (15.4 KB) | نفس `<picture>` و`srcset` بالحجم المعروض (~110px) في الموضعين |
| 4 | `unused-css-rules`: حوالي 11 KiB غير مستخدم | حزمة CSS واحدة لكل الصفحات | يُقبل الآن (الحجم صغير). يُراجع إذا كبرت الحزمة |
| 5 | LCP على Mobile قريب من الحد | **2.14–2.27s** مقابل حد **2.5s**. الهامش ~250ms فقط قبل إضافة الصور المعتمدة أو أي Tag | علاج البندين 1 و2 يعيد هامشًا كافيًا قبل وصول الصور (M-10) والتتبع (GA4/GTM) |

## 5. مقارنة جنبًا إلى جنب (ما يمكن مقارنته فعلًا)
**مطابقة الروابط** (حسب طلب المهمة):
- `/best-cafes-irbid-2026/` مقالة، وتقابلها صفحة الفروع **تقريبيًا فقط**.
- `/city-centre-branch/` تقابل فرع House (إربد سيتي سنتر).

| القديم ← الجديد | HTML مضغوط | سكربتات خارجية | CSS (ملفات) | أطراف خارجية | تحويل | Lighthouse قديم | Lighthouse جديد Mobile (Perf · LCP) | Lighthouse جديد Desktop (Perf · LCP) |
|---|---|---|---|---|---|---|---|---|
| `/` ← `/ar/` | 38 KB ← **4.6 KB** (gzip) | 27 ← 2 (ES modules) | 35 ← 1 | 5 ← **0** | — | BLOCKED | 98 · 2.14s | 100 · 0.45s |
| `/menu` ← `/ar/jo/menu/` | 35 KB ← 22.9 KB | 25 ← 3 | 23 ← 1 | 5 ← 0 | **301** ← لا | BLOCKED | 97 · 2.27s | 100 · 0.51s |
| `/best-cafes-irbid-2026/` ← `/ar/jo/locations/` | 31 KB ← 4.2 KB | 24 ← 2 | 19 ← 1 | 5 ← 0 | — | BLOCKED | 98 · 2.18s | 100 · 0.46s |
| `/city-centre-branch/` ← `…/irbid/house/` | 27 KB ← 4.8 KB | 24 ← 3 | 18 ← 1 | 5 ← 0 | — | BLOCKED | 98 · 2.20s | 100 · 0.46s |
| `/drive-thru/` ← `…/irbid/drive/` | 27 KB ← 4.8 KB | 24 ← 3 | 18 ← 1 | 5 ← 0 | — | BLOCKED | 98 · 2.19s | 100 · 0.52s |

**تنبيه عند المقارنة:**
- **الموقع الجديد لا يحمل بعد** الصور المعتمدة (M-10) ولا GA4 ولا GTM.
- الموقع القديم يحمل Google Ads وMeta Pixel وHubSpot وJetpack.
- **الفارق الحالي جزء منه "محتوى أقل".** المقارنة العادلة تُعاد بعد إضافة الصور والتتبع المعتمد، وبنفس المنهج (GEP-§40: قبل وبعد).

## 6. الحدود (Limits)
- **Lab ≠ Field:** كل الأرقام هنا مختبرية بمحاكاة شبكة ومعالج. **Core Web Vitals الحقيقية** (CrUX، وتقرير Search Console، وINP) تحتاج وصول Google: **PO-011**. لم تُستخدم أي أداة مدفوعة ولا حصة (D-044).
- **المحلي ≠ Production:**
  - الخادم الجديد محلي على نفس الجهاز، فزمن أول بايت 44–118ms، وهو أقل من أي خادم حقيقي.
  - لا Cloudflare، ولا HTTP/2 من CDN، ولا Brotli (الـProxy المحلي يضغط gzip فقط).
- **INP لا يُقاس في Lighthouse Navigation.** TBT بديل مختبري فقط.
- **الموقع القديم:**
  - أرقام §3 من طلب واحد عبر Proxy، وزمنها يشمل الـProxy.
  - لا Lighthouse بسبب §2.
  - لم يُعدَّل الموقع القديم بأي شكل (D-037).
- **الدقة:** 3 تشغيلات للجديد. التذبذب صغير لكن ليس صفرًا. تشغيل واحد مستبعد (`NO_NAVSTART`).
- **الحمل:** لم يُنفذ أي Load test ولا Crawl. الطلبات إلى `www.shelterjo.com` في هذه الجولة:
  - **13 طلب HTTP** عبر `curl`: فحص حالة، ثم قياس واحد لكل رابط، مع تحويل `/menu`.
  - **3 محاولات متصفح** (Lighthouse ×2 وPlaywright ×1) فشلت في TLS داخل الـSandbox قبل إرسال أي طلب HTTP.

## 7. التالي
| # | الخطوة | يعتمد على |
|---|---|---|
| 1 | تشغيل §1 على الروابط القديمة الخمسة من بيئة متصفحها يثق بالشبكة، وتعبئة عمود "Lighthouse قديم" | قرار البيئة (المستخدم) |
| 2 | تصدير CrUX/Search Console للروابط نفسها كـField baseline | PO-011 |
| 3 | علاج الأسباب 1–3 للموقع الجديد (CSS حرج، أوزان الخطوط، الشعار المكرر)، ثم إعادة القياس بنفس المنهج | قرار تقني (M38: يُبنى مباشرة) |
| 4 | إعادة القياس بعد الصور المعتمدة (M-10) وGA4/GTM، ثم بعد الإطلاق على Production بنفس المنهج | PHASE 5 / PHASE 7 |
