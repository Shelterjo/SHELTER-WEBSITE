import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.action-bar: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Site/Action bar' };
export default meta;

export const Default = story('action-bar', 'Default');
