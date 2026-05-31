<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { route } from 'ziggy-js';

interface User {
    id: number;
    name: string;
}

interface TemplateField {
    key: string;
    label: string;
}

interface CheckItem {
    key: string;
    label: string;
    requirement_text?: string;
    requirement?: string;
}

interface ItemConfig {
    id: number;
    checksheet_type_id: number;
    item_code: string;
    item_name: string | null;
    validation_rules?: Record<string, any>;
}

interface ChecksheetType {
    id: number;
    key: string;
    name: string;
    material_fields: TemplateField[];
    check_items: CheckItem[];
    item_configs: ItemConfig[];
}

interface ChecksheetSample {
    check_item_key: string;
    check_item_label: string;
    requirement_text?: string | null;
    sample_values: string[];
    sort_order: number;
}

interface Checksheet {
    id: number;
    checksheet_type_id: number;
    item_config_id?: number | null;
    item_code?: string | null;
    item_name?: string | null;
    month_year?: string | null;
    production_date?: string | null;
    machine_no?: string | null;
    letter_code?: string | null;
    prod_qty?: number | null;
    job_number?: string | null;
    quantity?: number | null;
    temperature?: number | null;
    material_fields?: Record<string, string>;
    operator_id?: number | null;
    technician_id?: number | null;
    checked_by_id?: number | null;
    operator_name_raw?: string | null;
    technician_name_raw?: string | null;
    checked_by_name_raw?: string | null;
    remarks?: string | null;
    samples?: ChecksheetSample[];
}

const props = defineProps<{
    users: User[];
    types: ChecksheetType[];
    checksheet?: Checksheet;
}>();

const isEdit = computed(() => Boolean(props.checksheet?.id));

const firstType = props.types[0] || null;

const form = useForm({
    checksheet_type_id: props.checksheet?.checksheet_type_id || firstType?.id || null,
    item_config_id: props.checksheet?.item_config_id || null,
    item_code: props.checksheet?.item_code || '',
    item_name: props.checksheet?.item_name || '',
    month_year: props.checksheet?.month_year || '',
    production_date: props.checksheet?.production_date || new Date().toISOString().split('T')[0],
    machine_no: props.checksheet?.machine_no || '',
    letter_code: props.checksheet?.letter_code || '',
    prod_qty: props.checksheet?.prod_qty ?? null,
    job_number: props.checksheet?.job_number || '',
    quantity: props.checksheet?.quantity ?? null,
    temperature: props.checksheet?.temperature ?? null,
    material_fields: props.checksheet?.material_fields || {},
    operator_id: props.checksheet?.operator_id || null,
    technician_id: props.checksheet?.technician_id || null,
    checked_by_id: props.checksheet?.checked_by_id || null,
    operator_name_raw: props.checksheet?.operator_name_raw || '',
    technician_name_raw: props.checksheet?.technician_name_raw || '',
    checked_by_name_raw: props.checksheet?.checked_by_name_raw || '',
    remarks: props.checksheet?.remarks || '',
    samples: props.checksheet?.samples?.length ? props.checksheet.samples : [],
});

const selectedType = computed(() => props.types.find(type => type.id === Number(form.checksheet_type_id)) || firstType);

const itemConfigs = computed(() => selectedType.value?.item_configs || []);

const selectedItemConfig = computed(() => itemConfigs.value.find(config => config.id === Number(form.item_config_id)) || null);

const normalizedRequirement = (item: CheckItem): string => item.requirement_text || item.requirement || '';

const makeSamples = (type: ChecksheetType | null): ChecksheetSample[] => {
    return (type?.check_items || []).map((item, index) => ({
        check_item_key: item.key,
        check_item_label: item.label,
        requirement_text: normalizedRequirement(item),
        sample_values: ['', '', '', '', ''],
        sort_order: index,
    }));
};

const syncTemplateFields = (resetSamples = false) => {
    const type = selectedType.value;
    if (!type) {
        return;
    }

    const nextFields: Record<string, string> = {};
    type.material_fields.forEach(field => {
        nextFields[field.key] = form.material_fields?.[field.key] || '';
    });
    form.material_fields = nextFields;

    if (resetSamples || !form.samples.length) {
        form.samples = makeSamples(type);
        return;
    }

    const existing = new Map(form.samples.map(sample => [sample.check_item_key, sample]));
    form.samples = type.check_items.map((item, index) => {
        const sample = existing.get(item.key);
        return {
            check_item_key: item.key,
            check_item_label: item.label,
            requirement_text: normalizedRequirement(item),
            sample_values: sample?.sample_values?.length === 5 ? sample.sample_values : ['', '', '', '', ''],
            sort_order: index,
        };
    });
};

watch(() => form.checksheet_type_id, () => {
    form.item_config_id = null;
    form.item_code = '';
    form.item_name = '';
    syncTemplateFields(true);
});

watch(() => form.item_config_id, () => {
    if (selectedItemConfig.value) {
        form.item_code = selectedItemConfig.value.item_code;
        form.item_name = selectedItemConfig.value.item_name || '';
    }
});

if (!form.samples.length && firstType) {
    syncTemplateFields(true);
} else {
    syncTemplateFields(false);
}

const submit = () => {
    if (isEdit.value && props.checksheet) {
        form.put(route('welding-checksheets.update', props.checksheet.id));
        return;
    }

    form.post(route('welding-checksheets.store'));
};

const validationSummary = computed(() => {
    const rules = selectedItemConfig.value?.validation_rules || {};
    const entries: string[] = [];

    if (rules.strength_min) {
        entries.push(`Strength >= ${rules.strength_min}`);
    }
    if (rules.measurement_1_type === 'range') {
        entries.push(`Measurement 1 ${rules.measurement_1_min} to ${rules.measurement_1_max}`);
    }
    if (rules.circumference_diff_type === 'max_limit') {
        entries.push(`Circumference diff <= ${rules.circumference_diff_max}`);
    }
    if (rules.collapse_depth_min) {
        entries.push(`Collapse depth >= ${rules.collapse_depth_min}`);
    }
    if (rules.collapse_time_min && rules.collapse_time_max) {
        entries.push(`Collapse time ${rules.collapse_time_min} to ${rules.collapse_time_max}`);
    }

    return entries;
});
</script>

<template>
    <form @submit.prevent="submit">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Template</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Checksheet Type *</label>
                        <select v-model="form.checksheet_type_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option v-for="type in types" :key="type.id" :value="type.id">{{ type.name }}</option>
                        </select>
                        <p v-if="form.errors.checksheet_type_id" class="mt-1 text-sm text-red-600">{{ form.errors.checksheet_type_id }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Code</label>
                        <select v-model="form.item_config_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option :value="null">Custom item code</option>
                            <option v-for="config in itemConfigs" :key="config.id" :value="config.id">{{ config.item_code }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Code Text</label>
                        <input v-model="form.item_code" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                        <p v-if="form.errors.item_code" class="mt-1 text-sm text-red-600">{{ form.errors.item_code }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
                        <input v-model="form.item_name" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    </div>
                </div>

                <div v-if="validationSummary.length" class="mt-4 rounded-md border border-blue-200 bg-blue-50 p-3 text-sm text-blue-800">
                    <span class="font-medium">Validation:</span>
                    {{ validationSummary.join(' | ') }}
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Production Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Production Date *</label>
                        <input v-model="form.production_date" type="date" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                        <p v-if="form.errors.production_date" class="mt-1 text-sm text-red-600">{{ form.errors.production_date }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Month/Year</label>
                        <input v-model="form.month_year" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Machine No.</label>
                        <input v-model="form.machine_no" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Letter Code</label>
                        <input v-model="form.letter_code" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Prod Qty</label>
                        <input v-model="form.prod_qty" type="number" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Job Number</label>
                        <input v-model="form.job_number" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                        <input v-model="form.quantity" type="number" min="0" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Temperature (C)</label>
                        <input v-model="form.temperature" type="number" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    </div>
                </div>
            </div>
        </div>

        <div v-if="selectedType?.material_fields?.length" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Material Fields</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div v-for="field in selectedType.material_fields" :key="field.key">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ field.label }}</label>
                        <input v-model="form.material_fields[field.key]" type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Welding Samples</h3>
                <div v-if="form.errors.samples" class="mb-4 rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                    {{ form.errors.samples }}
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Check Item</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requirement</th>
                                <th v-for="sampleIndex in 5" :key="sampleIndex" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Sample {{ sampleIndex }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="sample in form.samples" :key="sample.check_item_key">
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ sample.check_item_label }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ sample.requirement_text || '-' }}</td>
                                <td v-for="(_, index) in sample.sample_values" :key="index" class="px-4 py-3">
                                    <input v-model="sample.sample_values[index]" type="text" class="w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-center" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Personnel & Remarks</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Operator</label>
                        <select v-model="form.operator_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option :value="null">Select operator</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                        <input v-model="form.operator_name_raw" type="text" placeholder="Raw operator name" class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Technician</label>
                        <select v-model="form.technician_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option :value="null">Select technician</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                        <input v-model="form.technician_name_raw" type="text" placeholder="Raw technician name" class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Checked By</label>
                        <select v-model="form.checked_by_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option :value="null">Select checker</option>
                            <option v-for="user in users" :key="user.id" :value="user.id">{{ user.name }}</option>
                        </select>
                        <input v-model="form.checked_by_name_raw" type="text" placeholder="Raw checker name" class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" />
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                        <textarea v-model="form.remarks" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <Link :href="route('welding-checksheets.index')" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                Cancel
            </Link>
            <button type="submit" :disabled="form.processing" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-indigo-700 disabled:opacity-50">
                {{ form.processing ? 'Saving...' : (isEdit ? 'Update Checksheet' : 'Create Checksheet') }}
            </button>
        </div>
    </form>
</template>
