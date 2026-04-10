<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';

interface User {
    id: number;
    name: string;
}

interface Checksheet {
    id: number;
    item_code: string | null;
    production_date: string;
    lasermark_lot_number: string | null;
    machine_no: string | null;
    jo_number: string | null;
    prod_qty: number | null;
    status: string;
    created_at: string;
    created_by: User | null;
    operator: User | null;
}

interface Props {
    pendingChecksheets: Checksheet[];
    user: User;
}

const props = defineProps<Props>();

const selectedIds = ref<number[]>([]);
const approvalNotes = ref('');
const showNotesModal = ref(false);
const actionType = ref<'approve' | 'reject'>('approve');

const allSelected = computed(() => {
    return props.pendingChecksheets.length > 0 && 
           selectedIds.value.length === props.pendingChecksheets.length;
});

const toggleSelectAll = () => {
    if (allSelected.value) {
        selectedIds.value = [];
    } else {
        selectedIds.value = props.pendingChecksheets.map(c => c.id);
    }
};

const toggleSelect = (id: number) => {
    const index = selectedIds.value.indexOf(id);
    if (index === -1) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value.splice(index, 1);
    }
};

const openApproveModal = () => {
    if (selectedIds.value.length === 0) {
        alert('Please select at least one checksheet.');
        return;
    }
    actionType.value = 'approve';
    showNotesModal.value = true;
};

const openRejectModal = () => {
    if (selectedIds.value.length === 0) {
        alert('Please select at least one checksheet.');
        return;
    }
    actionType.value = 'reject';
    showNotesModal.value = true;
};

const approveForm = useForm({
    checksheet_ids: [] as number[],
    notes: '',
});

const submitAction = () => {
    approveForm.checksheet_ids = selectedIds.value;
    approveForm.notes = approvalNotes.value;

    if (actionType.value === 'approve') {
        approveForm.post(route('diaphragm-welding.bulk-approve'), {
            onSuccess: () => {
                showNotesModal.value = false;
                selectedIds.value = [];
                approvalNotes.value = '';
            }
        });
    } else {
        approveForm.post(route('diaphragm-welding.bulk-reject'), {
            onSuccess: () => {
                showNotesModal.value = false;
                selectedIds.value = [];
                approvalNotes.value = '';
            }
        });
    }
};

const formatDate = (dateString: string): string => {
    if (!dateString) return 'N/A';
    try {
        return new Date(dateString).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        });
    } catch {
        return dateString;
    }
};

const formatDateTime = (dateString: string): string => {
    if (!dateString) return 'N/A';
    try {
        return new Date(dateString).toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch {
        return dateString;
    }
};
</script>

<template>
    <Head title="Pending Approvals - Diaphragm Welding" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Pending Approvals - Diaphragm Welding
                </h2>
                <Link 
                    :href="route('diaphragm-welding.index')" 
                    class="text-gray-600 hover:text-gray-800"
                >
                    &larr; Back to List
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Action Buttons -->
                <div class="mb-6 flex justify-between items-center">
                    <p class="text-sm text-gray-600">
                        {{ selectedIds.length }} of {{ pendingChecksheets.length }} selected
                    </p>
                    <div class="space-x-2">
                        <button 
                            @click="openApproveModal"
                            :disabled="selectedIds.length === 0"
                            class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 disabled:opacity-50"
                        >
                            Approve Selected
                        </button>
                        <button 
                            @click="openRejectModal"
                            :disabled="selectedIds.length === 0"
                            class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 disabled:opacity-50"
                        >
                            Reject Selected
                        </button>
                    </div>
                </div>

                <!-- Checksheets Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left">
                                            <input 
                                                type="checkbox"
                                                :checked="allSelected"
                                                @change="toggleSelectAll"
                                                class="rounded border-gray-300 text-indigo-600"
                                            />
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item Code</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lasermark Lot #</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Machine</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">JO Number</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Submitted By</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="checksheet in pendingChecksheets" :key="checksheet.id" class="hover:bg-gray-50">
                                        <td class="px-4 py-4">
                                            <input 
                                                type="checkbox"
                                                :checked="selectedIds.includes(checksheet.id)"
                                                @change="toggleSelect(checksheet.id)"
                                                class="rounded border-gray-300 text-indigo-600"
                                            />
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-900">
                                            {{ formatDate(checksheet.production_date) }}
                                        </td>
                                        <td class="px-4 py-4 text-sm font-medium text-gray-900">
                                            {{ checksheet.item_code || 'N/A' }}
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-500">
                                            {{ checksheet.lasermark_lot_number || 'N/A' }}
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-500">
                                            {{ checksheet.machine_no || 'N/A' }}
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-500">
                                            {{ checksheet.jo_number || 'N/A' }}
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-500">
                                            {{ formatDateTime(checksheet.created_at) }}
                                        </td>
                                        <td class="px-4 py-4 text-sm text-gray-500">
                                            {{ checksheet.created_by?.name || 'System' }}
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <Link 
                                                :href="route('diaphragm-welding.show', checksheet.id)"
                                                class="text-indigo-600 hover:text-indigo-900 text-sm"
                                            >
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="pendingChecksheets.length === 0">
                                        <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-500">
                                            No pending checksheets to approve.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval/Rejection Modal -->
        <div v-if="showNotesModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" @click="showNotesModal = false"></div>
                
                <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ actionType === 'approve' ? 'Approve' : 'Reject' }} {{ selectedIds.length }} Checksheet(s)
                    </h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                        <textarea 
                            v-model="approvalNotes"
                            rows="3"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Add any notes about this decision..."
                        ></textarea>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button 
                            @click="showNotesModal = false"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button 
                            @click="submitAction"
                            :disabled="approveForm.processing"
                            class="px-4 py-2 rounded-md text-sm font-medium text-white"
                            :class="actionType === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700'"
                        >
                            {{ approveForm.processing ? 'Processing...' : (actionType === 'approve' ? 'Approve' : 'Reject') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
