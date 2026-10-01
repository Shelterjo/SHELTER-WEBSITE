import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.select: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Forms/Select' };
export default meta;

export const Default = story('select', 'Default');
export const Placeholder = story('select', 'Placeholder');
export const Error = story('select', 'Error');
export const Disabled = story('select', 'Disabled');
