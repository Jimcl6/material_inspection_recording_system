<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    annealingCheck: {
        type: Object,
        required: true
    }
});

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatTime = (timeString) => {
    if (!timeString) return 'N/A';
    const time = new Date(`1970-01-01T${timeString}`);
    return time.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    });
};
</script>

<template>
    <Head :title="`Annealing Check #${annealingCheck.id}`" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Annealing Check #{{ annealingCheck.id }}
                </h2>
                <div class="flex space-x-2">
                    <Link 
                        :href="route('annealing-checks.edit', annealingCheck.id)" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Edit
                    </Link>
                    <Link 
                        :href="route('annealing-checks.index')" 
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                    >
                        Back to List
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Header with Status -->
                    <div class="px-6 py-4 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">
                                    {{ annealingCheck.item_code }} - {{ annealingCheck.supplier_lot_number || 'No Lot #' }}
                                </h3>
                                <p class="text-sm text-gray-500">
                                    Annealed on {{ formatDate(annealingCheck.annealing_date) }}
                                </p>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Completed
                            </span>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="px-6 py-4">
                        <!-- Basic Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <h4 class="text-md font-medium text-gray-900 mb-4">Basic Information</h4>
                                <dl class="space-y-3">
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Item Code</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ annealingCheck.item_code }}
                                        </dd>
                                    </div>
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Supplier Lot #</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ annealingCheck.supplier_lot_number || 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Quantity</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ annealingCheck.quantity }}
                                        </dd>
                                    </div>
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Receiving Date</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ formatDate(annealingCheck.receiving_date) }}
                                        </dd>
                                    </div>
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Annealing Date</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ formatDate(annealingCheck.annealing_date) }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h4 class="text-md font-medium text-gray-900 mb-4">Machine Details</h4>
                                <dl class="space-y-3">
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Machine #</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ annealingCheck.machine_number || 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Machine Setting</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ annealingCheck.machine_setting || 'N/A' }}
                                        </dd>
                                    </div>
                                </dl>

                                <h4 class="text-md font-medium text-gray-900 mt-6 mb-4">Personnel</h4>
                                <dl class="space-y-3">
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">PIC</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ annealingCheck.pic?.name || 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Checked By</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ annealingCheck.checked_by?.name || 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Verified By</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ annealingCheck.verified_by?.name || 'N/A' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Temperature Readings -->
                        <div class="mt-8">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Temperature Readings</h4>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Time
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Temperature
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Recorded By
                                            </th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Recorded At
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(reading, index) in annealingCheck.temperature_readings" :key="index">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ formatTime(reading.reading_time) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ reading.temperature }} °C
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ reading.recorded_by?.name || 'System' }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ formatDate(reading.created_at) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="mt-8" v-if="annealingCheck.remarks">
                            <h4 class="text-md font-medium text-gray-900 mb-2">Remarks</h4>
                            <div class="bg-gray-50 p-4 rounded-md">
                                <p class="text-sm text-gray-700 whitespace-pre-line">{{ annealingCheck.remarks }}</p>
                            </div>
                        </div>

                        <!-- Audit Trail -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Audit Trail</h4>
                            <div class="text-xs text-gray-500 space-y-1">
                                <p>Created by {{ annealingCheck.created_by?.name || 'System' }} on {{ formatDate(annealingCheck.created_at) }}</p>
                                <p v-if="annealingCheck.updated_at !== annealingCheck.created_at">
                                    Last updated by {{ annealingCheck.updated_by?.name || 'System' }} on {{ formatDate(annealingCheck.updated_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
