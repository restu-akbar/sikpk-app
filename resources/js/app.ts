import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import { createApp, h } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthCenterLayout from '@/layouts/auth/AuthCenterLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import RootLayout from './RootLayout.vue'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),

    resolve: async (name) => {
        const page = await resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob('./pages/**/*.vue'),
        );

        /*
        |--------------------------------------------------------------------------
        | Layout Resolver
        |--------------------------------------------------------------------------
        */

        switch (true) {
            case name === 'Welcome':
                page.default.layout = null;
                break;

            case name === 'GettingStarted':
                page.default.layout = null;
                break;

            case name === 'auth/GoogleLogin':
                page.default.layout = AuthCenterLayout;
                break;

            case name.startsWith('auth/'):
                page.default.layout = AuthLayout;
                break;

            case name.startsWith('settings/'):
                page.default.layout = [AppLayout, SettingsLayout];
                break;
            case name.includes('login'):
                page.default.layout = null;
                break;
            default:
                page.default.layout = AppLayout;
                break;
        }

        return page;
    },

    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        const app = createApp({
            render: () =>
                h(RootLayout, null, {
                    default: () => h(App, props),
                }),
        })
            .use(plugin)
            .use(pinia);

        if (typeof window !== 'undefined') {
            app.mount(el);
        }

        return app;
    },

    progress: {
        color: '#4B5563',
    },
});

/*
|--------------------------------------------------------------------------
| Theme
|--------------------------------------------------------------------------
*/

initializeTheme();
