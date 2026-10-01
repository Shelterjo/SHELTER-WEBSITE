import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.field: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Forms/Field' };
export default meta;

export const Default = story('field', 'Default');
export const WithHint = story('field', 'WithHint');
export const Required = story('field', 'Required');
export const Optional = story('field', 'Optional');
export const Error = story('field', 'Error');
