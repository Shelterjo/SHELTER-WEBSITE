# SHELTER COFFEE WEBSITE — MASTER REQUIREMENTS

> **SINGLE SOURCE OF TRUTH** لمتطلبات المشروع كله: الموقع + الـOwner Dashboard + الـCMS + Google + الجودة.
> **الحالة:** `DRAFT — PENDING OWNER APPROVAL` (PO-045). كل متطلب اعتمده الـOwner سابقًا يبقى معتمدًا، والاعتماد المطلوب هو للتجميع نفسه.
> **آخر تحديث:** 2026-10-01 · **المصدر:** تدقيق كامل للمحادثة (38 رسالة، حوالي 439 ألف حرف) + ملفات المشروع + الكود والأدوات.

## كيف بُني هذا الملف
1. **الاستخراج:** كل رسائل الـOwner قُرئت كاملة بالترتيب M01 ← M38.
   - M01–M27: استُخرج منها **1797 بندًا خامًا** بمصدره ونصه الحرفي.
   - M28 (التوظيف) وM29 (الشراكات) وM30 (لوحة التحكم للـOwner فقط): مواصفات مُهيكلة، دُمجت مباشرة بمتطلبات قانونية مع مصادرها بالأقسام.
2. **الدمج:** دُمجت البنود في **1350 متطلبًا قانونيًا** بمعرفات ثابتة `PREFIX-NNN`.
3. **التغطية:**
   - 1789 بندًا خامًا مربوطة بمتطلب.
   - 8 مستبعدة لأنها مثال أو مرجع فقط، والسبب موثق.
   - **0 غير محسوبة.**
4. **قاعدة الأولوية بين المصادر:**
   1. آخر قرار صريح من الـOwner.
   2. قرار سابق للـOwner لم يُستبدل.
   3. وثيقة معتمدة.
   4. مصدر رسمي متحقق منه.
   5. الكود: دليل تنفيذ فقط.
   6. اقتراح AI.
5. **التاريخ لا يُحذف:** أي متطلب تغيّر يحمل سطر `↺ سابقًا`.
6. **أي تعارض لا يُحسم بصمت:** يُسجّل في `CONFLICT-REGISTER.md`.

## الملفات المرتبطة
| الملف | الدور |
|---|---|
| [`MASTER-DECISION-REGISTER.md`](MASTER-DECISION-REGISTER.md) | الحالة الحالية لكل قرار (455 قرارًا) |
| [`governance/DECISION-LOG.md`](governance/DECISION-LOG.md) | السجل الزمني (D-000 ← D-332) |
| [`CONFLICT-REGISTER.md`](CONFLICT-REGISTER.md) | 216 تعارضًا وطريقة حسمها |
| [`PENDING-OWNER-INPUT.md`](PENDING-OWNER-INPUT.md) | 64 بندًا فقط تحتاجك |
| [`REQUIREMENTS-TRACEABILITY-MATRIX.md`](REQUIREMENTS-TRACEABILITY-MATRIX.md) | متطلب ← قرار ← تصميم ← كود ← اختبار |
| [`IMPLEMENTATION-GAP-ANALYSIS.md`](IMPLEMENTATION-GAP-ANALYSIS.md) | ما الموجود وما الناقص |
| [`IMPLEMENTATION-PLAN.md`](IMPLEMENTATION-PLAN.md) | الخطة الموحدة P00 ← P12 والبوابات |
| [`menu-ia/MENU-DECISION-REGISTER.md`](menu-ia/MENU-DECISION-REGISTER.md) | تفاصيل قرارات المنيو (F / P / M / CF / R) |

## المفردات
- **الحالة (Status):**
  - `FROZEN`: مجمّد، لا يُفتح إلا بتعارض حقيقي.
  - `APPROVED`: توجيه أو قرار صريح من الـOwner.
  - `APPROVED WITH CONDITIONS`
  - `PENDING OWNER INPUT`
  - `PENDING VERIFICATION`
  - `DEFERRED`
  - `REJECTED`
  - `SUPERSEDED`
- **النوع (Type):**
  - `REQUIREMENT`: ما يُبنى.
  - `RULE`: قاعدة دائمة.
  - `GATE`: بوابة اعتماد.
  - `DELIVERABLE`: مُخرج.
  - `DECISION`: قرار.
- **الأولوية:**
  - `P0`: يمنع الإطلاق، أو خطأ أمني/بيانات/UX كبير.
  - `P1`: مهم جدًا قبل الإنتاج.
  - `P2`: تحسين مهم.
  - `P3`: مستقبلي.
- **التنفيذ:**
  - `NOT STARTED`
  - `PARTIAL`
  - `IMPLEMENTED — NOT TESTED`
  - `TESTED`
  - `FROZEN`: بيانات أو قرار مجمّد.
  - `NEEDS FIX`
  - `CONFLICT`
  - **المواصفات وحدها ليست تنفيذًا.** اختبارات الـWireframes = `PROTOTYPE`.
- **البيانات التجارية:** `APPROVED` · `MISSING` · `PENDING OWNER APPROVAL` · `PENDING VERIFICATION` · `REJECTED` · `SUPERSEDED`. **لا تخمين أبدًا.**

## لقطة الحالة
| المقياس | العدد |
|---|---|
| متطلبات | **1350**: REQUIREMENT 491, RULE 442, DECISION 209, DELIVERABLE 109, GATE 99 |
| حسب الحالة | APPROVED 900, FROZEN 294, APPROVED WITH CONDITIONS 73, PENDING OWNER INPUT 34, DEFERRED 22, SUPERSEDED 18, PENDING VERIFICATION 9 |
| حسب الأولوية | P0 691, P1 572, P2 69, P3 18 |
| حسب التنفيذ | NOT STARTED 830, PARTIAL 227, IMPLEMENTED — NOT TESTED 123, TESTED 116, FROZEN 43, NEEDS FIX 8, IMPLEMENTED — NOT YET VERIFIED 2, CONFLICT 1 |
| مُختبر | NO 684, N/A 337, YES 199, PROTOTYPE 130 |

## فهرس المجالات
| # | المجال | متطلبات | P0 | معتمد/مجمّد | معلّق | تنفيذ (منفذ أو مجمّد) |
|---|---|---|---|---|---|---|
| 01 | [Brand & Design System](master-requirements/01-brand-design-system.md) | 21 | 10 | 20 | 0 | 2 |
| 02 | [Information Architecture](master-requirements/02-information-architecture.md) | 22 | 7 | 17 | 2 | 4 |
| 03 | [Global Website Architecture](master-requirements/03-global-website-architecture.md) | 24 | 15 | 21 | 1 | 5 |
| 04 | [Responsive Design](master-requirements/04-responsive-design.md) | 29 | 7 | 29 | 0 | 1 |
| 05 | [Navigation](master-requirements/05-navigation.md) | 15 | 1 | 12 | 3 | 1 |
| 06 | [Homepage](master-requirements/06-homepage.md) | 5 | 1 | 4 | 1 | 0 |
| 07 | [Menu](master-requirements/07-menu.md) | 62 | 27 | 53 | 6 | 27 |
| 08 | [Products](master-requirements/08-products.md) | 36 | 19 | 34 | 1 | 19 |
| 09 | [Search](master-requirements/09-search.md) | 9 | 1 | 9 | 0 | 2 |
| 10 | [Branches & Locations](master-requirements/10-branches-locations.md) | 31 | 13 | 26 | 4 | 1 |
| 11 | [Hours](master-requirements/11-hours.md) | 13 | 6 | 13 | 0 | 5 |
| 12 | [Campaigns & Events](master-requirements/12-campaigns-events.md) | 7 | 4 | 7 | 0 | 0 |
| 13 | [About](master-requirements/13-about.md) | 4 | 2 | 3 | 1 | 0 |
| 14 | [Contact](master-requirements/14-contact.md) | 30 | 11 | 25 | 4 | 2 |
| 15 | [Blog / Coffee Knowledge](master-requirements/15-blog-coffee-knowledge.md) | 6 | 2 | 6 | 0 | 0 |
| 16 | [Franchise](master-requirements/16-franchise.md) | 108 | 50 | 105 | 1 | 28 |
| 17 | [CMS](master-requirements/17-cms.md) | 35 | 25 | 34 | 1 | 8 |
| 18 | [Owner Dashboard](master-requirements/18-owner-dashboard.md) | 35 | 22 | 33 | 0 | 1 |
| 19 | [Analytics](master-requirements/19-analytics.md) | 45 | 15 | 43 | 2 | 3 |
| 20 | [GA4 / GTM](master-requirements/20-ga4-gtm.md) | 23 | 2 | 18 | 3 | 3 |
| 21 | [Search Console](master-requirements/21-search-console.md) | 13 | 5 | 12 | 1 | 1 |
| 22 | [Google Business Profile & Maps](master-requirements/22-google-business-profile-maps.md) | 15 | 8 | 13 | 1 | 0 |
| 23 | [SEO](master-requirements/23-seo.md) | 41 | 21 | 39 | 1 | 4 |
| 24 | [AEO / GEO / AI Search](master-requirements/24-aeo-geo-ai-search.md) | 3 | 0 | 3 | 0 | 0 |
| 25 | [Schema](master-requirements/25-schema.md) | 10 | 3 | 10 | 0 | 0 |
| 26 | [Performance](master-requirements/26-performance.md) | 23 | 4 | 23 | 0 | 1 |
| 27 | [Accessibility](master-requirements/27-accessibility.md) | 20 | 0 | 20 | 0 | 1 |
| 28 | [Motion](master-requirements/28-motion.md) | 14 | 2 | 14 | 0 | 0 |
| 29 | [Media & Images](master-requirements/29-media-images.md) | 16 | 7 | 15 | 1 | 4 |
| 30 | [Security](master-requirements/30-security.md) | 11 | 8 | 11 | 0 | 0 |
| 31 | [Permissions & Auth](master-requirements/31-permissions-auth.md) | 11 | 6 | 10 | 0 | 1 |
| 32 | [Audit / Versioning](master-requirements/32-audit-versioning.md) | 10 | 10 | 10 | 0 | 1 |
| 33 | [Integrations & Paid Services](master-requirements/33-integrations-paid-services.md) | 16 | 4 | 13 | 1 | 9 |
| 34 | [Cloudflare / Hosting / DNS](master-requirements/34-cloudflare-hosting-dns.md) | 9 | 3 | 9 | 0 | 4 |
| 35 | [Testing & QA](master-requirements/35-testing-qa.md) | 24 | 11 | 24 | 0 | 2 |
| 36 | [Deployment & Production Safety](master-requirements/36-deployment-production-safety.md) | 14 | 13 | 14 | 0 | 6 |
| 37 | [Monitoring & Alerts](master-requirements/37-monitoring-alerts.md) | 13 | 6 | 11 | 0 | 0 |
| 38 | [Documentation & Governance](master-requirements/38-documentation-governance.md) | 91 | 64 | 84 | 0 | 44 |
| 39 | [Content & Copy](master-requirements/39-content-copy.md) | 24 | 8 | 19 | 3 | 10 |
| 40 | [Internationalization (AR/EN · RTL/LTR · Global)](master-requirements/40-internationalization-ar-en-rtl-ltr-global.md) | 15 | 6 | 12 | 3 | 5 |
| 41 | [Tooling](master-requirements/41-tooling.md) | 47 | 19 | 40 | 1 | 11 |
| 42 | [Privacy & Legal](master-requirements/42-privacy-legal.md) | 14 | 9 | 14 | 0 | 2 |
| 43 | [UX Principles](master-requirements/43-ux-principles.md) | 6 | 1 | 6 | 0 | 0 |
| 44 | [Careers & Recruitment](master-requirements/44-careers-recruitment.md) | 99 | 60 | 98 | 1 | 29 |
| 45 | [Dynamic Experience Engine](master-requirements/45-dynamic-experience-engine.md) | 38 | 14 | 38 | 0 | 12 |
| 46 | [Platform Quality & Operations](master-requirements/46-platform-quality-operations.md) | 56 | 39 | 56 | 0 | 4 |
| 47 | [Master Data & Channel Sync](master-requirements/47-master-data-channel-sync.md) | 36 | 34 | 36 | 0 | 6 |
| 48 | [Design System & UI Consistency](master-requirements/48-design-system-ui-consistency.md) | 29 | 29 | 29 | 0 | 1 |
| 49 | [Infrastructure, Release & Operations](master-requirements/49-infrastructure-release-operations.md) | 46 | 38 | 46 | 0 | 9 |
| 50 | [Build Mode & Delivery Governance](master-requirements/50-build-mode-delivery-governance.md) | 26 | 19 | 26 | 0 | 5 |

> **التفاصيل الكاملة لكل متطلب** في ملف مجاله تحت [`master-requirements/`](master-requirements/):
> - النص الكامل، والتفاصيل، والتاريخ (↺ سابقًا)، والتعارضات، والملاحظات، والمصادر.
> - **هذا الملف هو الفهرس والمرجع الرسمي، وملفات المجالات جزء منه.** القسمة لسهولة القراءة فقط، والمحتوى من مصدر واحد مُولّد.

## 01 · Brand & Design System — [التفاصيل](master-requirements/01-brand-design-system.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `BRAND-001` | الاسم الرسمي للعلامة: SHELTER COFFEE / شلتر كوفي | APPROVED | P0 | NOT STARTED |
| `BRAND-002` | الهوية البصرية المعتمدة فقط — ملفاتها مفقودة وتمنع Visual Design (M-10) | SUPERSEDED | P0 | NOT STARTED |
| `BRAND-003` | الموقع الحالي ليس مرجعًا للتصميم أو الـUI أو الـUX أو الحركة | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `BRAND-004` | تصميم Bespoke لعلامة قهوة مختصة عالمية — ليس قالبًا | APPROVED | P1 | NOT STARTED |
| `BRAND-005` | مبادئ التصميم المثبتة (Design Principles) | APPROVED | P1 | PARTIAL |
| `BRAND-006` | ممنوع AI / Vibe-coding look (قائمة الأنماط المحظورة) | APPROVED | P1 | NOT STARTED |
| `BRAND-007` | shadcn/Radix أساس هندسي فقط — ممنوع هوية shadcn الافتراضية | APPROVED | P0 | NOT STARTED |
| `BRAND-008` | Design System كامل (15 مجالًا) | APPROVED | P0 | PARTIAL |
| `BRAND-009` | هيكلية الـDesign System بطبقات | APPROVED | P1 | PARTIAL |
| `BRAND-010` | الحد الأدنى للـDesign Tokens + منع الـhardcode | APPROVED | P0 | PARTIAL |
| `BRAND-011` | Design Lock بعد اعتماد الـDesign System | APPROVED | P0 | NOT STARTED |
| `BRAND-012` | Design System واحد — لا مكونات أو أنظمة تصميم مكررة | APPROVED | P0 | PARTIAL |
| `BRAND-013` | الـPage Editor / CMS لا يسمح بكسر الـDesign System | APPROVED | P0 | NOT STARTED |
| `BRAND-014` | Reusable Sections ضمن الـDesign System | APPROVED | P1 | NOT STARTED |
| `BRAND-015` | Storybook: ورشة مكونات SHELTER (20 مكونًا × 11 حالة) | APPROVED WITH CONDITIONS | P0 | NOT STARTED |
| `BRAND-016` | الأيقونات فقط عندما تحسّن الفهم | APPROVED | P2 | NOT STARTED |
| `BRAND-017` | لا قلب (Mirror) للـLogo في RTL | APPROVED | P1 | NOT STARTED |
| `BRAND-018` | الشكل البصري النهائي للـCTA وBranch Card في مرحلة UX/UI فقط | APPROVED | P1 | NOT STARTED |
| `BRAND-019` | أسلوب تصميم الـOwner Dashboard | APPROVED | P1 | NOT STARTED |
| `BRAND-020` | أنماط ممنوعة في الـDashboard | APPROVED | P1 | NOT STARTED |
| `BRAND-021` | الهوية البصرية من الموقع القديم: الألوان والشعار والخطوط — نفس الثيم | APPROVED | P0 | IMPLEMENTED — NOT YET VERIFIED |

## 02 · Information Architecture — [التفاصيل](master-requirements/02-information-architecture.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `IA-001` | غرض الموقع ونطاقه الحالي | APPROVED | P0 | NOT STARTED |
| `IA-002` | الموقع ليس E-Commerce حاليًا — جاهز للطلب أونلاين لاحقًا | APPROVED | P1 | NOT STARTED |
| `IA-003` | مهام الزائر السريعة | APPROVED | P0 | NOT STARTED |
| `IA-004` | أهم أفعال الزائر بالترتيب | APPROVED | P0 | NOT STARTED |
| `IA-005` | بوابة الصفحات: سؤال الـOwner قبل بناء أي صفحة | PENDING OWNER INPUT | P0 | PARTIAL |
| `IA-006` | صفحة FAQ معتمدة (ضمن قائمة صفحات V1) | APPROVED | P1 | NOT STARTED |
| `IA-007` | صفحة التوظيف (Careers): معتمدة مع إعادة تصميم كاملة | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `IA-008` | صفحة Catering / B2B / Business inquiries / Events services = PAGE RESERVED | APPROVED WITH CONDITIONS | P2 | NOT STARTED |
| `IA-009` | فصل مصطلح Events: حملات SHELTER ≠ Catering / B2B / Private Events | APPROVED | P1 | NOT STARTED |
| `IA-010` | الرعايات: غير معتمدة — MISSING | PENDING OWNER INPUT | P3 | NOT STARTED |
| `IA-011` | صفحة الفريق / SHELTER Family: مؤجلة للنقاش | DEFERRED | P3 | NOT STARTED |
| `IA-012` | النشرة البريدية DEFERRED في V1 — «قائمة الولاء» ليست Loyalty Program | DEFERRED | P3 | NOT STARTED |
| `IA-013` | لا Linktree إذا كان الموقع يقدم تجربة أفضل | APPROVED | P2 | NOT STARTED |
| `IA-014` | لا UI Design قبل اعتماد الـInformation Architecture | SUPERSEDED | P0 | PARTIAL |
| `IA-015` | Hybrid Menu Architecture: صفحة منيو واحدة | FROZEN | P0 | NOT STARTED |
| `IA-016` | لا Product Page مستقلة لكل صنف في V1 | FROZEN | P1 | NOT STARTED |
| `IA-017` | تسلسل مكونات صفحة المنيو | APPROVED | P1 | NOT STARTED |
| `IA-018` | وثيقة SHELTER MENU IA SPEC (Phase B) | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `IA-019` | User Flows للمنيو (Phase D) | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `IA-020` | Low-Fi Wireframes للمنيو: 4 نسخ وحالات مهمة (Phase E) | APPROVED | P1 | TESTED |
| `IA-021` | UX Validation للـWireframes (Phase F) | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `IA-022` | SHELTER OWNER DASHBOARD IA (Tree كاملة) | APPROVED | P0 | NOT STARTED |

## 03 · Global Website Architecture — [التفاصيل](master-requirements/03-global-website-architecture.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `WEB-001` | إعادة بناء كاملة من الصفر لموقع عالمي طويل الأمد | APPROVED | P0 | NOT STARTED |
| `WEB-002` | Global-ready من البداية — غير مرتبط بالأردن | APPROVED | P0 | NOT STARTED |
| `WEB-003` | هرمية Country → City → Branch وجاهزية التوسع | APPROVED | P0 | NOT STARTED |
| `WEB-004` | لا اختراع للتوسع — أي دولة/فرع مستقبلي Hidden حتى موافقة الـOwner | APPROVED | P0 | NOT STARTED |
| `WEB-005` | الدومين الحالي shelterjo.com — لا تغيير ولا Domain Migration الآن | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `WEB-006` | الدومين العالمي المستقبلي — مؤجل | DEFERRED | P3 | NOT STARTED |
| `WEB-007` | Option C — Brand Layer + Market Layer (مبدئي) | APPROVED WITH CONDITIONS | P0 | NOT STARTED |
| `WEB-008` | P3: /ar/ و/en/ + طبقة السوق /ar/jo/… /en/jo/… (مبدئي بشرطين) | APPROVED WITH CONDITIONS | P0 | NOT STARTED |
| `WEB-009` | لا تجميد للـURL/Language Architecture بدون نقاش واعتماد الـOwner | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `WEB-010` | URL Tree كامل + مقارنة ترتيب اللغة/الدولة | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `WEB-011` | معايير الرموز: ar/en · ISO country (jo) · xx للتوثيق فقط | APPROVED | P0 | NOT STARTED |
| `WEB-012` | URLs نظيفة لاتينية: قصيرة · مقروءة · دلالية · SEO Friendly · قابلة للتوسع | APPROVED | P1 | NOT STARTED |
| `WEB-013` | ROOT-01 = D: الجذر / = Global Brand Gateway / x-default — لا 301 إلى /ar/ | APPROVED WITH CONDITIONS | P0 | NOT STARTED |
| `WEB-014` | دراسة الجذر A/B/C/D ثم انتظار قرار الـOwner | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `WEB-015` | وظيفة الجذر: مفيد للمستخدم والبحث والـAI — Header مختصر — ليس نسخة ثالثة | APPROVED | P1 | NOT STARTED |
| `WEB-016` | Wireframe ومحتوى الجذر قبل الـFreeze — لا تنفيذ قبل الموافقة | APPROVED WITH CONDITIONS | P0 | PARTIAL |
| `WEB-017` | لا Geo/IP/Language Redirect تلقائي — لا منع لاختيار English | APPROVED | P0 | NOT STARTED |
| `WEB-018` | URL-02: Slugs الفروع القصيرة drive و house | APPROVED | P1 | NOT STARTED |
| `WEB-019` | قاعدة الفرع الثاني من نفس النوع: drive-{area} / house-{area} (مبدئي) | APPROVED WITH CONDITIONS | P2 | NOT STARTED |
| `WEB-020` | روابط المنيو /ar/jo/menu/ · /en/jo/menu/ + ?branch=drive\|house | APPROVED | P1 | NOT STARTED |
| `WEB-021` | Menu IA س15: ما يكون URL مستقلًا وما يبقى داخل /menu | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `WEB-022` | الموقع ليس E-Commerce في المرحلة الحالية | APPROVED | P0 | NOT STARTED |
| `WEB-023` | جاهزية مستقبلية لـOnline Ordering — FUTURE CAPABILITY ONLY | DEFERRED | P3 | NOT STARTED |
| `WEB-024` | shop.shelterjo.com — فحص وعرض قبل أي قرار | PENDING VERIFICATION | P1 | NOT STARTED |

## 04 · Responsive Design — [التفاصيل](master-requirements/04-responsive-design.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `RESP-001` | Responsive إلزامي للموقع كاملًا وللـOwner Dashboard | APPROVED | P0 | NOT STARTED |
| `RESP-002` | Mobile-First Responsive Architecture | APPROVED | P0 | NOT STARTED |
| `RESP-003` | فئات الأجهزة المدعومة (10 فئات) | APPROVED | P1 | NOT STARTED |
| `RESP-004` | 14 عرضًا إلزاميًا مع اختبار فعلي بالمحتوى الحقيقي | APPROVED | P0 | PARTIAL |
| `RESP-005` | Portrait وLandscape | APPROVED | P1 | PARTIAL |
| `RESP-006` | أساليب الإدخال: Touch وMouse وKeyboard | APPROVED | P1 | PARTIAL |
| `RESP-007` | كل Responsive layout وكل اختبار بالعربي RTL والإنجليزي LTR | APPROVED | P0 | PARTIAL |
| `RESP-008` | Browser zoom 200% بلا فقدان محتوى | APPROVED | P1 | PARTIAL |
| `RESP-009` | No overflow · No clipping · No overlap — أي مشكلة Responsive = BUG | APPROVED | P0 | PARTIAL |
| `RESP-010` | Typography مقروءة: لا clipping ولا tiny fonts ولا سطور طويلة | APPROVED | P1 | NOT STARTED |
| `RESP-011` | Responsive typography scaling مدروس | APPROVED | P1 | NOT STARTED |
| `RESP-012` | القوائم سهلة الاستخدام بيد واحدة على الهاتف | APPROVED | P1 | NOT STARTED |
| `RESP-013` | العناصر اللاصقة والـbanners لا تغطي المحتوى ولا العناصر الأساسية | APPROVED | P0 | NOT STARTED |
| `RESP-014` | شريط إجراءات صفحة الفرع على الموبايل (CT-02) — معايير UX Testing | APPROVED | P1 | NOT STARTED |
| `RESP-015` | Bottom Sheets لا تتجاوز حدود الشاشة | APPROVED | P1 | NOT STARTED |
| `RESP-016` | دعم Safe Area لأجهزة iPhone | APPROVED | P1 | NOT STARTED |
| `RESP-017` | النماذج على الهاتف سهلة ولوحة المفاتيح لا تغطي الحقول | APPROVED | P1 | NOT STARTED |
| `RESP-018` | الجداول على الهاتف: Mobile-friendly بلا إخفاء بيانات مهمة | APPROVED | P1 | NOT STARTED |
| `RESP-019` | Desktop: max-width containers وعدم مط المحتوى | APPROVED | P1 | NOT STARTED |
| `RESP-020` | Desktop: الاستفادة من المساحة الإضافية دون كثافة زائدة | APPROVED | P2 | NOT STARTED |
| `RESP-021` | Tablet حالة مستقلة | APPROVED | P1 | NOT STARTED |
| `RESP-022` | كل Component متجاوب بذاته | APPROVED | P1 | NOT STARTED |
| `RESP-023` | Fluid Responsive System بلا device-specific patching | APPROVED | P1 | NOT STARTED |
| `RESP-024` | المبدأ النهائي: تجربة مصممة لكل جهاز بلا compromises | APPROVED | P1 | NOT STARTED |
| `RESP-025` | Owner Dashboard ممتازة على الهاتف | APPROVED | P0 | PARTIAL |
| `RESP-026` | شبكة المنيو على الموبايل: عمودان من 360px، عمود واحد أفقي تحته | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `RESP-027` | شبكة المنيو على التابلت والديسكتوب: 3 أعمدة حتى 1199px و4 من 1200px | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `RESP-028` | Responsive fallback للقائمة الجانبية على الديسكتوب | APPROVED | P1 | NOT STARTED |
| `RESP-029` | تفويض Phase G: حسم القرارات المتروكة للاختبار بعد الـwireframes | APPROVED WITH CONDITIONS | P1 | TESTED |

## 05 · Navigation — [التفاصيل](master-requirements/05-navigation.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `NAV-001` | دراسة الـHeaders والـMobile Navigation | PENDING OWNER INPUT | P1 | PARTIAL |
| `NAV-002` | اختصارات سريعة: Menu · Locations · Contact | PENDING OWNER INPUT | P1 | NOT STARTED |
| `NAV-003` | دراسة Event Indicator و Campaign Indicator في الـNavigation | PENDING OWNER INPUT | P2 | NOT STARTED |
| `NAV-004` | لا إخفاء للمعلومات أو الوظائف المهمة على الموبايل | APPROVED | P1 | NOT STARTED |
| `NAV-005` | Navigation حسب السياق بنفس الـIA | APPROVED | P1 | NOT STARTED |
| `NAV-006` | ترتيب الـCTA على الموبايل (مبدئي): Menu ثم Locations / Directions | APPROVED WITH CONDITIONS | P0 | NOT STARTED |
| `NAV-007` | المنيو في الـHeader وليس زرًا رابعًا في شريط صفحة الفرع | APPROVED | P1 | NOT STARTED |
| `NAV-008` | دراسة Menu IA تجيب عن أسئلة التنقل 9 و13 | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `NAV-009` | موبايل: Horizontal Sticky Category Bar مع active state تلقائي | FROZEN | P1 | NOT STARTED |
| `NAV-010` | زر «كل الفئات / All Categories» | FROZEN | P1 | NOT STARTED |
| `NAV-011` | وضوح التمرير الأفقي في شريط الفئات | APPROVED | P1 | NOT STARTED |
| `NAV-012` | ديسكتوب: Sticky Side Category Navigation بجانب الشبكة | FROZEN | P1 | NOT STARTED |
| `NAV-013` | Category anchors بـslug conventions نظيفة وثابتة | APPROVED | P1 | NOT STARTED |
| `NAV-014` | روابط الفئات تعمل بدون JavaScript | FROZEN | P1 | NOT STARTED |
| `NAV-015` | Sticky header offset عند القفز للـanchor | APPROVED | P1 | NOT STARTED |

## 06 · Homepage — [التفاصيل](master-requirements/06-homepage.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `HOME-001` | الصفحة الرئيسية معتمدة | APPROVED | P0 | NOT STARTED |
| `HOME-002` | لا Homepage Template عادية — Story Architecture خاصة بـSHELTER | APPROVED | P1 | NOT STARTED |
| `HOME-003` | أسئلة الرئيسية للـOwner ثم اقتراح أفضل User Flow | PENDING OWNER INPUT | P1 | NOT STARTED |
| `HOME-004` | ترتيب الجمهور مدخل فقط — يُشرح أثره قبل قرارات التصميم | APPROVED WITH CONDITIONS | P1 | PARTIAL |
| `HOME-005` | لا إبراز Online Ordering كهدف أساسي حاليًا | APPROVED | P1 | NOT STARTED |

## 07 · Menu — [التفاصيل](master-requirements/07-menu.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `MENU-001` | صفحة المنيو صفحة أساسية معتمدة — الهدف: أفضل Digital Menu لمقهى Specialty Coffee | APPROVED | P0 | NOT STARTED |
| `MENU-002` | مبدأ تجربة المنيو: FAST · VISUAL · SIMPLE · MOBILE-FIRST | APPROVED | P0 | NOT STARTED |
| `MENU-003` | المنيو ليست E-commerce (لا checkout/سلة/دفع في V1) | APPROVED | P1 | NOT STARTED |
| `MENU-004` | Data Model قوي وUX بسيط — لا واجهة تشبه POS ولا عرض كل الحقول | APPROVED | P1 | NOT STARTED |
| `MENU-005` | المنيو الرسمي من الـOwner = PRIMARY MENU SOURCE (المصدر الوحيد للحقيقة) | FROZEN | P0 | FROZEN |
| `MENU-006` | منيو الموقع القديم للمقارنة فقط | APPROVED | P0 | FROZEN |
| `MENU-007` | نطاق الملف الرسمي: مصدر للحقول والأصناف الموجودة فيه فقط | FROZEN | P0 | FROZEN |
| `MENU-008` | معالجة المنيو مسؤولية الفريق: قراءة كاملة، استخراج، كشف، Data Model، تعبئة SSOT | APPROVED | P0 | FROZEN |
| `MENU-009` | فحص جودة بيانات المنيو وفصل أنواع الملاحظات | APPROVED | P0 | FROZEN |
| `MENU-010` | تقرير أولي قبل أي تصميم (11 بندًا) والعرض على الـOwner | APPROVED | P0 | FROZEN |
| `MENU-011` | لا اختراع لبيانات المنيو الناقصة — MISSING — OWNER INPUT REQUIRED | APPROVED | P0 | PARTIAL |
| `MENU-012` | بوابة: التحقق مع الـOwner قبل نشر بيانات المنيو | APPROVED | P0 | NOT STARTED |
| `MENU-013` | لا تغيير لأسماء/أسعار/أوصاف/مكونات/فئات الأصناف بدون موافقة الـOwner | APPROVED | P0 | PARTIAL |
| `MENU-014` | البنية تدعم إظهار الأسعار بالكامل على الموقع | FROZEN | P0 | NOT STARTED |
| `MENU-015` | السعر المعروض = السعر الرسمي من الملف (نهائي شامل الضريبة) بلا حسابات في الواجهة | FROZEN | P0 | PARTIAL |
| `MENU-016` | الضريبة: لا تُضاف مرة ثانية ولا يُعدَّل السعر | FROZEN | P0 | FROZEN |
| `MENU-017` | لا '+ VAT' ولا Net Price أمام العميل | FROZEN | P0 | NOT STARTED |
| `MENU-018` | لا حساب Net Price أو VAT Amount أو Tax Rate الآن | FROZEN | P0 | FROZEN |
| `MENU-019` | جاهزية Tax Metadata بدون إعادة بناء (بدون تعبئة) | APPROVED | P2 | PARTIAL |
| `MENU-020` | تخزين السعر: price_fils (عدد صحيح) · currency = JOD · tax_inclusive = true | FROZEN | P0 | FROZEN |
| `MENU-021` | Menu Version MV-2026-10-01 وتاريخ سريان الأسعار 2026-10-01 | FROZEN | P0 | FROZEN |
| `MENU-022` | جاهزية أنواع الأسعار المستقبلية | APPROVED | P2 | PARTIAL |
| `MENU-023` | لا نظام عروض (Promotions) معقد داخل المنيو قبل مناقشة الـOwner | DEFERRED | P3 | NOT STARTED |
| `MENU-024` | فرق السعر بين DRIVE وHOUSE = نفس Product ID + Branch Price Override | APPROVED | P0 | PARTIAL |
| `MENU-025` | أسعار تطبيقات التوصيل (R2B-03) = MISSING | PENDING OWNER INPUT | P2 | NOT STARTED |
| `MENU-026` | لا افتراض لتطابق الأصناف والأسعار بين DRIVE وHOUSE | APPROVED | P0 | PARTIAL |
| `MENU-027` | حقول التوفر لكل صنف: availability_drive · availability_house | APPROVED | P1 | FROZEN |
| `MENU-028` | التوفر الحالي لكل الأصناف في الفرعين = UNKNOWN | PENDING OWNER INPUT | P1 | PARTIAL |
| `MENU-029` | حالات التوفر للعميل: Available · Unavailable + Show · Unavailable + Hide | FROZEN | P1 | NOT STARTED |
| `MENU-030` | كل مشروب ONE SIZE ONLY — مع بقاء الـData Model قابلًا لدعم الأحجام والـModifiers | APPROVED | P1 | FROZEN |
| `MENU-031` | لا تُعرض Add-ons على الموقع (show_on_website = false) | FROZEN | P1 | FROZEN |
| `MENU-032` | الغياب عن الملف ≠ إلغاء (NOT PRESENT IN CURRENT OWNER FILE) | APPROVED | P1 | FROZEN |
| `MENU-033` | 4 أصناف تُباع وليست في الملف = ACTIVE — DATA INCOMPLETE | PENDING OWNER INPUT | P1 | PARTIAL |
| `MENU-034` | Snacks · Pastries = OWNER VERIFICATION REQUIRED | PENDING VERIFICATION | P2 | NOT STARTED |
| `MENU-035` | الأرقام الناقصة في عمود # تبقى كما هي | FROZEN | P2 | FROZEN |
| `MENU-036` | 11 فئة مصدرية مجمّدة بمعرّفات ثابتة CAT-001 → CAT-011 | FROZEN | P0 | FROZEN |
| `MENU-037` | فصل اسم عرض الفئة عن الـSlug والـInternal ID | APPROVED | P1 | PARTIAL |
| `MENU-038` | التهجئة المعتمدة: SPECIALITY COFFEE (وليس SPECIALTY) | FROZEN | P1 | FROZEN |
| `MENU-039` | ترتيب الفئات للعميل | FROZEN | P1 | TESTED |
| `MENU-040` | SWEETS / حلويات: دمج CAKE + COOKIES بصريًا فقط | FROZEN | P1 | NOT STARTED |
| `MENU-041` | SPRING: الأصناف الخمسة متاحة حاليًا — مع بقاء Seasonal Metadata | APPROVED | P1 | FROZEN |
| `MENU-042` | تواريخ موسم SPRING = MISSING (لا تمنع العرض) | PENDING OWNER INPUT | P2 | NOT STARTED |
| `MENU-043` | SPRING قسم موسمي مستقل عمودي مضغوط أعلى المنيو (لا Carousel) | FROZEN | P1 | NOT STARTED |
| `MENU-044` | عند انتهاء الموسم يختفي القسم ولا تُحذف البيانات | APPROVED | P1 | TESTED |
| `MENU-045` | الأقسام الفرعية: داخل HOT وCOLD وFIZZY فقط وعند تحسين سرعة الوصول | FROZEN | P1 | NOT STARTED |
| `MENU-046` | مقترح الأقسام الفرعية وجدول التوزيع (Phase C) | PENDING OWNER INPUT | P1 | IMPLEMENTED — NOT TESTED |
| `MENU-047` | MENU INVENTORY v1.0 = APPROVED BASELINE (تجميد المعرّفات والـLineage) | FROZEN | P0 | FROZEN |
| `MENU-048` | تقارير ما قبل v1.0 وتقرير الـFreeze المختصر | APPROVED | P0 | FROZEN |
| `MENU-049` | دراسة Menu IA: 15 سؤالًا + OPTION A/B/C + مقارنة + توصية | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `MENU-050` | لا يُعاد فتح Menu IA المعتمدة | FROZEN | P0 | FROZEN |
| `MENU-051` | استخراج كل قرارات المنيو إلى Decision Register بحالة FROZEN/APPROVED | APPROVED | P1 | PARTIAL |
| `MENU-052` | بوابة: اعتماد الـOwner لـMenu IA Spec + Wireframes قبل Visual Design/Production | SUPERSEDED | P0 | IMPLEMENTED — NOT TESTED |
| `MENU-053` | تنقل الفئات: Sticky + قفز فوري + Active Category أثناء التمرير | APPROVED | P1 | NOT STARTED |
| `MENU-054` | لا Infinite Scroll ولا Pagination ولا Load More في صفحة المنيو | FROZEN | P1 | NOT STARTED |
| `MENU-055` | استعادة حالة الصفحة: التمرير والفرع وسياق الفئة | APPROVED | P1 | NOT STARTED |
| `MENU-056` | Lenis لا يُشغَّل على صفحة المنيو إلا إذا أثبت الاختبار فائدته | APPROVED WITH CONDITIONS | P2 | NOT STARTED |
| `MENU-057` | لا Favorites ولا Heart Icons في V1 | FROZEN | P2 | NOT STARTED |
| `MENU-058` | لا زر مشاركة للصنف في V1 (#p-{slug} للـHistory فقط) | FROZEN | P2 | NOT STARTED |
| `MENU-059` | أي Intelligent Recommendation System لاحقًا وليس في V1 | DEFERRED | P3 | NOT STARTED |
| `MENU-060` | Menu Editor في الـDashboard | APPROVED | P0 | PARTIAL |
| `MENU-061` | إدارة التوفر لكل صنف في DRIVE وHOUSE من الـMenu Editor | APPROVED | P0 | NOT STARTED |
| `MENU-062` | Bulk Actions للمنيو | APPROVED | P1 | TESTED |

## 08 · Products — [التفاصيل](master-requirements/08-products.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `PROD-001` | Product ID داخلي ثابت لكل صنف — لا يعتمد على الاسم ولا يتغير | FROZEN | P0 | FROZEN |
| `PROD-002` | Inventory مجمّد: 192 سجل مصدر ← 191 صنفًا فعالًا | FROZEN | P0 | FROZEN |
| `PROD-003` | PRD-00120 = RETIRED/MERGED ولا يُعاد استخدامه · PRD-00115 = Canonical | FROZEN | P0 | FROZEN |
| `PROD-004` | المعرّف التالي لأي صنف جديد: PRD-00193 | FROZEN | P0 | PARTIAL |
| `PROD-005` | تجميد الـID لا يجمّد الحقول — كلها Versioned مع Audit History | FROZEN | P0 | PARTIAL |
| `PROD-006` | عمود # في المصدر = SEQUENTIAL NUMBER ONLY | FROZEN | P0 | FROZEN |
| `PROD-007` | حقلا pos_item_id و external_item_id ثابتان وفارغان حتى توفر الـPOS | APPROVED | P1 | FROZEN |
| `PROD-008` | قواعد الدمج: Preserve first, merge later | APPROVED | P0 | FROZEN |
| `PROD-009` | DUP-01: #115 و#120 = SAME PRODUCT — دمج بحفظ كامل الـLineage | FROZEN | P0 | FROZEN |
| `PROD-010` | DUP-02: AMERICAN COFFEE ≠ AMERICANO · ICED AMERICAN ≠ ICED AMERICANO | FROZEN | P1 | FROZEN |
| `PROD-011` | DUP-03: ESPRESSO MACCHIATO ≠ MACCHIATO | FROZEN | P1 | FROZEN |
| `PROD-012` | DUP-04: أزواج ICED SHAKEN مقابل ICED … LATTE الخمسة = منتجات مختلفة | FROZEN | P0 | FROZEN |
| `PROD-013` | الاسم الإنجليزي المعتمد: ICED SHAKEN SALTED CARAMEL (#67) | FROZEN | P1 | FROZEN |
| `PROD-014` | TURKISH COFFEE: S = Single · D = Double | FROZEN | P1 | FROZEN |
| `PROD-015` | Item #129: الفاكهة الثالثة = MANGO | FROZEN | P1 | FROZEN |
| `PROD-016` | Sugar-Free / Red Bull / Iced Shaken: لا تحويل إلى Product + Variants ولا دمج | FROZEN | P0 | FROZEN |
| `PROD-017` | فصل هوية البيانات عن التجميع في العرض | APPROVED | P0 | FROZEN |
| `PROD-018` | Product Family presentation: مقترح لاحق وقرار الـOwner | DEFERRED | P3 | NOT STARTED |
| `PROD-019` | Source Preservation: طبقات SOURCE / NORMALIZED / DISPLAY | FROZEN | P0 | FROZEN |
| `PROD-020` | لا تصحيح تلقائي للأسماء — Suggested Correction وموافقة الـOwner | APPROVED | P0 | FROZEN |
| `PROD-021` | مجموعات التسمية الإنجليزية المعتمدة G1–G5 | FROZEN | P1 | FROZEN |
| `PROD-022` | الأسماء الإنجليزية للعرض ALL CAPS — أسلوب عرض فقط | FROZEN | P1 | PARTIAL |
| `PROD-023` | الأسماء غير الواضحة الستة تبقى كما هي — PENDING OWNER REVIEW | PENDING OWNER INPUT | P2 | PARTIAL |
| `PROD-024` | Menu Data Model كامل قبل أي UI — الحقول الأساسية (R3) | APPROVED | P0 | PARTIAL |
| `PROD-025` | حقول المصدر والحالة والإصدار الإلزامية (Data Model v0.3→v0.4) | FROZEN | P0 | FROZEN |
| `PROD-026` | حقول Product Data Model من موجز M23 §43 (delta v0.5) | APPROVED | P1 | PARTIAL |
| `PROD-027` | بطاقة الصنف: IMAGE ← الاسم الأساسي ← الثانوي ← PRICE · بلا وصف | FROZEN | P1 | NOT STARTED |
| `PROD-028` | البطاقة تعرض فقط المعلومات المعتمدة — الاسم العربي المعتمد فقط | APPROVED | P0 | NOT STARTED |
| `PROD-029` | السعر يظهر دائمًا داخل البطاقة | FROZEN | P0 | NOT STARTED |
| `PROD-030` | بطاقة بلا صورة مقصودة ونظيفة — لا Placeholder قبيح | APPROVED | P1 | NOT STARTED |
| `PROD-031` | الشارات العامة NEW / SEASONAL فقط — FEATURED داخلي | FROZEN | P2 | NOT STARTED |
| `PROD-032` | حالات المنتج من الـDashboard بدون تعديل الكود | APPROVED | P1 | NOT STARTED |
| `PROD-033` | تفاصيل الصنف: Bottom Sheet على الموبايل · Modal على الديسكتوب | FROZEN | P1 | NOT STARTED |
| `PROD-034` | التفاصيل تعرض الحقول الموجودة والمعتمدة فقط — لا حقول فارغة | FROZEN | P0 | NOT STARTED |
| `PROD-035` | Back (المتصفح/Android) يغلق الـBottom Sheet أولًا | FROZEN | P0 | NOT STARTED |
| `PROD-036` | بعد إغلاق التفاصيل يعود المستخدم لنفس موضع التمرير | FROZEN | P1 | NOT STARTED |

## 09 · Search — [التفاصيل](master-requirements/09-search.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `SRCH-001` | Search سريع داخل المنيو يوصل مباشرة للمنتج بدون Reload | APPROVED | P1 | NOT STARTED |
| `SRCH-002` | مكان البحث: مباشرة تحت عنوان Menu وقبل الفئات | FROZEN | P1 | NOT STARTED |
| `SRCH-003` | اقتراحات سريعة + فلترة مباشرة للشبكة أثناء الكتابة | FROZEN | P1 | NOT STARTED |
| `SRCH-004` | Jump to Product: الانتقال للصنف وإبرازه بلطف بدون Reload | FROZEN | P1 | NOT STARTED |
| `SRCH-005` | البحث بالعربي والإنجليزي بغض النظر عن لغة الصفحة | FROZEN | P0 | NOT STARTED |
| `SRCH-006` | مطابقة البحث: حالة الأحرف · تطبيع العربية · الأخطاء الشائعة · Aliases معتمدة | APPROVED | P1 | TESTED |
| `SRCH-007` | لا synonyms مخترعة أو غير موثوقة | FROZEN | P1 | TESTED |
| `SRCH-008` | بحث بلا نتائج: رسالة + مسح البحث (+ أقرب فئة إن كان منطقيًا) | APPROVED | P1 | NOT STARTED |
| `SRCH-009` | Sticky Search: حقل كامل في البداية ثم أيقونة مضغوطة داخل شريط الفئات | FROZEN | P1 | NOT STARTED |

## 10 · Branches & Locations — [التفاصيل](master-requirements/10-branches-locations.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `BRANCH-001` | بوابة نشر بيانات الفروع: لا نشر قبل تحقق/موافقة الـOwner | APPROVED | P0 | PARTIAL |
| `BRANCH-002` | الفروع العامة الحالية = فرعان فقط (DRIVE وHOUSE) في إربد | APPROVED | P0 | NOT STARTED |
| `BRANCH-003` | هوية فرع DRIVE: الاسم AR/EN والموقع ووجود Drive Thru | APPROVED | P0 | NOT STARTED |
| `BRANCH-004` | هوية فرع HOUSE: الاسم AR/EN والموقع داخل Irbid City Center | APPROVED | P0 | NOT STARTED |
| `BRANCH-005` | العنوان التفصيلي للفرعين (عربي/إنجليزي) — بانتظار التحقق من GBP ثم الـOwner | PENDING VERIFICATION | P1 | NOT STARTED |
| `BRANCH-006` | اسم المول الرسمي لـHOUSE من GBP/موقع المول ثم موافقة الـOwner | PENDING VERIFICATION | P1 | NOT STARTED |
| `BRANCH-007` | مشغل الحلويات = NON-PUBLIC LOCATION | APPROVED | P0 | NOT STARTED |
| `BRANCH-008` | صفحة الفروع (Locations) + صفحة مستقلة لكل فرع | APPROVED | P0 | NOT STARTED |
| `BRANCH-009` | نموذج بيانات الفرع (Branch data model) — الحقول | APPROVED | P1 | NOT STARTED |
| `BRANCH-010` | Branch Editor في الـOwner Dashboard مع Preview قبل Publish | APPROVED | P0 | PARTIAL |
| `BRANCH-011` | إمكانية إضافة فرع Coming Soon بدون إطلاق صفحة كاملة | APPROVED | P2 | NOT STARTED |
| `BRANCH-012` | خدمات الفروع: Data Model فقط — لا استنتاج ولا نشر True/False قبل تأكيد الـOwner | APPROVED | P0 | NOT STARTED |
| `BRANCH-013` | قيم خدمات DRIVE وHOUSE = MISSING — OWNER INPUT REQUIRED | PENDING OWNER INPUT | P1 | NOT STARTED |
| `BRANCH-014` | طرق الدفع: Architecture تدعم 5 طرق — لا نشر قبل تأكيد الـOwner | APPROVED | P0 | NOT STARTED |
| `BRANCH-015` | قيم طرق الدفع لـDRIVE وHOUSE = MISSING — OWNER INPUT REQUIRED | PENDING OWNER INPUT | P1 | NOT STARTED |
| `BRANCH-016` | بطاقة الفرع (Branch Card) تعرض حالة الفرع وساعات العمل | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `BRANCH-017` | شريط صفحة الفرع على الموبايل: [الاتجاهات] [اتصال] [واتساب] | APPROVED | P1 | NOT STARTED |
| `BRANCH-018` | عملية BRANCH DATA SYNC CHECK بين GBP والموقع والـSchema والخرائط | APPROVED | P0 | PARTIAL |
| `BRANCH-019` | متى يُشغَّل Branch Data Sync + فحص اتساق إلزامي عند بناء/تعديل أي صفحة فرع | APPROVED | P1 | PARTIAL |
| `BRANCH-020` | NAP Consistency: الاسم والعنوان والهاتف متطابقة وأي اختلاف يُسجَّل | APPROVED | P0 | NOT STARTED |
| `BRANCH-021` | محدد الفرع في المنيو: كل الفروع / DRIVE / HOUSE — اختياري والافتراضي كل الفروع | FROZEN | P0 | NOT STARTED |
| `BRANCH-022` | Segmented Control 'كل الفروع \| DRIVE \| HOUSE' قابل للتحول إلى Bottom Sheet | FROZEN | P1 | NOT STARTED |
| `BRANCH-023` | موضع محدد الفرع: اختبار A/B بالـWireframe والقرار A (تحت البحث وقبل الفئات) | APPROVED | P1 | TESTED |
| `BRANCH-024` | حفظ آخر اختيار للفرع على الجهاز دون قفل، وأولوية سياق صفحة الفرع | FROZEN | P1 | NOT STARTED |
| `BRANCH-025` | من صفحة الفرع إلى المنيو: ?branch=drive / ?branch=house بدون سؤال إضافي | FROZEN | P1 | NOT STARTED |
| `BRANCH-026` | تغيير الفرع يحافظ على الفئة الحالية وموضع التمرير | FROZEN | P1 | NOT STARTED |
| `BRANCH-027` | تفاصيل الصنف المفتوحة عند تغيير الفرع: 3 حالات | FROZEN | P1 | NOT STARTED |
| `BRANCH-028` | إظهار فرق التوفر بين الفروع فقط عند الحاجة | FROZEN | P1 | NOT STARTED |
| `BRANCH-029` | التوفر لكل فرع غير معروف: لا افتراض — البيانات PENDING OWNER VERIFICATION | APPROVED | P0 | NOT STARTED |
| `BRANCH-030` | اختيار فرع مغلق لا يمنع تصفح المنيو ويعرض Closed now وموعد الافتتاح التالي | FROZEN | P1 | NOT STARTED |
| `BRANCH-031` | زر "اطلب" لكل فرع = قدرة مستقبلية فقط | DEFERRED | P3 | NOT STARTED |

## 11 · Hours — [التفاصيل](master-requirements/11-hours.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `HOURS-001` | ساعات DRIVE العادية: السبت–الخميس 07:00–02:00 · الجمعة 08:00–02:00 | APPROVED | P0 | NOT STARTED |
| `HOURS-002` | ساعات HOUSE العادية: السبت–الأربعاء 09:00–22:00 · الخميس–الجمعة 09:00–23:00 | APPROVED | P0 | NOT STARTED |
| `HOURS-003` | لا فرق بين ساعات الدرايف ثرو والجلسات في DRIVE إلا بمعلومة موثقة وسؤال الـOwner | APPROVED | P1 | NOT STARTED |
| `HOURS-004` | ساعات المول المختلفة أو الخاصة لـHOUSE تُعرض كتعارض ولا تُغيَّر تلقائيًا | APPROVED | P1 | NOT STARTED |
| `HOURS-005` | بنية الساعات: Regular + Special/Holiday + Temporary Closure + Emergency Closure | APPROVED WITH CONDITIONS | P0 | TESTED |
| `HOURS-006` | تعديل رمضان/العيد لا يغيّر الساعات العادية | APPROVED | P1 | TESTED |
| `HOURS-007` | أولوية الحالة الخاصة/المؤقتة على الساعات العادية | APPROVED WITH CONDITIONS | P0 | TESTED |
| `HOURS-008` | تعديل الساعات بسهولة من الـDashboard (Regular ثم Special) | APPROVED | P1 | TESTED |
| `HOURS-009` | حالة الفرع تُحسب ديناميكيًا: Open Now · Closed Now · Closing Soon · Next Opening | FROZEN | P0 | NOT STARTED |
| `HOURS-010` | عرض الحالة مع معلومات ساعات اليوم داخل محدد الفرع (ونصوص AR/EN) | APPROVED | P1 | NOT STARTED |
| `HOURS-011` | يغلق قريبًا: آخر 60 دقيقة قبل الإغلاق مع العد | FROZEN | P1 | NOT STARTED |
| `HOURS-012` | التعامل الصحيح مع ساعات تمتد بعد منتصف الليل ("نقطة مهمة جدًا") | FROZEN | P0 | TESTED |
| `HOURS-013` | Google Special Hours تُقارن مع الموقع ولا تغيير تلقائي على Production | APPROVED | P1 | NOT STARTED |

## 12 · Campaigns & Events — [التفاصيل](master-requirements/12-campaigns-events.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `CAMP-001` | صفحة «الفعاليات والحملات» معتمدة | APPROVED | P1 | NOT STARTED |
| `CAMP-002` | مساحة مخصصة للحملات والفعاليات ضمن الـDesign System — ليست Banner عشوائية | APPROVED | P1 | NOT STARTED |
| `CAMP-003` | العروض والخصومات والفعاليات والحملات تُتحقق مع الـOwner قبل النشر | APPROVED | P0 | NOT STARTED |
| `CAMP-004` | Event / Campaign Manager في الـDashboard — الحقول | APPROVED | P0 | NOT STARTED |
| `CAMP-005` | دورة حياة الحملة: Draft · Scheduled · Active · Expired · Archived | APPROVED | P0 | NOT STARTED |
| `CAMP-006` | الجدولة التلقائية: ظهور واختفاء تلقائي ثم Archive | APPROVED | P0 | NOT STARTED |
| `CAMP-007` | Emergency Announcement سريع | APPROVED | P1 | NOT STARTED |

## 13 · About — [التفاصيل](master-requirements/13-about.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `ABOUT-001` | صفحة «من نحن» معتمدة | APPROVED | P1 | NOT STARTED |
| `ABOUT-002` | معلومات الهوية تُتحقق مع الـOwner قبل النشر | PENDING OWNER INPUT | P0 | NOT STARTED |
| `ABOUT-003` | سنة التأسيس: 2019 | APPROVED | P0 | NOT STARTED |
| `ABOUT-004` | تاريخ السنوية 20/04 واستخداماته | APPROVED | P1 | NOT STARTED |

## 14 · Contact — [التفاصيل](master-requirements/14-contact.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `CONTACT-001` | بوابة نشر بيانات التواصل: الهاتف وواتساب والبريد والحسابات | APPROVED | P0 | PARTIAL |
| `CONTACT-002` | صفحة التواصل (Contact) معتمدة | APPROVED | P0 | NOT STARTED |
| `CONTACT-003` | لا رقم في أي مكان قبل الاعتماد الصريح من الـOwner، ولا وظيفة رقم من مصادر خارجية | APPROVED | P0 | NOT STARTED |
| `CONTACT-004` | التوزيع الرسمي للأرقام (R2P-04 OFFICIAL MAPPING) | FROZEN | P0 | NOT STARTED |
| `CONTACT-005` | 0799009436 = الرقم العام الرئيسي لكل الفروع واستخداماته المسموحة | FROZEN | P0 | NOT STARTED |
| `CONTACT-006` | 0799009436 = رقم WhatsApp الرسمي | FROZEN | P0 | NOT STARTED |
| `CONTACT-007` | 0799338445 = الشكاوى والاقتراحات والفرنشايز — أماكن استخدامه فقط | FROZEN | P0 | NOT STARTED |
| `CONTACT-008` | 0799338445 ليس رقمًا عامًا للفروع ولا يظهر أبدًا في Branch Cards | APPROVED | P0 | NOT STARTED |
| `CONTACT-009` | أي اقتراح مستقبلي لفصل وظائف 0799338445 يُعرض على الـOwner أولًا | APPROVED | P2 | NOT STARTED |
| `CONTACT-010` | 0799530383 = الكيترنج والأعمال والفعاليات — ليس رقمًا عامًا | FROZEN | P0 | NOT STARTED |
| `CONTACT-011` | الحجوزات: لا رقم منفصل؛ أي Reservation flow يُسأل عنه أولًا | DEFERRED | P2 | NOT STARTED |
| `CONTACT-012` | الرعايات: لا رقم ولا قناة تلقائيًا — MISSING | PENDING OWNER INPUT | P2 | NOT STARTED |
| `CONTACT-013` | Contact Architecture حسب نية المستخدم (Intent-based) — لا 3 أرقام بلا سياق | APPROVED | P0 | NOT STARTED |
| `CONTACT-014` | مقترح UX لعرض قنوات التواصل في كل الأسطح (وثيقة 14) | APPROVED WITH CONDITIONS | P1 | PARTIAL |
| `CONTACT-015` | الـFooter غير مزدحم بقنوات التواصل | APPROVED | P1 | NOT STARTED |
| `CONTACT-016` | WhatsApp CTA مناسب للموبايل | APPROVED | P1 | NOT STARTED |
| `CONTACT-017` | أماكن واتساب الأربعة فقط: Branch Card · Branch Page · Contact Page · Footer | APPROVED | P1 | NOT STARTED |
| `CONTACT-018` | ممنوع Floating WhatsApp Bubble ثابتة على كل الصفحات | APPROVED | P1 | NOT STARTED |
| `CONTACT-019` | رسالة واتساب مسبقة حسب الفرع (W-2) — النص النهائي بانتظار الاعتماد | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `CONTACT-020` | صيغة عرض الرقم: عربي 0799009436 · إنجليزي +962 79 900 9436 | APPROVED | P1 | NOT STARTED |
| `CONTACT-021` | روابط tel: وWhatsApp والـSchema بالصيغة الدولية الصحيحة | APPROVED | P1 | NOT STARTED |
| `CONTACT-022` | واتساب والهاتف ليسا قناة طلب رسمية — MISSING | PENDING OWNER INPUT | P1 | NOT STARTED |
| `CONTACT-023` | البريد info@shelterjo.com = PENDING OWNER VERIFICATION — لا يُنشر بعد | PENDING VERIFICATION | P1 | NOT STARTED |
| `CONTACT-024` | دعم مستقبلي لبريد منفصل: Careers · Franchise · Business inquiries | APPROVED | P2 | NOT STARTED |
| `CONTACT-025` | ممنوع إنشاء أو نشر أي بريد غير موجود فعليًا | APPROVED | P0 | NOT STARTED |
| `CONTACT-026` | الحسابات الاجتماعية: لا اعتماد لأي حساب حتى الآن ولا اعتماد تلقائي من البحث | PENDING VERIFICATION | P1 | TESTED |
| `CONTACT-027` | جدول التحقق من الحسابات الاجتماعية وعرضها واحدًا واحدًا | APPROVED | P1 | TESTED |
| `CONTACT-028` | youtube.com/@sheltercoffee لا يُعتبر رسميًا حتى يؤكده الـOwner | APPROVED | P1 | NOT STARTED |
| `CONTACT-029` | لا افتراض لقرار تغيير اسم Instagram أو أي Handle قبل النقاش | APPROVED | P1 | NOT STARTED |
| `CONTACT-030` | Snapchat Place ليس حسابًا اجتماعيًا رسميًا | APPROVED | P2 | NOT STARTED |

## 15 · Blog / Coffee Knowledge — [التفاصيل](master-requirements/15-blog-coffee-knowledge.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `BLOG-001` | قسم BLOG / Coffee Knowledge Hub معتمد | APPROVED | P1 | NOT STARTED |
| `BLOG-002` | Knowledge Hub مترابط وليس Posts عشوائية | APPROVED | P1 | NOT STARTED |
| `BLOG-003` | مواضيع المدونة وبناء Topic Authority | APPROVED | P2 | NOT STARTED |
| `BLOG-004` | لا اختراع للمحتوى — فقط محتوى صحيح ومعتمد | APPROVED | P0 | NOT STARTED |
| `BLOG-005` | حزمة اعتماد المقال قبل النشر — AI لا ينشر من نفسه | APPROVED | P0 | NOT STARTED |
| `BLOG-006` | Content Editor للمقالات — الحقول | APPROVED | P1 | NOT STARTED |

## 16 · Franchise — [التفاصيل](master-requirements/16-franchise.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `FRAN-001` | صفحة Franchise الكاملة: قدرة مستقبلية — غير منشورة حتى اعتماد المحتوى | APPROVED WITH CONDITIONS | P1 | TESTED |
| `FRAN-002` | خيار «Franchise Inquiries / استفسارات الفرنشايز» في صفحة التواصل من يوم الإطلاق | APPROVED | P1 | NOT STARTED |
| `FRAN-003` | ممنوع نشر رسوم أو شروط أو ادعاءات Franchise من عندنا | APPROVED | P0 | NOT STARTED |
| `FRAN-004` | M29 = المرجع التنفيذي الحالي لصفحة الفرنشايز + طلب الشراكة + وحدة الـDashboard | APPROVED | P0 | NOT STARTED |
| `FRAN-005` | ترتيب السلطة الخاص بالفرنشايز (7 مستويات) | APPROVED | P0 | NOT STARTED |
| `FRAN-006` | الصفحة القديمة /franchise-shelter-coffee/ ليست مصدر حقيقة — للهجرة التقنية فقط | APPROVED | P0 | NOT STARTED |
| `FRAN-007` | كل محتوى الفرنشايز التجاري من APPROVED SHELTER FRANCHISE MASTER FILES — ليس من AI | APPROVED | P0 | NOT STARTED |
| `FRAN-008` | المبدأ التجاري: بيع العلامة والنظام — لا وعود مالية | APPROVED | P1 | NOT STARTED |
| `FRAN-009` | هدف الصفحة: تجربة شراكة Premium + قصة + فرصة + تأهيل + طلب + Pipeline | APPROVED | P1 | NOT STARTED |
| `FRAN-010` | الصفحة رحلة: DISCOVER → UNDERSTAND → TRUST → QUALIFY → APPLY | APPROVED | P1 | NOT STARTED |
| `FRAN-011` | عنوان الصفحة: «كن شريكًا مع SHELTER COFFEE» — والتوصيف الثانوي FRANCHISE | APPROVED | P1 | TESTED |
| `FRAN-012` | الـHero: قوي جدًا — عنوان + جملة اتجاه (ليست التزامًا قانونيًا) | APPROVED WITH CONDITIONS | P1 | TESTED |
| `FRAN-013` | استراتيجية الـCTA: «ابدأ طلب الشراكة» في 3 مواضع بلا إزعاج | APPROVED | P1 | TESTED |
| `FRAN-014` | حقائق العلامة المعتمدة للصفحة: 2019 · 20/04 · إربد · DRIVE/HOUSE | APPROVED | P0 | NOT STARTED |
| `FRAN-015` | قسم «من هي SHELTER؟»: حقائق معتمدة فقط — بلا ادعاءات تفوق | APPROVED | P0 | TESTED |
| `FRAN-016` | DRIVE/HOUSE = «نماذج تجربة SHELTER الحالية» — وليست باقات فرنشايز | APPROVED | P0 | TESTED |
| `FRAN-017` | Global-ready بلا إعلان أي دولة/مدينة/Territory متاحة | APPROVED | P0 | NOT STARTED |
| `FRAN-018` | الصفحة كاملة AR (RTL) وEN (LTR) — بلا ترجمة حرفية | APPROVED | P0 | TESTED |
| `FRAN-019` | الروابط: /ar/franchise/ و /en/franchise/ (طبقة العلامة — مبدئي) | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `FRAN-020` | محتوى قديم يُحذف ولا يُعاد استخدامه (9 بنود) | APPROVED | P0 | NOT STARTED |
| `FRAN-021` | محتوى قديم يبقى كفكرة ويُعاد كتابته (8 بنود) — بعد المطابقة | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `FRAN-022` | حظر نشر الشروط المالية (19 بندًا) بدون موافقة من مشروع Franchise | APPROVED | P0 | TESTED |
| `FRAN-023` | الـIA الأساسية للصفحة: 13 قسمًا | APPROVED | P1 | NOT STARTED |
| `FRAN-024` | قصة بصرية متسلسلة — لا Section dump | APPROVED | P1 | NOT STARTED |
| `FRAN-025` | صور الـHero والصفحة: صور SHELTER حقيقية معتمدة فقط | APPROVED | P0 | NOT STARTED |
| `FRAN-026` | «لماذا تصبح شريكًا مع SHELTER؟»: 9 ركائز = PENDING VERIFICATION حتى المطابقة | APPROVED WITH CONDITIONS | P0 | PARTIAL |
| `FRAN-027` | Visual hierarchy متنوع — لا 9 بطاقات متطابقة ولا شكل Admin Dashboard | APPROVED | P1 | NOT STARTED |
| `FRAN-028` | قسم «أكثر من مجرد اسم على الواجهة» — منظومة لا ترخيص شعار | APPROVED WITH CONDITIONS | P1 | TESTED |
| `FRAN-029` | رحلة الدعم (Support Journey) — 9 مراحل — Franchise Master Process يفوز | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `FRAN-030` | «ما الذي نبحث عنه في الشريك؟»: 7 معايير عامة قابلة للعرض | APPROVED WITH CONDITIONS | P1 | TESTED |
| `FRAN-031` | ممنوع نشر اشتراطات الشريك المحددة حتى اعتمادها | APPROVED | P0 | TESTED |
| `FRAN-032` | رحلة الشراكة العامة (9 خطوات) — تُطابق مع Franchise Master قبل النشر | APPROVED WITH CONDITIONS | P1 | TESTED |
| `FRAN-033` | لا وعد بمدة رد (لا SLA) — الصياغة البديلة المعتمدة | APPROVED | P0 | NOT STARTED |
| `FRAN-034` | قسم «أسواق النمو / Where We Grow» عام — Jordan + International Growth بلا Territories | APPROVED WITH CONDITIONS | P1 | TESTED |
| `FRAN-035` | FAQ خاص بالفرنشايز — 10 مواضيع | APPROVED | P1 | TESTED |
| `FRAN-036` | إجابات FAQ: لا إجابات تجارية مخترعة — الصياغة المحايدة المعتمدة | APPROVED | P0 | TESTED |
| `FRAN-037` | الروابط الداخلية: About · Locations · Menu · Coffee Knowledge · Contact | APPROVED | P2 | NOT STARTED |
| `FRAN-038` | SHELTER PARTNERSHIP APPLICATION أصلي: لا HubSpot/Google Forms/Airtable/CRM — Cloudways | APPROVED | P0 | NOT STARTED |
| `FRAN-039` | لا قاعدة بيانات محلية — لا Falcon — كل البيانات Server-side | APPROVED | P0 | NOT STARTED |
| `FRAN-040` | Cloudways audit قبل التنفيذ — بلا عبث بـWordPress القديم أو Production DB | APPROVED | P0 | NOT STARTED |
| `FRAN-041` | فلسفة النموذج: Lead Qualification محترف — ليس طويلًا ولا استجوابًا ماليًا | APPROVED | P1 | NOT STARTED |
| `FRAN-042` | بيانات الطلب الأولية (11 حقلًا) — النموذج ثنائي اللغة | APPROVED WITH CONDITIONS | P1 | TESTED |
| `FRAN-043` | حقول مستقبلية (5) — أي حقل مالي/استثماري لا يُنشر بلا موافقة | DEFERRED | P3 | NOT STARTED |
| `FRAN-044` | FRANCHISE APPLICATION FIELD MATRIX قبل التنفيذ النهائي للنموذج | APPROVED | P0 | PARTIAL |
| `FRAN-045` | Single Structured Form مقابل Short Multi-step — يُختار الأقل Friction بالاختبار | APPROVED | P1 | NOT STARTED |
| `FRAN-046` | رقم طلب فريد FR-YYYY-NNNNN — يُولد في الخادم، قابل للبحث، بلا PII | APPROVED | P0 | TESTED |
| `FRAN-047` | صفحة النجاح: شكر + رقم الطلب + المراجعة — بلا أي وعد | APPROVED | P0 | TESTED |
| `FRAN-048` | Disclaimer قانوني: الإرسال ليس موافقة ولا التزامًا تعاقديًا — الصياغة النهائية معلقة | PENDING OWNER INPUT | P0 | TESTED |
| `FRAN-049` | أمان النموذج العام (8 ضوابط) — بلا CAPTCHA خارجي تلقائيًا | APPROVED | P0 | NOT STARTED |
| `FRAN-050` | الخصوصية: لا تحويل للنشرة، لا Marketing opt-in افتراضي، موافقة واضحة | APPROVED | P0 | TESTED |
| `FRAN-051` | المرفقات: مستقبلية واختيارية — غير إلزامية في V1 — تخزين خاص فقط | DEFERRED | P2 | NOT STARTED |
| `FRAN-052` | لا وثائق داخلية في الحزمة العامة للموقع | APPROVED | P0 | NOT STARTED |
| `FRAN-053` | Attribution آمن للخصوصية يُخزن مع الطلب | APPROVED | P1 | TESTED |
| `FRAN-054` | وحدة «Franchise & Partnerships» أصلية في الـOwner Dashboard — ليست iframe | APPROVED | P1 | PARTIAL |
| `FRAN-055` | IA مبدئية للوحدة (11 عنصرًا) — ليست الحالات النهائية؛ Franchise Master يفوز | APPROVED WITH CONDITIONS | P1 | TESTED |
| `FRAN-056` | أعمدة قائمة الطلبات المبدئية (7) | APPROVED | P1 | TESTED |
| `FRAN-057` | Quick Side Panel + Full Application Page | APPROVED | P1 | PARTIAL |
| `FRAN-058` | ملاحظات داخلية متعددة — الكاتب والوقت والمحتوى — بلا Overwrite صامت | APPROVED | P1 | TESTED |
| `FRAN-059` | إدارة الاجتماعات — الملاحظات الداخلية لا تُعرض للمتقدم | APPROVED | P1 | TESTED |
| `FRAN-060` | الطلبات المكررة: لا منع تلقائي، كشف بالهاتف/البريد/اسم الشركة، بلا دمج | APPROVED | P1 | PARTIAL |
| `FRAN-061` | لا قبول ولا رفض آلي — لا تأهيل أو تقييم استثماري آلي | APPROVED | P0 | TESTED |
| `FRAN-062` | الصلاحيات: بيانات تجارية حساسة — V1 للـOwner فقط — Server-side | FROZEN | P0 | TESTED |
| `FRAN-063` | تحليلات الوحدة: القيمة التشغيلية أولًا — لا 30 رسمًا | APPROVED | P2 | PARTIAL |
| `FRAN-064` | البحث والفلاتر في وحدة الشراكات | APPROVED | P1 | TESTED |
| `FRAN-065` | Audit Log للوحدة: 8 أنواع إجراءات + 6 حقول | APPROVED | P0 | PARTIAL |
| `FRAN-066` | النسخ الاحتياطي: DB + مرفقات خاصة + إجراء استعادة — يُتحقق ولا يُفترض | APPROVED | P0 | NOT STARTED |
| `FRAN-067` | الصفحة قابلة للتحرير بالكامل من الـOwner Dashboard بلا كود (16 عنصرًا) | APPROVED | P1 | PARTIAL |
| `FRAN-068` | Design System Lock: المحتوى قابل للتحرير — التصميم مضبوط | APPROVED | P0 | NOT STARTED |
| `FRAN-069` | Workflow المحتوى: Draft · Review · Scheduled · Published · Archived + Preview + Versions + Audit | APPROVED | P1 | NOT STARTED |
| `FRAN-070` | حالة لكل محتوى فرنشايز: APPROVED · PENDING · MISSING · SUPERSEDED — المعلّق لا يُنشر كحقيقة | APPROVED | P0 | NOT STARTED |
| `FRAN-071` | Versioning إلزامي لمحتوى الفرنشايز الحساس (7 أنواع) | APPROVED | P1 | NOT STARTED |
| `FRAN-072` | وسائط الصفحة معتمدة من الـOwner + حقول Media Library | APPROVED | P0 | NOT STARTED |
| `FRAN-073` | الاتجاه البصري: Premium · Warm · Minimal · Architectural · Modern · Confident · Global · Coffee-led | APPROVED | P1 | NOT STARTED |
| `FRAN-074` | ممنوعات التصميم لصفحة الفرنشايز (8) | APPROVED | P1 | NOT STARTED |
| `FRAN-075` | الـVisual Concept السابق: اتجاه فقط — «أسود/كريمي» ليس Brand Tokens | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `FRAN-076` | Motion Premium هادف — بلا Scroll hijack — مع prefers-reduced-motion | APPROVED | P1 | NOT STARTED |
| `FRAN-077` | أدوات الحركة: الـStack الحالي فقط — لا مكتبة حركة مكررة | APPROVED | P1 | NOT STARTED |
| `FRAN-078` | الأدوات: أدوات المشروع الحالية — بلا بدائل مكررة | APPROVED | P2 | NOT STARTED |
| `FRAN-079` | Responsive إلزامي: 14 عرضًا · 5 فئات أجهزة · RTL/LTR · Portrait/Landscape · 200% Zoom | APPROVED | P0 | NOT STARTED |
| `FRAN-080` | الموبايل: Mobile-first وليس Desktop مصغرًا (11 قاعدة) | APPROVED | P0 | NOT STARTED |
| `FRAN-081` | التابلت حالة UX مستقلة | APPROVED | P1 | NOT STARTED |
| `FRAN-082` | الديسكتوب يستفيد من المساحة — بحاويات max-width | APPROVED | P1 | NOT STARTED |
| `FRAN-083` | Accessibility: 14 بندًا (بما فيها ملخص أخطاء النموذج وFAQ) | APPROVED | P0 | NOT STARTED |
| `FRAN-084` | الأداء: السرعة أهم من البصريات — LCP/INP/CLS — بلا فيديو ضخم على الموبايل | APPROVED | P0 | NOT STARTED |
| `FRAN-085` | تحسين كل صورة معتمدة + أولوية تحميل صورة الـLCP | APPROVED | P1 | NOT STARTED |
| `FRAN-086` | Performance Budget واقعي للصفحة — بلا Score عشوائي | APPROVED | P1 | PARTIAL |
| `FRAN-087` | سكربتات الطرف الثالث لا تؤخر الصفحة — تدقيق HubSpot قبل أي إزالة/إبقاء | APPROVED | P0 | NOT STARTED |
| `FRAN-088` | نية البحث (AR 7 · EN 4) — بلا Keyword Stuffing | APPROVED | P1 | NOT STARTED |
| `FRAN-089` | AEO/GEO: عناوين واقعية وحقائق مختصرة وFAQ وعلاقات كيان — بلا خداع | APPROVED | P1 | NOT STARTED |
| `FRAN-090` | Structured Data: Organization · WebPage · BreadcrumbList · FAQPage (عند الملاءمة) — ممنوع اختراع Offer/Price/Rating/Review/Investment/Availability | APPROVED | P0 | NOT STARTED |
| `FRAN-091` | أحداث Analytics للفرنشايز (7) — ذات معنى فقط | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `FRAN-092` | لا PII في الـAnalytics | APPROVED | P0 | NOT STARTED |
| `FRAN-093` | هجرة الرابط القديم /franchise-shelter-coffee/: لا Redirect الآن — 10 فحوص — 301 بعد موافقة الـOwner | APPROVED | P0 | NOT STARTED |
| `FRAN-094` | Canonical + hreflang + x-default للزوج AR/EN — بلا تحويل جغرافي تلقائي | APPROVED | P0 | NOT STARTED |
| `FRAN-095` | منظومة Google: تدقيق الموجود أولًا — بلا تتبع مكرر — الصفحة في Sitemap/GSC/Analytics/المراقبة | APPROVED | P1 | NOT STARTED |
| `FRAN-096` | نبرة المحتوى AR/EN | APPROVED | P1 | NOT STARTED |
| `FRAN-097` | كلمات ممنوعة (6) ومفضلة (7) | APPROVED | P0 | NOT STARTED |
| `FRAN-098` | مصفوفة QA للصفحة: AR/EN · 10 عروض (ضمن الـ14) · اتجاهان · Zoom · 3 محركات · لمس/كيبورد/حركة مخفضة | APPROVED | P0 | NOT STARTED |
| `FRAN-099` | تدفقات Playwright (13 على الأقل) | APPROVED | P0 | NOT STARTED |
| `FRAN-100` | SEO QA للصفحة (12 فحصًا) | APPROVED | P1 | NOT STARTED |
| `FRAN-101` | Analytics QA: 6 تحققات — بلا أحداث مكررة — بلا PII | APPROVED | P1 | NOT STARTED |
| `FRAN-102` | Definition of Done لصفحة الفرنشايز (21 شرطًا) | APPROVED | P0 | NOT STARTED |
| `FRAN-103` | لا تغييرات Production في المرحلة الأولى — وثائق وWireframes فقط | APPROVED | P0 | NOT STARTED |
| `FRAN-104` | مخرجات المرحلة الأولى (20) ثم التوقف قبل التنفيذ الكامل | APPROVED | P0 | PARTIAL |
| `FRAN-105` | ترتيب البدء (9 خطوات) + ملخص تنفيذي | APPROVED | P1 | PARTIAL |
| `FRAN-106` | لا أسئلة صغيرة للـOwner — Claude يتخذ أفضل قرار تقني | APPROVED | P2 | NOT STARTED |
| `FRAN-107` | سؤال الـOwner فقط عن القرارات التجارية الحقيقية (12) | APPROVED | P1 | NOT STARTED |
| `FRAN-108` | Master Project Sync: كل قرار ← Master/Decision Register/Traceability — والتعارض والنواقص في سجلاتها | APPROVED | P0 | PARTIAL |

## 17 · CMS — [التفاصيل](master-requirements/17-cms.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `CMS-001` | 95%+ من العمليات اليومية يديرها الـOwner من الـDashboard بدون لمس الكود | APPROVED | P0 | PARTIAL |
| `CMS-002` | نطاق كيانات الـCMS (القائمة الموحدة القابلة للتعديل من UI) | APPROVED | P0 | NOT STARTED |
| `CMS-003` | Menu CMS = Single Source of Truth في V1 (A الآن + جاهزية C) — لا Google Sheet | APPROVED | P0 | NOT STARTED |
| `CMS-004` | مصدر واحد للحقيقة: لا CMS مزدوج ولا مصدر منيو مزدوج | APPROVED | P0 | NOT STARTED |
| `CMS-005` | إدارة المنيو بعد الإطلاق من الـDashboard | APPROVED | P0 | NOT STARTED |
| `CMS-006` | Workflow لكل المحتوى: DRAFT · IN REVIEW · SCHEDULED · PUBLISHED · ARCHIVED | APPROVED | P0 | PARTIAL |
| `CMS-007` | لا يصل أي تعديل للعميل قبل Publish — لا تعديل مباشر على Production | APPROVED | P0 | NOT STARTED |
| `CMS-008` | Preview قبل Publish: Desktop · Mobile (+ Arabic · English إذا أمكن) | APPROVED | P0 | NOT STARTED |
| `CMS-009` | Scheduling والنشر الموسمي: تفعيل/انتهاء تلقائي · تجاوز يدوي · Audit trail | FROZEN | P1 | TESTED |
| `CMS-010` | Page Editor بسيط مبني على Sections/Blocks (ليس Page Builder مثل Elementor) | APPROVED | P0 | TESTED |
| `CMS-011` | Design Lock: الـCMS لا يسمح بكسر الـDesign System | APPROVED | P0 | PARTIAL |
| `CMS-012` | Global Components تُدار من مكان واحد | APPROVED | P1 | NOT STARTED |
| `CMS-013` | نظام Special Hours في الـCMS (Regular · Special/Holiday · Closure/Emergency) | APPROVED | P0 | TESTED |
| `CMS-014` | مناقشة UX والـCMS لنظام الساعات الخاصة مع الـOwner قبل التنفيذ | PENDING OWNER INPUT | P1 | NOT STARTED |
| `CMS-015` | التوفر لكل Product × Branch: Available · Unavailable + Show · Unavailable + Hide | FROZEN | P0 | NOT STARTED |
| `CMS-016` | ترتيب المنتجات بـsort_order يدوي من الـCMS — ممنوع Random Algorithm | FROZEN | P1 | NOT STARTED |
| `CMS-017` | FEATURED = خاصية CMS داخلية — لا Badge 'Featured' للعميل | FROZEN | P1 | TESTED |
| `CMS-018` | Search Alias Dictionary قابل للإدارة من الـCMS | FROZEN | P1 | TESTED |
| `CMS-019` | حالة المحتوى في الـDashboard: صفحات تحتاج تحديث + Drafts تنتظر النشر | APPROVED | P1 | TESTED |
| `CMS-020` | لا تعديل يدوي لقاعدة البيانات: كل Business Content اليومي له UI | APPROVED | P0 | NOT STARTED |
| `CMS-021` | CMS Data Model والتخزين (CMS-DATA-MODEL.md) — Supabase مشروط بقرار DB-08 | APPROVED WITH CONDITIONS | P1 | PARTIAL |
| `CMS-022` | المنيو بلا كود: كل حقول المنتج والتوفر والنشر | FROZEN | P0 | PARTIAL |
| `CMS-023` | الوسائط بلا كود | FROZEN | P0 | PARTIAL |
| `CMS-024` | الصفحات بلا كود | FROZEN | P0 | TESTED |
| `CMS-025` | الفروع والساعات بلا كود | FROZEN | P0 | PARTIAL |
| `CMS-026` | الحملات والفعاليات بلا كود | FROZEN | P0 | PARTIAL |
| `CMS-027` | المدونة / المعرفة بلا كود | FROZEN | P1 | NOT STARTED |
| `CMS-028` | التوظيف بلا كود | FROZEN | P0 | PARTIAL |
| `CMS-029` | الشراكات / الفرنشايز بلا كود | FROZEN | P1 | NOT STARTED |
| `CMS-030` | معلومات التواصل بلا كود | FROZEN | P0 | TESTED |
| `CMS-031` | الـSEO بلا كود (والمتقدم محمي) | FROZEN | P0 | NOT STARTED |
| `CMS-032` | الإعدادات العامة للموقع بلا كود | FROZEN | P0 | NOT STARTED |
| `CMS-033` | قاعدة لا-كود: أي تغيير عادي يحتاج كودًا = مشكلة معمارية | FROZEN | P0 | NOT STARTED |
| `CMS-034` | حماية نظام التصميم: محتوى حر + خيارات مضبوطة فقط | FROZEN | P0 | NOT STARTED |
| `CMS-035` | مسودة ← معاينة ← نشر ← جدولة ← أرشفة للتغييرات المهمة | FROZEN | P0 | NOT STARTED |

## 18 · Owner Dashboard — [التفاصيل](master-requirements/18-owner-dashboard.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `DASH-001` | Owner Dashboard = SHELTER Website Control Center (جزء أساسي من المشروع) | APPROVED | P0 | NOT STARTED |
| `DASH-002` | مبدأ الـDashboard: فهم خلال ثوانٍ + WHAT/WHERE/WHY/ATTENTION + Action | APPROVED | P0 | NOT STARTED |
| `DASH-003` | أولويات الـDashboard لـVersion 1 (P0 / P1 / P2 حسب M25 §68) | APPROVED | P0 | NOT STARTED |
| `DASH-004` | تسلسل تنفيذ الـDashboard (Phases A–N) — لا بناء قبل Gate H | APPROVED | P0 | NOT STARTED |
| `DASH-005` | Low-Fidelity Wireframes للـDashboard قبل الـCoding (12 شاشة) | APPROVED | P0 | NOT STARTED |
| `DASH-006` | وحدات الـDashboard والتنقل — القائمة الدنيا الكاملة (M25 §49 ∪ M27 §13) | APPROVED | P0 | PARTIAL |
| `DASH-007` | الشاشة الأولى بعد Login = Executive Dashboard (Owner KPIs فقط) | APPROVED | P0 | PARTIAL |
| `DASH-008` | TOP SUMMARY: 10 مؤشرات + مقارنة + percentage/trend arrow/mini chart | APPROVED | P0 | NOT STARTED |
| `DASH-009` | Date Selector عام أعلى الـDashboard مع Presets | APPROVED | P0 | NOT STARTED |
| `DASH-010` | Comparison: previous period / previous year — فقط إذا البيانات متوفرة | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `DASH-011` | وحدات Analytics في الـDashboard (حد أدنى) | APPROVED | P0 | NOT STARTED |
| `DASH-012` | وحدات Click / Customer Actions: WhatsApp · Phone · Directions · Branch | APPROVED | P0 | NOT STARTED |
| `DASH-013` | وحدات SEO: Search Console · SEO Health · Indexation | APPROVED | P0 | NOT STARTED |
| `DASH-014` | وحدات الأداء والصحة: CWV · Lighthouse · Site Health · Errors · Accessibility | APPROVED | P0 | NOT STARTED |
| `DASH-015` | وحدة Media Health (صحة الصور والوسائط) | APPROVED | P1 | NOT STARTED |
| `DASH-016` | Page Health View لكل صفحة | APPROVED | P1 | NOT STARTED |
| `DASH-017` | Needs Attention (Command Center) أعلى الـOverview — CRITICAL/WARNING/INFO | APPROVED | P0 | NOT STARTED |
| `DASH-018` | كل Metric أو Alert قابل للتنفيذ عبر Deep Link | APPROVED | P0 | NOT STARTED |
| `DASH-019` | Global Search داخل الـDashboard عبر كل الكيانات | APPROVED | P1 | NOT STARTED |
| `DASH-020` | لغة سهلة للـOwner + Advanced Details للمطور | APPROVED | P1 | NOT STARTED |
| `DASH-021` | مصادر بيانات الـDashboard: APIs رسمية فقط · لا APIs ثقيلة أو مدفوعة بدون موافقة | APPROVED | P1 | NOT STARTED |
| `DASH-022` | Data freshness indicators + زر Refresh (مع Caching من جهة الخادم) | APPROVED | P1 | NOT STARTED |
| `DASH-023` | Graceful degradation: الـDashboard لا تنهار إذا توقفت خدمة خارجية | APPROVED | P0 | NOT STARTED |
| `DASH-024` | Labels واضحة: DEMO DATA مقابل LIVE DATA — ممنوع الخلط | APPROVED | P0 | NOT STARTED |
| `DASH-025` | لا أرقام مزيفة: أي Score يوضح طريقة حسابه | APPROVED | P0 | TESTED |
| `DASH-026` | Summary Health Score اختياري — فقط بقواعد واضحة؛ الـIssues أهم من الرقم | SUPERSEDED | P2 | NOT STARTED |
| `DASH-027` | الـOwner Dashboard كاملة الوظائف على Mobile و Desktop | APPROVED | P0 | PARTIAL |
| `DASH-028` | 9 مهام Owner يجب أن تكون سهلة على الهاتف | APPROVED | P1 | NOT STARTED |
| `DASH-029` | Dashboard cards لا تنضغط بشكل سيئ على الهاتف · Desktop أكثر كثافة عند الفائدة | APPROVED | P1 | NOT STARTED |
| `DASH-030` | Charts متجاوبة — وتتحول إلى Summary/Card على الشاشات الصغيرة عند الحاجة | APPROVED | P1 | NOT STARTED |
| `DASH-031` | AI Assistant داخل الـDashboard — مستقبلي (Architecture جاهزة فقط إن لم تعقّد) | DEFERRED | P2 | NOT STARTED |
| `DASH-032` | V1: لوحة التحكم للـOwner فقط — لا مستخدمين آخرين | FROZEN | P0 | NOT STARTED |
| `DASH-033` | لا حاجة لفتح ملفات المصدر أو GitHub أو قاعدة البيانات للإدارة اليومية | FROZEN | P0 | NOT STARTED |
| `DASH-034` | لغة صاحب العمل لا لغة المطور (+ قسم Advanced) | FROZEN | P1 | NOT STARTED |
| `DASH-035` | معيار النجاح النهائي: 95%+ من العمليات اليومية بلا كود | FROZEN | P0 | NOT STARTED |

## 19 · Analytics — [التفاصيل](master-requirements/19-analytics.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `ANL-001` | جرد التتبع والتكاملات الحالية في الموقع القديم | APPROVED | P1 | PARTIAL |
| `ANL-002` | فحص Duplicate Tracking في الموقع القديم والجديد | APPROVED | P0 | NOT STARTED |
| `ANL-003` | مصدر واحد لكل آلية تتبع — نظام Analytics واحد بلا GA4/GTM مكرر | APPROVED | P0 | NOT STARTED |
| `ANL-004` | لا حذف لأي Duplicate (Tag/Property/Tracking) بدون موافقة الـOwner | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `ANL-005` | Measurement Plan للموقع قبل تنفيذ أي Tracking | APPROVED | P1 | PARTIAL |
| `ANL-006` | تتبع ذو معنى تجاري فقط — لا Tracking بلا داعٍ ولا كل Click | APPROVED | P1 | NOT STARTED |
| `ANL-007` | توثيق كل Event بحقول ثابتة | APPROVED | P1 | PARTIAL |
| `ANL-008` | Event Taxonomy موحدة قبل التنفيذ — القائمة الـcanonical للأحداث | APPROVED WITH CONDITIONS | P1 | NEEDS FIX |
| `ANL-009` | الأسماء النهائية للأحداث تُعرض على الـOwner قبل الاعتماد | APPROVED | P1 | PARTIAL |
| `ANL-010` | قاعدة إعادة التسمية التقنية: نفس المعنى + توثيق أي تعديل | APPROVED | P2 | IMPLEMENTED — NOT TESTED |
| `ANL-011` | Menu Measurement Plan (Phase I) — أحداث المنيو الستة | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `ANL-012` | حدث `menu_view` | APPROVED | P1 | NOT STARTED |
| `ANL-013` | حدث `menu_search` | APPROVED | P1 | NOT STARTED |
| `ANL-014` | حدث `zero_result_search` | APPROVED | P1 | NOT STARTED |
| `ANL-015` | حدث `menu_category_click` | APPROVED | P1 | NOT STARTED |
| `ANL-016` | حدث `product_view` | APPROVED | P1 | NOT STARTED |
| `ANL-017` | حدث `branch_filter_change` | APPROVED | P1 | NOT STARTED |
| `ANL-018` | حدث `directions_click` | APPROVED | P1 | NOT STARTED |
| `ANL-019` | حدث `phone_click` | APPROVED | P1 | NOT STARTED |
| `ANL-020` | حدث `whatsapp_click` | APPROVED | P1 | NOT STARTED |
| `ANL-021` | حدث `campaign_view` | APPROVED | P1 | NOT STARTED |
| `ANL-022` | حدث `campaign_click` | APPROVED | P1 | NOT STARTED |
| `ANL-023` | حدث `article_view` | APPROVED | P1 | NOT STARTED |
| `ANL-024` | حدث `language_switch` | APPROVED | P1 | NOT STARTED |
| `ANL-025` | حدث `site_search` | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `ANL-026` | أحداث مرشحة بانتظار قرار: branch_view · social_click · event_view | PENDING OWNER INPUT | P1 | NOT STARTED |
| `ANL-027` | Search Analytics: ما يجب معرفته من البحث | APPROVED | P1 | NOT STARTED |
| `ANL-028` | قاموس Event Parameters موحد بتسمية ثابتة | PENDING OWNER INPUT | P1 | NEEDS FIX |
| `ANL-029` | استخدام بيانات GA4 الفعلية بدل التخمين — دون أن تقرر التصميم وحدها | APPROVED | P2 | NOT STARTED |
| `ANL-030` | لا Business Conclusions غير مدعومة بالبيانات | APPROVED | P0 | NOT STARTED |
| `ANL-031` | الـDashboard تجيب أسئلة الـOwner التحليلية (محتوى · أفعال · جمهور) | APPROVED | P0 | NOT STARTED |
| `ANL-032` | Traffic Analytics | APPROVED | P0 | NOT STARTED |
| `ANL-033` | Traffic Sources: القنوات + Source/Medium + جودة القناة | APPROVED WITH CONDITIONS | P0 | NOT STARTED |
| `ANL-034` | Top Pages مع Drill-down | APPROVED | P0 | NOT STARTED |
| `ANL-035` | Menu Analytics Dashboard (مهمة جدًا لـSHELTER) | APPROVED | P0 | NOT STARTED |
| `ANL-036` | Click / Event Analytics Dashboard | APPROVED | P0 | NOT STARTED |
| `ANL-037` | Customer Actions (CTA Analytics) حسب الفرع | APPROVED WITH CONDITIONS | P0 | NOT STARTED |
| `ANL-038` | Device / UX Data | APPROVED | P1 | NOT STARTED |
| `ANL-039` | Location Analytics فقط إذا Privacy-safe | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `ANL-040` | Real-Time Overview إذا سمح GA4/API | APPROVED WITH CONDITIONS | P2 | NOT STARTED |
| `ANL-041` | عرض ما يفيد القرار فقط (Charts بمعنى، لا نسخ لكل GA4) | APPROVED | P1 | NOT STARTED |
| `ANL-042` | Dashboard SHELTER حقيقية من APIs معتمدة — ليست iframe لـGA | APPROVED | P0 | NOT STARTED |
| `ANL-043` | تصنيف KPIs: OWNER · MARKETING · SEO · UX · TECHNICAL | APPROVED | P0 | NOT STARTED |
| `ANL-044` | Data Source Matrix | APPROVED | P0 | NOT STARTED |
| `ANL-045` | مصادر بيانات الـAnalytics المفضلة | APPROVED | P0 | NOT STARTED |

## 20 · GA4 / GTM — [التفاصيل](master-requirements/20-ga4-gtm.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `GOOGLE-001` | Google Ecosystem Policy إلزامية + نطاق منظومة Google | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `GOOGLE-002` | منظومة Google كنظام واحد مترابط + اتساق بيانات SHELTER | APPROVED | P1 | PARTIAL |
| `GOOGLE-003` | Google Measurement & Search Architecture + المعمارية المستهدفة | APPROVED WITH CONDITIONS | P1 | PARTIAL |
| `GOOGLE-004` | جاهزية الموقع لـGA4 وGTM وSearch Console (تكاملات مطلوبة لاحقًا) | APPROVED | P1 | PARTIAL |
| `GOOGLE-005` | Claude ينفذ إعداد وربط Google بنفسه عند المرحلة والصلاحيات | APPROVED | P1 | NOT STARTED |
| `GOOGLE-006` | تدخل الـOwner فقط عند Login/OAuth/2FA/Ownership/Permission/Legal-financial | APPROVED | P1 | PARTIAL |
| `GOOGLE-007` | وصول GA4/GTM للقراءة فقط لاحقًا (Read-only أولًا) | DEFERRED | P2 | NOT STARTED |
| `GOOGLE-008` | ممنوع تغيير أي Google property/configuration في المرحلة الحالية | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOOGLE-009` | Audit الإعداد الموجود أولًا قبل إنشاء أي GA4/GTM/Integration | APPROVED | P0 | NOT STARTED |
| `GOOGLE-010` | قرار طريقة نشر Google tag واستخدام GTM (GA-01) — Implementation واحد | PENDING OWNER INPUT | P1 | PARTIAL |
| `GOOGLE-011` | GTM ليس مستودع Scripts — كل Tag Documented/Named/Justified/Tested | APPROVED | P1 | NOT STARTED |
| `GOOGLE-012` | GTM Naming Standard موحد قبل التنفيذ | APPROVED | P1 | NEEDS FIX |
| `GOOGLE-013` | إعداد GTM Container احترافي (إذا تقرر GTM) | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `GOOGLE-014` | GTM Version Control بأسماء واضحة وNotes | APPROVED | P1 | NOT STARTED |
| `GOOGLE-015` | اختبار Debug قبل نشر أي تغيير GA4/GTM — لا Event مرتين | APPROVED | P1 | NOT STARTED |
| `GOOGLE-016` | إعداد GA4 Property كامل (إن لزم) ومراجعة كل الإعدادات الافتراضية | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `GOOGLE-017` | مراجعة Enhanced Measurement في GA4 | APPROVED | P1 | NOT STARTED |
| `GOOGLE-018` | GA4 Internal + Developer traffic filtering | APPROVED | P1 | NOT STARTED |
| `GOOGLE-019` | GA4 Data retention + Referral exclusions عند الحاجة | APPROVED | P2 | NOT STARTED |
| `GOOGLE-020` | Cross-domain tracking — مستقبلًا فقط إذا احتجناه | DEFERRED | P3 | NOT STARTED |
| `GOOGLE-021` | Key Events تُحدد مع الـOwner — ليس كل Click Conversion | PENDING OWNER INPUT | P1 | PARTIAL |
| `GOOGLE-022` | Consent (Consent Mode v2) مدمج في معمارية GA4/GTM — بانتظار قرار الخصوصية | PENDING OWNER INPUT | P1 | CONFLICT |
| `GOOGLE-023` | استخراج كل متطلبات Google السابقة (GA4/GTM/Tag/GSC/GBP/Maps/Schema/Sitemap) | APPROVED | P1 | IMPLEMENTED — NOT TESTED |

## 21 · Search Console — [التفاصيل](master-requirements/21-search-console.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `GSC-001` | إعداد وربط Search Console كاملًا — الفريق ينفذ | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `GSC-002` | GSC = مصدر الحقيقة لأداء Google Search — لا Semrush بدلًا منه | APPROVED | P1 | NOT STARTED |
| `GSC-003` | الوصول: مجاني أولًا · Read-only / Restricted · Exports من الـOwner | APPROVED WITH CONDITIONS | P0 | NOT STARTED |
| `GSC-004` | لا تعديل في GSC (Sitemaps، Removals، Users، Ownership، Disavow) بلا موافقة | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GSC-005` | Search Console Migration Baseline قبل الإطلاق — مدخل لخطة النقل | APPROVED | P0 | NOT STARTED |
| `GSC-006` | دراسة GSC Property Architecture (Domain vs URL Prefix) | PENDING OWNER INPUT | P1 | NOT STARTED |
| `GSC-007` | Verification بعد الموافقة — DNS TXT يُجهَّز ولا تغيير DNS بلا موافقة | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `GSC-008` | ربط GSC مع GA4 إذا كان مناسبًا | APPROVED WITH CONDITIONS | P2 | NOT STARTED |
| `GSC-009` | فحص Google بعد الربط (Indexing، Coverage، Rich Results، CWV …) | APPROVED | P1 | NOT STARTED |
| `GSC-010` | لا Finished إذا أظهر Google مشاكل مهمة | APPROVED | P0 | NOT STARTED |
| `GSC-011` | Dashboard ↔ Search Console API: Google Search Performance | APPROVED | P0 | NOT STARTED |
| `GSC-012` | مقارنة الاستعلامات عبر الزمن (Clicks · Impressions · Position · Trend) | APPROVED | P1 | NOT STARTED |
| `GSC-013` | قسم Google Indexation — فقط ما يسمح به الـAPI | APPROVED WITH CONDITIONS | P1 | NOT STARTED |

## 22 · Google Business Profile & Maps — [التفاصيل](master-requirements/22-google-business-profile-maps.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `GBP-001` | الموقع جاهز للتكامل مع Google Business Profile وGoogle Maps | APPROVED | P1 | NOT STARTED |
| `GBP-002` | ملفان رسميان فقط على Google Business Profile: DRIVE وHOUSE | APPROVED | P0 | NOT STARTED |
| `GBP-003` | GBP الرسمي لكل فرع = OFFICIAL OPERATIONAL SOURCE (تابع للـOwner) | SUPERSEDED | P0 | PARTIAL |
| `GBP-004` | GBP لا يتجاوز الـOwner: الاختلاف = CONFLICT — OWNER REVIEW REQUIRED | APPROVED | P0 | NOT STARTED |
| `GBP-005` | هاتف الفروع في GBP والـSchema = 0799009436؛ المختلف = CONFLICT | APPROVED | P0 | NOT STARTED |
| `GBP-006` | لا تُستخدم بيانات منتجات/منيو Google بدل مصدر المنيو الرسمي | APPROVED | P1 | NOT STARTED |
| `GBP-007` | AC-03: صلاحية Google Business Profile = Read-only information فقط | APPROVED | P1 | NOT STARTED |
| `GBP-008` | جمع بيانات GBP بالخيار المجاني وللتحقق فقط (لا Places API مدفوع) | APPROVED | P1 | NOT STARTED |
| `GBP-009` | تعديل GBP: Proposal → Owner Approval → Change (ينفذه Claude) | APPROVED | P0 | NOT STARTED |
| `GBP-010` | الفريق ينفذ فعليًا اتساق Business Profile وMaps (وليس تعليمات فقط) | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `GBP-011` | رابط Maps الرسمي لكل فرع في صفحته — لا نفس الرابط للفرعين | APPROVED | P0 | NOT STARTED |
| `GBP-012` | لا Pin يدوي إذا كان GBP الرسمي موجودًا | APPROVED | P1 | NOT STARTED |
| `GBP-013` | روابط Google Maps لـDRIVE وHOUSE = MISSING — VERIFY | PENDING VERIFICATION | P0 | NOT STARTED |
| `GBP-014` | قبل الإطلاق: ملفا GBP للفرعين Verified | APPROVED | P0 | NOT STARTED |
| `GBP-015` | Google Reviews تُقرأ كمرجع لفهم تجربة العملاء فقط | APPROVED | P1 | NOT STARTED |

## 23 · SEO — [التفاصيل](master-requirements/23-seo.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `SEO-001` | SEO من اليوم الأول — الموقع القديم ليس مرجع SEO Architecture | APPROVED | P1 | NOT STARTED |
| `SEO-002` | جاهزية International SEO و Local SEO | APPROVED | P1 | NOT STARTED |
| `SEO-003` | فحص الموقع الحالي واستخراج بيانات الـSEO (مصدر معلومات فقط) | APPROVED | P0 | PARTIAL |
| `SEO-004` | SEO Audit للموقع الحالي (فهرسة، بنية، جودة، تقنية) | APPROVED | P0 | PARTIAL |
| `SEO-005` | الظهور على Intent حقيقي وليس الاسم فقط | APPROVED | P1 | NOT STARTED |
| `SEO-006` | ممنوع Keyword Stuffing | APPROVED | P1 | NOT STARTED |
| `SEO-007` | Internal Linking قوية في Coffee Knowledge Hub | APPROVED | P1 | NOT STARTED |
| `SEO-008` | Local SEO لكل فرع (NAP، Maps، الساعات، Schema، Metadata) | APPROVED | P1 | PARTIAL |
| `SEO-009` | URL Migration Map رسمية قبل تغيير الموقع القديم | APPROVED | P0 | PARTIAL |
| `SEO-010` | تصنيف كل URL قديم ذي قيمة: KEEP · REBUILD · REDIRECT · REMOVE | APPROVED | P0 | PARTIAL |
| `SEO-011` | 301 بقفزة واحدة لكل URL ذي قيمة يتغير — بلا خسارة SEO أو Backlinks | APPROVED | P0 | NOT STARTED |
| `SEO-012` | MENU-01: /menu → 301 → /ar/jo/menu/ | APPROVED WITH CONDITIONS | P0 | NOT STARTED |
| `SEO-013` | تنفيذ /menu 301 وقت الإطلاق فقط بعد 6 فحوص + موافقة الـOwner | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `SEO-014` | أي QR أو رابط قديم يستمر بالعمل بعد الإطلاق | APPROVED | P0 | NOT STARTED |
| `SEO-015` | لا URLs ولا Redirects ولا SEO changes على Production الآن | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `SEO-016` | رابط الفرع الأصلي لا يتغير أبدًا | APPROVED | P0 | NOT STARTED |
| `SEO-017` | جاهزية نقل 1:1 إلى Global Domain بلا خسارة SEO | APPROVED | P1 | NOT STARTED |
| `SEO-018` | Redirect Manager لإدارة 301 والروابط القديمة والمتغيرة | APPROVED | P1 | NOT STARTED |
| `SEO-019` | صفحة 404 غير فارغة: Menu · Locations · Search · Home | APPROVED | P1 | NOT STARTED |
| `SEO-020` | قبل الإطلاق: 404 reviewed · Old URLs mapped · 301 migration ready | APPROVED | P0 | NOT STARTED |
| `SEO-021` | Canonical صحيح لكل صفحة | APPROVED | P1 | NOT STARTED |
| `SEO-022` | ?branch= بـCanonical واحد — لا نسخة منيو لكل فرع | APPROVED | P0 | NOT STARTED |
| `SEO-023` | لا URLs قابلة للفهرسة من search/category/branch | APPROVED | P1 | NOT STARTED |
| `SEO-024` | لا صفحات أصناف في V1 — لا 191 thin pages | FROZEN | P1 | NOT STARTED |
| `SEO-025` | صفحات أصناف مستقبلًا فقط لمنتجات تستحق SEO Content فعليًا | DEFERRED | P3 | NOT STARTED |
| `SEO-026` | كل المنتجات والأسعار في الـHTML الأولي — لا JS-only ولا Virtualization | APPROVED | P0 | NOT STARTED |
| `SEO-027` | تحقق ما بعد البناء: Crawlability · Indexability · robots.txt · noindex … | APPROVED | P1 | NOT STARTED |
| `SEO-028` | Staging لا يُفهرس: Authentication + noindex + blocking | APPROVED | P0 | NOT STARTED |
| `SEO-029` | XML Sitemap: Canonical · Public · Indexable · Approved فقط | APPROVED | P1 | TESTED |
| `SEO-030` | إرسال الـSitemap لـGSC بعد اعتماد الموقع — Submit ≠ Success | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `SEO-031` | تقرير Indexing حقيقي بعد الإطلاق | APPROVED | P1 | NOT STARTED |
| `SEO-032` | hreflang صحيح: Arabic Alternate + English Alternate لكل صفحة مترجمة | APPROVED | P1 | NOT STARTED |
| `SEO-033` | x-default: الجذر محسوم (/) — صفحات العلامة والسوق معلّقة (ROOT-02) | PENDING OWNER INPUT | P1 | NOT STARTED |
| `SEO-034` | خطة ما قبل Freeze: canonical · hreflang · x-default · redirects · /menu | APPROVED | P0 | PARTIAL |
| `SEO-035` | SEO Editor في الـDashboard لكل Page / Article (/ Product) | APPROVED | P0 | NOT STARTED |
| `SEO-036` | إعدادات SEO الخطرة في Advanced section | APPROVED | P0 | NOT STARTED |
| `SEO-037` | SEO Preview (Google) + Social Preview | APPROVED | P1 | NOT STARTED |
| `SEO-038` | SEO HEALTH CENTER: GOOD/WARNING/CRITICAL + Issue/Page/Severity/Fix | APPROVED | P0 | NOT STARTED |
| `SEO-039` | قائمة فحوص SEO Health | APPROVED | P0 | NOT STARTED |
| `SEO-040` | لا تغييرات SEO تلقائية — موافقة الـOwner على أي تعديل حساس | APPROVED | P0 | TESTED |
| `SEO-041` | DoD (بحث): SEO Clean · Google/AEO/GEO Ready · Redirects/Schema Correct | APPROVED | P0 | NOT STARTED |

## 24 · AEO / GEO / AI Search — [التفاصيل](master-requirements/24-aeo-geo-ai-search.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `AEO-001` | الظهور في AI Search (AI Overviews، ChatGPT، Gemini، Perplexity، Copilot، Bing) | APPROVED | P1 | NOT STARTED |
| `AEO-002` | Entity Clarity: أسئلة الكيان بالإنجليزية والعربية | APPROVED | P1 | NOT STARTED |
| `AEO-003` | اتساق GBP ↔ الموقع وهرمية الكيان SHELTER COFFEE → Jordan → Irbid → DRIVE/HOUSE | APPROVED | P1 | NOT STARTED |

## 25 · Schema — [التفاصيل](master-requirements/25-schema.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `SCHEMA-001` | أنواع Schema المسموحة عند الحاجة | APPROVED | P1 | NOT STARTED |
| `SCHEMA-002` | Structured Data حقيقي وظاهر فقط — ممنوع Schema Spam | APPROVED | P1 | NOT STARTED |
| `SCHEMA-003` | ممنوع اختراع rating/reviews/availability/offers/nutrition/claims في Schema | APPROVED | P0 | NOT STARTED |
| `SCHEMA-004` | FAQPage فقط عند استيفاء الشروط ووجود محتوى فعلي | APPROVED WITH CONDITIONS | P2 | NOT STARTED |
| `SCHEMA-005` | Branch Schema متوافق مع GBP: Name · Address · Geo · Hours · Phone · SameAs | APPROVED | P1 | NOT STARTED |
| `SCHEMA-006` | Image في الـSchema فقط إذا اعتمدها الـOwner | APPROVED | P0 | NOT STARTED |
| `SCHEMA-007` | Telephone في Schema/GBP = 0799009436 (+962799009436) | APPROVED | P0 | NOT STARTED |
| `SCHEMA-008` | تنفيذ الـSchema فعليًا بعد اعتماد البيانات — البيانات المطلوبة لكل نوع | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `SCHEMA-009` | تحقق Rich Results: إصلاح Errors قبل الإطلاق — JSON-LD ≠ مكتمل | APPROVED | P1 | NOT STARTED |
| `SCHEMA-010` | Schema Settings في الـDashboard حسب نوع المحتوى | APPROVED | P1 | NOT STARTED |

## 26 · Performance — [التفاصيل](master-requirements/26-performance.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `PERF-001` | الموقع سريع جدًا ولا يشعر بأنه ثقيل (CRITICAL) | APPROVED | P0 | NOT STARTED |
| `PERF-002` | Core Web Vitals جيدة تحت قيود شبكة وأجهزة الموبايل | APPROVED | P1 | NOT STARTED |
| `PERF-003` | مراقبة Core Web Vitals طوال المشروع | APPROVED | P1 | PARTIAL |
| `PERF-004` | Baseline أداء الموقع القديم قبل وبعد إعادة البناء | APPROVED | P1 | NOT STARTED |
| `PERF-005` | Performance Budget للموقع كاملًا | APPROVED | P1 | PARTIAL |
| `PERF-006` | Performance Budget أولي للمنيو (Phase H) بأهداف واقعية ومبررة | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `PERF-007` | المنيو: ممنوع تحميل 191 صورة عالية الدقة مباشرة | APPROVED | P0 | NOT STARTED |
| `PERF-008` | الصور: responsive sizes وAVIF/WebP وsrcset وأبعاد محجوزة | APPROVED | P1 | PARTIAL |
| `PERF-009` | Lazy loading تحت الـfold وأولوية لصور الشاشة الأولى فقط | APPROVED | P1 | NOT STARTED |
| `PERF-010` | النص والواجهة الأساسية أولًا ثم الصور | APPROVED | P1 | NOT STARTED |
| `PERF-011` | الـThird-party وGoogle scripts لا تبطئ الموقع ولا تؤخر أول محتوى مفيد | APPROVED | P0 | NOT STARTED |
| `PERF-012` | الخرائط: لا Google Map Embed ثقيل تلقائيًا — اختيار الأخف | APPROVED | P1 | NOT STARTED |
| `PERF-013` | أداء الخطوط | APPROVED | P1 | NOT STARTED |
| `PERF-014` | Code splitting حيث يناسب | APPROVED | P1 | NOT STARTED |
| `PERF-015` | Progressive Enhancement عند فشل JS | APPROVED | P1 | NOT STARTED |
| `PERF-016` | أداء الحركة: transform/opacity وتجنب Jank | APPROVED | P1 | NOT STARTED |
| `PERF-017` | Lighthouse / PageSpeed checks قابلة للتكرار (Mobile وDesktop) | APPROVED | P1 | PARTIAL |
| `PERF-018` | لا مطاردة الـScore — معالجة الأسباب الجذرية وتجربة المستخدم الحقيقية | APPROVED | P1 | PARTIAL |
| `PERF-019` | sitespeed.io لفحص أداء أعمق للصفحات المهمة | APPROVED WITH CONDITIONS | P2 | NOT STARTED |
| `PERF-020` | Dashboard: لوحة Performance & Core Web Vitals بلغة سهلة | APPROVED | P1 | NOT STARTED |
| `PERF-021` | Dashboard: Lighthouse مجدول ويدوي مع الأسباب الجذرية | APPROVED | P1 | NOT STARTED |
| `PERF-022` | الـDashboard نفسها سريعة | APPROVED | P1 | NOT STARTED |
| `PERF-023` | لا استدعاء APIs في كل Page Load ولا real-time polling ثقيل | APPROVED | P0 | NOT STARTED |

## 27 · Accessibility — [التفاصيل](master-requirements/27-accessibility.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `A11Y-001` | الهدف WCAG 2.2 AA وWCAG-conscious UX | APPROVED | P1 | NOT STARTED |
| `A11Y-002` | نطاق مراجعة الوصولية | APPROVED | P1 | PARTIAL |
| `A11Y-003` | الاختبار الآلي لا يكفي: automated + manual checklist | APPROVED | P1 | PARTIAL |
| `A11Y-004` | axe يُفشل/يبلّغ المشاكل الجدية | APPROVED | P1 | PARTIAL |
| `A11Y-005` | احترام prefers-reduced-motion | APPROVED | P1 | NOT STARTED |
| `A11Y-006` | تباين كافٍ | APPROVED | P1 | NOT STARTED |
| `A11Y-007` | Keyboard navigation كامل مع focus مرئي | APPROVED | P1 | NOT STARTED |
| `A11Y-008` | تفاصيل المنتج: إدارة التركيز وEscape وfocus trap/return | APPROVED | P1 | NOT STARTED |
| `A11Y-009` | Screen reader labels وARIA فقط حيث يلزم | APPROVED | P1 | NOT STARTED |
| `A11Y-010` | إعلانات البحث: عدد النتائج وزر مسح accessible | APPROVED | P1 | NOT STARTED |
| `A11Y-011` | Alt نصي ذو معنى للصور | APPROVED | P1 | NOT STARTED |
| `A11Y-012` | تسلسل عناوين صحيح | APPROVED | P1 | NOT STARTED |
| `A11Y-013` | سمات اللغة والاتجاه الصحيحة | APPROVED | P1 | NOT STARTED |
| `A11Y-014` | السعر مفهوم لقارئ الشاشة | APPROVED | P1 | NOT STARTED |
| `A11Y-015` | ALL CAPS لا يسبب نطقًا حرفيًا | APPROVED | P1 | NOT STARTED |
| `A11Y-016` | الحالات لا تعتمد على اللون وحده | APPROVED | P1 | NOT STARTED |
| `A11Y-017` | أهداف اللمس ≥ 44×44px | APPROVED | P1 | NOT STARTED |
| `A11Y-018` | ممنوع منع pinch zoom | APPROVED | P1 | NOT STARTED |
| `A11Y-019` | Accessibility checklist لصفحة المنيو (Phase J) | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `A11Y-020` | Dashboard: تقرير مشاكل الوصولية | APPROVED | P1 | NOT STARTED |

## 28 · Motion — [التفاصيل](master-requirements/28-motion.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `MOTION-001` | الموقع يجب أن يشعر بأنه حيّ (Alive) — Premium وليس Static | APPROVED | P1 | NOT STARTED |
| `MOTION-002` | الحركة تخدم الـUX — لا حركة لمجرد أن المكتبة تدعمها | APPROVED | P1 | NOT STARTED |
| `MOTION-003` | Performance always wins over decorative motion — لا مساس بـLCP / INP / CLS | APPROVED | P1 | NOT STARTED |
| `MOTION-004` | Motion Design System | APPROVED | P1 | PARTIAL |
| `MOTION-005` | دراسة تقنيات الحركة الممكنة (ليست كلها معتمدة) | APPROVED | P2 | NOT STARTED |
| `MOTION-006` | ممنوعات الحركة (Do not over-animate) | APPROVED | P1 | NOT STARTED |
| `MOTION-007` | تقنية الحركة: Motion كخيار أساسي (يحل محل دراسة GSAP / Motion.page) | APPROVED WITH CONDITIONS | P2 | NOT STARTED |
| `MOTION-008` | Lenis: اختياري ومشروط — ليس عامًا، وليس على المنيو افتراضيًا | APPROVED WITH CONDITIONS | P2 | NOT STARTED |
| `MOTION-009` | مكتبات مشروطة: Lottie · Swiper · Three.js | APPROVED WITH CONDITIONS | P3 | NOT STARTED |
| `MOTION-010` | Progressive Enhancement: المحتوى لا يعتمد على الحركة | APPROVED | P0 | NOT STARTED |
| `MOTION-011` | دعم prefers-reduced-motion | APPROVED | P1 | NOT STARTED |
| `MOTION-012` | حركة المنيو: High Motion للموقع لكن المنيو سريعة | APPROVED | P1 | NOT STARTED |
| `MOTION-013` | نطاق حركة الواجهة المسموح | APPROVED | P2 | NOT STARTED |
| `MOTION-014` | حركة Premium هادفة: الأزرار والتنقل والأقسام والـSheets والصفحات — مع بدائل reduced-motion | APPROVED | P0 | NOT STARTED |

## 29 · Media & Images — [التفاصيل](master-requirements/29-media-images.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `MEDIA-001` | قاعدة صارمة: لا صورة أو Asset بدون موافقة الـOwner (كل صورة بموافقة منفصلة) | APPROVED | P0 | PARTIAL |
| `MEDIA-002` | حزمة عرض الصورة المقترحة | APPROVED | P0 | PARTIAL |
| `MEDIA-003` | كل صورة مقترحة أو مطلوبة تبدأ PENDING OWNER APPROVAL | APPROVED | P0 | TESTED |
| `MEDIA-004` | صور Google ليست Approved Assets | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `MEDIA-005` | لا Stock ولا AI ولا Google ولا صور الموقع القديم تلقائيًا | APPROVED | P0 | TESTED |
| `MEDIA-006` | نظام Approved Asset Library | APPROVED | P1 | TESTED |
| `MEDIA-007` | صور المنتجات من الـOwner وربطها بالـProduct ID | PENDING OWNER INPUT | P1 | PARTIAL |
| `MEDIA-008` | Media Architecture / Media Model | APPROVED | P1 | PARTIAL |
| `MEDIA-009` | صورة بطاقة المنتج: كبيرة بنسبة 1:1 | FROZEN | P1 | NOT STARTED |
| `MEDIA-010` | أداء الصور و Responsive images | APPROVED | P1 | PARTIAL |
| `MEDIA-011` | Sharp image pipeline: بلا تكبير، الأصول الأصلية محفوظة، الاعتماد قائم | APPROVED | P1 | PARTIAL |
| `MEDIA-012` | Media Library في الـDashboard | APPROVED | P0 | NOT STARTED |
| `MEDIA-013` | تتبع استخدام كل صورة (Usage tracking) | APPROVED | P0 | NOT STARTED |
| `MEDIA-014` | لا ملفات مكررة في الـMedia Library | APPROVED | P1 | NOT STARTED |
| `MEDIA-015` | Image Health في الـDashboard مع الإصلاح | APPROVED | P1 | NOT STARTED |
| `MEDIA-016` | ممنوع Autoplay Audio | APPROVED | P1 | NOT STARTED |

## 30 · Security — [التفاصيل](master-requirements/30-security.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `SEC-001` | فحص أمني شامل (الموقع الحالي ثم الجديد قبل الإطلاق) | APPROVED | P0 | NOT STARTED |
| `SEC-002` | لا Secrets في الـFront-End — الـCredentials Server-side وفي إعدادات البيئة فقط | APPROVED | P0 | NOT STARTED |
| `SEC-003` | Maps API Key (إن لزم) مقيد: HTTP referrer · API restrictions · Usage limits | APPROVED | P1 | NOT STARTED |
| `SEC-004` | حماية الـStaging: Authentication + noindex — robots.txt وحده ليس حماية أمنية | APPROVED | P0 | NOT STARTED |
| `SEC-005` | Secure authentication + Session security لنظام الـAdmin | APPROVED | P0 | NOT STARTED |
| `SEC-006` | CSRF protection حسب الـArchitecture | APPROVED | P0 | NOT STARTED |
| `SEC-007` | Rate limiting حيث يلزم (Login · Forms · APIs) | APPROVED | P1 | PARTIAL |
| `SEC-008` | Secure API access + audit logging — لا Admin APIs عامة بدون Authorization | APPROVED | P0 | NOT STARTED |
| `SEC-009` | Safe file uploads في الـMedia Library | APPROVED | P0 | NOT STARTED |
| `SEC-010` | أمان الموقع القديم: الخيار A — Backup → Staging → … → Owner Approval | APPROVED | P1 | NOT STARTED |
| `SEC-011` | ممنوع حذف Comments أو Users أو Plugins أو Content بدون موافقة الـOwner | APPROVED | P0 | NOT STARTED |

## 31 · Permissions & Auth — [التفاصيل](master-requirements/31-permissions-auth.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `PERM-001` | V1: الـOwner وحده لديه Full Control ويرى كل شيء | APPROVED | P0 | NOT STARTED |
| `PERM-002` | أدوار مستقبلية: OWNER · ADMIN · EDITOR · SEO · MARKETING · CONTENT MANAGER | SUPERSEDED | P1 | NOT STARTED |
| `PERM-003` | Least privilege للمستخدمين: لا Full Access للجميع | APPROVED | P0 | NOT STARTED |
| `PERM-004` | الصلاحيات تُفرض Server-side — لا اعتماد على إخفاء الأزرار | APPROVED | P0 | NOT STARTED |
| `PERM-005` | Permissions Model (PERMISSIONS.md) قبل التنفيذ — Phase E | APPROVED | P0 | NOT STARTED |
| `PERM-006` | تعديلات المنيو الحساسة: موافقة الـOwner حيث يلزم | APPROVED | P1 | NOT STARTED |
| `PERM-007` | AI لا ينفذ تغييرات حساسة بدون Approval | APPROVED | P0 | NOT STARTED |
| `PERM-008` | بروتوكول طلب الصلاحيات (5 بنود) بأقل صلاحية لازمة | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `PERM-009` | الـOwner يُطلب منه فقط Auth · Approval · 2FA · Ownership · Billing · Legal | APPROVED | P1 | NOT STARTED |
| `PERM-010` | AC-01: وصول مباشر للقراءة والفحص فقط على shelterjo.com · www · shop — أولًا | APPROVED | P0 | NOT STARTED |
| `PERM-011` | المستخدمون مستقبلًا: إنشاء صريح وصلاحيات مختارة — بلا أدوار معقدة الآن | FROZEN | P1 | NOT STARTED |

## 32 · Audit / Versioning — [التفاصيل](master-requirements/32-audit-versioning.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `AUDIT-001` | Audit Log لكل Action في الـDashboard (Who · What · Before · After · Date/Time) | APPROVED | P0 | NOT STARTED |
| `AUDIT-002` | لا Silent Changes | APPROVED | P0 | NOT STARTED |
| `AUDIT-003` | Version History لكل تغيير مهم (User · Time · Old Value · New Value) قابلة للعرض | APPROVED | P0 | NOT STARTED |
| `AUDIT-004` | Restore / Revert لنسخة سابقة — إذا كان آمنًا | APPROVED WITH CONDITIONS | P0 | NOT STARTED |
| `AUDIT-005` | MENU VERSION `MV-YYYY-MM-DD` — الحالية MV-2026-10-01 (سارية من 2026-10-01) | FROZEN | P0 | FROZEN |
| `AUDIT-006` | استيراد المنيو لا يمسح التاريخ (Previous/Current Price · Effective Date) | APPROVED | P0 | NOT STARTED |
| `AUDIT-007` | الحفاظ على Lineage لكل منتج (المصدر · الصف · الـHash · النسخة · التاريخ) | FROZEN | P0 | PARTIAL |
| `AUDIT-008` | Versioning + Backup strategy + Safe rollback لإدارة المحتوى الحساس | APPROVED | P0 | NOT STARTED |
| `AUDIT-009` | سجل نسخ مفهوم: ماذا تغير، من ماذا، إلى ماذا، ومتى | FROZEN | P0 | NOT STARTED |
| `AUDIT-010` | معمارية Audit Log حتى مع مستخدم واحد | FROZEN | P0 | NOT STARTED |

## 33 · Integrations & Paid Services — [التفاصيل](master-requirements/33-integrations-paid-services.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `INT-001` | لا Paid API/Quota/Credits/Service بدون موافقة مسبقة + إفصاح خماسي | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `INT-002` | لا Google Places API مدفوع حاليًا | APPROVED | P2 | IMPLEMENTED — NOT TESTED |
| `INT-003` | Supermetrics (AC-04): لا استهلاك Quota قبل الإفصاح والموافقة | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `INT-004` | الأدوات المحلية المجانية مسموحة: Playwright · Lighthouse · local testing | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `INT-005` | خدمة مراقبة أخطاء خارجية (مثل Sentry) تُقترح أولًا ولا تُفعّل قبل الموافقة | APPROVED | P1 | NOT STARTED |
| `INT-006` | Official APIs فقط (GA Data API · GSC API) — لا scraping لـGoogle dashboards | APPROVED | P0 | NOT STARTED |
| `INT-007` | ممنوع تثبيت WordPress Plugins في هذه المرحلة | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `INT-008` | No Plugin Bloat + تقييم أي Plugin/أداة طرف ثالث قبل الإضافة | APPROVED | P1 | PARTIAL |
| `INT-009` | Google Site Kit (موجود حاليًا): فحص ما يديره — لا افتراض لاستخدامه في الجديد | APPROVED | P1 | PARTIAL |
| `INT-010` | منصات التوصيل لكل فرع: من الـOwner فقط — لا استنتاج من الموقع القديم أو الإنترنت | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `INT-011` | R2B-01: منصات التوصيل لكل فرع وروابطها الرسمية = MISSING | PENDING OWNER INPUT | P1 | NOT STARTED |
| `INT-012` | نظام الكاشير (POS) = LATER / MISSING — ممنوع اختراع اسمه | DEFERRED | P2 | IMPLEMENTED — NOT TESTED |
| `INT-013` | حقلا pos_item_id و external_item_id يبقيان nullable وفارغين | APPROVED | P2 | PARTIAL |
| `INT-014` | عمود # ليس معرّفًا تجاريًا ولا يُستخدم في أي تكامل | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `INT-015` | لا اتصال مباشر بقاعدة بيانات POS الآن | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `INT-016` | جاهزية تكامل مستقبلي: POS / ERP / Customer App / Login | DEFERRED | P3 | NOT STARTED |

## 34 · Cloudflare / Hosting / DNS — [التفاصيل](master-requirements/34-cloudflare-hosting-dns.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `CF-001` | البنية الحالية: www.shelterjo.com على Cloudways خلف Cloudflare | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `CF-002` | Phase 01: ممنوع تغيير Cloudflare أو Cloudways أو DNS | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `CF-003` | لا تغيير DNS (أو إعدادات Cloudflare Production) بدون موافقة الـOwner | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `CF-004` | مراجعة إعدادات Cloudflare | APPROVED | P1 | NOT STARTED |
| `CF-005` | مراجعة إعدادات Cloudways | APPROVED | P1 | NOT STARTED |
| `CF-006` | جاهزية Global CDN | APPROVED | P2 | NOT STARTED |
| `CF-007` | تجهيز قيمة DNS verification (TXT) لـSearch Console مسبقًا | APPROVED | P1 | NOT STARTED |
| `CF-008` | Cloudflare API للـSite Health: شرح الصلاحيات الدقيقة أولًا | APPROVED | P1 | PARTIAL |
| `CF-009` | Cloudflare Token بأقل صلاحية — لا Global API Key | APPROVED | P0 | IMPLEMENTED — NOT TESTED |

## 35 · Testing & QA — [التفاصيل](master-requirements/35-testing-qa.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `TEST-001` | بوابة QA قبل الإطلاق (M01 §92) | APPROVED | P0 | NOT STARTED |
| `TEST-002` | Definition of Done: لا Requirement منتهية لمجرد كتابة الكود | APPROVED | P0 | NOT STARTED |
| `TEST-003` | التحقق من التنفيذ الحقيقي — لا افتراض من أسماء الملفات | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `TEST-004` | بوابة اكتمال الصفحة: UX Quality Gates + Final Responsive Gate | APPROVED | P0 | PARTIAL |
| `TEST-005` | الإصدارات الكبرى: Unlighthouse وsitespeed.io عند الحاجة | APPROVED | P1 | NOT STARTED |
| `TEST-006` | RESPONSIVE-QA-MATRIX.md لكل صفحة رئيسية | APPROVED | P1 | PARTIAL |
| `TEST-007` | تغطية حالات وتدفقات المنيو في الاختبارات | APPROVED | P1 | PARTIAL |
| `TEST-008` | اختبار browser history وscroll restoration | APPROVED | P1 | NOT STARTED |
| `TEST-009` | اختبار Campaign banner وContact CTAs | APPROVED | P1 | NOT STARTED |
| `TEST-010` | اختبار عبر المتصفحات | APPROVED | P0 | PARTIAL |
| `TEST-011` | لا اعتماد على المحاكي فقط — فحص سلوك حقيقي | APPROVED | P1 | NOT STARTED |
| `TEST-012` | Screenshots للمراجعة البصرية ومقارنتها | APPROVED | P0 | PARTIAL |
| `TEST-013` | اختبار الأداء في بيئة مخنوقة (throttled) | APPROVED | P1 | PARTIAL |
| `TEST-014` | اختبار User Journeys حقيقية بـPlaywright | APPROVED | P1 | NOT STARTED |
| `TEST-015` | Debug Everything: أدوات ومعايير قبول التتبع | APPROVED | P1 | NOT STARTED |
| `TEST-016` | Analytics Validation Report | APPROVED | P1 | NOT STARTED |
| `TEST-017` | FINAL GOOGLE ACCEPTANCE TEST | APPROVED | P0 | NOT STARTED |
| `TEST-018` | Chrome DevTools لفحص الأداء والمتصفح | APPROVED | P1 | NOT STARTED |
| `TEST-019` | Code Review بعد كل Development Milestone | APPROVED | P1 | NOT STARTED |
| `TEST-020` | الأدوات كحارس انحدار (regression guard) | APPROVED | P0 | PARTIAL |
| `TEST-021` | اختبارات جاهزية الـDashboard | APPROVED | P0 | NOT STARTED |
| `TEST-022` | سيناريوهات فشل الـDashboard | APPROVED | P0 | NOT STARTED |
| `TEST-023` | إعادة الفحص الحقيقي للموقع الحالي بعد تفعيل AC-01 | APPROVED | P1 | NOT STARTED |
| `TEST-024` | لا Load Testing ولا Crawling عدواني على Production | APPROVED | P0 | IMPLEMENTED — NOT TESTED |

## 36 · Deployment & Production Safety — [التفاصيل](master-requirements/36-deployment-production-safety.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `DEPLOY-001` | لا أي تغيير على Production بدون موافقة الـOwner | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `DEPLOY-002` | مسموح بلا موافقة مسبقة: Configure · Build · Test · Prepare | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `DEPLOY-003` | التغييرات الحساسة: Summary ← موافقة ← تنفيذ | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `DEPLOY-004` | مسار Staging → Testing → Owner Approval → Production | APPROVED | P0 | NOT STARTED |
| `DEPLOY-005` | Backup قبل Migration وDeployment وPlugin Updates والتغييرات الكبرى | APPROVED | P0 | NOT STARTED |
| `DEPLOY-006` | الموقع القديم: لا Update على Production الآن | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `DEPLOY-007` | المرحلة الحالية: لا Production Deployment ولا تغيير للموقع الحي | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `DEPLOY-008` | المرحلة الحالية: لا irreversible database migrations | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `DEPLOY-009` | تغيير Production الخطر يحتاج Gate (Staging·Backup·Test·Approval·Rollback) | APPROVED | P0 | PARTIAL |
| `DEPLOY-010` | Before Launch — Google Checklist | APPROVED | P0 | NOT STARTED |
| `DEPLOY-011` | الـCMS: لا تغيير على Production أثناء التحرير — فقط بعد Publish | APPROVED | P0 | NOT STARTED |
| `DEPLOY-012` | Confirmation حسب الخطورة للعمليات الحساسة | APPROVED | P0 | NOT STARTED |
| `DEPLOY-013` | الحذف = أرشفة بدل hard delete | APPROVED | P0 | NOT STARTED |
| `DEPLOY-014` | خطأ محرر لا يمسح الموقع | APPROVED | P0 | NOT STARTED |

## 37 · Monitoring & Alerts — [التفاصيل](master-requirements/37-monitoring-alerts.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `MON-001` | Site Health — التوفر: Online · SSL · Domain · DNS · Cloudflare · Response time | APPROVED | P0 | NOT STARTED |
| `MON-002` | Site Health — الأخطاء وملفات الزحف | APPROVED | P0 | NOT STARTED |
| `MON-003` | الـDashboard تجيب عن أسئلة صحة الموقع والتكاملات | APPROVED | P0 | NOT STARTED |
| `MON-004` | Error Center: JS · API · 404 · 500 · Failed Requests — بدون PII | APPROVED | P1 | NOT STARTED |
| `MON-005` | Periodic checks: Uptime · Broken pages · Performance · Sitemap · Robots | APPROVED | P1 | NOT STARTED |
| `MON-006` | لا Aggressive crawling — جدول فحص منطقي | APPROVED | P0 | NOT STARTED |
| `MON-007` | Alerts / Attention Needed داخل الـDashboard | APPROVED | P0 | NOT STARTED |
| `MON-008` | لا noisy alerts ولا notification spam — ترتيب حسب الأولوية | APPROVED | P1 | NOT STARTED |
| `MON-009` | V1: الإشعارات داخل الـDashboard فقط | APPROVED | P1 | NOT STARTED |
| `MON-010` | مستقبلًا: Email / WhatsApp / Push — فقط إذا اعتُمدت | DEFERRED | P3 | NOT STARTED |
| `MON-011` | مراقبة ما بعد الإطلاق: Day 1 · 3 · 7 · 14 · 30 — المشروع لا ينتهي عند الـLaunch | APPROVED | P1 | NOT STARTED |
| `MON-012` | هبوط الزيارات بعد الـMigration: لا افتراض للسبب — فحص منهجي ثم Diagnosis | APPROVED | P1 | NOT STARTED |
| `MON-013` | مستقبلًا: فحص دوري لتطابق GBP مع الموقع | SUPERSEDED | P0 | NOT STARTED |

## 38 · Documentation & Governance — [التفاصيل](master-requirements/38-documentation-governance.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `GOV-001` | الـOwner هو المرجع النهائي (Final Authority / Source of Truth) | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-002` | Source Priority وإجراء تعارض المصادر (A · B · Difference · Correction) | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-003` | أي معلومة من الموقع القديم أو مصدر خارجي = PENDING OWNER VERIFICATION | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-004` | OLD WEBSITE = INFORMATION SOURCE ONLY — إعادة بناء من الصفر | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-005` | ترتيب السلطة لتحديد الحقيقة (Authority Order — M27 §42) | APPROVED | P0 | PARTIAL |
| `GOV-006` | المحادثة كاملة = مشروع واحد؛ لا يضيع أي Requirement (One Project Brain) | APPROVED | P0 | PARTIAL |
| `GOV-007` | آخر قرار صريح يتقدم، والتصحيح اللاحق يلغي القديم (قاعدتا التعارض 1 و3) | APPROVED | P0 | PARTIAL |
| `GOV-008` | قرار الـOwner فوق أي اقتراح؛ اقتراح Claude ليس قرارًا (قاعدتا 2 و5) | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-009` | السكوت ليس موافقة (Silence ≠ Approval) | FROZEN | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-010` | لا تغيير لقرار معتمد ولا إعادة فتح لقرار FROZEN بدون Conflict حقيقي (قاعدة 4) | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-011` | ممنوع حل التعارض بصمت (No Silent Conflict Resolution) | APPROVED | P0 | PARTIAL |
| `GOV-012` | docs/CONFLICT-REGISTER.md وإجراء حسم التعارض | APPROVED | P0 | NOT STARTED |
| `GOV-013` | توثيق القرارات المستبدلة بدون حذف التاريخ (OLD → SUPERSEDED BY → Current) | APPROVED | P0 | NEEDS FIX |
| `GOV-014` | Continuous Sync: كل Prompt جديد يُقارن تلقائيًا بالمرجع | APPROVED | P0 | NOT STARTED |
| `GOV-015` | تفويض القرارات: التقني/UX لـClaude؛ Business/Content للـOwner | FROZEN | P0 | PARTIAL |
| `GOV-016` | عرض Options A / B / C مع أثر كل خيار ثم ترك الاختيار للـOwner | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `GOV-017` | الفكرة الأفضل تُعرض ولا تُنفذ مباشرة (Better Idea Protocol) | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-018` | ترتيب الأولويات عند التعارض التقني — ولا يُستخدم لتغيير قرار تجاري | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `GOV-019` | معايير كل قرار في مرحلة المنيو + Simplicity wins | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `GOV-020` | المشروع ليس Vibe Coding — Architecture أولًا | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-021` | دور Claude: فريق خبراء متكامل وليس راسم شاشات | APPROVED | P2 | IMPLEMENTED — NOT TESTED |
| `GOV-022` | ممنوع تخمين أو اختراع أي Business data — الناقص يُعلَّم ولا يُملأ | FROZEN | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-023` | الوسوم الموحدة: MISSING — OWNER INPUT REQUIRED / NEEDS OWNER VERIFICATION | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-024` | مفردات حالة Business data (M27 §7) وتوحيدها مع حالات السجلات | APPROVED | P0 | PARTIAL |
| `GOV-025` | مسار اعتماد أي معلومة تظهر للزوار | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-026` | بوابة النشر: لا نشر بدون موافقة صريحة؛ فقط APPROVED قابل للنشر | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-027` | Content Approval Register (الأعمدة والحالات) | APPROVED | P0 | NEEDS FIX |
| `GOV-028` | قائمة المعلومات التي تحتاج موافقة الـOwner (Phase 01 بند 5) | APPROVED | P0 | NEEDS FIX |
| `GOV-029` | تسلسل المشروع الإلزامي (36 مرحلة) | SUPERSEDED | P0 | PARTIAL |
| `GOV-030` | بوابة المراحل: لا قفز قبل اعتماد المرحلة الحالية + إجراء كل مرحلة (10 خطوات) | SUPERSEDED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-031` | الأسئلة على مراحل ودفعات قصيرة مرتبة حسب الأثر — لا 50 سؤالًا دفعة واحدة | FROZEN | P1 | IMPLEMENTED — NOT TESTED |
| `GOV-032` | السؤال الحرج للـArchitecture يُطرح فورًا | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `GOV-033` | اكتشاف النواقص ذاتيًا وسؤال الـOwner مباشرة (قائمة التغطية 27 مجالًا) | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `GOV-034` | لا إعادة سؤال عن معلومة معتمدة — البحث في المرجع أولًا | APPROVED | P1 | PARTIAL |
| `GOV-035` | الناقص/المعلق لا يوقف العمل غير المتأثر؛ السؤال فقط عند المنع | FROZEN | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-036` | صيغة مراجعات الـOwner: مجموعات مختصرة وفقط ما لا يمكن استنتاجه | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `GOV-037` | لا يُطلب من الـOwner تعبئة قوالب أو إعادة كتابة معلومات موجودة في ملفاته | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `GOV-038` | لا عمل يدوي على الـOwner إذا أمكن التنفيذ (استثناءات محددة) | APPROVED | P1 | NOT STARTED |
| `GOV-039` | PHASE 01 — DISCOVERY & OWNER INTERVIEW فقط وقيودها | SUPERSEDED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-040` | بوابة كود الموقع: لا Coding/Framework قبل اعتماد الـArchitecture | SUPERSEDED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-041` | تسلسل جولات Discovery (R1 → R2 → R2P → R2B → R3 → Menu) | APPROVED | P2 | IMPLEMENTED — NOT TESTED |
| `GOV-042` | تحديد أهم القرارات قبل التصميم (Phase 01 بند 6) | APPROVED | P0 | NEEDS FIX |
| `GOV-043` | أسئلة Phase 01: Pages · Nav · Homepage · Menu · Locations · Blog · Contact | APPROVED | P0 | PARTIAL |
| `GOV-044` | تسلسل عمل المنيو (R3 → Inventory → Data Model → Menu IA → اعتماد → Menu UX/UI) | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-045` | تطبيق القرارات على الـInventory مع إبقاء Source Data untouched | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-046` | Menu Data Model كامل قبل أي UI للمنيو | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-047` | بوابة Menu UI: لا Visual/Production UI قبل اعتماد IA + Wireframes | SUPERSEDED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-048` | موجز M23 هو المرجع التنفيذي الأحدث لمرحلة MENU IA / UX / WIREFRAME | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-049` | مراحل عمل المنيو PHASE A → J + Phase A (التحقق) ومخرجها MENU-DECISION-REGISTER | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `GOV-050` | تصحيح كل مشاكل P0 قبل عرض الـWireframes النهائية | APPROVED | P0 | TESTED |
| `GOV-051` | صيغة العرض النهائي لمرحلة المنيو (18 بندًا — ليس تقريرًا نصيًا ضخمًا) | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `GOV-052` | بعد عرض المنيو النهائي: توقف + موافقة + مراجعة خارجية قبل التصميم/الكود | SUPERSEDED | P0 | PARTIAL |
| `GOV-053` | Dashboard: Audit أولًا (لا بناء Blind) — SHELTER DASHBOARD AUDIT + Inventory | APPROVED | P0 | NOT STARTED |
| `GOV-054` | ترتيب تنفيذ الـDashboard الإلزامي PHASE A → N | APPROVED | P0 | NOT STARTED |
| `GOV-055` | بوابة الـDashboard: لا كود قبل موافقة PHASE H على Architecture + Wireframes | SUPERSEDED | P0 | NOT STARTED |
| `GOV-056` | وثائق الـDashboard الست (Architecture · Measurement · CMS · Health · Perms) | APPROVED | P1 | NOT STARTED |
| `GOV-057` | OWNER-DASHBOARD-GUIDE.md بسيط جدًا للـOwner | APPROVED | P1 | NOT STARTED |
| `GOV-058` | حدود عمل المطوّر: functionality · architecture · integrations · redesign | APPROVED | P1 | NOT STARTED |
| `GOV-059` | Google Ecosystem Policy إلزامية طوال المشروع وجزء من البنية من البداية | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `GOV-060` | DO NOT CHANGE GOOGLE DATA WITHOUT OWNER APPROVAL | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-061` | ملكية تنفيذ منظومة Google: Claude يربط ويضبط ويختبر ويوثق (ليس توصيات فقط) | APPROVED | P1 | NOT STARTED |
| `GOV-062` | بوابة تنفيذ Google: فقط عند مرحلة التنفيذ وبعد منح الصلاحيات | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-063` | وثائق Google (M11 §56) + Checklist + قائمة الصلاحيات المطلوبة | APPROVED | P1 | PARTIAL |
| `GOV-064` | توثيق نهاية إعداد Google (M12 §30) لأي Developer لاحق | APPROVED | P1 | NOT STARTED |
| `GOV-065` | DECISION LOG (Decision · Date · Reason · Owner Approval · Impact) | APPROVED | P0 | PARTIAL |
| `GOV-066` | docs/MASTER-DECISION-REGISTER.md (الأقسام الثمانية والحقول) | APPROVED | P0 | NOT STARTED |
| `GOV-067` | Full Conversation Audit: READ → AUDIT → … → PLAN → EXECUTE | APPROVED | P0 | PARTIAL |
| `GOV-068` | استخراج المتطلبات بنظام Traceable (IDs ثابتة PREFIX-NNN والحقول) | APPROVED | P0 | PARTIAL |
| `GOV-069` | Prompt Audit: تصنيف A–I وعدم تحويل الأسئلة/الأمثلة إلى متطلبات | APPROVED | P0 | PARTIAL |
| `GOV-070` | Existing Project Audit: WHAT I REQUESTED vs WHAT EXISTS وحالات التنفيذ | APPROVED | P0 | PARTIAL |
| `GOV-071` | ممنوع ادعاء الإنجاز: DONE / IMPLEMENTED / TESTED / VERIFIED فقط بعد تحقق فعلي | APPROVED | P0 | PARTIAL |
| `GOV-072` | IMPLEMENTATION-GAP-ANALYSIS.md + قرار KEEP/…/REMOVE لكل Module | APPROVED | P0 | NOT STARTED |
| `GOV-073` | SHELTER-WEBSITE-MASTER-REQUIREMENTS.md = SSOT بلا فقدان تفاصيل | APPROVED | P0 | NOT STARTED |
| `GOV-074` | docs/PENDING-OWNER-INPUT.md (Pending Register) — فقط ما يحتاج الـOwner | APPROVED | P0 | NOT STARTED |
| `GOV-075` | REQUIREMENTS-TRACEABILITY-MATRIX.md (Requirement → … → Test) | APPROVED | P0 | NOT STARTED |
| `GOV-076` | Implementation Plan واحدة للمشروع كامل — لا خطط متعارضة | APPROVED | P0 | NOT STARTED |
| `GOV-077` | تعريف الأولويات P0–P3 (لا استخدام عشوائي) | APPROVED | P1 | PARTIAL |
| `GOV-078` | No Duplicate Architecture — ONE SOURCE OF TRUTH | APPROVED | P0 | PARTIAL |
| `GOV-079` | CLAUDE.md / تعليمات المشروع: مراجع مختصرة للـSource of Truth + قواعد الـTooling | APPROVED WITH CONDITIONS | P1 | PARTIAL |
| `GOV-080` | صيغة تقرير التدقيق: Executive Summary (A–M) + تعارضات + Blocking فقط | APPROVED | P1 | NOT STARTED |
| `GOV-081` | بوابة M27: لا Feature جديدة قبل اكتمال التدقيق؛ البوابات السابقة تبقى | APPROVED | P0 | PARTIAL |
| `GOV-082` | بعد الوثائق السبع: تنفيذ العمل الموافق عليه مع احترام البوابات | APPROVED WITH CONDITIONS | P0 | NOT STARTED |
| `GOV-083` | صيانة الموقع القديم مسار منفصل يُسجل في Risk Register ولا يشغل المشروع | APPROVED | P2 | IMPLEMENTED — NOT TESTED |
| `GOV-084` | نطاق مهمة M24: Tooling + Quality Infrastructure فقط | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `GOV-085` | docs/FRONTEND-TOOLING.md | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `GOV-086` | Definition of Done — الموافقات المطلوبة قبل اعتبار الموقع Finished | APPROVED | P0 | NOT STARTED |
| `GOV-087` | قاعدة التوضيح النهائية — OWNER APPROVED · FROZEN · GLOBAL PROJECT RULE | FROZEN | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-088` | التعارض غير المحسوم: A / B / Impact / Recommendation ثم السؤال | FROZEN | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-089` | لا إصلاح صامت لبيانات الأعمال — CONFLICT DETECTED | FROZEN | P0 | NOT STARTED |
| `GOV-090` | طريقة السؤال: واضح، واحد واحد، مرتب بالأولوية، وتسجيل الجواب | FROZEN | P0 | IMPLEMENTED — NOT TESTED |
| `GOV-091` | الخدمات الخارجية والتغييرات الحساسة في Production تحتاج موافقة صريحة | FROZEN | P0 | NOT STARTED |

## 39 · Content & Copy — [التفاصيل](master-requirements/39-content-copy.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `CONTENT-001` | جرد محتوى الموقع القديم وتصنيفه KEEP/FIX/REMOVE/MISSING/VERIFY | APPROVED | P0 | PARTIAL |
| `CONTENT-002` | تحقق الـOwner قبل نشر Awards/Statistics/Claims/SEO texts/FAQ/Blog | APPROVED | P0 | TESTED |
| `CONTENT-003` | لا يكتب Claude Facts عن SHELTER — الـAI ينظم ويعيد الصياغة ويقترح فقط | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `CONTENT-004` | لا Product copy من Claude ولا تخمين وصف/مكونات/حساسية/قيم غذائية | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `CONTENT-005` | القيمتان 2018 و«منذ 2022» = OLD OR INCORRECT — لا تُستخدمان أبدًا | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `CONTENT-006` | أي صياغة تسويقية عن عدد السنوات أو تاريخ المناسبة تُعرض على الـOwner قبل النشر | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `CONTENT-007` | عدد السنوات يُحسب ديناميكيًا من سنة التأسيس 2019 وليس نصًا ثابتًا | APPROVED | P2 | NOT STARTED |
| `CONTENT-008` | ادعاءات الرئيسية القديمة = PENDING OWNER VERIFICATION ولا تُنقل | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `CONTENT-009` | ذكر مشغل الحلويات (Production / Pastry Facility) — مستقبلًا وبموافقة على الصياغة | DEFERRED | P3 | NOT STARTED |
| `CONTENT-010` | تسميات قنوات التواصل للرقمين 0799338445 و0799530383 | APPROVED | P1 | NOT STARTED |
| `CONTENT-011` | نص زر واتساب (مبدئي): «راسلنا على واتساب» / «Message us on WhatsApp» | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `CONTENT-012` | الرسالة المسبقة لواتساب حسب الفرع — مسودة فقط؛ اقتراح نص قصير وطبيعي AR/EN | PENDING OWNER INPUT | P1 | NOT STARTED |
| `CONTENT-013` | نصوص الجذر RG-04 / RG-05 = DRAFT ONLY — Placeholder واضح في الـWireframe فقط | APPROVED | P1 | PARTIAL |
| `CONTENT-014` | مرحلة Copy: 3 خيارات لكل نص (Minimal · Brand-led · SEO-aware) AR/EN | DEFERRED | P1 | NOT STARTED |
| `CONTENT-015` | لا حشو كلمات SEO ولا صياغة تبدو مولّدة بالذكاء الاصطناعي | APPROVED | P1 | NOT STARTED |
| `CONTENT-016` | صفحة Catering / B2B: لا نشر ولا خدمات من عند Claude قبل تفاصيل الـOwner | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `CONTENT-017` | لا التباس في كلمة Events بين حملات SHELTER وخدمات Catering/B2B | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `CONTENT-018` | اقتراح Naming نهائي AR/EN للمفهومين (Events/Campaigns و Catering/B2B) | PENDING OWNER INPUT | P2 | NOT STARTED |
| `CONTENT-019` | لا تُملّ المستخدم (DO NOT BORE THE USER) | APPROVED | P1 | NOT STARTED |
| `CONTENT-020` | أسماء المنتجات الإنجليزية في العرض ALL CAPS | APPROVED | P2 | NOT STARTED |
| `CONTENT-021` | معيار التسمية: تصحيح الإملاء الواضح في normalized/display فقط | FROZEN | P0 | FROZEN |
| `CONTENT-022` | تصحيحات التسمية المعتمدة (FRAPPE · TURKISH SINGLE/DOUBLE · آيس شيكن …) | FROZEN | P1 | FROZEN |
| `CONTENT-023` | الاسم العربي لـICED SHAKEN SALTED CARAMEL بانتظار موافقة الـOwner | PENDING OWNER INPUT | P2 | NOT STARTED |
| `CONTENT-024` | لغة الـDashboard سهلة للمالك (عنوان واضح · Reason · Suggested) | APPROVED | P1 | NOT STARTED |

## 40 · Internationalization (AR/EN · RTL/LTR · Global) — [التفاصيل](master-requirements/40-internationalization-ar-en-rtl-ltr-global.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `I18N-001` | العربية أساسية والإنجليزية نسخة كاملة | APPROVED | P0 | NOT STARTED |
| `I18N-002` | جاهزية لغات مستقبلية — لا لغة ثالثة معتمدة | APPROVED | P1 | NOT STARTED |
| `I18N-003` | RTL/LTR صحيح بلا hacks أحادية الاتجاه | APPROVED | P0 | NOT STARTED |
| `I18N-004` | كل Responsive Layout يُختبر مرتين: AR RTL و EN LTR | APPROVED | P0 | NOT STARTED |
| `I18N-005` | المنيو ثنائي اللغة: RTL/LTR، أولوية الأسماء معكوسة بالإنجليزية | APPROVED | P1 | NOT STARTED |
| `I18N-006` | صيغة السعر: 3.50 د.أ (AR) · 3.50 JOD (EN) | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `I18N-007` | RG-03: العربية أولًا بصريًا في الجذر (الأردن، Mobile) | APPROVED | P1 | NOT STARTED |
| `I18N-008` | Language Switch في الـNavigation — دراسة وتصميم | PENDING OWNER INPUT | P1 | NOT STARTED |
| `I18N-009` | أسماء المنتجات: لا ترجمة نهائية تلقائية — الاعتماد للـOwner وحده | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `I18N-010` | قائمة الأصناف التي تحتاج اسمًا عربيًا (Suggested Arabic Name + Reason) | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `I18N-011` | الأسماء العربية من المصدر (152) = SOURCE-PROVIDED · PENDING OWNER REVIEW | PENDING OWNER INPUT | P0 | IMPLEMENTED — NOT TESTED |
| `I18N-012` | طبقات الاسم العربي: source ثابت · normalized/display = الاسم المعتمد | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `I18N-013` | أسماء عربية معتمدة (M20: G6 · G7 · G8 · G9) | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `I18N-014` | الاسم العربي لـICED SHAKEN SALTED CARAMEL — معلّق | PENDING OWNER INPUT | P2 | NOT STARTED |
| `I18N-015` | Dashboard RTL/LTR + Sidebar حسب الاتجاه (اقتراح) | APPROVED | P1 | NOT STARTED |

## 41 · Tooling — [التفاصيل](master-requirements/41-tooling.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `TOOL-001` | كل أداة في مكانها — لا استخدام لمجرد التوفر | APPROVED | P2 | PARTIAL |
| `TOOL-002` | خريطة مهارات/أدوات Claude المعتمدة والتحقق من توفرها | PENDING VERIFICATION | P2 | PARTIAL |
| `TOOL-003` | الأدوات المحلية المجانية مسموحة بلا موافقة مسبقة | APPROVED | P2 | IMPLEMENTED — NOT TESTED |
| `TOOL-004` | Toolchain صغيرة وقوية — أقل مجموعة أدوات بغرض واضح | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `TOOL-005` | فحص البيئة قبل أي تثبيت | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `TOOL-006` | لا أدوات/مكتبات مكررة | APPROVED | P1 | TESTED |
| `TOOL-007` | SHELTER FRONTEND TOOLING MATRIX (قبل أي تثبيت) | APPROVED | P1 | TESTED |
| `TOOL-008` | سياسة التثبيت: فقط المجموعة التقنية التي لا تحتاج قرارًا تجاريًا من الـOwner | APPROVED | P1 | TESTED |
| `TOOL-009` | كل dependency لها تكلفة: تفضيل الـplatform وتوثيق كل إضافة | APPROVED | P1 | PARTIAL |
| `TOOL-010` | ضبط الأدوات وبناء بنية الجودة (scripts، a11y، perf، Storybook، tokens، images) | APPROVED | P1 | PARTIAL |
| `TOOL-011` | Health check وتقرير الحالة | APPROVED | P1 | PARTIAL |
| `TOOL-012` | Tooling Registry لكل الأدوات المناقشة والمثبتة (ومهارات Claude) | APPROVED | P2 | PARTIAL |
| `TOOL-013` | Playwright — أداة الاختبار الأساسية (مثبتة) | APPROVED | P1 | TESTED |
| `TOOL-014` | axe-core — فحص الوصولية الآلي (مثبت) | APPROVED | P1 | TESTED |
| `TOOL-015` | Google Lighthouse — مثبت مع بوابات وأسباب جذرية | APPROVED | P1 | TESTED |
| `TOOL-016` | Sharp — خط معالجة صور المنتجات (مثبت) | APPROVED | P1 | TESTED |
| `TOOL-017` | Unlighthouse — قبل الإطلاق وفي مراحل QA الكبرى (بعد فحص التوافق) | DEFERRED | P1 | NOT STARTED |
| `TOOL-018` | sitespeed.io — مرحلة QA وعند الحاجة، ليس في كل build | DEFERRED | P2 | NOT STARTED |
| `TOOL-019` | shadcn/ui — أساس هندسي فقط، والشكل الافتراضي ممنوع | SUPERSEDED | P1 | NOT STARTED |
| `TOOL-020` | Radix UI Primitives — الأساس السلوكي والوصولي (بلا تكرار) | SUPERSEDED | P1 | NOT STARTED |
| `TOOL-021` | Tailwind CSS — إذا دعمه الـstack وبعد tokens العلامة | SUPERSEDED | P1 | NOT STARTED |
| `TOOL-022` | Motion — مكتبة الحركة الأساسية (مشروطة بالـstack) | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `TOOL-023` | Lenis — اختياري، ليس عامًا، وليس على المنيو إلا بإثبات | APPROVED WITH CONDITIONS | P3 | NOT STARTED |
| `TOOL-024` | Storybook — ورشة مكونات SHELTER Design System | APPROVED | P1 | NOT STARTED |
| `TOOL-025` | Lucide — نظام أيقونات واحد موحد | APPROVED | P2 | NOT STARTED |
| `TOOL-026` | React Aria — فقط عند حاجة فعلية | SUPERSEDED | P3 | NOT STARTED |
| `TOOL-027` | المجموعة النهائية المطلوبة (مشروطة بتوافق الـstack) | APPROVED WITH CONDITIONS | P1 | PARTIAL |
| `TOOL-028` | Toolchain audit أولًا ثم تثبيت الناقص المفيد فقط (M37) | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `TOOL-029` | Storybook = مرجع الـDesign System بحالات كاملة | APPROVED | P0 | NOT STARTED |
| `TOOL-030` | الوصولية: axe آليًا + فحص يدوي إلزامي | APPROVED | P0 | PARTIAL |
| `TOOL-031` | Playwright Core QA للصفحات والتدفقات | APPROVED | P0 | PARTIAL |
| `TOOL-032` | Visual regression ببصمات ثابتة وحدود صارمة | APPROVED | P0 | PARTIAL |
| `TOOL-033` | Chrome DevTools أثناء التطوير (لا الاعتماد على Screenshots) | APPROVED | P0 | NOT STARTED |
| `TOOL-034` | Lighthouse CI بميزانيات ومراقبة LCP/INP/CLS | APPROVED | P0 | PARTIAL |
| `TOOL-035` | فحص الأنواع والـLint والتنسيق الصارم | APPROVED | P0 | NOT STARTED |
| `TOOL-036` | Knip لاكتشاف الكود الميت — بلا حذف تلقائي | APPROVED | P2 | NOT STARTED |
| `TOOL-037` | اختبارات وحدات لمنطق الأعمال المهم فقط | APPROVED | P0 | NOT STARTED |
| `TOOL-038` | Security QA: Semgrep CE + Gitleaks + Dependency/Trivy + ZAP على Staging | APPROVED | P0 | NOT STARTED |
| `TOOL-039` | SEO crawl واحد + Structured data QA من Master Data | APPROVED | P0 | NOT STARTED |
| `TOOL-040` | خط جودة GitHub Actions ببوابات حرجة وتقسيم ذكي | APPROVED | P0 | NOT STARTED |
| `TOOL-041` | استخدام أدوات Claude فعليًا والتوثيق الرسمي قبل أي API | APPROVED | P0 | PARTIAL |
| `TOOL-042` | إعادة استخدام المكونات وتنظيم الكود وحدود الوحدات | APPROVED | P0 | NOT STARTED |
| `TOOL-043` | Security headers مختبرة | APPROVED | P0 | NOT STARTED |
| `TOOL-044` | سياسة الحزم وضبط حجم الحزمة وأداء الـDashboard | APPROVED | P0 | NOT STARTED |
| `TOOL-045` | فحص جودة التصميم قبل اعتبار أي صفحة جاهزة | APPROVED | P0 | NOT STARTED |
| `TOOL-046` | خط الصور: Original → Variants → WebP/AVIF مع srcset/sizes | APPROVED | P0 | PARTIAL |
| `TOOL-047` | أدوار أدوات التصميم: THINK · DISCOVER · PRIMITIVES · ANIMATE — والـDesign System هو المرجع النهائي | APPROVED | P0 | NOT STARTED |

## 42 · Privacy & Legal — [التفاصيل](master-requirements/42-privacy-legal.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `PRIV-001` | صفحة سياسة الخصوصية (Privacy Policy) معتمدة ضمن الصفحات | APPROVED | P0 | NOT STARTED |
| `PRIV-002` | صفحة الشروط / Terms عند الحاجة القانونية | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `PRIV-003` | التحقق مع الـOwner قبل نشر أي معلومة قانونية أو معلومات خصوصية | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `PRIV-004` | نموذج التوظيف: Data minimisation وسياسة خصوصية قبل الإطلاق | APPROVED | P1 | NOT STARTED |
| `PRIV-005` | جاهزية Consent: Cookie Consent · Analytics Consent · Marketing Consent | APPROVED | P1 | NOT STARTED |
| `PRIV-006` | Google Consent Mode عند الحاجة | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `PRIV-007` | لا تفعيل لأي Advertising Tracking بدون موافقة الـOwner | APPROVED | P0 | NOT STARTED |
| `PRIV-008` | أي Tracking يراعي Privacy Policy · Cookie Policy · Consent | APPROVED | P0 | NOT STARTED |
| `PRIV-009` | لا نشر لتتبع يحتاج إفصاحًا قانونيًا قبل تحديث صفحات الخصوصية | APPROVED | P0 | NOT STARTED |
| `PRIV-010` | التقييمات والشهادات (القديمة وGBP): لا نقل بدون موافقة الـOwner ومراجعة الحقوق | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `PRIV-011` | تسجيل استعلامات البحث بشكل Privacy-conscious بدون PII غير ضروري | APPROVED | P0 | NOT STARTED |
| `PRIV-012` | ممنوع تخزين الموقع الدقيق للمستخدم (Location analytics) | APPROVED | P0 | NOT STARTED |
| `PRIV-013` | تسجيل الأخطاء بدون تخزين PII | APPROVED | P0 | NOT STARTED |
| `PRIV-014` | تقليل البيانات: لا نسخ GA4 raw data كاملة بدون سبب | APPROVED | P1 | NOT STARTED |

## 43 · UX Principles — [التفاصيل](master-requirements/43-ux-principles.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `UX-001` | Simplicity wins في كل قرار | APPROVED | P1 | PARTIAL |
| `UX-002` | مبادئ UX العالمية — الموقع سهل جدًا | APPROVED | P1 | PARTIAL |
| `UX-003` | كل صفحة تجيب بسرعة عن أربعة أسئلة — Do not bore the user | APPROVED | P1 | NOT STARTED |
| `UX-004` | Friction Audit لكل Flow | APPROVED | P1 | PARTIAL |
| `UX-005` | أولويات الـUX: clarity · speed · mobile · discoverability · a11y · consistency | APPROVED | P1 | PARTIAL |
| `UX-006` | مراجعة تصميم قبل اعتبار أي صفحة مكتملة (ui-ux-pro-max) | APPROVED | P0 | NOT STARTED |

## 44 · Careers & Recruitment — [التفاصيل](master-requirements/44-careers-recruitment.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `CAREERS-001` | نظام التوظيف جزء أصلي من SHELTER WEBSITE + OWNER DASHBOARD — ليس تكاملًا خارجيًا | FROZEN | P0 | NOT STARTED |
| `CAREERS-002` | ممنوع: HubSpot · Google Forms · Airtable · CRM خارجي · Database محلية · Falcon | FROZEN | P0 | NOT STARTED |
| `CAREERS-003` | المعمارية: Public Website → HTTPS → SHELTER Backend → Private DB on Cloudways → Private Storage → Owner Dashboard | FROZEN | P0 | NOT STARTED |
| `CAREERS-004` | Cloudways Architecture Audit قبل أي Database أو Storage (17 بندًا) | APPROVED | P0 | NOT STARTED |
| `CAREERS-005` | Application/Database معزولة للموقع الجديد — لا استخدام قاعدة WordPress القديمة كمخزن | APPROVED WITH CONDITIONS | P1 | NOT STARTED |
| `CAREERS-006` | روابط التوظيف: /ar/careers/ النموذج الأساسي · /en/careers/ محتوى إنجليزي · Apply → التدفق العربي | FROZEN | P0 | NOT STARTED |
| `CAREERS-007` | واجهة النموذج عربية فقط — والحقول تقبل العربية والإنجليزية والخليط | FROZEN | P0 | NOT STARTED |
| `CAREERS-008` | SEO تقني للتدفق العربي فقط: noindex لصفحات النموذج الفرعية والنجاح والمتابعة | APPROVED | P1 | NOT STARTED |
| `CAREERS-009` | قاعدة الإلزام: كل الحقول Required — والحقل الشرطي Required بمجرد ظهوره | FROZEN | P0 | NOT STARTED |
| `CAREERS-010` | حقل 1: الاسم الكامل | FROZEN | P0 | NOT STARTED |
| `CAREERS-011` | حقل 2: رقم الهاتف — إدخال حر بلا Country Selector ولا فرض +962 | FROZEN | P0 | NOT STARTED |
| `CAREERS-012` | حقل 3: البريد الإلكتروني | FROZEN | P0 | NOT STARTED |
| `CAREERS-013` | حقل 4: الجنس — ذكر / أنثى | FROZEN | P0 | NOT STARTED |
| `CAREERS-014` | حقل 5: تاريخ الميلاد — 3 Selects (اليوم/الشهر/السنة) وحساب العمر تلقائيًا | FROZEN | P0 | NOT STARTED |
| `CAREERS-015` | حقل 6: الحالة الاجتماعية — أعزب / متزوج / أخرى | FROZEN | P0 | NOT STARTED |
| `CAREERS-016` | حقل 7: الجنسية — أردني / غير أردني | FROZEN | P0 | NOT STARTED |
| `CAREERS-017` | حقل شرطي: الرقم الوطني (للأردني) — Required وحساس | FROZEN | P0 | NOT STARTED |
| `CAREERS-018` | حقل شرطي: «ما هي جنسيتك؟» (لغير الأردني) — نص حر | FROZEN | P0 | NOT STARTED |
| `CAREERS-019` | حقل شرطي: رقم جواز السفر أو رقم وثيقة الهوية (لغير الأردني) — حساس | FROZEN | P0 | NOT STARTED |
| `CAREERS-020` | حقل 8: المدينة — Dropdown لمدن الأردن (ليست كتابة حرة ولا المحافظات فقط) | FROZEN | P0 | NOT STARTED |
| `CAREERS-021` | قائمة مدن الأردن (Dataset موثوق مخزن داخليًا) — PENDING DATA VERIFICATION حتى التحقق | PENDING VERIFICATION | P1 | NOT STARTED |
| `CAREERS-022` | حقل 9: المنطقة — نص حر (لا عنوان كامل) | FROZEN | P0 | NOT STARTED |
| `CAREERS-023` | حقل 10: الوظيفة المتقدم لها — نص حر بلا Autocorrection | FROZEN | P0 | NOT STARTED |
| `CAREERS-024` | حقل 11: المؤهل العلمي — 6 خيارات فقط | FROZEN | P0 | NOT STARTED |
| `CAREERS-025` | حقل 12: سنوات الخبرة — 6 فئات | FROZEN | P0 | NOT STARTED |
| `CAREERS-026` | حقل 13: خبرة سابقة في نفس المجال أو الوظيفة — نعم / لا | FROZEN | P0 | NOT STARTED |
| `CAREERS-027` | حقل 14: هل تعمل حاليًا؟ — نعم / لا | FROZEN | P0 | NOT STARTED |
| `CAREERS-028` | حقل 15: الراتب المتوقع — رقمي فقط بالدينار (يُعرض 450 د.أ) | FROZEN | P0 | NOT STARTED |
| `CAREERS-029` | حقل 16: رخصة القيادة — نعم / لا (بدون نوع الرخصة) | FROZEN | P0 | NOT STARTED |
| `CAREERS-030` | حقل 17: ملاحظات إضافية — Text Area إلزامي | FROZEN | P0 | NOT STARTED |
| `CAREERS-031` | قائمة «لا تسأل»: حقول ممنوع إضافتها أو اقتراحها | FROZEN | P0 | NOT STARTED |
| `CAREERS-032` | المنطق الشرطي: الإظهار/الإخفاء والإلزام والتخزين | FROZEN | P0 | NOT STARTED |
| `CAREERS-033` | حقل 18: المرفقات — منطقة رفع واحدة (CV + شهادات + دورات + مستندات) | FROZEN | P0 | NOT STARTED |
| `CAREERS-034` | أنواع الملفات: الواجهة غير محصورة بـPDF/DOC/DOCX — ومنع الأنواع الخطرة بفحص متعدد | FROZEN | P0 | NOT STARTED |
| `CAREERS-035` | حجم الملفات: لا حدود Business مخترعة — حدود تقنية آمنة من فحص البيئة | FROZEN | P0 | NOT STARTED |
| `CAREERS-036` | CV Auto-Detection محلي/Server-side — وعند عدم الثقة يُسأل المتقدم | FROZEN | P0 | NOT STARTED |
| `CAREERS-037` | تخزين خاص للـCVs والمرفقات — لا Public URL، والوصول بعد Authentication + Owner authorization | FROZEN | P0 | NOT STARTED |
| `CAREERS-038` | حقل 19: الإقرار والموافقة — Checkbox إلزامي بنص معتمد وسجل مُنسخ (versioned) | FROZEN | P0 | TESTED |
| `CAREERS-039` | Server-side validation إلزامي عند Submit | APPROVED | P0 | NOT STARTED |
| `CAREERS-040` | رقم طلب فريد Server-side (صيغة مقترحة JOB-2026-00125) | FROZEN | P0 | TESTED |
| `CAREERS-041` | صفحة النجاح بالعربي — بلا Email ولا WhatsApp تلقائي | FROZEN | P0 | NOT STARTED |
| `CAREERS-042` | متابعة طلب التوظيف بدون حساب: رقم الطلب + رقم الهاتف فقط | FROZEN | P0 | NOT STARTED |
| `CAREERS-043` | لا تعديل للطلب من المتقدم — التصحيح الإداري Owner only + Audit Log | FROZEN | P0 | NOT STARTED |
| `CAREERS-044` | المتقدمون المكررون: التقديم المتعدد مسموح + ربط بلا دمج | FROZEN | P1 | NOT STARTED |
| `CAREERS-045` | حماية الرقم الوطني ورقم جواز السفر/الوثيقة (Sensitive Data) | APPROVED | P0 | NOT STARTED |
| `CAREERS-046` | الحالات الداخلية السبع + سجل كل تغيير حالة | FROZEN | P0 | TESTED |
| `CAREERS-047` | Public Status Mapping — المتقدم لا يرى الحالة الداخلية دائمًا | FROZEN | P0 | TESTED |
| `CAREERS-048` | إدارة المقابلات: تاريخ · وقت · مكان (Dropdown: DRIVE / HOUSE) · ملاحظات داخلية | FROZEN | P1 | TESTED |
| `CAREERS-049` | لا نظام تقييم: لا Score ولا نجوم ولا AI ranking | FROZEN | P1 | TESTED |
| `CAREERS-050` | وحدة التوظيف OWNER ONLY — Server-side Owner Authorization | FROZEN | P0 | TESTED |
| `CAREERS-051` | قسم «التوظيف / Careers / Recruitment» داخل SHELTER OWNER DASHBOARD (IA مقترحة) | FROZEN | P1 | NOT STARTED |
| `CAREERS-052` | Recruitment Overview: 8 بطاقات قابلة للنقر — تشغيلية أولًا | FROZEN | P1 | TESTED |
| `CAREERS-053` | الفترة الزمنية: افتراضي «هذا الشهر» — بلا نسب مقارنة | FROZEN | P1 | TESTED |
| `CAREERS-054` | شارة «جديد»: فصل Application Status عن Read Status | FROZEN | P1 | TESTED |
| `CAREERS-055` | إشعارات الطلب الجديد: داخل الـDashboard فقط (لا Email ولا WhatsApp) | FROZEN | P1 | PARTIAL |
| `CAREERS-056` | جدول الطلبات: 7 أعمدة افتراضية والترتيب الأحدث أولًا | FROZEN | P1 | TESTED |
| `CAREERS-057` | الترتيب المتقدم: 6 خيارات | FROZEN | P1 | TESTED |
| `CAREERS-058` | Global Quick Search حي أثناء الكتابة (مع Debounce) | FROZEN | P1 | TESTED |
| `CAREERS-059` | Advanced Filters (10 على الأقل) مع دمج عدة Filters | FROZEN | P1 | TESTED |
| `CAREERS-060` | Saved Filters بأسماء يحددها الـOwner | FROZEN | P1 | TESTED |
| `CAREERS-061` | تخصيص الأعمدة: Show/Hide · Drag & Drop · Reset — مع حفظ التفضيل | FROZEN | P1 | TESTED |
| `CAREERS-062` | كثافة الجدول Comfortable/Compact + عرض مناسب للهاتف | FROZEN | P1 | TESTED |
| `CAREERS-063` | عدد الصفوف: 25 / 50 / 100 — الافتراضي على Desktop = 50 مع تذكر آخر اختيار | FROZEN | P1 | TESTED |
| `CAREERS-064` | Traditional Pagination — لا Infinite Scroll لقائمة الإدارة | FROZEN | P1 | TESTED |
| `CAREERS-065` | Quick View (Side Panel) — الحد الأدنى للمحتوى | FROZEN | P1 | TESTED |
| `CAREERS-066` | صفحة الطلب الكاملة بترتيب UX واضح (ليست Dump لحقول DB) | FROZEN | P1 | TESTED |
| `CAREERS-067` | الملاحظات الداخلية: ملاحظات متعددة مستقلة — بلا Overwrite | FROZEN | P1 | TESTED |
| `CAREERS-068` | Bulk Actions: تغيير الحالة · Archive · Export · Download Attachments — بلا Bulk Permanent Delete | FROZEN | P1 | TESTED |
| `CAREERS-069` | Bulk Status Change: تأكيد واحد واضح + Audit Log | FROZEN | P1 | TESTED |
| `CAREERS-070` | Archive ثم Permanent Delete (Owner only، تأكيد قوي، Tombstone) — لا حذف تلقائي | FROZEN | P0 | TESTED |
| `CAREERS-071` | الطلبات القديمة: Last Updated + تنبيه اختياري — بلا حذف تلقائي | FROZEN | P2 | TESTED |
| `CAREERS-072` | Export: Excel · CSV · PDF — والهوية Masked افتراضيًا | FROZEN | P1 | TESTED |
| `CAREERS-073` | تنزيل المرفقات: Action منفصل يجمعها في ZIP — Owner only ومسجل | FROZEN | P1 | TESTED |
| `CAREERS-074` | Audit Log للتوظيف: 13 حدثًا × 7 حقول — بلا محتوى ملفات حساس | APPROVED | P0 | NOT STARTED |
| `CAREERS-075` | Settings للتوظيف: قوائم قابلة للإدارة (المدن · أماكن المقابلة) | APPROVED | P2 | TESTED |
| `CAREERS-076` | ممنوع إرسال PII إلى GA4 · GTM · Google Analytics · Search Console · أي Analytics خارجية | APPROVED | P0 | NOT STARTED |
| `CAREERS-077` | أحداث Privacy-safe: careers_page_view · application_started · application_submitted · application_error | APPROVED WITH CONDITIONS | P2 | NOT STARTED |
| `CAREERS-078` | نموذج البيانات: Normalized schema (11 كيانًا مفاهيميًا) بلا جداول مكررة | APPROVED | P0 | PARTIAL |
| `CAREERS-079` | مطابقة التكرار: إشارات وثقة فقط — لا دمج أو حذف تلقائي | FROZEN | P1 | NOT STARTED |
| `CAREERS-080` | أمان الملفات المرفوعة (Untrusted content) — 13 ضابطًا | APPROVED | P0 | NOT STARTED |
| `CAREERS-081` | أمان النموذج العام (11 ضابطًا) — لا CAPTCHA خارجي تلقائيًا | APPROVED | P0 | NOT STARTED |
| `CAREERS-082` | أمان صفحة المتابعة: Rate limits · Progressive cooldown · رسائل عامة · Public Status فقط | APPROVED | P0 | NOT STARTED |
| `CAREERS-083` | اختبارات أمان الـDashboard: 6 حالات يجب أن تفشل كلها | APPROVED | P0 | NOT STARTED |
| `CAREERS-084` | Responsive: النموذج والـDashboard ممتازان على 14 عرضًا — ومهام الـOwner على الهاتف | FROZEN | P1 | NOT STARTED |
| `CAREERS-085` | Accessibility للنموذج (12 بندًا) — والخطأ لا يعتمد على اللون وحده | APPROVED | P1 | NOT STARTED |
| `CAREERS-086` | تجربة النموذج: SHELTER Premium Minimal Arabic-first — ليس نموذجًا حكوميًا أو HubSpot | FROZEN | P1 | NOT STARTED |
| `CAREERS-087` | هيكل النموذج: مجموعات منطقية — صفحة واحدة مقابل خطوات قصيرة يُحسم بالاختبار (مفوض) | APPROVED | P1 | NOT STARTED |
| `CAREERS-088` | الأداء: نموذج خفيف — لا مكتبات Dashboard على الصفحة العامة — رفع غير مجمِّد | APPROVED | P1 | NOT STARTED |
| `CAREERS-089` | حالات الفشل (12 حالة) — لا ضياع لكل المدخلات بسبب ملف واحد | APPROVED | P1 | NOT STARTED |
| `CAREERS-090` | Backup Strategy لبيانات التوظيف والمرفقات — تحقق لا افتراض | APPROVED | P0 | NOT STARTED |
| `CAREERS-091` | Self-contained: لا خدمات خارجية تلقائيًا — اعرض الحاجة والبديل المحلي أولًا | FROZEN | P0 | NOT STARTED |
| `CAREERS-092` | سلامة التطوير والإنتاج: لا Falcon · لا DB محلية · لا Migration خطرة بلا Backup · لا حذف · لا عبث بـWordPress | APPROVED | P0 | NOT STARTED |
| `CAREERS-093` | الاختبارات المطلوبة قبل اعتبار النظام جاهزًا (M28 §73) | APPROVED | P0 | NOT STARTED |
| `CAREERS-094` | Playwright للتدفقات الحرجة (15 تدفقًا) | APPROVED | P0 | NOT STARTED |
| `CAREERS-095` | UX لوحة التوظيف: SHELTER Design System — لا Admin Template — الإجراءات الحساسة أكثر تحفظًا | FROZEN | P1 | NOT STARTED |
| `CAREERS-096` | وثائق التنفيذ السبع + إضافة القرارات إلى Master / Decision Register / Traceability | APPROVED | P1 | PARTIAL |
| `CAREERS-097` | ترتيب التنفيذ: 17 مرحلة — وبوابة Phase 8 قبل أي Backend | APPROVED | P0 | PARTIAL |
| `CAREERS-098` | لا إعادة فتح ولا أسئلة مكررة — الاستثناءات الأربعة فقط، والتقني يقرره Claude | FROZEN | P0 | NOT STARTED |
| `CAREERS-099` | Definition of Done لنظام التوظيف (23 بندًا) | APPROVED | P0 | NOT STARTED |

## 45 · Dynamic Experience Engine — [التفاصيل](master-requirements/45-dynamic-experience-engine.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `DX-001` | طبقة التجربة الديناميكية: الموقع يتفاعل ولا يبقى ثابتًا | FROZEN | P1 | NOT STARTED |
| `DX-002` | صفحة/تجربة SHELTER Family | FROZEN | P1 | TESTED |
| `DX-003` | حقول الملف العام للموظف | FROZEN | P1 | TESTED |
| `DX-004` | ممنوع عرض بيانات الموظف الخاصة | FROZEN | P0 | NOT STARTED |
| `DX-005` | النشر بالاختيار: لا موظف علنيًا إلا بتفعيل الـOwner | FROZEN | P0 | TESTED |
| `DX-006` | إدارة SHELTER Family بلا كود | FROZEN | P1 | TESTED |
| `DX-007` | الموظف المثالي لهذا الشهر (Employee of the Month) | FROZEN | P1 | NOT STARTED |
| `DX-008` | حالات الموظف المثالي + الأرشيف | FROZEN | P1 | NOT STARTED |
| `DX-009` | مواضع ظهور الموظف المثالي | FROZEN | P1 | NOT STARTED |
| `DX-010` | نظام الإعلانات الديناميكي وأنواع العرض | FROZEN | P1 | PARTIAL |
| `DX-011` | حقول التجربة/الإعلان | FROZEN | P1 | PARTIAL |
| `DX-012` | لا حالة فارغة: لا شيء نشط = لا مكون | FROZEN | P0 | TESTED |
| `DX-013` | بدء وانتهاء تلقائي + تجاوز يدوي + إيقاف طارئ | FROZEN | P0 | TESTED |
| `DX-014` | المشاركة في الفعاليات وأسطح الظهور | FROZEN | P1 | PARTIAL |
| `DX-015` | محرك التجارب الموسمية القابل للإعداد | FROZEN | P1 | NOT STARTED |
| `DX-016` | مثال عيد الاستقلال الأردني — حدود المعالجة | FROZEN | P1 | NOT STARTED |
| `DX-017` | مثال الكريسماس — حدود المعالجة | FROZEN | P1 | NOT STARTED |
| `DX-018` | أنواع التجارب وقواعد العرض | FROZEN | P1 | PARTIAL |
| `DX-019` | وحدة Experiences في الـDashboard | FROZEN | P1 | PARTIAL |
| `DX-020` | شاشة ACTIVE NOW + Disable Now | FROZEN | P0 | TESTED |
| `DX-021` | محرك الأولوية والتعارض | FROZEN | P0 | TESTED |
| `DX-022` | العدّ التنازلي (اختياري) | FROZEN | P2 | NOT STARTED |
| `DX-023` | المناطق الزمنية | FROZEN | P0 | TESTED |
| `DX-024` | تكامل Media Center: ارفع مرة واستخدم في كل مكان | FROZEN | P1 | PARTIAL |
| `DX-025` | إدارة كاملة بلا كود | FROZEN | P0 | PARTIAL |
| `DX-026` | قواعد الحركة | FROZEN | P1 | NOT STARTED |
| `DX-027` | Reduced motion لكل تجربة موسمية | FROZEN | P0 | NOT STARTED |
| `DX-028` | معالجة موبايل لكل تجربة | FROZEN | P0 | NOT STARTED |
| `DX-029` | ميزانية أداء لكل تجربة + تحميل مشروط | FROZEN | P0 | NOT STARTED |
| `DX-030` | سلامة التصميم: العودة للحالة الطبيعية تمامًا | FROZEN | P0 | NOT STARTED |
| `DX-031` | المعاينة قبل التفعيل (بتاريخ ووقت محددين) | FROZEN | P1 | PARTIAL |
| `DX-032` | أوامر الجدولة | FROZEN | P1 | TESTED |
| `DX-033` | سجل التدقيق للتجارب | FROZEN | P1 | PARTIAL |
| `DX-034` | سجل النسخ للتجارب | FROZEN | P1 | PARTIAL |
| `DX-035` | أحداث القياس للتجارب (بلا PII) | FROZEN | P1 | NOT STARTED |
| `DX-036` | خصوصية الموظفين: فصل الملف العام عن سجل HR | FROZEN | P0 | TESTED |
| `DX-037` | العزل عند الفشل: الموقع الأساسي مستقل | FROZEN | P0 | TESTED |
| `DX-038` | الهدف النهائي: موقع حي وعودة نظيفة | FROZEN | P1 | NOT STARTED |

## 46 · Platform Quality & Operations — [التفاصيل](master-requirements/46-platform-quality-operations.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `OPS-001` | M32 ملحق لإغلاق النواقص — لا يستبدل السابق: صنّف أولًا ثم أكمل النواقص فقط | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `OPS-002` | لا تكرار — ONE SOURCE OF TRUTH: بحث إلزامي قبل بناء أي نظام P0/P1 | APPROVED | P0 | PARTIAL |
| `OPS-003` | Simplicity wins — اختبار قبول أي Feature (7 أسئلة) وإلا لا تُضاف | APPROVED | P1 | PARTIAL |
| `OPS-004` | نظام يحافظ على نفسه بعد الإطلاق — يُدار من الـOwner Dashboard بلا كود | APPROVED | P0 | NOT STARTED |
| `OPS-005` | الـDashboard تجيب دائمًا عن الأسئلة العشرة — وكل شيء مهم Actionable | APPROVED | P0 | NOT STARTED |
| `OPS-006` | المخرج الأول قبل أي Feature: A–J ثم Wireframes الوحدات الجديدة ثم مراجعة الـOwner | APPROVED | P0 | PARTIAL |
| `OPS-007` | معمارية البيانات: ربط الأنظمة الجديدة بالكيانات القائمة — لا Data Silos | APPROVED | P0 | NOT STARTED |
| `OPS-008` | الوثائق التسع عشرة لأنظمة M32 (§51) | APPROVED | P0 | NOT STARTED |
| `OPS-009` | مزامنة المرجع: أنظمة M32 في MASTER REQUIREMENTS وDECISION REGISTER وTRACEABILITY | APPROVED | P0 | PARTIAL |
| `OPS-010` | أولوية تنفيذ أنظمة M32: P0 / P1 / P2-FUTURE | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `OPS-011` | SHELTER GLOBAL DATA REGISTRY — مصدر واحد للبيانات المتكررة | APPROVED | P0 | NOT STARTED |
| `OPS-012` | لا Hardcode للبيانات المركزية + تحديث كل مواضع الاستخدام + فهرس Usage | APPROVED | P0 | NOT STARTED |
| `OPS-013` | SHELTER FACT REGISTRY — الحقول العشرة والحالات | APPROVED | P0 | NOT STARTED |
| `OPS-014` | ربط الحقائق المهمة بالـFact Registry — المعلّق لا يُنشر، و04 بذرة ثم مصدر واحد | APPROVED | P0 | NOT STARTED |
| `OPS-015` | CHANGE IMPACT PREVIEW — WHAT WILL CHANGE? قبل نشر أي تغيير مركزي | APPROVED | P0 | NOT STARTED |
| `OPS-016` | PUBLISH GUARD — الفحوص الآلية الستة عشر قبل النشر حسب نوع المحتوى | APPROVED | P0 | NOT STARTED |
| `OPS-017` | نتائج Publish Guard: PASS · WARNING · BLOCKING — والتجاوز مسجل في Audit Log | APPROVED | P0 | NOT STARTED |
| `OPS-018` | CONTENT HEALTH / FRESHNESS — المراقبات الثلاث عشرة | APPROVED | P1 | NOT STARTED |
| `OPS-019` | LANGUAGE PARITY CHECKER — مقارنة العربي والإنجليزي | APPROVED | P0 | NOT STARTED |
| `OPS-020` | لا Auto Translation + Auto Publish — AI يقترح والـOwner يراجع وينشر | APPROVED | P0 | PARTIAL |
| `OPS-021` | GLOBAL CONTENT CALENDAR — تقويم واحد: LIVE NOW · STARTS NEXT · EXPIRES NEXT | APPROVED | P0 | NOT STARTED |
| `OPS-022` | EXPERIENCE COLLISION DETECTOR — ربط التقويم بمحرك التجارب ومنع Visual Chaos | APPROVED | P0 | NOT STARTED |
| `OPS-023` | WEBSITE SAFE MODE — إيقاف كل الطبقات الديناميكية مع بقاء الموقع الأساسي | APPROVED | P0 | TESTED |
| `OPS-024` | EMERGENCY CONTROLS — أربعة مفاتيح مع Confirmation + Audit | APPROVED | P0 | PARTIAL |
| `OPS-025` | REAL USER MONITORING — جمع Web Vitals حقيقية Privacy-safe | APPROVED | P0 | NOT STARTED |
| `OPS-026` | عرض RUM: GOOD · NEEDS IMPROVEMENT · POOR بـp75 لكل صفحة/جهاز/متصفح/لغة | APPROVED | P0 | NOT STARTED |
| `OPS-027` | PERFORMANCE ALERTS — تراجع حقيقي بلا تخمين للسبب | APPROVED | P1 | NOT STARTED |
| `OPS-028` | REPUTATION CENTER — سمعة الفروع عبر Google Business Profile الرسمي | APPROVED | P1 | NOT STARTED |
| `OPS-029` | REVIEW RESPONSE FLOW — لا رد على Google بلا موافقة الـOwner | APPROVED | P1 | NOT STARTED |
| `OPS-030` | VOICE OF CUSTOMER — نظام Feedback داخلي بسيط بلا PII وبلا Review Gating | APPROVED | P1 | PARTIAL |
| `OPS-031` | FEEDBACK DASHBOARD — اتجاهات ومقارنة فروع، وتحليل AI موسوم AI ASSISTED ANALYSIS | APPROVED | P1 | PARTIAL |
| `OPS-032` | PRESS / MEDIA KIT ضمن Media Center — محتوى معتمد فقط | APPROVED | P1 | PARTIAL |
| `OPS-033` | MEDIA RIGHTS MANAGER — حقول الحقوق والموافقات لكل Asset في المكتبة الواحدة | APPROVED | P1 | PARTIAL |
| `OPS-034` | لا تُنشر صورة شخص بلا موافقة نشر معتمدة — وتُفرض القيود والانتهاء والنطاق | APPROVED | P0 | TESTED |
| `OPS-035` | SECURITY CENTER — لوحة أمان مفهومة للـOwner بلا أي Secrets | APPROVED | P0 | NOT STARTED |
| `OPS-036` | مصادقة قوية للـOwner: Passkeys/2FA · جلسات آمنة · Rate limiting · Re-auth للإجراءات الحساسة | APPROVED | P0 | NOT STARTED |
| `OPS-037` | PRIVACY & DATA CENTER — الخصوصية نظام مُدار وليست صفحة ثابتة | APPROVED | P0 | NOT STARTED |
| `OPS-038` | DATA RETENTION VISIBILITY — عمر السجلات وآخر وصول بلا حذف تلقائي | APPROVED | P1 | NOT STARTED |
| `OPS-039` | CI/CD QUALITY GATE — 13 مرحلة، وأي P0 = STOP DEPLOY | APPROVED | P0 | PARTIAL |
| `OPS-040` | VISUAL REGRESSION — 10 صفحات حرجة × 3 أجهزة × AR/EN بلا False positives | APPROVED | P0 | PARTIAL |
| `OPS-041` | BACKUP / RESTORE HEALTH داخل Site Health — النسخة لا تُعد حقيقية بلا قابلية استرجاع | APPROVED | P0 | NOT STARTED |
| `OPS-042` | RESTORE TEST دوري على Staging — وتظهر نتيجته في Site Health | APPROVED | P0 | NOT STARTED |
| `OPS-043` | DEPENDENCY HEALTH — ملخص للـOwner ولا Major Update تلقائي على Production | APPROVED | P1 | NOT STARTED |
| `OPS-044` | PUBLIC GLOBAL SEARCH — بحث خفيف للموقع كله بلا أي محتوى خاص | APPROVED | P1 | NOT STARTED |
| `OPS-045` | SEARCH INTELLIGENCE — Top · Zero Result · Emerging بتسجيل Privacy-safe | APPROVED | P1 | NOT STARTED |
| `OPS-046` | CONTENT OPPORTUNITY ENGINE — فرص بدليل، والـOwner يقرر | APPROVED | P1 | NOT STARTED |
| `OPS-047` | No SEO content farm — اقتراحات People-first فقط وبموافقة الـOwner | APPROVED | P1 | NOT STARTED |
| `OPS-048` | محرك Issues واحد لكل المراقبات ← NEEDS ATTENTION — لا Health center ثانٍ | APPROVED | P0 | NOT STARTED |
| `OPS-049` | تجميع IA الـDashboard في 7 مجموعات — لا 40 عنصرًا في الـSidebar | APPROVED | P0 | NOT STARTED |
| `OPS-050` | كل وحدات M32 للـOwner فقط في V1 مع Server-side Authorization | APPROVED | P0 | NOT STARTED |
| `OPS-051` | لا كود للعمليات اليومية في أنظمة M32 — وإلا فهي Architecture Gap | APPROVED | P0 | NOT STARTED |
| `OPS-052` | لا مفاجآت خدمات خارجية في أنظمة M32 — إفصاح بالحقول الخمسة ثم الموافقة | APPROVED | P0 | PARTIAL |
| `OPS-053` | مسار إلزامي لأي إجراء AI: OWNER REQUEST → AI PLAN → PREVIEW → IMPACT → OWNER APPROVAL → APPLY → AUDIT LOG | APPROVED | P0 | NOT STARTED |
| `OPS-054` | Definition of Done لأنظمة M32 — 10 شروط | APPROVED | P0 | NOT STARTED |
| `OPS-055` | PWA / Offline — تقييم مستقبلي موثق RECOMMENDED / NOT RECOMMENDED | APPROVED | P2 | NOT STARTED |
| `OPS-056` | EXPERIMENTATION ENGINE — جاهزية معمارية لـA/B بلا تجارب بلا Traffic وقياس صحيح | APPROVED WITH CONDITIONS | P2 | NOT STARTED |

## 47 · Master Data & Channel Sync — [التفاصيل](master-requirements/47-master-data-channel-sync.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `MDH-001` | ONE SOURCE OF TRUTH → MULTIPLE CHANNELS: الـOwner يعدّل المعلومة مرة واحدة فقط | FROZEN | P0 | NOT STARTED |
| `MDH-002` | سلطة المصدر: SHELTER MASTER DATA > أي نسخة خارجية (إلا Adopt صريح) | FROZEN | P0 | PARTIAL |
| `MDH-003` | SHELTER MASTER DATA HUB في الـDashboard — مخزن واحد يضم Global Data Registry | FROZEN | P0 | NOT STARTED |
| `MDH-004` | كيان BRAND: الاسمان · سنة التأسيس · السنوية · الأوصاف الرسمية | FROZEN | P0 | NOT STARTED |
| `MDH-005` | كيان BRANCH (BR-DRIVE · BR-HOUSE) بحقوله المركزية | FROZEN | P0 | TESTED |
| `MDH-006` | كيان HOURS مركزي بخمسة أنواع — مصدر جدول واحد لكل القنوات | FROZEN | P0 | TESTED |
| `MDH-007` | أولوية Emergency > Temporary > Special/Holiday > Regular + effective_hours واحدة | FROZEN | P0 | TESTED |
| `MDH-008` | CONTACT + SOCIAL Registry: الأرقام حسب الدور · واتساب · البريد · روابط السوشال | FROZEN | P0 | TESTED |
| `MDH-009` | MENU Master واحد — الموقع لا يحمل نسخة منفصلة من الأسعار | FROZEN | P0 | TESTED |
| `MDH-010` | Branch-specific overrides: Inherited / Overridden + Reset to Master | FROZEN | P0 | TESTED |
| `MDH-011` | EVENTS / CAMPAIGNS Registry من محرك التجارب — بلا مخزن ثانٍ | FROZEN | P1 | PARTIAL |
| `MDH-012` | طبقة الحقائق فوق قيم الـHub: فقط APPROVED / VERIFIED تصل لأي قناة | FROZEN | P0 | NOT STARTED |
| `MDH-013` | كل Channel يقرأ من Master Data — بما فيها SEO metadata وأسطح الموقع | FROZEN | P0 | NOT STARTED |
| `MDH-014` | MASTER DATA → CHANNEL ADAPTERS مستقلة — لا Point-to-point spaghetti | FROZEN | P0 | NOT STARTED |
| `MDH-015` | Stable IDs + external_references واحد — نفس هوية المنتج/الفرع في كل القنوات | FROZEN | P0 | NOT STARTED |
| `MDH-016` | Website Adapter: توليد كل أسطح الموقع من الـHub + إبطال Cache انتقائي بعد النشر | FROZEN | P0 | NOT STARTED |
| `MDH-017` | Schema Adapter: لا Data Entry مستقل للـStructured Data — مشتق من السجلات | FROZEN | P0 | NOT STARTED |
| `MDH-018` | اتساق NAP + الساعات عبر Website · Schema · GBP · Contact — بالبناء ثم بالكشف | FROZEN | P0 | NOT STARTED |
| `MDH-019` | No duplicate editors: نفس الـControl في أكثر من سياق — مصدر بيانات واحد | FROZEN | P0 | PARTIAL |
| `MDH-020` | Google Business Adapter عبر Official API فقط — لا scraping | FROZEN | P0 | NOT STARTED |
| `MDH-021` | GBP Capability Matrix حية لكل فرع × حقل (7 أعمدة) + MANUAL ACTION REQUIRED | FROZEN | P0 | NOT STARTED |
| `MDH-022` | مزامنة الساعات مع Google: العادية · الخاصة · المؤقت · الطارئ · منتصف الليل | FROZEN | P0 | NOT STARTED |
| `MDH-023` | بوابة تفعيل مزامنة GBP: وصول API + OAuth الـOwner + Verified + P09 | FROZEN | P0 | NOT STARTED |
| `MDH-024` | EXTERNAL CHANGE DETECTED: Keep Master · Adopt · Review — بلا كتابة فوق الـMaster | FROZEN | P0 | NOT STARTED |
| `MDH-025` | تدفق النشر والمزامنة التسعي — نشر الـOwner بعد معاينة أثر تذكر GBP = موافقته | FROZEN | P0 | NOT STARTED |
| `MDH-026` | حالات المزامنة الست لكل Channel + Last Sync · Last Successful · Error · Retry | FROZEN | P0 | NOT STARTED |
| `MDH-027` | NO SILENT FAILURE: فشل مزامنة Google = تنبيه بالسبب والإجراء لا نجاح كامل | FROZEN | P0 | NOT STARTED |
| `MDH-028` | إعادة المحاولة والأخطاء: sync_jobs · 1→5→30 د→6 س · حد 5 · تصنيف الأخطاء | FROZEN | P0 | NOT STARTED |
| `MDH-029` | OUT-OF-SYNC DETECTOR: فحص اتساق دوري Master ↔ Website ↔ GBP ↔ Schema | FROZEN | P0 | NOT STARTED |
| `MDH-030` | Channel-specific overrides: موثق · مدقق · قابل للعكس ولا يغيّر الـMaster | FROZEN | P1 | NOT STARTED |
| `MDH-031` | شاشة DATA CONSISTENCY: ✓ أو ⚠ OUT OF SYNC لكل قناة مع Action واضح | FROZEN | P0 | NOT STARTED |
| `MDH-032` | Needs Attention لبيانات الـMaster: ست مراقبات في محرك Issues الواحد | FROZEN | P0 | NOT STARTED |
| `MDH-033` | Failure safety: تعطل أي API خارجي لا يكسر الموقع — الموقع يستمر بالـMaster | FROZEN | P0 | NOT STARTED |
| `MDH-034` | أمان الرموز: Server-side · Least privilege · تجديد/إعادة ربط/إلغاء آمن | FROZEN | P0 | NOT STARTED |
| `MDH-035` | خطة ترحيل البيانات إلى الـHub (7 خطوات) — من المصادر الحالية بلا تعديل المصدر | FROZEN | P0 | NOT STARTED |
| `MDH-036` | المخرج الأول قبل التنفيذ: 14 بندًا (MASTER-DATA-HUB.md) — مُسلَّم مع فجوات | FROZEN | P0 | PARTIAL |

## 48 · Design System & UI Consistency — [التفاصيل](master-requirements/48-design-system-ui-consistency.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `DS-001` | ONE DESIGN SYSTEM · ONE VISUAL LANGUAGE · NO PAGE-SPECIFIC CHAOS — الموقع + الـDashboard | FROZEN | P0 | PARTIAL |
| `DS-002` | Design Tokens مركزية — مصدر واحد للموقع والـDashboard والتطبيق المستقبلي | FROZEN | P0 | PARTIAL |
| `DS-003` | Typography System موحد للعربي والإنجليزي — Tokens لا أحجام عشوائية | FROZEN | P0 | PARTIAL |
| `DS-004` | Color Tokens دلالية — لا Hex خام ولا لون جديد خارج النظام | FROZEN | P0 | PARTIAL |
| `DS-005` | Spacing Scale موحد — margin/padding/gap من النظام فقط | FROZEN | P0 | PARTIAL |
| `DS-006` | Radius Tokens + Primary Radius للعلامة — زوايا مستديرة لا حادة | FROZEN | P0 | PARTIAL |
| `DS-007` | Shadow Tokens محدودة — clean / subtle / premium بلا Glow | FROZEN | P0 | PARTIAL |
| `DS-008` | Motion Tokens موحدة — لا Duration عشوائي لكل Component | FROZEN | P0 | PARTIAL |
| `DS-009` | Grid / Container — Layout Grammar واحدة لكل الصفحات | FROZEN | P0 | PARTIAL |
| `DS-010` | Responsive Tokens — نقاط تحول من النظام فقط، ولا Breakpoint خاص بصفحة | FROZEN | P0 | PARTIAL |
| `DS-011` | RTL/LTR من أساس الـDesign System — لا إصلاحات منفصلة لكل صفحة | FROZEN | P0 | PARTIAL |
| `DS-012` | Icon System — Lucide فقط، أحجام icon-sm/md/lg وStroke ثابت | FROZEN | P0 | PARTIAL |
| `DS-013` | مكتبة مكونات واحدة مشتركة x-ui.* للموقع والـDashboard — القائمة المعتمدة | FROZEN | P0 | NOT STARTED |
| `DS-014` | Button System — Component واحد بسبعة Variants وثلاثة أحجام وست حالات | FROZEN | P0 | PARTIAL |
| `DS-015` | Form System — نفس المكونات ونفس القياسات والحالات لكل النماذج | FROZEN | P0 | PARTIAL |
| `DS-016` | Card System — أساس واحد ومتغيرات بالمحتوى لا بالأسلوب | FROZEN | P0 | PARTIAL |
| `DS-017` | Section Component موحد — eyebrow · title · description · media · CTA · variants | FROZEN | P0 | NOT STARTED |
| `DS-018` | Navigation موحدة — Header / Mobile Navigation / Footer بنفس المكونات | FROZEN | P0 | NOT STARTED |
| `DS-019` | States موحدة لكل Component — ولا اعتماد على اللون وحده | FROZEN | P0 | PARTIAL |
| `DS-020` | الـOwner Dashboard بنفس SHELTER Design Language — أكثر Functional لا Generic SaaS | FROZEN | P0 | PARTIAL |
| `DS-021` | تحرير المحتوى ≠ تحرير التصميم — سجل المتغيرات المعتمدة هو كل ما يعرضه الـCMS | FROZEN | P0 | NOT STARTED |
| `DS-022` | Component Governance — Reuse أولًا، ثم متغير معتمد، ولا Component مكرر | FROZEN | P0 | IMPLEMENTED — NOT TESTED |
| `DS-023` | No One-off Styles — ممنوع التنسيق العشوائي إلا بـDesign Exception موثقة + بوابة آلية | FROZEN | P0 | PARTIAL |
| `DS-024` | Storybook = DESIGN SYSTEM REFERENCE — قصص مولّدة من مكونات Blade نفسها | FROZEN | P0 | NOT STARTED |
| `DS-025` | Visual Regression للـCore Components — يظهر أثر تغيير Button أو Input قبل Production | FROZEN | P0 | PARTIAL |
| `DS-026` | Consistency Audit للمشروع الحالي ثم Consolidation | FROZEN | P0 | PARTIAL |
| `DS-027` | Design System Health Report — Approved · Duplicate · Deprecated · Unused Variants · Token Violations | FROZEN | P0 | PARTIAL |
| `DS-028` | القاعدة النهائية: ONE SHELTER PRODUCT عبر Home · Menu · Locations · About · Franchise · Careers · Media · Dashboard | FROZEN | P0 | NOT STARTED |
| `DS-029` | الموقع يجب ألا يبدو Template أو AI أو SaaS — Premium = الوضوح والطباعة والتكوين والتفاصيل | APPROVED | P0 | NOT STARTED |

## 49 · Infrastructure, Release & Operations — [التفاصيل](master-requirements/49-infrastructure-release-operations.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `INFRA-001` | M35 ملحق الاكتمال النهائي — صنّف أولًا (5 تصنيفات) ثم نفّذ النواقص الفعلية فقط | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `INFRA-002` | ثلاث بيئات منفصلة: DEVELOPMENT · STAGING · PRODUCTION | APPROVED | P0 | NOT STARTED |
| `INFRA-003` | عزل الـStaging: لا فهرسة، لا Analytics Production، ونماذجه لا تختلط بالإنتاج | APPROVED | P0 | NOT STARTED |
| `INFRA-004` | سجل الإصدارات (Release Registry) — WHAT CHANGED IN THIS RELEASE? | APPROVED | P0 | NOT STARTED |
| `INFRA-005` | نظام التراجع (Rollback) — العودة السريعة إلى Previous Stable Release | APPROVED | P0 | NOT STARTED |
| `INFRA-006` | حوكمة Database Migrations — versioned، لا تعديل يدوي للإنتاج | APPROVED | P0 | NOT STARTED |
| `INFRA-007` | Monitoring Center — المصادر الخمسة عشر في محرك إشارات واحد | APPROVED | P0 | NOT STARTED |
| `INFRA-008` | صحة المجدول (Scheduled Job Health): Last Run · Next Run · Failures | APPROVED | P0 | NOT STARTED |
| `INFRA-009` | موثوقية الأتمتة الزمنية — server-controlled · timezone-aware · auditable | APPROVED | P0 | NOT STARTED |
| `INFRA-010` | نموذج الصحة: 9 فئات بحالات فعلية — بلا رقم إجمالي مزيف | APPROVED | P0 | NOT STARTED |
| `INFRA-011` | SHELTER DIGITAL COMMAND CENTER — الرئيسية للـDashboard بلا Charts تجميلية | APPROVED | P0 | NOT STARTED |
| `INFRA-012` | مركز الإشعارات الموحد (NOTIFICATIONS) — لا إشعارات عشوائية داخل الوحدات | APPROVED | P1 | NOT STARTED |
| `INFRA-013` | أولويات الإشعارات وإجراءاتها — لا إخفاء لحادثة حرجة غير محلولة بلا سجل | APPROVED | P0 | NOT STARTED |
| `INFRA-014` | مركز الاستفسارات الموحد (INQUIRIES / INBOX) — التوظيف والشراكات مستقلان | APPROVED | P0 | NOT STARTED |
| `INFRA-015` | نموذج بيانات الاستفسار وحالاته (NEW · OPEN · IN PROGRESS · RESOLVED · ARCHIVED) | APPROVED | P0 | NOT STARTED |
| `INFRA-016` | طبقة موثوقية النماذج: لا Success قبل تأكيد الحفظ — لكل النماذج | APPROVED | P0 | NOT STARTED |
| `INFRA-017` | معالجة فشل النماذج: حفظ المدخلات · رسالة واضحة · إعادة محاولة آمنة · سجل بلا بيانات حساسة | APPROVED | P0 | NOT STARTED |
| `INFRA-018` | Idempotency — لا طلبين متطابقين بسبب الضغط المزدوج، ولا منع لطلبات مستقلة صحيحة | APPROVED | P0 | NOT STARTED |
| `INFRA-019` | خدمة الأرقام المرجعية المشتركة: JOB · FR · INQ — بلا PII | APPROVED | P0 | NOT STARTED |
| `INFRA-020` | تجربة 500 / الأخطاء المؤقتة — بلا Stack trace وبإجراءات واضحة | APPROVED | P0 | NOT STARTED |
| `INFRA-021` | تجربة الصيانة (Maintenance) — بهوية SHELTER، والملاذ الأخير فقط | APPROVED | P1 | NOT STARTED |
| `INFRA-022` | Empty States محترمة لكل Module (الموقع يُخفي، الـDashboard يشرح) | APPROVED | P1 | NOT STARTED |
| `INFRA-023` | فشل الشبكة/الـAPI برفق — بلا Full PWA الآن | APPROVED | P1 | NOT STARTED |
| `INFRA-024` | معمارية الـCache/CDN — مصدر واستراتيجية إبطال لكل طبقة | APPROVED | P0 | NOT STARTED |
| `INFRA-025` | سجل التكاملات الداخلي (Integration Registry) — بلا Secrets، مع Fix Connection | APPROVED | P0 | NOT STARTED |
| `INFRA-026` | سجل ملكية الخدمات (Service Ownership Register) — لا فقدان للملكية | APPROVED | P1 | PARTIAL |
| `INFRA-027` | خطة التعافي من الكوارث (DISASTER RECOVERY PLAN) | APPROVED | P0 | PARTIAL |
| `INFRA-028` | إعداد السوق (Market Configuration) — جاهزية التدويل | APPROVED | P0 | NOT STARTED |
| `INFRA-029` | لا تثبيت للدولة في الكود — السوق الحالي JO · ar · en · JOD · Asia/Amman | APPROVED | P0 | NOT STARTED |
| `INFRA-030` | SITE-INVENTORY.md — جرد كل Public Route بحقوله العشرة | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `INFRA-031` | خريطة مصدر الحقيقة لكل نوع محتوى (CONTENT-SOURCE-OF-TRUTH) — لا نوع بلا مالك | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `INFRA-032` | سجل الروابط القديمة (LEGACY URL INVENTORY) قبل الإطلاق | APPROVED | P0 | PARTIAL |
| `INFRA-033` | خريطة التحويل (REDIRECT MAP): أقرب بديل مكافئ — لا تحويل جماعي للرئيسية | APPROVED | P0 | NOT STARTED |
| `INFRA-034` | Global Definition of Done — RELEASE-CHECKLIST.md المرجع الوحيد (17 فحصًا) | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `INFRA-035` | قابلية نقل البيانات (Data Portability) — تصدير منظم بصلاحيات الـOwner | APPROVED | P1 | NOT STARTED |
| `INFRA-036` | أمان التصدير: Owner only · تأكيد صريح · Audit · إخفاء حيث يلزم | APPROVED | P0 | TESTED |
| `INFRA-037` | قابلية نقل النظام — لا حبس لبيانات SHELTER عند Vendor | APPROVED | P1 | PARTIAL |
| `INFRA-038` | حوكمة تخزين الملفات — أربع مناطق، ولا ملفات خاصة في مجلدات الويب العامة | APPROVED | P0 | NOT STARTED |
| `INFRA-039` | تصنيف البيانات: PUBLIC · INTERNAL · CONFIDENTIAL · SENSITIVE | APPROVED | P0 | NOT STARTED |
| `INFRA-040` | Command palette / إجراءات سريعة للـOwner — اختياري ولا يعقّد V1 | APPROVED WITH CONDITIONS | P2 | NOT STARTED |
| `INFRA-041` | قائمة جاهزية الإطلاق (LAUNCH READINESS CHECKLIST) — قائمة واحدة | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `INFRA-042` | التحقق بعد الإطلاق: T+1h · T+24h · T+7d (ضمن جدول المراقبة الموحد) | APPROVED | P0 | PARTIAL |
| `INFRA-043` | الوثائق العشرون لأنظمة M35 (§56) | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `INFRA-044` | المعمارية النهائية المستهدفة (M35 §60) | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `INFRA-045` | الاستجابة الأولى لـM35: المراجعة المعمارية ذات البنود الـ17 | APPROVED | P0 | NEEDS FIX |
| `INFRA-046` | مكان الأسرار: لا سر في Environment variables لبيئة التطوير | APPROVED | P0 | IMPLEMENTED — NOT YET VERIFIED |

## 50 · Build Mode & Delivery Governance — [التفاصيل](master-requirements/50-build-mode-delivery-governance.md)

| ID | المتطلب | الحالة | P | التنفيذ |
|---|---|---|---|---|
| `BUILD-001` | التخطيط مغلق — BUILD MODE، ويبدأ البناء بعد إنهاء كل المهام الحالية | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `BUILD-002` | تنظيم المشروع داخل المستودع قبل أول Implementation كبير — لا اعتماد على ذاكرة المحادثة | APPROVED | P0 | PARTIAL |
| `BUILD-003` | إزالة التكرار — نظام واحد لكل وظيفة | APPROVED | P0 | PARTIAL |
| `BUILD-004` | حل التعارض: آخر قرار صريح يفوز — لا دمج لقرارين متعارضين، والقديم SUPERSEDED ولا يُستخدم | APPROVED | P0 | PARTIAL |
| `BUILD-005` | البيانات الناقصة لا توقف المشروع — والسؤال فقط عند Blocker من الفئات المحددة | APPROVED | P0 | PARTIAL |
| `BUILD-006` | ترتيب البناء: PHASE 1–7 بقوائم وحداتها | APPROVED | P0 | PARTIAL |
| `BUILD-007` | داخل كل Phase: PLAN → BUILD → TEST → FIX → DOCUMENT → COMMIT → VERIFY — لا أنظمة نصف مكتملة | APPROVED | P0 | PARTIAL |
| `BUILD-008` | Design System أولًا — قبل تكاثر الصفحات، بلا One-off styling | APPROVED | P0 | PARTIAL |
| `BUILD-009` | Master Data أولًا — لا ساعات أو أسعار أو بيانات فروع أو تواصل مكتوبة في الصفحات | APPROVED | P0 | PARTIAL |
| `BUILD-010` | الـOwner المستخدم الوحيد للـDashboard — كل عملية يومية بلا Code (معيار قبول الوحدات) | APPROVED | P0 | NOT STARTED |
| `BUILD-011` | لا تلمس Production مبكرًا — Dev → Testing → Staging → Final Verification → Owner Approval → Production | APPROVED | P0 | PARTIAL |
| `BUILD-012` | اختبر أثناء البناء — لا تأجيل للاختبار | APPROVED | P0 | PARTIAL |
| `BUILD-013` | Responsive ليس اختياريًا — 14 عرضًا + RTL/LTR + Touch + Keyboard + 200% zoom باستمرار | APPROVED | P0 | PARTIAL |
| `BUILD-014` | الأداء مع كل Phase — لا تأجيل للنهاية | APPROVED | P0 | PARTIAL |
| `BUILD-015` | Security by default — خصوصًا المصادقة وبيانات التوظيف والشراكات والملفات والتصدير والأسرار | APPROVED | P0 | PARTIAL |
| `BUILD-016` | قاعدة البيانات: لا DB على جهاز الـOwner، لا تلمس Falcon، والبنية ضمن Cloudways المعتمدة | APPROVED | P0 | PARTIAL |
| `BUILD-017` | Git discipline — Commits صغيرة ذات معنى، ولا عمليات Git مدمرة بلا داعٍ | APPROVED | P1 | PARTIAL |
| `BUILD-018` | وثّق أثناء البناء — لا توثيق مؤجل | APPROVED | P1 | PARTIAL |
| `BUILD-019` | لوحة التقدم (Progress Dashboard) — الحالات الست والأسئلة الخمسة | APPROVED | P1 | IMPLEMENTED — NOT TESTED |
| `BUILD-020` | لا ادعاء DONE / COMPLETE / FROZEN بلا Build + Test + Verify — وإلا IMPLEMENTED — NOT YET VERIFIED | APPROVED | P0 | IMPLEMENTED — NOT TESTED |
| `BUILD-021` | استخدم الأدوات الموجودة — لا framework أو component library أو animation أو testing أو SEO tool مكرر | APPROVED | P1 | PARTIAL |
| `BUILD-022` | ليس موقعًا عامًا — SHELTER COFFEE لا قالب قهوة ولا Dashboard SaaS ولا صفحة AI | APPROVED | P1 | NOT STARTED |
| `BUILD-023` | Keep it simple — أقل تعقيد يحقق السيطرة والتجربة والسرعة والموثوقية والتوسع والأمان والصيانة | APPROVED | P1 | PARTIAL |
| `BUILD-024` | الدور والمبادئ الختامية: Lead Engineer + System Architect + UX Quality Owner | APPROVED | P1 | PARTIAL |
| `BUILD-025` | منصة البناء (ADR-001): Laravel 13 + MySQL/MariaDB على تطبيق Cloudways Flexible جديد، Blade | APPROVED WITH CONDITIONS | P0 | IMPLEMENTED — NOT TESTED |
| `BUILD-026` | تحويل البوابات: مراجعات المعمارية التقنية والـWireframes غير مانعة (READY FOR REVIEW)، وبوابات الإنتاج والحقائق تبقى | APPROVED | P0 | IMPLEMENTED — NOT TESTED |

## البنود المستبعدة من الدمج (8)

| البند | السبب |
|---|---|
| M01-086 | example-only: أمثلة URLs صرّح النص بأنها 'ممكنة فقط وليست قرارات' (/, /menu, /locations, /about, /blog, /contact, /locations/branch-name)؛ القاعدة الفعلية في WEB-012 والبنية الفعلية P3 في WEB-008 |
| M01-127 | example-only: أمثلة أنواع الحملات (International Coffee Day · 50% Discount · New Drink · Seasonal Menu · New Branch · Event · Announcement) — ليست متطلبًا ولا عرضًا معتمدًا؛ مذكورة كأمثلة في تفاصيل CAMP-002 |
| M01-240 | مثال فقط («مثلاً»): تقسيم Stage 1–8 (Website Goals … Design) ترتيب مقترح غير ملزم؛ القاعدة الملزمة (العمل بمراحل، لا 50 سؤالًا) في GOV-031 |
| M05-001 | مرجع فقط (رابط الموقع الحالي https://www.shelterjo.com/ أُرسل midturn بلا تعليمات) — ليس متطلبًا؛ الفحص المباشر مغطى في TEST-023. |
| M09-001 | مرجع فقط (توضيح أن لقطات M06–M08 من الموقع الحالي) — مادة مرجعية للفحص، ليست متطلبًا ولا أصولًا معتمدة (D-000، D-003). |
| M14-052 | ملاحظة المستشار للـOwner خارج الـblock وليست تعليمات لـClaude؛ الانتقال إلى R3 مغطى في GOV-041 (M14-050) |
| M25-040 | أمثلة توضيحية فقط (ICED SPANISH LATTE 120 product views · SPECIALITY COFFEE 350 category clicks · "cold brew" 45 searches · Zero result "matcha" 15) — أرقام وهمية لا تُستخدم كبيانات ولا كدليل على توفر/غياب منتجات؛ مذكورة في notes الخاصة بـANL-035. |
| M25-082 | example-only: Blocks الرئيسية («مثلاً» Hero · Campaign · About · Menu Preview · Branches · Coffee Story · Blog · CTA · Footer) لشرح الـPage Editor — ليست اعتمادًا لبنية الرئيسية؛ موثقة في conflict «مثال Blocks الرئيسية» وفي HOME-002 |
