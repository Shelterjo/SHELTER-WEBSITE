# LOCAL-SEO-CHANGELOG — M57 (2026-10-02)

Every material change: page, before, after, why, target intent, and source/evidence. Business values come from Master Data or Owner-approved texts only. Every change is local (Dev) and nothing is published to Production.

## A. Titles (M57 §15) — Owner-editable in Site texts, with a Google warning (§49)
| Page | Before | After | Target intent | Source |
|---|---|---|---|---|
| Home AR | شلتر كوفي — SHELTER COFFEE | شلتر كوفي — قهوة مختصة ودرايف ثرو في إربد | Brand, specialty coffee, drive-thru in Irbid (clusters A–D) | D-332 approved phrase; research §3.1–§3.4 |
| Home EN | SHELTER COFFEE | SHELTER COFFEE — Specialty Coffee & Drive-Thru in Irbid | Same, English | D-332 (EN gateway line) |
| Menu AR | المنيو — SHELTER COFFEE | منيو شلتر كوفي في إربد — القهوة والمشروبات والأسعار | Brand + menu (third-party menu sites rank today) | Competitor matrix §5.2; prices shown from Master Data |
| Menu EN | Menu — SHELTER COFFEE | SHELTER COFFEE Menu in Irbid — Coffee, Drinks & Prices | Same | — |
| Locations AR/EN | الفروع — SHELTER COFFEE · Locations — SHELTER COFFEE | فروع شلتر كوفي في إربد — ساعات الدوام · SHELTER COFFEE Locations in Irbid — Opening Hours | Branches and hours in Irbid | D-008 |
| Branch AR/EN | `:name — ساعات الدوام` · `:name — Opening hours` | `:name — :kind في إربد \| ساعات الدوام` · `:name — :kind in Irbid \| Opening Hours` (DRIVE → «درايف ثرو», HOUSE → «كوفي هاوس») | Drive-thru Irbid (D), coffee house (E) | Master-data branch type (D-008) |
| Contact | تواصل معنا — SHELTER COFFEE · Contact — SHELTER COFFEE | تواصل مع شلتر كوفي — إربد · Contact SHELTER COFFEE — Irbid | Brand + contact | — |
| Events | الفعاليات — SHELTER COFFEE | فعاليات شلتر كوفي في إربد · SHELTER COFFEE Events in Irbid | Events (shown only while events exist) | — |
| Careers | التوظيف — SHELTER COFFEE · Careers — SHELTER COFFEE | وظائف شلتر كوفي في إربد · Careers at SHELTER COFFEE, Irbid | Barista jobs in Irbid (K) | Research §2 #K |

Why the city and brand appear together in every title: «شلتر كافية» in Riyadh and "Shelter Coffee" elsewhere in the world collide with the brand name (research §6). The city is written once per title, with no repetition.

## B. Descriptions (§16)
| Page | Before | After | Source |
|---|---|---|---|
| `/` gateway | none | its approved line «قهوة مختصة ودرايف ثرو في إربد. اختر لغتك…» | D-332 |
| All other pages | D-332 (approved) | unchanged | D-332 |

## C. Visible copy and headings (§9, §10, §17)
| Page | Before | After | Why |
|---|---|---|---|
| Home lead (hero) | «تصفّح المنيو، واعرف أيّ فرع مفتوح الآن ومتى يُغلق.» | «قهوة مختصة ودرايف ثرو في إربد. تصفّح المنيو، …» (EN likewise) | The home page never said coffee or Irbid; the hero stays a single short line |
| Home branches heading | «الفروع» / “Locations” | «فروعنا في إربد» / “Our branches in Irbid” | Local relevance where the branches are listed |
| Locations lead | «ساعات الدوام وحالة كل فرع الآن.» | «فروع شلتر كوفي في إربد، مع ساعات الدوام وحالة كل فرع الآن.» | §10: it now states that the branches are in Irbid; no branch count is hard-coded |
| Branch page, under the H1 | — | the kind and the city: “Coffee house in Irbid” / «درايف ثرو» (the Arabic city appears once «إربد» is approved, CF-M-036) | §11–§12: what the branch is and where; read from Master Data |
| Branch cards (home, locations) | name, status, today | + 📍 kind (+ city) (+ approved location description) | Same facts on every surface |
| H1s | — | unchanged (one per page) | §17: no keyword headings |

## D. Master Data (§1, §19)
| Change | Detail |
|---|---|
| New fields `landmark_ar` / `landmark_en` (location description) | A migration, the Fact Registry (registered **MISSING — PO-010** until the Owner saves them) and the branch editor (saving = approval). Shown under the address on the branch page and on cards; never used as `streetAddress`. **No value was entered**, because entering the M57 §1 wording as approved data needs the Owner's confirmation (PO-079) |
| **Values entered (D-334, Owner «أ» to PO-079)** | DRIVE `landmark_ar` «بجانب منطقة قصر النخيل / أرابيلا»; HOUSE `landmark_ar` «إربد سيتي سنتر، الطابق الأول، بجانب البنك الإسلامي الأردني» and `landmark_en` “Irbid City Center, First Floor, next to Jordan Islamic Bank”; city spelling «إربد» (CF-M-036 resolved). DRIVE `landmark_en` is still MISSING (PO-081). Cards name the city only once: «كوفي هاوس · إربد سيتي سنتر…» |
| **Google Business links (D-336, supersedes D-335)** | DRIVE `https://maps.app.goo.gl/zNfDbkxcT1aMdQiWA`, HOUSE `https://maps.app.goo.gl/k31BVoaAb1fcAF9e6`, sent by the Owner from Google Business, and the names match it. Before that (D-335): |
| ↳ D-335 (superseded) | DRIVE `https://share.google/Cko3RPFoBGY21bco4`, HOUSE `https://share.google/d7T2jt7BKhidMHG4A` (as the Owner's own site maps them). The Directions button shows on each branch page and in the mobile action bar, and `hasMap` is in the schema. The branch editor and the directions_click hook accept share.google links |
| `MasterData::cityName()` | The Arabic city name shows only once its fact is approved (CF-M-036); English comes from the fixed page address (D-053) |
| `HoursResolver::specialDays()` | The exception days ahead, by the same priority rule the page uses: one source for the page and the schema (§19, §21) |

## E. Structured data (§18–§19)
| Entity | Before | After |
|---|---|---|
| Organization | name, alternateName, url, logo, foundingDate | + `@id` `/#organization` |
| WebSite | — | new on `/`, `/ar/`, `/en/`: name, alternateName, inLanguage, publisher → Organization |
| CafeOrCoffeeShop | name, url, logo, telephone, regular hours | + `@id`, `image` (approved logo), `parentOrganization`, `address` (addressLocality Irbid, addressCountry JO; street only when approved), `hasMenu`, and exception days for the next 60 days (`validFrom`/`validThrough`; a closed day = 00:00–00:00) |

## F. Measurement hooks (§47) — no service and no PII
`resources/js/ui/track.ts`:
- It pushes the approved event names to `window.dataLayer` **only if a tag manager created it**. None is installed, so nothing is sent.
- The events are marked through `data-track-*` attributes on the branch page, the cards, the action bar, the footer, the contact cards and the menu.
- `menu_search` and `zero_result_search` carry the result count and the language, never the typed text.

## G. Owner Dashboard (§48–§49)
- **New screen «الظهور في Google» (`/dashboard/seo`):**
  - Indexing state and Search Console (not connected).
  - Per branch, nine items with present or missing, plus a link to the branch editor.
  - Each page's title and description in both languages, with length and repeat warnings, plus a link to Site texts.
  - Count of menu sections and items without Arabic names, plus a link to the menu.
- **Site texts:** new title fields for Locations, Contact, Events and Careers, and a warning on every title and description field ("changing it changes how your result looks in Google").
- **Branch editor:** location description fields (AR/EN).

## H. Redirect plan (documentation only — execution is gated: PHASE 7 + Owner approval)
- `docs/google/SEO-MIGRATION-MAP.md`: `/blog/` added. It ranks in Arabic results today (research §7.1).

## I. Tests
- `tests/Feature/Site/LocalSeoTest.php` (6) covers:
  - the kind and city line, and the gate on the Arabic city;
  - the location description (missing, then saved);
  - the schema `@id` links and the special days;
  - WebSite plus Organization;
  - unique titles carrying the brand and the city;
  - measurement markup with no tag loaded.
- `tests/Feature/Dashboard/SeoHealthTest.php` (3).
- `resources/js/ui/track.test.ts` (4).
- Updated: `LocationsPagesTest` (the address now carries city and country only) and `SiteTextsTest` (title placeholders and the separate title key).

## J. «كافيه» in Google titles and descriptions (D-338, Owner «ج» to PO-080) — SUPERSEDED by K (D-340)
| Page | Before | After |
|---|---|---|
| Home title AR | شلتر كوفي — قهوة مختصة ودرايف ثرو في إربد | شلتر كوفي — كافيه قهوة مختصة ودرايف ثرو في إربد |
| Home description AR | شلتر كوفي في إربد: قهوة مختصة وV60 … | شلتر كوفي، كافيه قهوة مختصة في إربد: V60 … |
| Locations title AR | فروع شلتر كوفي في إربد — ساعات الدوام | فروع شلتر كوفي في إربد — كافيه ودرايف ثرو \| ساعات الدوام |
| Locations description AR | فرعا شلتر كوفي في إربد، الدرايف والهاوس: … | فرعا شلتر كوفي في إربد: الدرايف ثرو، والكافيه في سيتي سنتر. … |
| HOUSE title AR | شلتر كوفي هاوس — كوفي هاوس في إربد \| ساعات الدوام | شلتر كوفي هاوس — كافيه في إربد \| ساعات الدوام (a title-only kind label; the page keeps «كوفي هاوس») |

Why: «كافيه» is the most used local word (research §3.2). It goes only into the cluster-B pages (home, locations) and the sit-down branch, so there's no stuffing or cannibalisation. Visible copy and the brand name stay «كوفي» (D-007).

## K. «كافيه» only in two Google descriptions, after approval (D-340, Owner «ب» to PO-080 — supersedes J) — SUPERSEDED by L (D-341) before publishing
| Page | Before (J, D-338) | After (D-340) |
|---|---|---|
| Home title AR | شلتر كوفي — كافيه قهوة مختصة ودرايف ثرو في إربد | شلتر كوفي — قهوة مختصة ودرايف ثرو في إربد |
| Locations title AR | فروع شلتر كوفي في إربد — كافيه ودرايف ثرو \| ساعات الدوام | فروع شلتر كوفي في إربد — ساعات الدوام |
| HOUSE title AR | شلتر كوفي هاوس — كافيه في إربد \| ساعات الدوام | شلتر كوفي هاوس — كوفي هاوس في إربد \| ساعات الدوام (the title-only kind label is removed) |
| Home description AR | شلتر كوفي، كافيه قهوة مختصة في إربد: … | back to D-332: شلتر كوفي في إربد: قهوة مختصة وV60 … |
| Locations description AR | فرعا شلتر كوفي في إربد: الدرايف ثرو، والكافيه في سيتي سنتر. … | back to D-332: فرعا شلتر كوفي في إربد، الدرايف والهاوس: … |

Why: the Owner chose «ب». «كافيه» covers the most used search word through the descriptions, while every title, the visible copy and the brand name keep «كوفي». The two descriptions stay on the approved D-332 wording until the Owner approves the «كافيه» wording (PO-082); then only those two lines change. Test: `LocalSeoTest` asserts that no title on the home or locations page, and not the HOUSE title, contains «كافيه».

## L. Final placement of «كافيه» (D-341: the Owner left the choice to Claude — supersedes K)
| Page | Title AR | Description AR |
|---|---|---|
| Home | شلتر كوفي — كافيه قهوة مختصة ودرايف ثرو في إربد | شلتر كوفي، كافيه قهوة مختصة في إربد: V60 ومشروبات ساخنة وباردة وحلويات، في فرع درايف ثرو وفرع كوفي هاوس. تصفّح المنيو وساعات الدوام. (only the opening differs from D-332) |
| Locations | فروع شلتر كوفي في إربد — ساعات الدوام (unchanged) | فرعا شلتر كوفي في إربد: الدرايف ثرو، والكافيه في سيتي سنتر. ساعات الدوام وأيّ فرع مفتوح الآن. |
| HOUSE | شلتر كوفي هاوس — كافيه في إربد \| ساعات الدوام (a title-only kind label; the page keeps «كوفي هاوس») | D-332 (unchanged) |
| DRIVE and every other page | unchanged | unchanged |

Why:
- «كافيه» is the most searched local word (research §3.2), and Google titles weigh more than descriptions. It goes once into each page that answers a general café search: the home page, and HOUSE as the sit-down branch. HOUSE also loses the repeated «كوفي هاوس — كوفي هاوس».
- The locations title stays on «branches and hours» so it doesn't compete with the home page for the same search. Its description names the café branch.
- The brand name and all visible copy keep «كوفي» (D-007), and «كوفي شوب» is never used.
- Tested by `LocalSeoTest`, which checks every title and description above.
