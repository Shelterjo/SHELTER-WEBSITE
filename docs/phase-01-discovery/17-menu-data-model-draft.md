# 17 — Menu Data Model (v0.3)

> **الحالة:** `DRAFT v0.3 — PENDING OWNER APPROVAL` · **المعرّفات PROVISIONAL** · **آخر تحديث:** 2026-10-01
>
> **السجل:**
> - **v0.1:** قبل الملف (D-075 → D-089).
> - **v0.2:** بعد قراءة الملف.
> - **v0.3:** بعد قرارات الـOwner D-090 → D-108، وهي الأساس الآن.
>
> **المراجع:** التقرير [`18`](18-official-menu-inventory-report.md) · أسئلة P0 [`19`](19-menu-p0-owner-review.md) · البيانات [`menu/SHELTER-MENU-INVENTORY-v0.2.xlsx`](menu/SHELTER-MENU-INVENTORY-v0.2.xlsx)
>
> **المنصة لم تُختر بعد (DB-08).** هذا نموذج منطقي يصلح لأي CMS أو قاعدة بيانات (مثل Supabase/Postgres).

## 0. المبادئ (قرارات الـOwner)

| # | المبدأ | القرار |
|---|---|---|
| 1 | **SOURCE → NORMALIZED → DISPLAY.** بيانات المصدر لا تُعدّل أبدًا. التصحيح المعتمد يُكتب في `normalized_*`، وما يظهر للزبون في `display_*` | D-092 |
| 2 | **لا تصحيح تلقائي**، حتى الواضح منه. كل تصحيح = Suggested Correction يُعتمد كدفعة واحدة | D-093 |
| 3 | **كل صف في المصدر = صنف مستقل بمعرّف مستقل.** لا دمج ولا تحويل إلى Variants بسبب الـUX | D-096، D-097 |
| 4 | **هوية البيانات ≠ التجميع في العرض.** التجميع البصري (Product Family) طبقة منفصلة، ولا يُفعّل إلا بقرارك | D-097 |
| 5 | **Preserve first, merge later.** أي دمج لاحق يحفظ: Source row · Source name · Price · POS reference · Historical identity | D-096 |
| 6 | **لا اختراع:** أحجام، إضافات، أسماء عربية نهائية، أوصاف، صور، توفر، فروق أسعار | D-081، D-101 |
| 7 | **غائب عن الملف ≠ متوقف:** الحالة `NOT PRESENT IN CURRENT OWNER FILE` | D-099 |
| 8 | **السعر بالفلس** كعدد صحيح، شامل الضريبة، والوحدة موثقة صراحة. لا Net Price ولا Tax data الآن | D-103، D-104 |
| 9 | **Menu Versioning:** أي استيراد لا يمسح التاريخ | D-107 |
| 10 | **المعرّفات PROVISIONAL** حتى إغلاق P0 وإصدار v1.0 | D-090 |

---

## 1. الكيانات

```
menu_version (MV-YYYY-MM-DD) ── import_batch (IMP-####: الملف + SHA-256)
market (jo) ── branch (drive · house)

category (CAT-###)
product (PRD-#####)                      ← صف واحد من المصدر = صنف واحد. لا يُحذف ولا يُدمج تلقائيًا
  ├── product_size (PRD-#####-SZ##)      ← فقط من مصدر رسمي — لا يوجد الآن
  ├── price (PRC-######)                 ← price_fils · tax_inclusive · branch · size · menu_version · valid_from/to
  │     └── price_tax_detail             ← محجوز وفارغ (تكامل ERP/POS مستقبلًا)
  ├── availability                       ← product [× size] × branch
  ├── media (MED-######)
  └── modifier_link → modifier_group (MGR-###) → modifier (MOD-####)   ← لا يوجد الآن

product_family (FAM-###)   ← تجميع بصري للعرض فقط — فارغ حتى قرارك
variant_group  (VGR-###)   ← أصناف أكّدت أنها "نفس الصنف بخيار مختلف" — فارغ حتى قرارك
content_review (REV-#####) · menu_change (CHG-#####) · audit_log
```

**الفرق بين Product Family وVariant Group:**

| | Product Family (`FAM-`) | Variant Group (`VGR-`) |
|---|---|---|
| ماذا يعني | **عرض:** عدة أصناف تظهر تحت بطاقة واحدة على الموبايل | **علاقة:** الأصناف خيارات لنفس الصنف الأساسي (مثل: عادي / بدون سكر) |
| يغيّر الهوية؟ | لا | لا |
| ID وسعر وPOS مستقل لكل صنف؟ | نعم | نعم |
| متى يُنشأ | بعد مراجعة المنيو: نقترح، ثم تقرر (MQ-08) | فقط بتأكيدك |

---

## 2. الحقول

### menu_version (جديد — D-107)
| الحقل | ملاحظات |
|---|---|
| `menu_version_id` | `MV-2026-10-01` |
| `status` | `DRAFT` → `APPROVED` → `SUPERSEDED` |
| `received_at` | 2026-10-01 |
| `effective_from` | **`OWNER VERIFICATION REQUIRED`** (MQ-17) |
| `import_batch_ids` · `previous_version_id` | — |
| `approved_by` · `approved_at` · `notes` | — |

### import_batch
| الحقل | ملاحظات |
|---|---|
| `import_batch_id` | `IMP-0001` |
| `source_file` | `OWNER-OFFICIAL-MENU-2026-10-01.xlsx` |
| `source_hash` | SHA-256: `dfde223f…8d97` |
| `received_from` · `received_at` · `sheets` · `notes` | ملاحظات الشكل محفوظة في `19` §2 |

### category (D-105)
| الحقل | ملاحظات |
|---|---|
| `category_id` · `id_status` | `CAT-###` · `PROVISIONAL` / `FROZEN` |
| `source_category_name` | كما في الملف، مثل `SPECIALITY COFFEE`. **لا يُعدّل** |
| `source_category_name_ar` + `_status` | من عنوان القسم، مثل "قهوة مختصة" · `SOURCE-PROVIDED — PENDING OWNER REVIEW` (D-091) |
| `source_header_raw` · `source_sheet` · `source_row` | النص الأصلي للعنوان بمسافاته |
| `normalized_category_name_en/ar` | بعد اعتماد التصحيحات |
| `display_category_name_en` · `display_category_name_ar` | **فارغ حتى اعتمادك** |
| `slug` · `sort_order` | الـSlug بعد اعتماد اسم العرض |
| `category_type` | `SEASONAL` لفئة SPRING (D-100) · غيرها `UNSPECIFIED` |
| `current_availability` | SPRING: `OWNER VERIFICATION REQUIRED` |
| `season_start` · `season_end` · `historical_context` | `MISSING`. لا يوجد لدينا دليل تاريخي على موسم محدد |
| `approval_status` · `created_at` · `updated_at` | — |

### product
| المجموعة | الحقل | القيمة الحالية / القاعدة |
|---|---|---|
| **الهوية** | `product_id` · `id_status` | `PRD-#####` · `PROVISIONAL` |
| | `category_id` | — |
| **المصدر** (لا يُعدّل أبدًا) | `source_file` · `source_sheet` · `source_row` · `source_hash` · `menu_version` | مثال: `Sheet1` · `60` · `MV-2026-10-01` |
| | `source_row_number` | قيمة عمود #: **`SOURCE ROW NUMBER / UNKNOWN BUSINESS MEANING`** (D-102). لا إعادة ترقيم (D-099) |
| | `source_category_name` · `source_name_en` · `source_name_ar` · `source_price` | كما في الملف حرفيًا. `source_price` نص مثل `"2.75"` |
| | `source_name_ar_status` | `SOURCE-PROVIDED — PENDING OWNER REVIEW` (166 صنفًا) · `MISSING — OWNER INPUT REQUIRED` (26 صنفًا) |
| **المعالجة** | `normalized_name_en` · `normalized_name_ar` | **فارغ.** يُملأ فقط بالتصحيحات المعتمدة |
| **العرض** | `display_name_en` · `display_name_ar` | **فارغ حتى اعتمادك.** الموقع لا يعرض إلا `display_*` المعتمد |
| | `slug` | بعد اعتماد `display_name_en` |
| **التجميع** | `product_family_id` · `variant_group_id` | **فارغان** (D-097) |
| | `merged_into_product_id` | فارغ. يُستخدم فقط إذا اعتمدت دمجًا لاحقًا، والصنف المدموج يبقى بكل تاريخه |
| **الحالة** | `approval_status` | `PENDING OWNER REVIEW` · `APPROVED` · `REJECTED` |
| | `data_quality_status` + `data_quality_flags` | `SOURCE_CONFLICT` · `POSSIBLE_DUPLICATE` · `MEANING_UNVERIFIED` · `UNCLEAR_NAME` · `SEASONAL_AVAILABILITY_UNVERIFIED` · `ARABIC_NAME_MISSING` · `SUGGESTED_CORRECTION` · `NO_SOURCE_ISSUE_FOUND` |
| | `presence_status` | `PRESENT IN CURRENT OWNER FILE` · `NOT PRESENT IN CURRENT OWNER FILE` · `DISCONTINUED` (بتأكيدك فقط) |
| | `publish_status` | `Draft` · `Published` · `Hidden` · `Archived` |
| **التشغيل** | `availability_drive` · `availability_house` | `UNKNOWN` (D-094). ملخص من جدول `availability` |
| | `size_info_status` · `modifiers_status` | `MISSING — OWNER INPUT REQUIRED` (D-101). **لا نفترض حجمًا واحدًا** |
| | `pos_item_id` · `external_item_id` | **فارغان** (D-102) |
| **المحتوى** | `description_*` · `ingredients_*` · `allergens` · `calories` | `DESCRIPTION MISSING` / `MISSING` (D-081) |
| | `image_status` | `MISSING` · `PENDING` · `APPROVED` (D-083) |
| | `temperature` · `is_sugar_free` (اختياري) | فارغ. **لا يُستنتج من الاسم** |
| | `is_featured` · `is_new` · `is_seasonal` | فارغ |
| **الزمن** | `valid_from` · `valid_to` | فترة وجود الصنف في المنيو. `valid_from` ينتظر MQ-17 |
| | `created_at` · `updated_at` | — |

### product_size (D-101)
| الحقل | ملاحظات |
|---|---|
| `size_id` | `{product_id}-SZ##` |
| `source_*` · `name_en/ar` · `sort_order` · `pos_item_id` | **يُنشأ فقط من مصدر رسمي.** الملف الحالي لا يحتوي أحجامًا، فلا توجد سجلات |

النموذج يدعم:
- **حجم واحد:** السعر بلا `size_id`.
- **أحجام متعددة:** سعر لكل `size_id`.
- **إضافات وModifiers:** عبر `modifier_link`.

### price (D-103، D-104، D-107)
| الحقل | ملاحظات |
|---|---|
| `price_id` | `PRC-######` |
| `product_id` · `size_id` | `size_id` فارغ = "حسب المصدر، الحجم غير محدد" |
| `branch_id` | فارغ = السعر الأساسي · `drive` / `house` = Branch override لنفس الصنف |
| `market` · `currency` | `jo` · **`JOD`** |
| **`price_fils`** | **عدد صحيح بالفلس. 1 د.أ = 1000 فلس.** مثال: 2.750 د.أ = `2750` |
| `source_price` | النص كما في الملف (`"2.75"`). **لا يُعدّل** |
| **`tax_inclusive`** | **`true`.** السعر النهائي للزبون كما في الملف |
| `price_type` | `base` · `branch_override`. مستقبلًا (غير مفعّل): `temporary` · `promotional` |
| `menu_version_id` · `valid_from` · `valid_to` | السعر الحالي: `valid_to` فارغ. عند التغيير يُغلق القديم بـ`valid_to` ويُضاف سجل جديد. **لا overwrite ولا حذف** |
| `created_at` · `updated_at` | — |

### price_tax_detail (محجوز — D-104)
| الحقل | ملاحظات |
|---|---|
| `price_id` (1:1) · `price_before_tax_fils` · `tax_rate_basis_points` · `tax_amount_fils` · `tax_source` · `rounding_rule` | **الجدول موجود في التصميم وفارغ.** لا يُملأ من عندنا. يُفعّل فقط بقرار ومن مصدر رسمي (ERP/POS)، فيمكن إضافة الضريبة لاحقًا بدون إعادة بناء |

### availability (D-094)
| الحقل | ملاحظات |
|---|---|
| `product_id` · `size_id` (اختياري) · `branch_id` | 192 × 2 = 384 سجلًا |
| `state` | `UNKNOWN` (الحالي) · `AVAILABLE` · `UNAVAILABLE` |
| `note` · `updated_at` | — |

### product_family / variant_group (D-097)
| الحقل | ملاحظات |
|---|---|
| `family_id` (`FAM-###`) · `display_name_en/ar` · `sort_order` · `approval_status` | تجميع بصري فقط |
| `variant_group_id` (`VGR-###`) · `base_label` · `option_label` لكل صنف · `approval_status` | علاقة "خيار لنفس الصنف" |

**لا سجلات الآن.** المقترح يُعرض عليك بعد المراجعة، ويوضح:
- ما الأصناف المقترح جمعها، ولماذا.
- كيف تظهر على الموبايل.
- تأكيد أن كل خيار يبقى بمعرّفه وسعره.

### media (D-083)
| الحقل | ملاحظات |
|---|---|
| `media_id` (`MED-######`) · `product_id` · `role` | اسم الملف: `PRD-00058_main.webp` |
| `alt_ar` · `alt_en` · `focal_point` · `formats` (WebP/AVIF) · `source` · `rights` | — |
| `approval_status` | `Pending` · `Approved` · `Rejected` · `Archived`. **لا عرض قبل Approved** |

### modifier_group / modifier (D-080، D-101)
| الحقل | ملاحظات |
|---|---|
| `MGR-###` · `MOD-####` · `name_en/ar` · `price_delta_fils` · `selection_rule` · `applies_to` | **من مصدر رسمي فقط. لا توجد سجلات الآن** |

### content_review (D-093)
| الحقل | ملاحظات |
|---|---|
| `review_id` · `entity` · `entity_id` · `field` | مثال: `product` · `PRD-00064` · `name_en` |
| `source_value` · `suggested_value` · `reason` · `rule` | مثال: `ICE IRSH LATTE` → `ICED IRISH LATTE` · `NR-05` |
| `review_type` | `SUGGESTED_CORRECTION` · `ARABIC_NAME` · `POSSIBLE_DUPLICATE` · `MISSING_INFO` |
| `decision` · `decided_by` · `decided_at` · `batch_id` | التصحيحات تُعتمد **كدفعة واحدة**. عند `Approve` تُكتب في `normalized_*` |

### menu_change (جديد — D-107)
| الحقل | ملاحظات |
|---|---|
| `change_id` · `menu_version_id` · `product_id` · `change_type` | `ADDED` · `PRICE_CHANGED` · `NAME_CHANGED` · `CATEGORY_CHANGED` · `NOT_PRESENT` · `RESTORED` · `ARCHIVED` · `MERGED` |
| `field` · `previous_value` · `new_value` · `effective_date` | مثال: `price_fils` · `2750` → `3000` · 2027-04-01 |
| `source_ref` · `approved_by` · `approved_at` | — |

### audit_log (D-084)
- **ماذا يسجل:** من، متى، الكيان، الحقل، القيمة القديمة والجديدة.
- **إلزامي لـ:** الأسعار والتوفر والأسماء والدمج.

---

## 3. المعرّفات (PROVISIONAL — D-090)

| الكيان | الصيغة | الحالي |
|---|---|---|
| Category | `CAT-###` | `CAT-001` → `CAT-011` |
| Product | `PRD-#####` | `PRD-00001` → `PRD-00192` (ترتيب الصفوف: الورقة الأولى ثم الثانية) |
| Size | `{product_id}-SZ##` | لا يوجد |
| Price | `PRC-######` | داخلي |
| Product Family / Variant Group | `FAM-###` / `VGR-###` | لا يوجد |
| Menu Version / Import | `MV-YYYY-MM-DD` / `IMP-####` | `MV-2026-10-01` / `IMP-0001` |
| Review / Change | `REV-#####` / `CHG-#####` | — |
| Media · Modifier | `MED-######` · `MGR-###` / `MOD-####` | — |

**القواعد:**
- المعرّف **لا يحتوي** الاسم ولا الفئة ولا رقم الكاشير.
- **الآن:** المعرّفات PROVISIONAL. تُجمّد عند إصدار v1.0 بعد إغلاق P0.
- **بعد التجميد:**
  - لا يتغير أي معرّف، ولا يُعاد استخدام أي رقم.
  - الصنف المدموج يحتفظ بمعرّفه وتاريخه (`merged_into_product_id`).
  - الأصناف الجديدة تبدأ من `PRD-00193`.

**الفصل:**
- **Internal ID:** ثابت لا يتغير.
- **Source:** كما في الملف، ولا يُعدّل أبدًا.
- **Normalized:** بعد اعتماد التصحيحات.
- **Display:** ما يراه الزبون.
- **Slug:** يتغير فقط مع 301.

## 4. Menu Versioning — سير الاستيراد القادم (D-107)

1. ملف جديد من الـOwner ← `import_batch` جديد + `menu_version` جديد بحالة `DRAFT`.
2. مطابقة الصفوف مع الأصناف الحالية:
   - مطابقة تلقائية بالقيمة المصدرية والموقع.
   - **أي مطابقة غير مؤكدة تُعرض عليك.**
3. **تقرير تغييرات (Diff):**
   - أصناف مضافة.
   - أسعار تغيرت (السابق ← الجديد).
   - أسماء تغيرت.
   - أصناف `NOT PRESENT` في الملف الجديد.
4. موافقتك ← تُكتب سجلات `menu_change`:
   - السعر القديم يُغلق بـ`valid_to`، ويُضاف السعر الجديد بـ`valid_from`.
   - **لا حذف ولا overwrite.**
5. الصنف الغائب عن الملف الجديد لا يُؤرشف تلقائيًا. يبقى `NOT PRESENT IN CURRENT OWNER FILE` حتى تقرر.
6. النسخة السابقة تصبح `SUPERSEDED`، ويبقى تاريخها كاملًا. هذا يفيد لاحقًا في الربط مع POS/ERP وSHELTER COFFEE DRIVE SYSTEM.

## 5. ما تغيّر من v0.2

| v0.2 | v0.3 |
|---|---|
| DM-01: Variant `V01` لكل صنف، والتجميع كـVariants | **مسحوب.** الصنف هو الوحدة، والأحجام فقط من المصدر الرسمي. التجميع = `product_family` / `variant_group` منفصلان وبقرارك |
| `name_en_source` + `name_en` (عرض) | ثلاث طبقات: `source_*` · `normalized_*` · `display_*` |
| `amount_minor` | `price_fils` + `currency` + `tax_inclusive` |
| `price_before_tax / tax_rate / tax_amount` "غير موجودة" | جدول `price_tax_detail` **محجوز وفارغ** |
| — | `menu_version` · `menu_change` · `presence_status` · `data_quality_status` · `approval_status` · `image_status` · `valid_from/to` · `created_at/updated_at` |
| المعرّفات "تُجمّد عند اعتماد التقرير" | `PROVISIONAL` حتى إغلاق P0 + v1.0 |
