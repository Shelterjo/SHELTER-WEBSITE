// شلتور — the conversation (M69). Fetched on the first open only (resources/js/shaltoor/launcher.ts, which owns the
// listeners and hands each question here). One question in, one answer out: the server answers from Master Data and
// returns plain text, the actions it offers and follow-up suggestions. Everything from the server is written with textContent (never as HTML) and every link is checked before
// it is drawn. Measurement goes through track() (a no-op until a tag manager exists) and never carries what the visitor
// typed: only the language, the page kind, the answer topic and the action kind.
import { track } from '../ui/track';

export interface ShaltoorAction {
    readonly kind: 'call' | 'whatsapp' | 'directions' | 'link';
    readonly label: string;
    readonly href: string;
}

export interface ShaltoorReply {
    readonly text: string;
    readonly topic: string;
    readonly answered: boolean;
    readonly actions: readonly ShaltoorAction[];
    readonly suggestions: readonly string[];
}

const KINDS = new Set(['call', 'whatsapp', 'directions', 'link']);

const isRecord = (value: unknown): value is Record<string, unknown> => typeof value === 'object' && value !== null;

/** The server's JSON, checked field by field; anything malformed is dropped rather than drawn. */
export function parseReply(data: unknown): ShaltoorReply | null {
    if (!isRecord(data) || typeof data['text'] !== 'string' || typeof data['topic'] !== 'string') return null;
    const actions: ShaltoorAction[] = [];
    for (const item of Array.isArray(data['actions']) ? (data['actions'] as unknown[]) : []) {
        if (!isRecord(item)) continue;
        const { kind, label, href } = item;
        if (typeof kind === 'string' && KINDS.has(kind) && typeof label === 'string' && typeof href === 'string') {
            actions.push({ kind: kind as ShaltoorAction['kind'], label, href });
        }
    }
    const suggestions = (Array.isArray(data['suggestions']) ? (data['suggestions'] as unknown[]) : []).filter(
        (s): s is string => typeof s === 'string' && s.trim() !== '',
    );

    return { text: data['text'], topic: data['topic'], answered: data['answered'] === true, actions, suggestions };
}

/** Only tel:, the site itself and https links are drawn (no javascript:, data: or plain http elsewhere). */
export function safeHref(href: string, base: string): string | null {
    if (/^tel:\+?[0-9]{6,15}$/.test(href)) return href;
    let url: URL;
    try {
        url = new URL(href, base);
    } catch {
        return null;
    }
    const origin = new URL(base).origin;
    if (url.origin === origin) return url.href;

    return url.protocol === 'https:' ? url.href : null;
}

/** A link that leaves the site opens in a new tab (with noopener); calls and site pages stay in place. */
export function opensNewTab(href: string, base: string): boolean {
    if (href.startsWith('tel:')) return false;
    try {
        return new URL(href, base).origin !== new URL(base).origin;
    } catch {
        return false;
    }
}

export type AskSource = 'typed' | 'suggestion';

export interface Shaltoor {
    ask(question: string, source: AskSource): Promise<void>;
}

/** A random conversation id for this page view only: not stored, not a cookie, never linked to a person. */
function conversationId(): string {
    if (typeof crypto !== 'undefined' && 'randomUUID' in crypto) return crypto.randomUUID();
    return `${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 10)}`;
}

export function createShaltoor(root: HTMLElement): Shaltoor {
    const log = root.querySelector<HTMLOListElement>('[data-shaltoor-log]');
    const form = root.querySelector<HTMLFormElement>('[data-shaltoor-form]');
    const input = form?.querySelector<HTMLInputElement>('input[name="question"]') ?? null;
    const submit = form?.querySelector<HTMLButtonElement>('button[type="submit"]') ?? null;
    const token = form?.querySelector<HTMLInputElement>('input[name="_token"]')?.value ?? '';
    const suggestionsBox = root.querySelector<HTMLElement>('[data-shaltoor-suggestions]');
    const endpoint = root.dataset['endpoint'] ?? '';
    const page = root.dataset['page'] ?? 'default';
    const branch = root.dataset['branch'] ?? '';
    const language = document.documentElement.lang || 'ar';
    const conversation = conversationId();
    let busy = false;

    const icon = (kind: string): Node | null =>
        root.querySelector<HTMLTemplateElement>(`template[data-shaltoor-icon="${kind}"]`)?.content.cloneNode(true) ??
        null;

    const scrollToEnd = (): void => {
        if (log !== null) log.scrollTop = log.scrollHeight;
    };

    const message = (text: string, who: 'bot' | 'user'): HTMLLIElement => {
        const item = document.createElement('li');
        item.className = `ui-shaltoor__message ui-shaltoor__message--${who}`;
        item.textContent = text;
        log?.append(item);
        scrollToEnd();
        return item;
    };

    const actionsFor = (reply: ShaltoorReply): HTMLElement | null => {
        const base = window.location.href;
        const links = reply.actions
            .map((action) => {
                const href = safeHref(action.href, base);
                if (href === null) return null;
                const link = document.createElement('a');
                link.className = 'ui-button ui-button--secondary ui-button--sm';
                link.href = href;
                const glyph = icon(action.kind);
                if (glyph !== null) link.append(glyph);
                link.append(document.createTextNode(action.label));
                if (opensNewTab(href, base)) {
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                }
                link.addEventListener('click', () =>
                    track('shaltoor_cta_click', { language, page_type: page, action_kind: action.kind }),
                );
                return link;
            })
            .filter((link): link is HTMLAnchorElement => link !== null);
        if (links.length === 0) return null;
        const box = document.createElement('div');
        box.className = 'ui-shaltoor__actions';
        box.append(...links);
        return box;
    };

    // Follow-ups from the answer replace the chips; with none, the page's own suggestions stay.
    const showSuggestions = (suggestions: readonly string[]): void => {
        if (suggestionsBox === null || suggestions.length === 0) return;
        const chips = suggestions.slice(0, 4).map((text) => {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'ui-chip';
            chip.dataset['shaltoorAsk'] = '';
            chip.textContent = text;
            return chip;
        });
        suggestionsBox.replaceChildren(...chips);
    };

    const setBusy = (value: boolean): void => {
        busy = value;
        log?.setAttribute('aria-busy', String(value));
        if (submit !== null) submit.setAttribute('aria-disabled', String(value));
    };

    const ask = async (raw: string, source: AskSource): Promise<void> => {
        const question = raw.trim();
        if (question === '' || busy || endpoint === '') return;
        setBusy(true);
        message(question, 'user');
        if (input !== null && source === 'typed') input.value = '';
        track('shaltoor_question', { language, page_type: page, source });

        const typing = message(root.dataset['typing'] ?? '…', 'bot');
        typing.classList.add('ui-shaltoor__message--typing');
        typing.setAttribute('aria-hidden', 'true');

        let reply: ShaltoorReply | null;
        try {
            const body = new FormData();
            body.set('question', question);
            body.set('page', page);
            body.set('conversation', conversation);
            if (branch !== '') body.set('branch', branch);
            const response = await fetch(endpoint, {
                method: 'POST',
                body,
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
            });
            reply = parseReply(await response.json());
        } catch {
            reply = null;
        }
        typing.remove();

        const answer = message(reply?.text ?? root.dataset['error'] ?? '', 'bot');
        const actions = reply !== null ? actionsFor(reply) : null;
        if (actions !== null) answer.append(actions);
        scrollToEnd();
        if (reply !== null) {
            showSuggestions(reply.suggestions);
            track(reply.answered ? 'shaltoor_answer_success' : 'shaltoor_no_answer', {
                language,
                page_type: page,
                topic: reply.topic,
            });
        }
        setBusy(false);
    };

    return { ask };
}
