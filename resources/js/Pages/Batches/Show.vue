<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
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
        ItemName?: string;
        ItemCode?: string;
    };
    checkpoints: Array<{
        CheckpointID: number;
        CheckpointNumber: number;
        PositionLabel: string;
        InspectorName_First: string;
        Judgement_First: string;
        InspectorName_Last: string;
        Judgement_Last: string;
        samples_first: string[];
        samples_last: string[];
    }>;
}

const props = defineProps<Props>();

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
    <Head title="Magnetism Checksheet Details" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Checksheet #{{ batch.BatchID }}
                </h2>
                <div class="space-x-2">
                    <Link
                        :href="route('magnetism-checksheet.edit', { magnetism_checksheet: batch.BatchID })"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Checksheet
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
            <div class="max-w-7xl mx-auto">
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
                                <dt class="text-sm font-medium text-gray-500">Item Description</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ batch.ItemName || 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Item Code</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ batch.ItemCode || 'N/A' }}</dd>
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
                                <dt class="text-sm font-medium text-gray-500">Job Order #</dt>
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

                <!-- Inspection Samples Grid -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Inspection Samples</h3>
                            <Link
                                :href="route('magnetism-checksheet.checkpoints.create', { magnetism_checksheet: batch.BatchID })"
                                class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Edit Samples
                            </Link>
                        </div>

                        <!-- Inspector Names Display -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6" v-if="checkpoints.length">
                            <div class="bg-blue-50 rounded-lg p-3">
                                <span class="text-sm font-medium text-blue-700">Inspector (First):</span>
                                <span class="ml-2 text-sm text-blue-900">{{ checkpoints[0]?.InspectorName_First || 'N/A' }}</span>
                            </div>
                            <div class="bg-green-50 rounded-lg p-3">
                                <span class="text-sm font-medium text-green-700">Inspector (Last):</span>
                                <span class="ml-2 text-sm text-green-900">{{ checkpoints[0]?.InspectorName_Last || 'N/A' }}</span>
                            </div>
                        </div>

                        <!-- Samples Grid Display -->
                        <div class="overflow-x-auto" v-if="checkpoints.length">
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
                                        <th v-for="n in 5" :key="'fh'+n" class="px-2 py-1 text-center text-xs font-medium text-gray-500 border-b border-r border-gray-200 bg-blue-50 w-14">
                                            {{ n }}
                                        </th>
                                        <th class="px-2 py-1 text-center text-xs font-medium text-gray-500 border-b border-r border-gray-200 bg-blue-50 w-16">
                                            Judge
                                        </th>
                                        <th v-for="n in 5" :key="'lh'+n" class="px-2 py-1 text-center text-xs font-medium text-gray-500 border-b border-r border-gray-200 bg-green-50 w-14">
                                            {{ n }}
                                        </th>
                                        <th class="px-2 py-1 text-center text-xs font-medium text-gray-500 border-b border-gray-200 bg-green-50 w-16">
                                            Judge
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="cp in checkpoints" :key="cp.CheckpointID" class="hover:bg-gray-50">
                                        <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-200">
                                            {{ cp.CheckpointNumber }} - {{ cp.PositionLabel }}
                                        </td>
                                        <!-- First Inspection Samples -->
                                        <td v-for="(val, i) in cp.samples_first" :key="'f'+cp.CheckpointNumber+'-'+i" class="px-2 py-2 text-center text-sm text-gray-700 border-r border-gray-200">
                                            {{ val || '-' }}
                                        </td>
                                        <!-- First Inspection Judgement -->
                                        <td class="px-2 py-2 text-center text-sm border-r border-gray-200">
                                            <span :class="[
                                                'px-2 py-1 rounded text-xs font-medium',
                                                cp.Judgement_First?.toUpperCase() === 'OK' ? 'bg-green-100 text-green-800' :
                                                cp.Judgement_First?.toUpperCase() === 'NG' ? 'bg-red-100 text-red-800' :
                                                'text-gray-700'
                                            ]">
                                                {{ cp.Judgement_First || '-' }}
                                            </span>
                                        </td>
                                        <!-- Last Inspection Samples -->
                                        <td v-for="(val, i) in cp.samples_last" :key="'l'+cp.CheckpointNumber+'-'+i" class="px-2 py-2 text-center text-sm text-gray-700 border-r border-gray-200">
                                            {{ val || '-' }}
                                        </td>
                                        <!-- Last Inspection Judgement -->
                                        <td class="px-2 py-2 text-center text-sm">
                                            <span :class="[
                                                'px-2 py-1 rounded text-xs font-medium',
                                                cp.Judgement_Last?.toUpperCase() === 'OK' ? 'bg-green-100 text-green-800' :
                                                cp.Judgement_Last?.toUpperCase() === 'NG' ? 'bg-red-100 text-red-800' :
                                                'text-gray-700'
                                            ]">
                                                {{ cp.Judgement_Last || '-' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No inspection data</h3>
                            <p class="mt-1 text-sm text-gray-500">Get started by adding inspection samples.</p>
                            <div class="mt-6">
                                <Link
                                    :href="route('magnetism-checksheet.checkpoints.create', { magnetism_checksheet: batch.BatchID })"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    Add Inspection Data
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
