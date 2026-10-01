// Design-system browser gate (M37 §2, ACCESSIBILITY-STANDARD §4 AX-T01/AX-T04): every Storybook story of the x-ui
// library, in Arabic RTL and English LTR, at 360 / 768 / 1280 — axe (0 serious/critical) + no horizontal overflow.
// Uses the QA install in tooling/ (one Playwright for the repo, TOOLCHAIN.md); Chromium only, never `playwright install`.
// Run: npm run storybook:build && npm run test:storybook
import process from 'node:process';
import { defineConfig, devices } from './tooling/node_modules/@playwright/test/index.mjs';

const PORT = Number(process.env.DS_STORYBOOK_PORT ?? 6007);
const VIEWPORTS = [
    ['mobile-360', 360, 800, true],
    ['tablet-768', 768, 1024, true],
    ['desktop-1280', 1280, 800, false],
];

export default defineConfig({
    testDir: './tests/Browser',
    testMatch: /storybook-.*\.spec\.mjs$/,
    outputDir: 'tooling/reports/ds-test-results',
    reporter: [['list'], ['json', { outputFile: 'tooling/reports/ds-storybook-results.json' }]],
    fullyParallel: true,
    forbidOnly: Boolean(process.env.CI),
    use: { ...devices['Desktop Chrome'], baseURL: `http://127.0.0.1:${PORT}` },
    projects: VIEWPORTS.map(([name, width, height, touch]) => ({
        name,
        use: { viewport: { width, height }, hasTouch: touch, deviceScaleFactor: touch ? 2 : 1 },
    })),
    webServer: {
        command: 'node tooling/scripts/serve-static.mjs storybook-static',
        env: { PORT: String(PORT) },
        url: `http://127.0.0.1:${PORT}/index.json`,
        reuseExistingServer: !process.env.CI,
    },
});
