// Phones: the dashboard navigation is one scrolling row (UX-006 DR-18). Its edges fade only where more items wait
// beyond them (data-more-before / data-more-after, read by shell.css), and the current screen's item starts in the
// middle. Right-to-left rows report a negative scrollLeft, so the offset is read as a distance from the start.

interface StripMetrics {
    readonly scrollLeft: number;
    readonly scrollWidth: number;
    readonly clientWidth: number;
}

/** Whether items are hidden before the visible part of the row and after it, in reading order. */
export function hiddenEdges(strip: StripMetrics): { before: boolean; after: boolean } {
    const fromStart = Math.abs(strip.scrollLeft);
    const room = strip.scrollWidth - strip.clientWidth;
    // One pixel of slack: zoomed and high-density screens stop a fraction short of either end.
    return { before: fromStart > 1, after: room - fromStart > 1 };
}

export function installNavStrip(strip: HTMLElement): void {
    const update = (): void => {
        const { before, after } = hiddenEdges(strip);
        strip.toggleAttribute('data-more-before', before);
        strip.toggleAttribute('data-more-after', after);
    };
    const current = strip.querySelector<HTMLElement>('[aria-current="page"]');
    if (current && strip.scrollWidth > strip.clientWidth) {
        current.scrollIntoView({ behavior: 'instant', block: 'nearest', inline: 'center' });
    }
    strip.addEventListener('scroll', update, { passive: true });
    if (typeof ResizeObserver !== 'undefined') new ResizeObserver(update).observe(strip);
    strip.setAttribute('data-ready', '');
    update();
}
