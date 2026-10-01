import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.badge: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Display/Badge' };
export default meta;

export const Variants = story('badge', 'Variants');
export const Default = story('badge', 'Default');
