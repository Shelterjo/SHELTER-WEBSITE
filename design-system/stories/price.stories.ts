import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.price: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Display/Price' };
export default meta;

export const Default = story('price', 'Default');
export const QuarterDinar = story('price', 'QuarterDinar');
