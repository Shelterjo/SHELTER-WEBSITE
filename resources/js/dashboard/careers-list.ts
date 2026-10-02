// Job applications list (CAREERS-058/065): live search while typing (debounced; only the results area is replaced,
// so the focus and the caret stay in the search box) and the quick view side panel. Both are enhancements: without
// the script the search button and the full application page work as before.
import { installBulk } from './bulk';

export const LIVE_DELAY_MS = 400;

/** Calls `run` once typing pauses for `delay` ms (a query per pause, not per keystroke). */
export function debounce<A extends unknown[]>(run: (...args: A) => void, delay: number): (...args: A) => void {
    let timer: ReturnType<typeof setTimeout> | undefined;
    return (...args: A): void => {
        if (timer !== undefined) clearTimeout(timer);
        timer = setTimeout(() => run(...args), delay);
    };
}

/** A search runs for an empty box (all results) or from two characters; one character is too broad. */
export function shouldSearch(value: string): boolean {
    const length = value.trim().length;
    return length === 0 || length >= 2;
}

/** The list URL for the form's current values (page reset to 1). */
export function listUrl(action: string, entries: [string, string][]): string {
    const url = new URL(action, 'http://localhost');
    const params = new URLSearchParams();
    for (const [key, value] of entries) {
        if (value !== '' && key !== 'page') params.append(key, value);
    }
    url.search = params.toString();
    return url.pathname + (url.search === '' ? '' : url.search);
}

export function installCareersList(results: HTMLElement): void {
    const form = document.querySelector<HTMLFormElement>('form.ui-inbox-filters');
    const input = form?.querySelector<HTMLInputElement>('#q');
    let controller: AbortController | null = null;

    const refresh = async (): Promise<void> => {
        if (form === null || form === undefined || input === null || input === undefined || !shouldSearch(input.value))
            return;
        const entries = Array.from(new FormData(form).entries()).map(([k, v]) => [k, String(v)] as [string, string]);
        const target = listUrl(form.action, entries);
        controller?.abort();
        controller = new AbortController();
        try {
            const response = await fetch(target, { headers: { Accept: 'text/html' }, signal: controller.signal });
            if (!response.ok) return;
            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const fresh = page.querySelector('[data-careers-results]');
            if (fresh === null) return;
            results.replaceChildren(...Array.from(fresh.childNodes).map((node) => document.importNode(node, true)));
            window.history.replaceState(window.history.state, '', target);
            const bulk = results.querySelector<HTMLFormElement>('form[data-bulk]');
            if (bulk !== null) installBulk(bulk);
        } catch {
            // Aborted by a newer search, or offline: the button still submits the form normally.
        }
    };
    input?.addEventListener(
        'input',
        debounce(() => void refresh(), LIVE_DELAY_MS),
    );

    // Quick view: a plain click on a name opens the side panel; a new-tab click (Ctrl/⌘/middle) opens the full page.
    const panel = document.querySelector<HTMLDialogElement>('dialog[data-quick-view-panel]');
    const slot = panel?.querySelector<HTMLElement>('[data-quick-target]');
    document.addEventListener('click', (event) => {
        const link = (event.target as Element).closest<HTMLAnchorElement>('a[data-quick-view]');
        if (link === null || panel === null || panel === undefined || slot === null || slot === undefined) return;
        if (event.ctrlKey || event.metaKey || event.shiftKey || event.button !== 0) return;
        event.preventDefault();
        void (async () => {
            try {
                const response = await fetch(link.dataset.quickView ?? '', {
                    headers: { 'X-Quick-View': '1', Accept: 'text/html' },
                });
                if (!response.ok) throw new Error(String(response.status));
                const page = new DOMParser().parseFromString(await response.text(), 'text/html');
                const body = page.querySelector<HTMLElement>('[data-quick-body]');
                if (body === null) throw new Error('empty');
                slot.replaceChildren(document.importNode(body, true));
                const title = panel.querySelector<HTMLElement>('.ui-dialog__title');
                if (title !== null && body.dataset.quickTitle) title.textContent = body.dataset.quickTitle;
                if (!panel.open) panel.showModal();
            } catch {
                window.location.href = link.href; // no panel: the full page
            }
        })();
    });
}
