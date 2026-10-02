import { describe, expect, it } from 'vitest';
import { opensElsewhere, renderedAt } from './form-guard';

describe('double-send guard', () => {
    it('lets a preview that opens in a new tab through without blocking the save after it', () => {
        const preview = { getAttribute: (name: string) => (name === 'formtarget' ? '_blank' : null) };
        expect(opensElsewhere(preview)).toBe(true);
    });

    it('counts every other button (and the Enter key, no button) as a send', () => {
        expect(opensElsewhere({ getAttribute: () => null })).toBe(false);
        expect(opensElsewhere(null)).toBe(false);
    });
});

describe('stale-edit moment', () => {
    it('takes the nearest rendered moment (a panel loaded later wins over the page)', () => {
        const panel = { dataset: { uiRenderedAt: '1790000100' } };
        const form = { closest: () => panel } as unknown as Element;
        expect(renderedAt(form)).toBe('1790000100');
    });

    it('has none outside a dashboard page', () => {
        const form = { closest: () => null } as unknown as Element;
        expect(renderedAt(form)).toBeNull();
    });
});
