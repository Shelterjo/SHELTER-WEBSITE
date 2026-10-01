# 20 — Menu: مراجعة ما قبل Menu Inventory v1.0

> **الحالة:** `PRE-v1.0 — WAITING FOR OWNER` · **آخر تحديث:** 2026-10-01
> - **P0 أُغلقت** بقراراتك D-109 → D-123.
> - **Inventory v0.3:** [`menu/SHELTER-MENU-INVENTORY-v0.3.xlsx`](menu/SHELTER-MENU-INVENTORY-v0.3.xlsx) · CSV: [`menu/menu-inventory-v0.3.csv`](menu/menu-inventory-v0.3.csv)
> - **Data Model:** [`17`](17-menu-data-model-draft.md) **v0.4**.
> - **لم يُجمّد أي معرّف بعد.** لا Menu IA ولا UX/UI.

## ما طُبّق من قراراتك

| القرار | ما تغيّر في البيانات |
|---|---|
| TURKISH S/D = Single/Double (D-109) | توضيح معتمد على الصنفين. الاسم المصدري لم يتغير، والتصحيح في المجموعة G6 |
| DUP-01 = نفس الصنف (D-110) | **خطة دمج:** `PRD-00120` يُدمج في `PRD-00115`، وصفا المصدر محفوظان في `Source_Records`. التنفيذ عند v1.0 |
| DUP-02، DUP-03 = مختلفة (D-111، D-112) | أُزيلت علامة التكرار عن 6 أصناف |
| DUP-04 = كل زوج وحده (D-113) | 10 أصناف `HOLD`، ولا دمج |
| #129 = MANGO (D-114) | `normalized_name_en = SMOOTHES PASSION+PINAPPLE+MANGO`. المصدر بلا تغيير |
| أصناف تُباع وليست في الملف (D-115، D-116) | سجل مستقل `PND-001` → `PND-006`. لا معرّف PRD ولا بيانات مخترعة |
| SPRING متاحة الآن (D-117) | `CURRENTLY AVAILABLE`. التواريخ `MISSING` ولا تمنع العرض |
| حجم واحد لكل مشروب (D-118) | `ONE SIZE ONLY` لكل أصناف المشروبات (166) |
| الإضافات لا تظهر على الموقع (D-119) | `addons_on_website = false` و`show_on_website = false` |
| عمود # تسلسلي فقط (D-120) | `source_sequence_number`، ولا يُستخدم في أي تكامل |
| POS لاحقًا (D-121) | `pos_item_id` و`external_item_id` فارغان |
| السريان 2026-10-01 (D-122) | `MV-2026-10-01` · `valid_from = 2026-10-01` لكل الأسعار (192) |

---

## 1. العدد النهائي المتوقع

| البند | العدد |
|---|---|
| صفوف المصدر | 192 |
| **الأصناف بعد دمج DUP-01** | **191** |
| بعد DUP-04 | بين **186** (لو كانت الأزواج الخمسة نفس الصنف) و**191** (لو كانت كلها مختلفة) |
| الفئات | 11 |
| أصناف تُباع وليست في الملف | 4، ولا تدخل العدد قبل وصول بياناتها. قد يكون أحدها أكثر من صنف (مثل نكهات الآيس كريم) |

## 2. أصناف تُباع حاليًا وغير موجودة في الملف

| # | الصنف | الحالة | ذُكر في | سؤال إضافي |
|---|---|---|---|---|
| PND-001 | Cold Brew | `ACTIVE PRODUCT — MISSING CURRENT MENU DATA` | CR-069، CR-070 | هل هو صنف واحد أم أكثر (نكهات/أنواع)؟ |
| PND-002 | Lotus Cheesecake | `ACTIVE PRODUCT — MISSING CURRENT MENU DATA` | CR-070 | هل هو نفس الاسم المستخدم حاليًا؟ |
| PND-003 | Ice Cream | `ACTIVE PRODUCT — MISSING CURRENT MENU DATA` | CR-007 | صنف واحد أم عدة نكهات/أحجام؟ كل نكهة بسعر = صنف مستقل |
| PND-004 | Single Espresso | `ACTIVE PRODUCT — MISSING CURRENT MENU DATA` | — | في الملف ESPRESSO D فقط (#9). لا نستنتج سعر أو فئة السينجل منه |
| PND-005 | Snacks | `STATUS UNKNOWN — OWNER VERIFICATION REQUIRED` | CR-007 | هل تُباع حاليًا؟ وما الأصناف؟ |
| PND-006 | Pastries | `STATUS UNKNOWN — OWNER VERIFICATION REQUIRED` | — | هل تُباع حاليًا؟ وما الأصناف؟ |

## 3. Missing Data لهذه الأصناف

**المطلوب لكل صنف من `PND-001` إلى `PND-004`** (كلها `MISSING — OWNER INPUT REQUIRED`):

| الحقل | لازم لإنشاء الصنف؟ |
|---|---|
| الاسم الرسمي بالإنجليزي (كما في الكاشير أو المنيو) | ✅ |
| السعر شامل الضريبة | ✅ |
| الفئة (من الفئات الـ11 أو فئة جديدة) | ✅ |
| الاسم العربي | قبل العرض |
| التوفر في DRIVE / HOUSE | قبل العرض |
| الوصف | اختياري |
| الصورة | لاحقًا |

- عند وصول الحقول الثلاثة الأولى يأخذ الصنف معرّف `PRD-00193` وما بعده.
- يُسجّل كإضافة (`ADDED`) في سجل التغييرات، مع مصدره (رسالتك أو ملف جديد).
- **Snacks وPastries:** `STATUS UNKNOWN — OWNER VERIFICATION REQUIRED` حتى تقرر.

## 4. DUP-04 — كل زوج على حدة

> لا دمج لأي زوج قبل اعتمادك (D-113). الأصناف العشرة `HOLD`.
> **ملاحظة مشتركة:** كل الأصناف العشرة بسعر 3.50 وفي COLD DRINKS. أصناف SHAKEN الخمسة تتبع نمط تسمية واحد (ICED SHAKEN + نكهة)، ولا يوجد لأي منها نسخة "بدون سكر"، بينما يوجد ذلك لـ3 من أصناف LATTE. معلومة عامة عن الصناعة وليست دليلًا من SHELTER: "Iced Shaken Espresso" عادة إسبريسو يُرج مع الثلج والسيرب بحليب أقل، و"Iced Latte" إسبريسو + حليب على الثلج.

| زوج | Product A (Source EN / AR) | Product B (Source EN / AR) | السعر A / B | Evidence |
|---|---|---|---|---|
| PAIR-1 | `PRD-00042` ICED SHAKEN CARAMEL<br>آيس شيكن كراميل | `PRD-00043` ICED CARAMEL LATTE<br>آيس كراميل لاتيه | 3.50 / 3.50 | صفان متجاوران (#42، #43). في الملف 5 مشروبات كراميل باردة: ICED SHAKEN CARAMEL و ICED CARAMEL LATTE و ICED CARAMEL FREE LATTE (#44) و ICED CARAMEL MACCHIATO (#61) و ICED SALTED CARAMEL LATTE (#66). المنيو القديم (CR-067) ذكر "Cold Caramel Latte" وصنفًا منفصلًا باسم "Iced Chicken" (ترجمة آلية لـ"آيس شيكن")، والنكهة غير معروفة |
| PAIR-2 | `PRD-00047` ICED SHAKEN WHITE MOCHA<br>آيس شيكن وايت موكا | `PRD-00062` ICED WHITE MOCHA<br>آيس وايت موكا | 3.50 / 3.50 | الصنف B اسمه ICED WHITE MOCHA (بدون كلمة LATTE). يوجد WHITE MOCHA ساخن (#31). المنيو القديم (CR-067) ذكر "Iced White Mocha 3.50" |
| PAIR-3 | `PRD-00052` ICED SHAKEN VANILLA<br>آيس شيكن فانيلا | `PRD-00048` ICED VANILLA LATTE<br>آيس فانيلا لاتيه | 3.50 / 3.50 | للفانيلا الباردة 3 أصناف في الملف: SHAKEN، و LATTE، و LATTE بدون سكر (#49) |
| PAIR-4 | `PRD-00057` ICED SHAKEN HAZELNUT<br>آيس شيكن هازلنت | `PRD-00053` ICED HAZELNUT LATTE<br>آيس هازلنت لاتيه | 3.50 / 3.50 | للبندق الباردة 3 أصناف في الملف: SHAKEN، و LATTE، و LATTE بدون سكر (#54) |
| PAIR-5 | `PRD-00067` ICED SHAKEN SALTED<br>آيس شيكن سولتد | `PRD-00066` ICED SALTED CARAMEL LATTE<br>آيس سولتد كراميل لاتيه | 3.50 / 3.50 | اسم A ناقص: "SALTED" بدون نكهة، والعربي "سولتد" فقط (MQ-12). ربطه بـ Salted Caramel افتراض منا — أكّد الاسم الكامل أولًا |

**السؤال لكل زوج:** نفس الصنف أم صنفان مختلفان؟
- **PAIR-5 فقط:** ما الاسم الكامل لـ`ICED SHAKEN SALTED`؟

## 5. Blockers حقيقية تمنع التجميد

| # | Blocker | يخص | المطلوب |
|---|---|---|---|
| B-1 | **أزواج DUP-04** | 10 معرّفات فقط | جوابك على الأزواج الخمسة |
| B-2 | **اعتماد خطة دمج DUP-01** | `PRD-00115` و`PRD-00120` | موافقة على بقاء `PRD-00115` ودمج `PRD-00120` فيه |
| B-3 | **اعتمادك لـInventory v1.0** | الكل | بعد B-1 وB-2 |

**ليست Blockers للتجميد** (المعرّفات لا تعتمد على الأسماء):
- **تصحيحات الأسماء (القسم 6):** مطلوبة لأسماء نظيفة على الموقع.
  - **توصيتي:** اعتمدها الآن كمجموعات حتى تصدر v1.0 بأسماء نهائية مرة واحدة.
- **الأسماء العربية:** مراجعة 166 اسمًا موجودًا + 28 اسمًا جديدًا. مطلوبة قبل العرض.
- **بيانات أخرى ناقصة:**
  - التوفر في الفرعين.
  - بيانات الأصناف الأربعة خارج الملف.
  - نظام الكاشير.
  - تواريخ SPRING.
  - الأوصاف والصور.
- **الأسماء غير الواضحة (MQ-12):** MESTEKH · ICED CROCCONATE · MILKSHAKE BAJ · MILKSHAKE ASH BERRY · V60 HONDURAS LAS · ROZY BASIL. أما ICED SHAKEN SALTED فضمن PAIR-5.

**جاهزية التجميد الآن:** 181 صنفًا `FREEZE-READY` + 11 فئة · 10 `HOLD` · `PRD-00120` يُحفظ كمعرّف مدموج.

## 6. تصحيحات الأسماء — كمجموعات

> **72 صنفًا** لها اقتراح محدد: 69 سابقًا + 3 بعد قرارات P0 (TURKISH S وD، و#129).
> - بعض الأصناف في أكثر من مجموعة، مثل `FRAPE VANILIA` في G1 وG2. إذا رُفضت مجموعة يُعاد حساب الاسم المقترح لهذه الأصناف.
> - عند الاعتماد يُكتب الاسم في `normalized_*`، وتبقى `source_*` كما هي.
> - التفاصيل لكل صنف في ورقتي `Naming_Groups` و`Possible_Corrections`.

| مجموعة | الموضوع | عدد الأصناف | أمثلة |
|---|---|---|---|
| G1 | أخطاء إملائية واضحة | 17 | VANILIA → VANILLA · CINAMON → CINNAMON · ICE IRSH → ICED IRISH · TOFFE → TOFFEE · ICE TEA → ICED TEA · POMGRANATE · EARLY GRAY → EARL GREY · COOCKIES · VALVET · CHOCLATE |
| G2 | FRAPE → FRAPPE | 14 | FRAPE VANILIA → FRAPPE VANILLA … |
| G3 | أسماء السموذي: البادئة SMOOTHIE + فك الاختصارات + علامة "+" | 19 | SMOOTHES MANGO+PASSION → SMOOTHIE MANGO + PASSION · PINAPPLE LMN STRWBERY → PINEAPPLE + LEMON + STRAWBERRY |
| G4 | صيغة "بدون سكر" وعائلة RED BULL | 9 | CARAMEL FREE LATTE → CARAMEL LATTE (SUGAR-FREE) · REDBULL → RED BULL |
| G5 | توحيد بسيط | 8 | 7 UP → 7UP · ESPRESSO D → ESPRESSO DOUBLE · CROCANTE → CROCCANTE · CHEESE CAKE → CHEESECAKE · AFFOGATO COFFEE → AFFOGATO |
| G6 | TURKISH COFFEE S / D (بعد قرار P0-01) | 2 | TURKISH COFFEE S → TURKISH COFFEE SINGLE · العربي "قهوة تركية سادة" → "قهوة تركية سينجل" (لأن "سادة" تصف السكر لا الحجم) |
| G7 | تصحيحات عربية | 11 | "شيلتر" → "شلتر" (5) · "(بدون سكر)" · "مياه معدنية" · إضافة "سموذي" |
| G8 | "آيس شيكن" (خيار واحد من ثلاثة) | 5 | (أ) كما هو · (ب) Shaken بالإنجليزي داخل الاسم العربي · (ج) وصف عربي مثل "مرجوج" |
| C1 | اسم الفئة: SPECIALITY COFFEE أم SPECIALTY COFFEE | فئة واحدة | الصيغتان صحيحتان |
| C2 | طريقة كتابة الأسماء على الموقع: ALL CAPS أم Title Case | كل الأصناف | عرض فقط، بدون تعديل البيانات |
| G9 | أسماء عربية جديدة للكيك والكوكيز | 26 + فئتان | ورقة Arabic_Name_Suggestions |

**صيغة الجواب:** لكل مجموعة: موافق / موافق مع استثناء (اذكره) / مرفوض. ولـG8 اختر (أ) أو (ب) أو (ج).
