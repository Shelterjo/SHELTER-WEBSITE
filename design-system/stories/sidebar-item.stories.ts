import type { Meta } from '@storybook/html-vite';
import '../../resources/css/components/dashboard/sidebar.css';
import { story } from './support';

// x-ui.sidebar-item: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Dashboard/Sidebar item' };
export default meta;

export const Default = story('sidebar-item', 'Default');
export const Hover = story('sidebar-item', 'Hover');
export const Focus = story('sidebar-item', 'Focus');
