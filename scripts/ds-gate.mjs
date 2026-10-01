// Design-system gate (M34 §23 "no one-off styles", DS-023, TOOLCHAIN.md "ds-audit as a gate instead of Stylelint").
// Fails (exit 1) when application CSS or Blade views leave the token system:
//   CSS (resources/css/**/*.css)
//     - raw colours (hex, rgb()/hsl()/…, named colours)          → var(--color-*)
//     - px values (allowed only in @media/@container conditions and as a 1px border)
//     - non-token radius / box-shadow / font-size / z-index / letter-spacing / durations
//     - physical direction properties (margin-left, padding-right, left/right, text-align: left …) → logical ones
//     - stroke-width other than the icon token
//   Blade (resources/views/**/*.blade.php)
//     - inline style attributes or <style> blocks (CSP + M34 §23)
//     - raw colours in fill / stroke / color attributes, stroke-width other than the token
//     - `ui-*` classes that no stylesheet defines (catches leftovers such as .ui-btn)
//   Icons (resources/icons/*.svg): stroke-width must equal tokens.json size.icon.stroke (1.75).
// The rules are self-tested on every run (fixtures below) so a broken regex cannot silently pass everything.
// Usage: node scripts/ds-gate.mjs
import { readFileSync, readdirSync, statSync } from 'node:fs';
import { join, relative } from 'node:path';

const ROOT = process.cwd();
const STROKE = String(JSON.parse(readFileSync('design-system/tokens/tokens.json', 'utf8')).size.icon.stroke.$value);

// CSS named colours (CSS Color 4). `transparent` and `currentColor` are allowed keywords, not colours.
const NAMED = new Set(
    (
        'aliceblue antiquewhite aqua aquamarine azure beige bisque black blanchedalmond blue blueviolet brown burlywood ' +
        'cadetblue chartreuse chocolate coral cornflowerblue cornsilk crimson cyan darkblue darkcyan darkgoldenrod darkgray ' +
        'darkgreen darkgrey darkkhaki darkmagenta darkolivegreen darkorange darkorchid darkred darksalmon darkseagreen ' +
        'darkslateblue darkslategray darkslategrey darkturquoise darkviolet deeppink deepskyblue dimgray dimgrey dodgerblue ' +
        'firebrick floralwhite forestgreen fuchsia gainsboro ghostwhite gold goldenrod gray green greenyellow grey honeydew ' +
        'hotpink indianred indigo ivory khaki lavender lavenderblush lawngreen lemonchiffon lightblue lightcoral lightcyan ' +
        'lightgoldenrodyellow lightgray lightgreen lightgrey lightpink lightsalmon lightseagreen lightskyblue lightslategray ' +
        'lightslategrey lightsteelblue lightyellow lime limegreen linen magenta maroon mediumaquamarine mediumblue ' +
        'mediumorchid mediumpurple mediumseagreen mediumslateblue mediumspringgreen mediumturquoise mediumvioletred ' +
        'midnightblue mintcream mistyrose moccasin navajowhite navy oldlace olive olivedrab orange orangered orchid ' +
        'palegoldenrod palegreen paleturquoise palevioletred papayawhip peachpuff peru pink plum powderblue purple ' +
        'rebeccapurple red rosybrown royalblue saddlebrown salmon sandybrown seagreen seashell sienna silver skyblue ' +
        'slateblue slategray slategrey snow springgreen steelblue tan teal thistle tomato turquoise violet wheat white ' +
        'whitesmoke yellow yellowgreen'
    ).split(' '),
);
const COLOR_PROPS =
    /^(color|background(-color)?|border(-(block|inline|top|bottom|left|right)(-(start|end))?)?(-color)?|outline(-color)?|fill|stroke|box-shadow|text-decoration(-color)?|accent-color|caret-color|column-rule(-color)?|text-shadow|--.*)$/;
const RAW_COLOR = /#[0-9a-f]{3,8}\b|\b(?:rgba?|hsla?|hwb|lab|lch|oklab|oklch|color)\(/i;
const PX = /(?<![\w.-])-?\d*\.?\d+px\b/;
const WIDE_KEYWORDS = '(?:inherit|initial|unset|revert)';
const only = (tokenPattern) => new RegExp(`^(?:(?:${tokenPattern}|${WIDE_KEYWORDS})\\s*)+$`);
const TOKEN_ONLY = {
    radius: only('var\\(--radius-[a-z0-9-]+\\)|0|inherit'),
    shadow: only('var\\(--shadow-[a-z0-9-]+\\)|none'),
    fontSize: only('var\\(--text-[a-z0-9-]+\\)'),
    zIndex: only('var\\(--z-[a-zA-Z0-9-]+\\)|auto'),
    tracking: only('var\\(--tracking-[a-z0-9-]+\\)|0|normal'),
    stroke: only(`var\\(--icon-stroke\\)|${STROKE.replace('.', '\\.')}`),
};
const PHYSICAL_PROPS =
    /^(margin|padding|border|scroll-margin|scroll-padding)-(left|right)(-.*)?$|^(left|right)$|^border-(top|bottom)-(left|right)-radius$/;
const DURATION = /(?<![\w-])\d*\.?\d+m?s\b/;

const lineOf = (text, index) => text.slice(0, index).split('\n').length;
// Comments become spaces (line numbers stay right; "3px" in a comment is not a value).
const stripComments = (css) => css.replace(/\/\*[\s\S]*?\*\//g, (c) => c.replace(/[^\n]/g, ' '));

export function checkCss(source, file = 'inline.css') {
    const css = stripComments(source);
    const out = [];
    const flag = (index, rule, text) => out.push({ file, line: lineOf(css, index), rule, text: text.trim() });
    let start = 0;
    for (let i = 0; i < css.length; i++) {
        const ch = css[i];
        if (ch !== '{' && ch !== ';' && ch !== '}') continue;
        const chunk = css.slice(start, i);
        const at = start + (chunk.length - chunk.trimStart().length);
        start = i + 1;
        if (ch === '{') {
            // Selector or at-rule prelude: px allowed only in @media / @container conditions.
            if (
                /^\s*@/.test(chunk) &&
                !/^\s*@(media|container|supports|keyframes|starting-style|layer|font-face|view-transition)\b/.test(
                    chunk,
                )
            ) {
                flag(at, 'unknown at-rule', chunk);
            }
            continue;
        }
        const text = chunk.trim();
        if (text === '' || text.startsWith('@')) continue;
        const colon = text.indexOf(':');
        if (colon < 1) continue;
        const prop = text.slice(0, colon).trim().toLowerCase();
        const value = text
            .slice(colon + 1)
            .replace(/!important\s*$/, '')
            .trim();
        const lower = value.toLowerCase();
        if (COLOR_PROPS.test(prop)) {
            if (RAW_COLOR.test(value)) flag(at, 'raw colour (use var(--color-*))', text);
            else if (lower.split(/[\s,()]+/).some((word) => NAMED.has(word)))
                flag(at, 'named colour (use var(--color-*))', text);
        }
        const pxAllowed =
            /^border(-(block|inline|top|bottom)(-(start|end))?)?(-width)?$/.test(prop) && /^1px\b/.test(value);
        if (PX.test(value) && !pxAllowed) flag(at, 'px value (use a token)', text);
        if (/^border(-[a-z]+)*-radius$/.test(prop) && !TOKEN_ONLY.radius.test(value))
            flag(at, 'non-token radius', text);
        if (prop === 'box-shadow' && !TOKEN_ONLY.shadow.test(value)) flag(at, 'non-token shadow', text);
        if (prop === 'text-shadow' && lower !== 'none') flag(at, 'text-shadow is not in the system', text);
        if (prop === 'font-size' && !TOKEN_ONLY.fontSize.test(value)) flag(at, 'non-token font-size', text);
        if (prop === 'font' && !/^(inherit|initial|unset|revert)$/.test(lower))
            flag(at, 'font shorthand (use tokens)', text);
        if (prop === 'z-index' && !TOKEN_ONLY.zIndex.test(value)) flag(at, 'non-token z-index', text);
        if (prop === 'letter-spacing' && !TOKEN_ONLY.tracking.test(value)) flag(at, 'non-token letter-spacing', text);
        if (prop === 'stroke-width' && !TOKEN_ONLY.stroke.test(value)) flag(at, `stroke-width must be ${STROKE}`, text);
        if (
            /^(transition|animation)(-duration|-delay)?$/.test(prop) &&
            DURATION.test(value.replace(/var\([^)]*\)/g, ''))
        )
            flag(at, 'raw duration (use var(--motion-*))', text);
        if (PHYSICAL_PROPS.test(prop)) flag(at, 'physical direction property (use logical inline-start/end)', text);
        if (/^(text-align|float|clear)$/.test(prop) && /^(left|right)$/.test(lower))
            flag(at, 'physical direction value (use start/end)', text);
    }
    return out;
}

export function cssClasses(source) {
    const classes = new Set();
    for (const m of stripComments(source).matchAll(/\.(ui-[a-z0-9_-]+)/g)) classes.add(m[1]);
    return classes;
}

export function checkBlade(source, file = 'inline.blade.php', known = null) {
    const out = [];
    const flag = (index, rule, text) => out.push({ file, line: lineOf(source, index), rule, text: text.trim() });
    // Blade comments are documentation, not markup.
    const blade = source.replace(/\{\{--[\s\S]*?--\}\}/g, (c) => c.replace(/[^\n]/g, ' '));
    for (const m of blade.matchAll(/\s:?style\s*=/g)) flag(m.index, 'inline style attribute', m[0]);
    for (const m of blade.matchAll(/<style\b/g)) flag(m.index, '<style> block in a view', m[0]);
    for (const m of blade.matchAll(/\b(fill|stroke|color|bgcolor)\s*=\s*["']([^"']*)["']/g)) {
        if (RAW_COLOR.test(m[2]) || NAMED.has(m[2].toLowerCase())) flag(m.index, 'raw colour attribute', m[0]);
    }
    for (const m of blade.matchAll(/stroke-width\s*=\s*["']([^"']*)["']/g)) {
        if (m[1] !== STROKE) flag(m.index, `stroke-width must be ${STROKE}`, m[0]);
    }
    if (known) {
        const used = [];
        for (const m of blade.matchAll(/\bclass\s*=\s*"([^"]*)"/g)) {
            for (const token of m[1].split(/\s+/)) used.push([m.index, token]);
        }
        for (const m of blade.matchAll(/'(ui-[a-z0-9_-]+)'/g)) used.push([m.index, m[1]]);
        for (const [index, token] of used) {
            if (!/^ui-[a-z0-9_-]*[a-z0-9]$/.test(token) || /[{}$@]/.test(token)) continue;
            if (!known.has(token)) flag(index, `unknown class .${token} (not defined in resources/css)`, token);
        }
    }
    return out;
}

export function checkIcon(svg, file = 'icon.svg') {
    const widths = [...svg.matchAll(/stroke-width="([^"]*)"/g)].map((m) => m[1]);
    if (widths.length === 0 || widths.some((w) => w !== STROKE)) {
        return [{ file, line: 1, rule: `icon stroke-width must be ${STROKE}`, text: widths.join(', ') || 'missing' }];
    }
    return [];
}

function selfTest() {
    const cases = [
        [checkCss('.a{color:#fff}').length, 1],
        [checkCss('.a{background:rgb(0 0 0 / .5)}').length, 1],
        [checkCss('.a{color:red}').length, 1],
        [checkCss('.a{color:var(--color-text);background:transparent}').length, 0],
        [checkCss('.a{padding:17px}').length, 1],
        [checkCss('@media (min-width: 600px){.a{padding:var(--space-4)}}').length, 0],
        [checkCss("@font-face{font-family:'X';font-display:swap;src:url('/a.woff2') format('woff2')}").length, 0],
        [checkCss('@property --x{syntax:"*"}').length, 1],
        // Cross-document page transitions (M40 §31, TOOL-047): the at-rule is allowed, durations still need tokens.
        [checkCss('@view-transition{navigation:auto}').length, 0],
        [checkCss('::view-transition-old(root){animation-duration:250ms}').length, 1],
        [checkCss('.a{border-block-end:1px solid var(--color-border)}').length, 0],
        [checkCss('.a{border-radius:12px}').length, 2],
        [checkCss('.a{border-start-start-radius:var(--radius-xl)}').length, 0],
        [checkCss('.a{box-shadow:0 0 4px var(--color-text)}').length, 2],
        [checkCss('.a{font-size:15px}').length, 2],
        [checkCss('.a{font-size:1.1rem}').length, 1],
        [checkCss('.a{z-index:999}').length, 1],
        [checkCss('.a{transition:color 150ms ease}').length, 1],
        [checkCss('.a{transition:color var(--motion-fast) var(--easing-standard)}').length, 0],
        [checkCss('.a{margin-left:var(--space-2)}').length, 1],
        [checkCss('.a{left:0}').length, 1],
        [checkCss('.a{text-align:right}').length, 1],
        [checkCss('.a{inset-inline-start:0;text-align:start}').length, 0],
        [checkCss('.a{stroke-width:2}').length, 1],
        [checkCss('/* 3px #fff */ .a{color:inherit}').length, 0],
        [checkBlade('<div style="color:red">').length, 1],
        [checkBlade('<a href="#main" class="ui-x">').length, 0],
        [checkBlade('<svg stroke-width="2">').length, 1],
        [checkBlade('<path fill="#000">').length, 1],
        [checkBlade('<b class="ui-btn ui-ok">', 'f', new Set(['ui-ok'])).length, 1],
        [checkBlade("@php $c = ['ui-gone' => true, 'ui-ok--'.$x]; @endphp", 'f', new Set(['ui-ok'])).length, 1],
        [checkIcon('<svg stroke-width="2"></svg>').length, 1],
        [checkIcon(`<svg stroke-width="${STROKE}"></svg>`).length, 0],
    ];
    const failed = cases.map(([got, want], i) => [i, got, want]).filter(([, got, want]) => got !== want);
    if (failed.length > 0) {
        for (const [i, got, want] of failed)
            console.error(`ds-gate self-test #${i}: ${got} violation(s), expected ${want}`);
        process.exit(2);
    }
    return cases.length;
}

function files(dir, suffix) {
    const result = [];
    for (const entry of readdirSync(dir)) {
        const path = join(dir, entry);
        if (statSync(path).isDirectory()) result.push(...files(path, suffix));
        else if (path.endsWith(suffix)) result.push(path);
    }
    return result;
}

const tests = selfTest();
const cssFiles = files('resources/css', '.css');
const known = new Set();
const violations = [];
for (const file of cssFiles) {
    const css = readFileSync(file, 'utf8');
    violations.push(...checkCss(css, relative(ROOT, file)));
    for (const c of cssClasses(css)) known.add(c);
}
const views = files('resources/views', '.blade.php');
for (const file of views) violations.push(...checkBlade(readFileSync(file, 'utf8'), relative(ROOT, file), known));
const icons = files('resources/icons', '.svg');
for (const file of icons) violations.push(...checkIcon(readFileSync(file, 'utf8'), relative(ROOT, file)));

for (const v of violations) console.error(`${v.file}:${v.line}  ${v.rule}  →  ${v.text}`);
console.log(
    `ds-gate: ${cssFiles.length} stylesheets · ${views.length} views · ${icons.length} icons · ${tests} rule self-tests · ` +
        `${violations.length} violation(s)`,
);
if (violations.length > 0) process.exit(1);
