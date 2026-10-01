// Public site entry. Progressive enhancement only: every page works without JavaScript (ADR-001).
// Only what the public pages need: guarded aria-disabled controls and dialog fallbacks (menu product detail).
import { installAriaDisabledGuard } from './ui/aria-disabled';
import { installDialogs } from './ui/dialog';

installAriaDisabledGuard();
installDialogs();
