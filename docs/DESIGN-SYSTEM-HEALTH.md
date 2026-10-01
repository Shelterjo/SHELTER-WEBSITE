# DESIGN SYSTEM HEALTH

> **مولّد** بـ`npm run ds:audit` (داخل `tooling/`) من `design-system/tokens/tokens.json`. لا يُعدّل يدويًا.
> **النطاق اليوم:** واجهات المشروع الموجودة فعلًا = الـWireframes منخفضة الدقة. لا يوجد كود موقع بعد.
> **عند البناء:** نفس الفحص يُطبّق على الكود عبر قاعدة Lint تمنع القيم الخام (Hex، px خارج الـTokens) إلا باستثناء موثق.

## الملخص
| المجموعة | الصفحات | أحجام خط خارج الـTokens | Radius خارج الـTokens | مسافات خارج الـScale | ألوان خارج اللوحة | أنماط ظلال | عائلات خطوط | أسماء أزرار مختلفة |
|---|---|---|---|---|---|---|---|---|
| menu | 30 | 0 | 0 | 0 | 4 | 3 | 2 | 10 |
| careers | 44 | 0 | 0 | 0 | 4 | 3 | 2 | 10 |
| franchise | 41 | 0 | 0 | 0 | 4 | 3 | 2 | 10 |
| dashboard | 54 | 0 | 0 | 0 | 4 | 3 | 2 | 10 |

## التفاصيل (أكثر 12 قيمة مخالفة لكل نوع)

### menu
- **أحجام الخط (px):** ✅ لا مخالفات
- **Radius (px):** ✅ لا مخالفات
- **مسافات (px):** ✅ لا مخالفات
- **ألوان:** `rgb(0 0 0 / 0.06)`×30 · `rgb(0 0 0 / 0.08)`×30 · `rgb(0 0 0 / 0.12)`×30 · `rgb(0 0 0 / .5)`×30
- **الظلال:** `var(--shadow-none)` · `var(--shadow-sm)` · `var(--shadow-lg)`
- **عائلات الخطوط:** `var(--font-ar)` · `var(--font-en)`
- **أصناف الأزرار:** `.ds-btn` · `.ds-btn--primary` · `.ds-btn--secondary` · `.ds-btn--outline` · `.ds-btn--ghost` · `.ds-btn--danger` · `.ds-btn--link` · `.ds-btn--icon` · `.ds-btn--sm` · `.ds-btn--lg`

### careers
- **أحجام الخط (px):** ✅ لا مخالفات
- **Radius (px):** ✅ لا مخالفات
- **مسافات (px):** ✅ لا مخالفات
- **ألوان:** `rgb(0 0 0 / 0.06)`×44 · `rgb(0 0 0 / 0.08)`×44 · `rgb(0 0 0 / 0.12)`×44 · `rgb(0 0 0 / .5)`×44
- **الظلال:** `var(--shadow-none)` · `var(--shadow-sm)` · `var(--shadow-lg)`
- **عائلات الخطوط:** `var(--font-ar)` · `var(--font-en)`
- **أصناف الأزرار:** `.ds-btn` · `.ds-btn--primary` · `.ds-btn--secondary` · `.ds-btn--outline` · `.ds-btn--ghost` · `.ds-btn--danger` · `.ds-btn--link` · `.ds-btn--icon` · `.ds-btn--sm` · `.ds-btn--lg`

### franchise
- **أحجام الخط (px):** ✅ لا مخالفات
- **Radius (px):** ✅ لا مخالفات
- **مسافات (px):** ✅ لا مخالفات
- **ألوان:** `rgb(0 0 0 / 0.06)`×41 · `rgb(0 0 0 / 0.08)`×41 · `rgb(0 0 0 / 0.12)`×41 · `rgb(0 0 0 / .5)`×41
- **الظلال:** `var(--shadow-none)` · `var(--shadow-sm)` · `var(--shadow-lg)`
- **عائلات الخطوط:** `var(--font-ar)` · `var(--font-en)`
- **أصناف الأزرار:** `.ds-btn` · `.ds-btn--primary` · `.ds-btn--secondary` · `.ds-btn--outline` · `.ds-btn--ghost` · `.ds-btn--danger` · `.ds-btn--link` · `.ds-btn--icon` · `.ds-btn--sm` · `.ds-btn--lg`

### dashboard
- **أحجام الخط (px):** ✅ لا مخالفات
- **Radius (px):** ✅ لا مخالفات
- **مسافات (px):** ✅ لا مخالفات
- **ألوان:** `rgb(0 0 0 / 0.06)`×54 · `rgb(0 0 0 / 0.08)`×54 · `rgb(0 0 0 / 0.12)`×54 · `rgb(0 0 0 / .5)`×54
- **الظلال:** `var(--shadow-none)` · `var(--shadow-sm)` · `var(--shadow-lg)`
- **عائلات الخطوط:** `var(--font-ar)` · `var(--font-en)`
- **أصناف الأزرار:** `.ds-btn` · `.ds-btn--primary` · `.ds-btn--secondary` · `.ds-btn--outline` · `.ds-btn--ghost` · `.ds-btn--danger` · `.ds-btn--link` · `.ds-btn--icon` · `.ds-btn--sm` · `.ds-btn--lg`

## السجل (Component Health)
| الفئة | الحالة اليوم |
|---|---|
| **Approved Components** | لا يوجد بعد. المكونات تُعتمد في مرحلة الـDesign System (P05) وStorybook (بعد DB-08). القائمة المستهدفة في `docs/DESIGN-SYSTEM-STANDARD.md` §8 |
| **Duplicate Components** | كل مجموعة Wireframes تعرّف أزرارها وبطاقاتها وحقولها بنفسها (أعلاه). **مقبول مؤقتًا للنماذج منخفضة الدقة، ويُوحّد عبر `design-system/build/tokens.css` + `wireframe-kit.css`** |
| **Deprecated Components** | — |
| **Unused Variants** | — |
| **Token Violations** | الأعمدة أعلاه |
