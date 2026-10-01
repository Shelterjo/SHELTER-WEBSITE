import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.hero: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Site/Hero' };
export default meta;

export const Brand = story('hero', 'Brand');
