import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.section-heading: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Site/Section heading' };
export default meta;

export const Default = story('section-heading', 'Default');
export const WithLink = story('section-heading', 'WithLink');
