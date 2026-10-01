const { chromium } = require('playwright');
const P = '/home/user/SHELTER-WEBSITE/docs/menu-ia/wireframes/png/';
const MC = [['01-top','أعلى الصفحة: بحث + الفرع + الموسم','Top: search + branch + seasonal'],['02-hot-sticky-subcats','داخل الساخن: شريط لاصق + بحث مضغوط + أقسام فرعية','In HOT: sticky bar + compact search + subcategories'],
 ['03-search-suggestions','البحث: اقتراحات + فلترة مباشرة','Search: suggestions + live filter'],['04-zero-results','لا نتائج','Zero results'],['05-product-sheet','تفاصيل الصنف (Bottom Sheet)','Product detail (bottom sheet)'],
 ['06-all-categories','كل الفئات','All categories'],['07-house-closed','HOUSE مغلق (وقت محاكى 8:00 ص)','HOUSE closed (simulated 8:00 AM)'],['08-unavailable-branch-only','غير متوفر / فرع واحد (مثال)','Unavailable / one branch only (demo)'],
 ['09-drive-closing-soon','DRIVE يغلق قريبًا (محاكى 1:15 ص)','DRIVE closing soon (simulated 1:15 AM)'],['10-branch-variant-B','بديل B: الفرع بجانب العنوان','Variant B: selector by title'],
 ['11-one-column-320','عمود واحد على 320px','One column at 320px'],['12-no-image-grid','بطاقات بلا صور (حالة الإطلاق)','No-image cards (launch state)'],['13-spring-seasonal','قسم الموسم SPRING','SPRING seasonal section']];
const DC = [['d1-top-4col','4 أعمدة + قائمة جانبية (1440px)','4 columns + side nav (1440px)'],['d2-3col-1024','3 أعمدة (1024px)','3 columns (1024px)'],['d3-product-modal','تفاصيل الصنف (Modal)','Product detail (modal)'],['d4-house-closed-unavailable','HOUSE مغلق + غير متوفر (مثال)','HOUSE closed + unavailable (demo)']];
(async () => {
  const b = await chromium.launch();
  for (const lang of ['ar','en']) {
    for (const [kind, list, w, per] of [['m', MC, 280, 5], ['d', DC, 760, 2]]) {
      const ci = lang === 'ar' ? 1 : 2;
      const figs = list.map(([f, ...c]) => `<figure><img src="${kind}-${lang}-${f}.png"><figcaption>${c[ci-1]}</figcaption></figure>`).join('');
      const title = (kind === 'm' ? (lang === 'ar' ? 'MOBILE — ARABIC (RTL)' : 'MOBILE — ENGLISH (LTR)') : (lang === 'ar' ? 'DESKTOP — ARABIC (RTL)' : 'DESKTOP — ENGLISH (LTR)'));
      const html = `<html dir="${lang==='ar'?'rtl':'ltr'}"><body style="margin:0;padding:24px;background:#f0f0f0;font-family:DejaVu Sans,sans-serif"><h1 style="margin:0 0 4px;font-size:22px">SHELTER COFFEE — Menu Low-Fi Wireframes · ${title}</h1><p style="margin:0 0 16px;color:#555;font-size:13px">LOW-FI · not visual design · no approved images yet · DEMO = illustrative state</p><div style="display:grid;grid-template-columns:repeat(${per},${w}px);gap:20px">${figs}</div><style>figure{margin:0;background:#fff;padding:8px;border-radius:10px;box-shadow:0 1px 3px rgba(0,0,0,.15)}img{width:100%;display:block;border:1px solid #ddd}figcaption{font-size:13px;padding:6px 2px 0;color:#222}</style></body></html>`;
      const p = await b.newPage({ viewport: { width: per * (w + 20) + 48, height: 800 } });
      const fn = P + `_sheet-${kind}-${lang}.html`; require('fs').writeFileSync(fn, html); await p.goto('file://' + fn); await p.waitForLoadState('load');
      await p.screenshot({ path: P + `SHEET-${kind === 'm' ? 'mobile' : 'desktop'}-${lang}.png`, fullPage: true }); require('fs').unlinkSync(P + `_sheet-${kind}-${lang}.html`); await p.close();
    }
  }
  await b.close(); console.log('sheets done');
})();
