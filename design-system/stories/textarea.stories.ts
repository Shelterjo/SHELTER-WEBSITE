import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.textarea: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Forms/Textarea' };
export default meta;

export const Default = story('textarea', 'Default');
export const Error = story('textarea', 'Error');
export const Disabled = story('textarea', 'Disabled');
