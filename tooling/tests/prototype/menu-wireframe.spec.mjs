// Prototype contract tests on the low-fi menu wireframes (docs/menu-ia/wireframes/html).
// They encode the IA spec rules (SHELTER-MENU-IA-SPEC.md, UX-VALIDATION.md, ACCESSIBILITY-CHECKLIST.md)
// so the same checks can later run against the real menu page.
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

import { DESKTOP_MIN } from '../../viewports.mjs';

const pageFor = (lang, testInfo) => (testInfo.project.use.viewport.width >= DESKTOP_MIN ? `d-${lang}-default.html` : `m-${lang}-default.html`);

for (const lang of ['ar', 'en']) {
  test.describe(`menu prototype — ${lang.toUpperCase()}`, () => {
    test(`language and direction @structure`, async ({ page }, testInfo) => {
      await page.goto(pageFor(lang, testInfo));
      await expect(page.locator('html')).toHaveAttribute('lang', lang);
      await expect(page.locator('html')).toHaveAttribute('dir', lang === 'ar' ? 'rtl' : 'ltr');
      const other = lang === 'ar' ? 'en' : 'ar';
      const secondary = page.locator('.card .n2');
      expect(await secondary.count()).toBeGreaterThan(0);
      expect(await secondary.evaluateAll((els, o) => els.filter(e => e.getAttribute('lang') !== o).length, other)).toBe(0);
    });

    test(`heading hierarchy @structure`, async ({ page }, testInfo) => {
      await page.goto(pageFor(lang, testInfo));
      const levels = await page.locator('h1,h2,h3,h4,h5,h6').evaluateAll(hs => hs.map(h => +h.tagName[1]));
      expect(levels.filter(l => l === 1)).toHaveLength(1);
      for (let i = 1; i < levels.length; i++) expect(levels[i] - levels[i - 1]).toBeLessThanOrEqual(1);
    });

    test(`no horizontal overflow, prices on one line, names readable @responsive`, async ({ page }, testInfo) => {
      await page.goto(pageFor(lang, testInfo));
      const r = await page.evaluate(() => {
        const lines = el => Math.round(el.getBoundingClientRect().height / parseFloat(getComputedStyle(el).lineHeight));
        const cards = [...document.querySelectorAll('.grid .card')];
        return {
          overflowX: document.documentElement.scrollWidth > innerWidth,
          priceWrap: cards.filter(c => lines(c.querySelector('.pr')) > 1).length,
          maxNameLines: Math.max(...cards.map(c => lines(c.querySelector('.n1')))),
          cards: cards.length,
        };
      });
      expect(r.cards).toBe(186); // 191 active products − 5 SPRING items shown in the seasonal section
      expect(r.overflowX).toBe(false);
      expect(r.priceWrap).toBe(0);
      expect(r.maxNameLines).toBeLessThanOrEqual(4); // UX-VALIDATION R-07
    });

    test(`touch targets ≥ 44px @responsive @a11y`, async ({ page }, testInfo) => {
      test.skip(testInfo.project.use.viewport.width >= DESKTOP_MIN, 'mobile/tablet controls only');
      await page.goto(`m-${lang}-scrolled.html`);
      await page.locator('#hot-drinks h3').first().scrollIntoViewIfNeeded();
      const small = await page.locator('.chip, .subchip, .hbtn, .seg button').evaluateAll(els => els
        .map(e => e.getBoundingClientRect()).filter(r => r.width && (r.width < 44 || r.height < 44)).length);
      expect(small).toBe(0);
    });

    test(`axe: no serious or critical WCAG issues @a11y`, async ({ page }, testInfo) => {
      await page.goto(pageFor(lang, testInfo));
      const results = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']).analyze();
      await testInfo.attach('axe-results.json', { body: JSON.stringify(results.violations, null, 2), contentType: 'application/json' });
      const blocking = results.violations.filter(v => ['serious', 'critical'].includes(v.impact));
      expect(blocking.map(v => `${v.id} (${v.nodes.length})`)).toEqual([]);
    });
  });
}
