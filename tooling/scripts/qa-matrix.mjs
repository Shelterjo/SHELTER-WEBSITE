// Builds docs/qa/RESPONSIVE-QA-MATRIX.md from the latest Playwright JSON results (reports/results.json).
// PASS = every automated check for that page × viewport × language passed · FAIL = at least one failed (screenshot in reports/test-results)
// NEEDS REVIEW = automated checks passed but manual checks (real devices, Safari/Firefox, screen readers) are still due · — = page not built yet.
import { readFile, writeFile, mkdir } from 'node:fs/promises';
import { VIEWPORTS } from '../viewports.mjs';

const r = JSON.parse(await readFile('reports/results.json', 'utf8'));
const cell = {}; // `${project}|${lang}` → {pass, fail}
const walk = (s, langCtx) => {
  const lang = /— AR/.test(s.title) ? 'ar' : /— EN/.test(s.title) ? 'en' : langCtx;
  for (const sp of s.specs || []) for (const t of sp.tests) {
    if (!lang || t.status === 'skipped') continue;
    const k = `${t.projectName}|${lang}`; cell[k] ??= { pass: 0, fail: [] };
    if (t.status === 'expected') cell[k].pass++; else cell[k].fail.push(sp.title);
  }
  for (const c of s.suites || []) walk(c, lang);
};
r.suites.forEach(s => walk(s));

const PAGES = ['Home', 'Menu', 'Locations', 'Branch', 'About', 'Contact', 'Blog', 'Article', 'Campaign/Event', 'Owner Dashboard', 'Menu Editor', 'Product Editor', 'Analytics', 'Site Health'];
const status = (vp, lang) => { const c = cell[`chromium-${vp}|${lang}`]; if (!c) return 'NOT RUN'; return c.fail.length ? `**FAIL** (${c.fail.length})` : 'PASS ⁽ᵃ⁾'; };
let md = `# RESPONSIVE QA MATRIX

> **مولّد آليًا** من \`tooling/reports/results.json\` بالأمر \`npm run qa:matrix\` (داخل \`tooling/\`). لا تعدّله يدويًا.
> **آخر تشغيل:** ${r.stats.startTime} · Chromium فقط · ${r.stats.expected} نجح · ${r.stats.unexpected} فشل · ${r.stats.skipped} متخطى (سيناريوهات التطبيق المعلقة + فحوص لا تنطبق على هذا العرض).
> **القاعدة (إلزامية):** لا تُعتبر أي صفحة DONE قبل:
> - QA على الموبايل والتابلت والديسكتوب.
> - RTL وLTR.
> - Touch وKeyboard.
> - تكبير 200%.
> - الاتجاهين (Portrait/Landscape).
> - Screenshot comparison.
>
> **أي Overflow أو Clipping أو Overlap = Bug.**

## الرموز
| الرمز | المعنى |
|---|---|
| \`PASS ⁽ᵃ⁾\` | كل الفحوص الآلية نجحت. **⁽ᵃ⁾ = يبقى الفحص اليدوي:** أجهزة حقيقية، Safari/WebKit، Firefox، قارئ شاشة. **لذلك ليست DONE** |
| \`FAIL (n)\` | n فحص فشل. الصورة والـTrace في \`tooling/reports/test-results/\` |
| \`NEEDS REVIEW\` | يحتاج حكمًا بشريًا |
| \`—\` | الصفحة غير مبنية بعد |

**ما يُفحص آليًا لكل خلية:**
1. اللغة والاتجاه.
2. ترتيب العناوين.
3. لا تجاوز أفقي.
4. السعر في سطر واحد.
5. الاسم ≤ 4 أسطر.
6. لا قص للنص.
7. نص الواجهة ≥ 12px.
8. التكبير غير ممنوع.
9. الـAnchor لا يختفي تحت الأشرطة اللاصقة.
10. الأشرطة اللاصقة ≤ 30% من الارتفاع.
11. الـSheet/Modal داخل الشاشة، وزر الإغلاق ≥ 44px.
12. أهداف اللمس ≥ 44px.
13. حلقة تركيز مرئية.
14. عرض المحتوى ≤ 1280px على الشاشات العريضة.
15. axe WCAG 2.2 AA (لا Serious ولا Critical).

## المصفوفة
**Menu** = الـWireframes منخفضة الدقة (نموذج، وليس الموقع). كل الصفحات الأخرى غير مبنية.

| Viewport | المجموعة | ${PAGES.map(p => p === 'Menu' ? 'Menu AR | Menu EN' : p).join(' | ')} |
|---|---|${PAGES.map(p => p === 'Menu' ? '---|---' : '---').join('|')}|
`;
for (const [name, w, h, , group] of VIEWPORTS) {
  md += `| ${name} (${w}×${h}) | ${group} | ${PAGES.map(p => p === 'Menu' ? `${status(name, 'ar')} | ${status(name, 'en')}` : '—').join(' | ')} |\n`;
}
const fails = Object.entries(cell).filter(([, c]) => c.fail.length);
md += `\n## الإخفاقات المفتوحة\n${fails.length ? fails.map(([k, c]) => `- \`${k}\`: ${[...new Set(c.fail)].join('، ')}`).join('\n') : '- لا يوجد في آخر تشغيل.'}\n
## إصلاحات تمت (2026-10-01)
أول تشغيل على الـ20 عرضًا كشف **120 إخفاقًا**. أُصلحت في مولّد الـWireframes (\`docs/menu-ia/wireframes/tools/wf.py\`):

| الإخفاق | قبل | بعد | نطاقه |
|---|---|---|---|
| تباين نص عدد الأصناف | 4.47:1 (\`#777\`) | 5.74:1 (\`#666\`) | كل العروض، باللغتين |
| شارة "موسمي" | 11px | 12px | |
| حلقة تركيز الكيبورد | الافتراضية 1px | 3px (\`:focus-visible\`) | |
| الأشرطة اللاصقة على 568×320 (أفقي) | 35% من الارتفاع | 17.5% (R-09: شريط الفئات وحده لاصق عند ارتفاع ≤ 500px) | الموبايل الأفقي |

## ما لم يُفحص بعد (مطلوب قبل الاعتماد النهائي)
- **Safari/WebKit وFirefox:** غير مثبتين في هذه البيئة. تُشغّل في CI بـ\`SHELTER_ALL_BROWSERS=1\`.
- **أجهزة حقيقية:** iPhone (Safari، Safe Area، لوحة المفاتيح فوق الحقول) وAndroid (Chrome).
- **قراءة الشاشة يدويًا:** VoiceOver وTalkBack.
- **مقارنة الصور:** \`@visual\` معطلة حتى اعتماد التصميم المرئي.
- **التكبير الحقيقي:** فحص الـ200% هنا مكافئ عرض (640×400 @2x). يُكمّل بتكبير متصفح حقيقي.
- **الـDashboard والصفحات الأخرى:** غير مبنية. تُضاف أعمدتها للاختبار عند بنائها.
`;
await mkdir('../docs/qa', { recursive: true });
await writeFile('../docs/qa/RESPONSIVE-QA-MATRIX.md', md);
console.log(`matrix written · ${fails.length} failing cells`);
