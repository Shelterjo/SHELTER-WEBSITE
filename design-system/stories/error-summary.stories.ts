import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.error-summary: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Forms/Error summary' };
export default meta;

export const Error = story('error-summary', 'Error');
export const Empty = story('error-summary', 'Empty');
