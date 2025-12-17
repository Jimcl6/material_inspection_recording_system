<script setup>
import { ref, computed, onMounted } from 'vue';
import { useForm, Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import SelectInput from '@/Components/SelectInput.vue';
import { PlusIcon, XMarkIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    record: {
        type: Object,
        default: () => ({
            date: new Date().toISOString().split('T')[0],
            shift: '',
            time: new Date().toTimeString().substring(0, 5),
            po: '',
            supplier: '',
            material_name: '',
            material_type: '',
            material_condition: '',
            temp_reading: '',
            temp_setting: '',
            corrected_by: '',
            remarks: '',
        }),
    },
    editMode: {
        type: Boolean,
        default: false,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    date: props.record.date || new Date().toISOString().split('T')[0],
    shift: props.record.shift || '',
    time: props.record.time || new Date().toTimeString().substring(0, 5),
    po: props.record.po || '',
    supplier: props.record.supplier || '',
    material_name: props.record.material_name || '',
    material_type: props.record.material_type || '',
    material_condition: props.record.material_condition || '',
    temp_reading: props.record.temp_reading || '',
    temp_setting: props.record.temp_setting || '',
    corrected_by: props.record.corrected_by || '',
    remarks: props.record.remarks || '',
});

const submit = () => {
    if (props.editMode) {
        form.put(route('temp-records.update', props.record.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Handle success
            },
        });
    } else {
        form.post(route('temp-records.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                // Handle success
            },
        });
    }
};

// Options for select inputs
const shifts = [
    { value: 'A', label: 'Shift A' },
    { value: 'B', label: 'Shift B' },
    { value: 'C', label: 'Shift C' },
];

const materialTypes = [
    { value: 'Type 1', label: 'Type 1' },
    { value: 'Type 2', label: 'Type 2' },
    { value: 'Type 3', label: 'Type 3' },
];

const materialConditions = [
    { value: 'New', label: 'New' },
    { value: 'Used', label: 'Used' },
    { value: 'Reconditioned', label: 'Reconditioned' },
];
</script>

<template>
    <AppLayout title="Temperature Records">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ editMode ? 'Edit' : 'Create' }} Temperature Record
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <!-- Date -->
                                <div>
                                    <InputLabel for="date" value="Date" class="mb-1" />
                                    <TextInput
                                        id="date"
                                        type="date"
                                        class="mt-1 block w-full"
                                        v-model="form.date"
                                        :error="errors.date"
                                    />
                                    <InputError :message="errors.date" class="mt-2" />
                                </div>

                                <!-- Shift -->
                                <div>
                                    <InputLabel for="shift" value="Shift" class="mb-1" />
                                    <SelectInput
                                        id="shift"
                                        class="mt-1 block w-full"
                                        v-model="form.shift"
                                        :options="shifts"
                                        :error="errors.shift"
                                    />
                                    <InputError :message="errors.shift" class="mt-2" />
                                </div>

                                <!-- Time -->
                                <div>
                                    <InputLabel for="time" value="Time" class="mb-1" />
                                    <TextInput
                                        id="time"
                                        type="time"
                                        class="mt-1 block w-full"
                                        v-model="form.time"
                                        :error="errors.time"
                                    />
                                    <InputError :message="errors.time" class="mt-2" />
                                </div>

                                <!-- PO Number -->
                                <div>
                                    <InputLabel for="po" value="PO Number" class="mb-1" />
                                    <TextInput
                                        id="po"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.po"
                                        :error="errors.po"
                                    />
                                    <InputError :message="errors.po" class="mt-2" />
                                </div>

                                <!-- Supplier -->
                                <div>
                                    <InputLabel for="supplier" value="Supplier" class="mb-1" />
                                    <TextInput
                                        id="supplier"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.supplier"
                                        :error="errors.supplier"
                                    />
                                    <InputError :message="errors.supplier" class="mt-2" />
                                </div>

                                <!-- Material Name -->
                                <div>
                                    <InputLabel for="material_name" value="Material Name" class="mb-1" />
                                    <TextInput
                                        id="material_name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.material_name"
                                        :error="errors.material_name"
                                    />
                                    <InputError :message="errors.material_name" class="mt-2" />
                                </div>

                                <!-- Material Type -->
                                <div>
                                    <InputLabel for="material_type" value="Material Type" class="mb-1" />
                                    <SelectInput
                                        id="material_type"
                                        class="mt-1 block w-full"
                                        v-model="form.material_type"
                                        :options="materialTypes"
                                        :error="errors.material_type"
                                    />
                                    <InputError :message="errors.material_type" class="mt-2" />
                                </div>

                                <!-- Material Condition -->
                                <div>
                                    <InputLabel for="material_condition" value="Material Condition" class="mb-1" />
                                    <SelectInput
                                        id="material_condition"
                                        class="mt-1 block w-full"
                                        v-model="form.material_condition"
                                        :options="materialConditions"
                                        :error="errors.material_condition"
                                    />
                                    <InputError :message="errors.material_condition" class="mt-2" />
                                </div>

                                <!-- Temperature Reading -->
                                <div>
                                    <InputLabel for="temp_reading" value="Temperature Reading (°C)" class="mb-1" />
                                    <TextInput
                                        id="temp_reading"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full"
                                        v-model="form.temp_reading"
                                        :error="errors.temp_reading"
                                    />
                                    <InputError :message="errors.temp_reading" class="mt-2" />
                                </div>

                                <!-- Temperature Setting -->
                                <div>
                                    <InputLabel for="temp_setting" value="Temperature Setting (°C)" class="mb-1" />
                                    <TextInput
                                        id="temp_setting"
                                        type="number"
                                        step="0.01"
                                        class="mt-1 block w-full"
                                        v-model="form.temp_setting"
                                        :error="errors.temp_setting"
                                    />
                                    <InputError :message="errors.temp_setting" class="mt-2" />
                                </div>

                                <!-- Corrected By -->
                                <div>
                                    <InputLabel for="corrected_by" value="Corrected By" class="mb-1" />
                                    <TextInput
                                        id="corrected_by"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="form.corrected_by"
                                        :error="errors.corrected_by"
                                    />
                                    <InputError :message="errors.corrected_by" class="mt-2" />
                                </div>
                            </div>

                            <!-- Remarks -->
                            <div>
                                <InputLabel for="remarks" value="Remarks" class="mb-1" />
                                <textarea
                                    id="remarks"
                                    rows="3"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm"
                                    v-model="form.remarks"
                                    :class="{ 'border-red-500': errors.remarks }"
                                ></textarea>
                                <InputError :message="errors.remarks" class="mt-2" />
                            </div>

                            <div class="flex items-center justify-end mt-8 space-x-4">
                                <SecondaryButton
                                    type="button"
                                    @click="$inertia.visit(route('temp-records.index'))"
                                >
                                    Cancel
                                </SecondaryButton>
                                <PrimaryButton :disabled="form.processing">
                                    {{ form.processing ? (editMode ? 'Updating...' : 'Creating...') : (editMode ? 'Update' : 'Create') }} Record
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
