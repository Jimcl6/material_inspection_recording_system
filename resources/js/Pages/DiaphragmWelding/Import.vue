<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';
import { route } from 'ziggy-js';

interface ImportResults {
    imported: number;
    skipped: number;
    errors: string[];
}

interface Props {
    import_results?: ImportResults;
    success?: string;
    error?: string;
}

const props = defineProps<Props>();

const form = useForm({
    file: null as File | null,
    overwrite: false,
});

const fileInput = ref<HTMLInputElement | null>(null);
const selectedFileName = ref('');

const handleFileChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.file = target.files[0];
        selectedFileName.value = target.files[0].name;
    }
};

const submit = () => {
    if (!form.file) {
        alert('Please select a file to import.');
        return;
    }

    form.post(route('diaphragm-welding.import'), {
        forceFormData: true,
    });
};

const resetForm = () => {
    form.reset();
    selectedFileName.value = '';
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};
</script>

<template>
    <Head title="Import Diaphragm Welding Checksheets" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Import Diaphragm Welding Checksheets
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
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <!-- Success Message -->
                <div v-if="success" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">{{ success }}</p>
                        </div>
                    </div>
                </div>

                <!-- Error Message -->
                <div v-if="error" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">{{ error }}</p>
                        </div>
                    </div>
                </div>

                <!-- Import Results -->
                <div v-if="import_results" class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Import Results</h3>
                        
                        <div class="grid grid-cols-3 gap-4 mb-4">
                            <div class="bg-green-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-green-600">{{ import_results.imported }}</p>
                                <p class="text-sm text-green-800">Imported</p>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-yellow-600">{{ import_results.skipped }}</p>
                                <p class="text-sm text-yellow-800">Skipped</p>
                            </div>
                            <div class="bg-red-50 rounded-lg p-4 text-center">
                                <p class="text-2xl font-bold text-red-600">{{ import_results.errors?.length || 0 }}</p>
                                <p class="text-sm text-red-800">Errors</p>
                            </div>
                        </div>

                        <div v-if="import_results.errors && import_results.errors.length > 0" class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <h4 class="font-medium text-red-800 mb-2">Error Details:</h4>
                            <ul class="text-sm text-red-700 list-disc list-inside max-h-40 overflow-y-auto">
                                <li v-for="(err, index) in import_results.errors" :key="index">{{ err }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Import Form -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Upload Excel File</h3>
                        
                        <form @submit.prevent="submit">
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
                                <p v-if="form.errors.file" class="mt-1 text-sm text-red-600">{{ form.errors.file }}</p>
                            </div>

                            <!-- Overwrite Option -->
                            <div class="mb-6">
                                <label class="flex items-center">
                                    <input 
                                        type="checkbox" 
                                        v-model="form.overwrite"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    />
                                    <span class="ml-2 text-sm text-gray-600">
                                        Delete all existing records before import
                                    </span>
                                </label>
                                <p class="mt-1 text-xs text-red-500">
                                    Warning: This will permanently delete all existing checksheet records!
                                </p>
                            </div>

                            <!-- Instructions -->
                            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h4 class="font-medium text-blue-800 mb-2">Import Instructions</h4>
                                <ul class="text-sm text-blue-700 list-disc list-inside space-y-1">
                                    <li>Use the same Excel format as the reference file (DFB6602001..xlsx)</li>
                                    <li>Each record spans 10 rows starting with a date in column A</li>
                                    <li>Data starts from row 10 in each sheet</li>
                                    <li>Master/template sheets will be skipped automatically</li>
                                    <li>Records with invalid dates will be skipped</li>
                                </ul>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end space-x-4">
                                <button 
                                    type="button"
                                    @click="resetForm"
                                    class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                                >
                                    Reset
                                </button>
                                <button 
                                    type="submit"
                                    :disabled="form.processing || !form.file"
                                    class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 disabled:opacity-50"
                                >
                                    {{ form.processing ? 'Importing...' : 'Import' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
