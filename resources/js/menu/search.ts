// Menu search matching (Menu IA spec §8): Arabic + English normalisation, word-prefix matching (an Arabic word is also
// tried without its definite article: "فرنشايز" finds "الفرنشايز"), then one-letter tolerance for words of 5+ letters
// ("سبانش" / "سبانيش"). No invented synonyms. Pure functions — unit tested against the same examples file as the PHP
// twin (App\Services\Content\Search\Normalizer, tests/fixtures/search-normalization.json).

const ARABIC_DIACRITICS = /[ً-ٰٟۖ-ۭ]/g;
const TATWEEL = /ـ/g;
const ARABIC_INDIC_DIGITS = /[٠-٩]/g;
const EASTERN_DIGITS = /[۰-۹]/g;

export function normalize(text: string): string {
    return text
        .normalize('NFKC')
        .replace(ARABIC_DIACRITICS, '')
        .replace(TATWEEL, '')
        .replace(/[أإآٱ]/g, 'ا')
        .replace(/ة/g, 'ه')
        .replace(/ى/g, 'ي')
        .replace(ARABIC_INDIC_DIGITS, (d) => String(d.charCodeAt(0) - 0x0660))
        .replace(EASTERN_DIGITS, (d) => String(d.charCodeAt(0) - 0x06f0))
        .toLowerCase()
        .replace(/[+()\-_/.,:;'"«»]/g, ' ')
        .replace(/\s+/g, ' ')
        .trim();
}

export function words(text: string): string[] {
    const normalized = normalize(text);
    return normalized === '' ? [] : normalized.split(' ');
}

/** True when a and b differ by at most one insertion, deletion or substitution. */
export function withinOneEdit(a: string, b: string): boolean {
    if (a === b) return true;
    if (Math.abs(a.length - b.length) > 1) return false;
    let i = 0;
    let j = 0;
    let edits = 0;
    while (i < a.length && j < b.length) {
        if (a[i] === b[j]) {
            i++;
            j++;
            continue;
        }
        if (++edits > 1) return false;
        if (a.length > b.length) i++;
        else if (a.length < b.length) j++;
        else {
            i++;
            j++;
        }
    }
    return edits + (a.length - i) + (b.length - j) <= 1;
}

function tokenMatches(token: string, candidates: string[]): boolean {
    if (candidates.some((word) => word.startsWith(token))) return true;
    if (token.length < 5) return false;
    // Tolerance: compare with the same-length start of each candidate word (typing in progress) and the whole word.
    return candidates.some((word) => withinOneEdit(token, word) || withinOneEdit(token, word.slice(0, token.length)));
}

/** Each word, plus the same word without a leading "ال" when something is left after it. */
function withoutArticle(list: string[]): string[] {
    return [...list, ...list.filter((word) => word.startsWith('ال') && word.length > 3).map((word) => word.slice(2))];
}

/** Every query word must match the start of some word of the item (any of its names / search terms). */
export function matches(query: string, terms: string[]): boolean {
    const tokens = words(query);
    if (tokens.length === 0) return true;
    const candidates = withoutArticle(terms.flatMap(words));
    return tokens.every((token) => tokenMatches(token, candidates));
}
