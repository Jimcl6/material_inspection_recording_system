<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { route } from 'ziggy-js';
import { usePermissions } from '@/Composables/usePermissions';

const { canUpdate, approvalsEnabled } = usePermissions();

const props = defineProps<{
    checksheet: any;
}>();

const formatDate = (value?: string): string => value ? new Date(value).toLocaleDateString() : 'N/A';
const materialFields = () => props.checksheet.material_fields || {};
const sampleValues = (sample: any): string[] => sample.sample_values || [];
</script>

<template>
    <Head title="Welding Checksheet Details" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Welding Checksheet Details</h2>
                <div class="space-x-3">
                    <Link v-if="canUpdate('welding')" :href="route('welding-checksheets.edit', checksheet.id)" class="text-blue-600 hover:text-blue-800">Edit</Link>
                    <Link :href="route('welding-checksheets.index')" class="text-gray-600 hover:text-gray-800">&larr; Back to List</Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Summary</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                            <div><dt class="font-medium text-gray-500">Type</dt><dd>{{ checksheet.type?.name || 'N/A' }}</dd></div>
                            <div><dt class="font-medium text-gray-500">Item Code</dt><dd>{{ checksheet.item_code || 'N/A' }}</dd></div>
                            <div><dt class="font-medium text-gray-500">Production Date</dt><dd>{{ formatDate(checksheet.production_date) }}</dd></div>
                            <div v-if="approvalsEnabled"><dt class="font-medium text-gray-500">Status</dt><dd class="capitalize">{{ checksheet.status }}</dd></div>
                            <div><dt class="font-medium text-gray-500">Machine No.</dt><dd>{{ checksheet.machine_no || 'N/A' }}</dd></div>
                            <div><dt class="font-medium text-gray-500">Letter Code</dt><dd>{{ checksheet.letter_code || 'N/A' }}</dd></div>
                            <div><dt class="font-medium text-gray-500">Job Number</dt><dd>{{ checksheet.job_number || 'N/A' }}</dd></div>
                            <div><dt class="font-medium text-gray-500">Quantity</dt><dd>{{ checksheet.quantity ?? checksheet.prod_qty ?? 'N/A' }}</dd></div>
                        </dl>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Materials</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                            <div v-for="(value, key) in materialFields()" :key="key">
                                <dt class="font-medium text-gray-500">{{ String(key).replaceAll('_', ' ') }}</dt>
                                <dd>{{ value || 'N/A' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Samples</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Item</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requirement</th>
                                        <th v-for="index in 5" :key="index" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sample {{ index }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="sample in checksheet.samples" :key="sample.id">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ sample.check_item_label }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ sample.requirement_text || '-' }}</td>
                                        <td v-for="(value, index) in sampleValues(sample)" :key="index" class="px-4 py-3 text-center text-sm text-gray-700">{{ value || '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Personnel & Remarks</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                            <div><dt class="font-medium text-gray-500">Operator</dt><dd>{{ checksheet.operator?.name || checksheet.operator_name_raw || 'N/A' }}</dd></div>
                            <div><dt class="font-medium text-gray-500">Technician</dt><dd>{{ checksheet.technician?.name || checksheet.technician_name_raw || 'N/A' }}</dd></div>
                            <div><dt class="font-medium text-gray-500">Checked By</dt><dd>{{ checksheet.checked_by?.name || checksheet.checked_by_name_raw || 'N/A' }}</dd></div>
                            <div><dt class="font-medium text-gray-500">Temperature</dt><dd>{{ checksheet.temperature ?? 'N/A' }}</dd></div>
                            <div class="md:col-span-4"><dt class="font-medium text-gray-500">Remarks</dt><dd>{{ checksheet.remarks || 'N/A' }}</dd></div>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
