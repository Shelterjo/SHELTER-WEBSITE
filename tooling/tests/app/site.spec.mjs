// Public site — full QA against the running app (SHELTER_BASE_URL, e.g. http://127.0.0.1:8000). Every public page in
// Arabic and English on every viewport project: the right HTTP status, html lang/dir, a title, exactly one <h1>, no
// horizontal overflow and no script errors. @a11y: axe (WCAG 2.2 AA) without serious or critical findings, on a phone
// and a desktop. @links: every same-site link and asset on the page answers (once, on the desktop project).
// Pages whose content the Owner has not published yet (about, FAQ, legal, Media Center, awards, family) answer 404 by
// design (M36: nothing unapproved goes public); they are checked fully as soon as they answer 200.
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';

const APP = Boolean(process.env.SHELTER_BASE_URL);

/** [path, expected status] — 'optional' = 200 once the Owner publishes it, 404 until then. */
const PAGES = [
  ['/', 200],
  ['/ar/', 200], ['/en/', 200],
  ['/ar/jo/menu/', 200], ['/en/jo/menu/', 200],
  ['/ar/jo/locations/', 200], ['/en/jo/locations/', 200],
  ['/ar/jo/locations/irbid/drive/', 200], ['/en/jo/locations/irbid/drive/', 200],
  ['/ar/jo/locations/irbid/house/', 200], ['/en/jo/locations/irbid/house/', 200],
  ['/ar/contact/', 200], ['/en/contact/', 200],
  ['/ar/jo/events/', 200], ['/en/jo/events/', 200],
  ['/ar/careers/', 200], ['/en/careers/', 200], ['/ar/careers/track/', 200],
  ['/ar/franchise/', 200], ['/en/franchise/', 200],
  ['/ar/feedback/', 200], ['/en/feedback/', 200],
  ['/ar/search/?q=latte', 200], ['/en/search/?q=latte', 200],
  ['/ar/about/', 'optional'], ['/en/about/', 'optional'], ['/ar/faq/', 'optional'], ['/en/faq/', 'optional'],
  ['/ar/privacy/', 'optional'], ['/en/privacy/', 'optional'], ['/ar/terms/', 'optional'], ['/en/terms/', 'optional'],
  ['/ar/media/', 'optional'], ['/en/media/', 'optional'], ['/ar/awards/', 'optional'], ['/en/awards/', 'optional'],
  ['/ar/family/', 'optional'], ['/en/family/', 'optional'],
  ['/ar/no-such-page/', 404], ['/en/no-such-page/', 404],
];

const A11Y_PROJECTS = /-(m390|d1440)$/;
const LINK_PROJECT = /-d1440$/;

/** Opens a page; returns null when an optional page is not published yet. */
async function open(page, path, expected) {
  const errors = [];
  page.on('pageerror', (e) => errors.push(e.message));
  const response = await page.goto(path, { waitUntil: 'networkidle' });
  const status = response?.status() ?? 0;
  if (expected === 'optional' && status === 404) return null;
  expect(status, `${path} status`).toBe(expected === 'optional' ? 200 : expected);
  return errors;
}

test.describe('public site @app', () => {
  test.skip(!APP, 'set SHELTER_BASE_URL to run against the app');

  for (const [path, expected] of PAGES) {
    test(`${path} — layout, language, one h1, no overflow @responsive`, async ({ page }) => {
      const errors = await open(page, path, expected);
      test.skip(errors === null, 'not published yet');
      const ar = path === '/' || path.startsWith('/ar/');
      await expect(page.locator('html')).toHaveAttribute('lang', ar ? 'ar' : 'en');
      await expect(page.locator('html')).toHaveAttribute('dir', ar ? 'rtl' : 'ltr');
      expect((await page.title()).trim().length, 'title').toBeGreaterThan(0);
      expect(await page.locator('h1').count(), 'exactly one h1').toBe(1);
      const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
      expect(overflow, 'horizontal overflow (px)').toBeLessThanOrEqual(0);
      // Nothing may stick out of the viewport sideways (clipped text, wide tables) — scrollers that are meant to scroll aside.
      const wide = await page.evaluate(() => [...document.querySelectorAll('main *')].filter((el) => {
        const r = el.getBoundingClientRect();
        if (r.width === 0 || getComputedStyle(el).visibility === 'hidden') return false;
        let p = el.parentElement;
        while (p) { const s = getComputedStyle(p); if (/(auto|scroll|hidden|clip)/.test(s.overflowX)) return false; p = p.parentElement; }
        return r.right > window.innerWidth + 1 || r.left < -1;
      }).slice(0, 3).map((el) => `${el.tagName.toLowerCase()}.${[...el.classList].join('.')}`));
      expect(wide, 'elements outside the viewport').toEqual([]);
      expect(errors, 'script errors').toEqual([]);
    });

    test(`${path} — accessibility (axe, WCAG 2.2 AA) @a11y`, async ({ page }, info) => {
      test.skip(!A11Y_PROJECTS.test(info.project.name), 'axe runs on a phone and a desktop');
      const errors = await open(page, path, expected);
      test.skip(errors === null, 'not published yet');
      const result = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']).analyze();
      const serious = result.violations.filter((v) => v.impact === 'serious' || v.impact === 'critical')
        .map((v) => `${v.id} (${v.impact}): ${v.nodes.slice(0, 2).map((n) => n.target.join(' ')).join(' | ')}`);
      expect(serious, 'serious/critical axe findings').toEqual([]);
    });

    test(`${path} — same-site links and assets answer @links`, async ({ page, request }, info) => {
      test.skip(!LINK_PROJECT.test(info.project.name), 'links are checked once, on the desktop project');
      const errors = await open(page, path, expected);
      test.skip(errors === null, 'not published yet');
      const origin = new URL(page.url()).origin;
      const urls = await page.evaluate((o) => [...new Set([
        ...[...document.querySelectorAll('a[href]')].map((a) => a.href),
        ...[...document.querySelectorAll('img[src], source[srcset], link[rel="stylesheet"], script[src]')]
          .map((el) => el.getAttribute('src') ?? (el.getAttribute('srcset') ?? '').split(/[ ,]/)[0] ?? el.getAttribute('href')),
      ].filter(Boolean).map((u) => new URL(u, location.href).href.split('#')[0]).filter((u) => u.startsWith(o)))], origin);
      const self = page.url().split('#')[0];
      const broken = [];
      // The 404 page's language switch points at the same missing address in the other language: expected.
      for (const url of urls.filter((u) => u !== self && !(expected === 404 && u.includes('/no-such-page/')))) {
        const response = await request.get(url, { maxRedirects: 3 });
        if (response.status() >= 400) broken.push(`${response.status()} ${url.replace(origin, '')}`);
      }
      expect(broken, 'broken same-site links').toEqual([]);
    });
  }
});
