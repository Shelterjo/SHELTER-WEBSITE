import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.event-card: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Display/Event card' };
export default meta;

export const Default = story('event-card', 'Default');
export const WithBadge = story('event-card', 'WithBadge');
export const WithMedia = story('event-card', 'WithMedia');
export const Link = story('event-card', 'Link');
export const Hover = story('event-card', 'Hover');
export const Focus = story('event-card', 'Focus');
