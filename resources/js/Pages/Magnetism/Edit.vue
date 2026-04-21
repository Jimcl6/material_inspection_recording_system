<template>
    <Head title="Edit Magnetism Checksheet" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Magnetism Checksheet
                </h2>
                <Link 
                    :href="route('magnetism-checksheet.show', checksheet.id)" 
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-2xl mx-auto">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="item_code" class="block text-sm font-medium text-gray-700">Item Code <span class="text-red-500">*</span></label>
                                    <input
                                        id="item_code"
                                        v-model="form.item_code"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        :class="{ 'border-red-500': form.errors.item_code }"
                                    />
                                    <p v-if="form.errors.item_code" class="mt-1 text-sm text-red-600">{{ form.errors.item_code }}</p>
                                </div>

                                <div>
                                    <label for="item_name" class="block text-sm font-medium text-gray-700">Item Name <span class="text-red-500">*</span></label>
                                    <input
                                        id="item_name"
                                        v-model="form.item_name"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        :class="{ 'border-red-500': form.errors.item_name }"
                                    />
                                    <p v-if="form.errors.item_name" class="mt-1 text-sm text-red-600">{{ form.errors.item_name }}</p>
                                </div>

                                <div>
                                    <label for="machine_no" class="block text-sm font-medium text-gray-700">Machine No. <span class="text-red-500">*</span></label>
                                    <input
                                        id="machine_no"
                                        v-model="form.machine_no"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        :class="{ 'border-red-500': form.errors.machine_no }"
                                    />
                                    <p v-if="form.errors.machine_no" class="mt-1 text-sm text-red-600">{{ form.errors.machine_no }}</p>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="month" class="block text-sm font-medium text-gray-700">Month <span class="text-red-500">*</span></label>
                                        <select
                                            id="month"
                                            v-model="form.month"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            :class="{ 'border-red-500': form.errors.month }"
                                        >
                                            <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                                        </select>
                                        <p v-if="form.errors.month" class="mt-1 text-sm text-red-600">{{ form.errors.month }}</p>
                                    </div>

                                    <div>
                                        <label for="year" class="block text-sm font-medium text-gray-700">Year <span class="text-red-500">*</span></label>
                                        <select
                                            id="year"
                                            v-model="form.year"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            :class="{ 'border-red-500': form.errors.year }"
                                        >
                                            <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                                        </select>
                                        <p v-if="form.errors.year" class="mt-1 text-sm text-red-600">{{ form.errors.year }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end space-x-3">
                                <Link
                                    :href="route('magnetism-checksheet.show', checksheet.id)"
                                    class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
                                >
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Update Checksheet
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

declare function route(name: string, params?: any): string;

interface Checksheet {
    id: number;
    item_code: string;
    item_name: string;
    machine_no: string;
    month: number;
    year: number;
}

const props = defineProps<{
    checksheet: Checksheet;
}>();

const months = [
    { value: 1, label: 'January' },
    { value: 2, label: 'February' },
    { value: 3, label: 'March' },
    { value: 4, label: 'April' },
    { value: 5, label: 'May' },
    { value: 6, label: 'June' },
    { value: 7, label: 'July' },
    { value: 8, label: 'August' },
    { value: 9, label: 'September' },
    { value: 10, label: 'October' },
    { value: 11, label: 'November' },
    { value: 12, label: 'December' },
];

const currentYear = new Date().getFullYear();
const years = Array.from({ length: 5 }, (_, i) => currentYear - 2 + i);

const form = useForm({
    item_code: props.checksheet.item_code,
    item_name: props.checksheet.item_name,
    machine_no: props.checksheet.machine_no,
    month: props.checksheet.month,
    year: props.checksheet.year,
});

const submit = () => {
    form.put(route('magnetism-checksheet.update', props.checksheet.id));
};
</script>
