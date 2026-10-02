> **Status:** RESEARCH EVIDENCE (M57 §7, §23, §39), collected 2026-10-02 with free tools only. Competitor sites were mostly blocked for fetching, so titles come from search results and structured data is marked unknown. Ratings and counts are *as seen*, with sources, and are not claims. Competitor copy is never reused.
> **Used by:** `docs/seo/LOCAL-SEO-MAP.md` · **Companion:** `LOCAL-KEYWORD-RESEARCH.md`

# SHELTER COFFEE — Local competitor research (Irbid) · as seen 2026-10-02

Status: RESEARCH INPUT — not a decision. Nothing here is approved business data. Every figure below is "as observed" with its source; nothing was invented. Recommendations are proposals for the Owner (M38: business facts stay `PENDING OWNER INPUT`).

---

## 1. Method and limitations

**Method**
- Tools: WebSearch (about 50 queries, Arabic + English) and WebFetch only. No MCP tools, Semrush, paid services, Google Maps access or scraping of review text. Nobody was contacted.
- Arabic queries: `كافيه اربد قهوة مختصة`, `كوفي اربد درايف ثرو`, `افضل كافيهات اربد`, `كافيه داخل اربد ستي سنتر`, `بن معروف اربد`, `عصير تاين اربد`, `قهوة مختصة اربد V60`, `شلتر كوفي طلبات اربد`, `تايم آوت … كافيهات شارع الجامعة اربد`, and others.
- English queries: `best cafes in Irbid Jordan`, `specialty coffee Irbid`, `coffee shop Irbid`, `coffee shop Irbid City Center mall cafe`, `"Irbid" coffee drive thru Jordan`, `foursquare best coffee Irbid`, `wanderlog best coffee shops cafes Irbid`, plus each competitor name and site-restricted queries (`allowed_domains`) to see which pages each brand site has indexed.
- The Owner's names were checked one by one: BLK, AJDA, KEY, DISTRICT, عصير تاين, BKLEN, بن معروف.

**Limitations (read before using any number)**
1. **WebFetch worked only on shelterjo.com.** The network egress proxy blocked every other domain tried: TripAdvisor, irbid-now.com, wanderlog.com, qahwablk.com, apaqcafe.com, ozcoffee.house, maroufcoffee.com, key-jo.com, locations.starbucks.com.jo, linktr.ee, accessiblejordan.com, portal.hatanjo.com and en.ibnbattutatravel.com.
   - So for competitor sites the H1, meta description, JSON-LD and speed were **not observed**.
   - Competitor page titles below are the **search-result title strings**. These are usually the HTML `<title>`, but search engines can rewrite them.
2. **Most details come from search-engine summary snippets.** These are machine summaries of the indexed pages. They can merge sources.
   - Example: one Arabic summary gave the same "4.9 / 1,233 reviews" to two different cafés.
   - Example: one aggregator showed "4.50 from 91,000+ reviews" for a single Irbid café, which is not plausible.
   - Values like these are marked **unreliable — not used**.
3. **WebSearch runs from a US index.** It is not Jordan-localized Google. The Google **local pack and Maps** results, where most café discovery happens, were **not observed** (Maps access is blocked by policy and was not attempted).
4. **JSON-LD / schema:** WebFetch turns pages into markdown and does not reliably show `<script type="application/ld+json">`. Schema is therefore **unknown** for every site, including shelterjo.com, where it was "not visible in converted content". This must be checked locally (Lighthouse / view-source) before anyone relies on it.
5. Snippets showed ratings, ranks and follower counts. These are recorded with their source and **"as seen 2026-10-02 (snippet-level, not verified on page)"**. No review text was collected.
6. **"BKLEN"** returned no matching Irbid business. Results were unrelated ("Blank Cafe", "b-k.coffee"). It is not assumed to be either of them.
7. **"عصير تاين"** most likely matches **"عصير تايم - اربد" (@39ertime.irbid)**. That is a near-match, not a confirmed identity.

---

## 2. Competitor matrix

### 2a. Identity and visibility

| # | Business (as seen) | Type | Irbid location context (as seen) | Where it appeared | Own website |
|---|---|---|---|---|---|
| 1 | **Starbucks** (Alshaya franchise) | International chain. Has a drive-thru | **Irbid City Centre** (same mall as SHELTER HOUSE) and **"Wasfi Al Tal Street – Drive thru"**. Other listings: Arabella Mall (TripAdvisor) and "Sameh mall" (mat3am) | "specialty coffee Irbid" (Starbucks location page was the **#1 result**), "KEY coffee Irbid", "cafe Irbid City Center"; TripAdvisor, Foursquare, mat3am | Yes. `locations.starbucks.com.jo` and `locations.alshaya.com` |
| 2 | **Qahwa BLK / قهوة بلاك** | Jordanian specialty chain (since Oct 2019 per its About page) | "Abu Rashid St." (snippet). "Yarmouk University Street" (irbid-now snippet). An older 7hillsjo article said "soon-to-open spot in Irbid" | "BLK coffee Irbid", "افضل كافيهات اربد" (irbid-now snippet) | Yes. `blk.jo`, `qahwablk.com` |
| 3 | **Al Ameed Coffee / قهوة العميد** | Jordanian coffee brand and roaster. Has a **drive-thru** | "Irbid Main Branch" (Friday = "Drive Thru only") and "Sameh Mall Irbid" | `"Irbid" coffee drive thru Jordan`, brand queries | Yes. `alameedcoffee.com` (has a branches page) and `store.alameedcoffee.com` |
| 4 | **Marouf Coffee / بن معروف** | Jordanian chain (founded 2018 per snippet). Also sells franchises | An Irbid branch exists (snippet). Its address was not observed | "بن معروف اربد"; Talabat | Yes. `maroufcoffee.com` |
| 5 | **Trasimeno Coffee / تراسيمينو للقهوه** | Jordanian chain | "Trasimeno Coffee (Irbid)". Location given as "Aydoun" (top-rated.online) or "Mecca Street" (snippet) | "افضل كافيهات اربد" summary; Talabat | Yes. `trasimenocoffee.com` (cited in snippet, not indexed in results) |
| 6 | **Meta Coffee / مقهى ميتا للقهوة المختصة** | Local specialty café plus e-shop. **Car pickup via Drivu** | "Hasan St." and "بجانب البنك العربي Near Arab Bank" (Drivu) | "foursquare best coffee Irbid", "wanderlog…", Drivu | Yes. `metacoffeejo.com` (Shopify-style collections and a privacy policy) |
| 7 | **OZ Speciality Coffee / اوزي للقهوة المختصة** | Local specialty café with beans and tools | "Yarmouk University Area" | "كافيه اربد قهوة مختصة" summary, irbid-now | Yes. `ozcoffee.house` (with `/help/faq`; "web app for ordering" per snippet) |
| 8 | **AJDA Coffee / AJDA Coffee & Bakery / AJDA COFFEE HOUSE** | Local specialty café and bakery ("24h" per snippets) | "Al-Hezam Al-Akhdar St." (TripAdvisor). "Petra St., 300m after Thaqafa Circle" (snippet) | "specialty coffee Irbid", TripAdvisor, Talabat, Snapchat, menu-world | `ajdacoffee.com` was cited in a snippet, but a site search **returned no indexed pages** |
| 9 | **District 7 Irbid / District7** | Local specialty café | "Shaheed Wasfi Al-Tal Street" | "District coffee Irbid" | **None observed.** Facebook, Instagram and Threads only |
| 10 | **KEY - IRBID / Key Cafe** | Restaurant and café (mid-range, full food menu) | "Wasfi Al-Tal Street, near Parks Circle" (Snapchat snippet) | "KEY coffee Irbid" | Yes. `key-jo.com` (`/menu` in AR, `/menu-eng` in EN) |
| 11 | **عصير تايم - اربد** (likely the Owner's "عصير تاين") | Juice | "Shahid Wasfi Al-Tal Street (Abu Rashed Street)" | "عصير تاين اربد Tyne" | **None observed.** Instagram and Threads only |
| 12 | **Hatan Coffee / هتان كافيه** | Café with shisha and board games | "Pr. Hasan St." (Talabat snippet). "في قلب مدينة إربد بالقرب من … الجامعات" (snippet) | "كافيه اربد قهوة مختصة" (Facebook page **ranked #1**), "افضل كافيهات اربد" | Yes. `hatanjo.com` (menu) and **`portal.hatanjo.com` (blog)** |
| 13 | **Sago Coffee House** | Study/work coffee house | "Al-Hashmi Street" | "best cafes in Irbid", TripAdvisor | None observed. Instagram `@sago.coffeehouse` |
| 14 | **Think Coffee House / THINK. Coffee House** | Coffee, pizza and desserts | "Wasfi Al-Tal Street". Also "Abu Rashid Roundabout" (mat3am) | "coffee shop Irbid" (own site in the top 10) | Yes. `thinkcoffeejo.com` (`/menu/`, `/contact.html`) |
| 15 | **Bizantino Coffee Roaster / محمص بن البيزنطي** | Roaster café | "Al Nuzha" (snippet). "حي الملعب" (top-rated.online) | `"Irbid" coffee drive thru` summary, Waze, Accessible Jordan | None observed. Facebook only |
| 16 | **Hail Cafe / مطعم و كافيه هيل** | Café and restaurant **inside Irbid City Centre** ("first floor" per snippet) | Irbid City Centre, Pr. Hasan St. | "cafe Irbid City Center", "كافيه داخل اربد ستي سنتر" | None observed. Facebook and Instagram `@hailcafejo` |
| 17 | **عبق كافيه / Apaq Cafe** | Café (drinks, desserts, shisha) | "دوار المهباش (الدلة) – مقابل بنك الاتحاد" | "كافيه اربد قهوة مختصة", "افضل كافيهات اربد" | Yes. `apaqcafe.com` (`/menu/` titled "قائمة عبق كافية في اربد") |

Mentioned once and not profiled (thin evidence): 2K Coffee House, Dorzo Coffee House, The Line Coffee House, Container Cafe, Caffeine 86, LAVIE Café, The Hive Café, ICC Cafe (Irbid City Centre, 3rd floor), Crepello, Cuzins, Zeus Café, Guevara coffee, Sydney Coffee House, Refresh Coffee, Secret Garden Café, The Scandinavian House.

**Observed pattern (not verified):** KEY, District 7 and عصير تايم all list addresses on Wasfi Al-Tal / Abu Rashed Street. Their snippet contact numbers run in sequence (…3040501, …3040502, …3040503). This *may* mean one operator runs all three. That is **not confirmed**.

### 2b. Website audit (what could be observed)

| Brand | Title (search-result string) | H1 / meta desc. | Menu format | Prices visible | Branch / location pages | Hours shown | Schema | AR / EN | Mobile CTAs |
|---|---|---|---|---|---|---|---|---|---|
| Starbucks | "Discover new coffees in Irbid - Starbucks Coffee Company"; "Brewed Coffee at Starbucks Irbid City Centre, Irbid"; "Frappuccino at Starbucks Irbid City Centre, Irbid"; "Starbucks in Irbid," | not observed (blocked) | HTML category pages **per location** | not observed | **Yes.** One page per branch, including the drive-thru, plus **location × product-category pages**. Yext-style `y_source` parameter in URLs | Yes (snippet: City Centre "Open 24 Hours"; Drive-thru "7:00 AM – 11:30 PM") | unknown | EN observed | Phone shown (snippet) |
| Qahwa BLK | "Locations - Qahwa BLK", "QahwaBLK - University Street 1 - Qahwa BLK", "About Us - Qahwa BLK" | not observed | not observed | not observed | **Yes**, per-location pages (`blk.jo/locations/<slug>/`). Irbid is a location category | not observed | unknown | EN paths (`/en/`) and an Arabic name | not observed |
| Al Ameed | "Al Ameed Coffee - Local Branches", "Al Ameed Coffee - FAQs" | not observed | Store (Shopify) for beans | Store prices: not observed | **Yes**, a branches page with an Irbid entry | Yes. Irbid Main: Sat–Thu 8:00–22:30, Fri 9:00–21:00 "Drive Thru only". Sameh Mall: 8:00–24:00 (snippet) | unknown | `?language=en` | App on Google Play |
| Marouf | "Menu Marouf - Marouf Coffee", "Our branches - Marouf Coffee", "Find Marouf Coffee Near You", "اعثر على بن معروف بالقرب منك" | not observed | **HTML product-category menu pages** (e.g. "Marouf Cafe Archives – Page 8 of 8", "Salads Archives") plus franchise booklet PDFs | not observed | Branch finder and branches page | not observed | unknown | AR (`/ar/`) and EN | not observed |
| Meta Coffee | "Meta Coffee » Coffee Envelopes (NAGIN)", "مقهى ميتا للقهوة المختصة » سياسة الخصوصية" | not observed | E-shop collections (beans, cups). Café menu is on Drivu | not observed | Drivu page "Near Arab Bank" | Drivu: 10:00 AM – 11:00 PM (snippet) | unknown | AR and EN (`/en/`) | Drivu car-pickup ordering |
| OZ Speciality | "OZ Speciality Coffee" (home and `/help/faq`) | not observed | Category list in snippet: Bakery, Cold Drinks, Desserts, Hot Drinks, Coffee Beans Packages, Frappé, Smoothies, Milkshakes, Chips, Salads, **V60 Tools**, Protein Items | not observed | Single location | "open 24 hours" (snippet) | unknown | not observed | Phone and email in snippet; web-app ordering |
| KEY | "KEY - IRBID" (`/menu-eng`), "القائمة - KEY - IRBID" (`/menu`) | not observed | **HTML menu, Arabic and English** | **Yes, JOD prices** (snippet showed several items with JOD prices) | not observed | Snapchat snippet: 14:00–22:00 | unknown | **AR + EN menus** | not observed |
| Hatan | "هتان كافية: أفضل كافيهات في إربد لعام 2025" (portal) | not observed | Menu on `hatanjo.com` (Threads post: "للقائمة: hatanjo.com") | not observed | not observed | Talabat snippet: 8:00 AM – 2:00 AM | unknown | AR. The Threads post was bilingual | not observed |
| Think Coffee | "Think Coffee House — Coffee, Pizza & Desserts in Irbid", "Menu — Think Coffee House", "Think Coffee House - Contact" | not observed | **HTML menu** ("116 drinks and dishes" per snippet) | not observed | Single location; contact page | "Open daily 8am to 2am" (snippet) | unknown | EN titles observed | Phone in snippet |
| Apaq | "عبق كافية", "قائمة عبق كافية في اربد" | not observed | **HTML menu page** (`/menu/`) | Snippet said "detailed menu information and pricing", but prices were **not directly observed** | Single location | not observed | unknown | AR | not observed |
| District 7, Tyme, Sago, Bizantino, Hail, AJDA | — | — | **No own-site menu observed.** Menus appear on Talabat, social media, or third-party auto-menu sites (menu-world.com for AJDA, goto-where.com for Hatan) | Talabat only (not observed) | — | Snippets from Snapchat, TripAdvisor or Talabat | — | — | — |

**SHELTER's current site (shelterjo.com, fetched; it is the old site and an information source only):**
- **Homepage** title: "شلتر كافيه إربد - قهوة مختصة ودرايف ثرو | كافيهات اربد". H1: "شلتر كافيه أفضل كافيهات أربد".
- Arabic only; no language switcher was observed.
- The homepage shows menu items with د.أ prices.
- It links to Talabat, Careem and Linktree, and has Google Maps links for both branches.
- No JSON-LD was visible in the converted content. That is **unverified**, because the conversion hides scripts.
- Blog posts listed: "دليل كافيهات إربد 2026…", "الفرق بين كوفي هاوس وكافيه…", "كيف يعمل الدرايف ثرو…".
- `/city-centre-branch/` has a `tel:` link and Google Maps + Waze links.
- `/drive-thru/` lists menu categories with **no prices** and no phone number.

---

## 3. Per-competitor notes

1. **Starbucks (Irbid City Centre + Wasfi Al-Tal drive-thru)**
   - It competes with **both** SHELTER formats: a café inside the same mall, and a drive-thru.
   - Strength: an enterprise local-SEO structure. Each branch has its own page, and there are pages for each **product category at each branch** ("Brewed Coffee at Starbucks Irbid City Centre"). That page was the top result for "specialty coffee Irbid".
   - Weakness: generic content with no Irbid-specific story. TripAdvisor showed 3.0 from 1 review (snippet, as seen 2026-10-02). Foursquare showed 8.7/10 (snippet).
2. **Qahwa BLK**
   - Strengths: per-location pages and a clear value message on its About page ("great coffee … at a reasonable price", paraphrased).
   - Expanding (Irbid now appears as a location category).
   - Irbid branch detail was not observed.
3. **Al Ameed**
   - Strength: a branches page with separate drive-thru hours ("Drive Thru only" on Friday), an app, and a store.
   - Its "automated drive-thru" is on Airport Road, Amman, not in Irbid.
4. **Marouf**
   - Strength: a full HTML menu built from product-category pages, and a branch finder.
   - Weakness: the Irbid branch address and hours were not surfaced in search.
5. **Trasimeno**
   - Mostly Talabat and social. Its website is cited but did not appear in results.
   - The aggregator rating seen is implausible and was **not used**.
6. **Meta Coffee**
   - Positions itself as "the first cafe to serve specialty coffee in Irbid (Family Project)" and "established 2017" (snippets). These are its own claims.
   - It is the only local independent observed on **Drivu** (order from the car). That is a drive-thru-like offer competing directly with SHELTER DRIVE's main differentiator.
   - Also sells beans (NAGIN envelopes).
   - Foursquare 7.8/10 (snippet, as seen 2026-10-02).
7. **OZ Speciality**
   - The most "specialty-coded" own site observed: beans, V60 tools, an FAQ and web-app ordering.
   - Rating not reliably observed (the summary duplicated the figure of another café).
8. **AJDA**
   - Strong social reach: "over 63,000 Instagram followers" (snippet, as seen 2026-10-02).
   - Two listing names: "AJDA Coffee" and "AJDA Coffee & Bakery".
   - TripAdvisor 3.0, "#26 of 31" (snippet). Talabat "reviewed 500 times … rating of 5" (snippet).
   - Its own site is not indexed, so third-party "menu-world" pages answer menu queries instead.
9. **District 7 Irbid**
   - Social-only. "over 53,000 Instagram followers" (snippet, as seen 2026-10-02).
   - No website, so menu and hours appear only on social media and Talabat (`district-7-al-worod`, which is SHELTER DRIVE's district on Talabat).
10. **KEY - IRBID**
    - The only Irbid player observed with a **bilingual HTML menu with JOD prices** on its own domain.
    - It is a restaurant-café, so not a direct specialty competitor.
11. **عصير تايم - اربد**
    - A juice concept, social-only, close to the Abu Rashed cluster.
    - Its relevance to SHELTER is limited to cold-drink and juice demand.
12. **Hatan**
    - Runs a **content portal** with a "best cafés in Irbid 2025" article. A brand writing "best cafés" content is the same tactic as SHELTER's old `/best-cafes-irbid-2026/`.
    - Its Facebook page ranked #1 for "كافيه اربد قهوة مختصة".
    - Talabat 4.8 from 22 reviews (snippet, as seen 2026-10-02).
13. **Sago Coffee House**
    - Clear positioning (study zone, non-smoking area, meeting rooms, per snippet).
    - TripAdvisor 5.0, shown as both "#13 of 53" and "#14 of 30" (snippets disagree).
    - Instagram only.
14. **Think Coffee House**
    - An independent with a simple brand site in English (home, menu, contact) that ranks in the top 10 for English "coffee shop Irbid".
    - Shows that a basic site can beat directories for English intent.
15. **Bizantino Coffee Roaster**
    - Roaster-café ("watch your coffee being roasted", paraphrased from the snippet).
    - Facebook, Waze and Accessible Jordan only, with no website. Its education and roasting story is left unexploited.
16. **Hail Cafe (Irbid City Centre)**
    - Same mall as SHELTER HOUSE. Food and café.
    - Social only.
17. **Apaq / عبق**
    - Its own HTML menu page ranks for Arabic "best cafés" and "specialty coffee" queries.
    - Shisha positioning.

---

## 4. Directories / aggregators that rank for Irbid café searches

| Directory | Ranked for (observed) | SHELTER present? | What SHELTER's listing shows (recorded exactly, as seen 2026-10-02) | URL |
|---|---|---|---|---|
| **TripAdvisor** (4 list pages: Cafés, Coffee & Tea, city and governorate) | Nearly every English query: "best cafes in Irbid", "coffee shop Irbid", "specialty coffee Irbid", "cafe Irbid City Center", "drive thru" | **Yes**, one listing | Name "SHELTER COFFEE DRIVE"; title "SHELTER COFFEE DRIVE, Irbid - Menu, Prices & Restaurant Reviews". Category "Coffee & Tea", with an "American" cuisine tag (snippet). Description "Shelter is a Drive Thru Coffee Shop serving hot drinks, cold drinks, ice-cream and some cold and hot cakes". Address "Sharie Eiwad Rashidat, Irbid 21110 Jordan". Phone "+962 7 9900 9436". Rating 5.0; rank shown as "#18 of 31" and "#18 of 30" restaurants, and "#3 of 9 Coffee & Tea Spots" (snippets). **No separate HOUSE / City Centre listing observed** | tripadvisor.com/Restaurant_Review-g656904-d23032228-… |
| **Talabat** | "شلتر كوفي طلبات", brand queries; also hosts AJDA, Hatan, District 7, Trasimeno and Marouf | **Yes** | "SHELTER COFFEE DRIVE" / "شلتر كوفي درايف". Restaurant 637492, branch slug `shelter-coffee-drive-al-worod`, delivery areas Al Worod, Al A'awdah, Al Afrah, Al Manara. Best-sellers listed: Iced Spanish Latte, Cookies, Nutella Cookies, Code Red With Flavor. "reviewed 1000 times … rating of 4.4" (search summary). **No City Centre branch observed** | talabat.com/jordan/shelter-coffee-drive |
| **irbid-now.com** ("Cafes in Irbid Governorate — 281/282 businesses") | "best cafes in Irbid", "افضل كافيهات اربد", Arabic specialty queries | Unknown (fetch blocked) | not observed | irbid-now.com/en/irbid/restaurants/cafes |
| **Foursquare** | "foursquare best coffee Irbid" | An old venue "Shelter coffee - Café" (id 53ac1c1f…) exists. **Not verified as SHELTER** | Title only | foursquare.com/v/shelter-coffee/53ac1c1f498e23677d643c54 |
| **mat3am.net** | "mat3am coffee shop Irbid" | **Yes** | "SHELTER COFFEE DRIVE - Al-Worood District - Irbid - coffee shop". Address "in front of Wasfi Al-Tal Street" (snippet). Phone "+962 799009436". Best-sellers incl. Caramel Frappe, Code Red with Flavor, Red Velvet Cheesecake, Iced Spanish Latte | mat3am.net/restaurant/23350/… |
| **Snapchat Places** | "Shelter coffee Irbid", "KEY coffee Irbid", "AJDA…" | **Yes** | Place title "SHELTER COFFEE HOUSE". The same place id 3875adf0-… appears under both `/shelter-coffee-drive/` and `/shelter-coffee-house/` slugs. Profile "SHELTER COFFEE DRIVE (@shelter_coffee)". Hours "Monday - Wednesday, Saturday - Sunday from 09:00 - 22:00" (snippet). Phone "07 9900 9436" | snapchat.com/place/shelter-coffee-house/3875adf0-… |
| **Facebook** | Many queries; café pages often outrank café websites | **Yes** | "Shelter Coffee Drive" (facebook.com/sheltercoffeedrive) | facebook.com/sheltercoffeedrive/ |
| **Threads** | Brand query | **Yes** | "SHELTER COFFEE DRIVE • IRBID (@sheltercoffeedrive)" | threads.com/@sheltercoffeedrive |
| **Linktree** | "شلتر كوفي طلبات اربد" | **Yes** | Title "شلتر كوفي درايف اربد , روابط المنيو و الطلب" | linktr.ee/sheltercoffeedrive |
| **Accessible Jordan** | "Shelter coffee Irbid", "drive thru" | **Yes** | "Shelter Coffee Drive". Area "Irbid - Al Nuzha", "Awad Rshedat Street". Phone "07 9900 9436", Facebook URL. Accessibility note: suitable only "for people who can walk up a flight of stairs" (snippet) | accessiblejordan.com/en/places/view?slug=shelter-coffee-drive |
| **foodyas.com / findglocal.com** (Facebook-mirror sites) | "Shelter coffee Irbid" | **Yes** | "Shelter Coffee Drive, Awad rshedat st Shelter coffee Drive, Irbid (2026)". Hours "9:00 AM to 11:00 PM" (snippet). Services listed: delivery, drive-thru, takeout, outdoor seating, credit cards, valet parking | foodyas.com/JO/Irbid/256499115299768/Shelter-Coffee-Drive |
| **Drivu** (car-pickup ordering app) | "Irbid coffee drive thru" | **Not observed.** Meta Coffee is listed | — | drivu.co/shops/jordan/irbid |
| **Time Out Amman (trend.timeoutamman.com)** | "كافيهات شارع الجامعة اربد", Irbid City Centre and Arabella store guides | Not observed in snippets | — | timeoutamman.com/كافيهات-شارع-الجامعة-اربد/ |
| **ibnbattutatravel.com, regencyholidays.com, traveltriangle.com** ("best cafes/restaurants in Irbid" blogs) | "best cafes in Irbid Jordan" | Unknown (fetch blocked) | — | en.ibnbattutatravel.com/asia/discover-the-best-of-irbid-jordan-7-top-rated-cafes-to-visit/ |
| **jordanyp.com** ("Top 6 Coffee Shops in Irbid") | "jordanyp coffee shops Irbid", "District coffee Irbid" | **No** (old-style coffee houses listed) | — | jordanyp.com/category/Coffee_shops/city:Irbid |
| **arabplaces / top-rated.online / worldplaces / cybo / yellowplace** (scraped aggregators) | Brand + reviews queries | Not observed for SHELTER | — | jo.arabplaces.com/irbid/coffee-shop |
| **menu-world.com / goto-where.com** (auto-generated "menu" sites) | AJDA and Hatan menu queries | Not observed for SHELTER | — | ajda-coffee-bakery.menu-world.com |
| **Waze live-map** | Bizantino and Hail directions | Not observed | — | waze.com/live-map/… |
| **Wanderlog** | **No Irbid list surfaced.** Amman and Erbil lists appeared instead | n/a | — | — |
| **Careem** | Not observed in search results (mentioned only on SHELTER's own site and in Trasimeno snippets) | n/a | — | — |
| **Contractor portfolio (alekirjo.com)** | "Shelter coffee Irbid" | Yes, a third-party page | "Shelter Coffee - Irbid Branch" outdoor-space design project, "began on May 20, 2023" (snippet) | alekirjo.com/project/67bc…/Shelter Coffee - Irbid Branch |

### 4a. SHELTER public NAP / hours as seen in different sources — `CONFLICT DETECTED` (recorded, not resolved)

This is recorded for the Owner and CONFLICT-REGISTER. No value is endorsed. The new site must take every value from Owner-approved master data.

| Field | Source → text exactly as seen |
|---|---|
| City Centre floor | shelterjo.com homepage → "اربد ستي سنتر الطابق الثاني"; shelterjo.com/city-centre-branch/ → "إربد ستي سنتر، الطابق الأول، بجانب البنك الإسلامي الأردني" |
| City Centre hours | Homepage → Sat–Thu 9:00 AM–11:00 PM, Fri 1:00 PM–11:00 PM; /city-centre-branch/ → Sat–Wed & Fri 9:00–22:00, Thu 9:00–23:00; Snapchat (snippet) → "Monday - Wednesday, Saturday - Sunday 09:00 - 22:00" |
| Main / drive-thru hours | Homepage → Sat–Thu 7:00 AM–2:00 AM, Fri 8:00 AM–2:00 AM; /drive-thru/ → "يومياً 7:00 ص – 2:00 ل، الجمعة 8:00 ص – 2:00 ل" (a search summary rendered this as "7 AM to 2 PM"); foodyas (snippet) → "9:00 AM to 11:00 PM" |
| Main address wording | /drive-thru/ → "إربد، بجانب قاعات قصر النخيل وأرابيلا، شارع عوض رشيدات"; search summary of shelterjo → "يقع في شارع الجامعة بجانب قصر النخيل"; TripAdvisor → "Sharie Eiwad Rashidat, Irbid 21110"; Accessible Jordan → "Irbid - Al Nuzha … Awad Rshedat Street"; mat3am → "Al-Worood District … in front of Wasfi Al-Tal Street"; Talabat → "Al Worod" |
| Brand / branch names | "SHELTER COFFEE DRIVE" (TripAdvisor, Talabat, mat3am, Facebook, Threads, Accessible Jordan); "SHELTER COFFEE HOUSE" (Snapchat place); "شلتر كافيه / شلتر كافية" (shelterjo.com) |
| Phone | Consistent everywhere observed: +962 7 9900 9436 / 0799009436 |
| Public claims on old site | "أول كافية … درايف ثرو في شمال الأردن", "أكثر من 8000 زبون عاد لشلتر". These are claims and need Owner approval plus evidence before any reuse. In the same market, Starbucks (Wasfi Al-Tal drive-thru), Al Ameed (Irbid drive-thru) and Meta (Drivu car pickup) were observed. Their opening dates were not observed |

---

## 5. GAPS analysis

### 5.1 CONTENT GAPS (what searchers need that competitors don't give)
1. **Menu as HTML text with in-store prices.**
   - Observed only at KEY (bilingual, JOD), Think Coffee, Marouf (product pages), Apaq (prices not confirmed) and SHELTER's old homepage.
   - Most Irbid specialty independents (District 7, AJDA, Sago, Hatan, Tyme, Bizantino) leave the menu to Talabat, Instagram or third-party auto-menu sites.
   - No competitor shows **per-branch availability** of items.
2. **Separate pages for each branch with complete local facts.** Only the chains have them (Starbucks, Qahwa BLK, Al Ameed). No local independent publishes a page per branch with floor/landmark, hours, a tel: link, Maps/Waze links and parking/entrance notes.
3. **Live open/closed status.** Not observed on any competitor surface, including Starbucks (static hours only, in snippets).
4. **Drive-thru specifics.** Lane hours vs. café hours, what can be ordered, payment, wait expectations.
   - Al Ameed only states "Drive Thru only" on Friday.
   - Starbucks has a drive-thru location page.
   - Meta relies on the Drivu app.
   - Only SHELTER's old site has a "how it works" article.
5. **Coffee education** (V60/pour-over, origins, roast, beans).
   - `قهوة مختصة اربد V60` returned **no Irbid result at all**, only Saudi e-commerce.
   - OZ (V60 tools category), Meta (beans) and Bizantino (roaster) have the products but no indexed education content.
6. **Arabic + English parity.** Only KEY's menu, Qahwa BLK, Marouf and Starbucks offer English. SHELTER's old site is Arabic-only. English queries are almost entirely directories.
7. **Mall-visit intent** (Irbid City Centre). Results are TripAdvisor, Wikipedia, Foursquare and Starbucks pages. Nobody answers "which floor / next to what / mall hours vs café hours" except SHELTER's old branch page, which itself conflicts with the homepage (see 4a).
8. **Accessibility facts.** Only Accessible Jordan carries them (for SHELTER, a note about stairs). No café site observed publishes accessibility information.

### 5.2 SEARCH GAPS (results are mostly directories or lists, so a strong brand site could be the best answer)
| Query / intent | What ranked (observed) | Opportunity |
|---|---|---|
| "best cafes in Irbid" / "coffee shop Irbid" / "cafe Irbid City Center" (EN) | TripAdvisor (up to 4 URLs), Facebook pages, Wikipedia, irbid-now, travel blogs, jordanyp. Brand sites: only Think Coffee, Starbucks | High. shelterjo.com **did not appear** for any English query observed |
| "specialty coffee Irbid" (EN) | A Starbucks location-product page (#1), SHELTER's Facebook page, TripAdvisor, Facebook pages | High. No local specialty brand site ranks |
| "drive thru coffee Irbid" (EN) / "كوفي اربد درايف ثرو" (AR) | EN: SHELTER TripAdvisor/Facebook, Accessible Jordan, Al Ameed FAQ, JRA tag page, Drivu. AR: Saudi results plus shelterjo.com pages | High. Drive-thru is an intent with very few relevant local pages |
| "كافيه اربد قهوة مختصة", "افضل كافيهات اربد", "كافيه داخل اربد ستي سنتر" (AR) | **shelterjo.com already ranks with several URLs** (/, /drive-thru/, /best-cafes-irbid-2026/, /coffee-house-vs-cafe/, /city-centre-branch/, /blog/), alongside Hatan, Apaq, irbid-now and TripAdvisor | **Keep it.** The URL inventory and redirect plan must preserve these (redirects need the phase gate and Owner approval) |
| Brand + "menu" (e.g. AJDA, Hatan) | Third-party auto-menu sites (menu-world, goto-where), Talabat | A brand-owned HTML menu page captures "شلتر منيو / shelter menu" |
| Coffee education (`V60`, origins) in Irbid | Nothing local | Open field |
| Landmark queries (Yarmouk University, Wasfi Al-Tal St, قصر النخيل, Arabella) | **Not tested.** Validate later with free Search Console data | — |

### 5.3 UX GAPS
- **Social-only menus and hours:** District 7, Tyme, Sago, AJDA, Bizantino, Hail. Customers have to open Instagram, Snapchat or Talabat to see a menu.
- **Link-hub hops:** SHELTER itself routes "menu and order" through Linktree ("روابط المنيو و الطلب"). That adds an extra tap before the menu.
- **PDF menus:** none observed for Irbid competitors. The PDFs found were Marouf franchise booklets and an unrelated Boston "District 7 Cafe" menu. This is not a common local weakness, so there is no differentiation in "not PDF" alone.
- **Ambiguous time formats:** "2:00 ل" on SHELTER's drive-thru page was misread by a machine summarizer as 2 PM. Explicit formats and structured hours reduce misreading by search features and AI answers.
- **Speed / mobile:** **not assessed** (fetch blocked, no Lighthouse run on competitors). No claims are made.

### 5.4 LOCAL AUTHORITY GAPS
- **Schema:** unknown for all competitors. No JSON-LD was visible for shelterjo.com in converted content (verify locally). A correctly marked-up `CafeOrCoffeeShop` per branch, plus `Menu` and `openingHoursSpecification`, is an opportunity no local independent was observed using. Chains with Yext-style pages probably do, but this is unverified.
- **NAP consistency:** SHELTER's public footprint has inconsistent floor, hours, street wording and branch naming (section 4a). The City Centre branch (HOUSE) has **no separate listing** observed on TripAdvisor or Talabat. Chains keep NAP centralized.
- **Branch entities:** "SHELTER COFFEE DRIVE" is used as the brand name everywhere, while the Snapchat place is titled "SHELTER COFFEE HOUSE". Search engines may not see two distinct branches.
- **Maps links:** SHELTER's old branch page has Google Maps + Waze. Competitor sites: not observed. Waze place pages exist for Bizantino and Hail.
- **Events / community:** no Irbid competitor event pages were observed. Hatan mentions board games, Sago mentions study zones and meeting rooms, Bizantino the roasting experience.
- **Duplicate or legacy listings:** an old Foursquare venue "Shelter coffee - Café", Facebook-mirror sites (foodyas/findglocal), and the Accessible Jordan listing all carry the old wording.

---

## 6. What SHELTER can do better (factual levers, no claims)

All business values must come from Owner-approved master data. Where it is missing, the value stays `PENDING OWNER INPUT`.

1. **One HTML menu (AR + EN) from master data** with in-store prices once the Owner approves them, and per-branch availability. This replaces the Linktree hop. It is the direct answer to the content gap that only KEY partly fills locally.
2. **Two branch pages (DRIVE / HOUSE)**, each with:
   - its own exact address text and landmark (floor for HOUSE);
   - hours shown in an explicit format, with drive-thru lane hours separated if they differ;
   - a `tel:` link, Google Maps and Waze links;
   - parking/entrance notes, and accessibility facts if the Owner provides them.
   This needs the Owner to settle the section 4a variance first.
3. **Live open/closed status** calculated server-side from master hours. Not observed at any competitor.
4. **Structured data:** `Organization`, plus 2 × `CafeOrCoffeeShop` with `openingHoursSpecification`, `geo`, `telephone` and `hasMenu` → `Menu`/`MenuSection`/`MenuItem` (with `offers` only where prices are approved). The NAP must match the visible text and the listings exactly.
5. **English parity** for English queries, where TripAdvisor now dominates and shelterjo.com was absent.
6. **Drive-thru page** covering how it works, lane hours, what is available and payment. Few relevant local pages exist for this intent.
7. **Coffee education pages** (brew methods, origins), only for products and origins the Owner confirms. The local SERP for these terms is empty.
8. **Preserve existing Arabic rankings.** Map the ranking old URLs (`/`, `/drive-thru/`, `/city-centre-branch/`, `/best-cafes-irbid-2026/`, `/coffee-house-vs-cafe/`, `/blog/`) into the redirect plan. Executing it is gated by the phase gate and Owner approval.
9. **Listing hygiene checklist for the Owner** (Owner-operated accounts; no changes by Claude):
   - TripAdvisor (add or confirm a HOUSE listing?), Talabat (City Centre branch?), Snapchat place naming, Facebook, mat3am, Accessible Jordan, foodyas mirror.
   - Each one aligned to approved NAP and hours.
10. **Claims governance:** old-site claims ("first drive-thru in north Jordan", "8000 returning customers", "أفضل كافيهات أربد" in the H1) are public claims. Reusing them needs Owner approval and evidence. Drive-thru services by Starbucks and Al Ameed, and car pickup by Meta via Drivu, exist in Irbid today. Their opening dates were not observed.

---

## 7. Evidence URLs (all as seen 2026-10-02)

**SHELTER**
- https://www.shelterjo.com/ (fetched)
- https://www.shelterjo.com/city-centre-branch/ (fetched)
- https://www.shelterjo.com/drive-thru/ (fetched)
- https://www.shelterjo.com/best-cafes-irbid-2026/ (fetched)
- https://www.shelterjo.com/coffee-house-vs-cafe/ · https://www.shelterjo.com/blog/ · https://www.shelterjo.com/drive-thru-shelter-irbid-how-it-works/ (search results)
- https://www.tripadvisor.com/Restaurant_Review-g656904-d23032228-Reviews-Shelter_Coffee_Drive-Irbid_Irbid_Governorate.html
- https://www.talabat.com/jordan/shelter-coffee-drive · https://www.talabat.com/ar/jordan/shelter-coffee-drive · https://www.talabat.com/jordan/restaurant/637492/shelter-coffee-drive-al-worod?aid=4875
- https://www.mat3am.net/restaurant/23350/SHELTER-COFFEE-DRIVE-Al-Worood-District?lang=en
- https://www.snapchat.com/place/shelter-coffee-house/3875adf0-dca7-11ec-b133-1bba295e4827 · https://www.snapchat.com/@shelter_coffee
- https://www.facebook.com/sheltercoffeedrive/ · https://www.threads.com/@sheltercoffeedrive · https://linktr.ee/sheltercoffeedrive
- https://www.accessiblejordan.com/en/places/view?slug=shelter-coffee-drive
- https://www.foodyas.com/JO/Irbid/256499115299768/Shelter-Coffee-Drive · https://www.findglocal.com/JO/Irbid/256499115299768/Shelter-Coffee-Drive
- https://foursquare.com/v/shelter-coffee/53ac1c1f498e23677d643c54
- http://www.alekirjo.com/project/67bc0068610ef678e922dbaa/Shelter%20Coffee%20-%20Irbid%20Branch

**Competitors**
- Starbucks: https://locations.starbucks.com.jo/directory/irbid · https://locations.starbucks.com.jo/directory/irbid/irbid-city-centre · https://locations.starbucks.com.jo/directory/irbid/wasfi-al-tal-street-drive-thru · https://locations.starbucks.com.jo/irbid/irbid-city-centre/brewed-coffee · https://locations.alshaya.com/starbucks/jo/irbid · https://www.tripadvisor.com/Restaurant_Review-g656904-d10475913-Reviews-Starbucks_Coffee-Irbid_Irbid_Governorate.html
- Qahwa BLK: https://blk.jo/en/locations/ · https://qahwablk.com/en/qahwablk-english/ · https://qahwablk.com/en/locations-2/ · https://www.7hillsjo.com/eat-drink/qahwa-blk-taste-jordanian-excellence-coffee-day
- Al Ameed: https://www.alameedcoffee.com/branches?language=en · https://www.alameedcoffee.com/faq · https://store.alameedcoffee.com/pages/faqs · https://alameedexperience.com/ameed-automated-drive-thru
- Marouf: https://maroufcoffee.com/our-branches/ · https://maroufcoffee.com/menu-cafe/ · https://maroufcoffee.com/find-us/ · https://www.talabat.com/ar/jordan/bon-marouf
- Trasimeno: https://www.talabat.com/jordan/trasimeno-coffee-house · https://www.top-rated.online/cities/Aydoun/place/p/14614979/ · https://linktr.ee/trasimenocoffee
- Meta Coffee: https://metacoffeejo.com/en/collections/nagin · https://foursquare.com/v/meta-cafe/5a2453d9e679bc15e5a59d8e · https://drivu.co/shops/jordan/irbid · https://www.tripadvisor.com/Restaurant_Review-g656904-d19298593-Reviews-Meta_Coffee-Irbid_Irbid_Governorate.html
- OZ: https://ozcoffee.house/ · https://ozcoffee.house/help/faq
- AJDA: https://www.tripadvisor.com/Restaurant_Review-g656904-d27134856-Reviews-AJDA_Coffee_House-Irbid_Irbid_Governorate.html · https://www.instagram.com/ajdacoffee/ · https://www.talabat.com/jordan/ajda-coffee-bakery · https://ajda-coffee-bakery.menu-world.com/
- District 7: https://www.facebook.com/District7.Irbid/ · https://www.instagram.com/district7.irbid/ · https://www.talabat.com/jordan/restaurant/725684/district-7-al-worod?aid=4844
- KEY: https://key-jo.com/menu-eng · https://key-jo.com/menu · https://www.facebook.com/Key.cafejo/ · https://www.snapchat.com/place/key-cafe/77784f02-916e-11ef-9bda-ef8e4e898b97
- عصير تايم: https://www.instagram.com/39ertime.irbid/ · https://www.threads.com/@39ertime.irbid
- Hatan: https://www.facebook.com/hatan.cafejo/ · https://www.portal.hatanjo.com/ (article "هتان كافية: أفضل كافيهات في إربد لعام 2025") · https://www.talabat.com/jordan/hatan · https://hatan-coffee.goto-where.com/menu
- Sago: https://www.tripadvisor.com/Restaurant_Review-g656904-d23702440-Reviews-Sago_Coffee_House-Irbid_Irbid_Governorate.html · https://www.instagram.com/sago.coffeehouse/
- Think Coffee: https://thinkcoffeejo.com/ · https://thinkcoffeejo.com/menu/
- Bizantino: https://www.facebook.com/Bizantino.coffeeroaster/ · https://www.accessiblejordan.com/en/places/view?slug=bizantino-coffee-roaster
- Hail: https://www.facebook.com/Haelcafejo/ · https://www.instagram.com/hailcafejo/ · https://www.tripadvisor.com/Restaurant_Review-g656904-d26499632-Reviews-Hail_cafe-Irbid_Irbid_Governorate.html
- Apaq: https://apaqcafe.com/ · https://apaqcafe.com/menu/

**Directories / lists**
- https://www.tripadvisor.com/Restaurants-g656904-c8-Irbid_Irbid_Governorate.html · https://www.tripadvisor.com/Restaurants-g656904-zfg9900-Irbid_Irbid_Governorate.html · https://www.tripadvisor.com/Restaurants-g2625858-zfg9900-Irbid_Governorate.html
- https://irbid-now.com/en/irbid/restaurants/cafes · https://irbid-now.com/categories/cafes-irbid
- https://en.ibnbattutatravel.com/asia/discover-the-best-of-irbid-jordan-7-top-rated-cafes-to-visit/ · https://www.regencyholidays.com/blog/restaurants-in-irbid/ · https://traveltriangle.com/blog/restaurants-in-irbid/
- https://www.timeoutamman.com/كافيهات-شارع-الجامعة-اربد/ · https://trend.timeoutamman.com/إربد-سيتي-سنتر/
- https://www.jordanyp.com/category/Coffee_shops/city:Irbid · https://jo.arabplaces.com/irbid/coffee-shop
- https://www.jra.jo/feature/1061/drive-thru?pagesize=9 · https://drivu.co/shops/jordan/irbid
