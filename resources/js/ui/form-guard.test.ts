import { describe, expect, it } from 'vitest';
import { renderedAt } from './form-guard';

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
