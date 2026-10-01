import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.skeleton: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Feedback/Skeleton' };
export default meta;

export const Loading = story('skeleton', 'Loading');
export const WithMedia = story('skeleton', 'WithMedia');
export const CardGrid = story('skeleton', 'CardGrid');
