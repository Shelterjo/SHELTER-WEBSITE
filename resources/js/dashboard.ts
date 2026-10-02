// Owner dashboard entry. Never imported by the public site (separate Vite entry, M37 §30).
import { installAriaDisabledGuard } from './ui/aria-disabled';
import { installDialogs } from './ui/dialog';
import { installTabs } from './ui/tabs';

installAriaDisabledGuard();
installDialogs();
installTabs();

// The page editor script loads only on the page editor (its own chunk).
const pageEditor = document.querySelector<HTMLFormElement>('form[data-page-editor]');
if (pageEditor !== null) {
    void import('./dashboard/page-editor').then(({ installPageEditor }) => installPageEditor(pageEditor));
}

// Bulk selection on the applications list, and the print button of the export's PDF view (own chunk).
const bulk = document.querySelector<HTMLFormElement>('form[data-bulk]');
if (bulk !== null || document.querySelector('button[data-print]') !== null) {
    void import('./dashboard/bulk').then(({ installBulk, installPrint }) => {
        if (bulk !== null) installBulk(bulk);
        installPrint();
    });
}

// Job applications list: live search and quick view (own chunk).
const careersResults = document.querySelector<HTMLElement>('[data-careers-results]');
if (careersResults !== null) {
    void import('./dashboard/careers-list').then(({ installCareersList }) => installCareersList(careersResults));
}

// Phones: the navigation is one scrollable row — bring the current screen's item into view (RTL handled by the browser).
const nav = document.querySelector<HTMLElement>('.ui-shell__nav');
const current = nav?.querySelector<HTMLElement>('[aria-current="page"]');
if (nav && current && nav.scrollWidth > nav.clientWidth) {
    current.scrollIntoView({ behavior: 'instant', block: 'nearest', inline: 'center' });
}
