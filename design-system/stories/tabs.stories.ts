import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.tabs: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Navigation/Tabs' };
export default meta;

export const Default = story('tabs', 'Default');
export const SecondSelected = story('tabs', 'SecondSelected');
export const Hover = story('tabs', 'Hover');
export const Focus = story('tabs', 'Focus');
