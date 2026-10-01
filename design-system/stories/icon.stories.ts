import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.icon: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Foundations/Icon' };
export default meta;

export const Library = story('icon', 'Library');
export const Sizes = story('icon', 'Sizes');
export const Directional = story('icon', 'Directional');
