// Dashboard saves (FINAL-QA QA-044): every POST form carries the moment its page was rendered (`_seen_at`), so the
// server can refuse a save from an old tab instead of silently overwriting a newer change; and a second click on the
// same form within a few seconds is ignored, so a double click never sends the same save twice.

const SEEN_FIELD = '_seen_at';
const RESUBMIT_MS = 5000;

/** The render moment for a form: its own fragment's (a panel loaded later) or the page's. */
export function renderedAt(form: Element): string | null {
    return form.closest<HTMLElement>('[data-ui-rendered-at]')?.dataset['uiRenderedAt'] ?? null;
}

export function installFormGuard(doc: Document = document): void {
    doc.addEventListener('submit', (event) => {
        const form = event.target;
        // A submit another script already stopped (its own checks) is not a send.
        if (event.defaultPrevented || !(form instanceof HTMLFormElement) || form.method.toLowerCase() !== 'post')
            return;
        const last = Number(form.dataset['uiSubmittedAt'] ?? '0');
        if (Date.now() - last < RESUBMIT_MS) {
            event.preventDefault();
            return;
        }
        form.dataset['uiSubmittedAt'] = String(Date.now());
        const seen = renderedAt(form);
        if (seen === null) return;
        let input = form.querySelector<HTMLInputElement>(`input[name="${SEEN_FIELD}"]`);
        if (input === null) {
            input = doc.createElement('input');
            input.type = 'hidden';
            input.name = SEEN_FIELD;
            form.append(input);
        }
        input.value = seen;
    });
}
