# FINAL QA MATRIX — SHELTER COFFEE website + Owner Dashboard

> Owner directive **M51 / D-328** (ULTIMATE FULL-SPECTRUM TESTING + QA + REPAIR). Local build only: **Cloudways and
> Cloudflare are not connected → every infrastructure test is `DEFERRED — INFRASTRUCTURE NOT CONNECTED`.**
> Run date 2026-10-02 · branch `claude/determined-newton-ftnxpn` · final code commit `db92ad1` (+ this documentation commit).
> Loop per issue: DISCOVER → REPRODUCE → UNDERSTAND → FIX → RETEST → REGRESSION. The summary and the Owner questions are
> in [`FINAL-QA-REPORT.md`](FINAL-QA-REPORT.md).

**STATUS:** PASS · FAIL · PASS WITH NOTES · BLOCKED · DEFERRED · NOT BUILT. **SEVERITY:** P0 blocker · P1 high ·
P2 medium · P3 polish. `FIXED` rows are listed under the area where the issue lived; RETEST = the check that proved the fix.

**Environments used**
- **Local** — `php artisan serve :8000`, the working tree and the dev database.
- **Clean** — a fresh clone, then `composer install` from the lock file, `npm ci`, `npm run build`, a new SQLite DB with `migrate:fresh --seed`, served on :8100.
- **Production mode** — the same clean clone served on :8101 with `APP_ENV=production` and `APP_DEBUG=false`.

## 1. Build, toolchain and automated suites

| AREA | TEST | EXPECTED | ACTUAL | STATUS | SEV | ISSUE | FIX | RETEST | EVIDENCE |
|---|---|---|---|---|---|---|---|---|---|
| Build | Fresh clone → dependencies from lock files | installs | `npm ci` OK. `composer install`: GitHub's API answers "could not authenticate" in this sandbox, so packages came from the local git cache, and the one dist-only package (phpstan, pinned commit `46a6d906`) came from a git fetch of its tag | PASS WITH NOTES | — | — | sandbox network only; the lock file is unchanged | — | clean-composer.log |
| Build | `migrate:fresh --seed` on a new DB | approved data only, no default admin | 192 products · 11 categories · 2 branches · **0 users** (the owner is created by command) | PASS | — | — | — | — | clean clone |
| Build | `npm run build` (tokens → brand guard → Vite → budgets) | under budget | site.css 12.9 KB gz / 25 · site.js 2.2 / 30 · dashboard.css 10.8 / 40 · dashboard.js 2.4 / 100 | PASS | — | — | — | — | build log |
| Build | Brand guard | ok, nothing pending | was 1 open item (Arabic font licence, PO-071) → **ok, 0 open** after D-329 | PASS | — | QA-039 | Noto Kufi Arabic (OFL) | build | build log |
| Config | Fresh install keeps private files private | careers disk outside `public/` | `.env.example` `CAREERS_STORAGE_ROOT=` (empty) → disk root `''` → files next to the running script (`public/` under a web server) | FIXED | **P1** | QA-001 | empty env falls back (`?:`) for 3 disks | `StorageRootsTest` fails before / passes after; clean-clone PHPUnit | commit d48de4b |
| Config | Server cannot be weakened by the example env | HTTPS-only cookie, no debug | `.env.example` `SESSION_SECURE_COOKIE=false` / `APP_DEBUG=true` overrode the server defaults (seen live on the production-mode server: cookie without `Secure`) | FIXED | **P1** | QA-025 | staging/production always secure + never debug | `ProductionConfigTest` | 5e691e5 |
| Config | `.env.example` explains which values are laptop-only | documented | not stated | FIXED | P3 | QA-014 | comments for APP_DEBUG / LOG_LEVEL / SESSION_SECURE_COOKIE | — | d48de4b |
| Suites | PHPUnit (Pint + Larastan level 8 + PHPUnit) | all pass | **352 / 352** · 4 724 assertions · Larastan 0 · Pint clean (local and clean clone) | PASS | — | — | — | — | `composer qa:fast` |
| Suites | TypeScript strict · ESLint · Prettier · ds-gate · Vitest | all pass | tsc 0 · eslint 0 · prettier clean · ds-gate 73 stylesheets / 144 views / 41 icons, 0 violations · **Vitest 56 / 56** | PASS | — | — | — | — | `npm run qa:fast` |
| Suites | Playwright — public site matrix (40 pages × 20 viewports: layout, one h1, lang/dir, overflow, clipping, script errors · axe on 390 + 1440 · links on 1440) | 0 failures | see the final regression in §15 | PASS | — | — | — | final regression | final-pw.json |
| Suites | Playwright — menu journeys (was 13 `test.fixme` placeholders, 260 skipped) | real tests | 40 / 40 after rewrite | FIXED | P2 | QA-015 | real F-01…F-13 journeys | 40/40 | 198cb2e |
| Suites | Playwright — site journeys · dashboard journey | pass | 39 + 1 (phone + desktop) | PASS | — | — | — | — | `tests/app/*.spec.mjs` |
| Suites | Playwright — wireframe prototypes | pass | 2 616 passed · 1 failed (a trace file lost because two Playwright runs shared `reports/`) → 1/1 alone | PASS WITH NOTES | — | QA-029 | none (test infra) | rerun 1/1 | pw-proto.json |
| Suites | Firefox + WebKit | pass | not installed locally (rule: never `playwright install`); they run in CI (`SHELTER_ALL_BROWSERS=1`) | BLOCKED (local) | — | — | — | CI | quality.yml |
| Security scan | semgrep (project rules) | 0 findings, full parse | 0 findings · 1 file only partly parsed (`SiteTexts.php:90`) | FIXED | P3 | QA-002 | `require` rewritten | 0 findings, 0 errors | semgrep JSON |
| Security scan | gitleaks (history) · composer audit · npm audit (app + tooling) | none | no leaks · no advisories · 0 vulnerabilities ×2 | PASS | — | — | — | — | — |
| Hygiene | Debug artifacts / source maps | none | no `dd/dump/ray/console.*` outside tests (review) · 0 `.map` files in `public/build` | PASS | — | — | — | — | find |
| DB | Migrations down/up | reversible | 20 rolled back, 20 re-applied on a temporary DB | PASS | — | — | — | — | migrate:reset |

## 2. Visitor journeys, buttons, links, history

| AREA | TEST | EXPECTED | ACTUAL | STATUS | SEV | ISSUE | FIX | RETEST | EVIDENCE |
|---|---|---|---|---|---|---|---|---|---|
| Navigation | Every header/drawer link → 200, one h1, right language (ar + en, phone + desktop) | yes | yes (Menu, Locations; other pages in the footer) | PASS | — | — | — | — | journeys.spec |
| Links | Every same-site link and asset on 40 pages | no 4xx/5xx | none broken | PASS | — | — | — | — | site.spec @links |
| History | Back / Forward / refresh | right pages | right pages, one h1 after refresh | PASS | — | — | — | — | journeys.spec |
| Language | Switch keeps the page (contact, locations, branch, careers, events) | same page other language | yes | PASS | — | — | — | — | journeys.spec |
| Language | Switch keeps context (menu `?branch`, search `?q`) | kept | lost — server knew only the requested `?branch`; a branch picked in the browser never reached the switch; search hreflang carried `?q` | FIXED | P2 | QA-016 | `languageLinks` + script updates `a[hreflang]`; hreflang clean | PHPUnit + E2E 40/40 | 198cb2e |
| 404 | Unknown address | 404 page, way home, working search | yes | PASS | — | — | — | — | journeys.spec |
| Redirects | Missing trailing slash | one 301 | one 301 for real pages; **unknown paths 301 → 404** (`/menu` → `/menu/` → 404) | FIXED | P3 | QA-051 | slash only when a route answers | RoutingTest | db92ad1 |
| Gateway `/` | Designed, titled, branded | premium entry | bare PHASE-1 shell, no title (same as /en/), no Organization | FIXED | **P1** | QA-020 | design-system layout, own title, Organization JSON-LD | RoutingTest + screenshots | 198cb2e |
| Search | Results · empty · hostile input (`<script>`, quotes, Arabic) | escaped text, no script | as expected | PASS | — | — | — | — | journeys.spec |
| Search | `?q[]=x` (array) | a page | **500** "Array to string" | FIXED | P2 | QA-026 | `App\Support\Input` for 51 reads | `TamperedInputTest` | 5e691e5 |
| Search | Brand page published/unpublished/scheduled → search follows | at once / at its time | page saves did not refresh the index (results linking to 404; scheduled pages never appeared) | FIXED | P2 | QA-043 | Page/PageSection invalidate; next publish time scheduled | SearchPageTest | d96c6e0 |
| Forms | Feedback: errors first, then one record on a double click | one answer | yes (idempotency key) | PASS | — | — | — | — | journeys.spec + FeedbackFormTest |
| Forms | Careers (Arabic): error summary takes focus, links reach fields; CV upload + remove | yes | yes after fix | FIXED | P3 | QA-022 | script remove buttons carry the same hook | E2E 2/2 | 198cb2e |
| Forms | Expired page (419) on a public form | back to the form with input | framework page, unstyled (CSP), English, input lost | FIXED | P2 | QA-006 | styled 403/419/429/4xx/5xx; 419 → form with input (never the ID number) | `ErrorPagesTest` | d48de4b |

## 3. Menu, branches, master data, time

| AREA | TEST | EXPECTED | ACTUAL | STATUS | SEV | ISSUE | FIX | RETEST | EVIDENCE |
|---|---|---|---|---|---|---|---|---|---|
| Menu | Without JavaScript: items, prices, category anchors | full menu | > 100 cards, prices, anchors | PASS | — | — | — | — | menu.spec F-01 |
| Menu | Live search, suggestions, ↓/Enter jump, focus, URL untouched | yes | yes | PASS | — | — | — | — | F-02 |
| Menu | Zero results message + one-step clear | yes | yes | PASS | — | — | — | — | F-13 |
| Menu | Branch choice: `?branch`, no history entry, remembered | yes | yes | PASS | — | — | — | — | F-03 |
| Menu | Details dialog: Esc, Back, focus return, scroll lock, deep link + refresh | yes | yes | PASS | — | — | — | — | F-04/05 |
| Menu | Category jump lands below sticky bars | not covered | not covered | PASS | — | — | — | — | F-06 |
| Menu | Long names wrap, no broken image | yes | yes (320–1920 in the matrix) | PASS | — | — | — | — | menu.spec |
| Menu | Seeded v1.0 items are `publish_status=draft` yet public | intended? | by design: approved menu v1.0; hide = archived; nothing else creates draft | PASS WITH NOTES | — | QA-003 | — | — | MenuPage.php |
| Master data | Names, phones, WhatsApp, hours agree on home, locations, branch, contact, schema, llms.txt | identical | identical (Friday 08:00 vs Sat–Thu 07:00 in all) | PASS | — | QA-046 | — | — | consistency probe |
| Time | Exact closing/opening minute, Thu→Fri handover, year change inside a shift, UTC vs Amman | correct | correct | PASS | — | QA-045 | tests added | HoursResolverTest 8/8 | d96c6e0 |
| Season | Dates in Amman, first/last day, by hand on/off | correct | correct | PASS | — | — | — | — | MenuSearchSeasonTest |

## 4. Dashboard (Owner), no-code editing, data safety

| AREA | TEST | EXPECTED | ACTUAL | STATUS | SEV | ISSUE | FIX | RETEST | EVIDENCE |
|---|---|---|---|---|---|---|---|---|---|
| Access | 50 dashboard GET routes without a session | → sign-in | 50 / 50 → `/dashboard/login` | PASS | — | — | — | — | route probe |
| Access | Password alone; wrong code; right code; sign-out + Back | no access / no access / in / no dashboard | as expected | PASS | — | — | — | — | dashboard.spec |
| Auth | Second-factor brute force | bounded | 5/min only (≈ 7 200 tries a day) | FIXED | P2 | QA-008 | hourly lock after 10 failures, audited | OwnerAuthenticationTest | d48de4b |
| Auth | Same TOTP code in two parallel requests | one wins | both could pass (read-then-save) | FIXED | P3 | QA-009 | conditional update | test | d48de4b |
| No-code | Edit a site text → visible on the site → put back | yes | yes | PASS | — | — | — | — | dashboard.spec |
| Data safety | Same record from two tabs | newer change kept | **older tab silently overwrote** | FIXED | **P1** | QA-044 | `_seen_at` + `PreventStaleEdits`; double click ignored | StaleEditTest + browser two-tab | d96c6e0 |
| Data safety | Group saves without a record (site texts, settings) | — | not covered by QA-044 | PASS WITH NOTES | P3 | QA-044 | — | — | remaining |
| Privacy | Dashboard and applicant pages kept by the browser | no-store | `no-cache` only; print export (full IDs) cached | FIXED | P2 | QA-007 | `no-store, private` | test | d48de4b |
| Safety | Outside link to a GET dashboard screen | stays on site | Referer could become "back" | FIXED | P3 | QA-010 | `DashboardBack` | MenuBulkTest | d48de4b |
| Safety | Cross-site link marks an application seen | records nothing | it did | FIXED | P3 | QA-011 | `OwnNavigation` | CareersInboxTest | d48de4b |
| UX | Dashboard top bar at 320–390 px | one line | words wrapped to two lines | FIXED | P3 | QA-036 | icon-only under 480 px (names kept) | screenshot | b54297b |
| UX | Dashboard screens (64 × 9 widths) | no overflow, one h1, axe clean | clean (previous full sweep on this lineage) | PASS | — | — | — | — | commit 8c114c4 |
| Labels | Owner-facing text | Arabic, no internal IDs | "AI ASSISTED ANALYSIS" in English; "(PO-019)" shown | FIXED | P2 | QA-037 | Arabic label; IDs removed | LocalizationTest | b54297b |

## 5. Security (probes against the running app + review)

| AREA | TEST | EXPECTED | ACTUAL | STATUS | SEV | ISSUE | FIX | RETEST | EVIDENCE |
|---|---|---|---|---|---|---|---|---|---|
| Uploads | No-JS careers submit stores files | inside the upload limit | stored before validation, outside any limiter | FIXED | **P1** | QA-004 | per-file limit + `attempts_per_hour` on 3 forms | CareersFormTest, FeedbackFormTest | d48de4b |
| Uploads | PDF active content via `#xx` name escapes | refused | passed the heuristic | FIXED | P3 | QA-S10 | names decoded first (malware scan A-11 still planned) | CareersFormTest | d48de4b |
| Host | Forged Host header (production mode) | 400 | 400 | FIXED → PASS | **P1** | QA-012 | `trustHosts` + `CanonicalHost` (APP_URL) | test + probe | d48de4b |
| Files | `.env`, `.git`, composer, logs, sqlite, vendor, storage, wp-login… | 404 | 404 (local `artisan serve` answers `/.htaccess` and its router — not on Apache/Nginx) | PASS WITH NOTES | — | QA-028 | — | staging | probe.sh |
| Redirects | 8 open-redirect vectors (`//evil`, `%2F%2F`, `\`) | stay on host | 404, no Location | PASS | — | — | — | — | probe.sh |
| XSS | Reflected payload in search, 404 path, `?branch`, feedback | escaped | escaped ×4 | PASS | — | — | — | — | probe.sh |
| CSRF | Dashboard POST without token | 419/405 | 419 / 405, never 200 | PASS | — | — | — | — | probe.sh |
| Methods | TRACE / PUT / DELETE / PATCH on pages | 405 | 405 | PASS | — | — | — | — | probe.sh |
| Errors | Production-mode 500 | styled, no trace | styled Arabic page, 0 trace markers | PASS | — | — | — | — | :8101 |
| Headers | CSP, XFO, nosniff, Referrer, Permissions, COOP, HSTS (https) | present | present · `X-Powered-By: PHP/8.3.6` leaked | FIXED | P3 | QA-027 | header removed | test | 5e691e5 |
| Cookies | HttpOnly, SameSite=Lax, Secure on servers | yes | yes after QA-025 | FIXED | P1 | QA-025 | see §1 | — | — |
| Mass assignment / IDOR / SQL / SSRF | review | none | none found (explicit arrays, cross-record checks, constant raw SQL, no outgoing HTTP) | PASS | — | — | — | — | security review |
| Server | Directory listing | off | `-Indexes` only inside an IfModule | FIXED | P3 | QA-013 | `Options -Indexes` on its own | — | d48de4b |
| Tracking | Applicant tracking limiter bypass with Arabic digits | one counter | separate counters | FIXED | P3 | QA-S05 | digits normalised | — | d48de4b |

## 6. Accessibility (WCAG 2.2 AA), responsive, RTL/LTR

| AREA | TEST | EXPECTED | ACTUAL | STATUS | SEV | ISSUE | FIX | RETEST | EVIDENCE |
|---|---|---|---|---|---|---|---|---|---|
| axe | 40 pages × phone + desktop | 0 serious/critical | 0 | PASS | — | — | — | — | site.spec @a11y |
| Keyboard | Skip link first, every stop shows focus | yes | yes (stretched links ring their card) | PASS | — | — | — | — | journeys.spec |
| 2.4.11 | Focus never fully under a sticky bar | never | branch phone link fully under the call/WhatsApp bar (phone) | FIXED | **P1** | QA-035 | `scroll-padding` for the bar | journeys.spec (5 pages, 60 tabs each) | b54297b |
| Dialogs | Drawer: aria-expanded, focus trap, Esc, focus return, scroll lock | yes | aria-expanded stuck at false | FIXED | P3 | QA-017 | follows the dialog | journeys.spec | 198cb2e |
| 2.5.8 | Touch targets ≥ 24 px (token 44) | yes | yes; standalone list/contact links raised to 44 | FIXED | P3 | QA-032 | min-block-size | journeys.spec | b54297b |
| 2.2.2 | Nothing moves > 5 s by itself | yes | "open" dot pulsed forever | FIXED | P3 | QA-031 | 2 beats | — | b54297b |
| Names | Icon-only actions unique per branch | distinct | same label on both branches | FIXED | P3 | QA-034 | branch name in label | curl | b54297b |
| Motion | Reduced motion: nothing running, nothing hidden | yes | yes | PASS | — | — | — | — | journeys.spec |
| Motion | Tall sections reveal; print shows all | visible | sections > ~6.7 screens stayed invisible; print blank | FIXED | **P1** | QA-018 | reveal on any visible part; print rule | journeys.spec | 198cb2e |
| Native UI | Date/time pickers, scrollbars on dark theme | dark | `color-scheme: light` | FIXED | **P1** | QA-019 | `color-scheme: dark` (computed `dark` on date inputs) | probe | 198cb2e |
| Responsive | 320 → 1920, zoom 200 %, landscape (20 projects) | no overflow/clipping | none | PASS | — | — | — | — | site.spec @responsive |
| Contrast | Primary button #58595B on #DDDDDE | ≥ 4.5 | 5.17 : 1 (brand D-309) | PASS WITH NOTES | — | QA-023 | — | — | computed |
| Screen readers | Real NVDA / VoiceOver pass | — | not available in this sandbox; names, roles, live regions covered by axe + tests | BLOCKED (tool) | — | — | — | Owner device test at staging | — |
| i18n | Same keys in every language file | yes | only `ui.php` was checked | FIXED | P3 | QA-033 | all files (Arabic-only careers form and plural forms allowed) | LocalizationTest | b54297b |
| Design system | Tokens used are defined | yes | `--leading-normal`, `--ui-nudge` undefined | FIXED | P3 | QA-030 | language/direction-aware aliases | ds-gate | b54297b |

## 7. Performance

| AREA | TEST | EXPECTED | ACTUAL | STATUS | SEV | ISSUE | FIX | RETEST | EVIDENCE |
|---|---|---|---|---|---|---|---|---|---|
| Lab (Lighthouse, mobile, gzip front) | `/ar/` · `/ar/jo/menu/` · `/en/jo/locations/irbid/drive/` | perf ≥ 0.9, a11y 1 | perf 1.00 / 0.99 / 0.99 · a11y 1 · BP 1 · LCP 1.1 / 1.8 / 1.8 s · CLS ≤ 0.025 · TBT 0 · 132–151 KB | PASS | — | — | — | — | reports/lighthouse |
| Lab after D-329 (Noto Kufi Arabic) | `/ar/` · `/ar/jo/menu/` · `/ar/jo/locations/irbid/drive/` | CLS < 0.1, perf ≥ 0.9 | first run: **CLS 0.223** on `/ar/` (perf 0.89) — the text reflowed when the new font swapped in | FIXED | P2 | QA-052 | Arabic pages preload Kufi Light + Bold and Poppins Regular | perf 0.98 / 0.97 / 0.97 · CLS 0.000 / 0.001 / 0.001 · LCP 2.0–2.3 s (fonts now load early: ~+0.4 s, still < 2.5 s) | reports/lighthouse |
| Lab | SEO score | ≥ 0.9 on production | 0.61 locally = noindex by design + empty meta descriptions (Owner content) | PASS WITH NOTES | — | QA-049 | — | production check | reports/lighthouse |
| Fonts | Arabic licensed font | deployed or not declared | GE SS Two declared always; files git-ignored → 404 on a server without them | FIXED (Owner D-329: PO-071 → ج) | P2 | QA-039 | Noto Kufi Arabic (SIL OFL), self-hosted, same Kufi character, size-adjust 92 %, Light text / Bold headings | full matrix rerun + screenshots | fonts.css |
| Network | Real CDN / HTTP2 / compression / cache | — | — | DEFERRED — INFRASTRUCTURE NOT CONNECTED | — | — | — | staging | — |

## 8. SEO, structured data, analytics

| AREA | TEST | EXPECTED | ACTUAL | STATUS | SEV | ISSUE | FIX | RETEST | EVIDENCE |
|---|---|---|---|---|---|---|---|---|---|
| Indexing | Non-production never indexable; production + flag indexable; dashboard never | yes | yes | PASS | — | — | — | — | SecurityAndIndexingTest |
| Canonical | Absolute, clean (`?branch`, `?q` stripped), from APP_URL on servers | yes | yes after QA-012 | PASS | — | — | — | — | tests |
| hreflang | Reciprocal clusters; x-default = gateway | consistent | home lacked x-default (gateway + sitemap had it) | FIXED | P2 | QA-021 | added | RoutingTest | 198cb2e |
| Sitemap | All indexable pages | complete | Contact and event pages missing | FIXED | P2 | QA-042 | added | EventsPagesTest | d96c6e0 |
| robots.txt | Sitemap line; dashboard with/without slash | yes | missing / slash only | FIXED | P2 | QA-042 | added | test | d96c6e0 |
| Share previews | OG + Twitter (SEO-035) | yes | none | FIXED | P2 | QA-041 | title/description/url/locale/type/twitter card; image only when approved | tests | d96c6e0 |
| Share image | Site-wide default | approved image | card designed from approved brand parts (D-330), approved by the Owner (D-331) and published | PASS | — | PO-077 | `public/brand/og-default-1200x630.png` + `config shelter.share_image` | SecurityAndIndexingTest | og-card.mjs |
| Schema | Organization, CafeOrCoffeeShop, Menu, Event, FAQPage, Breadcrumb — approved data only, `</script>` safe | yes | yes; Event lacks an address (event ↔ branch address not linked); no `lastmod` | PASS WITH NOTES | P3 | QA-047 | — | — | SEO review |
| Meta descriptions | Every page | written | empty until the Owner writes them (Site texts) | PENDING OWNER INPUT | P2 | QA-049 | editor ready | — | — |
| Legacy URLs | Old site addresses → new | 301 map | not built; production redirects are a PHASE 7 gate | DEFERRED (Owner approval + PHASE 7) | P1 at launch | QA-048 | — | — | LEGACY-URL-MIGRATION |
| Analytics | GA4 / GTM / Meta Pixel / consent | per approved plan | not implemented (PO-012, PO-036, PO-043, PO-019); no PII anywhere today | DEFERRED | — | QA-050 | — | — | — |

## 9. Content, media, events, campaigns, careers, franchise

| AREA | TEST | EXPECTED | ACTUAL | STATUS | SEV | ISSUE | FIX | RETEST | EVIDENCE |
|---|---|---|---|---|---|---|---|---|---|
| Content pages | About, FAQ, privacy, terms, Media Center, awards, family | public only when published in both languages | 404 until published (Owner content) | PASS (by design) | — | — | — | — | site.spec "not published yet" |
| Events / campaigns | Visible by state and time; ended = noindex; image approved only | yes | yes | PASS | — | — | — | — | EventsPagesTest, Announcement tests |
| Media | Only approved images public; rights per channel | yes | yes | PASS | — | — | — | — | MediaEditorTest |
| Careers | Validation, files by content, private storage, rate limits, tracking (public status only), double submit | yes | yes after QA-001/QA-004 | PASS | — | — | — | — | Careers* tests |
| Franchise / feedback | Closed in production until switched on; idempotent; limits | yes | yes (production mode: careers form closed, feedback 404) | PASS | — | — | — | — | :8101 probe |
| Honeypot | Not autofilled | yes | named `website` (autofill target) | FIXED | P3 | QA-005 | `hp_extra` | tests | d48de4b |
| Brand naming | One rule for "SHELTER" vs "SHELTER COFFEE"; Arabic name for "SHELTER Family" | decided | mixed usage | OWNER DECISION REQUIRED | P2 | PO-078 | — | — | — |

## 10. Planned modules not built yet (no new features were added in this pass)

| Module | STATUS |
|---|---|
| Seasonal experiences engine beyond the menu season, Employee of the Month, global content calendar, notifications, inquiries module, publish guard / change-impact preview | NOT BUILT — planned later phases (not "random features"; outside this QA pass) |
| Malware scanning of uploads (A-11) | NOT BUILT — needs a service decision |

## 11. Infrastructure (not connected)

| AREA | STATUS |
|---|---|
| Cloudways staging/production apps, PHP-FPM/Nginx rules, cron/queue, backups | DEFERRED — INFRASTRUCTURE NOT CONNECTED (PO-064) |
| Cloudflare SSL Full (strict), TLS, DNSSEC, WAF, caching, apex → www redirect | DEFERRED — INFRASTRUCTURE NOT CONNECTED (PO-073) |
| Email DNS (SPF / DKIM / DMARC) | DEFERRED (PO-072) |
| Real device / real network / real screen reader on staging | DEFERRED until staging |

## 12. Test-data cleanup and evidence

| Item | Result |
|---|---|
| Local test rows created by the journeys (13 feedback answers, 2 draft CV files) | removed from the local dev database only |
| Site text changed by the dashboard journey | put back to the original (row kept with its history, by design) |
| Menu item changed by the two-tab journey | put back to its original order |
| Evidence | PHPUnit/Vitest output, Playwright JSON reports, Lighthouse reports (`tooling/reports/`), screenshots sent in the conversation, commits d48de4b · 198cb2e · 5e691e5 · b54297b · d96c6e0 · db92ad1 |

## 15. Final regression (clean clone, fresh DB, clean browser contexts)

| AREA | TEST | EXPECTED | ACTUAL | STATUS | EVIDENCE |
|---|---|---|---|---|---|
| Regression | Fresh pull of `db92ad1` → build → new DB → `migrate:fresh --seed` | ok | ok | PASS | final-regression.out |
| Regression | PHPUnit + Larastan + Pint | all | 352 / 352 · 0 · clean | PASS | final-qa-php.log |
| Regression | tsc + ESLint + Prettier + ds-gate + Vitest | all | 0 · 0 · clean · 0 · 56 / 56 | PASS | final-qa-js.log |
| Regression | Playwright `tests/app` (site × 20 viewports + axe + links · menu journeys · site journeys) | 0 failures | **675 passed · 0 failed · 0 flaky** (skips by design: axe on 2 projects, links on 1, journeys on phone + desktop, unpublished pages, careers form closed without recruitment keys in the clean env — 2/2 with keys locally) | PASS | final-pw.json |
| Regression | Dashboard journey (needs the local owner) | pass | 1 / 1 on the local environment (sign-in with TOTP, no-code edit, two tabs, sign-out) | PASS | dashboard.spec |
