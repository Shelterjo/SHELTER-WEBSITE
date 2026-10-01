# CLAUDE.md — SHELTER COFFEE Website

Rebuild of www.shelterjo.com. The Owner (Mahmoud Dalgamouni) is the final authority for every business decision.
The repo holds discovery docs, the menu inventory and IA, low-fi wireframes, quality tooling and (from PHASE 1) the Laravel application.

## Source of truth: read before acting, never answer from memory
| File | Role |
|---|---|
| `docs/SHELTER-WEBSITE-MASTER-REQUIREMENTS.md` | **Single source of truth for requirements.** Every requirement has an ID, status and sources |
| `docs/MASTER-DECISION-REGISTER.md` | Current state of every decision (FROZEN / APPROVED / … / SUPERSEDED) |
| `docs/governance/DECISION-LOG.md` | Chronological journal (D-000…). Append-only: new decisions get the next D-number here first |
| `docs/CONFLICT-REGISTER.md` | Every conflict and how it was resolved, or `OWNER DECISION REQUIRED` |
| `docs/PENDING-OWNER-INPUT.md` | The only open questions for the Owner. **Check here and in the registers before asking anything** |
| `docs/IMPLEMENTATION-PLAN.md` · `docs/IMPLEMENTATION-GAP-ANALYSIS.md` · `docs/REQUIREMENTS-TRACEABILITY-MATRIX.md` | Phases and gates · what is missing · Requirement → Decision → Design → Code → Test |
| `docs/menu-ia/MENU-DECISION-REGISTER.md` | Menu detail (F-xx frozen, R-xx test-based), indexed by the master register |

> The master files are `DRAFT — PENDING OWNER APPROVAL` until the Owner approves them. Decisions inside them that the Owner already approved stay approved.

## Continuous sync: every new Owner message
Compare each new message with the Master Requirements and the Decision Register, then:
- **NEW** → add it.
- **UPDATE** → change it and keep the history.
- **CONFLICT** → record it and show it.
- **SUPERSEDE** → mark the old item `SUPERSEDED`.

Never treat a prompt in isolation. Never resolve a conflict silently.

**Authority order:**
1. Latest explicit Owner decision.
2. Earlier Owner decision that has not been superseded.
3. Approved doc.
4. Verified official source.
5. Code (evidence only).
6. AI suggestion.

## Non-negotiable rules (Owner-approved)
- **Approval:**
  - Silence ≠ approval.
  - A Claude proposal is not a decision until the Owner approves it.
  - Frozen decisions are not reopened without a real conflict.
- **No invented business data.** This covers prices, products, availability, phones, addresses, hours, offers, policies, claims, founding year, content, images and translations. Missing data is written `MISSING — OWNER INPUT REQUIRED`.
- **Sources:**
  - The old site is an information source only.
  - Source data is never modified. Corrections go only in the normalized/display layers.
  - Retired IDs are never reused (next product ID: PRD-00193).
- **Images:** only Owner-approved images. No Google, stock, AI-generated or old-site images.
- **Production safety:**
  - No Production change, publishing, redirect, Cloudflare/Cloudways/DNS change or Google configuration without the matching phase gate and explicit Owner approval.
  - Google setup is executed by Claude when the phase and permissions allow. The Owner is needed only for login, OAuth, 2FA, ownership and permission approval.
  - Audit what already exists first. No duplicate tracking.
- **Cost:** no paid API, quota (Semrush…) or paid service (Sentry…) without prior approval. First state the service, purpose, expected usage, cost and free alternative. Local Playwright and Lighthouse runs are fine.
- **Security:**
  - Secrets stay server-side.
  - Permissions are enforced server-side.
  - Least privilege (e.g. a scoped Cloudflare token, never the Global API Key).
  - No PII in analytics.
  - Archive instead of hard delete.
- **Done means verified.** Use `IMPLEMENTED — NOT YET VERIFIED` (M36 §20) or `PARTIAL` honestly.
- **Responsive is mandatory** for the website and the Owner Dashboard. It must be mobile-first and checked on every viewport in `tooling/viewports.mjs`, in RTL and LTR. Overflow, clipping or overlap is a bug.
- **Replies to the Owner:** Arabic, structured, technical terms in English.

## Build mode (M36 — Owner approved, PLANNING CLOSED)
- **Planning is closed; the build is running** phase by phase: PHASE 1 Foundation → 2 Core Website → 3 Owner Dashboard → 4 Business Modules → 5 Integrations → 6 Quality/Operations → 7 Release.
- **Loop inside every phase:** PLAN → BUILD → TEST → FIX → DOCUMENT → COMMIT → VERIFY.
- **Progress:** live status is in `docs/PROGRESS.md`. Statuses: NOT STARTED · IN PROGRESS · BLOCKED · READY FOR REVIEW · TESTED · COMPLETE. Anything untested is `IMPLEMENTED — NOT YET VERIFIED`.
- **Platform:** `docs/adr/ADR-001-platform.md` (Laravel 13 + MySQL on a new Cloudways Flexible app, Blade). Architecture is in `docs/architecture/PLATFORM-ARCHITECTURE.md`, with ops specs in `docs/platform/`.
- **Order:** design system first and master data first. No hours, prices, branch or contact data hardcoded in pages.
- **Gates that still block:**
  - Production, DNS, production redirects, the production DB and live Google setup. Flow: Dev → Testing → Staging → Final Verification → **Owner Approval** → Production.
  - Paid services, accounts and credentials.
  - Brand files (M-10) for the final visual layer.
  - Unapproved business facts and media.

## Owner clarification rule (M38 — OWNER APPROVED · FROZEN · GLOBAL PROJECT RULE)
- **Known → build.** For a **technical decision**, decide professionally and build. Never ask about spacing, breakpoints, radius, CSS, naming, folders, durations, state or tests.
- **Unknown business fact → never guess.**
  - Covers prices, hours, phones, addresses, branch names, products, offers, dates, staff, awards, events, franchise, fees, profits, investment terms, policies, legal and any public fact.
  - Mark it `PENDING OWNER INPUT`, keep building everything independent of it, and ask only when it truly blocks.
  - Decisions about money, legal terms, public claims, brand policy, people, privacy or commercial terms: show the options and ask.
- **Conflict:**
  - The latest explicit Owner decision wins.
  - If which one is latest is unclear, show **A — existing / B — conflicting / Impact / Recommendation** and ask.
  - Never silently "fix" business data. If Google differs from Master Data, report `CONFLICT DETECTED`.
- **Media:** unapproved media is never public. Mark it `MEDIA PENDING OWNER APPROVAL` and use internal placeholders only.
- **External services:** paid plugins/APIs/SaaS, accounts, billing, OAuth, DNS, production credentials, Google/Cloudflare/Cloudways permissions. Explain what is needed, why, cost, risk and the alternative, then ask.
- **Production:** sensitive production changes need explicit approval. These are DNS, redirects, DB migrations, destructive operations, Google Business, auth, security rules, live integrations and permanent delete.
- **How to ask:** one decision at a time, ordered by priority. Use this format:
  - المعلومة المطلوبة
  - سبب الحاجة
  - الخيارات
  - توصيتك
- **Record every answer** in Master Requirements, the Decision Register and the Traceability Matrix, and mark old items `SUPERSEDED`.
- **Silence ≠ approval:** an item stays `PENDING OWNER APPROVAL` until approved.

## Tooling (`tooling/`, Node 22)
`npm ci` · `npm run test:e2e` · `npm run qa:matrix` · `npm run lighthouse` · `npm run images:selftest`

- **Browsers:** Chromium only locally. Firefox and WebKit run in CI with `SHELTER_ALL_BROWSERS=1`. Do not run `playwright install`.
- **Details:** `docs/FRONTEND-TOOLING.md`. One tool per job. Every new tool goes into the registry first.
