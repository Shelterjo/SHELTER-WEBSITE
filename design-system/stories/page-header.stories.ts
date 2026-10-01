import type { Meta } from '@storybook/html-vite';
import '../../resources/css/components/dashboard/page-header.css';
import { story } from './support';

// x-ui.page-header: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Dashboard/Page header' };
export default meta;

export const Default = story('page-header', 'Default');
export const WithActions = story('page-header', 'WithActions');
export const WithBreadcrumb = story('page-header', 'WithBreadcrumb');
