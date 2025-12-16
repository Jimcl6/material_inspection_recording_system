<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { ref, onMounted } from 'vue';

const props = defineProps({
    annealingCheck: {
        type: Object,
        default: () => ({
            id: null,
            item_code: '',
            receiving_date: '',
            supplier_lot_number: '',
            quantity: 1,
            annealing_date: new Date().toISOString().split('T')[0],
            machine_number: '',
            machine_setting: '',
            pic_id: '',
            checked_by_id: '',
            verified_by_id: '',
            remarks: '',
            temperature_readings: [
                { id: null, reading_time: '08:00', temperature: '' },
                { id: null, reading_time: '12:00', temperature: '' },
                { id: null, reading_time: '16:00', temperature: '' },
                { id: null, reading_time: '20:00', temperature: '' },
            ]
        })
    },
    users: {
        type: Array,
        default: () => []
    },
    isEdit: {
        type: Boolean,
        default: false
    }
});

const form = useForm({
    _method: props.isEdit ? 'PUT' : 'POST',
    item_code: props.annealingCheck.item_code || '',
    receiving_date: props.annealingCheck.receiving_date || new Date().toISOString().split('T')[0],
    supplier_lot_number: props.annealingCheck.supplier_lot_number || '',
    quantity: props.annealingCheck.quantity || 1,
    annealing_date: props.annealingCheck.annealing_date || new Date().toISOString().split('T')[0],
    machine_number: props.annealingCheck.machine_number || '',
    machine_setting: props.annealingCheck.machine_setting || '',
    pic_id: props.annealingCheck.pic_id || '',
    checked_by_id: props.annealingCheck.checked_by_id || '',
    verified_by_id: props.annealingCheck.verified_by_id || '',
    remarks: props.annealingCheck.remarks || '',
    temperature_readings: [...(props.annealingCheck.temperature_readings || [
        { id: null, reading_time: '08:00', temperature: '' },
        { id: null, reading_time: '12:00', temperature: '' },
        { id: null, reading_time: '16:00', temperature: '' },
        { id: null, reading_time: '20:00', temperature: '' },
    ])]
});

const addTemperatureReading = () => {
    form.temperature_readings.push({ 
        id: null, 
        reading_time: '00:00', 
        temperature: '' 
    });
};

const removeTemperatureReading = (index) => {
    if (form.temperature_readings.length > 1) {
        form.temperature_readings.splice(index, 1);
    }
};

const submit = () => {
    // Ensure temperature readings are properly formatted
    const formattedReadings = form.temperature_readings.map(reading => ({
        ...reading,
        temperature: parseFloat(reading.temperature) || 0,
        reading_time: reading.reading_time || '00:00'
    }));
    
    form.temperature_readings = formattedReadings;

    if (props.isEdit) {
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
                // Show error message
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        message: 'Failed to update annealing check. Please check the form for errors.'
                    }
                }));
            }
        });
    } else {
        form.post(route('annealing-checks.store'), {
            onSuccess: () => {
                // Show success message
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'success',
                        message: 'Annealing check created successfully!'
                    }
                }));
            },
            onError: (errors) => {
                // Show error message
                window.dispatchEvent(new CustomEvent('toast', {
                    detail: {
                        type: 'error',
                        message: 'Failed to create annealing check. Please check the form for errors.'
                    }
                }));
            }
        });
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Annealing Check' : 'Create Annealing Check'" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ isEdit ? 'Edit' : 'Create' }} Annealing Check
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <!-- Left Column -->
                                <div>
                                    <div class="mb-4">
                                        <InputLabel for="item_code" value="Item Code *" />
                                        <TextInput
                                            id="item_code"
                                            v-model="form.item_code"
                                            type="text"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                        <InputError :message="form.errors.item_code" class="mt-2" />
                                    </div>

                                    <div class="mb-4">
                                        <InputLabel for="receiving_date" value="Receiving Date *" />
                                        <TextInput
                                            id="receiving_date"
                                            v-model="form.receiving_date"
                                            type="date"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                        <InputError :message="form.errors.receiving_date" class="mt-2" />
                                    </div>

                                    <div class="mb-4">
                                        <InputLabel for="supplier_lot_number" value="Supplier Lot #" />
                                        <TextInput
                                            id="supplier_lot_number"
                                            v-model="form.supplier_lot_number"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="form.errors.supplier_lot_number" class="mt-2" />
                                    </div>

                                    <div class="mb-4">
                                        <InputLabel for="quantity" value="Quantity *" />
                                        <TextInput
                                            id="quantity"
                                            v-model="form.quantity"
                                            type="number"
                                            min="1"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                        <InputError :message="form.errors.quantity" class="mt-2" />
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div>
                                    <div class="mb-4">
                                        <InputLabel for="annealing_date" value="Annealing Date *" />
                                        <TextInput
                                            id="annealing_date"
                                            v-model="form.annealing_date"
                                            type="date"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                        <InputError :message="form.errors.annealing_date" class="mt-2" />
                                    </div>

                                    <div class="mb-4">
                                        <InputLabel for="machine_number" value="Machine # *" />
                                        <TextInput
                                            id="machine_number"
                                            v-model="form.machine_number"
                                            type="text"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                        <InputError :message="form.errors.machine_number" class="mt-2" />
                                    </div>

                                    <div class="mb-4">
                                        <InputLabel for="machine_setting" value="Machine Setting" />
                                        <TextInput
                                            id="machine_setting"
                                            v-model="form.machine_setting"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="form.errors.machine_setting" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Personnel Section -->
                            <div class="border-t border-gray-200 pt-6 mb-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Personnel</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <InputLabel for="pic_id" value="PIC *" />
                                        <select
                                            id="pic_id"
                                            v-model="form.pic_id"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                                            required
                                        >
                                            <option value="">Select PIC</option>
                                            <option v-for="user in users" :key="user.id" :value="user.id">
                                                {{ user.name }}
                                            </option>
                                        </select>
                                        <InputError :message="form.errors.pic_id" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="checked_by_id" value="Checked By" />
                                        <select
                                            id="checked_by_id"
                                            v-model="form.checked_by_id"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                                        >
                                            <option value="">Select Checked By</option>
                                            <option v-for="user in users" :key="user.id" :value="user.id">
                                                {{ user.name }}
                                            </option>
                                        </select>
                                        <InputError :message="form.errors.checked_by_id" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="verified_by_id" value="Verified By" />
                                        <select
                                            id="verified_by_id"
                                            v-model="form.verified_by_id"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                                        >
                                            <option value="">Select Verified By</option>
                                            <option v-for="user in users" :key="user.id" :value="user.id">
                                                {{ user.name }}
                                            </option>
                                        </select>
                                        <InputError :message="form.errors.verified_by_id" class="mt-2" />
                                    </div>
                                </div>
                            </div>

                            <!-- Temperature Readings -->
                            <div class="border-t border-gray-200 pt-6 mb-6">
                                <div class="flex justify-between items-center mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Temperature Readings</h3>
                                    <button
                                        type="button"
                                        @click="addTemperatureReading"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    >
                                        Add Reading
                                    </button>
                                </div>

                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Time
                                                </th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Temperature (°C)
                                                </th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Actions
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="(reading, index) in form.temperature_readings" :key="index">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <TextInput
                                                        v-model="reading.reading_time"
                                                        type="time"
                                                        class="block w-full"
                                                        required
                                                    />
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <TextInput
                                                            v-model="reading.temperature"
                                                            type="number"
                                                            step="0.01"
                                                            class="block w-full"
                                                            placeholder="0.00"
                                                            required
                                                        />
                                                        <span class="ml-2 text-gray-500">°C</span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                    <button
                                                        type="button"
                                                        @click="removeTemperatureReading(index)"
                                                        :disabled="form.temperature_readings.length <= 1"
                                                        class="text-red-600 hover:text-red-900 disabled:opacity-50 disabled:cursor-not-allowed"
                                                    >
                                                        Remove
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <InputError :message="form.errors['temperature_readings']" class="mt-2" />
                            </div>

                            <!-- Remarks -->
                            <div class="mb-6">
                                <InputLabel for="remarks" value="Remarks" />
                                <textarea
                                    id="remarks"
                                    v-model="form.remarks"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                                ></textarea>
                                <InputError :message="form.errors.remarks" class="mt-2" />
                            </div>

                            <!-- Form Actions -->
                            <div class="flex items-center justify-end mt-6">
                                <Link
                                    :href="route('annealing-checks.index')"
                                    class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mr-4"
                                >
                                    Cancel
                                </Link>
                                <PrimaryButton
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    {{ isEdit ? 'Update' : 'Create' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
