import { describe, expect, it } from 'vitest';
import { currentSegment, minutesLeft, parseTimeline, segmentText, type Timeline } from './open-status';

// 2026-10-02 21:00 Amman = 18:00 UTC; DRIVE-like day: open until 01:00, closing soon from 00:00, then closed.
const at = (iso: string): number => Date.parse(iso);
const timeline: Timeline = {
    segments: [
        { u: at('2026-10-02T21:00:00Z'), s: 'open', t: 'مفتوح الآن — حتى 1:00 ص', c: null },
        { u: at('2026-10-02T22:00:00Z'), s: 'closing', t: '', c: at('2026-10-02T22:00:00Z') },
        { u: at('2026-10-03T04:00:00Z'), s: 'closed', t: 'مغلق الآن — يفتح 7:00 ص', c: null },
    ],
    closing: {
        zero: 'يغلق الآن',
        one: 'يغلق بعد دقيقة',
        two: 'يغلق بعد دقيقتين',
        few: 'يغلق بعد :n دقائق',
        many: 'يغلق بعد :n دقيقة',
        other: 'يغلق بعد :n دقيقة',
    },
};

describe('open-state segments', () => {
    it('keeps the rendered segment until its time, then moves on', () => {
        expect(currentSegment(timeline, at('2026-10-02T20:59:59Z'))?.s).toBe('open');
        expect(currentSegment(timeline, at('2026-10-02T21:00:00Z'))?.s).toBe('closing');
        expect(currentSegment(timeline, at('2026-10-02T22:00:00Z'))?.s).toBe('closed');
    });

    it('returns nothing past the last known segment (the line is hidden, never a guessed "open")', () => {
        expect(currentSegment(timeline, at('2026-10-03T04:00:00Z'))).toBeNull();
        const open = { segments: [{ u: null, s: 'closed', t: 'مغلق الآن', c: null }], closing: {} };
        expect(currentSegment(open, at('2030-01-01T00:00:00Z'))?.t).toBe('مغلق الآن');
    });
});

describe('closing-soon countdown', () => {
    it('rounds up like the server and never shows zero', () => {
        expect(minutesLeft(at('2026-10-02T22:00:00Z'), at('2026-10-02T21:59:30Z'))).toBe(1);
        expect(minutesLeft(at('2026-10-02T22:00:00Z'), at('2026-10-02T22:00:00Z'))).toBe(1);
        expect(minutesLeft(at('2026-10-02T22:00:00Z'), at('2026-10-02T21:00:00Z'))).toBe(60);
    });

    it('picks the Arabic plural template the server would pick (App\\Support\\PluralCategory)', () => {
        const closing = timeline.segments[1];
        expect(closing).toBeDefined();
        if (closing === undefined) return;
        const text = (iso: string): string => segmentText(closing, timeline, at(iso), 'ar');
        expect(text('2026-10-02T21:59:30Z')).toBe('يغلق بعد دقيقة');
        expect(text('2026-10-02T21:58:00Z')).toBe('يغلق بعد دقيقتين');
        expect(text('2026-10-02T21:55:00Z')).toBe('يغلق بعد 5 دقائق');
        expect(text('2026-10-02T21:35:00Z')).toBe('يغلق بعد 25 دقيقة');
    });

    it('uses English one/other', () => {
        const closing = { u: 0, s: 'closing', t: '', c: at('2026-10-02T22:00:00Z') };
        const en: Timeline = { segments: [closing], closing: { one: 'Closes in 1 min', other: 'Closes in :n min' } };
        expect(segmentText(closing, en, at('2026-10-02T21:59:10Z'), 'en')).toBe('Closes in 1 min');
        expect(segmentText(closing, en, at('2026-10-02T21:50:00Z'), 'en')).toBe('Closes in 10 min');
    });

    it('shows the fixed text of a segment without a countdown', () => {
        const open = timeline.segments[0];
        expect(open).toBeDefined();
        if (open === undefined) return;
        expect(segmentText(open, timeline, at('2026-10-02T20:00:00Z'), 'ar')).toBe('مفتوح الآن — حتى 1:00 ص');
    });
});

describe('timeline data', () => {
    it('reads the server JSON and refuses anything malformed', () => {
        expect(parseTimeline(JSON.stringify(timeline))?.segments).toHaveLength(3);
        expect(parseTimeline('{"segments":[]}')).toBeNull();
        expect(parseTimeline('not json')).toBeNull();
        expect(parseTimeline(undefined)).toBeNull();
    });
});
