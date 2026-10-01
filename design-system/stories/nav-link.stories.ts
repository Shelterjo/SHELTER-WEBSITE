import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.nav-link: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Navigation/Nav link' };
export default meta;

export const Default = story('nav-link', 'Default');
export const Hover = story('nav-link', 'Hover');
export const Focus = story('nav-link', 'Focus');
export const Active = story('nav-link', 'Active');
