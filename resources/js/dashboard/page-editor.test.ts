import { describe, expect, it } from 'vitest';
import { fillTemplate, nextIndex } from './page-editor';

describe('page editor', () => {
    it('fills the blank section template', () => {
        expect(fillTemplate('sections[__INDEX__][type] · __N__ · s__INDEX__-type', 7, 4)).toBe(
            'sections[7][type] · 4 · s7-type',
        );
    });

    it('never reuses a form index', () => {
        expect(nextIndex([])).toBe(0);
        expect(nextIndex([0, 1, 5])).toBe(6);
    });
});
