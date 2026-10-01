import type { StorybookConfig } from '@storybook/html-vite';

// Storybook = SHELTER design-system reference (M37 §2). Stories render HTML exported from the Blade x-ui components
// (`php artisan ds:export`), so Blade stays the single source of component markup (ADR-001, TOOLCHAIN.md).
const config: StorybookConfig = {
    stories: ['../design-system/stories/**/*.stories.ts'],
    addons: ['@storybook/addon-a11y', 'storybook-addon-pseudo-states'],
    framework: {
        name: '@storybook/html-vite',
        options: { builder: { viteConfigPath: '.storybook/vite.config.ts' } },
    },
    core: { disableTelemetry: true },
};

export default config;
