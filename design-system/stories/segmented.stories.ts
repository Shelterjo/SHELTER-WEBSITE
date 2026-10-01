import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.segmented: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Navigation/Segmented control' };
export default meta;

export const Default = story('segmented', 'Default');
export const SecondSelected = story('segmented', 'SecondSelected');
export const ThirdSelected = story('segmented', 'ThirdSelected');
export const Hover = story('segmented', 'Hover');
export const Focus = story('segmented', 'Focus');
