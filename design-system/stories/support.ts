// Shared story factory: every story shows HTML that `php artisan ds:export` rendered from the real Blade x-ui
// component (design-system/catalog.php → generated/{component}.json), in Arabic (RTL) or English (LTR) following the
// locale toolbar. No component markup is written here (ADR-001: Blade is the single source).
import type { StoryObj } from '@storybook/html-vite';
import { installAriaDisabledGuard } from '../../resources/js/ui/aria-disabled';
import { installDialogs } from '../../resources/js/ui/dialog';
import { installTabs } from '../../resources/js/ui/tabs';

// The same progressive enhancement as the site and dashboard entries.
installAriaDisabledGuard();
installDialogs();
installTabs();

interface ExportedStory {
    story: string;
    state: string;
    html_ar: string;
    html_en: string;
}

const generated = import.meta.glob<ExportedStory[]>('./generated/*.json', { eager: true, import: 'default' });

// Interaction states are shown statically with storybook-addon-pseudo-states (M37 §2).
const PSEUDO: Record<string, Record<string, boolean>> = {
    hover: { hover: true },
    focus: { focus: true, focusVisible: true },
    active: { hover: true, active: true },
};

function missing(component: string, name: string): HTMLElement {
    const message = document.createElement('p');
    message.className = 'ds-missing-story';
    message.textContent = `No exported story ${component}/${name}. Run "php artisan ds:export" (design-system/catalog.php).`;
    return message;
}

export function story(component: string, name: string): StoryObj {
    const entry = generated[`./generated/${component}.json`]?.find((item) => item.story === name);
    const state = entry?.state ?? 'default';
    return {
        parameters: { pseudo: PSEUDO[state] ?? {}, dsState: state },
        render: (_args, context) => {
            if (!entry) return missing(component, name);
            return context.globals['locale'] === 'en' ? entry.html_en : entry.html_ar;
        },
        play: async ({ canvasElement, id }) => {
            // "Open" overlays are shown as real modal dialogs (top layer, inert page, focus on the title).
            if (state === 'open') canvasElement.querySelector<HTMLDialogElement>('dialog[data-ui-dialog]')?.showModal();
            // Marker for tests/Browser/storybook-a11y.spec.mjs: the story is rendered and its play step is done.
            document.body.dataset['dsReady'] = id;
        },
    };
}
