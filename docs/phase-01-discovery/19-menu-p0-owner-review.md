# 19 — Menu: P0 Owner Review + Issue Separation

> **الحالة:** `OPEN — WAITING FOR OWNER` · **آخر تحديث:** 2026-10-01
> - **المعرّفات PROVISIONAL**، وغير مجمّدة حتى تُغلق أسئلة P0 ويصدر **Menu Inventory v1.0** المعتمد (D-090).
> - الملف المنظم: [`menu/SHELTER-MENU-INVENTORY-v0.2.xlsx`](menu/SHELTER-MENU-INVENTORY-v0.2.xlsx). ورقة `P0_Owner_Review` فيها عمود للإجابة.
> - الـData Model: [`17`](17-menu-data-model-draft.md) **v0.3**.
> - التقرير الأصلي (11 بندًا): [`18`](18-official-menu-inventory-report.md).
> - **لا Menu IA ولا UX/UI قبل إغلاق P0 (D-108).**

## 1. P0 Owner Review — فقط ما لا يمكن استنتاجه

| # | سؤال | القرار المطلوب | يخص | صيغة الجواب |
|---|---|---|---|---|
| 1 | MQ-02 | ما معنى S و D في TURKISH COFFEE S / TURKISH COFFEE D؟ | `PRD-00001` · `PRD-00002` | معنى كل حرف |
| 2 | MQ-03 | هل هذه أصناف مكررة أم مختلفة؟ DUP-01: #115 و#120 (سموذي أناناس/ليمون/فراولة) · DUP-02: AMERICAN COFFEE و AMERICANO، و ICED AMERICAN و ICED AMERICANO · DUP-03: ESPRESSO MACCHIATO و MACCHIATO · DUP-04: خمسة أزواج ICED SHAKEN و ICED … LATTE | 4 مجموعات | لكل مجموعة: مكرر / مختلف |
| 3 | MQ-04 | #129: ما الفاكهة الثالثة؟ (الإنجليزي "BA"، العربي "مانجو") | `PRD-00129` | اسم الفاكهة |
| 4 | MQ-05 | هذه الأصناف غير موجودة في ملفك: Cold Brew · Lotus Cheesecake · Ice Cream · Snacks · Pastries · Single Espresso. لكل واحد: متوقف رسميًا، أم يُباع ولم يرد في الملف؟ | 6 | متوقف / يُباع (+ السعر) |
| 5 | MQ-06 | SPRING: هل الأصناف الخمسة متاحة الآن؟ وما تواريخ الموسم إن وُجدت؟ | `PRD-00162` → `PRD-00166` | متاح / غير متاح + التواريخ |
| 6 | MQ-07 | هل يوجد لأي مشروب أكثر من حجم، أو إضافات مدفوعة (شوت إضافي، حليب بديل، نكهة)؟ | المشروبات | لا يوجد / يوجد (أرسل القائمة والأسعار) |
| 7 | MQ-09 | ما معنى عمود # في ملفك؟ وما اسم نظام الكاشير؟ | عمود # | المعنى + اسم النظام |
| 8 | MQ-17 | هل نعتبر 2026-10-01 تاريخ سريان أسعار هذا الملف (بداية Menu Version الأولى)؟ | MV-2026-10-01 | نعم / التاريخ الصحيح |

**غير مطلوب الآن** (موثق ولا يوقف التقدم):
- **MQ-01 التوفر في الفرعين:** كل الأصناف `UNKNOWN` في DRIVE وHOUSE، وتؤكد الاختلافات لاحقًا (D-094).
- **MQ-08 التجميع:** لا دمج. نقترح Product Families بعد اكتمال المراجعة، ثم تقرر (D-097).
- **أسئلة الأسماء MQ-10 → MQ-14:** تُعتمد كدفعة واحدة لاحقًا (D-093).

---

## 2. Source Errors — مشاكل في الملف نفسه (حقائق)

> لا نعدّل المصدر أبدًا (D-092). كل قيمة أصلية محفوظة في حقول `source_*`.

| # | المشكلة | أين | ما في الملف | المعالجة |
|---|---|---|---|---|
| SE-01 | تعارض بين الإنجليزي والعربي في الصنف نفسه | `PRD-00129` (#129) | الإنجليزي `SMOOTHES PASSION+PINAPPLE+BA` والعربي "سموذي باشن + أناناس + مانجو" | تُحفظ القيمتان كما هما · MISSING — OWNER INPUT REQUIRED (D-098) |
| SE-02 | اسم العلامة بالعربي يخالف القرار المعتمد | 5 أصناف (#82، #84، #86، #93، #139) | "شيلتر" بالياء، والمعتمد "شلتر" (D-007) | اقتراح تصحيح في Possible Corrections (AR-03) |
| SE-03 | عمود # ليس فريدًا | الورقتان | الأرقام 1–26 موجودة في الورقتين | SOURCE ROW NUMBER / UNKNOWN BUSINESS MEANING — لا يُستخدم معرّفًا (D-102) |
| SE-04 | فجوات في عمود # | الورقة الأولى | لا توجد الأرقام 150–151 و164–259 (98 رقمًا) | تُحفظ كما هي — لا إعادة ترقيم ولا ملء (D-099) |
| SE-05 | شكل الورقة الثانية مختلف | 26 صنفًا + فئتان | ترتيب أعمدة مختلف (Name قبل Category)، بلا صف عناوين، بلا أسماء عربية | قُرئت حسب ترتيبها؛ لا أثر على البيانات |
| SE-06 | مسافات زائدة في عناوين الفئات | COLD DRINKS · MILKSHAKE · SMOOTHIES | مسافة مزدوجة أو في آخر العنوان | تجميلي؛ النص الأصلي محفوظ في `source_header_raw` |

## 3. Possible Corrections — اقتراحات تُعتمد كدفعة واحدة لاحقًا

> **لم يُطبّق أي تصحيح، حتى الواضح منها** مثل VANILIA → VANILLA (D-093).
> بعد الاعتماد يُكتب التصحيح في `normalized_*`، وتبقى `source_*` كما هي.
> التفاصيل لكل صنف في ورقة `Possible_Corrections`: **69 صنفًا** لها اقتراح محدد، و5 أصناف لها خيارات (AR-04).

| قاعدة | النوع | عدد الأصناف | مثال (Source → Suggested) | السبب |
|---|---|---|---|---|
| NR-01 | SPELL | 4 | VANILIA LATTE → VANILLA LATTE | الملف نفسه يكتب VANILLA في #48 و#49 و#52 |
| NR-02 | CONSIST | 7 | CARAMEL FREE LATTE → CARAMEL LATTE (SUGAR-FREE) | كلمة FREE وحدها غامضة، والاسم العربي في الملف نفسه "(بدون سكر)" |
| NR-03 | CONSIST | 3 | REDBULL FREE SUGAR → RED BULL (SUGAR-FREE) | الملف يكتب RED BULL منفصلة في #78 و#86 و#87 |
| NR-04 | SPELL | 2 | CINAMON NUT LATTE → CINNAMON NUT LATTE | خطأ إملائي |
| NR-05 | SPELL | 1 | ICE IRSH LATTE → ICED IRISH LATTE | خطآن: ICE بدل ICED، وIRSH بدل IRISH (الملف يكتب IRISH في #25 و#33) |
| NR-06 | SPELL | 1 | ICED TOFFE NUT LATTE → ICED TOFFEE NUT LATTE | خطأ إملائي |
| NR-07 | SPELL | 3 | ICE TEA BERRY → ICED TEA BERRY | الملف يكتب ICED TEA في #72 و#73 و#74 |
| NR-08 | CONSIST | 1 | MOJITO 7 UP → MOJITO 7UP | الملف يكتب 7UP في #79 و#84 |
| NR-09 | CONSIST | 15 | SMOOTHIES ORANGE → SMOOTHIE ORANGE | البادئة مكتوبة بثلاث صيغ (SMOOTHIE / SMOOTHIES / SMOOTHES)، وصنفان بلا بادئة |
| NR-10 | ABBR | 9 | SMOOTHIE PINAPPLE LMN STRWBERY → SMOOTHIE PINEAPPLE + LEMON + STRAWBERRY | PINAPPLE / STRWBERY / STRWBERRY / STRW / STRAW / PINA / LMN، والرموز _ و - بدل + |
| NR-11 | SPELL | 14 | FRAPE VANILIA → FRAPPE VANILLA | الفئة نفسها مكتوبة FRAPPE |
| NR-12 | SPELL | 1 | TEA EARLY GRAY → TEA EARL GREY | الاسم الصحيح للشاي Earl Grey، والعربي في الملف صحيح "إيرل غراي" |
| NR-13 | ABBR | 1 | ESPRESSO D → ESPRESSO DOUBLE | اختصار، والعربي في الملف "إسبريسو دوبل" |
| NR-14 | SPELL | 3 | COOCKIES CHOCOLATE → COOKIES CHOCOLATE | الفئة نفسها مكتوبة COOKIES |
| NR-15 | SPELL | 1 | RED VALVET CAKE → RED VELVET CAKE | خطأ إملائي |
| NR-16 | SPELL | 1 | GERMAN CHOCLATE → GERMAN CHOCOLATE | خطأ إملائي |
| NR-17 | CONSIST | 1 | CROCANTE CAKE → CROCCANTE CAKE | الملف يكتب CROCCANTE في #102 و#143 |
| NR-18 | CONSIST | 3 | MILKSHAKE CHEESE CAKE → MILKSHAKE CHEESECAKE | الملف يكتب CHEESECAKE في كيك #17 و#19 |
| NR-19 | CONSIST | 1 | NUTELLA COOKIES → COOKIES NUTELLA | 4 من 5 أصناف تبدأ بـ COOKIES |
| NR-20 | CONSIST | 1 | AFFOGATO COFFEE → AFFOGATO | العربي في الملف "أفوغاتو" فقط |
| NR-21 | CATEGORY | 1 | SPECIALITY COFFEE → SPECIALTY COFFEE | الصيغتان صحيحتان — قرارك (MQ-14) |
| NR-22 | CATEGORY | — | مفرد/جمع في أسماء الفئات | مرحلة Menu IA |
| NR-23 | DISPLAY | — | ALL CAPS → طريقة عرض | قرارك (MQ-14) — بدون تعديل البيانات |
| AR-03 | ARABIC | 5 | شيلتر إنرجي كود رد → شلتر إنرجي كود رد | "شيلتر" → "شلتر" (D-007) |
| AR-05 | ARABIC | 3 | آيس كراميل لاتيه بدون سكر → آيس كراميل لاتيه (بدون سكر) | توحيد "(بدون سكر)" |
| AR-06 | ARABIC | 1 | ماء معدنية → مياه معدنية | "ماء معدنية" → "مياه معدنية" |
| AR-07 | ARABIC | 2 | فراولة + مانجو + باشن → سموذي فراولة + مانجو + باشن | إضافة "سموذي" |
| AR-04 | ARABIC | 5 | آيس شيكن … | الموقع القديم ترجمها آليًا "Iced Chicken" — 3 خيارات لقرارك |

## 4. Possible Duplicates — لا دمج

> الحالة: `POSSIBLE DUPLICATE / OWNER REVIEW REQUIRED`
> **Preserve first, merge later (D-096).** أي دمج لاحق يحفظ: Source row · Source name · Price · POS reference · Historical identity.

| # | الأصناف (المصدر — السعر) | الملاحظة | الحالة |
|---|---|---|---|
| DUP-01 | `PRD-00115` SMOOTHIE PINAPPLE LMN STRWBERY — 3.50<br>`PRD-00120` SMOOTHIES PINA-STRAW-LEMON — 3.50 | نفس الفواكه الثلاث (أناناس + ليمون + فراولة) بترتيب مختلف، ونفس السعر 3.50، والعربي متطابق تقريبًا | POSSIBLE DUPLICATE / OWNER REVIEW REQUIRED — لا دمج (D-096) |
| DUP-02 | `PRD-00003` AMERICAN COFFEE — 2.00<br>`PRD-00004` AMERICANO — 2.00<br>`PRD-00040` ICED AMERICAN — 2.75<br>`PRD-00045` ICED AMERICANO — 2.75 | AMERICAN COFFEE وAMERICANO بنفس السعر (2.00)، وICED AMERICAN وICED AMERICANO بنفس السعر (2.75) | POSSIBLE DUPLICATE / OWNER REVIEW REQUIRED — لا دمج (D-096) |
| DUP-03 | `PRD-00011` ESPRESSO MACCHIATO — 2.50<br>`PRD-00012` MACCHIATO — 2.50 | ESPRESSO MACCHIATO وMACCHIATO بنفس السعر (2.50) | POSSIBLE DUPLICATE / OWNER REVIEW REQUIRED — لا دمج (D-096) |
| DUP-04 | `PRD-00042` ICED SHAKEN CARAMEL — 3.50<br>`PRD-00043` ICED CARAMEL LATTE — 3.50<br>`PRD-00047` ICED SHAKEN WHITE MOCHA — 3.50<br>`PRD-00062` ICED WHITE MOCHA — 3.50<br>`PRD-00052` ICED SHAKEN VANILLA — 3.50<br>`PRD-00048` ICED VANILLA LATTE — 3.50<br>`PRD-00057` ICED SHAKEN HAZELNUT — 3.50<br>`PRD-00053` ICED HAZELNUT LATTE — 3.50<br>`PRD-00067` ICED SHAKEN SALTED — 3.50<br>`PRD-00066` ICED SALTED CARAMEL LATTE — 3.50 | خمسة أزواج SHAKEN / LATTE بنفس النكهة ونفس السعر 3.50 | POSSIBLE DUPLICATE / OWNER REVIEW REQUIRED — لا دمج (D-096) |

## 5. Missing Business Information

| # | المعلومة الناقصة | النطاق | الحالة | المرجع |
|---|---|---|---|---|
| MB-01 | التوفر في DRIVE وHOUSE | 192 × 2 | UNKNOWN | MQ-01 — لا يوقف الـData Model (D-094) |
| MB-02 | الأحجام | كل الأصناف | MISSING — OWNER INPUT REQUIRED | MQ-07 (D-101) |
| MB-03 | الإضافات والـModifiers | كل الأصناف | MISSING — OWNER INPUT REQUIRED | MQ-07 (D-101) |
| MB-04 | معنى عمود # + نظام الكاشير + أرقام الأصناف فيه | كل الأصناف | SOURCE ROW NUMBER / UNKNOWN BUSINESS MEANING · POS = MISSING | MQ-09 (D-102) |
| MB-05 | معنى S و D | TURKISH COFFEE S / D | NEEDS OWNER VERIFICATION | MQ-02 (D-095) |
| MB-06 | الفاكهة الثالثة في #129 | 1 | MISSING — OWNER INPUT REQUIRED | MQ-04 (D-098) |
| MB-07 | ما هو الصنف / اسمه الكامل | 7: MESTEKH · ICED CROCCONATE · ICED SHAKEN SALTED · MILKSHAKE BAJ · MILKSHAKE ASH BERRY · V60 HONDURAS LAS · ROZY BASIL | MISSING — OWNER INPUT REQUIRED | MQ-12 |
| MB-08 | توفر SPRING الحالي وتواريخ الموسم | 5 | SEASONAL CATEGORY · CURRENT AVAILABILITY = OWNER VERIFICATION REQUIRED | MQ-06 (D-100) — لا يوجد لدينا دليل تاريخي على موسم محدد |
| MB-09 | أصناف غير موجودة في ملفك | Cold Brew · Lotus Cheesecake · Ice Cream · Snacks · Pastries · Single Espresso · وغيرها | NOT PRESENT IN CURRENT OWNER FILE (ليس DISCONTINUED) | MQ-05 (D-099) |
| MB-10 | تاريخ سريان أسعار هذا الملف | Menu Version MV-2026-10-01 | OWNER VERIFICATION REQUIRED — المسجّل فقط: تاريخ الاستلام 2026-10-01 | MQ-17 (D-107) |
| MB-11 | أسماء عربية ناقصة | 26 صنفًا + فئتان (الورقة الثانية) | MISSING — مقترحات غير معتمدة جاهزة | MQ-13 |
| MB-12 | مراجعة الأسماء العربية الموجودة في الملف | 166 صنفًا + 9 فئات | SOURCE-PROVIDED ARABIC NAME · PENDING OWNER REVIEW | MQ-11 (D-091) |
| MB-13 | الوصف · المكونات · الحساسية · السعرات | كل الأصناف | DESCRIPTION MISSING / MISSING | D-081 |
| MB-14 | الصور | كل الأصناف | MISSING | D-083 |
| MB-15 | وحدة سعر الكيك والكوكيز (قطعة / شريحة) | 26 | MISSING | MQ-16 (P2) |
| MB-16 | قواعد أسماء العرض: SPECIALITY أم SPECIALTY، والأحرف الكبيرة | الفئات + كل الأصناف | OWNER DECISION | MQ-14 |

## 6. ما سحبناه أو صححناه من التقرير السابق

| ما كان في `18` (النسخة الأولى) | الآن |
|---|---|
| توصية بتجميع "بدون سكر" وRED BULL كـVariants تحت صنف واحد (MQ-08، DM-01 v0.2) | **مسحوبة.** كل صنف من المصدر كيان مستقل. التجميع البصري (Product Family) منفصل عن هوية البيانات ويحتاج قرارك (D-097) |
| `PRD-xxxxx-V01` كوحدة بيع افتراضية لكل صنف | **مسحوبة.** السعر مرتبط بالصنف، والحجم غير محدد = `MISSING`. الأحجام تُنشأ فقط من مصدر رسمي (D-101) |
| "قد تكون S/D = Single/Double" (استنتاج من السعر) | **مسحوب.** `NEEDS OWNER VERIFICATION` (D-095) |
| "على الأغلب صنف مكرر" (DUP-01) و"قد تعني Banana" (#129) | **مسحوب.** `POSSIBLE DUPLICATE` و`MISSING — OWNER INPUT REQUIRED` |
| "غير موجود في الملف" | `NOT PRESENT IN CURRENT OWNER FILE`، وليس `DISCONTINUED` (D-099) |
| المعرّفات "تُجمّد عند اعتماد التقرير" | `PROVISIONAL` حتى إغلاق P0 وإصدار v1.0 (D-090) |
| `amount_minor` | `price_fils` + `currency = JOD` + `tax_inclusive = true` (D-103) |
