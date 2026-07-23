import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue, route as ziggyRoute } from 'ziggy-js';
import type { Page } from '@inertiajs/core';
import type { Config, RouteParam, RouteParamsWithQueryOverload, Router as ZiggyRouter } from 'ziggy-js';
import { createPinia } from 'pinia';

declare global {
    interface Window {
        Ziggy: Config;
        route: typeof route;
    }
}

declare function route(
    name?: string,
    params?: RouteParamsWithQueryOverload | RouteParam,
    absolute?: boolean,
    customZiggy?: Config
): string;

declare function route(
    name: undefined,
    params?: RouteParamsWithQueryOverload | RouteParam,
    absolute?: boolean,
    customZiggy?: Config
): typeof ZiggyRouter;

// Import your global components here
// import SomeComponent from '@/Components/SomeComponent.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Material Inspection Recording System';

const syncCsrfToken = (page: Page): void => {
    const token = page.props.csrf_token;

    if (typeof token !== 'string' || token === '') {
        return;
    }

    const metas = document.head.querySelectorAll<HTMLMetaElement>('meta[name="csrf-token"]');

    if (metas.length === 0) {
        const meta = document.createElement('meta');
        meta.name = 'csrf-token';
        meta.content = token;
        document.head.appendChild(meta);
        return;
    }

    metas.forEach((meta) => {
        meta.content = token;
    });
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        syncCsrfToken(props.initialPage);
        router.on('navigate', (event) => syncCsrfToken(event.detail.page));

        const pageZiggy = (props.initialPage.props as any).ziggy;
        const ziggy = {
            ...pageZiggy,
            url: window.location.origin,
            port: window.location.port ? Number(window.location.port) : null,
        };

        window.Ziggy = ziggy;
        window.route = ((name?: string, params?: RouteParamsWithQueryOverload | RouteParam, absolute?: boolean, customZiggy?: Config) => {
            return ziggyRoute(name as any, params as any, absolute, customZiggy ?? ziggy) as any;
        }) as typeof route;
        
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue, ziggy)
            // Register global components here
            // .component('some-component', SomeComponent)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
        showSpinner: true,
    },
});

// Register global components
// app.component('some-component', SomeComponent);
