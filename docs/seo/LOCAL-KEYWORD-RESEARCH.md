> **Status:** RESEARCH EVIDENCE (M57 §3–§6), collected 2026-10-02 with free tools only (WebSearch + WebFetch; no Semrush or any paid quota). Google Search and Maps were **not** reachable from this environment (network policy), so no Local Pack, Maps or search volume is claimed. Values marked *tool-summary* are unverified. Nothing here is a business fact: the website takes every fact from Owner-approved Master Data.
> **Used by:** `docs/seo/LOCAL-SEO-MAP.md` (keyword-to-page map) · **Companion:** `LOCAL-COMPETITOR-MATRIX.md`

# SHELTER COFFEE: local search-language research (Irbid)

Status: RESEARCH EVIDENCE ONLY. Nothing here is a decision, and nothing here is a verified business fact about SHELTER.
Date of research: 2026-10-02. Researcher: Claude (sub-agent).

---

## 1. Method and limitations

**Tools used**
- `WebSearch`: 86 queries.
- `WebFetch`: 6 successful fetches, all on `www.shelterjo.com`. 8 more fetches were blocked by the network egress proxy: talabat.com, tripadvisor.com, mat3am.net, irbid-now.com, accessiblejordan.com, timeoutamman.com and portal.hatanjo.com. I did not try to bypass the block.
- No MCP tools were used: no Semrush and nothing paid or quota-based. No curl/Bash calls to google.com or bing.com.

**Index limitation (important)**
- `WebSearch` is a general web index. The tool describes itself as "US-only".
- It is **not** Google Jordan (google.jo), **not** the Google Local Pack and **not** Google Maps.
- I did **not** see any Local Pack, Maps or "near me" geo result. Rankings and ordering here do **not** show what a user in Irbid sees on Google.
- In particular, "near me" style queries can't be judged from this index.

**No volumes**
- No search volumes, CPCs or rankings are claimed anywhere in this file.
- Every "count" is a **sample count**: the number of distinct pages/titles *I saw* in this research. It is **not** demand or search volume.

**Evidence rule**
- Only result **titles and URLs** count as primary evidence, plus the 6 fetched shelterjo.com pages.
- The search tool also returns a machine-written summary under each result list. Those summaries sometimes added ratings, hours or phone numbers, and sometimes misread them: one turned the old site's "2:00 ل" into "2:00 PM".
- Any fact taken only from such a summary is tagged **(tool-summary, unverified)**.

**WebFetch caveat**
- The fetch tool returns a small-model summary of the page, not raw HTML.
- Exact wording quoted from shelterjo.com must be re-checked against the live HTML before anyone relies on it.

**Confidence scale**
- HIGH: the pattern appears in ≥3 independent domains' titles.
- MED: 2 independent domains, or several pages of one directory.
- LOW: 1 page, only the brand's own site, or only a tool summary.

**Starting-set queries not run verbatim** (each was covered by a close variant):
- `قهوة في اربد` and `افضل قهوة اربد`: covered by `افضل قهوة في اربد` and `قهوة اربد`.
- `افضل كافيه اربد`: run as `افضل كافيه في اربد`.
- `كوفي اربد سيتي سنتر`: covered by `قهوة اربد سيتي سنتر` and `كافيه اربد سيتي سنتر`.
- `coffee Irbid` and `cafe Irbid`: covered by `coffee shop Irbid`, `cafes in Irbid Jordan` and `best cafe Irbid`.
- `coffee drive thru Irbid`: covered by `drive thru coffee Irbid` and `"drive thru" Irbid`.
- `coffee Irbid City Center`: covered by `cafe Irbid City Center` and `coffee Irbid City Centre mall cafe`.

---

## 2. Per-query table

**How to read the table**
- Evidence IDs (E-nn) point to the full URL list in §8.
- Brand-site? = does any café's own website (not a directory or social profile) appear in the results?
- SH = shelterjo.com (SHELTER's old site).

**Intent codes**
- NAV = navigational-brand.
- LOC = local-transactional ("where to go now").
- PROD = product.
- INFO = informational.
- LIST = list / comparison.
- JOB = careers.
- EVT = events.

| # | Query | Lang | Intent | SERP type observed | Brand-site? | Competitors / other names seen | Relevance to SHELTER | Confidence | Evidence | Notes (spelling variants seen) |
|---|---|---|---|---|---|---|---|---|---|---|
| 1 | قهوة مختصة اربد | AR | LOC/LIST | SH pages ×5 (home, blog, guide, coffee-house-vs-cafe); FB pages; directory (irbid-now); one UAE brand site | Yes: SH, apaqcafe.com | Makki Delights, Hatan Coffee, عبق كافية | HIGH: core category + city | HIGH: 5 SH titles contain "قهوة مختصة" + Irbid | E1–E4, E40, E43, E44, E47 | SH titles use "إربد" (hamza). Snippet spelling "اوزي للقهوة المختصة" (OZ) |
| 2 | كافيهات اربد | AR | LIST/LOC | TikTok discover; competitor blog list (hatanjo "أفضل كافيهات في إربد لعام 2025"); irbid-now directory; SH guide + home; Time Out Amman list; apaqcafe | Yes: SH, hatanjo, apaqcafe | Hatan, عبق, قهوة بلاك, Moments, Karaz, Al Kamal, Key, VIVAN (tool-summary) | HIGH | HIGH: plural "كافيهات" in 4 independent titles | E3, E1, E40, E42, E44, E48, E55 | "كافيهات اربد/إربد" both seen; Time Out uses "اربد" without hamza |
| 3 | coffee shop Irbid | EN | LOC | Almost all Facebook pages; Wikipedia (City Centre); one brand site | Yes: thinkcoffeejo.com | Chill Café, Guevara, Pure Cafe, THINK. Coffee House, Sydney Coffee House, The Line Coffee House, Dorzo Coffee House, Refresh Coffee | MED: SH absent from top results here | HIGH: 8 FB pages | E68, E69, E93 | English page names favour "Coffee House" |
| 4 | شلتر كوفي اربد | AR | NAV | SH ×8 (home, city-centre-branch, guide, careers, location, about, menu); Wikipedia noise | Yes: SH | none | HIGH: brand | HIGH: SH dominates | E1, E3–E5, E9–E12 | Query says "كوفي" but SH titles say "كافيه/كافية". Old-site titles contain "اربد" (no hamza) in about/location/menu/careers |
| 5 | قهوة اربد | AR | LOC | SH ×4; irbid-now; FB (Makki); mat3am directory ("قهوة عرب"); Wikipedia noise | Yes: SH | Makki Delights, قهوة عرب, Astrolabe, Black Coffee, OZ, Trasimeno (tool-summary) | HIGH | MED: mostly SH + 2 directories | E1, E3, E10, E12, E40, E47, E62 | mat3am titles: "… - اربد - كوفي شوب" |
| 6 | كوفي اربد | AR | LOC | **News** (Addustour ×2 on closing "كوفي شوب" violators / governor rules); TikTok; irbid-now; SH ×3 | Yes: SH | Black Coffee, OZ, Trasimeno, Hatan, Moments (tool-summary) | MED: "كوفي" pulls the shisha "كوفي شوب" news context | HIGH: 2 Addustour news titles + TikTok | E57, E58, E56, E40, E1, E3, E12 | "كوفي شوب" in news = regulated shisha lounges |
| 7 | كافيه اربد | AR | LOC | Instagram (عَبْقّ كافيه); hatanjo list; SH ×4; irbid-now; apaqcafe | Yes: SH, hatanjo, apaqcafe | عبق, Hatan, Black Coffee, Moments, Karaz, The Hive (tool-summary) | HIGH | HIGH | E46, E42, E1, E3, E10, E11, E40, E44 | "كافيه" (ه) is the dominant spelling in titles |
| 8 | مقاهي اربد | AR | LIST | TripAdvisor Arabic ("أفضل 10 مقاهي في محافظة إربد", "أفضل 5 مقاهي في إربد"); OpenSooq guide; Middle East Online ("105 مقاهي انترنت"); TikTok; restaurant blog | No café brand sites | Starbucks (Arabella), Crepello, Scandinavian House + a long list of traditional مقهى names (tool-summary) | MED: wording is formal/old-style; mixes internet cafés & traditional مقهى | MED | E50, E51, E66, E67, E55 | "مقاهي" mainly in TripAdvisor's machine-Arabic titles |
| 9 | افضل كافيه في اربد | AR | LIST | TripAdvisor Arabic ×4; TikTok; hatanjo list; SH home; apaqcafe; irbid-now | Yes: SH, hatanjo, apaqcafe | Hatan, عبق, OZ, Bello Cafe (tool-summary) | MED: "best" = a claim (see §6) | HIGH | E50, E51, E42, E1, E44, E40 | TripAdvisor owns the "أفضل" intent |
| 10 | افضل قهوة في اربد | AR | LIST | TripAdvisor Arabic ×3 ("قهوة وشاي"); TikTok; hatanjo; SH ×2; irbid-now; Time Out | Yes: SH, hatanjo | OZ, Hatan, Wake Cup (ويك كوب), Crepello, قهوة بلاك, Trasimeno (tool-summary) | MED (claim) | HIGH | E52, E50, E51, E42, E1, E3, E48 | — |
| 11 | درايف ثرو اربد | AR | LOC | SH ×5 (drive-thru, how-it-works, home, guide, about); **Saudi municipal investment docs** (furas.momah.gov.sa ×3) | Yes: SH | none local besides SH | HIGH: DRIVE page | MED: only SH is Irbid-specific; the term itself is pan-Arab | E6, E7, E1, E3, E11, E118 | "درايف ثرو" (Arabic) seen only on SH for Irbid; independent Irbid pages use English "Drive Thru" |
| 12 | كوفي شوب اربد | AR | (mismatch) | **News only**: Al Rai (municipal closure complaint), Saraya (shooting at a كوفي شوب), Addustour (10 violators closed), rmix; mat3am "… - كوفي شوب" ×3 | No | قهوة عرب, اون ذا رن كافيه, ذا هيف | LOW / AVOID: shisha-lounge and crime/regulation connotation | HIGH: 4 news domains | E59, E60, E57, E61, E62–E64 | mat3am uses "كوفي شوب" as a category label, incl. "ليمون شيشة بيسترو" |
| 13 | كوفي درايف اربد | AR | NAV/LOC | SH ×4; linktr.ee "شلتر كوفي درايف اربد , روابط المنيو و الطلب"; Instagram "Drive coffee"; Saudi franchise news ("درايف كوفي") | Yes: SH | "Drive coffee" (IG, location unknown) | HIGH: DRIVE brand form | MED | E1, E6, E3, E12, E13, E74 | Brand-owned linktr.ee title uses "شلتر كوفي درايف" (not كافيه) |
| 14 | drive thru coffee Irbid | EN | LOC | Starbucks locator "Wasfi Al-Tal Street Drive Thru"; Story Drive Thru (FB + IG); SHELTER (FB, TripAdvisor, Threads); Drivu app; foodyas aggregator; Pinterest | Partly: SH not in list; Starbucks locator | **Story Drive Thru, Starbucks Wasfi Al-Tal Drive Thru** | HIGH: DRIVE page | HIGH: 3 independent drive-thru businesses seen | E70, E71, E72, E14, E20, E15, E73, E29 | "Drive Thru" (no hyphen) is the dominant form; no "drive through" |
| 15 | specialty coffee Irbid | EN | LOC | Starbucks City Centre locator ("Brewed Coffee"); SHELTER FB; Wikipedia; TripAdvisor (2K Coffee House; Cafés; Coffee & Tea); FB (Dorzo, The Line) | No café brand sites besides Starbucks locator | 2K Coffee House, Starbucks City Centre, Sago, AJDA | HIGH | MED | E75, E14, E78, E51, E53, E68 | English "specialty" (US) spelling; OZ uses "Speciality" (UK) |
| 16 | best cafe Irbid | EN | LIST | TripAdvisor ×5; ibnbattutatravel list; arabplaces directory ×2; regencyholidays blog | No | Hattan Farm, Scandinavian House, Cortina.D, Layl & Nhar, Bizantino, Starbucks Arabella, CUZINS | MED (claim) | HIGH | E50, E51, E52, E53, E80, E81, E82 | TripAdvisor shows "Updated 2023" vs "Updated 2026" title variants |
| 17 | cafes in Irbid Jordan | EN | LIST | TripAdvisor ×3; FB (STEEL, LeoMak); TikTok; travel blogs; Wikipedia | No | Scandinavian House, Sago, AJDA, Meta Coffee, Hail, Cuzins, Via Via, STEEL | MED | HIGH | E51, E52, E53, E55, E83, E82 | — |
| 18 | best coffee Irbid | EN | LIST | TripAdvisor ×6 (incl. Starbucks review, 2K review, **SHELTER photo page**); travel blog | No | 2K Coffee House, Starbucks, Sago, Cortina.D | MED (claim) | HIGH | E51, E52, E53, E79, E78, E22 | — |
| 19 | Shelter Coffee Irbid | EN | NAV | TripAdvisor (SHELTER COFFEE DRIVE); FB; Accessible Jordan; alekirjo (designer portfolio "Shelter Coffee - Irbid Branch"); Threads; mat3am ×2; foodyas; findglocal | No (SH absent!) | none | HIGH: brand | HIGH | E20, E14, E25, E26, E15, E27, E28, E29, E30 | In English the brand surfaces via third parties, not shelterjo.com |
| 20 | shelterjo | EN | NAV | SH home + location page; rest is Wikipedia/IMDb "Shelter" noise | Yes: SH | none | HIGH | HIGH | E1, E10 | One tool-summary gave phone **0799338445** and "Founded in 2019" (unverified, see §7) |
| 21 | Shelter Coffee Drive Irbid instagram | EN | NAV | Snapchat (@shelter_coffee; place "SHELTER COFFEE DRIVE"); Accessible Jordan; X (@ShelterDrive); TripAdvisor; Threads; mat3am; findglocal; cybo | No | none | HIGH | HIGH | E17, E18, E25, E16, E15, E30, E28 | Instagram profile itself not returned as a result; handle @sheltercoffeedrive seen via Threads/linktr.ee |
| 22 | شلتر درايف | AR | NAV | SH ×8; foodyas | Yes: SH | none | HIGH | HIGH | E1, E4, E6, E7, E3, E10, E11, E12, E29 | A tool-summary says some SH page states the business "started in 2022" (conflicts with 2019) |
| 23 | شلتر كوفي | AR | NAV | **Foursquare "شلتر كوفي - Shelter Coffee" = a Riyadh café (closed)** ×2; SH ×5; linktr.ee | Yes: SH | Riyadh "Shelter Coffee" | HIGH | HIGH | E31, E1, E4, E3, E13, E11, E12 | **Brand collision**: the Arabic "شلتر كوفي" without a city brings up a Saudi venue |
| 24 | Shelter Coffee House Irbid City Center | EN | NAV | Snapchat place "SHELTER COFFEE HOUSE"; TripAdvisor UK + .com (DRIVE listing); FB; Wikipedia City Centre; alekirjo; Threads; mat3am; foodyas | No | none | HIGH: HOUSE page | MED: HOUSE has a thin third-party footprint | E19, E21, E20, E14, E93, E26 | "SHELTER COFFEE HOUSE" seen only on Snapchat place title |
| 25 | V60 اربد | MIXED | PROD | Only V60 how-to/shop pages (Saudi roasteries/stores: roastinghouse.sa, ash.coffee, qavashop, wasq, koffiqa, merakiartisan, lamcup, granocafe-sa) | No Irbid sites | none | LOW: no local product intent visible | HIGH (that no Irbid result appears) | E111 | "V60" always in Latin letters in titles |
| 26 | سبانش لاتيه اربد | AR | PROD | Recipes only (Pinterest, cookpad, sayidaty ×2, hellooha, arabecoffee, coffeetast); Saudi ananinja product | No | none | LOW | HIGH | E113 | Spellings: "سبانش لاتيه", "سبانش لاتية" |
| 27 | ايس كوفي اربد | AR | PROD | Recipes (Ahram, elbalad, cookpad, sayidaty, ordercafenajjar, mufhras) + Addustour "كوفي شوب" closure news | No | none | LOW | HIGH | E114, E57 | "آيس كوفي" and "ايس كوفي" both used |
| 28 | حلويات اربد | AR | (mismatch) | Oriental sweets shops: IG (حلويات السلطان فراس الحسنات · Irbid); FB (Shira Sweets, Sharqi Sweet); safarway (Al Aqsa Sweets 2); mat3am (حلويات نفيسة); allspots; halabazaar | No | Kunafa / baklava makers | LOW / AVOID: means traditional sweets (kunafa), not café desserts | HIGH | E108 | "حلويات" alone = Arabic sweets trade |
| 29 | كافيه اربد سيتي سنتر | AR | LOC | FB (هيل كافيه, Icc cafe); IG (Secret cafe); SH ×3; secretcafejo.com; Time Out "دليل محلات إربد سيتي سنتر"; Wikipedia | Yes: SH, secretcafejo.com | Hail/Hael Cafe, ICC Cafe, Secret Cafe, Starbucks, La Vigor (tool-summary) | HIGH: HOUSE page | HIGH | E85, E86, E87, E88, E49, E1, E3, E11 | "سيتي سنتر" (Time Out, independent) vs "ستي سنتر" (SH only) |
| 30 | coffee Irbid City Centre mall cafe | EN | LOC | Starbucks locator ×2; TripAdvisor ×4; Arabela Mall; Wikipedia; Foursquare (Lemon - ليمون - Irbid City Centre); IG Via Via | Starbucks locator | Starbucks City Centre, Lemon | MED | MED | E75, E76, E89, E93, E120 | "Irbid City Centre" (UK) common in EN titles |
| 31 | قهوة وحلويات اربد | AR | PROD/LOC | FB (bakery/grocery); TripAdvisor Arabic; SH home + menu; irbid-now (cafés + restaurants); apaqcafe menu; Qasioun Bakery; allspots sweets | Yes: SH, apaqcafe, qasioungroup | عبق, Qasioun | MED | MED | E1, E12, E40, E45, E109, E52 | Mixed café + bakery intent |
| 32 | coffee and dessert Irbid | EN | PROD/LOC | IG (Moments Coffee irbid); TripAdvisor ×5 (incl. "THE BEST Dessert in Irbid Governorate"); TikTok; Wikipedia noise | No | Moments, 2K, AJDA, Planet Donuts, Crepello | MED | MED | E96, E52, E54, E78 | — |
| 33 | كوفي قريب مني اربد | AR | LOC (near-me) | Talabat UAE ("في 60 سبيشالتي كوفي"); FB (Hatan, Moments); IG (Secret Garden Café); SH home; Saudi "اقرب كوفي من موقعي" page; Marouf Coffee branches | Yes: SH, maroufcoffee.com | Hatan, Moments, Secret Garden, Marouf | MED: real intent sits in Maps (not visible here) | LOW: index can't show near-me behaviour | E100, E43, E97, E98, E1, E99 | "في 60" appears only as a brand name |
| 34 | coffee near me Irbid | EN | LOC (near-me) | TripAdvisor ×4; TikTok; FB (The Line, Dorzo); jordanyp "Top 6 Coffee Shops in Irbid"; Wikipedia | No | Sago, 2K, Scandinavian House, Crepello, Dorzo, AJDA | MED | LOW: same reason | E52, E51, E84, E68 | — |
| 35 | قهوة باردة اربد | AR | PROD | Recipes/brands (Starbucks at home, Al Jazeera 2026-07, cookpad, ananinja, cpt.sa, hawun, wamda) | No Irbid sites | none | LOW | HIGH | E114 | "قهوة باردة" / "قهوه بارده" (cookpad) |
| 36 | iced coffee Irbid | EN | PROD | Starbucks City Centre "Frappuccino"; Think Coffee House site; FB Fury Coffee; Wikipedia ×5 | Yes: thinkcoffeejo.com | Starbucks, Think, Fury | LOW–MED | MED | E77, E69, E102 | — |
| 37 | قهوة مختصة الاردن | AR | INFO/LOC | Talabat "في ٦٠ محامص القهوة المختصة"; Jordanian coffee brands (Al-Ameed, Al-Rayhan, Shammout, أقصى الشمال); **SH home appears** | Yes: SH, alameedcoffee.com | V60 Specialty Coffee Roasters (brand), Al-Ameed | LOW: national term; dominated by bean sellers | MED | E101, E115, E1 | Arabic-Indic digits "٦٠" in the Talabat title |
| 38 | specialty coffee Jordan | EN | INFO/LIST | Sprudge Amman guide; TripAdvisor Amman; Tourist Jordan "Best Coffee in Amman"; Wanderlog Amman roasters; intelligence.coffee; NEBO Roastery | Yes: neboroastery.com | Amman only (Dimitri's, NEBO, Geisha; tool-summary) | LOW: no Irbid page surfaces | HIGH | E115 | Irbid absent from English national results |
| 39 | ما هي القهوة المختصة | AR | INFO | 8 roastery blogs, mostly Saudi (.sa / Saudi roasters: Sout, Air, Koffiqa, Bourbon, Exotics, Mazaqat, OB) | Yes: roastery sites | — | LOW–MED (future knowledge section) | HIGH: content landscape exists | E110 | No Jordanian page in top results |
| 40 | طريقة تحضير V60 | MIXED | INFO | 9 brew guides (qavashop, ash.coffee, Lavazza MENA, koffiqa, i-whj, Kuwait coffee farm, hasil-r, glamor, rashfat) | Yes: roastery/shop sites | — | LOW–MED (knowledge section) | HIGH | E111 | "V60" Latin; "ال v60" lowercase variant; no "في 60" in titles |
| 41 | الفرق بين اللاتيه والكابتشينو | AR | INFO | Nescafé MENA, Marie Claire Arabia, qavashop, bashasaray, Sout, Café Najjar, arabecoffee, ruknqahwa, coffeetast | Yes: brand sites | — | LOW–MED | HIGH | E112 | Words "كابتشينو" and "كابوتشينو" (SH menu uses "كابوتشينو", tool-summary) |
| 42 | سبانش لاتيه ما هو | AR | INFO | Recipe/knowledge blogs (cafeah, arabecoffee, Coffee Curve KSA, coffeeworldblog, dp-cafe, coffeetast, linguacoffee, ejaba, arabianrecipe) | Yes | — | LOW–MED | HIGH | E113 | — |
| 43 | coffee اربد | MIXED | LOC | FB (هيل كافيه, Moments Coffee, Hatan Coffee); irbid-now; SH home + about; halabazaar; Wikipedia | Yes: SH | Hail, Moments, Hatan, Crepello, OZ (tool-summary) | MED | MED | E85, E97, E43, E40, E1, E11 | Mixed query → bilingual FB page names |
| 44 | كافيه irbid | MIXED | LOC | FB pages only: بلانك كافيه - Blank Cafe, Cuchina Cafe -كوتشينا كافيه, RB NOWAR Coffee-نوار كافيه, Lion Cafe - ليون كافيه, اللورد كافيه Al-Lord Cafe, CAV Cafe, هيل كافيه | No | as listed | MED | HIGH: bilingual naming pattern ×6 | E107, E85 | Pattern "EN name - AR name" |
| 45 | talabat Irbid coffee | EN | LOC/delivery | Talabat ×7 (Coffee Town, Hybrid Coffee, Brazilian Coffee House Irbid ×4, Bizantino, Cowboy Irbid); TripAdvisor; Think site | Yes: thinkcoffeejo.com | Brazilian Coffee House, Hybrid, Coffee Town, Bizantino | MED (delivery channel) | MED | E103, E69 | Talabat neighbourhood names: "Al Worod", "King Abdullah II Gardens", "Al Sareeh", "Al Marrj" |
| 46 | wanderlog best coffee shops Irbid | EN | LIST | TripAdvisor ×3; Wanderlog pages for *other* cities only | No | Hattan Farm, Sago, AJDA, Barqesh, Shot Cofee, Hit & Run (tool-summary) | LOW | MED: no Wanderlog Irbid list surfaced | E51, E52, E53 | No Wanderlog Irbid page seen |
| 47 | foursquare coffee shop Irbid | EN | LIST | Foursquare ×10 (Meta Cafe, on the run, Container Cafe, Caffeine 86, Lemon, Irbid City Centre) | No | Meta, Container, Caffeine 86, Lemon, on the run | LOW (old listings) | MED | E104, E105, E89 | Foursquare tags many places "Irbid City Center (University Street)" (tool-summary) |
| 48 | Irbid drive through coffee Starbucks Story Drive | EN | LOC | Story Drive Thru IG + US Starbucks noise | No | Story Drive Thru | MED | MED | E72 | Story Drive Thru described (tool-summary) as "east of Qasr Al-Nakheel halls", i.e. the same micro-area as SHELTER DRIVE |
| 49 | "drive thru" Irbid | EN | LOC | Story Drive Thru FB; Starbucks Wasfi Al-Tal Drive Thru; SHELTER (TripAdvisor, FB, Threads, foodyas); US/UK noise | No | Story, Starbucks DT | HIGH | HIGH | E71, E70, E20, E14, E15, E29 | — |
| 50 | كافيه قصر النخيل اربد | AR | LOC (landmark) | Qasr Al-Nakheel halls (Snapchat, Accessible Jordan AR, X, directoryjordan); SH home + drive-thru + guide | Yes: SH | none | HIGH: landmark for DRIVE | MED | E119, E1, E6, E3 | Landmark spellings: "قصر النخيل", "قاعات قصر النخيل", "صالة قصر النخيل"; EN "Qaser Al-Nakheel", "Qasaralnokhleh" |
| 51 | Story Drive Thru Irbid | EN | NAV (competitor) | Story FB + IG; Starbucks DT; SHELTER TripAdvisor/FB/Threads; Drivu; foodyas | No | Story Drive Thru | n/a (competitor check) | HIGH | E71, E72, E70, E20 | SHELTER co-appears on a competitor's brand query |
| 52 | "درايف ثرو" إربد قهوة | AR | LOC | SH ×7 (incl. /family/ "عائلة شلتر كافيه - فريق درايف ثرو القهوة المختصة"); Saudi HungerStation | Yes: SH | none | HIGH | MED (only SH) | E8, E4, E6, E7, E1, E3, E11, E12 | SH drive-thru hours text "7:00 ص – 2:00 ل" ("ل" = ليلاً), misread as PM by the tool summary |
| 53 | افضل كوفي اربد | AR | LIST | IG Secret Garden Café; hatanjo list; SH ×2; irbid-now; apaqcafe; halabazaar | Yes: SH, hatanjo, apaqcafe | Secret Garden, Hatan, عبق, Crepello | MED (claim) | MED | E98, E42, E1, E3, E40, E44 | — |
| 54 | قهوة مختصة في اربد | AR | LOC | SH ×7; apaqcafe; irbid-now | Yes: SH, apaqcafe | عبق, OZ, Dose Coffee (دووز) (tool-summary) | HIGH | MED: SH-dominated | E1, E6, E2, E4, E3, E10, E11, E44, E40 | Tool-summary quotes SH: "شارع الجامعة بجانب قصر النخيل … مواقف مجانية … 7ص—2ل" |
| 55 | في 60 اربد | AR | PROD | Pure noise (number 60, Quran verse, Genesis GV60, Irbid hospital, DST) | No | — | NONE | HIGH | — | The Arabic-script "في 60" is not used for V60 in an Irbid context |
| 56 | ايس سبانش لاتيه | AR | PROD/INFO | Starbucks at Home; cookpad **jo** "آيس سبانش لاتيه 🥤"; cookpad eg "ايس سبانش لاتيه"; El Abd "أسبانش لاتيه"; Amazon EG RTD; Coffee Curve; Sprink "Ice Spanish Latte -آيس سبانيش لاتيه" | Yes: sprinkcoffee.com | — | LOW–MED (menu naming) | HIGH | E113 | Jordanian cookpad uses "آيس" (with madda) |
| 57 | Spanish latte Irbid | EN | PROD | Recipes only (US/UK blogs, Nescafé PH) | No | — | LOW | HIGH | — | No local result |
| 58 | V60 Irbid coffee | EN | PROD | Brew guides only (US/UK roasters, Gulf News) | No | — | LOW | HIGH | — | No local result |
| 59 | شلتر اربد | AR | NAV | SH ×8 (incl. careers "فرص عمل شلتر كافيه اربد"); Airbnb noise | Yes: SH | — | HIGH | HIGH | E1, E5, E6, E7, E3, E9, E10, E11 | — |
| 60 | وظائف باريستا اربد | AR | JOB | OpenSooq ×4 ("وظائف باريستا في إربد", "وظائف شاغرة في إربد", a barista ad); Saudi sabbar ×2; wzzff; jordanrec | No | Ben Izhiman (barista ad; tool-summary) | MED: careers page | HIGH: OpenSooq dominates | E116 | OpenSooq category titles use "إربد" (hamza) |
| 61 | كافيهات أربد | AR | LIST | Same as #2 (TikTok, hatanjo, irbid-now, SH, apaqcafe, Time Out) | Yes | as #2 | MED | MED | E55, E42, E40, E1, E44, E48 | The search treats أربد ≈ اربد ≈ إربد; "أربد" itself appears only on SH (H1, careers title) |
| 62 | كافيهات إربد شارع الجامعة | AR | LIST/LOC | irbid-now (AR + EN "كافيهات في محافظة إربد — 282 نشاطًا"); IG Secret Garden; perfecta-hier "كافيهات شارع الجامعة اربد"; SH guide; Wikipedia (Amman street) noise | Yes: SH | Secret Garden, Shisha Café, قهوة بلاك, كريبيلو, Alghanem (tool-summary) | LOW: SHELTER isn't on University Street (but see §7: one SH title says "شارع الجامعة") | MED | E40, E41, E98, E127, E3 | "شارع الجامعة" = "شارع اليرموك" (University St.) is the main café strip |
| 63 | فعاليات كافيه اربد | AR | EVT | TripAdvisor activities; IG Secret Garden; TikTok ×2; Wikipedia (Arab Capital of Culture) | No | Secret Garden, CAV Café (events; tool-summary) | LOW: no café-events ecosystem visible | LOW | E117, E98 | — |
| 64 | cafe Irbid City Center | EN | LOC | Starbucks locator; TripAdvisor Secret Cafe ×2; Foursquare Lemon; Alshaya Starbucks; Wikipedia; Wikimapia; m-icc.com (official mall); urtrips | Starbucks/Alshaya, m-icc.com | Starbucks, Secret Cafe, Lemon | HIGH: HOUSE page | HIGH | E76, E95, E89, E90, E92, E93, E94 | Official mall site writes "Irbid City Center" (US); Wikipedia writes "Centre" |
| 65 | Shelter Coffee talabat Jordan | EN | NAV/delivery | Talabat ×5 ("SHELTER COFFEE DRIVE delivery service in Jordan", menus per area); Accessible Jordan; FB; TripAdvisor ×2 | No | — | HIGH | HIGH | E23, E24, E25, E14, E20, E22 | Talabat branch slug "shelter-coffee-drive-al-worod". Tool-summary: best-sellers include "Iced Spanish Latte", Cookies, Nutella Cookies, "Code Red With Flavor" (unverified) |
| 66 | "Shelter Coffee House" Irbid | EN | NAV | Snapchat "SHELTER COFFEE HOUSE"; TripAdvisor ×2; FB; alekirjo; Threads; foodyas; mat3am ×2; findglocal | No | — | HIGH: HOUSE | MED | E19, E20, E21, E14, E26 | Third parties mostly list everything under "DRIVE" |
| 67 | قهوة درايف ثرو اربد | AR | LOC | X post (Saudi "درايف كافية … مقابل النخيل مول"); HungerStation KSA; Haraj KSA; SH ×5 | Yes: SH | Saudi "Drive Coffee" | HIGH | MED | E118, E7, E6, E1, E3, E11 | Note "النخيل مول" (Saudi) vs "قصر النخيل" (Irbid): a name-confusion risk |
| 68 | قهوة اربد سيتي سنتر | AR | LOC | FB (هيل كافيه, Icc cafe); Alshaya "ستاربكس في إربد، الأردن"; khareta property ad; IG Secret cafe; SH ×3; mat3am ×2 ("ليمون شيشة بيسترو - اربد سيتي سنتر") | Yes: SH | Hail, Starbucks, ICC, Secret, Lemon Shisha Bistro | HIGH: HOUSE | HIGH | E85, E86, E91, E87, E3, E1, E11, E63, E65 | "سيتي سنتر" in 3 independent titles |
| 69 | كافيه للدراسة اربد | AR | LOC (use-case) | TikTok ×3; Wikipedia noise | No | Bello, ICC, Secret (tool-summary) | UNKNOWN: depends on whether HOUSE offers study seating (MISSING — OWNER INPUT REQUIRED) | LOW | E56 | Study-café demand appears on TikTok, not the web |
| 70 | محمصة قهوة اربد | AR | LOC/PROD (beans) | FB (Makki, Hatan, Black House); ash.coffee (KSA); alarbaroasters; irbid-now; SH home; IG Secret Garden | Yes: SH, alarbaroasters.com, ash.coffee | Makki Delights, Black House | LOW unless SHELTER sells beans (MISSING — OWNER INPUT REQUIRED) | MED | E47, E43, E40, E1 | Tool-summary repeats "second floor" for City Centre (conflict; see §7) |
| 71 | OZ Speciality Coffee Irbid | EN | NAV (competitor) | ozcoffee.house ×2; App Store; IG (brew.oz, oz.ae: other cities); FB ×2; irbid-now EN | Yes: ozcoffee.house | OZ (has its own site **and app**) | n/a (competitor check) | HIGH | E106, E41 | Competitor runs a brand site + app |
| 72 | instagram sheltercoffeedrive | EN | NAV | FB "The Shelter Coffee" (unrelated? see §7); Accessible Jordan; FB Shelter Coffee Drive; linktr.ee; Threads | No | — | HIGH | MED | E33, E25, E14, E13, E15 | Tool-summary: IG bio "Leader Of Coffee in North", "Quality You Can Taste STAY SHELTERED" (unverified) |
| 73 | كافيه 24 ساعة اربد | AR | LOC (hours) | Riyadh/Medina/Jeddah/Khobar "24 hour cafés" lists; TikTok Amman; irbid-now | No | قهوة بلاك, OZ, Trasimeno, Al Kamal, The Hive (tool-summary) | DO NOT TARGET until Owner confirms hours | MED | E40 | "24 ساعة" / "٢٤ ساعه" variants; Gulf content dominates |
| 74 | Meta Cafe Irbid specialty coffee | EN | NAV (competitor) | Foursquare; TripAdvisor ×3; FB metacoffeejo; YouTube; metacoffeejo.com (shop) | Yes: metacoffeejo.com | Meta Coffee | n/a | MED | E104 | Meta's Foursquare/FB text claims "first cafe to serve specialty coffee in Irbid" (tool-summary). Relevant to any "first" claim (§6) |
| 75 | "Shelter Coffee" | EN | NAV (global) | FB The Shelter Coffee; TripAdvisor HCMC; sheltercoffee.com; Shelter Dog Coffee; Yelp Buenos Aires; Wanderlog | Yes: other brands | Global namesakes | n/a | HIGH | E33, E35, E36 | Bare "Shelter Coffee" is heavily contested globally |
| 76 | "The Shelter Coffee" facebook | EN | NAV (global) | 9 FB pages: Buenos Aires, Phuket, Hanoi, Aberdeen, Bangkok, HCMC, etc. | — | — | n/a | HIGH | E33 | — |
| 77 | شلتر كافيه | AR | NAV | **cafesriyadh.com "شلتر كافية ( الأسعار + المنيو + الموقع ) - كافيهات و مطاعم الرياض"** (2021, rank 1); SH ×6; FB "Shelter Café" (unknown page) | Yes: SH | Riyadh "شلتر كافية" | HIGH | HIGH | E32, E8, E5, E1, E6, E11, E12, E34 | Collision: "شلتر كافية" is also a Riyadh café name |
| 78 | شلتر كافية الطلب كريم طلبات | AR | NAV/delivery | SH home + careers ("طلب توظيف في شلتر كافية أربد") + location; Careem noise | Yes: SH | — | MED | MED | E1, E9, E10 | SH location page says ordering via "كريم و طلبات" (fetched) |
| 79 | كوفي هاوس اربد | AR | LOC | Talabat Amman coffee houses ×5; SH home + coffee-house-vs-cafe + guide; FB Black House Cafe | Yes: SH | Shams/Miel/Q Light (Amman, Talabat), Black House | MED: "Coffee House" is the brand's 2nd branch name | MED | E125, E1, E4, E3 | "كوفي هاوس" in Irbid titles: SH + mat3am "ثنك كوفي هاوس" |
| 80 | منيو كافيه اربد | AR | PROD (menu) | FB Makan; IG Secret Garden, Secret cafe; key-jo.com "القائمة - KEY - IRBID"; halabazaar | Yes: key-jo.com | Makan, KEY, Secret | MED: menu page | MED | E122, E98, E87 | "منيو" (colloquial) vs "القائمة" (SH and KEY page titles) |
| 81 | ستاربكس اربد درايف ثرو وصفي التل | AR | NAV (competitor) | Bayut Dubai; mat3am (Think Coffee House); Riyadh Starbucks | No | — | n/a | LOW | — | Arabic results for Starbucks Irbid DT are weak; the EN locator exists (E70) |
| 82 | Irbid coffee culture specialty cafes students Yarmouk | EN | INFO | Yarmouk Univ. pages; TripAdvisor near Yarmouk; travel blogs | No | 2K, Scandinavian House, Cortina.D | LOW | MED | E124, E78 | — |
| 83 | كوكيز اربد | AR | PROD | FB "اربد - irbid"; TikTok; El Abd cookies; FB "Grano cookies -جرانو كوكيز" (Irbid bakery); Wikipedia noise | No | Grano cookies | LOW–MED (if cookies are an approved menu item) | LOW | E121, E126 | — |
| 84 | شارع عوض رشيدات اربد كافيه | AR | LOC (street) | TripAdvisor AR (near 7 Days Hotel); Ministry of Tourism restaurant lists (PDF ×2); Wikipedia noise; SH home + guide | Yes: SH | — | HIGH: DRIVE address term | LOW: almost no independent page uses the street name | E123, E1, E3 | Street spellings: "عوض رشيدات", "Awad Rshedat", "Eiwad Rashidat", "Awad Rashbaddat" (fetch-tool typo) |
| 85 | Arabella mall Irbid coffee near | EN | LOC | TripAdvisor Starbucks (Arabella); Arabela Mall attraction; Snapchat; arabellamall.jo; urtrips; mapcarta | arabellamall.jo | Starbucks Arabella, ICC Cafe | LOW: "Arabella" queries resolve to the mall, where SHELTER isn't | MED | E79, E120 | Spellings "Arabella" / "Arabela" / "أرابيلا" |
| 86 | مقهى اربد قهوة مختصة سبيشالتي | AR | LOC | FB Hatan; SH home + guide; irbid-now; apaqcafe; Saudi pages | Yes: SH, apaqcafe | Hatan ("كوفي شوب & قهوة مختصة أراجيل"), عبق ("أجواء الأرجيلة الفاخرة") (snippets) | MED | MED | E43, E1, E3, E40, E44 | "سبيشالتي" never appears in Irbid titles. Local competitors pair "قهوة مختصة" with **أراجيل** (shisha) |

---

## 3. Real local language

These are **sample counts**: distinct pages whose *titles* (or URL slugs where stated) I saw in an Irbid context. They are **not** search volume. "Independent" means not shelterjo.com and not a SHELTER-owned profile.

### 3.1 City name
| Variant | Independent pages seen | Domains | SH pages | Where it appears | Confidence |
|---|---|---|---|---|---|
| **اربد** (no hamza) | ~21 | ≥10 (Time Out Amman, mat3am ×6, Addustour, Al Rai, Saraya, rmix, Middle East Online, TikTok, OpenSooq ad, arbdar, khareta, perfecta-hier, FB "اربد - irbid", allspots, apaqcafe menu) | 4 (about, location, menu, careers) | News, user-generated content, directories, social | HIGH: the everyday spelling |
| **إربد** (hamza below) | ~14 | 7 (irbid-now ×2, hatanjo, TripAdvisor-AR ×5, OpenSooq ×3, Time Out trend, Alshaya-AR, safarway) | ~7 (home, guide, drive-thru, how-it-works, coffee-house-vs-cafe, city-centre) | Formal directories, TripAdvisor's Arabic, OpenSooq categories | HIGH: the formal/directory spelling |
| **أربد** (hamza above) | 0 | 0 | 2 (home H1 "أفضل كافيهات أربد"; careers title "… شلتر كافية أربد") | Only on SHELTER's old site | LOW: not real local usage in this sample |
| **Irbid** (EN) | many (TripAdvisor, FB, Starbucks, Foursquare, Talabat) | ≥8 | — | All English listings | HIGH |

Searches for اربد, إربد and أربد returned near-identical result sets (queries #2 vs #61), so the index seems to normalise them. Page titles still differ, as the table shows.

### 3.2 "Café" words
| Term | Independent Irbid titles seen | Character of usage | Confidence |
|---|---|---|---|
| **كافيه / كافيهات** | ~16 (irbid-now ×2, hatanjo, Time Out, perfecta-hier, TikTok, FB: هيل كافيه, بلانك كافيه, كوتشينا كافيه, نوار كافيه, ليون كافيه, اللورد كافيه; IG عَبْقّ كافيه; mat3am اون ذا رن كافيه) | Neutral, modern; the default word | HIGH |
| **كافية** (ة) | 3 (apaqcafe "عبق كافية" ×2, hatanjo "هتان كافية") + SH site name "شلتر كافية" | Used by some brands as their own name | MED |
| **كوفي شوب** | ~10 pages / 5 domains (Addustour ×2, Al Rai, Saraya, rmix, mat3am category ×5 incl. "ليمون شيشة بيسترو") | **Shisha-lounge / regulatory / crime-news connotation** in Irbid | HIGH |
| **كوفي هاوس** | 1 Irbid (mat3am "ثنك كوفي هاوس"); Talabat Amman ×5 | Brand-name style; English "Coffee House" very common in Irbid FB names (THINK, Sydney, The Line, Dorzo, 2K, Sago, AJDA) | MED |
| **كوفي** (alone) | Brand names only: "شلتر كوفي درايف" (linktr.ee), "شلتر كوفي" (Foursquare, Riyadh) | — | LOW |
| **مقهى / مقاهي** | 3 (TripAdvisor-AR ×2, Middle East Online "مقاهي انترنت") | Formal/machine-translated; also internet cafés and traditional مقهى | MED |
| **قهوة** (in a venue name) | 2 (mat3am "قهوة عرب"; TripAdvisor-AR "قهوة وشاي" category) | — | LOW |

### 3.3 Specialty coffee
| Term | Seen in Irbid titles | Notes | Confidence |
|---|---|---|---|
| **قهوة مختصة** | 0 independent titles; 5+ SH titles; in snippets: Hatan FB ("كوفي شوب & قهوة مختصة"), OZ listing ("اوزي للقهوة المختصة") | The established Arabic term; also standard across Saudi/Gulf content (#39) | MED |
| **سبيشالتي** | 0 Irbid; only "في 60 سبيشالتي كوفي" (Talabat UAE brand) | Not used locally in this sample | LOW |
| **Specialty / Speciality** (EN) | "OZ Speciality Coffee" (UK spelling, brand); "Specialty" in Starbucks pages and generic EN | Both spellings exist | MED |

### 3.4 Drive-thru
| Variant | Irbid evidence | Confidence |
|---|---|---|
| **Drive Thru** (EN, no hyphen) | Story Drive Thru (FB + IG), Starbucks "Wasfi Al-Tal Street Drive Thru", SHELTER listings ("SHELTER COFFEE DRIVE") | HIGH: 3 independent businesses |
| **drive-thru** (hyphen) | SH URL `/drive-thru/` only | LOW |
| **drive through** | 0 Irbid (Pinterest/US only) | LOW |
| **درايف ثرو** (AR) | SH titles ×5; otherwise Saudi pages (municipal investment, HungerStation, Haraj, X) | MED: real Arabic term, but no independent Irbid title uses it |
| **درايف** (alone, in a brand name) | "شلتر كوفي درايف" (linktr.ee), "Drive coffee" (IG, location unknown), "Drivu" (app) | LOW |

### 3.5 Products
| Item | Variants seen (sample counts across all titles, not Irbid-specific) | Confidence |
|---|---|---|
| V60 | "V60" in Latin in ~14 titles; "ال v60" ×2; "في 60" / "في ٦٠" **only** as the brand name "V60 Specialty Coffee (Roasters)" on Talabat ×2; "ف60" ×0 | HIGH |
| Spanish latte | "سبانش لاتيه" ~11; "سبانش لاتية" 1; "سبانيش" 1 (Sprink); "أسبانش" 1 (El Abd) | HIGH |
| Iced | "آيس" ×6 (incl. **cookpad.com/jo**, Jordan); "ايس" ×4 | MED: both live; the Jordan sample used "آيس" |
| Cappuccino | "كابتشينو" in most titles; SH menu uses "كابوتشينو" (tool-summary) | MED |
| Menu | "منيو" (colloquial, query) vs "القائمة" (SH, KEY page titles) | MED |

### 3.6 Mall / landmarks / streets
| Item | Variants (sample) | Confidence |
|---|---|---|
| City Centre (AR) | **"سيتي سنتر"** ×4 independent (Time Out trend, mat3am, khareta, Alshaya-AR slug); **"ستي سنتر"** on SH titles + 1 khareta slug | HIGH that "سيتي سنتر" is the common form |
| City Centre (EN) | "Irbid City Centre" ×~5 (Wikipedia, Starbucks, Foursquare, Alshaya); "Irbid City Center" ×3 (m-icc.com official, Wikimapia, urtrips) | HIGH: both forms; the official mall site uses "Center" |
| Qasr Al-Nakheel | "قصر النخيل", "قاعات قصر النخيل", "صالة قصر النخيل"; EN "Qaser Al-Nakheel", "Qasr Alnakheel", "Qasaralnokhleh" | MED |
| Arabella | "Arabella", "Arabela", "أرابيلا" (mostly resolves to Arabella Mall) | MED |
| Café-strip names people use | شارع الجامعة / شارع اليرموك (main strip), وصفي التل, أبو راشد, دوار الطيارة, دوار الثقافة, Hasan St., سامح مول | MED |

### 3.7 Mixed-language patterns
- Facebook/Instagram page names are often bilingual. I saw ~11: "بلانك كافيه - Blank Cafe", "Cuchina Cafe -كوتشينا كافيه", "RB NOWAR Coffee-نوار كافيه", "Lion Cafe - ليون كافيه", "اللورد كافيه Al-Lord Cafe", "Lemon - ليمون", "Grano cookies -جرانو كوكيز", "حلويات شيره / Shira Sweets", "حلويات شرقي Sharqi Sweet", "مكي ديلايتس Makki Delights", "Moments Coffee irbid 🤍".
- Mixed queries ("coffee اربد", "كافيه irbid") return mainly these bilingual social pages plus SH.
- SHELTER's own handles are mixed too: "SHELTER COFFEE DRIVE • IRBID" (Threads) and "شلتر كوفي درايف اربد , روابط المنيو و الطلب" (linktr.ee).
- Confidence: MED. The pattern is clearly real in page names; the index can't tell how often people *type* mixed queries.

---

## 4. Clusters

The "Likely target page" column is a **proposal for the keyword-to-page map, not a decision**.

| Cluster | Queries (from §2) | What the SERP looks like | Likely target page (proposal) | Notes |
|---|---|---|---|---|
| **A. Brand** | شلتر كوفي اربد, شلتر اربد, شلتر درايف, شلتر كافيه, شلتر كوفي, Shelter Coffee Irbid, shelterjo, "Shelter Coffee House" Irbid, Shelter Coffee talabat | AR: SH dominates. EN: TripAdvisor, FB, Talabat, Threads, aggregators; shelterjo.com is absent | Home (+ DRIVE / HOUSE pages for the branch-named forms) | Collisions: Riyadh "شلتر كوفي/كافية", global "Shelter Coffee". Always pair the brand with Irbid / إربد |
| **B. General local café** | قهوة اربد, كوفي اربد, كافيه اربد, كافيهات اربد/أربد, coffee shop Irbid, cafes in Irbid Jordan, coffee اربد, كافيه irbid | Directories (irbid-now, mat3am), a competitor blog list (hatanjo), TripAdvisor, FB pages, TikTok, SH | Home + Locations | Prefer "كافيه" and "اربد/إربد"; avoid "كوفي شوب" |
| **C. Specialty coffee** | قهوة مختصة اربد, قهوة مختصة في اربد, specialty coffee Irbid, مقهى اربد قهوة مختصة سبيشالتي | SH-dominated in AR; EN = TripAdvisor + Starbucks locator | Home + Menu (+ future knowledge hub) | Use "قهوة مختصة", not "سبيشالتي" |
| **D. Drive-thru** | درايف ثرو اربد, كوفي درايف اربد, قهوة درايف ثرو اربد, "درايف ثرو" إربد قهوة, drive thru coffee Irbid, "drive thru" Irbid, كافيه قصر النخيل اربد, شارع عوض رشيدات اربد كافيه | AR: only SH is Irbid-specific. EN: Story Drive Thru, Starbucks Wasfi Al-Tal Drive Thru, SHELTER listings | **DRIVE branch page** | Real competitors exist (Story DT in the same Qasr Al-Nakheel area; Starbucks DT). EN form "Drive Thru"; AR form "درايف ثرو" |
| **E. Café / sit-down** | كوفي هاوس اربد, كافيه للدراسة اربد, coffee and dessert Irbid | Mixed; study intent lives on TikTok | **HOUSE branch page** | Study / co-working claims need Owner confirmation |
| **F. Location / City Centre** | كافيه اربد سيتي سنتر, قهوة اربد سيتي سنتر, cafe Irbid City Center, coffee Irbid City Centre mall cafe, Shelter Coffee House Irbid City Center | FB/IG of mall cafés (Hail, ICC, Secret), Starbucks locator, Foursquare Lemon, official mall site, SH | **HOUSE branch page** + Locations | Use "سيتي سنتر" (common) and keep "ستي سنتر" as a secondary variant; EN "City Center" (official mall) + "City Centre" |
| **G. Product intent** | V60 اربد, سبانش لاتيه اربد, ايس كوفي اربد, قهوة باردة اربد, iced coffee Irbid, Spanish latte Irbid, V60 Irbid coffee, منيو كافيه اربد, كوكيز اربد | Recipes and regional (mainly Saudi) shops; **no Irbid café pages** | Menu (item names and categories) | Little local "product + city" result in this index; real demand may sit in Maps/Talabat (not visible). Name items with the common spellings (V60 in Latin, "سبانش لاتيه", "آيس/ايس") |
| **H. Dessert pairing** | قهوة وحلويات اربد, coffee and dessert Irbid, (حلويات اربد = mismatch) | Mixed café/bakery; "حلويات" alone = oriental sweets | Menu (cakes/cookies section) | Use "قهوة و كيك" / "كيك" / "حلى"-style wording only if the Owner approves those items; avoid bare "حلويات اربد" |
| **I. Near-me** | كوفي قريب مني اربد, coffee near me Irbid | Can't be judged here (Maps/Local Pack not visible) | Locations + Google Business Profile (gated) | Needs Search Console / GBP data later (phase-gated) |
| **J. Informational** | ما هي القهوة المختصة, طريقة تحضير V60, الفرق بين اللاتيه والكابتشينو, سبانش لاتيه ما هو, ايس سبانش لاتيه | A large Arabic content landscape, dominated by Saudi roasteries/stores and recipe sites (cookpad, sayidaty); no Jordanian page in the top results | Future coffee-knowledge section (optional) | Demand and content clearly exist; competition is regional, not Irbid-local |
| **K. Careers** | وظائف باريستا اربد | OpenSooq dominates; SH careers page ranks for brand+careers | Careers page | — |
| **L. Events** | فعاليات كافيه اربد | No café-events ecosystem visible | Events page (low search value per this sample) | — |

---

## 5. Competitor / ecosystem observations (record only)

These are names that appeared repeatedly. This is not a quality judgement.

**Competitors that run their own website**
- عبق كافية (apaqcafe.com)
- Hatan (portal.hatanjo.com, which hosts a "best cafés in Irbid 2025" list)
- THINK Coffee House (thinkcoffeejo.com)
- OZ Speciality Coffee (ozcoffee.house + an iOS app)
- Secret Cafe (secretcafejo.com)
- KEY Irbid (key-jo.com)
- Meta Coffee (metacoffeejo.com, a shop)
- Marouf Coffee (maroufcoffee.com)

**Drive-thru competitors**
- Story Drive Thru (FB/IG). Located, per a tool-summary, "east of Qasr Al-Nakheel halls".
- Starbucks Wasfi Al-Tal Street Drive Thru (Starbucks locator).

**City Centre mall cafés**
- Starbucks City Centre
- Hail/Hael Cafe
- ICC Cafe
- Secret Cafe
- Lemon / ليمون شيشة بيسترو

**Directories that recur**
- irbid-now.com (Arabic + English)
- mat3am.net
- TripAdvisor (EN + machine-AR)
- Foursquare
- foodyas / findglocal
- jordanyp
- arabplaces
- OpenSooq (jobs)
- Talabat (delivery)

**Social**
- TikTok "discover" pages show up for Arabic café queries ("Irbid Cafe", "كافيه اربد الجديد كارفور", "اماكن ترفيهيه في اربد").

**Shisha**
- Several Irbid cafés pair specialty coffee with argileh in their own descriptions (Hatan, عبق; snippets).
- Whether SHELTER serves argileh is unknown → **MISSING — OWNER INPUT REQUIRED**. Don't target.

---

## 6. Queries we should NOT target (with reasons)

| Query / pattern | Reason |
|---|---|
| **كوفي شوب اربد** (and "كوفي شوب" generally) | In Irbid the SERP is regulatory/crime news about shisha "coffee shops" (closures, violations, a shooting). The brand-safety risk is high (E57–E61) |
| **حلويات اربد** (bare) | The intent is oriental sweets / kunafa shops, not café desserts (E108) |
| **افضل … اربد / best … Irbid** as a claim on our own pages (titles, H1, copy) | "Best" is a public claim that needs proof (M38: public claims → Owner decision). The old site's H1 "شلتر كافيه أفضل كافيهات أربد" and "أفضل قهوة مختصة" are exactly this. TripAdvisor / hatanjo own the list intent anyway |
| **"أول درايف ثرو في شمال الأردن" / "first" claims** | Competing "first" claims and facts exist: Meta Coffee says it is the "first cafe to serve specialty coffee in Irbid" (tool-summary). Story Drive Thru and Starbucks Drive Thru operate in Irbid. Any "first" needs Owner evidence → **PENDING OWNER INPUT** |
| **في 60 اربد / ف60** | No such local usage; returns noise. "في ٦٠" is a different roaster brand (Talabat) |
| **كافيه 24 ساعة اربد** | SHELTER's hours are inconsistent across sources (§7). Don't target until the Owner confirms Master Data hours |
| **كافيه للدراسة اربد / co-working** | Only the old "about" page claims co-working spaces (fetched). Needs Owner confirmation |
| **محمصة قهوة اربد / بن** | Only relevant if SHELTER sells beans or roasts; unknown → **MISSING — OWNER INPUT REQUIRED** |
| **أراجيل / شيشة** queries | Unknown offering; brand-policy question |
| **Arabella mall coffee** | Resolves to Arabella Mall (Starbucks inside). SHELTER DRIVE is *near* the Arabella area, not in the mall. Use "Arabella" only as a landmark, never as "in Arabella Mall" |
| **City Centre + drive-thru** | The old City Centre page itself says the drive-thru is only at the main branch (fetched). Never combine them |
| **Bare "Shelter Coffee" / "شلتر كافية" / "شلتر كوفي"** without Irbid | Global and Riyadh namesakes dominate (E31–E36). Always qualify with Irbid / إربد / اربد |
| **specialty coffee Jordan / قهوة مختصة الاردن** | National; Amman guides and bean sellers dominate. Low relevance for a 2-branch Irbid business |
| **Recipe queries** ("طريقة عمل سبانش لاتيه", "طريقة عمل ايس كوفي") as primary targets | Home-recipe intent, not visit intent. At most a knowledge article later |
| **Competitor brand queries** (Starbucks اربد, Story Drive Thru, OZ…) | Not our pages; also avoid comparative claims |
| **شارع الجامعة كافيهات** | SHELTER isn't on the University-Street café strip per most sources (but see the §7 conflict: one old-site title says "شارع الجامعة") |
| **مقاهي انترنت / مقهى** (old-style) | A different venue type (internet / traditional cafés) |
| **سبيشالتي** | Zero local usage in this sample |

---

## 7. How SHELTER appears today (observed, not judged)

Everything below is **recorded as seen**. None of it is verified business data. Master Data must come from the Owner.

### 7.1 URLs that surface
**Old site (www.shelterjo.com):** it dominates almost every Arabic Irbid café query in this index. Pages and titles seen:
- `/` "شلتر كافيه إربد - قهوة مختصة ودرايف ثرو". Fetched title adds "| كافيهات اربد". H1s: "شلتر كافيه أفضل كافيهات أربد", "شلتر كافية اكتشف مذاق جديد".
- `/drive-thru/` "خدمة الدرايف ثرو في شلتر كافيه إربد - شلتر كافية"
- `/drive-thru-shelter-irbid-how-it-works/` "كيف يعمل الدرايف ثرو في شلتر كافيه إربد — خطوة بخطوة"
- `/city-centre-branch/` "شلتر كافيه — إربد ستي سنتر، الطابق الأول - شلتر كافية"
- `/best-cafes-irbid-2026/` "دليل كافيهات إربد 2026 — قهوة مختصة، كوفي هاوس ودرايف ثرو - شلتر كافية"
- `/coffee-house-vs-cafe/` "الفرق بين كوفي هاوس وكافيه — وأيّهما يناسبك في إربد - شلتر كافية"
- `/blog/` "مدونة شلتر كافية - مقالات عن القهوة المختصة"
- `/family/` "عائلة شلتر كافيه - فريق درايف ثرو القهوة المختصة"
- `/من-هو-شلتر-كافية-في-اربد/` "من نحن - شلتر كافيه اربد". Fetched title: "من نحن - شلتر كافيه اربد | قصة قهوة مختصة".
- `/موقع-شلتر-كافية-محافظة-اربد/` "موقع شلتر كافيه اربد". Fetched title: "موقع شلتر كافيه اربد | شارع الجامعة - قصر النخيل".
- `/القائمة-شلتر-كافية-محافظة-اربد/` "القائمة - شلتر كافيه اربد". Fetched title: "… | 40+ مشروب مختص".
- `/التوظيف-في-شلتر-كافية/` "فرص عمل شلتر كافيه اربد" / "طلب توظيف في شلتر كافية أربد"
- Home navigation also links to "فرص الشراكة FRANCHISE".

**Third-party listings and profiles**
- TripAdvisor "SHELTER COFFEE DRIVE, Irbid - Menu, Prices & Restaurant Reviews" (+ UK and photo pages). Tool-summary: "5.0", "#18 of 31".
- Facebook "Shelter Coffee Drive" (/sheltercoffeedrive).
- Threads "SHELTER COFFEE DRIVE • IRBID (@sheltercoffeedrive)".
- X "Shelter Coffee Drive (@ShelterDrive)".
- Snapchat "SHELTER COFFEE DRIVE (@shelter_coffee)" and places "SHELTER COFFEE DRIVE" + "SHELTER COFFEE HOUSE".
- linktr.ee "شلتر كوفي درايف اربد , روابط المنيو و الطلب".
- Talabat "SHELTER COFFEE DRIVE delivery service in Jordan" + area menus (slug `shelter-coffee-drive-al-worod`). Tool-summary: 4.4, ~1000 reviews (unverified).
- mat3am "SHELTER COFFEE DRIVE - Al-Worood District - Irbid - coffee shop" + "branches".
- Accessible Jordan "Shelter Coffee Drive".
- foodyas / findglocal "Shelter Coffee Drive, Awad rshedat st …".
- alekirjo.com "Shelter Coffee - Irbid Branch" (a designer's project page).
- Foursquare: only the **Riyadh** "شلتر كوفي - Shelter Coffee" surfaced. No Irbid Foursquare listing was seen.
- Instagram: the profile URL itself did not surface as a result. The handle @sheltercoffeedrive was seen via Threads/linktr.ee.

### 7.2 Brand naming inconsistencies (observed)
- Arabic: "شلتر كافيه" · "شلتر كافية" (both on the same old-site pages) · "شلتر كوفي" (linktr.ee) · "شلتر كوفي درايف".
- English: "Shelter Coffee Drive" · "SHELTER COFFEE DRIVE" · "SHELTER COFFEE HOUSE" (Snapchat only) · "Shelter Café/Cafe" · "Shelterjo" · handles @sheltercoffeedrive, @ShelterDrive, @shelter_coffee.
- Third parties file **both branches under "DRIVE"**. The HOUSE name barely exists online.
- Collisions: Riyadh "شلتر كافية" (cafesriyadh.com, ranked first for "شلتر كافيه"); Riyadh "Shelter Coffee" (Foursquare); FB "The Shelter Coffee" (Buenos Aires etc.); FB "Shelter Café" page (owner/location unknown).

### 7.3 Address / location inconsistencies (observed)
- **Main branch street:** "شارع عوض رشيدات" (drive-thru page, home, third parties: "Awad Rshedat st", "Sharie Eiwad Rashidat, Irbid 21110"). But the location-page title says "**شارع الجامعة** - قصر النخيل", and a tool-summary quotes "شارع الجامعة بجانب قصر النخيل".
- **District:** "Al-Worood / الورود" (mat3am, Talabat) vs Qasr Al-Nakheel halls stated (tool-summary) as being in "النزهة" / "behind New Amman complex".
- **Landmark wording:** "بجانب قاعات قصر النخيل وأرابيلا" / "بجانب صالة قصر النخيل وأرابيلا" / "beside Qaser Al-Nakheel Hall and Arabella".
- **City Centre floor:** "الطابق الأول بجانب البنك الإسلامي الأردني" (city-centre page, location page; fetched) vs "**2nd floor / second floor**" (home and about pages as summarised by the fetch tool; also one search-tool summary). Re-check against the HTML.

### 7.4 Hours inconsistencies (observed)
**Main / DRIVE**
- "يومياً 7:00 ص – 2:00 ل، الجمعة 8:00 ص – 2:00 ل" (drive-thru page). "ل" = ليلاً, and the search tool misread it as 2 PM.
- Home: Sat–Thu 7:00–2:00 AM, Fri 8:00–2:00 AM.
- About page: 8 AM–1 AM (Ramadan to 3 AM).
- TripAdvisor (tool-summary): Mon–Sat 7 AM–2 AM, Sun 8 AM–2 AM.
- foodyas/findglocal (tool-summary): 9 AM–11 PM daily.

**City Centre / HOUSE**
- Branch page: Sat–Wed & Fri 9 AM–10 PM, Thu 9 AM–11 PM.
- Location page: Fri 2 PM–11 PM.
- Home: Sat–Thu 9 AM–11 PM, Fri 1 PM–11 PM.
- About: 10 AM–11 PM (Ramadan to 1 AM).
- A third-party snippet: Mon–Wed & Sat–Sun 09:00–22:00.

### 7.5 Phones / emails (observed)
- 0799009436 / +962 7 9900 9436: city-centre page, location page, many third parties.
- **0799338445**: seen once, in a search-tool summary for "shelterjo". Source page unknown. LOW confidence.
- info@Shelterjo.com / info@shelterjo.com.

### 7.6 Claims (observed; all need Owner proof before reuse)
- "أول كافية مع خدمة سيارات" / "first drive-thru (specialty coffee) in northern Jordan"
- "أفضل كافيهات أربد" (H1) / "أفضل قهوة مختصة" (tool-summary)
- "الاسرع بالخدمة"
- "Leader Of Coffee in North" (IG bio, tool-summary)
- "Quality You Can Taste STAY SHELTERED" (tool-summary)
- Customers: "أكثر من 8000 زبون" (home) vs "اكثر من 28 الف عميل في قائمة الولاء" (about)
- Experience/founding: "7+ years" (home); founded **2019** after a 2018 market study (about); "started in **2022**" (tool-summary of an SH page)
- "40+ مشروب مختص"; "prices from 1.5 JD" (tool-summary); "مشروبات الربيع 2026"
- About page: co-working spaces, free Wi-Fi, cultural events. Free parking (tool-summary).
- Third-party ratings (TripAdvisor 5.0; Talabat 4.4): tool-summaries, unverified.

**Implication for the build (observation, not a decision):** the old site holds most of the Arabic search visibility seen in this index. Any later URL change or redirect map is a production-gated, Owner-approved step (CLAUDE.md).

---

## 8. Evidence URLs (full list)

### SHELTER: own site (old)
- E1 https://www.shelterjo.com/
- E2 https://www.shelterjo.com/blog/
- E3 https://www.shelterjo.com/best-cafes-irbid-2026/
- E4 https://www.shelterjo.com/coffee-house-vs-cafe/
- E5 https://www.shelterjo.com/city-centre-branch/
- E6 https://www.shelterjo.com/drive-thru/
- E7 https://www.shelterjo.com/drive-thru-shelter-irbid-how-it-works/
- E8 https://www.shelterjo.com/family/
- E9 https://www.shelterjo.com/%D8%A7%D9%84%D8%AA%D9%88%D8%B8%D9%8A%D9%81-%D9%81%D9%8A-%D8%B4%D9%84%D8%AA%D8%B1-%D9%83%D8%A7%D9%81%D9%8A%D8%A9/
- E10 https://www.shelterjo.com/%D9%85%D9%88%D9%82%D8%B9-%D8%B4%D9%84%D8%AA%D8%B1-%D9%83%D8%A7%D9%81%D9%8A%D8%A9-%D9%85%D8%AD%D8%A7%D9%81%D8%B8%D8%A9-%D8%A7%D8%B1%D8%A8%D8%AF/
- E11 https://www.shelterjo.com/%D9%85%D9%86-%D9%87%D9%88-%D8%B4%D9%84%D8%AA%D8%B1-%D9%83%D8%A7%D9%81%D9%8A%D8%A9-%D9%81%D9%8A-%D8%A7%D8%B1%D8%A8%D8%AF/
- E12 https://www.shelterjo.com/%D8%A7%D9%84%D9%82%D8%A7%D8%A6%D9%85%D8%A9-%D8%B4%D9%84%D8%AA%D8%B1-%D9%83%D8%A7%D9%81%D9%8A%D8%A9-%D9%85%D8%AD%D8%A7%D9%81%D8%B8%D8%A9-%D8%A7%D8%B1%D8%A8%D8%AF/

### SHELTER: profiles and third-party listings
- E13 https://linktr.ee/sheltercoffeedrive
- E14 https://www.facebook.com/sheltercoffeedrive/
- E15 https://www.threads.com/@sheltercoffeedrive
- E16 https://x.com/ShelterDrive
- E17 https://www.snapchat.com/@shelter_coffee
- E18 https://www.snapchat.com/place/shelter-coffe-drive/74cd05ca-9207-11e9-83fa-93967fb5e6e2
- E19 https://www.snapchat.com/place/shelter-coffee-house/3875adf0-dca7-11ec-b133-1bba295e4827
- E20 https://www.tripadvisor.com/Restaurant_Review-g656904-d23032228-Reviews-Shelter_Coffee_Drive-Irbid_Irbid_Governorate.html
- E21 https://www.tripadvisor.co.uk/Restaurant_Review-g656904-d23032228-Reviews-Shelter_Coffee_Drive-Irbid_Irbid_Governorate.html
- E22 https://www.tripadvisor.com/LocationPhotoDirectLink-g656904-d23032228-i478768209-Shelter_Coffee_Drive-Irbid_Irbid_Governorate.html
- E23 https://www.talabat.com/jordan/shelter-coffee-drive
- E24 https://www.talabat.com/jordan/restaurant/637492/shelter-coffee-drive-al-worod?aid=4875 (also aid=6664, 6666, 6660)
- E25 https://www.accessiblejordan.com/en/places/view?slug=shelter-coffee-drive
- E26 http://www.alekirjo.com/project/67bc0068610ef678e922dbaa/Shelter%20Coffee%20-%20Irbid%20Branch
- E27 https://www.mat3am.net/restaurant/23350/SHELTER-COFFEE-DRIVE-Al-Worood-District?lang=en
- E28 https://www.mat3am.net/restaurant/23350/branches/SHELTER-COFFEE-DRIVE-branches-in-Irbid?lang=en
- E29 https://www.foodyas.com/JO/Irbid/256499115299768/Shelter-Coffee-Drive
- E30 https://www.findglocal.com/JO/Irbid/256499115299768/Shelter-Coffee-Drive

### Brand collisions
- E31 https://foursquare.com/v/%D8%B4%D9%84%D8%AA%D8%B1-%D9%83%D9%88%D9%81%D9%8A---shelter-coffee/5d1229fe3e8ac40023298f3d (Riyadh, closed)
- E32 https://cafesriyadh.com/2021/%D8%B4%D9%84%D8%AA%D8%B1-%D9%83%D8%A7%D9%81%D9%8A%D8%A9/ (Riyadh)
- E33 https://www.facebook.com/TheShelterCoffee/ ; https://www.facebook.com/thesheltercoffeephuket/ ; https://www.facebook.com/thesheltercafe/ ; https://www.facebook.com/sheltercafeaberdeen/ ; https://www.facebook.com/shelterbangkok/ ; https://www.facebook.com/sheltercoffeeandtea/
- E34 https://www.facebook.com/p/Shelter-Caf%C3%A9-61569982503664/ (owner/location unknown)
- E35 https://www.yelp.com/biz/the-shelter-coffee-buenos-aires ; https://sheltercoffee.com/ ; https://wanderlog.com/place/details/370728/the-shelter-coffee
- E36 https://www.tripadvisor.com/Restaurant_Review-g293925-d6961746-Reviews-Shelter_Coffee_Tea-Ho_Chi_Minh_City.html

### Directories, lists, competitors (Irbid)
- E40 https://irbid-now.com/categories/cafes-irbid
- E41 https://irbid-now.com/en/irbid/restaurants/cafes
- E42 https://www.portal.hatanjo.com/%D9%87%D8%AA%D8%A7%D9%86-%D9%83%D8%A7%D9%81%D9%8A%D8%A9-%D8%A3%D9%81%D8%B6%D9%84-%D9%83%D8%A7%D9%81%D9%8A%D9%87%D8%A7%D8%AA-%D9%81%D9%8A-%D8%A5%D8%B1%D8%A8%D8%AF-%D9%84%D8%B9%D8%A7%D9%85-2025/
- E43 https://www.facebook.com/hatan.cafejo/
- E44 https://apaqcafe.com/
- E45 https://apaqcafe.com/menu/
- E46 https://www.instagram.com/apaq_cafee/
- E47 https://www.facebook.com/makkidelights/
- E48 https://www.timeoutamman.com/%D9%83%D8%A7%D9%81%D9%8A%D9%87%D8%A7%D8%AA-%D8%B4%D8%A7%D8%B1%D8%B9-%D8%A7%D9%84%D8%AC%D8%A7%D9%85%D8%B9%D8%A9-%D8%A7%D8%B1%D8%A8%D8%AF/
- E49 https://trend.timeoutamman.com/%D8%A5%D8%B1%D8%A8%D8%AF-%D8%B3%D9%8A%D8%AA%D9%8A-%D8%B3%D9%86%D8%AA%D8%B1/
- E50 https://www.tripadvisor.com/Restaurants-g2625858-c8-Irbid_Governorate.html
- E51 https://www.tripadvisor.com/Restaurants-g656904-c8-Irbid_Irbid_Governorate.html
- E52 https://www.tripadvisor.com/Restaurants-g656904-zfg9900-Irbid_Irbid_Governorate.html
- E53 https://www.tripadvisor.com/Restaurants-g2625858-zfg9900-Irbid_Governorate.html
- E54 https://www.tripadvisor.com/Restaurants-g2625858-zfg9909-Irbid_Governorate.html
- E55 https://www.tiktok.com/discover/irbid-cafe
- E56 https://www.tiktok.com/discover/%D9%83%D8%A7%D9%81%D9%8A%D9%87-%D8%A7%D8%B1%D8%A8%D8%AF-%D8%A7%D9%84%D8%AC%D8%AF%D9%8A%D8%AF-%D9%83%D8%A7%D8%B1%D9%81%D9%88%D8%B1
- E57 https://www.addustour.com/articles/688852-%D8%A7%D8%BA%D9%84%D8%A7%D9%82-%D8%B9%D8%B4%D8%B1%D8%A9-%D9%85%D8%AD%D9%84%D8%A7%D8%AA-%D9%83%D9%88%D9%81%D9%8A-%D8%B4%D9%88%D8%A8-%D9%85%D8%AE%D8%A7%D9%84%D9%81%D8%A9-%D9%81%D9%8A-%D8%A7%D8%B1%D8%A8%D8%AF
- E58 https://www.addustour.com/articles/698305-%D9%85%D8%AD%D8%A7%D9%81%D8%B8-%D8%A7%D8%B1%D8%A8%D8%AF-%D9%8A%D8%B5%D8%AF%D8%B1-%D8%AA%D8%B9%D9%84%D9%8A%D9%85%D8%A7%D8%AA-%D9%88%D8%B4%D8%B1%D9%88%D8%B7%D8%A7-%D8%AC%D8%AF%D9%8A%D8%AF%D8%A9-%D9%84%D9%85%D8%AD%D9%84%D8%A7%D8%AA-%C2%AB%D8%A7%D9%84%D9%83%D9%88%D9%81%D9%8A-%D8%B4%D9%88%D8%A8%C2%BB
- E59 https://alrai.com/article/335358/%D9%85%D8%AD%D9%84%D9%8A%D8%A7%D8%AA/%D8%B5%D8%AD%D8%A7%D9%81%D8%A9/%D8%B4%D9%83%D9%88%D9%89-%D8%A8%D8%AD%D9%82-%D9%85%D9%88%D8%B8%D9%81-%D9%81%D9%8A-%D8%A8%D9%84%D8%AF%D9%8A%D8%A9-%D8%A7%D8%B1%D8%A8%D8%AF-%D8%B9%D9%84%D9%89-%D8%AE%D9%84%D9%81%D9%8A%D8%A9-%D8%A7%D8%BA%D9%84%D8%A7%D9%82%D9%87-%D9%85%D8%AD%D8%A7%D9%84-%D9%83%D9%88%D9%81%D9%8A-%D8%B4%D9%88%D8%A8
- E60 https://www.sarayanews.com/article/654754
- E61 https://www.rmix.ps/news/143519.html
- E62 https://www.mat3am.net/restaurant/5915
- E63 https://www.mat3am.net/restaurant/5847
- E64 https://www.mat3am.net/restaurant/12111/The-Hive-WASFI-TAL-STREET
- E65 https://www.mat3am.net/restaurant/5834/%D9%84%D9%8A%D9%85%D9%88%D9%86-%D8%B4%D9%8A%D8%B4%D8%A9-%D8%A8%D9%8A%D8%B3%D8%AA%D8%B1%D9%88-%D8%A7%D8%B1%D8%A8%D8%AF-%D8%B3%D9%8A%D8%AA%D9%8A-%D8%B3%D9%86%D8%AA%D8%B1
- E66 https://middle-east-online.com/%D8%A7%D8%B1%D8%A8%D8%AF-105-%D9%85%D9%82%D8%A7%D9%87%D9%8A-%D8%A7%D9%86%D8%AA%D8%B1%D9%86%D8%AA-%D9%81%D9%8A-%D8%B4%D8%A7%D8%B1%D8%B9-%D8%B7%D9%88%D9%84%D9%87-%D9%83%D9%8A%D9%84%D9%88%D9%85%D8%AA%D8%B1-%D9%88%D8%A7%D8%AD%D8%AF
- E67 https://guide.opensooq.com/%D8%A7%D9%84%D8%A3%D8%B1%D8%AF%D9%86/%D8%A5%D8%B1%D8%A8%D8%AF/
- E68 https://www.facebook.com/chillcafe.jo/ ; https://www.facebook.com/coffee.irbed/ ; https://www.facebook.com/p/Pure-Cafe-61562127543820/ ; https://www.facebook.com/ThinkCoffeeHouse/ ; https://www.facebook.com/sydneycoffeehouse/ ; https://www.facebook.com/p/The-Line-Coffee-House-100085071737576/ ; https://www.facebook.com/p/Dorzo-Coffee-House-61558118574306/ ; https://www.facebook.com/refresh.coffee.irbid/ ; https://www.facebook.com/steel.jor/ ; https://www.facebook.com/leomakcafes/
- E69 https://thinkcoffeejo.com/
- E70 https://locations.starbucks.com.jo/directory/irbid/wasfi-al-tal-street-drive-thru
- E71 https://www.facebook.com/StoryDriveThru/
- E72 https://www.instagram.com/storydrivethru/
- E73 https://drivu.co/shops/jordan/irbid
- E74 https://www.instagram.com/drive.ve/
- E75 https://locations.starbucks.com.jo/irbid/irbid-city-centre/brewed-coffee
- E76 https://locations.starbucks.com.jo/directory/irbid/irbid-city-centre
- E77 https://locations.starbucks.com.jo/irbid/irbid-city-centre/frappuccino
- E78 https://www.tripadvisor.com/Restaurant_Review-g656904-d33065595-Reviews-2K_Coffee_House-Irbid_Irbid_Governorate.html
- E79 https://www.tripadvisor.com/Restaurant_Review-g656904-d10475913-Reviews-Starbucks_Coffee-Irbid_Irbid_Governorate.html
- E80 https://en.ibnbattutatravel.com/asia/discover-the-best-of-irbid-jordan-7-top-rated-cafes-to-visit/
- E81 https://jo.arabplaces.com/irbid/cafe ; https://jo.arabplaces.com/irbid/cafe/5
- E82 https://www.regencyholidays.com/blog/restaurants-in-irbid/
- E83 https://traveltriangle.com/blog/restaurants-in-irbid/
- E84 https://www.jordanyp.com/category/Coffee_shops/city:Irbid
- E85 https://www.facebook.com/Haelcafejo/
- E86 https://www.facebook.com/Icccafejo/
- E87 https://www.instagram.com/secret_cafejo/
- E88 https://www.secretcafejo.com/en/contact-4
- E89 https://foursquare.com/v/lemon--%D9%84%D9%8A%D9%85%D9%88%D9%86/538dc8a2498e0a886b0e383d
- E90 https://locations.alshaya.com/starbucks/jo/irbid/irbid-city-centre
- E91 https://locations.alshaya.com/ar/starbucks/jo/%D8%A5%D8%B1%D8%A8%D8%AF/%D8%B3%D9%8A%D8%AA%D9%8A-%D8%B3%D9%86%D8%AA%D8%B1-%D8%A5%D8%B1%D8%A8%D8%AF
- E92 https://m-icc.com/icc-en/about.php
- E93 https://en.wikipedia.org/wiki/Irbid_City_Centre
- E94 https://wikimapia.org/26552838/Irbid-City-Center-Mall ; https://www.urtrips.com/en/irbid-city-center-mall/
- E95 https://www.tripadvisor.com/Restaurant_Review-g656904-d25456813-Reviews-Secret_Cafe-Irbid_Irbid_Governorate.html
- E96 https://www.instagram.com/momentscafe.irbid/
- E97 https://www.facebook.com/MomentsCafePage/
- E98 https://www.instagram.com/_secret_garden_cafe/
- E99 https://maroufcoffee.com/our-branches/
- E100 https://www.talabat.com/ar/uae/v60-speciatly-coffee
- E101 https://www.talabat.com/ar/jordan/v60-specialty-coffee-roasters
- E102 https://www.facebook.com/FURY.COFFEE.MORE/
- E103 https://www.talabat.com/jordan/coffee-town ; https://www.talabat.com/jordan/hybrid-coffee ; https://www.talabat.com/jordan/restaurant/46463/brazilian-coffee-house-irbid-main?aid=4875 ; https://www.talabat.com/jordan/bizantino-coffee-roaster ; https://www.talabat.com/jordan/cowboy-irbid
- E104 https://foursquare.com/v/meta-cafe/5a2453d9e679bc15e5a59d8e ; https://www.tripadvisor.com/Restaurant_Review-g656904-d19298593-Reviews-Meta_Coffee-Irbid_Irbid_Governorate.html ; https://www.facebook.com/metacoffeejo/ ; https://metacoffeejo.com/en/collections/collection-183878 ; https://www.youtube.com/watch?v=uD4p5EkwAAo
- E105 https://foursquare.com/v/caffeine-86/5c05435e364d97002caffd08 ; https://foursquare.com/v/container-cafe/5756415f498eaf7656734c31 ; https://foursquare.com/v/on-the-run/4f36d441e4b017ad7a66f8a8
- E106 https://ozcoffee.house/ ; https://apps.apple.com/nz/app/oz-speciality-coffee/id6748605255 ; https://www.facebook.com/ozspecialtycoffee/
- E107 https://www.facebook.com/BlankCafe0/ ; https://www.facebook.com/CuchinaCafe/ ; https://www.facebook.com/rb.nowar.coffee ; https://www.facebook.com/1lioncafe/ ; https://www.facebook.com/allordcafe/ ; https://www.facebook.com/CAVJOR/
- E108 https://www.instagram.com/sultan.feras.sweets/ ; https://www.facebook.com/kunafashira/ ; https://www.facebook.com/p/%D8%AD%D9%84%D9%88%D9%8A%D8%A7%D8%AA-%D8%B4%D8%B1%D9%82%D9%8A-Sharqi-Sweet-61559569656462/ ; https://safarway.com/en/property/al-aqsa-sweets-2 ; https://www.mat3am.net/restaurant/11603/branches/-branches-in-?lang=en
- E109 https://www.qasioungroup.com/ ; https://www.facebook.com/alrahmaZK/

### Informational / product (regional Arabic content)
- E110 https://soutroastery.com/blog/%D9%85%D8%A7%20%D9%87%D9%8A%20%D8%A7%D9%84%D9%82%D9%87%D9%88%D8%A9%20%D8%A7%D9%84%D9%85%D8%AE%D8%AA%D8%B5%D8%A9/a-656572742 ; https://airroastery.com/%D9%85%D8%B9%D9%84%D9%88%D9%85%D8%A7%D8%AA-%D8%B9%D9%86-%D8%A7%D9%84%D9%82%D9%87%D9%88%D8%A9-%D8%A7%D9%84%D9%85%D8%AE%D8%AA%D8%B5%D8%A9/ ; https://bourbonksa.com/blog/%D9%85%D8%A7-%D9%87%D9%8A-%D8%A7%D9%84%D9%82%D9%87%D9%88%D8%A9-%D8%A7%D9%84%D9%85%D8%AE%D8%AA%D8%B5%D8%A9/a-1310659657 ; https://exoticsspecialtycoffee.com/blog/what-is-specialty-coffee/ ; https://mazaqat.com/%D8%A7%D9%84%D9%82%D9%87%D9%88%D8%A9-%D8%A7%D9%84%D9%85%D8%AE%D8%AA%D8%B5%D8%A9/
- E111 https://qavashop.com/ar/academy/post/how-to-brew-the-perfect-v60 ; https://ash.coffee/blog/%D8%AA%D8%AD%D8%B6%D9%8A%D8%B1-%D9%82%D9%87%D9%88%D8%A9-v60-%D9%81%D9%8A-%D8%A7%D9%84%D9%85%D9%86%D8%B2%D9%84-%D9%83%D8%A7%D9%84%D9%85%D8%AD%D8%AA%D8%B1%D9%81%D9%8A%D9%86/a-1838597994 ; https://www.lavazzamena.com/ar/coffee-hacks/v60 ; https://roastinghouse.sa/hario-v60-ice-coffee-maker.html ; https://wasq-store.com/%D9%82%D9%87%D9%88%D8%A9-v60-%D8%A8%D8%A7%D8%B1%D8%AF%D8%A9/ ; https://merakiartisan.com/ar/blogs/meraki/v60
- E112 https://www.nescafe.com/mena/ar-ae/coffee-culture/coffee-knowledge/latte ; https://marieclairearabia.com/%D9%85%D9%86%D9%88%D8%B9%D8%A7%D8%AA/%D9%81%D9%86%D9%88%D9%86-%D8%A7%D9%84%D9%85%D8%A7%D8%A6%D8%AF%D8%A9/%D8%A7%D9%84%D9%81%D8%B1%D9%82-%D8%A8%D9%8A%D9%86-%D8%A7%D9%84%D9%84%D8%A7%D8%AA%D9%8A%D9%87-%D9%88%D8%A7%D9%84%D9%83%D8%A7%D8%A8%D8%AA%D8%B4%D9%8A%D9%86%D9%88/ ; https://qavashop.com/ar/academy/post/demystifying-the-difference-between-cappuccino-latte-and-macchiato ; https://bashasaray.com/the-difference-between-cappuccino-and-latte/ ; https://coffeetast.net/flat-white-vs-latte-vs-cappuccino/
- E113 https://cookpad.com/jo/%D9%88%D8%B5%D9%81%D8%A7%D8%AA/11416380 ; https://cookpad.com/eg/%D9%88%D8%B5%D9%81%D8%A7%D8%AA/25254452 ; https://sprinkcoffee.com/en/Bdzexz ; https://elabdfoods.com/spanish-latte ; https://cafeah.com/%D8%B7%D8%B1%D9%8A%D9%82%D8%A9-%D8%B3%D8%A8%D8%A7%D9%86%D8%B4-%D9%84%D8%A7%D8%AA%D9%8A%D9%87/ ; https://coffeetast.net/spanish-latte-vs-latte/ ; https://kitchen.sayidaty.net/node/16722/%D8%B3%D8%A8%D8%A7%D9%86%D8%B4-%D9%84%D8%A7%D8%AA%D9%8A%D8%A9-%D8%A8%D8%A7%D8%B1%D8%AF-%D8%A8%D8%A7%D9%84%D8%A8%D9%8A%D8%AA/%D9%85%D8%B4%D8%B1%D9%88%D8%A8%D8%A7%D8%AA-%D9%88%D8%B9%D8%B5%D8%A7%D8%A6%D8%B1/%D9%88%D8%B5%D9%81%D8%A7%D8%AA ; https://www.arabecoffee.com/2024/10/how-to-make-cold-Spanish-latte.html
- E114 https://gate.ahram.org.eg/News/3689070.aspx ; https://www.elbalad.news/6967016 ; https://mufhras.com/%D8%B7%D8%B1%D9%8A%D9%82%D8%A9-%D8%A7%D9%8A%D8%B3-%D9%83%D9%88%D9%81%D9%8A-%D8%A8%D8%A7%D8%B1%D8%AF/ ; https://www.starbucksathome.com/mena/ar-ae/recipes/iced ; https://www.aljazeera.net/lifestyle/2026/7/2/%D8%A8%D8%B7%D8%B1%D9%8A%D9%82%D8%A9-%D8%A7%D8%AD%D8%AA%D8%B1%D8%A7%D9%81%D9%8A%D8%A9-%D9%87%D9%83%D8%B0%D8%A7-%D8%AA%D8%AD%D8%B6%D8%B1-%D8%A7%D9%84%D9%82%D9%87%D9%88%D8%A9
- E115 https://sprudge.com/amman-jordan-the-sprudge-coffee-guide-142523.html ; https://www.touristjordan.com/best-coffee-in-amman/ ; https://wanderlog.com/list/geoCategory/1862949/best-coffee-roasters-in-amman ; https://intelligence.coffee/2022/09/how-jordans-coffee-culture-went-premium/ ; https://www.neboroastery.com/ ; https://alameedcoffee.com/ar ; https://shammoutcoffee.com/en/product/arabic-instant-coffee-jordanian-1-liter/

### Careers / events / landmarks / misc
- E116 https://jo.opensooq.com/ar/%D8%A5%D8%B1%D8%A8%D8%AF/%D9%88%D8%B8%D8%A7%D8%A6%D9%81/%D9%88%D8%B8%D8%A7%D8%A6%D9%81-%D8%B4%D8%A7%D8%BA%D8%B1%D8%A9/%D8%A8%D8%A7%D8%B1%D9%8A%D8%B3%D8%AA%D8%A7 ; https://jo.opensooq.com/ar/%D8%A5%D8%B1%D8%A8%D8%AF/%D9%88%D8%B8%D8%A7%D8%A6%D9%81/%D9%88%D8%B8%D8%A7%D8%A6%D9%81-%D8%B4%D8%A7%D8%BA%D8%B1%D8%A9 ; https://jo.opensooq.com/ar/search/278944779 ; https://wzzff.com/jobs/jordan/Irbid ; https://jordanrec.com/archives/tag/%D9%88%D8%B8%D8%A7%D8%A6%D9%81-%D8%A8%D8%A7%D8%B1%D9%8A%D8%B3%D8%AA%D8%A7
- E117 https://www.tripadvisor.com/Attractions-g2625858-Activities-Irbid_Governorate.html ; https://www.tiktok.com/discover/%D8%A7%D9%85%D8%A7%D9%83%D9%86-%D8%AA%D8%B1%D9%81%D9%8A%D9%87%D9%8A%D9%87-%D9%81%D9%8A-%D8%A7%D8%B1%D8%A8%D8%AF
- E118 https://furas.momah.gov.sa/en/node/17488 ; https://hungerstation.com/sa-ar/restaurant/bbq/arar/arar/106450 ; https://x.com/DHDH9933/status/1752740593858461712?lang=ar ; https://sharikatmubasher.com/media-hub/news/21466068/ (Saudi "درايف كوفي")
- E119 https://www.snapchat.com/place/-/6fd4c0dc-1e37-11ef-b05b-0be27444e9fa ; https://www.accessiblejordan.com/ar/places/view?slug=qaser-al-nakheel-halls ; https://x.com/qasralnakheel ; https://www.directoryjordan.com/%D8%B5%D8%A7%D9%84%D8%A9-%D9%82%D8%B5%D8%B1-%D8%A7%D9%84%D9%86%D8%AE%D9%8A%D9%84-%D9%84%D9%84%D8%A7%D9%81%D8%B1%D8%A7%D8%AD-%D9%88%D8%A7%D9%84%D8%A7%D8%AD%D8%AA%D9%81%D8%A7%D9%84%D8%A7%D8%AA
- E120 https://arabellamall.jo/en/home/ ; https://www.tripadvisor.com/Attraction_Review-g656904-d4471274-Reviews-Arabela_Mall-Irbid_Irbid_Governorate.html
- E121 https://www.facebook.com/p/Grano-cookies-%D8%AC%D8%B1%D8%A7%D9%86%D9%88-%D9%83%D9%88%D9%83%D9%8A%D8%B2-100095211108945/
- E122 https://key-jo.com/menu ; https://www.facebook.com/makancafejo/
- E123 https://www.mota.gov.jo/EBV4.0/Root_Storage/AR/EB_Info_Page/%D8%A7%D9%84%D9%85%D8%B7%D8%A7%D8%B9%D9%85.pdf ; https://ar.tripadvisor.com/RestaurantsNear-g656904-d4965003-7_Days_Hotel-Irbid_Irbid_Governorate.html
- E124 https://langcenter.yu.edu.jo/index.php/about-yarmouk-university-16 ; https://www.tripadvisor.com/RestaurantsNear-g656904-d7890175-Yarmouk_University-Irbid_Irbid_Governorate.html
- E125 https://www.talabat.com/ar/jordan/shams-coffee-house ; https://www.talabat.com/ar/jordan/miel-coffee-house ; https://www.talabat.com/ar/jordan/qlight-coffee-house
- E126 https://www.facebook.com/irbid1/
- E127 https://perfecta-hier.com/ar/listing/%D9%85%D9%82%D8%A7%D9%88%D9%84-%D8%AA%D9%86%D9%81%D9%8A%D8%B0-%D9%85%D8%B7%D8%A7%D8%B9%D9%85-%D9%83%D8%A7%D9%81%D9%8A%D9%87%D8%A7%D8%AA-%D8%AF%D9%8A%D9%83%D9%88%D8%B1%D8%A7%D8%AAt-rle2029-7-l-dveo4370jxa
- E128 https://khareta.com/properties/%D8%A8%D9%8A%D8%B9-%D9%85%D8%AD%D9%84-%D8%B4%D8%A7%D9%85%D9%84-%D9%81%D9%8A-%D8%A7%D8%B1%D8%A8%D8%AF-%D8%B3%D8%AA%D9%8A-%D8%B3%D9%86%D8%AA%D8%B1-1738825965

### Fetches attempted but blocked by the egress proxy (not bypassed)
- talabat.com/jordan/shelter-coffee-drive
- tripadvisor.com (SHELTER review page)
- mat3am.net (SHELTER page)
- irbid-now.com/categories/cafes-irbid
- accessiblejordan.com (SHELTER page)
- timeoutamman.com (University-Street list)
- portal.hatanjo.com (2025 list)
