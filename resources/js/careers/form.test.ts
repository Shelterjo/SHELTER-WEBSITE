import { describe, expect, it } from 'vitest';
import { withName } from './form';

describe('careers form helpers', () => {
    it('gives every remove button the file name in its accessible name', () => {
        expect(withName('إزالة :name', 'cv.pdf')).toBe('إزالة cv.pdf');
        expect(withName('Remove', 'cv.pdf')).toBe('Remove');
    });
});
