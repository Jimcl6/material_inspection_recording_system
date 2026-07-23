/// <reference types="@inertiajs/vue3" />

declare module '@inertiajs/vue3' {
    import type { App, DefineComponent } from 'vue';
    
    interface InertiaAppProps {
        initialPage: any;
        resolveComponent: (name: string) => DefineComponent | Promise<DefineComponent>;
        initialComponent?: object;
        titleCallback?: (title: string) => string;
        onHeadUpdate?: (elements: string[]) => void;
    }

    export function createInertiaApp(options: {
        id?: string;
        title?: (title: string) => string;
        resolve: (name: string) => DefineComponent | Promise<DefineComponent> | { default: DefineComponent };
        setup(options: {
            el: Element;
            App: DefineComponent;
            props: {
                initialPage: any;
                initialComponent?: object;
                resolveComponent: (name: string) => DefineComponent | Promise<DefineComponent>;
            };
            plugin: any;
        }): void | App;
    }): void;

    export const Link: DefineComponent;
    export const useForm: any;
    export const usePage: () => any;
    export const router: any;
    export const useForm: any;
    export const Head: DefineComponent;
}
