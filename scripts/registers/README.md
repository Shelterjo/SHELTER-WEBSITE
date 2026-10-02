# Register generator

Builds the master governance documents from structured JSON, so that every requirement, decision, conflict and
pending Owner question has one source and the documents never drift from each other.

## Outputs (written into `docs/`)

- `SHELTER-WEBSITE-MASTER-REQUIREMENTS.md` and `master-requirements/*.md`
- `MASTER-DECISION-REGISTER.md` · `CONFLICT-REGISTER.md` · `PENDING-OWNER-INPUT.md`
- `REQUIREMENTS-TRACEABILITY-MATRIX.md` · `IMPLEMENTATION-GAP-ANALYSIS.md` · `IMPLEMENTATION-PLAN.md`
- `governance/DECISION-LOG.md` (new D-numbers appended, SUPERSEDED status synced)

## Inputs

| Path | What it holds |
|---|---|
| `cons/G1.json … G9.json` | Consolidated requirements from the Owner messages M01…M27 |
| `cons/G10.json …` | One file per later group: new requirements, `updates_to_existing` (status changes with code and test references), conflicts, new decisions, pending Owner items, technical flags |
| `out/[A-H]-*.json` | Raw extracted items (every one must be covered or explicitly dropped — the run reports `unaccounted`) |
| `out/I-docs-registry.json` | Registry of the project documents |
| `manual/*.md` | Hand-written module list and phase plan |
| `msgs-stats.json` | Message and character counts only — the raw Owner messages are not stored in the repo |

## Run

```bash
PYTHONHASHSEED=0 python3 scripts/registers/build_master.py
```

`PYTHONHASHSEED=0` keeps the output byte-stable. A run with no new input changes nothing in `docs/`.

## Adding a group

1. Write `cons/G<n>.json` with the keys used by the latest group (`group`, `notes`, `requirements`, `updates_to_existing`,
   `conflicts`, `new_decisions`, `pending_owner`, `technical_flags`, `cost_api_dependencies`, `dropped`).
2. Add `'G<n>'` to the `EXTRA` list in `build_master.py`.
3. Run, review the diff in `docs/`, then commit the input and the documents together.

Statuses must be honest: `TESTED` only with a passing test reference, otherwise `PARTIAL` or
`IMPLEMENTED — NOT YET VERIFIED` (M36 §20). An Owner decision is recorded as a decision only when the Owner approved it.
