// Share card (Open Graph, 1200 × 630) built only from approved brand parts: the white logo (AST-001), the brand
// background #131313, the hairline hexagon of the hero (from the emblem) and the brand name in Poppins (D-007).
// PO-077 → A. Output stays in resources/brand/og/ until the Owner approves the card; then it is copied to public/brand/
// and config('shelter.share_image') points to it.  Run: node scripts/og-card.mjs  (from tooling/)
import { chromium } from '@playwright/test';
import { mkdirSync, mkdtempSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';

const root = resolve(import.meta.dirname, '../..');
const out = resolve(root, 'resources/brand/og');
mkdirSync(out, { recursive: true });
const html = `<!doctype html><html><head><meta charset="utf-8"><style>
@font-face{font-family:Poppins;font-weight:600;src:url('file://${root}/resources/fonts/poppins/poppins-latin-600-normal.woff2')}
html,body{margin:0;width:1200px;height:630px;overflow:hidden;background:#131313}
.card{position:relative;width:1200px;height:630px;display:grid;place-items:center}
.mark{position:absolute;top:50%;right:-330px;height:820px;transform:translateY(-50%);color:#272727}
.inner{position:relative;display:grid;justify-items:center;gap:36px}
.logo{width:520px;height:auto;display:block}
.name{font:600 22px/1 Poppins;letter-spacing:.32em;color:#9A9B9C;text-transform:uppercase;margin:0;padding-left:.32em}
</style></head><body><div class="card">
<svg class="mark" viewBox="0 0 120 138" aria-hidden="true"><path d="M60 1 119 35v68L60 137 1 103V35Z" fill="none" stroke="currentColor" stroke-width="0.6"/><path d="M60 13 108 41v56L60 125 12 97V41Z" fill="none" stroke="currentColor" stroke-width="0.6"/></svg>
<div class="inner"><img class="logo" src="file://${root}/resources/brand/source/old-site-logo-white.png" alt=""><p class="name">Shelter Coffee</p></div>
</div></body></html>`;

const browser = await chromium.launch({ executablePath: process.env.CHROME_PATH || undefined });
const page = await browser.newPage({ viewport: { width: 1200, height: 630 }, deviceScaleFactor: 1 });
// A file page, not setContent: about:blank may not read the local logo and font files.
const dir = mkdtempSync(join(tmpdir(), 'og-card-'));
writeFileSync(join(dir, 'card.html'), html);
await page.goto(`file://${join(dir, 'card.html')}`, { waitUntil: 'load' });
await page.evaluate(() => document.fonts.ready);
await page.screenshot({ path: resolve(out, 'og-default-1200x630.png') });
await browser.close();
rmSync(dir, { recursive: true, force: true });
console.log('og-card: resources/brand/og/og-default-1200x630.png');
