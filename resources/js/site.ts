// Public site entry. Progressive enhancement only: every page works without JavaScript (ADR-001).
// Only what the public pages need: guarded aria-disabled controls, dialog fallbacks and the lazy section reveal
// (Motion is loaded only when a page has something to reveal).
import { installAriaDisabledGuard } from './ui/aria-disabled';
import { installDialogs } from './ui/dialog';
import { installReveal } from './ui/reveal';

installAriaDisabledGuard();
installDialogs();
void installReveal();

// The menu page script loads only on the menu page (its own chunk).
const menu = document.querySelector<HTMLElement>('[data-ui-menu]');
if (menu !== null) {
    void import('./menu/page').then(({ installMenuPage }) => installMenuPage(menu));
}

// The careers form script loads only on the careers page (its own chunk).
const careers = document.querySelector<HTMLFormElement>('form[data-careers-form]');
if (careers !== null) {
    void import('./careers/form').then(({ installCareersForm }) => installCareersForm(careers));
}
