import { afterEach, describe, expect, it, vi } from 'vitest';
import { classify, track } from './track';

const BASE = 'https://www.shelterjo.com/ar/jo/locations/irbid/drive/';

describe('contact link classification', () => {
    it('names call, WhatsApp and directions links by their address alone', () => {
        expect(classify('tel:+962799009436', BASE)).toBe('phone_click');
        expect(classify('https://wa.me/962799009436', BASE)).toBe('whatsapp_click');
        expect(classify('https://maps.app.goo.gl/abc123', BASE)).toBe('directions_click');
        expect(classify('https://share.google/Cko3RPFoBGY21bco4', BASE)).toBe('directions_click');
        expect(classify('https://www.google.com/maps/place/x', BASE)).toBe('directions_click');
        expect(classify('https://maps.google.com/?q=1,2', BASE)).toBe('directions_click');
    });

    it('ignores every other link, including Google pages that are not Maps', () => {
        expect(classify('/ar/jo/menu/?branch=drive', BASE)).toBeNull();
        expect(classify('https://www.google.com/search?q=shelter', BASE)).toBeNull();
        expect(classify('mailto:info@example.test', BASE)).toBeNull();
        expect(classify('https://wa.me.example.test/1', BASE)).toBeNull();
    });
});

describe('dataLayer push', () => {
    afterEach(() => {
        vi.unstubAllGlobals();
    });

    it('does nothing without a tag manager (never creates the dataLayer)', () => {
        const fakeWindow: { dataLayer?: unknown[] } = {};
        vi.stubGlobal('window', fakeWindow);
        track('branch_view', { branch_id: 'drive' });
        expect(fakeWindow.dataLayer).toBeUndefined();
    });

    it('pushes the event and its parameters when the dataLayer exists', () => {
        const fakeWindow = { dataLayer: [] as unknown[] };
        vi.stubGlobal('window', fakeWindow);
        track('directions_click', { branch_id: 'house', placement: 'action_bar' });
        expect(fakeWindow.dataLayer).toEqual([
            { event: 'directions_click', branch_id: 'house', placement: 'action_bar' },
        ]);
    });
});
