<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { reactive, ref } from 'vue';

// Import route helper
declare function route(name: string, params?: any): string;

interface CheckpointData {
    CheckpointID: number | null;
    CheckpointNumber: number;
    label: string;
    Judgement_First: string;
    Judgement_Last: string;
    samples_first: string[];
    samples_last: string[];
}

interface Props {
    batch: {
        BatchID: number;
        ProductionDate: string;
        LetterCode: string;
        QRCode: string;
        MaterialLotNumber: string;
    };
    checkpoints: CheckpointData[];
    inspectorFirst: string;
    inspectorLast: string;
}

const props = defineProps<Props>();

// Initialize reactive state from props
const inspectorFirst = ref(props.inspectorFirst || '');
const inspectorLast = ref(props.inspectorLast || '');

const checkpoints = reactive<CheckpointData[]>(
    props.checkpoints.map(cp => ({
        ...cp,
        samples_first: [...cp.samples_first],
        samples_last: [...cp.samples_last],
    }))
);

const form = useForm({});

const submit = () => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    const payload = {
        _token: csrf,
        inspectorFirst: inspectorFirst.value,
        inspectorLast: inspectorLast.value,
        checkpoints: checkpoints.map(cp => ({
            CheckpointNumber: cp.CheckpointNumber,
            Judgement_First: cp.Judgement_First,
            Judgement_Last: cp.Judgement_Last,
            samples_first: cp.samples_first,
            samples_last: cp.samples_last,
        })),
    };
    
    form.transform(() => payload)
        .put(`/magnetism-checksheet/${props.batch.BatchID}/checkpoints/update`, {
            onSuccess: () => {
                window.location.href = route('magnetism-checksheet.show', props.batch.BatchID);
            }
        });
};
</script>

<template>
    <Head title="Edit Inspection Samples" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Inspection Samples - Checksheet #{{ batch.BatchID }}
                </h2>
                <div class="space-x-2">
                    <Link
                        :href="route('magnetism-checksheet.show', batch.BatchID)"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Checksheet
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Batch Info Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-4 bg-blue-50 border-b border-blue-200">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h3 class="text-sm font-medium text-blue-900">Editing samples for:</h3>
                                <p class="text-sm text-blue-700">Batch #{{ batch.BatchID }} - {{ batch.QRCode }} - {{ batch.MaterialLotNumber }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit" class="space-y-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Inspection Samples</h3>
                            
                            <!-- Inspector Names -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Inspector (First Inspection)</label>
                                    <input
                                        v-model="inspectorFirst"
                                        type="text"
                                        placeholder="Enter inspector name"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Inspector (Last Inspection)</label>
                                    <input
                                        v-model="inspectorLast"
                                        type="text"
                                        placeholder="Enter inspector name"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    />
                                </div>
                            </div>

                            <!-- Samples Grid -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full border border-gray-200 rounded-lg">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th rowspan="2" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-r border-gray-200 align-middle">
                                                Checkpoint
                                            </th>
                                            <th colspan="6" class="px-3 py-2 text-center text-xs font-medium text-blue-600 uppercase tracking-wider border-b border-r border-gray-200 bg-blue-50">
                                                First Inspection (N=5)
                                            </th>
                                            <th colspan="6" class="px-3 py-2 text-center text-xs font-medium text-green-600 uppercase tracking-wider border-b border-gray-200 bg-green-50">
                                                Last Inspection (N=5)
                                            </th>
                                        </tr>
                                        <tr>
                                            <th v-for="n in 5" :key="'fh'+n" class="px-2 py-1 text-center text-xs font-medium text-gray-500 border-b border-r border-gray-200 bg-blue-50 w-16">
                                                {{ n }}
                                            </th>
                                            <th class="px-2 py-1 text-center text-xs font-medium text-gray-500 border-b border-r border-gray-200 bg-blue-50 w-20">
                                                Judge
                                            </th>
                                            <th v-for="n in 5" :key="'lh'+n" class="px-2 py-1 text-center text-xs font-medium text-gray-500 border-b border-r border-gray-200 bg-green-50 w-16">
                                                {{ n }}
                                            </th>
                                            <th class="px-2 py-1 text-center text-xs font-medium text-gray-500 border-b border-gray-200 bg-green-50 w-20">
                                                Judge
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="cp in checkpoints" :key="cp.CheckpointNumber" class="hover:bg-gray-50">
                                            <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-200">
                                                {{ cp.CheckpointNumber }} - {{ cp.label }}
                                            </td>
                                            <!-- First Inspection Samples -->
                                            <td v-for="(_, i) in cp.samples_first" :key="'f'+cp.CheckpointNumber+'-'+i" class="px-1 py-1 border-r border-gray-200">
                                                <input
                                                    v-model="cp.samples_first[i]"
                                                    type="text"
                                                    class="w-14 text-center text-sm rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    placeholder="-"
                                                />
                                            </td>
                                            <!-- First Inspection Judgement -->
                                            <td class="px-1 py-1 border-r border-gray-200">
                                                <input
                                                    v-model="cp.Judgement_First"
                                                    type="text"
                                                    class="w-16 text-center text-sm rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    placeholder="OK"
                                                />
                                            </td>
                                            <!-- Last Inspection Samples -->
                                            <td v-for="(_, i) in cp.samples_last" :key="'l'+cp.CheckpointNumber+'-'+i" class="px-1 py-1 border-r border-gray-200">
                                                <input
                                                    v-model="cp.samples_last[i]"
                                                    type="text"
                                                    class="w-14 text-center text-sm rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    placeholder="-"
                                                />
                                            </td>
                                            <!-- Last Inspection Judgement -->
                                            <td class="px-1 py-1">
                                                <input
                                                    v-model="cp.Judgement_Last"
                                                    type="text"
                                                    class="w-16 text-center text-sm rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                    placeholder="OK"
                                                />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">Enter measurement values for each sample position. Leave empty if not applicable.</p>

                            <!-- Form Actions -->
                            <div class="flex justify-between items-center pt-6 border-t">
                                <div class="text-sm text-red-600" v-if="form.errors && Object.keys(form.errors).length">
                                    <div v-for="(msg, key) in form.errors" :key="key" class="mb-1">
                                        {{ msg }}
                                    </div>
                                </div>
                                <div class="flex space-x-3 ml-auto">
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
                                        Save Changes
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
