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

/** The column order after dropping `moved` before (or after) `target`; unchanged for an unknown or same column. */
export function reorder(order: string[], moved: string, target: string, after: boolean): string[] {
    if (moved === target || !order.includes(moved) || !order.includes(target)) return order;
    const rest = order.filter((column) => column !== moved);
    const at = rest.indexOf(target) + (after ? 1 : 0);
    return [...rest.slice(0, at), moved, ...rest.slice(at)];
}

/**
 * Drag and drop for the table columns (CAREERS-061, mouse): a shown column dropped on another takes its place and the
 * form is sent at once with `reorder` (the ticked boxes arrive in the new order). Keyboard and touch keep Up / Down.
 */
export function installColumnDrag(list: HTMLElement): void {
    const form = list.closest('form');
    if (form === null) return;
    const items = (): HTMLElement[] => [...list.querySelectorAll<HTMLElement>('[data-column]')];
    let dragged: HTMLElement | null = null;
    for (const item of items()) {
        if (item.querySelector<HTMLInputElement>('input[name="shown[]"]')?.checked !== true) continue;
        item.draggable = true;
        item.addEventListener('dragstart', (event) => {
            dragged = item;
            item.dataset.dragging = '';
            event.dataTransfer?.setData('text/plain', item.dataset.column ?? '');
        });
        item.addEventListener('dragend', () => {
            delete item.dataset.dragging;
            dragged = null;
        });
        item.addEventListener('dragover', (event) => {
            if (dragged !== null && dragged !== item) event.preventDefault();
        });
        item.addEventListener('drop', (event) => {
            event.preventDefault();
            if (dragged === null || dragged === item) return;
            const box = item.getBoundingClientRect();
            const next = reorder(
                items().map((i) => i.dataset.column ?? ''),
                dragged.dataset.column ?? '',
                item.dataset.column ?? '',
                event.clientY > box.top + box.height / 2,
            );
            const byName = new Map(items().map((i) => [i.dataset.column ?? '', i]));
            for (const name of next) {
                const node = byName.get(name);
                if (node !== undefined) list.append(node);
            }
            const flag = document.createElement('input');
            flag.type = 'hidden';
            flag.name = 'reorder';
            flag.value = '1';
            form.append(flag);
            form.requestSubmit();
        });
    }
}

export function installCareersList(results: HTMLElement): void {
    const columns = document.querySelector<HTMLElement>('[data-column-list]');
    if (columns !== null) installColumnDrag(columns);
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
