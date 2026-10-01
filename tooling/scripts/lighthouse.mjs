// Repeatable Lighthouse audits with root-cause output (not just scores). Mobile emulation + simulated throttling (Lighthouse defaults).
// Usage: node scripts/lighthouse.mjs [--assert] [url ...]
//   No URLs → audits the low-fi menu prototype (served locally). With the real app: SHELTER_BASE_URL=https://preview… node scripts/lighthouse.mjs /ar/jo/menu/
import lighthouse from 'lighthouse';
import * as chromeLauncher from 'chrome-launcher';
import { mkdir, writeFile } from 'node:fs/promises';
import { serve } from './serve-static.mjs';

const args = process.argv.slice(2);
const ASSERT = args.includes('--assert');
const paths = args.filter(a => !a.startsWith('--'));
const CHROME = process.env.CHROME_PATH || '/opt/pw-browsers/chromium';
// Gates from docs/menu-ia/PERFORMANCE-BUDGET.md
const GATES = { performance: 0.9, accessibility: 0.95, 'best-practices': 0.9, seo: 0.9, lcp: 2500, cls: 0.1, tbt: 200 };

let server, base = process.env.SHELTER_BASE_URL;
if (!base) { server = await serve('../docs/menu-ia/wireframes/html', 4174); base = 'http://127.0.0.1:4174/'; }
const targets = (paths.length ? paths : ['m-ar-default.html', 'm-en-default.html']).map(p => new URL(p, base).href);

await mkdir('reports/lighthouse', { recursive: true });
const chrome = await chromeLauncher.launch({ chromePath: CHROME, chromeFlags: ['--headless=new', '--no-sandbox'] });
let failed = 0;
for (const url of targets) {
  const { lhr, report } = await lighthouse(url, { port: chrome.port, output: ['html', 'json'], logLevel: 'error',
    onlyCategories: ['performance', 'accessibility', 'best-practices', 'seo'] });
  const name = url.replace(/^https?:\/\//, '').replace(/[^a-z0-9]+/gi, '_').slice(0, 80);
  await writeFile(`reports/lighthouse/${name}.html`, report[0]); await writeFile(`reports/lighthouse/${name}.json`, report[1]);
  const s = Object.fromEntries(Object.entries(lhr.categories).map(([k, v]) => [k, v.score]));
  const m = { lcp: lhr.audits['largest-contentful-paint'].numericValue, cls: lhr.audits['cumulative-layout-shift'].numericValue, tbt: lhr.audits['total-blocking-time'].numericValue };
  // Root causes: failing audits with real weight, in plain language order (opportunities first)
  const causes = Object.values(lhr.audits).filter(a => a.score !== null && a.score < 0.9 && a.scoreDisplayMode !== 'informative' && a.scoreDisplayMode !== 'notApplicable')
    .sort((a, b) => (b.details?.overallSavingsMs || 0) - (a.details?.overallSavingsMs || 0)).slice(0, 8).map(a => `${a.id}: ${a.title}${a.displayValue ? ' — ' + a.displayValue : ''}`);
  const gateFails = [...Object.entries(s).filter(([k, v]) => GATES[k] && v < GATES[k]).map(([k, v]) => `${k} ${v} < ${GATES[k]}`),
    ...['lcp', 'cls', 'tbt'].filter(k => m[k] > GATES[k]).map(k => `${k} ${Math.round(m[k] * 1000) / 1000} > ${GATES[k]}`)];
  failed += gateFails.length ? 1 : 0;
  console.log(`\n${url}\n  scores: ${JSON.stringify(s)}\n  LCP ${Math.round(m.lcp)}ms · CLS ${m.cls.toFixed(3)} · TBT ${Math.round(m.tbt)}ms\n  gates: ${gateFails.length ? 'FAIL → ' + gateFails.join('; ') : 'PASS'}\n  root causes:\n    ${causes.join('\n    ') || '—'}`);
}
await chrome.kill(); if (server) server.close();
if (ASSERT && failed) process.exit(1);
