<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { route } from 'ziggy-js';
import { usePermissions } from '@/Composables/usePermissions';
import { useSingleExpandedRow } from '@/Composables/useSingleExpandedRow';
import RecordDetailPanel from '@/Components/RecordDetailPanel.vue';

const { canCreate, canUpdate, canDelete, canImport, canExport, approvalsEnabled } = usePermissions();
const { toggleExpanded, isExpanded } = useSingleExpandedRow();

interface Checksheet {
    id: number;
    item_code: string | null;
    item_name: string | null;
    production_date: string;
    machine_no: string | null;
    letter_code: string | null;
    job_number: string | null;
    prod_qty: number | null;
    quantity: number | null;
    status: string;
    type?: { id: number; name: string };
    operator?: { id: number; name: string };
    operator_name_raw?: string | null;
}

interface Paginated<T> {
    data: T[];
    links: any[];
    from: number;
    to: number;
    total: number;
}

const props = withDefaults(defineProps<{
    checksheets: Paginated<Checksheet>;
    filters: Record<string, any>;
    types: any[];
    itemCodes: string[];
}>(), {
    filters: () => ({}),
    types: () => [],
    itemCodes: () => [],
});

const search = ref(props.filters.search || '');
const typeId = ref(props.filters.checksheet_type_id || '');
const itemCode = ref(props.filters.item_code || '');
const machineNo = ref(props.filters.machine_no || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const status = ref(props.filters.status || '');

const applyFilters = () => {
    router.get(route('welding-checksheets.index'), {
        search: search.value || undefined,
        checksheet_type_id: typeId.value || undefined,
        item_code: itemCode.value || undefined,
        machine_no: machineNo.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
        status: approvalsEnabled.value ? status.value || undefined : undefined,
    }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    search.value = '';
    typeId.value = '';
    itemCode.value = '';
    machineNo.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    status.value = '';
    router.get(route('welding-checksheets.index'));
};

const confirmDelete = (checksheet: Checksheet) => {
    if (confirm(`Delete welding checksheet ${checksheet.item_code || checksheet.id}?`)) {
        router.delete(route('welding-checksheets.destroy', checksheet.id));
    }
};

const formatDate = (value: string): string => value ? new Date(value).toLocaleDateString() : '';

const statusClass = (value: string): string => {
    if (value === 'approved') return 'bg-green-100 text-green-800';
    if (value === 'rejected') return 'bg-red-100 text-red-800';
    return 'bg-yellow-100 text-yellow-800';
};

const checksheetDetailSections = (checksheet: Checksheet) => [
    {
        title: 'Record Details',
        items: [
            { label: 'Date', value: formatDate(checksheet.production_date) },
            { label: 'Type', value: checksheet.type?.name },
            { label: 'Item Code', value: checksheet.item_code },
            { label: 'Item Name', value: checksheet.item_name },
        ],
    },
    {
        title: 'Process Details',
        items: [
            { label: 'Machine', value: checksheet.machine_no },
            { label: 'Letter Code', value: checksheet.letter_code },
            { label: 'Job Number', value: checksheet.job_number },
            { label: 'Quantity', value: checksheet.prod_qty },
        ],
    },
    {
        title: 'Approval Details',
        items: [
            { label: 'Status', value: checksheet.status },
            { label: 'Operator', value: checksheet.operator?.name || checksheet.operator_name_raw },
        ],
    },
];
</script>

<template>
    <Head title="Welding Checksheet" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Welding Checksheet</h2>
                <div class="space-x-2 flex items-end mx-3">
                    <a
                        v-if="canExport('welding')"
                        :href="route('welding-checksheets.export')"
                        class="inline-flex items-center px-4 py-2 bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-800"
                    >
                        Export
                    </a>
                    <Link
                        v-if="canImport('welding')"
                        :href="route('welding-checksheets.import.form')"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700"
                    >
                        Import
                    </Link>
                    <Link
                        v-if="canCreate('welding')"
                        :href="route('welding-checksheets.create')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                    >
                        New Checksheet
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div
                            class="grid grid-cols-1 gap-4"
                            :class="approvalsEnabled ? 'md:grid-cols-7' : 'md:grid-cols-6'"
                        >
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <input v-model="search" @keyup.enter="applyFilters" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                                <select v-model="typeId" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">All Types</option>
                                    <option v-for="type in types" :key="type.id" :value="type.id">{{ type.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Item Code</label>
                                <select v-model="itemCode" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">All Codes</option>
                                    <option v-for="code in itemCodes" :key="code" :value="code">{{ code }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Machine</label>
                                <input v-model="machineNo" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                                <input v-model="dateFrom" type="date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                                <input v-model="dateTo" type="date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>
                            <div v-if="approvalsEnabled">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select v-model="status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">All Status</option>
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end space-x-2">
                            <button @click="resetFilters" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Reset</button>
                            <button @click="applyFilters" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-md hover:bg-indigo-700">Apply Filters</button>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="w-10 px-3 py-3">
                                            <span class="sr-only">Details</span>
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Code</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                                        <th v-if="approvalsEnabled" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <template v-for="checksheet in checksheets.data" :key="checksheet.id">
                                    <tr class="hover:bg-gray-50" :class="{ 'bg-indigo-50/40': isExpanded(checksheet.id) }">
                                        <td class="px-3 py-4 whitespace-nowrap">
                                            <button
                                                type="button"
                                                @click="toggleExpanded(checksheet.id)"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                :aria-expanded="isExpanded(checksheet.id)"
                                                :title="isExpanded(checksheet.id) ? 'Hide details' : 'Show details'"
                                            >
                                                <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-90': isExpanded(checksheet.id) }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                </svg>
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(checksheet.production_date) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ checksheet.type?.name || 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ checksheet.item_code || 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ checksheet.prod_qty ?? checksheet.quantity ?? 'N/A' }}</td>
                                        <td v-if="approvalsEnabled" class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="statusClass(checksheet.status)">
                                                {{ checksheet.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <Link
                                                    :href="route('welding-checksheets.show', checksheet.id)"
                                                    class="p-1 text-indigo-600 hover:text-indigo-900 rounded-full hover:bg-indigo-50"
                                                    title="View"
                                                >
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </Link>
                                                <Link
                                                    v-if="canUpdate('welding')"
                                                    :href="route('welding-checksheets.edit', checksheet.id)"
                                                    class="p-1 text-blue-600 hover:text-blue-900 rounded-full hover:bg-blue-50"
                                                    title="Edit"
                                                >
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </Link>
                                                <button
                                                    v-if="canDelete('welding')"
                                                    @click="confirmDelete(checksheet)"
                                                    class="p-1 text-red-600 hover:text-red-900 rounded-full hover:bg-red-50"
                                                    title="Delete"
                                                >
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="isExpanded(checksheet.id)" class="bg-gray-50">
                                        <td :colspan="approvalsEnabled ? 7 : 6" class="px-4 py-4">
                                            <RecordDetailPanel :sections="checksheetDetailSections(checksheet)" />
                                        </td>
                                    </tr>
                                    </template>
                                    <tr v-if="!checksheets.data.length">
                                        <td :colspan="approvalsEnabled ? 7 : 6" class="px-6 py-4 text-center text-sm text-gray-500">No welding checksheets found.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

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
