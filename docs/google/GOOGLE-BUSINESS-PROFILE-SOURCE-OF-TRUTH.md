# GOOGLE BUSINESS PROFILE — SOURCE OF TRUTH (الفرعان) — `SUPERSEDED BY MASTER DATA HUB (M33)`

> ⚠️ **SUPERSEDED BY M33 (2026-10-01) — المصدر الآن [`MASTER-DATA-HUB`](../MASTER-DATA-HUB.md):**
> - **D-048 ("GBP = Official Operational Source") → `SUPERSEDED`.** المصدر التشغيلي الوحيد لكل المعلومات العامة = **SHELTER MASTER DATA HUB**. **GBP قناة تُزامَن من الـMaster**، لا مصدر (M33 §23، MDH-002، G15-CF-01).
> - **D-047 (ترتيب المصادر) يبقى فقط** لتقييم القيم التي **لم يعتمدها الـOwner بعد** (مثل هذا الجدول أثناء Discovery). بعد دخول القيمة الـHub معتمدة، تصبح هي المرجع وGBP نسخة.
> - **التغيير على GBP:** نشر الـOwner في الـDashboard بعد معاينة أثر تذكر GBP = موافقته (المعاينة = Proposal، والنشر = Approval — G15-CF-02، MASTER-DATA-HUB §15). الاسم والعنوان والـPin يدوية (`MANUAL ACTION REQUIRED`).
> - **أي اختلاف** بين GBP والـMaster = `OUT OF SYNC` و**CONFLICT DETECTED** (M38 §7) ← قرار الـOwner: Keep Master · Adopt · Review. **لا اعتماد تلقائي** لقيمة Google أبدًا (MASTER-DATA-HUB §10).
> - **مفردات الحالة:** `SYNCED` · `PENDING` · `FAILED` · `NOT SUPPORTED` · `MANUAL ACTION REQUIRED` · `OUT OF SYNC` (MASTER-DATA-HUB §11).
> - **ما يلي سجل Discovery تاريخي يُحفظ كما هو**، ويُستخدم كلقطة اختلافات أولية فقط (MASTER-DATA-HUB §14 الخطوة 3). لا قيمة فيه تتغير بهذا البانر.

> **D-048** _(تاريخي — `SUPERSEDED` بـM33)_: الـGBP الرسمي لكل فرع = **OFFICIAL OPERATIONAL SOURCE** للحقول أدناه — **لكنه لا يتغلب على بيانات الـOwner المعتمدة** (§5 من السياسة).
> **الحالة:** بيانات الـGBP **لم تصلنا بعد** (G-01 و G-02). لا تعديل على أي ملف **بدون موافقة الـOwner** (§44 من السياسة): أي تغيير = Proposal → Owner Approval → Change، وبعد الموافقة ومنح الصلاحية ينفّذه الفريق (D-051، D-055). "لا تعديل للملف" في D-043 = قيد مرحلة الاستكشاف بدون موافقة. _(آلية الموافقة محدّثة: انظر البانر أعلاه.)_
> **آخر تحديث:** 2026-10-01

## SHELTER COFFEE DRIVE — شلتر كوفي درايف

| الحقل | Owner-approved | GBP الرسمي | الموقع القديم | الـSchema القديمة | الحالة |
|---|---|---|---|---|---|
| Official branch name | `SHELTER COFFEE DRIVE` / `شلتر كوفي درايف` (D-020) | MISSING | "شلتر كافيه" (عام) | اسم غير مكشوف | ⏳ بانتظار GBP |
| Address | وصف: "إربد — بجانب صالة قصر النخيل / منطقة أرابيلا" (ليس صياغة نهائية) | MISSING | "شارع عوض رشيدات" / "شارع الجامعة" (متعارض) | **بدون عنوان** (خطأ Semrush #45) | ⏳ |
| Map pin / Coordinates | — | MISSING | `locations.kml` (غير مقروء) | غير مكشوف | ⏳ |
| Google Maps URL | MISSING (D-020) | MISSING | `share.google/Cko3RPFoBGY21bco4` (غير مؤكد) | — | ⏳ |
| Opening hours | السبت–الخميس 07:00 ص – 02:00 ص · الجمعة 08:00 ص – 02:00 ص (D-020) | MISSING | "7:00 ص – 2:00 ل" | غير مكشوف | ⏳ مقارنة |
| Special hours | نظام D-021 | MISSING | — | — | ⏳ |
| Phone shown publicly | **0799009436** (D-057، D-060) | MISSING | 0799009436 | غير مكشوف | ⏳ مقارنة مع GBP — أي اختلاف = `OUT OF SYNC` / CONFLICT DETECTED ← قرار الـOwner |
| Website URL | — | MISSING (ملاحظة: الـKnowledge Panel يشير لـ`shelterjo.com/` بدون www) | — | — | ⏳ |
| Business category | — | MISSING | — | — | ⏳ |
| Business status | فرع عام نشط (D-008) | MISSING | — | — | ⏳ |

## SHELTER COFFEE HOUSE — شلتر كوفي هاوس

| الحقل | Owner-approved | GBP الرسمي | الموقع القديم | الـSchema القديمة | الحالة |
|---|---|---|---|---|---|
| Official branch name | `SHELTER COFFEE HOUSE` / `شلتر كوفي هاوس` (D-020) | MISSING | "شلتر كافيه — إربد ستي سنتر" | غير مكشوف | ⏳ |
| Address | Irbid City Center، الطابق الأول، بجانب البنك الإسلامي الأردني (D-020) — **اسم المول الرسمي يُؤخذ من GBP** | MISSING | الطابق الأول / الثاني (متعارض) | بدون عنوان | ⏳ |
| Map pin / Coordinates | — | MISSING | — | — | ⏳ |
| Google Maps URL | MISSING | MISSING | `share.google/d7T2jt7BKhidMHG4A` (غير مؤكد) | — | ⏳ |
| Opening hours | السبت–الأربعاء 09:00 ص – 10:00 م · الخميس–الجمعة 09:00 ص – 11:00 م (D-020) | MISSING | متعارض (الجمعة 9 ص أو 2 ظ) | — | ⏳ مقارنة |
| Special hours (بما فيها ساعات المول) | نظام D-021 — أي تعارض مع المول يُعرض | MISSING | — | — | ⏳ |
| Phone shown publicly | **0799009436** (D-057، D-060) | MISSING | — | — | ⏳ مقارنة مع GBP — أي اختلاف = `OUT OF SYNC` / CONFLICT DETECTED ← قرار الـOwner |
| Website URL | — | MISSING | — | — | ⏳ |
| Business category | — | MISSING | — | — | ⏳ |
| Business status | فرع عام نشط (D-008) | MISSING | — | — | ⏳ |

## مواقع غير عامة (لا GBP عام)
- **مشغل الحلويات** — NON-PUBLIC LOCATION (D-026). إذا كان له ملف على Google أصلًا، نخبرك ولا نتصرف.
