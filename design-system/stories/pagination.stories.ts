import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.pagination: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Navigation/Pagination' };
export default meta;

export const Middle = story('pagination', 'Middle');
export const FirstPage = story('pagination', 'FirstPage');
export const LastPage = story('pagination', 'LastPage');
export const FewPages = story('pagination', 'FewPages');
export const Hover = story('pagination', 'Hover');
export const Focus = story('pagination', 'Focus');
