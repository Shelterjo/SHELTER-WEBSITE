// Several applications at once (CAREERS-068): "select all on this page", a live count of the selection, and the print
// button of the PDF view. Everything here is an enhancement — without it the rows can still be ticked one by one.

/** "3 selected" in the page language, from the message templates the page provides ({0}, {1}, {n} …:count). */
export function countText(templates: Record<string, string>, count: number, locale: string): string {
    const category = count === 0 ? 'zero' : new Intl.PluralRules(locale).select(count);
    const template = templates[category] ?? templates['other'] ?? '';
    return template.replace(':count', new Intl.NumberFormat(locale).format(count));
}

export function installBulk(form: HTMLFormElement): void {
    const all = document.querySelector<HTMLInputElement>('input[data-select-all]');
    const items = Array.from(document.querySelectorAll<HTMLInputElement>('input[data-select-item]'));
    const status = form.querySelector<HTMLElement>('[data-bulk-count]');
    const locale = document.documentElement.lang || 'ar';
    let templates: Record<string, string> = {};
    try {
        templates = JSON.parse(form.dataset.bulkForms ?? '{}') as Record<string, string>;
    } catch {
        templates = {};
    }

    const refresh = (): void => {
        const selected = items.filter((item) => item.checked).length;
        if (all !== null) {
            all.checked = selected > 0 && selected === items.length;
            all.indeterminate = selected > 0 && selected < items.length;
        }
        if (status !== null) status.textContent = selected === 0 ? '' : countText(templates, selected, locale);
    };
    all?.addEventListener('change', () => {
        items.forEach((item) => {
            item.checked = all.checked;
        });
        refresh();
    });
    items.forEach((item) => item.addEventListener('change', refresh));
    refresh();
}

export function installPrint(): void {
    document.querySelectorAll<HTMLButtonElement>('button[data-print]').forEach((button) => {
        button.addEventListener('click', () => window.print());
    });
}
