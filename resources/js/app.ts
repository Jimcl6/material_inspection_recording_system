import './bootstrap';
import '../css/app.css';

import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import { ZiggyVue, route as ziggyRoute } from 'ziggy-js';
import type { Page } from '@inertiajs/core';
import type { Config } from 'ziggy-js';
import { createPinia } from 'pinia';

declare global {
    interface Window {
        Ziggy: Config;
        route: typeof ziggyRoute;
        flatpickr?: (element: Element, options: Record<string, unknown>) => unknown;
    }
}

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
        const pages = import.meta.glob<{ default: DefineComponent }>('./Pages/**/*.vue', { eager: true });
        const page = pages[`./Pages/${name}.vue`];

        if (!page) {
            throw new Error(`Unable to resolve Inertia page: ${name}`);
        }

        return page.default;
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
        window.route = ziggyRoute;

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue, ziggy);

        app.mount(el);

        return app;
    },
    progress: {
        color: '#4B5563',
        showSpinner: true,
    },
});

// Register global components
// app.component('some-component', SomeComponent);
