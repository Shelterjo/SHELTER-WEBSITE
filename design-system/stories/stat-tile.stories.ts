import type { Meta } from '@storybook/html-vite';
import '../../resources/css/components/dashboard/stat-tile.css';
import { story } from './support';

// x-ui.stat-tile: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Dashboard/Stat tile' };
export default meta;

export const Default = story('stat-tile', 'Default');
export const TrendUp = story('stat-tile', 'TrendUp');
export const TrendDown = story('stat-tile', 'TrendDown');
export const Empty = story('stat-tile', 'Empty');
export const Link = story('stat-tile', 'Link');
export const Hover = story('stat-tile', 'Hover');
export const Focus = story('stat-tile', 'Focus');
