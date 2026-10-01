import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.site-footer: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Site/Footer' };
export default meta;

export const Default = story('site-footer', 'Default');
