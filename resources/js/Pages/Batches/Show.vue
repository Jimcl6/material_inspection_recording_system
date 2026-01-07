<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Import route helper
declare function route(name: string, params?: any): string;

interface Props {
    batch: {
        BatchID: number;
        ProductionDate: string;
        LetterCode: string;
        QRCode: string;
        MaterialLotNumber: string;
        ProduceQty: number;
        JobNumber: string;
        TotalQty: number;
        Remarks?: string;
    };
    checkpoints: Array<{
        CheckpointID: number;
        CheckpointNumber: number;
        InspectorName_First: string;
        Judgement_First: string;
        InspectorName_Last: string;
        Judgement_Last: string;
        samples_count: number;
    }>;
}

const props = defineProps<Props>();

const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const form = useForm({
    CheckpointNumber: '',
    InspectorName_First: '',
    Judgement_First: '',
    InspectorName_Last: '',
    Judgement_Last: '',
});

const submitAdd = () => {
    form.transform((data) => ({ ...data, _token: csrf }))
        .post(route('production-batches.checkpoints.store', { production_batch: props.batch.BatchID }), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
};

const destroyCheckpoint = (id: number) => {
    if (confirm('Delete this checkpoint?')) {
        router.post(`/checkpoints/${id}`, { _method: 'delete', _token: csrf }, { preserveScroll: true });
    }
};

const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};
</script>

<template>
    <Head title="Production Batch Details" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Batch #{{ batch.BatchID }}
                </h2>
                <div class="space-x-2">
                    <Link
                        :href="route('production-batches.edit', { production_batch: batch.BatchID })"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Batch
                    </Link>
                    <Link
                        :href="route('production-batches.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Batches
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Batch Information Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Batch Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Production Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ formatDate(batch.ProductionDate) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Letter Code</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ batch.LetterCode || 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">QR Code</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ batch.QRCode || 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Material Lot Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ batch.MaterialLotNumber || 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Produce Qty</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ batch.ProduceQty || 0 }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Job Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ batch.JobNumber || 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Total Qty</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ batch.TotalQty || 0 }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Remarks</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ batch.Remarks || 'N/A' }}</dd>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Checkpoints Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Checkpoints</h3>
                            <Link
                                :href="route('production-batches.checkpoints.create', { production_batch: batch.BatchID })"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Add Checkpoint
                            </Link>
                        </div>

                        <!-- Quick Add Form -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Quick Add Checkpoint</h4>
                            <form @submit.prevent="submitAdd" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Checkpoint #</label>
                                    <input
                                        v-model="form.CheckpointNumber"
                                        type="number"
                                        min="1"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        required
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Inspector (First)</label>
                                    <input
                                        v-model="form.InspectorName_First"
                                        type="text"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Judgement (First)</label>
                                    <input
                                        v-model="form.Judgement_First"
                                        type="text"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Inspector (Last)</label>
                                    <input
                                        v-model="form.InspectorName_Last"
                                        type="text"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Judgement (Last)</label>
                                    <input
                                        v-model="form.Judgement_Last"
                                        type="text"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    />
                                </div>
                                <div class="flex items-end">
                                    <button
                                        type="submit"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        :disabled="form.processing"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Add
                                    </button>
                                </div>
                            </form>
                            <div class="mt-3 text-sm text-red-600" v-if="form.errors && Object.keys(form.errors).length">
                                <div v-for="(msg, key) in form.errors" :key="key" class="mb-1">
                                    {{ msg }}
                                </div>
                            </div>
                        </div>

                        <!-- Checkpoints Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            ID
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            #
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Inspector (First)
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Judgement (First)
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Inspector (Last)
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Judgement (Last)
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Samples
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="checkpoint in checkpoints" :key="checkpoint.CheckpointID" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ checkpoint.CheckpointID }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ checkpoint.CheckpointNumber }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ checkpoint.InspectorName_First || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ checkpoint.Judgement_First || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ checkpoint.InspectorName_Last || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ checkpoint.Judgement_Last || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ checkpoint.samples_count || 0 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <Link
                                                    :href="`/checkpoints/${checkpoint.CheckpointID}/edit`"
                                                    class="p-1 text-blue-600 hover:text-blue-900 rounded hover:bg-gray-100"
                                                    title="Manage"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </Link>
                                                <button
                                                    @click="destroyCheckpoint(checkpoint.CheckpointID)"
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
                                    <tr v-if="!checkpoints.length">
                                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No checkpoints found for this batch.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
