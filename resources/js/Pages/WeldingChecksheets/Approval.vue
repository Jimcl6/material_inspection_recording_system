<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TabletApprovalReview from '@/Components/Tablet/TabletApprovalReview.vue';
import { useTabletMode } from '@/Composables/useTabletMode';
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
    from: number | null;
    to: number | null;
    total: number;
}

const props = defineProps<{
    pendingChecksheets: Paginated<PendingChecksheet>;
}>();

const selectedIds = ref<number[]>([]);
const notes = ref('');
const tabletBulkMode = ref(false);
const { isTabletMode } = useTabletMode();

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

const sampleSummary = (sample: Sample): string => {
    const values = sampleValues(sample)
        .map((value, index) => `S${index + 1}: ${displayValue(value)}`)
        .join(', ');

    return [
        sample.requirement_text ? `Requirement: ${sample.requirement_text}` : null,
        values || null,
    ].filter(Boolean).join(' | ') || 'N/A';
};

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

const tabletDetailSections = (checksheet: PendingChecksheet): DetailSection[] => [
    ...recordDetailSections(checksheet),
    {
        title: 'Materials',
        items: materialEntries(checksheet).length
            ? materialEntries(checksheet).map(([key, value]) => ({ label: materialFieldLabel(checksheet, key), value }))
            : [{ label: 'Materials', value: 'No material fields encoded' }],
    },
    {
        title: 'Samples',
        items: checksheet.samples?.length
            ? checksheet.samples.map(sample => ({
                label: sample.check_item_label || 'Sample',
                value: sampleSummary(sample),
            }))
            : [{ label: 'Samples', value: 'No samples encoded' }],
    },
    {
        title: 'Remarks',
        items: [
            { label: 'Remarks', value: checksheet.remarks },
            { label: 'Source File', value: checksheet.source_file },
            { label: 'Source Sheet', value: checksheet.source_sheet },
            { label: 'Source Row', value: checksheet.source_row },
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
                        <TabletApprovalReview
                            v-if="isTabletMode && !tabletBulkMode"
                            v-model:notes="notes"
                            :records="pendingRecords"
                            :title-for="(checksheet) => checksheet.item_code || `Record #${checksheet.id}`"
                            :subtitle-for="(checksheet) => checksheet.type?.name"
                            :facts-for="(checksheet) => [
                                { label: 'Date', value: formatDate(checksheet.production_date) },
                                { label: 'Machine', value: checksheet.machine_no },
                                { label: 'Job number', value: checksheet.job_number },
                                { label: 'Operator', value: checksheet.operator?.name || checksheet.operator_name_raw },
                            ]"
                            :details-for="tabletDetailSections"
                            show-route-name="welding-checksheets.show"
                            :processing="approveForm.processing"
                            @approve="(checksheet) => submitOne(checksheet, 'approve')"
                            @reject="(checksheet) => submitOne(checksheet, 'reject')"
                            @bulk="tabletBulkMode = true"
                        />
                        <template v-else>
                            <div class="mb-5 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">Pending Checksheets</h3>
                                    <p class="text-sm text-gray-500">
                                        Showing {{ pendingChecksheets.from || 0 }} to {{ pendingChecksheets.to || 0 }} of {{ totalPending }} record(s) awaiting review.
                                    </p>
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

                            <div v-if="pendingRecords.length" class="space-y-5">
                                <article v-for="checksheet in pendingRecords" :key="checksheet.id" class="rounded-md border border-gray-200 bg-white shadow-sm">
                                    <div class="flex flex-col gap-4 border-b border-gray-200 px-5 py-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div class="flex gap-3">
                                            <input v-model="selectedIds" :value="checksheet.id" type="checkbox" class="mt-1 rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                            <div>
                                                <p class="text-xs font-semibold uppercase tracking-wider text-yellow-700">Pending review</p>
                                                <h4 class="mt-1 text-base font-semibold text-gray-900">
                                                    {{ checksheet.item_code || `Record #${checksheet.id}` }}
                                                </h4>
                                                <p class="text-sm text-gray-500">
                                                    {{ checksheet.type?.name || 'N/A' }} · {{ formatDate(checksheet.production_date) }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap items-center gap-2">
                                            <Link :href="route('welding-checksheets.show', checksheet.id)" class="px-3 py-2 text-sm font-medium text-indigo-700 hover:text-indigo-900">
                                                View
                                            </Link>
                                            <button @click="submitOne(checksheet, 'reject')" :disabled="approveForm.processing" class="px-3 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700 disabled:opacity-50">Reject</button>
                                            <button @click="submitOne(checksheet, 'approve')" :disabled="approveForm.processing" class="px-3 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700 disabled:opacity-50">Approve</button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 gap-5 px-5 py-5 lg:grid-cols-3">
                                        <section v-for="section in recordDetailSections(checksheet)" :key="section.title">
                                            <h5 class="text-xs font-semibold uppercase text-gray-500">{{ section.title }}</h5>
                                            <dl class="mt-3 space-y-2">
                                                <div v-for="item in section.items" :key="`${section.title}-${item.label}`" class="grid grid-cols-3 gap-3 text-sm">
                                                    <dt class="text-gray-500">{{ item.label }}</dt>
                                                    <dd class="col-span-2 break-words font-medium text-gray-900">{{ displayValue(item.value) }}</dd>
                                                </div>
                                            </dl>
                                        </section>
                                    </div>

                                    <div class="border-t border-gray-200 px-5 py-5">
                                        <h5 class="text-xs font-semibold uppercase text-gray-500">Materials</h5>
                                        <dl v-if="materialEntries(checksheet).length" class="mt-3 grid grid-cols-1 gap-3 text-sm md:grid-cols-2 lg:grid-cols-4">
                                            <div v-for="[key, value] in materialEntries(checksheet)" :key="key">
                                                <dt class="font-medium text-gray-500">{{ materialFieldLabel(checksheet, key) }}</dt>
                                                <dd class="mt-1 break-words text-gray-900">{{ displayValue(value) }}</dd>
                                            </div>
                                        </dl>
                                        <p v-else class="mt-3 text-sm text-gray-500">No material fields encoded.</p>
                                    </div>

                                    <div class="border-t border-gray-200 px-5 py-5">
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

                                    <div class="grid grid-cols-1 gap-4 border-t border-gray-200 px-5 py-5 text-sm md:grid-cols-4">
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
                                </article>
                            </div>

                            <div v-else class="py-10 text-center text-sm text-gray-500">
                                No pending welding checksheets.
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
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
    <!-- eslint-enable vue/valid-v-for -->
</template>
