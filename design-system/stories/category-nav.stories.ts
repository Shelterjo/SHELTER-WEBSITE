import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.category-nav: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Navigation/Category bar' };
export default meta;

export const Default = story('category-nav', 'Default');
export const WithActions = story('category-nav', 'WithActions');
export const Sidebar = story('category-nav', 'Sidebar');
export const Focus = story('category-nav', 'Focus');
