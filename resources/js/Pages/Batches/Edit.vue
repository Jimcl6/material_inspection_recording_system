<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { watch, ref } from 'vue';

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
}

const props = defineProps<Props>();

const form = useForm({
    ProductionDate: '',
    LetterCode: '',
    QRCode: '',
    MaterialLotNumber: '',
    ProduceQty: 0,
    JobNumber: '',
    TotalQty: 0,
    Remarks: '',
});

// Preload form data when batch prop changes
const preload = () => {
    if (!props.batch) return;
    form.ProductionDate = props.batch.ProductionDate || '';
    form.LetterCode = props.batch.LetterCode || '';
    form.QRCode = props.batch.QRCode || '';
    form.MaterialLotNumber = props.batch.MaterialLotNumber || '';
    form.ProduceQty = props.batch.ProduceQty ?? 0;
    form.JobNumber = props.batch.JobNumber || '';
    form.TotalQty = props.batch.TotalQty ?? 0;
    form.Remarks = props.batch.Remarks || '';
};

// Initialize form data
preload();

const submit = () => {
    if (!props.batch) return;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    form.transform((data) => ({ ...data, _token: csrf }))
        .put(`/batches/${props.batch.BatchID}`, {
            onSuccess: () => {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: 'Batch updated successfully!'
                    }
                }));
            }
        });
};
</script>

<template>
    <Head title="Edit Production Batch" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Batch #{{ batch.BatchID }}
                </h2>
                <div class="space-x-2">
                    <Link
                        :href="route('production-batches.show', batch.BatchID)"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        View Batch
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
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Basic Information Section -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Production Date</label>
                                        <input
                                            v-model="form.ProductionDate"
                                            type="date"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Letter Code</label>
                                        <input
                                            v-model="form.LetterCode"
                                            type="text"
                                            maxlength="5"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">QR Code</label>
                                        <input
                                            v-model="form.QRCode"
                                            type="text"
                                            maxlength="20"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Material Lot Number</label>
                                        <input
                                            v-model="form.MaterialLotNumber"
                                            type="text"
                                            maxlength="50"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            required
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Quantity Information Section -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Quantity Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Produce Qty</label>
                                        <input
                                            v-model.number="form.ProduceQty"
                                            type="number"
                                            min="0"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Qty</label>
                                        <input
                                            v-model.number="form.TotalQty"
                                            type="number"
                                            min="0"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Job Number</label>
                                        <input
                                            v-model="form.JobNumber"
                                            type="text"
                                            maxlength="50"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            required
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Remarks Section -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                                <textarea
                                    v-model="form.Remarks"
                                    rows="3"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                ></textarea>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-between items-center pt-6 border-t">
                                <div class="text-sm text-red-600" v-if="form.errors && Object.keys(form.errors).length">
                                    <div v-for="(msg, key) in form.errors" :key="key" class="mb-1">
                                        {{ msg }}
                                    </div>
                                </div>
                                <div class="flex space-x-3">
                                    <Link
                                        :href="route('production-batches.show', batch.BatchID)"
                                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        Cancel
                                    </Link>
                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        :disabled="form.processing"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Update Batch
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
