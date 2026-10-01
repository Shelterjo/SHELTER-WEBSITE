// Roving-focus math for composite widgets (ARIA APG): tabs today, any list of options later.
// Pure function, no DOM: unit-tested in roving.test.ts.

export type Direction = 'ltr' | 'rtl';
export type Orientation = 'horizontal' | 'vertical';

/**
 * Index that receives focus after `key` in a list of `count` items, or null when the key does not move focus.
 * Horizontal lists follow the reading direction: in RTL, ArrowLeft moves forward. Arrows wrap around; Home and End
 * jump to the first and last enabled item. Disabled items are skipped.
 */
export function nextIndex(
    current: number,
    count: number,
    key: string,
    dir: Direction = 'ltr',
    orientation: Orientation = 'horizontal',
    isDisabled: (index: number) => boolean = () => false,
): number | null {
    if (count <= 0) return null;
    const forward = orientation === 'vertical' ? 'ArrowDown' : dir === 'rtl' ? 'ArrowLeft' : 'ArrowRight';
    const backward = orientation === 'vertical' ? 'ArrowUp' : dir === 'rtl' ? 'ArrowRight' : 'ArrowLeft';
    let start: number;
    let step: number;
    switch (key) {
        case forward:
            start = current + 1;
            step = 1;
            break;
        case backward:
            start = current - 1;
            step = -1;
            break;
        case 'Home':
            start = 0;
            step = 1;
            break;
        case 'End':
            start = count - 1;
            step = -1;
            break;
        default:
            return null;
    }
    for (let tries = 0; tries < count; tries++) {
        const index = (((start + step * tries) % count) + count) % count;
        if (!isDisabled(index)) return index;
    }
    return null;
}
