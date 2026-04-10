<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';
import { usePermissions } from '@/Composables/usePermissions';

const { canCreate, canUpdate, canDelete, canImport, canExport } = usePermissions();

interface Checksheet {
    id: number;
    item_code: string;
    item_name: string;
    production_date: string;
    lasermark_lot_number: string;
    machine_no: string;
    letter_code: string;
    jo_number: string;
    prod_qty: number;
    status: string;
    operator?: { id: number; name: string };
    technician?: { id: number; name: string };
    checked_by?: { id: number; name: string };
}

interface ChecksheetsResponse {
    data: Checksheet[];
    links: any[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Props {
    checksheets: ChecksheetsResponse;
    filters: {
        search?: string;
        date_from?: string;
        date_to?: string;
        status?: string;
        item_code?: string;
    };
    itemCodes: string[];
}

const props = withDefaults(defineProps<Props>(), {
    checksheets: () => ({
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
    itemCodes: () => []
});

const search = ref(props.filters.search || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const status = ref(props.filters.status || '');
const itemCode = ref(props.filters.item_code || '');

const applyFilters = () => {
    router.get(route('diaphragm-welding.index'), {
        search: search.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        status: status.value || undefined,
        item_code: itemCode.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

const resetFilters = () => {
    search.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    status.value = '';
    itemCode.value = '';
    router.get(route('diaphragm-welding.index'));
};

const confirmDelete = (checksheet: Checksheet) => {
    if (confirm(`Are you sure you want to delete checksheet for ${checksheet.item_code}?`)) {
        router.delete(route('diaphragm-welding.destroy', checksheet.id));
    }
};

const formatDate = (dateString: string): string => {
    if (!dateString) return '';
    try {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch {
        return dateString;
    }
};

const statusClass = (status: string) => {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-yellow-100 text-yellow-800';
    }
};
</script>

<template>
    <Head title="Diaphragm Welding Checksheets" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Diaphragm Welding Checksheets
                </h2>
                <div class="space-x-2">
                    <Link 
                        v-if="canImport('diaphragm')"
                        :href="route('diaphragm-welding.import.form')" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700"
                    >
                        Import
                    </Link>
                    <Link 
                        v-if="canCreate('diaphragm')"
                        :href="route('diaphragm-welding.create')" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                    >
                        New Checksheet
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input 
                                    type="text" 
                                    v-model="search"
                                    @keyup.enter="applyFilters"
                                    placeholder="Item code, lot #, JO #..."
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                                <input 
                                    type="date" 
                                    v-model="dateFrom"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                                <input 
                                    type="date" 
                                    v-model="dateTo"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select 
                                    v-model="status"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Item Code</label>
                                <select 
                                    v-model="itemCode"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option value="">All Item Codes</option>
                                    <option v-for="code in itemCodes" :key="code" :value="code">{{ code }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end space-x-2">
                            <button 
                                @click="resetFilters"
                                class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800"
                            >
                                Reset
                            </button>
                            <button 
                                @click="applyFilters"
                                class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700"
                            >
                                Apply Filters
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Code</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lasermark Lot #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Machine</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">JO Number</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="checksheet in checksheets.data" :key="checksheet.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(checksheet.production_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ checksheet.item_code || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ checksheet.lasermark_lot_number || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ checksheet.machine_no || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ checksheet.jo_number || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ checksheet.prod_qty || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                                :class="statusClass(checksheet.status)"
                                            >
                                                {{ checksheet.status ? checksheet.status.charAt(0).toUpperCase() + checksheet.status.slice(1) : 'N/A' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <Link 
                                                    :href="route('diaphragm-welding.show', checksheet.id)" 
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                    title="View"
                                                >
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                </Link>
                                                <Link 
                                                    v-if="canUpdate('diaphragm')"
                                                    :href="route('diaphragm-welding.edit', checksheet.id)" 
                                                    class="text-blue-600 hover:text-blue-900"
                                                    title="Edit"
                                                >
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </Link>
                                                <button 
                                                    v-if="canDelete('diaphragm')"
                                                    @click="confirmDelete(checksheet)" 
                                                    class="text-red-600 hover:text-red-900"
                                                    title="Delete"
                                                >
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!checksheets.data.length">
                                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No checksheets found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4" v-if="checksheets.links && checksheets.links.length > 3">
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-700">
                                    Showing <span class="font-medium">{{ checksheets.from }}</span> to 
                                    <span class="font-medium">{{ checksheets.to }}</span> of 
                                    <span class="font-medium">{{ checksheets.total }}</span> results
                                </div>
                                <div class="flex space-x-1">
                                    <template v-for="(link, index) in checksheets.links" :key="index">
                                        <Link 
                                            v-if="link.url"
                                            :href="link.url"
                                            v-html="link.label"
                                            class="px-4 py-2 border rounded-md text-sm font-medium"
                                            :class="{
                                                'bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                                                'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active
                                            }"
                                        />
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
