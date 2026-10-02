// Copies the ALLOW-LIST of Lucide icons (DS-012: one icon library) from lucide-static into resources/icons/
// and normalises them for <x-ui.icon>: stroke-width = 1.75 (tokens.json size.icon.stroke), no license comment,
// no class/width/height (the component sets size and accessibility attributes). Icons not on the list are removed,
// so the folder always equals the list. The ISC licence text is kept next to the icons (resources/icons/LICENSE).
// Usage: npm run icons
import { readFileSync, readdirSync, writeFileSync, mkdirSync, rmSync, copyFileSync } from 'node:fs';
import { join } from 'node:path';

const SOURCE = 'node_modules/lucide-static/icons';
const TARGET = 'resources/icons';
const STROKE = JSON.parse(readFileSync('design-system/tokens/tokens.json', 'utf8')).size.icon.stroke.$value;

// Only icons a component or an approved pattern uses. Add here first, then use it (TOOLCHAIN §4: no full icon set).
const ICONS = [
    'arrow-right', // site: branch link, CTA nudge (directional)
    'arrow-up', // Shaltoor: send the question (never mirrors)
    'ban', // status-pill NOT SUPPORTED
    'briefcase-business', // contact page: catering, B2B & events intent
    'calendar', // event-card date
    'check', // pressed chip (state not shown by colour alone)
    'chevron-down', // select
    'chevron-left', // pagination previous (directional)
    'chevron-right', // pagination next, breadcrumb separator (directional)
    'circle-alert', // field error, danger alert
    'circle-check', // success alert, status-pill SYNCED
    'circle-x', // status-pill FAILED
    'clock', // status-pill PENDING
    'coffee', // dashboard navigation: the menu
    'download', // dashboard: download an applicant's file (FINAL-QA)
    'external-link', // dashboard: open the live page on the site
    'file-text', // careers: an uploaded file in the list
    'hand', // status-pill MANUAL ACTION REQUIRED
    'handshake', // contact page: franchise inquiries intent
    'house', // navigation (home)
    'image', // media placeholder
    'inbox', // empty state
    'info', // info alert
    'loader-circle', // loading
    'mail', // contact page: email (only once approved — D-035)
    'log-out', // dashboard sign out (directional)
    'map-pin', // event-card place
    'menu', // site header drawer + menu page "all categories"
    'message-circle', // site: WhatsApp action (no brand logos — Lucide only, D-062)
    'message-square-text', // contact page: complaints & feedback intent
    'messages-square', // Shaltoor launcher (not message-circle: that one means WhatsApp)
    'pause', // dashboard: pause an event
    'phone', // site: call action (tel:)
    'play', // dashboard: resume an event
    'refresh-cw-off', // status-pill OUT OF SYNC
    'rotate-cw', // 500 page: try again
    'search', // search field, compact search in the menu category bar (never mirrors)
    'store', // contact page: general & branches intent
    'trending-down', // stat-tile trend
    'trending-up', // stat-tile trend
    'triangle-alert', // warning alert
    'upload', // careers: the one upload area
    'x', // close
];

function normalise(svg, stroke = STROKE) {
    return (
        svg
            .replace(/<!--[\s\S]*?-->/g, '')
            // Only the root <svg> loses class/width/height (shapes such as <rect width height> keep theirs).
            .replace(/<svg\b[^>]*>/, (tag) => tag.replace(/\s(class|width|height)="[^"]*"/g, ''))
            .replace(/stroke-width="[^"]*"/g, `stroke-width="${stroke}"`)
            .replace(/\s+/g, ' ')
            .replace(/>\s+</g, '><')
            .replace(/\s+(\/?)>/g, '$1>')
            .replace(/<svg\s+/, '<svg ')
            .trim()
    );
}

mkdirSync(TARGET, { recursive: true });
const wanted = new Set(ICONS.map((name) => `${name}.svg`));
for (const file of readdirSync(TARGET)) {
    if (file.endsWith('.svg') && !wanted.has(file)) rmSync(join(TARGET, file));
}
for (const name of ICONS) {
    const svg = normalise(readFileSync(join(SOURCE, `${name}.svg`), 'utf8'));
    if (!svg.includes(`stroke-width="${STROKE}"`))
        throw new Error(`icons: ${name} has no stroke-width after normalising`);
    writeFileSync(join(TARGET, `${name}.svg`), `${svg}\n`);
}
copyFileSync('node_modules/lucide-static/LICENSE', join(TARGET, 'LICENSE'));
console.log(`icons: ${ICONS.length} Lucide icons → ${TARGET} (stroke ${STROKE})`);
