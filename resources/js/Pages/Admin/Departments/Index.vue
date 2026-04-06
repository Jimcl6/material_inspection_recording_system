<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { debounce } from 'lodash';

const props = defineProps({
    departments: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');

const applyFilters = debounce(() => {
    router.get(route('admin.departments.index'), {
        search: search.value || undefined,
        status: status.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch([search, status], applyFilters);

const clearFilters = () => {
    search.value = '';
    status.value = '';
    router.get(route('admin.departments.index'));
};

const toggleStatus = (department) => {
    router.post(route('admin.departments.toggle-status', department.id), {}, {
        preserveScroll: true,
    });
};

const deleteDepartment = (department) => {
    if (confirm(`Are you sure you want to delete "${department.name}"? This action cannot be undone.`)) {
        router.delete(route('admin.departments.destroy', department.id), {
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Department Management" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Departments</h1>
                        <p class="mt-1 text-sm text-gray-600">Manage organizational departments</p>
                    </div>
                    <Link
                        :href="route('admin.departments.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Department
                    </Link>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow mb-6 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search by name or code..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select
                                v-model="status"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button
                                @click="clearFilters"
                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900"
                            >
                                Clear Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Positions</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="department in departments.data" :key="department.id" class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ department.name }}</div>
                                    <div v-if="department.description" class="text-sm text-gray-500 truncate max-w-xs">{{ department.description }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ department.code }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ department.users_count || 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ department.positions_count || 0 }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        :class="[
                                            'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                            department.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                                        ]"
                                    >
                                        {{ department.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <Link
                                        :href="route('admin.departments.edit', department.id)"
                                        class="text-blue-600 hover:text-blue-900 mr-3"
                                    >
                                        Edit
                                    </Link>
                                    <button
                                        @click="toggleStatus(department)"
                                        :class="department.is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900'"
                                        class="mr-3"
                                    >
                                        {{ department.is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                    <button
                                        @click="deleteDepartment(department)"
                                        class="text-red-600 hover:text-red-900"
                                        :disabled="department.users_count > 0"
                                        :class="{ 'opacity-50 cursor-not-allowed': department.users_count > 0 }"
                                    >
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="departments.data.length === 0">
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    No departments found.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="departments.links && departments.links.length > 3" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <nav class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ departments.from }} to {{ departments.to }} of {{ departments.total }} results
                            </div>
                            <div class="flex space-x-1">
                                <Link
                                    v-for="link in departments.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    :class="[
                                        'px-3 py-2 text-sm rounded-md',
                                        link.active ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100',
                                        !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                    ]"
                                    v-html="link.label"
                                    :disabled="!link.url"
                                />
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
