<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { route } from 'ziggy-js';

interface Sample {
    id: number;
    check_item: string;
    sample_1: string | null;
    sample_2: string | null;
    sample_3: string | null;
    sample_4: string | null;
    sample_5: string | null;
}

interface User {
    id: number;
    name: string;
}

interface Checksheet {
    id: number;
    item_name: string | null;
    item_code: string | null;
    month_year: string | null;
    production_date: string;
    lasermark_lot_number: string | null;
    machine_no: string | null;
    letter_code: string | null;
    df_rubber_lot: string | null;
    center_plate_a_lot: string | null;
    center_plate_b_lot: string | null;
    prod_qty: number | null;
    jo_number: string | null;
    temperature: number | null;
    remarks: string | null;
    status: string;
    approved_at: string | null;
    approval_notes: string | null;
    created_at: string;
    updated_at: string;
    operator: User | null;
    technician: User | null;
    checked_by: User | null;
    created_by: User | null;
    updated_by: User | null;
    samples: Sample[];
}

interface Props {
    checksheet: Checksheet;
    checkItems: Record<string, string>;
}

const props = defineProps<Props>();

const formatDate = (dateString: string | null): string => {
    if (!dateString) return 'N/A';
    try {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    } catch {
        return dateString;
    }
};

const formatDateTime = (dateString: string | null): string => {
    if (!dateString) return 'N/A';
    try {
        return new Date(dateString).toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch {
        return dateString;
    }
};

const getSampleByCheckItem = (checkItem: string): Sample | undefined => {
    return props.checksheet.samples?.find(s => s.check_item === checkItem);
};

const statusClass = (status: string) => {
    switch (status) {
        case 'approved':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'rejected':
            return 'bg-red-100 text-red-800 border-red-200';
        default:
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
    }
};

const checkItemOrder = [
    'collapse_depth',
    'collapse_time',
    'strength',
    'appearance',
    'welding_condition',
    'measurement_1',
    'measurement_2',
    'measurement_3',
    'measurement_4',
    'measurement_5',
];
</script>

<template>
    <Head title="View Diaphragm Welding Checksheet" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Diaphragm Welding Checksheet Details
                </h2>
                <div class="space-x-2">
                    <Link 
                        :href="route('diaphragm-welding.edit', checksheet.id)" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700"
                    >
                        Edit
                    </Link>
                    <Link 
                        :href="route('diaphragm-welding.index')" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                    >
                        Back to List
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Status Banner -->
                <div 
                    class="mb-6 p-4 rounded-lg border"
                    :class="statusClass(checksheet.status)"
                >
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="font-semibold">Status:</span> 
                            {{ checksheet.status ? checksheet.status.charAt(0).toUpperCase() + checksheet.status.slice(1) : 'Unknown' }}
                        </div>
                        <div v-if="checksheet.approved_at" class="text-sm">
                            {{ checksheet.status === 'approved' ? 'Approved' : 'Processed' }} on {{ formatDateTime(checksheet.approved_at) }}
                        </div>
                    </div>
                    <p v-if="checksheet.approval_notes" class="mt-2 text-sm">
                        <strong>Notes:</strong> {{ checksheet.approval_notes }}
                    </p>
                </div>

                <!-- Material Monitoring Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Material Monitoring</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Item Code</label>
                                <p class="mt-1 text-sm text-gray-900 font-semibold">{{ checksheet.item_code || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Item Name</label>
                                <p class="mt-1 text-sm text-gray-900">{{ checksheet.item_name || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Production Date</label>
                                <p class="mt-1 text-sm text-gray-900">{{ formatDate(checksheet.production_date) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Month/Year</label>
                                <p class="mt-1 text-sm text-gray-900">{{ checksheet.month_year || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Lasermark Lot Number</label>
                                <p class="mt-1 text-sm text-gray-900">{{ checksheet.lasermark_lot_number || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Machine No.</label>
                                <p class="mt-1 text-sm text-gray-900">{{ checksheet.machine_no || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Letter Code</label>
                                <p class="mt-1 text-sm text-gray-900">{{ checksheet.letter_code || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">DF Rubber Lot</label>
                                <p class="mt-1 text-sm text-gray-900">{{ checksheet.df_rubber_lot || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Center Plate A Lot</label>
                                <p class="mt-1 text-sm text-gray-900">{{ checksheet.center_plate_a_lot || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Center Plate B Lot</label>
                                <p class="mt-1 text-sm text-gray-900">{{ checksheet.center_plate_b_lot || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Prod Qty</label>
                                <p class="mt-1 text-sm text-gray-900">{{ checksheet.prod_qty ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">JO Number</label>
                                <p class="mt-1 text-sm text-gray-900">{{ checksheet.jo_number || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Welding Data Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4 border-b pb-2">
                            <h3 class="text-lg font-medium text-gray-900">Welding Data</h3>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-2">
                                <p class="text-sm text-yellow-800">
                                    <strong>Legend:</strong> P = Pass | F = Fail
                                </p>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-64">Check Item</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sample 1</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sample 2</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sample 3</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sample 4</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sample 5</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(checkItem, index) in checkItemOrder" :key="checkItem" :class="{ 'bg-gray-50': index % 2 === 0 }">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                            {{ checkItems[checkItem] || checkItem }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center text-gray-900">
                                            {{ getSampleByCheckItem(checkItem)?.sample_1 || '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center text-gray-900">
                                            {{ getSampleByCheckItem(checkItem)?.sample_2 || '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center text-gray-900">
                                            {{ getSampleByCheckItem(checkItem)?.sample_3 || '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center text-gray-900">
                                            {{ getSampleByCheckItem(checkItem)?.sample_4 || '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-center text-gray-900">
                                            {{ getSampleByCheckItem(checkItem)?.sample_5 || '-' }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Personnel & Additional Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Personnel & Additional Info</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Temperature (°C)</label>
                                <p class="mt-1 text-sm text-gray-900">{{ checksheet.temperature ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Operator</label>
                                <p class="mt-1 text-sm text-gray-900">{{ checksheet.operator?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Technician</label>
                                <p class="mt-1 text-sm text-gray-900">{{ checksheet.technician?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Checked By</label>
                                <p class="mt-1 text-sm text-gray-900">{{ checksheet.checked_by?.name || 'N/A' }}</p>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-gray-500">Remarks</label>
                                <p class="mt-1 text-sm text-gray-900">{{ checksheet.remarks || 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Audit Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Audit Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Created By</label>
                                <p class="mt-1 text-sm text-gray-900">{{ checksheet.created_by?.name || 'System' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Created At</label>
                                <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(checksheet.created_at) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Updated By</label>
                                <p class="mt-1 text-sm text-gray-900">{{ checksheet.updated_by?.name || 'System' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500">Updated At</label>
                                <p class="mt-1 text-sm text-gray-900">{{ formatDateTime(checksheet.updated_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
