import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.search-field: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Forms/Search field' };
export default meta;

export const Default = story('search-field', 'Default');
export const Hover = story('search-field', 'Hover');
export const Focus = story('search-field', 'Focus');
