import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.table: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Display/Table' };
export default meta;

export const Default = story('table', 'Default');
export const Stacked = story('table', 'Stacked');
export const StackedWide = story('table', 'StackedWide');
