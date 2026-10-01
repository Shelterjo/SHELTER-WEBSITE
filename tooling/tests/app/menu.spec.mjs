// App-level menu suites. Pending until the real menu page exists (framework decision DB-08).
// Each scenario maps to docs/menu-ia/USER-FLOWS.md and must pass in AR (RTL) and EN (LTR) on every viewport project.
import { test } from '@playwright/test';

const SCENARIOS = [
  'F-01 opens menu: content and prices visible before JS, menu_view fired once',
  'F-02 search: suggestions + live filter + jump-to-product highlight; Arabic query on EN page and vice versa',
  'F-03 branch change keeps scroll context and updates ?branch without a history entry',
  'F-04/F-05 product detail: sheet (mobile) / modal (desktop); Back closes first and restores scroll; Esc; focus returns',
  'F-06/F-07 category chip, subcategory chip and All Categories sheet scroll to the right anchor under sticky bars',
  'F-08/F-09 entering from DRIVE / HOUSE pages pre-selects the branch',
  'F-10 closed branch still allows full browsing and shows next opening time',
  'F-11 closing soon (≤ 60 min) and overnight shifts (Asia/Amman clock mocked)',
  'F-12 unavailable item: Show vs Hide per branch; nothing shown while availability is UNKNOWN',
  'F-13 zero-result search: message, clear, browse categories, zero_result_search fired with sanitized term',
  'missing product image renders the intentional text card',
  'long names wrap (no truncation) at 320/360/390/430',
  'scroll restoration after back navigation from another page',
];

test.describe('menu app (pending — no app yet)', () => {
  for (const s of SCENARIOS) test.fixme(s, async () => {});
});
