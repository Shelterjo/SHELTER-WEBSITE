// Keeps every branch open-state line current in an open tab (HOURS-009…011). The server computes the segments in the
// market timezone (App\Services\Site\StatusTimeline) and renders the current one; this script only moves to the next
// segment when its time comes and counts "closes in N min" down each minute, with the same CLDR plural templates the
// server uses. Past the last known segment the line is hidden: never an unconfirmed "open" (SPEC §20). No hours logic.

export interface Segment {
    /** epoch ms when the next segment starts; null = no known change */
    u: number | null;
    s: string;
    t: string;
    /** epoch ms of the closing time, only for "closing soon" */
    c: number | null;
}

export interface Timeline {
    segments: Segment[];
    closing: Partial<Record<Intl.LDMLPluralRule, string>>;
}

export function currentSegment(timeline: Timeline, now: number): Segment | null {
    return timeline.segments.find((segment) => segment.u === null || now < segment.u) ?? null;
}

export function minutesLeft(closesAt: number, now: number): number {
    return Math.max(1, Math.ceil((closesAt - now) / 60_000));
}

export function segmentText(segment: Segment, timeline: Timeline, now: number, locale: string): string {
    if (segment.c === null) {
        return segment.t;
    }
    const minutes = minutesLeft(segment.c, now);
    const template = timeline.closing[new Intl.PluralRules(locale).select(minutes)] ?? timeline.closing.other ?? '';

    return template.replace(':n', String(minutes));
}

export function parseTimeline(json: string | undefined): Timeline | null {
    if (json === undefined || json === '') {
        return null;
    }
    try {
        const data = JSON.parse(json) as Partial<Timeline>;

        return Array.isArray(data.segments) && data.segments.length > 0
            ? { segments: data.segments, closing: data.closing ?? {} }
            : null;
    } catch {
        return null;
    }
}

function render(element: HTMLElement, timeline: Timeline, now: number, locale: string): void {
    const segment = currentSegment(timeline, now);
    if (segment === null) {
        element.hidden = true;

        return;
    }
    element.dataset.state = segment.s;
    const text = element.querySelector<HTMLElement>('.ui-open-status__text');
    const value = segmentText(segment, timeline, now, locale);
    if (text !== null && text.textContent !== value) {
        text.textContent = value;
    }
}

export function installOpenStatus(root: ParentNode = document): void {
    const locale = document.documentElement.lang || 'ar';
    const items = [...root.querySelectorAll<HTMLElement>('[data-ui-open-status]')].flatMap((element) => {
        const timeline = parseTimeline(element.dataset.uiOpenStatus);

        return timeline === null ? [] : [{ element, timeline }];
    });
    if (items.length === 0) {
        return;
    }
    let timer = 0;
    const tick = (): void => {
        window.clearTimeout(timer);
        const now = Date.now();
        for (const { element, timeline } of items) {
            render(element, timeline, now, locale);
        }
        // Next run on the next minute boundary (+ a little), when a countdown or a segment can change.
        timer = window.setTimeout(tick, 60_000 - (now % 60_000) + 250);
    };
    tick();
    // A tab that slept or a page restored from the back/forward cache catches up at once.
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            tick();
        }
    });
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            tick();
        }
    });
}
