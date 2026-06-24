<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed, ref } from 'vue';

const props = defineProps({
    pendingRecords: {
        type: Array,
        default: () => [],
    },
});

const selectedIds = ref([]);
const notes = ref('');

const form = useForm({
    record_ids: [],
    notes: '',
});

const allSelected = computed(() => props.pendingRecords.length > 0 && selectedIds.value.length === props.pendingRecords.length);

const toggleAll = () => {
    selectedIds.value = allSelected.value ? [] : props.pendingRecords.map((record) => record.id);
};

const submit = (action) => {
    form.record_ids = selectedIds.value;
    form.notes = notes.value;

    form.post(route(action === 'approve' ? 'torque-records.bulk-approve' : 'torque-records.bulk-reject'), {
        onSuccess: () => {
            selectedIds.value = [];
            notes.value = '';
            form.reset();
        },
    });
};

const formatDate = (value) => value ? new Date(value).toLocaleDateString() : 'N/A';
</script>

<template>
    <Head title="Approve Torque Records" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Torque Record Approval</h2>
                <Link :href="route('torque-records.index')" class="text-gray-600 hover:text-gray-800">
                    &larr; Back to List
                </Link>
            </div>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="mb-4 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Pending Torque Records</h3>
                            <p class="text-sm text-gray-500">{{ pendingRecords.length }} record(s) awaiting review.</p>
                        </div>
                        <div class="flex-1 md:max-w-md">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Approval Notes</label>
                            <input v-model="notes" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                        </div>
                        <div class="space-x-2">
                            <button @click="submit('reject')" :disabled="!selectedIds.length || form.processing" class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 disabled:opacity-50">Reject</button>
                            <button @click="submit('approve')" :disabled="!selectedIds.length || form.processing" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 disabled:opacity-50">Approve</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left">
                                        <input type="checkbox" :checked="allSelected" @change="toggleAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model Series</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Driver</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Screw Type</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PIC</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Checked By</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="record in pendingRecords" :key="record.id">
                                    <td class="px-4 py-3">
                                        <input v-model="selectedIds" :value="record.id" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(record.date) }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ record.model_series || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ record.driver_model || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ record.screw_type || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ record.person_in_charge || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-500">{{ record.checked_by || 'N/A' }}</td>
                                    <td class="px-4 py-3 text-right text-sm">
                                        <Link :href="route('torque-records.show', record.id)" class="text-indigo-600 hover:text-indigo-900">View</Link>
                                    </td>
                                </tr>
                                <tr v-if="!pendingRecords.length">
                                    <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">No pending torque records.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
