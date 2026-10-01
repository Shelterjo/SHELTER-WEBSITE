// Contact sheets of the franchise / partnership wireframes at 390px and 1440px → ../png/SHEET-*.png
// Run after build.py:  node sheets.cjs   (uses @playwright/test from tooling/node_modules — run `npm ci` in tooling/ first)
const path = require('path'), fs = require('fs');
const { chromium } = require(path.join(__dirname, '../../../../tooling/node_modules/@playwright/test'));
const H = 'file://' + path.join(__dirname, '../html/');
const P = path.join(__dirname, '../png/'), T = path.join(P, '_tmp/');

// [file, caption, scroll selector | null, fullPage]
const SECTIONS = l => {
  const t = (ar, en) => (l === 'ar' ? ar : en);
  return [
    [`${l}-franchise`, t('1 · Hero (الشاشة الأولى)', '1 · Hero (first screen)'), null], [`${l}-franchise`, t('2 · من هي SHELTER؟', '2 · Who is SHELTER?'), '#who'],
    [`${l}-franchise`, t('3 · نماذج التجربة الحالية', '3 · Today’s experiences'), '#experiences'], [`${l}-franchise`, t('4 · لماذا الشراكة (3 صفوف)', '4 · Why partner (3 rows)'), '#why'],
    [`${l}-franchise`, t('5 · أكثر من اسم + CTA الأوسط', '5 · More than a name + mid CTA'), '#system'], [`${l}-franchise`, t('6 · معايير الشريك', '6 · Who we look for'), '#criteria'],
    [`${l}-franchise`, t('7 · رحلة الشراكة + الزر الثابت', '7 · Journey + sticky CTA'), '#journey'], [`${l}-franchise`, t('8 · رحلة الدعم', '8 · Support journey'), '#support'],
    [`${l}-franchise`, t('9 · أسواق النمو + FAQ', '9 · Where we grow + FAQ'), '#markets'], [`${l}-franchise`, t('11 · طلب الشراكة (الزر الثابت مخفي)', '11 · Application (sticky hidden)'), '#apply'],
    [`${l}-franchise`, t('12–13 · CTA ختامي + Footer', '12–13 · Final CTA + footer'), '#final'], [`${l}-franchise`, t('ملاحظة: FAQ مخفية حتى التحقق', 'Annotation: FAQ hidden until verified'), 'aside'],
  ];
};
const SECTIONS_D = l => SECTIONS(l).filter(([, , s]) => [null, '#who', '#why', '#journey', '#markets', '#apply', '#final', 'aside'].includes(s));
const AB = l => [[`${l}-form-a`, `A · ${l.toUpperCase()} · full page`, null, true], ...[1, 2, 3, 4, 5].map(i => [`${l}-form-b${i > 1 ? '-s' + i : ''}`, `B · ${l.toUpperCase()} · step ${i}/5`, null, true])];
const STATES = [['ar-form-a-errors', 'A · أخطاء: ملخص role=alert', '.esum'], ['ar-form-b-errors', 'B · أخطاء الخطوة 1', '.prog'], ['ar-form-a-network', 'A · خطأ شبكة — المدخلات محفوظة', 'form'],
  ['ar-success', 'نجاح · FR-2026-00125 (SAMPLE)', null], ['en-form-a-errors', 'A · EN errors', '.esum'], ['en-success', 'EN success · SAMPLE number', null]];
const DASH = [['overview', 'نظرة عامة: بطاقات تشغيلية'], ['list', 'الطلبات: بحث + مراحل + فلاتر محفوظة + ترقيم'], ['list-filters', 'الفلاتر الخمسة + حفظ'], ['quickview', 'عرض سريع (لوحة جانبية)'],
  ['application', 'الطلب الكامل'], ['meetings', 'الاجتماعات'], ['archived', 'الأرشيف (استعادة، بلا حذف)'], ['settings', 'الإعدادات: المراحل + القنوات + النسخ']];

async function shoot(b, list, w, h, prefix) {
  const out = [];
  for (const [i, [f, cap, sel, full = false]] of list.entries()) {
    const p = await b.newPage({ viewport: { width: w, height: h }, deviceScaleFactor: 1 });
    await p.goto(H + f + '.html');
    if (sel) await p.evaluate(s => { const e = document.querySelector(s); window.scrollTo(0, e.getBoundingClientRect().top + scrollY - 8); }, sel);
    await p.waitForTimeout(150); // IntersectionObserver (sticky CTA) settles
    const fn = `${prefix}-${String(i).padStart(2, '0')}.png`;
    await p.screenshot({ path: T + fn, fullPage: full }); await p.close();
    out.push([fn, cap]);
  }
  return out;
}

async function sheet(b, name, title, shots, colW, per) {
  const figs = shots.map(([f, c]) => `<figure><img src="${f}"><figcaption>${c}</figcaption></figure>`).join('');
  const html = `<html dir="rtl" lang="ar"><body style="margin:0;padding:24px;background:#f0f0f0;font-family:DejaVu Sans,sans-serif">`
    + `<h1 style="margin:0 0 4px;font-size:22px">SHELTER COFFEE — Franchise &amp; Partnerships · Low-Fi Wireframes · ${title}</h1>`
    + `<p style="margin:0 0 16px;color:#444;font-size:14px">LOW-FI — ليست هوية بصرية (M-10) · نظام تصميم واحد tokens.css + wireframe-kit.css (M34) · كل البيانات تجريبية · M29 المخرجات 8–11</p>`
    + `<div style="display:grid;grid-template-columns:repeat(${per},${colW}px);gap:20px;align-items:start">${figs}</div>`
    + `<style>figure{margin:0;background:#fff;padding:8px;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.15)}img{width:100%;display:block;border:1px solid #ddd}figcaption{font-size:14px;padding:6px 2px 0;color:#111}</style></body></html>`;
  const fn = T + `_${name}.html`; fs.writeFileSync(fn, html);
  const p = await b.newPage({ viewport: { width: per * (colW + 20) + 48, height: 800 } });
  await p.goto('file://' + fn); await p.waitForLoadState('load');
  await p.screenshot({ path: P + `SHEET-${name}.png`, fullPage: true }); await p.close();
}

(async () => {
  fs.rmSync(T, { recursive: true, force: true }); fs.mkdirSync(T, { recursive: true });
  const b = await chromium.launch();
  await sheet(b, 'page-390', 'Franchise page · 390px · AR (row 1–2) + EN (row 3–4)',
    [...await shoot(b, SECTIONS('ar'), 390, 844, 'pa'), ...await shoot(b, SECTIONS('en'), 390, 844, 'pe')], 260, 6);
  await sheet(b, 'page-1440', 'Franchise page · 1440px · AR (rows 1–2) + EN (rows 3–4)',
    [...await shoot(b, SECTIONS_D('ar'), 1440, 900, 'da'), ...await shoot(b, SECTIONS_D('en'), 1440, 900, 'de')], 560, 4);
  await sheet(b, 'form-A-vs-B-390', 'A (صفحة واحدة) مقابل B (5 خطوات) · صفحة كاملة · 390px · AR ثم EN',
    [...await shoot(b, [...AB('ar'), ...AB('en')], 390, 844, 'ab'), ...await shoot(b, STATES, 390, 844, 'st')], 260, 6);
  await sheet(b, 'form-A-vs-B-1440', 'A vs B · 1440px · AR + EN', await shoot(b, [['ar-form-a', 'A · AR · full page', null, true], ['ar-form-b', 'B · AR · step 1/5', null, true],
    ['ar-form-b-s5', 'B · AR · review & submit', null, true], ['ar-success', 'AR · success (SAMPLE)', null], ['en-form-a', 'A · EN · full page', null, true], ['en-form-b', 'B · EN · step 1/5', null, true],
    ['en-form-b-s5', 'B · EN · review & submit', null, true], ['en-success', 'EN · success (SAMPLE)', null]], 1440, 900, 'abd'), 480, 4);
  await sheet(b, 'dashboard-1440', 'Owner Dashboard · الشراكات · Desktop 1440px', await shoot(b, DASH.map(([f, c]) => [`d-${f}`, c, null]), 1440, 900, 'd'), 620, 3);
  await sheet(b, 'dashboard-390', 'Owner Dashboard · الشراكات · Mobile 390px', await shoot(b, DASH.map(([f, c]) => [`m-${f}`, c, null]), 390, 844, 'm'), 300, 4);
  await b.close();
  fs.rmSync(T, { recursive: true, force: true });
  console.log('sheets →', P);
})();
