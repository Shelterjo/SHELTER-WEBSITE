import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.breadcrumb: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Navigation/Breadcrumb' };
export default meta;

export const Default = story('breadcrumb', 'Default');
export const Focus = story('breadcrumb', 'Focus');
