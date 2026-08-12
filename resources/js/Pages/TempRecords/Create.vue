<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import Card from '@/Components/Card.vue'
import NumericKeypadField from '@/Components/NumericKeypadField.vue'
import TabletTextKeyboardField from '@/Components/TabletTextKeyboardField.vue'
import ModelTypeRadioGroup from '@/Components/ModelTypeRadioGroup.vue'
import SectionTitle from '@/Components/SectionTitle.vue'
import TabletFormStepper from '@/Components/Tablet/TabletFormStepper.vue'
import { useTabletMode } from '@/Composables/useTabletMode'
import { ref } from 'vue'

const props = defineProps({
    equipmentTypes: {
        type: Array,
        default: () => ['Soldering Iron', 'Soldering Pot'],
    },
    users: {
        type: Array,
        default: () => [],
    }
})

const now = new Date()
const currentTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`
const isMorning = now.getHours() < 12

const form = useForm({
    date: new Date().toISOString().split('T')[0],
    model_series: '',
    solder_model: '',
    line_assigned: '',
    control_no: '',
    equipment_type: '',
    process_assigned: '',
    person_in_charge: '',
    time_am: isMorning ? currentTime : '',
    temp_am: '',
    time_pm: isMorning ? '' : currentTime,
    temp_pm: '',
    col_remarks: '',
    checked_by: '',
})
const { isTabletMode } = useTabletMode()
const currentStep = ref(0)
const tabletSteps = ['Basic information', 'Equipment', 'Readings', 'Review and save']

const submit = () => {
    form.post(route('temp-records.store'), {
        onSuccess: () => {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    type: 'success',
                    message: 'Temperature record created successfully!'
                }
            }))
        },
        onError: () => {
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    type: 'error',
                    message: 'Failed to create temperature record. Please check the form for errors.'
                }
            }))
        }
    })
}
</script>

<template>
    <AppLayout title="Create Temperature Record">
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create Temperature Record
                </h2>
                <Link :href="route('temp-records.index')" class="text-sm text-gray-600 hover:text-gray-900">
                    &larr; Back to Records
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Card class="overflow-hidden">
                    <form @submit.prevent="submit" class="p-6">
                        <TabletFormStepper v-if="isTabletMode" v-model="currentStep" :steps="tabletSteps" />
                        <!-- Basic Information Card -->
                        <Card v-show="!isTabletMode || currentStep === 0" class="mb-6">
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
                                    <TabletTextKeyboardField
                                        v-if="isTabletMode"
                                        id="model_series"
                                        v-model="form.model_series"
                                        label="Model Series *"
                                        dialog-title="Model Series"
                                        placeholder="Tap to enter model"
                                        :error="form.errors.model_series"
                                    />
                                    <template v-else>
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
                                    </template>
                                </div>

                                <ModelTypeRadioGroup
                                    id="solder_model"
                                    v-model="form.solder_model"
                                    label="Solder Model"
                                    :options="['Pot', 'Iron']"
                                    :error="form.errors.solder_model"
                                    required
                                />

                                <div>
                                    <TabletTextKeyboardField
                                        v-if="isTabletMode"
                                        id="line_assigned"
                                        v-model="form.line_assigned"
                                        label="Line Assigned"
                                        dialog-title="Line Assigned"
                                        placeholder="Tap to enter line"
                                        :error="form.errors.line_assigned"
                                    />
                                    <template v-else>
                                        <InputLabel for="line_assigned" value="Line Assigned" />
                                        <TextInput
                                            id="line_assigned"
                                            type="text"
                                            v-model="form.line_assigned"
                                            class="mt-1 block w-full"
                                            :class="{ 'border-red-500': form.errors.line_assigned }"
                                        />
                                        <InputError :message="form.errors.line_assigned" class="mt-2" />
                                    </template>
                                </div>
                            </div>
                        </Card>

                        <!-- Equipment Information Card -->
                        <Card v-show="!isTabletMode || currentStep === 1" class="mb-6">
                            <template #header>
                                <SectionTitle>Equipment Information</SectionTitle>
                            </template>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <TabletTextKeyboardField
                                        v-if="isTabletMode"
                                        id="control_no"
                                        v-model="form.control_no"
                                        label="Control Number"
                                        dialog-title="Control Number"
                                        placeholder="Tap to enter control"
                                        :error="form.errors.control_no"
                                    />
                                    <template v-else>
                                        <InputLabel for="control_no" value="Control Number" />
                                        <TextInput
                                            id="control_no"
                                            type="text"
                                            v-model="form.control_no"
                                            class="mt-1 block w-full"
                                            :class="{ 'border-red-500': form.errors.control_no }"
                                        />
                                        <InputError :message="form.errors.control_no" class="mt-2" />
                                    </template>
                                </div>

                                <div>
                                    <InputLabel for="equipment_type" value="Equipment Type *" />
                                    <select
                                        id="equipment_type"
                                        v-model="form.equipment_type"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        :class="{ 'border-red-500': form.errors.equipment_type }"
                                        required
                                    >
                                        <option value="" disabled>Select Equipment Type</option>
                                        <option value="Soldering Iron">Soldering Iron</option>
                                        <option value="Soldering Pot">Soldering Pot</option>
                                    </select>
                                    <InputError :message="form.errors.equipment_type" class="mt-2" />
                                </div>

                                <div>
                                    <TabletTextKeyboardField
                                        v-if="isTabletMode"
                                        id="process_assigned"
                                        v-model="form.process_assigned"
                                        label="Process Assigned"
                                        dialog-title="Process Assigned"
                                        placeholder="Tap to enter process"
                                        :error="form.errors.process_assigned"
                                    />
                                    <template v-else>
                                        <InputLabel for="process_assigned" value="Process Assigned" />
                                        <TextInput
                                            id="process_assigned"
                                            type="text"
                                            v-model="form.process_assigned"
                                            class="mt-1 block w-full"
                                            :class="{ 'border-red-500': form.errors.process_assigned }"
                                        />
                                        <InputError :message="form.errors.process_assigned" class="mt-2" />
                                    </template>
                                </div>

                                <div>
                                    <TabletTextKeyboardField
                                        v-if="isTabletMode"
                                        id="person_in_charge"
                                        v-model="form.person_in_charge"
                                        label="Person In Charge"
                                        dialog-title="Person In Charge"
                                        placeholder="Tap to enter PIC"
                                        :error="form.errors.person_in_charge"
                                    />
                                    <template v-else>
                                        <InputLabel for="person_in_charge" value="Person In Charge" />
                                        <TextInput
                                            id="person_in_charge"
                                            type="text"
                                            v-model="form.person_in_charge"
                                            class="mt-1 block w-full"
                                            :class="{ 'border-red-500': form.errors.person_in_charge }"
                                        />
                                        <InputError :message="form.errors.person_in_charge" class="mt-2" />
                                    </template>
                                </div>
                            </div>
                        </Card>

                        <!-- Temperature Readings Card -->
                        <Card v-show="!isTabletMode || currentStep === 2" class="mb-6">
                            <template #header>
                                <SectionTitle>Temperature Readings</SectionTitle>
                            </template>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <!-- Morning Reading -->
                                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
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
                                        <NumericKeypadField
                                            v-if="isTabletMode"
                                            id="temp_am"
                                            v-model="form.temp_am"
                                            label="Temperature (°C)"
                                            dialog-title="Morning (AM) Temperature"
                                            unit="°C"
                                            placeholder="0.0"
                                            :decimal-places="1"
                                            :max-integer-digits="18"
                                            :error="form.errors.temp_am"
                                        />
                                        <div v-else>
                                            <InputLabel for="temp_am" value="Temperature (°C)" />
                                            <TextInput
                                                id="temp_am"
                                                v-model="form.temp_am"
                                                type="number"
                                                min="0"
                                                step="0.1"
                                                placeholder="0.0"
                                                class="mt-1 block w-full"
                                                :class="{ 'border-red-500': form.errors.temp_am }"
                                            />
                                            <InputError :message="form.errors.temp_am" class="mt-2" />
                                        </div>
                                    </div>
                                </div>

                                <!-- Afternoon Reading -->
                                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
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
                                        <NumericKeypadField
                                            v-if="isTabletMode"
                                            id="temp_pm"
                                            v-model="form.temp_pm"
                                            label="Temperature (°C)"
                                            dialog-title="Afternoon (PM) Temperature"
                                            unit="°C"
                                            placeholder="0.0"
                                            :decimal-places="1"
                                            :max-integer-digits="18"
                                            :error="form.errors.temp_pm"
                                        />
                                        <div v-else>
                                            <InputLabel for="temp_pm" value="Temperature (°C)" />
                                            <TextInput
                                                id="temp_pm"
                                                v-model="form.temp_pm"
                                                type="number"
                                                min="0"
                                                step="0.1"
                                                placeholder="0.0"
                                                class="mt-1 block w-full"
                                                :class="{ 'border-red-500': form.errors.temp_pm }"
                                            />
                                            <InputError :message="form.errors.temp_pm" class="mt-2" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Card>

                        <!-- Remarks & Checked By Card -->
                        <Card v-show="!isTabletMode || currentStep === 3" card-class="mb-6">
                            <template #header>
                                <SectionTitle>Additional Information</SectionTitle>
                            </template>
                            <div class="grid grid-cols-1 gap-6">
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
                                    <TabletTextKeyboardField
                                        v-if="isTabletMode"
                                        id="checked_by"
                                        v-model="form.checked_by"
                                        label="Checked By"
                                        dialog-title="Checked By"
                                        placeholder="Tap to enter checker"
                                        :error="form.errors.checked_by"
                                    />
                                    <template v-else>
                                        <InputLabel for="checked_by" value="Checked By" />
                                        <TextInput
                                            id="checked_by"
                                            type="text"
                                            v-model="form.checked_by"
                                            class="mt-1 block w-full"
                                            :class="{ 'border-red-500': form.errors.checked_by }"
                                        />
                                        <InputError :message="form.errors.checked_by" class="mt-2" />
                                    </template>
                                </div>
                            </div>
                        </Card>

                        <!-- Form Actions -->
                        <div v-show="!isTabletMode || currentStep === 3" class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                            <Link 
                                :href="route('temp-records.index')" 
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
                                <span v-else>Save Temperature Record</span>
                            </PrimaryButton>
                        </div>
                    </form>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
