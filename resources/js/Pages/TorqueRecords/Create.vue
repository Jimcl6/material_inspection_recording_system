<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Card from '@/Components/Card.vue';
import SectionTitle from '@/Components/SectionTitle.vue';

const form = useForm({
    date: new Date().toISOString().split('T')[0],
    model_series: '',
    driver_model: '',
    line_assigned: '',
    control_no: '',
    screw_type:'',
    driver_type: '',
    process_assigned: '',
    person_in_charge: '',
    time_am: '',
    torque_am: '',
    time_pm: '',
    torque_pm: '',
    col_remarks: '',
    checked_by: '',
});

const submit = () => {
    form.post(route('torque-records.store'), {
        onSuccess: () => {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    type: 'success',
                    message: 'Torque record created successfully!'
                }
            }));
        },
        onError: () => {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    type: 'error',
                    message: 'Failed to create torque record. Please check the form for errors.'
                }
            }));
        }
    });
};
</script>

<template>
    <AppLayout title="Create Torque Record">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create Torque Record
                </h2>
                <Link :href="route('torque-records.index')" class="text-sm text-gray-600 hover:text-gray-900">
                    &larr; Back to Records
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card class="overflow-hidden">
                    <form @submit.prevent="submit" class="p-6">
                        <!-- Basic Information Card -->
                        <Card class="mb-6">
                            <template #header>
                                <SectionTitle>Basic Information</SectionTitle>
                            </template>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="date" value="Date *" />
                                    <TextInput
                                        id="date"
                                        type="date"
                                        v-model="form.date"
                                        class="mt-1 block w-full"
                                        :class="{ 'border-red-500': form.errors.date }"
                                        required
                                    />
                                    <InputError :message="form.errors.date" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="model_series" value="Model Series *" />
                                    <TextInput
                                        id="model_series"
                                        type="text"
                                        v-model="form.model_series"
                                        class="mt-1 block w-full"
                                        :class="{ 'border-red-500': form.errors.model_series }"
                                        required
                                    />
                                    <InputError :message="form.errors.model_series" class="mt-2" />
                                </div>
                            </div>
                        </Card>

                        <!-- Driver Details Card -->
                        <Card class="mb-6">
                            <template #header>
                                <SectionTitle>Driver Details</SectionTitle>
                            </template>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel for="driver_model" value="Driver Model" />
                                    <TextInput
                                        id="driver_model"
                                        type="text"
                                        v-model="form.driver_model"
                                        class="mt-1 block w-full"
                                        :class="{ 'border-red-500': form.errors.driver_model }"
                                    />
                                    <InputError :message="form.errors.driver_model" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="line_assigned" value="Line Assigned" />
                                    <TextInput
                                        id="line_assigned"
                                        type="text"
                                        v-model="form.line_assigned"
                                        class="mt-1 block w-full"
                                        :class="{ 'border-red-500': form.errors.line_assigned }"
                                    />
                                    <InputError :message="form.errors.line_assigned" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="control_no" value="Control Number" />
                                    <TextInput
                                        id="control_no"
                                        type="text"
                                        v-model="form.control_no"
                                        class="mt-1 block w-full"
                                        :class="{ 'border-red-500': form.errors.control_no }"
                                    />
                                    <InputError :message="form.errors.control_no" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="screw_type" value="Screw Type" />
                                    <TextInput
                                        id="screw_type"
                                        type="text"
                                        v-model="form.screw_type"
                                        class="mt-1 block w-full"
                                        :class="{ 'border-red-500': form.errors.screw_type }"
                                    />
                                    <InputError :message="form.errors.screw_type" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="driver_type" value="Driver Type *" />
                                    <select
                                        id="driver_type"
                                        v-model="form.driver_type"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': form.errors.driver_type }"
                                        required
                                    >
                                        <option value="" disabled>Select Driver Type</option>
                                        <option value="Manual">Manual</option>
                                        <option value="Automatic">Automatic</option>
                                    </select>
                                    <InputError :message="form.errors.driver_type" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="process_assigned" value="Process Assigned" />
                                    <TextInput
                                        id="process_assigned"
                                        type="text"
                                        v-model="form.process_assigned"
                                        class="mt-1 block w-full"
                                        :class="{ 'border-red-500': form.errors.process_assigned }"
                                    />
                                    <InputError :message="form.errors.process_assigned" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="person_in_charge" value="Person In Charge" />
                                    <TextInput
                                        id="person_in_charge"
                                        type="text"
                                        v-model="form.person_in_charge"
                                        class="mt-1 block w-full"
                                        :class="{ 'border-red-500': form.errors.person_in_charge }"
                                    />
                                    <InputError :message="form.errors.person_in_charge" class="mt-2" />
                                </div>
                            </div>
                        </Card>

                        <!-- Torque Readings Card -->
                        <Card class="mb-6">
                            <template #header>
                                <SectionTitle>Torque Readings</SectionTitle>
                            </template>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Morning Reading -->
                                <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                                    <h4 class="text-lg font-medium text-gray-900 mb-4">Morning (AM)</h4>
                                    <div class="space-y-4">
                                        <div>
                                            <InputLabel for="time_am" value="Time" />
                                            <TextInput
                                                id="time_am"
                                                type="time"
                                                v-model="form.time_am"
                                                class="mt-1 block w-full"
                                                :class="{ 'border-red-500': form.errors.time_am }"
                                            />
                                            <InputError :message="form.errors.time_am" class="mt-2" />
                                        </div>
                                        <div>
                                            <InputLabel for="torque_am" value="Torque" />
                                            <div class="relative rounded-md shadow-sm">
                                                <TextInput
                                                    id="torque_am"
                                                    type="number"
                                                    step="0.01"
                                                    v-model="form.torque_am"
                                                    class="block w-full pr-10"
                                                    :class="{ 'border-red-500': form.errors.torque_am }"
                                                    placeholder="0.00"
                                                />
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">N·m</span>
                                                </div>
                                            </div>
                                            <InputError :message="form.errors.torque_am" class="mt-2" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Afternoon Reading -->
                                <div class="border border-gray-200 rounded-lg p-6 bg-gray-50">
                                    <h4 class="text-lg font-medium text-gray-900 mb-4">Afternoon (PM)</h4>
                                    <div class="space-y-4">
                                        <div>
                                            <InputLabel for="time_pm" value="Time" />
                                            <TextInput
                                                id="time_pm"
                                                type="time"
                                                v-model="form.time_pm"
                                                class="mt-1 block w-full"
                                                :class="{ 'border-red-500': form.errors.time_pm }"
                                            />
                                            <InputError :message="form.errors.time_pm" class="mt-2" />
                                        </div>
                                        <div>
                                            <InputLabel for="torque_pm" value="Torque" />
                                            <div class="relative rounded-md shadow-sm">
                                                <TextInput
                                                    id="torque_pm"
                                                    type="number"
                                                    step="0.01"
                                                    v-model="form.torque_pm"
                                                    class="block w-full pr-10"
                                                    :class="{ 'border-red-500': form.errors.torque_pm }"
                                                    placeholder="0.00"
                                                />
                                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                    <span class="text-gray-500 sm:text-sm">N·m</span>
                                                </div>
                                            </div>
                                            <InputError :message="form.errors.torque_pm" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Card>

                        <!-- Additional Information Card -->
                        <Card class="mb-6">
                            <template #header>
                                <SectionTitle>Additional Information</SectionTitle>
                            </template>
                            <div class="space-y-4">
                                <div>
                                    <InputLabel for="col_remarks" value="Remarks" />
                                    <textarea
                                        id="col_remarks"
                                        v-model="form.col_remarks"
                                        rows="3"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': form.errors.col_remarks }"
                                        placeholder="Any additional notes or comments..."
                                    ></textarea>
                                    <InputError :message="form.errors.col_remarks" class="mt-2" />
                                </div>

                                <div class="w-full md:w-1/2">
                                    <InputLabel for="checked_by" value="Checked By" />
                                    <TextInput
                                        id="checked_by"
                                        type="text"
                                        v-model="form.checked_by"
                                        class="mt-1 block w-full"
                                        :class="{ 'border-red-500': form.errors.checked_by }"
                                    />
                                    <InputError :message="form.errors.checked_by" class="mt-2" />
                                </div>
                            </div>
                        </Card>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <Link 
                                :href="route('torque-records.index')" 
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton
                                type="submit"
                                :class="{ 'opacity-25': form.processing }"
                                :disabled="form.processing"
                            >
                                <span v-if="form.processing">Saving...</span>
                                <span v-else>Save Torque Record</span>
                            </PrimaryButton>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>