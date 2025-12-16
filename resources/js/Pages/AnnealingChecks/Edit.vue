<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, onMounted } from 'vue';

const props = defineProps({
    annealingCheck: {
        type: Object,
        required: true
    },
    temperatureReadings: {
        type: Array,
        default: () => []
    },
    users: {
        type: Array,
        default: () => []
    }
});

const form = useForm({
    item_code: props.annealingCheck.item_code || '',
    receiving_date: props.annealingCheck.receiving_date || '',
    supplier_lot_number: props.annealingCheck.supplier_lot_number || '',
    quantity: props.annealingCheck.quantity || 1,
    annealing_date: props.annealingCheck.annealing_date || '',
    machine_number: props.annealingCheck.machine_number || '',
    machine_setting: props.annealingCheck.machine_setting || '',
    pic_id: props.annealingCheck.pic_id || '',
    checked_by_id: props.annealingCheck.checked_by_id || '',
    verified_by_id: props.annealingCheck.verified_by_id || '',
    remarks: props.annealingCheck.remarks || '',
    temperature_readings: props.temperatureReadings.length > 0 
        ? [...props.temperatureReadings] 
        : [
            { reading_time: '08:00', temperature: '' },
            { reading_time: '12:00', temperature: '' },
            { reading_time: '16:00', temperature: '' },
        ]
});

const addTemperatureReading = () => {
    form.temperature_readings.push({ reading_time: '', temperature: '' });
};

const removeTemperatureReading = (index: number) => {
    form.temperature_readings.splice(index, 1);
};

const submit = () => {
    form.put(route('annealing-checks.update', props.annealingCheck.id), {
        onSuccess: () => {
            // Show success message
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    type: 'success',
                    message: 'Annealing check updated successfully!'
                }
            }));
        },
        onError: (errors) => {
            console.error('Error updating check:', errors);
            // Show error message
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    type: 'error',
                    message: 'Failed to update annealing check. Please check the form for errors.'
                }
            }));
        }
    });
};
</script>

<template>
    <Head :title="`Edit Annealing Check #${annealingCheck.id}`" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Annealing Check #{{ annealingCheck.id }}
                </h2>
            </div>
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
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
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
                                    <select
                                        id="pic_id"
                                        v-model="form.pic_id"
                                        required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Select PIC</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.pic_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.pic_id }}
                                    </p>
                                </div>

                                <!-- Checked By -->
                                <div>
                                    <label for="checked_by_id" class="block text-sm font-medium text-gray-700">
                                        Checked By
                                    </label>
                                    <select
                                        id="checked_by_id"
                                        v-model="form.checked_by_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Select Checked By</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.checked_by_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.checked_by_id }}
                                    </p>
                                </div>

                                <!-- Verified By -->
                                <div>
                                    <label for="verified_by_id" class="block text-sm font-medium text-gray-700">
                                        Verified By
                                    </label>
                                    <select
                                        id="verified_by_id"
                                        v-model="form.verified_by_id"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >
                                        <option value="">Select Verified By</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
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
                            <div class="flex justify-between pt-6">
                                <button
                                    type="button"
                                    @click="if (confirm('Are you sure you want to delete this check?')) { $inertia.delete(route('annealing-checks.destroy', annealingCheck.id)) }"
                                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Delete Check
                                </button>
                                <div class="space-x-3">
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
                                            Updating...
                                        </span>
                                        <span v-else>
                                            Update Annealing Check
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
