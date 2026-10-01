import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.open-status: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Site/Open status' };
export default meta;

export const Open = story('open-status', 'Open');
export const Closing = story('open-status', 'Closing');
export const Closed = story('open-status', 'Closed');
export const Large = story('open-status', 'Large');
