// Dialog triggers for <x-ui.modal | drawer | bottom-sheet> (native <dialog>). Modern browsers open them through Invoker
// Commands (commandfor + command="show-modal") and close them on a backdrop click (closedby="any") with no script; this
// module adds both behaviours only where the browser lacks them. Esc, the focus trap and focus return are native.

const installed = new WeakSet<Document>();

interface Box {
    readonly left: number;
    readonly right: number;
    readonly top: number;
    readonly bottom: number;
}

/** True when a click at (x, y) lands outside the dialog box, i.e. on its ::backdrop. */
export function isOutside(box: Box, x: number, y: number): boolean {
    return x < box.left || x > box.right || y < box.top || y > box.bottom;
}

export function supportsInvokerCommands(): boolean {
    return typeof HTMLButtonElement !== 'undefined' && 'commandForElement' in HTMLButtonElement.prototype;
}

export function supportsClosedBy(): boolean {
    return typeof HTMLDialogElement !== 'undefined' && 'closedBy' in HTMLDialogElement.prototype;
}

/** aria-expanded on a button that opens a dialog follows the dialog's state (FINAL-QA QA-017). */
function syncExpanded(doc: Document): void {
    if (typeof MutationObserver === 'undefined') return;
    doc.querySelectorAll<HTMLElement>('[aria-controls][aria-expanded]').forEach((trigger) => {
        const dialog = doc.getElementById(trigger.getAttribute('aria-controls') ?? '');
        if (!(dialog instanceof HTMLDialogElement)) return;
        const sync = (): void => trigger.setAttribute('aria-expanded', String(dialog.open));
        new MutationObserver(sync).observe(dialog, { attributes: true, attributeFilter: ['open'] });
        sync();
    });
}

export function installDialogs(doc: Document = document): void {
    if (installed.has(doc)) return;
    installed.add(doc);
    syncExpanded(doc);
    if (!supportsInvokerCommands()) {
        doc.addEventListener('click', (event) => {
            if (!(event.target instanceof Element)) return;
            const trigger = event.target.closest<HTMLElement>('[data-ui-dialog-open]');
            const dialog = trigger ? doc.getElementById(trigger.dataset['uiDialogOpen'] ?? '') : null;
            if (dialog instanceof HTMLDialogElement && !dialog.open) {
                event.preventDefault();
                dialog.showModal();
            }
        });
    }
    if (!supportsClosedBy()) {
        doc.addEventListener('click', (event) => {
            const dialog = event.target;
            if (!(dialog instanceof HTMLDialogElement) || !dialog.open || !dialog.hasAttribute('data-ui-dialog'))
                return;
            if (isOutside(dialog.getBoundingClientRect(), event.clientX, event.clientY)) dialog.close();
        });
    }
}
