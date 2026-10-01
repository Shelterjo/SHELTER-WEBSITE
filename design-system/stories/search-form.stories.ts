import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.search-form: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Site/Search form' };
export default meta;

export const Default = story('search-form', 'Default');
export const WithQuery = story('search-form', 'WithQuery');
export const Compact = story('search-form', 'Compact');
