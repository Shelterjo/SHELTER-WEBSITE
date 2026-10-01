import { describe, expect, it } from 'vitest';
import { nextIndex } from './roving';

describe('nextIndex (roving focus)', () => {
    it('moves forward with ArrowRight in LTR and wraps at the end', () => {
        expect(nextIndex(0, 3, 'ArrowRight')).toBe(1);
        expect(nextIndex(2, 3, 'ArrowRight')).toBe(0);
        expect(nextIndex(0, 3, 'ArrowLeft')).toBe(2);
    });

    it('follows the reading direction in RTL: ArrowLeft is forward', () => {
        expect(nextIndex(0, 3, 'ArrowLeft', 'rtl')).toBe(1);
        expect(nextIndex(0, 3, 'ArrowRight', 'rtl')).toBe(2);
        expect(nextIndex(2, 3, 'ArrowLeft', 'rtl')).toBe(0);
    });

    it('jumps to the first and last item with Home and End', () => {
        expect(nextIndex(1, 4, 'Home')).toBe(0);
        expect(nextIndex(1, 4, 'End')).toBe(3);
        expect(nextIndex(1, 4, 'Home', 'rtl')).toBe(0);
    });

    it('uses ArrowUp / ArrowDown for vertical lists and ignores horizontal arrows there', () => {
        expect(nextIndex(0, 3, 'ArrowDown', 'ltr', 'vertical')).toBe(1);
        expect(nextIndex(0, 3, 'ArrowUp', 'rtl', 'vertical')).toBe(2);
        expect(nextIndex(0, 3, 'ArrowRight', 'ltr', 'vertical')).toBeNull();
    });

    it('skips disabled items, also for Home and End', () => {
        const disabled = (i: number) => i === 1 || i === 3;
        expect(nextIndex(0, 4, 'ArrowRight', 'ltr', 'horizontal', disabled)).toBe(2);
        expect(nextIndex(2, 4, 'ArrowRight', 'ltr', 'horizontal', disabled)).toBe(0);
        expect(nextIndex(2, 4, 'End', 'ltr', 'horizontal', disabled)).toBe(2);
        expect(nextIndex(2, 4, 'Home', 'ltr', 'horizontal', (i) => i === 0)).toBe(1);
    });

    it('returns null for keys that do not move focus, empty lists and all-disabled lists', () => {
        expect(nextIndex(0, 3, 'Enter')).toBeNull();
        expect(nextIndex(0, 3, 'Tab')).toBeNull();
        expect(nextIndex(0, 0, 'ArrowRight')).toBeNull();
        expect(nextIndex(0, 2, 'ArrowRight', 'ltr', 'horizontal', () => true)).toBeNull();
    });
});
