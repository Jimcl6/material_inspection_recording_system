<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
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
    checkpoint: {
        CheckpointID: number;
        CheckpointNumber: number;
        InspectorName_First?: string;
        Judgement_First?: string;
        InspectorName_Last?: string;
        Judgement_Last?: string;
        samples: Array<{
            SampleID: number;
            SampleOrder: number;
            Phase: string;
            Value: string;
        }>;
    };
}

const props = defineProps<Props>();

// Initialize form with checkpoint data
const checkpointData = props.checkpoint;
const samplesFirst = checkpointData.samples.filter(s => s.Phase === 'FIRST').sort((a, b) => a.SampleOrder - b.SampleOrder);
const samplesLast = checkpointData.samples.filter(s => s.Phase === 'LAST').sort((a, b) => a.SampleOrder - b.SampleOrder);

const form = useForm({
    CheckpointNumber: checkpointData.CheckpointNumber,
    InspectorName_First: checkpointData.InspectorName_First || '',
    Judgement_First: checkpointData.Judgement_First || '',
    InspectorName_Last: checkpointData.InspectorName_Last || '',
    Judgement_Last: checkpointData.Judgement_Last || '',
    samples_first: samplesFirst.length > 0 ? samplesFirst.map(s => s.Value) : [''],
    samples_last: samplesLast.length > 0 ? samplesLast.map(s => s.Value) : [''],
});

const addFirst = () => { form.samples_first.push(''); };
const removeFirst = (i: number) => { form.samples_first.splice(i, 1); };
const addLast = () => { form.samples_last.push(''); };
const removeLast = (i: number) => { form.samples_last.splice(i, 1); };

const submit = () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    // Transform samples to the expected format
    const samples: Array<{
        SampleOrder: number;
        Phase: string;
        Value: string;
    }> = [];
    
    // Add first phase samples
    form.samples_first.forEach((value, index) => {
        if (value && value.trim()) {
            samples.push({
                SampleOrder: index + 1,
                Phase: 'FIRST',
                Value: value.trim()
            });
        }
    });
    
    // Add last phase samples
    form.samples_last.forEach((value, index) => {
        if (value && value.trim()) {
            samples.push({
                SampleOrder: index + 1,
                Phase: 'LAST',
                Value: value.trim()
            });
        }
    });
    
    form.transform((data) => ({ 
        ...data, 
        _token: csrf,
        samples: samples
    }))
    .put(`/magnetism-checksheet/${props.batch.BatchID}/checkpoints/${props.checkpoint.CheckpointID}`, {
        onSuccess: () => {
            window.location.href = route('magnetism-checksheet.show', props.batch.BatchID);
        }
    });
};
</script>

<template>
    <Head title="Edit Checkpoint" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Checkpoint #{{ checkpoint.CheckpointNumber }} - Checksheet #{{ batch.BatchID }}
                </h2>
                <div class="space-x-2">
                    <Link
                        :href="route('magnetism-checksheet.show', batch.BatchID)"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Back to Checksheet
                    </Link>
                    <Link
                        :href="route('magnetism-checksheet.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Checksheets
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Checkpoint Information -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Checkpoint Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Checkpoint Number</label>
                                        <input
                                            v-model.number="form.CheckpointNumber"
                                            type="number"
                                            min="1"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Inspector Name (First)</label>
                                        <input
                                            v-model="form.InspectorName_First"
                                            type="text"
                                            maxlength="255"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Judgement (First)</label>
                                        <input
                                            v-model="form.Judgement_First"
                                            type="text"
                                            maxlength="255"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Inspector Name (Last)</label>
                                        <input
                                            v-model="form.InspectorName_Last"
                                            type="text"
                                            maxlength="255"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Judgement (Last)</label>
                                        <input
                                            v-model="form.Judgement_Last"
                                            type="text"
                                            maxlength="255"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- First Phase Samples -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">First Phase Samples</h3>
                                <div class="space-y-2">
                                    <div v-for="(sample, i) in form.samples_first" :key="'first-' + i" class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-gray-700 w-16">Sample {{ (i + 1).toString() }}:</span>
                                        <input
                                            v-model="form.samples_first[i as number]"
                                            type="text"
                                            maxlength="255"
                                            placeholder="Enter sample value"
                                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                        <button
                                            type="button"
                                            @click="removeFirst(i as number)"
                                            class="p-2 text-red-600 hover:text-red-900 rounded hover:bg-gray-100"
                                            v-if="form.samples_first.length > 1"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <button
                                        type="button"
                                        @click="addFirst"
                                        class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Add First Phase Sample
                                    </button>
                                </div>
                            </div>

                            <!-- Last Phase Samples -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Last Phase Samples</h3>
                                <div class="space-y-2">
                                    <div v-for="(sample, i) in form.samples_last" :key="'last-' + i" class="flex items-center space-x-2">
                                        <span class="text-sm font-medium text-gray-700 w-16">Sample {{ (i + 1).toString() }}:</span>
                                        <input
                                            v-model="form.samples_last[i as number]"
                                            type="text"
                                            maxlength="255"
                                            placeholder="Enter sample value"
                                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                        <button
                                            type="button"
                                            @click="removeLast(i as number)"
                                            class="p-2 text-red-600 hover:text-red-900 rounded hover:bg-gray-100"
                                            v-if="form.samples_last.length > 1"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <button
                                        type="button"
                                        @click="addLast"
                                        class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        Add Last Phase Sample
                                    </button>
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
                                        :href="route('magnetism-checksheet.show', batch.BatchID)"
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
                                        Update Checkpoint
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
