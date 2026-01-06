<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// Import route helper
declare function route(name: string, params?: any): string;

interface Props {
    batch?: {
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

const props = withDefaults(defineProps<Props>(), {
    batch: () => ({
        BatchID: 0,
        ProductionDate: '',
        LetterCode: '',
        QRCode: '',
        MaterialLotNumber: '',
        ProduceQty: 0,
        JobNumber: '',
        TotalQty: 0,
        Remarks: ''
    })
});

const form = useForm({
    CheckpointNumber: 1,
    InspectorName_First: '',
    Judgement_First: '',
    InspectorName_Last: '',
    Judgement_Last: '',
    samples_first: [''],
    samples_last: [''],
});

const addFirst = () => { form.samples_first.push(''); };
const removeFirst = (i: number) => { form.samples_first.splice(i, 1); };
const addLast = () => { form.samples_last.push(''); };
const removeLast = (i: number) => { form.samples_last.splice(i, 1); };

const submit = () => {
    if (!props.batch?.BatchID) {
        window.dispatchEvent(new CustomEvent('toast', {
            detail: {
                type: 'error',
                message: 'Batch information not available. Please try again.'
            }
        }));
        return;
    }
    
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    form.transform((data) => ({ ...data, _token: csrf }))
        .post(`/production-batches/${props.batch.BatchID}/checkpoints`, {
            onSuccess: () => {
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: 'Checkpoint created successfully!'
                    }
                }));
                // Redirect to batch show page
                window.location.href = route('production-batches.show', props.batch.BatchID);
            }
        });
};
</script>

<template>
    <Head title="Add Checkpoint" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Add Checkpoint - Batch #{{ batch.BatchID }}
                </h2>
                <div class="space-x-2">
                    <Link
                        :href="route('production-batches.show', batch.BatchID)"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Batch
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Error State -->
                <div v-if="!batch?.BatchID" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900">Batch Not Found</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                The batch information could not be loaded. Please go back and try again.
                            </p>
                            <div class="mt-6">
                                <Link
                                    :href="route('production-batches.index')"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Back to Batches
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div v-else>
                    <!-- Batch Info Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-4 bg-blue-50 border-b border-blue-200">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <h3 class="text-sm font-medium text-blue-900">Adding checkpoint to:</h3>
                                    <p class="text-sm text-blue-700">Batch #{{ batch.BatchID }} - {{ batch.QRCode }} - {{ batch.MaterialLotNumber }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <form @submit.prevent="submit" class="space-y-6">
                                <!-- Checkpoint Information Section -->
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Checkpoint Information</h3>
                                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Checkpoint #</label>
                                            <input
                                                v-model.number="form.CheckpointNumber"
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
                                    </div>
                                </div>

                                <!-- Samples Section -->
                                <div class="border-t pt-6">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Samples</h3>
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        <div>
                                            <div class="flex justify-between items-center mb-3">
                                                <label class="block text-sm font-medium text-gray-700">Samples (FIRST)</label>
                                                <button
                                                    type="button"
                                                    @click="addFirst"
                                                    class="inline-flex items-center px-3 py-1 bg-indigo-600 border border-transparent rounded-md text-xs font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                >
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                    Add Sample
                                                </button>
                                            </div>
                                            <div class="space-y-2">
                                                <div v-for="(sample, i) in form.samples_first" :key="'f' + i" class="flex items-center space-x-2">
                                                    <span class="inline-flex items-center justify-center w-12 h-10 rounded-md border border-gray-300 bg-gray-50 text-sm font-medium text-gray-700">
                                                        #{{ (i + 1).toString() }}
                                                    </span>
                                                    <input
                                                        v-model="form.samples_first[i]"
                                                        type="text"
                                                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    />
                                                    <button
                                                        type="button"
                                                        @click="removeFirst(i)"
                                                        class="inline-flex items-center p-2 border border-transparent rounded-md text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <div class="flex justify-between items-center mb-3">
                                                <label class="block text-sm font-medium text-gray-700">Samples (LAST)</label>
                                                <button
                                                    type="button"
                                                    @click="addLast"
                                                    class="inline-flex items-center px-3 py-1 bg-indigo-600 border border-transparent rounded-md text-xs font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                >
                                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                    </svg>
                                                    Add Sample
                                                </button>
                                            </div>
                                            <div class="space-y-2">
                                                <div v-for="(sample, i) in form.samples_last" :key="'l' + i" class="flex items-center space-x-2">
                                                    <span class="inline-flex items-center justify-center w-12 h-10 rounded-md border border-gray-300 bg-gray-50 text-sm font-medium text-gray-700">
                                                        #{{ (i + 1).toString() }}
                                                    </span>
                                                    <input
                                                        v-model="form.samples_last[i]"
                                                        type="text"
                                                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    />
                                                    <button
                                                        type="button"
                                                        @click="removeLast(i)"
                                                        class="inline-flex items-center p-2 border border-transparent rounded-md text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                                            class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                            :disabled="form.processing"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                            </svg>
                                            Create Checkpoint
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
