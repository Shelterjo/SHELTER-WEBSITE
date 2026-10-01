import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.card: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Display/Card' };
export default meta;

export const Default = story('card', 'Default');
export const Raised = story('card', 'Raised');
export const WithFooter = story('card', 'WithFooter');
export const WithMedia = story('card', 'WithMedia');
export const Link = story('card', 'Link');
export const Hover = story('card', 'Hover');
export const Focus = story('card', 'Focus');
