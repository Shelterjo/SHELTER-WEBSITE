import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.checkbox: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Forms/Checkbox' };
export default meta;

export const Default = story('checkbox', 'Default');
export const Checked = story('checkbox', 'Checked');
export const WithHint = story('checkbox', 'WithHint');
export const Focus = story('checkbox', 'Focus');
export const Disabled = story('checkbox', 'Disabled');
export const Error = story('checkbox', 'Error');
