// SHELTER COFFEE — Playwright configuration (quality infrastructure, framework-agnostic).
// Today it targets the low-fi menu wireframes (docs/menu-ia/wireframes/html) as a "prototype contract".
// When the real app exists, set SHELTER_BASE_URL and the app suites in tests/app/ take over.
// Viewports: viewports.mjs (mandatory responsive matrix). Firefox + WebKit run only where installed (CI): SHELTER_ALL_BROWSERS=1.
import { defineConfig, devices } from '@playwright/test';
import { VIEWPORTS } from './viewports.mjs';

const BASE = process.env.SHELTER_BASE_URL || 'http://127.0.0.1:4173';
const ALL_BROWSERS = process.env.SHELTER_ALL_BROWSERS === '1';
const engines = [['chromium', devices['Desktop Chrome']], ...(ALL_BROWSERS ? [['firefox', devices['Desktop Firefox']], ['webkit', devices['Desktop Safari']]] : [])];

export default defineConfig({
  testDir: './tests',
  outputDir: 'reports/test-results',
  reporter: [['list'], ['html', { outputFolder: 'reports/playwright', open: 'never' }], ['json', { outputFile: 'reports/results.json' }]],
  fullyParallel: true,
  grepInvert: process.env.SHELTER_VISUAL ? undefined : /@visual/, // visual baselines only after the visual design is approved
  expect: { toHaveScreenshot: { maxDiffPixelRatio: 0.01, animations: 'disabled' } },
  use: { baseURL: BASE, screenshot: 'only-on-failure', trace: 'retain-on-failure' },
  projects: engines.flatMap(([engine, device]) => VIEWPORTS.map(([name, width, height, touch, group]) => ({
    name: `${engine}-${name}`,
    metadata: { group },
    use: { ...device, viewport: { width, height }, hasTouch: touch, isMobile: touch && engine !== 'firefox',
      deviceScaleFactor: group === 'zoom-200' || touch ? 2 : 1 },
  }))),
  webServer: process.env.SHELTER_BASE_URL ? undefined : {
    command: 'node scripts/serve-static.mjs ../docs/menu-ia/wireframes/html',
    url: 'http://127.0.0.1:4173/m-ar-default.html',
    reuseExistingServer: true,
  },
});
