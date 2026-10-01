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
