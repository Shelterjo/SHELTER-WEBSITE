// Buttons and links marked aria-disabled="true" (loading buttons keep focus, disabled links keep their name) must not
// activate: one capture-phase listener cancels the click, which also cancels implicit form submission and commands.

const installed = new WeakSet<Document>();

export function installAriaDisabledGuard(doc: Document = document): void {
    if (installed.has(doc)) return;
    installed.add(doc);
    doc.addEventListener(
        'click',
        (event) => {
            if (event.target instanceof Element && event.target.closest('[aria-disabled="true"]')) {
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        },
        true,
    );
}
