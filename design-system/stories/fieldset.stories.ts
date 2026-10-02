import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.fieldset: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Forms/Fieldset' };
export default meta;

export const Default = story('fieldset', 'Default');
export const Error = story('fieldset', 'Error');
export const Required = story('fieldset', 'Required');
