<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import axios from 'axios';

interface ItemConfig {
    id: number;
    item_code: string;
    item_name: string | null;
}

interface ChecksheetType {
    id: number;
    name: string;
    key: string;
    item_configs: ItemConfig[];
}

interface PreviewRecord {
    checksheet_type_id: number | null;
    item_code: string | null;
    production_date: string | null;
    machine_no: string | null;
    letter_code: string | null;
    job_number: string | null;
    prod_qty: number | null;
    quantity: number | null;
    material_fields: Record<string, string>;
    operator_name_raw: string | null;
    checked_by_name_raw: string | null;
    remarks: string | null;
}

interface DuplicateRecord {
    existing_id: number;
    existing_data: PreviewRecord;
    new_data: PreviewRecord;
}

interface PreviewResults {
    new_records: PreviewRecord[];
    duplicate_records: DuplicateRecord[];
    total_parsed: number;
    errors: string[];
}

interface ImportResults {
    imported: number;
    updated: number;
    skipped: number;
    errors: string[];
}

const props = defineProps<{
    types: ChecksheetType[];
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const selectedFileName = ref('');
const selectedTypeId = ref<number | null>(props.types[0]?.id || null);
const selectedItemConfigId = ref<number | null>(null);
const itemCode = ref('');
const itemName = ref('');
const previewResults = ref<PreviewResults | null>(null);
const importResults = ref<ImportResults | null>(null);
const updateDuplicates = ref(false);
const isPreviewLoading = ref(false);
const isImportLoading = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const hasTypes = computed(() => props.types.length > 0);
const selectedType = computed(() => props.types.find(type => type.id === Number(selectedTypeId.value)) || props.types[0]);
const itemConfigs = computed(() => selectedType.value?.item_configs || []);
const selectedItemConfig = computed(() => itemConfigs.value.find(config => config.id === Number(selectedItemConfigId.value)) || null);

const hasPreview = computed(() => previewResults.value !== null);
const hasDuplicates = computed(() => (previewResults.value?.duplicate_records?.length || 0) > 0);
const hasNewRecords = computed(() => (previewResults.value?.new_records?.length || 0) > 0);
const hasErrors = computed(() => (previewResults.value?.errors?.length || 0) > 0);
const canConfirmImport = computed(() => hasTypes.value && (hasNewRecords.value || hasDuplicates.value));

watch(selectedTypeId, () => {
    selectedItemConfigId.value = null;
    itemCode.value = '';
    itemName.value = '';
    previewResults.value = null;
    importResults.value = null;
});

watch(selectedItemConfigId, () => {
    if (selectedItemConfig.value) {
        itemCode.value = selectedItemConfig.value.item_code;
        itemName.value = selectedItemConfig.value.item_name || '';
    }
});

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        selectedFile.value = target.files[0];
        selectedFileName.value = target.files[0].name;
        previewResults.value = null;
        importResults.value = null;
        errorMessage.value = '';
        successMessage.value = '';
    }
};

const previewImport = async () => {
    if (!hasTypes.value) {
        errorMessage.value = 'No active welding checksheet types are available.';
        return;
    }

    if (!selectedTypeId.value) {
        errorMessage.value = 'Please select a checksheet type.';
        return;
    }

    if (!selectedFile.value) {
        errorMessage.value = 'Please select a file to import.';
        return;
    }

    const formData = new FormData();
    formData.append('file', selectedFile.value);
    formData.append('checksheet_type_id', String(selectedTypeId.value));
    if (selectedItemConfigId.value) formData.append('item_config_id', String(selectedItemConfigId.value));
    if (itemCode.value) formData.append('item_code', itemCode.value);
    if (itemName.value) formData.append('item_name', itemName.value);

    isPreviewLoading.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    try {
        const response = await axios.post(route('welding-checksheets.import.preview'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        if (response.data.success) {
            previewResults.value = response.data.preview;
        } else {
            errorMessage.value = response.data.error || 'Failed to preview file.';
        }
    } catch (error: any) {
        errorMessage.value = error.response?.data?.error || error.message || 'Failed to preview file.';
    } finally {
        isPreviewLoading.value = false;
    }
};

const executeImport = async () => {
    if (!hasTypes.value) {
        errorMessage.value = 'No active welding checksheet types are available.';
        return;
    }

    isImportLoading.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.post(route('welding-checksheets.import.execute'), {
            update_duplicates: updateDuplicates.value,
        });

        if (response.data.success) {
            importResults.value = response.data.results;
            successMessage.value = response.data.message;
            previewResults.value = null;
        } else {
            errorMessage.value = response.data.error || 'Failed to import file.';
        }
    } catch (error: any) {
        errorMessage.value = error.response?.data?.error || error.message || 'Failed to import file.';
    } finally {
        isImportLoading.value = false;
    }
};

const resetForm = () => {
    selectedFile.value = null;
    selectedFileName.value = '';
    previewResults.value = null;
    importResults.value = null;
    updateDuplicates.value = false;
    errorMessage.value = '';
    successMessage.value = '';
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};
</script>

<template>
    <Head title="Import Welding Checksheets" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Import Welding Checksheets
                </h2>
                <Link
                    :href="route('welding-checksheets.index')"
                    class="text-gray-600 hover:text-gray-800"
                >
                    &larr; Back to List
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div v-if="successMessage" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ successMessage }}</p>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="errorMessage" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ errorMessage }}</p>
                        </div>
                    </div>
                </div>

                <!-- Import Results -->
                <div v-if="importResults" class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Import Results</h3>

                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="bg-green-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-green-600">{{ importResults.imported }}</p>
                                <p class="text-sm text-green-800">Created</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-blue-600">{{ importResults.updated }}</p>
                                <p class="text-sm text-blue-800">Updated</p>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-yellow-600">{{ importResults.skipped }}</p>
                                <p class="text-sm text-yellow-800">Skipped</p>
                            </div>
                        </div>

                        <div v-if="importResults.errors && importResults.errors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="font-medium text-red-800 mb-2">Error Details:</h4>
                            <ul class="text-sm text-red-700 list-disc list-inside max-h-40 overflow-y-auto">
                                <li v-for="(err, index) in importResults.errors" :key="index">{{ err }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Preview Results -->
                <div v-if="hasPreview" class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Preview Results</h3>

                        <div class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg p-3">
                            <h4 class="font-medium text-indigo-800 mb-2">Import Context</h4>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ selectedType?.name }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ itemCode || 'Item code from file/custom import' }}
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-4 gap-4 mb-4">
                            <div class="bg-green-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-green-600">{{ previewResults?.new_records?.length || 0 }}</p>
                                <p class="text-sm text-green-800">New Records</p>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-yellow-600">{{ previewResults?.duplicate_records?.length || 0 }}</p>
                                <p class="text-sm text-yellow-800">Duplicates</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-gray-600">{{ previewResults?.total_parsed || 0 }}</p>
                                <p class="text-sm text-gray-800">Total Parsed</p>
                            </div>
                            <div class="bg-red-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-red-600">{{ previewResults?.errors?.length || 0 }}</p>
                                <p class="text-sm text-red-800">Errors</p>
                            </div>
                        </div>

                        <div v-if="hasErrors" class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="font-medium text-red-800 mb-2">Parsing Errors:</h4>
                            <ul class="text-sm text-red-700 list-disc list-inside max-h-40 overflow-y-auto">
                                <li v-for="(err, index) in previewResults?.errors" :key="index">{{ err }}</li>
                            </ul>
                        </div>

                        <div v-if="hasDuplicates" class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h4 class="font-medium text-yellow-800 mb-2">Duplicate Records Detected</h4>
                            <p class="text-sm text-yellow-700 mb-3">
                                {{ previewResults?.duplicate_records?.length || 0 }} record(s) already exist based on type, item code, production date, machine, letter code, and job number.
                            </p>

                            <label class="flex items-center">
                                <input
                                    type="checkbox"
                                    v-model="updateDuplicates"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                />
                                <span class="ml-2 text-sm text-yellow-800 font-medium">
                                    Update existing records with new data
                                </span>
                            </label>
                            <p class="mt-1 ml-6 text-xs text-yellow-600">
                                If unchecked, duplicates will be skipped. Updating duplicates replaces their sample rows.
                            </p>

                            <details class="mt-3">
                                <summary class="cursor-pointer text-sm font-medium text-yellow-800 hover:text-yellow-900">
                                    View duplicate records ({{ previewResults?.duplicate_records?.length }})
                                </summary>
                                <div class="mt-2 max-h-40 overflow-y-auto">
                                    <table class="min-w-full text-xs">
                                        <thead class="bg-yellow-100">
                                            <tr>
                                                <th class="px-2 py-1 text-left">Date</th>
                                                <th class="px-2 py-1 text-left">Item</th>
                                                <th class="px-2 py-1 text-left">Machine</th>
                                                <th class="px-2 py-1 text-left">Job Number</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(dup, index) in previewResults?.duplicate_records" :key="index" class="border-b border-yellow-100">
                                                <td class="px-2 py-1">{{ dup.new_data.production_date }}</td>
                                                <td class="px-2 py-1">{{ dup.new_data.item_code || 'N/A' }}</td>
                                                <td class="px-2 py-1">{{ dup.new_data.machine_no || 'N/A' }}</td>
                                                <td class="px-2 py-1">{{ dup.new_data.job_number || 'N/A' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        </div>

                        <div v-if="hasNewRecords" class="mb-4">
                            <details>
                                <summary class="cursor-pointer text-sm font-medium text-gray-700 hover:text-gray-900">
                                    View new records ({{ previewResults?.new_records?.length }})
                                </summary>
                                <div class="mt-2 max-h-60 overflow-y-auto">
                                    <table class="min-w-full text-xs">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-2 py-1 text-left">Date</th>
                                                <th class="px-2 py-1 text-left">Item</th>
                                                <th class="px-2 py-1 text-left">Machine</th>
                                                <th class="px-2 py-1 text-left">Letter</th>
                                                <th class="px-2 py-1 text-left">Job Number</th>
                                                <th class="px-2 py-1 text-left">Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(rec, index) in previewResults?.new_records" :key="index" class="border-b border-gray-100">
                                                <td class="px-2 py-1">{{ rec.production_date }}</td>
                                                <td class="px-2 py-1">{{ rec.item_code || 'N/A' }}</td>
                                                <td class="px-2 py-1">{{ rec.machine_no || 'N/A' }}</td>
                                                <td class="px-2 py-1">{{ rec.letter_code || 'N/A' }}</td>
                                                <td class="px-2 py-1">{{ rec.job_number || 'N/A' }}</td>
                                                <td class="px-2 py-1">{{ rec.prod_qty ?? rec.quantity ?? 'N/A' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        </div>

                        <div class="flex justify-end space-x-4 pt-4 border-t">
                            <button
                                type="button"
                                @click="resetForm"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                @click="executeImport"
                                :disabled="isImportLoading || !canConfirmImport"
                                class="px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 disabled:opacity-50"
                            >
                                {{ isImportLoading ? 'Importing...' : 'Confirm Import' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Upload Form -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Upload Excel File</h3>

                        <div v-if="!hasTypes" role="alert" class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                            No active welding checksheet types are available. Contact an administrator to restore the Welding configuration.
                        </div>

                        <div v-if="hasTypes" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div>
                                <label for="checksheet_type" class="block text-sm font-medium text-gray-700">
                                    Checksheet Type <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="checksheet_type"
                                    v-model="selectedTypeId"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                    <option v-for="type in types" :key="type.id" :value="type.id">{{ type.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="item_config" class="block text-sm font-medium text-gray-700">Item Code</label>
                                <select
                                    id="item_config"
                                    v-model="selectedItemConfigId"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                    <option :value="null">Custom or from file</option>
                                    <option v-for="config in itemConfigs" :key="config.id" :value="config.id">
                                        {{ config.item_code }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label for="item_code" class="block text-sm font-medium text-gray-700">Item Code Text</label>
                                <input
                                    id="item_code"
                                    v-model="itemCode"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    placeholder="e.g., CSB29046P3"
                                />
                            </div>
                            <div>
                                <label for="item_name" class="block text-sm font-medium text-gray-700">Item Name</label>
                                <input
                                    id="item_name"
                                    v-model="itemName"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                />
                            </div>
                        </div>

                        <div v-if="hasTypes" class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select Excel File (.xlsx, .xls) <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center justify-center w-full">
                                <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 mb-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                        <p class="mb-2 text-sm text-gray-500">
                                            <span class="font-semibold">Click to upload</span> or drag and drop
                                        </p>
                                        <p class="text-xs text-gray-500">Excel files only (max 10MB)</p>
                                    </div>
                                    <input
                                        ref="fileInput"
                                        type="file"
                                        class="hidden"
                                        accept=".xlsx,.xls"
                                        @change="handleFileChange"
                                    />
                                </label>
                            </div>
                            <p v-if="selectedFileName" class="mt-2 text-sm text-gray-600">
                                Selected: <span class="font-medium">{{ selectedFileName }}</span>
                            </p>
                        </div>

                        <div v-if="hasTypes" class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-medium text-blue-800 mb-2">Import Instructions</h4>
                            <ul class="text-sm text-blue-700 list-disc list-inside space-y-1">
                                <li>Select a checksheet type before uploading the workbook.</li>
                                <li>Casing-Tank files read records starting at row 10, with one record spanning 5 check rows.</li>
                                <li>Samples are imported from columns S through W for Casing-Tank templates.</li>
                                <li>Duplicates are detected by type, item code, production date, machine number, letter code, and job number.</li>
                                <li>Raw operator and checked-by names are retained even when no matching user exists.</li>
                                <li>Master, template, and item-code sheets are skipped automatically.</li>
                            </ul>
                        </div>

                        <div v-if="hasTypes" class="flex justify-end space-x-4">
                            <button
                                type="button"
                                @click="resetForm"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                            >
                                Reset
                            </button>
                            <button
                                type="button"
                                @click="previewImport"
                                :disabled="isPreviewLoading || !selectedFile || !hasTypes"
                                class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 disabled:opacity-50"
                            >
                                {{ isPreviewLoading ? 'Analyzing...' : 'Preview Import' }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
