<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { format } from 'date-fns';
import AppLayout from '@/Layouts/AppLayout.vue';

// Import route helper
declare function route(name: string, params?: any): string;

interface ModificationLog {
    id: number;
    prod_date: string;
    col_4m?: string;
    col_line?: string;
    model_code: string;
    item_for_modification: string;
    nature_of_change?: string;
    col_from?: string;
    col_to?: string;
    material_lot_no?: string;
    start_serial?: string;
    end_serial?: string;
    recorded_by: string;
    source_of_info?: string;
    approved_by?: string;
    job_no_transfer_order?: string;
    col_remarks?: string;
    created_at: string;
    updated_at: string;
}

const props = defineProps<{
    log: ModificationLog;
}>();

function formatDateTime(dateTime: string): string {
    if (!dateTime) return 'N/A';
    try {
        // First try parsing as ISO string
        const date = new Date(dateTime);
        if (isNaN(date.getTime())) {
            // If not a valid date, try parsing as custom format (d/m/Y H:i)
            const [datePart, timePart] = dateTime.split(' ');
            const [day, month, year] = datePart.split('/');
            const formattedDate = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}${timePart ? 'T' + timePart : ''}`;
            return format(new Date(formattedDate), 'dd/MM/yyyy HH:mm');
        }
        return format(date, 'dd/MM/yyyy HH:mm');
    } catch (e) {
        console.error('Error formatting date:', e);
        return dateTime; // Return original if can't parse
    }
}
</script>

<template>
    <Head title="Modification Log Details" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Modification Log #{{ log.id }}
                </h2>
                <div class="flex space-x-3">
                    <Link
                        :href="route('modification-logs.edit', log.id)"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
                    </Link>
                    <Link
                        :href="route('modification-logs.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Modification Logs
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header Card -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="px-6 py-4 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">
                                    Modification Log #{{ log.id }}
                                </h3>
                                
                            </div>
                            <div class="flex space-x-2">
                                <Link
                                    :href="route('modification-logs.edit', log.id)"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit
                                </Link>
                                <Link
                                    :href="route('modification-logs.index')"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                    </svg>
                                    Back to List
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Content Cards -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Basic Information Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 bg-white border-b border-gray-200">
                            <h4 class="text-md font-medium text-gray-900">Basic Information</h4>
                        </div>
                        <div class="px-6 py-4">
                            <dl class="space-y-3">
                                <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Production Date/Time</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                        {{ formatDateTime(log.prod_date) }}
                                    </dd>
                                </div>
                                <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Model Code</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                        {{ log.model_code }}
                                    </dd>
                                </div>
                                <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Item for Modification</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                        {{ log.item_for_modification }}
                                    </dd>
                                </div>
                                <div v-if="log.nature_of_change" class="sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Nature of Change</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2 whitespace-pre-wrap">
                                        {{ log.nature_of_change }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Modification Details Card -->
                    <div v-if="log.col_from || log.col_to || log.material_lot_no || log.job_no_transfer_order" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 bg-white border-b border-gray-200">
                            <h4 class="text-md font-medium text-gray-900">Modification Details</h4>
                        </div>
                        <div class="px-6 py-4">
                            <dl class="space-y-3">
                                <div v-if="log.col_from" class="sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">From</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                        {{ log.col_from }}
                                    </dd>
                                </div>
                                <div v-if="log.col_to" class="sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">To</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                        {{ log.col_to }}
                                    </dd>
                                </div>
                                <div v-if="log.material_lot_no" class="sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Material Lot No</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                        {{ log.material_lot_no }}
                                    </dd>
                                </div>
                                <div v-if="log.job_no_transfer_order" class="sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Job No / Transfer Order</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                        {{ log.job_no_transfer_order }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Serial Numbers Card -->
                    <div v-if="log.start_serial || log.end_serial" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 bg-white border-b border-gray-200">
                            <h4 class="text-md font-medium text-gray-900">Serial Numbers</h4>
                        </div>
                        <div class="px-6 py-4">
                            <dl class="space-y-3">
                                <div v-if="log.start_serial" class="sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Start Serial</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                        {{ log.start_serial }}
                                    </dd>
                                </div>
                                <div v-if="log.end_serial" class="sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">End Serial</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                        {{ log.end_serial }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Personnel Card -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="px-6 py-4 bg-white border-b border-gray-200">
                            <h4 class="text-md font-medium text-gray-900">Personnel</h4>
                        </div>
                        <div class="px-6 py-4">
                            <dl class="space-y-3">
                                <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Recorded By</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                        {{ log.recorded_by }}
                                    </dd>
                                </div>
                                <div v-if="log.source_of_info" class="sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Source of Info</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                        {{ log.source_of_info }}
                                    </dd>
                                </div>
                                <div v-if="log.approved_by" class="sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Approved By</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                        {{ log.approved_by }}
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Additional Information Card -->
                    <div v-if="log.col_4m || log.col_line || log.col_remarks" class="bg-white overflow-hidden shadow-sm sm:rounded-lg lg:col-span-2">
                        <div class="px-6 py-4 bg-white border-b border-gray-200">
                            <h4 class="text-md font-medium text-gray-900">Additional Information</h4>
                        </div>
                        <div class="px-6 py-4">
                            <dl class="space-y-3">
                                <div v-if="log.col_4m" class="sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">4M</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                        {{ log.col_4m }}
                                    </dd>
                                </div>
                                <div v-if="log.col_line" class="sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Line</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                        {{ log.col_line }}
                                    </dd>
                                </div>
                                <div v-if="log.col_remarks" class="sm:grid sm:grid-cols-3 sm:gap-4">
                                    <dt class="text-sm font-medium text-gray-500">Remarks</dt>
                                    <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                        <div class="bg-gray-50 p-3 rounded-md">
                                            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ log.col_remarks }}</p>
                                        </div>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Audit Trail -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                    <div class="px-6 py-4 bg-white border-t border-gray-200">
                        <h4 class="text-sm font-medium text-gray-500 mb-2">Audit Trail</h4>
                        <div class="text-xs text-gray-500 space-y-1">
                            <p>Created on {{ formatDateTime(log.created_at) }}</p>
                            <p v-if="log.updated_at !== log.created_at">
                                Last updated on {{ formatDateTime(log.updated_at) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
