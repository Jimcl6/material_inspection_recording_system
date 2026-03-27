<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTableFilters from '@/Components/DataTableFilters.vue';

// Import route helper
declare function route(name: string, params?: any): string;

interface ModificationLog {
    id: number;
    prod_date: string;
    col_4m?: string;
    col_line?: string;
    model_code: string;
    item_for_modification: string;
    nature_of_change?: string;
    col_from?: string;
    col_to?: string;
    material_lot_no?: string;
    start_serial?: string;
    end_serial?: string;
    recorded_by: string;
    source_of_info?: string;
    approved_by?: string;
    job_no_transfer_order?: string;
    col_remarks?: string;
}

interface LogsResponse {
    data: ModificationLog[];
    links: any[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Props {
    logs: LogsResponse;
    filters: Record<string, string>;
    lineOptions: string[];
    fourMOptions: string[];
}

const props = withDefaults(defineProps<Props>(), {
    logs: () => ({
        data: [],
        links: [],
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
        from: 0,
        to: 0
    }),
    filters: () => ({}),
    lineOptions: () => [],
    fourMOptions: () => []
});

const filterConfig = computed(() => [
    {
        type: 'text',
        key: 'search',
        label: 'Search',
        placeholder: 'Model code, item, recorded by...',
    },
    {
        type: 'date',
        key: 'date_from',
        label: 'Date From',
    },
    {
        type: 'date',
        key: 'date_to',
        label: 'Date To',
    },
    {
        type: 'select',
        key: 'col_line',
        label: 'Line',
        placeholder: 'All Lines',
        options: props.lineOptions.map((l: string) => ({ value: l, label: l })),
    },
    {
        type: 'select',
        key: 'col_4m',
        label: '4M Category',
        placeholder: 'All Categories',
        options: props.fourMOptions.map((m: string) => ({ value: m, label: m })),
    },
]);

const formatDate = (dateString: string): string => {
    if (!dateString) return 'N/A';
    try {
        const options: Intl.DateTimeFormatOptions = { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        };
        return new Date(dateString).toLocaleDateString(undefined, options);
    } catch (error) {
        console.error('Error formatting date:', error);
        return 'Invalid date';
    }
};

const confirmDelete = (log: ModificationLog) => {
    if (confirm(`Are you sure you want to delete this modification log? This action cannot be undone.`)) {
        router.delete(`/modification-logs/${log.id}`, {
            onSuccess: () => {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: 'Modification log deleted successfully!'
                    }
                }));
            },
            onError: () => {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        message: 'Failed to delete modification log. Please try again.'
                    }
                }));
            }
        });
    }
};
</script>

<template>
    <Head title="Modification Logs" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Modification Logs
                </h2>
                <Link
                    :href="route('modification-logs.create')"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    New Modification Log
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <DataTableFilters
                    :filters="filters"
                    :filter-config="filterConfig"
                    route-name="modification-logs.index"
                />

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Table -->
                        <div class="overflow-x-auto lg:overflow-visible">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ID
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Model Code
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Item for Modification
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                            Recorded By
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="log in logs.data" :key="log.id" class="hover:bg-gray-50">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ log.id }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ formatDate(log.prod_date) }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ log.model_code || 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="text-sm text-gray-900 max-w-xs truncate" :title="log.item_for_modification">
                                                {{ log.item_for_modification || 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap hidden sm:table-cell">
                                            <div class="text-sm text-gray-900">
                                                {{ log.recorded_by || 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <Link
                                                    :href="route('modification-logs.show', log.id)"
                                                    class="p-1 text-indigo-600 hover:text-indigo-900 rounded hover:bg-gray-100"
                                                    title="View"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </Link>
                                                <Link
                                                    :href="route('modification-logs.edit', log.id)"
                                                    class="p-1 text-blue-600 hover:text-blue-900 rounded hover:bg-gray-100"
                                                    title="Edit"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </Link>
                                                <button
                                                    @click="confirmDelete(log)"
                                                    class="p-1 text-red-600 hover:text-red-900 rounded hover:bg-gray-100"
                                                    title="Delete"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!logs.data.length">
                                        <td colspan="6" class="px-4 py-4 text-center text-sm text-gray-500">
                                            No modification logs found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="logs.links.length > 3" class="mt-6">
                            <div class="flex flex-wrap -mb-1">
                                <template v-for="(link, key) in logs.links" :key="key">
                                    <div v-if="link.url === null" class="mb-1 mr-1 px-4 py-3 text-sm text-gray-400 border rounded-md">
                                        {{ link.label }}
                                    </div>
                                    <Link
                                        v-else
                                        :href="link.url"
                                        class="mb-1 mr-1 px-4 py-3 text-sm text-gray-700 bg-white border rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        :class="{ 'bg-indigo-600 text-white border-indigo-600': link.active }"
                                    >
                                        {{ link.label }}
                                    </Link>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
