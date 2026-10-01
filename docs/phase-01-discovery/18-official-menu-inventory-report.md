# 18 — Official Menu File: Inventory Report (R3)

> **الحالة:** `DRAFT — PENDING OWNER APPROVAL` · **آخر تحديث:** 2026-10-01
>
> **المصدر:** ملف المنيو الرسمي من الـOwner، استُلم 2026-10-01.
> - نسخة مطابقة للأصل بدون أي تعديل: [`menu/source/OWNER-OFFICIAL-MENU-2026-10-01.xlsx`](menu/source/OWNER-OFFICIAL-MENU-2026-10-01.xlsx)
> - SHA-256: `dfde223f219443454872e81ae64a1e8d4c55e486fe52a29f87ca1682979c8d97`
>
> **النسخة المنظمة (للمراجعة والاعتماد):**
> - [`menu/SHELTER-MENU-INVENTORY-v0.1.xlsx`](menu/SHELTER-MENU-INVENTORY-v0.1.xlsx) — فيها عمود `owner_decision` لكل صف.
> - [`menu/menu-inventory-v0.1.csv`](menu/menu-inventory-v0.1.csv)
>
> **القواعد المطبقة (D-076، D-079، D-081، D-082، D-089):**
> - الملف = **PRIMARY OFFICIAL MENU SOURCE** للحقول الموجودة فيه فقط.
> - السعر = السعر النهائي للزبون **شامل الضريبة**: لم تُضف ضريبة، ولم يُعدَّل أي سعر، ولم يُحسب سعر قبل الضريبة.
> - **لم يُصحَّح أي اسم.** كل تصحيح معروض بصيغة Current / Suggested / Reason وينتظر موافقتك.
> - أي حقل غير موجود في الملف = `MISSING — OWNER INPUT REQUIRED`.
> - المنيو القديم استُخدم للمقارنة فقط.
> - **لا UI** قبل اعتماد البيانات.

---

## الخلاصة

| البند | النتيجة |
|---|---|
| الفئات | **11** (9 في الورقة الأولى + 2 في الورقة الثانية) — لا فئة مكررة |
| الأصناف | **192** (166 مشروبًا في الورقة الأولى + 26 حلوى في الورقة الثانية) |
| نطاق الأسعار | **0.50 → 4.25 د.أ** (شامل الضريبة) — الوسيط 3.50 |
| أسعار ناقصة / صفر / غير رقمية | **0** |
| صفوف فارغة | **0** |
| تكرار حرفي في الأسماء | **0** · **4 حالات تشابه** تحتاج تأكيدك (DUP-01 → DUP-04) |
| أسماء إنجليزية فيها ملاحظات | **66 صنفًا** ضمن 20 قاعدة (NR-01 → NR-20) + 3 ملاحظات على الفئات والعرض |
| الأسماء العربية | **موجودة لـ166 صنفًا في الورقة الأولى** (أكثر مما توقعنا)، و8 ملاحظات عليها · **ناقصة لـ26 صنفًا + فئتين** في الورقة الثانية ← مقترحات للاعتماد |
| أسئلة التحقق | **9 أسئلة P0** قبل تجميد البيانات · 5 أسئلة P1 للأسماء · 2 أسئلة P2 لمرحلة الـIA |

**ملاحظات على شكل الملف:**
1. **الورقتان بترتيب أعمدة مختلف:**
   - الورقة الأولى: `# · Category · Item Name (EN) · Item Name (AR) · Price (JOD)` مع صف عناوين.
   - الورقة الثانية: `# · Name · Category · Price` بدون صف عناوين وبدون عربي.
   - تمت قراءة كل ورقة حسب ترتيبها، والتحقق من أن عمود الفئة يطابق عنوان القسم في **كل** الصفوف.
2. **عمود # ليس معرّفًا فريدًا:**
   - الأرقام 1–26 تتكرر بين الورقتين.
   - الورقة الأولى تقفز من 149 إلى 152، ومن 163 إلى 260.
   - لذلك لم نستخدمه معرّفًا، وحفظناه كمرجع للمصدر (MQ-09).
3. **الأسماء الإنجليزية تبدو أسماء نظام كاشير مختصرة:**
   - كلها بأحرف كبيرة، وأطولها 30 حرفًا بالضبط.
   - فيها اختصارات مثل LMN وSTRW وPINA وBA وS وD.
   - **هذه ملاحظة وليست حقيقة مؤكدة.**
4. **مسافات زائدة في 3 عناوين فئات** (COLD DRINKS، MILKSHAKE، SMOOTHIES) تم تجاهلها عند القراءة. لا أثر لها على أسماء الأصناف.

---

## 1. Categories list

| Category ID | Category (EN — كما في الملف) | الاسم العربي (من الملف) | المصدر | عدد الأصناف |
|---|---|---|---|---|
| `CAT-001` | HOT DRINKS | مشروبات ساخنة | Sheet1 — صف 2 | 39 |
| `CAT-002` | COLD DRINKS | مشروبات باردة | Sheet1 — صف 42 | 36 |
| `CAT-003` | FIZZY DRINKS | مشروبات غازية | Sheet1 — صف 79 | 15 |
| `CAT-004` | MILKSHAKE | ميلك شيك | Sheet1 — صف 95 | 21 |
| `CAT-005` | SMOOTHIES | سموذي | Sheet1 — صف 117 | 24 |
| `CAT-006` | FRAPPE | فرابيه | Sheet1 — صف 142 | 14 |
| `CAT-007` | TEA | شاي | Sheet1 — صف 157 | 8 |
| `CAT-008` | SPECIALITY COFFEE | قهوة مختصة | Sheet1 — صف 166 | 4 |
| `CAT-009` | SPRING | سبرينغ | Sheet1 — صف 171 | 5 |
| `CAT-010` | CAKE | `MISSING` — مقترح: كيك (MQ-13) | Sheet2 — صف 1 | 21 |
| `CAT-011` | COOKIES | `MISSING` — مقترح: كوكيز (MQ-13) | Sheet2 — صف 23 | 5 |
| | **المجموع** | | | **192** |

## 2. Number of categories

**11 فئة:**
- 9 فئات مشروبات: HOT DRINKS → SPRING.
- فئتا حلويات: CAKE وCOOKIES.
- لا توجد فئة مكررة.
- لا توجد فئات فرعية في الملف.

## 3. Product count

**192 صنفًا:**
- 166 في الورقة الأولى (المشروبات).
- 26 في الورقة الثانية (الكيك والكوكيز).
- كل صنف له سعر واحد. لا توجد أحجام أو خيارات في الملف.

> إذا اعتمدت التوصية في MQ-08 (تجميع "بدون سكر" وعائلة RED BULL كخيارات)، يبقى عدد **وحدات البيع** 192 وينخفض عدد **البطاقات المعروضة**. وإذا ثبت أن DUP-01 مكرر، يصبح العدد 191.

## 4. Products grouped by category

> رقم الـProduct ID **لا يساوي** رقم الملف. التطابق في أول 149 صنفًا صدفة لأن الترقيم متتابع هناك.
> عمود "ملاحظات" يشير إلى الأقسام 7 و8 و9.

#### `CAT-001` HOT DRINKS — مشروبات ساخنة · 39 صنفًا

| Product ID | # في الملف | Name EN (كما في الملف) | Name AR (كما في الملف) | السعر د.أ (شامل الضريبة) | ملاحظات |
|---|---|---|---|---|---|
| `PRD-00001` | 1 | TURKISH COFFEE S | قهوة تركية سادة | 1.50 | MQ-02 AR-01 |
| `PRD-00002` | 2 | TURKISH COFFEE D | قهوة تركية وسط/حلوة | 2.50 | MQ-02 AR-01 |
| `PRD-00003` | 3 | AMERICAN COFFEE | قهوة أمريكية | 2.00 | DUP-02 |
| `PRD-00004` | 4 | AMERICANO | أمريكانو | 2.00 | DUP-02 |
| `PRD-00005` | 5 | RED EYE | ريد آي | 2.50 |  |
| `PRD-00006` | 6 | BLACK EYE | بلاك آي | 2.75 |  |
| `PRD-00007` | 7 | DEAD EYE | ديد آي | 3.00 |  |
| `PRD-00008` | 8 | EYE OF THE TIGER | آي أوف ذا تايغر | 3.25 |  |
| `PRD-00009` | 9 | ESPRESSO D | إسبريسو دوبل | 2.00 | NR-13 |
| `PRD-00010` | 10 | ESPRESSO CON PANNA | إسبريسو كون بانا | 2.50 |  |
| `PRD-00011` | 11 | ESPRESSO MACCHIATO | إسبريسو ماكياتو | 2.50 | DUP-03 |
| `PRD-00012` | 12 | MACCHIATO | ماكياتو | 2.50 | DUP-03 |
| `PRD-00013` | 13 | CARAMEL MACCHIATO | كراميل ماكياتو | 3.00 |  |
| `PRD-00014` | 14 | CAPPUCCINO | كابتشينو | 2.50 |  |
| `PRD-00015` | 15 | LATTE | لاتيه | 2.50 |  |
| `PRD-00016` | 16 | FLAT WHITE | فلات وايت | 2.50 |  |
| `PRD-00017` | 17 | CORTADO | كورتادو | 2.50 |  |
| `PRD-00018` | 18 | SPANISH LATTE | سبانيش لاتيه | 3.00 |  |
| `PRD-00019` | 19 | FRENCH COFFEE | قهوة فرنسية | 3.25 |  |
| `PRD-00020` | 20 | CARAMEL LATTE | كراميل لاتيه | 2.75 |  |
| `PRD-00021` | 21 | VANILIA LATTE | فانيلا لاتيه | 2.75 | NR-01 |
| `PRD-00022` | 22 | HAZELNUT LATTE | هازلنت لاتيه | 2.75 |  |
| `PRD-00023` | 23 | CINAMON NUT LATTE | سينامون نت لاتيه | 3.00 | NR-04 |
| `PRD-00024` | 24 | SALTED CARAMEL LATTE | سولتد كراميل لاتيه | 3.00 |  |
| `PRD-00025` | 25 | IRISH LATTE | آيريش لاتيه | 3.00 |  |
| `PRD-00026` | 26 | MATCHA LATTE | ماتشا لاتيه | 3.50 |  |
| `PRD-00027` | 27 | CARAMEL FREE LATTE | كراميل لاتيه (بدون سكر) | 2.75 | NR-02 AR-05 MQ-08 |
| `PRD-00028` | 28 | VANILIA FREE LATTE | فانيلا لاتيه (بدون سكر) | 2.75 | NR-01 NR-02 AR-05 MQ-08 |
| `PRD-00029` | 29 | HAZELNUT FREE LATTE | هازلنت لاتيه (بدون سكر) | 2.75 | NR-02 AR-05 MQ-08 |
| `PRD-00030` | 30 | MOCHA | موكا | 3.00 |  |
| `PRD-00031` | 31 | WHITE MOCHA | وايت موكا | 3.00 |  |
| `PRD-00032` | 32 | AFFOGATO COFFEE | أفوغاتو | 2.50 | NR-20 |
| `PRD-00033` | 33 | IRISH COFFEE | آيريش كوفي | 2.75 |  |
| `PRD-00034` | 34 | NESCAFE | نسكافيه | 1.50 |  |
| `PRD-00035` | 35 | MESTEKH | مستكة | 2.50 | MQ-12 |
| `PRD-00036` | 36 | HOT CHOCOLATE | هوت شوكليت | 2.00 |  |
| `PRD-00037` | 37 | HOT LOTUS | هوت لوتس | 3.50 |  |
| `PRD-00038` | 38 | HOT PISTACHIO | هوت فستق | 3.50 |  |
| `PRD-00039` | 39 | HOT NUTELLA | هوت نوتيلا | 3.50 |  |

#### `CAT-002` COLD DRINKS — مشروبات باردة · 36 صنفًا

| Product ID | # في الملف | Name EN (كما في الملف) | Name AR (كما في الملف) | السعر د.أ (شامل الضريبة) | ملاحظات |
|---|---|---|---|---|---|
| `PRD-00040` | 40 | ICED AMERICAN | آيس أمريكان | 2.75 | DUP-02 |
| `PRD-00041` | 41 | ICED CAPPUCCINO | آيس كابتشينو | 3.50 |  |
| `PRD-00042` | 42 | ICED SHAKEN CARAMEL | آيس شيكن كراميل | 3.50 | DUP-04 AR-04 |
| `PRD-00043` | 43 | ICED CARAMEL LATTE | آيس كراميل لاتيه | 3.50 |  |
| `PRD-00044` | 44 | ICED CARAMEL FREE LATTE | آيس كراميل لاتيه بدون سكر | 3.50 | NR-02 AR-05 MQ-08 |
| `PRD-00045` | 45 | ICED AMERICANO | آيس أمريكانو | 2.75 | DUP-02 |
| `PRD-00046` | 46 | ICED LATTE | آيس لاتيه | 3.00 |  |
| `PRD-00047` | 47 | ICED SHAKEN WHITE MOCHA | آيس شيكن وايت موكا | 3.50 | DUP-04 AR-04 |
| `PRD-00048` | 48 | ICED VANILLA LATTE | آيس فانيلا لاتيه | 3.50 |  |
| `PRD-00049` | 49 | ICED VANILLA FREE LATTE | آيس فانيلا لاتيه بدون سكر | 3.50 | NR-02 AR-05 MQ-08 |
| `PRD-00050` | 50 | ICED MATCHA | آيس ماتشا | 4.25 |  |
| `PRD-00051` | 51 | ICED NUTELLA | آيس نوتيلا | 3.75 |  |
| `PRD-00052` | 52 | ICED SHAKEN VANILLA | آيس شيكن فانيلا | 3.50 | DUP-04 AR-04 |
| `PRD-00053` | 53 | ICED HAZELNUT LATTE | آيس هازلنت لاتيه | 3.50 |  |
| `PRD-00054` | 54 | ICED HAZELNUT FREE LATTE | آيس هازلنت لاتيه بدون سكر | 3.50 | NR-02 AR-05 MQ-08 |
| `PRD-00055` | 55 | ICED PISTACHIO | آيس فستق | 3.75 |  |
| `PRD-00056` | 56 | ICED LOTUS | آيس لوتس | 3.75 |  |
| `PRD-00057` | 57 | ICED SHAKEN HAZELNUT | آيس شيكن هازلنت | 3.50 | DUP-04 AR-04 |
| `PRD-00058` | 58 | ICED SPANISH LATTE | آيس سبانيش لاتيه | 3.50 |  |
| `PRD-00059` | 59 | ICED CHOCOLATE | آيس شوكليت | 3.50 |  |
| `PRD-00060` | 60 | ICED CROCCONATE | آيس كروكونات | 3.75 | MQ-12 NR-17 |
| `PRD-00061` | 61 | ICED CARAMEL MACCHIATO | آيس كراميل ماكياتو | 3.50 |  |
| `PRD-00062` | 62 | ICED WHITE MOCHA | آيس وايت موكا | 3.50 |  |
| `PRD-00063` | 63 | ICED MOCHA | آيس موكا | 3.50 |  |
| `PRD-00064` | 64 | ICE IRSH LATTE | آيس آيريش لاتيه | 3.50 | NR-05 |
| `PRD-00065` | 65 | ICED TOFFE NUT LATTE | آيس توفي نت لاتيه | 3.50 | NR-06 |
| `PRD-00066` | 66 | ICED SALTED CARAMEL LATTE | آيس سولتد كراميل لاتيه | 3.50 |  |
| `PRD-00067` | 67 | ICED SHAKEN SALTED | آيس شيكن سولتد | 3.50 | DUP-04 AR-04 MQ-12 |
| `PRD-00068` | 68 | ICED CINAMON NUT LATTE | آيس سينامون نت لاتيه | 3.50 | NR-04 |
| `PRD-00069` | 69 | ICE TEA BERRY | آيس تي بيري | 3.00 | NR-07 |
| `PRD-00070` | 70 | ICE TEA POMGRANATE | آيس تي رمان | 3.00 | NR-07 |
| `PRD-00071` | 71 | ICE TEA PASSION + PEACH | آيس تي باشن + بيتش | 3.25 | NR-07 |
| `PRD-00072` | 72 | ICED TEA PEACH | آيس تي بيتش | 3.00 |  |
| `PRD-00073` | 73 | ICED TEA LEMON | آيس تي ليمون | 3.00 |  |
| `PRD-00074` | 74 | ICED TEA MIX | آيس تي مكس | 3.25 |  |
| `PRD-00075` | 75 | KIDS STRAWBERRY | كيدز فراولة | 3.50 |  |

#### `CAT-003` FIZZY DRINKS — مشروبات غازية · 15 صنفًا

| Product ID | # في الملف | Name EN (كما في الملف) | Name AR (كما في الملف) | السعر د.أ (شامل الضريبة) | ملاحظات |
|---|---|---|---|---|---|
| `PRD-00076` | 76 | MOJITO 7 UP | موهيتو سفن أب | 3.25 | NR-08 |
| `PRD-00077` | 77 | CODE RED | كود رد | 2.00 |  |
| `PRD-00078` | 78 | RED BULL | ريد بُل | 3.00 | MQ-08 |
| `PRD-00079` | 79 | 7UP | سفن أب | 1.50 |  |
| `PRD-00080` | 80 | MINERAL WATER | ماء معدنية | 0.50 | AR-06 MQ-15 |
| `PRD-00081` | 81 | MOJITO SODA | موهيتو صودا | 3.25 |  |
| `PRD-00082` | 82 | SHELTER ENERGY CODE RED | شيلتر إنرجي كود رد | 2.50 | AR-03 |
| `PRD-00083` | 83 | REDBULL FREE SUGAR | ريد بُل بدون سكر | 3.00 | NR-02 NR-03 MQ-08 |
| `PRD-00084` | 84 | 7UP SHELTER | سفن أب شيلتر | 2.00 | AR-03 |
| `PRD-00085` | 85 | MOJITO CODE RED | موهيتو كود رد | 3.50 |  |
| `PRD-00086` | 86 | SHELTER ENERGY RED BULL | شيلتر إنرجي ريد بُل | 3.25 | AR-03 |
| `PRD-00087` | 87 | MOJITO RED BULL | موهيتو ريد بُل | 4.00 |  |
| `PRD-00088` | 88 | REDBULL WATERMELON | ريد بُل بطيخ | 3.00 | NR-03 MQ-08 |
| `PRD-00089` | 89 | REDBULL COCONUT | ريد بُل جوز هند | 3.00 | NR-03 MQ-08 |
| `PRD-00090` | 90 | SODA SUMMER CRUSH | صودا سامر كراش | 2.00 |  |

#### `CAT-004` MILKSHAKE — ميلك شيك · 21 صنفًا

| Product ID | # في الملف | Name EN (كما في الملف) | Name AR (كما في الملف) | السعر د.أ (شامل الضريبة) | ملاحظات |
|---|---|---|---|---|---|
| `PRD-00091` | 91 | MILKSHAKE VANILIA | ميلك شيك فانيلا | 3.50 | NR-01 |
| `PRD-00092` | 92 | MILKSHAKE OREO | ميلك شيك أوريو | 3.50 |  |
| `PRD-00093` | 93 | MILKSHAKE SHELTER | ميلك شيك شيلتر | 3.50 | AR-03 |
| `PRD-00094` | 94 | MILKSHAKE CARAMEL | ميلك شيك كراميل | 3.50 |  |
| `PRD-00095` | 95 | MILKSHAKE PISTACHIO | ميلك شيك فستق | 4.00 |  |
| `PRD-00096` | 96 | MILKSHAKE CHOCOLATE | ميلك شيك شوكليت | 3.50 |  |
| `PRD-00097` | 97 | MILKSHAKE NUTELLA | ميلك شيك نوتيلا | 3.50 |  |
| `PRD-00098` | 98 | MILKSHAKE LOTUS | ميلك شيك لوتس | 3.50 |  |
| `PRD-00099` | 99 | MILKSHAKE TWIX | ميلك شيك تويكس | 3.50 |  |
| `PRD-00100` | 100 | MILKSHAKE SNICKERS | ميلك شيك سنيكرز | 3.50 |  |
| `PRD-00101` | 101 | MILKSHAKE CHEESE CAKE | ميلك شيك تشيز كيك | 3.50 | NR-18 |
| `PRD-00102` | 102 | MILKSHAKE CROCCANTE | ميلك شيك كروكانتي | 3.50 |  |
| `PRD-00103` | 103 | MILKSHAKE SALTED CARAMEL | ميلك شيك سولتد كراميل | 3.50 |  |
| `PRD-00104` | 104 | MILKSHAKE FERRERO | ميلك شيك فيريرو | 3.50 |  |
| `PRD-00105` | 105 | MILKSHAKE BAJ | ميلك شيك بَج | 3.75 | MQ-12 |
| `PRD-00106` | 106 | MILKSHAKE FERRERO NUTELLA | ميلك شيك فيريرو نوتيلا | 4.00 |  |
| `PRD-00107` | 107 | MILKSHAKE ASH BERRY | ميلك شيك آش بيري | 3.75 | MQ-12 |
| `PRD-00108` | 108 | MILKSHAKE STRAWBERRY | ميلك شيك فراولة | 3.75 |  |
| `PRD-00109` | 109 | MILKSHAKE BLUEBERRY | ميلك شيك بلوبيري | 3.75 |  |
| `PRD-00110` | 110 | MILKSHAKE OWAR QALB | ميلك شيك عَوَر قلب | 3.75 |  |
| `PRD-00111` | 111 | MILKSHAKE MIXBERRY | ميلك شيك مكس بيري | 3.75 |  |

#### `CAT-005` SMOOTHIES — سموذي · 24 صنفًا

| Product ID | # في الملف | Name EN (كما في الملف) | Name AR (كما في الملف) | السعر د.أ (شامل الضريبة) | ملاحظات |
|---|---|---|---|---|---|
| `PRD-00112` | 112 | SMOOTHIES ORANGE | سموذي برتقال | 3.50 | NR-09 |
| `PRD-00113` | 113 | SMOOTHIE MANGO | سموذي مانجو | 3.50 |  |
| `PRD-00114` | 114 | SMOOTHIES LEMON | سموذي ليمون | 3.50 | NR-09 |
| `PRD-00115` | 115 | SMOOTHIE PINAPPLE LMN STRWBERY | سموذي أناناس + ليمون + فراولة | 3.50 | NR-10 DUP-01 |
| `PRD-00116` | 116 | SMOOTHIE STRAWBERRY | سموذي فراولة | 3.50 |  |
| `PRD-00117` | 117 | SMOOTHIE ORANGE_PASSION | سموذي برتقال + باشن | 3.50 | NR-10 |
| `PRD-00118` | 118 | SMOOTHIE MANGO STRWBERRY | سموذي مانجو + فراولة | 3.50 | NR-10 |
| `PRD-00119` | 119 | SMOOTHIE LEMON MINT | سموذي ليمون نعناع | 3.50 |  |
| `PRD-00120` | 120 | SMOOTHIES PINA-STRAW-LEMON | سموذي أناناس + فراولة + ليمون | 3.50 | NR-09 NR-10 DUP-01 |
| `PRD-00121` | 121 | SMOOTHIES BLUEBERRY | سموذي بلوبيري | 3.50 | NR-09 |
| `PRD-00122` | 122 | SMOOTHIES KIWI | سموذي كيوي | 3.50 | NR-09 |
| `PRD-00123` | 123 | SMOOTHIES PINAPPLE | سموذي أناناس | 3.50 | NR-09 NR-10 |
| `PRD-00124` | 124 | SMOOTHIES MIXBERRY | سموذي مكس بيري | 3.50 | NR-09 |
| `PRD-00125` | 125 | SMOOTHIES PEACH | سموذي خوخ | 3.50 | NR-09 |
| `PRD-00126` | 126 | STRW_MANGO PASSION | فراولة + مانجو + باشن | 3.50 | NR-09 NR-10 AR-07 |
| `PRD-00127` | 127 | LEMON_KIWI | ليمون + كيوي | 3.50 | NR-09 NR-10 AR-07 |
| `PRD-00128` | 128 | SMOOTHIE PASSION | سموذي باشن | 3.50 |  |
| `PRD-00129` | 129 | SMOOTHES PASSION+PINAPPLE+BA | سموذي باشن + أناناس + مانجو | 3.50 | NR-09 NR-10 MQ-04 AR-02 |
| `PRD-00130` | 130 | SMOOTHES MANGO+PINAPPLE+ORANGE | سموذي مانجو + أناناس + برتقال | 3.50 | NR-09 NR-10 |
| `PRD-00131` | 131 | SMOOTHES BERRY MINT | سموذي بيري نعناع | 3.50 | NR-09 |
| `PRD-00132` | 132 | SMOOTHES MANGO + PINAPPLE | سموذي مانجو + أناناس | 3.50 | NR-09 NR-10 |
| `PRD-00133` | 133 | SMOOTHES STRAWBERRY+PASSION | سموذي فراولة + باشن | 3.50 | NR-09 |
| `PRD-00134` | 134 | SMOOTHES MANGO+PASSION | سموذي مانجو + باشن | 3.50 | NR-09 |
| `PRD-00135` | 135 | ORANGE JUICE | عصير برتقال | 3.50 | MQ-15 |

#### `CAT-006` FRAPPE — فرابيه · 14 صنفًا

| Product ID | # في الملف | Name EN (كما في الملف) | Name AR (كما في الملف) | السعر د.أ (شامل الضريبة) | ملاحظات |
|---|---|---|---|---|---|
| `PRD-00136` | 136 | FRAPE VANILIA | فرابيه فانيلا | 3.50 | NR-11 NR-01 |
| `PRD-00137` | 137 | FRAPE CHOCOLATE | فرابيه شوكليت | 3.50 | NR-11 |
| `PRD-00138` | 138 | FRAPE CARAMEL | فرابيه كراميل | 3.50 | NR-11 |
| `PRD-00139` | 139 | FRAPE SHELTER | فرابيه شيلتر | 3.50 | AR-03 NR-11 |
| `PRD-00140` | 140 | FRAPE PISTACHIO | فرابيه فستق | 3.50 | NR-11 |
| `PRD-00141` | 141 | FRAPE ESPRESSO | فرابيه إسبريسو | 3.50 | NR-11 |
| `PRD-00142` | 142 | FRAPE TWIX | فرابيه تويكس | 3.50 | NR-11 |
| `PRD-00143` | 143 | FRAPE CROCCANTE | فرابيه كروكانتي | 3.50 | NR-11 |
| `PRD-00144` | 144 | FRAPE MATCHA | فرابيه ماتشا | 4.00 | NR-11 |
| `PRD-00145` | 145 | FRAPE WHITE MOCHA | فرابيه وايت موكا | 3.50 | NR-11 |
| `PRD-00146` | 146 | FRAPE MOCHA | فرابيه موكا | 3.50 | NR-11 |
| `PRD-00147` | 147 | FRAPE SNICKERS | فرابيه سنيكرز | 3.50 | NR-11 |
| `PRD-00148` | 148 | FRAPE LOTUS | فرابيه لوتس | 3.50 | NR-11 |
| `PRD-00149` | 149 | FRAPE SALTED CARAMEL | فرابيه سولتد كراميل | 3.50 | NR-11 |

#### `CAT-007` TEA — شاي · 8 صنفًا

| Product ID | # في الملف | Name EN (كما في الملف) | Name AR (كما في الملف) | السعر د.أ (شامل الضريبة) | ملاحظات |
|---|---|---|---|---|---|
| `PRD-00150` | 152 | TEA ROYAL BREAKFAST | شاي رويال بريكفاست | 2.00 |  |
| `PRD-00151` | 153 | TEA EARLY GRAY | شاي إيرل غراي | 2.00 | NR-12 |
| `PRD-00152` | 154 | TEA MASALA | شاي ماسالا | 2.00 |  |
| `PRD-00153` | 155 | TEA GREEN TEA CURLS | شاي أخضر كيرلز | 2.00 |  |
| `PRD-00154` | 156 | TEA HAPPY FOREST | شاي هابي فورست | 2.00 |  |
| `PRD-00155` | 157 | TEA ROSA | شاي روزا | 2.00 |  |
| `PRD-00156` | 158 | TEA MOROCCAN NIGHTS | شاي ليالي مغربية | 2.00 |  |
| `PRD-00157` | 159 | TEA GINGER ZEST | شاي زنجبيل زست | 2.00 |  |

#### `CAT-008` SPECIALITY COFFEE — قهوة مختصة · 4 صنفًا

| Product ID | # في الملف | Name EN (كما في الملف) | Name AR (كما في الملف) | السعر د.أ (شامل الضريبة) | ملاحظات |
|---|---|---|---|---|---|
| `PRD-00158` | 160 | V60 COLOMBIA HUILA | V60 كولومبيا هويلا | 3.50 |  |
| `PRD-00159` | 161 | V60 HONDURAS LAS | V60 هندوراس لاس | 3.50 | MQ-12 AR-08 |
| `PRD-00160` | 162 | V60 INDONESIA | V60 إندونيسيا | 3.50 |  |
| `PRD-00161` | 163 | V60 PERU CAJAMARCA | V60 بيرو كاخاماركا | 3.50 |  |

#### `CAT-009` SPRING — سبرينغ · 5 صنفًا

| Product ID | # في الملف | Name EN (كما في الملف) | Name AR (كما في الملف) | السعر د.أ (شامل الضريبة) | ملاحظات |
|---|---|---|---|---|---|
| `PRD-00162` | 260 | ROZY BASIL | روزي بازل | 3.50 | MQ-06 MQ-12 |
| `PRD-00163` | 261 | RED LEMONADE | ريد ليموناضة | 3.50 | MQ-06 |
| `PRD-00164` | 262 | MANGO SUNRISE SHAKE | مانجو سن رايز شيك | 3.75 | MQ-06 |
| `PRD-00165` | 263 | PEACH LEMONADE | بيتش ليموناضة | 3.50 | MQ-06 |
| `PRD-00166` | 264 | GREEN MIX | غرين مكس | 3.50 | MQ-06 |

#### `CAT-010` CAKE · 21 صنفًا

| Product ID | # في الملف | Name EN (كما في الملف) | Name AR (كما في الملف) | السعر د.أ (شامل الضريبة) | ملاحظات |
|---|---|---|---|---|---|
| `PRD-00167` | 1 | ENGLISH CAKE FRUIT | `MISSING` | 1.00 |  |
| `PRD-00168` | 2 | ENGLISH CAKE CHOCOLATE | `MISSING` | 1.00 |  |
| `PRD-00169` | 3 | COFFEE BEANS CAKE | `MISSING` | 2.00 |  |
| `PRD-00170` | 4 | LAZY CAKE | `MISSING` | 2.00 |  |
| `PRD-00171` | 5 | TIRAMISU | `MISSING` | 3.00 |  |
| `PRD-00172` | 6 | HAZELNUT CAKE | `MISSING` | 3.00 |  |
| `PRD-00173` | 7 | DUBAI CHOCO MOUSSE | `MISSING` | 3.00 |  |
| `PRD-00174` | 8 | CHEESE CAKE STRAWBERRY | `MISSING` | 3.00 | NR-18 |
| `PRD-00175` | 9 | CRUNCHOCO CAKE | `MISSING` | 3.00 |  |
| `PRD-00176` | 10 | CHEESE CAKE BLUEBERRY | `MISSING` | 3.00 | NR-18 |
| `PRD-00177` | 11 | CARROT CAKE | `MISSING` | 3.50 |  |
| `PRD-00178` | 12 | SAN SEBASTIAN | `MISSING` | 3.50 |  |
| `PRD-00179` | 13 | GERMAN CHOCLATE | `MISSING` | 3.50 | NR-16 |
| `PRD-00180` | 14 | CROCANTE CAKE | `MISSING` | 3.50 | NR-17 |
| `PRD-00181` | 15 | RED VALVET CAKE | `MISSING` | 3.50 | NR-15 |
| `PRD-00182` | 16 | HONEY CAKE | `MISSING` | 3.50 |  |
| `PRD-00183` | 17 | MANGO CHEESECAKE | `MISSING` | 3.50 |  |
| `PRD-00184` | 18 | BROWNIES | `MISSING` | 3.50 |  |
| `PRD-00185` | 19 | PASSION FRUIT CHEESECAKE | `MISSING` | 3.50 |  |
| `PRD-00186` | 20 | LONDON CAKE | `MISSING` | 3.50 |  |
| `PRD-00187` | 21 | SAN SEBASTIAN BERRIES | `MISSING` | 4.00 |  |

#### `CAT-011` COOKIES · 5 صنفًا

| Product ID | # في الملف | Name EN (كما في الملف) | Name AR (كما في الملف) | السعر د.أ (شامل الضريبة) | ملاحظات |
|---|---|---|---|---|---|
| `PRD-00188` | 22 | COOCKIES CHOCOLATE | `MISSING` | 1.00 | NR-14 |
| `PRD-00189` | 23 | COOCKIES MILK CHOCOLATE | `MISSING` | 1.00 | NR-14 |
| `PRD-00190` | 24 | NUTELLA COOKIES | `MISSING` | 1.50 | NR-19 |
| `PRD-00191` | 25 | COOCKIES KINDER | `MISSING` | 1.50 | NR-14 |
| `PRD-00192` | 26 | COOKIES PISTACHIO | `MISSING` | 1.50 |  |


## 5. Price range per category

> كل الأسعار من مضاعفات 0.25 د.أ.

| Category ID | Category | عدد | أقل سعر | أعلى سعر | الوسيط | توزيع الأسعار (سعر × عدد) |
|---|---|---|---|---|---|---|
| `CAT-001` | HOT DRINKS | 39 | 1.50 | 3.50 | 2.75 | 1.50×2 · 2.00×4 · 2.50×11 · 2.75×8 · 3.00×8 · 3.25×2 · 3.50×4 |
| `CAT-002` | COLD DRINKS | 36 | 2.75 | 4.25 | 3.50 | 2.75×2 · 3.00×5 · 3.25×2 · 3.50×22 · 3.75×4 · 4.25×1 |
| `CAT-003` | FIZZY DRINKS | 15 | 0.50 | 4.00 | 3.00 | 0.50×1 · 1.50×1 · 2.00×3 · 2.50×1 · 3.00×4 · 3.25×3 · 3.50×1 · 4.00×1 |
| `CAT-004` | MILKSHAKE | 21 | 3.50 | 4.00 | 3.50 | 3.50×13 · 3.75×6 · 4.00×2 |
| `CAT-005` | SMOOTHIES | 24 | 3.50 | 3.50 | 3.50 | 3.50×24 |
| `CAT-006` | FRAPPE | 14 | 3.50 | 4.00 | 3.50 | 3.50×13 · 4.00×1 |
| `CAT-007` | TEA | 8 | 2.00 | 2.00 | 2.00 | 2.00×8 |
| `CAT-008` | SPECIALITY COFFEE | 4 | 3.50 | 3.50 | 3.50 | 3.50×4 |
| `CAT-009` | SPRING | 5 | 3.50 | 3.75 | 3.50 | 3.50×4 · 3.75×1 |
| `CAT-010` | CAKE | 21 | 1.00 | 4.00 | 3.50 | 1.00×2 · 2.00×2 · 3.00×6 · 3.50×10 · 4.00×1 |
| `CAT-011` | COOKIES | 5 | 1.00 | 1.50 | 1.50 | 1.00×2 · 1.50×3 |
| | **كل المنيو** | **192** | **0.50** | **4.25** | **3.50** | |

**فحص القيم المشبوهة:**
- **لا توجد قيمة مشبوهة:** لا صفر، لا سالب، لا نص بدل رقم، ولا كسور غير معتادة.
- قيم تقع على أطراف النطاق، للعلم فقط وليست أخطاء:

| الصنف | السعر | الملاحظة |
|---|---|---|
| `PRD-00080` MINERAL WATER | 0.50 | أقل سعر في المنيو |
| `PRD-00050` ICED MATCHA | 4.25 | أعلى سعر في المنيو |
| `PRD-00167` و`PRD-00168` ENGLISH CAKE | 1.00 | أقل بكثير من وسيط الكيك (3.50) — هل هو للقطعة الصغيرة؟ (MQ-16) |
| `PRD-00001` و`PRD-00002` TURKISH COFFEE S / D | 1.50 / 2.50 | فرق 1.00 د.أ يناسب Single/Double أكثر من درجة السكر (MQ-02) |

- **اتساق مفيد:** الأصناف "بدون سكر" بنفس سعر الأصناف العادية (2.75 للساخن و3.50 للبارد). هذا يدعم عرضها كخيار واحد (MQ-08).

## 6. Missing values

**داخل الملف:**

| الحقل | الحالة |
|---|---|
| السعر | ✅ موجود لكل الأصناف (192/192) |
| الفئة | ✅ موجودة لكل الأصناف |
| الاسم الإنجليزي | ✅ موجود لكل الأصناف — 7 أسماء غير واضحة (القسم 8.2) |
| الاسم العربي للصنف | ✅ 166 (الورقة الأولى) · ❌ **26 ناقصة** (الورقة الثانية) ← مقترحات في القسم 8.4 |
| الاسم العربي للفئة | ✅ 9 · ❌ **2 ناقصة** (CAKE، COOKIES) |
| صف عناوين الأعمدة | ❌ ناقص في الورقة الثانية — لا أثر بعد القراءة |
| أرقام عمود # | 98 رقمًا غير موجود (150–151، 164–259) — هل هي أصناف محذوفة أو متوقفة في الكاشير؟ (MQ-05، MQ-09) |

**حقول غير موجودة في الملف أصلًا** (حالتها `MISSING — OWNER INPUT REQUIRED` لكل الأصناف، ولم نخترع منها شيئًا):

| الحقل | القيمة الحالية في النسخة المنظمة |
|---|---|
| Description AR/EN | `DESCRIPTION MISSING` |
| Ingredients · Allergens · Calories · Nutrition | `MISSING` |
| Sizes · Add-ons / Modifiers | `MISSING` (MQ-07) |
| Product Images | `MISSING` (D-083) |
| التوفر في DRIVE / HOUSE | `UNKNOWN` لكل صنف في كل فرع (MQ-01، D-079) |
| فروق الأسعار بين الفرعين | `MISSING` — لا Branch override (MQ-01) |
| POS Item ID | `MISSING` (MQ-09) |
| Featured · New · Seasonal + تواريخ الموسم | `MISSING` (MQ-06 لفئة SPRING) |
| SEO title / description · Slugs | لم تُنشأ — بعد اعتماد أسماء العرض |

## 7. Duplicate items

- **لا تكرار حرفي** في الأسماء الإنجليزية ولا العربية، ولا فئة مكررة.
- **حالات تشابه** تحتاج قرارك:

| # | الأولوية | الأصناف | الملاحظة | التقييم |
|---|---|---|---|---|
| DUP-01 | High | `PRD-00115` SMOOTHIE PINAPPLE LMN STRWBERY<br>`PRD-00120` SMOOTHIES PINA-STRAW-LEMON | نفس الفواكه الثلاث (أناناس + ليمون + فراولة) بترتيب مختلف، ونفس السعر 3.50، والعربي متطابق تقريبًا | على الأغلب صنف مكرر: نُبقي واحدًا ويُلغى الآخر قبل التجميد |
| DUP-02 | Medium | `PRD-00003` AMERICAN COFFEE<br>`PRD-00004` AMERICANO<br>`PRD-00040` ICED AMERICAN<br>`PRD-00045` ICED AMERICANO | AMERICAN COFFEE وAMERICANO بنفس السعر (2.00)، وICED AMERICAN وICED AMERICANO بنفس السعر (2.75) | قد يكونان مشروبين مختلفين (قهوة مفلترة مقابل إسبريسو + ماء) — يحتاج تأكيدك |
| DUP-03 | Medium | `PRD-00011` ESPRESSO MACCHIATO<br>`PRD-00012` MACCHIATO | ESPRESSO MACCHIATO وMACCHIATO بنفس السعر (2.50) | هل هما نفس المشروب؟ |
| DUP-04 | Low | `PRD-00042` ICED SHAKEN CARAMEL<br>`PRD-00043` ICED CARAMEL LATTE<br>`PRD-00047` ICED SHAKEN WHITE MOCHA<br>`PRD-00062` ICED WHITE MOCHA<br>`PRD-00052` ICED SHAKEN VANILLA<br>`PRD-00048` ICED VANILLA LATTE<br>`PRD-00057` ICED SHAKEN HAZELNUT<br>`PRD-00053` ICED HAZELNUT LATTE<br>`PRD-00067` ICED SHAKEN SALTED<br>`PRD-00066` ICED SALTED CARAMEL LATTE | خمسة أزواج SHAKEN / LATTE بنفس النكهة ونفس السعر 3.50 | غالبًا طريقتا تحضير مختلفتان — تأكيد فقط |

**ليست تكرارًا، لكنها مرشحة للتجميع كخيارات (MQ-08):**
- 6 أصناف "بدون سكر": #27، #28، #29، #44، #49، #54.
- عائلة RED BULL: #78، #83، #88، #89.

## 8. Naming inconsistencies

> **لم يُطبّق أي تعديل.** كل ما يلي مقترحات تنتظر موافقتك (MQ-10، MQ-11).

### 8.1 الأسماء الإنجليزية — Current / Suggested / Reason

| Rule | النوع | الأولوية | Current Name (كما في الملف) | Suggested Name | Reason |
|---|---|---|---|---|---|
| NR-01 | SPELL | High | `PRD-00021` VANILIA LATTE | VANILLA LATTE | الملف نفسه يكتب VANILLA في #48 و#49 و#52 |
|  |  |  | `PRD-00028` VANILIA FREE LATTE | VANILLA LATTE (SUGAR-FREE) | ″ |
|  |  |  | `PRD-00091` MILKSHAKE VANILIA | MILKSHAKE VANILLA | ″ |
|  |  |  | `PRD-00136` FRAPE VANILIA | FRAPPE VANILLA | ″ |
| NR-02 | CONSIST | Medium | `PRD-00027` CARAMEL FREE LATTE | CARAMEL LATTE (SUGAR-FREE) | كلمة FREE وحدها غامضة، والاسم العربي في الملف نفسه "(بدون سكر)" |
|  |  |  | `PRD-00028` VANILIA FREE LATTE | VANILLA LATTE (SUGAR-FREE) | ″ |
|  |  |  | `PRD-00029` HAZELNUT FREE LATTE | HAZELNUT LATTE (SUGAR-FREE) | ″ |
|  |  |  | `PRD-00044` ICED CARAMEL FREE LATTE | ICED CARAMEL LATTE (SUGAR-FREE) | ″ |
|  |  |  | `PRD-00049` ICED VANILLA FREE LATTE | ICED VANILLA LATTE (SUGAR-FREE) | ″ |
|  |  |  | `PRD-00054` ICED HAZELNUT FREE LATTE | ICED HAZELNUT LATTE (SUGAR-FREE) | ″ |
|  |  |  | `PRD-00083` REDBULL FREE SUGAR | RED BULL (SUGAR-FREE) | ″ |
| NR-03 | CONSIST | Low | `PRD-00083` REDBULL FREE SUGAR | RED BULL (SUGAR-FREE) | الملف يكتب RED BULL منفصلة في #78 و#86 و#87 |
|  |  |  | `PRD-00088` REDBULL WATERMELON | RED BULL WATERMELON | ″ |
|  |  |  | `PRD-00089` REDBULL COCONUT | RED BULL COCONUT | ″ |
| NR-04 | SPELL | High | `PRD-00023` CINAMON NUT LATTE | CINNAMON NUT LATTE | خطأ إملائي |
|  |  |  | `PRD-00068` ICED CINAMON NUT LATTE | ICED CINNAMON NUT LATTE | ″ |
| NR-05 | SPELL | High | `PRD-00064` ICE IRSH LATTE | ICED IRISH LATTE | خطآن: ICE بدل ICED، وIRSH بدل IRISH (الملف يكتب IRISH في #25 و#33) |
| NR-06 | SPELL | High | `PRD-00065` ICED TOFFE NUT LATTE | ICED TOFFEE NUT LATTE | خطأ إملائي |
| NR-07 | SPELL | High | `PRD-00069` ICE TEA BERRY | ICED TEA BERRY | الملف يكتب ICED TEA في #72 و#73 و#74 |
|  |  |  | `PRD-00070` ICE TEA POMGRANATE | ICED TEA POMEGRANATE | ″ |
|  |  |  | `PRD-00071` ICE TEA PASSION + PEACH | ICED TEA PASSION + PEACH | ″ |
| NR-08 | CONSIST | Low | `PRD-00076` MOJITO 7 UP | MOJITO 7UP | الملف يكتب 7UP في #79 و#84 |
| NR-09 | CONSIST | Medium | `PRD-00112` SMOOTHIES ORANGE | SMOOTHIE ORANGE | البادئة مكتوبة بثلاث صيغ (SMOOTHIE / SMOOTHIES / SMOOTHES)، وصنفان بلا بادئة |
|  |  |  | `PRD-00114` SMOOTHIES LEMON | SMOOTHIE LEMON | ″ |
|  |  |  | `PRD-00120` SMOOTHIES PINA-STRAW-LEMON | SMOOTHIE PINEAPPLE + STRAWBERRY + LEMON | ″ |
|  |  |  | `PRD-00121` SMOOTHIES BLUEBERRY | SMOOTHIE BLUEBERRY | ″ |
|  |  |  | `PRD-00122` SMOOTHIES KIWI | SMOOTHIE KIWI | ″ |
|  |  |  | `PRD-00123` SMOOTHIES PINAPPLE | SMOOTHIE PINEAPPLE | ″ |
|  |  |  | `PRD-00124` SMOOTHIES MIXBERRY | SMOOTHIE MIXBERRY | ″ |
|  |  |  | `PRD-00125` SMOOTHIES PEACH | SMOOTHIE PEACH | ″ |
|  |  |  | `PRD-00126` STRW_MANGO PASSION | SMOOTHIE STRAWBERRY + MANGO + PASSION | ″ |
|  |  |  | `PRD-00127` LEMON_KIWI | SMOOTHIE LEMON + KIWI | ″ |
|  |  |  | `PRD-00129` SMOOTHES PASSION+PINAPPLE+BA | — (ينتظر MQ) | ″ |
|  |  |  | `PRD-00130` SMOOTHES MANGO+PINAPPLE+ORANGE | SMOOTHIE MANGO + PINEAPPLE + ORANGE | ″ |
|  |  |  | `PRD-00131` SMOOTHES BERRY MINT | SMOOTHIE BERRY MINT | ″ |
|  |  |  | `PRD-00132` SMOOTHES MANGO + PINAPPLE | SMOOTHIE MANGO + PINEAPPLE | ″ |
|  |  |  | `PRD-00133` SMOOTHES STRAWBERRY+PASSION | SMOOTHIE STRAWBERRY + PASSION | ″ |
|  |  |  | `PRD-00134` SMOOTHES MANGO+PASSION | SMOOTHIE MANGO + PASSION | ″ |
| NR-10 | ABBR | High | `PRD-00115` SMOOTHIE PINAPPLE LMN STRWBERY | SMOOTHIE PINEAPPLE + LEMON + STRAWBERRY | PINAPPLE / STRWBERY / STRWBERRY / STRW / STRAW / PINA / LMN، والرموز _ و - بدل + |
|  |  |  | `PRD-00117` SMOOTHIE ORANGE_PASSION | SMOOTHIE ORANGE + PASSION | ″ |
|  |  |  | `PRD-00118` SMOOTHIE MANGO STRWBERRY | SMOOTHIE MANGO + STRAWBERRY | ″ |
|  |  |  | `PRD-00120` SMOOTHIES PINA-STRAW-LEMON | SMOOTHIE PINEAPPLE + STRAWBERRY + LEMON | ″ |
|  |  |  | `PRD-00123` SMOOTHIES PINAPPLE | SMOOTHIE PINEAPPLE | ″ |
|  |  |  | `PRD-00126` STRW_MANGO PASSION | SMOOTHIE STRAWBERRY + MANGO + PASSION | ″ |
|  |  |  | `PRD-00127` LEMON_KIWI | SMOOTHIE LEMON + KIWI | ″ |
|  |  |  | `PRD-00129` SMOOTHES PASSION+PINAPPLE+BA | — (ينتظر MQ) | ″ |
|  |  |  | `PRD-00130` SMOOTHES MANGO+PINAPPLE+ORANGE | SMOOTHIE MANGO + PINEAPPLE + ORANGE | ″ |
|  |  |  | `PRD-00132` SMOOTHES MANGO + PINAPPLE | SMOOTHIE MANGO + PINEAPPLE | ″ |
| NR-11 | SPELL | Medium | `PRD-00136` FRAPE VANILIA | FRAPPE VANILLA | الفئة نفسها مكتوبة FRAPPE |
|  |  |  | `PRD-00137` FRAPE CHOCOLATE | FRAPPE CHOCOLATE | ″ |
|  |  |  | `PRD-00138` FRAPE CARAMEL | FRAPPE CARAMEL | ″ |
|  |  |  | `PRD-00139` FRAPE SHELTER | FRAPPE SHELTER | ″ |
|  |  |  | `PRD-00140` FRAPE PISTACHIO | FRAPPE PISTACHIO | ″ |
|  |  |  | `PRD-00141` FRAPE ESPRESSO | FRAPPE ESPRESSO | ″ |
|  |  |  | `PRD-00142` FRAPE TWIX | FRAPPE TWIX | ″ |
|  |  |  | `PRD-00143` FRAPE CROCCANTE | FRAPPE CROCCANTE | ″ |
|  |  |  | `PRD-00144` FRAPE MATCHA | FRAPPE MATCHA | ″ |
|  |  |  | `PRD-00145` FRAPE WHITE MOCHA | FRAPPE WHITE MOCHA | ″ |
|  |  |  | `PRD-00146` FRAPE MOCHA | FRAPPE MOCHA | ″ |
|  |  |  | `PRD-00147` FRAPE SNICKERS | FRAPPE SNICKERS | ″ |
|  |  |  | `PRD-00148` FRAPE LOTUS | FRAPPE LOTUS | ″ |
|  |  |  | `PRD-00149` FRAPE SALTED CARAMEL | FRAPPE SALTED CARAMEL | ″ |
| NR-12 | SPELL | High | `PRD-00151` TEA EARLY GRAY | TEA EARL GREY | الاسم الصحيح للشاي Earl Grey، والعربي في الملف صحيح "إيرل غراي" |
| NR-13 | ABBR | Medium | `PRD-00009` ESPRESSO D | ESPRESSO DOUBLE | اختصار، والعربي في الملف "إسبريسو دوبل" |
| NR-14 | SPELL | High | `PRD-00188` COOCKIES CHOCOLATE | COOKIES CHOCOLATE | الفئة نفسها مكتوبة COOKIES |
|  |  |  | `PRD-00189` COOCKIES MILK CHOCOLATE | COOKIES MILK CHOCOLATE | ″ |
|  |  |  | `PRD-00191` COOCKIES KINDER | COOKIES KINDER | ″ |
| NR-15 | SPELL | High | `PRD-00181` RED VALVET CAKE | RED VELVET CAKE | خطأ إملائي |
| NR-16 | SPELL | High | `PRD-00179` GERMAN CHOCLATE | GERMAN CHOCOLATE | خطأ إملائي |
| NR-17 | CONSIST | Medium | `PRD-00060` ICED CROCCONATE | — (ينتظر MQ) | الملف يكتب CROCCANTE في #102 و#143 |
|  |  |  | `PRD-00180` CROCANTE CAKE | CROCCANTE CAKE | ″ |
| NR-18 | CONSIST | Low | `PRD-00101` MILKSHAKE CHEESE CAKE | MILKSHAKE CHEESECAKE | الملف يكتب CHEESECAKE في كيك #17 و#19 |
|  |  |  | `PRD-00174` CHEESE CAKE STRAWBERRY | STRAWBERRY CHEESECAKE | ″ |
|  |  |  | `PRD-00176` CHEESE CAKE BLUEBERRY | BLUEBERRY CHEESECAKE | ″ |
| NR-19 | CONSIST | Low | `PRD-00190` NUTELLA COOKIES | COOKIES NUTELLA | 4 من 5 أصناف تبدأ بـ COOKIES |
| NR-20 | CONSIST | Low | `PRD-00032` AFFOGATO COFFEE | AFFOGATO | العربي في الملف "أفوغاتو" فقط |
| NR-21 | CONSIST | Medium | `CAT-008` SPECIALITY COFFEE | SPECIALTY COFFEE | الصيغتان صحيحتان؛ "Specialty" هي الأكثر استخدامًا في البحث وفي مصطلحات الصناعة — قرارك (MQ-14) |
| NR-22 | CONSIST | Low | أسماء الفئات: MILKSHAKE · FRAPPE · CAKE (مفرد) مقابل SMOOTHIES · COOKIES · DRINKS (جمع) | توحيد الصيغة | يُحسم في مرحلة Menu IA |
| NR-23 | DISPLAY | Low | كل الأسماء بأحرف كبيرة (ALL CAPS) | تحديد طريقة العرض في الواجهة فقط — بدون تعديل البيانات | قرارك (MQ-14) |

### 8.2 أسماء غير واضحة — لا نقترح قبل جوابك (MQ-12)

| Product ID | Current Name | Name AR (الملف) | السؤال |
|---|---|---|---|
| `PRD-00035` | MESTEKH | مستكة | نقل حرفي لـ"مستكة" — هل هذه الكتابة المعتمدة؟ |
| `PRD-00060` | ICED CROCCONATE | آيس كروكونات | العربي "كروكونات"؛ في أصناف أخرى CROCCANTE — هل هو نفس الاسم؟ |
| `PRD-00067` | ICED SHAKEN SALTED | آيس شيكن سولتد | ناقص: SALTED ماذا؟ (Salted Caramel؟) — العربي "سولتد" فقط |
| `PRD-00105` | MILKSHAKE BAJ | ميلك شيك بَج | العربي "بَج" — ما هذا الصنف؟ |
| `PRD-00107` | MILKSHAKE ASH BERRY | ميلك شيك آش بيري | العربي "آش بيري" — هل المقصود Açaí Berry؟ |
| `PRD-00159` | V60 HONDURAS LAS | V60 هندوراس لاس | يبدو مقطوعًا — ما الاسم الكامل للمحصول؟ |
| `PRD-00162` | ROZY BASIL | روزي بازل | هل ROZY مقصودة أم ROSY؟ |

### 8.3 ملاحظات على الأسماء العربية الموجودة في الملف (MQ-11)

> الأسماء العربية في الورقة الأولى جاءت من ملفك، فلم نترجم منها شيئًا. هذه ملاحظات فقط:

| # | الأصناف (رقم الملف) | الملاحظة | المقترح |
|---|---|---|---|
| AR-01 | #1، #2 | "سادة" و"وسط/حلوة" تصف درجة السكر، بينما فرق السعر 1.00 د.أ، و"D" في #9 تعني "دوبل". قد تكون S/D = Single/Double | لا اقتراح قبل جوابك (MQ-02) |
| AR-02 | #129 | العربي "باشن + أناناس + مانجو" بينما الإنجليزي ينتهي بـ "BA"، وقد تعني Banana | لا اقتراح قبل جوابك (MQ-04) |
| AR-03 | #82، #84، #86، #93، #139 | "شيلتر" بالياء، بينما الاسم العربي المعتمد للعلامة "شلتر" (D-007) | "شلتر" — مثال: "شلتر إنرجي كود رد"، "ميلك شيك شلتر" |
| AR-04 | #42، #47، #52، #57، #67 | "آيس شيكن" تُقرأ "Chicken": الموقع القديم ترجمها آليًا "Iced Chicken" (CR-067) | خيارات: (أ) إبقاؤها · (ب) كتابة Shaken بالإنجليزي داخل الاسم العربي · (ج) وصف عربي مثل "مرجوج" |
| AR-05 | #27–#29، #44، #49، #54 | "بدون سكر" بين قوسين في الساخن وبدون قوسين في البارد | توحيد الصيغة: "(بدون سكر)" |
| AR-06 | #80 | "ماء معدنية": "ماء" مذكّر | "مياه معدنية" |
| AR-07 | #126، #127 | بلا كلمة "سموذي"، مثل الإنجليزي | إضافة "سموذي" إذا اعتمدت NR-09 |
| AR-08 | #161 | "هندوراس لاس" يبدو مقطوعًا | الاسم الكامل للمحصول (MQ-12) |

### 8.4 أصناف تحتاج اسمًا عربيًا — مقترحات غير معتمدة (MQ-13، D-082)

| Product ID | English Name (كما في الملف) | Suggested Arabic Name | Translation / Transliteration | Reason |
|---|---|---|---|---|
| `CAT-010` (فئة) | CAKE | كيك | Transliteration | الاسم المتداول |
| `CAT-011` (فئة) | COOKIES | كوكيز | Transliteration | الاسم المتداول |
| `PRD-00167` | ENGLISH CAKE FRUIT | إنجلش كيك فواكه | Mixed | اسم الصنف منقول حرفيًا والنكهة مترجمة، بنفس نمط الملف (مثل "ميلك شيك فراولة") |
| `PRD-00168` | ENGLISH CAKE CHOCOLATE | إنجلش كيك شوكليت | Transliteration | الملف يكتب "شوكليت" (#36، #96، #137) |
| `PRD-00169` | COFFEE BEANS CAKE | كوفي بينز كيك | Transliteration | بديل مترجم: "كيكة حبوب القهوة" |
| `PRD-00170` | LAZY CAKE | ليزي كيك | Transliteration | الاسم المتداول محليًا |
| `PRD-00171` | TIRAMISU | تيراميسو | Transliteration | اسم عالمي |
| `PRD-00172` | HAZELNUT CAKE | هازلنت كيك | Transliteration | الملف يكتب "هازلنت" (#22، #53) |
| `PRD-00173` | DUBAI CHOCO MOUSSE | دبي شوكو موس | Transliteration | يحافظ على اسم الصنف |
| `PRD-00174` | CHEESE CAKE STRAWBERRY | تشيز كيك فراولة | Mixed | الملف يكتب "تشيز كيك" (#101) و"فراولة" |
| `PRD-00175` | CRUNCHOCO CAKE | كرانشوكو كيك | Transliteration | اسم خاص — النطق يحتاج تأكيدك |
| `PRD-00176` | CHEESE CAKE BLUEBERRY | تشيز كيك بلوبيري | Transliteration | الملف يكتب "بلوبيري" (#109، #121) |
| `PRD-00177` | CARROT CAKE | كيكة الجزر | Translation | بديل منقول حرفيًا: "كاروت كيك" |
| `PRD-00178` | SAN SEBASTIAN | سان سيباستيان | Transliteration | اسم عالمي |
| `PRD-00179` | GERMAN CHOCLATE | جيرمان شوكليت | Transliteration | بعد تصحيح الإنجليزي (NR-16) |
| `PRD-00180` | CROCANTE CAKE | كروكانتي كيك | Transliteration | الملف يكتب "كروكانتي" (#102، #143) |
| `PRD-00181` | RED VALVET CAKE | ريد فيلفيت كيك | Transliteration | بعد تصحيح الإنجليزي (NR-15) |
| `PRD-00182` | HONEY CAKE | هاني كيك | Transliteration | بديل مترجم: "كيكة العسل" |
| `PRD-00183` | MANGO CHEESECAKE | تشيز كيك مانجو | Mixed | نفس نمط "تشيز كيك فراولة" |
| `PRD-00184` | BROWNIES | براونيز | Transliteration | اسم عالمي |
| `PRD-00185` | PASSION FRUIT CHEESECAKE | تشيز كيك باشن فروت | Transliteration | الملف يكتب "باشن" (#117، #128) |
| `PRD-00186` | LONDON CAKE | لندن كيك | Transliteration | يحافظ على اسم الصنف |
| `PRD-00187` | SAN SEBASTIAN BERRIES | سان سيباستيان بيريز | Transliteration | بديل: "سان سيباستيان بالتوت" |
| `PRD-00188` | COOCKIES CHOCOLATE | كوكيز شوكليت | Transliteration | الملف يكتب "شوكليت" |
| `PRD-00189` | COOCKIES MILK CHOCOLATE | كوكيز ميلك شوكليت | Transliteration | بديل: "كوكيز شوكليت بالحليب" |
| `PRD-00190` | NUTELLA COOKIES | كوكيز نوتيلا | Transliteration | الملف يكتب "نوتيلا" (#39، #51) |
| `PRD-00191` | COOCKIES KINDER | كوكيز كيندر | Transliteration | اسم علامة تجارية |
| `PRD-00192` | COOKIES PISTACHIO | كوكيز فستق | Mixed | الملف يكتب "فستق" (#38، #55، #95) |

## 9. Questions that need Owner verification

> **P0** = يلزم قبل تجميد البيانات والمعرّفات · **P1** = الأسماء · **P2** = مرحلة Menu IA.

| # | الأولوية | يخص | السؤال | خيارات الجواب | لماذا |
|---|---|---|---|---|---|
| MQ-01 | P0 | الفروع | هل هذا الملف لفرع واحد أم للفرعين؟ وهل توجد أصناف أو أسعار مختلفة في DRIVE أو HOUSE؟ | أ) نفس الأصناف والأسعار في الفرعين · ب) توجد استثناءات (أرسلها فقط) | حتى الجواب: التوفر = UNKNOWN لكل الأصناف في الفرعين (D-079) |
| MQ-02 | P0 | #1، #2 | TURKISH COFFEE S / D: هل S/D = Single/Double (حجم)، أم سادة/حلوة (سكر) كما في العربي؟ فرق السعر 1.00 د.أ | أ) Single / Double · ب) سادة / حلوة · ج) غير ذلك | يحدد هل هما Variants لصنف واحد أم صنفان، ويحدد الاسم العربي |
| MQ-03 | P0 | DUP-01 → DUP-04 | الأصناف المتشابهة: أيها مكرر وأيها مختلف فعلًا؟ | لكل حالة: مكرر (احذف أحدهما) / مختلف (أبقِ الاثنين) | يحدد عدد الأصناف النهائي والمعرّفات |
| MQ-04 | P0 | #129 | SMOOTHES PASSION+PINAPPLE+BA: الفاكهة الثالثة موز (BA) أم مانجو (كما في العربي)؟ | أ) موز · ب) مانجو | تعارض داخل الملف نفسه |
| MQ-05 | P0 | الملف كاملًا | هل الملف كامل؟ أصناف وُجدت في المنيو القديم أو في الأدلة الخارجية وغير موجودة في الملف: Cold Brew، Lotus Cheesecake، Red Velvet Cheesecake، Code Red with Flavor، آيس كريم، سناكات، معجنات، إسبريسو سينجل. والأرقام 150–151 و164–259 غير موجودة في عمود # | لكل صنف: متوقف / موجود وسقط من الملف (أرسل سعره) | لا نضيف أي صنف من المنيو القديم بدون موافقتك (D-076) |
| MQ-06 | P0 | SPRING (#260–#264) | هل SPRING منيو موسمي؟ هل هو متاح الآن (أكتوبر)؟ وما تواريخ الموسم؟ | أ) موسمي ومتاح الآن · ب) موسمي ومتوقف (Hidden) · ج) دائم | يحدد is_seasonal والتواريخ وهل يظهر عند الإطلاق |
| MQ-07 | P0 | كل المشروبات | الملف لا يحتوي أحجامًا ولا إضافات. هل كل مشروب بحجم واحد؟ وهل توجد إضافات مدفوعة (شوت إضافي، حليب بديل، نكهة)؟ | أ) حجم واحد ولا إضافات · ب) يوجد (أرسل القائمة والأسعار) | البنية جاهزة في الحالتين؛ لا نخترع أي إضافة (D-080) |
| MQ-08 | P0 | 12 صنفًا | الأصناف "بدون سكر" (6) وعائلة RED BULL (4): تبقى أصنافًا منفصلة كما في الملف، أم تُعرض كخيارات (Variants) تحت صنف واحد؟ | أ) Variants — توصيتنا · ب) أصناف منفصلة كما في الملف | قائمة أقصر وأوضح على الموبايل؛ السعر والكود يبقيان لكل خيار |
| MQ-09 | P0 | عمود # | هل الرقم في عمود # هو رقم الصنف في نظام الكاشير؟ وما اسم نظام الكاشير؟ | أ) نعم، رقم الكاشير · ب) ترقيم فقط | الأرقام 1–26 مكررة بين الورقتين، فلا يصلح معرّفًا وحده |
| MQ-10 | P1 | NR-01 → NR-20 | هل تعتمد تصحيحات الأسماء الإنجليزية المقترحة؟ | أ) كلها · ب) بعضها (حدد) · ج) لا — تبقى كما في الملف | لا يُطبّق أي تصحيح بدون موافقتك (D-089) |
| MQ-11 | P1 | الأسماء العربية في الملف | هل الأسماء العربية في الملف هي أسماء العرض النهائية؟ وما قرارك في "شيلتر" (5 أصناف) و"آيس شيكن" و"ماء معدنية"؟ | لكل ملاحظة AR-01 → AR-08: اعتماد / تعديل | الأسماء العربية الموجودة لا نغيرها بدون موافقتك |
| MQ-12 | P1 | 7 أصناف | أسماء غير واضحة: MESTEKH (#35) · ICED CROCCONATE (#60) · ICED SHAKEN SALTED (#67) · MILKSHAKE BAJ (#105) · MILKSHAKE ASH BERRY (#107) · V60 HONDURAS LAS (#161) · ROZY BASIL (#260) | الاسم الصحيح أو "كما هو" | لا نقترح اسمًا قبل أن نعرف الصنف |
| MQ-13 | P1 | 26 صنفًا + فئتان | اعتماد الأسماء العربية المقترحة للكيك والكوكيز | لكل صنف: اعتماد / تعديل | الورقة الثانية بلا أسماء عربية |
| MQ-14 | P1 | الفئات + طريقة العرض | SPECIALITY COFFEE أم SPECIALTY COFFEE؟ وهل تُعرض الأسماء بأحرف كبيرة (كما في الملف) أم بصيغة Title Case؟ | — | "Specialty" هي الصيغة الأكثر استخدامًا في البحث وفي مصطلحات الصناعة؛ الصيغتان صحيحتان |
| MQ-15 | P2 | مرحلة الـIA | مكان بعض الأصناف: MINERAL WATER ضمن FIZZY DRINKS · ORANGE JUICE ضمن SMOOTHIES · مشروبات الطاقة والموهيتو ضمن FIZZY | يُناقش في مرحلة Menu IA | لا نغيّر أي فئة الآن |
| MQ-16 | P2 | الكيك والكوكيز | هل السعر للقطعة أو للشريحة؟ | — | لطريقة العرض فقط |

### 9.1 مقارنة مع المنيو القديم والأدلة الخارجية — لاكتشاف الفروقات فقط (D-076)

> المرجع هو ملفك. لا يُضاف أي صنف من القديم بدون موافقتك. أرقام CR من [`04-content-approval-register.md`](04-content-approval-register.md).

| الصنف في المنيو القديم / الأدلة | المصدر | السعر القديم | المقابل في ملفك | سعر ملفك | النتيجة |
|---|---|---|---|---|---|
| Iced Coffee | CR-067 | 2.75 | `PRD-00040` ICED AMERICAN<br>`PRD-00045` ICED AMERICANO | 2.75 | ✅ السعر مطابق — الاسم القديم عام |
| Cold Cappuccino | CR-067 | 3.50 | `PRD-00041` ICED CAPPUCCINO | 3.50 | ✅ مطابق |
| Iced Chocolate | CR-067 | 3.50 | `PRD-00059` ICED CHOCOLATE | 3.50 | ✅ مطابق |
| Iced White Mocha | CR-067 | 3.50 | `PRD-00062` ICED WHITE MOCHA | 3.50 | ✅ مطابق |
| Iced Lotus | CR-067 | 3.75 | `PRD-00056` ICED LOTUS | 3.75 | ✅ مطابق |
| 'Iced Croissants' (ترجمة آلية) | CR-067 | 3.75 | `PRD-00060` ICED CROCCONATE | 3.75 | ✅ السعر مطابق — ترجمة آلية خاطئة لـ"كروكونات" |
| Iced Nutella | CR-067 | 3.75 | `PRD-00051` ICED NUTELLA | 3.75 | ✅ مطابق |
| 'Red Bull Shelter' | CR-067 | 3.25 | `PRD-00086` SHELTER ENERGY RED BULL | 3.25 | ✅ مطابق |
| Shelter Energy Drink | CR-067 | 2.50 | `PRD-00082` SHELTER ENERGY CODE RED | 2.50 | ✅ مطابق |
| Iced Tea Mix | CR-067 | 3.25 | `PRD-00074` ICED TEA MIX | 3.25 | ✅ مطابق |
| Iced Lemon Tea | CR-067 | 3.00 | `PRD-00073` ICED TEA LEMON | 3.00 | ✅ مطابق |
| Spanish Latte (بارد) | CR-067، CR-073 | 3.50 | `PRD-00058` ICED SPANISH LATTE | 3.50 | ✅ مطابق — تعارض CR-073 محسوم: صنفان منفصلان |
| Spanish Latte (ساخن) | CR-073 | 3.00 | `PRD-00018` SPANISH LATTE | 3.00 | ✅ مطابق |
| Cold Latte | CR-067 | 3.00 | `PRD-00046` ICED LATTE | 3.00 | ✅ مطابق |
| Cold Caramel Latte | CR-067 | 3.50 | `PRD-00043` ICED CARAMEL LATTE | 3.50 | ✅ مطابق |
| Iced Mocha | CR-067 | 3.50 | `PRD-00063` ICED MOCHA | 3.50 | ✅ مطابق |
| Iced Caramel Macchiato | CR-067 | 3.50 | `PRD-00061` ICED CARAMEL MACCHIATO | 3.50 | ✅ مطابق |
| Iced Pistachio | CR-067 | 3.75 | `PRD-00055` ICED PISTACHIO | 3.75 | ✅ مطابق |
| 'Iced Chicken' (ترجمة آلية) | CR-067 | 3.50 | `PRD-00042` ICED SHAKEN CARAMEL<br>`PRD-00047` ICED SHAKEN WHITE MOCHA<br>`PRD-00052` ICED SHAKEN VANILLA<br>`PRD-00057` ICED SHAKEN HAZELNUT<br>`PRD-00067` ICED SHAKEN SALTED | 3.50 | ⚠️ ترجمة آلية لـ"آيس شيكن" — انظر AR-04 |
| Red Bull + Flavor | CR-067 | 3.00 | `PRD-00088` REDBULL WATERMELON<br>`PRD-00089` REDBULL COCONUT | 3.00 | ❓ الاسم القديم عام |
| Carbonated Drink + Flavor | CR-067 | 1.50 | `PRD-00079` 7UP | 1.50 | ❓ الاسم القديم عام |
| Lemon & Mint Smoothie | CR-067 | 2.75 | `PRD-00119` SMOOTHIE LEMON MINT | 3.50 | 🔺 السعر تغيّر — المعتمد سعر ملفك |
| Vanilla Milkshake | CR-067 | 2.75 | `PRD-00091` MILKSHAKE VANILIA | 3.50 | 🔺 السعر تغيّر — المعتمد سعر ملفك |
| San Sebastian | CR-073 | 2.00 / 3.50 | `PRD-00178` SAN SEBASTIAN | 3.50 | ✅ تعارض CR-073 محسوم بملفك |
| Tiramisu | CR-070 | — | `PRD-00171` TIRAMISU | 3.00 | ✅ موجود |
| V60 Pour Over | CR-070، CR-076 | — | `PRD-00158` V60 COLOMBIA HUILA<br>`PRD-00159` V60 HONDURAS LAS<br>`PRD-00160` V60 INDONESIA<br>`PRD-00161` V60 PERU CAJAMARCA | 3.50 | ✅ 4 محاصيل |
| Caramel Frappe | CR-074 | — | `PRD-00138` FRAPE CARAMEL | 3.50 | ✅ موجود |
| Milkshake Blueberry | CR-069 | — | `PRD-00109` MILKSHAKE BLUEBERRY | 3.75 | ✅ موجود |
| Cookies · Nutella Cookies | CR-074 | — | `PRD-00188` COOCKIES CHOCOLATE<br>`PRD-00190` NUTELLA COOKIES | 1.00 / 1.50 | ✅ موجود |
| Cold Brew | CR-069، CR-070 | — | — | — | ❌ غير موجود في الملف (MQ-05) |
| Lotus Cheese Cake | CR-070 | — | — | — | ❌ غير موجود في الملف (MQ-05) |
| Red Velvet Cheesecake | CR-074 | — | `PRD-00181` RED VALVET CAKE | 3.50 | ❓ الملف فيه RED VALVET CAKE (كيك، ليس تشيز كيك) (MQ-05) |
| 'Code Red With Flavor' | CR-074 | — | — | — | ❓ لا تطابق مباشر: CODE RED / SHELTER ENERGY CODE RED / MOJITO CODE RED (MQ-05) |
| Ice cream · Snacks | CR-007 | — | — | — | ❌ غير موجود في الملف (MQ-05) |
| Aeropress · French press · Filter | CR-076 | — | — | — | ❌ غير موجود — الملف فيه V60 فقط (و AMERICAN COFFEE غير محدد) |

**الخلاصة:**
- **18** صنفًا من المنيو القديم تطابق ملفك بالسعر نفسه.
- **صنفان تغيّر سعرهما:** Lemon & Mint Smoothie وVanilla Milkshake، من 2.75 قديمًا إلى 3.50 في ملفك.
- **6 أصناف أو مجموعات** غير موجودة في ملفك أو لا تطابق مباشرة (MQ-05).
- **ملاحظة خطر:** الموقع القديم ما زال يعرض أسعارًا قديمة على الأغلب، وترجمات آلية خاطئة مثل "Iced Chicken" و"Iced Croissants" (RISK-09). لم نغيّر شيئًا في Production.

## 10. Proposed Product / Category ID structure

| الكيان | الصيغة | النطاق في هذا الملف | مثال | قاعدة الإسناد |
|---|---|---|---|---|
| **Category** | `CAT-` + 3 أرقام | `CAT-001` → `CAT-011` | `CAT-008` = SPECIALITY COFFEE | حسب ترتيب ظهور الفئة في الملف (الورقة الأولى ثم الثانية) |
| **Product** | `PRD-` + 5 أرقام | `PRD-00001` → `PRD-00192` | `PRD-00158` = V60 COLOMBIA HUILA | حسب ترتيب الصفوف في الملف. **لا يحتوي الاسم ولا الفئة ولا رقم الكاشير** |
| **Variant** (وحدة البيع، وهي التي تحمل السعر) | `{product_id}-V` + رقمان | `PRD-00001-V01` … | `PRD-00083-V01` | كل صنف يبدأ بخيار افتراضي واحد `V01` |
| **Import batch** | `IMP-` + 4 أرقام | `IMP-0001` | هذا الملف | يربط كل صف بالملف المصدر وبصمته SHA-256 |
| **Media** | `MED-` + 6 أرقام | — | `PRD-00158_main.webp` | عند وصول الصور (D-083) |
| **Modifier** | `MGR-###` · `MOD-####` | — | — | إذا وُجدت إضافات رسمية (MQ-07) |

**قواعد الثبات:**
1. **المعرّفات الآن مسودة.** تُجمّد عند اعتمادك لهذا التقرير، وبعدها **لا تتغير أبدًا**: لا بتغيير الاسم، ولا السعر، ولا الفئة، ولا الترتيب.
2. **لا يُعاد استخدام أي رقم.**
   - صنف يُلغى قبل التجميد (مثل أحد صنفي DUP-01) يصبح رقمه `VOID`.
   - صنف يتوقف بعد التجميد يصبح `Archived` ويبقى رقمه.
3. **الأصناف الجديدة مستقبلًا** تأخذ الرقم التالي: `PRD-00193` وما بعده.
4. **الفصل الثلاثي (D-089):**

| الطبقة | مثال | من يغيّرها | متى |
|---|---|---|---|
| **Internal ID** | `PRD-00058` | لا أحد | أبدًا |
| **Display Name** | ICED SPANISH LATTE / آيس سبانيش لاتيه | الـOwner من الـDashboard | بموافقة، مع سجل تعديل |
| **Slug** | `iced-spanish-latte` | الفريق بموافقة | فقط مع 301 وسجل Slugs — يُنشأ **بعد** اعتماد أسماء العرض |

5. **ربط الأنظمة مستقبلًا:**

| النظام | الحقل | يرتبط بـ |
|---|---|---|
| POS | `pos_item_id` | Variant |
| ERP / SHELTER COFFEE DRIVE SYSTEM | `external_item_id` | Variant |
| Analytics (GA4) | `item_id` = Product ID، و`item_variant` = Variant ID | — |
| الصور | `MED-` + اسم الملف `PRD-xxxxx_main` | Product |
| البحث | — | Product ID + الأسماء والمرادفات |
| التوفر حسب الفرع | — | Variant × Branch |

## 11. Proposed normalized Menu Data Model

النموذج الكامل محدّث إلى **v0.2** في [`17-menu-data-model-draft.md`](17-menu-data-model-draft.md). أهم ما تغيّر بعد قراءة الملف:

| # | التغيير | السبب من الملف |
|---|---|---|
| DM-01 | **Variant = وحدة البيع دائمًا.** كل Product له Variant واحد على الأقل (`V01`)، والسعر والتوفر ورقم الكاشير على الـVariant | يوحّد مكان السعر. يسمح بتجميع "بدون سكر" وRED BULL وTURKISH S/D لاحقًا بدون تغيير بنية (MQ-02، MQ-08) |
| DM-02 | **حقول مصدر غير قابلة للتعديل:** `import_batch_id` · `source_sheet` · `source_row` · `source_no` · `name_en_source` · `name_ar_source` · `price_source` | تتبع كل قيمة إلى ملفك، وإثبات أن السعر لم يُعدَّل |
| DM-03 | **فصل اسم المصدر عن اسم العرض:** `name_en` و`name_ar` تبدأ مساوية للمصدر بحالة `SOURCE_UNREVIEWED`. أي تعديل يمر عبر Name Review (Current / Suggested / Reason / Decision) | الأسماء مختصرة بأسلوب الكاشير، ولا تصحيح تلقائي |
| DM-04 | **Price:** `amount_minor` بالفلس كعدد صحيح (2.75 ← 2750) · `currency = JOD` · `price_includes_tax = true` · `branch_id = null` (سعر أساسي) | السعر كما هو بدون أخطاء تقريب. **لا Net Price ولا Tax fields** إلا بعد نقاش الـData Model (D-089) |
| DM-05 | **Availability:** 192 × 2 فرع = 384 سجلًا بحالة `UNKNOWN` | الملف لا يحدد الفرع (MQ-01) |
| DM-06 | **Category:** إضافة `name_ar_source` و`is_seasonal` و`season_start/end` | الفئات لها أسماء عربية في الملف، وSPRING قد تكون موسمية (MQ-06) |
| DM-07 | **سمات اختيارية:** `temperature` (Hot / Iced) و`is_sugar_free` — **لا تُملأ إلا بعد تأكيدك** | مفيدة للبحث والتصفية لاحقًا، ولا تُستنتج تلقائيًا |
| DM-08 | **Content Review entity** لكل اقتراح (اسم، اسم عربي، دمج مكرر) مع قرارك وتاريخه | سجل قرارات قابل للتدقيق (D-084) |

**الكيانات (منطقية — المنصة لم تُختر بعد، DB-08):**

```
import_batch ─┐
market ── branch                       (jo · drive / house)
category ── product ── variant ──┬── price          (base · branch_override)
   │           │                 ├── availability   (variant × branch)
   │           │                 └── pos mapping    (pos_item_id · external_item_id)
   │           ├── media
   │           └── modifier_group ── modifier   (فارغ حتى MQ-07)
content_review · audit_log · version
```

## 12. ما بعد الاعتماد

1. **أجوبتك على P0** (MQ-01 → MQ-09).
   - يمكن الرد مباشرة في المحادثة، أو في ورقة `Questions` داخل ملف الـInventory.
2. **قراراتك على الأسماء** (MQ-10 → MQ-14) في أوراق `Name_Review` و`Arabic_Suggestions`.
3. نصدر **Inventory v1.0**: المعرّفات مجمّدة، الأسماء معتمدة، وDM-01 → DM-08 معتمدة.
4. ← **Menu Information Architecture** ← **Menu UX/UI**.
5. **لا تصميم منيو قبل ذلك** (D-088).
