// Design System consistency audit (M34 §24–§25): scans every UI artifact in the repo (today: low-fi wireframes)
// and reports values that are not design tokens (font sizes, radii, spacing, colours, shadows, font families),
// plus duplicated component styles. Output: docs/DESIGN-SYSTEM-HEALTH.md
import { readFile, writeFile, readdir } from 'node:fs/promises';
import { join } from 'node:path';

const t = JSON.parse(await readFile('../design-system/tokens/tokens.json', 'utf8'));
const px = s => Math.round(parseFloat(s) * (/(r?em)$/.test(s) ? 16 : 1) * 100) / 100;
const ALLOWED = {
  fontSize: new Set([12, 14, 16, 18, 20, 24, 26, 28, 30, 32, 36, 48]),
  radius: new Set([0, 6, 10, 14, 20, 999]),
  space: new Set([0, 4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80]),
  color: new Set(Object.entries(t.color).filter(([k]) => !k.startsWith('$')).map(([, x]) => x.wireframe.$value.toLowerCase()).concat(['#fff', '#000', 'transparent', 'inherit', 'currentcolor'])),
};
const SETS = { menu: '../docs/menu-ia/wireframes/html', careers: '../docs/careers/wireframes/html', franchise: '../docs/franchise/wireframes/html', dashboard: '../docs/dashboard/wireframes/html' };
const report = {};
for (const [set, dir] of Object.entries(SETS)) {
  let files = []; try { files = (await readdir(dir)).filter(f => f.endsWith('.html')); } catch { continue; }
  const r = { files: files.length, fontSize: new Map(), radius: new Map(), space: new Map(), color: new Map(), shadow: new Map(), family: new Map(), buttons: new Set() };
  const bump = (m, k) => m.set(k, (m.get(k) || 0) + 1);
  for (const f of files) {
    const html = await readFile(join(dir, f), 'utf8');
    const css = [...html.matchAll(/<style[^>]*>([\s\S]*?)<\/style>/g)].map(m => m[1]).join('\n') + '\n' + [...html.matchAll(/style="([^"]*)"/g)].map(m => m[1]).join(';');
    for (const m of css.matchAll(/font-size:\s*([\d.]+(?:px|rem|em))/g)) bump(r.fontSize, px(m[1]));
    for (const m of css.matchAll(/border-radius:\s*([^;}]+)/g)) for (const v of m[1].trim().split(/\s+/)) if (/^[\d.]+(px|rem)$/.test(v)) bump(r.radius, px(v)); else if (v === '50%') bump(r.radius, '50%');
    for (const m of css.matchAll(/(?:^|[;{\s])(?:padding|margin|gap|row-gap|column-gap)(?:-[a-z-]+)?:\s*([^;}]+)/g)) for (const v of m[1].trim().split(/\s+/)) if (/^-?[\d.]+px$/.test(v)) bump(r.space, Math.abs(px(v)));
    for (const m of css.matchAll(/#[0-9a-fA-F]{3,8}\b|rgba?\([^)]*\)/g)) bump(r.color, m[0].toLowerCase());
    for (const m of css.matchAll(/box-shadow:\s*([^;}]+)/g)) bump(r.shadow, m[1].trim());
    for (const m of css.matchAll(/font-family:\s*([^;}]+)/g)) bump(r.family, m[1].trim());
    for (const m of css.matchAll(/\.([a-z][\w-]*(?:btn|button|cta)[\w-]*)\s*[{,]/gi)) r.buttons.add(m[1]);
  }
  report[set] = r;
}
const viol = (m, allow, f = x => x) => [...m].filter(([k]) => !allow.has(f(k)) && k !== '50%').sort((a, b) => b[1] - a[1]);
let md = `# DESIGN SYSTEM HEALTH

> **مولّد** بـ\`npm run ds:audit\` (داخل \`tooling/\`) من \`design-system/tokens/tokens.json\`. لا يُعدّل يدويًا.
> **النطاق اليوم:** واجهات المشروع الموجودة فعلًا = الـWireframes منخفضة الدقة. لا يوجد كود موقع بعد.
> **عند البناء:** نفس الفحص يُطبّق على الكود عبر قاعدة Lint تمنع القيم الخام (Hex، px خارج الـTokens) إلا باستثناء موثق.

## الملخص
| المجموعة | الصفحات | أحجام خط خارج الـTokens | Radius خارج الـTokens | مسافات خارج الـScale | ألوان خارج اللوحة | أنماط ظلال | عائلات خطوط | أسماء أزرار مختلفة |
|---|---|---|---|---|---|---|---|---|
`;
for (const [set, r] of Object.entries(report)) {
  md += `| ${set} | ${r.files} | ${viol(r.fontSize, ALLOWED.fontSize).length} | ${viol(r.radius, ALLOWED.radius).length} | ${viol(r.space, ALLOWED.space).length} | ${viol(r.color, ALLOWED.color).length} | ${r.shadow.size} | ${r.family.size} | ${r.buttons.size} |\n`;
}
md += `\n## التفاصيل (أكثر 12 قيمة مخالفة لكل نوع)\n`;
for (const [set, r] of Object.entries(report)) {
  const show = (title, arr) => `- **${title}:** ${arr.length ? arr.slice(0, 12).map(([k, n]) => `\`${k}\`×${n}`).join(' · ') : '✅ لا مخالفات'}\n`;
  md += `\n### ${set}\n` + show('أحجام الخط (px)', viol(r.fontSize, ALLOWED.fontSize)) + show('Radius (px)', viol(r.radius, ALLOWED.radius))
    + show('مسافات (px)', viol(r.space, ALLOWED.space)) + show('ألوان', viol(r.color, ALLOWED.color))
    + `- **الظلال:** ${[...r.shadow.keys()].map(s => `\`${s}\``).join(' · ') || '—'}\n- **عائلات الخطوط:** ${[...r.family.keys()].map(s => `\`${s}\``).join(' · ') || '—'}\n- **أصناف الأزرار:** ${[...r.buttons].map(s => `\`.${s}\``).join(' · ') || '—'}\n`;
}
md += `
## السجل (Component Health)
| الفئة | الحالة اليوم |
|---|---|
| **Approved Components** | لا يوجد بعد. المكونات تُعتمد في مرحلة الـDesign System (P05) وStorybook (بعد DB-08). القائمة المستهدفة في \`docs/DESIGN-SYSTEM-STANDARD.md\` §8 |
| **Duplicate Components** | كل مجموعة Wireframes تعرّف أزرارها وبطاقاتها وحقولها بنفسها (أعلاه). **مقبول مؤقتًا للنماذج منخفضة الدقة، ويُوحّد عبر \`design-system/build/tokens.css\` + \`wireframe-kit.css\`** |
| **Deprecated Components** | — |
| **Unused Variants** | — |
| **Token Violations** | الأعمدة أعلاه |
`;
await writeFile('../docs/DESIGN-SYSTEM-HEALTH.md', md);
console.log(Object.fromEntries(Object.entries(report).map(([k, r]) => [k, { files: r.files, fs: viol(r.fontSize, ALLOWED.fontSize).length, rad: viol(r.radius, ALLOWED.radius).length, sp: viol(r.space, ALLOWED.space).length, col: viol(r.color, ALLOWED.color).length, btn: r.buttons.size }])));
