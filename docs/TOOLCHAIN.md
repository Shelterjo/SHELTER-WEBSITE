# SHELTER DEVELOPMENT TOOLCHAIN — التدقيق والسجل الوحيد للأدوات

> **المرجع:** M37 "DEVELOPMENT TOOLCHAIN — AUDIT + INSTALL + INTEGRATE" (OWNER APPROVED).
> **هذا هو سجل الأدوات الوحيد.** `docs/FRONTEND-TOOLING.md` أصبح سجلًا تاريخيًا للقرارات السابقة، ويحيل إلى هنا.
> **المنصة:** [`ADR-001`](adr/ADR-001-platform.md). Laravel 13 (PHP) + Blade + Vite + MySQL. **لا React.** لهذا اختيرت لكل وظيفة الأداة الأنسب لهذه المنصة، وليس أداة React افتراضية.
> **المبدأ (M37 §33):** أدوات أقل وأعلى جودة. **أداة واحدة لكل وظيفة.**

## 1. جدول التدقيق (M37 §34)

**مفتاح "الحالة":**
- **INSTALLED:** مثبتة.
- **CONFIGURED:** مثبتة ومضبوطة.
- **PARTIAL:** موجودة جزئيًا.
- **MISSING:** غير موجودة.
- **REDUNDANT:** تكرر وظيفة أداة أخرى.
- **NOT NEEDED:** لا تناسب المنصة.

**مفتاح "أين":**
- **FAST:** محلي، عند كل تعديل.
- **CI:** عند كل دفع للكود.
- **FULL:** خط الجودة الكامل، يدويًا أو قبل الإصدار.
- **STAGING:** على بيئة الاختبار المحمية فقط.

| الأداة | الحالة قبل التدقيق | لماذا نحتاجها | تداخل؟ | تثبيت؟ | ضبط؟ | أين | التكلفة / الترخيص |
|---|---|---|---|---|---|---|---|
| **PHPUnit** | MISSING (لا تطبيق بعد) | اختبار منطق الأعمال: الساعات، والساعات الخاصة، والأسعار، والصلاحيات، والتحقق، والمزامنة، والترقيم | — | ✅ مع Laravel | ✅ | FAST + CI | مجاني · BSD-3 |
| **Larastan (PHPStan)** | MISSING | **فحص الأنواع للـPHP** (مكافئ TypeScript strict للخادم). مستوى صارم، **بلا تجاهل عشوائي** | — | ✅ | ✅ | FAST + CI | مجاني · MIT |
| **Laravel Pint** | MISSING | **تنسيق PHP موحد** (مكافئ Prettier للخادم) | لا (Prettier لا ينسق PHP) | ✅ مع Laravel | ✅ | FAST + CI | مجاني · MIT |
| **اختبارات حدود الوحدات** | MISSING | الواجهة لا تصل لقاعدة البيانات، والتكاملات داخل Services/Adapters (M37 §27) | — | ❌ **بلا حزمة**: اختبار PHPUnit (§29) | ✅ | FAST + CI | — |
| **TypeScript (strict)** | MISSING | الـJS القليل في الواجهة والـDashboard يُكتب TypeScript صارمًا، **بلا `any`** | — | ✅ | ✅ | FAST + CI | مجاني · Apache-2.0 |
| **ESLint** | MISSING | صحة الكود، والاستيرادات، والأنماط الخطرة في TS. **بلا قواعد React/Hooks** (لا React) | — | ✅ (Flat config، قواعد قليلة) | ✅ | FAST + CI | مجاني · MIT |
| **Prettier** | MISSING | تنسيق TS/CSS/JSON/YAML. **لا يلمس PHP** (Pint) **ولا الوثائق** (حتى لا تتغير الجداول) | لا | ✅ | ✅ | FAST + CI | مجاني · MIT |
| **Vitest** | MISSING | اختبار وحدات TS فقط (منطق الواجهة). **منطق الأعمال في PHPUnit** | لا (لغتان مختلفتان) | ✅ | ✅ | FAST + CI | مجاني · MIT |
| **Knip** | MISSING | الملفات والتصديرات والاعتماديات غير المستخدمة في جانب JS. **تقرير فقط، بلا حذف تلقائي** | — | ✅ | ✅ | يدوي / FULL | مجاني · ISC |
| **Vite** | MISSING | بناء CSS/JS بأسماء Hash (افتراضي Laravel) + **فحص ميزانية الحزمة** | — | ✅ مع Laravel | ✅ | CI | مجاني · MIT |
| **Storybook 10** (`@storybook/html-vite`) | MISSING | **مرجع الـDesign System:** كل مكون بحالاته Default / Hover / Focus / Active / Disabled / Loading / Error / Empty، وبالعربية RTL والإنجليزية LTR، وموبايل وديسكتوب. **القصص تُولّد من مكونات Blade نفسها**، فلا نسخة ثانية من المكونات | يحل محل معرض المكونات الداخلي (Route في التطبيق) المقترح سابقًا في ADR-001 | ✅ | ✅ | محلي + CI (بناء + a11y + Visual) | مجاني · MIT |
| **@storybook/addon-a11y** | MISSING | axe داخل Storybook لكل قصة | يكمل axe في Playwright | ✅ | ✅ | محلي + CI | مجاني · MIT |
| **storybook-addon-pseudo-states** | MISSING | إظهار Hover/Focus/Active ثابتة لكل مكون (للمرجع والـVisual regression) | — | ✅ | ✅ | محلي + CI | مجاني · MIT |
| **Lucide** | MISSING (مقرر) | مكتبة أيقونات **واحدة**: SVG ثابتة تُنسخ عند الحاجة فقط، **بلا حزمة أيقونات كاملة في الواجهة** | — | ✅ (`lucide-static`، تطوير فقط) | ✅ | Build | مجاني · ISC |
| **shadcn/ui** | REFERENCE ONLY (M40) | **مصدر أنماط الـPrimitives** (البنية والسلوك والوصولية) يُعاد بناؤه كمكونات Blade `x-ui` | — | ❌ لا تثبيت | — | — | تعمل مع React فقط (ADR-001). M40 §49: «primitive factory لا final design» (G22-CF-01). shadcn MCP غير متصل (G22-TF-01) |
| **21st (catalog)** | REFERENCE ONLY (M40) | إلهام واكتشاف أنماط (Hero، تنقل، أقسام تفاعلية)، **بلا نسخ** | — | ❌ لا تثبيت | — | — | MCP غير متصل (G22-TF-01) |
| **motion** (JS العادية، motion.dev) | **INSTALLED** `13.5.0` (M40، PHASE 2) | حركة Premium: Reveal، Stagger، فتح الـSheets/Drawers، Micro-interactions عند الحاجة | CSS transitions تغطي الأبسط | ✅ استيراد الدوال المطلوبة فقط (`animateMini` المبني على WAAPI · `inView` · `stagger`)، في وحدة تُحمّل كسولًا (Dynamic import) خارج حزمة `site.ts` | ✅ | Build | مجاني · MIT · اعتمادية تشغيل (`dependencies`) |
| **View Transitions (CSS)** | PLANNED (M40) | انتقالات خفيفة بين الصفحات **بلا JavaScript وبلا انتظار للتنقل** (`@view-transition`) | — | ✅ (ميزة متصفح، تحسين تدريجي) | ✅ | — | بلا حزمة |
| **Radix primitives** | NOT NEEDED | — | — | ❌ | — | — | **React فقط.** البديل عناصر HTML الأصلية (`<dialog>`، و`popover`، و`details`) بأنماط ARIA APG، **مختبرة بـaxe ولوحة المفاتيح** |
| **Tailwind CSS** | REDUNDANT | — | **يكرر** Tokens + CSS المكونات | ❌ | — | — | وجود نظامي تنسيق = One-off styling. القرار: **Tokens فقط** (M34) |
| **Playwright** | CONFIGURED (20 عرضًا، للنماذج) | أداة الـQA الأساسية: التنقل، والنماذج، والتحقق، والإرسال، والدخول، والصلاحيات، وتبديل اللغة، وRTL/LTR، والموبايل | — | ✔ موجود | ✅ يُوسَّع للتطبيق | CI (Chromium) + FULL (Firefox/WebKit) | مجاني · Apache-2.0 |
| **axe-core** (`@axe-core/playwright`) | CONFIGURED | WCAG 2.2 AA آليًا (صفر serious/critical). **لا يغني عن الفحص اليدوي:** لوحة المفاتيح، والتركيز، وقارئ الشاشة، واللمس، وتكبير 200%، وتقليل الحركة، والتباين | — | ✔ | ✅ | CI | مجاني · MPL-2.0 |
| **Visual regression** (Playwright screenshots) | PARTIAL (معطل حتى اعتماد التصميم) | خطوط أساس ثابتة للصفحات والمكونات: موبايل / تابلت / ديسكتوب × عربي / إنجليزي. **حدود صارمة** (لا Threshold عالٍ يخفي مشاكل) | — | ✔ | ✅ | CI | مجاني (لا Percy/Chromatic المدفوعين) |
| **Lighthouse** (سكربت مخصص) | CONFIGURED | — | **يكرر** Lighthouse CI | ❌ يُستبدل | — | — | يُدمج في LHCI |
| **Lighthouse CI** (`@lhci/cli`) | MISSING | Performance / A11y / BP / SEO + **ميزانيات** + LCP / CLS / TBT (بديل INP في المختبر). **النتائج تُحفظ محليًا** (`filesystem`)، **وليس** على تخزين Google العام المؤقت | يحل محل السكربت المخصص | ✅ | ✅ | CI | مجاني · Apache-2.0 |
| **Chrome DevTools** | PARTIAL | الشبكة، والرسم، والأداء، وLayout shifts، وأخطاء الـConsole، والذاكرة، وشجرة الوصولية | — | ✔ عبر **Playwright CDP** (Traces، Console، Network، Coverage) | ✅ سكربت `perf:trace` | محلي | مجاني |
| **Real Web Vitals (RUM)** | مصمم (`docs/platform/REAL-USER-MONITORING.md`) | بعد الإطلاق: `web-vitals` ذاتي الاستضافة، مجمّع، بلا PII | — | لاحقًا (Phase 6) | — | Production | مجاني · Apache-2.0 |
| **SEO crawl** | MISSING | العناوين، والوصف، والـCanonical، والـHreflang، والعناوين الرئيسية، والروابط المكسورة، والـSitemap، والـRobots، والـSchema، وسلاسل التحويل | — | ✅ **أمر داخلي** `shelter:seo-audit` = نفس فحوص Site Health في الـDashboard (أداة واحدة للتطوير وللمالك) | ✅ | CI + STAGING | — |
| **Unlighthouse** | NOT NEEDED | — | **يكرر** LHCI + الفاحص الداخلي | ❌ | — | — | — |
| **SearchFit SEO** | غير موجودة ضمن المهارات المفعّلة في هذه الجلسة | — | الموجود: مهارة `e2e-seo-assistant` (تدقيق SEO) | ❌ | — | تُستخدم المهارة الموجودة على Staging | — |
| **Structured data QA** | MISSING | Organization / LocalBusiness / Breadcrumb / Article / Event / FAQ **مولّدة من Master Data فقط** + اختبارات تحقق آلية | — | ❌ بلا حزمة (Service + اختبارات) | ✅ | CI | — |
| **Sharp** | CONFIGURED (دفعات محلية) | Original → Variants (240–1080) → AVIF/WebP، مع `srcset` و`sizes` ونقطة التركيز والأبعاد. **الأصل عالي الدقة لا يصل للعميل** | — | ✔ | ✅ | محلي / Build | مجاني · Apache-2.0 |
| **Semgrep CE** | MISSING | فحص أمني ثابت (PHP/TS): المصادقة، والرفع، واستعلامات القاعدة، والأنماط الخطرة. **`--metrics=off`، لا يُرسل كود**. **كل نتيجة تُتحقق قبل اعتبارها ثغرة** | — | ✅ (pip) | ✅ | CI + محلي | مجاني · LGPL-2.1 (القواعد: Semgrep Rules License، مسموحة للاستخدام الداخلي) |
| **Gitleaks** | MISSING | منع المفاتيح والرموز وكلمات المرور والمفاتيح الخاصة من دخول Git | — | ✅ محلي (pre-commit) + CI | ✅ | FAST + CI | مجاني · MIT. **ملاحظة:** `gitleaks-action` يحتاج ترخيصًا لحسابات المؤسسات، فنستخدم **الملف التنفيذي مباشرة** |
| **composer audit** · **npm audit** | متوفر | ثغرات الاعتماديات من ملفات القفل | — | مدمج | ✅ | CI (**بوابة**) | مجاني |
| **Trivy** | MISSING | تراخيص الاعتماديات + Misconfiguration (ملفات CI) + الثغرات. **لا حاويات في الإنتاج** (Cloudways مُدار) | **جزئي** مع composer/npm audit ← **ليس بوابة**، يعمل في الخط الكامل | ✅ CI فقط (**نسخة مثبتة + Checksum**، بلا Tags متغيرة) | ✅ | FULL | مجاني · Apache-2.0 |
| **OWASP ZAP** | MISSING | الـHeaders، وXSS، والحقن، والجلسات، والإعدادات الخاطئة. **على Staging أو بيئة محلية فقط**، ولا فحص هجومي على الإنتاج | — | ✅ خطة Automation Framework | ✅ | STAGING | مجاني · Apache-2.0. **يحتاج Staging** (إنشاء تطبيق Cloudways = تفويض منك) |
| **Security headers test** | MISSING | CSP، وHSTS، وX-Content-Type-Options، وReferrer-Policy، وPermissions-Policy، وframe-ancestors | — | ❌ بلا حزمة (Middleware + اختبار) | ✅ | CI | — |
| **GitHub Actions** | MISSING | خط الجودة (§3 أدناه). **إصدار لا يمر إذا فشلت بوابة حرجة** | — | ✅ `.github/workflows/` | ✅ | CI | **حصة مجانية** من دقائق GitHub لكل شهر. الخطوات الثقيلة يدوية لتوفير الحصة. **أي تجاوز للحصة = قرار مالي لك** |
| **Context7** | غير متصل بهذه الجلسة | توثيق رسمي محدث للأطر | — | ❌ (يحتاج ربط خدمة خارجية) | — | — | **البديل المستخدم:** قراءة التوثيق الرسمي مباشرة (laravel.com، storybook.js.org…) قبل استخدام أي API |
| **Chrome DevTools MCP** | غير متصل | — | Playwright CDP يغطيه | ❌ | — | — | — |
| **مهارات التصميم** (`frontend-design` · `ui-ux-pro-max`) | متوفرة | مراجعة جودة التصميم (M37 §32) قبل اعتبار أي صفحة جاهزة | — | — | — | أثناء البناء | — · M40: عقل التصميم لكل صفحة (UX-006)؛ قاعدة بحثه غير موجودة هنا فتُطبق قواعده مباشرة (G22-TF-01) |
| **مراجعة الكود** | متوفرة (وكيل مراجعة مستقل) | مراجعة عدائية قبل كل Milestone | — | — | — | قبل كل Commit كبير | — |

## 2. الـToolchain النهائي (بعد إزالة التكرار)
| الوظيفة | الأداة الوحيدة |
|---|---|
| فحص الأنواع | **Larastan** (PHP) · **TypeScript strict** (TS) |
| التنسيق | **Pint** (PHP) · **Prettier** (TS/CSS/JSON/YAML) |
| Lint | **ESLint** (TS) · Larastan يغطي PHP |
| اختبار الوحدات | **PHPUnit** (منطق الأعمال) · **Vitest** (منطق TS في الواجهة) |
| حدود الوحدات | اختبار معماري PHPUnit |
| الكود الميت | **Knip** (JS) · Larastan (PHP) |
| مرجع الـDesign System | **Storybook** (قصص مولّدة من Blade) + addon-a11y + pseudo-states |
| فرض الـTokens | **`scripts/ds-gate.mjs`** داخل `npm run lint` على CSS وBlade التطبيق (يغني عن Stylelint). `ds-audit` يبقى للـWireframes |
| E2E والوصولية والـVisual | **Playwright** + axe + screenshots |
| الأداء في المختبر | **Lighthouse CI** + Playwright CDP traces |
| الأداء الحقيقي | `web-vitals` ذاتي (بعد الإطلاق) |
| SEO والـSchema | **`shelter:seo-audit`** (نفس محرك Site Health) |
| الصور | **Sharp** |
| الأسرار | **Gitleaks** |
| الثغرات (بوابة) | **composer audit + npm audit** |
| الأمن الثابت | **Semgrep CE** |
| التراخيص والإعدادات | **Trivy** (الخط الكامل) |
| الفحص الديناميكي | **OWASP ZAP** (Staging) |
| سجلات المتطلبات والقرارات | **`scripts/registers/build_master.py`** يولّد Master Requirements وDecision/Conflict Registers وPending Owner Input وTraceability وGap Analysis من `cons/G*.json` (طريقة الاستخدام في `scripts/registers/README.md`) |

## 3. تقسيم التشغيل (M37 §22)
| المستوى | الأوامر | متى |
|---|---|---|
| **FAST** | `composer lint` (Pint --test) · `composer analyse` (Larastan) · `composer test` (PHPUnit) · `npm run lint` · `npm run typecheck` · `npm run test:unit` | محليًا قبل كل Commit، وفي CI عند كل دفع |
| **CI** | البناء + ميزانية الحزمة · Playwright (Chromium) + axe · فحص الـHeaders · `seo:audit` · Gitleaks · Semgrep · composer/npm audit · بناء Storybook + a11y | كل دفع |
| **FULL** | Firefox + WebKit · Visual regression · Lighthouse CI · Trivy · Knip | يدويًا (`workflow_dispatch`) + قبل كل إصدار |
| **APP QA (محلي/Staging)** | `cd tooling && SHELTER_BASE_URL=http://127.0.0.1:8000 npx playwright test tests/app` — كل صفحة عامة AR/EN × 20 عرضًا: الحالة، lang/dir، H1 واحد، بلا تمدد، بلا أخطاء JS · axe على 390 و1440 · الروابط والأصول. للبحث محليًا: `SHELTER_SEARCH_PER_MINUTE` أعلى في `.env` المحلي فقط · `SHELTER_BASE_URL=… node scripts/lighthouse.mjs /ar/ /ar/jo/menu/` | قبل كل إصدار وبعد إنشاء Staging |
| **STAGING** | ZAP baseline (passive) · فحص ZAP نشط **بموافقة** · الزحف الكامل · `e2e-seo-assistant` | بعد إنشاء Staging |

**بوابات الإصدار الحرجة (تمنع الإصدار):**
- فشل أي اختبار، أو خطأ Larastan أو TS.
- مخالفة axe بدرجة serious أو critical.
- سر مكتشف (Gitleaks).
- ثغرة High أو Critical في الاعتماديات.
- نتيجة Semgrep مؤكدة بعد التحقق.
- تجاوز ميزانية الأداء.
- فرق بصري غير معتمد.

## 4. سياسة الحزم (M37 §29)
**قبل أي حزمة جديدة، تُجاب خمسة أسئلة ويُسجل الجواب هنا:**
1. هل يوجد مكافئ مثبت؟
2. هل هي مُصانة؟
3. هل حجمها مناسب؟
4. هل فيها ثغرات؟
5. هل يمكن تنفيذ الحاجة ببساطة بدونها؟

**قواعد الحزمة العامة للموقع:**
- **ممنوع** في الحزمة العامة: حركة، أو أيقونات كاملة، أو رسوم بيانية، أو محررات، أو مكونات Dashboard.
- يتم التحميل الكسول عند الحاجة.

## 5. ما يحتاج موافقتك (لم يُثبت)
| الأداة / الخدمة | السبب |
|---|---|
| تشغيل ZAP على Staging | يحتاج إنشاء تطبيق Staging على Cloudways (تفويض خارجي) |
| Context7 / Chrome DevTools MCP | ربط خدمات خارجية. **غير ضروري الآن**؛ البديل موثق أعلاه |
| تجاوز حصة GitHub Actions المجانية | قرار مالي (إن حصل) |
| أي SaaS (Percy، Chromatic، Sentry، Snyk…) | **غير مستخدمة.** البدائل المجانية أعلاه تكفي |

## 6. حالة التثبيت الفعلية (2026-10-01)
| الأداة | الحالة | التحقق |
|---|---|---|
| PHPUnit · Pint · Larastan (المستوى 8) | **INSTALLED + CONFIGURED** | `composer qa:fast` ✅ |
| TypeScript 6 (strict) · ESLint 10 · Prettier · Vitest 5 · Knip | **INSTALLED + CONFIGURED** | `npm run qa:fast` ✅ |
| Vite 8 + ميزانية الحزمة + حارس الهوية | **INSTALLED + CONFIGURED** | `npm run build` ✅ |
| Storybook 10 (`html-vite`) + addon-a11y + pseudo-states | **TESTED** | 143 قصة مولّدة من Blade (`npm run storybook:build`)، و`npm run test:storybook` يفحص axe والتمدد واتجاه اللغة |
| Gitleaks | **INSTALLED + CONFIGURED** (Hook محلي + CI) | تاريخ Git كله: لا تسريبات ✅ |
| Semgrep CE | **INSTALLED + CONFIGURED** (قواعد المشروع `.semgrep/` + حزم السجل في CI) | لا نتائج ✅ |
| composer audit · npm audit | **CONFIGURED** (بوابة في CI) | لا ثغرات ✅ |
| Trivy | **CONFIGURED** (الخط الكامل فقط) | يُبنى من المصدر بنسخة مثبتة |
| GitHub Actions | **CONFIGURED** (`.github/workflows/quality.yml`) | الـActions مثبتة برقم الـCommit |
| Lighthouse CI | يُضبط مع أول صفحات حقيقية (نهاية PHASE 1 / PHASE 2) | — |
| OWASP ZAP | يُضبط مع إنشاء Staging | — |

**قرارات تقنية أثناء التثبيت:**
- **TypeScript 6.0 وليس 7.0:** `typescript-eslint` يدعم حتى `<6.1`. النسخة 7 (Native) لا تعمل معه بعد.
- **WebAuthn:** `laragear/webauthn` **متروكة** (Abandoned) ويوصي مطوروها بـ`laravel/passkeys` الرسمية. لذلك أُزيلت. تُضاف `laravel/passkeys` عند تنفيذ مفاتيح الدخول. **الـTOTP إلزامي من البداية** (`pragmarx/google2fa`).
- **Trivy:** في مارس 2026 تعرّض لهجوم سلسلة توريد (CVE-2026-33634: إصدار خبيث v0.69.4، ووسوم `trivy-action` و`setup-trivy` مُستبدلة).
  - **الإجراءات المتبعة:**
    - لا `trivy-action`.
    - لا Tags متغيرة.
    - بناء من المصدر بنسخة مثبتة (v0.75.0) عبر Go checksum DB.
    - يعمل في الخط الكامل فقط، **بلا أي أسرار في تلك الخطوة**.
  - المصادر: [GHSA-69fq-xp46-6x23](https://github.com/aquasecurity/trivy/security/advisories/GHSA-69fq-xp46-6x23) · [Aqua](https://www.aquasec.com/blog/trivy-supply-chain-attack-what-you-need-to-know/).
- **Gitleaks وSemgrep في CI:** نسخ مثبتة (`gitleaks@v8.30.1` من المصدر، و`semgrep==1.178.0`)، **بلا Actions خارجية**.
- **شبكة بيئة التطوير:** `api.github.com` وروابط إصدارات GitHub محجوبة (403)، و`git` يعمل.
  - الحزم تُثبّت محليًا من المصدر (`--prefer-source`).
  - في CI تعمل كل الأدوات طبيعيًا.
