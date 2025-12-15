/* eslint-disable */
declare module '*.vue' {
    import type { DefineComponent } from 'vue'
    const component: DefineComponent<{}, {}, any>
    export default component
}

// Add Inertia.js types
declare module '@inertiajs/vue3' {
    import type { ComponentPublicInstance } from 'vue'
    import type { InertiaAppProps } from '@inertiajs/inertia-vue3'

    export * from '@inertiajs/inertia-vue3'

    export function usePage<T = PageProps>(): { props: T & { auth: { user: User } } }
}

// Global type for the $page object
interface PageProps {
    auth: {
        user: User
    }
    [key: string]: any
}

// User type
interface User {
    id: number
    name: string
    email: string
    email_verified_at: string | null
    created_at: string
    updated_at: string
    profile_photo_url: string
}

// Extend the Window interface
declare global {
    interface Window {
        route: (name: string, params?: any) => string
    }
}