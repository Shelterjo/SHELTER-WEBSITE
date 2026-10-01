import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.switch: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Forms/Switch' };
export default meta;

export const Off = story('switch', 'Off');
export const On = story('switch', 'On');
export const WithHint = story('switch', 'WithHint');
export const Focus = story('switch', 'Focus');
export const Disabled = story('switch', 'Disabled');
