// Fails the build when a compressed entry exceeds its budget (docs/menu-ia/PERFORMANCE-BUDGET.md §2, M37 §30).
import { readFileSync } from 'node:fs';
import { gzipSync } from 'node:zlib';

const KB = 1024;
const BUDGETS = {
    'resources/css/site.css': 25 * KB,
    'resources/js/site.ts': 30 * KB,
    'resources/css/dashboard.css': 40 * KB,
    'resources/js/dashboard.ts': 100 * KB,
};
const manifest = JSON.parse(readFileSync('public/build/manifest.json', 'utf8'));
const size = (file) => gzipSync(readFileSync(`public/build/${file}`)).length;
let failed = false;
for (const [entry, budget] of Object.entries(BUDGETS)) {
    const chunk = manifest[entry];
    if (!chunk) continue;
    // Entry file + its statically imported chunks + CSS it pulls in = what the first view downloads.
    const files = new Set([chunk.file, ...(chunk.css ?? [])]);
    for (const imp of chunk.imports ?? []) files.add(manifest[imp].file);
    const total = [...files].reduce((sum, f) => sum + size(f), 0);
    const ok = total <= budget;
    failed ||= !ok;
    console.log(`${ok ? 'ok  ' : 'FAIL'} ${entry}: ${(total / KB).toFixed(1)} KB gz (budget ${budget / KB} KB)`);
}
if (failed) process.exit(1);
