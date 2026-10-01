import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.bottom-sheet: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Overlays/Bottom sheet' };
export default meta;

export const Open = story('bottom-sheet', 'Open');
export const AdaptiveOpen = story('bottom-sheet', 'AdaptiveOpen');
