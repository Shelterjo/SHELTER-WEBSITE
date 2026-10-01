import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

// ADR-001: Blade + Vite. Two separate entry pairs so dashboard code never enters the public bundle (M37 §30).
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/site.css',
                'resources/js/site.ts',
                'resources/css/dashboard.css',
                'resources/js/dashboard.ts',
            ],
            refresh: true,
        }),
    ],
    build: {
        cssMinify: true,
        modulePreload: { polyfill: false },
    },
    server: {
        watch: { ignored: ['**/storage/framework/views/**'] },
    },
    test: {
        include: ['resources/js/**/*.test.ts'],
        environment: 'node',
    },
});
