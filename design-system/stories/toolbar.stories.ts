import type { Meta } from '@storybook/html-vite';
import '../../resources/css/components/dashboard/toolbar.css';
import { story } from './support';

// x-ui.toolbar: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Dashboard/Toolbar' };
export default meta;

export const Default = story('toolbar', 'Default');
