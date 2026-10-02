// A link to one FAQ answer (#q-N — site search) opens that answer: the target <details> is expanded on load and on
// every later hash change. Without JavaScript the link still lands on the question.

/** The id named by a URL fragment ("#q-2" → "q-2"); null when there is none. */
export function fragmentId(hash: string): string | null {
    if (hash.length < 2) return null;
    try {
        return decodeURIComponent(hash.slice(1));
    } catch {
        return null;
    }
}

export function installHashDisclosure(doc: Document = document): void {
    const view = doc.defaultView;
    if (view === null) return;
    const open = (): void => {
        const id = fragmentId(view.location.hash);
        const target = id === null ? null : doc.getElementById(id);
        if (target instanceof view.HTMLDetailsElement) target.open = true;
    };
    open();
    view.addEventListener('hashchange', open);
}
