<template>
    <Head :title="`Magnetism Checksheet - ${checksheet.item_code}`" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Magnetism Checksheet: {{ checksheet.item_code }}
                </h2>
                <div class="space-x-2">
                    <Link 
                        :href="route('magnetism-checksheet.index')" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to List
                    </Link>
                    <Link
                        v-if="canUpdate('magnetism')"
                        :href="route('magnetism-checksheet.edit', checksheet.id)"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Header
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto space-y-6">
                <!-- Header Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <span class="text-sm text-gray-500">Item Code</span>
                                <p class="text-lg font-semibold">{{ checksheet.item_code }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Item Name</span>
                                <p class="text-lg font-semibold">{{ checksheet.item_name }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Machine No.</span>
                                <p class="text-lg font-semibold">{{ checksheet.machine_no }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Month/Year</span>
                                <p class="text-lg font-semibold">{{ checksheet.month_year_display }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Date Selection -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <label class="text-sm font-medium text-gray-700">Production Date:</label>
                                <select
                                    v-model="currentDate"
                                    @change="changeDate"
                                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                    <option value="">Select Date</option>
                                    <option v-for="date in productionDates" :key="date" :value="date">
                                        {{ formatDate(date) }}
                                    </option>
                                </select>
                            </div>
                            <button
                                v-if="canCreate('magnetism')"
                                @click="showBatchModal = true; editingBatch = null"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Batch
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Batches Table -->
                <div v-if="selectedDate" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Production Batches</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Letter</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Material Lot Number</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">QR Code</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Produce Qty</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Job Number</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Remarks</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="batch in batches" :key="batch.id" class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap font-semibold text-blue-600">{{ batch.letter_code }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">{{ batch.material_lot_number }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">{{ batch.qr_code }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right">{{ batch.produce_qty }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">{{ batch.job_number }}</td>
                                        <td class="px-4 py-3 text-sm">{{ batch.remarks || '-' }}</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right">
                                            <button
                                                v-if="canUpdate('magnetism')"
                                                @click="editBatch(batch)"
                                                class="p-1 text-blue-600 hover:text-blue-900 rounded hover:bg-gray-100"
                                                title="Edit"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button
                                                v-if="canDelete('magnetism')"
                                                @click="confirmDeleteBatch(batch)"
                                                class="p-1 text-red-600 hover:text-red-900 rounded hover:bg-gray-100"
                                                title="Delete"
                                            >
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="batches.length === 0">
                                        <td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500">
                                            No batches for this date. Click "Add Batch" to create one.
                                        </td>
                                    </tr>
                                    <tr v-if="batches.length > 0" class="bg-gray-100 font-semibold">
                                        <td colspan="3" class="px-4 py-3 text-right">Total QTY:</td>
                                        <td class="px-4 py-3 text-right text-blue-600">{{ totalQty }}</td>
                                        <td colspan="3"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Checkpoints Grid -->
                <div v-if="selectedDate" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Inspection Checkpoints</h3>
                            <span class="text-sm text-gray-500">Tesla Standard: {{ teslaStandard.min }} ~ {{ teslaStandard.max }} mT</span>
                        </div>
                        
                        <form @submit.prevent="saveCheckpoints">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase" rowspan="2">Checkpoint</th>
                                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-l" colspan="7">First Inspection</th>
                                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase border-l" colspan="7">Last Inspection</th>
                                        </tr>
                                        <tr>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 border-l">1</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500">2</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500">3</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500">4</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500">5</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500">Operator</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500">Judge</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500 border-l">1</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500">2</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500">3</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500">4</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500">5</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500">Operator</th>
                                            <th class="px-2 py-2 text-center text-xs font-medium text-gray-500">Judge</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(cp, idx) in checkpointForm" :key="cp.checkpoint_number" class="hover:bg-gray-50">
                                            <td class="px-3 py-2 whitespace-nowrap font-medium text-gray-900">
                                                {{ cp.position_label }}
                                            </td>
                                            <!-- First Inspection Samples -->
                                            <td v-for="s in 5" :key="'f'+s" class="px-1 py-1" :class="{ 'border-l': s === 1 }">
                                                <input
                                                    v-model.number="cp.samples_first[s-1]"
                                                    type="number"
                                                    step="0.1"
                                                    class="w-16 text-center text-sm rounded border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    :class="getSampleClass(cp.samples_first[s-1])"
                                                    :disabled="!canUpdate('magnetism')"
                                                />
                                            </td>
                                            <td class="px-1 py-1">
                                                <input
                                                    v-model="cp.operator_first"
                                                    type="text"
                                                    class="w-24 text-sm rounded border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    :disabled="!canUpdate('magnetism')"
                                                />
                                            </td>
                                            <td class="px-1 py-1">
                                                <select
                                                    v-model="cp.judgment_first"
                                                    class="w-16 text-sm rounded border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    :disabled="!canUpdate('magnetism')"
                                                >
                                                    <option value="">-</option>
                                                    <option value="OK">OK</option>
                                                    <option value="NG">NG</option>
                                                </select>
                                            </td>
                                            <!-- Last Inspection Samples -->
                                            <td v-for="s in 5" :key="'l'+s" class="px-1 py-1" :class="{ 'border-l': s === 1 }">
                                                <input
                                                    v-model.number="cp.samples_last[s-1]"
                                                    type="number"
                                                    step="0.1"
                                                    class="w-16 text-center text-sm rounded border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    :class="getSampleClass(cp.samples_last[s-1])"
                                                    :disabled="!canUpdate('magnetism')"
                                                />
                                            </td>
                                            <td class="px-1 py-1">
                                                <input
                                                    v-model="cp.operator_last"
                                                    type="text"
                                                    class="w-24 text-sm rounded border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    :disabled="!canUpdate('magnetism')"
                                                />
                                            </td>
                                            <td class="px-1 py-1">
                                                <select
                                                    v-model="cp.judgment_last"
                                                    class="w-16 text-sm rounded border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    :disabled="!canUpdate('magnetism')"
                                                >
                                                    <option value="">-</option>
                                                    <option value="OK">OK</option>
                                                    <option value="NG">NG</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div v-if="canUpdate('magnetism')" class="mt-4 flex justify-end">
                                <button
                                    type="submit"
                                    :disabled="savingCheckpoints"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 disabled:opacity-50"
                                >
                                    <svg v-if="savingCheckpoints" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Save Checkpoints
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- No Date Selected -->
                <div v-if="!selectedDate && productionDates.length === 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 text-center">
                        <p class="text-gray-500">No production dates found. Add a batch to get started.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batch Modal -->
        <BatchModal
            v-model="showBatchModal"
            :checksheet-id="checksheet.id"
            :batch="editingBatch"
            :selected-date="currentDate"
            @saved="onBatchSaved"
        />

        <!-- Delete Batch Confirmation -->
        <div v-if="showDeleteBatchModal" class="fixed inset-0 overflow-y-auto z-50">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showDeleteBatchModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Delete Batch</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to delete batch <span class="font-medium">{{ batchToDelete?.letter_code }}</span>?
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button @click="deleteBatch" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm">
                            Delete
                        </button>
                        <button @click="showDeleteBatchModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, reactive, computed, watch } from 'vue';
import { usePermissions } from '@/Composables/usePermissions';
import BatchModal from '@/Components/Magnetism/BatchModal.vue';

declare function route(name: string, params?: any): string;

const { canCreate, canUpdate, canDelete } = usePermissions();

interface Batch {
    id: number;
    letter_code: string;
    material_lot_number: string;
    qr_code: string;
    produce_qty: number;
    job_number: string;
    remarks: string | null;
}

interface Checkpoint {
    id: number | null;
    checkpoint_number: number;
    position_label: string;
    samples_first: (number | null)[];
    operator_first: string | null;
    judgment_first: string | null;
    samples_last: (number | null)[];
    operator_last: string | null;
    judgment_last: string | null;
    checked_by: string | null;
}

interface Props {
    checksheet: {
        id: number;
        item_code: string;
        item_name: string;
        machine_no: string;
        month: number;
        year: number;
        month_year_display: string;
    };
    productionDates: string[];
    selectedDate: string | null;
    batches: Batch[];
    totalQty: number;
    checkpoints: Checkpoint[];
    teslaStandard: { min: number; max: number };
}

const props = defineProps<Props>();

const currentDate = ref(props.selectedDate || '');
const showBatchModal = ref(false);
const editingBatch = ref<Batch | null>(null);
const showDeleteBatchModal = ref(false);
const batchToDelete = ref<Batch | null>(null);
const savingCheckpoints = ref(false);

const checkpointForm = reactive<Checkpoint[]>(
    props.checkpoints.map(cp => ({
        ...cp,
        samples_first: [...cp.samples_first],
        samples_last: [...cp.samples_last],
    }))
);

watch(() => props.checkpoints, (newCheckpoints) => {
    checkpointForm.length = 0;
    newCheckpoints.forEach(cp => {
        checkpointForm.push({
            ...cp,
            samples_first: [...cp.samples_first],
            samples_last: [...cp.samples_last],
        });
    });
});

const formatDate = (dateStr: string): string => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const changeDate = () => {
    if (currentDate.value) {
        router.get(route('magnetism-checksheet.show', props.checksheet.id), { date: currentDate.value }, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};

const getSampleClass = (value: number | null): string => {
    if (value === null || value === undefined) return '';
    if (value < props.teslaStandard.min || value > props.teslaStandard.max) {
        return 'bg-red-100 border-red-300 text-red-700';
    }
    return 'bg-green-50';
};

const editBatch = (batch: Batch) => {
    editingBatch.value = batch;
    showBatchModal.value = true;
};

const confirmDeleteBatch = (batch: Batch) => {
    batchToDelete.value = batch;
    showDeleteBatchModal.value = true;
};

const deleteBatch = () => {
    if (!batchToDelete.value) return;
    
    router.delete(route('magnetism-checksheet.batches.destroy', {
        magnetism_checksheet: props.checksheet.id,
        batch: batchToDelete.value.id,
    }), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteBatchModal.value = false;
            batchToDelete.value = null;
        },
    });
};

const onBatchSaved = () => {
    showBatchModal.value = false;
    editingBatch.value = null;
};

const saveCheckpoints = () => {
    savingCheckpoints.value = true;
    
    router.put(route('magnetism-checksheet.checkpoints.update', props.checksheet.id), {
        production_date: currentDate.value,
        checkpoints: checkpointForm.map(cp => ({
            checkpoint_number: cp.checkpoint_number,
            samples_first: cp.samples_first,
            operator_first: cp.operator_first,
            judgment_first: cp.judgment_first,
            samples_last: cp.samples_last,
            operator_last: cp.operator_last,
            judgment_last: cp.judgment_last,
            checked_by: cp.checked_by,
        })),
    }, {
        preserveScroll: true,
        onFinish: () => {
            savingCheckpoints.value = false;
        },
    });
};
</script>
