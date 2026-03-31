<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    pendingChecks: Array,
    user: Object,
});

const selectedChecks = ref([]);
const selectAll = ref(false);
const bulkAction = ref('');
const approvalNotes = ref('');

const filteredChecks = computed(() => {
    return props.pendingChecks.filter(check => check.status === 'pending');
});

const toggleSelectAll = () => {
    if (selectAll.value) {
        selectedChecks.value = filteredChecks.value.map(check => check.id);
    } else {
        selectedChecks.value = [];
    }
};

const bulkApprove = () => {
    if (selectedChecks.value.length === 0) {
        return;
    }
    
    Inertia.post(route('annealing-checks.bulk-approve'), {
        check_ids: selectedChecks.value,
        notes: approvalNotes.value,
    }, {
        onSuccess: () => {
            selectedChecks.value = [];
            selectAll.value = false;
            approvalNotes.value = '';
        }
    });
};

const bulkReject = () => {
    if (selectedChecks.value.length === 0) {
        return;
    }
    
    Inertia.post(route('annealing-checks.bulk-reject'), {
        check_ids: selectedChecks.value,
        notes: approvalNotes.value,
    }, {
        onSuccess: () => {
            selectedChecks.value = [];
            selectAll.value = false;
            approvalNotes.value = '';
        }
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};
</script>

<template>
    <Head title="Pending Approvals" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Pending Approvals
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="mb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-2">
                                Annealing Checks Pending Approval
                            </h3>
                            <p class="text-gray-600">
                                Review and approve or reject submitted annealing checks.
                            </p>
                        </div>

                        <!-- Bulk Actions -->
                        <div v-if="selectedChecks.length > 0" class="mb-4 p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-700">
                                    {{ selectedChecks.length }} item(s) selected
                                </span>
                                <div class="flex items-center space-x-2">
                                    <input
                                        v-model="approvalNotes"
                                        type="text"
                                        placeholder="Add notes (optional)"
                                        class="px-3 py-1 border border-gray-300 rounded-md text-sm"
                                    />
                                    <button
                                        @click="bulkApprove"
                                        class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700"
                                    >
                                        Approve Selected
                                    </button>
                                    <button
                                        @click="bulkReject"
                                        class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700"
                                    >
                                        Reject Selected
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Checks Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left">
                                            <input
                                                type="checkbox"
                                                v-model="selectAll"
                                                @change="toggleSelectAll"
                                                class="rounded border-gray-300"
                                            />
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Item Code
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Supplier Lot
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantity
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Annealing Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Machine
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Submitted By
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="check in filteredChecks" :key="check.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4">
                                            <input
                                                type="checkbox"
                                                :value="check.id"
                                                v-model="selectedChecks"
                                                class="rounded border-gray-300"
                                            />
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ check.item_code }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ check.supplier_lot_number || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ check.quantity }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(check.annealing_date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ check.machine_number || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ check.created_by?.name || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link
                                                :href="route('annealing-checks.show', check.id)"
                                                class="text-indigo-600 hover:text-indigo-900 mr-3"
                                            >
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <div v-if="filteredChecks.length === 0" class="text-center py-12">
                                <p class="text-gray-500">No pending approvals at this time.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
