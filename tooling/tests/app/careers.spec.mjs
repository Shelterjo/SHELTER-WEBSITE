// Careers (SI-B11) — the CAREERS-094 flows not yet automated, against the running app (SHELTER_BASE_URL): a complete
// application in Arabic (the form is Arabic only) with its CV uploaded on its own, the JOB-YYYY-NNNNN number on the
// success page, the tracking page finding it by number + phone (and the same answer for a wrong phone); then, as the
// LOCAL test owner on the desktop project (secrets from files, see owner.mjs): the "new" badge, the live search by
// phone, a filter, the quick view, the full application, a status change with a note, an interview, archive and
// restore, the export screen (behind the re-confirmation) and the applicant's tracking showing the Owner's change.
// Earlier runs from this address count against the real limits: a local run clears the app's cache first when
// SHELTER_APP_DIR is set (as journeys.spec.mjs). Sample applicants only — never business data.
import { execSync } from 'node:child_process';
import { test, expect } from '@playwright/test';
import { OWNER_READY, signIn, openDashboard } from './owner.mjs';

const APP = Boolean(process.env.SHELTER_BASE_URL);
const PHONE = /-m390$/;
const DESKTOP = /-d1440$/;
const MIN_FILL_MS = 8500; // the minimum fill time of a real person (config careers.abuse.min_fill_seconds = 8)

/** Collects script errors and console errors while a journey runs. */
function watch(page) {
  const problems = [];
  page.on('pageerror', (e) => problems.push(`script: ${e.message}`));
  page.on('console', (m) => { if (m.type() === 'error' && !/status of 404/.test(m.text())) problems.push(`console: ${m.text()}`); });
  return problems;
}

const resetLimits = () => { if (process.env.SHELTER_APP_DIR) execSync('php artisan cache:clear -q', { cwd: process.env.SHELTER_APP_DIR }); };

/** A small valid one-page PDF whose text local tools can read (same sample as journeys.spec.mjs). */
function samplePdf() {
  const stream = 'BT /F1 12 Tf 72 720 Td (Education Experience Skills) Tj ET';
  const objects = ['<< /Type /Catalog /Pages 2 0 R >>', '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
    '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>',
    `<< /Length ${stream.length} >>\nstream\n${stream}\nendstream`, '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>'];
  let pdf = '%PDF-1.4\n';
  const offsets = objects.map((o, i) => { const at = pdf.length; pdf += `${i + 1} 0 obj\n${o}\nendobj\n`; return at; });
  const xref = pdf.length;
  pdf += `xref\n0 ${objects.length + 1}\n0000000000 65535 f \n${offsets.map((o) => `${String(o).padStart(10, '0')} 00000 n \n`).join('')}trailer\n<< /Size ${objects.length + 1} /Root 1 0 R >>\nstartxref\n${xref}\n%%EOF`;
  return Buffer.from(pdf);
}

/** The first real option of a select (whatever the page offers — nothing assumed). */
async function chooseOption(page, selector, index = 0) {
  const options = page.locator(`${selector} option:not([value=""])`);
  const value = await options.nth(Math.min(index, (await options.count()) - 1)).getAttribute('value');
  await page.locator(selector).selectOption(value ?? '');
  return value ?? '';
}

/** Today + n days as YYYY-MM-DD in Amman (the dashboard's dates). */
function ammanDate(days = 0) {
  return new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Amman', year: 'numeric', month: '2-digit', day: '2-digit' }).format(new Date(Date.now() + days * 86_400_000));
}

test.describe('careers application, tracking and the Owner dashboard @app @journey @careers', () => {
  test.skip(!APP, 'set SHELTER_BASE_URL to run against the app');
  test.describe.configure({ mode: 'serial' });
  test.beforeEach(({}, info) => test.skip(!PHONE.test(info.project.name) && !DESKTOP.test(info.project.name), 'journeys run on a phone and a desktop'));
  test.setTimeout(120_000);

  /** The application sent by this run (this worker). */
  let applicant = null;
  let firstStatus = '';
  /** One signed-in Owner page shared by the Owner steps (one sign-in per run: codes are single-use). */
  let owner = null;

  async function ownerPage(browser, baseURL) {
    if (owner === null) {
      const context = await browser.newContext({ baseURL, viewport: { width: 1440, height: 900 } });
      const page = await context.newPage();
      const problems = watch(page);
      await signIn(page);
      owner = { context, page, problems };
    }
    owner.problems.length = 0;
    return owner.page;
  }

  /** Owner steps: desktop, a local owner, and an application from this run. */
  function ownerStep(info) {
    test.skip(!DESKTOP.test(info.project.name), 'one desktop run (sign-ins consume codes)');
    test.skip(!OWNER_READY, 'needs a local test owner (SHELTER_OWNER_EMAIL, SHELTER_OWNER_PASSWORD_FILE, SHELTER_OWNER_TOTP_FILE)');
    test.skip(applicant === null, 'no application was sent in this run');
  }

  /** The list filtered to this run's application (by its phone — CAREERS-058 quick search). */
  const listUrl = (extra = '') => `/dashboard/requests/careers?q=${encodeURIComponent(applicant.phone)}${extra}`;

  test.afterAll(async () => {
    await owner?.context.close();
    owner = null;
  });

  test('ar a complete application (all fields, a CV) is received with its JOB number', async ({ page }) => {
    resetLimits();
    const problems = watch(page);
    await page.goto('/ar/careers/', { waitUntil: 'networkidle' });
    const form = page.locator('form[data-careers-form]');
    test.skip((await form.count()) === 0, 'the form is closed in this environment');
    const stamp = `${Date.now().toString(36)}${Math.floor(Math.random() * 1296).toString(36)}`.toUpperCase();
    const digits = String(Math.floor(Math.random() * 10_000_000)).padStart(7, '0');
    const who = { name: `متقدم اختبار QA-${stamp}`, phone: `079${digits}`, email: `qa.careers.${stamp.toLowerCase()}@example.test`, cv: `سيرة-ذاتية-QA-${stamp}.pdf` };

    await page.locator('#full_name').fill(who.name);
    await page.locator('#phone').fill(who.phone);
    await page.locator('#email').fill(who.email);
    await page.locator('#gender-female').check({ force: true });
    await chooseOption(page, '#birth_day', 14);
    await chooseOption(page, '#birth_month', 5);
    await chooseOption(page, '#birth_year', 10);
    await chooseOption(page, '#marital_status');
    await page.locator('#nationality_type-jordanian').check({ force: true });
    await expect(page.locator('#national_id'), 'the national number appears for a Jordanian').toBeVisible();
    await expect(page.locator('#document_number')).toBeHidden();
    await page.locator('#national_id').fill(`999${digits}`);
    await chooseOption(page, '#city_id');
    await page.locator('#area').fill('منطقة اختبار');
    await chooseOption(page, '#education_level');
    await chooseOption(page, '#experience_band', 2);
    await page.locator('#same_field_experience-yes').check({ force: true });
    await page.locator('#currently_employed-no').check({ force: true });
    await page.locator('#job_title').fill('باريستا — اختبار آلي');
    await page.locator('#expected_salary').fill('450');
    await page.locator('#has_driving_license-no').check({ force: true });
    await page.locator('#notes').fill('طلب من اختبار آلي — CAREERS-094.\nSecond line.');

    // The CV uploads on its own (201) and is marked as the CV; a single file needs no choice.
    // In memory, like journeys.spec.mjs (a file PATH with an Arabic name does not reach the input in Chromium).
    await page.locator('#files-input').setInputFiles({ name: who.cv, mimeType: 'application/pdf', buffer: samplePdf() });
    const item = page.locator('[data-careers-files] .ui-upload__item').filter({ hasText: who.cv });
    await expect(item).toHaveAttribute('data-file-id', /\d+/);
    await expect(item).not.toHaveClass(/ui-upload__item--error/);
    if (await page.locator('[data-careers-cv-choice]').isVisible()) await page.locator('[data-careers-cv-options] input[type="radio"]').first().check({ force: true });
    await page.locator('#consent').check({ force: true });

    await page.waitForTimeout(MIN_FILL_MS);
    await page.locator('[data-careers-submit]').click();
    await page.waitForURL(/\/ar\/careers\/submitted\/$/);
    await expect(page.locator('h1')).toHaveCount(1);
    await expect(page.locator('h1')).toBeFocused();
    const number = ((await page.locator('.ui-apply-done__number').textContent()) ?? '').trim();
    expect(number).toMatch(new RegExp(`^JOB-${new Date().getFullYear()}-\\d{5}$`));
    expect(page.url(), 'nothing personal and no number in the address').not.toMatch(/JOB-|QA-|@/);
    await expect(page.locator('a[href$="/ar/careers/track/"]').first(), 'a way to the tracking page').toBeVisible();
    expect(problems).toEqual([]);
    applicant = { ...who, number };
  });

  test('ar the tracking page finds the application by number + phone, and gives one answer for anything wrong', async ({ page }) => {
    test.skip(applicant === null, 'no application was sent in this run');
    resetLimits();
    await page.goto('/ar/careers/track/');
    // Any equivalent phone format and a lower-case number are accepted.
    await page.locator('#number').fill(applicant.number.toLowerCase());
    await page.locator('#track-phone').fill(`+962 ${applicant.phone.slice(1, 3)} ${applicant.phone.slice(3)}`);
    await page.locator('form.ui-careers-track__form button[type="submit"]').click();
    const result = page.locator('.ui-careers-track__result');
    await expect(result).toBeVisible();
    firstStatus = ((await result.locator('.ui-careers-track__status').textContent()) ?? '').trim();
    expect(firstStatus.length).toBeGreaterThan(0);
    await expect(page.locator('main'), 'only the public status — nothing personal').not.toContainText(applicant.name);
    expect(page.url(), 'nothing in the address').not.toMatch(/JOB-|\d{7}/);
    // A wrong phone: the generic answer, no status.
    await page.locator('#number').fill(applicant.number);
    await page.locator('#track-phone').fill('0790000000');
    await page.locator('form.ui-careers-track__form button[type="submit"]').click();
    await expect(page.locator('.ui-alert[role="alert"]')).toBeVisible();
    await expect(page.locator('.ui-careers-track__result')).toHaveCount(0);
  });

  test('Owner: the new application shows its "new" badge; the live search by phone finds it; a filter narrows the list', async ({ browser, baseURL }, info) => {
    ownerStep(info);
    const page = await ownerPage(browser, baseURL);
    await openDashboard(page, '/dashboard/requests/careers', { waitUntil: 'networkidle' });
    await expect(page.locator('h1')).toHaveCount(1);
    // Live search (CAREERS-058): typing replaces the results in place; the focus stays in the box.
    const search = page.locator('#q');
    await search.fill(applicant.phone);
    const rows = page.locator('[data-careers-results] .ui-inbox-table tbody tr');
    await expect(rows).toHaveCount(1);
    await expect(rows.first().locator('.ui-inbox-name a')).toHaveText(applicant.name);
    await expect(search).toBeFocused();
    await expect.poll(() => new URL(page.url()).searchParams.get('q')).toBe(applicant.phone);
    await expect(rows.first(), 'marked new until seen').toHaveClass(/ui-inbox-row--new/);
    await expect(rows.first().locator('.ui-inbox-name .ui-badge').first()).toBeVisible();

    // A filter (More filters → gender): the same gender keeps it, the other one leaves the list empty.
    for (const [gender, expected] of [['female', 1], ['male', 0]]) {
      const more = page.locator('form.ui-inbox-filters details');
      if (!(await more.evaluate((d) => d.open))) await more.locator('summary').click();
      await page.locator('#gender').selectOption(gender);
      await page.locator('form.ui-inbox-filters .ui-inbox-filters__actions button[type="submit"]').click();
      await page.waitForURL((url) => url.searchParams.get('gender') === gender);
      await expect(page.locator('[data-careers-results] .ui-inbox-table tbody tr'), `gender=${gender}`).toHaveCount(expected);
      expect(new URL(page.url()).searchParams.get('q'), 'the search stays with the filter').toBe(applicant.phone);
    }
    // The "new" status filter (CAREERS-057) lists it too.
    await openDashboard(page, listUrl('&status=new'));
    await expect(page.locator('[data-careers-results] .ui-inbox-table tbody tr')).toHaveCount(1);
    expect(owner.problems).toEqual([]);
  });

  test('Owner: the quick view shows the essentials, then the full application opens', async ({ browser, baseURL }, info) => {
    ownerStep(info);
    const page = await ownerPage(browser, baseURL);
    await openDashboard(page, listUrl(), { waitUntil: 'networkidle' });
    await page.locator('[data-careers-results] .ui-inbox-name a').first().click();
    const panel = page.locator('dialog[data-quick-view-panel]');
    await expect(panel).toBeVisible();
    await expect(panel.locator('.ui-dialog__title')).toHaveText(applicant.name);
    await expect(panel).toContainText(applicant.number);
    await expect(panel).toContainText(applicant.cv);
    expect(new URL(page.url()).pathname, 'the list stays behind the panel').toBe('/dashboard/requests/careers');
    await page.keyboard.press('Escape');
    await expect(panel).toBeHidden();
    // Seen in the quick view → no longer new.
    await page.reload();
    await expect(page.locator('[data-careers-results] .ui-inbox-table tbody tr').first()).not.toHaveClass(/ui-inbox-row--new/);

    await page.locator('[data-careers-results] .ui-inbox-name a').first().click();
    await expect(panel).toBeVisible();
    await panel.locator('[data-quick-target] a[href*="/dashboard/requests/careers/"]').click();
    await page.waitForURL(/\/dashboard\/requests\/careers\/\d+$/);
    await expect(page.locator('h1')).toHaveText(applicant.name);
    await expect(page.locator('main')).toContainText(applicant.number);
    await expect(page.locator('section[aria-labelledby="attachments-title"]')).toContainText(applicant.cv);
    applicant.url = new URL(page.url()).pathname;
    expect(owner.problems).toEqual([]);
  });

  test('Owner: a status change with a note is saved and kept in the history', async ({ browser, baseURL }, info) => {
    ownerStep(info);
    test.skip(!applicant.url, 'the application page was not reached');
    const page = await ownerPage(browser, baseURL);
    await openDashboard(page, applicant.url);
    const note = `ملاحظة اختبار آلي ${Date.now()}`;
    await page.locator('#status').selectOption('under_review');
    await page.locator('#status-note').fill(note);
    await page.locator('form[action$="/status"] button[type="submit"]').click();
    await expect(page.locator('.ui-shell__flash').first()).toBeVisible();
    await expect(page.locator('#status')).toHaveValue('under_review');
    await expect(page.locator('section[aria-labelledby="history-title"]')).toContainText(note);
    expect(owner.problems).toEqual([]);
  });

  test('Owner: an interview is scheduled with a date, a time and a place', async ({ browser, baseURL }, info) => {
    ownerStep(info);
    test.skip(!applicant.url, 'the application page was not reached');
    const page = await ownerPage(browser, baseURL);
    await openDashboard(page, applicant.url);
    const date = ammanDate(2);
    await page.locator('#date').fill(date);
    await page.locator('#time').fill('10:30');
    const place = await chooseOption(page, '#location');
    const placeName = ((await page.locator(`#location option[value="${place}"]`).textContent()) ?? '').trim();
    await page.locator('#interview-notes').fill('مقابلة من اختبار آلي');
    await page.locator('form[action$="/interview"] button[type="submit"]').click();
    await page.waitForURL(/#interview$/);
    const section = page.locator('#interview');
    await expect(section.locator('.ui-record__status')).toContainText(`${date} · 10:30`);
    await expect(section.locator('.ui-record__status')).toContainText(placeName);
    expect(owner.problems).toEqual([]);
  });

  test('Owner: archive, then restore to the status it had', async ({ browser, baseURL }, info) => {
    ownerStep(info);
    test.skip(!applicant.url, 'the application page was not reached');
    const page = await ownerPage(browser, baseURL);
    await openDashboard(page, applicant.url);
    const before = await page.locator('#status').inputValue();
    expect(before).not.toBe('archived');
    await page.locator('#status').selectOption('archived');
    await page.locator('form[action$="/status"] button[type="submit"]').click();
    const restore = page.locator('form[action$="/restore"] button[type="submit"]');
    await expect(restore).toBeVisible();
    await expect(page.locator('#status')).toHaveValue('archived');
    await restore.click();
    await expect(page.locator('form[action$="/restore"]')).toHaveCount(0);
    await expect(page.locator('#status')).toHaveValue(before);
    expect(owner.problems).toEqual([]);
  });

  test('Owner: the export screen opens (behind the re-confirmation) without exporting anything yet', async ({ browser, baseURL }, info) => {
    ownerStep(info);
    const page = await ownerPage(browser, baseURL);
    const response = await openDashboard(page, `/dashboard/requests/careers/export?scope=filtered&q=${encodeURIComponent(applicant.phone)}`);
    expect(response?.status()).toBe(200);
    expect(new URL(page.url()).pathname).toBe('/dashboard/requests/careers/export');
    await expect(page.locator('h1')).toHaveCount(1);
    await expect(page.locator('form[action$="/dashboard/requests/careers/export"] button[type="submit"]')).toBeVisible();
    expect(owner.problems).toEqual([]);
  });

  test('ar the applicant\'s tracking shows the Owner\'s change (public status only)', async ({ page }, info) => {
    ownerStep(info);
    test.skip(!applicant.url || firstStatus === '', 'the earlier steps did not run');
    resetLimits();
    await page.goto('/ar/careers/track/');
    await page.locator('#number').fill(applicant.number);
    await page.locator('#track-phone').fill(applicant.phone);
    await page.locator('form.ui-careers-track__form button[type="submit"]').click();
    const status = page.locator('.ui-careers-track__result .ui-careers-track__status');
    await expect(status).toBeVisible();
    await expect(status, 'received → under review').not.toHaveText(firstStatus);
    await expect(page.locator('main'), 'no interview details in public').not.toContainText('10:30');
  });
});
