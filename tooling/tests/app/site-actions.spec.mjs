// Visitor actions against the running app (SHELTER_BASE_URL), in Arabic and English on a phone and a desktop project:
// the contact page's call / WhatsApp / email links carry the numbers shown with them; a branch page's Directions link is
// the branch's Google Maps link (the same in every place on the page and — when SHELTER_APP_DIR is set — the one in
// the approved master data, read-only) and opens in a new tab without a way back to the opener; the phone action bar
// stays at the bottom of the screen. Then, as the LOCAL test owner on the desktop project (secrets from files, see
// owner.mjs): an announcement for the top bar is created in Content → Announcements, shows on /ar/ and /en/ with its
// link, is stopped from «Active now» (/dashboard/live) and is gone from the site; it is archived afterwards (never
// deleted). External hosts (Google Maps, WhatsApp) are answered locally by the test — no request leaves the machine.
import { execFileSync } from 'node:child_process';
import { test, expect } from '@playwright/test';
import { OWNER_READY, signIn, openDashboard } from './owner.mjs';

const APP = Boolean(process.env.SHELTER_BASE_URL);
const PHONE = /-m390$/;
const DESKTOP = /-d1440$/;
const LOCALES = ['ar', 'en'];
const MAPS = /^https:\/\/(maps\.app\.goo\.gl|goo\.gl\/maps|(www\.)?google\.[a-z.]+\/maps|maps\.google\.[a-z.]+)\//;

/** Collects script errors and console errors while a journey runs. */
function watch(page) {
  const problems = [];
  page.on('pageerror', (e) => problems.push(`script: ${e.message}`));
  page.on('console', (m) => { if (m.type() === 'error' && !/status of 404/.test(m.text())) problems.push(`console: ${m.text()}`); });
  return problems;
}

/** A value from the app's approved master data (read-only, via tinker), or null without SHELTER_APP_DIR. */
function appValue(php) {
  if (!process.env.SHELTER_APP_DIR) return null;
  const out = execFileSync('php', ['artisan', 'tinker', `--execute=echo json_encode(${php});`],
    { cwd: process.env.SHELTER_APP_DIR, encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'] });
  return JSON.parse(out.trim().split('\n').pop() ?? 'null');
}

const digits = (value) => (value ?? '').replace(/\D/g, '');

/** The branch pages linked from the locations page (nothing assumed about how many there are). */
async function branchPaths(page, locale) {
  await page.goto(`/${locale}/jo/locations/`);
  const hrefs = await page.locator('main a[href]').evaluateAll((links) => links.map((a) => new URL(a.href).pathname));
  return [...new Set(hrefs.filter((p) => new RegExp(`^/${locale}/jo/locations/[a-z0-9-]+/[a-z0-9-]+/$`).test(p)))];
}

/** Clicks a target=_blank link with its external host answered locally; returns the new tab. */
async function openInNewTab(page, link) {
  const context = page.context();
  await context.route(/^https:\/\/(maps\.app\.goo\.gl|goo\.gl|(www\.)?google\.[a-z.]+|maps\.google\.[a-z.]+|wa\.me|api\.whatsapp\.com)\//,
    (route) => route.fulfill({ status: 200, contentType: 'text/html', body: '<!doctype html><title>external</title>' }));
  const [popup] = await Promise.all([page.waitForEvent('popup'), link.click()]);
  await popup.waitForLoadState('domcontentloaded');
  return popup;
}

test.describe('site actions @app @journey', () => {
  test.skip(!APP, 'set SHELTER_BASE_URL to run against the app');
  test.beforeEach(({}, info) => test.skip(!PHONE.test(info.project.name) && !DESKTOP.test(info.project.name), 'journeys run on a phone and a desktop'));

  for (const locale of LOCALES) {
    test(`${locale} contact page: each call link dials the number shown with it; WhatsApp and email are safe and consistent`, async ({ page }) => {
      const problems = watch(page);
      await page.goto(`/${locale}/contact/`, { waitUntil: 'networkidle' });
      const calls = await page.locator('main a[href^="tel:"]').evaluateAll((links) => links.map((a) => ({ href: a.getAttribute('href'), shown: a.textContent })));
      expect(calls.length, 'at least one number to call').toBeGreaterThan(0);
      for (const { href, shown } of calls) {
        expect(href, 'international tel: link').toMatch(/^tel:\+\d{8,15}$/);
        const national = digits(shown).replace(/^0+/, '');
        expect(national.length, `${href}: a number is shown with the link`).toBeGreaterThanOrEqual(7);
        expect(digits(href).endsWith(national), `${href} dials the number shown (${shown?.trim()})`).toBe(true);
      }
      const whatsapp = page.locator('main a[href*="wa.me/"], main a[href*="api.whatsapp.com/"]');
      const approved = appValue("app(App\\Services\\MasterData\\MasterData::class)->contact(App\\Enums\\ContactKind::Whatsapp)?->value");
      for (const link of await whatsapp.all()) {
        const href = (await link.getAttribute('href')) ?? '';
        expect(href).toMatch(/^https:\/\/wa\.me\/\d{8,15}$/);
        await expect(link).toHaveAttribute('target', '_blank');
        expect((await link.getAttribute('rel')) ?? '', 'rel noopener').toMatch(/\bnoopener\b/);
        if (approved !== null) expect(digits(href), 'the approved WhatsApp number').toBe(digits(approved));
      }
      if ((await whatsapp.count()) > 0) {
        const popup = await openInNewTab(page, whatsapp.first());
        expect(popup.url()).toBe(await whatsapp.first().getAttribute('href'));
        expect(await popup.evaluate(() => window.opener), 'no way back to the opener').toBeNull();
        await popup.close();
      }
      for (const mail of await page.locator('main a[href^="mailto:"]').all()) {
        const address = ((await mail.getAttribute('href')) ?? '').slice('mailto:'.length);
        await expect(mail, 'the address shown is the address written to').toContainText(address);
      }
      expect(problems).toEqual([]);
    });

    test(`${locale} branch pages: Directions is the branch's Maps link and opens in a new tab safely`, async ({ page }) => {
      const problems = watch(page);
      const paths = await branchPaths(page, locale);
      expect(paths.length, 'branch pages linked from the locations page').toBeGreaterThan(0);
      for (const path of paths) {
        await page.goto(path, { waitUntil: 'networkidle' });
        // Every new-tab link of the page (the place column and the action bar) other than WhatsApp is Directions.
        const external = page.locator('main a[href][target="_blank"]');
        const hrefs = [...new Set((await external.evaluateAll((links) => links.map((a) => a.getAttribute('href') ?? ''))).filter((h) => !/wa\.me|whatsapp/.test(h)))];
        test.info().annotations.push({ type: 'branch', description: `${path} → ${hrefs.join(', ') || 'no Maps link yet'}` });
        if (hrefs.length === 0) continue; // Directions shows once the Owner saves the Maps link (PO-010)
        expect(hrefs, `${path}: one Maps link everywhere on the page`).toHaveLength(1);
        expect(hrefs[0], `${path}: a Google Maps address`).toMatch(MAPS);
        const slug = path.split('/').filter(Boolean).pop() ?? '';
        const approved = appValue(`app(App\\Services\\MasterData\\MasterData::class)->branchField(App\\Models\\Branch::query()->where('slug', '${slug.replace(/[^a-z0-9-]/g, '')}')->firstOrFail(), 'maps_url')`);
        if (approved !== null) expect(hrefs[0], `${path}: the approved Maps link of this branch`).toBe(approved);
        const links = page.locator(`a[href="${hrefs[0]}"]`);
        for (const link of await links.all()) {
          await expect(link).toHaveAttribute('target', '_blank');
          expect((await link.getAttribute('rel')) ?? '', 'rel noopener').toMatch(/\bnoopener\b/);
        }
        const visible = links.filter({ visible: true }).first();
        const popup = await openInNewTab(page, visible);
        expect(popup.url()).toBe(hrefs[0]);
        expect(await popup.evaluate(() => window.opener), 'no way back to the opener').toBeNull();
        expect(new URL(page.url()).pathname, 'the branch page stays open').toBe(path);
        await popup.close();
      }
      expect(problems).toEqual([]);
    });

    test(`${locale} branch page action bar: at the bottom of a phone screen, aside on a desktop`, async ({ page }, info) => {
      const [path] = await branchPaths(page, locale);
      test.skip(path === undefined, 'no branch page');
      await page.goto(path, { waitUntil: 'networkidle' });
      const bar = page.locator('.ui-action-bar');
      test.skip((await bar.count()) === 0, 'no actions saved for this branch yet');
      if (!PHONE.test(info.project.name)) {
        await expect(bar, 'from 1024px the contact column carries the actions').toBeHidden();
        return;
      }
      const viewport = page.viewportSize() ?? { width: 390, height: 844 };
      const atBottom = async (where) => {
        const box = await bar.boundingBox();
        expect(box, where).not.toBeNull();
        expect(Math.abs((box?.y ?? 0) + (box?.height ?? 0) - viewport.height), `${where}: the bar sits on the bottom edge`).toBeLessThanOrEqual(2);
      };
      await expect(bar).toBeVisible();
      await atBottom('top of the page');
      // Half-way through the branch content (its end still below the screen): the bar stays on the bottom edge.
      const room = await page.evaluate(() => (document.querySelector('.ui-page')?.getBoundingClientRect().bottom ?? 0) + window.scrollY - window.innerHeight);
      if (room > 40) {
        await page.evaluate((y) => window.scrollTo(0, y), Math.floor(room / 2));
        await page.waitForTimeout(150);
        await atBottom('mid-page');
      }
      // Its actions are the page's own: the same call number and Maps link as the contact column.
      const actions = await bar.locator('a[href]').evaluateAll((links) => links.map((a) => a.getAttribute('href') ?? ''));
      expect(actions.length).toBeGreaterThan(0);
      for (const href of actions) {
        expect(await page.locator(`main a[href="${href}"]`).count(), `${href} is also in the page`).toBeGreaterThan(0);
      }
      for (const box of await bar.locator('a[href]').evaluateAll((links) => links.map((a) => a.getBoundingClientRect().height))) {
        expect(box, 'a full touch target').toBeGreaterThanOrEqual(44);
      }
      // At the very end the bar does not cover the last of the content (sticky in the flow, not fixed).
      await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
      await page.waitForTimeout(150);
      const covered = await page.evaluate(() => {
        const last = [...document.querySelectorAll('main .ui-container > *')].pop();
        const bar = document.querySelector('.ui-action-bar');
        if (!last || !bar) return false;
        return last.getBoundingClientRect().bottom > bar.getBoundingClientRect().top + 1;
      });
      expect(covered, 'the end of the content stays above the bar').toBe(false);
    });
  }
});

test.describe('announcement top bar from the Owner dashboard @app @journey @dashboard', () => {
  test.skip(!APP, 'set SHELTER_BASE_URL to run against the app');
  test.skip(!OWNER_READY, 'needs a local test owner (SHELTER_OWNER_EMAIL, SHELTER_OWNER_PASSWORD_FILE, SHELTER_OWNER_TOTP_FILE)');
  test.beforeEach(({}, info) => test.skip(!DESKTOP.test(info.project.name), 'one desktop run (sign-ins consume codes)'));
  test.setTimeout(150_000);

  test('created for the top bar it shows on /ar/ and /en/ with its link; «Stop now» takes it off the site', async ({ page, browser, baseURL }) => {
    const problems = watch(page);
    await signIn(page);
    const stamp = `${Date.now().toString(36)}`.toUpperCase();
    const text = {
      ar: { title: `إعلان اختبار QA-${stamp}`, body: 'نص قصير من اختبار آلي', label: 'رابط الاختبار' },
      en: { title: `Test announcement QA-${stamp}`, body: 'Short text from an automated test', label: 'Test link' },
    };
    const amman = (ms) => {
      const parts = Object.fromEntries(new Intl.DateTimeFormat('en-GB', { timeZone: 'Asia/Amman', year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', hourCycle: 'h23' })
        .formatToParts(new Date(ms)).map((p) => [p.type, p.value]));
      return { date: `${parts.year}-${parts.month}-${parts.day}`, time: `${parts.hour}:${parts.minute}` };
    };
    const starts = amman(Date.now() - 10 * 60_000);
    const ends = amman(Date.now() + 24 * 3_600_000);

    await openDashboard(page, '/dashboard/content/announcements/new');
    await page.locator('#status-published').check({ force: true });
    await page.locator('#type-announcement').check({ force: true });
    await page.locator('#placement-top_bar').check({ force: true });
    for (const locale of LOCALES) {
      await page.locator(`#title_${locale}`).fill(text[locale].title);
      await page.locator(`#body_${locale}`).fill(text[locale].body);
      await page.locator(`#cta_label_${locale}`).fill(text[locale].label);
    }
    await page.locator('#cta_url').fill('/ar/jo/menu/');
    await page.locator('#starts_date').fill(starts.date);
    await page.locator('#starts_time').fill(starts.time);
    await page.locator('#ends_date').fill(ends.date);
    await page.locator('#ends_time').fill(ends.time);
    await page.locator('#level').selectOption('urgent'); // the highest level, so nothing else takes the place in this test
    await page.locator('form.ui-editor button[type="submit"]').click();
    await page.waitForURL(/\/dashboard\/content\/announcements\/\d+$/);
    await expect(page.locator('.ui-error-summary')).toHaveCount(0);
    const id = new URL(page.url()).pathname.split('/').pop();
    await expect(page.locator(`form[action$="/announcements/${id}/pause"]`), 'live: it can be paused').toHaveCount(1);
    await expect(page.locator('.ui-preview .ui-announcement').first(), 'the preview shows the bar').toBeVisible();

    // The public site (a visitor, not signed in).
    const visitor = await browser.newContext({ baseURL });
    const site = await visitor.newPage();
    for (const locale of LOCALES) {
      await site.goto(`/${locale}/`);
      const bar = site.locator(`.ui-announcement[data-experience="${id}"]`);
      await expect(bar, `/${locale}/ shows the bar`).toBeVisible();
      await expect(bar).toContainText(text[locale].title);
      await expect(bar).toContainText(text[locale].body);
      const link = bar.locator('.ui-announcement__link');
      await expect(link).toHaveText(text[locale].label);
      expect(new URL((await link.getAttribute('href')) ?? '', baseURL).pathname, 'the link follows the page language').toBe(`/${locale}/jo/menu/`);
      // It is on every page (top bar), above the header.
      await site.goto(`/${locale}/contact/`);
      await expect(site.locator(`.ui-announcement[data-experience="${id}"]`)).toBeVisible();
    }
    await site.goto('/en/');
    await site.locator(`.ui-announcement[data-experience="${id}"] .ui-announcement__link`).click();
    await site.waitForURL(/\/en\/jo\/menu\/$/);

    // «Active now»: it is the one showing in the top bar; «Stop now» takes it off at once.
    await openDashboard(page, '/dashboard/live');
    const stop = page.locator(`form[action$="/dashboard/live/${id}/disable"] button[type="submit"]`);
    await expect(stop).toHaveCount(1);
    await expect(page.locator(`.ui-live-item:has(form[action$="/dashboard/live/${id}/disable"]) a[href$="/dashboard/content/announcements/${id}"]`)).toBeVisible();
    await stop.click();
    await expect(page.locator(`form[action$="/dashboard/live/${id}/disable"]`)).toHaveCount(0);
    for (const locale of LOCALES) {
      await site.goto(`/${locale}/`);
      await expect(site.locator(`.ui-announcement[data-experience="${id}"]`), `/${locale}/ no longer shows it`).toHaveCount(0);
      await expect(site.locator('body')).not.toContainText(`QA-${stamp}`);
    }
    await visitor.close();

    // Clean up: archive it (never deleted — "archive instead of hard delete").
    await openDashboard(page, `/dashboard/content/announcements/${id}`);
    const archive = page.locator(`form[action$="/announcements/${id}/archive"] button[type="submit"]`);
    if (await archive.count()) {
      await archive.click();
      await expect(page.locator(`form[action$="/announcements/${id}/restore"]`)).toHaveCount(1);
    }
    expect(problems).toEqual([]);
  });
});
