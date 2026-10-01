// A production release must not ship wireframe placeholder fonts/colours (DS: brand values MISSING until M-10).
import { readFileSync } from 'node:fs';

const tokens = readFileSync('design-system/tokens/tokens.json', 'utf8');
if (process.env.SHELTER_RELEASE === 'production' && tokens.includes('MISSING')) {
    console.error(
        'brand-guard: design tokens still contain MISSING brand values — production release blocked (PO-002 / M-10).',
    );
    process.exit(1);
}
console.log('brand-guard: ok');
