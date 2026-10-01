import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.skip-link: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Navigation/Skip link' };
export default meta;

export const Focus = story('skip-link', 'Focus');
