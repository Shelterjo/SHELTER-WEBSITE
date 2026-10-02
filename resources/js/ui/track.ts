// Privacy-safe measurement hooks (GA4-MEASUREMENT-PLAN event names, M57 §47). Events go to window.dataLayer only
// when a tag manager has already created it: this file never creates it, loads nothing and sends nothing by itself.
// No personal data: never a phone number, a message, an address typed by a visitor or the text of a search.

type Params = Record<string, string | number>;

declare global {
    interface Window {
        dataLayer?: unknown[];
    }
}

export type ContactEvent = 'phone_click' | 'whatsapp_click' | 'directions_click';

const MAP_HOSTS = new Set([
    'maps.app.goo.gl',
    'share.google',
    'goo.gl',
    'maps.google.com',
    'www.google.com',
    'google.com',
    'www.google.jo',
    'google.jo',
]);

export function track(event: string, params: Params = {}): void {
    const layer = window.dataLayer;
    if (!Array.isArray(layer)) return;
    layer.push({ event, ...params });
}

/** Which contact action a link is, from its address alone (tel:, WhatsApp, a Google Maps place or link). */
export function classify(href: string, base: string = window.location.href): ContactEvent | null {
    if (href.startsWith('tel:')) return 'phone_click';
    let url: URL;
    try {
        url = new URL(href, base);
    } catch {
        return null;
    }
    if (url.hostname === 'wa.me' || url.hostname === 'api.whatsapp.com') return 'whatsapp_click';
    if (MAP_HOSTS.has(url.hostname)) {
        const mapsHost =
            url.hostname === 'maps.app.goo.gl' ||
            url.hostname === 'share.google' ||
            url.hostname === 'maps.google.com' ||
            url.hostname === 'goo.gl';
        if (mapsHost || url.pathname.startsWith('/maps')) return 'directions_click';
    }
    return null;
}

/** Where the element sits: the page language, the branch, the placement and the phone's purpose when marked. */
function context(element: Element): Params {
    const params: Params = { language: document.documentElement.lang || 'ar' };
    const branch = element.closest<HTMLElement>('[data-track-branch]')?.dataset.trackBranch;
    if (branch !== undefined && branch !== '') params.branch_id = branch;
    const placement = element.closest<HTMLElement>('[data-track-placement]')?.dataset.trackPlacement;
    if (placement !== undefined && placement !== '') params.placement = placement;
    const purpose = element.closest<HTMLElement>('[data-track-purpose]')?.dataset.trackPurpose;
    if (purpose !== undefined && purpose !== '') params.phone_purpose = purpose;
    return params;
}

export function installTracking(root: Document = document): void {
    const view = root.querySelector<HTMLElement>('[data-track-view]');
    const name = view?.dataset.trackView;
    if (view !== null && name !== undefined && name !== '') track(name, context(view));

    root.addEventListener(
        'click',
        (event) => {
            const target = event.target;
            const link = target instanceof Element ? target.closest('a[href]') : null;
            if (!(link instanceof HTMLAnchorElement)) return;
            const contact = classify(link.getAttribute('href') ?? '');
            if (contact !== null) {
                track(contact, context(link));
                return;
            }
            const from = document.documentElement.lang;
            const to = link.hreflang;
            if (to !== '' && from !== '' && to !== from && to !== 'x-default') track('language_switch', { from, to });
        },
        { capture: true },
    );
}
