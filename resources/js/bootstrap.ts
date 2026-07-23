import { createApp, h, type DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { createPinia } from 'pinia';
import { createHead } from '@vueuse/head';
import type { Config } from 'ziggy-js';
import '../css/app.css';

declare global {
    interface Window {
        Ziggy: Config;
    }
}

// Type for the page component
interface PageComponent {
    default: DefineComponent;
    [key: string]: unknown;
}

// Import your global components here
// import SomeComponent from '@/Components/SomeComponent.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Material Inspection Recording System';

// Initialize the application

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name: string) => {
        const pages = import.meta.glob<PageComponent>('./Pages/**/*.vue', { eager: true });
        const page = pages[`./Pages/${name}.vue`];
        return page?.default || page;
    },
    setup({ el, App, props, plugin }: {
        el: Element;
        App: DefineComponent;
        props: Record<string, unknown>;
        plugin: any; // Using 'any' to avoid type issues with Inertia's plugin
    }) {
        const pinia = createPinia();
        const head = createHead();
        
        // Create the Vue app with proper typing
        const app = createApp({
            render: () => h(App, props as Record<string, unknown>)
        });

        // Use plugins
        app.use(plugin)
           .use(pinia)
           .use(head);
        
        // Only use ZiggyVue if Ziggy is available
        if (window.Ziggy) {
            app.use(ZiggyVue, window.Ziggy);
        }
        
        // Register global components here
        // app.component('some-component', SomeComponent);
        
        // Mount the app
        app.mount(el);
        
        // Return the app instance for HMR support
        return app;
    }
});
