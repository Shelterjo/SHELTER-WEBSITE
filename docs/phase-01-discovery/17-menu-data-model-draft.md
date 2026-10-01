# 17 — Menu Data Model (DRAFT — قبل استلام الملف)

> **الحالة:** `DRAFT — PENDING OWNER APPROVAL` · **آخر تحديث:** 2026-10-01
> هذه مسودة البنية فقط، مبنية على قراراتك D-075 → D-089. **لا تحتوي أي صنف أو سعر.** تُراجع وتُثبّت بعد قراءة ملف المنيو الرسمي.
> **المبدأ (D-086):** الـData Model قوي، والـUX بسيط (D-087). وجود حقل في النموذج **لا يعني** عرضه للزبون.

---

## 1. الكيانات (Entities)

```
Market (سوق: jo)
 ├── Branch (drive, house)                       ← من نموذج المواقع (AR-02)
 └── Category (فئة) ──┬── Subcategory (اختياري)
                      └── Product (صنف)
                            ├── Variant (حجم/خيار)        0..n
                            ├── Price                     ← Base + Variant + Branch override (+ مستقبلًا: Temporary / Promotional)
                            ├── Availability (لكل فرع)
                            ├── Modifier Group → Modifier (إضافات: Extra shot، حليب بديل، نكهات…)
                            ├── Media (صور)
                            └── SEO (اختياري)
```

## 2. الحقول

### Category
| الحقل | النوع | ملاحظات |
|---|---|---|
| `category_id` | معرّف ثابت داخلي (مثل `CAT-001`) | لا يتغير أبدًا، ولا يعتمد على الاسم (D-089) |
| `parent_category_id` | مرجع (اختياري) | للفئات الفرعية إن احتجناها |
| `slug` | لاتيني (مثل `hot-coffee`) | منفصل عن المعرّف. تغييره يتطلب 301 وسجل slugs |
| `name_en` | نص | من الملف الرسمي |
| `name_ar` | نص | `MISSING` حتى يُعتمد |
| `sort_order` | رقم | — |
| `status` | Active / Hidden / Archived | — |
| `seo_title_*`, `seo_description_*` | نص (اختياري) | فقط عند الحاجة |

### Product
| الحقل | النوع | ملاحظات |
|---|---|---|
| `product_id` | معرّف ثابت (مثل `PRD-00001`) | **عالمي للعلامة**: نفس الصنف في أي سوق مستقبلي له نفس المعرّف |
| `category_id` | مرجع | — |
| `slug` | لاتيني | من الاسم الإنجليزي بعد اعتماده، ومنفصل عن المعرّف |
| `name_en` | نص | **من الملف الرسمي كما هو** — لا تصحيح تلقائي (D-089) |
| `name_ar` | نص | `MISSING` ← مقترحات للاعتماد (D-082) |
| `name_ar_display_rule` | اختيار | هل يظهر الاسم الإنجليزي داخل النسخة العربية؟ (قرار لكل صنف — D-082) |
| `description_en` / `description_ar` | نص | `DESCRIPTION MISSING` حتى تُقدَّم أو تُعتمد (D-081) |
| `ingredients_*` / `allergens` / `calories` / `nutrition` | نص/قيم (اختياري) | `MISSING` — لا اختراع (D-081) |
| `is_featured` / `is_new` / `is_seasonal` | Y/N | — |
| `season_start` / `season_end` | تاريخ | للموسمي فقط |
| `status` | Active / Hidden / Archived | — |
| `sort_order` | رقم | — |
| `pos_item_id` | نص (اختياري) | ربط مستقبلي مع الكاشير (D-077) |
| `external_item_id` | نص (اختياري) | ربط مستقبلي مع ERP أو SHELTER COFFEE DRIVE SYSTEM (D-077) |
| `seo_*` | اختياري | فقط للأصناف التي لها صفحة مستقلة (DB-06 Hybrid) |

### Variant (الأحجام والخيارات)
| الحقل | ملاحظات |
|---|---|
| `variant_id` | `{product_id}-V{nn}` (مثل `PRD-00001-V01`) — ثابت |
| `name_en` / `name_ar` | من الملف فقط |
| `sort_order` · `status` · `pos_item_id` | قد يكون للحجم رمز مستقل في الكاشير |

### Price (D-078، D-089)
| الحقل | ملاحظات |
|---|---|
| `price_id` | معرّف داخلي |
| `product_id` + `variant_id` (اختياري) | السعر للصنف أو لحجم محدد |
| `market` | `jo` |
| `branch_id` | **فارغ = السعر الأساسي** · `drive` أو `house` = **Branch Price Override** لنفس الصنف (وليس صنفًا جديدًا) |
| `amount_minor` | **عدد صحيح بالفلس** (2.50 د.أ = 2500) حتى لا تحدث أخطاء تقريب. يُعرض بالصيغة المعتمدة |
| `currency` | `JOD` |
| `price_includes_tax` | **true** — السعر النهائي للزبون شامل الضريبة **كما في الملف**. لا تُضاف الضريبة مرة ثانية ولا يُعدَّل السعر |
| `price_type` | `base` · `branch_override` — مستقبلًا (غير مفعّل): `temporary` · `promotional` |
| `valid_from` / `valid_to` | للأنواع المستقبلية فقط |
| `price_before_tax` / `tax_rate` / `tax_amount` | **غير مُخزّنة الآن** — تُناقش قبل إضافتها (D-089) |

### Availability (D-079)
| الحقل | ملاحظات |
|---|---|
| `product_id` / `variant_id` × `branch_id` | — |
| `state` | `UNKNOWN` (الافتراضي) · `AVAILABLE` · `UNAVAILABLE` — **لا نفترض تطابق DRIVE وHOUSE**. أي فرق غير واضح = `OWNER VERIFICATION REQUIRED` |

### Modifier Group / Modifier (D-080)
| الحقل | ملاحظات |
|---|---|
| `modifier_group_id` (مثل `MGR-001`) | مثل: "الحليب"، "الإضافات" |
| `selection_rule` | اختياري / اختيار واحد / متعدد (للمستقبل) |
| `modifier_id` (مثل `MOD-0001`) · `name_en/ar` · `price_delta_minor` | **من الملف الرسمي فقط** — لا إضافات مخترعة |
| `applies_to` | فئات أو أصناف |
| **العرض للزبون** | معلومات مختصرة مفيدة فقط (مثل: "يتوفر حليب بديل")، **لا شاشة خيارات تشبه الكاشير** |

### Media (D-083)
| الحقل | ملاحظات |
|---|---|
| `media_id` (مثل `MED-000001`) · `product_id` · `role` (`main` / `additional`) | — |
| `file` · `alt_ar` · `alt_en` · `focal_point` (x, y) | — |
| `formats` | WebP / AVIF + نسخ متجاوبة (Responsive variants) تُولّد آليًا |
| `approval_status` | `Pending` · `Approved` · `Rejected` · `Archived` — **لا عرض قبل Approved** |
| `source` / `rights` | — |

### الحوكمة (D-084) — لكل الكيانات
Draft → Review → Published · سجل الإصدارات (Version History) · سجل تعديل (Audit Log: من، متى، القيمة القديمة والجديدة) **إلزامي للأسعار والتوفر** · موافقة الـOwner حيث يلزم.

## 3. بنية المعرّفات (مقترحة — D-089)

| الكيان | الصيغة | مثال | القاعدة |
|---|---|---|---|
| Category | `CAT-` + 3 أرقام | `CAT-001` | تسلسلي حسب ترتيب الملف عند أول استيراد، ثم يُجمّد بعد الاعتماد. لا يُعاد استخدام رقم محذوف |
| Product | `PRD-` + 5 أرقام | `PRD-00001` | عالمي للعلامة. لا يعتمد على الاسم أو الفئة. **لا يتغير بعد الاعتماد** |
| Variant | `{product_id}-V` + رقمان | `PRD-00001-V01` | — |
| Modifier Group / Modifier | `MGR-` + 3 · `MOD-` + 4 | `MGR-001` · `MOD-0001` | — |
| Media | `MED-` + 6 | `MED-000001` | اسم الملف: `PRD-00001_main.webp` |

**الفصل الثلاثي:**
- **Display Name:** ما يراه الزبون، ويتغير بموافقتك.
- **Slug:** في الرابط، ويتغير فقط مع 301.
- **Internal ID:** لا يتغير أبدًا.

## 4. سير معالجة ملف المنيو (عند وصوله — D-075، D-088)
1. **قراءة كاملة** لكل الصفحات والأوراق والصور.
2. **استخراج:** الفئات، والأصناف (الاسم الإنجليزي كما هو)، والسعر شامل الضريبة كما هو.
3. **فحوص:**
   - تكرار الأصناف والفئات.
   - أسعار ناقصة.
   - صفوف فارغة.
   - قيم أسعار مشبوهة (صفر، أو شاذة عن فئتها).
   - تسمية غير متسقة وأخطاء إملائية محتملة (**عرض فقط** بصيغة: Current Name · Suggested Name · Reason).
4. **مقارنة مع المنيو القديم** لاكتشاف الأصناف الناقصة أو القديمة والتعارضات فقط (D-076). **المرجع هو ملفك.**
5. **تقرير من 11 بندًا:**
   - قائمة الفئات وعددها، وعدد الأصناف، والأصناف حسب الفئة، ونطاق الأسعار لكل فئة.
   - القيم الناقصة، والتكرار، وعدم الاتساق في التسمية.
   - أسئلة التحقق.
   - بنية المعرّفات، والـData Model المعدّل.
6. **قائمة الأسماء العربية المقترحة:** English Name · Suggested Arabic Name · Translation/Transliteration · Reason — **غير معتمدة** (D-082).
7. **انتظار اعتمادك** ← ثم Menu Information Architecture ← ثم UX/UI.
