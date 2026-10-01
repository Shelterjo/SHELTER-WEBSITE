# FRONTEND TOOLING — Matrix · Registry · Rules

> **الحالة:** `IMPLEMENTED — PARTIAL` (أدوات الجودة المستقلة عن إطار العمل فقط) · **آخر تحديث:** 2026-10-01
> **المصدر:** طلب الـOwner (Tooling Audit) + قاعدة الاستجابة الإلزامية (Responsive mandatory) + قاعدة الدمج (Tooling Registry).
> **النطاق:** هذه أدوات جودة وبنية تحتية، **وليست تصميمًا ولا كود موقع.** لا Visual Design قبل اعتماد الـIA والـWireframes، ولا أدوات تعتمد على React قبل قرار المنصة **DB-08**.

## 1. القرار المختصر
| الحالة | الأدوات |
|---|---|
| **مثبتة ومُعدّة** (`tooling/`) | Playwright Test 1.56.1 · axe-core/playwright 4.13.0 · Lighthouse 13.5.0 · Sharp 0.35.5 |
| **مؤجلة إلى قرار المنصة DB-08** (تفترض React) | shadcn/ui · Radix · Motion (`motion/react`) · Storybook · Lucide (`lucide-react`) · React Aria |
| **مؤجلة إلى قرار المنصة + الهوية (M-10)** | Tailwind (لا قيم Tokens بدون ملفات الهوية) |
| **مؤجلة لمرحلة لاحقة** | Unlighthouse (قبل الإطلاق، يحتاج موقعًا حقيقيًا متعدد الصفحات) · sitespeed.io (مرحلة QA، عبر Docker) |
| **اختيارية ومقيدة** | Lenis: ليس عامًا، و**لا يُشغّل تلقائيًا على صفحة المنيو** (191 صنفًا، والتمرير الأصلي أفضل). يُستخدم فقط في صفحات تستفيد بصريًا مثل Home/About **إذا أثبت الاختبار أنه لا يضر الأداء** (نص الـOwner) |
| **مرفوضة / مكررة** | لا شيء مثبت مكرر. **ممنوع:** مكتبة حركة ثانية بجانب Motion · مكتبة أيقونات ثانية · إطار اختبار E2E ثانٍ (Cypress) · axe بأكثر من تكامل |

## 2. FRONTEND TOOLING MATRIX
| Tool | Already Installed? | Purpose | Overlap | Compatibility | Performance Cost (للزائر) | Recommended? | Install? | Reason |
|---|---|---|---|---|---|---|---|---|
| **Playwright Test** | ✅ الآن (+ Chromium مثبت مسبقًا في البيئة) | E2E · Responsive (20 Viewport) · RTL/LTR · Keyboard · Screenshots | لا | Node ≥ 22 ✅ · Chromium فقط محليًا | صفر (أداة تطوير) | **REQUIRED** | ✅ تم | أساس الـResponsive QA الإلزامي. الإصدار مثبت على 1.56.1 ليطابق Chromium الموجود |
| **@axe-core/playwright** | ✅ الآن | فحص WCAG 2.2 AA آلي داخل اختبارات Playwright | لا (axe واحد فقط) | ✅ | صفر | **REQUIRED** | ✅ تم | يكشف التباين والأسماء والأدوار تلقائيًا. لا يغني عن الفحص اليدوي |
| **Lighthouse** | ✅ الآن | Performance · A11y · Best Practices · SEO + أسباب جذرية | جزئي مع Unlighthouse (نفس المحرك) | ✅ (Node ≥ 22.19) | صفر | **REQUIRED** | ✅ تم | بوابات من `PERFORMANCE-BUDGET.md` |
| **Sharp** | ✅ الآن (libvips 8.18.7) | خط صور: قص 1:1، AVIF/WebP، `srcset`، بلا تكبير | لا | ✅ | **يقلل** حجم الصور للزائر | **REQUIRED** | ✅ تم | يعالج **فقط** الصور `APPROVED` |
| **Unlighthouse** | ❌ | Lighthouse لكل صفحات الموقع دفعة واحدة | Lighthouse | ✅ (≥ 22.18) | صفر | RECOMMENDED | ⏸ لاحقًا | لا يوجد موقع متعدد الصفحات بعد. يُثبت قبل الإطلاق على نسخة Preview |
| **sitespeed.io** | ❌ | قياس أداء متكرر ومقارنات زمنية | Lighthouse | Docker مفضّل (ثقيل) | صفر | OPTIONAL | ⏸ مرحلة QA | قيمته بعد وجود Staging |
| **shadcn/ui** | ❌ | مكونات مصدرية فوق Radix (تُنسخ للمشروع) | Radix | **React فقط** | منخفض (شجرة مكونات) | RECOMMENDED (شرطي) | ⏸ DB-08 | **أساس هندسي فقط. ممنوع شكله الافتراضي**، والهوية من SHELTER Tokens |
| **Radix Primitives** | ❌ | Dialog/Popover/Tabs… بسلوك وa11y صحيح | React Aria | **React فقط** | منخفض | RECOMMENDED (شرطي) | ⏸ DB-08 | Sheet/Modal/Combobox تحتاج سلوكًا صحيحًا |
| **React Aria** | ❌ | بديل/مكمل لـRadix لأنماط معقدة | Radix | React فقط | منخفض | OPTIONAL | ⏸ فقط عند فجوة حقيقية | لا نثبت مكتبتي Primitives بلا حاجة |
| **Tailwind CSS** | ❌ | تنفيذ الـTokens كـUtilities | — | أي إطار | منخفض (CSS مُقلّم) | RECOMMENDED (شرطي) | ⏸ DB-08 + M-10 | بلا قيم هوية لا فائدة من إعداده الآن |
| **Motion** | ❌ | حركة هادفة: Micro-interactions، انتقالات، **Sheet المنيو**، Modal، Cards (حسب الـOwner) | Lenis (جزئيًا) | `motion/react` يحتاج React. نسخة JS عامة متاحة | متوسط ← يُحمّل **Lazy** عند أول تفاعل | REQUIRED (شرطي) | ⏸ DB-08 | مسموح للـSheet/Modal في المنيو، **بشرط** ألا يدخل المسار الحرج: لا يؤثر على LCP/INP/CLS، وضمن ميزانية JS المنيو، وإلا CSS بديلًا (CF في سجل التعارضات) |
| **GSAP** | ❌ | تأثيرات حركة معقدة لا تحققها Motion أو CSS (مثل Timeline موسمي خاص) | **Motion** (تكرار) | أي إطار | متوسط | OPTIONAL | ⏸ فقط بمبرر موثق | مسموح "حيث مبرر" (M31). **لا تُحمّل مع Motion في نفس الصفحة** (قاعدة عدم الازدواج M29 §56). Motion هي الافتراضية |
| **Lenis** | ❌ | تمرير ناعم لصفحات Story/Home | قد يتعارض مع `scroll-padding` والـanchors والـa11y | أي إطار | متوسط | OPTIONAL | ⏸ عند الحاجة | ليس عامًا · لا يُشغّل تلقائيًا على المنيو · فقط إذا أثبت الاختبار أنه لا يضر الأداء |
| **Storybook** | ❌ | توثيق واختبار المكونات وحالاتها (AR/EN، Viewports) | لا | يحتاج إطارًا | صفر | RECOMMENDED | ⏸ DB-08 | الخطة في §6 |
| **Lucide** | ❌ | أيقونات خطية موحدة | — | `lucide-react` أو SVG ثابتة | منخفض جدًا (Tree-shaking) | RECOMMENDED | ⏸ DB-08 | مجموعة واحدة فقط |

## 3. TOOLING REGISTRY (قاعدة الدمج)
| TOOL | PURPOSE | INSTALLED? | CONFIGURED? | USED? | REDUNDANT? | KEEP/REMOVE |
|---|---|---|---|---|---|---|
| Playwright Test 1.56.1 (`tooling/`) | اختبارات Responsive/A11y/Structure | ✅ | ✅ `playwright.config.mjs` + `viewports.mjs` | ✅ 456 فحصًا ناجحًا على الـWireframes (آخر تشغيل) | لا | KEEP |
| Playwright CLI عام 1.56.1 (بيئة) | تشغيل أدوات الـWireframes القديمة (`shots.js`، `measure.js`، `compose.js`) | ✅ (بيئة) | ✅ | ✅ | **جزئيًا** (نفس الإصدار) | KEEP الآن · IMPROVE: نقل أدوات الـWireframes إلى `tooling/` لاحقًا |
| @axe-core/playwright 4.13.0 | WCAG آلي | ✅ | ✅ (wcag2a/aa، 21a/aa، 22aa) | ✅ | لا | KEEP |
| Lighthouse 13.5.0 | أداء/جودة | ✅ | ✅ `scripts/lighthouse.mjs` | ✅ | لا | KEEP |
| Sharp 0.35.5 | خط الصور | ✅ | ✅ `scripts/images.mjs` | ✅ (Selftest) · 0 صور معتمدة | لا | KEEP |
| Python 3 scripts (`hours_logic_check.py`، `wf.py`، `subcats.py`) | منطق الساعات (12/12) ومولد الـWireframes | ✅ | ✅ | ✅ | لا | KEEP (أدوات أدلة، ليست كود إنتاج) |
| Skill: ui-ux-pro-max | مرجع UX استُخدم في دراسة المنيو | — | — | ✅ مرة واحدة | لا | KEEP كمرجع |
| Connector: Semrush | بيانات SEO | متاح | ❌ | ❌ **لم يُستخدم** | — | **مدفوع/حصة: لا استخدام بلا موافقة مسبقة** |
| Connectors: Supabase · Vercel · Cloudflare · HubSpot · Sentry | منصات محتملة | متاحة في الجلسة | ❌ | ❌ | — | لا استخدام قبل قرارات DB-08 والصلاحيات. Sentry مدفوع جزئيًا: اقتراح أولًا |
| Unlighthouse · sitespeed.io · shadcn · Radix · Tailwind · Motion · Storybook · Lucide · React Aria · Lenis | انظر §2 | ❌ | ❌ | ❌ | — | مؤجلة بقرار |

## 4. الأوامر (من داخل `tooling/`)
| الأمر | ماذا يفعل |
|---|---|
| `npm ci` | تثبيت الإصدارات المثبتة بالضبط (`package-lock.json`) |
| `npm run test:e2e` | كل الاختبارات على كل الـViewports الـ20 (Chromium) |
| `npm run test:responsive` / `npm run test:a11y` | الاختبارات الموسومة فقط |
| `SHELTER_ALL_BROWSERS=1 npx playwright test` | Firefox + WebKit **حيث تكون مثبتة (CI)**. غير متاحة في هذه البيئة |
| `SHELTER_VISUAL=1 npx playwright test --grep @visual` | Screenshot comparison. **تُنشأ الـBaselines فقط بعد اعتماد التصميم المرئي** |
| `npm run lighthouse [-- --assert] [paths…]` | Lighthouse موبايل + بوابات الأداء. مع `SHELTER_BASE_URL` يفحص نسخة Preview |
| `npm run images:build` / `images:selftest` | خط الصور / اختبار ذاتي بصور اصطناعية |
| `npm run report` | تقرير HTML لآخر تشغيل |

## 5. بوابات الجودة (لا تُعتبر صفحة DONE قبلها)
1. **Responsive:** 20 مشروعًا في `tooling/viewports.mjs`:
   - العروض الإلزامية الـ14: 320 · 360 · 375 · 390 · 412 · 430 · 768 · 820 · 1024 · 1280 · 1366 · 1440 · 1536 · 1920.
   - إضافة: Ultra-wide 2560، Landscape (568×320، 844×390، 932×430، 1024×768، 1180×820)، وZoom 200% (1280 عند 200% = 640×400).
   - **لا Overflow ولا Clipping ولا Overlap. أي واحد منها Bug.**
2. **RTL + LTR:** كل اختبار يعمل بالعربية والإنجليزية.
3. **A11y:** axe بلا مخالفات Serious/Critical، وأهداف لمس ≥ 44px، وحلقة تركيز مرئية ≥ 2px، ونص الواجهة ≥ 12px، والتكبير غير ممنوع.
4. **Lighthouse (موبايل):**
   - Performance ≥ 90 · A11y ≥ 95 · Best Practices ≥ 90 · SEO ≥ 90.
   - LCP ≤ 2.5s · CLS ≤ 0.1 · TBT ≤ 200ms.
5. **Visual:** Screenshot comparison بعد اعتماد التصميم.
6. **يدوي:**
   - أجهزة حقيقية: iPhone Safari، Android Chrome.
   - قارئ شاشة: VoiceOver، TalkBack.
   - كيبورد فقط.
   - انظر `docs/qa/RESPONSIVE-QA-MATRIX.md`.

## 6. خطة Storybook (عند اعتماد DB-08)
- **قصة لكل مكون في الحالات التالية:**
  - AR/EN.
  - 320 / 390 / 768 / 1280.
  - مع صورة / بلا صورة.
  - اسم طويل.
  - تحميل / خطأ.
  - Reduced motion.
- **المكونات:**
  - Header.
  - شريط الفئات والشريط الفرعي.
  - SearchField + الاقتراحات + لا نتائج.
  - BranchSelector (كل الفروع / DRIVE / HOUSE × مفتوح / يغلق قريبًا / مغلق).
  - SeasonalBlock.
  - ProductCard (عادي، بلا صورة، `UNAVAILABLE_SHOW`، "متوفر في … فقط").
  - ProductGrid (1/2/3/4 أعمدة).
  - ProductSheet / Modal.
  - AllCategoriesSheet.
  - Footer.
  - **مكونات الـDashboard لاحقًا:** KPI Card، Chart، Table↔Cards، Alert، Editor forms.
- **إضافات:** a11y addon (axe) وviewport addon. **ممنوع** إدخال ثيم افتراضي.

## 7. سياسة الاعتماديات
- **الإصدارات:**
  - إصدارات دقيقة (`--save-exact`) و`package-lock.json` في git.
  - `npm audit` = 0 ثغرات عند التثبيت (تحقق: 0).
- **أداة واحدة لكل وظيفة.** أي أداة جديدة:
  - تُضاف للـRegistry أعلاه مع السبب، وتُرفض إذا كررت وظيفة موجودة.
- **الخدمات المدفوعة:** لا خدمة مدفوعة ولا API بحصة (Semrush، Sentry…) بلا موافقة مسبقة بعد عرض:
  - الخدمة والغرض.
  - الاستهلاك المتوقع والتكلفة.
  - البديل المجاني.
- **أسرار الخادم:** لا تُخزن في git ولا في الـFrontend. صلاحيات أقل ما يمكن (مثلًا Cloudflare Token محدود بدل Global API Key).
