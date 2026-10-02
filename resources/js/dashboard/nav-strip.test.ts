import { describe, expect, it } from 'vitest';
import { hiddenEdges } from './nav-strip';

describe('dashboard navigation row edges', () => {
    it('fades only the far edge at the start of the row', () => {
        expect(hiddenEdges({ scrollLeft: 0, scrollWidth: 1200, clientWidth: 300 })).toEqual({
            before: false,
            after: true,
        });
    });

    it('fades both edges in the middle, in either direction (RTL rows scroll to negative offsets)', () => {
        expect(hiddenEdges({ scrollLeft: 400, scrollWidth: 1200, clientWidth: 300 })).toEqual({
            before: true,
            after: true,
        });
        expect(hiddenEdges({ scrollLeft: -400, scrollWidth: 1200, clientWidth: 300 })).toEqual({
            before: true,
            after: true,
        });
    });

    it('fades only the near edge at the end of the row (a fraction of a pixel short still counts as the end)', () => {
        expect(hiddenEdges({ scrollLeft: -900, scrollWidth: 1200, clientWidth: 300 })).toEqual({
            before: true,
            after: false,
        });
        expect(hiddenEdges({ scrollLeft: 899.5, scrollWidth: 1200, clientWidth: 300 })).toEqual({
            before: true,
            after: false,
        });
    });

    it('fades nothing when everything fits (the desktop side column)', () => {
        expect(hiddenEdges({ scrollLeft: 0, scrollWidth: 240, clientWidth: 240 })).toEqual({
            before: false,
            after: false,
        });
    });
});
