import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.disclosure: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Navigation/Disclosure' };
export default meta;

export const Closed = story('disclosure', 'Closed');
export const Open = story('disclosure', 'Open');
export const Focus = story('disclosure', 'Focus');
