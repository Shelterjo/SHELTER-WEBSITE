import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.product-card: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Display/Product card' };
export default meta;

export const Default = story('product-card', 'Default');
export const ArabicNamePending = story('product-card', 'ArabicNamePending');
export const LongName = story('product-card', 'LongName');
export const WithMedia = story('product-card', 'WithMedia');
export const Grid = story('product-card', 'Grid');
export const Hover = story('product-card', 'Hover');
export const Focus = story('product-card', 'Focus');
export const Unavailable = story('product-card', 'Unavailable');
export const WithBadge = story('product-card', 'WithBadge');
