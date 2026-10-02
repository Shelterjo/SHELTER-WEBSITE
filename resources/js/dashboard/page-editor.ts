// Page editor (dashboard): "Add a section" clones the blank section template, and up/down buttons move a section and
// renumber the order fields. Progressive: without JavaScript the blank slot and the order numbers do the same job.

/** Fills the template placeholders for the new section's form index and on-screen number. */
export function fillTemplate(html: string, index: number, number: number): string {
    return html.replaceAll('__INDEX__', String(index)).replaceAll('__N__', String(number));
}

/** The next free form index after the ones in use (indexes are never reused while the page is open). */
export function nextIndex(used: number[]): number {
    return used.length === 0 ? 0 : Math.max(...used) + 1;
}

export function installPageEditor(form: HTMLFormElement): void {
    const list = form.querySelector<HTMLElement>('[data-sections]');
    const template = form.querySelector<HTMLTemplateElement>('template[data-section-template]');
    if (list === null || template === null) return;

    const cards = (): HTMLElement[] => Array.from(list.querySelectorAll<HTMLElement>(':scope > [data-section]'));
    const renumber = (): void => {
        cards().forEach((card, position) => {
            const sort = card.querySelector<HTMLInputElement>('[data-section-sort]');
            if (sort !== null) sort.value = String(position + 1);
            const number = card.querySelector<HTMLElement>('[data-section-number]');
            if (number !== null) number.textContent = String(position + 1);
        });
    };
    const reveal = (root: ParentNode): void => {
        root.querySelectorAll<HTMLElement>('[data-section-move]').forEach((element) => {
            element.hidden = false;
        });
    };
    reveal(form);

    const addRow = form.querySelector<HTMLElement>('[data-add-section-row]');
    const add = form.querySelector<HTMLButtonElement>('[data-add-section]');
    if (addRow !== null && add !== null) {
        addRow.hidden = false;
        add.addEventListener('click', () => {
            const used = cards().map((card) => Number((card.id.match(/^section-(\d+)$/) ?? [])[1] ?? -1));
            const fragment = template.content.cloneNode(true) as DocumentFragment;
            const card = fragment.firstElementChild;
            if (!(card instanceof HTMLElement)) return;
            const index = nextIndex(used);
            const number = cards().length + 1;
            // The placeholders live in attributes only (names, ids, for, values) — no HTML is parsed from strings.
            for (const element of [card, ...Array.from(card.querySelectorAll('*'))]) {
                for (const attribute of Array.from(element.attributes)) {
                    if (attribute.value.includes('__')) attribute.value = fillTemplate(attribute.value, index, number);
                }
            }
            list.append(card);
            reveal(card);
            renumber();
            card.querySelector<HTMLElement>('input, textarea, select')?.focus();
        });
    }

    list.addEventListener('click', (event) => {
        const button = (event.target as Element | null)?.closest<HTMLButtonElement>('[data-move]');
        const card = button?.closest<HTMLElement>('[data-section]');
        if (button === undefined || button === null || card === undefined || card === null) return;
        const sibling = button.dataset.move === 'up' ? card.previousElementSibling : card.nextElementSibling;
        if (!(sibling instanceof HTMLElement) || !sibling.matches('[data-section]')) return;
        if (button.dataset.move === 'up') sibling.before(card);
        else sibling.after(card);
        renumber();
        button.focus();
    });
}
