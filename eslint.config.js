// TOOLCHAIN.md: correctness + unsafe patterns for the small TypeScript surface. No React rules (ADR-001: Blade).
import js from '@eslint/js';
import globals from 'globals';
import tseslint from 'typescript-eslint';

export default tseslint.config(
    {
        ignores: [
            'vendor/**',
            'node_modules/**',
            'public/**',
            'storage/**',
            'bootstrap/cache/**',
            'storybook-static/**',
            'tooling/**',
            'docs/**',
            'design-system/stories/generated/**',
            '.claude/**',
        ],
    },
    js.configs.recommended,
    ...tseslint.configs.strict,
    {
        languageOptions: { globals: { ...globals.browser } },
        rules: {
            'no-console': ['error', { allow: ['warn', 'error'] }],
            'no-eval': 'error',
            'no-implied-eval': 'error',
            'no-new-func': 'error',
            'no-restricted-properties': [
                'error',
                { object: 'document', property: 'write', message: 'Unsafe DOM write.' },
            ],
            'no-restricted-syntax': [
                'error',
                {
                    selector: "AssignmentExpression[left.property.name='innerHTML']",
                    message: 'Use textContent or DOM APIs; innerHTML is an XSS risk.',
                },
            ],
            '@typescript-eslint/no-explicit-any': 'error',
        },
    },
    {
        files: ['*.config.{js,ts}', 'scripts/**/*.mjs', '.storybook/**/*.ts'],
        languageOptions: { globals: { ...globals.node } },
        rules: { 'no-console': 'off' },
    },
);
