# 21 — MENU INVENTORY v1.0 — Freeze Report

> **الحالة:** ✅ **v1.0 صادرة · المعرّفات مجمّدة** (D-134) · ⏳ **بانتظار موافقتك قبل Menu IA** · **آخر تحديث:** 2026-10-01
>
> **الملفات:**
> - [`menu/SHELTER-MENU-INVENTORY-v1.0.xlsx`](menu/SHELTER-MENU-INVENTORY-v1.0.xlsx)
> - [`menu/menu-inventory-v1.0.csv`](menu/menu-inventory-v1.0.csv)
> - المصدر الأصلي (بلا تعديل): [`menu/source/OWNER-OFFICIAL-MENU-2026-10-01.xlsx`](menu/source/OWNER-OFFICIAL-MENU-2026-10-01.xlsx)
>
> **Menu Version:** `MV-2026-10-01` · ACTIVE · سارية من 2026-10-01 · السعر `price_fils` · JOD · شامل الضريبة.
> **Data Model:** [`17`](17-menu-data-model-draft.md) v0.4.

| البند | النتيجة |
|---|---|
| **Final Category Count** | **11** |
| **Final Product Count** | **191** صنفًا فعالًا من الملف الرسمي |
| **Frozen Product ID range** | **`PRD-00001` → `PRD-00192`**<br>`PRD-00120` = **RETIRED — MERGED INTO `PRD-00115`** (محفوظ، لا يُنشر، لا يُعاد استخدامه)<br>المعرّف التالي لأي صنف جديد: **`PRD-00193`** |
| **Frozen Category IDs** | `CAT-001` HOT DRINKS · `CAT-002` COLD DRINKS · `CAT-003` FIZZY DRINKS · `CAT-004` MILKSHAKE · `CAT-005` SMOOTHIES · `CAT-006` FRAPPE · `CAT-007` TEA · `CAT-008` SPECIALITY COFFEE · `CAT-009` SPRING · `CAT-010` CAKE · `CAT-011` COOKIES |

## أصناف تُباع وليست في الملف الرسمي

ليست ضمن المعرّفات المجمّدة.

| # | الصنف | الحالة |
|---|---|---|
| PND-001 | Cold Brew | `ACTIVE PRODUCT — MISSING CURRENT MENU DATA` |
| PND-002 | Lotus Cheesecake | `ACTIVE PRODUCT — MISSING CURRENT MENU DATA` |
| PND-003 | Ice Cream | `ACTIVE PRODUCT — MISSING CURRENT MENU DATA` |
| PND-004 | Single Espresso | `ACTIVE PRODUCT — MISSING CURRENT MENU DATA` |
| PND-005 | Snacks | `STATUS UNKNOWN — OWNER VERIFICATION REQUIRED` |
| PND-006 | Pastries | `STATUS UNKNOWN — OWNER VERIFICATION REQUIRED` |

- **لإنشاء أي صنف منها يلزم:** الاسم الرسمي بالإنجليزي، والسعر شامل الضريبة، والفئة.
- **عند الإنشاء:** يأخذ المعرّف `PRD-00193` وما بعده، ويُسجّل كإضافة (ADDED) في سجل التغييرات.

## Remaining MISSING business fields

| الحقل | النطاق |
|---|---|
| التوفر في DRIVE / HOUSE | 191 × 2 = `UNKNOWN` |
| الوصف · المكونات · الحساسية · السعرات | 191 |
| الصور | 191 |
| أرقام الكاشير / ERP | 191 (LATER) |
| تواريخ موسم SPRING | 5 (لا تمنع العرض) |
| وحدة سعر الكيك والكوكيز (قطعة أم شريحة) | 26 |
| بيانات الأصناف الأربعة خارج الملف | PND-001 → PND-004 |

## Remaining OWNER VERIFICATION items

| # | البند | النطاق |
|---|---|---|
| OV-1 | **الأسماء العربية الموجودة في الملف ولم تُعتمد بعد للعرض** (D-091). يمكن اعتمادها دفعة واحدة كما هي من ورقة `Arabic_Names_Pending` | **152 صنفًا + 9 فئات** |
| OV-2 | الاسم العربي لـ`ICED SHAKEN SALTED CARAMEL`: المصدر "آيس شيكن سولتد"، والمقترح "آيس شيكن سولتد كراميل" | `PRD-00067` |
| OV-3 | أسماء غير واضحة، تُعرض كما في المصدر حتى تأكيدك (MQ-12): MESTEKH · ICED CROCCONATE · MILKSHAKE BAJ · MILKSHAKE ASH BERRY · V60 HONDURAS LAS · ROZY BASIL | 6 |
| OV-4 | حالة Snacks وPastries | 2 |
| OV-5 | أسماء عرض الفئات (EN/AR) وتجميعها | يُحسم في Menu IA (D-105). `CAT-008` يبقى SPECIALITY COFFEE (D-130) |

**أسماء العرض الآن:**
- **الإنجليزية: 191/191 معتمدة.**
  - 67 مصححة حسب المجموعات المعتمدة، و124 كما في الملف الرسمي.
  - ALL CAPS أسلوب عرض فقط، وليس جزءًا من الهوية (D-131).
- **العربية: 39 معتمدة:**
  - 26 اسمًا جديدًا (G9).
  - 13 مصححة: G6 (2) + G7 (11).
  - الفئتان CAKE ← كيك، وCOOKIES ← كوكيز.

## تأكيد: Source Lineage محفوظ

- **192 سجل مصدر** (`SRC-00001` → `SRC-00192`) في ورقة `Source_Records`، بحالة `IMMUTABLE`.
- **تحقق آلي:**
  - كل صف (الرقم، الفئة، الاسم الإنجليزي، الاسم العربي، السعر) **مطابق حرفيًا للملف الأصلي: 192/192**.
  - `price_fils` = السعر × 1000 لكل الصفوف.
  - بصمة الملف الأصلي SHA-256 لم تتغير.
- **حقول المصدر لم تُعدّل:** `source_name_en` و`source_name_ar` و`source_category_name` في أي صف.
- **دمج DUP-01:**
  - صفا المصدر #115 و#120 محفوظان.
  - صف #120 أصبح مربوطًا بـ`PRD-00115`.
  - `PRD-00120` باقٍ بحقوله وسعره كسجل تاريخي (`CHG-00002`).

## تأكيد: لا استنتاج غير مدعوم

- **كل قيمة في `normalized_*` أو `display_*` تختلف عن المصدر لها قرار معتمد** مسجل بجانبها:
  - **109 تغييرات** في ورقة `Name_Changes_Applied`.
  - كل تغيير مربوط بمجموعته وقراره (D-114، D-124، D-126 → D-132).
- **لم يُضف من عندنا أي:** سعر، أو Net Price، أو ضريبة، أو فئة، أو وصف، أو مكونات، أو صورة، أو توفر، أو فرق بين الفرعين، أو حجم، أو إضافة.
- **لم تُعتمد أي ترجمة عربية لم توافق عليها:**
  - الأسماء العربية الـ152 الباقية لم تُنسخ إلى `display_name_ar`.
  - "سولتد كراميل" في `PRD-00067` بقي مقترحًا فقط (OV-2).
- **لم يُطبّق "CROCCANTE" على `ICED CROCCONATE`**، لأنه لم يكن ضمن حالات G5 المعروضة. بقي سؤالًا (OV-3).

---

**الخطوة التالية بعد موافقتك:** Menu Information Architecture. **لم نبدأ IA ولا UX/UI.**
