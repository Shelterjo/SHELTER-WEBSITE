// Owner Dashboard journeys against the running app (SHELTER_BASE_URL), as the Owner would use it: sign in with the
// password and the authenticator code, change a site text without code and see it on the site, put it back, sign out
// (FINAL-QA QA-2). Runs only with a LOCAL test owner whose secrets are read from files — never from the command line or
// the repository: SHELTER_OWNER_EMAIL, SHELTER_OWNER_PASSWORD_FILE, SHELTER_OWNER_TOTP_FILE.
import { createHmac } from 'node:crypto';
import { readFileSync } from 'node:fs';
import { test, expect } from '@playwright/test';

const EMAIL = process.env.SHELTER_OWNER_EMAIL ?? '';
const read = (file) => (file ? readFileSync(file, 'utf8').trim() : '');
const READY = Boolean(process.env.SHELTER_BASE_URL && EMAIL && process.env.SHELTER_OWNER_PASSWORD_FILE && process.env.SHELTER_OWNER_TOTP_FILE);

/** RFC 6238 code for the current 30-second step (6 digits, SHA-1), from a base32 secret. */
function totp(secret, step = Math.floor(Date.now() / 30000)) {
  const alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
  let bits = '';
  for (const c of secret.replace(/=+$/, '').toUpperCase()) bits += alphabet.indexOf(c).toString(2).padStart(5, '0');
  const key = Buffer.from(bits.match(/.{8}/g).map((b) => parseInt(b, 2)));
  const counter = Buffer.alloc(8);
  counter.writeBigUInt64BE(BigInt(step));
  const mac = createHmac('sha1', key).update(counter).digest();
  const offset = mac[mac.length - 1] & 0xf;
  return String((mac.readUInt32BE(offset) & 0x7fffffff) % 1_000_000).padStart(6, '0');
}

/** Waits for a fresh 30-second step: a used code is refused (replay protection), so each sign-in needs a new one. */
async function freshCode(page, secret) {
  const left = 30000 - (Date.now() % 30000);
  await page.waitForTimeout(left + 500);
  return totp(secret);
}

test.describe('owner dashboard journeys @app @journey @dashboard', () => {
  test.skip(!READY, 'needs SHELTER_BASE_URL and a local test owner (see the header)');
  test.describe.configure({ mode: 'serial' });
  test.beforeEach(({}, info) => test.skip(!/-(d1440)$/.test(info.project.name), 'one desktop run (sign-ins consume codes)'));
  test.setTimeout(120_000);

  test('outsiders never see the dashboard; sign-in needs both factors; a change shows on the site; sign-out ends it', async ({ page, context }) => {
    const password = read(process.env.SHELTER_OWNER_PASSWORD_FILE);
    const secret = read(process.env.SHELTER_OWNER_TOTP_FILE);

    // 1. Not signed in: every dashboard address goes to the sign-in page.
    for (const path of ['/dashboard', '/dashboard/content/texts', '/dashboard/requests/careers']) {
      await page.goto(path);
      expect(new URL(page.url()).pathname, path).toBe('/dashboard/login');
    }

    // 2. A wrong password is refused with the same message as an unknown address; the password alone opens nothing.
    await page.locator('input[name="email"]').fill(EMAIL);
    await page.locator('input[name="password"]').fill('not-the-password');
    await page.locator('form button[type="submit"]').click();
    await expect(page.locator('[aria-invalid="true"], .ui-field__error').first()).toBeVisible();
    await page.locator('input[name="password"]').fill(password);
    await page.locator('form button[type="submit"]').click();
    await page.waitForURL(/\/dashboard\/two-factor/);
    await page.goto('/dashboard');
    expect(new URL(page.url()).pathname).toBe('/dashboard/login');

    // 3. Both factors: in.
    await page.locator('input[name="email"]').fill(EMAIL);
    await page.locator('input[name="password"]').fill(password);
    await page.locator('form button[type="submit"]').click();
    await page.waitForURL(/\/dashboard\/two-factor/);
    await page.locator('input[name="code"]').fill('000000');
    await page.locator('form button[type="submit"]').click();
    await expect(page.locator('[aria-invalid="true"], .ui-field__error').first()).toBeVisible();
    await page.locator('input[name="code"]').fill(await freshCode(page, secret));
    await page.locator('form button[type="submit"]').click();
    await page.waitForURL((url) => url.pathname === '/dashboard');
    await expect(page.locator('h1')).toHaveCount(1);

    // 4. No code: change the gateway lead in Site texts, see it on the site, then put the original (empty) back.
    const marker = `FINAL-QA ${Date.now()}`;
    await page.goto('/dashboard/content/texts?page=home');
    const field = page.locator('#t-site-gateway-lead-ar');
    test.skip((await field.count()) === 0, 'the gateway lead is not in this group');
    const original = await field.inputValue();
    await field.fill(marker);
    await field.locator('xpath=ancestor::form').locator('button[type="submit"]').click();
    await expect(page.locator('.ui-alert, [role="status"]').first()).toBeVisible();
    const site = await context.newPage();
    await site.goto('/');
    await expect(site.locator('body')).toContainText(marker);
    await field.fill(original);
    await field.locator('xpath=ancestor::form').locator('button[type="submit"]').click();
    await site.reload();
    await expect(site.locator('body')).not.toContainText(marker);
    await site.close();

    // 5. Two tabs on one menu item (FINAL-QA QA-044): the save from the tab opened first is refused, the newer one stays.
    await page.goto('/dashboard/data/menu');
    const itemHref = await page.locator('a[href*="/dashboard/data/menu/"]').evaluateAll((links) => links.map((a) => a.href).find((h) => /\/dashboard\/data\/menu\/\d+$/.test(h)));
    const tabA = page;
    await tabA.goto(itemHref ?? '/dashboard/data/menu');
    const sortA = tabA.locator('form[action$="/details"] #sort');
    const originalSort = await sortA.inputValue();
    await tabA.waitForTimeout(1100); // the next save lands in a later second than tab A's page
    const tabB = await context.newPage();
    await tabB.goto(itemHref ?? '/dashboard/data/menu');
    await tabB.locator('form[action$="/details"] #sort').fill(String(Number(originalSort || '0') + 7));
    await tabB.locator('form[action$="/details"] button[type="submit"]').click();
    await expect(tabB.locator('.ui-shell__flash').first()).toBeVisible();
    await sortA.fill(String(Number(originalSort || '0') + 3));
    await tabA.locator('form[action$="/details"] button[type="submit"]').click();
    await expect(tabA.locator('.ui-shell__flash').first()).toContainText(/(another tab|نافذة)/);
    await tabB.reload();
    await expect(tabB.locator('form[action$="/details"] #sort')).toHaveValue(String(Number(originalSort || '0') + 7));
    // Put it back from a fresh page.
    await tabB.locator('form[action$="/details"] #sort').fill(originalSort);
    await tabB.locator('form[action$="/details"] button[type="submit"]').click();
    await expect(tabB.locator('.ui-shell__flash').first()).toBeVisible();
    await tabB.close();

    // 6. Sign out: Back does not bring the dashboard back (no-store), the next request asks to sign in.
    await page.goto('/dashboard');
    await page.locator('form[action$="/dashboard/logout"] button').first().click();
    await page.waitForURL(/\/dashboard\/login/);
    await page.goBack();
    await page.waitForLoadState('domcontentloaded');
    expect(new URL(page.url()).pathname).toBe('/dashboard/login');
  });
});
