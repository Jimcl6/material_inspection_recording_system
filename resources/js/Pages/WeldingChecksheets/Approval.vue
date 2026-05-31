<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';

interface PendingChecksheet {
    id: number;
    item_code: string | null;
    production_date: string;
    machine_no: string | null;
    job_number: string | null;
    type?: { name: string };
    operator?: { name: string };
    operator_name_raw?: string | null;
}

const props = defineProps<{
    pendingChecksheets: PendingChecksheet[];
}>();

const selectedIds = ref<number[]>([]);
const notes = ref('');

const approveForm = useForm({
    checksheet_ids: [] as number[],
    notes: '',
});

const allSelected = computed(() => props.pendingChecksheets.length > 0 && selectedIds.value.length === props.pendingChecksheets.length);

const toggleAll = () => {
    selectedIds.value = allSelected.value ? [] : props.pendingChecksheets.map(checksheet => checksheet.id);
};

const submit = (action: 'approve' | 'reject') => {
    approveForm.checksheet_ids = selectedIds.value;
    approveForm.notes = notes.value;

    const targetRoute = action === 'approve'
        ? route('welding-checksheets.bulk-approve')
        : route('welding-checksheets.bulk-reject');

    approveForm.post(targetRoute, {
        onSuccess: () => {
            selectedIds.value = [];
            notes.value = '';
            approveForm.reset();
        },
    });
};

const formatDate = (value: string): string => value ? new Date(value).toLocaleDateString() : '';
</script>

<template>
    <Head title="Approve Welding Checksheets" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Welding Checksheet Approval</h2>
                <Link :href="route('welding-checksheets.index')" class="text-gray-600 hover:text-gray-800">
                    &larr; Back to List
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="mb-4 flex flex-col md:flex-row md:items-end md:justify-between gap-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Pending Checksheets</h3>
                                <p class="text-sm text-gray-500">{{ pendingChecksheets.length }} record(s) awaiting review.</p>
                            </div>
                            <div class="flex-1 md:max-w-md">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Approval Notes</label>
                                <input v-model="notes" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                            </div>
                            <div class="space-x-2">
                                <button @click="submit('reject')" :disabled="!selectedIds.length || approveForm.processing" class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 disabled:opacity-50">Reject</button>
                                <button @click="submit('approve')" :disabled="!selectedIds.length || approveForm.processing" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 disabled:opacity-50">Approve</button>
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
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Code</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Machine</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Job Number</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Operator</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="checksheet in pendingChecksheets" :key="checksheet.id">
                                        <td class="px-4 py-3">
                                            <input v-model="selectedIds" :value="checksheet.id" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ formatDate(checksheet.production_date) }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ checksheet.type?.name || 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ checksheet.item_code || 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ checksheet.machine_no || 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ checksheet.job_number || 'N/A' }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500">{{ checksheet.operator?.name || checksheet.operator_name_raw || 'N/A' }}</td>
                                        <td class="px-4 py-3 text-right text-sm">
                                            <Link :href="route('welding-checksheets.show', checksheet.id)" class="text-indigo-600 hover:text-indigo-900">View</Link>
                                        </td>
                                    </tr>
                                    <tr v-if="!pendingChecksheets.length">
                                        <td colspan="8" class="px-4 py-6 text-center text-sm text-gray-500">No pending welding checksheets.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
