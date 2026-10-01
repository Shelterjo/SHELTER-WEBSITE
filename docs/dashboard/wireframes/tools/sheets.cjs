// Contact sheets of the Owner Dashboard wireframes (390px mobile and 1440px desktop) → ../png/SHEET-*.png
// Run after build.py:  node sheets.cjs   (uses @playwright/test from tooling/node_modules — run `npm ci` in tooling/ first)
const path = require('path'), fs = require('fs');
const { chromium } = require(path.join(__dirname, '../../../../tooling/node_modules/@playwright/test'));
const H = 'file://' + path.join(__dirname, '../html/');
const P = path.join(__dirname, '../png/'), T = path.join(P, '_tmp/');

// [screen key, caption] — numbering = README screen list
const SCREENS = [
  ['home', '1 · مركز القيادة'], ['analytics', '2 · التحليلات'], ['menu', '3 · المنيو'], ['product', '4 · محرر الصنف'],
  ['impact', '5 · أثر التغيير'], ['guard', '6 · فحص النشر'], ['branch', '7 · الفروع والساعات'], ['active', '8 · النشط الآن'],
  ['calendar', '9 · التقويم'], ['experience', '10 · محرر التجربة'], ['family', '11 · الموظف المثالي + Family'], ['media', '12 · مركز الوسائط'],
  ['global-data', '13 · البيانات العامة'], ['facts', '14 · سجل الحقائق'], ['parity', '15 · تطابق اللغتين'], ['health', '16 · صحة الموقع'],
  ['performance', '17 · الأداء الفعلي'], ['a11y', '18 · الوصولية'], ['security', '19 · الأمان'], ['privacy', '20 · الخصوصية'],
  ['seo', '21 · SEO + البحث + الفرص'], ['reputation', '22 · السمعة'], ['feedback', '23 · آراء العملاء'], ['audit', '24 · سجل التدقيق'],
  ['safe-mode', '25 · وضع الأمان'],
];

async function shoot(b, list, w, h, prefix) {
  const out = [];
  for (const [i, [f, cap]] of list.entries()) {
    const p = await b.newPage({ viewport: { width: w, height: h }, deviceScaleFactor: 1 });
    await p.goto(H + f + '.html');
    const fn = `${prefix}-${String(i).padStart(2, '0')}.png`;
    await p.screenshot({ path: T + fn }); await p.close();
    out.push([fn, cap]);
  }
  return out;
}

async function sheet(b, name, title, shots, colW, per) {
  const figs = shots.map(([f, c]) => `<figure><img src="${f}"><figcaption>${c}</figcaption></figure>`).join('');
  const html = `<html dir="rtl" lang="ar"><body style="margin:0;padding:24px;background:#f7f7f7;font-family:DejaVu Sans,sans-serif">`
    + `<h1 style="margin:0 0 4px;font-size:24px">SHELTER COFFEE — Owner Dashboard · Low-Fi Wireframes · ${title}</h1>`
    + `<p style="margin:0 0 16px;color:#595959;font-size:14px">LOW-FI — design-system tokens + wireframe kit (M34) · ليست هوية بصرية (M-10) · كل الأرقام DEMO DATA · P04 · M25 المرحلة F + M32</p>`
    + `<div style="display:grid;grid-template-columns:repeat(${per},${colW}px);gap:20px;align-items:start">${figs}</div>`
    + `<style>figure{margin:0;background:#fff;padding:8px;border-radius:10px;border:1px solid #cccccc}img{width:100%;display:block;border:1px solid #cccccc}figcaption{font-size:14px;padding:6px 2px 0;color:#1a1a1a}</style></body></html>`;
  const fn = T + `_${name}.html`; fs.writeFileSync(fn, html);
  const p = await b.newPage({ viewport: { width: per * (colW + 20) + 48, height: 800 } });
  await p.goto('file://' + fn); await p.waitForLoadState('load');
  await p.screenshot({ path: P + `SHEET-${name}.png`, fullPage: true }); await p.close();
}

(async () => {
  fs.rmSync(T, { recursive: true, force: true }); fs.mkdirSync(T, { recursive: true });
  const b = await chromium.launch();
  await sheet(b, 'dashboard-1440', 'Desktop 1440px (sidebar · 7 groups)', await shoot(b, SCREENS.map(([f, c]) => [`d-${f}`, c]), 1440, 900, 'd'), 560, 5);
  await sheet(b, 'dashboard-390', 'Mobile 390px (bottom nav · 5 items)', await shoot(b, [...SCREENS, ['more', 'ورقة «المزيد»']].map(([f, c]) => [`m-${f}`, c]), 390, 844, 'm'), 280, 7);
  await b.close();
  fs.rmSync(T, { recursive: true, force: true });
  console.log('sheets →', P);
})();
