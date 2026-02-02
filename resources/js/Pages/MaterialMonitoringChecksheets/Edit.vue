<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteButton from '@/Components/DeleteButton.vue';

const props = defineProps({
    materialPart: {
        type: [Object, Array],
        required: true
    },
    materialTypes: {
        type: Object,
        default: () => ({})
    },
    subLotTitles: {
        type: Array,
        default: () => []
    }
});

// Debug: Log props to verify subLotTitles
console.log('Edit.vue props:', props);
console.log('Initial subLotTitles:', props.subLotTitles);

// Debug: Log what we're receiving
console.log('Props received:', props);

// Handle case where materialPart might be an array or empty
const materialPartData = Array.isArray(props.materialPart) ? {} : props.materialPart;

const form = useForm({
    material_type: materialPartData.material_type || '',
    date: materialPartData.date ? new Date(materialPartData.date).toISOString().split('T')[0] : '',
    item_block_code: materialPartData.item_block_code || '',
    letter_code: materialPartData.letter_code || '',
    main_lot_number: materialPartData.main_lot_number || '',
    sub_lot_numbers: materialPartData.sub_lot_numbers || { sub_lots: [] },
    produced_qty: materialPartData.produced_qty || 0,
    operator: materialPartData.operator || '',
    job_number: materialPartData.job_number || ''
});

const newSubLot = ref('');
const subLotTitles = ref(props.subLotTitles);

// Debug: Log subLotTitles changes
console.log('Edit.vue subLotTitles initialized:', subLotTitles.value);

const fetchSubLotTitles = async (materialType) => {
    console.log('fetchSubLotTitles called with:', materialType);
    if (!materialType) {
        subLotTitles.value = [];
        console.log('No material type, cleared subLotTitles');
        return;
    }
    try {
        const response = await fetch(`/api/material-types/${encodeURIComponent(materialType)}/sub-lot-titles`);
        console.log('API response status:', response.status);
        const data = await response.json();
        console.log('API response data:', data);
        subLotTitles.value = data.map(item => item.title);
        console.log('Updated subLotTitles.value:', subLotTitles.value);
    } catch (error) {
        console.error('Failed to fetch sub lot titles:', error);
        subLotTitles.value = [];
    }
};

const onMaterialTypeChange = () => {
    fetchSubLotTitles(form.material_type);
};

const materialTypeOptions = Object.entries(props.materialTypes).map(([key, value]) => ({
    value: key,
    label: value,
}));

// Debug: Log the form data and material part to see what's being loaded
console.log('Material Part Data:', props.materialPart);
console.log('Form Data:', form.data());
console.log('Material Types:', props.materialTypes);

const addSubLot = () => {
    if (newSubLot.value.trim()) {
        form.sub_lot_numbers.sub_lots.push(newSubLot.value.trim());
        newSubLot.value = '';
    }
};

const removeSubLot = (index) => {
    form.sub_lot_numbers.sub_lots.splice(index, 1);
};

const submit = () => {
    // Remove empty sub-lots before submitting
    form.sub_lot_numbers.sub_lots = form.sub_lot_numbers.sub_lots.filter(lot => lot.trim());
    
    // If no sub-lots, set to null
    if (form.sub_lot_numbers.sub_lots.length === 0) {
        form.sub_lot_numbers = null;
    }
    
    // Submit using Inertia's form
    form.put(route('material-monitoring-checksheets.update', props.materialPart.id), {
        onSuccess: () => {
            console.log('Form updated successfully');
        },
        onError: (errors) => {
            console.error('Form update errors:', errors);
        }
    });
};
</script>

<template>
    <Head title="Edit Material Monitoring Checksheet" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Edit Material Monitoring Checksheet
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
                    <form @submit.prevent="submit">
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
                                        disabled
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
                                        required
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
                            
                            <div class="space-y-2 mb-4">
                                <div v-for="(subLot, index) in form.sub_lot_numbers.sub_lots" :key="index" class="flex items-center space-x-2">
                                    <div v-if="subLotTitles[index]" class="w-48 text-sm font-medium text-gray-700">
                                        {{ subLotTitles[index] }}
                                    </div>
                                    <input
                                        v-model="form.sub_lot_numbers.sub_lots[index]"
                                        type="text"
                                        class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        placeholder="Enter sub lot number"
                                    />
                                    <button
                                        type="button"
                                        @click="removeSubLot(index)"
                                        class="inline-flex items-center px-3 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                <input
                                    v-model="newSubLot"
                                    @keyup.enter="addSubLot"
                                    type="text"
                                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    placeholder="Enter sub lot number and press Add"
                                />
                                <button
                                    type="button"
                                    @click="addSubLot"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    Add Sub Lot
                                </button>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="p-6 bg-gray-50 border-t border-gray-200">
                            <div class="flex justify-end space-x-3">
                                <Link
                                    :href="route('material-monitoring-checksheets.index')"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l-7 7m7 7v4m0 0l-7-7m-7 7v4"></path>
                                    </svg>
                                    Back to List
                                </Link>
                                
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    :disabled="form.processing"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Save Changes
                                </button>
                                
                                <DeleteButton :material-part="props.materialPart" />
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
