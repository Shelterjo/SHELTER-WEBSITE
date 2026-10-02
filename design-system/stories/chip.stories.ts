import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.chip: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Actions/Chip' };
export default meta;

export const Default = story('chip', 'Default');
export const Pressed = story('chip', 'Pressed');
export const CurrentLink = story('chip', 'CurrentLink');
export const Action = story('chip', 'Action');
export const Hover = story('chip', 'Hover');
export const Focus = story('chip', 'Focus');
export const Active = story('chip', 'Active');
export const Disabled = story('chip', 'Disabled');
