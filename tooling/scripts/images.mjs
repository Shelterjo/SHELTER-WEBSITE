// SHELTER image pipeline (Sharp): owner-approved originals → responsive 1:1 AVIF + WebP variants.
// Rules (D-083, D-144, R-04): only APPROVED media are processed · originals are never modified · never upscale · focal-point crop · sRGB · metadata stripped.
// Usage: node scripts/images.mjs [--manifest ../media/manifest.json] [--out ../media/derived]   |   node scripts/images.mjs --selftest
import sharp from 'sharp';
import { readFile, writeFile, mkdir, mkdtemp, rm, stat } from 'node:fs/promises';
import { createHash } from 'node:crypto';
import { dirname, join, resolve } from 'node:path';
import { tmpdir } from 'node:os';

const WIDTHS = [240, 360, 480, 720, 1080];
const MIN_SOURCE = 720; // below this the source is flagged as too small for sharp cards on 2× screens
const FORMATS = { avif: { quality: 55, effort: 4 }, webp: { quality: 72 } };
const arg = (k, d) => { const i = process.argv.indexOf(k); return i > -1 ? process.argv[i + 1] : d; };
const sha = async f => createHash('sha256').update(await readFile(f)).digest('hex');

export async function build(manifestPath, outDir) {
  const base = dirname(resolve(manifestPath));
  const items = JSON.parse(await readFile(manifestPath, 'utf8'));
  const out = []; const log = [];
  for (const m of items) {
    if (m.approval_status !== 'APPROVED') { log.push(`SKIP ${m.media_id}: approval_status=${m.approval_status}`); continue; }
    const src = resolve(base, m.file); const before = await sha(src);
    const img = sharp(src).rotate(); const meta = await img.metadata();
    const W = meta.autoOrient?.width ?? meta.width, H = meta.autoOrient?.height ?? meta.height, side = Math.min(W, H);
    const [fx, fy] = m.focal_point ?? [0.5, 0.5];
    const left = Math.round(Math.min(Math.max(fx * W - side / 2, 0), W - side)), top = Math.round(Math.min(Math.max(fy * H - side / 2, 0), H - side));
    const widths = WIDTHS.filter(w => w <= side);
    if (side < MIN_SOURCE) log.push(`WARN ${m.media_id}: source square side ${side}px < ${MIN_SOURCE}px (no upscaling; ask for a larger original)`);
    const variants = [];
    for (const w of widths) for (const [fmt, opts] of Object.entries(FORMATS)) {
      const rel = `${m.product_id}/${m.role || 'main'}-${w}.${fmt}`; const dest = join(outDir, rel);
      await mkdir(dirname(dest), { recursive: true });
      const info = await sharp(src).rotate().extract({ left, top, width: side, height: side }).resize(w, w).toColorspace('srgb')[fmt](opts).toFile(dest);
      variants.push({ format: fmt, width: w, bytes: info.size, path: rel });
    }
    if (await sha(src) !== before) throw new Error(`original modified: ${m.file}`);
    out.push({ media_id: m.media_id, product_id: m.product_id, role: m.role || 'main', source: { width: W, height: H, sha256: before }, crop: { left, top, side },
      alt_ar: m.alt_ar ?? null, alt_en: m.alt_en ?? null, variants,
      srcset: Object.fromEntries(Object.keys(FORMATS).map(f => [f, variants.filter(v => v.format === f).map(v => `${v.path} ${v.width}w`).join(', ')])) });
  }
  await mkdir(outDir, { recursive: true });
  await writeFile(join(outDir, 'derived-manifest.json'), JSON.stringify(out, null, 2));
  return { out, log };
}

async function selftest() {
  // Synthetic test images only (no product photos). Verifies: approval gate, no upscaling, focal crop, both formats, originals untouched.
  const dir = await mkdtemp(join(tmpdir(), 'shelter-img-'));
  const mk = (w, h) => sharp({ create: { width: w, height: h, channels: 3, background: { r: 120, g: 90, b: 60 } } }).jpeg().toBuffer();
  await writeFile(join(dir, 'big.jpg'), await mk(1600, 1200)); await writeFile(join(dir, 'small.jpg'), await mk(500, 500)); await writeFile(join(dir, 'pending.jpg'), await mk(1200, 1200));
  await writeFile(join(dir, 'manifest.json'), JSON.stringify([
    { media_id: 'MED-TEST-1', product_id: 'TEST-PRODUCT-A', file: 'big.jpg', approval_status: 'APPROVED', focal_point: [0.7, 0.5] },
    { media_id: 'MED-TEST-2', product_id: 'TEST-PRODUCT-B', file: 'small.jpg', approval_status: 'APPROVED' },
    { media_id: 'MED-TEST-3', product_id: 'TEST-PRODUCT-C', file: 'pending.jpg', approval_status: 'PENDING' }]));
  const { out, log } = await build(join(dir, 'manifest.json'), join(dir, 'derived'));
  const checks = {
    'pending media skipped': !out.find(o => o.media_id === 'MED-TEST-3') && log.some(l => l.startsWith('SKIP MED-TEST-3')),
    'large source → 5 widths × 2 formats': out[0].variants.length === 10,
    'no upscaling (500px source → 240/360/480 only)': JSON.stringify([...new Set(out[1].variants.map(v => v.width))]) === '[240,360,480]',
    'small source flagged': log.some(l => l.startsWith('WARN MED-TEST-2')),
    'focal crop shifts window (fx=0.7)': out[0].crop.left === 400 && out[0].crop.side === 1200,
    'avif + webp present': ['avif', 'webp'].every(f => out[0].variants.some(v => v.format === f)),
    'outputs are square': (await sharp(join(dir, 'derived', out[0].variants[0].path)).metadata()).width === 240,
  };
  await rm(dir, { recursive: true, force: true });
  for (const [k, v] of Object.entries(checks)) console.log(`${v ? 'PASS' : 'FAIL'}  ${k}`);
  if (Object.values(checks).some(v => !v)) process.exit(1);
}

if (process.argv.includes('--selftest')) await selftest();
else {
  const { out, log } = await build(arg('--manifest', '../media/manifest.json'), arg('--out', '../media/derived'));
  log.forEach(l => console.log(l)); console.log(`built ${out.length} approved media`);
}
