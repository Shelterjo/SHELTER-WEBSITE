import type { Preview } from '@storybook/html-vite';
import '../resources/css/site.css';

// Locale toolbar: every story renders in Arabic RTL and English LTR (M37 §2).
const preview: Preview = {
    globalTypes: {
        locale: {
            description: 'Language / direction',
            toolbar: {
                icon: 'globe',
                items: [
                    { value: 'ar', title: 'العربية (RTL)' },
                    { value: 'en', title: 'English (LTR)' },
                ],
                dynamicTitle: true,
            },
        },
    },
    initialGlobals: { locale: 'ar' },
    decorators: [
        (story, context) => {
            const locale = context.globals['locale'] === 'en' ? 'en' : 'ar';
            document.documentElement.lang = locale;
            document.documentElement.dir = locale === 'ar' ? 'rtl' : 'ltr';
            return story();
        },
    ],
    parameters: {
        a11y: { test: 'error' },
        layout: 'padded',
        viewport: {
            options: {
                mobile: { name: 'Mobile 360', styles: { width: '360px', height: '780px' } },
                tablet: { name: 'Tablet 768', styles: { width: '768px', height: '1024px' } },
                desktop: { name: 'Desktop 1280', styles: { width: '1280px', height: '800px' } },
            },
        },
    },
};

export default preview;
