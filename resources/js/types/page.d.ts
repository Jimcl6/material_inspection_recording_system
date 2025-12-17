import { PageProps as InertiaPageProps } from '@inertiajs/core';

declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps {
        auth: {
            user: {
                id: number;
                name: string;
                email: string;
                role_id?: number;
            } | null;
        };
        flash: {
            success?: string;
            error?: string;
            warning?: string;
            info?: string;
        };
        errors: Record<string, string>;
        users?: Array<{
            id: number;
            name: string;
            email: string;
        }>;
    }
}

export {};
