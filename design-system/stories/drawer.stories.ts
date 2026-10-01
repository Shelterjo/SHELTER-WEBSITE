import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.drawer: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Overlays/Drawer' };
export default meta;

export const OpenEnd = story('drawer', 'OpenEnd');
export const OpenStart = story('drawer', 'OpenStart');
