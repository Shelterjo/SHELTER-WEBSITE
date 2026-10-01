import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.site-header: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Site/Header' };
export default meta;

export const Default = story('site-header', 'Default');
export const Minimal = story('site-header', 'Minimal');
