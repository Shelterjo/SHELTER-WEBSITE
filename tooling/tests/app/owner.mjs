// Shared by the Owner Dashboard specs (not a spec itself): signs in the LOCAL test owner the way the Owner does —
// password, then the authenticator code — and answers the 10-minute re-confirmation (/dashboard/confirm) when a
// sensitive screen asks for it. The secrets are read from files and never printed, logged or passed on a command line:
// SHELTER_OWNER_EMAIL, SHELTER_OWNER_PASSWORD_FILE, SHELTER_OWNER_TOTP_FILE (same contract as dashboard.spec.mjs).
// A code is accepted once per 30-second step (replay protection): each new code waits for a step the server has not
// used yet. When SHELTER_APP_DIR is set, the last used step is read (read-only) from the app, so no step is wasted.
import { execFileSync } from 'node:child_process';
import { createHmac } from 'node:crypto';
import { readFileSync } from 'node:fs';
import { expect } from '@playwright/test';

export const OWNER_EMAIL = process.env.SHELTER_OWNER_EMAIL ?? '';
export const OWNER_READY = Boolean(process.env.SHELTER_BASE_URL && OWNER_EMAIL && process.env.SHELTER_OWNER_PASSWORD_FILE && process.env.SHELTER_OWNER_TOTP_FILE);

const read = (file) => (file ? readFileSync(file, 'utf8').trim() : '');
const STEP_MS = 30_000;

/** RFC 6238 code for a 30-second step (6 digits, SHA-1), from a base32 secret (same as dashboard.spec.mjs). */
export function totp(secret, step = Math.floor(Date.now() / STEP_MS)) {
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

/** The last code step the app accepted for the test owner (read-only), or null when the app directory is unknown. */
function serverLastStep() {
  if (!process.env.SHELTER_APP_DIR) return null;
  const email = Buffer.from(OWNER_EMAIL).toString('base64');
  try {
    const out = execFileSync('php', ['artisan', 'tinker', `--execute=echo (int) App\\Models\\User::query()->where('email', base64_decode('${email}'))->value('two_factor_last_step');`],
      { cwd: process.env.SHELTER_APP_DIR, encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'] });
    const step = Number.parseInt(out.trim().split('\n').pop() ?? '', 10);
    return Number.isFinite(step) ? step : null;
  } catch {
    return null;
  }
}

let lastUsed = 0; // per worker: the newest step this process has sent

/** A code for a step nobody has used yet: waits for the next step when the current one is (or may be) taken. */
async function freshCode(page, secret) {
  const server = serverLastStep();
  // Without the app's record (and nothing sent yet), behave like dashboard.spec.mjs: always the next step.
  let used = server === null && lastUsed === 0 ? Math.floor(Date.now() / STEP_MS) : Math.max(lastUsed, server ?? 0);
  for (;;) {
    const now = Date.now();
    const step = Math.floor(now / STEP_MS);
    const left = STEP_MS - (now % STEP_MS);
    if (step > used && left > 2000) {
      lastUsed = step;
      return totp(secret, step);
    }
    if (step > used) used = step; // too close to the end of its step: take the next one
    await page.waitForTimeout(left + 500);
  }
}

/** Sends a code on the current page (two-factor or confirm) until it is accepted (a race with another run retries). */
async function sendCode(page, secret, stayPath) {
  for (let attempt = 0; attempt < 3; attempt++) {
    await page.locator('input[name="code"]').fill(await freshCode(page, secret));
    await page.locator('form button[type="submit"]').click();
    const left = await page.waitForURL((url) => url.pathname !== stayPath, { timeout: 10_000 }).then(() => true, () => false);
    if (left) return;
    lastUsed = Math.floor(Date.now() / STEP_MS); // refused: that step is taken — wait for the next one
  }
  throw new Error(`the authenticator code was refused three times on ${stayPath}`);
}

/** Signs in with both factors; ends on the dashboard home. */
export async function signIn(page) {
  const password = read(process.env.SHELTER_OWNER_PASSWORD_FILE);
  const secret = read(process.env.SHELTER_OWNER_TOTP_FILE);
  await page.goto('/dashboard/login');
  await page.locator('input[name="email"]').fill(OWNER_EMAIL);
  await page.locator('input[name="password"]').fill(password);
  await page.locator('form button[type="submit"]').click();
  await page.waitForURL(/\/dashboard\/two-factor/);
  await sendCode(page, secret, '/dashboard/two-factor');
  expect(new URL(page.url()).pathname, 'signed in').toMatch(/^\/dashboard/);
  expect(new URL(page.url()).pathname, 'signed in').not.toMatch(/\/(login|two-factor)/);
}

/** Answers the re-confirmation step-up (password + a fresh code) when the page asks for it; returns whether it did. */
export async function confirmIfAsked(page) {
  if (new URL(page.url()).pathname !== '/dashboard/confirm') return false;
  await page.locator('input[name="password"]').fill(read(process.env.SHELTER_OWNER_PASSWORD_FILE));
  await sendCode(page, read(process.env.SHELTER_OWNER_TOTP_FILE), '/dashboard/confirm');
  return true;
}

/** Opens a dashboard address as the signed-in Owner, passing the re-confirmation when it is asked for. */
export async function openDashboard(page, path, options = {}) {
  let response = await page.goto(path, options);
  if (await confirmIfAsked(page)) {
    // The step-up returns to the screen it guarded (url.intended); load it again for a clean response object.
    response = await page.goto(path, options);
  }
  expect(new URL(page.url()).pathname, `${path} needs the sign-in again`).not.toBe('/dashboard/login');
  return response;
}
