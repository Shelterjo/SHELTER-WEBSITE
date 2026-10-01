// Generates CSS custom properties from design-system/tokens/tokens.json (single source).
// Output: design-system/build/tokens.css (structural tokens + wireframe placeholder colours/fonts until brand files arrive).
// Usage: node scripts/tokens-css.mjs
import { readFile, writeFile, mkdir } from 'node:fs/promises';

const t = JSON.parse(await readFile('../design-system/tokens/tokens.json', 'utf8'));
const v = x => (x && typeof x === 'object' && '$value' in x ? x.$value : x);
const lines = [];
const add = (name, val) => lines.push(`  --${name}: ${Array.isArray(val) ? val.map(f => (/\s/.test(f) ? `"${f}"` : f)).join(', ') : val};`);
const walk = (obj, prefix, skip = []) => {
  for (const [k, x] of Object.entries(obj)) {
    if (k.startsWith('$') || skip.includes(k)) continue;
    if (x && typeof x === 'object' && !('$value' in x)) walk(x, `${prefix}-${k}`, skip); else add(`${prefix}-${k}`, v(x));
  }
};
walk(t.font.size, 'text'); walk(t.font.weight, 'weight'); walk(t.font.lineHeight, 'leading'); walk(t.font.letterSpacing, 'tracking');
add('measure-reading', v(t.font.measure.reading));
walk(t.space, 'space'); walk(t.radius, 'radius'); walk(t.shadow, 'shadow');
walk(t.motion.duration, 'motion'); walk(t.motion.easing, 'easing');
add('touch-target', v(t.size.touchTarget)); walk(t.size.control, 'control'); walk(t.size.icon, 'icon'); walk(t.size.container, 'container');
add('sticky-stack', v(t.size.stickyStack)); walk(t.z, 'z');
add('border-default', v(t.border.width.default)); add('border-strong', v(t.border.width.strong));
// Fonts + colours: brand values are MISSING → emit wireframe placeholders (clearly marked).
add('font-ar', v(t.font.family.wireframe)); add('font-en', v(t.font.family.wireframe));
for (const [k, x] of Object.entries(t.color)) if (!k.startsWith('$')) add(`color-${k}`, v(x.wireframe));
const css = `/* GENERATED from design-system/tokens/tokens.json by tooling/scripts/tokens-css.mjs — do not edit by hand.
   Fonts and colours are WIREFRAME PLACEHOLDERS until the brand identity files arrive (M-10). */
:root {
${lines.join('\n')}
}
@media (prefers-reduced-motion: reduce) { :root { --motion-fast: 0ms; --motion-normal: 0ms; --motion-slow: 0ms; } }
`;
await mkdir('../design-system/build', { recursive: true });
await writeFile('../design-system/build/tokens.css', css);
console.log(`tokens.css: ${lines.length} custom properties`);
