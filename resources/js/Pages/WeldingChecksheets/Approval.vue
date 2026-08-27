<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import RecordDetailPanel from '@/Components/RecordDetailPanel.vue';
import { useSingleExpandedRow } from '@/Composables/useSingleExpandedRow';
import { computed, ref } from 'vue';
import { route } from 'ziggy-js';

type DetailItem = { label: string; value: unknown };
type DetailSection = { title: string; items: DetailItem[] };
type Sample = {
    id: number;
    check_item_label: string | null;
    requirement_text: string | null;
    sample_values: unknown[] | null;
};

interface PendingChecksheet {
    id: number;
    item_code: string | null;
    item_name: string | null;
    month_year: string | null;
    production_date: string;
    machine_no: string | null;
    letter_code: string | null;
    job_number: string | null;
    prod_qty: number | null;
    quantity: number | null;
    temperature: string | number | null;
    material_fields: Record<string, unknown> | null;
    remarks: string | null;
    status: string;
    submitted_at: string | null;
    source_file: string | null;
    source_sheet: string | null;
    source_row: number | null;
    type?: { name: string; material_fields?: Array<{ key: string; label: string }> };
    item_config?: { item_code: string; item_name?: string | null };
    created_by?: { name: string };
    operator?: { name: string };
    technician?: { name: string };
    checked_by?: { name: string };
    operator_name_raw?: string | null;
    technician_name_raw?: string | null;
    checked_by_name_raw?: string | null;
    samples?: Sample[];
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Paginated<T> {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    pendingChecksheets: Paginated<PendingChecksheet>;
}>();

const selectedIds = ref<number[]>([]);
const notes = ref('');
const { toggleExpanded, isExpanded } = useSingleExpandedRow();
const pageSizeOptions = [10, 25, 50];
const pageSize = ref(String(props.pendingChecksheets.per_page ?? 10));

const approveForm = useForm({
    checksheet_ids: [] as number[],
    notes: '',
});

const pendingRecords = computed(() => props.pendingChecksheets.data ?? []);
const totalPending = computed(() => props.pendingChecksheets.total ?? pendingRecords.value.length);
const allSelected = computed(() => pendingRecords.value.length > 0 && selectedIds.value.length === pendingRecords.value.length);

const toggleAll = () => {
    selectedIds.value = allSelected.value ? [] : pendingRecords.value.map(checksheet => checksheet.id);
};

const updatePageSize = () => {
    selectedIds.value = [];
    router.get(route('welding-checksheets.approval'), {
        per_page: pageSize.value,
    }, {
        preserveScroll: true,
        replace: true,
    });
};

const submit = (action: 'approve' | 'reject') => {
    if (!selectedIds.value.length) {
        return;
    }

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

const submitOne = (checksheet: PendingChecksheet, action: 'approve' | 'reject') => {
    selectedIds.value = [checksheet.id];
    submit(action);
};

const formatDate = (value?: string | null): string => value ? new Date(value).toLocaleDateString() : 'N/A';
const formatDateTime = (value?: string | null): string => value ? new Date(value).toLocaleString() : 'N/A';

const displayValue = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return 'N/A';
    }

    return String(value);
};

const materialEntries = (checksheet: PendingChecksheet): [string, unknown][] => Object.entries(checksheet.material_fields ?? {});

const materialFieldLabel = (checksheet: PendingChecksheet, key: string): string => {
    const field = checksheet.type?.material_fields?.find(item => item.key === key);

    return field?.label
        || key.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};

const sampleValues = (sample: Sample): unknown[] => sample.sample_values ?? [];

const quantityValue = (checksheet: PendingChecksheet): unknown => checksheet.prod_qty ?? checksheet.quantity;

const recordDetailSections = (checksheet: PendingChecksheet): DetailSection[] => [
    {
        title: 'Record Details',
        items: [
            { label: 'Type', value: checksheet.type?.name },
            { label: 'Item Code', value: checksheet.item_code },
            { label: 'Item Name', value: checksheet.item_name ?? checksheet.item_config?.item_name },
            { label: 'Month/Year', value: checksheet.month_year },
            { label: 'Production Date', value: formatDate(checksheet.production_date) },
        ],
    },
    {
        title: 'Process Details',
        items: [
            { label: 'Machine No.', value: checksheet.machine_no },
            { label: 'Letter Code', value: checksheet.letter_code },
            { label: 'Job Number', value: checksheet.job_number },
            { label: 'Quantity', value: quantityValue(checksheet) },
            { label: 'Temperature', value: checksheet.temperature },
        ],
    },
    {
        title: 'Personnel & Source',
        items: [
            { label: 'Operator', value: checksheet.operator?.name || checksheet.operator_name_raw },
            { label: 'Technician', value: checksheet.technician?.name || checksheet.technician_name_raw },
            { label: 'Checked By', value: checksheet.checked_by?.name || checksheet.checked_by_name_raw },
            { label: 'Submitted By', value: checksheet.created_by?.name },
            { label: 'Submitted At', value: formatDateTime(checksheet.submitted_at) },
        ],
    },
];

</script>

<template>
    <!-- eslint-disable vue/valid-v-for -- Known parser false positive for keyed Vue template loops. -->
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
                            <div class="mb-5 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">Pending Checksheets</h3>
                                    <p class="text-sm text-gray-500">
                                        Showing {{ pendingChecksheets.from || 0 }} to {{ pendingChecksheets.to || 0 }} of {{ totalPending }} record(s) awaiting review.
                                    </p>
                                </div>
                                <div class="w-full md:w-40">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Rows per page</label>
                                    <select
                                        v-model="pageSize"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        @change="updatePageSize"
                                    >
                                        <option v-for="option in pageSizeOptions" :key="option" :value="String(option)">
                                            {{ option }}
                                        </option>
                                    </select>
                                </div>
                                <div class="flex-1 md:max-w-md">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Approval Notes</label>
                                    <input v-model="notes" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                                </div>
                                <div class="flex items-center gap-2">
                                    <button @click="submit('reject')" :disabled="!selectedIds.length || approveForm.processing" class="px-4 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 disabled:opacity-50">Reject Selected</button>
                                    <button @click="submit('approve')" :disabled="!selectedIds.length || approveForm.processing" class="px-4 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 disabled:opacity-50">Approve Selected</button>
                                </div>
                            </div>

                            <div class="mb-4 flex items-center gap-3 border-y border-gray-200 py-3">
                                <input type="checkbox" :checked="allSelected" @change="toggleAll" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                <span class="text-sm font-medium text-gray-700">Select all records on this page</span>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="w-10 px-3 py-3">
                                                <span class="sr-only">Details</span>
                                            </th>
                                            <th class="w-10 px-3 py-3">
                                                <span class="sr-only">Select</span>
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Item Code</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Machine</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Job Number</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Operator</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <template v-for="checksheet in pendingRecords" :key="checksheet.id">
                                            <tr
                                                class="cursor-pointer hover:bg-gray-50"
                                                :class="{ 'bg-indigo-50/40': isExpanded(checksheet.id) }"
                                                @click="toggleExpanded(checksheet.id)"
                                            >
                                                <td class="px-3 py-4 whitespace-nowrap">
                                                    <button
                                                        type="button"
                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                                        :aria-expanded="isExpanded(checksheet.id)"
                                                        :title="isExpanded(checksheet.id) ? 'Hide details' : 'Show details'"
                                                        @click.stop="toggleExpanded(checksheet.id)"
                                                    >
                                                        <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-90': isExpanded(checksheet.id) }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                    </button>
                                                </td>
                                                <td class="px-3 py-4 whitespace-nowrap">
                                                    <input
                                                        v-model="selectedIds"
                                                        :value="checksheet.id"
                                                        type="checkbox"
                                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                                        @click.stop
                                                    />
                                                </td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(checksheet.production_date) }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ checksheet.type?.name || 'N/A' }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ checksheet.item_code || 'N/A' }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ checksheet.machine_no || 'N/A' }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ checksheet.job_number || 'N/A' }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">{{ checksheet.operator?.name || checksheet.operator_name_raw || 'N/A' }}</td>
                                                <td class="px-4 py-4 whitespace-nowrap text-right text-sm" @click.stop>
                                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                                        <Link :href="route('welding-checksheets.show', checksheet.id)" class="px-3 py-2 text-sm font-medium text-indigo-700 hover:text-indigo-900">
                                                            View
                                                        </Link>
                                                        <button @click="submitOne(checksheet, 'reject')" :disabled="approveForm.processing" class="px-3 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 disabled:opacity-50">Reject</button>
                                                        <button @click="submitOne(checksheet, 'approve')" :disabled="approveForm.processing" class="px-3 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 disabled:opacity-50">Approve</button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr v-if="isExpanded(checksheet.id)" class="bg-gray-50">
                                                <td colspan="9" class="px-4 py-4">
                                                    <div class="space-y-5">
                                                        <RecordDetailPanel :sections="recordDetailSections(checksheet)" />

                                                        <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                                                            <h5 class="text-xs font-semibold uppercase text-gray-500">Materials</h5>
                                                            <dl v-if="materialEntries(checksheet).length" class="mt-3 grid grid-cols-1 gap-3 text-sm md:grid-cols-2 lg:grid-cols-4">
                                                                <div v-for="[key, value] in materialEntries(checksheet)" :key="key">
                                                                    <dt class="font-medium text-gray-500">{{ materialFieldLabel(checksheet, key) }}</dt>
                                                                    <dd class="mt-1 break-words text-gray-900">{{ displayValue(value) }}</dd>
                                                                </div>
                                                            </dl>
                                                            <p v-else class="mt-3 text-sm text-gray-500">No material fields encoded.</p>
                                                        </div>

                                                        <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                                                            <h5 class="text-xs font-semibold uppercase text-gray-500">Samples</h5>
                                                            <div v-if="checksheet.samples?.length" class="mt-3 overflow-x-auto">
                                                                <table class="min-w-full divide-y divide-gray-200">
                                                                    <thead class="bg-gray-50">
                                                                        <tr>
                                                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Check Item</th>
                                                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Requirement</th>
                                                                            <th v-for="index in 5" :key="index" class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Sample {{ index }}</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="divide-y divide-gray-200 bg-white">
                                                                        <tr v-for="sample in checksheet.samples" :key="sample.id">
                                                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ displayValue(sample.check_item_label) }}</td>
                                                                            <td class="px-4 py-3 text-sm text-gray-500">{{ displayValue(sample.requirement_text) }}</td>
                                                                            <td v-for="index in 5" :key="index" class="px-4 py-3 text-center text-sm text-gray-700">
                                                                                {{ displayValue(sampleValues(sample)[index - 1]) }}
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            <p v-else class="mt-3 text-sm text-gray-500">No samples encoded.</p>
                                                        </div>

                                                        <div class="grid grid-cols-1 gap-4 rounded-md border border-gray-200 bg-white p-4 text-sm shadow-sm md:grid-cols-4">
                                                            <div class="md:col-span-2">
                                                                <dt class="font-medium text-gray-500">Remarks</dt>
                                                                <dd class="mt-1 break-words text-gray-900">{{ displayValue(checksheet.remarks) }}</dd>
                                                            </div>
                                                            <div>
                                                                <dt class="font-medium text-gray-500">Source Sheet</dt>
                                                                <dd class="mt-1 break-words text-gray-900">{{ displayValue(checksheet.source_sheet) }}</dd>
                                                            </div>
                                                            <div>
                                                                <dt class="font-medium text-gray-500">Source Row</dt>
                                                                <dd class="mt-1 break-words text-gray-900">{{ displayValue(checksheet.source_row) }}</dd>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr v-if="!pendingRecords.length">
                                            <td colspan="9" class="px-6 py-8 text-center text-sm text-gray-500">No pending welding checksheets.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-5" v-if="pendingChecksheets.links && pendingChecksheets.links.length > 3">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="text-sm text-gray-700">
                                        Showing <span class="font-medium">{{ pendingChecksheets.from || 0 }}</span> to
                                        <span class="font-medium">{{ pendingChecksheets.to || 0 }}</span> of
                                        <span class="font-medium">{{ totalPending }}</span> results
                                    </div>
                                    <div class="flex flex-wrap gap-1">
                                        <template v-for="(link, index) in pendingChecksheets.links" :key="index">
                                            <Link
                                                v-if="link.url"
                                                :href="link.url"
                                                class="px-4 py-2 border rounded-md text-sm font-medium"
                                                :class="{
                                                    'bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                                                    'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active
                                                }"
                                            >
                                                <span v-html="link.label" />
                                            </Link>
                                        </template>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
    <!-- eslint-enable vue/valid-v-for -->
</template>
