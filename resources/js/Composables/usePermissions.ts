import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface PageProps {
    auth: {
        user: {
            id: number;
            name: string;
            email: string;
            role?: {
                name: string;
                slug: string;
            };
            position?: {
                name: string;
            };
        } | null;
        permissions: Record<string, boolean>;
    };
}

export function usePermissions() {
    const page = usePage<PageProps>();

    const permissions = computed(() => page.props.auth?.permissions ?? {});
    const user = computed(() => page.props.auth?.user);

    /**
     * Check if user has a specific permission
     * @param module - The module name (e.g., 'annealing', 'temperature')
     * @param action - The action name (e.g., 'view', 'create', 'update', 'delete')
     */
    const can = (module: string, action: string): boolean => {
        const perms = permissions.value;
        
        // Super admin has all permissions (indicated by '*' key)
        if (perms['*'] === true) {
            return true;
        }

        const key = `${module}.${action}`;
        return perms[key] === true;
    };

    /**
     * Check if user can view a module
     */
    const canView = (module: string): boolean => can(module, 'view');

    /**
     * Check if user can create in a module
     */
    const canCreate = (module: string): boolean => can(module, 'create');

    /**
     * Check if user can update in a module
     */
    const canUpdate = (module: string): boolean => can(module, 'update');

    /**
     * Check if user can delete in a module
     */
    const canDelete = (module: string): boolean => can(module, 'delete');

    /**
     * Check if user can import in a module
     */
    const canImport = (module: string): boolean => can(module, 'import');

    /**
     * Check if user can export in a module
     */
    const canExport = (module: string): boolean => can(module, 'export');

    /**
     * Check if user can approve in a module
     */
    const canApprove = (module: string): boolean => can(module, 'approve');

    /**
     * Check if user is a super admin
     */
    const isSuperAdmin = computed((): boolean => {
        return user.value?.role?.slug === 'super_admin';
    });

    /**
     * Check if user is an admin (includes super_admin)
     */
    const isAdmin = computed((): boolean => {
        const roleSlug = user.value?.role?.slug;
        return roleSlug === 'admin' || roleSlug === 'super_admin';
    });

    /**
     * Check if user has any permission for a module
     */
    const hasAnyModulePermission = (module: string): boolean => {
        const perms = permissions.value;
        
        // Super admin has all permissions
        if (perms['*'] === true) {
            return true;
        }

        // Check if any permission exists for this module
        return Object.keys(perms).some(key => key.startsWith(`${module}.`));
    };

    return {
        permissions,
        user,
        can,
        canView,
        canCreate,
        canUpdate,
        canDelete,
        canImport,
        canExport,
        canApprove,
        isSuperAdmin,
        isAdmin,
        hasAnyModulePermission,
    };
}
