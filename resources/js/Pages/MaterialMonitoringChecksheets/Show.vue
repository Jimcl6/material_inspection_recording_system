<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    materialPart: {
        type: Object,
        required: true
    },
    materialTypes: {
        type: Object,
        default: () => ({})
    },
    subLotTitles: {
        type: Array,
        default: () => []
    }
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getSubLotCount = (materialPart) => {
    if (!materialPart.sub_lot_numbers || !materialPart.sub_lot_numbers.sub_lots) {
        return 0;
    }
    return materialPart.sub_lot_numbers.sub_lots.length;
};
</script>

<template>
    <Head title="Material Monitoring Checksheet Details" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Material Monitoring Checksheet Details
                </h2>
                <div class="flex items-center space-x-2">
                    <Link
                        :href="route('material-monitoring-checksheets.edit', props.materialPart)"
                        class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </Link>
                    
                    <Link
                        :href="route('material-monitoring-checksheets.index')"
                        class="inline-flex items-center px-3 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l-7 7m7 7v4m0 0l-7-7m-7 7v4"></path>
                        </svg>
                        Back to List
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Basic Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <!-- Material Type -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Material Type</h4>
                                <p class="text-lg text-gray-900">{{ props.materialTypes[props.materialPart.material_type] || props.materialPart.material_type }}</p>
                            </div>

                            <!-- Date -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Date</h4>
                                <p class="text-lg text-gray-900">{{ formatDate(props.materialPart.date) }}</p>
                            </div>

                            <!-- Item Block Code -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Item Block Code</h4>
                                <p class="text-lg text-gray-900">{{ props.materialPart.item_block_code }}</p>
                            </div>

                            <!-- Letter Code -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Letter Code</h4>
                                <div class="inline-flex items-center px-3 py-1 rounded-full text-lg font-medium bg-indigo-100 text-indigo-800">
                                    {{ props.materialPart.letter_code }}
                                </div>
                            </div>

                            <!-- Main Lot Number -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Main Lot Number</h4>
                                <p class="text-lg text-gray-900">{{ props.materialPart.main_lot_number }}</p>
                            </div>

                            <!-- Produced Quantity -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Produced Quantity</h4>
                                <p class="text-lg text-gray-900">{{ props.materialPart.produced_qty?.toLocaleString() || 0 }}</p>
                            </div>

                            <!-- Operator -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Operator</h4>
                                <p class="text-lg text-gray-900">{{ props.materialPart.operator || 'N/A' }}</p>
                            </div>

                            <!-- Job Number -->
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Job Number</h4>
                                <p class="text-lg text-gray-900">{{ props.materialPart.job_number || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sub Lot Numbers -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Sub Lot Numbers</h3>
                        
                        <div v-if="props.materialPart.sub_lot_numbers && props.materialPart.sub_lot_numbers.sub_lots && props.materialPart.sub_lot_numbers.sub_lots.length > 0">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div v-for="(subLot, index) in props.materialPart.sub_lot_numbers.sub_lots" :key="index" class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">
                                        {{ props.subLotTitles[index] || `Sub Lot #${index + 1}` }}
                                    </h4>
                                    <p class="text-lg text-gray-900 font-mono">{{ subLot }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div v-else class="text-center text-gray-500 py-8">
                            <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7a2 2 0 002 2h8l2 3h4a1 1 0 001 1v4a1 1 0 001 1h3a1 1 0 001-1v-4a1 1 0 001-1H8a1 1 0 001-1V6z"></path>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">No sub lot numbers found</p>
                        </div>
                    </div>
                </div>

                <!-- Timestamps -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Timestamps</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Created At</h4>
                                <p class="text-lg text-gray-900">{{ formatDate(props.materialPart.created_at) }}</p>
                            </div>
                            
                            <div>
                                <h4 class="text-sm font-medium text-gray-500 mb-1">Last Updated</h4>
                                <p class="text-lg text-gray-900">{{ formatDate(props.materialPart.updated_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
