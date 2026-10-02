import { describe, expect, it } from 'vitest';
import { barVisible, otherEnabled } from './page';

describe('franchise CTA bar', () => {
    it('shows only once the hero is gone and the application is not yet on screen', () => {
        expect(barVisible(true, false)).toBe(false);
        expect(barVisible(false, false)).toBe(true);
        expect(barVisible(false, true)).toBe(false);
        expect(barVisible(true, true)).toBe(false);
    });
});

describe('franchise interest description', () => {
    it('travels only with the "other" interest', () => {
        expect(otherEnabled('other')).toBe(true);
        expect(otherEnabled('single_location')).toBe(false);
        expect(otherEnabled(null)).toBe(false);
    });
});
