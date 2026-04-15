<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';
import axios from 'axios';

interface PreviewRecord {
    item_code: string;
    annealing_date: string;
    receiving_date: string;
    supplier_lot_number: string;
    quantity: number;
    machine_number: string;
    temperature_setting: string;
    annealing_time: string;
    damper_setting: string;
    time_in: string;
    time_out: string;
    remarks: string;
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

interface Props {
    import_results?: ImportResults;
    success?: string;
    error?: string;
}

const props = defineProps<Props>();

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFile = ref<File | null>(null);
const selectedFileName = ref('');
const isPreviewLoading = ref(false);
const isImportLoading = ref(false);
const previewResults = ref<PreviewResults | null>(null);
const importResults = ref<ImportResults | null>(props.import_results || null);
const updateDuplicates = ref(false);
const errorMessage = ref(props.error || '');
const successMessage = ref(props.success || '');

const hasPreview = computed(() => previewResults.value !== null);
const hasDuplicates = computed(() => (previewResults.value?.duplicate_records?.length || 0) > 0);
const hasNewRecords = computed(() => (previewResults.value?.new_records?.length || 0) > 0);
const hasErrors = computed(() => (previewResults.value?.errors?.length || 0) > 0);

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        selectedFile.value = target.files[0];
        selectedFileName.value = target.files[0].name;
        // Reset preview when new file selected
        previewResults.value = null;
        importResults.value = null;
        errorMessage.value = '';
        successMessage.value = '';
    }
};

const previewImport = async () => {
    if (!selectedFile.value) {
        errorMessage.value = 'Please select a file to import.';
        return;
    }

    isPreviewLoading.value = true;
    errorMessage.value = '';
    successMessage.value = '';

    const formData = new FormData();
    formData.append('file', selectedFile.value);

    try {
        const response = await axios.post(route('annealing-checks.import.preview'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
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
    isImportLoading.value = true;
    errorMessage.value = '';

    try {
        const response = await axios.post(route('annealing-checks.import.execute'), {
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
    <Head title="Import Annealing Checks" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Import Annealing Checks
                </h2>
                <Link 
                    :href="route('annealing-checks.index')" 
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
                        
                        <div class="grid grid-cols-4 gap-4 mb-4">
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
                            <div class="bg-red-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-red-600">{{ importResults.errors?.length || 0 }}</p>
                                <p class="text-sm text-red-800">Errors</p>
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
                        
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="bg-green-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-green-600">{{ previewResults?.new_records?.length || 0 }}</p>
                                <p class="text-sm text-green-800">New Records</p>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-yellow-600">{{ previewResults?.duplicate_records?.length || 0 }}</p>
                                <p class="text-sm text-yellow-800">Duplicates Found</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-gray-600">{{ previewResults?.total_parsed || 0 }}</p>
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
                                {{ previewResults?.duplicate_records?.length }} record(s) already exist in the database with matching item code and annealing date.
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

                            <!-- Duplicate Details (collapsible) -->
                            <details class="mt-3">
                                <summary class="cursor-pointer text-sm font-medium text-yellow-800 hover:text-yellow-900">
                                    View duplicate details
                                </summary>
                                <div class="mt-2 max-h-60 overflow-y-auto">
                                    <table class="min-w-full text-xs">
                                        <thead class="bg-yellow-100">
                                            <tr>
                                                <th class="px-2 py-1 text-left">Item Code</th>
                                                <th class="px-2 py-1 text-left">Annealing Date</th>
                                                <th class="px-2 py-1 text-left">Machine #</th>
                                                <th class="px-2 py-1 text-left">Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(dup, index) in previewResults?.duplicate_records" :key="index" class="border-b border-yellow-100">
                                                <td class="px-2 py-1">{{ dup.new_data.item_code }}</td>
                                                <td class="px-2 py-1">{{ dup.new_data.annealing_date }}</td>
                                                <td class="px-2 py-1">{{ dup.new_data.machine_number }}</td>
                                                <td class="px-2 py-1">{{ dup.new_data.quantity }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </details>
                        </div>

                        <!-- New Records Preview -->
                        <div v-if="hasNewRecords" class="mb-4">
                            <details>
                                <summary class="cursor-pointer text-sm font-medium text-gray-700 hover:text-gray-900">
                                    View new records ({{ previewResults?.new_records?.length }})
                                </summary>
                                <div class="mt-2 max-h-60 overflow-y-auto">
                                    <table class="min-w-full text-xs">
                                        <thead class="bg-gray-100">
                                            <tr>
                                                <th class="px-2 py-1 text-left">Item Code</th>
                                                <th class="px-2 py-1 text-left">Annealing Date</th>
                                                <th class="px-2 py-1 text-left">Machine #</th>
                                                <th class="px-2 py-1 text-left">Temp</th>
                                                <th class="px-2 py-1 text-left">Quantity</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(rec, index) in previewResults?.new_records" :key="index" class="border-b border-gray-100">
                                                <td class="px-2 py-1">{{ rec.item_code }}</td>
                                                <td class="px-2 py-1">{{ rec.annealing_date }}</td>
                                                <td class="px-2 py-1">{{ rec.machine_number }}</td>
                                                <td class="px-2 py-1">{{ rec.temperature_setting }}</td>
                                                <td class="px-2 py-1">{{ rec.quantity }}</td>
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
                        
                        <!-- File Input -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Select Excel File (.xlsx, .xls)
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
                                <li>Use the standard Annealing Checksheet Excel format</li>
                                <li>Column headers are detected from rows 8-9</li>
                                <li>Data rows start from row 10</li>
                                <li>Item Code is required and must match pattern (e.g., "AA123...")</li>
                                <li>Duplicates are detected by Item Code + Annealing Date</li>
                                <li>Sheets with less than 10 rows will be skipped</li>
                            </ul>
                            <p class="mt-2">
                                <a 
                                    :href="route('annealing-checks.export')" 
                                    class="font-medium text-blue-600 hover:text-blue-500"
                                    download
                                >
                                    Download Template
                                </a>
                            </p>
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
