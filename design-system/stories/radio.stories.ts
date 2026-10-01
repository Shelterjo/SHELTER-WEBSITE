import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.radio: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Forms/Radio' };
export default meta;

export const Group = story('radio', 'Group');
export const Disabled = story('radio', 'Disabled');
