// Contact sheets of the key careers wireframe screens (390px and 1440px) → ../png/SHEET-*.png
// Run after build.py:  node sheets.cjs   (uses @playwright/test from tooling/node_modules — run `npm ci` in tooling/ first)
const path = require('path'), fs = require('fs');
const { chromium } = require(path.join(__dirname, '../../../../tooling/node_modules/@playwright/test'));
const H = 'file://' + path.join(__dirname, '../html/');
const P = path.join(__dirname, '../png/'), T = path.join(P, '_tmp/');

// [file, caption, scroll selector | null, offset, fullPage]
const PUB = [
  ['form-a', 'A · أعلى النموذج', null], ['form-a', 'A · المؤهل والخبرة + معلومات العمل', '#g-edu', -8], ['form-a', 'A · المرفقات + الإقرار + إرسال', '#g-files', -8],
  ['form-a-errors', 'A · ملخص الأخطاء (role=alert)', '.esum', -8], ['form-a-errors', 'A · أخطاء داخلية: نص + أيقونة + إطار', '[data-f=dob]', -8],
  ['form-a-jo', 'أردني ← الرقم الوطني (مطلوب)', '[data-f=nat]', -8], ['form-a-nonjo', 'غير أردني ← الجنسية + رقم الوثيقة', '[data-f=nat]', -8],
  ['form-a-upload', '3 ملفات + السيرة الذاتية تلقائيًا', '#files-wrap', -8], ['form-a-cv-unknown', 'لم يُتعرّف على السيرة الذاتية', '#files-wrap', -8],
  ['form-a-file-errors', 'ملف خطر مرفوض + ملف كبير', '#files-wrap', -8], ['form-a-network', 'خطأ شبكة — المدخلات محفوظة', 'form', -8],
  ['form-b', 'B · الخطوة 1 من 4', '.prog', -8], ['form-b-s2', 'B · الخطوة 2 من 4', '.prog', -8], ['form-b-s3', 'B · الخطوة 3 من 4', '.prog', -8],
  ['form-b-s4', 'B · الخطوة 4 من 4', '.prog', -8], ['form-b-errors', 'B · أخطاء الخطوة 1', '.prog', -8],
  ['success', 'نجاح الإرسال (رقم الطلب عيّنة)', null], ['track', 'متابعة الطلب', null], ['track-error', 'متابعة: خطأ عام', null],
  ['track-result', 'متابعة: الحالة العامة فقط', null], ['en-careers', '/en/careers/ → النموذج العربي', null],
];
const PUB_D = [['form-a', 'A · 1440px', null], ['form-a-errors', 'A · أخطاء · 1440px', '.esum', -8], ['form-b', 'B · الخطوة 1 · 1440px', null],
  ['success', 'نجاح الإرسال', null], ['track-result', 'متابعة: الحالة العامة', null], ['en-careers', '/en/careers/', null]];
const DASH = [['overview', 'نظرة عامة: 8 بطاقات + الفترة'], ['list', 'قائمة الطلبات'], ['list-filters', 'فلاتر متقدمة + حفظ'], ['list-columns', 'الأعمدة: إظهار/إخفاء/سحب/استعادة'],
  ['list-bulk', 'تحديد جماعي (مضغوط)'], ['bulk-confirm', 'تأكيد التغيير الجماعي'], ['quickview', 'عرض سريع'], ['application', 'الطلب الكامل'],
  ['interviews', 'المقابلات'], ['archived', 'المؤرشفة'], ['delete-confirm', 'حذف نهائي: تأكيد قوي'], ['export', 'تصدير: الهوية مقنّعة افتراضيًا'], ['settings', 'الإعدادات']];

async function shoot(b, list, w, h, prefix) {
  const out = [];
  for (const [i, [f, cap, sel, off = 0, full = false]] of list.entries()) {
    const p = await b.newPage({ viewport: { width: w, height: h }, deviceScaleFactor: 1 });
    await p.goto(H + f + '.html');
    if (sel) await p.evaluate(([s, o]) => { const e = document.querySelector(s); window.scrollTo(0, e.getBoundingClientRect().top + scrollY + o); }, [sel, off]);
    const fn = `${prefix}-${String(i).padStart(2, '0')}.png`;
    await p.screenshot({ path: T + fn, fullPage: full }); await p.close();
    out.push([fn, cap]);
  }
  return out;
}

async function sheet(b, name, title, shots, colW, per) {
  const figs = shots.map(([f, c]) => `<figure><img src="${f}"><figcaption>${c}</figcaption></figure>`).join('');
  const html = `<html dir="rtl" lang="ar"><body style="margin:0;padding:24px;background:#f0f0f0;font-family:DejaVu Sans,sans-serif">`
    + `<h1 style="margin:0 0 4px;font-size:22px">SHELTER COFFEE — Careers &amp; Recruitment · Low-Fi Wireframes · ${title}</h1>`
    + `<p style="margin:0 0 16px;color:#444;font-size:14px">LOW-FI — ليست هوية بصرية (ملفات الهوية غير متوفرة) · كل البيانات تجريبية · M28 Phase 6 + 7</p>`
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
  await sheet(b, 'public-390', 'Public form · 390px (Arabic RTL)', await shoot(b, PUB, 390, 844, 'p390'), 300, 6);
  await sheet(b, 'public-1440', 'Public form · 1440px', await shoot(b, PUB_D, 1440, 900, 'p1440'), 620, 3);
  await sheet(b, 'form-A-vs-B-390', 'A (صفحة واحدة) مقابل B (4 خطوات) · صفحة كاملة · 390px',
    await shoot(b, [['form-a', 'A · الصفحة كاملة', null, 0, true], ['form-b', 'B · الخطوة 1', null, 0, true], ['form-b-s2', 'B · الخطوة 2', null, 0, true],
      ['form-b-s3', 'B · الخطوة 3', null, 0, true], ['form-b-s4', 'B · الخطوة 4', null, 0, true]], 390, 844, 'ab'), 280, 5);
  await sheet(b, 'dashboard-1440', 'Owner Dashboard · Desktop 1440px', await shoot(b, DASH.map(([f, c]) => [`d-${f}`, c, null]), 1440, 900, 'd'), 620, 3);
  await sheet(b, 'dashboard-390', 'Owner Dashboard · Mobile 390px', await shoot(b, DASH.filter(([f]) => f !== 'list-columns').map(([f, c]) => [`m-${f}`, c, null]), 390, 844, 'm'), 300, 6);
  await b.close();
  fs.rmSync(T, { recursive: true, force: true });
  console.log('sheets →', P);
})();
