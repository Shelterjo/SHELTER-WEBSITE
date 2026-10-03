# Website redesign — first implementation draft

Owner request, 2026-10-03: rebuild the Shelter website with Figma / UI UX Designer / GitHub and extract the current public site.

## Implementation
- Retain Laravel, Blade, existing master-data services, approved menu and business rules.
- Put branch decisions alongside the primary menu action on the homepage using an optional hero aside.
- Central rounded tokens (12 / 20 / 28), consistent card surfaces, framed menu tools, desktop navigation and footer.
- Existing campaign placement and recognition remain conditional; no fabricated offers, opening status or media.
- Source capture is evidence only. See source-2026-10-03/README.md for counts and conflicts.

## Design
https://www.figma.com/design/nxhW7Dko9MgD6itWGvT0oN
Four editable English concept frames: home and menu, desktop and mobile; shared button/product components and color variables. Poppins verified. Noto Kufi Arabic is unavailable in Figma's current font list; Arabic remains the existing self-hosted website font. Figma is a concept, not a pixel-exact representation of all runtime states.

## Verification and limitations
- brand-guard: PASS.
- ds-gate: PASS, 78 stylesheets / 161 views / 45 icons / 33 rule self-tests, zero violations.
- git diff --check: PASS.
- Figma desktop home and mobile menu visually inspected after sizing correction.
- Browser runtime unavailable: Playwright package exists but Chromium binary is absent; repository forbids playwright install. Responsive previews are IMPLEMENTED — NOT YET VERIFIED.
- PHP/Composer unavailable: Laravel rendering, PHPUnit, full responsive matrix, accessibility and production readiness NOT verified here. Existing historical test claims do not apply to this change.
- preview-ar.html / preview-en.html are static review fixtures using real CSS and an approved menu sample; they are not the running app.
- Draft only. No production deployment, DB import, DNS or live-site edits.
