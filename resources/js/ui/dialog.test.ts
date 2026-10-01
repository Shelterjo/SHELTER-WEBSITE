import { describe, expect, it } from 'vitest';
import { isOutside, supportsClosedBy, supportsInvokerCommands } from './dialog';

describe('dialog backdrop hit test', () => {
    const box = { left: 100, right: 500, top: 50, bottom: 450 };

    it('treats points inside the dialog box (edges included) as inside', () => {
        expect(isOutside(box, 300, 200)).toBe(false);
        expect(isOutside(box, 100, 50)).toBe(false);
        expect(isOutside(box, 500, 450)).toBe(false);
    });

    it('treats points beyond any edge as a backdrop click', () => {
        expect(isOutside(box, 99, 200)).toBe(true);
        expect(isOutside(box, 501, 200)).toBe(true);
        expect(isOutside(box, 300, 49)).toBe(true);
        expect(isOutside(box, 300, 451)).toBe(true);
    });
});

describe('feature detection', () => {
    it('reports no native support when the DOM classes do not exist (fallbacks stay on)', () => {
        expect(supportsInvokerCommands()).toBe(false);
        expect(supportsClosedBy()).toBe(false);
    });
});
