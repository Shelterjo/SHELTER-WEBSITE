import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.hours-table: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Site/Hours table' };
export default meta;

export const Week = story('hours-table', 'Week');
