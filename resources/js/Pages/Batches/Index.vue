<template>
    <Head title="Magnetism Checksheet" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Magnetism Checksheet
                </h2>
                <div class="space-x-2">
                    <Link 
                        :href="route('magnetism-checksheet.import.form')" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Import
                    </Link>
                    <Link 
                        :href="route('magnetism-checksheet.create')" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Checksheet
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Advanced Filters -->
                        <div class="mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                    <input
                                        v-model="filters.q"
                                        type="text"
                                        placeholder="QR / Lot / Job #"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">QR Code</label>
                                    <input
                                        v-model="filters.qr"
                                        type="text"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Lot</label>
                                    <input
                                        v-model="filters.lot"
                                        type="text"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                                    <input
                                        v-model="filters.date_from"
                                        type="date"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                                    <input
                                        v-model="filters.date_to"
                                        type="date"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    />
                                </div>
                            </div>
                            <div class="mt-4 flex space-x-2">
                                <button 
                                    @click="applyFilters"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                    </svg>
                                    Filter
                                </button>
                                <button 
                                    @click="resetFilters"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                    </svg>
                                    Reset
                                </button>
                            </div>
                        </div>

                        <div class="overflow-x-auto lg:overflow-visible">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Item Name
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                            Letter Code
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                            Item Code
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">
                                            Produce Qty
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="batch in batches.data" :key="batch.BatchID" class="hover:bg-gray-50">
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ formatDate(batch.ProductionDate) }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ batch.ItemName || 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap hidden sm:table-cell">
                                            <div class="text-sm text-gray-900">
                                                {{ batch.LetterCode || 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap hidden sm:table-cell">
                                            <div class="text-sm text-gray-900">
                                                {{ batch.ItemCode || 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap hidden sm:table-cell">
                                            <div class="text-sm text-gray-900">
                                                {{ batch.ProduceQty || 0 }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <Link
                                                    :href="route('magnetism-checksheet.show', { magnetism_checksheet: batch.BatchID })"
                                                    class="p-1 text-indigo-600 hover:text-indigo-900 rounded hover:bg-gray-100"
                                                    title="View"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </Link>
                                                <Link
                                                    :href="route('magnetism-checksheet.edit', { magnetism_checksheet: batch.BatchID })"
                                                    class="p-1 text-blue-600 hover:text-blue-900 rounded hover:bg-gray-100"
                                                    title="Edit"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </Link>
                                                <Link
                                                    :href="route('magnetism-checksheet.checkpoints.create', { magnetism_checksheet: batch.BatchID })"
                                                    class="p-1 text-green-600 hover:text-green-900 rounded hover:bg-gray-100"
                                                    title="Add Checkpoint"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                                    </svg>
                                                </Link>
                                                <button
                                                    @click="confirmDelete(batch)"
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
                                    <tr v-if="!batches.data.length">
                                        <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No production batches found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4" v-if="batches.links.length > 3">
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-700">
                                    Showing <span class="font-medium">{{ batches.from }}</span> to 
                                    <span class="font-medium">{{ batches.to }}</span> of 
                                    <span class="font-medium">{{ batches.total }}</span> results
                                </div>
                                <div class="flex space-x-1">
                                    <template v-for="(link, index) in batches.links" :key="index">
                                        <Link 
                                            v-if="link.url"
                                            :href="link.url"
                                            v-html="link.label"
                                            class="px-4 py-2 border rounded-md text-sm font-medium"
                                            :class="{
                                                'bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                                                'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active && link.url,
                                                'text-gray-300': !link.url
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

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showDeleteModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Delete Magnetism Checksheet
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to delete checksheet <span class="font-medium">#{{ batchToDelete?.BatchID }}</span>? This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button 
                            type="button" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="deleteBatch"
                        >
                            Delete
                        </button>
                        <button 
                            type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                            @click="showDeleteModal = false"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <!-- <NewBatchModal v-model="showNew" @created="onCreated" /> -->
        <!-- <CheckpointModal v-model="showCheckpoint" :batch-id="newBatchId" @saved="refreshList" /> -->
        <!-- <EditBatchModal v-model="showEdit" :batch="selectedBatch || undefined" @updated="refreshList" /> -->
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, reactive } from 'vue';

// Import route helper
declare function route(name: string, params?: any): string;

type Filters = {
    q?: string;
    qr?: string;
    lot?: string;
    date_from?: string;
    date_to?: string;
    [key: string]: any;
};

type ProductionBatch = {
    BatchID: number;
    ProductionDate: string;
    LetterCode: string;
    QRCode: string;
    MaterialLotNumber: string;
    ProduceQty: number;
    JobNumber: string;
    TotalQty: number;
    Remarks?: string;
    ItemName?: string;
    ItemCode?: string;
};

type BatchesResponse = {
    data: ProductionBatch[];
    links: any[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
};

interface Props {
    batches: BatchesResponse;
    filters: Filters;
}

const props = withDefaults(defineProps<Props>(), {
    batches: () => ({
        data: [],
        links: [],
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        from: 0,
        to: 0
    }),
    filters: () => ({
        q: '',
        qr: '',
        lot: '',
        date_from: '',
        date_to: ''
    })
});

const filters = reactive({
    q: props.filters?.q || '',
    qr: props.filters?.qr || '',
    lot: props.filters?.lot || '',
    date_from: props.filters?.date_from || '',
    date_to: props.filters?.date_to || '',
});

const showNew = ref(false);
const showCheckpoint = ref(false);
const newBatchId = ref<string | number | undefined>(undefined);
const showEdit = ref(false);
const selectedBatch = ref<ProductionBatch | null>(null);
const batchToDelete = ref<ProductionBatch | null>(null);
const showDeleteModal = ref<boolean>(false);
const page = usePage();
let shouldOpenCheckpoint = false;

const applyFilters = () => {
    const query = Object.fromEntries(
        Object.entries(filters).filter(([_, value]) => value !== '')
    );
    router.get(window.location.pathname, query, { preserveState: true, replace: true });
};

const resetFilters = () => {
    Object.assign(filters, {
        q: '',
        qr: '',
        lot: '',
        date_from: '',
        date_to: ''
    });
    applyFilters();
};

const destroyBatch = (id: number) => {
    if (confirm('Delete this checksheet?')) {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        router.post(`/magnetism-checksheet/${id}`, { _method: 'delete', _token: csrf }, { 
            preserveScroll: true,
            onSuccess: () => {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: 'Checksheet deleted successfully!'
                    }
                }));
            },
            onError: () => {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        message: 'Failed to delete checksheet. Please try again.'
                    }
                }));
            }
        });
    }
};

const confirmDelete = (batch: ProductionBatch) => {
    batchToDelete.value = batch;
    showDeleteModal.value = true;
};

const deleteBatch = (): void => {
    if (!batchToDelete.value) return;
    
    router.delete(`/magnetism-checksheet/${batchToDelete.value.BatchID}`, {
        onSuccess: (): void => {
            showDeleteModal.value = false;
            batchToDelete.value = null;
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    type: 'success',
                    message: 'Checksheet deleted successfully!'
                }
            }));
        },
        preserveScroll: true,
        onError: (error: Error): void => {
            console.error('Error deleting checksheet:', error);
            showDeleteModal.value = false;
            batchToDelete.value = null;
            
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    type: 'error',
                    message: 'Failed to delete Checksheet. Please try again.'
                }
            }));
        }
    });
};

const onCreated = (payload: any = {}) => {
    shouldOpenCheckpoint = !!payload.openCheckpoint;
    const query = Object.fromEntries(
        Object.entries(filters).filter(([_, value]) => value !== '')
    );
    router.get(window.location.pathname, query, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
        onSuccess: () => {
            const id = page.props.flash?.new_batch_id;
            if (id) {
                newBatchId.value = id;
                if (shouldOpenCheckpoint) showCheckpoint.value = true;
            }
        }
    });
};

const openCheckpoint = (batchId: number) => {
    newBatchId.value = batchId;
    showCheckpoint.value = true;
};

const refreshList = () => {
    const query = Object.fromEntries(
        Object.entries(filters).filter(([_, value]) => value !== '')
    );
    router.get(window.location.pathname, query, { preserveState: true, preserveScroll: true, replace: true });
};

const openEdit = (batch: ProductionBatch) => {
    selectedBatch.value = { ...batch };
    showEdit.value = true;
};

const formatDate = (dateString: string): string => {
    if (!dateString) return '';
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

// Import moduls
import NewBatchModal from './NewBatchModal.vue';
import CheckpointModal from './CheckpointModal.vue';
import EditBatchModal from './EditBatchModal.vue';
</script>
