# 17 — Menu Data Model (v0.2 — بعد قراءة ملف المنيو الرسمي)

> **الحالة:** `DRAFT v0.2 — PENDING OWNER APPROVAL` · **آخر تحديث:** 2026-10-01
> - v0.1: مسودة قبل الملف، مبنية على D-075 → D-089.
> - v0.2: محدّثة بعد قراءة الملف الرسمي (192 صنفًا، 11 فئة). التقرير: [`18-official-menu-inventory-report.md`](18-official-menu-inventory-report.md).
>
> **المبدأ:** الـData Model قوي (D-086)، والـUX بسيط (D-087). وجود حقل في النموذج **لا يعني** عرضه للزبون.
> **المنصة لم تُختر بعد (DB-08):** هذا نموذج منطقي يصلح لأي CMS أو قاعدة بيانات.

## ما تغيّر في v0.2 (للاعتماد)

| # | التغيير | السبب |
|---|---|---|
| DM-01 | **Variant = وحدة البيع دائمًا.** كل Product له Variant واحد على الأقل (`V01`). السعر والتوفر ورقم الكاشير على الـVariant | مكان واحد للسعر. يسمح بتجميع "بدون سكر" وRED BULL وTURKISH S/D بدون تغيير البنية (MQ-02، MQ-08) |
| DM-02 | **حقول مصدر غير قابلة للتعديل** + كيان `import_batch` | تتبع كل قيمة إلى ملف الـOwner، وإثبات أن السعر لم يُعدَّل |
| DM-03 | **اسم المصدر منفصل عن اسم العرض** + حالة الاسم | أسماء الملف مختصرة بأسلوب الكاشير، ولا تصحيح تلقائي (D-089) |
| DM-04 | السعر بالفلس كعدد صحيح + `price_includes_tax = true` + **لا Net / Tax fields** | D-089 |
| DM-05 | التوفر لكل Variant × فرع، والقيمة الافتراضية `UNKNOWN` | D-079، MQ-01 |
| DM-06 | الفئة: الاسم العربي من المصدر + الحقول الموسمية | فئة SPRING (MQ-06) |
| DM-07 | سمات اختيارية `temperature` و`is_sugar_free`، **لا تُملأ إلا بتأكيد الـOwner** | البحث والتصفية لاحقًا |
| DM-08 | كيان `content_review` لكل اقتراح وقرار | سجل قرارات قابل للتدقيق (D-084) |

---

## 1. الكيانات (Entities)

```
import_batch (IMP-0001 = ملف 2026-10-01)
market (jo) ── branch (drive · house)
category (CAT-###) ── [subcategory — اختياري، غير مستخدم الآن]
   └── product (PRD-#####)                 ← المحتوى: الاسم، الوصف، الصور، الـSEO
         ├── variant (PRD-#####-V##)       ← وحدة البيع
         │     ├── price          (base · branch_override · مستقبلًا: temporary / promotional)
         │     ├── availability   (variant × branch: UNKNOWN / AVAILABLE / UNAVAILABLE)
         │     └── pos mapping    (pos_item_id · external_item_id)
         ├── media (MED-######)
         └── modifier_group (MGR-###) ── modifier (MOD-####)    ← فارغ حتى MQ-07
content_review · audit_log · version            ← الحوكمة لكل الكيانات
```

## 2. الحقول

### import_batch (جديد — DM-02)
| الحقل | ملاحظات |
|---|---|
| `import_batch_id` | `IMP-0001` |
| `source_file_name` · `source_sha256` · `received_at` · `received_from` | الملف الأول: `OWNER-OFFICIAL-MENU-2026-10-01.xlsx` · `dfde223f…8d97` · 2026-10-01 · Owner |
| `notes` | ملاحظات على شكل الملف (ورقتان بترتيب أعمدة مختلف) |

### category
| الحقل | النوع | ملاحظات |
|---|---|---|
| `category_id` | `CAT-###` | ثابت، لا يعتمد على الاسم (D-089) |
| `parent_category_id` | مرجع (اختياري) | للفئات الفرعية إن اعتمدت في الـIA |
| `name_en_source` · `name_ar_source` | نص — **لا يُعدّل** | من الملف كما هو. الاسم العربي موجود لـ9 فئات |
| `name_en` · `name_ar` | نص | **اسم العرض.** يبدأ مساويًا للمصدر، ويتغير بموافقة |
| `name_status` | `SOURCE_UNREVIEWED` · `SUGGESTED` · `APPROVED` | — |
| `slug` | لاتيني | يُنشأ بعد اعتماد اسم العرض. تغييره يتطلب 301 |
| `sort_order` | رقم | الافتراضي: ترتيب الملف |
| `is_seasonal` · `season_start` · `season_end` | (جديد — DM-06) | لـSPRING إذا أكدت (MQ-06) |
| `status` | Active / Hidden / Archived | — |
| `seo_title_*` · `seo_description_*` | اختياري | فقط عند الحاجة |

### product (المحتوى)
| الحقل | النوع | ملاحظات |
|---|---|---|
| `product_id` | `PRD-#####` | **عالمي للعلامة:** نفس الصنف في أي سوق مستقبلي يحمل نفس المعرّف. لا يحتوي الاسم ولا الفئة |
| `category_id` | مرجع | — |
| `import_batch_id` · `source_sheet` · `source_row` · `source_no` | **لا يُعدّل** (DM-02) | مثال: `IMP-0001` · `Sheet1` · `60` · `58` |
| `name_en_source` · `name_ar_source` | **لا يُعدّل** (DM-03) | كما في الملف حرفيًا. `name_ar_source` فارغ للـ26 صنفًا في الورقة الثانية |
| `name_en` · `name_ar` | نص | **اسم العرض.** يبدأ مساويًا للمصدر، ويتغير فقط عبر `content_review` (D-082، D-089) |
| `name_status` | `SOURCE_UNREVIEWED` · `SUGGESTED` · `APPROVED` | `name_ar` الناقص = `MISSING — OWNER INPUT REQUIRED` |
| `name_ar_display_rule` | اختيار | هل يظهر الاسم الإنجليزي داخل النسخة العربية؟ (D-082) |
| `slug` | لاتيني | يُنشأ **بعد** اعتماد `name_en` |
| `description_en` / `description_ar` | نص | `DESCRIPTION MISSING` (D-081) |
| `ingredients_*` / `allergens` / `calories` / `nutrition` | اختياري | `MISSING` — لا اختراع (D-081) |
| `temperature` | `hot` · `iced` · `n/a` · فارغ | (DM-07) **لا تُستنتج تلقائيًا** — تُملأ بعد تأكيدك |
| `is_sugar_free` | Y / N / فارغ | (DM-07) نفس القاعدة |
| `is_featured` / `is_new` / `is_seasonal` + `season_start` / `season_end` | — | `MISSING` الآن |
| `status` | Active / Hidden / Archived | — |
| `sort_order` | رقم | الافتراضي: ترتيب الملف داخل الفئة |
| `seo_*` | اختياري | فقط للأصناف التي لها صفحة مستقلة (DB-06 Hybrid) |

### variant (وحدة البيع — DM-01)
| الحقل | ملاحظات |
|---|---|
| `variant_id` | `{product_id}-V##`. كل صنف يبدأ بـ`V01` |
| `variant_name_en` / `variant_name_ar` | فارغ لـ`V01` إذا كان للصنف خيار واحد. يُملأ فقط من مصدر رسمي (مثل Single / Double بعد MQ-02) |
| `is_default` | Y لـ`V01` |
| `pos_item_id` | رقم الصنف في الكاشير (MQ-09) — `MISSING` الآن (D-077) |
| `external_item_id` | ERP أو SHELTER COFFEE DRIVE SYSTEM مستقبلًا (D-077) |
| `sort_order` · `status` | — |

**مثال تجميع (فقط إذا اعتمدت MQ-08):**
- `PRD-00021` VANILLA LATTE:
  - `V01` عادي، 2.75.
  - `V02` بدون سكر، 2.75. هذا هو الصف المصدر `S1 #28`.
- رقم `PRD-00028` يصبح `VOID` قبل التجميد، ولا يُعاد استخدامه.

### price (DM-04، D-078، D-089)
| الحقل | ملاحظات |
|---|---|
| `price_id` | `PRC-######` داخلي |
| `variant_id` | السعر دائمًا على الـVariant |
| `market` · `currency` | `jo` · `JOD` |
| `branch_id` | **فارغ = السعر الأساسي** · `drive` أو `house` = **Branch Price Override** لنفس الـVariant (ليس صنفًا جديدًا) |
| `amount_minor` | **عدد صحيح بالفلس:** 2.75 د.أ = 2750. يُعرض بالصيغة المعتمدة لاحقًا |
| `price_source` | السعر كما في الملف (مثل 2.75) — **لا يُعدّل** |
| `price_includes_tax` | **true** — السعر النهائي للزبون كما في الملف. لا تُضاف ضريبة ولا يُعدَّل |
| `price_type` | `base` · `branch_override` — مستقبلًا (غير مفعّل): `temporary` · `promotional` |
| `valid_from` / `valid_to` | للأنواع المستقبلية فقط |
| `price_before_tax` · `tax_rate` · `tax_amount` | **غير موجودة.** لا نحسب Net Price. إذا احتاجها النظام داخليًا مستقبلًا تُناقش أولًا (D-089) |

**الحالة بعد الاستيراد:**
- 192 سعرًا أساسيًا (`branch_id` فارغ).
- 0 Branch override، حتى جوابك على MQ-01.

### availability (DM-05، D-079)
| الحقل | ملاحظات |
|---|---|
| `variant_id` × `branch_id` | 192 × 2 = **384 سجلًا** |
| `state` | `UNKNOWN` (الافتراضي) · `AVAILABLE` · `UNAVAILABLE`. **لا نفترض تطابق DRIVE وHOUSE** |
| `note` | سبب الاستثناء إن وُجد |

### modifier_group / modifier (D-080)
| الحقل | ملاحظات |
|---|---|
| `modifier_group_id` (`MGR-###`) · `selection_rule` | مثل: "الحليب"، "الإضافات" |
| `modifier_id` (`MOD-####`) · `name_en/ar` · `price_delta_minor` | **من مصدر رسمي فقط.** الملف لا يحتوي إضافات ← **فارغ** حتى MQ-07 |
| `applies_to` | فئات أو أصناف |
| **العرض للزبون** | معلومات مختصرة فقط، **لا شاشة تشبه الكاشير** |

### media (D-083)
| الحقل | ملاحظات |
|---|---|
| `media_id` (`MED-######`) · `product_id` · `role` (`main` / `additional`) | اسم الملف: `PRD-00058_main.webp` |
| `file` · `alt_ar` · `alt_en` · `focal_point` (x, y) | — |
| `formats` | WebP / AVIF + نسخ متجاوبة تُولّد آليًا |
| `approval_status` | `Pending` · `Approved` · `Rejected` · `Archived`. **لا عرض قبل Approved** |
| `source` / `rights` | — |

### content_review (جديد — DM-08)
| الحقل | ملاحظات |
|---|---|
| `review_id` · `entity` · `entity_id` · `field` | مثال: `product` · `PRD-00064` · `name_en` |
| `current_value` · `suggested_value` · `reason` · `rule` | مثال: `ICE IRSH LATTE` · `ICED IRISH LATTE` · خطأ إملائي · `NR-05` |
| `decision` · `decided_by` · `decided_at` | `Approve` · `Modify` · `Reject` — **لا يُطبّق شيء قبل القرار** |

### الحوكمة — لكل الكيانات (D-084)
- سير النشر: Draft → Review → Published.
- سجل الإصدارات (Version History).
- سجل التعديل (Audit Log): من، متى، القيمة القديمة والجديدة. **إلزامي للأسعار والتوفر والأسماء.**
- موافقة الـOwner حيث يلزم.

## 3. بنية المعرّفات (D-089)

| الكيان | الصيغة | في الملف الحالي | القاعدة |
|---|---|---|---|
| Category | `CAT-###` | `CAT-001` → `CAT-011` | ترتيب ظهور الفئة في الملف |
| Product | `PRD-#####` | `PRD-00001` → `PRD-00192` | ترتيب الصفوف: الورقة الأولى ثم الثانية |
| Variant | `{product_id}-V##` | `PRD-00001-V01` → `PRD-00192-V01` | `V01` افتراضي |
| Price | `PRC-######` | داخلي | — |
| Import batch | `IMP-####` | `IMP-0001` | — |
| Modifier Group / Modifier | `MGR-###` · `MOD-####` | — | — |
| Media | `MED-######` | — | — |

**قواعد الثبات:**
- المعرّفات **مسودة** حتى اعتماد التقرير `18`، ثم تُجمّد.
- لا يُعاد استخدام أي رقم:
  - ما يُلغى قبل التجميد = `VOID`.
  - ما يتوقف بعده = `Archived`.
- الأصناف الجديدة تبدأ من `PRD-00193`.

**الفصل الثلاثي:**
- **Display Name:** يتغير بموافقتك.
- **Slug:** يتغير فقط مع 301.
- **Internal ID:** لا يتغير أبدًا.

## 4. سير معالجة ملفات المنيو (D-075، D-088)

1. **قراءة كاملة** لكل الأوراق، وحفظ نسخة مطابقة للأصل مع SHA-256.
2. **استخراج:** الفئات، والأصناف (الأسماء كما هي)، والسعر شامل الضريبة كما هو.
3. **فحوص:**
   - التكرار.
   - الأسعار الناقصة.
   - الصفوف الفارغة.
   - القيم المشبوهة.
   - تطابق عمود الفئة مع عنوان القسم.
   - التسمية والإملاء: **عرض فقط** بصيغة Current / Suggested / Reason.
4. **مقارنة مع المنيو القديم** لاكتشاف الفروقات فقط (D-076).
5. **تقرير من 11 بندًا** + قائمة الأسماء العربية المقترحة (غير معتمدة).
6. **انتظار اعتمادك** ← Inventory v1.0 ← Menu IA ← Menu UX/UI.
7. **التحديثات اللاحقة على الملف:** كل ملف جديد = `import_batch` جديد ومقارنة (Diff) مع النسخة المعتمدة: أصناف جديدة، أسعار تغيرت، أصناف اختفت. **لا شيء يُنشر قبل موافقتك.**
