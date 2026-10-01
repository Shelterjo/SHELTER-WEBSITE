# PHASE 01 — DISCOVERY & OWNER INTERVIEW

**المشروع:** SHELTER COFFEE — Global Website Rebuild From Zero
**الحالة:** 🟡 In progress — R1 ✅ · R2 ✅ · R2P ✅ · PG ✅ · R2B موثقة (MISSING) · R3 قرارات ✅ · R3 ✅ · **MENU INVENTORY v1.0 = APPROVED BASELINE** (D-135) · Menu IA ✅ (D-143) · **⏳ مراجعة IA Spec + Wireframes في [`../menu-ia/`](../menu-ia/README.md)**
**آخر تحديث:** 2026-10-01

> لا Coding · لا Framework · لا Plugins · لا تغييرات Cloudflare/Cloudways/DNS · لا نشر.
> لا معلومة ولا صورة معتمدة بدون موافقة الـOwner الصريحة.

## الملفات

| # | الملف | المحتوى |
|---|---|---|
| 00 | [`00-access-and-method.md`](00-access-and-method.md) | منهجية الفحص، حدود الوصول، الأدوات المتاحة، الموارد المستهلكة، ما يفتح بقية الفحص |
| 01 | [`01-current-website-inventory.md`](01-current-website-inventory.md) | جرد الموقع الحالي: الخلاصة، التقنية، الصفحات، Technical SEO، خط الأساس للـSEO، الحضور الخارجي، تصنيف KEEP/FIX/REMOVE/MISSING/VERIFY |
| 02 | [`02-url-inventory-and-migration-seed.md`](02-url-inventory-and-migration-seed.md) | بذرة خريطة نقل الروابط (301) |
| 03 | [`03-verify-with-owner.md`](03-verify-with-owner.md) | 27 تعارضًا/سؤالًا مرتبة P0 / P1 / P2 |
| 04 | [`04-content-approval-register.md`](04-content-approval-register.md) | سجل اعتماد المحتوى: 112 معلومة وُجدت + 27 معلومة مفقودة + الادعاءات |
| 05 | [`05-decisions-before-design.md`](05-decisions-before-design.md) | القرارات المطلوبة قبل التصميم مع خيارات A/B/C |
| 06 | [`06-owner-interview-round-1.md`](06-owner-interview-round-1.md) | الجولة الأولى (محدّثة): أساس العلامة، النطاق العالمي، الجمهور، الصفحات — 10 أسئلة |
| 07 | [`07-question-backlog.md`](07-question-backlog.md) | كل الجولات R2–R12 + R2B (التوصيل) مرتبة حسب الأثر على الـArchitecture، والمواضيع المؤجلة بقرارك |
| 08 | [`08-access-requests.md`](08-access-requests.md) | طلبات الصلاحيات (13 خدمة) — لكل خدمة: لماذا، المستوى، ماذا نفحص، المخاطرة |
| 10 | [`10-url-architecture-draft.md`](10-url-architecture-draft.md) | مسودة الـURL Architecture: شجرة الروابط الكاملة + مقارنة ترتيب اللغة/الدولة (P1–P4) + محاكاة النقل للدومين العالمي |
| 11 | [`11-social-accounts-verification.md`](11-social-accounts-verification.md) | الحسابات التي نعتقد أنها رسمية — تُؤكد حسابًا حسابًا |
| 12 | [`12-homepage-screenshots-audit.md`](12-homepage-screenshots-audit.md) | جرد الرئيسية الحالية من لقطات الـOwner: الـHeader، الأقسام، الـFooter، الادعاءات، الصور، ملاحظات UX/Accessibility، إضافات WordPress، ملاحظة أمنية |
| 13 | [`13-root-and-international-seo-plan.md`](13-root-and-international-seo-plan.md) | دراسة الجذر `/` (A/B/C/D) + خطة canonical وhreflang وx-default + تحويلات النقل + `/menu` + نقل الدومين العالمي + أثر Slugs الفروع |
| 14 | [`14-contact-architecture-and-whatsapp.md`](14-contact-architecture-and-whatsapp.md) | بنية التواصل حسب النية + مقترح زر واتساب (مكان، نص، رسالة مسبقة) |
| 15 | [`15-root-gateway-wireframe.md`](15-root-gateway-wireframe.md) | Wireframe ومحتوى مقترح للجذر `/` (Global Brand Gateway / x-default) |
| 16 | [`16-menu-intake-and-ssot.md`](16-menu-intake-and-ssot.md) + [`templates/SHELTER-MENU-INTAKE-TEMPLATE.xlsx`](templates/SHELTER-MENU-INTAKE-TEMPLATE.xlsx) | R3: ما نحتاجه لاستلام المنيو الرسمي، القالب، خيارات المصدر الوحيد (SSOT) |
| 17 | [`17-menu-data-model-draft.md`](17-menu-data-model-draft.md) | Menu Data Model **v0.4**: Source / Normalized / Display · Source Lineage · إجراء الدمج · Pending Products · price_fils · Menu Versioning |
| 18 | [`18-official-menu-inventory-report.md`](18-official-menu-inventory-report.md) + [`menu/`](menu/) | تقرير ملف المنيو الرسمي (11 بندًا): 11 فئة · 192 صنفًا · المكرر · التسمية · الأسماء العربية · الأسئلة · المعرّفات. النسخة المنظمة `SHELTER-MENU-INVENTORY-v1.0.xlsx` (v1.0 — FROZEN) + CSV + نسخة مطابقة من الملف الأصلي |
| 19 | [`19-menu-p0-owner-review.md`](19-menu-p0-owner-review.md) | أسئلة P0 للمنيو (✅ أُجيبت) + فصل: Source errors · Possible corrections · Possible duplicates · Missing business information |
| 20 | [`20-menu-pre-v1-review.md`](20-menu-pre-v1-review.md) | مراجعة ما قبل v1.0 (✅ مغلقة) |
| 21 | [`21-menu-inventory-v1.0-freeze-report.md`](21-menu-inventory-v1.0-freeze-report.md) + `menu/SHELTER-MENU-INVENTORY-v1.0.xlsx` | **تقرير التجميد** (✅ APPROVED BASELINE): 11 فئة · 191 صنفًا · `PRD-00001→00192` · ما بقي MISSING أو بانتظار التحقق |
| 22 | [`22-menu-information-architecture.md`](22-menu-information-architecture.md) | **Menu IA:** Options A / B / C · مقارنة 9 معايير · إجابات 15 سؤالًا · التوصية C · قرارات IA-01 → IA-08 |
| 09 | [`09-architecture-options-after-r1.md`](09-architecture-options-after-r1.md) | مقترحات بعد الجولة 1: اللغة/السوق/الدومين (AR-01)، نموذج المواقع، ترتيب الـCTA، أثر الجمهور، النشرة، جاهزية الطلب أونلاين |

## الحوكمة

| الملف | المحتوى |
|---|---|
| [`../governance/DECISION-LOG.md`](../governance/DECISION-LOG.md) | سجل القرارات المعتمدة والمقترحة |
| [`../governance/APPROVED-ASSET-LIBRARY.md`](../governance/APPROVED-ASSET-LIBRARY.md) | مكتبة الأصول المعتمدة (فارغة — لا أصول معتمدة بعد) |
| [`../governance/RISK-REGISTER.md`](../governance/RISK-REGISTER.md) | سجل المخاطر (الموقع القديم، الروابط السبام، الزيارات، NAP…) |
| [`../google/`](../google/GOOGLE-ECOSYSTEM-POLICY.md) | منظومة Google: السياسة، ملكية التنفيذ، الـChecklist، مصدر الحقيقة للفروع، Branch Data Sync، Search Console Baseline، GA4، GTM، خريطة النقل، ما بعد الإطلاق |

## بوابة الخروج من Phase 01 (Exit Criteria)

- [x] ردود الـOwner على الجولة الأولى (R1-01 → R1-10) — توضيح R1-03a مفتوح
- [x] اختيار مبدئي لبنية اللغة/السوق/الدومين: Option C (D-019)
- [ ] اعتماد شجرة الروابط النهائية وترتيب اللغة/الدولة (`10`)
- [ ] ردود الجولات R2 (الفروع والتواصل) و R2B (التوصيل) و R3 (المنيو)
- [ ] قرار الـOwner على طلبات الصلاحيات AC-01 → AC-04
- [ ] حسم بنود P0 في `03-verify-with-owner.md`
- [x] اعتماد قائمة الصفحات الأساسية (R1-09 → D-015) — المؤجل: الحجز، الرعايات، الفريق، النشرة
- [ ] اعتماد استراتيجية اللغة والروابط (DB-02، DB-03) — المبدأ ✅ D-014
- [ ] (موصى به) فتح الوصول للموقع لإكمال جرد Navigation/Footer/النصوص/الصور/الأداء
- [x] استلام ملف المنيو الرسمي ← تقرير الـ11 بندًا (`18`)
- [x] أجوبة P0 (D-109 → D-123)
- [x] Menu Inventory v1.0 + تجميد المعرّفات (D-134، `21`)
- [x] موافقتك على تقرير التجميد (D-135)
- [ ] اختيار الـMenu Architecture (IA-01 → IA-08 في `22`) ← مواصفات IA تفصيلية ← Wireframes
- [x] اعتماد مجموعات الأسماء G1 → G9 وC1 وC2 (D-126 → D-132)
- [ ] الأسماء العربية الـ152 من الملف (OV-1) — ليست مانعًا لـIA

بعدها ننتقل إلى: **Business Requirements → Content Discovery → Information Architecture**.
