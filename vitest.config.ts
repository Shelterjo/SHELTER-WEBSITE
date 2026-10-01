import { defineConfig } from 'vitest/config';

// Unit tests for the small TypeScript surface. Kept separate from vite.config.js: the Laravel Vite plugin
// refuses to start in CI ("You should not run the Vite HMR server in CI environments").
export default defineConfig({
    test: {
        include: ['resources/js/**/*.test.ts'],
        environment: 'node',
    },
});
