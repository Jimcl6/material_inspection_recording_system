<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { format } from 'date-fns';

const props = defineProps({
    record: {
        type: Object,
        required: true,
    },
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    try {
        return format(new Date(dateString), 'MMM d, yyyy');
    } catch (e) {
        return dateString;
    }
};
</script>

<template>
    <AppLayout title="View Temperature Record">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Temperature Record #{{ record.id }}
                </h2>
                <div class="space-x-2">
                    <Link :href="route('temp-records.index')">
                        <SecondaryButton>Back to List</SecondaryButton>
                    </Link>
                    <Link :href="route('temp-records.edit', record.id)">
                        <PrimaryButton>Edit Record</PrimaryButton>
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <!-- Basic Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Date</p>
                                    <p class="mt-1">{{ formatDate(record.date) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Shift</p>
                                    <p class="mt-1">{{ record.shift || 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Time</p>
                                    <p class="mt-1">{{ record.time || 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- PO and Supplier -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Order Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">PO Number</p>
                                    <p class="mt-1">{{ record.po || 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Supplier</p>
                                    <p class="mt-1">{{ record.supplier || 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Material Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Material Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Material Name</p>
                                    <p class="mt-1">{{ record.material_name || 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Material Type</p>
                                    <p class="mt-1">{{ record.material_type || 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Condition</p>
                                    <p class="mt-1">{{ record.material_condition || 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Temperature Information -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Temperature Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Temperature Reading (°C)</p>
                                    <p class="mt-1">{{ record.temp_reading || 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Temperature Setting (°C)</p>
                                    <p class="mt-1">{{ record.temp_setting || 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Additional Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Corrected By</p>
                                    <p class="mt-1">{{ record.corrected_by || 'N/A' }}</p>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Remarks</p>
                                    <p class="mt-1 whitespace-pre-line">{{ record.remarks || 'No remarks' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
