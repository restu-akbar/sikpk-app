import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import { createApp, h } from 'vue';
import { initializeTheme } from '@/composables/useAppearance';
import AppLayout from '@/layouts/AppLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

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

            case name.startsWith('auth/'):
                page.default.layout = AuthLayout;
                break;

            case name.startsWith('settings/'):
                page.default.layout = [AppLayout, SettingsLayout];
                break;

            default:
                page.default.layout = AppLayout;
                break;
        }

        return page;
    },

    setup({ el, App, props, plugin }) {
        /*
        |--------------------------------------------------------------------------
        | Pinia
        |--------------------------------------------------------------------------
        */

        const pinia = createPinia();

        /*
        |--------------------------------------------------------------------------
        | Vue App
        |--------------------------------------------------------------------------
        */

        createApp({
            render: () => h(App, props),
        })
            .use(plugin)
            .use(pinia)
            .mount(el);
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

/*
|--------------------------------------------------------------------------
| Flash Toast
|--------------------------------------------------------------------------
*/

initializeFlashToast();
