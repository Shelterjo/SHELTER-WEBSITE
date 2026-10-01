// Generates CSS custom properties from design-system/tokens/tokens.json (single source).
// Output:
//   design-system/build/tokens.css       — structural tokens + neutral WIREFRAME colours/fonts (low-fi prototypes in docs/).
//   design-system/build/tokens-brand.css — the same structural tokens + SHELTER BRAND colours/fonts (the Laravel app, D-309).
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
const structural = [...lines];
// Reduced motion: every duration token becomes 0ms (static alternative, DS §7).
const reduced = Object.keys(t.motion.duration).filter(k => !k.startsWith('$')).map(k => `--motion-${k}: 0ms;`).join(' ');
const emit = async (file, header, fonts, colourOf) => {
  const out = [...structural];
  out.push(`  --font-ar: ${fonts.ar.map(f => (/\s/.test(f) ? `"${f}"` : f)).join(', ')};`);
  out.push(`  --font-en: ${fonts.en.map(f => (/\s/.test(f) ? `"${f}"` : f)).join(', ')};`);
  for (const [k, x] of Object.entries(t.color)) if (!k.startsWith('$')) out.push(`  --color-${k}: ${colourOf(x)};`);
  const css = `/* GENERATED from design-system/tokens/tokens.json by tooling/scripts/tokens-css.mjs — do not edit by hand.
   ${header} */
:root {
${out.join('\n')}
}
@media (prefers-reduced-motion: reduce) { :root { ${reduced} } }
`;
  await writeFile(`../design-system/build/${file}`, css);
  console.log(`${file}: ${out.length} custom properties`);
};
const brandValue = x => {
  const val = v(x.brand);
  if (val === 'MISSING') throw new Error('brand colour MISSING — fill design-system/tokens/tokens.json');
  return val;
};
await mkdir('../design-system/build', { recursive: true });
await emit('tokens.css', 'Fonts and colours are neutral WIREFRAME PLACEHOLDERS for the low-fi prototypes only.',
  { ar: v(t.font.family.wireframe), en: v(t.font.family.wireframe) }, x => v(x.wireframe));
await emit('tokens-brand.css', 'SHELTER brand colours and fonts (old site, D-309) for the Laravel app.',
  { ar: v(t.font.family.ar), en: v(t.font.family.en) }, brandValue);
