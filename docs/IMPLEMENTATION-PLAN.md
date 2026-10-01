# IMPLEMENTATION PLAN — SHELTER COFFEE Website (خطة واحدة للمشروع)

> **الحالة:** `DRAFT — PENDING OWNER APPROVAL` · **آخر تحديث:** 2026-10-01
> **لماذا خطة واحدة؟** تحل محل الخطط المتفرقة في الرسائل:
> - التسلسل الإلزامي من 36 خطوة.
> - مراحل Google.
> - مراحل المنيو A–J.
> - مراحل الـDashboard A–N.
> - الأدوات.
> - بوابة الاستجابة.
>
> **لا تُلغي أي بوابة اعتماد سابقة.**
>
> **القاعدة:** لا قفز إلى مرحلة قبل اعتماد بوابة المرحلة التي تسبقها. مراحل التوثيق المستقلة يمكن أن تسير بالتوازي إذا لم تتجاوز بوابة.

## خريطة الـ36 خطوة الإلزامية ← مراحل الخطة

| الخطوات (التسلسل الإلزامي) | المرحلة |
|---|---|
| 1 Discovery · 2 Current Website Audit · 3 Owner Interview · 4 Business Requirements · 5 Content Discovery | **P01** |
| 6 IA · 7 Page Architecture · 8 Navigation · 9 Sitemap · 10 URL Architecture · 11 User Journeys · 12 SEO Architecture · 13 AEO/GEO Architecture · 14 Content Architecture · 15 Wireframes | **P02** (المنيو، بطلب الـOwner مبكرًا) + **P03** (بقية الموقع) |
| 16 Design System · 17 Motion Design System · 18 UI Design · 19 Interactive Prototype | **P05** |
| 20 Technical Architecture · 21 CMS Architecture | **P06** (المنصة) + **P04** (معمارية الـDashboard والـCMS: وثائق فقط، طُلبت صراحة لاحقًا في رسالة الـDashboard) |
| 22 Development · 23 Content Migration | **P07** (المحتوى) + **P08** (البناء) + **P09** (Google) |
| 24 Responsive Testing … 32 QA | **P10** (مع اختبار مستمر من P08) |
| 33 Owner Approval · 34 Staging · 35 Launch | **P11** |
| 36 Post Launch Audit | **P12** |

> **الترتيب يتبع تسلسلك الأصلي:** التصميم (P05) قبل المعمارية التقنية (P06).
> - **الاستثناء الوحيد:** P04 (معمارية الـDashboard والـCMS كوثائق بلا تنفيذ)، لأنك طلبتها صراحةً بعد التسلسل.
> - **توصيتنا:** حسم المنصة مبكرًا، لأن أدوات التصميم (Storybook/shadcn/Tailwind) والـCMS تعتمد عليها. **هذا قرارك (PO-044)، وحتى يُحسم يبقى تسلسلك هو المطبق.**

## المراحل

| Phase | الهدف | الاعتماديات | المخرجات | الاختبارات | اعتماد الـOwner؟ | الحالة |
|---|---|---|---|---|---|---|
| **P00** Governance & Master SoT | عقل واحد للمشروع: المتطلبات والقرارات والتعارضات والمعلّق والتتبع | — | Master Requirements · Decision Register · Conflict Register · Pending · Traceability · Gap · Plan · CLAUDE.md | تغطية: كل بند خام من المحادثة له متطلب أو سبب استبعاد | ✅ اعتماد الـMaster | **الحالية (اكتملت المسودة)** |
| **P01** Discovery completion | إكمال المعلومات الناقصة والوصول | AC-01 (فحص الموقع الحقيقي) · Search Console · GBP | جرد الموقع الحقيقي، Baseline لـSearch Console، بيانات الفروع من GBP | تحقق من المصادر | ✅ للبيانات التجارية | جزئي: R1/R2/R3 ✅ · الوصول ⏳ |
| **P02** Menu IA & Wireframes | بنية المنيو وتجربتها | Inventory v1.0 ✅ | `docs/menu-ia/*` | 456 فحصًا آليًا ✅ (نموذج) · Lighthouse ✅ | ✅ **بوابة:** لا Visual ولا كود منيو قبل الاعتماد | **بانتظار اعتمادك** |
| **P03** Site-wide IA | الصفحات، والتنقل، والـSitemap، والـURLs، والـSEO/AEO، وهيكل المحتوى، ورحلات المستخدم، وWireframes لكل الموقع | P02 (للنمط) · قرارات الجذر والتواصل ✅ | Page inventory · Sitemap · Navigation (موبايل/ديسكتوب) · URL map نهائي · SEO/AEO architecture · Wireframes (Home، Locations، Branch، About، Contact، Blog، Article، Campaign) | Responsive matrix على الـWireframes · axe · flows | ✅ **بوابة:** لا كود قبل اعتماد Pages/Nav/Sitemap/URL/Content/SEO | لم تبدأ |
| **P04** Owner Dashboard & CMS architecture | مركز تحكم يدير 95%+ من العمليات بلا كود (مراحل الـDashboard A–G) | P02 (نموذج المنيو v0.5) · قائمة الوحدات | DASHBOARD-ARCHITECTURE · ANALYTICS-MEASUREMENT-PLAN · CMS-DATA-MODEL · SEO-HEALTH-SPEC · SITE-HEALTH-SPEC · PERMISSIONS · Data Source Matrix · Wireframes (Overview موبايل/ديسكتوب، Analytics، Menu Editor، Product Editor، Branch Editor، Campaign Editor، SEO Health، Site Health، Performance، Media Library، Audit Log) | Responsive matrix على الـWireframes (AR/EN) | ✅ **بوابة H:** لا تنفيذ قبل اعتماد المعمارية والـWireframes | **لم تبدأ — المرحلة التالية الموصى بها** |
| **P05** Brand, Design System & Visual | Tokens، والمكونات، والحركة، وتصميم الواجهات، والنموذج التفاعلي | **ملفات الهوية (M-10)** · P02/P03/P04 معتمدة · (أدوات React تنتظر P06) | Tokens · Storybook · UI لكل صفحة (AR/EN، كل الأحجام) · Motion system | Visual regression · axe · Responsive | ✅ لكل تصميم | **محجوبة (M-10)** |
| **P06** Platform & technical architecture (DB-08) | اختيار الإطار والاستضافة والـCMS/قاعدة البيانات والمصادقة والأمان | P05 (حسب تسلسلك) أو بعد P03/P04 إذا اعتمدت التوصية (PO-044) | ADR للمنصة: الخيارات والتكلفة والأداء وSSR/SSG والـRTL والـCMS. Supabase فقط إن كان مناسبًا. خطة Staging | Proof of concept صغير على Preview | ✅ (قرار معماري وتكلفة) | لم تبدأ |
| **P07** Content & media | جمع المحتوى والترجمات والصور واعتمادها | قوائم المعلّق | 152 اسمًا عربيًا (حسب الفئات) · نصوص الصفحات · صور معتمدة · نصوص قانونية | Content approval register | ✅ لكل بند | مستمر |
| **P08** Build (Staging) | الموقع، والـCMS، والـDashboard V1، على Staging | P03 · P04 · P05 · P06 | كود + CMS + Dashboard (P0 modules) · Preview لكل تغيير | Playwright (20 عرضًا × AR/EN) · axe · Lighthouse budgets · Unit tests (الساعات، البحث العربي، التسعير) | ✅ عند كل Release على Staging | لم تبدأ |
| **P09** Google ecosystem | جرد الموجود ثم إعداد GA4/GTM/Consent/GSC/GBP/Schema وتنفيذه **بواسطة Claude** | الصلاحيات · P08 | Tag register · Event dictionary منفذ · Consent Mode v2 · GSC · مزامنة GBP | GTM Preview · DebugView · لا ازدواج | ✅ للخطوات الحساسة (Publish، Ownership، تعديل GBP) | لم تبدأ |
| **P10** QA gate | بوابة الجودة النهائية | P08 · P09 | RESPONSIVE-QA-MATRIX كاملة · A11y audit · Performance · SEO/AEO audit · Security audit · Analytics validation · Cross-browser (Safari/Firefox) · أجهزة حقيقية | كل ما سبق | ✅ | لم تبدأ |
| **P11** SEO migration & launch | الإطلاق بلا خسارة SEO | P10 · Baseline لـGSC · خريطة 301 | 301 لـ`/menu` وغيره (عند الإطلاق فقط) · Sitemap · خطة Rollback · Backup | فحص Redirects/Canonical/hreflang · Smoke tests | ✅ **إلزامي** | لم تبدأ |
| **P12** Post-launch & V2 | المراقبة والتحسين والتوسع | P11 | تنبيهات · CWV ميداني · تقارير · أدوار متعددة · توسع عالمي | Monitoring | حسب البند | لم تبدأ |

## الترتيب الموصى به الآن
1. **اعتمادك لمسودة الـMaster** (P00) و**اعتماد Menu IA** (P02).
2. **بالتوازي، وثائق فقط وبلا تجاوز أي بوابة:**
   - **P04:** معمارية الـDashboard (A–G)، وطلبك الصريح الأخير قبل التدقيق.
   - **P03:** معمارية بقية الصفحات.
3. **P05 (التصميم):** يبدأ فور وصول ملفات الهوية (M-10) واعتماد P02/P03.
4. **P06 (المنصة DB-08):** بعد P05 حسب تسلسلك، أو مبكرًا بعد P03/P04 إذا اعتمدت التوصية (PO-044).

## المتطلبات حسب المرحلة (من الـMaster)

| Phase | الاسم | المتطلبات | P0 | P1 | P2/P3 | معلّق على الـOwner |
|---|---|---|---|---|---|---|
| P00 | Governance & Master Source of Truth | 85 | 51 | 27 | 7 | 1 |
| P01 | Discovery completion | 89 | 46 | 36 | 7 | 11 |
| P02 | Menu IA & Wireframes | 52 | 15 | 35 | 2 | 3 |
| P03 | Site-wide IA, Sitemap, URL & SEO architecture | 85 | 24 | 55 | 6 | 12 |
| P04 | Owner Dashboard & CMS architecture | 76 | 36 | 34 | 6 | 1 |
| P05 | Brand, Design System & Visual Design | 39 | 3 | 29 | 7 | 1 |
| P06 | Platform & technical architecture (DB-08) | 18 | 3 | 11 | 4 | 1 |
| P07 | Content & media approval | 60 | 30 | 24 | 6 | 11 |
| P08 | Build on staging (website + CMS + Dashboard V1) | 214 | 99 | 104 | 11 | 0 |
| P09 | Google ecosystem implementation | 71 | 15 | 53 | 3 | 2 |
| P10 | QA gate | 40 | 12 | 26 | 2 | 0 |
| P11 | SEO migration & launch | 15 | 13 | 2 | 0 | 0 |
| P12 | Post-launch & V2 | 22 | 1 | 3 | 18 | 0 |

> القائمة الكاملة لكل مرحلة: عمود Phase في [`IMPLEMENTATION-GAP-ANALYSIS.md`](IMPLEMENTATION-GAP-ANALYSIS.md).
