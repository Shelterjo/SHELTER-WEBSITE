const { chromium } = require('playwright');
const fs = require('fs');
const H = '/home/user/SHELTER-WEBSITE/docs/menu-ia/wireframes/html/';
const tag = process.argv[2] || 'v1';
(async () => {
  const b = await chromium.launch();
  const out = { mobile: [], variant: [], desktop: [], sticky: [], targets: [] };
  for (const lang of ['ar', 'en']) for (const mode of ['default', 'noimg']) for (const w of [320, 360, 375, 390, 430, 768]) {
    const p = await b.newPage({ viewport: { width: w, height: 800 } });
    await p.goto('file://' + H + `m-${lang}-${mode}.html`);
    const m = await p.evaluate(() => {
      const lines = el => { const lh = parseFloat(getComputedStyle(el).lineHeight); return Math.round(el.getBoundingClientRect().height / lh); };
      const cards = [...document.querySelectorAll('.grid .card')];
      let maxN1 = 0, maxN2 = 0, over3 = 0, priceWrap = 0, overflow = 0, n1Name = '', n2Name = '';
      for (const c of cards) {
        const a = lines(c.querySelector('.n1')), z = lines(c.querySelector('.n2')), pr = lines(c.querySelector('.pr'));
        if (a > maxN1) { maxN1 = a; n1Name = c.querySelector('.n1').textContent; }
        if (z > maxN2) { maxN2 = z; n2Name = c.querySelector('.n2').textContent; }
        if (a > 3 || z > 3) over3++; if (pr > 1) priceWrap++; if (c.scrollWidth > c.clientWidth + 1) overflow++;
      }
      const g = document.querySelector('.grid');
      return { cards: cards.length, cols: getComputedStyle(g).gridTemplateColumns.split(' ').length, cardW: Math.round(cards[0].getBoundingClientRect().width),
        maxN1, n1Name, maxN2, n2Name, over3, priceWrap, overflow, pageOverflowX: document.documentElement.scrollWidth > innerWidth,
        docH: document.documentElement.scrollHeight, screens: +(document.documentElement.scrollHeight / innerHeight).toFixed(1),
        firstCardY: Math.round(cards[0].getBoundingClientRect().top + scrollY) };
    });
    out.mobile.push({ lang, mode, w, ...m }); await p.close();
  }
  for (const lang of ['ar', 'en']) for (const v of ['default', 'variantB']) for (const w of [320, 360, 390]) {
    const p = await b.newPage({ viewport: { width: w, height: 800 } });
    await p.goto('file://' + H + `m-${lang}-${v}.html`);
    const m = await p.evaluate(() => {
      const st = document.querySelector('.bstat'); const lh = parseFloat(getComputedStyle(st).lineHeight);
      const tr = document.querySelector('.titlerow');
      return { firstCardY: Math.round(document.querySelector('.season').getBoundingClientRect().top + scrollY),
        statusLines: Math.round(st.getBoundingClientRect().height / lh), titleRowOverflow: tr ? tr.scrollWidth > tr.clientWidth + 1 : false,
        statusOverflow: st.scrollWidth > st.clientWidth + 1 };
    });
    out.variant.push({ lang, v, w, ...m }); await p.close();
  }
  for (const lang of ['ar', 'en']) {
    const p = await b.newPage({ viewport: { width: 360, height: 740 } });
    await p.goto('file://' + H + `m-${lang}-scrolled.html`);
    await p.evaluate(() => document.querySelector('#hot-drinks h3').scrollIntoView());
    const m = await p.evaluate(() => {
      const cb = document.querySelector('.catbar').getBoundingClientRect(), sb = document.querySelector('#hot-drinks .subbar').getBoundingClientRect();
      const small = [...document.querySelectorAll('.chip,.hbtn,.seg button,.subchip')].map(e => { const r = e.getBoundingClientRect(); return { c: e.className, w: Math.round(r.width), h: Math.round(r.height) }; }).filter(x => x.w && (x.h < 44 || x.w < 44));
      return { catbarH: Math.round(cb.height), subbarH: Math.round(sb.height), stickyStack: Math.round(cb.height + sb.height), viewportH: innerHeight,
        pct: +(100 * (cb.height + sb.height) / innerHeight).toFixed(1), targetsUnder44: small.length, sample: small.slice(0, 3) };
    });
    out.sticky.push({ lang, ...m }); await p.close();
  }
  for (const lang of ['ar', 'en']) for (const w of [1024, 1280, 1366, 1440, 1920]) {
    const p = await b.newPage({ viewport: { width: w, height: 900 } });
    await p.goto('file://' + H + `d-${lang}-default.html`);
    const m = await p.evaluate(() => { const g = document.querySelector('.grid'); const c = g.querySelector('.card');
      return { cols: getComputedStyle(g).gridTemplateColumns.split(' ').length, gridW: Math.round(g.getBoundingClientRect().width), cardW: Math.round(c.getBoundingClientRect().width),
        maxN1: Math.max(...[...document.querySelectorAll('.grid .n1')].map(e => Math.round(e.getBoundingClientRect().height / parseFloat(getComputedStyle(e).lineHeight))))}; });
    out.desktop.push({ lang, w, ...m }); await p.close();
  }
  fs.writeFileSync(`measure-${tag}.json`, JSON.stringify(out, null, 1));
  await b.close();
  console.log('done');
})();
