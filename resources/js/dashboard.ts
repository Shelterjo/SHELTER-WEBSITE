// Owner dashboard entry. Never imported by the public site (separate Vite entry, M37 §30).
import { installAriaDisabledGuard } from './ui/aria-disabled';
import { installDialogs } from './ui/dialog';
import { installTabs } from './ui/tabs';

installAriaDisabledGuard();
installDialogs();
installTabs();
