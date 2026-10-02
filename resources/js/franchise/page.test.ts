import { describe, expect, it } from 'vitest';
import { barVisible } from './page';

describe('franchise CTA bar', () => {
    it('shows only once the hero is gone and the application is not yet on screen', () => {
        expect(barVisible(true, false)).toBe(false);
        expect(barVisible(false, false)).toBe(true);
        expect(barVisible(false, true)).toBe(false);
        expect(barVisible(true, true)).toBe(false);
    });
});
