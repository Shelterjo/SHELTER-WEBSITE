// Visitor journeys across the public site against the running app (SHELTER_BASE_URL): navigation, the mobile drawer,
// keyboard use, the language switch, history, the 404 page, search, a public form end to end, reduced motion and a
// page without JavaScript. Phone and desktop projects (FINAL-QA QA-2). The full viewport matrix is in site.spec.mjs.
import { execSync } from 'node:child_process';
import { test, expect } from '@playwright/test';

const APP = Boolean(process.env.SHELTER_BASE_URL);
const PHONE = /-m390$/;
const DESKTOP = /-d1440$/;

/** Collects script errors and failed same-site requests while a journey runs. */
function watch(page) {
  const problems = [];
  page.on('pageerror', (e) => problems.push(`script: ${e.message}`));
  page.on('console', (m) => { if (m.type() === 'error' && !/status of 404/.test(m.text())) problems.push(`console: ${m.text()}`); });
  return problems;
}

test.describe('site journeys @app @journey', () => {
  test.skip(!APP, 'set SHELTER_BASE_URL to run against the app');
  test.beforeEach(({}, info) => test.skip(!PHONE.test(info.project.name) && !DESKTOP.test(info.project.name), 'journeys run on a phone and a desktop'));

  for (const locale of ['ar', 'en']) {
    test(`${locale} every header link opens its page (one h1, right language)`, async ({ page }, info) => {
      const problems = watch(page);
      await page.goto(`/${locale}/`, { waitUntil: 'networkidle' });
      const scope = PHONE.test(info.project.name) ? '.ui-nav-drawer' : '.ui-site-header';
      const hrefs = await page.locator(`${scope} nav a[href]`).evaluateAll((links) => links.map((a) => a.href));
      expect(hrefs.length).toBeGreaterThanOrEqual(2); // menu and locations; the other pages sit in the footer
      for (const href of hrefs) {
        const response = await page.goto(href, { waitUntil: 'domcontentloaded' });
        expect(response?.status(), href).toBe(200);
        await expect(page.locator('html')).toHaveAttribute('lang', locale);
        expect(await page.locator('h1').count(), href).toBe(1);
      }
      expect(problems).toEqual([]);
    });

    test(`${locale} the language switch keeps the page`, async ({ page }) => {
      const other = locale === 'ar' ? 'en' : 'ar';
      for (const path of ['contact/', 'jo/locations/', 'jo/locations/irbid/house/', 'careers/', 'jo/events/']) {
        await page.goto(`/${locale}/${path}`, { waitUntil: 'domcontentloaded' });
        const href = await page.locator(`a[hreflang="${other}"]`).first().getAttribute('href');
        expect(new URL(href ?? '').pathname, path).toBe(`/${other}/${path}`);
      }
    });

    test(`${locale} Back and Forward walk the visited pages; refresh keeps the page`, async ({ page }) => {
      await page.goto(`/${locale}/`);
      await page.goto(`/${locale}/jo/menu/`);
      await page.goto(`/${locale}/jo/locations/`);
      await page.goBack();
      expect(new URL(page.url()).pathname).toBe(`/${locale}/jo/menu/`);
      await page.goBack();
      expect(new URL(page.url()).pathname).toBe(`/${locale}/`);
      await page.goForward();
      expect(new URL(page.url()).pathname).toBe(`/${locale}/jo/menu/`);
      await page.reload();
      expect(new URL(page.url()).pathname).toBe(`/${locale}/jo/menu/`);
      await expect(page.locator('h1')).toHaveCount(1);
    });

    test(`${locale} an unknown address answers 404 with a way back and a working search`, async ({ page }) => {
      const response = await page.goto(`/${locale}/no-such-page-qa/`);
      expect(response?.status()).toBe(404);
      await expect(page.locator(`a[href$="/${locale}/"]`).first()).toBeVisible();
      const search = page.locator('.ui-error__search input[type="search"]');
      await search.fill('latte');
      await search.press('Enter');
      await page.waitForURL(/\/search\/\?q=latte/);
      await expect(page.locator('h1')).toHaveCount(1);
    });

    test(`${locale} site search: results, an empty search and a query with symbols`, async ({ page }) => {
      const problems = watch(page);
      await page.goto(`/${locale}/search/?q=latte`);
      expect(await page.locator('main a[href*="/menu/"]').count()).toBeGreaterThan(0);
      await page.goto(`/${locale}/search/?q=`);
      await expect(page.locator('h1')).toHaveCount(1);
      const odd = '<script>alert(1)</script> «قهوة» 100% "x"';
      await page.goto(`/${locale}/search/?q=${encodeURIComponent(odd)}`);
      await expect(page.locator('h1')).toHaveCount(1);
      expect(await page.locator('main script').count(), 'the query is shown as text, never run').toBe(0);
      expect(problems).toEqual([]);
    });

    test(`${locale} keyboard: the skip link comes first and every stop shows focus`, async ({ page }) => {
      await page.goto(`/${locale}/`, { waitUntil: 'networkidle' });
      await page.keyboard.press('Tab');
      await expect(page.locator('.ui-skip-link')).toBeFocused();
      await page.keyboard.press('Enter');
      expect(new URL(page.url()).hash).toBe('#main');
      const invisible = [];
      for (let i = 0; i < 25; i++) {
        await page.keyboard.press('Tab');
        const info = await page.evaluate(() => {
          const el = document.activeElement;
          if (el === null || el === document.body) return null;
          // A stretched link draws its ring on the card it covers (card.css: .ui-card:has(.ui-stretched:focus-visible)).
          const ringed = el.classList.contains('ui-stretched') ? el.closest('.ui-card, .ui-branch, article') ?? el : el;
          const s = getComputedStyle(ringed);
          const shown = (s.outlineStyle !== 'none' && parseFloat(s.outlineWidth) > 0) || s.boxShadow !== 'none';
          return shown ? null : `${el.tagName.toLowerCase()}.${[...el.classList].join('.')}`;
        });
        if (info !== null) invisible.push(info);
      }
      expect(invisible, 'focus stops without a visible indicator').toEqual([]);
    });

    test(`${locale} the feedback form: errors first, then sent once even on a double click`, async ({ page }) => {
      // Earlier runs from this address count against the real limits (5 answers / 10 min): a local run clears the
      // app's cache first when it is told where the app is (SHELTER_APP_DIR); otherwise the limit answer is accepted.
      if (process.env.SHELTER_APP_DIR) execSync('php artisan cache:clear -q', { cwd: process.env.SHELTER_APP_DIR });
      const problems = watch(page);
      await page.goto(`/${locale}/feedback/`);
      await page.waitForTimeout(3200); // the minimum fill time of a real person (config feedback.abuse.min_fill_seconds)
      await page.locator('form.ui-apply__form button[type="submit"]').click();
      await expect(page.locator('[aria-invalid="true"]').first()).toBeVisible();
      await page.locator('input[name="branch"]').first().check({ force: true });
      await page.locator('input[name="rating_overall"][value="4"]').check({ force: true });
      await page.locator('#comment').fill(locale === 'ar' ? 'تجربة اختبار آلي — FINAL-QA' : 'Automated QA check — FINAL-QA');
      await page.locator('form.ui-apply__form button[type="submit"]').dblclick();
      await page.waitForURL(/\/feedback\/submitted\//);
      await expect(page.locator('h1')).toHaveCount(1);
      expect(problems).toEqual([]);
    });
  }

  test('careers (Arabic form): the error summary takes focus, a CV uploads and can be removed', async ({ page }) => {
    if (process.env.SHELTER_APP_DIR) execSync('php artisan cache:clear -q', { cwd: process.env.SHELTER_APP_DIR });
    const problems = watch(page);
    await page.goto('/ar/careers/', { waitUntil: 'networkidle' });
    const form = page.locator('form[data-careers-form]');
    test.skip((await form.count()) === 0, 'the form is closed in this environment');
    await page.waitForTimeout(8500); // the minimum fill time of a real person (careers.abuse.min_fill_seconds)
    await page.locator('[data-careers-submit]').click();
    await page.waitForLoadState('networkidle');
    await expect(page.locator('.ui-error-summary')).toBeFocused();
    expect(await page.locator('.ui-error-summary a[href^="#"]').count()).toBeGreaterThan(3);
    // Each summary link leads to its field.
    const first = page.locator('.ui-error-summary a[href^="#"]').first();
    const target = (await first.getAttribute('href'))?.slice(1);
    await first.click();
    expect(await page.evaluate((id) => document.activeElement?.id === id || document.activeElement?.closest(`#${CSS.escape(id)}`) !== null, target)).toBe(true);

    const stream = 'BT /F1 12 Tf 72 720 Td (Education Experience Skills) Tj ET';
    const objects = ['<< /Type /Catalog /Pages 2 0 R >>', '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
      '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
      `<< /Length ${stream.length} >>\nstream\n${stream}\nendstream`, '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>'];
    let pdf = '%PDF-1.4\n';
    const offsets = objects.map((o, i) => { const at = pdf.length; pdf += `${i + 1} 0 obj\n${o}\nendobj\n`; return at; });
    const xref = pdf.length;
    pdf += `xref\n0 ${objects.length + 1}\n0000000000 65535 f \n${offsets.map((o) => `${String(o).padStart(10, '0')} 00000 n \n`).join('')}trailer\n<< /Size ${objects.length + 1} /Root 1 0 R >>\nstartxref\n${xref}\n%%EOF`;
    await page.locator('#files-input').setInputFiles({ name: 'سيرة-ذاتية-QA.pdf', mimeType: 'application/pdf', buffer: Buffer.from(pdf) });
    const item = page.locator('[data-careers-files] .ui-upload__item').filter({ hasText: 'سيرة-ذاتية-QA.pdf' });
    await expect(item).toBeVisible();
    await expect(item).toHaveAttribute('data-file-id', /\d+/); // stored on the server (201)
    await expect(item).not.toHaveClass(/ui-upload__item--error/);
    await item.locator('[data-careers-remove]').click();
    await expect(item).toHaveCount(0);
    expect(problems).toEqual([]);
  });

  test('ar phone: the menu drawer opens, traps focus, closes with Esc and gives focus back', async ({ page }, info) => {
    test.skip(!PHONE.test(info.project.name), 'the drawer is the phone navigation');
    await page.goto('/ar/', { waitUntil: 'networkidle' });
    const button = page.locator('[data-ui-nav-open]');
    await expect(button).toHaveAttribute('aria-expanded', 'false');
    await button.click();
    const drawer = page.locator('[data-ui-nav-drawer]');
    await expect(drawer).toBeVisible();
    await expect(button).toHaveAttribute('aria-expanded', 'true'); // FINAL-QA QA-017
    expect(await page.evaluate(() => getComputedStyle(document.documentElement).overflow)).toBe('hidden');
    for (let i = 0; i < 15; i++) {
      await page.keyboard.press('Tab');
      // Native modal dialog: focus cycles inside it and through the browser's own controls (activeElement = body),
      // never onto the page behind it (inert).
      expect(await page.evaluate(() => document.activeElement === document.body || document.querySelector('[data-ui-nav-drawer]')?.contains(document.activeElement)), 'focus never reaches the page behind').toBe(true);
    }
    await page.keyboard.press('Escape');
    await expect(drawer).toBeHidden();
    await expect(button).toBeFocused();
    await expect(button).toHaveAttribute('aria-expanded', 'false');
  });

  test('reduced motion: nothing moves and nothing is left hidden', async ({ browser, baseURL }) => {
    const context = await browser.newContext({ reducedMotion: 'reduce', baseURL });
    const page = await context.newPage();
    await page.goto('/ar/', { waitUntil: 'networkidle' });
    await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
    await page.waitForTimeout(300);
    const running = await page.evaluate(() => document.getAnimations().filter((a) => a.playState === 'running').length);
    expect(running).toBe(0);
    const hidden = await page.evaluate(() => [...document.querySelectorAll('[data-ui-reveal]')].filter((el) => getComputedStyle(el).opacity !== '1').length);
    expect(hidden).toBe(0);
    await context.close();
  });

  test('without JavaScript every page section is visible', async ({ browser, baseURL }) => {
    const context = await browser.newContext({ javaScriptEnabled: false, baseURL });
    const page = await context.newPage();
    for (const path of ['/ar/', '/en/jo/locations/', '/ar/contact/', '/en/careers/']) {
      await page.goto(path);
      const hidden = await page.evaluate(() => [...document.querySelectorAll('main section, [data-ui-reveal]')].filter((el) => getComputedStyle(el).opacity === '0').length);
      expect(hidden, path).toBe(0);
    }
    await context.close();
  });

  test('with motion, sections below the fold appear when scrolled to (none stays invisible)', async ({ page }) => {
    await page.goto('/en/', { waitUntil: 'networkidle' });
    const height = await page.evaluate(() => document.body.scrollHeight);
    for (let y = 0; y <= height; y += 400) {
      await page.evaluate((top) => window.scrollTo(0, top), y);
      await page.waitForTimeout(60);
    }
    await page.waitForTimeout(1200);
    const hidden = await page.evaluate(() => [...document.querySelectorAll('[data-ui-reveal]')].filter((el) => Number(getComputedStyle(el).opacity) < 0.99).length);
    expect(hidden).toBe(0);
  });

  test('touch targets on a phone are at least 24 × 24 px (WCAG 2.5.8)', async ({ page }, info) => {
    test.skip(!PHONE.test(info.project.name), 'phone only');
    const small = [];
    for (const path of ['/ar/', '/ar/jo/menu/', '/ar/jo/locations/', '/ar/contact/', '/ar/careers/']) {
      await page.goto(path, { waitUntil: 'networkidle' });
      small.push(...await page.evaluate((p) => [...document.querySelectorAll('a[href], button, input:not([type="hidden"]), select, textarea')]
        .filter((el) => {
          // A stretched action covers its whole card: the card is the target.
          const r = (el.classList.contains('ui-stretched') ? el.closest('.ui-card, .ui-branch, article') ?? el : el).getBoundingClientRect();
          if (r.width === 0 || r.height === 0 || getComputedStyle(el).visibility === 'hidden') return false;
          if (el.closest('.ui-apply__hp, dialog:not([open]), .ui-skip-link')) return false;
          if (el.matches('input[type="radio"], input[type="checkbox"]') && el.closest('label')) return false; // the label is the target
          // A visually hidden file input is reached through its (large) label.
          if (el.matches('input[type="file"]') && el.id && document.querySelector(`label[for="${el.id}"]`)?.getBoundingClientRect().height >= 24) return false;
          const inline = el.tagName === 'A' && getComputedStyle(el).display === 'inline' && el.closest('p, li > p, .ui-prose');
          return !inline && (r.width < 24 || r.height < 24);
        })
        .map((el) => `${p} ${el.tagName.toLowerCase()}.${[...el.classList].join('.')} ${Math.round(el.getBoundingClientRect().width)}×${Math.round(el.getBoundingClientRect().height)}`), path));
    }
    // A stretched action really receives a tap anywhere on its card.
    await page.goto('/ar/jo/menu/', { waitUntil: 'networkidle' });
    const card = page.locator('.ui-product-card').first();
    const box = await card.boundingBox();
    if (box !== null) {
      const hit = await page.evaluate(([x, y]) => document.elementFromPoint(x, y)?.closest('.ui-product-card__action, .ui-product-card') !== null, [box.x + box.width * 0.8, box.y + box.height - 4]);
      expect(hit, 'the card corner opens the item').toBe(true);
    }
    expect(small).toEqual([]);
  });
});
