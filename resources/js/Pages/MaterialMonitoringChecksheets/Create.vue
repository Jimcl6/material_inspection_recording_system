<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    materialTypes: {
        type: Object,
        default: () => ({})
    }
});

// Debug: Log props to verify subLotTitles
console.log('Create.vue props:', props);

const form = useForm({
    material_type: '',
    date: '',
    item_block_code: '',
    letter_code: '',
    main_lot_number: '',
    sub_lot_numbers: {},
    produced_qty: 0,
    operator: '',
    job_number: ''
});

const subLotFields = ref([]);
const subLotValues = ref({});

const fetchSubLotFields = async (materialType) => {
    if (!materialType) {
        subLotFields.value = [];
        subLotValues.value = {};
        form.sub_lot_numbers = {};
        return;
    }
    
    try {
        const response = await fetch(`/api/material-types/${encodeURIComponent(materialType)}/sub-lot-fields`);
        const fields = await response.json();
        
        subLotFields.value = fields;
        
        // Initialize sub-lot values object
        const initialValues = {};
        fields.forEach(field => {
            initialValues[field.toLowerCase().replace(/\s+/g, '_')] = '';
        });
        subLotValues.value = initialValues;
        form.sub_lot_numbers = initialValues;
        
    } catch (error) {
        console.error('Failed to fetch sub lot fields:', error);
        subLotFields.value = [];
        subLotValues.value = {};
        form.sub_lot_numbers = {};
    }
};

const onMaterialTypeChange = () => {
    fetchSubLotFields(form.material_type);
};

const materialTypeOptions = Object.entries(props.materialTypes).map(([key, value]) => ({
    value: key,
    label: value,
}));

const updateSubLotValue = (field, value) => {
    const key = field.toLowerCase().replace(/\s+/g, '_');
    subLotValues.value[key] = value;
    form.sub_lot_numbers[key] = value;
};

const submit = () => {
    console.log('Submitting form:', form.data());
    
    // Remove empty sub-lots before submitting
    const cleanedSubLots = {};
    Object.keys(form.sub_lot_numbers).forEach(key => {
        if (form.sub_lot_numbers[key] && form.sub_lot_numbers[key].trim()) {
            cleanedSubLots[key] = form.sub_lot_numbers[key].trim();
        }
    });
    
    // If no sub-lots, set to null
    if (Object.keys(cleanedSubLots).length === 0) {
        form.sub_lot_numbers = null;
    } else {
        form.sub_lot_numbers = cleanedSubLots;
    }
    
    console.log('Final form data:', form.data());
    
    // Submit using Inertia's form
    form.post(route('material-monitoring-checksheets.store'), {
        onSuccess: () => {
            console.log('Form submitted successfully');
        },
        onError: (errors) => {
            console.error('Form submission errors:', errors);
        }
    });
};
</script>

<template>
    <Head title="Create Material Monitoring Checksheet" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create Material Monitoring Checksheet
                </h2>
                <Link
                    :href="route('material-monitoring-checksheets.index')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Back to List
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" method="POST" :action="route('material-monitoring-checksheets.store')">
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-6">Material Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                <!-- Material Type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Material Type</label>
                                    <select
                                        v-model="form.material_type"
                                        @change="onMaterialTypeChange"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        required
                                    >
                                        <option value="">Select Material Type</option>
                                        <option
                                            v-for="type in materialTypeOptions"
                                            :key="type.value"
                                            :value="type.value"
                                        >
                                            {{ type.label }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Date -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                                    <input
                                        v-model="form.date"
                                        type="date"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        required
                                    />
                                </div>

                                <!-- Item Block Code -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Block Code</label>
                                    <input
                                        
                                        v-model="form.item_block_code"
                                        type="text"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        placeholder="e.g., ABC-123"
                                        required
                                    />
                                </div>

                                <!-- Letter Code -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Letter Code (Auto-generated)</label>
                                    <input
                                        v-model="form.letter_code"
                                        type="text"
                                        maxlength="1"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        placeholder="A, B, C..."
                                        readonly
                                    />
                                    <p class="mt-1 text-xs text-gray-500">Letter codes are automatically generated based on item block code and date</p>
                                </div>

                                <!-- Main Lot Number -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Main Lot Number</label>
                                    <input
                                        v-model="form.main_lot_number"
                                        type="text"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        placeholder="e.g., P23-001"
                                    />
                                </div>

                                <!-- Produced Quantity -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Produced Quantity</label>
                                    <input
                                        v-model.number="form.produced_qty"
                                        type="number"
                                        min="0"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        required
                                    />
                                </div>

                                <!-- Operator -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Operator</label>
                                    <input
                                        v-model="form.operator"
                                        type="text"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        placeholder="Operator name"
                                    />
                                </div>

                                <!-- Job Number -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Number</label>
                                    <input
                                        v-model="form.job_number"
                                        type="text"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        placeholder="Job number"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Sub Lot Numbers -->
                        <div class="p-6 bg-white border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Sub Lot Numbers</h3>
                            
                            <div v-if="subLotFields.length > 0" class="space-y-4">
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                                    <div v-for="field in subLotFields" :key="field">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            {{ field }}
                                        </label>
                                        <input
                                            :value="subLotValues[field.toLowerCase().replace(/\s+/g, '_')]"
                                            @input="updateSubLotValue(field, $event.target.value)"
                                            type="text"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            :placeholder="'Enter ' + field.toLowerCase()"
                                        />
                                    </div>
                                </div>
                            </div>
                            
                            <div v-else-if="form.material_type" class="text-gray-500 text-sm">
                                No sub-lot fields defined for this material type.
                            </div>
                            
                            <div v-else class="text-gray-500 text-sm">
                                Please select a material type to see sub-lot fields.
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="p-6 bg-gray-50 border-t border-gray-200">
                            <div class="flex justify-end space-x-3">
                                <Link
                                    :href="route('material-monitoring-checksheets.index')"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    Create Material Part
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
