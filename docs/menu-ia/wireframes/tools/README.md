# Wireframe tooling (not production code)

These scripts build the low-fi wireframes and evidence from the frozen inventory (`docs/phase-01-discovery/menu/menu-inventory-v1.0.csv`).

| Script | Purpose |
|---|---|
| `subcats.py` | Subcategory proposal → `../../subcategory-mapping.csv` + `subcats.json` |
| `wf.py` | Low-fi HTML wireframes (all states) → `../html/` |
| `measure.js` | Breakpoint measurements with Playwright → JSON (see `../../evidence/`) |
| `shots.js` | Screenshots → `../png/` |
| `compose.js` | Contact sheets → `../png/SHEET-*.png` |

Run from this folder: `python3 subcats.py && python3 wf.py && NODE_PATH=$(npm root -g) node shots.js && node measure.js v3 && node compose.js`
(`wf.py` imports `../../evidence/hours_logic_check.py` and reads the inventory CSV.)

**Design system (M34):** `wf.py` inlines `design-system/build/tokens.css` + `design-system/wireframe-kit.css` (read at build time) and its own CSS uses only `var(--…)` tokens and kit classes. Check with `cd tooling && npm run ds:audit`. Latest measurements: `../../evidence/breakpoints-measure-v4-tokens.json`.
`package.json` in this folder keeps the `.js` scripts CommonJS when the repo root declares `"type": "module"`.
