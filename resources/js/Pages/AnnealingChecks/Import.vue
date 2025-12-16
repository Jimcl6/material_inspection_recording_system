<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

const form = useForm({
    file: null,
    overwrite: false,
});

const fileInput = ref(null);

const handleFileChange = (event) => {
    form.file = event.target.files[0];
};

const submit = () => {
    form.post(route('annealing-checks.import'), {
        onSuccess: () => {
            form.reset();
            if (fileInput.value) {
                fileInput.value.value = '';
            }
        },
    });
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
                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                >
                    Back to List
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="max-w-3xl mx-auto">
                            <!-- Instructions -->
                            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-blue-800">Import Instructions</h3>
                                        <div class="mt-2 text-sm text-blue-700">
                                            <p class="mb-2">
                                                Upload an Excel file (.xlsx, .xls, .csv) containing annealing check data. The file should follow the standard format with these columns:
                                            </p>
                                            <ul class="list-disc pl-5 space-y-1">
                                                <li>Item Code (required)</li>
                                                <li>Receiving Date (YYYY-MM-DD, required)</li>
                                                <li>Supplier Lot #</li>
                                                <li>Quantity (required, minimum 1)</li>
                                                <li>Annealing Date (YYYY-MM-DD, required)</li>
                                                <li>Machine # (required)</li>
                                                <li>Machine Setting</li>
                                                <li>PIC (ID of the user)</li>
                                                <li>Checked By (ID of the user)</li>
                                                <li>Verified By (ID of the user)</li>
                                                <li>Temperature Readings (format: "HH:MM: TT.TT°C | HH:MM: TT.TT°C")</li>
                                                <li>Remarks</li>
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
                                    </div>
                                </div>
                            </div>

                            <!-- Import Form -->
                            <form @submit.prevent="submit">
                                <!-- File Input -->
                                <div class="mb-6">
                                    <InputLabel for="file" value="Excel File *" />
                                    <div class="mt-1 flex items-center">
                                        <input
                                            id="file"
                                            ref="fileInput"
                                            type="file"
                                            class="hidden"
                                            accept=".xlsx,.xls,.csv"
                                            @change="handleFileChange"
                                        />
                                        <label 
                                            for="file"
                                            class="cursor-pointer inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                                        >
                                            <svg class="w-5 h-5 mr-2 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                            {{ form.file ? form.file.name : 'Choose file...' }}
                                        </label>
                                        <span v-if="form.file" class="ml-3 text-sm text-gray-500">
                                            {{ (form.file.size / 1024).toFixed(1) }} KB
                                        </span>
                                    </div>
                                    <InputError :message="form.errors.file" class="mt-2" />
                                </div>

                                <!-- Options -->
                                <div class="mb-6">
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input
                                                id="overwrite"
                                                v-model="form.overwrite"
                                                type="checkbox"
                                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                                            />
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="overwrite" class="font-medium text-gray-700">
                                                Overwrite existing records
                                            </label>
                                            <p class="text-gray-500">
                                                Check this to delete all existing records before importing.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <div class="flex items-center justify-end">
                                    <Link
                                        :href="route('annealing-checks.index')"
                                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4"
                                    >
                                        Cancel
                                    </Link>
                                    <PrimaryButton
                                        :class="{ 'opacity-25': form.processing }"
                                        :disabled="!form.file || form.processing"
                                    >
                                        <span v-if="form.processing">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Importing...
                                        </span>
                                        <span v-else>Import Data</span>
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
