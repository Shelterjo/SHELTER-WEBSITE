// Owner dashboard entry. Never imported by the public site (separate Vite entry, M37 §30).
import { installNavStrip } from './dashboard/nav-strip';
import { installAriaDisabledGuard } from './ui/aria-disabled';
import { installDialogs } from './ui/dialog';
import { installFormGuard } from './ui/form-guard';
import { installTabs } from './ui/tabs';

installAriaDisabledGuard();
installDialogs();
installFormGuard();
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

// Dashboard search: the results refresh while typing (own chunk, only on the results page).
const ownerSearch = document.querySelector<HTMLElement>('[data-owner-search]');
if (ownerSearch !== null) {
    void import('./dashboard/owner-search').then(({ installOwnerSearch }) => installOwnerSearch(ownerSearch));
}

// Phones: the navigation is one scrollable row — the current screen's item in view, faded edges where more waits.
const navStrip = document.querySelector<HTMLElement>('[data-ui-nav-strip]');
if (navStrip !== null) installNavStrip(navStrip);
