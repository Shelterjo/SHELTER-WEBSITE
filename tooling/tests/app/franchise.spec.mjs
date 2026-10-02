// Franchise & partnerships (SI-B12) — the FRAN-099 flows against the running app (SHELTER_BASE_URL), in Arabic and
// English on a phone and a desktop project: the page opens, the language switch keeps it, the hero CTA leads to the
// form, FAQ answers open and close (keyboard too), validation with a focused error summary, the conditional «أخرى /
// Other» description, a form-level error (an expired form keeps what was typed), a complete application with its
// FR-YYYY-NNNNN number — then, as the LOCAL test owner on the desktop project (secrets from files, see owner.mjs), the
// request in Requests → Partnerships, opened; an outsider is sent to the sign-in page. Earlier runs from this address
// count against the real limits: a local run clears the app's cache first when SHELTER_APP_DIR is set (as journeys).
import { execSync } from 'node:child_process';
import { test, expect } from '@playwright/test';
import { OWNER_READY, signIn, openDashboard } from './owner.mjs';

const APP = Boolean(process.env.SHELTER_BASE_URL);
const PHONE = /-m390$/;
const DESKTOP = /-d1440$/;
const LOCALES = ['ar', 'en'];
const MIN_FILL_MS = 8500; // the minimum fill time of a real person (config franchise.abuse.min_fill_seconds = 8)

/** Collects script errors and console errors while a journey runs. */
function watch(page) {
  const problems = [];
  page.on('pageerror', (e) => problems.push(`script: ${e.message}`));
  page.on('console', (m) => { if (m.type() === 'error' && !/status of 404/.test(m.text())) problems.push(`console: ${m.text()}`); });
  return problems;
}

const resetLimits = () => { if (process.env.SHELTER_APP_DIR) execSync('php artisan cache:clear -q', { cwd: process.env.SHELTER_APP_DIR }); };

async function openFranchise(page, locale, hash = '') {
  const response = await page.goto(`/${locale}/franchise/${hash}`, { waitUntil: 'networkidle' });
  expect(response?.status(), `/${locale}/franchise/`).toBe(200);
}

const formOpen = async (page) => (await page.locator('form[data-franchise-form]').count()) > 0;

/** A sample applicant for this run (test data only — never business data). */
function sample(locale) {
  const stamp = `${Date.now().toString(36)}${Math.floor(Math.random() * 1296).toString(36)}`.toUpperCase();
  const digits = String(Math.floor(Math.random() * 10_000_000)).padStart(7, '0');
  return {
    stamp,
    name: locale === 'ar' ? `اختبار شراكة QA-${stamp}` : `Partnership Test QA-${stamp}`,
    phone: `079${digits}`,
    email: `qa.franchise.${stamp.toLowerCase()}@example.test`,
    city: locale === 'ar' ? 'مدينة اختبار' : 'Test city',
    market: locale === 'ar' ? 'سوق اختبار' : 'Test market',
    introduction: locale === 'ar' ? 'رسالة تعريفية من اختبار آلي — FRAN-099.' : 'Introduction sent by an automated test — FRAN-099.',
  };
}

/** The first real option of a select (whatever the page offers — nothing assumed). */
async function chooseFirst(page, selector) {
  const value = await page.locator(`${selector} option:not([value=""])`).first().getAttribute('value');
  await page.locator(selector).selectOption(value ?? '');
}

async function fillApplication(page, who) {
  await page.locator('#full_name').fill(who.name);
  await page.locator('#phone').fill(who.phone);
  await page.locator('#email').fill(who.email);
  await chooseFirst(page, '#country');
  await page.locator('#city').fill(who.city);
  await page.locator('#market').fill(who.market);
  await chooseFirst(page, '#experience_band');
  await page.locator('#owns_business-no').check({ force: true });
  await page.locator('#partnership_interest_type-general_interest').check({ force: true });
  await page.locator('#location_status-not_started').check({ force: true });
  await page.locator('#introduction').fill(who.introduction);
  await page.locator('#non_binding_acknowledgement').check({ force: true });
  await page.locator('#data_processing_consent').check({ force: true });
}

test.describe('franchise page @app @journey @franchise', () => {
  test.skip(!APP, 'set SHELTER_BASE_URL to run against the app');
  test.beforeEach(({}, info) => test.skip(!PHONE.test(info.project.name) && !DESKTOP.test(info.project.name), 'journeys run on a phone and a desktop'));

  for (const locale of LOCALES) {
    const other = locale === 'ar' ? 'en' : 'ar';

    test(`${locale} the page opens: language, title, one h1, FAQ data only for published answers`, async ({ page }) => {
      const problems = watch(page);
      await openFranchise(page, locale);
      await expect(page.locator('html')).toHaveAttribute('lang', locale);
      await expect(page.locator('html')).toHaveAttribute('dir', locale === 'ar' ? 'rtl' : 'ltr');
      expect((await page.title()).trim().length, 'title').toBeGreaterThan(0);
      await expect(page.locator('h1')).toHaveCount(1);
      await expect(page.locator('[data-franchise-hero] h1')).toBeVisible();
      const answers = await page.locator('details.ui-prose__faq').count();
      const faqData = await page.locator('script[type="application/ld+json"]').evaluateAll((scripts) => scripts
        .map((s) => JSON.parse(s.textContent ?? '{}')).filter((d) => d['@type'] === 'FAQPage').map((d) => d.mainEntity.length));
      expect(faqData, 'FAQPage structured data = the answers on the page').toEqual(answers > 0 ? [answers] : []);
      expect(problems).toEqual([]);
    });

    test(`${locale} the language switch keeps the franchise page`, async ({ page }) => {
      await openFranchise(page, locale);
      const link = page.locator(`a[hreflang="${other}"]`).first();
      expect(new URL((await link.getAttribute('href')) ?? '').pathname).toBe(`/${other}/franchise/`);
      if (await link.isVisible()) await link.click();
      else await page.goto((await link.getAttribute('href')) ?? '');
      await page.waitForURL(new RegExp(`/${other}/franchise/$`));
      await expect(page.locator('html')).toHaveAttribute('lang', other);
      await expect(page.locator('h1')).toHaveCount(1);
      // And back again.
      expect(new URL((await page.locator(`a[hreflang="${locale}"]`).first().getAttribute('href')) ?? '').pathname).toBe(`/${locale}/franchise/`);
    });

    test(`${locale} the hero CTA leads to the application; the next Tab lands in it`, async ({ page }) => {
      await openFranchise(page, locale);
      const cta = page.locator('[data-franchise-hero] .ui-franchise__actions a').first();
      const href = (await cta.getAttribute('href')) ?? '';
      expect(href, 'the hero CTA is an in-page link').toMatch(/^#(apply|franchise-contact)$/);
      await cta.click();
      await expect.poll(() => new URL(page.url()).hash).toBe(href);
      const target = page.locator(href);
      await expect(target).toBeInViewport();
      // The heading of the target is not hidden under a sticky bar.
      const heading = target.locator('h2').first();
      if (await heading.count()) await expect(heading).toBeInViewport();
      await page.keyboard.press('Tab');
      expect(await page.evaluate((id) => document.getElementById(id)?.contains(document.activeElement) ?? false, href.slice(1)), 'focus continues inside the target').toBe(true);
    });

    test(`${locale} FAQ answers open and close with a click, Enter and Space, and a link opens its answer`, async ({ page }) => {
      await openFranchise(page, locale);
      const items = page.locator('details.ui-prose__faq');
      test.skip((await items.count()) === 0, 'no published FAQ answers');
      const item = items.first();
      const summary = item.locator('summary');
      const isOpen = () => item.evaluate((d) => d.open);
      expect(await isOpen(), 'closed at first').toBe(false);
      await summary.click();
      await expect.poll(isOpen).toBe(true);
      await expect(item.locator('.ui-disclosure__body')).toBeVisible();
      await summary.click();
      await expect.poll(isOpen).toBe(false);
      await expect(item.locator('.ui-disclosure__body')).toBeHidden();
      await summary.focus();
      await page.keyboard.press('Enter');
      await expect.poll(isOpen).toBe(true);
      await page.keyboard.press('Space');
      await expect.poll(isOpen).toBe(false);
      // A link to one answer (#q-N, used by the site search) opens it.
      const id = await items.last().getAttribute('id');
      await page.goto('about:blank'); // a fresh load, not an in-page jump
      await openFranchise(page, locale, `#${id}`);
      await expect.poll(() => page.locator(`#${id}`).evaluate((d) => d.open)).toBe(true);
      await expect(page.locator(`#${id} summary`)).toBeInViewport();
    });

    test(`${locale} validation: the error summary takes focus, lists each field and keeps what was typed`, async ({ page }) => {
      resetLimits();
      const problems = watch(page);
      await openFranchise(page, locale);
      test.skip(!(await formOpen(page)), 'the form is closed in this environment');
      const who = sample(locale);
      await page.locator('#full_name').fill(who.name);
      await page.locator('#partnership_interest_type-other').check({ force: true }); // "other" without its description
      await page.waitForTimeout(MIN_FILL_MS);
      await page.locator('[data-franchise-submit]').click();
      const summary = page.locator('#apply .ui-error-summary');
      await expect(summary).toBeFocused();
      await expect(page.locator('form[data-franchise-form] .ui-alert'), 'not taken for a bot or an expired form').toHaveCount(0);
      const targets = await summary.locator('a[href^="#"]').evaluateAll((links) => links.map((a) => a.getAttribute('href')?.slice(1)));
      for (const field of ['phone', 'email', 'country', 'city', 'market', 'experience_band', 'owns_business', 'partnership_interest_other', 'location_status', 'introduction', 'non_binding_acknowledgement', 'data_processing_consent']) {
        expect(targets, `the summary names ${field}`).toContain(field);
      }
      expect(targets, 'a filled field is not reported').not.toContain('full_name');
      for (const id of ['phone', 'email', 'introduction', 'partnership_interest_other']) {
        await expect(page.locator(`#${id}`), id).toHaveAttribute('aria-invalid', 'true');
      }
      // What was typed and chosen stays; the description is shown again because "other" is still chosen.
      await expect(page.locator('#full_name')).toHaveValue(who.name);
      await expect(page.locator('#partnership_interest_type-other')).toBeChecked();
      await expect(page.locator('#partnership_interest_other')).toBeVisible();
      // Each summary link leads to its field.
      const link = summary.locator('a[href="#email"]');
      await link.click();
      await expect(page.locator('#email')).toBeFocused();
      expect(problems).toEqual([]);
    });

    test(`${locale} the «Other» description appears only while «Other» is chosen`, async ({ page }) => {
      await openFranchise(page, locale);
      test.skip(!(await formOpen(page)), 'the form is closed in this environment');
      const description = page.locator('#partnership_interest_other');
      await expect(description).toBeHidden();
      await page.locator('#partnership_interest_type-other').check({ force: true });
      await expect(description).toBeVisible();
      await expect(description).toBeEnabled();
      await description.fill(locale === 'ar' ? 'وصف من اختبار آلي' : 'Described by an automated test');
      await page.locator('#partnership_interest_type-single_location').check({ force: true });
      await expect(description).toBeHidden();
      await expect(description, 'a stale hidden value never travels').toBeDisabled();
      await page.locator('#partnership_interest_type-other').check({ force: true });
      await expect(description).toBeVisible();
      await expect(description).toBeEnabled();
    });

    test(`${locale} a form-level error (an expired form) is shown, what was typed is kept and the form is ready again`, async ({ page }) => {
      resetLimits();
      await openFranchise(page, locale);
      test.skip(!(await formOpen(page)), 'the form is closed in this environment');
      const who = sample(locale);
      await fillApplication(page, who);
      // A tab left open past the form's lifetime: its signed time token no longer reads (FormGuard → "expired").
      await page.locator('input[name="form_token"]').evaluate((input) => { input.value = 'expired-by-qa'; });
      await page.locator('[data-franchise-submit]').click();
      await expect(page.locator('#apply .ui-error-summary')).toBeFocused();
      await expect(page.locator('form[data-franchise-form] .ui-alert')).toBeVisible();
      expect(new URL(page.url()).pathname, 'still on the page, nothing sent').toBe(`/${locale}/franchise/`);
      await expect(page.locator('#full_name')).toHaveValue(who.name);
      await expect(page.locator('#email')).toHaveValue(who.email);
      await expect(page.locator('#introduction')).toHaveValue(who.introduction);
      await expect(page.locator('#non_binding_acknowledgement')).toBeChecked();
      await expect(page.locator('#data_processing_consent')).toBeChecked();
      await expect(page.locator('input[name="form_token"]'), 'a fresh form token').not.toHaveValue('expired-by-qa');
      await expect(page.locator('[data-franchise-submit]')).toBeEnabled();
    });

    // FRAN-099 "Form error": a failure while the application is SAVED (the controller's catch → franchise.errors.generic)
    // needs a failing database write. It cannot be caused safely from the browser against a shared app, so it is not
    // automated here; the expired-form case above covers how a form-level error is shown.
    test.skip(`${locale} a failure while saving shows the generic error (needs a failing database write)`, async () => {});
  }

  test('phone: the compact CTA bar appears once the hero is gone and steps aside at the form', async ({ page }, info) => {
    test.skip(!PHONE.test(info.project.name), 'the bar is the phone CTA');
    await openFranchise(page, 'ar');
    const bar = page.locator('[data-franchise-bar]');
    await expect(bar).toHaveAttribute('data-state', 'hidden');
    await page.evaluate(() => {
      const hero = document.querySelector('[data-franchise-hero]');
      window.scrollTo(0, (hero?.getBoundingClientRect().bottom ?? 0) + window.scrollY + 200);
    });
    await expect(bar).toHaveAttribute('data-state', 'shown');
    await expect(bar).toBeInViewport();
    const target = (await bar.locator('a').getAttribute('href')) ?? '#apply';
    await page.locator(target).scrollIntoViewIfNeeded();
    await expect(bar).toHaveAttribute('data-state', 'hidden');
  });
});

test.describe('franchise application to the Owner dashboard @app @journey @franchise', () => {
  test.skip(!APP, 'set SHELTER_BASE_URL to run against the app');
  test.describe.configure({ mode: 'serial' });
  test.beforeEach(({}, info) => test.skip(!PHONE.test(info.project.name) && !DESKTOP.test(info.project.name), 'journeys run on a phone and a desktop'));
  test.setTimeout(120_000);

  /** Applications sent by this run (this worker): { locale, name, stamp, number }. */
  const sent = [];
  let requestUrl = '';

  for (const locale of LOCALES) {
    test(`${locale} a complete application lands on the success page with its FR number (sent once on a double click)`, async ({ page }) => {
      resetLimits();
      const problems = watch(page);
      await openFranchise(page, locale);
      test.skip(!(await formOpen(page)), 'the form is closed in this environment');
      const who = sample(locale);
      await fillApplication(page, who);
      await page.waitForTimeout(MIN_FILL_MS);
      await page.locator('[data-franchise-submit]').dblclick();
      await page.waitForURL(new RegExp(`/${locale}/franchise/submitted/$`));
      await expect(page.locator('html')).toHaveAttribute('lang', locale);
      await expect(page.locator('h1')).toHaveCount(1);
      await expect(page.locator('h1')).toBeFocused();
      const number = ((await page.locator('.ui-apply-done__number').textContent()) ?? '').trim();
      expect(number).toMatch(new RegExp(`^FR-${new Date().getFullYear()}-\\d{5}$`));
      expect(page.url(), 'nothing personal and no number in the address').not.toMatch(/FR-|QA-|@/);
      // The number comes from the session once: a refresh leads back to the page, never to a stale number.
      await page.reload();
      expect(new URL(page.url()).pathname).toBe(`/${locale}/franchise/`);
      expect(problems).toEqual([]);
      sent.push({ locale, name: who.name, stamp: who.stamp, number });
    });
  }

  test('the Owner sees each new request in Requests → Partnerships, once, and opens it', async ({ page }, info) => {
    test.skip(!DESKTOP.test(info.project.name), 'one desktop run (sign-ins consume codes)');
    test.skip(!OWNER_READY, 'needs a local test owner (SHELTER_OWNER_EMAIL, SHELTER_OWNER_PASSWORD_FILE, SHELTER_OWNER_TOTP_FILE)');
    test.skip(sent.length === 0, 'no application was sent in this run');
    await signIn(page);
    for (const application of sent) {
      await openDashboard(page, `/dashboard/requests/partnerships?q=${encodeURIComponent(`QA-${application.stamp}`)}`);
      await expect(page.locator('h1')).toHaveCount(1);
      const rows = page.locator('.ui-inbox-table tbody tr');
      await expect(rows, `${application.number}: exactly one request for one (double-clicked) send`).toHaveCount(1);
      await expect(rows.first(), 'marked new until opened').toHaveClass(/ui-inbox-row--new/);
      await expect(rows.first().locator('.ui-inbox-name .ui-badge')).toBeVisible();
      const link = rows.first().locator('a[href*="/dashboard/requests/partnerships/"]');
      await expect(link).toHaveText(application.name);
      // The same request is found by its number.
      await openDashboard(page, `/dashboard/requests/partnerships?q=${encodeURIComponent(application.number)}`);
      await expect(page.locator('.ui-inbox-table tbody tr')).toHaveCount(1);
      await page.locator('.ui-inbox-table tbody tr a[href*="/dashboard/requests/partnerships/"]').click();
      await page.waitForURL(/\/dashboard\/requests\/partnerships\/\d+$/);
      await expect(page.locator('h1')).toHaveText(application.name);
      await expect(page.locator('main')).toContainText(application.number);
      await expect(page.locator('form[action$="/status"] select[name="status"]'), 'the stage can be changed by hand').toBeVisible();
      requestUrl = page.url();
      // Opened → no longer new.
      await openDashboard(page, `/dashboard/requests/partnerships?q=${encodeURIComponent(application.number)}`);
      await expect(page.locator('.ui-inbox-table tbody tr').first()).not.toHaveClass(/ui-inbox-row--new/);
    }
  });

  test('an outsider who opens a dashboard request is sent to the sign-in page', async ({ browser, baseURL }) => {
    const context = await browser.newContext({ baseURL });
    const page = await context.newPage();
    const paths = ['/dashboard/requests/partnerships', requestUrl === '' ? '/dashboard/requests/partnerships/1' : new URL(requestUrl).pathname];
    for (const path of paths) {
      await page.goto(path);
      expect(new URL(page.url()).pathname, path).toBe('/dashboard/login');
      await expect(page.locator('body'), 'nothing of the request leaks').not.toContainText(/FR-\d{4}-\d{5}/);
    }
    await context.close();
  });
});
