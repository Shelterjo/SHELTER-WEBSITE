// Franchise page (SI-B12, UX-F-03). On phones a compact CTA bar slides in once the hero's own CTA has scrolled away
// and steps aside while the application (or the inquiries contact) is on screen; the "other" description is sent only
// while "other" is the chosen interest (PF-06 — no stale hidden value); the submit button cannot be pressed twice.
// Without JavaScript the bar simply stays (it is only a link to #apply), CSS shows the description, the server drops a
// stale one and the form posts normally.

/** The bar shows only between the hero and the place it leads to. */
export function barVisible(heroInView: boolean, targetInView: boolean): boolean {
    return !heroInView && !targetInView;
}

/** The description travels only with the "other" interest. */
export function otherEnabled(selected: string | null): boolean {
    return selected === 'other';
}

export function installFranchisePage(root: HTMLElement): void {
    const bar = root.querySelector<HTMLElement>('[data-franchise-bar]');
    const hero = root.querySelector<HTMLElement>('[data-franchise-hero]');
    // The bar leads where its link points: the application when open, otherwise the inquiries contact.
    const href = bar?.querySelector('a')?.getAttribute('href') ?? '';
    const target = href.startsWith('#') && href.length > 1 ? root.querySelector<HTMLElement>(href) : null;
    if (bar !== null && hero !== null && 'IntersectionObserver' in window) {
        const seen = new Map<Element, boolean>([[hero, true]]);
        const update = (): void => {
            const shown = barVisible(seen.get(hero) === true, target !== null && seen.get(target) === true);
            bar.dataset.state = shown ? 'shown' : 'hidden';
        };
        update();
        const observer = new IntersectionObserver((entries) => {
            for (const entry of entries) seen.set(entry.target, entry.isIntersecting);
            update();
        });
        observer.observe(hero);
        if (target !== null) observer.observe(target);
    }

    const form = root.querySelector<HTMLFormElement>('form[data-franchise-form]');
    const submit = form?.querySelector<HTMLButtonElement>('[data-franchise-submit]') ?? null;
    if (form === null || submit === null) return;

    const other = form.querySelector<HTMLTextAreaElement>('[data-franchise-other]');
    if (other !== null) {
        const sync = (): void => {
            const chosen = form.querySelector<HTMLInputElement>('input[name="partnership_interest_type"]:checked');
            other.disabled = !otherEnabled(chosen?.value ?? null);
        };
        form.addEventListener('change', (event) => {
            if (event.target instanceof HTMLInputElement && event.target.name === 'partnership_interest_type') sync();
        });
        sync();
    }
    form.addEventListener('submit', (event) => {
        if (submit.disabled) {
            event.preventDefault();
            return;
        }
        submit.disabled = true;
        submit.setAttribute('aria-busy', 'true');
        submit.textContent = form.dataset.labelSubmitting ?? submit.textContent;
    });
}
