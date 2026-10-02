// Menu page journeys against the running app (SHELTER_BASE_URL). Each scenario maps to docs/menu-ia/USER-FLOWS.md and
// runs in Arabic (RTL) and English (LTR) on a phone and a desktop project (the full viewport matrix for layout is in
// site.spec.mjs). Replaces the placeholders that stood here before the app existed (FINAL-QA QA-015).
import { test, expect } from '@playwright/test';

const APP = Boolean(process.env.SHELTER_BASE_URL);
const PROJECTS = /-(m390|d1440)$/;
const LOCALES = ['ar', 'en'];

/** Opens the menu and waits for its script (the search index is read on load). */
async function openMenu(page, locale, query = '') {
  const errors = [];
  page.on('pageerror', (e) => errors.push(e.message));
  await page.goto(`/${locale}/jo/menu/${query}`, { waitUntil: 'networkidle' });
  await expect(page.locator('[data-ui-menu]')).toBeVisible();
  return errors;
}

test.describe('menu journeys @app @journey', () => {
  test.skip(!APP, 'set SHELTER_BASE_URL to run against the app');
  test.beforeEach(({}, info) => test.skip(!PROJECTS.test(info.project.name), 'journeys run on a phone and a desktop'));

  for (const locale of LOCALES) {
    test(`${locale} F-01 content and prices are there without JavaScript`, async ({ browser, baseURL }) => {
      const context = await browser.newContext({ javaScriptEnabled: false, baseURL });
      const page = await context.newPage();
      await page.goto(`/${locale}/jo/menu/`);
      const cards = page.locator('.ui-product-card');
      expect(await cards.count()).toBeGreaterThan(100);
      await expect(cards.first().locator('.ui-product-card__price')).toBeVisible();
      // Categories are plain links to anchors: they work with no script.
      const chip = page.locator('[data-ui-menu-nav]').first();
      const target = await chip.getAttribute('href');
      expect(target).toMatch(/#/);
      await context.close();
    });

    test(`${locale} F-02 search filters live, suggests, and jumps to the item`, async ({ page }) => {
      const errors = await openMenu(page, locale);
      const total = await page.locator('[data-ui-menu-item]:not([hidden])').count();
      const search = page.locator('#menu-search');
      await search.fill('latte');
      const visible = await page.locator('[data-ui-menu-item]:not([hidden])').count();
      expect(visible).toBeGreaterThan(0);
      expect(visible).toBeLessThan(total);
      await expect(page.locator('[data-ui-menu-count]')).toBeVisible();
      await expect(page.locator('#menu-search-list [role="option"]').first()).toBeVisible();
      await expect(search).toHaveAttribute('aria-expanded', 'true');

      // Keyboard: ↓ then Enter jumps to the item in its place, clears the filter and puts focus on the item.
      await search.press('ArrowDown');
      await expect(search).toHaveAttribute('aria-activedescendant', 'menu-option-0');
      const picked = await page.locator('#menu-option-0').getAttribute('data-target');
      await search.press('Enter');
      await expect(search).toHaveValue('');
      expect(await page.locator('[data-ui-menu-item]:not([hidden])').count()).toBe(total);
      const located = page.locator(`[data-ui-menu-item="${picked}"]`);
      await expect(located).toBeInViewport();
      await expect(located.locator('.ui-product-card__action')).toBeFocused();
      expect(new URL(page.url()).search, 'search text never enters the URL').toBe('');
      expect(errors).toEqual([]);
    });

    test(`${locale} F-13 a search with no result says so and clears in one step`, async ({ page }) => {
      await openMenu(page, locale);
      const total = await page.locator('[data-ui-menu-item]:not([hidden])').count();
      await page.locator('#menu-search').fill('zzqxw');
      await expect(page.locator('[data-ui-menu-empty]')).toBeVisible();
      await expect(page.locator('[data-ui-menu-empty]')).toContainText('zzqxw');
      expect(await page.locator('[data-ui-menu-item]:not([hidden])').count()).toBe(0);
      await page.locator('[data-ui-menu-clear]').click();
      await expect(page.locator('[data-ui-menu-empty]')).toBeHidden();
      expect(await page.locator('[data-ui-menu-item]:not([hidden])').count()).toBe(total);
      await expect(page.locator('#menu-search')).toBeFocused();
    });

    test(`${locale} F-03 the branch choice updates the address without a history entry and is remembered`, async ({ page }) => {
      await openMenu(page, locale);
      const before = await page.evaluate(() => history.length);
      const drive = page.locator('[data-ui-menu-branch] button[data-value="drive"]');
      await drive.click();
      await expect(drive).toHaveAttribute('aria-pressed', 'true');
      expect(new URL(page.url()).searchParams.get('branch')).toBe('drive');
      expect(await page.evaluate(() => history.length)).toBe(before);
      // A refresh keeps it (server-rendered from ?branch); a fresh visit without ?branch restores it from this device.
      await page.reload({ waitUntil: 'networkidle' });
      await expect(drive).toHaveAttribute('aria-pressed', 'true');
      await page.goto(`/${locale}/jo/menu/`, { waitUntil: 'networkidle' });
      await expect(page.locator('[data-ui-menu-branch] button[data-value="drive"]')).toHaveAttribute('aria-pressed', 'true');
      // The language switch keeps the branch (FINAL-QA QA-016).
      const other = locale === 'ar' ? 'en' : 'ar';
      const switchHref = await page.locator(`a[hreflang="${other}"]`).first().getAttribute('href');
      expect(switchHref).toContain('branch=drive');
      await page.locator('[data-ui-menu-branch] button[data-value="all"]').click();
      expect(new URL(page.url()).searchParams.has('branch')).toBe(false);
    });

    test(`${locale} F-04/F-05 details open as a dialog; Esc and Back close it and focus returns`, async ({ page }) => {
      const errors = await openMenu(page, locale);
      const action = page.locator('.ui-product-card__action').first();
      const name = (await page.locator('.ui-product-card__name span').first().textContent())?.trim();
      const dialog = page.locator('#menu-detail');

      await action.click();
      await expect(dialog).toBeVisible();
      await expect(dialog.locator('.ui-dialog__title')).toHaveText(name ?? '');
      expect(page.url()).toMatch(/#p-/);
      expect(await page.evaluate(() => getComputedStyle(document.documentElement).overflow)).toBe('hidden'); // scroll lock
      await page.keyboard.press('Escape');
      await expect(dialog).toBeHidden();
      await expect.poll(() => new URL(page.url()).hash).toBe('');
      await expect(action).toBeFocused();

      // Browser Back closes the dialog first and stays on the menu.
      await action.click();
      await expect(dialog).toBeVisible();
      await page.goBack();
      await expect(dialog).toBeHidden();
      expect(new URL(page.url()).pathname).toBe(`/${locale}/jo/menu/`);
      expect(errors).toEqual([]);
    });

    test(`${locale} deep link to an item opens its details, also after a refresh`, async ({ page }) => {
      await openMenu(page, locale);
      const id = await page.locator('.ui-product-card').nth(5).getAttribute('id');
      await page.goto(`/${locale}/jo/menu/#${id}`, { waitUntil: 'networkidle' });
      await page.reload({ waitUntil: 'networkidle' });
      await expect(page.locator('#menu-detail')).toBeVisible();
      await expect(page.locator(`#${id}`)).toBeInViewport();
    });

    test(`${locale} F-06 a category link lands on its section below the sticky bars`, async ({ page }) => {
      await openMenu(page, locale);
      const chips = page.locator('[data-ui-menu-nav]:visible');
      const chip = chips.nth(Math.min(3, (await chips.count()) - 1));
      const id = await chip.getAttribute('data-ui-menu-nav');
      await chip.click();
      const title = page.locator(`#${id}-title`);
      await expect(title).toBeInViewport();
      // Nothing sticky covers the heading after the jump.
      const covered = await title.evaluate((heading) => {
        const box = heading.getBoundingClientRect();
        const top = document.elementFromPoint(box.left + box.width / 2, box.top + 2);
        return top !== null && !heading.contains(top);
      });
      expect(covered, 'heading covered by a sticky bar').toBe(false);
    });

    test(`${locale} F-08/F-09 a branch page opens the menu with that branch selected`, async ({ page }) => {
      await page.goto(`/${locale}/jo/locations/irbid/drive/`, { waitUntil: 'networkidle' });
      await page.locator('a[href*="/jo/menu/?branch=drive"]').first().click();
      await page.waitForURL(/\/jo\/menu\/\?branch=drive/);
      await expect(page.locator('[data-ui-menu-branch] button[data-value="drive"]')).toHaveAttribute('aria-pressed', 'true');
    });

    test(`${locale} long names wrap, nothing is cut or overflows, no broken image`, async ({ page }) => {
      await openMenu(page, locale);
      const cut = await page.evaluate(() => [...document.querySelectorAll('.ui-product-card__name')]
        .filter((el) => el.scrollWidth > el.clientWidth + 1 || getComputedStyle(el).textOverflow === 'ellipsis')
        .map((el) => el.textContent?.trim()).slice(0, 3));
      expect(cut).toEqual([]);
      const broken = await page.evaluate(() => [...document.images].filter((img) => img.complete && img.naturalWidth === 0).map((img) => img.src));
      expect(broken).toEqual([]);
    });

    test(`${locale} scroll position comes back after visiting another page and pressing Back`, async ({ page }) => {
      await openMenu(page, locale);
      await page.evaluate(() => window.scrollTo(0, 2500));
      await page.waitForTimeout(200);
      const before = await page.evaluate(() => window.scrollY);
      await page.goto(`/${locale}/jo/locations/`, { waitUntil: 'networkidle' });
      await page.goBack({ waitUntil: 'networkidle' });
      await expect.poll(() => page.evaluate(() => window.scrollY)).toBeGreaterThan(before - 300);
    });
  }
});
