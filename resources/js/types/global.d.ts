// Type declarations for global objects and modules
declare module '*.vue' {
    import { DefineComponent } from 'vue';
    const component: DefineComponent<{}, {}, any>;
    export default component;
}

declare module '@inertiajs/vue3' {
    import { Plugin, Component } from 'vue';
    
    export const Head: Component;
    export const Link: Component;
    export const router: {
        get: (url: string, data?: any, options?: any) => Promise<any>;
        post: (url: string, data?: any, options?: any) => Promise<any>;
        put: (url: string, data?: any, options?: any) => Promise<any>;
        patch: (url: string, data?: any, options?: any) => Promise<any>;
        delete: (url: string, options?: any) => Promise<any>;
        on: (event: string, callback: (event: any) => void) => void;
    };
    
    export function usePage(): { props: Record<string, any> };
    export const InertiaApp: Component;
    export const InertiaLink: Component;
    export const InertiaForm: Component;
    export const InertiaHead: Component;
    export const useForm: (data?: any) => any;
    export const useRemember: (data: any, key?: string) => any;
    export const router: any;
    export const usePage: () => any;
    export const useForm: (data?: any) => any;
}

declare const route: (name: string, params?: any) => string;

declare module '*.vue' {
    import { DefineComponent } from 'vue';
    const component: DefineComponent<{}, {}, any>;
    export default component;
}

interface Window {
    route: (name: string, params?: any) => string;
    Inertia: any;
    _inertia_route: (name: string, params?: any) => string;
}
