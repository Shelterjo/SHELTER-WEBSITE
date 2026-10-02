// شلتور images (D-348): built only from the Owner's original (resources/brand/source/shaltoor-owner-original.webp,
// sent by the Owner as "the bot's icon", M71). Technical processing only — crop, resize, compress; the drawing, colours
// and white background are never changed and nothing is generated. Outputs in public/brand/:
//   shaltoor-avatar-{64,128,192}.{webp,png}  square crop of the head and keffiyeh (shown round by CSS)
//   shaltoor-{240,480}.{webp,png}           the whole figure (the welcome inside the conversation)
// Run: node scripts/shaltoor-avatar.mjs  (from tooling/)
import { resolve } from 'node:path';
import sharp from 'sharp';

const root = resolve(import.meta.dirname, '../..');
const source = resolve(root, 'resources/brand/source/shaltoor-owner-original.webp');
const out = (name) => resolve(root, 'public/brand', name);

// The head and keffiyeh of the 1254 × 1254 original (chosen on a preview over the site background #131313).
const HEAD = { left: 255, top: 55, width: 720, height: 720 };

const meta = await sharp(source).metadata();
if (meta.width !== 1254 || meta.height !== 1254) {
    throw new Error(`shaltoor-avatar: expected the 1254 × 1254 original, got ${meta.width} × ${meta.height}`);
}
for (const size of [64, 128, 192]) {
    const head = sharp(source).extract(HEAD).resize(size, size, { kernel: 'lanczos3' });
    await head.clone().webp({ quality: 86, effort: 6 }).toFile(out(`shaltoor-avatar-${size}.webp`));
    await head.clone().png({ compressionLevel: 9, palette: true, quality: 90 }).toFile(out(`shaltoor-avatar-${size}.png`));
}
for (const size of [240, 480]) {
    const whole = sharp(source).resize(size, size, { kernel: 'lanczos3' });
    await whole.clone().webp({ quality: 84, effort: 6 }).toFile(out(`shaltoor-${size}.webp`));
    await whole.clone().png({ compressionLevel: 9, palette: true, quality: 90 }).toFile(out(`shaltoor-${size}.png`));
}
console.log('shaltoor-avatar: 10 files in public/brand/');
