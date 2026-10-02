## تقييم ما هو موجود: KEEP / IMPROVE / REFACTOR / REPLACE / REMOVE

> لا موقع ولا كود تطبيق بعد. الموجود وثائق وبيانات ونماذج وأدوات جودة. التقييم يخص هذه فقط.

| Module | الحكم | السبب | الإجراء |
|---|---|---|---|
| `docs/governance/DECISION-LOG.md` | **KEEP** + IMPROVE | سجل زمني صحيح لـD-000…D-149 | **دوره:** سجل زمني Append-only. **الحالة الحالية لكل قرار** في `MASTER-DECISION-REGISTER.md`. أُضيفت قرارات الـOwner الجديدة D-150+ |
| `docs/menu-ia/MENU-DECISION-REGISTER.md` | **KEEP** | تفاصيل المنيو (F/P/M/CF/R) | مُفهرس في السجل الرئيسي. أُضيف R-09 |
| `docs/phase-01-discovery/05-decisions-before-design.md` (DB-xx) + جدول DB في الـDECISION-LOG | REFACTOR | حالة DB-xx مكررة في مكانين | الحالة في `MASTER-DECISION-REGISTER.md` فقط. ملف `05` يبقى لتحليل الخيارات |
| `03-verify-with-owner.md` · `07-question-backlog.md` · `04-content-approval-register.md` (قوائم معلقة) | REFACTOR | 416 بندًا معلقًا موزعة على عدة ملفات، وبعضها أُجيب ولم يُحدّث | **قائمة الأسئلة المفتوحة الوحيدة:** `PENDING-OWNER-INPUT.md`. ملف `04` يبقى سجل اعتماد المحتوى (APPROVED فقط يُنشر) |
| `docs/google/GA4-MEASUREMENT-PLAN.md` + `docs/menu-ia/MENU-MEASUREMENT-PLAN.md` + المطلوب `ANALYTICS-MEASUREMENT-PLAN.md` (Dashboard) | REFACTOR (P04) | خطر 3 خطط قياس | في P04: **`ANALYTICS-MEASUREMENT-PLAN.md` = المعجم الوحيد للأحداث على مستوى الموقع** (يُدمج فيه GA4-MEASUREMENT-PLAN)، وخطة المنيو تفصيل تابع. انظر سجل التعارضات |
| `18-official-menu-inventory-report.md` | IMPROVE | لافتته ما زالت "DRAFT v0.2 · PROVISIONAL" | توضيح أنه تاريخي، والمرجع `21` (v1.0 APPROVED BASELINE) |
| `22-menu-information-architecture.md` | KEEP (تاريخي) | حُسم بالموجز (D-142، D-143) | لا تغيير |
| `docs/phase-01-discovery/menu/` (المصدر + v1.0 + CSV) | **KEEP — FROZEN** | المصدر لا يُعدّل، وv1.0 خط أساس معتمد | لا تغيير |
| `docs/menu-ia/*` (Spec، Flows، Validation، Budget، Measurement، A11y) | KEEP | مخرجات المرحلة | بانتظار اعتماد الـOwner |
| `docs/menu-ia/wireframes/` (30 HTML + PNG + أدوات) | KEEP + IMPROVE | **نموذج تعاقدي** تُختبر عليه قواعد الـIA | أُصلحت 4 مشاكل (تباين، 11px، التركيز، R-09). لاحقًا: نقل أدواتها لـ`tooling/` |
| `docs/menu-ia/evidence/hours_logic_check.py` | KEEP | مرجع خوارزمية الساعات (12/12) | يُنقل كاختبارات وحدة عند البناء |
| `tooling/` | KEEP | بنية الجودة | الأدوات المعتمدة على React تنتظر DB-08 |
| `design-system/` · `media/` | KEEP (هيكل) | لا قيم ولا صور معتمدة | تُملأ بعد M-10 وصور الـOwner |
| `docs/google/*` | KEEP | سياسة الـOwner وخطط التنفيذ | يوجد اختلاف أسماء ملفات بين رسالتين ← خريطة أسماء في سجل التعارضات. لا ملفات جديدة مكررة |
| `docs/phase-01-discovery/01, 02, 11, 12` (جرد الموقع القديم) | KEEP | مصدر معلومات فقط | كل محتواها `PENDING OWNER VERIFICATION` |

## ازدواجية المعمارية (هدف: مصدر حقيقة واحد)

| ما وُجد | الحكم | المصدر الواحد المعتمد |
|---|---|---|
| ثلاثة سجلات قرارات (Log، Menu Register، Master) | **ليست ازدواجية** إذا ثُبتت الأدوار | Log = تاريخ · Master = الحالة الحالية · Menu Register = تفاصيل المنيو |
| قوائم أسئلة معلقة في 4 ملفات | **ازدواجية حقيقية** ← أُصلحت | `PENDING-OWNER-INPUT.md` |
| 3 خطط قياس محتملة | ازدواجية محتملة ← قرار | `ANALYTICS-MEASUREMENT-PLAN.md` (في P04) |
| ملفات المنيو (المصدر + v1.0 + CSV) | ليست ازدواجية (طبقات Source / Normalized) | المصدر لا يُعدّل، والعرض من v1.0 |
| GA4 / GTM / Site Kit في الموقع القديم | **غير معروف** | لا إنشاء لأي Property أو Container قبل جرد الموجود (AC-01 غير مفعّل) |
| نظام تصميم | لا يوجد بعد | `design-system/` (Tokens ← Pages). **ممنوع** الشكل الافتراضي لـshadcn |
| أداتان لتشغيل Playwright (عامة + `tooling/`) | ازدواجية خفيفة، نفس الإصدار | `tooling/` للاختبارات، والعامة لأدوات الـWireframes مؤقتًا |
