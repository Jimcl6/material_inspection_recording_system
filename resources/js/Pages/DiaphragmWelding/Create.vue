<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref, watch, computed } from 'vue';
import { route } from 'ziggy-js';

interface User {
    id: number;
    name: string;
}

interface ItemCodeConfig {
    item_code: string;
    item_name: string | null;
    strength_min: number;
    measurement_1_type: 'range' | 'data_recording';
    measurement_1_min: number | null;
    measurement_1_max: number | null;
    circumference_diff_type: 'max_limit' | 'data_recording';
    circumference_diff_max: number | null;
}

interface Props {
    users: User[];
    itemCodes: ItemCodeConfig[];
    checkItems: Record<string, string>;
}

const props = defineProps<Props>();

const checkItemKeys = [
    'collapse_depth',
    'collapse_time',
    'strength',
    'appearance',
    'welding_condition',
    'measurement_1',
    'measurement_2',
    'measurement_3',
    'measurement_4',
    'measurement_5',
];

const form = useForm({
    item_name: '',
    item_code: '',
    month_year: '',
    production_date: new Date().toISOString().split('T')[0],
    lasermark_lot_number: '',
    machine_no: '',
    letter_code: '',
    df_rubber_lot: '',
    center_plate_a_lot: '',
    center_plate_b_lot: '',
    prod_qty: null as number | null,
    jo_number: '',
    temperature: null as number | null,
    operator_id: null as number | null,
    technician_id: null as number | null,
    checked_by_id: null as number | null,
    remarks: '',
    samples: checkItemKeys.map(key => ({
        check_item: key,
        sample_1: '',
        sample_2: '',
        sample_3: '',
        sample_4: '',
        sample_5: '',
    })),
});

const selectedItemConfig = ref<ItemCodeConfig | null>(null);
const customItemCode = ref('');

watch(() => form.item_code, (newCode) => {
    if (newCode) {
        const config = props.itemCodes.find(c => c.item_code === newCode);
        selectedItemConfig.value = config || null;
    } else {
        selectedItemConfig.value = null;
    }
});

const validationInfo = computed(() => {
    if (!selectedItemConfig.value) {
        return {
            strength_min: 0.30,
            measurement_1_type: 'data_recording',
            circumference_diff_type: 'data_recording',
        };
    }
    return selectedItemConfig.value;
});

const submit = () => {
    form.post(route('diaphragm-welding.store'));
};

const getSampleByCheckItem = (checkItem: string) => {
    return form.samples.find(s => s.check_item === checkItem);
};

const resetAllSamples = () => {
    form.samples.forEach(sample => {
        sample.sample_1 = '';
        sample.sample_2 = '';
        sample.sample_3 = '';
        sample.sample_4 = '';
        sample.sample_5 = '';
    });
};
</script>

<template>
    <Head title="Create Diaphragm Welding Checksheet" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create Diaphragm Welding Checksheet
                </h2>
                <Link 
                    :href="route('diaphragm-welding.index')" 
                    class="text-gray-600 hover:text-gray-800"
                >
                    &larr; Back to List
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <form @submit.prevent="submit">
                    <!-- Material Monitoring Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Material Monitoring</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Code *</label>
                                    <select 
                                        v-model="form.item_code"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option value="">Select or type custom...</option>
                                        <option v-for="config in itemCodes" :key="config.item_code" :value="config.item_code">
                                            {{ config.item_code }}
                                        </option>
                                    </select>
                                    <input 
                                        v-if="!form.item_code"
                                        type="text"
                                        v-model="customItemCode"
                                        @blur="form.item_code = customItemCode"
                                        placeholder="Or enter custom item code"
                                        class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                    <p v-if="form.errors.item_code" class="mt-1 text-sm text-red-600">{{ form.errors.item_code }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
                                    <input 
                                        type="text"
                                        v-model="form.item_name"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Production Date *</label>
                                    <input 
                                        type="date"
                                        v-model="form.production_date"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                    <p v-if="form.errors.production_date" class="mt-1 text-sm text-red-600">{{ form.errors.production_date }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Month/Year</label>
                                    <input 
                                        type="text"
                                        v-model="form.month_year"
                                        placeholder="e.g., December 2025"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Lasermark Lot Number</label>
                                    <input 
                                        type="text"
                                        v-model="form.lasermark_lot_number"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Machine No.</label>
                                    <input 
                                        type="text"
                                        v-model="form.machine_no"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Letter Code</label>
                                    <input 
                                        type="text"
                                        v-model="form.letter_code"
                                        maxlength="5"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">DF Rubber Lot</label>
                                    <input 
                                        type="text"
                                        v-model="form.df_rubber_lot"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Center Plate A Lot</label>
                                    <input 
                                        type="text"
                                        v-model="form.center_plate_a_lot"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Center Plate B Lot</label>
                                    <input 
                                        type="text"
                                        v-model="form.center_plate_b_lot"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Prod Qty</label>
                                    <input 
                                        type="number"
                                        v-model="form.prod_qty"
                                        min="0"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">JO Number</label>
                                    <input 
                                        type="text"
                                        v-model="form.jo_number"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Validation Rules Info -->
                    <div v-if="selectedItemConfig" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <h4 class="font-medium text-blue-800 mb-2">Validation Rules for {{ selectedItemConfig.item_code }}</h4>
                        <ul class="text-sm text-blue-700 space-y-1">
                            <li><strong>Strength:</strong> Must be &ge; {{ selectedItemConfig.strength_min }} kN</li>
                            <li v-if="selectedItemConfig.measurement_1_type === 'range'">
                                <strong>Measurement 1 (Center):</strong> {{ selectedItemConfig.measurement_1_min }} - {{ selectedItemConfig.measurement_1_max }} mm
                            </li>
                            <li v-else>
                                <strong>Measurement 1 (Center):</strong> Data Recording Only
                            </li>
                            <li v-if="selectedItemConfig.circumference_diff_type === 'max_limit'">
                                <strong>Measurements 2-5:</strong> Difference must be &le; {{ selectedItemConfig.circumference_diff_max }}
                            </li>
                            <li v-else>
                                <strong>Measurements 2-5:</strong> Data Recording Only
                            </li>
                        </ul>
                    </div>

                    <!-- Welding Data Section -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Welding Data</h3>
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-2">
                                    <p class="text-sm text-yellow-800">
                                        <strong>Legend:</strong> P = Pass | F = Fail (triggers reset of all samples)
                                    </p>
                                </div>
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-64">Check Item</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sample 1</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sample 2</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sample 3</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sample 4</th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Sample 5</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(sample, index) in form.samples" :key="sample.check_item" :class="{ 'bg-gray-50': index % 2 === 0 }">
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                {{ checkItems[sample.check_item] || sample.check_item }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <input 
                                                    type="text"
                                                    v-model="sample.sample_1"
                                                    class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center"
                                                />
                                            </td>
                                            <td class="px-4 py-3">
                                                <input 
                                                    type="text"
                                                    v-model="sample.sample_2"
                                                    class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center"
                                                />
                                            </td>
                                            <td class="px-4 py-3">
                                                <input 
                                                    type="text"
                                                    v-model="sample.sample_3"
                                                    class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center"
                                                />
                                            </td>
                                            <td class="px-4 py-3">
                                                <input 
                                                    type="text"
                                                    v-model="sample.sample_4"
                                                    class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center"
                                                />
                                            </td>
                                            <td class="px-4 py-3">
                                                <input 
                                                    type="text"
                                                    v-model="sample.sample_5"
                                                    class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center"
                                                />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Sample validation errors -->
                            <div v-if="Object.keys(form.errors).some(k => k.startsWith('samples.'))" class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
                                <h4 class="font-medium text-red-800 mb-2">Validation Errors</h4>
                                <ul class="text-sm text-red-700 list-disc list-inside">
                                    <li v-for="(error, key) in form.errors" :key="key" v-show="key.startsWith('samples.')">
                                        {{ error }}
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Personnel & Additional Info -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Personnel & Additional Info</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Temperature (°C)</label>
                                    <input 
                                        type="number"
                                        step="0.01"
                                        v-model="form.temperature"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    />
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Operator</label>
                                    <select 
                                        v-model="form.operator_id"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option :value="null">Select operator...</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Technician</label>
                                    <select 
                                        v-model="form.technician_id"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option :value="null">Select technician...</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Checked By</label>
                                    <select 
                                        v-model="form.checked_by_id"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    >
                                        <option :value="null">Select checker...</option>
                                        <option v-for="user in users" :key="user.id" :value="user.id">
                                            {{ user.name }}
                                        </option>
                                    </select>
                                </div>

                                <div class="md:col-span-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                                    <textarea 
                                        v-model="form.remarks"
                                        rows="3"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                    ></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end space-x-4">
                        <Link 
                            :href="route('diaphragm-welding.index')"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </Link>
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Saving...' : 'Create Checksheet' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
