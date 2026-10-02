import { describe, expect, it } from 'vitest';
import { fragmentId } from './hash-disclosure';

describe('hash disclosure', () => {
    it('reads the id from a URL fragment', () => {
        expect(fragmentId('#q-2')).toBe('q-2');
        expect(fragmentId('#')).toBeNull();
        expect(fragmentId('')).toBeNull();
        expect(fragmentId('#%E0%A4%A')).toBeNull();
    });
});
