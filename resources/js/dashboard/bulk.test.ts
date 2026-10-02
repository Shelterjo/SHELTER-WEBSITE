import { describe, expect, it } from 'vitest';
import { countText } from './bulk';

describe('bulk selection', () => {
    const templates = {
        one: 'طلب واحد محدد',
        two: 'طلبان محددان',
        few: ':count طلبات محددة',
        many: ':count طلبًا محددًا',
        other: ':count طلب محدد',
    };

    it('says how many are selected in the page language', () => {
        expect(countText(templates, 1, 'ar')).toBe('طلب واحد محدد');
        expect(countText(templates, 2, 'ar')).toBe('طلبان محددان');
        expect(countText(templates, 5, 'ar')).toBe('5 طلبات محددة');
        expect(countText({ one: 'One selected', other: ':count selected' }, 12, 'en')).toBe('12 selected');
    });
});
