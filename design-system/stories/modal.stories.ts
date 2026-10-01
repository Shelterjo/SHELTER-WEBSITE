import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.modal: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Overlays/Modal' };
export default meta;

export const Open = story('modal', 'Open');
export const Trigger = story('modal', 'Trigger');
