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
| **Maps links entered (D-335, Owner «ج» to PO-010)** | DRIVE `https://share.google/Cko3RPFoBGY21bco4`, HOUSE `https://share.google/d7T2jt7BKhidMHG4A` (as the Owner's own site maps them). The Directions button shows on each branch page and in the mobile action bar, and `hasMap` is in the schema. The branch editor and the directions_click hook accept share.google links |
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
