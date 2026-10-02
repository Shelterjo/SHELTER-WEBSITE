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

// Phones: the navigation is one scrollable row — bring the current screen's item into view (RTL handled by the browser).
const nav = document.querySelector<HTMLElement>('.ui-shell__nav');
const current = nav?.querySelector<HTMLElement>('[aria-current="page"]');
if (nav && current && nav.scrollWidth > nav.clientWidth) {
    current.scrollIntoView({ behavior: 'instant', block: 'nearest', inline: 'center' });
}
