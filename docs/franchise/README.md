# SHELTER COFFEE — FRANCHISE / PARTNERSHIP · Phase 1

> **المرجع التنفيذي:** مواصفة الـOwner (M29، 2026-10-01) + قرار "لوحة التحكم للـOwner فقط وبلا كود" (M30، مجمّد).
> **الحالة:** `PHASE 1 — PENDING OWNER REVIEW` (المحتوى والنصوص).
> **البناء (PHASE 4):** الصفحة ونموذج FR **TESTED محليًا** — مخفيان حتى نشر المحتوى (PO-030) واعتماد PF-02/PF-03 (انظر `docs/PROGRESS.md`).
> **لم يحدث أي مما يلي:** نشر، Redirect، حذف للصفحة القديمة، تغيير DNS، إزالة HubSpot، أو لمس قاعدة بيانات.
> **الصفحة القديمة لم تُستخدم كمصدر** (فقط للهجرة التقنية لاحقًا).

## الـ20 مخرجًا
| # | المخرج | المكان | الحالة |
|---|---|---|---|
| 1 | تأكيد قراءة مصادر الحقيقة | [`01`](01-SOURCES-CONFLICTS-MISSING.md) §1 | ✅. **Franchise Master غير متاح** (PF-01) |
| 2 | تجميع متطلبات الفرنشايز | `FRAN-xxx` في [`../SHELTER-WEBSITE-MASTER-REQUIREMENTS.md`](../SHELTER-WEBSITE-MASTER-REQUIREMENTS.md) (المجال 16) | ✅ |
| 3 | فحص التعارضات | [`01`](01-SOURCES-CONFLICTS-MISSING.md) §2 + [`../CONFLICT-REGISTER.md`](../CONFLICT-REGISTER.md) | ✅ |
| 4 | البيانات التجارية الناقصة | [`01`](01-SOURCES-CONFLICTS-MISSING.md) §3 + [`../PENDING-OWNER-INPUT.md`](../PENDING-OWNER-INPUT.md) | ✅ |
| 5 | الـIA الجديدة للصفحة | [`02`](02-PAGE-IA-AND-CONTENT.md) §1 | ✅ مسودة |
| 6 | هيكل المحتوى العربي | [`02`](02-PAGE-IA-AND-CONTENT.md) §2 | ✅ مسودة |
| 7 | هيكل المحتوى الإنجليزي | [`02`](02-PAGE-IA-AND-CONTENT.md) §3 | ✅ مسودة |
| 8–11 | Wireframes: موبايل وديسكتوب، عربي وإنجليزي | [`wireframes/`](wireframes/) | انظر README الـWireframes |
| 12 | استراتيجية الـCTA | [`02`](02-PAGE-IA-AND-CONTENT.md) §5 | ✅ |
| 13 | مسودة الـFAQ | [`02`](02-PAGE-IA-AND-CONTENT.md) §4 | ✅ (إجابات محايدة، والتجاري ⏳) |
| 14 | مصفوفة حقول طلب الشراكة | [`03`](03-APPLICATION-FIELD-MATRIX.md) | ✅ |
| 15 | IA وحدة الشراكات في الـDashboard | [`04`](04-DASHBOARD-MODULE-IA.md) | ✅ (Owner فقط في V1) |
| 16 | خطة هجرة الـSEO | [`05`](05-SEO-ANALYTICS-PERFORMANCE.md) §1 | ✅ |
| 17 | خطة التحليلات | [`05`](05-SEO-ANALYTICS-PERFORMANCE.md) §2 | ✅ |
| 18 | خطة الأداء | [`05`](05-SEO-ANALYTICS-PERFORMANCE.md) §3 | ✅ |
| 19 | مراجعة الوصولية | [`06`](06-REVIEW-AND-ISSUES.md) | بعد اختبار الـWireframes |
| 20 | مشاكل P0 / P1 / P2 | [`06`](06-REVIEW-AND-ISSUES.md) | بعد اختبار الـWireframes |

## القرارات التقنية المتخذة (بلا سؤال، حسب M29 §94)
| ID | القرار | السبب |
|---|---|---|
| TD-AC-01 | **Applications Core مشترك** للتوظيف والشراكات: الترقيم، والملاحظات، وتاريخ الحالة، والمواعيد، والمرفقات الخاصة، والـAudit، والفلاتر المحفوظة، والتفضيلات. **مع جداول تفاصيل منفصلة** لكل وحدة | يمنع ازدواج المعمارية (مصدر واحد) |
| TD-FR-01 | مراحل الـPipeline **جدول إعداد**، وليست Enum | Franchise Master قد يغيّرها بلا كود |
| TD-FR-02 | الترقيم `FR-YYYY-NNNNN` من الخادم | متسق مع `JOB-YYYY-NNNNN` |
| UX-F-01…03 | رحلتان منفصلتان ومترابطتان · ترتيب "أسواق النمو" · مواضع الـCTA والزر الثابت | `02` §1 |
