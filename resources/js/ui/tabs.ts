// Tabs (ARIA APG, automatic activation) for <x-ui.tabs>. The server renders the complete ARIA state; this module only
// adds the interaction: click selects, Arrow keys (following the reading direction), Home and End move and select.
// Event delegation on the document, so tabs added later (Storybook, partial updates) work without re-initialising.
import { nextIndex } from './roving';

const TAB = '[data-ui-tabs] [role="tab"]';
const installed = new WeakSet<Document>();

function tabsOf(tab: HTMLElement): HTMLElement[] {
    const list = tab.closest('[role="tablist"]');
    return list ? Array.from(list.querySelectorAll<HTMLElement>('[role="tab"]')) : [];
}

function select(tab: HTMLElement): void {
    for (const item of tabsOf(tab)) {
        const selected = item === tab;
        item.setAttribute('aria-selected', String(selected));
        item.tabIndex = selected ? 0 : -1;
        const panel = item.ownerDocument.getElementById(item.getAttribute('aria-controls') ?? '');
        if (panel) panel.hidden = !selected;
    }
}

function tabFrom(target: EventTarget | null): HTMLElement | null {
    return target instanceof Element ? target.closest<HTMLElement>(TAB) : null;
}

export function installTabs(doc: Document = document): void {
    if (installed.has(doc)) return;
    installed.add(doc);
    doc.addEventListener('click', (event) => {
        const tab = tabFrom(event.target);
        if (tab && tab.getAttribute('aria-disabled') !== 'true') select(tab);
    });
    doc.addEventListener('keydown', (event) => {
        const tab = tabFrom(event.target);
        if (!tab) return;
        const tabs = tabsOf(tab);
        const list = tab.closest('[role="tablist"]');
        const dir = list && getComputedStyle(list).direction === 'rtl' ? 'rtl' : 'ltr';
        const vertical = list?.getAttribute('aria-orientation') === 'vertical';
        const index = nextIndex(
            tabs.indexOf(tab),
            tabs.length,
            event.key,
            dir,
            vertical ? 'vertical' : 'horizontal',
            (i) => tabs[i]?.getAttribute('aria-disabled') === 'true',
        );
        const next = index === null ? undefined : tabs[index];
        if (!next) return;
        event.preventDefault();
        next.focus();
        select(next);
    });
}
