import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import { Ziggy } from './ziggy';
import { createPinia } from 'pinia';

// Import your global components here
// import SomeComponent from '@/Components/SomeComponent.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Material Inspection Recording System';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();
        
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia)
            .use(ZiggyVue, Ziggy)
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
