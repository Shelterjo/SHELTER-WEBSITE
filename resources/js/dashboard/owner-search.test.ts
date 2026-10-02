import { describe, expect, it } from 'vitest';
import { searchable, searchUrl } from './owner-search';

describe('dashboard search', () => {
    it('asks the server from two characters, or to clear an empty box', () => {
        expect(searchable('')).toBe(true);
        expect(searchable(' ل ')).toBe(false);
        expect(searchable('لا')).toBe(true);
        expect(searchable('PR')).toBe(true);
    });

    it('builds the results address with the query only, tidied and capped', () => {
        expect(searchUrl('http://x.test/dashboard/search', '  spanish   latte ')).toBe(
            '/dashboard/search?q=spanish+latte',
        );
        expect(searchUrl('http://x.test/dashboard/search', 'لاتيه')).toBe(
            '/dashboard/search?q=%D9%84%D8%A7%D8%AA%D9%8A%D9%87',
        );
        expect(searchUrl('http://x.test/dashboard/search?q=old', '')).toBe('/dashboard/search');
        expect(searchUrl('/dashboard/search', 'x'.repeat(150))).toBe('/dashboard/search?q=' + 'x'.repeat(100));
    });
});
