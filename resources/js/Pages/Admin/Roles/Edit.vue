<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    role: Object,
    permissions: Object,
    assignedPermissions: Array,
});

const form = useForm({
    name: props.role.name,
    slug: props.role.slug,
    description: props.role.description || '',
    is_active: props.role.is_active,
    permissions: [...props.assignedPermissions],
});

const togglePermission = (permissionId) => {
    const index = form.permissions.indexOf(permissionId);
    if (index === -1) {
        form.permissions.push(permissionId);
    } else {
        form.permissions.splice(index, 1);
    }
};

const toggleModule = (modulePermissions) => {
    const moduleIds = modulePermissions.map(p => p.id);
    const allSelected = moduleIds.every(id => form.permissions.includes(id));
    
    if (allSelected) {
        form.permissions = form.permissions.filter(id => !moduleIds.includes(id));
    } else {
        moduleIds.forEach(id => {
            if (!form.permissions.includes(id)) {
                form.permissions.push(id);
            }
        });
    }
};

const isModuleFullySelected = (modulePermissions) => {
    return modulePermissions.every(p => form.permissions.includes(p.id));
};

const submit = () => {
    form.put(route('admin.roles.update', props.role.id));
};
</script>

<template>
    <AppLayout>
        <Head :title="role.is_system ? 'View Role' : 'Edit Role'" />

        <div class="py-6">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <Link
                        :href="route('admin.roles.index')"
                        class="text-blue-600 hover:text-blue-900 text-sm flex items-center"
                    >
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Roles
                    </Link>
                    <div class="flex items-center mt-2">
                        <h1 class="text-2xl font-semibold text-gray-900">
                            {{ role.is_system ? 'View Role' : 'Edit Role' }}
                        </h1>
                        <span v-if="role.is_system" class="ml-3 px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full">
                            System Role
                        </span>
                    </div>
                    <p class="text-sm text-gray-600">{{ role.users_count || 0 }} users assigned</p>
                </div>

                <!-- System Role Warning -->
                <div v-if="role.is_system" class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                This is a system role. You can modify permissions but cannot change the role's name, slug, or delete it.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Name *</label>
                                <input
                                    v-model="form.name"
                                    type="text"
                                    :disabled="role.is_system"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    :class="{ 
                                        'border-red-500': form.errors.name,
                                        'bg-gray-100 cursor-not-allowed': role.is_system
                                    }"
                                />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Slug *</label>
                                <input
                                    v-model="form.slug"
                                    type="text"
                                    :disabled="role.is_system"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono"
                                    :class="{ 
                                        'border-red-500': form.errors.slug,
                                        'bg-gray-100 cursor-not-allowed': role.is_system
                                    }"
                                />
                                <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea
                                    v-model="form.description"
                                    rows="2"
                                    :disabled="role.is_system"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    :class="{ 'bg-gray-100 cursor-not-allowed': role.is_system }"
                                ></textarea>
                            </div>

                            <div v-if="!role.is_system" class="flex items-center">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    id="is_active"
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                />
                                <label for="is_active" class="ml-2 block text-sm text-gray-700">Active</label>
                            </div>
                        </div>
                    </div>

                    <!-- Permissions -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">Role Permissions</h2>
                        <p class="text-sm text-gray-600 mb-4">
                            {{ role.is_system ? 'Modify the permissions for this system role.' : 'Select permissions that users with this role will have.' }}
                        </p>
                        
                        <div class="space-y-4">
                            <div v-for="(modulePerms, moduleName) in permissions" :key="moduleName" class="border rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-medium text-gray-900 capitalize">{{ moduleName }}</h3>
                                    <button
                                        type="button"
                                        @click="toggleModule(modulePerms)"
                                        class="text-xs text-blue-600 hover:text-blue-800"
                                    >
                                        {{ isModuleFullySelected(modulePerms) ? 'Deselect All' : 'Select All' }}
                                    </button>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                    <label
                                        v-for="perm in modulePerms"
                                        :key="perm.id"
                                        class="flex items-center space-x-2 text-sm"
                                    >
                                        <input
                                            type="checkbox"
                                            :checked="form.permissions.includes(perm.id)"
                                            @change="togglePermission(perm.id)"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                        <span class="capitalize">{{ perm.action }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <Link
                            :href="route('admin.roles.index')"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-blue-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
