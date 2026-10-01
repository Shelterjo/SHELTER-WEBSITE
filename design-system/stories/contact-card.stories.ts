import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.contact-card: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Site/Contact card' };
export default meta;

export const Intent = story('contact-card', 'Intent');
export const General = story('contact-card', 'General');
export const WithLink = story('contact-card', 'WithLink');
