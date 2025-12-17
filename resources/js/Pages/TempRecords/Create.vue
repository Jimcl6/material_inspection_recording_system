<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';

const props = defineProps({
    equipmentTypes: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    date: new Date().toISOString().split('T')[0], // Default to today
    model_series: '',
    solder_model: '',
    line_assigned: '',
    control_no: '',
    equipment_type: '',
    process_assigned: '',
    person_in_charge: '',
    time_am: '08:00',
    temp_am: '',
    time_pm: '13:00',
    temp_pm: '',
    col_remarks: '',
    checked_by: '',
});

const submit = () => {
    form.post(route('temp-records.store'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <AppLayout title="Create Temperature Record">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create New Temperature Record
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Basic Information Section -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Date -->
                                <div>
                                    <InputLabel for="date" value="Date" />
                                    <TextInput
                                        id="date"
                                        v-model="form.date"
                                        type="date"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.date" class="mt-2" />
                                </div>

                                <!-- Model Series -->
                                <div>
                                    <InputLabel for="model_series" value="Model Series" />
                                    <TextInput
                                        id="model_series"
                                        v-model="form.model_series"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.model_series" class="mt-2" />
                                </div>

                                <!-- Equipment Type -->
                                <div>
                                    <InputLabel for="equipment_type" value="Equipment Type" />
                                    <SelectInput
                                        id="equipment_type"
                                        v-model="form.equipment_type"
                                        class="mt-1 block w-full"
                                        required
                                    >
                                        <option value="">Select Equipment Type</option>
                                        <option v-for="type in equipmentTypes" :key="type" :value="type">
                                            {{ type }}
                                        </option>
                                    </SelectInput>
                                    <InputError :message="form.errors.equipment_type" class="mt-2" />
                                </div>

                                <!-- Person in Charge -->
                                <div>
                                    <InputLabel for="person_in_charge" value="Person in Charge" />
                                    <TextInput
                                        id="person_in_charge"
                                        v-model="form.person_in_charge"
                                        type="text"
                                        class="mt-1 block w-full"
                                        required
                                    />
                                    <InputError :message="form.errors.person_in_charge" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Temperature Readings Section -->
                        <div class="border-b border-gray-200 pb-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Temperature Readings</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- AM Reading -->
                                <div class="border rounded-lg p-4">
                                    <h4 class="font-medium text-gray-700 mb-3">AM Reading</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel for="time_am" value="Time" />
                                            <TextInput
                                                id="time_am"
                                                v-model="form.time_am"
                                                type="time"
                                                class="mt-1 block w-full"
                                            />
                                            <InputError :message="form.errors.time_am" class="mt-2" />
                                        </div>
                                        <div>
                                            <InputLabel for="temp_am" value="Temperature (°C)" />
                                            <TextInput
                                                id="temp_am"
                                                v-model="form.temp_am"
                                                type="number"
                                                step="0.1"
                                                class="mt-1 block w-full"
                                            />
                                            <InputError :message="form.errors.temp_am" class="mt-2" />
                                        </div>
                                    </div>
                                </div>

                                <!-- PM Reading -->
                                <div class="border rounded-lg p-4">
                                    <h4 class="font-medium text-gray-700 mb-3">PM Reading</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <InputLabel for="time_pm" value="Time" />
                                            <TextInput
                                                id="time_pm"
                                                v-model="form.time_pm"
                                                type="time"
                                                class="mt-1 block w-full"
                                            />
                                            <InputError :message="form.errors.time_pm" class="mt-2" />
                                        </div>
                                        <div>
                                            <InputLabel for="temp_pm" value="Temperature (°C)" />
                                            <TextInput
                                                id="temp_pm"
                                                v-model="form.temp_pm"
                                                type="number"
                                                step="0.1"
                                                class="mt-1 block w-full"
                                            />
                                            <InputError :message="form.errors.temp_pm" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information Section -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Remarks -->
                                <div class="md:col-span-2">
                                    <InputLabel for="col_remarks" value="Remarks" />
                                    <textarea
                                        id="col_remarks"
                                        v-model="form.col_remarks"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    ></textarea>
                                    <InputError :message="form.errors.col_remarks" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6">
                            <Link :href="route('temp-records.index')" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Cancel
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                Save Record
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>