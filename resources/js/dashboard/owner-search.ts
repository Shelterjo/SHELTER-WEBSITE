// Dashboard search (DASH-019): results refresh while typing (debounced; only the results area is replaced, so the
// focus and the caret stay in the box). An enhancement only: without the script the button submits the GET form.
// Nothing typed is stored in the browser — the address bar keeps the query like a submitted search would.

const SEARCH_DELAY_MS = 350;

/** The server answers from two characters; an empty box clears the results. */
export function searchable(value: string): boolean {
    const length = value.trim().length;
    return length === 0 || length >= 2;
}

/** The results address for a query: the form's action with `q` only (an empty box → the bare page). */
export function searchUrl(action: string, query: string): string {
    const url = new URL(action, 'http://localhost');
    const q = query.trim().replace(/\s+/g, ' ').slice(0, 100);
    url.search = q === '' ? '' : new URLSearchParams({ q }).toString();
    return url.pathname + url.search;
}

export function installOwnerSearch(root: HTMLElement): void {
    const form = root.querySelector<HTMLFormElement>('form[role="search"]');
    const input = form?.querySelector<HTMLInputElement>('input[name="q"]');
    const results = root.querySelector<HTMLElement>('[data-owner-search-results]');
    if (form === null || form === undefined || input === null || input === undefined || results === null) return;
    let timer: ReturnType<typeof setTimeout> | undefined;
    let controller: AbortController | null = null;

    const refresh = async (): Promise<void> => {
        if (!searchable(input.value)) return;
        const target = searchUrl(form.action, input.value);
        controller?.abort();
        controller = new AbortController();
        try {
            const response = await fetch(target, { headers: { Accept: 'text/html' }, signal: controller.signal });
            if (!response.ok) return;
            const page = new DOMParser().parseFromString(await response.text(), 'text/html');
            const fresh = page.querySelector('[data-owner-search-results]');
            if (fresh === null) return;
            results.replaceChildren(...Array.from(fresh.childNodes).map((node) => document.importNode(node, true)));
            window.history.replaceState(window.history.state, '', target);
        } catch {
            // Aborted by a newer search, or offline: the button still submits the form normally.
        }
    };

    input.addEventListener('input', () => {
        if (timer !== undefined) clearTimeout(timer);
        timer = setTimeout(() => void refresh(), SEARCH_DELAY_MS);
    });
}
