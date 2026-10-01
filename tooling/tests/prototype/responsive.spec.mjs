// Responsive QA contract (owner rule: RESPONSIVE DESIGN — MANDATORY). Runs on every project in viewports.mjs, AR (RTL) + EN (LTR).
// Any overflow, clipping or overlap is a BUG, not cosmetic. Results feed docs/qa/RESPONSIVE-QA-MATRIX.md.
import { test, expect } from '@playwright/test';
import { DESKTOP_MIN } from '../../viewports.mjs';

const isDesk = ti => ti.project.use.viewport.width >= DESKTOP_MIN;
const page_ = (kind, lang, ti) => `${isDesk(ti) ? 'd' : 'm'}-${lang}-${kind}.html`;
const UI_TEXT = '.n1,.n2,.pr,h1,h2,h3,.chip,.subchip,.seg button,.bstat,.badge,.note,.search label,.cnt,.compactsel,.desk .side a';

for (const lang of ['ar', 'en']) {
  test.describe(`responsive — ${lang.toUpperCase()}`, () => {
    test('zoom is never blocked (viewport meta) @responsive @a11y', async ({ page }, ti) => {
      await page.goto(page_('default', lang, ti));
      const content = await page.locator('meta[name=viewport]').getAttribute('content');
      expect(content).toContain('width=device-width');
      expect(content).not.toMatch(/user-scalable\s*=\s*(no|0)/);
      const max = content.match(/maximum-scale\s*=\s*([\d.]+)/);
      if (max) expect(+max[1]).toBeGreaterThanOrEqual(5);
    });

    test('no text clipping in UI text @responsive', async ({ page }, ti) => {
      await page.goto(page_('default', lang, ti));
      const clipped = await page.locator(UI_TEXT).evaluateAll(els => els.filter(e => {
        const cs = getComputedStyle(e); if (cs.display === 'none' || e.clientWidth === 0) return false;
        return e.scrollWidth - e.clientWidth > 1 || e.scrollHeight - e.clientHeight > 1 && cs.overflowY !== 'visible';
      }).map(e => `${e.className || e.tagName}: ${e.textContent.trim().slice(0, 30)}`));
      expect(clipped).toEqual([]);
    });

    test('UI text is at least 12px @responsive @a11y', async ({ page }, ti) => {
      await page.goto(page_('default', lang, ti));
      const tiny = await page.locator(UI_TEXT).evaluateAll(els => [...new Set(els
        .filter(e => e.getClientRects().length && parseFloat(getComputedStyle(e).fontSize) < 12)
        .map(e => `${e.className || e.tagName} ${getComputedStyle(e).fontSize}`))]);
      expect(tiny).toEqual([]);
    });

    test('category anchor lands below sticky bars (not covered) @responsive', async ({ page }, ti) => {
      await page.goto(page_('default', lang, ti) + '#cold-drinks');
      const covered = await page.evaluate(() => {
        const h = document.querySelector('#cold-drinks h2, #h-cold-drinks') || document.querySelector('#cold-drinks');
        const r = h.getBoundingClientRect(); const x = r.left + Math.min(20, r.width / 2);
        const hit = document.elementFromPoint(x, r.top + 2);
        return hit && !(h === hit || h.contains(hit)) ? (hit.className || hit.tagName) : null;
      });
      expect(covered).toBeNull();
    });

    test('sticky chrome leaves room for content (≤ 30% of viewport height) @responsive', async ({ page }, ti) => {
      test.skip(isDesk(ti), 'desktop uses a sticky sidebar, not top bars');
      await page.goto(`m-${lang}-scrolled.html`);
      // Scroll deep inside a section that has a subcategory bar so BOTH sticky bars are stuck (worst case).
      await page.evaluate(() => { const s = document.querySelector('#hot-drinks'); window.scrollTo(0, s.offsetTop + s.offsetHeight / 2); });
      const share = await page.evaluate(() => {
        const stuck = [...document.querySelectorAll('*')].filter(e => ['sticky', 'fixed'].includes(getComputedStyle(e).position))
          .map(e => e.getBoundingClientRect()).filter(r => r.top >= -1 && r.top <= 120 && r.bottom > 0 && r.width > innerWidth / 2);
        return { bars: stuck.length, share: Math.max(0, ...stuck.map(r => r.bottom)) / innerHeight };
      });
      expect(share.bars).toBeGreaterThanOrEqual(1); // measured deep inside a section: every bar that is sticky at this height is stuck (R-09: short viewports keep only the category bar sticky)
      expect(share.share).toBeLessThanOrEqual(0.3);
    });

    test('bottom sheet / modal fits the screen and close stays reachable @responsive', async ({ page }, ti) => {
      await page.goto(isDesk(ti) ? `d-${lang}-modal.html` : `m-${lang}-sheet.html`);
      const r = await page.evaluate(() => {
        const d = document.querySelector('[role=dialog]'); const b = d.getBoundingClientRect();
        const x = d.querySelector('.x, [aria-label]').getBoundingClientRect();
        return { top: b.top, bottom: b.bottom, left: b.left, right: b.right, vw: innerWidth, vh: innerHeight,
          scrollable: d.scrollHeight <= d.clientHeight + 1 || ['auto', 'scroll'].includes(getComputedStyle(d).overflowY),
          closeVisible: x.top >= 0 && x.bottom <= innerHeight && x.width >= 44 && x.height >= 44 };
      });
      expect(r.top).toBeGreaterThanOrEqual(0); expect(r.bottom).toBeLessThanOrEqual(r.vh + 1);
      expect(r.left).toBeGreaterThanOrEqual(-1); expect(r.right).toBeLessThanOrEqual(r.vw + 1);
      expect(r.scrollable).toBe(true); expect(r.closeVisible).toBe(true);
    });

    test('content width is constrained on wide screens @responsive', async ({ page }, ti) => {
      test.skip(ti.project.use.viewport.width < 1440, 'wide screens only');
      await page.goto(`d-${lang}-default.html`);
      const w = await page.locator('.grid').first().evaluate(e => e.getBoundingClientRect().width);
      expect(w).toBeLessThanOrEqual(1280);
    });

    test('keyboard: visible focus on the first interactive elements @a11y', async ({ page }, ti) => {
      await page.goto(page_('default', lang, ti));
      for (let i = 0; i < 6; i++) {
        await page.keyboard.press('Tab');
        const f = await page.evaluate(() => { const e = document.activeElement; const cs = getComputedStyle(e);
          return { tag: e.tagName, ring: cs.outlineStyle !== 'none' && parseFloat(cs.outlineWidth) >= 2 || cs.boxShadow !== 'none' }; });
        expect(f.tag).not.toBe('BODY'); expect(f.ring).toBe(true);
      }
    });

    test('visual snapshot @visual', async ({ page }, ti) => {
      await page.goto(page_('default', lang, ti));
      await expect(page).toHaveScreenshot(`${lang}-default.png`);
    });
  });
}
