import { describe, expect, it } from 'vitest';
import { startsBelowFold, toBezier, toSeconds } from './reveal';

describe('motion token parsing', () => {
    it('reads milliseconds and seconds', () => {
        expect(toSeconds('700ms', 0.6)).toBe(0.7);
        expect(toSeconds(' 0.45s ', 0.6)).toBe(0.45);
    });

    it('falls back for missing or unexpected values', () => {
        expect(toSeconds('', 0.6)).toBe(0.6);
        expect(toSeconds('var(--x)', 0.6)).toBe(0.6);
    });
});

describe('reveal targets', () => {
    it('prepares only elements that start below the fold (no flash for what is already visible)', () => {
        expect(startsBelowFold(900, 844)).toBe(true);
        expect(startsBelowFold(844, 844)).toBe(true);
        expect(startsBelowFold(300, 844)).toBe(false);
    });
});

describe('easing token parsing', () => {
    it('reads the cubic-bezier token the CSS uses', () => {
        expect(toBezier(' cubic-bezier(0, 0, .2, 1) ', [1, 1, 1, 1])).toEqual([0, 0, 0.2, 1]);
    });

    it('falls back for anything else', () => {
        expect(toBezier('ease-out', [0, 0, 0.2, 1])).toEqual([0, 0, 0.2, 1]);
        expect(toBezier('cubic-bezier(1, 2)', [0, 0, 0.2, 1])).toEqual([0, 0, 0.2, 1]);
    });
});
