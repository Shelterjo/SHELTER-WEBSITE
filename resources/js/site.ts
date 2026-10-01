// Public site entry. Progressive enhancement only: every page works without JavaScript (ADR-001).
// Only what the public pages need: guarded aria-disabled controls, dialog fallbacks and the lazy section reveal
// (Motion is loaded only when a page has something to reveal).
import { installAriaDisabledGuard } from './ui/aria-disabled';
import { installDialogs } from './ui/dialog';
import { installReveal } from './ui/reveal';

installAriaDisabledGuard();
installDialogs();
void installReveal();
