import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.rating: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Forms/Rating' };
export default meta;

export const Default = story('rating', 'Default');
export const Selected = story('rating', 'Selected');
export const Required = story('rating', 'Required');
export const Error = story('rating', 'Error');
