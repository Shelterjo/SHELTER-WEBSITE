# ADR-001 — منصة البناء (يحسم DB-08 / PO-006)

| البند | القيمة |
|---|---|
| **الحالة** | `ACCEPTED — TECHNICAL DECISION UNDER OWNER DELEGATION` |
| **التاريخ** | 2026-10-01 |
| **السلطة** | M36 §24: "لا توقف العمل لتسألني أسئلة تقنية… اتخذ القرار التقني الأفضل". وM36 §16: البنية التحتية ضمن Cloudways المعتمدة. |
| **يحل محل** | DB-08 `PROPOSED`، والشق التقني من PO-006 وPO-044 |
| **ما يبقى للـOwner** | **إنشاء تطبيقَي Cloudways** (Staging وProduction) أو منح صلاحية لإنشائهما (External authorization). أي **ترقية سيرفر مدفوعة** (قرار مالي). |

## القرار

| الطبقة | الاختيار |
|---|---|
| **الإطار** | **Laravel 13** (PHP 8.3+) |
| **الاستضافة** | **Cloudways Flexible**: تطبيق **جديد ومستقل** على السيرفر الحالي. **تطبيق WordPress القديم وقاعدته لا يُلمسان**، ويبقيان حتى الانتقال عند الإطلاق |
| **قاعدة البيانات** | **MySQL / MariaDB** على Cloudways. التطوير والاختبار الآلي: SQLite داخل حاوية التطوير و**الـCI فقط**. **لا قاعدة على جهاز الـOwner** |
| **الواجهة العامة** | **Blade** بعرض كامل من الخادم (HTML جاهز لمحركات البحث)، وJS خفيف تدريجي عند الحاجة فقط |
| **الـDashboard** | **Blade** نفسه، داخل نفس التطبيق، و**بنفس مكتبة المكونات**. لا Livewire ولا Filament ولا SPA في V1 |
| **التصميم** | `design-system/tokens/tokens.json` ← CSS variables، ومكونات Blade مشتركة `x-ui.*` (Website + Dashboard). **لا Tailwind** |
| **البناء** | **Vite** (افتراضي Laravel) للـCSS/JS، بأسماء ملفات بالـHash |
| **الأيقونات** | **Lucide** (SVG ثابتة، مكتبة واحدة) |
| **الحركة** | CSS transitions بالـTokens. **Motion** (vanilla) فقط حيث قرار M24 (Sheet المنيو)، **وبتحميل كسول** |
| **الملفات الخاصة** | خارج الجذر العام: `storage/app/private`، وعلى Cloudways داخل `private_html`، **لا رابط عام** |
| **المهام والجدولة** | Laravel Scheduler + Queue (`database` driver افتراضيًا، Redis إن توفر على السيرفر) |
| **الـCDN** | Cloudflare أمام التطبيق، مع إبطال انتقائي (`docs/platform/CACHE-CDN.md`) |
| **الاختبار والجودة** | السجل الوحيد: [`docs/TOOLCHAIN.md`](../TOOLCHAIN.md) (PHPUnit · Larastan · Pint · TypeScript strict · ESLint · Prettier · Vitest · Storybook · Playwright + axe · Lighthouse CI · Semgrep · Gitleaks · ZAP على Staging) |

## لماذا (مقابل البدائل)

| البديل | السبب |
|---|---|
| **A — Laravel على Flexible** ✅ | - **مدعوم أصلًا على Cloudways Flexible** (PHP + MySQL + Cron + `private_html` + Redis).<br>- **لا اشتراك جديد:** تطبيق إضافي على نفس السيرفر.<br>- **كل المطلوب جاهز ومجرب في الإطار:** صلاحيات من الخادم (Policies/Gates)، وتشفير، وMigrations قابلة للتراجع، وطوابير، وجدولة، وRate limiting، وتحقق من المدخلات.<br>- **HTML من الخادم** يعطي أفضل SEO/AEO وأداء ممتاز خلف Cloudflare.<br>- **أقل تعقيد** لمالك واحد (M36 §23). |
| B — Next.js على Cloudways Velocity | - قدرات القاعدة والتخزين الخاص وSSH غير متحقق منها.<br>- غالبًا منتج وسعر منفصلان (قرار مالي).<br>- يضيف فصلًا بين الواجهة والخادم بلا قيمة للمالك. |
| C — WordPress جديد | - لوحة WP لا تحقق Design lock، ولا Master Data Hub، ولا Publish Guard، ولا اللوحة الموحدة دون إعادة بناء أغلبها كإضافات.<br>- سطح أمني أوسع (إضافات). |
| D — خدمة خارجية (Supabase وغيرها) للبيانات | **مرفوض:** M28 يشترط Cloudways، ولا خدمة مدفوعة دون موافقة. |

## الأدوات المؤجلة في `FRONTEND-TOOLING.md` بعد هذا القرار

| الأداة | المصير | البديل المعتمد |
|---|---|---|
| shadcn/ui · Radix · React Aria | **NOT APPLICABLE** (تفترض React) | عناصر HTML أصلية بسلوك صحيح: `<dialog>`، و`popover`، و`details`. أنماط ARIA APG، **مختبرة بـaxe ولوحة المفاتيح** |
| Storybook | **مُعتمد (M37)** عبر `@storybook/html-vite` | **القصص تُولّد من مكونات Blade نفسها** (لا نسخة ثانية من المكونات) + addon-a11y + Visual regression بـPlaywright. **يحل محل** معرض المكونات الداخلي (Route في التطبيق) المقترح سابقًا — انظر `docs/TOOLCHAIN.md` |
| Tailwind | **REJECTED** | CSS مكونات + Tokens (يمنع One-off styling ويحقق "الـCMS لا يكسر التصميم") |
| Motion (`motion/react`) | **يصبح** `motion` vanilla | للـSheet فقط، بتحميل كسول |
| Lucide (`lucide-react`) | **يصبح** SVG ثابتة | Sprite/مكوّن Blade واحد |
| Lenis | يبقى غير مستخدم | التمرير الأصلي |

## العواقب

- **صورة واحدة للكود** تخدم Website وDashboard وAPI الداخلي.
- **الانتقال للإنتاج** يكون بتحويل الدومين للتطبيق الجديد عند الإطلاق، بموافقة الـOwner (P11)، مع بقاء التطبيق القديم للتراجع.
- **قابلية النقل:** Laravel وMySQL يعملان على أي استضافة PHP. البيانات قابلة للتصدير بمخططات موثقة (`docs/platform/DATA-PORTABILITY.md`).
- **إن ظهر أثناء الـCloudways audit** أن السيرفر لا يتحمل تطبيقين إضافيين: يُعرض على الـOwner خيار الترقية كقرار مالي، **ولا يُنفذ قبل موافقته**.
