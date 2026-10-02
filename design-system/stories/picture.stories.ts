import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.picture: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Display/Picture' };
export default meta;

export const Default = story('picture', 'Default');
export const Square = story('picture', 'Square');
