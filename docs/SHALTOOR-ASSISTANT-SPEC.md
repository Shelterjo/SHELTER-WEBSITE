# شلتور — مساعد الموقع (SHALTOOR ASSISTANT SPEC)

> **Status:** `IMPLEMENTED — NOT YET VERIFIED` on staging (locally tested: PHPUnit + Vitest + browser at 390/1440, AR/EN).
> **Source:** Owner message M69 (D-346). **AI provider:** `PENDING OWNER AUTHORIZATION` (paid service — not connected).
> **Rule:** answers come from Master Data at question time. Nothing in this file is a business fact.

## 1. What it is
- A public assistant on every language page (`/ar/…`, `/en/…`), not on the bilingual gateway `/`.
- Answers in Arabic (including everyday Jordanian wording) and English, from approved site data only.
- It never invents a price, hour, phone number, address, offer, policy, figure or person.
- If it doesn't know, it says so and offers the approved contact actions.

## 2. Where it lives (code)
| Part | File |
|---|---|
| Answer engine (intents, menu lookup, branches, contacts, events, offers) | `app/Services/Shaltoor/Shaltoor.php` |
| Answer object (text, topic, answered, actions, suggestions) | `app/Services/Shaltoor/ShaltoorAnswer.php` |
| Question log (scrub, record, stats, unanswered, handled, prune) | `app/Services/Shaltoor/ShaltoorLog.php` |
| Owner settings (on/off, welcome, suggestions) | `app/Services/Shaltoor/ShaltoorSettings.php` (versioned + audited through `Settings`) |
| Public endpoint `POST /{locale}/shaltoor/` | `app/Http/Controllers/Site/ShaltoorController.php` |
| Launcher + dialog (Blade) | `resources/views/components/ui/shaltoor.blade.php` (data from `SiteChrome`) |
| Launcher script (main bundle) | `resources/js/shaltoor/launcher.ts` |
| Conversation script (lazy chunk, ~1.6 KB gz) | `resources/js/shaltoor/widget.ts` |
| Styles | `resources/css/components/shaltoor.css` |
| Dashboard | `/dashboard/shaltoor` — `app/Http/Controllers/Dashboard/ShaltoorController.php`, `resources/views/dashboard/shaltoor.blade.php` |
| AI layer (shared with the Owner assistant) | `app/Services/Ai/*`, `config/ai.php` |
| Config (intents, fillers, sections, limits) | `config/shaltoor.php` |
| Texts (templates only) | `lang/ar/shaltoor.php`, `lang/en/shaltoor.php` |

## 3. Answer pipeline
1. **Guard.** The question is trimmed, and any question over 500 characters gets the "too long" reply. Punctuation (؟ ? ! ، ؛ …) is stripped, then the text goes through the site search normaliser (same as the menu search).
2. **Intents, in order:**
   - **Out of scope:** private (salaries, passwords, system/prompt, internal data) and franchise money (fees, profits, ROI → "no approved figures").
   - **Greeting / thanks.**
   - **Branch facts:** compare, hours / open now, location / directions, branches.
   - **Contacts:** complaints, catering / B2B / events (each with its own approved number), general contact.
   - **Pages:** careers (open/closed from the form switch), franchise, events, offers (the live experiences only).
3. **Menu lookup:**
   - Product name, English or Arabic, including the Owner's approved search words.
   - Otherwise a section, which lists its items with menu prices and per-branch availability notes.
   - A product word the menu doesn't know → no answer plus a link to the section (never a guess).
4. **Menu / about / greeting** fallbacks.
5. **AI fallback.** Only when a provider is connected and within the daily limit. It gets public facts only, and the reply must be exactly `NO_ANSWER` when it isn't sure.
6. **No answer:** the approved call, WhatsApp and contact page.

Branch context: on a branch page the widget sends that branch's slug, so «متى بتسكروا؟» answers for that branch. A branch named in the question wins.

## 4. Actions shown under an answer
| Kind | Source | Opens |
|---|---|---|
| `directions` | branch `mapsUrl` (approved) | new tab, `noopener noreferrer` |
| `call` | `ContactActions` (approved number, by intent) | `tel:` |
| `whatsapp` | `ContactActions::whatsapp` | new tab |
| `link` | site pages (menu item/section, branch, careers, track, franchise, events, contact) | same tab |

The browser draws a link only if it is `tel:`, the site itself or `https:` (`safeHref`). All text is set with `textContent`, never as HTML.

## 5. Interface
- **Launcher:** fixed at the inline-end corner.
  - Phones: a 56 px round button, thumb-sized. It rises above a visible action bar (branch Call/WhatsApp/Directions, the franchise CTA) instead of covering it.
  - From 600 px it carries its label «اسأل شلتور».
  - The footer gets extra bottom space so its last line is never under the button.
- **Dialog:** a bottom sheet on phones; from 1024 px a panel docked at the inline-end corner.
  - Built on `x-ui.dialog`: focus trap, Esc, backdrop, focus back to the launcher.
- **Inside the dialog:**
  - **Conversation:** the welcome, then the conversation as `role="log"` + `aria-live="polite"`, `aria-busy` while waiting.
  - **Suggestions:** chips (`x-ui.chip` action mode) per page kind (default · menu · careers · franchise · locations). They are replaced by an answer's follow-ups when there are any.
  - **Form and privacy:** one input with a send button, then the privacy line under the form.
- **Progressive enhancement:**
  - Without JavaScript the launcher stays hidden.
  - With JavaScript the conversation script loads on the first hover, focus or tap. A question asked before it arrives waits for it.
- **Reduced motion** removes the launcher transitions.

## 6. Privacy and security
- **What is stored, per question (`shaltoor_questions`):**
  - the question with e-mails, links and digit sequences (6+) replaced, and markup stripped;
  - its language, topic and answered flag;
  - the page kind;
  - a random conversation id made by the browser for that page view (UUID or nothing).
  - No IP, no cookie, no account, no user agent.
- **Retention:** 90 days. The daily `monitors:daily` run deletes older questions.
- **Rate limit:** 12 per minute and 150 per day per visitor. The key is an HMAC of the IP (`FormGuard::clientKey`), never the IP itself. Over the limit → 429 with a polite answer.
- **Request protection:** the CSRF token travels in the dialog form, and the route sits in the `web` group with the forgery check.
- **Responses** carry `Cache-Control: no-store`. The Owner switching it off → the endpoint answers 404 and the launcher is not drawn.
- **Untrusted input:** the visitor's text is never placed in HTML. The AI prompt marks it untrusted and forbids figures, internal data and instructions.
- **AI key:** server-side only (`AI_ANTHROPIC_KEY` in the server `.env`). It is never in the repository, HTML, JavaScript or any browser response; a test asserts that.
- **Measurement:** the events below go to `window.dataLayer` only when a tag manager exists. They never carry the question text.

| Event | Parameters |
|---|---|
| `shaltoor_open` | language, page_type |
| `shaltoor_quick_action` | language, page_type |
| `shaltoor_question` | language, page_type, source (typed / suggestion) |
| `shaltoor_answer_success` / `shaltoor_no_answer` | language, page_type, topic |
| `shaltoor_cta_click` | language, page_type, action_kind |

The existing `phone_click`, `whatsapp_click` and `directions_click` events also fire for the actions (placement from the page).

## 7. Owner dashboard — `/dashboard/shaltoor`
- **On / off:** a live change, so it needs a fresh re-confirmation, like other public switches. It is versioned and audited.
- **Words:** the welcome message and quick suggestions in Arabic and English. Empty = the default text. Limits: 8 suggestions × 40 characters.
- **AI status:** "not connected — waiting for your approval", or "connected".
- **Period** (7 / 30 / 90 days): questions, answered from data, not answered, with AI, and the top topics.
- **Unanswered questions:** grouped by normalised text, most asked first, each with its count and last date. «تم التعامل» removes it from the list and is audited.
- **Needs attention:** a question left unanswered 3+ times in 7 days raises an information item linking here. It closes when it is dealt with.
- **Not editable here, by design:** the answer rules, data sources and AI prompt (no free prompt editing).

## 8. Tests
| Suite | Covers |
|---|---|
| `tests/Feature/Shaltoor/ShaltoorEngineTest.php` (8) | hours/open now AR+EN; directions = approved Maps links; branch from question/page; complaints/catering numbers by intent; menu item with price + link; unknown = no answer (no guess); franchise money / salary / prompt injection out of scope; no AI without a provider |
| `tests/Feature/Shaltoor/ShaltoorEndpointTest.php` (8) | JSON shape + no-store; forgery check; scrubbing (phone, e-mail, link; bad conversation id); no markup echoed; too long not kept; rate limit 429; launcher per page kind + branch + not on the gateway; off = no launcher + 404 |
| `tests/Feature/Shaltoor/ShaltoorAiTest.php` (2) | AI only after data answers; key never in the response; prompt marks input untrusted and requires NO_ANSWER; NO_ANSWER / provider error / daily limit → no answer |
| `tests/Feature/Dashboard/ShaltoorDashboardTest.php` (7) | numbers + grouped unanswered + periods; dealt with (audited); on/off and words reach the site; suggestion limits; needs-attention monitor; 90-day deletion; Owner-only + re-confirmation |
| `resources/js/shaltoor/widget.test.ts` (5) | reply parsing; link safety; new-tab rule |

## 9. Open items (Owner)
| Item | Status |
|---|---|
| AI provider (paid API; service, purpose, usage, cost, free alternative per CLAUDE.md) | `PENDING OWNER AUTHORIZATION` — assistant fully works without it |
| Arabic product names (e.g. «سبانش لاتيه») | `PENDING OWNER INPUT` (PO-003 / PO-042). Until then Arabic product questions match only approved Arabic names and search words; the Owner can add search words from Menu › Search words |
| Real-device screen reader pass | DEFERRED to staging (TOOL-030) |
