// Owner Dashboard — the full responsive sweep, committable (responsive rules for the dashboard, FINAL-QA): every screen
// the Owner can open — the GET routes of `php artisan route:list --path=dashboard --method=GET --json` when
// SHELTER_APP_DIR is set (a fixed list otherwise) — signed in once as the LOCAL test owner (secrets from files, see
// owner.mjs), at 320 · 360 · 390 · 414 · 768 · 1024 · 1280 · 1440 · 1920: the screen itself (no bounce to sign-in), no
// horizontal overflow and nothing sticking out sideways, exactly one h1, no script or console error, no failed
// same-origin request; axe (wcag2a/aa, wcag21a/aa, wcag22aa) without serious or critical findings at 390 and 1440.
// The 10-minute re-confirmation (/dashboard/confirm) is answered with a fresh code like the Owner would. Routes with a
// parameter use an example id looked up read-only; the sign-in steps, recovery codes, the step-up page itself,
// downloads and the identity-number / permanent-delete confirmations are left out (SKIP below).
// SHELTER_BASE_URL serves the dashboard in Arabic; set SHELTER_DASHBOARD_EN_URL to sweep a second server that serves
// it in English (DASHBOARD_LOCALE=en). Runs on the d1440 project only: it sets the widths itself.
// The signed-in state is kept in a private temporary file for this run only (a failed test restarts the worker), and
// the last test signs out and removes it.
import { execFileSync } from 'node:child_process';
import { chmodSync, existsSync, readdirSync, rmSync, statSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join } from 'node:path';
import { test, expect } from '@playwright/test';
import AxeBuilder from '@axe-core/playwright';
import { OWNER_READY, signIn, openDashboard } from './owner.mjs';

const WIDTHS = [320, 360, 390, 414, 768, 1024, 1280, 1440, 1920];
const AXE_WIDTHS = [390, 1440];
const PROJECT = /-d1440$/;

/** Screens a sweep never opens, and why. */
const SKIP = [
  [/^dashboard\/(login|two-factor)(\/|$)/, 'sign-in steps (signIn covers them)'],
  [/^dashboard\/recovery-codes$/, 'shows one-time recovery codes'],
  [/^dashboard\/confirm$/, 'the step-up itself (answered whenever a screen asks for it)'],
  [/^dashboard\/requests\/attachments\//, 'a file download, not a screen'],
  [/\/preview$/, 'an image file, not a screen'],
  [/\/quick$/, 'a fragment for the quick-view panel (opened directly it redirects to the full page)'],
  [/\/identity$/, 'reveals a protected identity number (and is recorded in the audit log)'],
  [/\/delete$/, 'the permanent-delete confirmation (only for an archived application)'],
];

/** Used when the app directory is unknown: the screens without parameters. */
const FALLBACK = ['dashboard', 'dashboard/live', 'dashboard/seo', 'dashboard/content/pages', 'dashboard/content/texts', 'dashboard/content/media',
  'dashboard/content/media/upload', 'dashboard/content/awards', 'dashboard/content/awards/new', 'dashboard/content/team', 'dashboard/content/team/new',
  'dashboard/content/events', 'dashboard/content/events/new', 'dashboard/content/announcements', 'dashboard/content/announcements/new',
  'dashboard/requests/careers', 'dashboard/requests/careers/export', 'dashboard/requests/careers/settings', 'dashboard/requests/partnerships',
  'dashboard/requests/feedback', 'dashboard/data/branches', 'dashboard/data/contacts', 'dashboard/data/menu', 'dashboard/data/menu/new',
  'dashboard/data/menu/season', 'dashboard/data/menu/sections', 'dashboard/data/menu/words', 'dashboard/data/menu/bulk', 'dashboard/settings',
  'dashboard/settings/consents'];

function artisan(args) {
  return execFileSync('php', ['artisan', ...args], { cwd: process.env.SHELTER_APP_DIR, encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'] });
}

/** One example id per route parameter (read-only), e.g. { award: 3, key: 'about', 'application:JOB': 12 }. */
function exampleIds() {
  const php = `echo json_encode([
    'announcement' => App\\Models\\Experience::query()->whereIn('type', ['announcement', 'campaign'])->orderByDesc('id')->value('id'),
    'event' => App\\Models\\Experience::query()->where('type', 'event')->orderByDesc('id')->value('id'),
    'award' => App\\Models\\Award::query()->orderBy('id')->value('id'),
    'media' => App\\Models\\Media::query()->orderBy('id')->value('id'),
    'member' => App\\Models\\TeamMember::query()->orderBy('id')->value('id'),
    'key' => App\\Models\\Page::query()->orderBy('id')->value('key'),
    'branch' => App\\Models\\Branch::query()->orderBy('id')->value('id'),
    'category' => App\\Models\\MenuCategory::query()->orderBy('id')->value('id'),
    'product' => App\\Models\\Product::query()->where('status', App\\Models\\Product::STATUS_ACTIVE)->whereNull('merged_into_id')->orderBy('id')->value('id'),
    'application:careers' => App\\Models\\Recruitment\\Application::query()->where('type', 'JOB')->orderByDesc('id')->value('id'),
    'application:partnerships' => App\\Models\\Recruitment\\Application::query()->where('type', 'FR')->orderByDesc('id')->value('id'),
  ]);`;
  return JSON.parse(artisan(['tinker', `--execute=${php.replace(/\s+/g, ' ')}`]).trim().split('\n').pop() ?? '{}');
}

/** Screens that are a step of a flow: opened with an example selection (a GET without one goes back to its list). */
const QUERY = {
  'dashboard/data/menu/bulk': (ids) => (ids.product ? `?action=move&ids[]=${encodeURIComponent(String(ids.product))}` : null),
};

/** [path, note] for every screen to sweep; note says why a screen is left out (then path is null). */
function screens() {
  if (!process.env.SHELTER_APP_DIR) return FALLBACK.map((uri) => [`/${uri}`, null]);
  const routes = JSON.parse(artisan(['route:list', '--path=dashboard', '--method=GET', '--json']));
  const ids = exampleIds();
  return routes.map(({ uri }) => {
    const skip = SKIP.find(([pattern]) => pattern.test(uri));
    if (skip) return [null, `${uri}: ${skip[1]}`];
    let missing = null;
    const path = uri.replace(/\{(\w+)\??\}/g, (_, name) => {
      const area = /requests\/(careers|partnerships)\//.exec(uri)?.[1];
      const value = ids[area ? `${name}:${area}` : name] ?? ids[name];
      if (value === null || value === undefined) missing = name;
      return encodeURIComponent(String(value));
    });
    if (missing !== null) return [null, `${uri}: no {${missing}} exists to open`];
    const query = QUERY[uri] ? QUERY[uri](ids) : '';
    return query === null ? [null, `${uri}: no example to open it with`] : [`/${path}${query}`, null];
  });
}

const SERVERS = [['ar', process.env.SHELTER_BASE_URL], ['en', process.env.SHELTER_DASHBOARD_EN_URL]].filter(([, url]) => Boolean(url));
const LIST = OWNER_READY ? screens() : [];

/** A private file for this run's signed-in state (one per server); the run's processes share the parent id. */
const stateFile = (origin) => join(tmpdir(), `shelter-dashboard-sweep-${process.ppid}-${Buffer.from(origin).toString('hex')}.json`);
const sessions = new Map();

async function ownerPage(browser, origin) {
  const known = sessions.get(origin);
  if (known) return known.page;
  const file = stateFile(origin);
  const context = await browser.newContext({ baseURL: origin, ...(existsSync(file) ? { storageState: file } : {}) });
  const page = await context.newPage();
  await page.goto('/dashboard');
  if (/^\/dashboard\/(login|two-factor)/.test(new URL(page.url()).pathname)) {
    await signIn(page);
    await context.storageState({ path: file });
    chmodSync(file, 0o600);
  }
  sessions.set(origin, { context, page });
  return page;
}

/** Collects everything that went wrong on the page until `stop()` is called. */
function watch(page, origin) {
  const problems = [];
  const handlers = {
    pageerror: (e) => problems.push(`script: ${e.message}`),
    console: (m) => { if (m.type() === 'error') problems.push(`console: ${m.text().slice(0, 200)}`); },
    requestfailed: (r) => {
      const failure = r.failure()?.errorText ?? '';
      if (r.url().startsWith(origin) && !/ERR_ABORTED/.test(failure)) problems.push(`request failed: ${r.url().replace(origin, '')} ${failure}`);
    },
    response: (r) => { if (r.url().startsWith(origin) && r.status() >= 400) problems.push(`HTTP ${r.status()}: ${r.url().replace(origin, '')}`); },
  };
  for (const [event, handler] of Object.entries(handlers)) page.on(event, handler);
  return { problems, stop: () => { for (const [event, handler] of Object.entries(handlers)) page.off(event, handler); } };
}

test.describe('owner dashboard responsive sweep @app @dashboard @responsive', () => {
  test.skip(!process.env.SHELTER_BASE_URL, 'set SHELTER_BASE_URL to run against the app');
  test.skip(!OWNER_READY, 'needs a local test owner (SHELTER_OWNER_EMAIL, SHELTER_OWNER_PASSWORD_FILE, SHELTER_OWNER_TOTP_FILE)');
  test.describe.configure({ mode: 'default' }); // one after another in one worker: one sign-in serves every screen
  test.beforeEach(({}, info) => test.skip(!PROJECT.test(info.project.name), 'one project: the sweep sets every width itself'));
  test.setTimeout(240_000);

  test.afterAll(async () => {
    for (const { context } of sessions.values()) await context.close();
    sessions.clear();
  });

  for (const [, note] of LIST.filter(([path]) => path === null)) {
    test.skip(`left out — ${note}`, async () => {});
  }

  for (const [language, origin] of SERVERS) {
    for (const [path] of LIST.filter(([p]) => p !== null)) {
      test(`${language} ${path} — every width: the screen, no overflow, one h1, no errors; axe at 390 and 1440`, async ({ browser }) => {
        const page = await ownerPage(browser, origin);
        const found = [];
        for (const width of WIDTHS) {
          await page.setViewportSize({ width, height: width < 768 ? 800 : 900 });
          const { problems, stop } = watch(page, origin);
          try {
            const response = await openDashboard(page, path, { waitUntil: 'networkidle' });
            const at = `${width}px`;
            if ((response?.status() ?? 0) >= 400) found.push(`${at}: HTTP ${response?.status()}`);
            const landed = new URL(page.url());
            if (landed.pathname !== new URL(path, origin).pathname) {
              found.push(`${at}: landed on ${landed.pathname}`);
              continue;
            }
            if (width === WIDTHS[0]) await expect(page.locator('html')).toHaveAttribute('lang', language);
            const overflow = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
            if (overflow > 0) found.push(`${at}: horizontal overflow ${overflow}px`);
            const wide = await page.evaluate(() => [...document.querySelectorAll('.ui-shell__topbar *, .ui-shell__nav *, main *')].filter((el) => {
              const r = el.getBoundingClientRect();
              if (r.width === 0 || getComputedStyle(el).visibility === 'hidden') return false;
              let p = el.parentElement;
              while (p) { const s = getComputedStyle(p); if (/(auto|scroll|hidden|clip)/.test(s.overflowX)) return false; p = p.parentElement; }
              return r.right > window.innerWidth + 1 || r.left < -1;
            }).slice(0, 3).map((el) => `${el.tagName.toLowerCase()}.${[...el.classList].join('.')}`));
            if (wide.length > 0) found.push(`${at}: outside the viewport: ${wide.join(', ')}`);
            const h1 = await page.locator('h1').count();
            if (h1 !== 1) found.push(`${at}: ${h1} h1`);
            if (AXE_WIDTHS.includes(width)) {
              const result = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa']).analyze();
              for (const v of result.violations.filter((v) => v.impact === 'serious' || v.impact === 'critical')) {
                found.push(`${at}: axe ${v.id} (${v.impact}): ${v.nodes.slice(0, 2).map((n) => n.target.join(' ')).join(' | ')}`);
              }
            }
          } finally {
            stop();
            found.push(...problems.map((p) => `${width}px: ${p}`));
          }
        }
        expect(found, `${language} ${path}`).toEqual([]);
      });
    }
  }

  test('sign out of every swept dashboard and forget the saved session', async ({ browser }) => {
    for (const [, origin] of SERVERS) {
      if (!existsSync(stateFile(origin)) && !sessions.has(origin)) continue;
      const page = await ownerPage(browser, origin);
      await page.goto('/dashboard');
      await page.locator('form[action$="/dashboard/logout"] button').first().click();
      await page.waitForURL(/\/dashboard\/login/);
      rmSync(stateFile(origin), { force: true });
    }
    // Leftovers of earlier interrupted runs (older than a day).
    for (const name of readdirSync(tmpdir()).filter((n) => n.startsWith('shelter-dashboard-sweep-'))) {
      const file = join(tmpdir(), name);
      if (Date.now() - statSync(file).mtimeMs > 86_400_000) rmSync(file, { force: true });
    }
  });
});
