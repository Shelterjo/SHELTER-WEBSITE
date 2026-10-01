// A production release must ship the real brand: no MISSING brand value and no brand asset whose licence is still pending.
// D-309: brand colours, logo and fonts come from the old site; the GE SS Two web licence awaits the Owner (PO-071).
import { readFileSync } from 'node:fs';
import process from 'node:process';

const tokens = JSON.parse(readFileSync('design-system/tokens/tokens.json', 'utf8'));
const problems = [];
const walk = (node, path) => {
    if (!node || typeof node !== 'object') return;
    if (node.$value === 'MISSING') problems.push(`${path}: value MISSING`);
    const licence = node.$extensions?.shelter?.license;
    if (typeof licence === 'string' && licence.startsWith('PENDING')) problems.push(`${path}: licence ${licence}`);
    for (const [key, child] of Object.entries(node))
        if (!key.startsWith('$')) walk(child, path ? `${path}.${key}` : key);
};
walk(tokens, '');

if (process.env.SHELTER_RELEASE === 'production' && problems.length > 0) {
    console.error(`brand-guard: production release blocked —\n  ${problems.join('\n  ')}`);
    process.exit(1);
}
console.log(
    problems.length > 0
        ? `brand-guard: ok for non-production (${problems.length} open: ${problems.join('; ')})`
        : 'brand-guard: ok',
);
