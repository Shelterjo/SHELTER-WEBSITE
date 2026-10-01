import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.empty-state: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Feedback/Empty state' };
export default meta;

export const Empty = story('empty-state', 'Empty');
export const WithAction = story('empty-state', 'WithAction');
