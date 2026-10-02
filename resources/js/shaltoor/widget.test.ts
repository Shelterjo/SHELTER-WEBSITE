import { describe, expect, it } from 'vitest';
import { opensNewTab, parseReply, safeHref } from './widget';

const BASE = 'https://www.shelterjo.com/ar/jo/menu/';

describe('reply parsing', () => {
    it('keeps a well-formed reply with its actions and suggestions', () => {
        const reply = parseReply({
            text: 'نص',
            topic: 'hours',
            answered: true,
            actions: [{ kind: 'call', label: 'اتصال', href: 'tel:+962700000000' }],
            suggestions: ['المنيو', ' '],
        });
        expect(reply).toEqual({
            text: 'نص',
            topic: 'hours',
            answered: true,
            actions: [{ kind: 'call', label: 'اتصال', href: 'tel:+962700000000' }],
            suggestions: ['المنيو'],
        });
    });

    it('drops malformed replies, unknown action kinds and non-string fields', () => {
        expect(parseReply(null)).toBeNull();
        expect(parseReply({ topic: 'x' })).toBeNull();
        expect(parseReply('<b>hi</b>')).toBeNull();
        const reply = parseReply({
            text: 't',
            topic: 'x',
            answered: 'yes',
            actions: [{ kind: 'script', label: 'x', href: '/' }, { kind: 'link', label: 1, href: '/' }, 'x'],
            suggestions: [1, null],
        });
        expect(reply).toEqual({ text: 't', topic: 'x', answered: false, actions: [], suggestions: [] });
    });
});

describe('link safety', () => {
    it('allows tel:, the site itself and https links', () => {
        expect(safeHref('tel:+962700000000', BASE)).toBe('tel:+962700000000');
        expect(safeHref('/ar/jo/locations/', BASE)).toBe('https://www.shelterjo.com/ar/jo/locations/');
        expect(safeHref('https://wa.me/962700000000', BASE)).toBe('https://wa.me/962700000000');
    });

    it('refuses script, data and plain-http links and malformed numbers', () => {
        expect(safeHref('javascript:alert(1)', BASE)).toBeNull();
        expect(safeHref('data:text/html,<script>', BASE)).toBeNull();
        expect(safeHref('http://example.test/', BASE)).toBeNull();
        expect(safeHref('tel:call-me', BASE)).toBeNull();
    });

    it('opens other sites in a new tab and keeps calls and site pages in place', () => {
        expect(opensNewTab('https://maps.app.goo.gl/x', BASE)).toBe(true);
        expect(opensNewTab('https://www.shelterjo.com/ar/', BASE)).toBe(false);
        expect(opensNewTab('tel:+962700000000', BASE)).toBe(false);
    });
});
