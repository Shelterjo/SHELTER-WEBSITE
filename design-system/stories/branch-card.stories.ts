import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.branch-card: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Site/Branch card' };
export default meta;

export const Row = story('branch-card', 'Row');
export const Panel = story('branch-card', 'Panel');
export const Closed = story('branch-card', 'Closed');
