<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import axios from 'axios';

declare function route(name: string, params?: any): string;

interface BatchPreview {
    production_date: string;
    letter_code: string;
    material_lot_number: string;
    qr_code: string;
    produce_qty: number;
    job_number: string;
    month: number;
    year: number;
}

interface CheckpointPreview {
    production_date: string;
    checkpoint_number: number;
    sample1_first: number | null;
    operator_first: string | null;
    month: number;
    year: number;
}

interface DuplicateBatch {
    existing_id: number;
    existing_data: any;
    new_data: BatchPreview;
}

interface DuplicateCheckpoint {
    existing_id: number;
    existing_data: any;
    new_data: CheckpointPreview;
}

interface PreviewResults {
    new_batches: BatchPreview[];
    new_checkpoints: CheckpointPreview[];
    duplicate_batches: DuplicateBatch[];
    duplicate_checkpoints: DuplicateCheckpoint[];
    total_batches_parsed: number;
    total_checkpoints_parsed: number;
    errors: string[];
    sheets_processed: { name: string; month: number; year: number }[];
    detected_format: string | null;
}

interface ImportResults {
    batches_imported: number;
    batches_updated: number;
    batches_skipped: number;
    checkpoints_imported: number;
    checkpoints_updated: number;
    checkpoints_skipped: number;
    checksheets_created: { id: number; month: number; year: number }[];
    errors: string[];
}

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const selectedFileName = ref('');
const isPreviewLoading = ref(false);
const isImportLoading = ref(false);
const previewResults = ref<PreviewResults | null>(null);
const importResults = ref<ImportResults | null>(null);
const updateDuplicates = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

const itemCode = ref('');
const itemName = ref('');
const machineNo = ref('');
const selectedFormat = ref<string | null>(null); // null = auto-detect
const detectedFormat = ref<string | null>(null);
const availableFormats = ref<Record<string, string>>({});

const hasPreview = computed(() => previewResults.value !== null);
const hasDuplicateBatches = computed(() => (previewResults.value?.duplicate_batches?.length || 0) > 0);
const hasDuplicateCheckpoints = computed(() => (previewResults.value?.duplicate_checkpoints?.length || 0) > 0);
const hasDuplicates = computed(() => hasDuplicateBatches.value || hasDuplicateCheckpoints.value);
const hasNewBatches = computed(() => (previewResults.value?.new_batches?.length || 0) > 0);
const hasNewCheckpoints = computed(() => (previewResults.value?.new_checkpoints?.length || 0) > 0);
const hasNewRecords = computed(() => hasNewBatches.value || hasNewCheckpoints.value);
const hasErrors = computed(() => (previewResults.value?.errors?.length || 0) > 0);

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        selectedFile.value = target.files[0];
        selectedFileName.value = target.files[0].name;
        previewResults.value = null;
        importResults.value = null;
        detectedFormat.value = null;
        errorMessage.value = '';
        successMessage.value = '';
    }
};

const previewImport = async () => {
    if (!selectedFile.value) {
        errorMessage.value = 'Please select a file to import.';
        return;
    }
    if (!itemCode.value || !itemName.value || !machineNo.value) {
        errorMessage.value = 'Please fill in all required fields.';
        return;
    }

    isPreviewLoading.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    const formData = new FormData();
    formData.append('file', selectedFile.value);
    formData.append('item_code', itemCode.value);
    formData.append('item_name', itemName.value);
    formData.append('machine_no', machineNo.value);
    if (selectedFormat.value) {
        formData.append('format', selectedFormat.value);
    }

    try {
        const response = await axios.post(route('magnetism-checksheet.import.preview'), formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        if (response.data.success) {
            previewResults.value = response.data.preview;
            detectedFormat.value = response.data.detected_format;
            availableFormats.value = response.data.available_formats || {};
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
    isImportLoading.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.post(route('magnetism-checksheet.import.execute'), {
            update_duplicates: updateDuplicates.value,
            format: detectedFormat.value,
        });

        if (response.data.success) {
            importResults.value = response.data.results;
            successMessage.value = response.data.message;
            previewResults.value = null;

            // Redirect to checksheet if available
            if (response.data.redirect_id) {
                setTimeout(() => {
                    router.visit(route('magnetism-checksheet.show', response.data.redirect_id));
                }, 2000);
            }
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
    itemCode.value = '';
    itemName.value = '';
    machineNo.value = '';
    selectedFormat.value = null;
    detectedFormat.value = null;
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const getMonthName = (month: number): string => {
    const months = ['', 'January', 'February', 'March', 'April', 'May', 'June', 
                    'July', 'August', 'September', 'October', 'November', 'December'];
    return months[month] || '';
};
</script>

<template>
    <Head title="Import Magnetism Checksheet" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Import Magnetism Checksheet
                </h2>
                <Link 
                    :href="route('magnetism-checksheet.index')" 
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
                            <p class="text-xs text-green-600 mt-1">Redirecting to checksheet...</p>
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
                                <p class="text-2xl font-bold text-green-600">
                                    {{ importResults.batches_imported + importResults.checkpoints_imported }}
                                </p>
                                <p class="text-sm text-green-800">Created</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-blue-600">
                                    {{ importResults.batches_updated + importResults.checkpoints_updated }}
                                </p>
                                <p class="text-sm text-blue-800">Updated</p>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-yellow-600">
                                    {{ importResults.batches_skipped + importResults.checkpoints_skipped }}
                                </p>
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
                        
                        <!-- Format Detection -->
                        <div v-if="detectedFormat" class="mb-4 bg-purple-50 border border-purple-200 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-medium text-purple-800">Format Detected:</h4>
                                    <p class="text-sm text-purple-700">
                                        <span class="font-semibold">{{ detectedFormat }}</span>
                                        <span v-if="availableFormats[detectedFormat]" class="text-purple-600 ml-1">
                                            ({{ availableFormats[detectedFormat].replace(detectedFormat + ' ', '') }})
                                        </span>
                                    </p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-purple-700 mb-1">Change format:</label>
                                    <select 
                                        v-model="detectedFormat"
                                        class="text-sm rounded-md border-purple-300 shadow-sm focus:border-purple-500 focus:ring-purple-500"
                                    >
                                        <option v-for="(label, key) in availableFormats" :key="key" :value="key">
                                            {{ key }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Sheets Processed -->
                        <div v-if="previewResults?.sheets_processed?.length" class="mb-4 bg-indigo-50 border border-indigo-200 rounded-lg p-3">
                            <h4 class="font-medium text-indigo-800 mb-2">Sheets Detected:</h4>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="sheet in previewResults.sheets_processed" :key="sheet.name" 
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ sheet.name }} ({{ getMonthName(sheet.month) }} {{ sheet.year }})
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-4 gap-4 mb-4">
                            <div class="bg-green-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-green-600">{{ previewResults?.new_batches?.length || 0 }}</p>
                                <p class="text-sm text-green-800">New Batches</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-green-600">{{ previewResults?.new_checkpoints?.length || 0 }}</p>
                                <p class="text-sm text-green-800">New Checkpoints</p>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-yellow-600">
                                    {{ (previewResults?.duplicate_batches?.length || 0) + (previewResults?.duplicate_checkpoints?.length || 0) }}
                                </p>
                                <p class="text-sm text-yellow-800">Duplicates</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-gray-600">
                                    {{ (previewResults?.total_batches_parsed || 0) + (previewResults?.total_checkpoints_parsed || 0) }}
                                </p>
                                <p class="text-sm text-gray-800">Total Parsed</p>
                            </div>
                        </div>

                        <!-- Preview Errors -->
                        <div v-if="hasErrors" class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="font-medium text-red-800 mb-2">Parsing Errors:</h4>
                            <ul class="text-sm text-red-700 list-disc list-inside">
                                <li v-for="(err, index) in previewResults?.errors" :key="index">{{ err }}</li>
                            </ul>
                        </div>

                        <!-- Duplicate Handling -->
                        <div v-if="hasDuplicates" class="mb-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h4 class="font-medium text-yellow-800 mb-2">Duplicate Records Detected</h4>
                            <p class="text-sm text-yellow-700 mb-3">
                                {{ previewResults?.duplicate_batches?.length || 0 }} batch(es) and 
                                {{ previewResults?.duplicate_checkpoints?.length || 0 }} checkpoint(s) already exist.
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
                                If unchecked, duplicates will be skipped.
                            </p>

                            <!-- Duplicate Batches Details -->
                            <details v-if="hasDuplicateBatches" class="mt-3">
                                <summary class="cursor-pointer text-sm font-medium text-yellow-800 hover:text-yellow-900">
                                    View duplicate batches ({{ previewResults?.duplicate_batches?.length }})
                                </summary>
                                <div class="mt-2 max-h-40 overflow-y-auto">
                                    <table class="min-w-full text-xs">
                                        <thead class="bg-yellow-100">
                                            <tr>
                                                <th class="px-2 py-1 text-left">Date</th>
                                                <th class="px-2 py-1 text-left">Letter</th>
                                                <th class="px-2 py-1 text-left">Material Lot</th>
                                                <th class="px-2 py-1 text-left">QR Code</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(dup, index) in previewResults?.duplicate_batches" :key="index" class="border-b border-yellow-100">
                                                <td class="px-2 py-1">{{ dup.new_data.production_date }}</td>
                                                <td class="px-2 py-1">{{ dup.new_data.letter_code }}</td>
                                                <td class="px-2 py-1">{{ dup.new_data.material_lot_number }}</td>
                                                <td class="px-2 py-1">{{ dup.new_data.qr_code }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        </div>

                        <!-- New Batches Preview -->
                        <div v-if="hasNewBatches" class="mb-4">
                            <details>
                                <summary class="cursor-pointer text-sm font-medium text-gray-700 hover:text-gray-900">
                                    View new batches ({{ previewResults?.new_batches?.length }})
                                </summary>
                                <div class="mt-2 max-h-60 overflow-y-auto">
                                    <table class="min-w-full text-xs">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-2 py-1 text-left">Date</th>
                                                <th class="px-2 py-1 text-left">Letter</th>
                                                <th class="px-2 py-1 text-left">Material Lot</th>
                                                <th class="px-2 py-1 text-left">QR Code</th>
                                                <th class="px-2 py-1 text-left">Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(rec, index) in previewResults?.new_batches" :key="index" class="border-b border-gray-100">
                                                <td class="px-2 py-1">{{ rec.production_date }}</td>
                                                <td class="px-2 py-1">{{ rec.letter_code }}</td>
                                                <td class="px-2 py-1">{{ rec.material_lot_number }}</td>
                                                <td class="px-2 py-1">{{ rec.qr_code }}</td>
                                                <td class="px-2 py-1">{{ rec.produce_qty }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        </div>

                        <!-- Confirm Import Button -->
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
                                :disabled="isImportLoading || (!hasNewRecords && !hasDuplicates)"
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
                        
                        <!-- Header Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                            <div>
                                <label for="item_code" class="block text-sm font-medium text-gray-700">Item Code <span class="text-red-500">*</span></label>
                                <input
                                    id="item_code"
                                    v-model="itemCode"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    placeholder="e.g., RDB4600400"
                                />
                            </div>
                            <div>
                                <label for="item_name" class="block text-sm font-medium text-gray-700">Item Name <span class="text-red-500">*</span></label>
                                <input
                                    id="item_name"
                                    v-model="itemName"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                />
                            </div>
                            <div>
                                <label for="machine_no" class="block text-sm font-medium text-gray-700">Machine No. <span class="text-red-500">*</span></label>
                                <input
                                    id="machine_no"
                                    v-model="machineNo"
                                    type="text"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                />
                            </div>
                            <div>
                                <label for="format" class="block text-sm font-medium text-gray-700">Document Format</label>
                                <select
                                    id="format"
                                    v-model="selectedFormat"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                >
                                    <option :value="null">Auto-detect</option>
                                    <option value="HPI-PR05-03">HPI-PR05-03 (Tesla 160~210mT)</option>
                                    <option value="HPI-PR03-01">HPI-PR03-01 (Tesla 120~150mT)</option>
                                </select>
                                <p class="mt-1 text-xs text-gray-500">Leave as auto-detect to identify format from file structure</p>
                            </div>
                        </div>

                        <!-- File Input -->
                        <div class="mb-6">
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

                        <!-- Instructions -->
                        <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-medium text-blue-800 mb-2">Import Instructions</h4>
                            <ul class="text-sm text-blue-700 list-disc list-inside space-y-1">
                                <li><strong>Document Format:</strong> Auto-detect identifies the format from file structure, or manually select HPI-PR03-01 or HPI-PR05-03</li>
                                <li>Month/year is auto-detected from sheet names (e.g., "December 2025")</li>
                                <li>Batches: date, letter code, material lot, QR code, produce qty, job number</li>
                                <li>Checkpoints: 4 rows per date with Tesla samples (first/last inspection)</li>
                                <li>Duplicate batches are detected by date + material lot + QR code</li>
                                <li>Duplicate checkpoints are detected by date + checkpoint number</li>
                                <li>Master/template sheets are skipped automatically</li>
                            </ul>
                        </div>

                        <!-- Preview Button -->
                        <div class="flex justify-end space-x-4">
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
                                :disabled="isPreviewLoading || !selectedFile"
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
