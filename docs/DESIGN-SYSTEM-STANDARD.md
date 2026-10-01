# SHELTER DESIGN SYSTEM — GLOBAL UI CONSISTENCY STANDARD

> **المتطلب:** `OWNER APPROVED · FROZEN · P0 · GLOBAL DESIGN SYSTEM` (M34، 2026-10-01).
> **هذه الوثيقة:** القواعد والحوكمة. **القيم في مصدر واحد:** [`../design-system/tokens/tokens.json`](../design-system/tokens/tokens.json) ← يُولّد منه `design-system/build/tokens.css` (`npm run tokens:css`).
> **الصحة:** [`DESIGN-SYSTEM-HEALTH.md`](DESIGN-SYSTEM-HEALTH.md) (`npm run ds:audit`).

**المبدأ:** ONE DESIGN SYSTEM · ONE VISUAL LANGUAGE · NO PAGE-SPECIFIC CHAOS.
- **يطبّق على:** الموقع + Owner Dashboard + أي تطبيق مستقبلي.
- **الهدف:** المستخدم يشعر أنه داخل منتج واحد في: Home · Menu · Locations · About · Franchise · Careers · Media · Dashboard.

## 1. ما قُرر الآن وما ينتظر الهوية
| الطبقة | الحالة | من يقرر |
|---|---|---|
| **قيم بنيوية** (المسافات، الزوايا، سلّم الخط، الحركة، الظلال، الطبقات، نقاط التحول، أحجام التحكم والأيقونات، الحاويات) | ✅ **مقررة** في `tokens.json` v0.1. قابلة للضبط في التصميم المرئي | Claude (قرار تقني مفوّض) |
| **الخطوط الأساسية** (العربي والإنجليزي) | ⛔ `MISSING` | **ملفات الهوية (M-10 / PO-002).** إن لم يحتوِ الدليل خطوطًا، يقترح Claude أزواجًا عربية/لاتينية للاعتماد |
| **قيم الألوان** | ⛔ `MISSING` | ملفات الهوية. **الأسماء الدلالية** (`bg` · `surface` · `text` · `muted` · `border` · `accent` · `success` · `warning` · `danger` · `info` · `focus`) مقررة الآن |
| **الأيقونات** | ✅ مكتبة واحدة: **Lucide** (بعد DB-08). الأحجام 16/20/24، والـStroke ثابت 1.75 | |

> قيم الـ`wireframe` في `tokens.json` **رمادية للنماذج فقط، ولا تُشحن أبدًا.**

## 2. الخط (Typography)
- **العائلات:** `--font-ar` و`--font-en`. احتياطي العربي: Noto Sans Arabic ← Segoe UI ← Tahoma ← system-ui.
- **السلّم:** `--text-xs` (12) … `--text-2xl` (24)، ثم مقاسات مرنة `--text-3xl/4xl/5xl` بـ`clamp()`.
  - **أصغر نص واجهة 12px.**
  - النص الأساسي 16px على الموبايل.
- **الأوزان:** 400 / 500 / 700 فقط.
- **ارتفاع السطر:** العربي 1.7، والإنجليزي 1.5، والعناوين 1.25.
- **⚠️ قاعدة عربية:** **لا Letter-spacing على العربي أبدًا** (يكسر اتصال الحروف). التباعد `0.02em` للأحرف اللاتينية الكبيرة فقط (أسماء المنتجات ALL CAPS).
- **طول السطر للقراءة:** ≤ `68ch` على الديسكتوب (`.ds-reading`).
- **الاستخدامات:** خط الأزرار والتنقل والـDashboard = نفس السلّم:
  - الأزرار `--text-sm` عريض.
  - التنقل `--text-sm`/`--text-base`.
  - جداول الـDashboard `--text-sm`.
- **ممنوع:** حجم خط عشوائي داخل أي مكون.

## 3. الأزرار — Component واحد
| المتغير | الاستخدام |
|---|---|
| Primary | فعل رئيسي واحد في الشاشة |
| Secondary | فعل ثانوي |
| Outline | بديل خفيف |
| Ghost | أفعال داخل الأشرطة والجداول |
| Danger | حذف أو أرشفة نهائية **مع تأكيد** |
| Text/Link | روابط بشكل زر |
| Icon Button | أيقونة + `aria-label` إلزامي |

- **الأحجام:**
  - **Small (36):** للجداول الكثيفة على الديسكتوب فقط (مؤشر دقيق).
  - **Medium (44):** الافتراضي.
  - **Large (52).**
- **الحالات:** Default · Hover · Pressed · **Focus** (حلقة 3px، لا تُزال) · Disabled · Loading (`aria-busy`، والنص لا يختفي).
- **ثابت لكل الأزرار:** نفس الـradius (`--radius-md`)، والخط، ومنطق الارتفاع، والحشوة، والتركيز، والحركة.

## 4. الزوايا (Radius) — مستديرة لا حادة
| Token | القيمة | الاستخدام |
|---|---|---|
| `--radius-md` | **10px — الأساسي للعلامة** | الأزرار، والحقول، والقوائم، ومحدد الفرع |
| `--radius-lg` | 14px | البطاقات، واللوحات، وصور البطاقات |
| `--radius-xl` | 20px | الـBottom sheet (أعلى)، والـModal، وصور الـHero |
| `--radius-sm` | 6px | الشارات، والعناصر داخل الجداول الكثيفة |
| `--radius-pill` | دائري | الـChips فقط |

**ممنوع:** زوايا مختلفة حسب الوحدة أو الصفحة.

## 5. الألوان
- **الأدوار الدلالية فقط** (§1). **ممنوع Hex خام** في أي صفحة أو مكون.
- **أي لون جديد** = إضافة Token إلى `tokens.json` باسم دور، **وليس باسم قيمة**.
- **التباين:**
  - النص ≥ 4.5:1، والكبير ≥ 3:1، والإطارات والأيقونات ≥ 3:1.
  - **يُفحص آليًا** (axe) لكل ثيم، بما فيه الثيمات الموسمية في محرك التجارب.

## 6. المسافات والتخطيط
- **المسافات:** سلّم 4px فقط (`--space-1…20`). كل margin وpadding وgap من السلّم.
- **الحاويات:**

| الحاوية | العرض الأقصى |
|---|---|
| الصفحة | 1280 |
| الـDashboard | 1440 |
| القراءة | 720 |

- **الهوامش الجانبية (Gutters):** 16 موبايل / 24 تابلت / 32 ديسكتوب.
- **الأعمدة:**
  - الموبايل: عمود واحد إلى عمودين.
  - التابلت (≥ 600): 3 أعمدة للشبكات.
  - الديسكتوب (≥ 1024): شبكة 12 عمودًا.
  - ≥ 1200: 4 أعمدة للبطاقات.
- **نقاط التحول مبنية على المحتوى:** 360 · 600 · 1024 · 1200 + `max-height: 500px`.
  - **داخل المكونات:** Container Queries حيث تفيد.
  - **ممنوع** Breakpoint خاص بصفحة بلا سبب موثق.

## 7. الظلال والحركة والطبقات
- **الظلال:** `none` · `sm` · `md` · `lg` (للـModal والـSheet فقط). نظيفة وخفيفة، **بلا توهج**.
- **الحركة:**
  - المدد: `--motion-fast` (120ms) · `normal` (200ms) · `slow` (320ms).
  - التسارع: `--easing-standard` · `enter` · `exit`.
  - `prefers-reduced-motion` ← صفر وبديل ثابت.
  - **Motion هي المكتبة الوحيدة** (GSAP فقط بمبرر موثق ولا تجتمعان).
- **الطبقات (Z-index) — ترتيب ثابت:**
  1. `sticky` (20)
  2. `header` (30)
  3. `dropdown` (40)
  4. `overlay` (50)
  5. `modal` (60)
  6. `toast` (70)
  7. `safeMode` (80)

## 8. المكونات المعتمدة (المكتبة المستهدفة — Storybook بعد DB-08)
| الفئة | المكونات |
|---|---|
| أساسيات | Button · Icon Button · Link · Badge · Chip · Tag · Divider · Skeleton · Spinner |
| النماذج | Input · Textarea · Select · Checkbox · Radio · Switch · Date (3 Selects للتوظيف، Date picker للـDashboard) · File Upload · Search · Validation message · Error summary |
| البطاقات (أساس واحد) | Standard · Media · **Product** · **Metric** (Dashboard) · Article · Event · Branch |
| التنقل | Header · Mobile nav · Bottom nav (Dashboard) · Sidebar (Dashboard) · Breadcrumb · Tabs · Category bar · Segmented control (الفرع) · Pagination · Footer |
| الطبقات | Modal · Bottom Sheet · Drawer/Side panel · Dropdown · Tooltip · Toast |
| البيانات | Table (← بطاقات على الموبايل) · List · Empty state · Alert · Status chip (PASS/WARNING/BLOCKING، SYNCED/OUT OF SYNC…) · Chart (بسيط، وبديل ملخص على الموبايل) |
| الأقسام | **Section** (eyebrow · title · description · media · CTA · background variant · spacing variant) · Hero · CTA band · Timeline · FAQ accordion |

**القصص (Stories):** لكل مكون: Default · Hover · Focus · Disabled · Loading · Error · Empty · Mobile · RTL · LTR.

**الحالات الموحدة:** Hover · Focus · Active · Disabled · Loading · Error · Success · Empty. **الحالة لا تُعرف باللون وحده** (نص أو أيقونة أو شكل).

## 9. RTL / LTR من الأساس
**التنفيذ:**
- **Logical properties فقط:** `margin-inline-start` · `inset-inline` · `text-align: start`. **لا** `left`/`right` في المكونات.
- **الأيقونات:**
  - **تنعكس** الاتجاهية: الأسهم، و"التالي/السابق"، والـBreadcrumb، والـCarousel.
  - **لا تنعكس:** البحث، والإغلاق، والتشغيل، والشعارات.
- **الـDrawers والـSide panels:**
  - تظهر من جهة **البداية** في الـDashboard العربي (اليمين).
  - تظهر من جهة **النهاية** للعرض السريع، بشكل ثابت عبر اللغتين.
- **النصوص المختلطة:** داخل `<bdi>` (الأسعار، والأرقام، والأسماء اللاتينية في العربي).

**الاختبار:** كل مكون وكل صفحة بالعربي والإنجليزي ضمن مصفوفة الـ20 عرضًا (`tooling/viewports.mjs`).

## 10. الـDashboard = نفس اللغة
- **نفس:** الـTokens والمكونات والخطوط والزوايا.
- **الفرق الوحيد كثافة أعلى:**
  - جداول `--text-sm`.
  - أزرار Small على الديسكتوب.
  - حاوية 1440.
- **ممنوع** مظهر SaaS عام أو ثيم مكتبة افتراضي (shadcn أساس هندسي فقط — M24).

## 11. تحرير المحتوى ≠ تحرير التصميم (CMS Design Lock)
- **الـOwner يغيّر:** النص · الصور · المحتوى · الترتيب · الإظهار · **المتغيرات المعتمدة** (مثل: Section بخلفية فاتحة أو داكنة، أو Hero بصورة أو بلا صورة).
- **لا يوجد في الـCMS:**
  - حقل CSS، أو لون Hex حر، أو حجم خط حر.
  - نمط زر حر، أو مسافة حرة.
- **قوالب الأقسام** مبنية من المكونات المعتمدة فقط.

## 12. حوكمة المكونات (قبل أي Component جديد)
1. **هل يوجد مكون أو متغير يحقق الحاجة؟** ← **أعِد الاستخدام.**
2. **لا؟** ← اقتراح **متغير جديد** في النظام: الاسم، والسبب، والحالات، وRTL/LTR، والموبايل. يُضاف للمكتبة، **وليس داخل الصفحة.**
3. **استثناء ضروري** (Design Exception) ← يُسجّل في الجدول أدناه: السبب، والمكان، وتاريخ المراجعة.

| # | الاستثناء | المكان | السبب | المراجعة |
|---|---|---|---|---|
| DE-01 | لا شيء بعد | — | — | — |

**ممنوع:** Inline styling عشوائي · CSS خاص بصفحة · أزرار أو حقول أو زوايا مختلفة لكل وحدة.

## 13. الضمانات الآلية
| الضمانة | الآن | عند البناء (بعد DB-08) |
|---|---|---|
| تدقيق الـTokens | `npm run ds:audit` على كل الـWireframes ← `DESIGN-SYSTEM-HEALTH.md` | + قاعدة Lint (Stylelint أو ما يناسب المنصة) تمنع Hex وpx خارج الـTokens في الـCI. **أداة جديدة تُسجّل في Tooling Registry قبل التثبيت** |
| الاتساق البصري | مصفوفة الاستجابة (20 عرضًا، AR/EN) + axe | **Visual Regression للمكونات الأساسية** في Storybook + للصفحات الحرجة (M32). عتبة معقولة بلا آلاف الإنذارات الكاذبة |
| تقرير الصحة | Approved · Duplicate · Deprecated · Unused variants · Token violations | نفس التقرير من الكود |

## 14. المرحلة الحالية
| المرحلة | الحالة |
|---|---|
| **الآن (قبل الهوية والمنصة)** | Tokens بنيوية + طقم Wireframes مشترك (`design-system/wireframe-kit.css`) **تستخدمه كل النماذج.** الترحيل جارٍ للمنيو والتوظيف، والشراكات والـDashboard تبنى عليه |
| **عند وصول ملفات الهوية (M-10)** | تُملأ قيم الألوان والخطوط في `tokens.json`. **لا تغيير في أي صفحة**، فالكل يقرأ الـTokens |
| **عند DB-08** | المكتبة الحقيقية + Storybook + Lint + Visual regression |
