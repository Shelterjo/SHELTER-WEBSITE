# SHELTER Design System — structure (no visual values yet)

> **Status:** `STRUCTURE ONLY` — every visual value is `MISSING — OWNER INPUT REQUIRED` until the Brand Identity files arrive (M-10 / MI-021 logo vector, MI-022 colors, fonts, guide).
> Visual Design has not started (gate: Owner approval of IA + Wireframes). Nothing here is a design decision.

## Layers
`Tokens → Primitives → Components → Patterns → Sections → Pages`

| Layer | What | Examples (from the approved Menu IA spec) |
|---|---|---|
| Tokens | Named design decisions (`tokens/`) in W3C DTCG JSON | color, typography, spacing, radius, elevation, motion, breakpoints |
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
