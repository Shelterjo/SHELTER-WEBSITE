// Every design-system story (storybook-static/index.json) × ar / en × the viewport projects of playwright.ds.config.mjs:
//   - axe-core, WCAG 2.0/2.1/2.2 A + AA tags: 0 serious / critical violations (ACCESSIBILITY-STANDARD §4)
//   - no horizontal page overflow (AX-T04: scrollWidth ≤ clientWidth)
//   - the story really rendered the exported Blade HTML in the requested language and direction
// Stories set body[data-ds-ready=<story id>] at the end of their play step (design-system/stories/support.ts), so the
// checks run after "open" overlays are shown as real modal dialogs.
import { readFileSync } from 'node:fs';
import { test, expect } from '../../tooling/node_modules/@playwright/test/index.mjs';
import AxeBuilder from '../../tooling/node_modules/@axe-core/playwright/dist/index.mjs';

const index = JSON.parse(readFileSync(new URL('../../storybook-static/index.json', import.meta.url), 'utf8'));
const stories = Object.values(index.entries).filter((entry) => entry.type === 'story');
const LOCALES = [
    ['ar', 'rtl'],
    ['en', 'ltr'],
];
const TAGS = ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'];

async function analyze(page) {
    // The a11y addon may still be running its own axe pass in the preview; wait for it instead of failing.
    for (let attempt = 0; ; attempt++) {
        try {
            return await new AxeBuilder({ page }).include('#storybook-root').withTags(TAGS).analyze();
        } catch (error) {
            if (attempt >= 5 || !/already running/i.test(String(error))) throw error;
            await page.waitForTimeout(200);
        }
    }
}

test('the story index is not empty', () => {
    expect(stories.length).toBeGreaterThan(0);
});

for (const entry of stories) {
    test(`${entry.title} / ${entry.name}`, async ({ page }) => {
        for (const [locale, dir] of LOCALES) {
            await page.goto(`/iframe.html?id=${entry.id}&viewMode=story&globals=locale:${locale}`);
            await page.locator(`body[data-ds-ready="${entry.id}"]`).waitFor({ state: 'attached' });

            const html = page.locator('html');
            await expect(html, `${locale}: lang`).toHaveAttribute('lang', locale);
            await expect(html, `${locale}: dir`).toHaveAttribute('dir', dir);
            await expect(page.locator('.ds-missing-story'), `${locale}: exported HTML`).toHaveCount(0);

            const results = await analyze(page);
            const blocking = results.violations
                .filter((violation) => violation.impact === 'serious' || violation.impact === 'critical')
                .map(
                    (violation) =>
                        `${violation.id} (${violation.impact}): ${violation.nodes.map((n) => n.target.join(' ')).join(', ')}`,
                );
            expect(blocking, `${locale}: axe serious/critical`).toEqual([]);

            const overflow = await page.evaluate(
                () => document.documentElement.scrollWidth - document.documentElement.clientWidth,
            );
            expect(overflow, `${locale}: horizontal overflow (px)`).toBeLessThanOrEqual(0);
        }
    });
}
