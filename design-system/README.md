# SHELTER Design System — structure and library

> **Status:** `TOKENS v0.1` + **x-ui component library (PHASE 1)** — structural values (spacing, radius, type scale, motion, shadows, z-index, breakpoints, sizes) are decided in [`tokens/tokens.json`](tokens/tokens.json); **brand fonts and colour values are `MISSING`** until the Brand Identity files arrive (M-10 / PO-002). The library renders with neutral wireframe placeholders until then — nothing in it is a visual-direction decision. Standard and governance: [`../docs/DESIGN-SYSTEM-STANDARD.md`](../docs/DESIGN-SYSTEM-STANDARD.md) (owner rule M34 — FROZEN P0). Platform: [`ADR-001`](../docs/adr/ADR-001-platform.md) (Blade, no React, no Tailwind). Tools: [`../docs/TOOLCHAIN.md`](../docs/TOOLCHAIN.md).

## Layers
`Tokens → Components (Blade x-ui.*) → Patterns → Sections → Pages`

| Layer | Where | Notes |
|---|---|---|
| Tokens | `tokens/tokens.json` (W3C DTCG) → `build/tokens.css` (`npm run tokens`) | The ONLY place values live. Brand values arrive with M-10; no page changes then |
| Component CSS | `resources/css/ui.css` (base · layout · `components/*.css`) | Token-only, logical properties only. Shared by `site.css` and `dashboard.css` |
| Dashboard-only CSS | `resources/css/components/dashboard/*.css` | Shell, sidebar, page header, toolbar, stat tile, status pill, sign-in panel. Only in `dashboard.css` (M37 §30) |
| Components | `resources/views/components/ui/*.blade.php` → `<x-ui.button>` … | Anonymous Blade components: `@props` with defaults, no inline styles, strings via `__('ui.*')` (`lang/ar/ui.php` primary, `lang/en/ui.php` mirror) |
| Behaviour | `resources/js/ui/*.ts` | Progressive enhancement only (tabs, dialog fallbacks, aria-disabled guard). Native `<dialog>` / `<details>` are the primitives |
| Icons | `resources/icons/*.svg` (`npm run icons`) | Lucide allow-list, stroke 1.75 from the tokens. Add a name to `scripts/icons.mjs` first |
| Reference | Storybook (`npm run storybook`) | Stories are rendered from the Blade components themselves (below) |

## Components (36 with stories)
| Group | x-ui components |
|---|---|
| Foundations | `icon` |
| Actions | `button` (primary · secondary · outline · ghost · danger · link; sm · md · lg; icon-only; loading; `href` → `<a>`; `opens` → dialog), `chip` |
| Forms | `field` (label · hint · error, wires the control through `@aware`), `input`, `select`, `textarea`, `checkbox`, `radio`, `switch`, `fieldset`, `error-summary` |
| Display | `card` (one base), `product-card`, `event-card`, `price`, `badge`, `media-placeholder`, `table` (caption, scoped headers, scroll region, `stack` on mobile) |
| Feedback | `alert`, `empty-state`, `skeleton` |
| Navigation | `pagination`, `breadcrumb`, `skip-link`, `nav-link`, `tabs` + `tab-panel`, `disclosure` |
| Overlays | `modal`, `drawer` (end / start), `bottom-sheet` — all on the `dialog` base (native `<dialog>`, Esc, focus trap and return, title focused, `closedby="any"`, safe-area insets) |
| Dashboard | `stat-tile`, `status-pill` (SYNCED · PENDING · FAILED · NOT SUPPORTED · MANUAL ACTION REQUIRED · OUT OF SYNC, icon + text), `sidebar-item`, `page-header`, `toolbar` |

## Storybook from Blade
1. [`catalog.php`](catalog.php) lists every component's stories (props · slot · named slots · state, or a full Blade composition). Texts are `['@ar' => …, '@en' => …]` pairs.
2. `php artisan ds:export` renders each story through the real component in Arabic (RTL) and English (LTR) → `stories/generated/{component}.json` = `[{story, state, html_ar, html_en}]` (gitignored, no database). It **fails** when a component has no stories or a story has no export in its `stories/{component}.stories.ts`.
3. `stories/*.stories.ts` export one story per catalog entry through `stories/support.ts`: the locale toolbar picks `html_ar` / `html_en`; `hover` / `focus` / `active` states use pseudo-states; `open` overlays are opened in `play()`.
4. `npm run storybook` (dev) · `npm run storybook:build` (static) · `npm run test:storybook` = every story × ar/en × 360/768/1280: axe (0 serious/critical) + no horizontal overflow ([`../tests/Browser/storybook-a11y.spec.mjs`](../tests/Browser/storybook-a11y.spec.mjs)).

**Sample content rule:** no invented business data. Product cards use real approved rows of the menu inventory (an Arabic name only when `display_name_ar` is approved — spec §6 CF-03); availability, badges, events, metrics and form texts are labelled samples; no images, only the internal placeholder.

## Rules (enforced)
- **`npm run lint` runs [`../scripts/ds-gate.mjs`](../scripts/ds-gate.mjs):** no raw colours, no px (except `@media`/`@container` conditions and 1px borders), radius/shadow/font-size/z-index/letter-spacing/durations from tokens only, no physical direction properties, no inline styles or `<style>` in views, icon stroke = token, no undefined `ui-*` classes. Its rules are self-tested on every run.
- **No default library look** (no shadcn/Radix/Tailwind — ADR-001). **Brand direction (owner):** premium · minimal · warm · modern · fast · confident · distinctive · rounded corners · minimal colour. **Not:** generic AI look, generic SaaS admin, purple/blue gradients, heavy glassmorphism, random floating cards, template feel.
- **Responsive is built into each component** (mobile-first, content breakpoints 360 · 600 · 1024 · 1200, `max-height: 500px`). No device-name targeting or pixel patches.
- **RTL + LTR from logical properties** (`inline-start/end`); directional icons mirror, close/search/status icons never do.
- **State is never colour alone** (icon, text, shape or weight as well).
- The CMS edits content, **never** tokens or component styles. New needs → reuse, then extend with an approved variant (DS-022), never a page-specific style.
