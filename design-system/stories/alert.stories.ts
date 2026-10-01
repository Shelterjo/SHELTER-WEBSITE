import type { Meta } from '@storybook/html-vite';
import { story } from './support';

// x-ui.alert: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Feedback/Alert' };
export default meta;

export const Info = story('alert', 'Info');
export const Success = story('alert', 'Success');
export const Warning = story('alert', 'Warning');
export const Error = story('alert', 'Error');
export const WithoutTitle = story('alert', 'WithoutTitle');
