import { describe, expect, it } from 'vitest';
import examples from '../../../tests/fixtures/search-normalization.json';
import { matches, normalize, withinOneEdit } from './search';

describe('normalize (Menu IA §8)', () => {
    it('removes diacritics and tatweel and unifies alef, taa marbuta and alef maqsura', () => {
        expect(normalize('قَهْوَة')).toBe('قهوه');
        expect(normalize('إسبريسـو')).toBe('اسبريسو');
        expect(normalize('آيس')).toBe('ايس');
        expect(normalize('شاي مصطفى')).toBe('شاي مصطفي');
    });

    it('turns Arabic-Indic digits into Western digits, lowercases and drops symbols', () => {
        expect(normalize('٢ SCOOPS')).toBe('2 scoops');
        expect(normalize('STRAWBERRY + MANGO (SUGAR-FREE)')).toBe('strawberry mango sugar free');
    });
});

describe('withinOneEdit', () => {
    it('accepts one insertion, deletion or substitution', () => {
        expect(withinOneEdit('سبانش', 'سبانيش')).toBe(true);
        expect(withinOneEdit('latte', 'late')).toBe(true);
        expect(withinOneEdit('latte', 'lotte')).toBe(true);
    });

    it('rejects two edits', () => {
        expect(withinOneEdit('latte', 'lote')).toBe(false);
    });
});

describe('matches', () => {
    const terms = ['ICED SPANISH LATTE', 'آيس سبانيش لاتيه'];

    it('matches word prefixes in either language', () => {
        expect(matches('span', terms)).toBe(true);
        expect(matches('lat', terms)).toBe(true);
        expect(matches('سبان', terms)).toBe(true);
        expect(matches('ايس لات', terms)).toBe(true);
    });

    it('tolerates one wrong letter only for words of five letters or more', () => {
        expect(matches('سبانش', terms)).toBe(true);
        expect(matches('spanich', terms)).toBe(true);
        expect(matches('lote', terms)).toBe(false);
    });

    it('needs every query word to match', () => {
        expect(matches('spanish mocha', terms)).toBe(false);
        expect(matches('', terms)).toBe(true);
    });
});

// GS-T1: the same examples file as the PHP normaliser (tests/Unit/SearchNormalizerTest.php).
describe('shared examples (tests/fixtures/search-normalization.json)', () => {
    const normalizeCases = examples.normalize as [string, string][];
    const matchCases = examples.matches as [string, string[], boolean][];

    it.each(normalizeCases)('normalize(%j) = %j', (input, expected) => {
        expect(normalize(input)).toBe(expected);
    });

    it.each(matchCases)('matches(%j, %j) = %j', (query, terms, expected) => {
        expect(matches(query, terms)).toBe(expected);
    });
});
