// Menu page behaviour (Menu IA spec §7–§9, M40 §12): search with suggestions and live filtering, the branch selector
// (?branch= via replaceState + last choice on this device), the product detail dialog with browser Back, and the
// current category in the category bar. Everything here is an enhancement: the server-rendered menu works without it.
import { matches } from './search';

interface IndexItem {
    id: string;
    name: string;
    lang: string | null;
    secondary: string | null;
    secondaryLang: string | null;
    section: string;
    price: number;
    terms: string[];
}

/** What a card shows for one branch choice (Menu IA §9.6), rendered by the server: hidden · status · faded · price. */
interface BranchView {
    h: boolean;
    s: string | null;
    u: boolean;
    p: number;
}

interface Messages {
    results: Record<string, string>;
    noResults: string;
    seasonal: string;
}

const STORAGE_KEY = 'shelter.menu.branch';
const MAX_SUGGESTIONS = 5;
const LOCATE_MS = 1500;

function readJson<T>(id: string): T | null {
    const node = document.getElementById(id);
    if (node === null || node.textContent === null) return null;
    try {
        return JSON.parse(node.textContent) as T;
    } catch {
        return null;
    }
}

function resultsText(messages: Messages, count: number, locale: string): string {
    const category = count === 0 ? 'zero' : new Intl.PluralRules(locale).select(count);
    const template = messages.results[category] ?? messages.results['other'] ?? '';
    return template.replace(':count', new Intl.NumberFormat(locale).format(count));
}

export function installMenuPage(root: HTMLElement): void {
    const locale = document.documentElement.lang || 'ar';
    const index = readJson<IndexItem[]>('menu-index') ?? [];
    const messages = readJson<Messages>('menu-messages');
    if (messages === null) return;

    const byId = new Map(index.map((item) => [item.id, item]));
    const items = Array.from(root.querySelectorAll<HTMLElement>('[data-ui-menu-item]'));
    const sections = Array.from(root.querySelectorAll<HTMLElement>('[data-ui-menu-section]'));
    const count = root.querySelector<HTMLElement>('[data-ui-menu-count]');
    const empty = root.querySelector<HTMLElement>('[data-ui-menu-empty]');
    const input = root.querySelector<HTMLInputElement>('#menu-search');
    const list = root.querySelector<HTMLUListElement>('#menu-search-list');
    const clear = root.querySelector<HTMLButtonElement>('[data-ui-search-clear]');

    // ── Search ──────────────────────────────────────────────────────────────────────────────────────────────
    let active = -1;
    let options: IndexItem[] = [];

    const closeList = (): void => {
        if (list === null || input === null) return;
        list.hidden = true;
        list.replaceChildren();
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        active = -1;
    };

    const filter = (query: string): void => {
        const q = query.trim();
        let visible = 0;
        for (const element of items) {
            const item = byId.get(element.dataset.uiMenuItem ?? '');
            const matched =
                q === '' || (item !== undefined && matches(q, [item.name, item.secondary ?? '', ...item.terms]));
            // An item hidden at the chosen branch (UNAVAILABLE_HIDE) stays hidden whatever the search.
            const show = matched && element.dataset.branchHidden !== 'true';
            element.hidden = !show;
            if (show) visible++;
        }
        for (const section of sections) {
            section.hidden = section.querySelector('[data-ui-menu-item]:not([hidden])') === null;
            // A section with nothing at the chosen branch loses its chip too; a search never hides chips.
            const empty = section.querySelector('[data-ui-menu-item]:not([data-branch-hidden="true"])') === null;
            const chip = root
                .querySelector<HTMLElement>(`[data-ui-menu-nav="${CSS.escape(section.id)}"]`)
                ?.closest('li');
            if (chip !== null && chip !== undefined) chip.hidden = empty;
        }
        if (count !== null) {
            count.hidden = q === '';
            count.textContent = q === '' ? '' : resultsText(messages, visible, locale);
        }
        if (empty !== null) {
            empty.hidden = q === '' || visible > 0;
            const title = empty.querySelector('h2, h3, .ui-empty-state__title');
            if (title !== null) title.textContent = messages.noResults.replace(':query', q);
        }
        if (clear !== null) clear.hidden = q === '';
    };

    const renderOptions = (query: string): void => {
        if (list === null || input === null) return;
        const q = query.trim();
        options =
            q.length < 2 ? [] : index.filter((item) => matches(q, [item.name, item.secondary ?? '', ...item.terms]));
        options = options.slice(0, MAX_SUGGESTIONS);
        if (options.length === 0) {
            closeList();
            return;
        }
        list.replaceChildren(
            ...options.map((item, position) => {
                const option = document.createElement('li');
                option.id = `menu-option-${position}`;
                option.setAttribute('role', 'option');
                option.className = 'ui-search__option';
                option.dataset.target = item.id;
                const name = document.createElement('span');
                name.className = 'ui-search__option-name';
                name.textContent = item.name;
                if (item.lang !== null) name.lang = item.lang;
                const meta = document.createElement('span');
                meta.className = 'ui-search__option-meta';
                meta.textContent = item.section;
                option.append(name, meta);
                option.addEventListener('mousedown', (event) => event.preventDefault());
                option.addEventListener('click', () => locate(item.id));
                return option;
            }),
        );
        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    };

    const highlight = (position: number): void => {
        if (list === null || input === null || options.length === 0) return;
        active = (position + options.length) % options.length;
        list.querySelectorAll('[role="option"]').forEach((node, i) =>
            node.setAttribute('aria-selected', String(i === active)),
        );
        input.setAttribute('aria-activedescendant', `menu-option-${active}`);
    };

    // Jump to the product in its natural place (spec §8.3): clear the filter, scroll, outline it for 1.5 s.
    const locate = (id: string): void => {
        if (input !== null) input.value = '';
        closeList();
        filter('');
        const target = root.querySelector<HTMLElement>(`[data-ui-menu-item="${CSS.escape(id)}"]`);
        if (target === null) return;
        target.scrollIntoView({ block: 'center' });
        target.classList.add('is-located');
        target.querySelector<HTMLElement>('.ui-product-card__action')?.focus({ preventScroll: true });
        window.setTimeout(() => target.classList.remove('is-located'), LOCATE_MS);
    };

    if (input !== null) {
        input.addEventListener('input', () => {
            filter(input.value);
            renderOptions(input.value);
        });
        input.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                highlight(active + 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                highlight(active - 1);
            } else if (event.key === 'Enter') {
                event.preventDefault();
                const picked = active >= 0 ? options[active] : undefined;
                if (picked !== undefined) locate(picked.id);
                else closeList();
            } else if (event.key === 'Escape') {
                closeList();
            }
        });
        input.addEventListener('blur', () => window.setTimeout(closeList, 0));
    }
    const reset = (): void => {
        if (input === null) return;
        input.value = '';
        closeList();
        filter('');
        input.focus();
    };
    clear?.addEventListener('click', reset);
    root.querySelector('[data-ui-menu-clear]')?.addEventListener('click', reset);

    // ── Branch selector (spec §9.2–9.3) ─────────────────────────────────────────────────────────────────────
    // Items that differ by branch carry their view for every choice; switching only swaps text, price and visibility.
    const views = new Map<HTMLElement, Record<string, BranchView>>();
    for (const element of items) {
        if (element.dataset.uiMenuBranches === undefined) continue;
        try {
            views.set(element, JSON.parse(element.dataset.uiMenuBranches) as Record<string, BranchView>);
        } catch {
            // Malformed data: the card keeps what the server rendered.
        }
    }
    const showBranch = (value: string): void => {
        views.forEach((byBranch, element) => {
            const view = byBranch[value] ?? byBranch['all'];
            if (view === undefined) return;
            if (view.h) element.dataset.branchHidden = 'true';
            else delete element.dataset.branchHidden;
            const card = element.querySelector<HTMLElement>('.ui-product-card');
            if (card === null) return;
            card.classList.toggle('ui-product-card--unavailable', view.u);
            const status = card.querySelector<HTMLElement>('.ui-product-card__status');
            const text = status?.querySelector('span');
            if (status !== null && text !== null && text !== undefined) {
                text.textContent = view.s ?? '';
                status.hidden = view.s === null;
            }
            const amount = (view.p / 1000).toFixed(2);
            card.querySelectorAll('.ui-product-card__price bdi').forEach((node) => {
                node.textContent = amount;
            });
            const spoken = card.querySelector('.ui-product-card__price .ui-visually-hidden');
            if (spoken?.textContent) spoken.textContent = spoken.textContent.replace(/^[\d.]+/, amount);
        });
        filter(input?.value ?? '');
    };
    const segmented = root.querySelector<HTMLElement>('[data-ui-menu-branch]');
    const applyBranch = (value: string, remember: boolean): void => {
        if (segmented === null) return;
        segmented.querySelectorAll<HTMLButtonElement>('button[data-value]').forEach((button) => {
            button.setAttribute('aria-pressed', String(button.dataset.value === value));
        });
        root.querySelectorAll<HTMLElement>('[data-ui-menu-status]').forEach((line) => {
            line.hidden = value !== 'all' && line.dataset.uiMenuStatus !== value;
        });
        root.dataset.branch = value;
        showBranch(value);
        const url = new URL(window.location.href);
        if (value === 'all') url.searchParams.delete('branch');
        else url.searchParams.set('branch', value);
        window.history.replaceState(window.history.state, '', url);
        if (remember) {
            try {
                window.localStorage.setItem(STORAGE_KEY, value);
            } catch {
                // Private mode / storage off: the choice simply is not remembered.
            }
        }
    };
    if (segmented !== null) {
        segmented.addEventListener('click', (event) => {
            const button = (event.target as Element).closest<HTMLButtonElement>('button[data-value]');
            if (button?.dataset.value !== undefined) applyBranch(button.dataset.value, true);
        });
        if (!new URL(window.location.href).searchParams.has('branch')) {
            let saved: string | null = null;
            try {
                saved = window.localStorage.getItem(STORAGE_KEY);
            } catch {
                saved = null;
            }
            const known = Array.from(segmented.querySelectorAll<HTMLButtonElement>('button[data-value]')).some(
                (button) => button.dataset.value === saved,
            );
            if (saved !== null && known && saved !== root.dataset.branch) applyBranch(saved, false);
        }
    }

    // ── Product detail (spec §7) ────────────────────────────────────────────────────────────────────────────
    const dialog = document.getElementById('menu-detail') as HTMLDialogElement | null;
    let pushed = false;
    const fill = (card: HTMLElement): void => {
        if (dialog === null) return;
        const title = dialog.querySelector<HTMLElement>('.ui-dialog__title');
        const name = card.querySelector<HTMLElement>('.ui-product-card__name span');
        if (title !== null && name !== null) {
            title.textContent = name.textContent;
            if (name.lang !== '') title.lang = name.lang;
            else title.removeAttribute('lang');
        }
        const secondary = card.querySelector<HTMLElement>('.ui-product-card__secondary');
        const secondaryTarget = dialog.querySelector<HTMLElement>('[data-detail-secondary]');
        if (secondaryTarget !== null) {
            secondaryTarget.textContent = secondary?.textContent ?? '';
            secondaryTarget.hidden = secondary === null;
            if (secondary?.lang) secondaryTarget.lang = secondary.lang;
        }
        const location = dialog.querySelector<HTMLElement>('[data-detail-location]');
        if (location !== null) location.textContent = card.dataset.location ?? '';
        const description = dialog.querySelector<HTMLElement>('[data-detail-description]');
        if (description !== null) {
            description.textContent = card.dataset.description ?? '';
            description.hidden = (card.dataset.description ?? '') === '';
        }
        const picture = card.querySelector('.ui-card__media picture');
        const media = dialog.querySelector<HTMLElement>('[data-detail-media]');
        if (media !== null) {
            media.replaceChildren(...(picture !== null ? [picture.cloneNode(true)] : []));
            media.hidden = picture === null;
        }
        const price = card.querySelector<HTMLElement>('.ui-product-card__price');
        const priceTarget = dialog.querySelector<HTMLElement>('[data-detail-price]');
        if (priceTarget !== null) priceTarget.replaceChildren(...(price !== null ? [price.cloneNode(true)] : []));
    };
    const open = (card: HTMLElement, push: boolean): void => {
        if (dialog === null) return;
        fill(card);
        if (!dialog.open) dialog.showModal();
        if (push && window.location.hash !== `#${card.id}`) {
            window.history.pushState({ menuDetail: card.id }, '', `#${card.id}`);
            pushed = true;
        }
    };
    root.addEventListener(
        'click',
        (event) => {
            const action = (event.target as Element).closest<HTMLElement>('.ui-product-card__action');
            const card = action?.closest<HTMLElement>('.ui-product-card');
            if (action === null || action === undefined || card === null || card === undefined || dialog === null)
                return;
            event.preventDefault();
            event.stopPropagation();
            open(card, true);
        },
        true,
    );
    dialog?.addEventListener('close', () => {
        if (pushed) {
            pushed = false;
            window.history.back();
        }
    });
    window.addEventListener('popstate', () => {
        if (dialog?.open === true && !window.location.hash.startsWith('#p-')) {
            pushed = false;
            dialog.close();
        }
    });
    // A shared link to a product (#p-…) scrolls to it, then opens its details (no share button — CF-04).
    if (window.location.hash.startsWith('#p-')) {
        const card = document.getElementById(window.location.hash.slice(1));
        if (card !== null && card.classList.contains('ui-product-card')) {
            card.scrollIntoView({ block: 'center' });
            open(card, false);
        }
    }

    // ── Current category in the category bar ────────────────────────────────────────────────────────────────
    const links = new Map(
        Array.from(root.querySelectorAll<HTMLElement>('[data-ui-menu-nav]')).map((link) => [
            link.dataset.uiMenuNav ?? '',
            link,
        ]),
    );
    if ('IntersectionObserver' in window && links.size > 0) {
        const observer = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (!entry.isIntersecting) continue;
                    links.forEach((link, id) => {
                        if (id === entry.target.id) link.setAttribute('aria-current', 'true');
                        else link.removeAttribute('aria-current');
                    });
                }
            },
            { rootMargin: '-30% 0px -60% 0px' },
        );
        sections.forEach((section) => observer.observe(section));
    }
}
