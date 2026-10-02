import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.announcement-bar: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Feedback/Announcement bar' };
export default meta;

export const Default = story('announcement-bar', 'Default');
export const WithLink = story('announcement-bar', 'WithLink');
export const Urgent = story('announcement-bar', 'Urgent');
