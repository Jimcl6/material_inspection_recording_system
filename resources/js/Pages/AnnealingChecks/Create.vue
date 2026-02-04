<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { route } from 'ziggy-js';
import AppLayout from '@/Layouts/AppLayout.vue';

interface User {
    id: number;
    name: string;
}

interface TemperatureReading {
    reading_time: string;
    temperature: string | number;
}

interface FormData {
    item_code: string;
    receiving_date: string;
    supplier_lot_number: string;
    quantity: number;
    annealing_date: string;
    machine_number: string;
    machine_setting: string;
    pic_id: string | number;
    checked_by_id: string | number;
    verified_by_id: string | number;
    remarks: string;
    temperature_readings: TemperatureReading[];
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
    
    // Validate temperature readings
    form.temperature_readings.forEach((reading, index) => {
        if (!reading.temperature) {
            errors[`temperature_readings.${index}.temperature`] = 'Temperature is required';
        } else if (isNaN(Number(reading.temperature))) {
            errors[`temperature_readings.${index}.temperature`] = 'Temperature must be a number';
        }
    });
    
    return errors;
};

const form = useForm({
    item_code: '',
    receiving_date: '',
    supplier_lot_number: '',
    quantity: 1,
    annealing_date: '',
    machine_number: '',
    machine_setting: '',
    pic_id: '',
    checked_by_id: '',
    verified_by_id: '',
    remarks: '',
    temperature_readings: [
        { reading_time: '08:00', temperature: '' },
        { reading_time: '12:00', temperature: '' },
        { reading_time: '16:00', temperature: '' },
    ]
});

const addTemperatureReading = (): void => {
    form.temperature_readings.push({ reading_time: '08:00', temperature: '' });
};

const removeTemperatureReading = (index: number): void => {
    if (form.temperature_readings.length > 1) {
        form.temperature_readings.splice(index, 1);
    }
};

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
        form.clearErrors(field);
    }
};
</script>

<template>
    <AppLayout title="Create Annealing Check">
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

                                <!-- Machine Setting -->
                                <div>
                                    <label for="machine_setting" class="block text-sm font-medium text-gray-700">
                                        Machine Setting
                                    </label>
                                    <input
                                        id="machine_setting"
                                        v-model="form.machine_setting"
                                        type="text"
                                        :class="{
                                            'border-red-500': form.errors.machine_setting,
                                            'border-gray-300': !form.errors.machine_setting
                                        }"
                                        class="mt-1 block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        @blur="onBlur('machine_setting')"
                                    />
                                    <p v-if="form.errors.machine_setting" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.machine_setting }}
                                    </p>
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

                            <!-- Temperature Readings -->
                            <div class="mt-8">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Temperature Readings</h3>
                                    <button
                                        type="button"
                                        @click="addTemperatureReading"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        Add Reading
                                    </button>
                                </div>

                                <div v-if="form.temperature_readings.length === 0" class="text-sm text-gray-500">
                                    No temperature readings added yet.
                                </div>

                                <div v-else class="space-y-4">
                                    <div v-for="(reading, index) in form.temperature_readings" :key="index" class="flex items-center space-x-4">
                                        <div class="flex-1">
                                            <label :for="`reading_time_${index}`" class="block text-sm font-medium text-gray-700">
                                                Time
                                            </label>
                                            <input
                                                :id="`reading_time_${index}`"
                                                v-model="reading.reading_time"
                                                type="time"
                                                required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                            <p v-if="form.errors[`temperature_readings.${index}.reading_time`]" class="mt-1 text-sm text-red-600">
                                                {{ form.errors[`temperature_readings.${index}.reading_time`] }}
                                            </p>
                                        </div>
                                        <div class="flex-1">
                                            <label :for="`temperature_${index}`" class="block text-sm font-medium text-gray-700">
                                                Temperature (°C)
                                            </label>
                                            <input
                                                :id="`temperature_${index}`"
                                                v-model.number="reading.temperature"
                                                type="number"
                                                step="0.01"
                                                required
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                            <p v-if="form.errors[`temperature_readings.${index}.temperature`]" class="mt-1 text-sm text-red-600">
                                                {{ form.errors[`temperature_readings.${index}.temperature`] }}
                                            </p>
                                        </div>
                                        <button
                                            type="button"
                                            @click="removeTemperatureReading(index)"
                                            class="mt-6 p-2 text-red-600 hover:text-red-800"
                                            title="Remove reading"
                                        >
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
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
