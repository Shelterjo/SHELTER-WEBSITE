import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.shaltoor: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
// The launcher is hidden until the site script shows it; the dialog opens from it.
const meta: Meta = { title: 'Site/Shaltoor' };
export default meta;

export const Default = story('shaltoor', 'Default');
