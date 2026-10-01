import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.media-placeholder: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Display/Media placeholder' };
export default meta;

export const Default = story('media-placeholder', 'Default');
