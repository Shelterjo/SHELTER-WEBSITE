// Branch open state, live in an open tab (HOURS-009…011; resources/js/ui/open-status.ts) against the running app
// (SHELTER_BASE_URL). The browser clock is installed at the real time before the page loads, then moved forward. The
// server's own segments (data-ui-open-status: u = epoch-ms boundary, s = state, t = text, c = closing time) are the
// oracle: at a boundary the line moves to the next segment, a "closes in N min" countdown goes down each minute, and
// past the last known segment the line is hidden (never a guessed "open"). No hours are assumed by the test: whatever
// the approved hours give today is what is checked. Phone and desktop, Arabic and English.
import { test, expect } from '@playwright/test';

const APP = Boolean(process.env.SHELTER_BASE_URL);
const PHONE = /-m390$/;
const DESKTOP = /-d1440$/;
const LOCALES = ['ar', 'en'];

/** The branch pages linked from the locations page. */
async function branchPaths(page, locale) {
  await page.goto(`/${locale}/jo/locations/`);
  const hrefs = await page.locator('main a[href]').evaluateAll((links) => links.map((a) => new URL(a.href).pathname));
  return [...new Set(hrefs.filter((p) => new RegExp(`^/${locale}/jo/locations/[a-z0-9-]+/[a-z0-9-]+/$`).test(p)))];
}

/** Opens a page with the browser clock installed at the real time (the server rendered the segments for now). */
async function openWithClock(page, path) {
  await page.clock.install({ time: Date.now() });
  await page.goto(path, { waitUntil: 'networkidle' });
}

const readTimeline = async (line) => JSON.parse((await line.getAttribute('data-ui-open-status')) ?? '{}');
const pageNow = (page) => page.evaluate(() => Date.now());

/**
 * What the line must say at `now`: the segment whose boundary is still ahead, its text — or, for "closing soon", the
 * plural template with the minutes left (rounded up, never zero) — and null past the last known segment. Computed in
 * the page so the plural rules are the browser's own (the same ICU the script uses).
 */
function expectedAt(page, timeline, now) {
  return page.evaluate(({ timeline, now }) => {
    const segment = timeline.segments.find((s) => s.u === null || now < s.u) ?? null;
    if (segment === null) return null;
    if (segment.c === null) return { state: segment.s, text: segment.t, minutes: null };
    const minutes = Math.max(1, Math.ceil((segment.c - now) / 60_000));
    const rule = new Intl.PluralRules(document.documentElement.lang || 'ar').select(minutes);
    const template = timeline.closing[rule] ?? timeline.closing.other ?? '';
    return { state: segment.s, text: template.replace(':n', String(minutes)), minutes };
  }, { timeline, now });
}

async function expectLine(page, line, timeline, where) {
  const expected = await expectedAt(page, timeline, await pageNow(page));
  if (expected === null) {
    await expect(line, `${where}: hidden past the known horizon`).toBeHidden();
    return null;
  }
  await expect(line, where).toHaveAttribute('data-state', expected.state);
  await expect(line.locator('.ui-open-status__text'), where).toHaveText(expected.text);
  return expected;
}

test.describe('branch open state stays current in an open tab @app @journey', () => {
  test.skip(!APP, 'set SHELTER_BASE_URL to run against the app');
  test.beforeEach(({}, info) => test.skip(!PHONE.test(info.project.name) && !DESKTOP.test(info.project.name), 'journeys run on a phone and a desktop'));

  for (const locale of LOCALES) {
    test(`${locale} at its boundary the line moves to the next segment (state and text)`, async ({ page }) => {
      const errors = [];
      page.on('pageerror', (e) => errors.push(e.message));
      const [path] = await branchPaths(page, locale);
      test.skip(path === undefined, 'no branch page');
      await openWithClock(page, path);
      const line = page.locator('.ui-page-intro [data-ui-open-status]');
      test.skip((await line.count()) === 0, 'no approved hours for this branch: no open state is shown');
      const timeline = await readTimeline(line);
      const [first, next] = timeline.segments;
      test.skip(first.u === null, 'no known change ahead');
      expect(await pageNow(page), 'the page opens inside the first segment').toBeLessThan(first.u);
      await expectLine(page, line, timeline, 'as rendered');
      // Just before the boundary nothing changes; just after it the next segment shows.
      await page.clock.fastForward(first.u - (await pageNow(page)) - 5_000);
      await expectLine(page, line, timeline, '5 s before the boundary');
      expect(await line.getAttribute('data-state')).toBe(first.s);
      await page.clock.fastForward(6_000);
      await expectLine(page, line, timeline, 'after the boundary');
      if (next !== undefined) {
        await expect(line).toHaveAttribute('data-state', next.s);
        expect(next.s === first.s && next.t === first.t, 'the next segment says something else').toBe(false);
      }
      expect(errors).toEqual([]);
    });

    test(`${locale} a "closes in N min" countdown goes down as the minutes pass`, async ({ page }) => {
      const [path] = await branchPaths(page, locale);
      test.skip(path === undefined, 'no branch page');
      await openWithClock(page, path);
      const line = page.locator('.ui-page-intro [data-ui-open-status]');
      test.skip((await line.count()) === 0, 'no approved hours for this branch: no open state is shown');
      const timeline = await readTimeline(line);
      const k = timeline.segments.findIndex((s) => s.c !== null);
      test.skip(k < 0, 'no closing-soon segment in the known horizon');
      // Into the closing-soon segment: a second after it starts (or now, when it already runs).
      if (k > 0) await page.clock.fastForward(timeline.segments[k - 1].u - (await pageNow(page)) + 1_000);
      const before = await expectLine(page, line, timeline, 'closing soon starts');
      expect(before?.state).toBe(timeline.segments[k].s);
      test.skip((before?.minutes ?? 0) < 4, 'too close to closing to watch two minutes go by');
      const shown = Number(((await line.locator('.ui-open-status__text').textContent()) ?? '').match(/\d+/)?.[0] ?? NaN);
      expect(shown, 'the minutes are in the text').toBe(before?.minutes);
      await page.clock.fastForward(2 * 60_000);
      const after = await expectLine(page, line, timeline, 'two minutes later');
      const now = Number(((await line.locator('.ui-open-status__text').textContent()) ?? '').match(/\d+/)?.[0] ?? NaN);
      expect(after?.minutes).toBe((before?.minutes ?? 0) - 2);
      expect(now, 'the countdown went down by two minutes').toBe(shown - 2);
      await page.clock.fastForward(60_000);
      await expectLine(page, line, timeline, 'one more minute');
    });

    test(`${locale} past the last known segment the line is hidden, never a guessed "open"`, async ({ page }) => {
      const [path] = await branchPaths(page, locale);
      test.skip(path === undefined, 'no branch page');
      await openWithClock(page, path);
      const line = page.locator('.ui-page-intro [data-ui-open-status]');
      test.skip((await line.count()) === 0, 'no approved hours for this branch: no open state is shown');
      const timeline = await readTimeline(line);
      const last = timeline.segments[timeline.segments.length - 1];
      test.skip(last.u === null, 'the last known segment has no end');
      await page.clock.fastForward(last.u - (await pageNow(page)) + 1_000);
      await expect(line).toBeHidden();
    });

    test(`${locale} every open-state line on the locations and contact pages follows its own timeline`, async ({ page }) => {
      for (const path of [`/${locale}/jo/locations/`, `/${locale}/contact/`]) {
        await page.goto('about:blank');
        await openWithClock(page, path);
        const lines = page.locator('[data-ui-open-status]');
        const count = await lines.count();
        if (count === 0) continue;
        const timelines = [];
        for (let i = 0; i < count; i++) timelines.push(await readTimeline(lines.nth(i)));
        for (let i = 0; i < count; i++) await expectLine(page, lines.nth(i), timelines[i], `${path} #${i} as rendered`);
        const boundaries = timelines.map((t) => t.segments[0].u).filter((u) => u !== null);
        if (boundaries.length === 0) continue;
        await page.clock.fastForward(Math.min(...boundaries) - (await pageNow(page)) + 1_000);
        for (let i = 0; i < count; i++) await expectLine(page, lines.nth(i), timelines[i], `${path} #${i} after the first boundary`);
      }
    });
  }
});
