import type { Meta } from '@storybook/html-vite';
import '../../resources/css/components/dashboard/status-pill.css';
import { story } from './support';

// x-ui.status-pill: stories come from design-system/catalog.php through `php artisan ds:export` (Blade is the source).
const meta: Meta = { title: 'Dashboard/Status pill' };
export default meta;

export const AllStatuses = story('status-pill', 'AllStatuses');
export const Synced = story('status-pill', 'Synced');
export const Pending = story('status-pill', 'Pending');
export const Failed = story('status-pill', 'Failed');
export const NotSupported = story('status-pill', 'NotSupported');
export const ManualActionRequired = story('status-pill', 'ManualActionRequired');
export const OutOfSync = story('status-pill', 'OutOfSync');
