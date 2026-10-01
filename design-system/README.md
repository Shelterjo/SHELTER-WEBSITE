# SHELTER Design System — structure (no visual values yet)

> **Status:** `TOKENS v0.1` — structural values (spacing, radius, type scale, motion, shadows, z-index, breakpoints, sizes) are decided in [`tokens/tokens.json`](tokens/tokens.json); **brand fonts and colour values are `MISSING`** until the Brand Identity files arrive (M-10 / PO-002). Standard and governance: [`../docs/DESIGN-SYSTEM-STANDARD.md`](../docs/DESIGN-SYSTEM-STANDARD.md) (owner rule M34 — FROZEN P0). Generated CSS: `build/tokens.css` (`cd tooling && npm run tokens:css`). Shared prototype primitives: [`wireframe-kit.css`](wireframe-kit.css). Health: `npm run ds:audit` → `docs/DESIGN-SYSTEM-HEALTH.md`.
> Visual Design has not started (gate: Owner approval of IA + Wireframes). Nothing here is a design decision.

## Layers
`Tokens → Primitives → Components → Patterns → Sections → Pages`

| Layer | What | Examples (from the approved Menu IA spec) |
|---|---|---|
| Tokens | Named design decisions in `tokens/tokens.json` (W3C DTCG JSON) — the ONLY place values live | color, typography, spacing, radius, elevation, motion, breakpoints |
| Primitives | Unstyled, accessible behaviour (Radix / React Aria **only if** the platform decision DB-08 confirms React) | Dialog, Popover, Tabs, ToggleGroup, ScrollArea |
| Components | SHELTER-styled building blocks | Button, Chip, SegmentedControl, SearchField, ProductCard, Badge, Sheet, Modal |
| Patterns | Components combined for one job | Category bar + subcategory bar, search + suggestions, branch selector + status line |
| Sections | Page regions | Menu header, seasonal block, category section, footer |
| Pages | Routes | `/ar/jo/menu/`, `/en/jo/menu/`, branch pages, dashboard screens |

## Rules
- **No default library look** (shadcn/Radix are engineering primitives only). Typography, radius, spacing, cards, navigation, motion, images, layout and buttons come from SHELTER tokens.
- **Brand direction (owner):** premium · minimal · warm · modern · fast · confident · distinctive · rounded corners · minimal colour. **Not:** generic AI look, generic SaaS admin, purple/blue gradients, heavy glassmorphism, random floating cards, template feel.
- **Responsive is built into each component** (mobile-first, container queries where useful). No device-name targeting or pixel patches.
- **RTL + LTR from logical properties** (`inline-start/end`), never hacks for one direction.
- The CMS edits content, **never** tokens or component styles (CMS cannot break the design system).
