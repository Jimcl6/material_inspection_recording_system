<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { route } from 'ziggy-js';
import AppLayout from '@/Layouts/AppLayout.vue';

interface User {
    id: number;
    name: string;
}

interface FormData {
    item_code: string;
    receiving_date: string;
    supplier_lot_number: string;
    quantity: number;
    annealing_date: string;
    machine_number: string;
    temperature_setting: number | null;
    annealing_time: string;
    damper_setting: string;
    time_in: string;
    time_out: string;
    pic_id: string | number;
    checked_by_id: string | number;
    verified_by_id: string | number;
    remarks: string;
}

const page = usePage();
const isLoading = ref(false);
const showSuccess = ref(false);
const users = computed(() => (page.props as { users: User[] }).users || []);

// Form validation rules
const validateForm = () => {
    const errors: Record<string, string> = {};
    
    if (!form.item_code) errors.item_code = 'Item code is required';
    if (!form.receiving_date) errors.receiving_date = 'Receiving date is required';
    if (!form.annealing_date) errors.annealing_date = 'Annealing date is required';
    if (!form.pic_id) errors.pic_id = 'Person in charge is required';

    return errors;
};

const form = useForm({
    item_code: '',
    receiving_date: '',
    supplier_lot_number: '',
    quantity: 1,
    annealing_date: '',
    machine_number: '',
    temperature_setting: null,
    annealing_time: '',
    damper_setting: '',
    time_in: '',
    time_out: '',
    pic_id: '',
    checked_by_id: '',
    verified_by_id: '',
    remarks: ''
});

const submit = async (): Promise<void> => {
    const validationErrors = validateForm();
    if (Object.keys(validationErrors).length > 0) {
        form.setError(validationErrors);
        return;
    }
    
    isLoading.value = true;
    form.post('/annealing-checks', {
        onSuccess: () => {
            form.reset();
            showSuccess.value = true;
            // Scroll to top
            window.scrollTo({ top: 0, behavior: 'smooth' });
            // Hide success message after 5 seconds
            setTimeout(() => {
                showSuccess.value = false;
            }, 5000);
        },
        onError: (errors: Record<string, string>) => {
            console.error('Error creating check:', errors);
            // Scroll to first error
            const firstError = Object.keys(errors)[0];
            if (firstError) {
                const element = document.querySelector(`[name="${firstError}"]`);
                element?.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

// Auto-validate on field blur
const onBlur = (field: string) => {
    if (form.errors[field]) {
        form.clearErrors(field as any);
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Create Annealing Check" />
        <!-- Success Message -->
        <div v-if="showSuccess" class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline"> Annealing check created successfully!</span>
        </div>

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create New Annealing Check
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Basic Information -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Item Code -->
                                <div>
                                    <label for="item_code" class="block text-sm font-medium text-gray-700">
                                        Item Code <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="item_code"
                                        v-model="form.item_code"
                                        type="text"
                                        required
                                        :class="{
                                            'border-red-500': form.errors.item_code,
                                            'border-gray-300': !form.errors.item_code
                                        }"
                                        class="mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        @blur="onBlur('item_code')"
                                    />
                                    <p v-if="form.errors.item_code" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.item_code }}
                                    </p>
                                </div>

                                <!-- Receiving Date -->
                                <div>
                                    <label for="receiving_date" class="block text-sm font-medium text-gray-700">
                                        Receiving Date <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="receiving_date"
                                        v-model="form.receiving_date"
                                        type="date"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <p v-if="form.errors.receiving_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.receiving_date }}
                                    </p>
                                </div>

                                <!-- Supplier Lot Number -->
                                <div>
                                    <label for="supplier_lot_number" class="block text-sm font-medium text-gray-700">
                                        Supplier Lot Number <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="supplier_lot_number"
                                        v-model="form.supplier_lot_number"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <p v-if="form.errors.supplier_lot_number" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.supplier_lot_number }}
                                    </p>
                                </div>

                                <!-- Quantity -->
                                <div>
                                    <label for="quantity" class="block text-sm font-medium text-gray-700">
                                        Quantity <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="quantity"
                                        v-model.number="form.quantity"
                                        type="number"
                                        min="1"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <p v-if="form.errors.quantity" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.quantity }}
                                    </p>
                                </div>

                                <!-- Annealing Date -->
                                <div>
                                    <label for="annealing_date" class="block text-sm font-medium text-gray-700">
                                        Annealing Date <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="annealing_date"
                                        v-model="form.annealing_date"
                                        type="date"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <p v-if="form.errors.annealing_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.annealing_date }}
                                    </p>
                                </div>

                                <!-- Machine Number -->
                                <div>
                                    <label for="machine_number" class="block text-sm font-medium text-gray-700">
                                        Machine Number <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="machine_number"
                                        v-model="form.machine_number"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <p v-if="form.errors.machine_number" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.machine_number }}
                                    </p>
                                </div>

                                <!-- Process Settings -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:col-span-2">
                                    <!-- Temperature Setting -->
                                    <div>
                                        <label for="temperature_setting" class="block text-sm font-medium text-gray-700">
                                            Temperature Setting (°C)
                                        </label>
                                        <input
                                            id="temperature_setting"
                                            v-model.number="form.temperature_setting"
                                            type="number"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="e.g., 850.00"
                                        />
                                        <p v-if="form.errors.temperature_setting" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.temperature_setting }}
                                        </p>
                                    </div>

                                    <!-- Annealing Time -->
                                    <div>
                                        <label for="annealing_time" class="block text-sm font-medium text-gray-700">
                                            Annealing Time
                                        </label>
                                        <input
                                            id="annealing_time"
                                            v-model="form.annealing_time"
                                            type="time"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <p v-if="form.errors.annealing_time" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.annealing_time }}
                                        </p>
                                    </div>

                                    <!-- Damper Setting -->
                                    <div>
                                        <label for="damper_setting" class="block text-sm font-medium text-gray-700">
                                            Damper Setting
                                        </label>
                                        <input
                                            id="damper_setting"
                                            v-model="form.damper_setting"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            placeholder="e.g., 50%"
                                        />
                                        <p v-if="form.errors.damper_setting" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.damper_setting }}
                                        </p>
                                    </div>

                                    <!-- Time In -->
                                    <div>
                                        <label for="time_in" class="block text-sm font-medium text-gray-700">
                                            Time In
                                        </label>
                                        <input
                                            id="time_in"
                                            v-model="form.time_in"
                                            type="time"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                        <p v-if="form.errors.time_in" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.time_in }}
                                        </p>
                                    </div>

                                    <!-- Time Out -->
                                    <div class="md:col-span-2">
                                        <label for="time_out" class="block text-sm font-medium text-gray-700">
                                            Time Out
                                        </label>
                                        <input
                                            id="time_out"
                                            v-model="form.time_out"
                                            type="time"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 md:w-1/2"
                                        />
                                        <p v-if="form.errors.time_out" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.time_out }}
                                        </p>
                                    </div>
                                </div>

                                <!-- PIC -->
                                <div>
                                    <label for="pic_id" class="block text-sm font-medium text-gray-700">
                                        Person In Charge <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="pic_id"
                                        v-model="form.pic_id"
                                        type="text"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Enter name of person in charge"
                                    />
                                    <p v-if="form.errors.pic_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.pic_id }}
                                    </p>
                                </div>

                                <!-- Checked By -->
                                <div>
                                    <label for="checked_by_id" class="block text-sm font-medium text-gray-700">
                                        Checked By
                                    </label>
                                    <input
                                        id="checked_by_id"
                                        v-model="form.checked_by_id"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Enter name of person who checked"
                                    />
                                    <p v-if="form.errors.checked_by_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.checked_by_id }}
                                    </p>
                                </div>

                                <!-- Verified By -->
                                <div>
                                    <label for="verified_by_id" class="block text-sm font-medium text-gray-700">
                                        Verified By
                                    </label>
                                    <input
                                        id="verified_by_id"
                                        v-model="form.verified_by_id"
                                        type="text"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        placeholder="Enter name of person who verified"
                                    />
                                    <p v-if="form.errors.verified_by_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.verified_by_id }}
                                    </p>
                                </div>
                            </div>

                            <!-- Remarks -->
                            <div class="mt-6">
                                <label for="remarks" class="block text-sm font-medium text-gray-700">
                                    Remarks
                                </label>
                                <textarea
                                    id="remarks"
                                    v-model="form.remarks"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                                <p v-if="form.errors.remarks" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.remarks }}
                                </p>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-end space-x-3 pt-6">
                                <a
                                    :href="route('annealing-checks.index')"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    Cancel
                                </a>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
                                    <span v-if="form.processing">
                                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Processing...
                                    </span>
                                    <span v-else>
                                        Create Annealing Check
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
