<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    record: {
        type: Object,
        required: true,
    },
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
    try {
        const [hours, minutes] = timeString.split(':');
        return new Date(2000, 0, 1, hours, minutes).toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    } catch (e) {
        return timeString;
    }
};
</script>

<template>
    <Head :title="`Torque Record #${record.id}`" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Torque Record #{{ record.id }}
                </h2>
                <div class="flex space-x-2">
                    <Link 
                        :href="route('torque-records.edit', record.id)" 
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Edit
                    </Link>
                    <Link 
                        :href="route('torque-records.index')" 
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
                                    {{ record.model_series || 'Torque Record' }}
                                    <span v-if="record.driver_model" class="text-gray-600">- {{ record.driver_model }}</span>
                                </h3>
                                <p class="text-sm text-gray-500">
                                    Recorded on {{ formatDate(record.date) }}
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
                                <h4 class="text-md font-medium text-gray-900 mb-4">Driver Information</h4>
                                <dl class="space-y-3">
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Date</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ formatDate(record.date) }}
                                        </dd>
                                    </div>
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Model Series</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ record.model_series || 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Driver Model</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ record.driver_model || 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Driver Type</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ record.driver_type || 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Screw Type</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ record.screw_type || 'N/A' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            <div>
                                <h4 class="text-md font-medium text-gray-900 mb-4">Assignment Details</h4>
                                <dl class="space-y-3">
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Control Number</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ record.control_no || 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Line Assigned</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ record.line_assigned || 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Process Assigned</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ record.process_assigned || 'N/A' }}
                                        </dd>
                                    </div>
                                </dl>

                                <h4 class="text-md font-medium text-gray-900 mt-6 mb-4">Personnel</h4>
                                <dl class="space-y-3">
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Person In Charge</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ record.person_in_charge || 'N/A' }}
                                        </dd>
                                    </div>
                                    <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                        <dt class="text-sm font-medium text-gray-500">Checked By</dt>
                                        <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                            {{ record.checked_by || 'N/A' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <!-- Torque Readings -->
                        <div class="mt-8">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Torque Readings</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Morning Reading -->
                                <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                                    <h5 class="text-md font-medium text-gray-900 mb-4">Morning (AM)</h5>
                                    <dl class="space-y-3">
                                        <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                            <dt class="text-sm font-medium text-gray-500">Time</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                                {{ formatTime(record.time_am) }}
                                            </dd>
                                        </div>
                                        <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                            <dt class="text-sm font-medium text-gray-500">Torque</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                                <span class="px-2 py-1 rounded-full text-xs font-medium" 
                                                      :class="getTorqueClass(record.torque_am)">
                                                    {{ record.torque_am ? `${record.torque_am} N·m` : 'N/A' }}
                                                </span>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>

                                <!-- Afternoon Reading -->
                                <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                                    <h5 class="text-md font-medium text-gray-900 mb-4">Afternoon (PM)</h5>
                                    <dl class="space-y-3">
                                        <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                            <dt class="text-sm font-medium text-gray-500">Time</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                                {{ formatTime(record.time_pm) }}
                                            </dd>
                                        </div>
                                        <div class="sm:grid sm:grid-cols-3 sm:gap-4">
                                            <dt class="text-sm font-medium text-gray-500">Torque</dt>
                                            <dd class="mt-1 text-sm text-gray-900 sm:col-span-2">
                                                <span class="px-2 py-1 rounded-full text-xs font-medium" 
                                                      :class="getTorqueClass(record.torque_pm)">
                                                    {{ record.torque_pm ? `${record.torque_pm} N·m` : 'N/A' }}
                                                </span>
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="mt-8" v-if="record.col_remarks">
                            <h4 class="text-md font-medium text-gray-900 mb-2">Remarks</h4>
                            <div class="bg-gray-50 p-4 rounded-md">
                                <p class="text-sm text-gray-700 whitespace-pre-line">{{ record.col_remarks }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script>
export default {
    methods: {
        getTorqueClass(torque) {
            const torqueValue = parseFloat(torque) || 0;
            // Adjust these thresholds based on your torque requirements
            if (torqueValue < 5) return 'bg-blue-100 text-blue-800';
            if (torqueValue > 15) return 'bg-red-100 text-red-800';
            return 'bg-green-100 text-green-800';
        }
    }
}
</script>