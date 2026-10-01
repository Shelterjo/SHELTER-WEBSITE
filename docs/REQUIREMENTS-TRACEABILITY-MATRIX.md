# REQUIREMENTS TRACEABILITY MATRIX

> **Requirement → Decision → Design → Code → Test.** لكل متطلب من **866**: من أين جاء، وبأي قرار، وأين صُمم، وأين نُفذ، وكيف يُختبر. · **آخر تحديث:** 2026-10-01
> **المصادر:** مفاتيح البنود الخام (مثل `M23-045`)، وأرشيفها في التدقيق.
>
> **قراءة الأعمدة:**
> - **Code فارغ** = لا تنفيذ بعد. لا يوجد كود موقع؛ `tooling/` أدوات جودة فقط.
> - **Test `PROTOTYPE`** = اختبار على الـWireframes فقط.

| ID | Title | Source (raw items) | Decision | Design | Code | Test | Impl | Tested |
|---|---|---|---|---|---|---|---|---|
| `BRAND-001` | الاسم الرسمي للعلامة: SHELTER COFFEE / شلتر كوفي | M03-001, M03-002, M03-003, M03-004 | D-007, DB-01 | docs/governance/DECISION-LOG.md D-007 · docs/phase-01-discovery/12-homepage-screenshots-audit.md §6 · docs/phase-01-discovery/15-root-gateway-wireframe.md §3 |  |  | NOT STARTED | N/A |
| `BRAND-002` | الهوية البصرية المعتمدة فقط — ملفاتها مفقودة وتمنع Visual Design (M-10) | M23-260 | M-10, CF-11, MI-021, MI-022 | docs/menu-ia/MENU-DECISION-REGISTER.md M-10, CF-11 · docs/phase-01-discovery/04-content-approval-register.md MI-021, MI-022 · design-system/README.md · design-system/tokens/tokens.template.json · docs/menu-ia/PERFORMANCE-BUDGET.md |  |  | NOT STARTED | N/A |
| `BRAND-003` | الموقع الحالي ليس مرجعًا للتصميم أو الـUI أو الـUX أو الحركة | M01-006 | D-000 | docs/governance/DECISION-LOG.md D-000 · docs/phase-01-discovery/12-homepage-screenshots-audit.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `BRAND-004` | تصميم Bespoke لعلامة قهوة مختصة عالمية — ليس قالبًا | M01-207, M01-247, M01-248 | D-005, D-004 | design-system/README.md |  |  | NOT STARTED | NO |
| `BRAND-005` | مبادئ التصميم المثبتة (Design Principles) | M23-259, M27-093, M27-094, M27-095, M14-011, M27-101 | D-005, D-062 | design-system/README.md §Rules · design-system/tokens/tokens.template.json |  |  | PARTIAL | N/A |
| `BRAND-006` | ممنوع AI / Vibe-coding look (قائمة الأنماط المحظورة) | M01-206, M23-258, M27-096, M27-098, M27-099, M27-100 | D-005 | design-system/README.md §Rules · docs/governance/DECISION-LOG.md D-005 |  |  | NOT STARTED | NO |
| `BRAND-007` | shadcn/Radix أساس هندسي فقط — ممنوع هوية shadcn الافتراضية | M24-046, M24-047, M24-048 | DB-08 | docs/FRONTEND-TOOLING.md §2 · design-system/README.md §Rules |  |  | NOT STARTED | NO |
| `BRAND-008` | Design System كامل (15 مجالًا) | M01-204 |  | design-system/README.md · design-system/tokens/tokens.template.json |  |  | PARTIAL | N/A |
| `BRAND-009` | هيكلية الـDesign System بطبقات | M24-049 |  | design-system/README.md §Layers |  |  | PARTIAL | N/A |
| `BRAND-010` | الحد الأدنى للـDesign Tokens + منع الـhardcode | M24-050, M24-051 |  | design-system/tokens/tokens.template.json · docs/menu-ia/MENU-DECISION-REGISTER.md R-02, R-03, R-06, R-09 |  |  | PARTIAL | N/A |
| `BRAND-011` | Design Lock بعد اعتماد الـDesign System | M01-205 |  | design-system/README.md |  |  | NOT STARTED | NO |
| `BRAND-012` | Design System واحد — لا مكونات أو أنظمة تصميم مكررة | M27-144 |  | design-system/README.md · docs/FRONTEND-TOOLING.md §3 |  |  | PARTIAL | N/A |
| `BRAND-013` | الـPage Editor / CMS لا يسمح بكسر الـDesign System | M25-084 |  | design-system/README.md §Rules |  |  | NOT STARTED | NO |
| `BRAND-014` | Reusable Sections ضمن الـDesign System | M01-221 |  | design-system/README.md |  |  | NOT STARTED | NO |
| `BRAND-015` | Storybook: ورشة مكونات SHELTER (20 مكونًا × 11 حالة) | M24-015, M24-016 | DB-08 | docs/FRONTEND-TOOLING.md §6 |  |  | NOT STARTED | NO |
| `BRAND-016` | الأيقونات فقط عندما تحسّن الفهم | M24-018 |  | docs/FRONTEND-TOOLING.md §2 |  |  | NOT STARTED | NO |
| `BRAND-017` | لا قلب (Mirror) للـLogo في RTL | M01-203 |  |  |  |  | NOT STARTED | NO |
| `BRAND-018` | الشكل البصري النهائي للـCTA وBranch Card في مرحلة UX/UI فقط | M04-049 | D-027, D-061 | docs/governance/DECISION-LOG.md D-027 · docs/phase-01-discovery/09-architecture-options-after-r1.md AR-03 |  |  | NOT STARTED | NO |
| `BRAND-019` | أسلوب تصميم الـOwner Dashboard | M25-023, M25-024, M25-140 |  | design-system/README.md |  |  | NOT STARTED | NO |
| `BRAND-020` | أنماط ممنوعة في الـDashboard | M25-141, M27-097 | D-005 |  |  |  | NOT STARTED | NO |
| `IA-001` | غرض الموقع ونطاقه الحالي | M01-066, M03-057 | D-016 | docs/governance/DECISION-LOG.md D-016 · docs/phase-01-discovery/10-url-architecture-draft.md §1, §3 |  |  | NOT STARTED | NO |
| `IA-002` | الموقع ليس E-Commerce حاليًا — جاهز للطلب أونلاين لاحقًا | M01-067 | D-016, D-073 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-06 |  |  | NOT STARTED | NO |
| `IA-003` | مهام الزائر السريعة | M01-068, M01-069, M01-070 |  | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-04 |  |  | NOT STARTED | NO |
| `IA-004` | أهم أفعال الزائر بالترتيب | M03-027 | D-013, D-027 | docs/governance/DECISION-LOG.md D-013 · docs/phase-01-discovery/15-root-gateway-wireframe.md §2 |  |  | NOT STARTED | NO |
| `IA-005` | بوابة الصفحات: سؤال الـOwner قبل بناء أي صفحة | M01-079, M01-080, M01-081 | D-015, DB-04, DB-05 | docs/phase-01-discovery/07-question-backlog.md R4-01..R4-05 · docs/phase-01-discovery/10-url-architecture-draft.md §6 URL-04 |  |  | PARTIAL | N/A |
| `IA-006` | صفحة FAQ معتمدة (ضمن قائمة صفحات V1) | M03-044 | D-015, URL-06 | docs/governance/DECISION-LOG.md D-015 · docs/phase-01-discovery/10-url-architecture-draft.md §3, §6 · docs/phase-01-discovery/04-content-approval-register.md MI-019 |  |  | NOT STARTED | NO |
| `IA-007` | صفحة التوظيف (Careers): معتمدة مع إعادة تصميم كاملة | M03-049 | D-015, RISK-06, D-024 | docs/phase-01-discovery/10-url-architecture-draft.md §3 careers/ |  |  | NOT STARTED | NO |
| `IA-008` | صفحة Catering / B2B / Business inquiries / Events services = PAGE RESERVED | M03-047, M14-029 | D-015, D-069, D-057 | docs/governance/DECISION-LOG.md D-069 |  |  | NOT STARTED | NO |
| `IA-009` | فصل مصطلح Events: حملات SHELTER ≠ Catering / B2B / Private Events | M14-031 | D-070 | docs/governance/DECISION-LOG.md D-070 |  |  | NOT STARTED | NO |
| `IA-010` | الرعايات: غير معتمدة — MISSING | M03-048, M14-038 | D-015, D-072, DB-04 | docs/governance/DECISION-LOG.md D-072 |  |  | NOT STARTED | NO |
| `IA-011` | صفحة الفريق / SHELTER Family: مؤجلة للنقاش | M03-053 | D-015, DB-04, VQ-19 |  |  |  | NOT STARTED | NO |
| `IA-012` | النشرة البريدية DEFERRED في V1 — «قائمة الولاء» ليست Loyalty Program | M03-054, M10-038, M10-039 | D-040, DB-17, DB-14 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-05 · docs/phase-01-discovery/12-homepage-screenshots-audit.md §2-15 |  |  | NOT STARTED | NO |
| `IA-013` | لا Linktree إذا كان الموقع يقدم تجربة أفضل | M10-025 | D-036 |  |  |  | NOT STARTED | NO |
| `IA-014` | لا UI Design قبل اعتماد الـInformation Architecture | M01-257 | D-001, D-017, D-142 | docs/menu-ia/README.md · docs/phase-01-discovery/10-url-architecture-draft.md |  |  | PARTIAL | N/A |
| `IA-015` | Hybrid Menu Architecture: صفحة منيو واحدة | M23-035 | D-143, F-09, DB-06 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §1–§3 · docs/menu-ia/MENU-DECISION-REGISTER.md F-09 |  | tooling/tests/prototype/menu-wireframe.spec.mjs · tooling/tests/app/menu.spec.mjs | NOT STARTED | PROTOTYPE |
| `IA-016` | لا Product Page مستقلة لكل صنف في V1 | M23-036 | D-143, D-147, F-09, F-21 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §1, §13 · docs/phase-01-discovery/10-url-architecture-draft.md §3 |  |  | NOT STARTED | NO |
| `IA-017` | تسلسل مكونات صفحة المنيو | M23-037 | D-143, F-15, F-17, R-01 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §2, §3 |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `IA-018` | وثيقة SHELTER MENU IA SPEC (Phase B) | M23-238 | D-142 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `IA-019` | User Flows للمنيو (Phase D) | M23-240 | D-142 | docs/menu-ia/USER-FLOWS.md |  | tooling/tests/app/menu.spec.mjs | IMPLEMENTED — NOT TESTED | N/A |
| `IA-020` | Low-Fi Wireframes للمنيو: 4 نسخ وحالات مهمة (Phase E) | M23-241, M23-242, M23-243 | D-142 | docs/menu-ia/wireframes/README.md |  | tooling/tests/prototype/menu-wireframe.spec.mjs · tooling/tests/prototype/responsive.spec.mjs | TESTED | PROTOTYPE |
| `IA-021` | UX Validation للـWireframes (Phase F) | M23-244 | D-142 | docs/menu-ia/UX-VALIDATION.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `IA-022` | SHELTER OWNER DASHBOARD IA (Tree كاملة) | M25-170 |  |  |  |  | NOT STARTED | NO |
| `WEB-001` | إعادة بناء كاملة من الصفر لموقع عالمي طويل الأمد | M01-001, M01-012 | D-000, D-001 | docs/governance/DECISION-LOG.md |  |  | NOT STARTED | N/A |
| `WEB-002` | Global-ready من البداية — غير مرتبط بالأردن | M01-071, M03-014, M03-021 | D-010, D-011 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-01 · docs/phase-01-discovery/10-url-architecture-draft.md §1–§5 |  |  | NOT STARTED | N/A |
| `WEB-003` | هرمية Country → City → Branch وجاهزية التوسع | M01-072, M01-073, M01-077, M03-009, M03-016 | D-010, D-008, D-026, DB-15, DB-07 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-02 · docs/phase-01-discovery/10-url-architecture-draft.md §3 |  |  | NOT STARTED | N/A |
| `WEB-004` | لا اختراع للتوسع — أي دولة/فرع مستقبلي Hidden حتى موافقة الـOwner | M03-017, M03-018 | D-010 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-02 · docs/phase-01-discovery/10-url-architecture-draft.md §3 |  |  | NOT STARTED | N/A |
| `WEB-005` | الدومين الحالي shelterjo.com — لا تغيير ولا Domain Migration الآن | M03-019, M03-022, M03-023 | D-011, DB-11, D-002 | docs/phase-01-discovery/05-decisions-before-design.md DB-11 · docs/phase-01-discovery/13-root-and-international-seo-plan.md §1 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `WEB-006` | الدومين العالمي المستقبلي — مؤجل | M03-020 | D-011 | docs/phase-01-discovery/13-root-and-international-seo-plan.md §7 |  |  | NOT STARTED | N/A |
| `WEB-007` | Option C — Brand Layer + Market Layer (مبدئي) | M04-006 | D-019, DB-02 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-01 · docs/phase-01-discovery/10-url-architecture-draft.md §1 |  |  | NOT STARTED | N/A |
| `WEB-008` | P3: /ar/ و/en/ + طبقة السوق /ar/jo/… /en/jo/… (مبدئي بشرطين) | M10-002, M10-003, M03-033 | D-031, D-014, DB-02 | docs/phase-01-discovery/10-url-architecture-draft.md §2–§5 · docs/phase-01-discovery/13-root-and-international-seo-plan.md |  |  | NOT STARTED | N/A |
| `WEB-009` | لا تجميد للـURL/Language Architecture بدون نقاش واعتماد الـOwner | M01-078, M03-034, M04-007 | D-014, D-019, D-031, DB-02, D-017 | docs/phase-01-discovery/10-url-architecture-draft.md · docs/phase-01-discovery/13-root-and-international-seo-plan.md · docs/governance/DECISION-LOG.md DB-02 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `WEB-010` | URL Tree كامل + مقارنة ترتيب اللغة/الدولة | M04-008, M04-009 | D-019, D-031 | docs/phase-01-discovery/10-url-architecture-draft.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `WEB-011` | معايير الرموز: ar/en · ISO country (jo) · xx للتوثيق فقط | M10-008, M10-009, M10-010 | D-031 | docs/phase-01-discovery/10-url-architecture-draft.md · docs/phase-01-discovery/13-root-and-international-seo-plan.md §4 |  |  | NOT STARTED | N/A |
| `WEB-012` | URLs نظيفة لاتينية: قصيرة · مقروءة · دلالية · SEO Friendly · قابلة للتوسع | M01-084, M01-085, M03-032 | D-014, DB-03 | docs/phase-01-discovery/10-url-architecture-draft.md §3 · docs/phase-01-discovery/05-decisions-before-design.md DB-03 |  |  | NOT STARTED | N/A |
| `WEB-013` | ROOT-01 = D: الجذر / = Global Brand Gateway / x-default — لا 301 إلى /ar/ | M13-001, M10-004, M11-050 | D-052, D-031, DB-19, GEP-§20/§21 | docs/phase-01-discovery/13-root-and-international-seo-plan.md §2 · docs/phase-01-discovery/15-root-gateway-wireframe.md |  |  | NOT STARTED | N/A |
| `WEB-014` | دراسة الجذر A/B/C/D ثم انتظار قرار الـOwner | M10-005, M10-006, M11-051, M11-052 | D-031, D-052, GEP-§20/§21 | docs/phase-01-discovery/13-root-and-international-seo-plan.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `WEB-015` | وظيفة الجذر: مفيد للمستخدم والبحث والـAI — Header مختصر — ليس نسخة ثالثة | M13-003, M14-020, M14-021, M14-022 | D-052, D-066, DB-19 | docs/phase-01-discovery/15-root-gateway-wireframe.md |  |  | NOT STARTED | N/A |
| `WEB-016` | Wireframe ومحتوى الجذر قبل الـFreeze — لا تنفيذ قبل الموافقة | M13-004 | D-052, D-066, D-067, D-068, DB-19 | docs/phase-01-discovery/15-root-gateway-wireframe.md |  |  | PARTIAL | N/A |
| `WEB-017` | لا Geo/IP/Language Redirect تلقائي — لا منع لاختيار English | M13-002, M14-024, M10-007 | D-052, D-067, D-031 | docs/phase-01-discovery/13-root-and-international-seo-plan.md §1–§2 · docs/phase-01-discovery/15-root-gateway-wireframe.md §5 |  |  | NOT STARTED | N/A |
| `WEB-018` | URL-02: Slugs الفروع القصيرة drive و house | M13-005, M10-013, M10-014 | D-053, D-032 | docs/phase-01-discovery/10-url-architecture-draft.md §3 · docs/phase-01-discovery/13-root-and-international-seo-plan.md §8 |  |  | NOT STARTED | N/A |
| `WEB-019` | قاعدة الفرع الثاني من نفس النوع: drive-{area} / house-{area} (مبدئي) | M13-006 | D-053 | docs/phase-01-discovery/13-root-and-international-seo-plan.md §8 |  |  | NOT STARTED | N/A |
| `WEB-020` | روابط المنيو /ar/jo/menu/ · /en/jo/menu/ + ?branch=drive\|house | M23-188, M23-104 | D-145, F-18, D-054 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §1، §9، §12 |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | PROTOTYPE |
| `WEB-021` | Menu IA س15: ما يكون URL مستقلًا وما يبقى داخل /menu | M21-039 | D-141, D-143, D-147, F-09, F-21 | docs/phase-01-discovery/22-menu-information-architecture.md · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §12 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `WEB-022` | الموقع ليس E-Commerce في المرحلة الحالية | M03-056 | D-016 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-06 |  |  | NOT STARTED | N/A |
| `WEB-023` | جاهزية مستقبلية لـOnline Ordering — FUTURE CAPABILITY ONLY | M03-058, M14-047 | D-016, D-073 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-06 |  |  | NOT STARTED | N/A |
| `WEB-024` | shop.shelterjo.com — فحص وعرض قبل أي قرار | M03-055 | D-016, D-041, DB-12 | docs/phase-01-discovery/02-url-inventory-and-migration-seed.md §C |  |  | NOT STARTED | N/A |
| `RESP-001` | Responsive إلزامي للموقع كاملًا وللـOwner Dashboard | M26-001, M26-002, M27-056, M27-057 |  | docs/FRONTEND-TOOLING.md §5 · docs/qa/RESPONSIVE-QA-MATRIX.md | tooling/viewports.mjs · tooling/playwright.config.mjs | tooling/tests/prototype/responsive.spec.mjs · tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `RESP-002` | Mobile-First Responsive Architecture | M01-096, M26-004, M27-058 |  | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §11 |  |  | NOT STARTED | PROTOTYPE |
| `RESP-003` | فئات الأجهزة المدعومة (10 فئات) | M26-003, M01-097, M27-062 |  |  | tooling/viewports.mjs | tooling/tests/prototype/responsive.spec.mjs | NOT STARTED | PROTOTYPE |
| `RESP-004` | 14 عرضًا إلزاميًا مع اختبار فعلي بالمحتوى الحقيقي | M26-005, M26-006, M27-059, M27-060, M27-061, M24-021, M23-053 |  | docs/menu-ia/UX-VALIDATION.md §3 · docs/qa/RESPONSIVE-QA-MATRIX.md | tooling/viewports.mjs · tooling/playwright.config.mjs | tooling/tests/prototype/menu-wireframe.spec.mjs · tooling/tests/prototype/responsive.spec.mjs | PARTIAL | PROTOTYPE |
| `RESP-005` | Portrait وLandscape | M26-045, M27-064 |  |  | tooling/viewports.mjs | tooling/tests/prototype/responsive.spec.mjs | PARTIAL | PROTOTYPE |
| `RESP-006` | أساليب الإدخال: Touch وMouse وKeyboard | M01-098, M27-066 |  |  | tooling/playwright.config.mjs | tooling/tests/prototype/responsive.spec.mjs | PARTIAL | PROTOTYPE |
| `RESP-007` | كل Responsive layout وكل اختبار بالعربي RTL والإنجليزي LTR | M27-063, M24-020 |  |  |  | tooling/tests/prototype/menu-wireframe.spec.mjs · tooling/tests/prototype/responsive.spec.mjs | PARTIAL | PROTOTYPE |
| `RESP-008` | Browser zoom 200% بلا فقدان محتوى | M26-046, M27-065 |  | docs/menu-ia/ACCESSIBILITY-CHECKLIST.md | tooling/viewports.mjs | tooling/tests/prototype/responsive.spec.mjs | PARTIAL | PROTOTYPE |
| `RESP-009` | No overflow · No clipping · No overlap — أي مشكلة Responsive = BUG | M27-067, M27-068, M26-055, M26-007 |  |  |  | tooling/tests/prototype/menu-wireframe.spec.mjs · tooling/tests/prototype/responsive.spec.mjs | PARTIAL | PROTOTYPE |
| `RESP-010` | Typography مقروءة: لا clipping ولا tiny fonts ولا سطور طويلة | M26-008, M26-022, M26-023 |  |  |  | tooling/tests/prototype/responsive.spec.mjs | NOT STARTED | PROTOTYPE |
| `RESP-011` | Responsive typography scaling مدروس | M26-021 |  | design-system/README.md |  |  | NOT STARTED | NO |
| `RESP-012` | القوائم سهلة الاستخدام بيد واحدة على الهاتف | M26-010 |  | docs/menu-ia/UX-VALIDATION.md §1, §2 |  |  | NOT STARTED | PROTOTYPE |
| `RESP-013` | العناصر اللاصقة والـbanners لا تغطي المحتوى ولا العناصر الأساسية | M26-011, M23-221, M23-220 | R-06 | docs/menu-ia/UX-VALIDATION.md §3 R-06 |  | tooling/tests/prototype/responsive.spec.mjs | NOT STARTED | PROTOTYPE |
| `RESP-014` | شريط إجراءات صفحة الفرع على الموبايل (CT-02) — معايير UX Testing | M14-004, M14-005, M14-006, M14-007 | D-061 | docs/phase-01-discovery/14-contact-architecture-whatsapp.md |  |  | NOT STARTED | NO |
| `RESP-015` | Bottom Sheets لا تتجاوز حدود الشاشة | M26-012 | D-146, R-05 |  |  | tooling/tests/prototype/responsive.spec.mjs | NOT STARTED | PROTOTYPE |
| `RESP-016` | دعم Safe Area لأجهزة iPhone | M26-013 |  |  |  |  | NOT STARTED | NO |
| `RESP-017` | النماذج على الهاتف سهلة ولوحة المفاتيح لا تغطي الحقول | M26-015, M26-016 |  |  |  |  | NOT STARTED | NO |
| `RESP-018` | الجداول على الهاتف: Mobile-friendly بلا إخفاء بيانات مهمة | M26-039, M26-040 |  | docs/FRONTEND-TOOLING.md §6 |  |  | NOT STARTED | NO |
| `RESP-019` | Desktop: max-width containers وعدم مط المحتوى | M26-017 | R-03 |  |  | tooling/tests/prototype/responsive.spec.mjs | NOT STARTED | PROTOTYPE |
| `RESP-020` | Desktop: الاستفادة من المساحة الإضافية دون كثافة زائدة | M26-018 |  | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §11 |  |  | NOT STARTED | PROTOTYPE |
| `RESP-021` | Tablet حالة مستقلة | M26-019 | R-03 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §11 · docs/menu-ia/UX-VALIDATION.md §2 |  | tooling/tests/prototype/responsive.spec.mjs | NOT STARTED | PROTOTYPE |
| `RESP-022` | كل Component متجاوب بذاته | M26-020 |  | docs/FRONTEND-TOOLING.md §6 |  |  | NOT STARTED | NO |
| `RESP-023` | Fluid Responsive System بلا device-specific patching | M26-052, M26-053 |  | design-system/README.md |  |  | NOT STARTED | NO |
| `RESP-024` | المبدأ النهائي: تجربة مصممة لكل جهاز بلا compromises | M26-056, M26-057 |  |  |  |  | NOT STARTED | NO |
| `RESP-025` | Owner Dashboard ممتازة على الهاتف | M25-138, M25-139 |  |  |  |  | NOT STARTED | NO |
| `RESP-026` | شبكة المنيو على الموبايل: عمودان من 360px، عمود واحد أفقي تحته | M23-052 | F-12, R-02, R-07, D-144 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §11 · docs/menu-ia/UX-VALIDATION.md §3 · docs/menu-ia/MENU-DECISION-REGISTER.md R-02, R-07 |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `RESP-027` | شبكة المنيو على التابلت والديسكتوب: 3 أعمدة حتى 1199px و4 من 1200px | M23-055 | F-12, R-03, D-144 | docs/menu-ia/UX-VALIDATION.md §3 R-03 |  | tooling/tests/prototype/menu-wireframe.spec.mjs · tooling/tests/prototype/responsive.spec.mjs | NOT STARTED | PROTOTYPE |
| `RESP-028` | Responsive fallback للقائمة الجانبية على الديسكتوب | M23-088 | R-03, R-05 |  | tooling/viewports.mjs | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `RESP-029` | تفويض Phase G: حسم القرارات المتروكة للاختبار بعد الـwireframes | M23-246, M23-054 | R-01, R-02, R-03, R-04, R-05, R-06, R-07 | docs/menu-ia/UX-VALIDATION.md §3 |  |  | TESTED | PROTOTYPE |
| `NAV-001` | دراسة الـHeaders والـMobile Navigation | M01-099 | DB-05, D-061 | docs/phase-01-discovery/05-decisions-before-design.md DB-05 · docs/phase-01-discovery/07-question-backlog.md R4-04 |  |  | PARTIAL | N/A |
| `NAV-002` | اختصارات سريعة: Menu · Locations · Contact | M01-100 | DB-05, D-027 | docs/phase-01-discovery/05-decisions-before-design.md DB-05 |  |  | NOT STARTED | NO |
| `NAV-003` | دراسة Event Indicator و Campaign Indicator في الـNavigation | M01-102 |  |  |  |  | NOT STARTED | NO |
| `NAV-004` | لا إخفاء للمعلومات أو الوظائف المهمة على الموبايل | M01-103, M26-027 | DB-05 | docs/phase-01-discovery/05-decisions-before-design.md DB-05 |  |  | NOT STARTED | NO |
| `NAV-005` | Navigation حسب السياق بنفس الـIA | M26-026 |  |  |  |  | NOT STARTED | NO |
| `NAV-006` | ترتيب الـCTA على الموبايل (مبدئي): Menu ثم Locations / Directions | M03-028, M03-029, M04-047 | D-013, D-027, DB-16 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-03 · docs/governance/DECISION-LOG.md D-027 |  |  | NOT STARTED | NO |
| `NAV-007` | المنيو في الـHeader وليس زرًا رابعًا في شريط صفحة الفرع | M14-003 | D-061, D-062 | docs/governance/DECISION-LOG.md D-061 · docs/phase-01-discovery/09-architecture-options-after-r1.md AR-03 |  |  | NOT STARTED | NO |
| `NAV-008` | دراسة Menu IA تجيب عن أسئلة التنقل 9 و13 | M21-033, M21-037 | D-141, D-142, F-16 | docs/phase-01-discovery/22-menu-information-architecture.md · docs/menu-ia/MENU-DECISION-REGISTER.md F-16 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `NAV-009` | موبايل: Horizontal Sticky Category Bar مع active state تلقائي | M23-084 | F-16, R-06, R-09 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §2, §5 · docs/menu-ia/MENU-DECISION-REGISTER.md R-06, R-09 |  | tooling/tests/prototype/responsive.spec.mjs | NOT STARTED | PROTOTYPE |
| `NAV-010` | زر «كل الفئات / All Categories» | M23-085 | F-16 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §2 · docs/menu-ia/USER-FLOWS.md F-07 |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | PROTOTYPE |
| `NAV-011` | وضوح التمرير الأفقي في شريط الفئات | M23-086 |  | docs/menu-ia/UX-VALIDATION.md P1-04 |  |  | NOT STARTED | PROTOTYPE |
| `NAV-012` | ديسكتوب: Sticky Side Category Navigation بجانب الشبكة | M23-087 | F-16, R-03 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §11 · docs/menu-ia/MENU-DECISION-REGISTER.md R-03 |  | tooling/tests/prototype/responsive.spec.mjs | NOT STARTED | PROTOTYPE |
| `NAV-013` | Category anchors بـslug conventions نظيفة وثابتة | M23-089 | F-16 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §4 |  |  | NOT STARTED | PROTOTYPE |
| `NAV-014` | روابط الفئات تعمل بدون JavaScript | M23-090 | F-16 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §20 |  |  | NOT STARTED | PROTOTYPE |
| `NAV-015` | Sticky header offset عند القفز للـanchor | M23-091 | R-06, R-09 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md |  | tooling/tests/prototype/responsive.spec.mjs · tooling/tests/app/menu.spec.mjs | NOT STARTED | PROTOTYPE |
| `HOME-001` | الصفحة الرئيسية معتمدة | M03-036 | D-015, D-052, D-066 | docs/phase-01-discovery/10-url-architecture-draft.md §3 · docs/phase-01-discovery/15-root-gateway-wireframe.md |  |  | NOT STARTED | NO |
| `HOME-002` | لا Homepage Template عادية — Story Architecture خاصة بـSHELTER | M01-087, M01-088 |  | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-04 |  |  | NOT STARTED | NO |
| `HOME-003` | أسئلة الرئيسية للـOwner ثم اقتراح أفضل User Flow | M01-089, M01-090 | D-068 | docs/phase-01-discovery/07-question-backlog.md R4-01..R4-03 · docs/phase-01-discovery/09-architecture-options-after-r1.md AR-04 |  |  | NOT STARTED | NO |
| `HOME-004` | ترتيب الجمهور مدخل فقط — يُشرح أثره قبل قرارات التصميم | M03-025, M03-026 | D-012 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-04 · docs/governance/DECISION-LOG.md D-012 |  |  | PARTIAL | N/A |
| `HOME-005` | لا إبراز Online Ordering كهدف أساسي حاليًا | M14-044 | D-073, D-016 |  |  |  | NOT STARTED | NO |
| `MENU-001` | صفحة المنيو صفحة أساسية معتمدة — الهدف: أفضل Digital Menu لمقهى Specialty Coffee | M03-037, M23-008, M23-012 | D-015, D-087, D-142, D-143, DB-06 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §1–§3 · docs/menu-ia/README.md §1 · docs/menu-ia/PERFORMANCE-BUDGET.md |  | tooling/tests/prototype/menu-wireframe.spec.mjs · tooling/tests/prototype/responsive.spec.mjs · tooling/tests/app/menu.spec.mjs | NOT STARTED | PROTOTYPE |
| `MENU-002` | مبدأ تجربة المنيو: FAST · VISUAL · SIMPLE · MOBILE-FIRST | M01-104, M15-055, M15-057 | D-087, D-142 | docs/menu-ia/UX-VALIDATION.md §1 · docs/menu-ia/USER-FLOWS.md |  | tooling/tests/prototype/menu-wireframe.spec.mjs · tooling/tests/prototype/responsive.spec.mjs · tooling/tests/app/menu.spec.mjs | NOT STARTED | PROTOTYPE |
| `MENU-003` | المنيو ليست E-commerce (لا checkout/سلة/دفع في V1) | M15-056, M23-011 | D-016, D-087 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §2 |  |  | NOT STARTED | NO |
| `MENU-004` | Data Model قوي وUX بسيط — لا واجهة تشبه POS ولا عرض كل الحقول | M15-028, M15-054, M23-135 | D-080, D-086 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §6 · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §7 |  |  | NOT STARTED | NO |
| `MENU-005` | المنيو الرسمي من الـOwner = PRIMARY MENU SOURCE (المصدر الوحيد للحقيقة) | M01-061, M14-051, M15-002, M15-010, M15-013, M16-001, M17-002, M23-013 | D-006, D-074, D-075, D-076 | docs/phase-01-discovery/16-menu-intake-and-ssot.md · docs/phase-01-discovery/18-official-menu-inventory-report.md · docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md §Source Lineage |  |  | FROZEN | YES |
| `MENU-006` | منيو الموقع القديم للمقارنة فقط | M01-062, M01-063, M15-011, M15-012 | D-006, D-076 | docs/phase-01-discovery/16-menu-intake-and-ssot.md · docs/phase-01-discovery/18-official-menu-inventory-report.md |  |  | FROZEN | N/A |
| `MENU-007` | نطاق الملف الرسمي: مصدر للحقول والأصناف الموجودة فيه فقط | M15-062, M15-063, M15-064, M18-022 | D-089, D-091, D-099 | docs/phase-01-discovery/18-official-menu-inventory-report.md · docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | YES |
| `MENU-008` | معالجة المنيو مسؤولية الفريق: قراءة كاملة، استخراج، كشف، Data Model، تعبئة SSOT | M01-064, M15-004, M15-005, M15-006, M15-065, M15-066, M15-068 | D-075, D-088 | docs/phase-01-discovery/16-menu-intake-and-ssot.md · docs/phase-01-discovery/18-official-menu-inventory-report.md · docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | YES |
| `MENU-009` | فحص جودة بيانات المنيو وفصل أنواع الملاحظات | M15-069, M18-048 | D-089, D-108 | docs/phase-01-discovery/18-official-menu-inventory-report.md · docs/phase-01-discovery/19-menu-p0-owner-review.md |  |  | FROZEN | YES |
| `MENU-010` | تقرير أولي قبل أي تصميم (11 بندًا) والعرض على الـOwner | M15-060, M15-090 | D-088, D-089 | docs/phase-01-discovery/18-official-menu-inventory-report.md |  |  | FROZEN | N/A |
| `MENU-011` | لا اختراع لبيانات المنيو الناقصة — MISSING — OWNER INPUT REQUIRED | M15-029, M15-030, M15-031, M15-078, M15-079 | D-081, D-089 | docs/phase-01-discovery/16-menu-intake-and-ssot.md · docs/phase-01-discovery/17-menu-data-model-draft.md · docs/menu-ia/MENU-DECISION-REGISTER.md §3 |  |  | PARTIAL | YES |
| `MENU-012` | بوابة: التحقق مع الـOwner قبل نشر بيانات المنيو | M01-047, M15-033 | D-081, D-084, D-089 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §18 · docs/phase-01-discovery/16-menu-intake-and-ssot.md |  |  | NOT STARTED | NO |
| `MENU-013` | لا تغيير لأسماء/أسعار/أوصاف/مكونات/فئات الأصناف بدون موافقة الـOwner | M01-065, M15-038 | D-082, D-089, D-133 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §18 |  |  | PARTIAL | YES |
| `MENU-014` | البنية تدعم إظهار الأسعار بالكامل على الموقع | M15-017 | D-078, DB-10 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §6 |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `MENU-015` | السعر المعروض = السعر الرسمي من الملف (نهائي شامل الضريبة) بلا حسابات في الواجهة | M15-018, M15-072, M15-077, M17-003 | D-078, D-089, D-122, F-01 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §6 (4) |  | tooling/tests/prototype/menu-wireframe.spec.mjs | PARTIAL | YES |
| `MENU-016` | الضريبة: لا تُضاف مرة ثانية ولا يُعدَّل السعر | M15-067, M15-073, M15-074, M23-016 | D-089, D-122, F-01 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | YES |
| `MENU-017` | لا '+ VAT' ولا Net Price أمام العميل | M23-068 | D-104, F-01 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §6 (4) |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `MENU-018` | لا حساب Net Price أو VAT Amount أو Tax Rate الآن | M15-075, M18-035, M18-037, M19-035 | D-104, D-122 | docs/phase-01-discovery/17-menu-data-model-draft.md §2 price/price_tax_detail |  |  | FROZEN | YES |
| `MENU-019` | جاهزية Tax Metadata بدون إعادة بناء (بدون تعبئة) | M15-076, M18-036 | D-089, D-103, D-104 | docs/phase-01-discovery/17-menu-data-model-draft.md §2 |  |  | PARTIAL | N/A |
| `MENU-020` | تخزين السعر: price_fils (عدد صحيح) · currency = JOD · tax_inclusive = true | M18-033, M18-034, M19-034, M23-015, M23-017 | D-103, D-122, F-01 | docs/phase-01-discovery/17-menu-data-model-draft.md §2 price · docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | YES |
| `MENU-021` | Menu Version MV-2026-10-01 وتاريخ سريان الأسعار 2026-10-01 | M19-032, M23-014 | D-107, D-122, D-135, F-01 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md · docs/phase-01-discovery/17-menu-data-model-draft.md §4 |  |  | FROZEN | YES |
| `MENU-022` | جاهزية أنواع الأسعار المستقبلية | M15-019 | D-078 | docs/phase-01-discovery/17-menu-data-model-draft.md §2 price |  |  | PARTIAL | N/A |
| `MENU-023` | لا نظام عروض (Promotions) معقد داخل المنيو قبل مناقشة الـOwner | M15-020 | D-078 |  |  |  | NOT STARTED | N/A |
| `MENU-024` | فرق السعر بين DRIVE وHOUSE = نفس Product ID + Branch Price Override | M15-021, M23-134 | D-078, D-079 | docs/phase-01-discovery/17-menu-data-model-draft.md §2 price |  |  | PARTIAL | N/A |
| `MENU-025` | أسعار تطبيقات التوصيل (R2B-03) = MISSING | M14-045 | D-073 |  |  |  | NOT STARTED | N/A |
| `MENU-026` | لا افتراض لتطابق الأصناف والأسعار بين DRIVE وHOUSE | M15-022, M15-023, M15-025 | D-079, D-094 | docs/menu-ia/MENU-DECISION-REGISTER.md §3 M-01 |  |  | PARTIAL | YES |
| `MENU-027` | حقول التوفر لكل صنف: availability_drive · availability_house | M15-024 | D-079, D-106, D-145 | docs/phase-01-discovery/17-menu-data-model-draft.md §2 · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §19 |  |  | FROZEN | YES |
| `MENU-028` | التوفر الحالي لكل الأصناف في الفرعين = UNKNOWN | M18-010 | D-094, F-19 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.6 · docs/menu-ia/MENU-DECISION-REGISTER.md §3 M-01 |  | tooling/tests/app/menu.spec.mjs | PARTIAL | YES |
| `MENU-029` | حالات التوفر للعميل: Available · Unavailable + Show · Unavailable + Hide | M23-115, M23-116 | D-145, F-19 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.6 · docs/menu-ia/USER-FLOWS.md F-12 |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `MENU-030` | كل مشروب ONE SIZE ONLY — مع بقاء الـData Model قابلًا لدعم الأحجام والـModifiers | M15-026, M18-027, M18-028, M19-024, M19-025 | D-080, D-101, D-118 | docs/phase-01-discovery/17-menu-data-model-draft.md §2 product_size · docs/menu-ia/MENU-DECISION-REGISTER.md §3 M-11 |  |  | FROZEN | YES |
| `MENU-031` | لا تُعرض Add-ons على الموقع (show_on_website = false) | M15-027, M19-026, M19-027, M19-028, M23-130 | D-080, D-119, D-147, F-21 | docs/phase-01-discovery/17-menu-data-model-draft.md §2 modifier_group · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §18 |  |  | FROZEN | YES |
| `MENU-032` | الغياب عن الملف ≠ إلغاء (NOT PRESENT IN CURRENT OWNER FILE) | M18-023 | D-099 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | N/A |
| `MENU-033` | 4 أصناف تُباع وليست في الملف = ACTIVE — DATA INCOMPLETE | M19-012, M19-013, M19-014, M19-015, M19-017, M19-018, M19-019, M21-019, M21-020, M23-139, M23-140 | D-115, D-140 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md · docs/menu-ia/MENU-DECISION-REGISTER.md §3 M-06 |  |  | PARTIAL | N/A |
| `MENU-034` | Snacks · Pastries = OWNER VERIFICATION REQUIRED | M19-016, M21-021, M23-141 | D-116, D-140 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | NOT STARTED | N/A |
| `MENU-035` | الأرقام الناقصة في عمود # تبقى كما هي | M18-024 | D-099 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | YES |
| `MENU-036` | 11 فئة مصدرية مجمّدة بمعرّفات ثابتة CAT-001 → CAT-011 | M15-087, M23-025 | D-089, D-134, D-135, F-05 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md · docs/menu-ia/MENU-DECISION-REGISTER.md §1 F-05 |  |  | FROZEN | YES |
| `MENU-037` | فصل اسم عرض الفئة عن الـSlug والـInternal ID | M15-088, M18-038, M18-039 | D-089, D-105, D-143 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §4 · docs/phase-01-discovery/17-menu-data-model-draft.md §2 category |  |  | PARTIAL | N/A |
| `MENU-038` | التهجئة المعتمدة: SPECIALITY COFFEE (وليس SPECIALTY) | M18-040, M20-022, M23-026 | D-105, D-130, F-05 | docs/menu-ia/MENU-DECISION-REGISTER.md §1 F-05 |  |  | FROZEN | YES |
| `MENU-039` | ترتيب الفئات للعميل | M23-027 | D-143, F-06 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §3 · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §4 |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `MENU-040` | SWEETS / حلويات: دمج CAKE + COOKIES بصريًا فقط | M23-029 | D-143, F-07 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §4 · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §19 |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `MENU-041` | SPRING: الأصناف الخمسة متاحة حاليًا — مع بقاء Seasonal Metadata | M18-025, M18-026, M19-020, M19-021 | D-100, D-117, F-17 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §10 |  |  | FROZEN | YES |
| `MENU-042` | تواريخ موسم SPRING = MISSING (لا تمنع العرض) | M19-022, M19-023 | D-117 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §10 |  |  | NOT STARTED | N/A |
| `MENU-043` | SPRING قسم موسمي مستقل عمودي مضغوط أعلى المنيو (لا Carousel) | M23-028, M23-092, M23-093 | D-143, F-06, F-17 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §10 · docs/menu-ia/UX-VALIDATION.md §2 P1-05/P1-09 |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `MENU-044` | عند انتهاء الموسم يختفي القسم ولا تُحذف البيانات | M23-096 | D-148, F-17 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §10 |  |  | NOT STARTED | NO |
| `MENU-045` | الأقسام الفرعية: داخل HOT وCOLD وFIZZY فقط وعند تحسين سرعة الوصول | M23-030, M23-031, M23-032, M23-034 | D-143, F-08 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §5 · docs/menu-ia/SUBCATEGORY-PROPOSAL.md |  | tooling/tests/prototype/menu-wireframe.spec.mjs · tooling/tests/prototype/responsive.spec.mjs | NOT STARTED | PROTOTYPE |
| `MENU-046` | مقترح الأقسام الفرعية وجدول التوزيع (Phase C) | M23-033, M23-239 | F-08, P-04 | docs/menu-ia/SUBCATEGORY-PROPOSAL.md · docs/menu-ia/subcategory-mapping.csv | docs/menu-ia/wireframes/tools/subcats.py |  | IMPLEMENTED — NOT TESTED | N/A |
| `MENU-047` | MENU INVENTORY v1.0 = APPROVED BASELINE (تجميد المعرّفات والـLineage) | M18-002, M19-036, M20-029, M20-030, M21-001, M21-002 | D-090, D-113, D-134, D-135, D-136, DB-22 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md · docs/menu-ia/MENU-DECISION-REGISTER.md §1 |  |  | FROZEN | YES |
| `MENU-048` | تقارير ما قبل v1.0 وتقرير الـFreeze المختصر | M19-038, M20-032 | D-123, D-134 | docs/phase-01-discovery/20-menu-pre-v1-review.md · docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | YES |
| `MENU-049` | دراسة Menu IA: 15 سؤالًا + OPTION A/B/C + مقارنة + توصية | M21-024, M21-025, M21-026, M21-027, M21-028, M21-029, M21-030, M21-031, M21-032, M21-034, M21-040, M21-041, M21-042 | D-141, D-142, D-143, DB-06 | docs/phase-01-discovery/22-menu-information-architecture.md · docs/menu-ia/MENU-DECISION-REGISTER.md §4 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `MENU-050` | لا يُعاد فتح Menu IA المعتمدة | M27-069 | D-142, D-143, D-144, D-145, D-146, D-147, D-148, D-149 | docs/menu-ia/MENU-DECISION-REGISTER.md §1 |  |  | FROZEN | N/A |
| `MENU-051` | استخراج كل قرارات المنيو إلى Decision Register بحالة FROZEN/APPROVED | M27-070, M27-071 | D-142 | docs/menu-ia/MENU-DECISION-REGISTER.md · docs/governance/DECISION-LOG.md |  |  | PARTIAL | N/A |
| `MENU-052` | بوابة: اعتماد الـOwner لـMenu IA Spec + Wireframes قبل Visual Design/Production |  | D-142, DB-06, F-23 | docs/menu-ia/README.md · docs/menu-ia/SHELTER-MENU-IA-SPEC.md · docs/menu-ia/MENU-DECISION-REGISTER.md · docs/menu-ia/UX-VALIDATION.md · docs/menu-ia/wireframes/README.md | docs/menu-ia/wireframes/tools/wf.py | tooling/tests/prototype/menu-wireframe.spec.mjs · tooling/tests/prototype/responsive.spec.mjs | IMPLEMENTED — NOT TESTED | PROTOTYPE |
| `MENU-053` | تنقل الفئات: Sticky + قفز فوري + Active Category أثناء التمرير | M01-105, M01-106, M01-107 | F-16, D-143 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §2 · docs/menu-ia/USER-FLOWS.md F-06 · docs/menu-ia/UX-VALIDATION.md §3 R-06 |  | tooling/tests/prototype/responsive.spec.mjs · tooling/tests/app/menu.spec.mjs | NOT STARTED | PROTOTYPE |
| `MENU-054` | لا Infinite Scroll ولا Pagination ولا Load More في صفحة المنيو | M23-039 | D-147, F-09 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §2 · docs/menu-ia/PERFORMANCE-BUDGET.md |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `MENU-055` | استعادة حالة الصفحة: التمرير والفرع وسياق الفئة | M23-216 | D-145, D-146 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §12 |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `MENU-056` | Lenis لا يُشغَّل على صفحة المنيو إلا إذا أثبت الاختبار فائدته | M24-013 |  | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §17 |  |  | NOT STARTED | N/A |
| `MENU-057` | لا Favorites ولا Heart Icons في V1 | M23-127 | D-147, F-21 | docs/menu-ia/MENU-DECISION-REGISTER.md §1 F-21 |  |  | NOT STARTED | N/A |
| `MENU-058` | لا زر مشاركة للصنف في V1 (#p-{slug} للـHistory فقط) | M23-129 | D-147, F-21 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §7 · docs/menu-ia/MENU-DECISION-REGISTER.md §4 CF-04 |  |  | NOT STARTED | N/A |
| `MENU-059` | أي Intelligent Recommendation System لاحقًا وليس في V1 | M23-208 | D-148 |  |  |  | NOT STARTED | N/A |
| `MENU-060` | Menu Editor في الـDashboard | M25-085 | D-084, D-136, D-148 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §18 |  |  | NOT STARTED | NO |
| `MENU-061` | إدارة التوفر لكل صنف في DRIVE وHOUSE من الـMenu Editor | M25-086, M25-087 | D-145, F-19 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §18 |  |  | NOT STARTED | NO |
| `MENU-062` | Bulk Actions للمنيو | M25-088 |  |  |  |  | NOT STARTED | NO |
| `PROD-001` | Product ID داخلي ثابت لكل صنف — لا يعتمد على الاسم ولا يتغير | M15-083, M15-084, M15-085, M15-086, M20-003 | D-089, D-133, D-135, F-03 | docs/phase-01-discovery/17-menu-data-model-draft.md §3 · docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | YES |
| `PROD-002` | Inventory مجمّد: 192 سجل مصدر ← 191 صنفًا فعالًا | M21-008, M23-018, M23-019 | D-135, F-02, F-03, DB-22 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md · docs/menu-ia/MENU-DECISION-REGISTER.md §1 F-02/F-03 |  |  | FROZEN | YES |
| `PROD-003` | PRD-00120 = RETIRED/MERGED ولا يُعاد استخدامه · PRD-00115 = Canonical | M20-011, M21-003, M21-004, M23-020 | D-125, D-136, F-03 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md · docs/phase-01-discovery/17-menu-data-model-draft.md §4b |  |  | FROZEN | YES |
| `PROD-004` | المعرّف التالي لأي صنف جديد: PRD-00193 | M21-005, M23-021 | D-136, F-03 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | PARTIAL | N/A |
| `PROD-005` | تجميد الـID لا يجمّد الحقول — كلها Versioned مع Audit History | M21-006 | D-136 | docs/phase-01-discovery/17-menu-data-model-draft.md §2 |  |  | PARTIAL | N/A |
| `PROD-006` | عمود # في المصدر = SEQUENTIAL NUMBER ONLY | M18-031, M19-029, M23-138 | D-102, D-120 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | YES |
| `PROD-007` | حقلا pos_item_id و external_item_id ثابتان وفارغان حتى توفر الـPOS | M15-016 | D-077, D-102, D-121 | docs/phase-01-discovery/17-menu-data-model-draft.md §2 product |  |  | FROZEN | YES |
| `PROD-008` | قواعد الدمج: Preserve first, merge later | M18-013, M18-014, M18-015 | D-096, D-110, D-125 | docs/phase-01-discovery/17-menu-data-model-draft.md §4b |  |  | FROZEN | YES |
| `PROD-009` | DUP-01: #115 و#120 = SAME PRODUCT — دمج بحفظ كامل الـLineage | M19-003, M19-004, M19-037, M20-010, M20-028 | D-110, D-125, F-22 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | YES |
| `PROD-010` | DUP-02: AMERICAN COFFEE ≠ AMERICANO · ICED AMERICAN ≠ ICED AMERICANO | M19-005, M19-006, M23-143, M23-144 | D-111, F-22 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | YES |
| `PROD-011` | DUP-03: ESPRESSO MACCHIATO ≠ MACCHIATO | M19-007, M23-145 | D-112, F-22 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | YES |
| `PROD-012` | DUP-04: أزواج ICED SHAKEN مقابل ICED … LATTE الخمسة = منتجات مختلفة | M19-008, M19-009, M19-010, M20-004, M20-005, M20-006, M20-007, M20-008, M23-146, M23-147, M23-148, M23-149, M23-150 | D-113, D-124, F-22 | docs/phase-01-discovery/20-menu-pre-v1-review.md · docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | YES |
| `PROD-013` | الاسم الإنجليزي المعتمد: ICED SHAKEN SALTED CARAMEL (#67) | M20-009, M21-015, M23-151 | D-124, D-138, F-22, P-02 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md · docs/menu-ia/MENU-DECISION-REGISTER.md §2 P-02 |  |  | FROZEN | YES |
| `PROD-014` | TURKISH COFFEE: S = Single · D = Double | M18-012, M19-002, M20-018, M23-142 | D-095, D-109, D-127, F-22 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | YES |
| `PROD-015` | Item #129: الفاكهة الثالثة = MANGO | M18-021, M19-011, M23-152 | D-098, D-114, F-22 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | YES |
| `PROD-016` | Sugar-Free / Red Bull / Iced Shaken: لا تحويل إلى Product + Variants ولا دمج | M18-016, M18-029, M20-015, M20-016 | D-097, D-126, F-22 | docs/phase-01-discovery/17-menu-data-model-draft.md §5 (DM-01 مسحوب) |  |  | FROZEN | YES |
| `PROD-017` | فصل هوية البيانات عن التجميع في العرض | M18-017, M18-018, M18-019, M18-020 | D-097 | docs/phase-01-discovery/17-menu-data-model-draft.md §1 |  |  | FROZEN | N/A |
| `PROD-018` | Product Family presentation: مقترح لاحق وقرار الـOwner | M18-030, M21-035 | D-097, P-09 | docs/menu-ia/MENU-DECISION-REGISTER.md §2 P-09 |  |  | NOT STARTED | N/A |
| `PROD-019` | Source Preservation: طبقات SOURCE / NORMALIZED / DISPLAY | M15-034, M18-005, M18-006, M20-025, M20-026, M23-022, M23-024 | D-082, D-092, D-133, F-04 | docs/phase-01-discovery/17-menu-data-model-draft.md §0–§2 · docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | YES |
| `PROD-020` | لا تصحيح تلقائي للأسماء — Suggested Correction وموافقة الـOwner | M15-070, M15-071, M18-007, M18-008 | D-089, D-093 | docs/phase-01-discovery/19-menu-p0-owner-review.md · docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | YES |
| `PROD-021` | مجموعات التسمية الإنجليزية المعتمدة G1–G5 | M20-012, M20-013, M20-014, M20-017 | D-126, F-22 | docs/phase-01-discovery/20-menu-pre-v1-review.md · docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md |  |  | FROZEN | YES |
| `PROD-022` | الأسماء الإنجليزية للعرض ALL CAPS — أسلوب عرض فقط | M20-023, M20-024 | D-131, F-22 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §6 (3) |  | tooling/tests/prototype/menu-wireframe.spec.mjs | PARTIAL | PROTOTYPE |
| `PROD-023` | الأسماء غير الواضحة الستة تبقى كما هي — PENDING OWNER REVIEW | M21-017, M21-018, M23-160 | D-139, P-03 | docs/menu-ia/MENU-DECISION-REGISTER.md §2 P-03 |  |  | PARTIAL | N/A |
| `PROD-024` | Menu Data Model كامل قبل أي UI — الحقول الأساسية (R3) | M15-053 | D-086, DB-22 | docs/phase-01-discovery/17-menu-data-model-draft.md §1–§2 |  |  | PARTIAL | N/A |
| `PROD-025` | حقول المصدر والحالة والإصدار الإلزامية (Data Model v0.3→v0.4) | M18-041, M18-046 | D-106, D-108, DB-22 | docs/phase-01-discovery/17-menu-data-model-draft.md §2 |  |  | FROZEN | YES |
| `PROD-026` | حقول Product Data Model من موجز M23 §43 (delta v0.5) | M23-133 | D-142, D-148 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §19 |  |  | PARTIAL | N/A |
| `PROD-027` | بطاقة الصنف: IMAGE ← الاسم الأساسي ← الثانوي ← PRICE · بلا وصف | M01-109, M23-040, M23-041, M23-056 | D-144, F-10 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §6 · docs/menu-ia/UX-VALIDATION.md §3 R-02/R-07 |  | tooling/tests/prototype/menu-wireframe.spec.mjs · tooling/tests/app/menu.spec.mjs | NOT STARTED | PROTOTYPE |
| `PROD-028` | البطاقة تعرض فقط المعلومات المعتمدة — الاسم العربي المعتمد فقط | M01-110 | D-091, D-137, F-10 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §6 · docs/menu-ia/MENU-DECISION-REGISTER.md §4 CF-03 |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `PROD-029` | السعر يظهر دائمًا داخل البطاقة | M23-066 | D-144, F-10 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §6 |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `PROD-030` | بطاقة بلا صورة مقصودة ونظيفة — لا Placeholder قبيح | M23-050 | D-083, F-11 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §6 (8) · docs/menu-ia/UX-VALIDATION.md §2 P1-03 |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | PROTOTYPE |
| `PROD-031` | الشارات العامة NEW / SEASONAL فقط — FEATURED داخلي | M23-069, M23-071 | D-144, F-14 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §6 |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `PROD-032` | حالات المنتج من الـDashboard بدون تعديل الكود | M01-111 | D-084, D-086, D-148 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §18 |  |  | NOT STARTED | NO |
| `PROD-033` | تفاصيل الصنف: Bottom Sheet على الموبايل · Modal على الديسكتوب | M23-057, M23-058 | D-146, F-13, R-05 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §7 · docs/menu-ia/UX-VALIDATION.md §3 R-05 |  | tooling/tests/prototype/responsive.spec.mjs · tooling/tests/app/menu.spec.mjs | NOT STARTED | PROTOTYPE |
| `PROD-034` | التفاصيل تعرض الحقول الموجودة والمعتمدة فقط — لا حقول فارغة | M23-059, M23-060 | D-146, F-13 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §7 · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §20 |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `PROD-035` | Back (المتصفح/Android) يغلق الـBottom Sheet أولًا | M23-062 | D-146, F-13 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §7 |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `PROD-036` | بعد إغلاق التفاصيل يعود المستخدم لنفس موضع التمرير | M23-063 | D-146, F-13 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §7 |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `SRCH-001` | Search سريع داخل المنيو يوصل مباشرة للمنتج بدون Reload | M01-108 | D-143, F-15 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §8 · docs/menu-ia/USER-FLOWS.md F-02 |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `SRCH-002` | مكان البحث: مباشرة تحت عنوان Menu وقبل الفئات | M23-072 | F-15 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §8 |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `SRCH-003` | اقتراحات سريعة + فلترة مباشرة للشبكة أثناء الكتابة | M23-073, M23-074 | D-143, F-15 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §8 |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `SRCH-004` | Jump to Product: الانتقال للصنف وإبرازه بلطف بدون Reload | M23-075 | F-15 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §8 |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `SRCH-005` | البحث بالعربي والإنجليزي بغض النظر عن لغة الصفحة | M23-076 | D-143, F-15 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §8 |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `SRCH-006` | مطابقة البحث: حالة الأحرف · تطبيع العربية · الأخطاء الشائعة · Aliases معتمدة | M23-077 | F-15 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §8 |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `SRCH-007` | لا synonyms مخترعة أو غير موثوقة | M23-078 | F-15, P-06 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §8 |  |  | NOT STARTED | N/A |
| `SRCH-008` | بحث بلا نتائج: رسالة + مسح البحث (+ أقرب فئة إن كان منطقيًا) | M23-080, M23-081 | F-15 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §8 · docs/menu-ia/USER-FLOWS.md F-13 |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `SRCH-009` | Sticky Search: حقل كامل في البداية ثم أيقونة مضغوطة داخل شريط الفئات | M23-083 | F-15, R-06 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §8 · docs/menu-ia/UX-VALIDATION.md §3 R-06 |  | tooling/tests/prototype/responsive.spec.mjs | NOT STARTED | PROTOTYPE |
| `BRANCH-001` | بوابة نشر بيانات الفروع: لا نشر قبل تحقق/موافقة الـOwner | M01-045 | D-003, D-017, D-008, D-020 | docs/phase-01-discovery/04-content-approval-register.md §A CR-017…CR-037، §B MI-007…MI-009 · docs/phase-01-discovery/03-verify-with-owner.md VQ-02، VQ-09…VQ-12، VQ-15 |  |  | PARTIAL | N/A |
| `BRANCH-002` | الفروع العامة الحالية = فرعان فقط (DRIVE وHOUSE) في إربد | M03-008, M03-010, M03-015, M23-097 | D-008, D-010, GEP-§3 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-02 · docs/google/GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md |  |  | NOT STARTED | NO |
| `BRANCH-003` | هوية فرع DRIVE: الاسم AR/EN والموقع ووجود Drive Thru | M03-005, M04-011, M04-012 | D-008, D-020, D-053 | docs/google/GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md (DRIVE) · docs/phase-01-discovery/04-content-approval-register.md CR-018…CR-024 |  |  | NOT STARTED | NO |
| `BRANCH-004` | هوية فرع HOUSE: الاسم AR/EN والموقع داخل Irbid City Center | M03-006, M04-021, M04-022 | D-008, D-020, D-053 | docs/google/GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md (HOUSE) · docs/phase-01-discovery/04-content-approval-register.md CR-030…CR-037 |  |  | NOT STARTED | NO |
| `BRANCH-005` | العنوان التفصيلي للفرعين (عربي/إنجليزي) — بانتظار التحقق من GBP ثم الـOwner | M04-013, M04-014, M04-024 | D-020, D-048, GEP-§5 | docs/google/GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md · docs/phase-01-discovery/03-verify-with-owner.md VQ-09، VQ-21 · docs/phase-01-discovery/04-content-approval-register.md CR-019…CR-021 |  |  | NOT STARTED | N/A |
| `BRANCH-006` | اسم المول الرسمي لـHOUSE من GBP/موقع المول ثم موافقة الـOwner | M04-023 | D-020 | docs/google/GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md |  |  | NOT STARTED | N/A |
| `BRANCH-007` | مشغل الحلويات = NON-PUBLIC LOCATION | M03-007, M04-044 | D-008, D-026 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-02 · docs/google/GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md §مواقع غير عامة |  |  | NOT STARTED | NO |
| `BRANCH-008` | صفحة الفروع (Locations) + صفحة مستقلة لكل فرع | M03-038, M03-039, M01-215 | D-015, D-031, D-053, DB-07 | docs/phase-01-discovery/10-url-architecture-draft.md §3، §5، §6 |  |  | NOT STARTED | NO |
| `BRANCH-009` | نموذج بيانات الفرع (Branch data model) — الحقول | M01-214 | D-010, D-049, DB-15, DB-07 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-02، AR-08 · docs/google/BRANCH-DATA-SYNC.md |  |  | NOT STARTED | NO |
| `BRANCH-010` | Branch Editor في الـOwner Dashboard مع Preview قبل Publish | M25-090, M25-091 | D-021, D-049, D-057, D-058, D-056 |  |  |  | NOT STARTED | NO |
| `BRANCH-011` | إمكانية إضافة فرع Coming Soon بدون إطلاق صفحة كاملة | M01-217 | D-010, DB-15 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-02 |  |  | NOT STARTED | NO |
| `BRANCH-012` | خدمات الفروع: Data Model فقط — لا استنتاج ولا نشر True/False قبل تأكيد الـOwner | M10-015, M10-016, M10-018 | D-033 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-08 |  |  | NOT STARTED | NO |
| `BRANCH-013` | قيم خدمات DRIVE وHOUSE = MISSING — OWNER INPUT REQUIRED | M04-019, M10-017 | D-020, D-033, D-045 | docs/phase-01-discovery/03-verify-with-owner.md VQ-24 · docs/phase-01-discovery/04-content-approval-register.md CR-025، CR-083 |  |  | NOT STARTED | N/A |
| `BRANCH-014` | طرق الدفع: Architecture تدعم 5 طرق — لا نشر قبل تأكيد الـOwner | M10-019, M10-020 | D-034 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-08 |  |  | NOT STARTED | NO |
| `BRANCH-015` | قيم طرق الدفع لـDRIVE وHOUSE = MISSING — OWNER INPUT REQUIRED | M04-020 | D-020, D-034 | docs/phase-01-discovery/04-content-approval-register.md CR-082 |  |  | NOT STARTED | N/A |
| `BRANCH-016` | بطاقة الفرع (Branch Card) تعرض حالة الفرع وساعات العمل | M04-048 | D-027, D-062, D-057 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-03 · docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §2 |  |  | NOT STARTED | NO |
| `BRANCH-017` | شريط صفحة الفرع على الموبايل: [الاتجاهات] [اتصال] [واتساب] | M14-002 | D-061, D-062, D-064, D-065 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §3 · docs/phase-01-discovery/09-architecture-options-after-r1.md AR-03 |  |  | NOT STARTED | NO |
| `BRANCH-018` | عملية BRANCH DATA SYNC CHECK بين GBP والموقع والـSchema والخرائط | M11-020, M11-021, M12-048, M11-126, M11-133 | D-049, GEP-§7/§8, GIO-§A17/§A18/§A19, D-047 | docs/google/BRANCH-DATA-SYNC.md · docs/google/GOOGLE-ECOSYSTEM-POLICY.md §4، §7، §8، §11 · docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §17–§19 |  |  | PARTIAL | NO |
| `BRANCH-019` | متى يُشغَّل Branch Data Sync + فحص اتساق إلزامي عند بناء/تعديل أي صفحة فرع | M11-014, M11-015, M11-022 | D-049, GEP-§4, GEP-§7/§8 | docs/google/BRANCH-DATA-SYNC.md · docs/google/GOOGLE-ECOSYSTEM-POLICY.md §4، §8 |  |  | PARTIAL | NO |
| `BRANCH-020` | NAP Consistency: الاسم والعنوان والهاتف متطابقة وأي اختلاف يُسجَّل | M11-027, M11-028 | GEP-§5, D-060 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §11 |  |  | NOT STARTED | NO |
| `BRANCH-021` | محدد الفرع في المنيو: كل الفروع / DRIVE / HOUSE — اختياري والافتراضي كل الفروع | M23-098 | F-18, D-145, CF-02 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.1، §9.6 · docs/menu-ia/MENU-DECISION-REGISTER.md F-18، CF-02 |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `BRANCH-022` | Segmented Control 'كل الفروع \| DRIVE \| HOUSE' قابل للتحول إلى Bottom Sheet | M23-099, M23-100 | F-18, D-145 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.1 |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `BRANCH-023` | موضع محدد الفرع: اختبار A/B بالـWireframe والقرار A (تحت البحث وقبل الفئات) | M23-101 | R-01, D-142 | docs/menu-ia/UX-VALIDATION.md R-01 · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.1 |  |  | TESTED | PROTOTYPE |
| `BRANCH-024` | حفظ آخر اختيار للفرع على الجهاز دون قفل، وأولوية سياق صفحة الفرع | M23-102, M23-103 | F-18, D-145 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.2 |  |  | NOT STARTED | NO |
| `BRANCH-025` | من صفحة الفرع إلى المنيو: ?branch=drive / ?branch=house بدون سؤال إضافي | M23-107 | F-18, D-145 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §1، §9.2 |  | tooling/tests/app/menu.spec.mjs (F-08/F-09 — fixme) | NOT STARTED | NO |
| `BRANCH-026` | تغيير الفرع يحافظ على الفئة الحالية وموضع التمرير | M23-108 | F-18, D-145, D-149 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.3 |  | tooling/tests/app/menu.spec.mjs (F-03 — fixme) | NOT STARTED | NO |
| `BRANCH-027` | تفاصيل الصنف المفتوحة عند تغيير الفرع: 3 حالات | M23-109, M23-110, M23-111 | F-19, D-145, D-146 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §7 |  | tooling/tests/app/menu.spec.mjs (F-12 — fixme) | NOT STARTED | NO |
| `BRANCH-028` | إظهار فرق التوفر بين الفروع فقط عند الحاجة | M23-112, M23-113 | F-19, D-145, D-079 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.6 |  |  | NOT STARTED | PROTOTYPE |
| `BRANCH-029` | التوفر لكل فرع غير معروف: لا افتراض — البيانات PENDING OWNER VERIFICATION | M23-209, M23-210 | D-079, D-094, CF-02, M-01 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.6 · docs/menu-ia/MENU-DECISION-REGISTER.md CF-02، M-01 |  |  | NOT STARTED | PROTOTYPE |
| `BRANCH-030` | اختيار فرع مغلق لا يمنع تصفح المنيو ويعرض Closed now وموعد الافتتاح التالي | M23-126 | F-20, D-145 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.4 |  | tooling/tests/app/menu.spec.mjs (F-10 — fixme) | NOT STARTED | PROTOTYPE |
| `BRANCH-031` | زر "اطلب" لكل فرع = قدرة مستقبلية فقط | M14-043 | D-073, D-016 |  |  |  | NOT STARTED | N/A |
| `HOURS-001` | ساعات DRIVE العادية: السبت–الخميس 07:00–02:00 · الجمعة 08:00–02:00 | M04-016, M23-119 | D-020, F-20, P-08 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.4 · docs/menu-ia/evidence/hours_logic_check.py · docs/google/GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md |  |  | NOT STARTED | PROTOTYPE |
| `HOURS-002` | ساعات HOUSE العادية: السبت–الأربعاء 09:00–22:00 · الخميس–الجمعة 09:00–23:00 | M04-026, M23-120 | D-020, F-20, P-08 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.4 · docs/menu-ia/evidence/hours_logic_check.py · docs/google/GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md |  |  | NOT STARTED | PROTOTYPE |
| `HOURS-003` | لا فرق بين ساعات الدرايف ثرو والجلسات في DRIVE إلا بمعلومة موثقة وسؤال الـOwner | M04-017 | D-020 |  |  |  | NOT STARTED | N/A |
| `HOURS-004` | ساعات المول المختلفة أو الخاصة لـHOUSE تُعرض كتعارض ولا تُغيَّر تلقائيًا | M04-027 | D-020, GEP-§5, GEP-§9 | docs/google/GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md |  |  | NOT STARTED | N/A |
| `HOURS-005` | بنية الساعات: Regular + Special/Holiday + Temporary Closure + Emergency Closure | M04-030, M23-121 | D-021, DB-18, F-20 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-07 · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §19 |  |  | NOT STARTED | PROTOTYPE |
| `HOURS-006` | تعديل رمضان/العيد لا يغيّر الساعات العادية | M04-029 | D-021 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-07 |  |  | NOT STARTED | NO |
| `HOURS-007` | أولوية الحالة الخاصة/المؤقتة على الساعات العادية | M23-122 | D-021, F-20, DB-18 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.4 |  |  | NOT STARTED | PROTOTYPE |
| `HOURS-008` | تعديل الساعات بسهولة من الـDashboard (Regular ثم Special) | M25-092 | D-021, DB-18, D-049 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-07 |  |  | NOT STARTED | NO |
| `HOURS-009` | حالة الفرع تُحسب ديناميكيًا: Open Now · Closed Now · Closing Soon · Next Opening | M23-118, M25-093 | F-20, D-145, D-021 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.4 · docs/menu-ia/evidence/hours_logic_check.py | docs/menu-ia/evidence/hours_logic_check.py | tooling/tests/app/menu.spec.mjs (F-10، F-11 — fixme) · docs/menu-ia/evidence/hours_logic_check.py | NOT STARTED | PROTOTYPE |
| `HOURS-010` | عرض الحالة مع معلومات ساعات اليوم داخل محدد الفرع (ونصوص AR/EN) | M23-117, M23-124 | F-20, D-145 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.4 |  |  | NOT STARTED | PROTOTYPE |
| `HOURS-011` | يغلق قريبًا: آخر 60 دقيقة قبل الإغلاق مع العد | M23-123 | F-20, D-145 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.4 · docs/menu-ia/evidence/hours_logic_check.py | docs/menu-ia/evidence/hours_logic_check.py | tooling/tests/app/menu.spec.mjs (F-11 — fixme) · docs/menu-ia/evidence/hours_logic_check.py | NOT STARTED | PROTOTYPE |
| `HOURS-012` | التعامل الصحيح مع ساعات تمتد بعد منتصف الليل ("نقطة مهمة جدًا") | M23-125 | F-20, D-145 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.4 · docs/menu-ia/evidence/hours_logic_check.py | docs/menu-ia/evidence/hours_logic_check.py | tooling/tests/app/menu.spec.mjs (F-11 — fixme) · docs/menu-ia/evidence/hours_logic_check.py | NOT STARTED | PROTOTYPE |
| `HOURS-013` | Google Special Hours تُقارن مع الموقع ولا تغيير تلقائي على Production | M11-023, M11-024 | GEP-§9, D-049, D-048 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §9 · docs/google/BRANCH-DATA-SYNC.md |  |  | NOT STARTED | N/A |
| `CAMP-001` | صفحة «الفعاليات والحملات» معتمدة | M03-042 | D-015, D-070, URL-04 | docs/phase-01-discovery/10-url-architecture-draft.md §3, §6 URL-04 · docs/governance/DECISION-LOG.md D-070 |  |  | NOT STARTED | NO |
| `CAMP-002` | مساحة مخصصة للحملات والفعاليات ضمن الـDesign System — ليست Banner عشوائية | M01-126, M01-128 |  |  |  |  | NOT STARTED | NO |
| `CAMP-003` | العروض والخصومات والفعاليات والحملات تُتحقق مع الـOwner قبل النشر | M01-048 | D-003, MI-015 | docs/phase-01-discovery/04-content-approval-register.md MI-015 · docs/phase-01-discovery/07-question-backlog.md R4-06, R4-07, R4-10 |  |  | NOT STARTED | NO |
| `CAMP-004` | Event / Campaign Manager في الـDashboard — الحقول | M01-129, M01-130, M25-094 |  |  |  |  | NOT STARTED | NO |
| `CAMP-005` | دورة حياة الحملة: Draft · Scheduled · Active · Expired · Archived | M25-095 | D-148 |  |  |  | NOT STARTED | NO |
| `CAMP-006` | الجدولة التلقائية: ظهور واختفاء تلقائي ثم Archive | M01-131, M01-132, M01-133, M25-096 | D-148, F-17 |  |  |  | NOT STARTED | NO |
| `CAMP-007` | Emergency Announcement سريع | M01-134, M01-135 | D-021 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-07 · docs/phase-01-discovery/07-question-backlog.md R4-08 |  |  | NOT STARTED | NO |
| `ABOUT-001` | صفحة «من نحن» معتمدة | M03-040 | D-015 | docs/phase-01-discovery/10-url-architecture-draft.md §3 |  |  | NOT STARTED | NO |
| `ABOUT-002` | معلومات الهوية تُتحقق مع الـOwner قبل النشر | M01-044 | D-003, D-007, D-038 | docs/phase-01-discovery/04-content-approval-register.md MI-002, MI-003, MI-004, MI-006 |  |  | NOT STARTED | NO |
| `ABOUT-003` | سنة التأسيس: 2019 | M03-011, M04-001 | D-018, D-009, D-038 | docs/governance/DECISION-LOG.md D-018 |  |  | NOT STARTED | N/A |
| `ABOUT-004` | تاريخ السنوية 20/04 واستخداماته | M03-012, M04-002, M04-004 | D-018 | docs/governance/DECISION-LOG.md D-018 |  |  | NOT STARTED | N/A |
| `CONTACT-001` | بوابة نشر بيانات التواصل: الهاتف وواتساب والبريد والحسابات | M01-046 | D-003, D-017, D-057, D-058, D-035, D-036 | docs/phase-01-discovery/04-content-approval-register.md · docs/phase-01-discovery/11-social-accounts-verification.md |  |  | PARTIAL | N/A |
| `CONTACT-002` | صفحة التواصل (Contact) معتمدة | M03-043 | D-015, D-031, D-059 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §4 · docs/phase-01-discovery/10-url-architecture-draft.md §3، URL-06 |  |  | NOT STARTED | NO |
| `CONTACT-003` | لا رقم في أي مكان قبل الاعتماد الصريح من الـOwner، ولا وظيفة رقم من مصادر خارجية | M13-021, M13-022 | D-057 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §1 |  |  | NOT STARTED | N/A |
| `CONTACT-004` | التوزيع الرسمي للأرقام (R2P-04 OFFICIAL MAPPING) | M13-035, M04-018, M04-033, M04-035, M10-058 | D-057, D-022, D-058 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §1 |  |  | NOT STARTED | N/A |
| `CONTACT-005` | 0799009436 = الرقم العام الرئيسي لكل الفروع واستخداماته المسموحة | M13-023, M13-025 | D-057, D-060 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §1، §2 |  |  | NOT STARTED | N/A |
| `CONTACT-006` | 0799009436 = رقم WhatsApp الرسمي | M13-024, M04-036 | D-058, D-023 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §5.4 |  |  | NOT STARTED | N/A |
| `CONTACT-007` | 0799338445 = الشكاوى والاقتراحات والفرنشايز — أماكن استخدامه فقط | M13-027, M13-030, M04-034 | D-057, D-071 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §2، §4 |  |  | NOT STARTED | N/A |
| `CONTACT-008` | 0799338445 ليس رقمًا عامًا للفروع ولا يظهر أبدًا في Branch Cards | M13-029 | D-057, D-060 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §2 |  |  | NOT STARTED | N/A |
| `CONTACT-009` | أي اقتراح مستقبلي لفصل وظائف 0799338445 يُعرض على الـOwner أولًا | M13-031 | D-057 |  |  |  | NOT STARTED | N/A |
| `CONTACT-010` | 0799530383 = الكيترنج والأعمال والفعاليات — ليس رقمًا عامًا | M13-032, M13-034 | D-057, D-059, D-069, D-070 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §1، §2 |  |  | NOT STARTED | N/A |
| `CONTACT-011` | الحجوزات: لا رقم منفصل؛ أي Reservation flow يُسأل عنه أولًا | M13-036 | D-057, D-015 |  |  |  | NOT STARTED | N/A |
| `CONTACT-012` | الرعايات: لا رقم ولا قناة تلقائيًا — MISSING | M14-037 | D-072 |  |  |  | NOT STARTED | N/A |
| `CONTACT-013` | Contact Architecture حسب نية المستخدم (Intent-based) — لا 3 أرقام بلا سياق | M13-037, M14-039, M14-040 | D-059, D-071 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §1، §4 |  |  | NOT STARTED | NO |
| `CONTACT-014` | مقترح UX لعرض قنوات التواصل في كل الأسطح (وثيقة 14) | M13-038 | D-059, D-061, D-062, D-063, D-064, D-065 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §2، §6 |  |  | PARTIAL | N/A |
| `CONTACT-015` | الـFooter غير مزدحم بقنوات التواصل | M13-039 | D-059, D-062 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §2 |  |  | NOT STARTED | NO |
| `CONTACT-016` | WhatsApp CTA مناسب للموبايل | M13-041 | D-058, D-063 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §5 |  |  | NOT STARTED | NO |
| `CONTACT-017` | أماكن واتساب الأربعة فقط: Branch Card · Branch Page · Contact Page · Footer | M14-009, M13-042 | D-062, D-058 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §5.1 |  |  | NOT STARTED | NO |
| `CONTACT-018` | ممنوع Floating WhatsApp Bubble ثابتة على كل الصفحات | M14-010 | D-062 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §5.1 |  |  | NOT STARTED | N/A |
| `CONTACT-019` | رسالة واتساب مسبقة حسب الفرع (W-2) — النص النهائي بانتظار الاعتماد | M14-014, M13-043 | D-064, D-058 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §5.3 |  |  | NOT STARTED | N/A |
| `CONTACT-020` | صيغة عرض الرقم: عربي 0799009436 · إنجليزي +962 79 900 9436 | M14-018 | D-065 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §6 |  |  | NOT STARTED | N/A |
| `CONTACT-021` | روابط tel: وWhatsApp والـSchema بالصيغة الدولية الصحيحة | M14-019 | D-065, D-060 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §5.4 |  |  | NOT STARTED | N/A |
| `CONTACT-022` | واتساب والهاتف ليسا قناة طلب رسمية — MISSING | M14-046 | D-073 |  |  |  | NOT STARTED | N/A |
| `CONTACT-023` | البريد info@shelterjo.com = PENDING OWNER VERIFICATION — لا يُنشر بعد | M04-037, M10-021 | D-024, D-035 | docs/phase-01-discovery/03-verify-with-owner.md VQ-25 · docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md §4 |  |  | NOT STARTED | N/A |
| `CONTACT-024` | دعم مستقبلي لبريد منفصل: Careers · Franchise · Business inquiries | M04-038 | D-024 |  |  |  | NOT STARTED | NO |
| `CONTACT-025` | ممنوع إنشاء أو نشر أي بريد غير موجود فعليًا | M04-039 | D-024 |  |  |  | NOT STARTED | N/A |
| `CONTACT-026` | الحسابات الاجتماعية: لا اعتماد لأي حساب حتى الآن ولا اعتماد تلقائي من البحث | M04-040, M10-022 | D-025, D-036 | docs/phase-01-discovery/11-social-accounts-verification.md |  |  | NOT STARTED | N/A |
| `CONTACT-027` | جدول التحقق من الحسابات الاجتماعية وعرضها واحدًا واحدًا | M04-041, M10-023 | D-025, D-036 | docs/phase-01-discovery/11-social-accounts-verification.md |  |  | PARTIAL | N/A |
| `CONTACT-028` | youtube.com/@sheltercoffee لا يُعتبر رسميًا حتى يؤكده الـOwner | M04-042 | D-025 | docs/phase-01-discovery/11-social-accounts-verification.md |  |  | NOT STARTED | N/A |
| `CONTACT-029` | لا افتراض لقرار تغيير اسم Instagram أو أي Handle قبل النقاش | M04-043 | D-025 |  |  |  | NOT STARTED | N/A |
| `CONTACT-030` | Snapchat Place ليس حسابًا اجتماعيًا رسميًا | M10-024 | D-036 | docs/phase-01-discovery/11-social-accounts-verification.md |  |  | NOT STARTED | N/A |
| `BLOG-001` | قسم BLOG / Coffee Knowledge Hub معتمد | M01-136, M03-041 | D-015, DB-09, URL-03 | docs/phase-01-discovery/05-decisions-before-design.md DB-09 · docs/phase-01-discovery/10-url-architecture-draft.md §3, §6 URL-03 · docs/phase-01-discovery/07-question-backlog.md R8-04 |  |  | NOT STARTED | NO |
| `BLOG-002` | Knowledge Hub مترابط وليس Posts عشوائية | M01-139, M01-140 |  | docs/phase-01-discovery/10-url-architecture-draft.md §3 |  |  | NOT STARTED | NO |
| `BLOG-003` | مواضيع المدونة وبناء Topic Authority | M01-137, M11-102 | GEP-§47 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §47 · docs/phase-01-discovery/07-question-backlog.md R8-03, R8-08 |  |  | NOT STARTED | NO |
| `BLOG-004` | لا اختراع للمحتوى — فقط محتوى صحيح ومعتمد | M01-138, M11-103 | D-003, GEP-§47 | docs/phase-01-discovery/04-content-approval-register.md MI-020 · docs/phase-01-discovery/07-question-backlog.md R8-01, R8-06 |  |  | NOT STARTED | NO |
| `BLOG-005` | حزمة اعتماد المقال قبل النشر — AI لا ينشر من نفسه | M01-142, M25-098 | D-003 |  |  |  | NOT STARTED | NO |
| `BLOG-006` | Content Editor للمقالات — الحقول | M25-097 |  | docs/phase-01-discovery/07-question-backlog.md R8-05 |  |  | NOT STARTED | NO |
| `FRAN-001` | صفحة Franchise الكاملة: قدرة مستقبلية — غير منشورة حتى اعتماد المحتوى | M03-051, M14-035 | D-015, D-071, D-010 | docs/phase-01-discovery/10-url-architecture-draft.md §3, §5 · docs/governance/DECISION-LOG.md D-071 |  |  | NOT STARTED | NO |
| `FRAN-002` | خيار «Franchise Inquiries / استفسارات الفرنشايز» في صفحة التواصل من يوم الإطلاق | M14-034 | D-071, D-057, D-059 | docs/governance/DECISION-LOG.md D-071, D-057, D-059 |  |  | NOT STARTED | NO |
| `FRAN-003` | ممنوع نشر رسوم أو شروط أو ادعاءات Franchise من عندنا | M03-052, M14-036 | D-015, D-071 |  |  |  | NOT STARTED | NO |
| `CMS-001` | 95%+ من العمليات اليومية يديرها الـOwner من الـDashboard بدون لمس الكود | M01-112, M25-079, M25-207, M27-050 | D-084, DB-08 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §18 (CMS — SSOT في V1) · docs/phase-01-discovery/05-decisions-before-design.md DB-08 |  |  | NOT STARTED | NO |
| `CMS-002` | نطاق كيانات الـCMS (القائمة الموحدة القابلة للتعديل من UI) | M01-113, M01-114, M01-115, M01-116, M25-080, M25-209, M27-051, M27-052, M27-053, M27-054 | D-084, D-148, D-021 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §18 (CMS — SSOT في V1) · docs/phase-01-discovery/17-menu-data-model-draft.md (v0.4) |  |  | NOT STARTED | NO |
| `CMS-003` | Menu CMS = Single Source of Truth في V1 (A الآن + جاهزية C) — لا Google Sheet | M15-048, M15-050, M23-202 | D-085, DB-21, D-142, D-148 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §18 (CMS — SSOT في V1) · docs/phase-01-discovery/17-menu-data-model-draft.md (v0.4) · docs/phase-01-discovery/16-menu-intake-and-ssot.md |  |  | NOT STARTED | NO |
| `CMS-004` | مصدر واحد للحقيقة: لا CMS مزدوج ولا مصدر منيو مزدوج | M27-143 | D-085 | docs/phase-01-discovery/05-decisions-before-design.md DB-08 |  |  | NOT STARTED | NO |
| `CMS-005` | إدارة المنيو بعد الإطلاق من الـDashboard | M15-045 | D-084, D-148 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §18 (CMS — SSOT في V1) · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §19 (Data Model v0.5 delta) |  |  | NOT STARTED | NO |
| `CMS-006` | Workflow لكل المحتوى: DRAFT · IN REVIEW · SCHEDULED · PUBLISHED · ARCHIVED | M01-120, M23-203, M25-109, M23-204 | D-148, D-084 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §18 (CMS — SSOT في V1) · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §19 (Data Model v0.5 delta) |  |  | NOT STARTED | NO |
| `CMS-007` | لا يصل أي تعديل للعميل قبل Publish — لا تعديل مباشر على Production | M23-205 | D-148 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §18 (CMS — SSOT في V1) |  |  | NOT STARTED | NO |
| `CMS-008` | Preview قبل Publish: Desktop · Mobile (+ Arabic · English إذا أمكن) | M01-119, M25-116 | D-148 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §18 (CMS — SSOT في V1) |  |  | NOT STARTED | NO |
| `CMS-009` | Scheduling والنشر الموسمي: تفعيل/انتهاء تلقائي · تجاوز يدوي · Audit trail | M23-095 | D-148, F-17, D-117 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §19 (Data Model v0.5 delta) · docs/menu-ia/MENU-DECISION-REGISTER.md F-17 |  |  | NOT STARTED | NO |
| `CMS-010` | Page Editor بسيط مبني على Sections/Blocks (ليس Page Builder مثل Elementor) | M25-081, M25-083 | DB-08 | docs/phase-01-discovery/05-decisions-before-design.md DB-08 |  |  | NOT STARTED | NO |
| `CMS-011` | Design Lock: الـCMS لا يسمح بكسر الـDesign System | M01-117, M01-118, M27-055 |  | design-system/README.md |  |  | NOT STARTED | NO |
| `CMS-012` | Global Components تُدار من مكان واحد | M01-220 |  |  |  |  | NOT STARTED | NO |
| `CMS-013` | نظام Special Hours في الـCMS (Regular · Special/Holiday · Closure/Emergency) | M04-028, M04-031 | D-021, DB-18 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-07 (نموذج الساعات الخاصة — للنقاش) · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.4 · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §19 (Data Model v0.5 delta) |  | docs/menu-ia/evidence/hours_logic_check.py | NOT STARTED | NO |
| `CMS-014` | مناقشة UX والـCMS لنظام الساعات الخاصة مع الـOwner قبل التنفيذ | M04-032 | D-021, DB-18 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-07 (نموذج الساعات الخاصة — للنقاش) |  |  | NOT STARTED | N/A |
| `CMS-015` | التوفر لكل Product × Branch: Available · Unavailable + Show · Unavailable + Hide | M23-114 | D-145, F-19, D-079 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §19 (Data Model v0.5 delta) · docs/menu-ia/MENU-DECISION-REGISTER.md F-19 · CF-06 |  |  | NOT STARTED | NO |
| `CMS-016` | ترتيب المنتجات بـsort_order يدوي من الـCMS — ممنوع Random Algorithm | M23-206 | D-148 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §18 (CMS — SSOT في V1) |  |  | NOT STARTED | NO |
| `CMS-017` | FEATURED = خاصية CMS داخلية — لا Badge 'Featured' للعميل | M23-070, M23-207 | F-14, D-144, D-148 | docs/menu-ia/MENU-DECISION-REGISTER.md F-14 · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §19 (Data Model v0.5 delta) |  |  | NOT STARTED | NO |
| `CMS-018` | Search Alias Dictionary قابل للإدارة من الـCMS | M23-079 | D-143, F-15 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §19 (Data Model v0.5 delta) · docs/menu-ia/MENU-DECISION-REGISTER.md F-15 · P-06 |  |  | NOT STARTED | NO |
| `CMS-019` | حالة المحتوى في الـDashboard: صفحات تحتاج تحديث + Drafts تنتظر النشر | M25-011 |  |  |  |  | NOT STARTED | NO |
| `CMS-020` | لا تعديل يدوي لقاعدة البيانات: كل Business Content اليومي له UI | M25-165, M25-166 | DB-08 | docs/phase-01-discovery/05-decisions-before-design.md DB-08 |  |  | NOT STARTED | NO |
| `CMS-021` | CMS Data Model والتخزين (CMS-DATA-MODEL.md) — Supabase مشروط بقرار DB-08 | M25-135 | DB-08, D-107, D-135, D-136 | docs/phase-01-discovery/17-menu-data-model-draft.md (v0.4) · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §19 (Data Model v0.5 delta) · docs/phase-01-discovery/05-decisions-before-design.md DB-08 |  |  | PARTIAL | N/A |
| `DASH-001` | Owner Dashboard = SHELTER Website Control Center (جزء أساسي من المشروع) | M25-001, M25-002, M25-003, M27-040, M12-071 | GIO-§31, DB-08, D-084, D-148 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §31 (اقتراح قديم — تجاوزه M25) · docs/phase-01-discovery/05-decisions-before-design.md DB-08 · docs/FRONTEND-TOOLING.md (مكونات الـDashboard لاحقًا: KPI Card · Chart · Table↔Cards · Alert · Editor forms) |  |  | NOT STARTED | NO |
| `DASH-002` | مبدأ الـDashboard: فهم خلال ثوانٍ + WHAT/WHERE/WHY/ATTENTION + Action | M25-004, M25-012, M25-205 |  | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §31 (اقتراح قديم — تجاوزه M25) |  |  | NOT STARTED | NO |
| `DASH-003` | أولويات الـDashboard لـVersion 1 (P0 / P1 / P2 حسب M25 §68) | M25-189, M25-190, M25-191 |  | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §31 (اقتراح قديم — تجاوزه M25) |  |  | NOT STARTED | N/A |
| `DASH-004` | تسلسل تنفيذ الـDashboard (Phases A–N) — لا بناء قبل Gate H | M25-178 | DB-08 | docs/phase-01-discovery/05-decisions-before-design.md DB-08 |  |  | NOT STARTED | NO |
| `DASH-005` | Low-Fidelity Wireframes للـDashboard قبل الـCoding (12 شاشة) | M25-176 |  | docs/qa/RESPONSIVE-QA-MATRIX.md (أعمدة Owner Dashboard · Menu Editor · Product Editor · Analytics · Site Health — غير مبنية) · docs/menu-ia/wireframes/README.md (نموذج الأسلوب) |  |  | NOT STARTED | NO |
| `DASH-006` | وحدات الـDashboard والتنقل — القائمة الدنيا الكاملة (M25 §49 ∪ M27 §13) | M25-143, M25-144, M27-041, M27-047, M27-048, M27-049 |  | docs/FRONTEND-TOOLING.md (مكونات الـDashboard لاحقًا: KPI Card · Chart · Table↔Cards · Alert · Editor forms) · docs/qa/RESPONSIVE-QA-MATRIX.md (أعمدة Owner Dashboard · Menu Editor · Product Editor · Analytics · Site Health — غير مبنية) |  |  | NOT STARTED | NO |
| `DASH-007` | الشاشة الأولى بعد Login = Executive Dashboard (Owner KPIs فقط) | M25-018, M25-019, M25-175 |  | docs/FRONTEND-TOOLING.md (مكونات الـDashboard لاحقًا: KPI Card · Chart · Table↔Cards · Alert · Editor forms) |  |  | NOT STARTED | NO |
| `DASH-008` | TOP SUMMARY: 10 مؤشرات + مقارنة + percentage/trend arrow/mini chart | M25-020, M25-021, M25-022 | D-149 | docs/FRONTEND-TOOLING.md (مكونات الـDashboard لاحقًا: KPI Card · Chart · Table↔Cards · Alert · Editor forms) |  |  | NOT STARTED | NO |
| `DASH-009` | Date Selector عام أعلى الـDashboard مع Presets | M25-025, M25-026, M25-028 |  |  |  |  | NOT STARTED | NO |
| `DASH-010` | Comparison: previous period / previous year — فقط إذا البيانات متوفرة | M25-027 |  |  |  |  | NOT STARTED | NO |
| `DASH-011` | وحدات Analytics في الـDashboard (حد أدنى) | M27-042, M12-072 | D-149 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §31 (اقتراح قديم — تجاوزه M25) · docs/google/GA4-MEASUREMENT-PLAN.md (DRAFT) · docs/menu-ia/MENU-MEASUREMENT-PLAN.md |  |  | NOT STARTED | NO |
| `DASH-012` | وحدات Click / Customer Actions: WhatsApp · Phone · Directions · Branch | M27-043 | D-149 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md |  |  | NOT STARTED | NO |
| `DASH-013` | وحدات SEO: Search Console · SEO Health · Indexation | M27-044 |  | docs/google/SEARCH-CONSOLE-BASELINE.md |  |  | NOT STARTED | NO |
| `DASH-014` | وحدات الأداء والصحة: CWV · Lighthouse · Site Health · Errors · Accessibility | M27-045 |  | docs/menu-ia/PERFORMANCE-BUDGET.md · docs/menu-ia/ACCESSIBILITY-CHECKLIST.md | tooling/scripts/lighthouse.mjs · tooling/playwright.config.mjs |  | NOT STARTED | NO |
| `DASH-015` | وحدة Media Health (صحة الصور والوسائط) | M27-046 | D-083 |  | tooling/scripts/images.mjs |  | NOT STARTED | NO |
| `DASH-016` | Page Health View لكل صفحة | M25-078 | D-031, D-054 |  |  |  | NOT STARTED | NO |
| `DASH-017` | Needs Attention (Command Center) أعلى الـOverview — CRITICAL/WARNING/INFO | M25-145, M25-146 |  |  |  |  | NOT STARTED | NO |
| `DASH-018` | كل Metric أو Alert قابل للتنفيذ عبر Deep Link | M25-147 |  |  |  |  | NOT STARTED | NO |
| `DASH-019` | Global Search داخل الـDashboard عبر كل الكيانات | M25-117 |  |  |  |  | NOT STARTED | NO |
| `DASH-020` | لغة سهلة للـOwner + Advanced Details للمطور | M25-149 |  |  |  |  | NOT STARTED | NO |
| `DASH-021` | مصادر بيانات الـDashboard: APIs رسمية فقط · لا APIs ثقيلة أو مدفوعة بدون موافقة | M12-073 | D-029, D-044, D-046, D-051, GIO-§A3/§A4, GIO-§31 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §31 (اقتراح قديم — تجاوزه M25) |  |  | NOT STARTED | NO |
| `DASH-022` | Data freshness indicators + زر Refresh (مع Caching من جهة الخادم) | M25-134 |  |  |  |  | NOT STARTED | NO |
| `DASH-023` | Graceful degradation: الـDashboard لا تنهار إذا توقفت خدمة خارجية | M25-196 |  |  |  |  | NOT STARTED | NO |
| `DASH-024` | Labels واضحة: DEMO DATA مقابل LIVE DATA — ممنوع الخلط | M25-179 |  |  |  |  | NOT STARTED | NO |
| `DASH-025` | لا أرقام مزيفة: أي Score يوضح طريقة حسابه | M25-121 |  |  |  |  | NOT STARTED | NO |
| `DASH-026` | Summary Health Score اختياري — فقط بقواعد واضحة؛ الـIssues أهم من الرقم | M25-120, M25-122 |  |  |  |  | NOT STARTED | NO |
| `DASH-027` | الـOwner Dashboard كاملة الوظائف على Mobile و Desktop | M26-028 |  | docs/qa/RESPONSIVE-QA-MATRIX.md (أعمدة Owner Dashboard · Menu Editor · Product Editor · Analytics · Site Health — غير مبنية) | tooling/viewports.mjs |  | NOT STARTED | NO |
| `DASH-028` | 9 مهام Owner يجب أن تكون سهلة على الهاتف | M26-029, M26-030, M26-031, M26-032, M26-033, M26-034, M26-035, M26-036, M26-037 |  | docs/qa/RESPONSIVE-QA-MATRIX.md (أعمدة Owner Dashboard · Menu Editor · Product Editor · Analytics · Site Health — غير مبنية) | tooling/viewports.mjs |  | NOT STARTED | NO |
| `DASH-029` | Dashboard cards لا تنضغط بشكل سيئ على الهاتف · Desktop أكثر كثافة عند الفائدة | M26-014, M26-038 |  | docs/qa/RESPONSIVE-QA-MATRIX.md (أعمدة Owner Dashboard · Menu Editor · Product Editor · Analytics · Site Health — غير مبنية) |  |  | NOT STARTED | NO |
| `DASH-030` | Charts متجاوبة — وتتحول إلى Summary/Card على الشاشات الصغيرة عند الحاجة | M26-041, M26-042 |  | docs/FRONTEND-TOOLING.md (مكونات الـDashboard لاحقًا: KPI Card · Chart · Table↔Cards · Alert · Editor forms) |  |  | NOT STARTED | NO |
| `DASH-031` | AI Assistant داخل الـDashboard — مستقبلي (Architecture جاهزة فقط إن لم تعقّد) | M25-150, M25-152 |  |  |  |  | NOT STARTED | N/A |
| `ANL-001` | جرد التتبع والتكاملات الحالية في الموقع القديم | M01-036 | GC-06, D-041 | docs/phase-01-discovery/01-current-website-inventory.md · docs/phase-01-discovery/12-homepage-screenshots-audit.md §7 · docs/google/GTM-TAG-REGISTER.md |  |  | PARTIAL | NO |
| `ANL-002` | فحص Duplicate Tracking في الموقع القديم والجديد | M11-072, M12-012 | GEP-§31, D-050, GIO-§A3/§A4 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §31 · docs/google/POST-LAUNCH-GOOGLE-CHECKLIST.md |  |  | NOT STARTED | NO |
| `ANL-003` | مصدر واحد لكل آلية تتبع — نظام Analytics واحد بلا GA4/GTM مكرر | M12-013, M25-127, M27-075, M27-142 | D-050, D-051, GEP-§31 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A4 §A33 |  |  | NOT STARTED | NO |
| `ANL-004` | لا حذف لأي Duplicate (Tag/Property/Tracking) بدون موافقة الـOwner | M12-076 | D-051, GIO-§A3/§A4 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A33 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `ANL-005` | Measurement Plan للموقع قبل تنفيذ أي Tracking | M01-209, M11-062 | D-050, GEP-§25/§27, GC-13 | docs/google/GA4-MEASUREMENT-PLAN.md · docs/google/GOOGLE-ECOSYSTEM-CHECKLIST.md GC-13 |  |  | PARTIAL | NO |
| `ANL-006` | تتبع ذو معنى تجاري فقط — لا Tracking بلا داعٍ ولا كل Click | M01-212, M11-061, M12-031, M25-042, M27-092 | GEP-§25/§27, GIO-§A9/§A10, D-050 | docs/google/GA4-MEASUREMENT-PLAN.md · docs/menu-ia/MENU-MEASUREMENT-PLAN.md §6 |  |  | NOT STARTED | NO |
| `ANL-007` | توثيق كل Event بحقول ثابتة | M01-211, M11-065, M23-196 | GEP-§25/§27 | docs/google/GA4-MEASUREMENT-PLAN.md · docs/menu-ia/MENU-MEASUREMENT-PLAN.md |  |  | PARTIAL | NO |
| `ANL-008` | Event Taxonomy موحدة قبل التنفيذ — القائمة الـcanonical للأحداث | M01-210, M12-028, M12-029, M12-030, M25-041, M27-077 | D-149, GIO-§A9/§A10, GEP-§26, CF-09 | docs/google/GA4-MEASUREMENT-PLAN.md · docs/google/GOOGLE-ECOSYSTEM-POLICY.md §26 · docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A9 · docs/menu-ia/MENU-MEASUREMENT-PLAN.md |  |  | NEEDS FIX | NO |
| `ANL-009` | الأسماء النهائية للأحداث تُعرض على الـOwner قبل الاعتماد | M11-064 | GEP-§25/§27 | docs/menu-ia/README.md §16 |  |  | PARTIAL | NO |
| `ANL-010` | قاعدة إعادة التسمية التقنية: نفس المعنى + توثيق أي تعديل | M23-198 | D-149 | docs/menu-ia/MENU-MEASUREMENT-PLAN.md §2 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `ANL-011` | Menu Measurement Plan (Phase I) — أحداث المنيو الستة | M23-194, M23-195, M23-250 | D-149, CF-09, CF-10 | docs/menu-ia/MENU-MEASUREMENT-PLAN.md · docs/menu-ia/README.md §16 · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §14 |  | tooling/tests/app/menu.spec.mjs | IMPLEMENTED — NOT TESTED | N/A |
| `ANL-012` | حدث `menu_view` | M27-078 | D-149 | docs/google/GA4-MEASUREMENT-PLAN.md · docs/menu-ia/MENU-MEASUREMENT-PLAN.md |  | tooling/tests/app/menu.spec.mjs (F-01 'menu_view fired once' — test.fixme) | NOT STARTED | NO |
| `ANL-013` | حدث `menu_search` | M27-079 | D-149, CF-09, CF-10 | docs/google/GA4-MEASUREMENT-PLAN.md · docs/menu-ia/MENU-MEASUREMENT-PLAN.md |  |  | NOT STARTED | NO |
| `ANL-014` | حدث `zero_result_search` | M27-091, M23-082 | D-149 | docs/google/GA4-MEASUREMENT-PLAN.md · docs/menu-ia/MENU-MEASUREMENT-PLAN.md |  | tooling/tests/app/menu.spec.mjs (F-13 'zero_result_search fired with sanitized term' — test.fixme) | NOT STARTED | NO |
| `ANL-015` | حدث `menu_category_click` | M27-080 | D-149 | docs/google/GA4-MEASUREMENT-PLAN.md · docs/menu-ia/MENU-MEASUREMENT-PLAN.md |  |  | NOT STARTED | NO |
| `ANL-016` | حدث `product_view` | M27-081 | D-149, D-146, D-147 | docs/google/GA4-MEASUREMENT-PLAN.md · docs/menu-ia/MENU-MEASUREMENT-PLAN.md |  |  | NOT STARTED | NO |
| `ANL-017` | حدث `branch_filter_change` | M27-082 | D-149, D-145 | docs/google/GA4-MEASUREMENT-PLAN.md · docs/menu-ia/MENU-MEASUREMENT-PLAN.md |  |  | NOT STARTED | NO |
| `ANL-018` | حدث `directions_click` | M27-083, M11-076, M11-077 | GEP-§33/§34 (G4), D-013 | docs/google/GA4-MEASUREMENT-PLAN.md |  |  | NOT STARTED | NO |
| `ANL-019` | حدث `phone_click` | M27-084 | D-057, D-059 | docs/google/GA4-MEASUREMENT-PLAN.md |  |  | NOT STARTED | NO |
| `ANL-020` | حدث `whatsapp_click` | M27-085 | D-058, D-061, D-062 | docs/google/GA4-MEASUREMENT-PLAN.md |  |  | NOT STARTED | NO |
| `ANL-021` | حدث `campaign_view` | M27-086 | D-070 | docs/google/GA4-MEASUREMENT-PLAN.md |  |  | NOT STARTED | NO |
| `ANL-022` | حدث `campaign_click` | M27-087 |  | docs/google/GA4-MEASUREMENT-PLAN.md |  |  | NOT STARTED | NO |
| `ANL-023` | حدث `article_view` | M27-088 |  | docs/google/GA4-MEASUREMENT-PLAN.md |  |  | NOT STARTED | NO |
| `ANL-024` | حدث `language_switch` | M27-089 | D-014 | docs/google/GA4-MEASUREMENT-PLAN.md |  |  | NOT STARTED | NO |
| `ANL-025` | حدث `site_search` | M27-090 |  | docs/google/GA4-MEASUREMENT-PLAN.md |  |  | NOT STARTED | NO |
| `ANL-026` | أحداث مرشحة بانتظار قرار: branch_view · social_click · event_view | M11-063 | GIO-§A9/§A10, GEP-§26, D-036 | docs/google/GA4-MEASUREMENT-PLAN.md · docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A9 |  |  | NOT STARTED | NO |
| `ANL-027` | Search Analytics: ما يجب معرفته من البحث | M23-200 | D-149, CF-10 | docs/menu-ia/MENU-MEASUREMENT-PLAN.md §2 §3 |  |  | NOT STARTED | NO |
| `ANL-028` | قاموس Event Parameters موحد بتسمية ثابتة | M12-032 | GIO-§A9/§A10 | docs/google/GA4-MEASUREMENT-PLAN.md §معجم · docs/menu-ia/MENU-MEASUREMENT-PLAN.md §2 §5 |  |  | NEEDS FIX | NO |
| `ANL-029` | استخدام بيانات GA4 الفعلية بدل التخمين — دون أن تقرر التصميم وحدها | M11-110, M11-111 | GEP-§50/§52 (G8), D-042 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §52 |  |  | NOT STARTED | NO |
| `ANL-030` | لا Business Conclusions غير مدعومة بالبيانات | M25-206 |  |  |  |  | NOT STARTED | NO |
| `ANL-031` | الـDashboard تجيب أسئلة الـOwner التحليلية (محتوى · أفعال · جمهور) | M25-005, M25-006, M25-007 |  |  |  | tooling/scripts/qa-matrix.mjs (صفحة Analytics ضمن مصفوفة QA) | NOT STARTED | NO |
| `ANL-032` | Traffic Analytics | M25-031 |  |  |  |  | NOT STARTED | NO |
| `ANL-033` | Traffic Sources: القنوات + Source/Medium + جودة القناة | M25-033, M25-034, M25-035 |  |  |  |  | NOT STARTED | NO |
| `ANL-034` | Top Pages مع Drill-down | M25-036, M25-037 |  |  |  |  | NOT STARTED | NO |
| `ANL-035` | Menu Analytics Dashboard (مهمة جدًا لـSHELTER) | M25-038, M25-039 | D-149 |  |  |  | NOT STARTED | NO |
| `ANL-036` | Click / Event Analytics Dashboard | M25-043 |  |  |  |  | NOT STARTED | NO |
| `ANL-037` | Customer Actions (CTA Analytics) حسب الفرع | M25-044, M25-045 |  |  |  |  | NOT STARTED | NO |
| `ANL-038` | Device / UX Data | M25-046 |  |  |  |  | NOT STARTED | NO |
| `ANL-039` | Location Analytics فقط إذا Privacy-safe | M25-048 |  |  |  |  | NOT STARTED | NO |
| `ANL-040` | Real-Time Overview إذا سمح GA4/API | M25-029 |  |  |  |  | NOT STARTED | NO |
| `ANL-041` | عرض ما يفيد القرار فقط (Charts بمعنى، لا نسخ لكل GA4) | M25-032, M25-047, M25-129, M25-173 |  |  |  |  | NOT STARTED | NO |
| `ANL-042` | Dashboard SHELTER حقيقية من APIs معتمدة — ليست iframe لـGA | M25-128 |  |  |  |  | NOT STARTED | NO |
| `ANL-043` | تصنيف KPIs: OWNER · MARKETING · SEO · UX · TECHNICAL | M25-174 |  |  |  |  | NOT STARTED | NO |
| `ANL-044` | Data Source Matrix | M25-171, M25-172 |  |  |  |  | NOT STARTED | NO |
| `ANL-045` | مصادر بيانات الـAnalytics المفضلة | M25-125 |  |  |  |  | NOT STARTED | NO |
| `GOOGLE-001` | Google Ecosystem Policy إلزامية + نطاق منظومة Google | M11-004, M11-005, M11-006, M13-012 | D-046, GEP-§1 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §1 §57 · docs/google/GOOGLE-ECOSYSTEM-CHECKLIST.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `GOOGLE-002` | منظومة Google كنظام واحد مترابط + اتساق بيانات SHELTER | M12-006, M11-007 | GIO-§A1/§A2, GEP-§1, D-046, D-049 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A1 · docs/google/GOOGLE-INTEGRATION-ARCHITECTURE.md |  |  | PARTIAL | NO |
| `GOOGLE-003` | Google Measurement & Search Architecture + المعمارية المستهدفة | M12-007, M12-080 | GIO-§A1/§A2, D-050, D-051 | docs/google/GOOGLE-INTEGRATION-ARCHITECTURE.md §2 §3 · docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §C |  |  | PARTIAL | NO |
| `GOOGLE-004` | جاهزية الموقع لـGA4 وGTM وSearch Console (تكاملات مطلوبة لاحقًا) | M01-151, M01-208, M11-127 | D-050 | docs/google/GOOGLE-INTEGRATION-ARCHITECTURE.md §2 · docs/menu-ia/PERFORMANCE-BUDGET.md (GTM/GA4 بعد load + Idle) |  |  | PARTIAL | NO |
| `GOOGLE-005` | Claude ينفذ إعداد وربط Google بنفسه عند المرحلة والصلاحيات | M13-013, M25-180, M27-073 | D-051, D-055, GIO-§A1/§A2 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md |  |  | NOT STARTED | NO |
| `GOOGLE-006` | تدخل الـOwner فقط عند Login/OAuth/2FA/Ownership/Permission/Legal-financial | M25-181, M27-074 | D-051, GIO-§A1/§A2 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A2 §A29 |  |  | PARTIAL | NO |
| `GOOGLE-007` | وصول GA4/GTM للقراءة فقط لاحقًا (Read-only أولًا) | M10-047 | D-042, AC-05 | docs/phase-01-discovery/08-access-requests.md AC-05 · docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §B |  |  | NOT STARTED | NO |
| `GOOGLE-008` | ممنوع تغيير أي Google property/configuration في المرحلة الحالية | M11-129, M23-231 | D-046, D-149, GEP-§58 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §58 · docs/menu-ia/MENU-MEASUREMENT-PLAN.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `GOOGLE-009` | Audit الإعداد الموجود أولًا قبل إنشاء أي GA4/GTM/Integration | M12-010, M12-011, M12-081, M25-126, M27-076 | D-051, GIO-§A3/§A4, GC-06 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A3 §D · docs/google/GTM-TAG-REGISTER.md · docs/google/GOOGLE-ECOSYSTEM-CHECKLIST.md GC-06 |  |  | NOT STARTED | NO |
| `GOOGLE-010` | قرار طريقة نشر Google tag واستخدام GTM (GA-01) — Implementation واحد | M11-066, M11-075, M12-020, M12-021 | GIO-§A7/§A8, GEP-§32, GA-01, GC-14 | docs/google/GOOGLE-INTEGRATION-ARCHITECTURE.md §3 · docs/google/GOOGLE-ECOSYSTEM-POLICY.md §32 · docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A7 |  |  | PARTIAL | NO |
| `GOOGLE-011` | GTM ليس مستودع Scripts — كل Tag Documented/Named/Justified/Tested | M11-067, M11-068 | GEP-§28/§29/§30, D-050 | docs/google/GTM-TAG-REGISTER.md · docs/google/GOOGLE-ECOSYSTEM-POLICY.md §28 |  |  | NOT STARTED | NO |
| `GOOGLE-012` | GTM Naming Standard موحد قبل التنفيذ | M11-069, M12-016, M12-017, M12-018, M12-019 | GEP-§28/§29/§30, GIO-§A5/§A6/§A27 | docs/google/GTM-TAG-REGISTER.md · docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A6 |  |  | NEEDS FIX | NO |
| `GOOGLE-013` | إعداد GTM Container احترافي (إذا تقرر GTM) | M12-015 | GIO-§A5/§A6/§A27, D-051 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A5 · docs/menu-ia/MENU-MEASUREMENT-PLAN.md §5 |  |  | NOT STARTED | NO |
| `GOOGLE-014` | GTM Version Control بأسماء واضحة وNotes | M12-064, M12-065 | GIO-§A5/§A6/§A27 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A27 |  |  | NOT STARTED | NO |
| `GOOGLE-015` | اختبار Debug قبل نشر أي تغيير GA4/GTM — لا Event مرتين | M11-070, M11-071 | GEP-§28/§29/§30 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §30 · docs/menu-ia/MENU-MEASUREMENT-PLAN.md §6 |  | tooling/tests/app/menu.spec.mjs (F-01, F-13 — test.fixme) | NOT STARTED | NO |
| `GOOGLE-016` | إعداد GA4 Property كامل (إن لزم) ومراجعة كل الإعدادات الافتراضية | M12-022, M12-027 | GIO-§A7/§A8, D-051 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A8 §C (GA4-CONFIGURATION.md) |  |  | NOT STARTED | NO |
| `GOOGLE-017` | مراجعة Enhanced Measurement في GA4 | M12-023 | GIO-§A7/§A8 | docs/google/GA4-MEASUREMENT-PLAN.md · docs/menu-ia/MENU-MEASUREMENT-PLAN.md §5 |  |  | NOT STARTED | NO |
| `GOOGLE-018` | GA4 Internal + Developer traffic filtering | M12-024 | GIO-§A7/§A8 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A8 |  |  | NOT STARTED | NO |
| `GOOGLE-019` | GA4 Data retention + Referral exclusions عند الحاجة | M12-026 | GIO-§A7/§A8 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A8 |  |  | NOT STARTED | NO |
| `GOOGLE-020` | Cross-domain tracking — مستقبلًا فقط إذا احتجناه | M12-025 | GIO-§A7/§A8 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A8 |  |  | NOT STARTED | NO |
| `GOOGLE-021` | Key Events تُحدد مع الـOwner — ليس كل Click Conversion | M12-033, M12-034, M23-197 | D-149, GIO-§A9/§A10 | docs/google/GA4-MEASUREMENT-PLAN.md §Key Events · docs/menu-ia/MENU-MEASUREMENT-PLAN.md §1 |  |  | PARTIAL | NO |
| `GOOGLE-022` | Consent (Consent Mode v2) مدمج في معمارية GA4/GTM — بانتظار قرار الخصوصية |  | GIO-§A22/§A23 (G1), R11-04 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A22 §A23 · docs/menu-ia/MENU-MEASUREMENT-PLAN.md §3 §5 · docs/menu-ia/PERFORMANCE-BUDGET.md |  |  | CONFLICT | NO |
| `GOOGLE-023` | استخراج كل متطلبات Google السابقة (GA4/GTM/Tag/GSC/GBP/Maps/Schema/Sitemap) | M27-072 | D-046, D-051, D-055 |  |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `GSC-001` | إعداد وربط Search Console كاملًا — الفريق ينفذ | M01-150, M12-035, M13-014 | D-050, D-051, D-055 | docs/google/GOOGLE-INTEGRATION-ARCHITECTURE.md · docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md |  |  | NOT STARTED | N/A |
| `GSC-002` | GSC = مصدر الحقيقة لأداء Google Search — لا Semrush بدلًا منه | M11-029, M11-030, M11-031, M11-109, M10-046 | GEP-§12/§51, D-042, D-029 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §12، §51 · docs/google/SEARCH-CONSOLE-BASELINE.md |  |  | NOT STARTED | N/A |
| `GSC-003` | الوصول: مجاني أولًا · Read-only / Restricted · Exports من الـOwner | M04-053, M10-044, M10-045, M11-032 | D-028, D-042, GEP-§13 | docs/google/SEARCH-CONSOLE-BASELINE.md |  |  | NOT STARTED | N/A |
| `GSC-004` | لا تعديل في GSC (Sitemaps، Removals، Users، Ownership، Disavow) بلا موافقة | M11-033 | GEP-§13, D-051, DB-13 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §13 · docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `GSC-005` | Search Console Migration Baseline قبل الإطلاق — مدخل لخطة النقل | M11-034, M11-035, M11-036, M27-121 | GEP-§14, D-054 | docs/google/SEARCH-CONSOLE-BASELINE.md · docs/google/SEO-MIGRATION-MAP.md |  |  | NOT STARTED | N/A |
| `GSC-006` | دراسة GSC Property Architecture (Domain vs URL Prefix) | M12-036, M12-037 | D-051, GIO | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md |  |  | NOT STARTED | N/A |
| `GSC-007` | Verification بعد الموافقة — DNS TXT يُجهَّز ولا تغيير DNS بلا موافقة | M12-040 | D-051, GIO | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md |  |  | NOT STARTED | N/A |
| `GSC-008` | ربط GSC مع GA4 إذا كان مناسبًا | M12-041 | D-050 | docs/google/GOOGLE-INTEGRATION-ARCHITECTURE.md |  |  | NOT STARTED | N/A |
| `GSC-009` | فحص Google بعد الربط (Indexing، Coverage، Rich Results، CWV …) | M01-153 | GEP-§17/§19/§22, GEP-§41 | docs/google/POST-LAUNCH-GOOGLE-CHECKLIST.md |  |  | NOT STARTED | N/A |
| `GSC-010` | لا Finished إذا أظهر Google مشاكل مهمة | M01-154 |  |  |  |  | NOT STARTED | N/A |
| `GSC-011` | Dashboard ↔ Search Console API: Google Search Performance | M25-008, M25-050, M25-051 | D-050 |  |  |  | NOT STARTED | N/A |
| `GSC-012` | مقارنة الاستعلامات عبر الزمن (Clicks · Impressions · Position · Trend) | M25-052 |  |  |  |  | NOT STARTED | N/A |
| `GSC-013` | قسم Google Indexation — فقط ما يسمح به الـAPI | M25-063 |  |  |  |  | NOT STARTED | N/A |
| `GBP-001` | الموقع جاهز للتكامل مع Google Business Profile وGoogle Maps | M01-152 | D-046, D-050, D-051 | docs/google/GOOGLE-INTEGRATION-ARCHITECTURE.md · docs/google/GOOGLE-ECOSYSTEM-POLICY.md §1، §49 |  |  | NOT STARTED | NO |
| `GBP-002` | ملفان رسميان فقط على Google Business Profile: DRIVE وHOUSE | M11-012 | GEP-§3, D-048, D-008 | docs/google/GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md |  |  | NOT STARTED | N/A |
| `GBP-003` | GBP الرسمي لكل فرع = OFFICIAL OPERATIONAL SOURCE (تابع للـOwner) | M11-013, M13-018, M23-211, M11-124 | D-048, GEP-§3, D-047, D-056 | docs/google/GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md · docs/google/GOOGLE-ECOSYSTEM-POLICY.md §2، §3 |  |  | PARTIAL | N/A |
| `GBP-004` | GBP لا يتجاوز الـOwner: الاختلاف = CONFLICT — OWNER REVIEW REQUIRED | M11-016 | GEP-§5, D-048, D-047 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §5 · docs/google/BRANCH-DATA-SYNC.md |  |  | NOT STARTED | N/A |
| `GBP-005` | هاتف الفروع في GBP والـSchema = 0799009436؛ المختلف = CONFLICT | M13-026 | D-060, D-057 | docs/google/GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md |  |  | NOT STARTED | N/A |
| `GBP-006` | لا تُستخدم بيانات منتجات/منيو Google بدل مصدر المنيو الرسمي | M23-213 | D-076, D-089 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §9.5 |  |  | NOT STARTED | N/A |
| `GBP-007` | AC-03: صلاحية Google Business Profile = Read-only information فقط | M04-054 | D-028 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md |  |  | NOT STARTED | N/A |
| `GBP-008` | جمع بيانات GBP بالخيار المجاني وللتحقق فقط (لا Places API مدفوع) | M10-049, M10-051 | D-043, D-029, D-044 | docs/google/GOOGLE-INTEGRATION-ARCHITECTURE.md §6 |  |  | NOT STARTED | N/A |
| `GBP-009` | تعديل GBP: Proposal → Owner Approval → Change (ينفذه Claude) | M11-096, M11-097, M12-045 | GEP-§44, GIO-§A17/§A18/§A19, D-043, D-051 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §44 · docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §17 |  |  | NOT STARTED | N/A |
| `GBP-010` | الفريق ينفذ فعليًا اتساق Business Profile وMaps (وليس تعليمات فقط) | M13-016 | D-055, D-051, GIO-§A17/§A18/§A19 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md |  |  | NOT STARTED | N/A |
| `GBP-011` | رابط Maps الرسمي لكل فرع في صفحته — لا نفس الرابط للفرعين | M11-078, M12-046, M12-047 | GEP-§33/§34, GIO-§A17/§A18/§A19, D-013 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §34 · docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §18 |  |  | NOT STARTED | NO |
| `GBP-012` | لا Pin يدوي إذا كان GBP الرسمي موجودًا | M11-079 | GEP-§33/§34 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §34 |  |  | NOT STARTED | N/A |
| `GBP-013` | روابط Google Maps لـDRIVE وHOUSE = MISSING — VERIFY | M04-015, M04-025 | D-020 | docs/google/GOOGLE-BUSINESS-PROFILE-SOURCE-OF-TRUTH.md · docs/phase-01-discovery/03-verify-with-owner.md VQ-15 |  |  | NOT STARTED | N/A |
| `GBP-014` | قبل الإطلاق: ملفا GBP للفرعين Verified | M11-113 | D-046, GEP-§3 | docs/google/GOOGLE-ECOSYSTEM-CHECKLIST.md |  |  | NOT STARTED | N/A |
| `GBP-015` | Google Reviews تُقرأ كمرجع لفهم تجربة العملاء فقط | M11-094 | D-046 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §42 |  |  | NOT STARTED | N/A |
| `SEO-001` | SEO من اليوم الأول — الموقع القديم ليس مرجع SEO Architecture | M01-143, M01-008 | D-000, D-004 | docs/phase-01-discovery/10-url-architecture-draft.md · docs/phase-01-discovery/13-root-and-international-seo-plan.md · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §13 | tooling/scripts/lighthouse.mjs |  | NOT STARTED | PROTOTYPE |
| `SEO-002` | جاهزية International SEO و Local SEO | M01-075 | D-010, GEP-§10/§11, GEP-§20/§21 | docs/phase-01-discovery/13-root-and-international-seo-plan.md · docs/google/GOOGLE-ECOSYSTEM-POLICY.md §10، §20 |  |  | NOT STARTED | N/A |
| `SEO-003` | فحص الموقع الحالي واستخراج بيانات الـSEO (مصدر معلومات فقط) | M01-032, M01-034 | D-000, D-028, D-041, D-044 | docs/phase-01-discovery/01-current-website-inventory.md · docs/phase-01-discovery/02-url-inventory-and-migration-seed.md |  |  | PARTIAL | N/A |
| `SEO-004` | SEO Audit للموقع الحالي (فهرسة، بنية، جودة، تقنية) | M01-144, M01-145 | D-028, D-041 | docs/phase-01-discovery/01-current-website-inventory.md |  |  | PARTIAL | N/A |
| `SEO-005` | الظهور على Intent حقيقي وليس الاسم فقط | M11-099, M11-100 | GEP-§46/§47, D-007 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §46 |  |  | NOT STARTED | N/A |
| `SEO-006` | ممنوع Keyword Stuffing | M11-101 | GEP-§46/§47, D-068 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §46 |  |  | NOT STARTED | N/A |
| `SEO-007` | Internal Linking قوية في Coffee Knowledge Hub | M01-141 | GEP-§46/§47, DB-09 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §47 |  |  | NOT STARTED | N/A |
| `SEO-008` | Local SEO لكل فرع (NAP، Maps، الساعات، Schema، Metadata) | M01-216, M11-025, M11-026 | GEP-§10/§11, D-020, D-048, D-049, D-057, D-060 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §10–§11 · docs/google/BRANCH-DATA-SYNC.md |  |  | NOT STARTED | N/A |
| `SEO-009` | URL Migration Map رسمية قبل تغيير الموقع القديم | M01-146, M27-119 | GEP-§15, D-054, DB-13 | docs/google/SEO-MIGRATION-MAP.md · docs/phase-01-discovery/02-url-inventory-and-migration-seed.md · docs/phase-01-discovery/13-root-and-international-seo-plan.md §5–§7 |  |  | PARTIAL | N/A |
| `SEO-010` | تصنيف كل URL قديم ذي قيمة: KEEP · REBUILD · REDIRECT · REMOVE | M01-147, M11-037, M11-038 | GEP-§15 | docs/google/SEO-MIGRATION-MAP.md · docs/phase-01-discovery/02-url-inventory-and-migration-seed.md |  |  | PARTIAL | N/A |
| `SEO-011` | 301 بقفزة واحدة لكل URL ذي قيمة يتغير — بلا خسارة SEO أو Backlinks | M01-148, M01-149, M11-039, M27-120 | GEP-§15, D-011, D-054 | docs/phase-01-discovery/13-root-and-international-seo-plan.md §5 · docs/google/SEO-MIGRATION-MAP.md |  |  | NOT STARTED | N/A |
| `SEO-012` | MENU-01: /menu → 301 → /ar/jo/menu/ | M13-009, M11-040, M11-041, M23-189 | D-054, DB-20, GEP-§16, F-23 | docs/phase-01-discovery/13-root-and-international-seo-plan.md §6 · docs/google/SEO-MIGRATION-MAP.md · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §1 |  |  | NOT STARTED | N/A |
| `SEO-013` | تنفيذ /menu 301 وقت الإطلاق فقط بعد 6 فحوص + موافقة الـOwner | M13-010, M23-190 | D-054, DB-20, F-23 | docs/phase-01-discovery/13-root-and-international-seo-plan.md §6 · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §1 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `SEO-014` | أي QR أو رابط قديم يستمر بالعمل بعد الإطلاق | M13-011 | D-054 | docs/phase-01-discovery/10-url-architecture-draft.md §3 · docs/phase-01-discovery/13-root-and-international-seo-plan.md §6 |  |  | NOT STARTED | N/A |
| `SEO-015` | لا URLs ولا Redirects ولا SEO changes على Production الآن | M13-008, M23-230, M27-123 | D-053, D-054, F-23, D-002 | docs/menu-ia/MENU-DECISION-REGISTER.md F-23 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `SEO-016` | رابط الفرع الأصلي لا يتغير أبدًا | M13-007 | D-053 | docs/phase-01-discovery/13-root-and-international-seo-plan.md §8 |  |  | NOT STARTED | N/A |
| `SEO-017` | جاهزية نقل 1:1 إلى Global Domain بلا خسارة SEO | M03-024, M04-010 | D-011, D-019, D-031 | docs/phase-01-discovery/09-architecture-options-after-r1.md AR-01 · docs/phase-01-discovery/10-url-architecture-draft.md §5 · docs/phase-01-discovery/13-root-and-international-seo-plan.md §7 |  |  | NOT STARTED | N/A |
| `SEO-018` | Redirect Manager لإدارة 301 والروابط القديمة والمتغيرة | M01-219 |  | docs/phase-01-discovery/13-root-and-international-seo-plan.md §5 |  |  | NOT STARTED | N/A |
| `SEO-019` | صفحة 404 غير فارغة: Menu · Locations · Search · Home | M01-218 |  |  |  |  | NOT STARTED | N/A |
| `SEO-020` | قبل الإطلاق: 404 reviewed · Old URLs mapped · 301 migration ready | M11-114 | GEP-§15 | docs/google/GOOGLE-ECOSYSTEM-CHECKLIST.md |  |  | NOT STARTED | N/A |
| `SEO-021` | Canonical صحيح لكل صفحة | M11-053, M11-054 | GEP-§17/§19/§22 | docs/phase-01-discovery/13-root-and-international-seo-plan.md §3 · docs/google/GOOGLE-ECOSYSTEM-POLICY.md §22 |  |  | NOT STARTED | N/A |
| `SEO-022` | ?branch= بـCanonical واحد — لا نسخة منيو لكل فرع | M23-106, M23-105 | D-145, F-18 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §12–§13 |  |  | NOT STARTED | NO |
| `SEO-023` | لا URLs قابلة للفهرسة من search/category/branch | M23-214, M23-215 | D-145, F-18 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §12 |  |  | NOT STARTED | N/A |
| `SEO-024` | لا صفحات أصناف في V1 — لا 191 thin pages | M23-132 | D-143, D-147, F-09, F-21 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §13 · docs/menu-ia/MENU-DECISION-REGISTER.md |  |  | NOT STARTED | N/A |
| `SEO-025` | صفحات أصناف مستقبلًا فقط لمنتجات تستحق SEO Content فعليًا | M23-131 | D-147, D-081 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §13 |  |  | NOT STARTED | N/A |
| `SEO-026` | كل المنتجات والأسعار في الـHTML الأولي — لا JS-only ولا Virtualization | M23-038, M23-187, M23-219 | F-09, D-147, F-16 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §13 |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `SEO-027` | تحقق ما بعد البناء: Crawlability · Indexability · robots.txt · noindex … | M11-042 | GEP-§17/§19/§22 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §17 |  |  | NOT STARTED | N/A |
| `SEO-028` | Staging لا يُفهرس: Authentication + noindex + blocking | M11-043 | GEP-§18, GEP-§17/§19/§22 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §18 |  |  | NOT STARTED | N/A |
| `SEO-029` | XML Sitemap: Canonical · Public · Indexable · Approved فقط | M11-045, M11-046 | GEP-§17/§19/§22 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §19 · docs/phase-01-discovery/10-url-architecture-draft.md §3 · docs/phase-01-discovery/02-url-inventory-and-migration-seed.md §B |  |  | NOT STARTED | N/A |
| `SEO-030` | إرسال الـSitemap لـGSC بعد اعتماد الموقع — Submit ≠ Success | M12-042, M12-043 | GIO-§A15/§A16, D-055 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md |  |  | NOT STARTED | N/A |
| `SEO-031` | تقرير Indexing حقيقي بعد الإطلاق | M12-044 | GIO-§A15/§A16 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md · docs/google/POST-LAUNCH-GOOGLE-CHECKLIST.md |  |  | NOT STARTED | N/A |
| `SEO-032` | hreflang صحيح: Arabic Alternate + English Alternate لكل صفحة مترجمة | M11-048 | GEP-§20/§21, D-031 | docs/phase-01-discovery/13-root-and-international-seo-plan.md §4 · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §13 |  |  | NOT STARTED | N/A |
| `SEO-033` | x-default: الجذر محسوم (/) — صفحات العلامة والسوق معلّقة (ROOT-02) | M11-049 | D-052, GEP-§20/§21 | docs/phase-01-discovery/13-root-and-international-seo-plan.md §4، §9 · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §13 |  |  | NOT STARTED | N/A |
| `SEO-034` | خطة ما قبل Freeze: canonical · hreflang · x-default · redirects · /menu | M10-011, M10-012, M27-122 | D-031, DB-02, DB-11 | docs/phase-01-discovery/13-root-and-international-seo-plan.md · docs/phase-01-discovery/10-url-architecture-draft.md §6 |  |  | PARTIAL | N/A |
| `SEO-035` | SEO Editor في الـDashboard لكل Page / Article (/ Product) | M01-160, M01-161, M25-105 | D-086 |  |  |  | NOT STARTED | N/A |
| `SEO-036` | إعدادات SEO الخطرة في Advanced section | M25-107 |  |  |  |  | NOT STARTED | N/A |
| `SEO-037` | SEO Preview (Google) + Social Preview | M25-108 |  |  |  |  | NOT STARTED | N/A |
| `SEO-038` | SEO HEALTH CENTER: GOOD/WARNING/CRITICAL + Issue/Page/Severity/Fix | M25-053, M25-061 |  |  |  |  | NOT STARTED | N/A |
| `SEO-039` | قائمة فحوص SEO Health | M01-163, M25-054, M25-055, M25-056, M25-057, M25-058, M25-059 |  |  |  |  | NOT STARTED | N/A |
| `SEO-040` | لا تغييرات SEO تلقائية — موافقة الـOwner على أي تعديل حساس | M25-060, M25-062 |  |  |  |  | NOT STARTED | N/A |
| `SEO-041` | DoD (بحث): SEO Clean · Google/AEO/GEO Ready · Redirects/Schema Correct | M01-235 |  |  |  |  | NOT STARTED | N/A |
| `AEO-001` | الظهور في AI Search (AI Overviews، ChatGPT، Gemini، Perplexity، Copilot، Bing) | M01-155, M11-104 | GEP-§48/§49, D-003 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §48 · docs/phase-01-discovery/13-root-and-international-seo-plan.md §1 · docs/phase-01-discovery/15-root-gateway-wireframe.md §1 |  |  | NOT STARTED | N/A |
| `AEO-002` | Entity Clarity: أسئلة الكيان بالإنجليزية والعربية | M01-156, M01-157 | D-007, D-008, D-018, D-020, D-057 |  |  |  | NOT STARTED | N/A |
| `AEO-003` | اتساق GBP ↔ الموقع وهرمية الكيان SHELTER COFFEE → Jordan → Irbid → DRIVE/HOUSE | M11-105, M11-106 | GEP-§48/§49, D-048, D-049 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §49 · docs/google/BRANCH-DATA-SYNC.md |  |  | NOT STARTED | N/A |
| `SCHEMA-001` | أنواع Schema المسموحة عند الحاجة | M01-158, M11-056 | GEP-§23/§24 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §23 · docs/phase-01-discovery/15-root-gateway-wireframe.md §5 · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §13 |  |  | NOT STARTED | N/A |
| `SCHEMA-002` | Structured Data حقيقي وظاهر فقط — ممنوع Schema Spam | M01-159, M11-055, M11-058, M23-191, M23-193 | GEP-§23/§24 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §23 |  |  | NOT STARTED | N/A |
| `SCHEMA-003` | ممنوع اختراع rating/reviews/availability/offers/nutrition/claims في Schema | M23-192 | D-038, D-039, D-081, D-089, D-094 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §13 |  |  | NOT STARTED | N/A |
| `SCHEMA-004` | FAQPage فقط عند استيفاء الشروط ووجود محتوى فعلي | M11-057 | GEP-§23/§24 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §23 |  |  | NOT STARTED | N/A |
| `SCHEMA-005` | Branch Schema متوافق مع GBP: Name · Address · Geo · Hours · Phone · SameAs | M11-059 | GEP-§23/§24, D-020, D-021, D-048, D-049, D-033, D-034 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §24 · docs/google/BRANCH-DATA-SYNC.md · docs/phase-01-discovery/09-architecture-options-after-r1.md AR-07، AR-08 |  |  | NOT STARTED | N/A |
| `SCHEMA-006` | Image في الـSchema فقط إذا اعتمدها الـOwner | M11-060 | GEP-§23/§24, D-056, D-083 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §24 |  |  | NOT STARTED | N/A |
| `SCHEMA-007` | Telephone في Schema/GBP = 0799009436 (+962799009436) | M13-040 | D-060, D-057, D-065 | docs/phase-01-discovery/15-root-gateway-wireframe.md |  |  | NOT STARTED | N/A |
| `SCHEMA-008` | تنفيذ الـSchema فعليًا بعد اعتماد البيانات — البيانات المطلوبة لكل نوع | M12-049, M13-015 | GIO-§A20/§A21, D-055, D-018, D-007, D-070 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md · docs/phase-01-discovery/15-root-gateway-wireframe.md §5 · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §13 |  |  | NOT STARTED | N/A |
| `SCHEMA-009` | تحقق Rich Results: إصلاح Errors قبل الإطلاق — JSON-LD ≠ مكتمل | M11-092, M11-093, M12-050, M12-051 | GEP-§41, GIO-§A20/§A21 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §41 · docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md |  |  | NOT STARTED | N/A |
| `SCHEMA-010` | Schema Settings في الـDashboard حسب نوع المحتوى | M01-162, M25-106 |  |  |  |  | NOT STARTED | N/A |
| `PERF-001` | الموقع سريع جدًا ولا يشعر بأنه ثقيل (CRITICAL) | M01-184 |  | docs/menu-ia/PERFORMANCE-BUDGET.md | tooling/scripts/lighthouse.mjs |  | NOT STARTED | PROTOTYPE |
| `PERF-002` | Core Web Vitals جيدة تحت قيود شبكة وأجهزة الموبايل | M23-185 | GEP-§36/§39/§40 | docs/menu-ia/PERFORMANCE-BUDGET.md §3 | tooling/scripts/lighthouse.mjs |  | NOT STARTED | PROTOTYPE |
| `PERF-003` | مراقبة Core Web Vitals طوال المشروع | M01-185, M11-090 | GEP-§36/§39/§40 | docs/menu-ia/PERFORMANCE-BUDGET.md §4 · docs/google/GOOGLE-ECOSYSTEM-POLICY.md §40 | tooling/scripts/lighthouse.mjs |  | PARTIAL | PROTOTYPE |
| `PERF-004` | Baseline أداء الموقع القديم قبل وبعد إعادة البناء | M01-035, M11-091 | GEP-§36/§39/§40, D-041, D-042 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §40 · docs/google/SEARCH-CONSOLE-BASELINE.md |  |  | NOT STARTED | NO |
| `PERF-005` | Performance Budget للموقع كاملًا | M01-186 |  | docs/menu-ia/PERFORMANCE-BUDGET.md |  |  | PARTIAL | PROTOTYPE |
| `PERF-006` | Performance Budget أولي للمنيو (Phase H) بأهداف واقعية ومبررة | M23-248, M23-249 |  | docs/menu-ia/PERFORMANCE-BUDGET.md | tooling/scripts/lighthouse.mjs |  | IMPLEMENTED — NOT TESTED | PROTOTYPE |
| `PERF-007` | المنيو: ممنوع تحميل 191 صورة عالية الدقة مباشرة | M23-179, M21-036 | D-147 | docs/menu-ia/PERFORMANCE-BUDGET.md §2 |  |  | NOT STARTED | PROTOTYPE |
| `PERF-008` | الصور: responsive sizes وAVIF/WebP وsrcset وأبعاد محجوزة | M23-180, M26-025 |  |  | tooling/scripts/images.mjs |  | PARTIAL | PROTOTYPE |
| `PERF-009` | Lazy loading تحت الـfold وأولوية لصور الشاشة الأولى فقط | M23-181 |  | docs/menu-ia/PERFORMANCE-BUDGET.md |  |  | NOT STARTED | NO |
| `PERF-010` | النص والواجهة الأساسية أولًا ثم الصور | M23-183 |  |  |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | PROTOTYPE |
| `PERF-011` | الـThird-party وGoogle scripts لا تبطئ الموقع ولا تؤخر أول محتوى مفيد | M23-184, M11-082, M11-083 | GEP-§36/§39/§40 | docs/menu-ia/PERFORMANCE-BUDGET.md §2 · docs/google/GOOGLE-ECOSYSTEM-POLICY.md §36 |  |  | NOT STARTED | NO |
| `PERF-012` | الخرائط: لا Google Map Embed ثقيل تلقائيًا — اختيار الأخف | M11-080, M11-081 |  | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §35 |  |  | NOT STARTED | NO |
| `PERF-013` | أداء الخطوط | M01-189, M11-084, M11-085 |  | docs/menu-ia/PERFORMANCE-BUDGET.md §2 · docs/google/GOOGLE-ECOSYSTEM-POLICY.md §37 |  |  | NOT STARTED | NO |
| `PERF-014` | Code splitting حيث يناسب | M23-182 |  | docs/menu-ia/PERFORMANCE-BUDGET.md |  |  | NOT STARTED | NO |
| `PERF-015` | Progressive Enhancement عند فشل JS | M23-218 | F-16 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §20 |  |  | NOT STARTED | PROTOTYPE |
| `PERF-016` | أداء الحركة: transform/opacity وتجنب Jank | M01-190, M01-191 |  | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §17 |  |  | NOT STARTED | NO |
| `PERF-017` | Lighthouse / PageSpeed checks قابلة للتكرار (Mobile وDesktop) | M11-088, M24-032 | GEP-§36/§39/§40 |  | tooling/scripts/lighthouse.mjs |  | PARTIAL | PROTOTYPE |
| `PERF-018` | لا مطاردة الـScore — معالجة الأسباب الجذرية وتجربة المستخدم الحقيقية | M11-089, M24-033 | GEP-§36/§39/§40 |  | tooling/scripts/lighthouse.mjs |  | PARTIAL | PROTOTYPE |
| `PERF-019` | sitespeed.io لفحص أداء أعمق للصفحات المهمة | M24-037, M24-038 |  | docs/FRONTEND-TOOLING.md §2 |  |  | NOT STARTED | NO |
| `PERF-020` | Dashboard: لوحة Performance & Core Web Vitals بلغة سهلة | M25-065, M25-066, M25-067 |  |  |  |  | NOT STARTED | NO |
| `PERF-021` | Dashboard: Lighthouse مجدول ويدوي مع الأسباب الجذرية | M25-068, M25-069, M25-070 | D-044 |  | tooling/scripts/lighthouse.mjs |  | NOT STARTED | NO |
| `PERF-022` | الـDashboard نفسها سريعة | M25-137 |  |  |  |  | NOT STARTED | NO |
| `PERF-023` | لا استدعاء APIs في كل Page Load ولا real-time polling ثقيل | M25-133, M25-030 | D-029, D-044 |  |  |  | NOT STARTED | NO |
| `A11Y-001` | الهدف WCAG 2.2 AA وWCAG-conscious UX | M01-199, M23-164 |  | docs/menu-ia/ACCESSIBILITY-CHECKLIST.md |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `A11Y-002` | نطاق مراجعة الوصولية | M01-200 |  | docs/menu-ia/ACCESSIBILITY-CHECKLIST.md |  |  | PARTIAL | PROTOTYPE |
| `A11Y-003` | الاختبار الآلي لا يكفي: automated + manual checklist | M24-030, M25-124 |  | docs/menu-ia/ACCESSIBILITY-CHECKLIST.md · docs/qa/RESPONSIVE-QA-MATRIX.md |  |  | PARTIAL | PROTOTYPE |
| `A11Y-004` | axe يُفشل/يبلّغ المشاكل الجدية | M24-029 |  |  |  | tooling/tests/prototype/menu-wireframe.spec.mjs | PARTIAL | PROTOTYPE |
| `A11Y-005` | احترام prefers-reduced-motion | M01-193, M24-010 |  | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §17 · docs/menu-ia/ACCESSIBILITY-CHECKLIST.md |  |  | NOT STARTED | NO |
| `A11Y-006` | تباين كافٍ | M23-172 |  | docs/menu-ia/ACCESSIBILITY-CHECKLIST.md |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `A11Y-007` | Keyboard navigation كامل مع focus مرئي | M23-166 |  | docs/menu-ia/ACCESSIBILITY-CHECKLIST.md |  | tooling/tests/prototype/responsive.spec.mjs | NOT STARTED | PROTOTYPE |
| `A11Y-008` | تفاصيل المنتج: إدارة التركيز وEscape وfocus trap/return | M23-064, M23-065, M23-168 | D-146, F-13, R-05 | docs/menu-ia/ACCESSIBILITY-CHECKLIST.md |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `A11Y-009` | Screen reader labels وARIA فقط حيث يلزم | M23-167 |  | docs/menu-ia/ACCESSIBILITY-CHECKLIST.md |  |  | NOT STARTED | PROTOTYPE |
| `A11Y-010` | إعلانات البحث: عدد النتائج وزر مسح accessible | M23-169 |  | docs/menu-ia/ACCESSIBILITY-CHECKLIST.md |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `A11Y-011` | Alt نصي ذو معنى للصور | M23-170 |  | docs/menu-ia/ACCESSIBILITY-CHECKLIST.md |  |  | NOT STARTED | NO |
| `A11Y-012` | تسلسل عناوين صحيح | M23-171 |  | docs/menu-ia/ACCESSIBILITY-CHECKLIST.md |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `A11Y-013` | سمات اللغة والاتجاه الصحيحة | M23-042 |  | docs/menu-ia/ACCESSIBILITY-CHECKLIST.md |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `A11Y-014` | السعر مفهوم لقارئ الشاشة | M23-173 | R-08 | docs/menu-ia/ACCESSIBILITY-CHECKLIST.md |  |  | NOT STARTED | NO |
| `A11Y-015` | ALL CAPS لا يسبب نطقًا حرفيًا | M23-045 | D-131 | docs/menu-ia/ACCESSIBILITY-CHECKLIST.md |  |  | NOT STARTED | NO |
| `A11Y-016` | الحالات لا تعتمد على اللون وحده | M23-094 |  | docs/menu-ia/ACCESSIBILITY-CHECKLIST.md |  |  | NOT STARTED | PROTOTYPE |
| `A11Y-017` | أهداف اللمس ≥ 44×44px | M23-165, M26-009 |  | docs/menu-ia/UX-VALIDATION.md §2 P0-01 · docs/menu-ia/ACCESSIBILITY-CHECKLIST.md |  | tooling/tests/prototype/menu-wireframe.spec.mjs · tooling/tests/prototype/responsive.spec.mjs | NOT STARTED | PROTOTYPE |
| `A11Y-018` | ممنوع منع pinch zoom | M26-047 |  |  |  | tooling/tests/prototype/responsive.spec.mjs | NOT STARTED | PROTOTYPE |
| `A11Y-019` | Accessibility checklist لصفحة المنيو (Phase J) | M23-251 |  | docs/menu-ia/ACCESSIBILITY-CHECKLIST.md |  |  | IMPLEMENTED — NOT TESTED | PROTOTYPE |
| `A11Y-020` | Dashboard: تقرير مشاكل الوصولية | M25-123 |  |  |  |  | NOT STARTED | NO |
| `MOTION-001` | الموقع يجب أن يشعر بأنه حيّ (Alive) — Premium وليس Static | M01-164, M24-054, M27-102 | D-004 | design-system/tokens/tokens.template.json motion |  |  | NOT STARTED | NO |
| `MOTION-002` | الحركة تخدم الـUX — لا حركة لمجرد أن المكتبة تدعمها | M01-165, M01-170, M24-055 |  | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §17 |  |  | NOT STARTED | NO |
| `MOTION-003` | Performance always wins over decorative motion — لا مساس بـLCP / INP / CLS | M27-103, M24-009 | D-004 | docs/FRONTEND-TOOLING.md §5 · docs/menu-ia/PERFORMANCE-BUDGET.md | tooling/scripts/lighthouse.mjs |  | NOT STARTED | NO |
| `MOTION-004` | Motion Design System | M01-166, M01-167, M01-168 |  | design-system/tokens/tokens.template.json · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §17 |  |  | PARTIAL | N/A |
| `MOTION-005` | دراسة تقنيات الحركة الممكنة (ليست كلها معتمدة) | M01-169 |  |  |  |  | NOT STARTED | NO |
| `MOTION-006` | ممنوعات الحركة (Do not over-animate) | M01-171, M01-172, M24-056 |  | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §17 |  |  | NOT STARTED | NO |
| `MOTION-007` | تقنية الحركة: Motion كخيار أساسي (يحل محل دراسة GSAP / Motion.page) | M01-174, M01-175, M01-176 | DB-08 | docs/FRONTEND-TOOLING.md §1, §2 |  |  | NOT STARTED | NO |
| `MOTION-008` | Lenis: اختياري ومشروط — ليس عامًا، وليس على المنيو افتراضيًا | M01-177, M01-178, M24-012 |  | docs/FRONTEND-TOOLING.md §1, §2 |  |  | NOT STARTED | NO |
| `MOTION-009` | مكتبات مشروطة: Lottie · Swiper · Three.js | M01-179, M01-180, M01-181 |  | docs/governance/APPROVED-ASSET-LIBRARY.md · docs/FRONTEND-TOOLING.md §7 |  |  | NOT STARTED | NO |
| `MOTION-010` | Progressive Enhancement: المحتوى لا يعتمد على الحركة | M01-192, M23-177 |  | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §16, §20 |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `MOTION-011` | دعم prefers-reduced-motion | M23-178 |  | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §17 · docs/menu-ia/ACCESSIBILITY-CHECKLIST.md |  |  | NOT STARTED | NO |
| `MOTION-012` | حركة المنيو: High Motion للموقع لكن المنيو سريعة | M23-174, M23-175, M23-176 |  | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §17 · docs/menu-ia/PERFORMANCE-BUDGET.md |  |  | NOT STARTED | NO |
| `MOTION-013` | نطاق حركة الواجهة المسموح | M24-008 |  |  |  |  | NOT STARTED | NO |
| `MEDIA-001` | قاعدة صارمة: لا صورة أو Asset بدون موافقة الـOwner (كل صورة بموافقة منفصلة) | M01-055, M01-056, M13-020, M23-049, M27-114, M27-113 | D-003, D-056, D-083, F-11, AAL-RULE-01 | docs/governance/APPROVED-ASSET-LIBRARY.md · media/README.md | tooling/scripts/images.mjs |  | PARTIAL | YES |
| `MEDIA-002` | حزمة عرض الصورة المقترحة | M01-057 | AAL-RULE-01 | docs/governance/APPROVED-ASSET-LIBRARY.md · media/README.md |  |  | PARTIAL | N/A |
| `MEDIA-003` | كل صورة مقترحة أو مطلوبة تبدأ PENDING OWNER APPROVAL | M01-058, M15-042, M27-116 | D-083, AAL-RULE-01 | media/README.md | tooling/scripts/images.mjs |  | PARTIAL | YES |
| `MEDIA-004` | صور Google ليست Approved Assets | M13-019, M11-017, M11-018, M11-019 | D-056, D-046, GEP-§6/§43 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §6, §43 · docs/governance/DECISION-LOG.md D-056 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `MEDIA-005` | لا Stock ولا AI ولا Google ولا صور الموقع القديم تلقائيًا | M15-040, M15-041, M23-048, M27-115 | D-083, F-11, F-23 | docs/menu-ia/MENU-DECISION-REGISTER.md F-11 · docs/menu-ia/wireframes/README.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `MEDIA-006` | نظام Approved Asset Library | M01-059, M01-060 | AAL-RULE-01 | docs/governance/APPROVED-ASSET-LIBRARY.md · media/README.md |  |  | PARTIAL | N/A |
| `MEDIA-007` | صور المنتجات من الـOwner وربطها بالـProduct ID | M15-039, M15-043 | D-083, M-02, MI-023 | docs/menu-ia/MENU-DECISION-REGISTER.md M-02 · media/README.md · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §6 | tooling/scripts/images.mjs |  | PARTIAL | YES |
| `MEDIA-008` | Media Architecture / Media Model | M15-044, M23-051 | D-083 | media/README.md |  |  | PARTIAL | N/A |
| `MEDIA-009` | صورة بطاقة المنتج: كبيرة بنسبة 1:1 | M23-046, M23-047 | F-11, R-04, CF-05 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §6 · docs/menu-ia/MENU-DECISION-REGISTER.md F-11, R-04, CF-05 | tooling/scripts/images.mjs |  | NOT STARTED | PROTOTYPE |
| `MEDIA-010` | أداء الصور و Responsive images | M01-187, M01-188, M26-024 |  | docs/menu-ia/PERFORMANCE-BUDGET.md · media/README.md | tooling/scripts/images.mjs · tooling/scripts/lighthouse.mjs |  | PARTIAL | YES |
| `MEDIA-011` | Sharp image pipeline: بلا تكبير، الأصول الأصلية محفوظة، الاعتماد قائم | M24-041, M24-042, M24-043, M24-044 |  | media/README.md · docs/FRONTEND-TOOLING.md §2 | tooling/scripts/images.mjs | tooling/scripts/images.mjs (selftest) | TESTED | YES |
| `MEDIA-012` | Media Library في الـDashboard | M25-099, M25-100 | D-083, D-056, AAL-RULE-01 | media/README.md |  |  | NOT STARTED | NO |
| `MEDIA-013` | تتبع استخدام كل صورة (Usage tracking) | M25-101 |  |  |  |  | NOT STARTED | NO |
| `MEDIA-014` | لا ملفات مكررة في الـMedia Library | M25-102 |  |  |  |  | NOT STARTED | NO |
| `MEDIA-015` | Image Health في الـDashboard مع الإصلاح | M25-103, M25-104 |  |  |  |  | NOT STARTED | NO |
| `MEDIA-016` | ممنوع Autoplay Audio | M01-173 |  |  |  |  | NOT STARTED | NO |
| `SEC-001` | فحص أمني شامل (الموقع الحالي ثم الجديد قبل الإطلاق) | M01-222, M01-223 | D-037, D-028 | docs/governance/RISK-REGISTER.md RISK-01 · docs/phase-01-discovery/08-access-requests.md |  |  | NOT STARTED | NO |
| `SEC-002` | لا Secrets في الـFront-End — الـCredentials Server-side وفي إعدادات البيئة فقط | M01-224, M11-087, M25-132 | AC-RULE-02, GIO-§B-SEC, GEP-§38 | docs/phase-01-discovery/08-access-requests.md · docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §B (مبادئ الأمان + مصفوفة الصلاحيات) |  |  | NOT STARTED | NO |
| `SEC-003` | Maps API Key (إن لزم) مقيد: HTTP referrer · API restrictions · Usage limits | M11-086 | GEP-§38, D-043, D-046 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §38 Google Maps API |  |  | NOT STARTED | N/A |
| `SEC-004` | حماية الـStaging: Authentication + noindex — robots.txt وحده ليس حماية أمنية | M11-044 | GEP-§18, D-046 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §18 Staging Must Not Index |  |  | NOT STARTED | NO |
| `SEC-005` | Secure authentication + Session security لنظام الـAdmin | M25-153, M25-155 | DB-08 |  |  |  | NOT STARTED | NO |
| `SEC-006` | CSRF protection حسب الـArchitecture | M25-156 | DB-08 |  |  |  | NOT STARTED | NO |
| `SEC-007` | Rate limiting حيث يلزم (Login · Forms · APIs) | M25-157 |  |  |  |  | NOT STARTED | NO |
| `SEC-008` | Secure API access + audit logging — لا Admin APIs عامة بدون Authorization | M25-158, M25-160 |  |  |  |  | NOT STARTED | NO |
| `SEC-009` | Safe file uploads في الـMedia Library | M25-159 | D-083 |  | tooling/scripts/images.mjs |  | NOT STARTED | NO |
| `SEC-010` | أمان الموقع القديم: الخيار A — Backup → Staging → … → Owner Approval | M10-026, M10-027, M10-030 | D-037 | docs/governance/RISK-REGISTER.md RISK-01 |  |  | NOT STARTED | NO |
| `SEC-011` | ممنوع حذف Comments أو Users أو Plugins أو Content بدون موافقة الـOwner | M10-029 | D-037, GIO-§A29/§A33 | docs/governance/RISK-REGISTER.md RISK-01 |  |  | NOT STARTED | N/A |
| `PERM-001` | V1: الـOwner وحده لديه Full Control ويرى كل شيء | M25-013, M25-015 | D-148 |  |  |  | NOT STARTED | NO |
| `PERM-002` | أدوار مستقبلية: OWNER · ADMIN · EDITOR · SEO · MARKETING · CONTENT MANAGER | M25-014, M01-122 | D-148 |  |  |  | NOT STARTED | NO |
| `PERM-003` | Least privilege للمستخدمين: لا Full Access للجميع | M01-123, M25-016 | D-051, GIO-§A29/§A33 |  |  |  | NOT STARTED | NO |
| `PERM-004` | الصلاحيات تُفرض Server-side — لا اعتماد على إخفاء الأزرار | M25-017, M25-154 |  |  |  |  | NOT STARTED | NO |
| `PERM-005` | Permissions Model (PERMISSIONS.md) قبل التنفيذ — Phase E | M25-214 |  |  |  |  | NOT STARTED | NO |
| `PERM-006` | تعديلات المنيو الحساسة: موافقة الـOwner حيث يلزم | M15-047 | D-084 |  |  |  | NOT STARTED | NO |
| `PERM-007` | AI لا ينفذ تغييرات حساسة بدون Approval | M25-151 |  |  |  |  | NOT STARTED | N/A |
| `PERM-008` | بروتوكول طلب الصلاحيات (5 بنود) بأقل صلاحية لازمة | M02-006, M12-077 | AC-RULE-01, AC-RULE-04, GIO-§B-SEC, GIO-§A29/§A33, D-028, D-029, D-051 | docs/phase-01-discovery/08-access-requests.md · docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §B (مبادئ الأمان + مصفوفة الصلاحيات) |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `PERM-009` | الـOwner يُطلب منه فقط Auth · Approval · 2FA · Ownership · Billing · Legal | M12-069 | GIO-§A29/§A33, D-051, D-055 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §29/§33 |  |  | NOT STARTED | N/A |
| `PERM-010` | AC-01: وصول مباشر للقراءة والفحص فقط على shelterjo.com · www · shop — أولًا | M04-050, M10-040 | D-028, D-041 | docs/phase-01-discovery/08-access-requests.md · docs/governance/RISK-REGISTER.md RISK-07 |  |  | NOT STARTED | NO |
| `AUDIT-001` | Audit Log لكل Action في الـDashboard (Who · What · Before · After · Date/Time) | M01-124, M01-125, M25-113, M25-114 | D-084, D-148 | docs/phase-01-discovery/17-menu-data-model-draft.md §2 audit_log · menu_change · menu_version · docs/menu-ia/SHELTER-MENU-IA-SPEC.md §18 (CMS — SSOT في V1) · docs/phase-01-discovery/09-architecture-options-after-r1.md AR-08 |  |  | NOT STARTED | NO |
| `AUDIT-002` | لا Silent Changes | M25-115 | D-093 | docs/phase-01-discovery/17-menu-data-model-draft.md §2 audit_log · menu_change · menu_version |  |  | NOT STARTED | NO |
| `AUDIT-003` | Version History لكل تغيير مهم (User · Time · Old Value · New Value) قابلة للعرض | M25-111, M15-046, M21-007 | D-084, D-136, D-148 | docs/phase-01-discovery/17-menu-data-model-draft.md §2 audit_log · menu_change · menu_version · docs/phase-01-discovery/17-menu-data-model-draft.md §4 Menu Versioning |  |  | NOT STARTED | NO |
| `AUDIT-004` | Restore / Revert لنسخة سابقة — إذا كان آمنًا | M01-121, M25-112 |  |  |  |  | NOT STARTED | NO |
| `AUDIT-005` | MENU VERSION `MV-YYYY-MM-DD` — الحالية MV-2026-10-01 (سارية من 2026-10-01) | M18-043, M19-033, M20-031 | D-107, D-122, D-135, F-01 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md · docs/phase-01-discovery/17-menu-data-model-draft.md (v0.4) §2 menu_version · docs/menu-ia/MENU-DECISION-REGISTER.md F-01 |  |  | FROZEN | N/A |
| `AUDIT-006` | استيراد المنيو لا يمسح التاريخ (Previous/Current Price · Effective Date) | M18-044, M18-042 | D-107, D-099 | docs/phase-01-discovery/17-menu-data-model-draft.md §4 Menu Versioning |  |  | NOT STARTED | NO |
| `AUDIT-007` | الحفاظ على Lineage لكل منتج (المصدر · الصف · الـHash · النسخة · التاريخ) | M23-023 | D-110, D-133, D-135, F-02, F-04 | docs/phase-01-discovery/menu/menu-inventory-v1.0.csv + SHELTER-MENU-INVENTORY-v1.0.xlsx · docs/phase-01-discovery/17-menu-data-model-draft.md (v0.4) |  |  | PARTIAL | N/A |
| `AUDIT-008` | Versioning + Backup strategy + Safe rollback لإدارة المحتوى الحساس | M25-161 |  |  |  |  | NOT STARTED | NO |
| `INT-001` | لا Paid API/Quota/Credits/Service بدون موافقة مسبقة + إفصاح خماسي | M04-056, M10-052, M11-107, M11-108, M11-128, M23-225, M25-077, M27-110, M27-111 | D-029, D-044, D-046, GEP-§50/§52 (G8) | docs/phase-01-discovery/08-access-requests.md · docs/FRONTEND-TOOLING.md §7 · docs/google/GOOGLE-ECOSYSTEM-POLICY.md §50 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `INT-002` | لا Google Places API مدفوع حاليًا | M10-050 | D-043 (G4) | docs/phase-01-discovery/08-access-requests.md AC-03 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `INT-003` | Supermetrics (AC-04): لا استهلاك Quota قبل الإفصاح والموافقة | M04-055, M10-048 | D-028 (G7), D-042 (G5) | docs/phase-01-discovery/08-access-requests.md AC-04 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `INT-004` | الأدوات المحلية المجانية مسموحة: Playwright · Lighthouse · local testing | M27-112 | D-044 |  | tooling/ |  | IMPLEMENTED — NOT TESTED | N/A |
| `INT-005` | خدمة مراقبة أخطاء خارجية (مثل Sentry) تُقترح أولًا ولا تُفعّل قبل الموافقة | M25-076 |  | docs/FRONTEND-TOOLING.md §3 §7 |  |  | NOT STARTED | NO |
| `INT-006` | Official APIs فقط (GA Data API · GSC API) — لا scraping لـGoogle dashboards | M25-130, M25-131 |  |  |  |  | NOT STARTED | NO |
| `INT-007` | ممنوع تثبيت WordPress Plugins في هذه المرحلة | M01-024 | D-002 (G1), D-037 |  |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `INT-008` | No Plugin Bloat + تقييم أي Plugin/أداة طرف ثالث قبل الإضافة | M01-182, M01-183 |  | docs/FRONTEND-TOOLING.md §7 |  |  | PARTIAL | NO |
| `INT-009` | Google Site Kit (موجود حاليًا): فحص ما يديره — لا افتراض لاستخدامه في الجديد | M11-073, M11-074, M12-082 | GEP-§32, GA-01 | docs/phase-01-discovery/12-homepage-screenshots-audit.md §7 · docs/google/GOOGLE-INTEGRATION-ARCHITECTURE.md G-06 |  |  | PARTIAL | NO |
| `INT-010` | منصات التوصيل لكل فرع: من الـOwner فقط — لا استنتاج من الموقع القديم أو الإنترنت | M03-059, M14-041 | D-016 (G2), D-073 |  |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `INT-011` | R2B-01: منصات التوصيل لكل فرع وروابطها الرسمية = MISSING | M14-042 | D-073 |  |  |  | NOT STARTED | NO |
| `INT-012` | نظام الكاشير (POS) = LATER / MISSING — ممنوع اختراع اسمه | M15-014, M19-031, M23-136 | D-077 (G3), D-102 (G3), D-121 |  |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `INT-013` | حقلا pos_item_id و external_item_id يبقيان nullable وفارغين | M18-032, M23-137 | D-077 (G3), D-102 (G3), D-121 | docs/phase-01-discovery/17-menu-data-model-draft.md |  |  | PARTIAL | YES |
| `INT-014` | عمود # ليس معرّفًا تجاريًا ولا يُستخدم في أي تكامل | M19-030 | D-102 (G3), D-120 (G3) |  |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `INT-015` | لا اتصال مباشر بقاعدة بيانات POS الآن | M15-051 | D-085 (G7) |  |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `INT-016` | جاهزية تكامل مستقبلي: POS / ERP / Customer App / Login | M15-049, M23-010, M23-128 | D-085 (G7), D-077 (G3), D-107, D-147 (G3) |  |  |  | NOT STARTED | NO |
| `CF-001` | البنية الحالية: www.shelterjo.com على Cloudways خلف Cloudflare | M01-002, M01-003, M01-004 | D-011 |  |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `CF-002` | Phase 01: ممنوع تغيير Cloudflare أو Cloudways أو DNS | M01-025, M01-026, M01-027 | D-002 (G1) |  |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `CF-003` | لا تغيير DNS (أو إعدادات Cloudflare Production) بدون موافقة الـOwner | M12-039 | GIO-§A13, D-051 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A13 §A28 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `CF-004` | مراجعة إعدادات Cloudflare | M01-194 | AC-06 | docs/phase-01-discovery/08-access-requests.md AC-06 |  |  | NOT STARTED | NO |
| `CF-005` | مراجعة إعدادات Cloudways | M01-196 | AC-11 | docs/phase-01-discovery/08-access-requests.md AC-11 |  |  | NOT STARTED | NO |
| `CF-006` | جاهزية Global CDN | M01-076 | D-010, D-011 |  |  |  | NOT STARTED | NO |
| `CF-007` | تجهيز قيمة DNS verification (TXT) لـSearch Console مسبقًا | M12-038 | GIO-§A13 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A13 §B |  |  | NOT STARTED | NO |
| `CF-008` | Cloudflare API للـSite Health: شرح الصلاحيات الدقيقة أولًا | M25-182 |  |  |  |  | PARTIAL | NO |
| `CF-009` | Cloudflare Token بأقل صلاحية — لا Global API Key | M25-183 | GIO-§A13, D-051 | docs/phase-01-discovery/08-access-requests.md AC-06 · docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §B |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `TEST-001` | بوابة QA قبل الإطلاق (M01 §92) | M01-225, M01-226, M01-227, M01-228, M01-229 |  |  |  |  | NOT STARTED | NO |
| `TEST-002` | Definition of Done: لا Requirement منتهية لمجرد كتابة الكود | M01-236, M27-145 |  | docs/FRONTEND-TOOLING.md §5 |  |  | NOT STARTED | N/A |
| `TEST-003` | التحقق من التنفيذ الحقيقي — لا افتراض من أسماء الملفات | M27-036 |  |  |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `TEST-004` | بوابة اكتمال الصفحة: UX Quality Gates + Final Responsive Gate | M24-052, M26-054, M26-058 |  | docs/FRONTEND-TOOLING.md §5 · docs/qa/RESPONSIVE-QA-MATRIX.md | tooling/playwright.config.mjs | tooling/tests/prototype/menu-wireframe.spec.mjs · tooling/tests/prototype/responsive.spec.mjs | PARTIAL | PROTOTYPE |
| `TEST-005` | الإصدارات الكبرى: Unlighthouse وsitespeed.io عند الحاجة | M24-053 |  |  |  |  | NOT STARTED | NO |
| `TEST-006` | RESPONSIVE-QA-MATRIX.md لكل صفحة رئيسية | M26-050, M26-051, M23-222 |  | docs/qa/RESPONSIVE-QA-MATRIX.md | tooling/scripts/qa-matrix.mjs |  | PARTIAL | PROTOTYPE |
| `TEST-007` | تغطية حالات وتدفقات المنيو في الاختبارات | M23-223, M24-022, M24-024 |  | docs/menu-ia/USER-FLOWS.md |  | tooling/tests/app/menu.spec.mjs · tooling/tests/prototype/menu-wireframe.spec.mjs | PARTIAL | PROTOTYPE |
| `TEST-008` | اختبار browser history وscroll restoration | M23-217, M24-023 | D-146, F-13 |  |  | tooling/tests/app/menu.spec.mjs | NOT STARTED | NO |
| `TEST-009` | اختبار Campaign banner وContact CTAs | M24-025 |  |  |  |  | NOT STARTED | NO |
| `TEST-010` | اختبار عبر المتصفحات | M24-026, M26-049 |  |  | tooling/playwright.config.mjs |  | PARTIAL | PROTOTYPE |
| `TEST-011` | لا اعتماد على المحاكي فقط — فحص سلوك حقيقي | M26-048 |  | docs/qa/RESPONSIVE-QA-MATRIX.md |  |  | NOT STARTED | NO |
| `TEST-012` | Screenshots للمراجعة البصرية ومقارنتها | M24-027 |  |  | tooling/playwright.config.mjs | tooling/tests/prototype/responsive.spec.mjs | PARTIAL | PROTOTYPE |
| `TEST-013` | اختبار الأداء في بيئة مخنوقة (throttled) | M23-186 |  | docs/menu-ia/PERFORMANCE-BUDGET.md §4 | tooling/scripts/lighthouse.mjs |  | PARTIAL | PROTOTYPE |
| `TEST-014` | اختبار User Journeys حقيقية بـPlaywright | M01-230, M12-059, M12-060, M12-061 | GIO-§A24/§A25/§A26 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §25 |  |  | NOT STARTED | NO |
| `TEST-015` | Debug Everything: أدوات ومعايير قبول التتبع | M12-057, M12-058, M12-062 | GIO-§A24/§A25/§A26, D-051 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §24–§25 |  |  | NOT STARTED | NO |
| `TEST-016` | Analytics Validation Report | M12-063 | GIO-§A24/§A25/§A26 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §26 |  |  | NOT STARTED | NO |
| `TEST-017` | FINAL GOOGLE ACCEPTANCE TEST | M12-074 | D-051 | docs/google/GOOGLE-ECOSYSTEM-CHECKLIST.md |  |  | NOT STARTED | NO |
| `TEST-018` | Chrome DevTools لفحص الأداء والمتصفح | M01-231, M01-019 |  |  |  |  | NOT STARTED | N/A |
| `TEST-019` | Code Review بعد كل Development Milestone | M01-232, M01-021 |  |  |  |  | NOT STARTED | N/A |
| `TEST-020` | الأدوات كحارس انحدار (regression guard) | M24-074 |  |  | tooling/package.json |  | PARTIAL | PROTOTYPE |
| `TEST-021` | اختبارات جاهزية الـDashboard | M25-194 |  |  |  |  | NOT STARTED | NO |
| `TEST-022` | سيناريوهات فشل الـDashboard | M25-195 |  |  |  |  | NOT STARTED | NO |
| `TEST-023` | إعادة الفحص الحقيقي للموقع الحالي بعد تفعيل AC-01 | M04-051, M04-052, M10-041, M10-042, M10-043 | D-028, D-041 | docs/phase-01-discovery/08-access-requests.md |  |  | NOT STARTED | NO |
| `TEST-024` | لا Load Testing ولا Crawling عدواني على Production | M10-054, M10-055 | D-044 |  |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `DEPLOY-001` | لا أي تغيير على Production بدون موافقة الـOwner | M01-028, M01-195, M02-007, M12-014, M12-078 | D-002, D-017, AC-RULE-03, D-051 | docs/phase-01-discovery/08-access-requests.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `DEPLOY-002` | مسموح بلا موافقة مسبقة: Configure · Build · Test · Prepare | M12-066 | GIO-§A28, D-051 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §28 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `DEPLOY-003` | التغييرات الحساسة: Summary ← موافقة ← تنفيذ | M12-067 | GIO-§A28, D-051 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §28 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `DEPLOY-004` | مسار Staging → Testing → Owner Approval → Production | M01-197 | AC-RULE-03, D-037 |  |  |  | NOT STARTED | NO |
| `DEPLOY-005` | Backup قبل Migration وDeployment وPlugin Updates والتغييرات الكبرى | M01-198 | D-037 |  |  |  | NOT STARTED | NO |
| `DEPLOY-006` | الموقع القديم: لا Update على Production الآن | M10-028 | D-037 | docs/governance/RISK-REGISTER.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `DEPLOY-007` | المرحلة الحالية: لا Production Deployment ولا تغيير للموقع الحي | M23-228, M23-229 | F-23, D-142 | docs/menu-ia/MENU-DECISION-REGISTER.md F-23 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `DEPLOY-008` | المرحلة الحالية: لا irreversible database migrations | M23-233 | F-23 |  |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `DEPLOY-009` | تغيير Production الخطر يحتاج Gate (Staging·Backup·Test·Approval·Rollback) | M27-124, M27-125 |  |  |  |  | PARTIAL | N/A |
| `DEPLOY-010` | Before Launch — Google Checklist | M11-112 | D-046 | docs/google/GOOGLE-ECOSYSTEM-CHECKLIST.md · docs/google/GOOGLE-ECOSYSTEM-POLICY.md §53 |  |  | NOT STARTED | NO |
| `DEPLOY-011` | الـCMS: لا تغيير على Production أثناء التحرير — فقط بعد Publish | M25-110 | D-148 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md §18 |  |  | NOT STARTED | NO |
| `DEPLOY-012` | Confirmation حسب الخطورة للعمليات الحساسة | M25-163, M25-089 |  |  |  |  | NOT STARTED | NO |
| `DEPLOY-013` | الحذف = أرشفة بدل hard delete | M25-164 | D-148 |  |  |  | NOT STARTED | NO |
| `DEPLOY-014` | خطأ محرر لا يمسح الموقع | M25-162 |  |  |  |  | NOT STARTED | NO |
| `MON-001` | Site Health — التوفر: Online · SSL · Domain · DNS · Cloudflare · Response time | M25-071 |  |  |  |  | NOT STARTED | NO |
| `MON-002` | Site Health — الأخطاء وملفات الزحف | M25-072, M25-073 |  |  |  |  | NOT STARTED | NO |
| `MON-003` | الـDashboard تجيب عن أسئلة صحة الموقع والتكاملات | M25-009, M25-010 |  |  |  |  | NOT STARTED | NO |
| `MON-004` | Error Center: JS · API · 404 · 500 · Failed Requests — بدون PII | M25-074 | D-044 |  |  |  | NOT STARTED | NO |
| `MON-005` | Periodic checks: Uptime · Broken pages · Performance · Sitemap · Robots | M25-184 |  |  | tooling/scripts/lighthouse.mjs |  | NOT STARTED | NO |
| `MON-006` | لا Aggressive crawling — جدول فحص منطقي | M25-185 | D-044 |  |  |  | NOT STARTED | N/A |
| `MON-007` | Alerts / Attention Needed داخل الـDashboard | M25-118 |  |  |  |  | NOT STARTED | NO |
| `MON-008` | لا noisy alerts ولا notification spam — ترتيب حسب الأولوية | M25-119, M25-188 |  |  |  |  | NOT STARTED | NO |
| `MON-009` | V1: الإشعارات داخل الـDashboard فقط | M25-186 |  |  |  |  | NOT STARTED | NO |
| `MON-010` | مستقبلًا: Email / WhatsApp / Push — فقط إذا اعتُمدت | M25-187 |  |  |  |  | NOT STARTED | N/A |
| `MON-011` | مراقبة ما بعد الإطلاق: Day 1 · 3 · 7 · 14 · 30 — المشروع لا ينتهي عند الـLaunch | M01-237, M01-238, M11-115 | GEP-§45/§54/§55, D-046 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §54 After Launch · §55 Traffic Drop Alert · docs/google/POST-LAUNCH-GOOGLE-CHECKLIST.md |  |  | NOT STARTED | NO |
| `MON-012` | هبوط الزيارات بعد الـMigration: لا افتراض للسبب — فحص منهجي ثم Diagnosis | M11-116 | GEP-§45/§54/§55 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §54 After Launch · §55 Traffic Drop Alert |  |  | NOT STARTED | N/A |
| `MON-013` | مستقبلًا: فحص دوري لتطابق GBP مع الموقع | M11-098 | GEP-§45/§54/§55, D-043 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §45 GBP Monitoring · docs/google/BRANCH-DATA-SYNC.md |  |  | NOT STARTED | N/A |
| `GOV-001` | الـOwner هو المرجع النهائي (Final Authority / Source of Truth) | M01-038, M11-008, M27-033 | GEP-§2, GEP-GOLDEN, D-047 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §2, §57 · docs/governance/DECISION-LOG.md (header) |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-002` | Source Priority وإجراء تعارض المصادر (A · B · Difference · Correction) | M11-009, M11-010, M11-011, M11-120, M11-125, M23-212 | D-047, GEP-§2, GEP-GOLDEN, D-048, D-060 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §2, §5 · docs/google/BRANCH-DATA-SYNC.md |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-003` | أي معلومة من الموقع القديم أو مصدر خارجي = PENDING OWNER VERIFICATION | M01-009, M01-039, M01-040 | D-000, D-003 | docs/phase-01-discovery/00-access-and-method.md §6 · docs/phase-01-discovery/04-content-approval-register.md |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-004` | OLD WEBSITE = INFORMATION SOURCE ONLY — إعادة بناء من الصفر | M01-005, M01-007 | D-000 | docs/governance/DECISION-LOG.md D-000 |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-005` | ترتيب السلطة لتحديد الحقيقة (Authority Order — M27 §42) | M27-153, M27-154 |  |  |  |  | PARTIAL | NO |
| `GOV-006` | المحادثة كاملة = مشروع واحد؛ لا يضيع أي Requirement (One Project Brain) | M27-001, M27-004, M27-005, M27-007, M27-156 |  |  |  |  | PARTIAL | NO |
| `GOV-007` | آخر قرار صريح يتقدم، والتصحيح اللاحق يلغي القديم (قاعدتا التعارض 1 و3) | M27-008, M27-010 | D-018, D-057, D-058 |  |  |  | PARTIAL | NO |
| `GOV-008` | قرار الـOwner فوق أي اقتراح؛ اقتراح Claude ليس قرارًا (قاعدتا 2 و5) | M27-009, M27-012 | DL-RULE-01 | docs/governance/DECISION-LOG.md §الحالات |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-009` | السكوت ليس موافقة (Silence ≠ Approval) | M01-043, M27-013, M14-053 | DL-RULE-01, CAR-RULE-01, D-060 | docs/governance/DECISION-LOG.md (header) · docs/phase-01-discovery/04-content-approval-register.md (header) · docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md (CT-06) · docs/phase-01-discovery/15-root-gateway-wireframe.md (RG-01) |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-010` | لا تغيير لقرار معتمد ولا إعادة فتح لقرار FROZEN بدون Conflict حقيقي (قاعدة 4) | M01-243, M27-011, M23-003, M25-219 | DL-RULE-02, D-142, F-23, D-030 | docs/menu-ia/MENU-DECISION-REGISTER.md §1 |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-011` | ممنوع حل التعارض بصمت (No Silent Conflict Resolution) | M27-006, M27-030, M23-237 | MDR-RULE-01 | docs/menu-ia/MENU-DECISION-REGISTER.md §4 |  |  | PARTIAL | NO |
| `GOV-012` | docs/CONFLICT-REGISTER.md وإجراء حسم التعارض | M27-027, M27-028 |  |  |  |  | NOT STARTED | NO |
| `GOV-013` | توثيق القرارات المستبدلة بدون حذف التاريخ (OLD → SUPERSEDED BY → Current) | M27-026 | D-045, D-088, D-108, D-123 | docs/governance/DECISION-LOG.md |  |  | NEEDS FIX | NO |
| `GOV-014` | Continuous Sync: كل Prompt جديد يُقارن تلقائيًا بالمرجع | M27-150 |  |  |  |  | NOT STARTED | NO |
| `GOV-015` | تفويض القرارات: التقني/UX لـClaude؛ Business/Content للـOwner | M23-007, M23-247, M25-217, M25-218, M27-014, M27-015 | D-142 | docs/menu-ia/MENU-DECISION-REGISTER.md §5 · docs/menu-ia/UX-VALIDATION.md §3 |  |  | PARTIAL | NO |
| `GOV-016` | عرض Options A / B / C مع أثر كل خيار ثم ترك الاختيار للـOwner | M01-082, M01-083 |  | docs/phase-01-discovery/05-decisions-before-design.md |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-017` | الفكرة الأفضل تُعرض ولا تُنفذ مباشرة (Better Idea Protocol) | M01-244, M14-008 | D-061 |  |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-018` | ترتيب الأولويات عند التعارض التقني — ولا يُستخدم لتغيير قرار تجاري | M01-245, M01-246 | D-004 |  |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-019` | معايير كل قرار في مرحلة المنيو + Simplicity wins | M23-256, M23-257 | D-142, D-147 | docs/menu-ia/UX-VALIDATION.md |  |  | IMPLEMENTED — NOT TESTED | PROTOTYPE |
| `GOV-020` | المشروع ليس Vibe Coding — Architecture أولًا | M01-011 | D-005, D-001 |  |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-021` | دور Claude: فريق خبراء متكامل وليس راسم شاشات | M01-010, M23-255 |  |  |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `GOV-022` | ممنوع تخمين أو اختراع أي Business data — الناقص يُعلَّم ولا يُملأ | M02-003, M14-001, M23-005, M25-064, M27-031 | D-003, D-017, D-081, F-23 | docs/phase-01-discovery/04-content-approval-register.md §B · docs/menu-ia/MENU-DECISION-REGISTER.md §3 |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-023` | الوسوم الموحدة: MISSING — OWNER INPUT REQUIRED / NEEDS OWNER VERIFICATION | M01-051, M03-061, M03-062 | D-017 |  |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-024` | مفردات حالة Business data (M27 §7) وتوحيدها مع حالات السجلات | M27-032 | CAR-RULE-01 |  |  |  | PARTIAL | NO |
| `GOV-025` | مسار اعتماد أي معلومة تظهر للزوار | M01-041 | D-003 | docs/phase-01-discovery/04-content-approval-register.md · docs/governance/APPROVED-ASSET-LIBRARY.md |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-026` | بوابة النشر: لا نشر بدون موافقة صريحة؛ فقط APPROVED قابل للنشر | M01-029, M01-042, M01-054 | CAR-RULE-01, F-23, D-003, D-002 | docs/phase-01-discovery/04-content-approval-register.md (header) · docs/menu-ia/MENU-DECISION-REGISTER.md F-23 |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-027` | Content Approval Register (الأعمدة والحالات) | M01-052, M01-053 | CAR-RULE-01, D-003 | docs/phase-01-discovery/04-content-approval-register.md |  |  | NEEDS FIX | NO |
| `GOV-028` | قائمة المعلومات التي تحتاج موافقة الـOwner (Phase 01 بند 5) | M01-254 | D-003 | docs/phase-01-discovery/03-verify-with-owner.md |  |  | NEEDS FIX | NO |
| `GOV-029` | تسلسل المشروع الإلزامي (36 مرحلة) | M01-030 | D-001 | docs/phase-01-discovery/README.md (Exit Criteria) |  |  | PARTIAL | N/A |
| `GOV-030` | بوابة المراحل: لا قفز قبل اعتماد المرحلة الحالية + إجراء كل مرحلة (10 خطوات) | M01-031, M01-241 | D-001 |  |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-031` | الأسئلة على مراحل ودفعات قصيرة مرتبة حسب الأثر — لا 50 سؤالًا دفعة واحدة | M01-239, M02-004, M02-009 | D-017 | docs/phase-01-discovery/07-question-backlog.md |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-032` | السؤال الحرج للـArchitecture يُطرح فورًا | M03-060 | D-017 |  |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-033` | اكتشاف النواقص ذاتيًا وسؤال الـOwner مباشرة (قائمة التغطية 27 مجالًا) | M02-002, M02-005, M02-008 | D-017 | docs/phase-01-discovery/07-question-backlog.md · docs/phase-01-discovery/08-access-requests.md |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-034` | لا إعادة سؤال عن معلومة معتمدة — البحث في المرجع أولًا | M04-057, M13-044, M27-128 | D-030, D-057 |  |  |  | PARTIAL | NO |
| `GOV-035` | الناقص/المعلق لا يوقف العمل غير المتأثر؛ السؤال فقط عند المنع | M10-056, M14-049, M15-015, M15-061, M18-011, M21-011, M23-006, M27-029, M27-134 | D-045, D-074, D-077, D-094, D-121, D-137 | docs/menu-ia/MENU-DECISION-REGISTER.md §2 |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-036` | صيغة مراجعات الـOwner: مجموعات مختصرة وفقط ما لا يمكن استنتاجه | M15-009, M18-009, M18-047, M18-049, M19-039, M21-012, M21-014 | D-108, D-123, D-126, D-132, D-137 | docs/phase-01-discovery/19-menu-p0-owner-review.md · docs/phase-01-discovery/20-menu-pre-v1-review.md |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-037` | لا يُطلب من الـOwner تعبئة قوالب أو إعادة كتابة معلومات موجودة في ملفاته | M15-003, M15-008, M17-001 | D-075 |  |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-038` | لا عمل يدوي على الـOwner إذا أمكن التنفيذ (استثناءات محددة) | M12-009, M12-068 | D-051 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A |  |  | NOT STARTED | N/A |
| `GOV-039` | PHASE 01 — DISCOVERY & OWNER INTERVIEW فقط وقيودها | M01-249, M01-250, M11-121 | D-002, D-017 | docs/phase-01-discovery/README.md · docs/phase-01-discovery/00-access-and-method.md |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-040` | بوابة كود الموقع: لا Coding/Framework قبل اعتماد الـArchitecture | M01-022, M01-023, M01-258, M03-064 | D-002, D-017, D-015, D-031, D-052 | docs/phase-01-discovery/README.md · docs/phase-01-discovery/10-url-architecture-draft.md · docs/phase-01-discovery/13-root-and-international-seo-plan.md |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-041` | تسلسل جولات Discovery (R1 → R2 → R2P → R2B → R3 → Menu) | M02-001, M03-063, M10-001, M10-057, M11-131, M14-048, M14-050, M15-001 | D-045, D-074 | docs/phase-01-discovery/07-question-backlog.md · docs/phase-01-discovery/README.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `GOV-042` | تحديد أهم القرارات قبل التصميم (Phase 01 بند 6) | M01-255 | D-001 | docs/phase-01-discovery/05-decisions-before-design.md |  |  | NEEDS FIX | N/A |
| `GOV-043` | أسئلة Phase 01: Pages · Nav · Homepage · Menu · Locations · Blog · Contact | M01-256 | D-014, D-015, D-020 | docs/phase-01-discovery/07-question-backlog.md |  |  | PARTIAL | N/A |
| `GOV-044` | تسلسل عمل المنيو (R3 → Inventory → Data Model → Menu IA → اعتماد → Menu UX/UI) | M15-007, M15-059, M15-089, M15-091, M18-001, M18-045, M18-050, M19-040, M20-033, M21-022 | D-088, D-108, D-123, D-134, D-135, D-141, D-142, D-143 | docs/phase-01-discovery/16-menu-intake-and-ssot.md · docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md · docs/menu-ia/README.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `GOV-045` | تطبيق القرارات على الـInventory مع إبقاء Source Data untouched | M19-001, M20-027 | D-092, D-133, D-134 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md · docs/phase-01-discovery/17-menu-data-model-draft.md |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-046` | Menu Data Model كامل قبل أي UI للمنيو | M15-052 | D-086, D-135, D-136 | docs/phase-01-discovery/17-menu-data-model-draft.md · docs/menu-ia/SHELTER-MENU-IA-SPEC.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `GOV-047` | بوابة Menu UI: لا Visual/Production UI قبل اعتماد IA + Wireframes | M15-058, M21-023, M21-043, M23-004, M23-227, M23-263 | D-141, D-142, D-143, F-23 | docs/menu-ia/README.md · docs/menu-ia/MENU-DECISION-REGISTER.md F-23 |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-048` | موجز M23 هو المرجع التنفيذي الأحدث لمرحلة MENU IA / UX / WIREFRAME | M23-001, M23-002 | D-142, MDR-RULE-01 | docs/menu-ia/MENU-DECISION-REGISTER.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `GOV-049` | مراحل عمل المنيو PHASE A → J + Phase A (التحقق) ومخرجها MENU-DECISION-REGISTER | M23-234, M23-235, M23-236, M23-261 | D-142 | docs/menu-ia/MENU-DECISION-REGISTER.md · docs/menu-ia/README.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `GOV-050` | تصحيح كل مشاكل P0 قبل عرض الـWireframes النهائية | M23-245 | D-142 | docs/menu-ia/UX-VALIDATION.md | tooling/viewports.mjs | tooling/tests/prototype/menu-wireframe.spec.mjs · tooling/tests/prototype/responsive.spec.mjs | TESTED | PROTOTYPE |
| `GOV-051` | صيغة العرض النهائي لمرحلة المنيو (18 بندًا — ليس تقريرًا نصيًا ضخمًا) | M23-252, M23-253 | D-142 | docs/menu-ia/README.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `GOV-052` | بعد عرض المنيو النهائي: توقف + موافقة + مراجعة خارجية قبل التصميم/الكود | M23-254, M23-264 | D-142 | docs/menu-ia/README.md |  |  | PARTIAL | N/A |
| `GOV-053` | Dashboard: Audit أولًا (لا بناء Blind) — SHELTER DASHBOARD AUDIT + Inventory | M25-167, M25-168, M25-169, M25-212 |  |  |  |  | NOT STARTED | NO |
| `GOV-054` | ترتيب تنفيذ الـDashboard الإلزامي PHASE A → N | M25-192, M25-211, M25-213 | D-001 |  |  |  | NOT STARTED | N/A |
| `GOV-055` | بوابة الـDashboard: لا كود قبل موافقة PHASE H على Architecture + Wireframes | M25-177, M25-193, M25-210, M25-215, M25-216 |  |  |  |  | NOT STARTED | N/A |
| `GOV-056` | وثائق الـDashboard الست (Architecture · Measurement · CMS · Health · Perms) | M25-197, M25-198, M25-199, M25-200, M25-201, M25-202 |  |  |  |  | NOT STARTED | NO |
| `GOV-057` | OWNER-DASHBOARD-GUIDE.md بسيط جدًا للـOwner | M25-203, M25-204 |  |  |  |  | NOT STARTED | NO |
| `GOV-058` | حدود عمل المطوّر: functionality · architecture · integrations · redesign | M25-208 |  |  |  |  | NOT STARTED | N/A |
| `GOV-059` | Google Ecosystem Policy إلزامية طوال المشروع وجزء من البنية من البداية | M11-001, M11-002, M11-003, M11-118, M11-122, M11-132 | D-046, GEP-GOLDEN | docs/google/GOOGLE-ECOSYSTEM-POLICY.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `GOV-060` | DO NOT CHANGE GOOGLE DATA WITHOUT OWNER APPROVAL | M11-119 | GEP-GOLDEN, D-043, D-046, D-051 | docs/google/GOOGLE-ECOSYSTEM-POLICY.md §44, §57 |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-061` | ملكية تنفيذ منظومة Google: Claude يربط ويضبط ويختبر ويوثق (ليس توصيات فقط) | M12-001, M12-002, M12-004, M12-005, M12-008, M12-075, M12-079, M13-017 | D-051, D-055 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md |  |  | NOT STARTED | NO |
| `GOV-062` | بوابة تنفيذ Google: فقط عند مرحلة التنفيذ وبعد منح الصلاحيات | M12-003 | D-051, GIO-§A22/§A23 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md (header) |  |  | IMPLEMENTED — NOT TESTED | NO |
| `GOV-063` | وثائق Google (M11 §56) + Checklist + قائمة الصلاحيات المطلوبة | M11-117, M11-123, M11-130 | D-046, D-049, D-050 | docs/google/GOOGLE-ECOSYSTEM-CHECKLIST.md · docs/google/GOOGLE-INTEGRATION-ARCHITECTURE.md · docs/phase-01-discovery/08-access-requests.md |  |  | PARTIAL | N/A |
| `GOV-064` | توثيق نهاية إعداد Google (M12 §30) لأي Developer لاحق | M12-070 | D-051 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §C |  |  | NOT STARTED | NO |
| `GOV-065` | DECISION LOG (Decision · Date · Reason · Owner Approval · Impact) | M01-242 | DL-RULE-01, DL-RULE-02 | docs/governance/DECISION-LOG.md |  |  | PARTIAL | N/A |
| `GOV-066` | docs/MASTER-DECISION-REGISTER.md (الأقسام الثمانية والحقول) | M27-024, M27-025 |  |  |  |  | NOT STARTED | NO |
| `GOV-067` | Full Conversation Audit: READ → AUDIT → … → PLAN → EXECUTE | M27-002, M27-003, M27-017, M27-151, M27-152, M27-157 |  |  |  |  | PARTIAL | NO |
| `GOV-068` | استخراج المتطلبات بنظام Traceable (IDs ثابتة PREFIX-NNN والحقول) | M27-018, M27-019, M27-020, M27-021 |  |  |  |  | PARTIAL | NO |
| `GOV-069` | Prompt Audit: تصنيف A–I وعدم تحويل الأسئلة/الأمثلة إلى متطلبات | M27-022, M27-023 |  |  |  |  | PARTIAL | NO |
| `GOV-070` | Existing Project Audit: WHAT I REQUESTED vs WHAT EXISTS وحالات التنفيذ | M27-034, M27-035 |  |  |  |  | PARTIAL | NO |
| `GOV-071` | ممنوع ادعاء الإنجاز: DONE / IMPLEMENTED / TESTED / VERIFIED فقط بعد تحقق فعلي | M27-149 |  |  |  |  | PARTIAL | NO |
| `GOV-072` | IMPLEMENTATION-GAP-ANALYSIS.md + قرار KEEP/…/REMOVE لكل Module | M27-037, M27-139, M27-140 |  |  |  |  | NOT STARTED | NO |
| `GOV-073` | SHELTER-WEBSITE-MASTER-REQUIREMENTS.md = SSOT بلا فقدان تفاصيل | M27-038, M27-039, M27-148 |  |  |  |  | NOT STARTED | NO |
| `GOV-074` | docs/PENDING-OWNER-INPUT.md (Pending Register) — فقط ما يحتاج الـOwner | M27-131, M27-132, M27-133, M23-262 |  |  |  |  | NOT STARTED | NO |
| `GOV-075` | REQUIREMENTS-TRACEABILITY-MATRIX.md (Requirement → … → Test) | M27-135 |  |  |  |  | NOT STARTED | NO |
| `GOV-076` | Implementation Plan واحدة للمشروع كامل — لا خطط متعارضة | M27-136, M27-137 |  |  |  |  | NOT STARTED | NO |
| `GOV-077` | تعريف الأولويات P0–P3 (لا استخدام عشوائي) | M27-138 |  |  |  |  | PARTIAL | N/A |
| `GOV-078` | No Duplicate Architecture — ONE SOURCE OF TRUTH | M27-141 |  | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §C · docs/FRONTEND-TOOLING.md §3 |  |  | PARTIAL | NO |
| `GOV-079` | CLAUDE.md / تعليمات المشروع: مراجع مختصرة للـSource of Truth + قواعد الـTooling | M27-129, M27-130, M24-070 |  |  |  |  | PARTIAL | NO |
| `GOV-080` | صيغة تقرير التدقيق: Executive Summary (A–M) + تعارضات + Blocking فقط | M27-146, M27-147, M27-158 |  |  |  |  | NOT STARTED | N/A |
| `GOV-081` | بوابة M27: لا Feature جديدة قبل اكتمال التدقيق؛ البوابات السابقة تبقى | M27-016, M27-126, M27-127 |  |  |  |  | PARTIAL | N/A |
| `GOV-082` | بعد الوثائق السبع: تنفيذ العمل الموافق عليه مع احترام البوابات | M27-155 |  |  |  |  | NOT STARTED | N/A |
| `GOV-083` | صيانة الموقع القديم مسار منفصل يُسجل في Risk Register ولا يشغل المشروع | M10-031 | D-037, RISK-01, RR-RULE-01 | docs/governance/RISK-REGISTER.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `GOV-084` | نطاق مهمة M24: Tooling + Quality Infrastructure فقط | M24-063 |  |  | tooling/package.json · design-system/tokens/tokens.template.json |  | IMPLEMENTED — NOT TESTED | N/A |
| `GOV-085` | docs/FRONTEND-TOOLING.md | M24-069 |  | docs/FRONTEND-TOOLING.md | tooling/package.json |  | IMPLEMENTED — NOT TESTED | N/A |
| `GOV-086` | Definition of Done — الموافقات المطلوبة قبل اعتبار الموقع Finished | M01-233, M01-234 |  | docs/FRONTEND-TOOLING.md §5 |  |  | NOT STARTED | NO |
| `CONTENT-001` | جرد محتوى الموقع القديم وتصنيفه KEEP/FIX/REMOVE/MISSING/VERIFY | M01-033, M01-037, M01-252, M01-253 | D-000, D-041 | docs/phase-01-discovery/01-current-website-inventory.md · docs/phase-01-discovery/00-access-and-method.md §1 |  |  | PARTIAL | NO |
| `CONTENT-002` | تحقق الـOwner قبل نشر Awards/Statistics/Claims/SEO texts/FAQ/Blog | M01-049 | D-003, D-038 | docs/phase-01-discovery/04-content-approval-register.md §B, §C |  |  | IMPLEMENTED — NOT TESTED | NO |
| `CONTENT-003` | لا يكتب Claude Facts عن SHELTER — الـAI ينظم ويعيد الصياغة ويقترح فقط | M27-117, M27-118 | D-003 |  |  |  | IMPLEMENTED — NOT TESTED | NO |
| `CONTENT-004` | لا Product copy من Claude ولا تخمين وصف/مكونات/حساسية/قيم غذائية | M15-032, M23-061, M23-232 | D-081, F-23, F-13 | docs/menu-ia/MENU-DECISION-REGISTER.md M-03, F-13 · docs/menu-ia/SHELTER-MENU-IA-SPEC.md |  |  | IMPLEMENTED — NOT TESTED | PROTOTYPE |
| `CONTENT-005` | القيمتان 2018 و«منذ 2022» = OLD OR INCORRECT — لا تُستخدمان أبدًا | M03-013, M04-003 | D-018 | docs/governance/DECISION-LOG.md D-018 · docs/phase-01-discovery/04-content-approval-register.md CR-009 |  |  | IMPLEMENTED — NOT TESTED | NO |
| `CONTENT-006` | أي صياغة تسويقية عن عدد السنوات أو تاريخ المناسبة تُعرض على الـOwner قبل النشر | M04-005, M10-035 | D-018, D-038 |  |  |  | IMPLEMENTED — NOT TESTED | NO |
| `CONTENT-007` | عدد السنوات يُحسب ديناميكيًا من سنة التأسيس 2019 وليس نصًا ثابتًا | M10-034 | D-038, D-018 |  |  |  | NOT STARTED | NO |
| `CONTENT-008` | ادعاءات الرئيسية القديمة = PENDING OWNER VERIFICATION ولا تُنقل | M10-032, M10-033 | D-038 | docs/phase-01-discovery/12-homepage-screenshots-audit.md §4 · docs/phase-01-discovery/04-content-approval-register.md §E |  |  | IMPLEMENTED — NOT TESTED | NO |
| `CONTENT-009` | ذكر مشغل الحلويات (Production / Pastry Facility) — مستقبلًا وبموافقة على الصياغة | M04-045, M04-046 | D-026 |  |  |  | NOT STARTED | N/A |
| `CONTENT-010` | تسميات قنوات التواصل للرقمين 0799338445 و0799530383 | M13-028, M13-033 | D-057, D-059 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md |  |  | NOT STARTED | N/A |
| `CONTENT-011` | نص زر واتساب (مبدئي): «راسلنا على واتساب» / «Message us on WhatsApp» | M14-012, M14-013 | D-063 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md |  |  | NOT STARTED | N/A |
| `CONTENT-012` | الرسالة المسبقة لواتساب حسب الفرع — مسودة فقط؛ اقتراح نص قصير وطبيعي AR/EN | M14-015, M14-016, M14-017 | D-064, D-058 | docs/phase-01-discovery/14-contact-architecture-and-whatsapp.md |  |  | NOT STARTED | N/A |
| `CONTENT-013` | نصوص الجذر RG-04 / RG-05 = DRAFT ONLY — Placeholder واضح في الـWireframe فقط | M14-025, M14-028 | D-068 | docs/phase-01-discovery/15-root-gateway-wireframe.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `CONTENT-014` | مرحلة Copy: 3 خيارات لكل نص (Minimal · Brand-led · SEO-aware) AR/EN | M14-026 | D-068 |  |  |  | NOT STARTED | N/A |
| `CONTENT-015` | لا حشو كلمات SEO ولا صياغة تبدو مولّدة بالذكاء الاصطناعي | M14-027 | D-068 |  |  |  | NOT STARTED | NO |
| `CONTENT-016` | صفحة Catering / B2B: لا نشر ولا خدمات من عند Claude قبل تفاصيل الـOwner | M14-030 | D-069 |  |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `CONTENT-017` | لا التباس في كلمة Events بين حملات SHELTER وخدمات Catering/B2B | M14-032 | D-070 |  |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `CONTENT-018` | اقتراح Naming نهائي AR/EN للمفهومين (Events/Campaigns و Catering/B2B) | M14-033 | D-070 |  |  |  | NOT STARTED | N/A |
| `CONTENT-019` | لا تُملّ المستخدم (DO NOT BORE THE USER) | M01-094 |  |  |  |  | NOT STARTED | NO |
| `CONTENT-020` | أسماء المنتجات الإنجليزية في العرض ALL CAPS | M23-044 | D-131 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md |  |  | NOT STARTED | PROTOTYPE |
| `CONTENT-021` | معيار التسمية: تصحيح الإملاء الواضح في normalized/display فقط | M23-153 | D-092, D-093, D-133, F-04 | docs/phase-01-discovery/17-menu-data-model-draft.md · docs/menu-ia/MENU-DECISION-REGISTER.md F-04 |  |  | FROZEN | NO |
| `CONTENT-022` | تصحيحات التسمية المعتمدة (FRAPPE · TURKISH SINGLE/DOUBLE · آيس شيكن …) | M23-154, M23-155, M23-156, M23-157, M23-158 | D-126, D-127, D-129, D-109, F-22 | docs/phase-01-discovery/21-menu-inventory-v1.0-freeze-report.md · docs/menu-ia/MENU-DECISION-REGISTER.md F-22 |  |  | FROZEN | NO |
| `CONTENT-023` | الاسم العربي لـICED SHAKEN SALTED CARAMEL بانتظار موافقة الـOwner | M23-159 | D-138 | docs/menu-ia/MENU-DECISION-REGISTER.md P-02 |  |  | NOT STARTED | N/A |
| `CONTENT-024` | لغة الـDashboard سهلة للمالك (عنوان واضح · Reason · Suggested) | M25-148 |  |  |  |  | NOT STARTED | NO |
| `I18N-001` | العربية أساسية والإنجليزية نسخة كاملة | M03-030, M03-031, M11-047 | D-014 | docs/phase-01-discovery/10-url-architecture-draft.md §3 |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `I18N-002` | جاهزية لغات مستقبلية — لا لغة ثالثة معتمدة | M01-074, M03-035 | D-014, D-031 | docs/phase-01-discovery/10-url-architecture-draft.md |  |  | NOT STARTED | N/A |
| `I18N-003` | RTL/LTR صحيح بلا hacks أحادية الاتجاه | M01-201, M26-044 | D-144 |  |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `I18N-004` | كل Responsive Layout يُختبر مرتين: AR RTL و EN LTR | M01-202, M26-043 |  |  | tooling/viewports.mjs | tooling/tests/prototype/menu-wireframe.spec.mjs · tooling/tests/prototype/responsive.spec.mjs | NOT STARTED | PROTOTYPE |
| `I18N-005` | المنيو ثنائي اللغة: RTL/LTR، أولوية الأسماء معكوسة بالإنجليزية | M23-009, M21-038, M23-043 | D-143, D-144, F-10, F-15, CF-03 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md · docs/phase-01-discovery/22-menu-information-architecture.md |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `I18N-006` | صيغة السعر: 3.50 د.أ (AR) · 3.50 JOD (EN) | M23-067 | R-08, F-01, D-089 | docs/menu-ia/MENU-DECISION-REGISTER.md R-08 |  | tooling/tests/prototype/menu-wireframe.spec.mjs | NOT STARTED | PROTOTYPE |
| `I18N-007` | RG-03: العربية أولًا بصريًا في الجذر (الأردن، Mobile) | M14-023 | D-067 | docs/phase-01-discovery/15-root-gateway-wireframe.md §2 |  |  | NOT STARTED | N/A |
| `I18N-008` | Language Switch في الـNavigation — دراسة وتصميم | M01-101 | D-067 | docs/phase-01-discovery/13-root-and-international-seo-plan.md §4 |  |  | NOT STARTED | N/A |
| `I18N-009` | أسماء المنتجات: لا ترجمة نهائية تلقائية — الاعتماد للـOwner وحده | M15-035, M15-081, M15-037 | D-082 | docs/phase-01-discovery/menu/menu-inventory-v1.0.csv |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `I18N-010` | قائمة الأصناف التي تحتاج اسمًا عربيًا (Suggested Arabic Name + Reason) | M15-036, M15-080, M15-082 | D-082, D-132 | docs/phase-01-discovery/18-official-menu-inventory-report.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `I18N-011` | الأسماء العربية من المصدر (152) = SOURCE-PROVIDED · PENDING OWNER REVIEW | M18-003, M18-004, M21-009, M21-010, M21-013, M23-161 | D-091, D-137, P-01, CF-03 | docs/menu-ia/MENU-DECISION-REGISTER.md P-01، CF-03 · docs/phase-01-discovery/menu/menu-inventory-v1.0.csv |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `I18N-012` | طبقات الاسم العربي: source ثابت · normalized/display = الاسم المعتمد | M20-002, M23-162, M23-163 | D-092, F-04, D-093 | docs/phase-01-discovery/menu/menu-inventory-v1.0.csv · docs/menu-ia/MENU-DECISION-REGISTER.md F-04 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `I18N-013` | أسماء عربية معتمدة (M20: G6 · G7 · G8 · G9) | M20-001, M20-019, M20-020, M20-021 | D-127, D-128, D-129, D-132 | docs/phase-01-discovery/menu/menu-inventory-v1.0.csv · docs/phase-01-discovery/18-official-menu-inventory-report.md |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `I18N-014` | الاسم العربي لـICED SHAKEN SALTED CARAMEL — معلّق | M21-016, M21-044 | D-138, P-02 | docs/menu-ia/MENU-DECISION-REGISTER.md P-02 |  |  | NOT STARTED | N/A |
| `I18N-015` | Dashboard RTL/LTR + Sidebar حسب الاتجاه (اقتراح) | M25-220, M25-142 |  |  |  |  | NOT STARTED | N/A |
| `TOOL-001` | كل أداة في مكانها — لا استخدام لمجرد التوفر | M01-014, M23-224 |  | docs/FRONTEND-TOOLING.md |  |  | PARTIAL | N/A |
| `TOOL-002` | خريطة مهارات/أدوات Claude المعتمدة والتحقق من توفرها | M01-013, M01-015, M01-016, M01-017, M01-020, M01-251, M22-001 |  |  |  |  | PARTIAL | N/A |
| `TOOL-003` | الأدوات المحلية المجانية مسموحة بلا موافقة مسبقة | M10-053, M23-226 | D-044, D-029, GEP-§50/§52 | docs/FRONTEND-TOOLING.md §3, §7 |  |  | IMPLEMENTED — NOT TESTED | N/A |
| `TOOL-004` | Toolchain صغيرة وقوية — أقل مجموعة أدوات بغرض واضح | M24-001 |  | docs/FRONTEND-TOOLING.md | tooling/package.json |  | IMPLEMENTED — NOT TESTED | N/A |
| `TOOL-005` | فحص البيئة قبل أي تثبيت | M24-064 |  |  | tooling/package.json |  | IMPLEMENTED — NOT TESTED | N/A |
| `TOOL-006` | لا أدوات/مكتبات مكررة | M24-065, M27-108 |  | docs/FRONTEND-TOOLING.md §1, §3 | tooling/package.json |  | TESTED | YES |
| `TOOL-007` | SHELTER FRONTEND TOOLING MATRIX (قبل أي تثبيت) | M24-060, M24-061, M24-066 |  | docs/FRONTEND-TOOLING.md §2 |  |  | TESTED | YES |
| `TOOL-008` | سياسة التثبيت: فقط المجموعة التقنية التي لا تحتاج قرارًا تجاريًا من الـOwner | M24-059, M24-062 |  | docs/FRONTEND-TOOLING.md §1 |  |  | TESTED | YES |
| `TOOL-009` | كل dependency لها تكلفة: تفضيل الـplatform وتوثيق كل إضافة | M24-057, M24-058 |  | docs/FRONTEND-TOOLING.md §7 |  |  | PARTIAL | N/A |
| `TOOL-010` | ضبط الأدوات وبناء بنية الجودة (scripts، a11y، perf، Storybook، tokens، images) | M24-067, M24-068 |  |  | tooling/package.json · tooling/playwright.config.mjs · tooling/scripts/ |  | PARTIAL | PROTOTYPE |
| `TOOL-011` | Health check وتقرير الحالة | M24-071 |  | docs/FRONTEND-TOOLING.md |  |  | PARTIAL | PROTOTYPE |
| `TOOL-012` | Tooling Registry لكل الأدوات المناقشة والمثبتة (ومهارات Claude) | M27-107, M27-109 |  | docs/FRONTEND-TOOLING.md §3 |  |  | PARTIAL | N/A |
| `TOOL-013` | Playwright — أداة الاختبار الأساسية (مثبتة) | M24-019, M01-018 |  |  | tooling/playwright.config.mjs · tooling/viewports.mjs | tooling/tests/prototype/menu-wireframe.spec.mjs · tooling/tests/prototype/responsive.spec.mjs · tooling/tests/app/menu.spec.mjs | TESTED | YES |
| `TOOL-014` | axe-core — فحص الوصولية الآلي (مثبت) | M24-028 |  |  |  | tooling/tests/prototype/menu-wireframe.spec.mjs | TESTED | YES |
| `TOOL-015` | Google Lighthouse — مثبت مع بوابات وأسباب جذرية | M24-031 |  |  | tooling/scripts/lighthouse.mjs |  | TESTED | YES |
| `TOOL-016` | Sharp — خط معالجة صور المنتجات (مثبت) | M24-040 |  |  | tooling/scripts/images.mjs |  | TESTED | YES |
| `TOOL-017` | Unlighthouse — قبل الإطلاق وفي مراحل QA الكبرى (بعد فحص التوافق) | M24-034, M24-035 |  | docs/FRONTEND-TOOLING.md §2 |  |  | NOT STARTED | NO |
| `TOOL-018` | sitespeed.io — مرحلة QA وعند الحاجة، ليس في كل build | M24-036, M24-039 |  | docs/FRONTEND-TOOLING.md §2 |  |  | NOT STARTED | NO |
| `TOOL-019` | shadcn/ui — أساس هندسي فقط، والشكل الافتراضي ممنوع | M24-002 |  | docs/FRONTEND-TOOLING.md §2 |  |  | NOT STARTED | NO |
| `TOOL-020` | Radix UI Primitives — الأساس السلوكي والوصولي (بلا تكرار) | M24-003, M24-004, M24-005 |  | docs/FRONTEND-TOOLING.md §2 |  |  | NOT STARTED | NO |
| `TOOL-021` | Tailwind CSS — إذا دعمه الـstack وبعد tokens العلامة | M24-006 |  | docs/FRONTEND-TOOLING.md §2 |  |  | NOT STARTED | NO |
| `TOOL-022` | Motion — مكتبة الحركة الأساسية (مشروطة بالـstack) | M24-007 |  | docs/FRONTEND-TOOLING.md §2 |  |  | NOT STARTED | NO |
| `TOOL-023` | Lenis — اختياري، ليس عامًا، وليس على المنيو إلا بإثبات | M24-011 |  | docs/FRONTEND-TOOLING.md §2 |  |  | NOT STARTED | NO |
| `TOOL-024` | Storybook — ورشة مكونات SHELTER Design System | M24-014 |  | docs/FRONTEND-TOOLING.md §6 |  |  | NOT STARTED | NO |
| `TOOL-025` | Lucide — نظام أيقونات واحد موحد | M24-017 |  | docs/FRONTEND-TOOLING.md §2 |  |  | NOT STARTED | NO |
| `TOOL-026` | React Aria — فقط عند حاجة فعلية | M24-045 |  | docs/FRONTEND-TOOLING.md §2 |  |  | NOT STARTED | N/A |
| `TOOL-027` | المجموعة النهائية المطلوبة (مشروطة بتوافق الـstack) | M24-072, M24-073 |  | docs/FRONTEND-TOOLING.md | tooling/package.json |  | PARTIAL | YES |
| `PRIV-001` | صفحة سياسة الخصوصية (Privacy Policy) معتمدة ضمن الصفحات | M03-045 | D-015 | docs/governance/DECISION-LOG.md D-015 · docs/phase-01-discovery/04-content-approval-register.md MI-017 |  |  | NOT STARTED | NO |
| `PRIV-002` | صفحة الشروط / Terms عند الحاجة القانونية | M03-046 | D-015 |  |  |  | NOT STARTED | N/A |
| `PRIV-003` | التحقق مع الـOwner قبل نشر أي معلومة قانونية أو معلومات خصوصية | M01-050 | D-003 | docs/phase-01-discovery/03-verify-with-owner.md VQ-20 |  |  | IMPLEMENTED — NOT TESTED | NO |
| `PRIV-004` | نموذج التوظيف: Data minimisation وسياسة خصوصية قبل الإطلاق | M03-050 | D-015, RISK-06 | docs/governance/RISK-REGISTER.md RISK-06 · docs/phase-01-discovery/03-verify-with-owner.md VQ-05 |  |  | NOT STARTED | NO |
| `PRIV-005` | جاهزية Consent: Cookie Consent · Analytics Consent · Marketing Consent | M01-213, M12-052 | GIO-§A22/§A23, D-061 | docs/google/GOOGLE-IMPLEMENTATION-OWNERSHIP.md §A22–§A23 |  |  | NOT STARTED | NO |
| `PRIV-006` | Google Consent Mode عند الحاجة | M12-053 | GIO-§A22/§A23 |  |  |  | NOT STARTED | NO |
| `PRIV-007` | لا تفعيل لأي Advertising Tracking بدون موافقة الـOwner | M12-054 | GIO-§A22/§A23 | docs/google/GTM-TAG-REGISTER.md |  |  | NOT STARTED | NO |
| `PRIV-008` | أي Tracking يراعي Privacy Policy · Cookie Policy · Consent | M12-055 | GIO-§A22/§A23 |  |  |  | NOT STARTED | NO |
| `PRIV-009` | لا نشر لتتبع يحتاج إفصاحًا قانونيًا قبل تحديث صفحات الخصوصية | M12-056 | GIO-§A22/§A23 |  |  |  | NOT STARTED | NO |
| `PRIV-010` | التقييمات والشهادات (القديمة وGBP): لا نقل بدون موافقة الـOwner ومراجعة الحقوق | M10-036, M10-037, M11-095 | D-039, GEP-§42 | docs/governance/DECISION-LOG.md D-039 · docs/google/GOOGLE-ECOSYSTEM-POLICY.md §42 |  |  | IMPLEMENTED — NOT TESTED | NO |
| `PRIV-011` | تسجيل استعلامات البحث بشكل Privacy-conscious بدون PII غير ضروري | M23-199, M23-201 | D-149 | docs/menu-ia/MENU-MEASUREMENT-PLAN.md · docs/menu-ia/MENU-DECISION-REGISTER.md CF-10 |  |  | NOT STARTED | NO |
| `PRIV-012` | ممنوع تخزين الموقع الدقيق للمستخدم (Location analytics) | M25-049 |  |  |  |  | NOT STARTED | NO |
| `PRIV-013` | تسجيل الأخطاء بدون تخزين PII | M25-075 |  |  |  |  | NOT STARTED | NO |
| `PRIV-014` | تقليل البيانات: لا نسخ GA4 raw data كاملة بدون سبب | M25-136 |  |  |  |  | NOT STARTED | NO |
| `UX-001` | Simplicity wins في كل قرار | M27-104 | D-004 | docs/menu-ia/SHELTER-MENU-IA-SPEC.md (المبادئ) · docs/menu-ia/UX-VALIDATION.md |  |  | PARTIAL | N/A |
| `UX-002` | مبادئ UX العالمية — الموقع سهل جدًا | M01-091, M01-092 |  | docs/menu-ia/UX-VALIDATION.md |  | tooling/tests/prototype/menu-wireframe.spec.mjs | PARTIAL | PROTOTYPE |
| `UX-003` | كل صفحة تجيب بسرعة عن أربعة أسئلة — Do not bore the user | M01-093 |  | docs/phase-01-discovery/12-homepage-screenshots-audit.md §6 |  |  | NOT STARTED | NO |
| `UX-004` | Friction Audit لكل Flow | M01-095, M27-105 |  | docs/menu-ia/UX-VALIDATION.md · docs/menu-ia/USER-FLOWS.md |  |  | PARTIAL | PROTOTYPE |
| `UX-005` | أولويات الـUX: clarity · speed · mobile · discoverability · a11y · consistency | M27-106 | D-004 | docs/menu-ia/UX-VALIDATION.md · docs/governance/DECISION-LOG.md D-004 |  |  | PARTIAL | N/A |
