const { chromium } = require('playwright');
const H = 'file:///home/user/SHELTER-WEBSITE/docs/menu-ia/wireframes/html/';
const O = '/home/user/SHELTER-WEBSITE/docs/menu-ia/wireframes/png/';
const M = [ // [file, out, width, scrollTarget, offset]
 ['default','01-top',390,null,0], ['scrolled','02-hot-sticky-subcats',390,'#hot-drinks h3',-130], ['search','03-search-suggestions',390,null,0],
 ['zero','04-zero-results',390,null,0], ['sheet','05-product-sheet',390,null,0], ['allcats','06-all-categories',390,null,0],
 ['house-closed','07-house-closed',390,null,0], ['house-cold','08-unavailable-branch-only',390,'#p-iced-latte',-250],
 ['drive-closing','09-drive-closing-soon',390,null,0], ['variantB','10-branch-variant-B',390,null,0], ['default','11-one-column-320',320,'#speciality-coffee',-60],
 ['noimg','12-no-image-grid',390,'#hot-drinks',-60], ['default','13-spring-seasonal',390,'#spring',-140]];
(async () => {
  const b = await chromium.launch();
  for (const lang of ['ar','en']) {
    for (const [f,o,w,sel,off] of M) {
      const p = await b.newPage({ viewport: { width: w, height: 800 }, deviceScaleFactor: 1.5 });
      await p.goto(H + `m-${lang}-${f}.html`);
      if (sel) await p.evaluate(([s,off]) => { const el = document.querySelector(s); window.scrollTo(0, el.getBoundingClientRect().top + scrollY + off); }, [sel,off]);
      await p.screenshot({ path: O + `m-${lang}-${o}.png` }); await p.close();
    }
    const D = [['default','d1-top-4col',1440,null,0],['default','d2-3col-1024',1024,'#hot-drinks',-20],['modal','d3-product-modal',1440,null,0],['house','d4-house-closed-unavailable',1440,'#p-iced-latte',-300]];
    for (const [f,o,w,sel,off] of D) {
      const p = await b.newPage({ viewport: { width: w, height: 900 }, deviceScaleFactor: 1 });
      await p.goto(H + `d-${lang}-${f}.html`);
      if (sel) await p.evaluate(([s,off]) => { const el = document.querySelector(s); window.scrollTo(0, el.getBoundingClientRect().top + scrollY + off); }, [sel,off]);
      await p.screenshot({ path: O + `d-${lang}-${o}.png` }); await p.close();
    }
  }
  await b.close(); console.log('shots done');
})();
