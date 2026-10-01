import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.input: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Forms/Input' };
export default meta;

export const Default = story('input', 'Default');
export const Filled = story('input', 'Filled');
export const Hover = story('input', 'Hover');
export const Focus = story('input', 'Focus');
export const Disabled = story('input', 'Disabled');
export const Error = story('input', 'Error');
