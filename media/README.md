# media/ — Approved media only

> **Rule (D-003 · D-056 · D-083 · Menu IA F-xx):** no image, video or graphic is used anywhere in the website until the Owner approves it.
> Do **not** use Google Images, stock, AI-generated or old-site images just because they are available.
> Every new asset enters as `PENDING OWNER APPROVAL`, with: source · license/rights · suggested placement · why selected.

| Path | Content | In git? |
|---|---|---|
| `manifest.json` | One entry per asset (metadata only). Today: `[]`, **0 approved images** | ✅ |
| `originals/` | Owner-supplied originals, never modified (checksums verified by the pipeline) | ❌ (storage decision pending) |
| `derived/` | Generated AVIF/WebP variants + `derived-manifest.json` (srcset strings) | ❌ (rebuilt on demand) |

## Manifest entry
```json
{ "media_id": "MED-00001", "product_id": "PRD-00058", "role": "main", "file": "originals/PRD-00058-main.jpg",
  "approval_status": "PENDING OWNER APPROVAL", "source": "", "license": "", "approved_by": "", "approved_at": "",
  "focal_point": [0.5, 0.5], "alt_ar": null, "alt_en": null }
```
- `approval_status` must be exactly `APPROVED` for the pipeline to process it. Everything else is skipped and logged.
- `alt_ar` / `alt_en` are content and need Owner approval like any other text (empty → decorative `alt=""` in cards).

## Pipeline (`tooling/scripts/images.mjs`, Sharp)
- 1:1 crop around the focal point (Menu IA: 1:1 product images). Widths 240 / 360 / 480 / 720 / 1080, **never upscaled**.
- AVIF (q55) + WebP (q72), sRGB, metadata stripped. Originals hash-checked before and after (never modified).
- Sources whose square side is < 720px are flagged (too small for sharp 2× cards).
- `npm run images:build` (in `tooling/`) · `npm run images:selftest` (synthetic test images only).
