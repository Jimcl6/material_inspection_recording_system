declare module '*.vue' {
    import type { DefineComponent } from 'vue';
    const component: DefineComponent<Record<string, unknown>, Record<string, unknown>, any>;
    export default component;
}

interface PageProps {
    auth: {
        user: User;
    };
    [key: string]: unknown;
}

interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    profile_photo_url: string;
}

declare global {
    interface Window {
        route: typeof import('ziggy-js').route;
        flatpickr?: (element: Element, options: Record<string, unknown>) => unknown;
    }
}

export {};
