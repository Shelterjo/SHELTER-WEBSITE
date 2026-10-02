import { describe, expect, it, vi } from 'vitest';
import { debounce, listUrl, shouldSearch } from './careers-list';

describe('careers list', () => {
    it('searches once typing pauses, not on every key', () => {
        vi.useFakeTimers();
        const run = vi.fn();
        const typed = debounce(run, 400);
        typed('a');
        typed('ab');
        typed('abc');
        vi.advanceTimersByTime(399);
        expect(run).not.toHaveBeenCalled();
        vi.advanceTimersByTime(1);
        expect(run).toHaveBeenCalledTimes(1);
        expect(run).toHaveBeenCalledWith('abc');
        vi.useRealTimers();
    });

    it('waits for two characters, or an empty box', () => {
        expect(shouldSearch('')).toBe(true);
        expect(shouldSearch(' أ ')).toBe(false);
        expect(shouldSearch('أح')).toBe(true);
    });

    it('builds the list address from the form without empty values or the page', () => {
        expect(
            listUrl('http://x.test/dashboard/requests/careers', [
                ['q', 'باريستا'],
                ['status', ''],
                ['page', '3'],
                ['period', 'all'],
            ]),
        ).toBe('/dashboard/requests/careers?q=%D8%A8%D8%A7%D8%B1%D9%8A%D8%B3%D8%AA%D8%A7&period=all');
    });
});
