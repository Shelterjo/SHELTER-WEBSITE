import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.button: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Actions/Button' };
export default meta;

export const Primary = story('button', 'Primary');
export const Secondary = story('button', 'Secondary');
export const Outline = story('button', 'Outline');
export const Ghost = story('button', 'Ghost');
export const Danger = story('button', 'Danger');
export const Link = story('button', 'Link');
export const Sizes = story('button', 'Sizes');
export const WithIcon = story('button', 'WithIcon');
export const IconOnly = story('button', 'IconOnly');
export const Hover = story('button', 'Hover');
export const Focus = story('button', 'Focus');
export const Active = story('button', 'Active');
export const Disabled = story('button', 'Disabled');
export const Loading = story('button', 'Loading');
