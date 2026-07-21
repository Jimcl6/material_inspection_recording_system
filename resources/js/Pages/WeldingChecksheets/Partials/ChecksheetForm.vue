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

const diaphragmDataRecordingRules = (strengthMin = 0.30): Record<string, any> => ({
    strength_min: strengthMin,
    measurement_1_type: 'data_recording',
    measurement_1_min: null,
    measurement_1_max: null,
    circumference_diff_type: 'data_recording',
    circumference_diff_max: null,
});

const diaphragmFormulaRules = (
    strengthMin: number,
    measurementOneMin: number,
    measurementOneMax: number,
): Record<string, any> => ({
    strength_min: strengthMin,
    measurement_1_type: 'range',
    measurement_1_min: measurementOneMin,
    measurement_1_max: measurementOneMax,
    circumference_diff_type: 'max_limit',
    circumference_diff_max: 0.30,
});

const LEGACY_DIAPHRAGM_RULES: Record<string, Record<string, any>> = {
    DFB8402000: diaphragmDataRecordingRules(0.40),
    DFB8402001: diaphragmDataRecordingRules(0.40),
    DFB660220P: diaphragmDataRecordingRules(),
    DFB660023P: {
        ...diaphragmFormulaRules(0.30, 7.890, 7.950),
    },
    DFB660024P: {
        ...diaphragmFormulaRules(0.30, 7.890, 7.950),
    },
    DFB660050P: {
        ...diaphragmFormulaRules(0.30, 8.250, 8.390),
    },
    DFB6602000: diaphragmDataRecordingRules(),
    DFB6602001: diaphragmDataRecordingRules(),
    DFB5902000: diaphragmDataRecordingRules(),
    DFB5902001: diaphragmDataRecordingRules(),
    DFB4805004: diaphragmDataRecordingRules(),
    DFB6602201: diaphragmDataRecordingRules(),
    DFB660240P: {
        ...diaphragmFormulaRules(0.30, 7.890, 7.950),
    },
    DFB660221P: diaphragmDataRecordingRules(),
    DFB480300P: {
        ...diaphragmFormulaRules(0.15, 7.280, 7.590),
    },
    DFB660024V: {
        ...diaphragmFormulaRules(0.30, 7.890, 7.950),
    },
    DFB4803000: {
        strength_min: 0.15,
        measurement_1_type: 'not_recorded',
        measurement_1_min: null,
        measurement_1_max: null,
        circumference_diff_type: 'data_recording',
        circumference_diff_max: null,
    },
};

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

const typedItemConfig = computed(() => {
    const itemCode = String(form.item_code || '').trim().toUpperCase();
    if (!itemCode) {
        return null;
    }

    return itemConfigs.value.find(config => String(config.item_code).trim().toUpperCase() === itemCode) || null;
});

const activeValidationRules = computed<Record<string, any>>(() => {
    const itemCode = String(form.item_code || selectedItemConfig.value?.item_code || '').trim().toUpperCase();

    return selectedItemConfig.value?.validation_rules
        || typedItemConfig.value?.validation_rules
        || LEGACY_DIAPHRAGM_RULES[itemCode]
        || {};
});

const hasActiveValidationRules = computed(() => Object.keys(activeValidationRules.value).length > 0);

const needsItemCodeValidation = computed(() => selectedType.value?.key === 'diaphragm' && !hasActiveValidationRules.value);

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

watch(activeValidationRules, rules => {
    if (rules.measurement_1_type !== 'not_recorded') {
        return;
    }

    const measurementOne = sampleByKey('measurement_1');
    if (measurementOne) {
        measurementOne.sample_values = ['', '', '', '', ''];
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
    const rules = activeValidationRules.value;
    const entries: string[] = [];

    if (selectedType.value?.check_items?.some(item => item.key === 'appearance')) {
        entries.push('Appearance P or /');
    }
    if (rules.strength_min) {
        entries.push(`Strength >= ${rules.strength_min}`);
    }
    if (rules.measurement_1_type === 'range') {
        entries.push(`Measurement 1 ${rules.measurement_1_min} to ${rules.measurement_1_max}`);
    }
    if (rules.measurement_1_type === 'data_recording') {
        entries.push('Measurement 1 data recording only');
    }
    if (rules.measurement_1_type === 'not_recorded') {
        entries.push('Measurement 1 not recorded');
    }
    if (rules.circumference_diff_type === 'max_limit') {
        entries.push(`Circumference diff <= ${rules.circumference_diff_max}`);
    }
    if (rules.circumference_diff_type === 'data_recording') {
        entries.push('Circumference diff data recording only');
    }
    if (rules.collapse_depth_min) {
        entries.push(`Collapse depth >= ${rules.collapse_depth_min}`);
    }
    if (rules.collapse_time_min && rules.collapse_time_max) {
        entries.push(`Collapse time ${rules.collapse_time_min} to ${rules.collapse_time_max}`);
    }

    return entries;
});

const numericSampleValue = (value: string | null | undefined): number | null => {
    const trimmed = String(value ?? '').trim();
    if (!trimmed || trimmed === '/') {
        return null;
    }

    const numericValue = Number(trimmed);
    return Number.isFinite(numericValue) ? numericValue : null;
};

const sampleByKey = (key: string): ChecksheetSample | undefined => {
    return form.samples.find(sample => sample.check_item_key === key);
};

const circumferenceDiffLimit = computed<number | null>(() => {
    const rules = activeValidationRules.value;
    if (rules.circumference_diff_type !== 'max_limit') {
        return null;
    }

    const limit = Number(rules.circumference_diff_max);
    return Number.isFinite(limit) ? limit : null;
});

const measurementOneRange = computed<{ min: number; max: number } | null>(() => {
    const rules = activeValidationRules.value;
    if (rules.measurement_1_type !== 'range') {
        return null;
    }

    const min = Number(rules.measurement_1_min);
    const max = Number(rules.measurement_1_max);
    return Number.isFinite(min) && Number.isFinite(max) ? { min, max } : null;
});

const strengthMinimum = computed<number | null>(() => {
    if (selectedType.value?.key !== 'diaphragm') {
        return null;
    }

    const min = Number(activeValidationRules.value.strength_min ?? 0.30);
    return Number.isFinite(min) ? min : null;
});

const circumferenceDiff = (sample: ChecksheetSample, index: number): number | null => {
    if (!['measurement_2', 'measurement_3', 'measurement_4', 'measurement_5'].includes(sample.check_item_key)) {
        return null;
    }

    const centerValue = numericSampleValue(sampleByKey('measurement_1')?.sample_values[index]);
    const measuredValue = numericSampleValue(sample.sample_values[index]);
    if (centerValue === null || measuredValue === null) {
        return null;
    }

    return Math.abs(measuredValue - centerValue) / 2;
};

const hasCircumferenceDiffError = (sample: ChecksheetSample, index: number): boolean => {
    const limit = circumferenceDiffLimit.value;
    const diff = circumferenceDiff(sample, index);

    return limit !== null && diff !== null && diff > limit;
};

const hasAppearanceError = (sample: ChecksheetSample, index: number): boolean => {
    if (sample.check_item_key !== 'appearance') {
        return false;
    }

    const value = String(sample.sample_values[index] ?? '').trim().toUpperCase();
    return value !== '' && value !== '/' && value !== 'P';
};

const hasStrengthError = (sample: ChecksheetSample, index: number): boolean => {
    const min = strengthMinimum.value;
    const value = numericSampleValue(sample.sample_values[index]);

    return sample.check_item_key === 'strength'
        && min !== null
        && value !== null
        && value < min;
};

const hasMeasurementOneError = (sample: ChecksheetSample, index: number): boolean => {
    const range = measurementOneRange.value;
    const value = numericSampleValue(sample.sample_values[index]);

    return sample.check_item_key === 'measurement_1'
        && range !== null
        && value !== null
        && (value < range.min || value > range.max);
};

const hasMeasurementOneNotRecordedError = (sample: ChecksheetSample, index: number): boolean => {
    const value = String(sample.sample_values[index] ?? '').trim();

    return sample.check_item_key === 'measurement_1'
        && activeValidationRules.value.measurement_1_type === 'not_recorded'
        && value !== ''
        && value !== '/';
};

const hasSampleInputError = (sample: ChecksheetSample, index: number): boolean => {
    return hasAppearanceError(sample, index)
        || hasStrengthError(sample, index)
        || hasMeasurementOneError(sample, index)
        || hasMeasurementOneNotRecordedError(sample, index)
        || hasCircumferenceDiffError(sample, index);
};

const sampleInputClass = (sample: ChecksheetSample, index: number): string[] => {
    if (isSampleInputDisabled(sample)) {
        return ['border-gray-200', 'bg-gray-100', 'text-gray-400', 'cursor-not-allowed'];
    }

    if (!hasSampleInputError(sample, index)) {
        return ['border-gray-300', 'focus:border-indigo-500', 'focus:ring-indigo-500'];
    }

    return ['sample-input-invalid'];
};

const isSampleInputDisabled = (sample: ChecksheetSample): boolean => {
    return sample.check_item_key === 'measurement_1'
        && activeValidationRules.value.measurement_1_type === 'not_recorded';
};

const sampleRequirementText = (sample: ChecksheetSample): string => {
    const rules = activeValidationRules.value;

    if (sample.check_item_key === 'measurement_1') {
        if (rules.measurement_1_type === 'range') {
            return `${rules.measurement_1_min} to ${rules.measurement_1_max}`;
        }
        if (rules.measurement_1_type === 'not_recorded') {
            return 'Not recorded';
        }
        if (rules.measurement_1_type === 'data_recording') {
            return 'Data recording';
        }
    }

    if (['measurement_2', 'measurement_3', 'measurement_4', 'measurement_5'].includes(sample.check_item_key)) {
        if (rules.circumference_diff_type === 'max_limit') {
            return `Difference <= ${rules.circumference_diff_max}`;
        }
        if (rules.circumference_diff_type === 'data_recording') {
            return 'Data recording';
        }
    }

    return sample.requirement_text || '-';
};

const sampleInputTitle = (sample: ChecksheetSample, index: number): string | undefined => {
    if (hasAppearanceError(sample, index)) {
        return 'Appearance must be P or /.';
    }

    const min = strengthMinimum.value;
    const strengthValue = numericSampleValue(sample.sample_values[index]);
    if (sample.check_item_key === 'strength' && min !== null && strengthValue !== null && strengthValue < min) {
        return `Strength is ${strengthValue}. Minimum allowed is ${min}.`;
    }

    if (hasMeasurementOneNotRecordedError(sample, index)) {
        return 'Measurement 1 is not recorded for this item code.';
    }

    const range = measurementOneRange.value;
    const value = numericSampleValue(sample.sample_values[index]);
    if (
        sample.check_item_key === 'measurement_1'
        && range !== null
        && value !== null
        && (value < range.min || value > range.max)
    ) {
        return `Measurement 1 is ${value}. Allowed range is ${range.min} to ${range.max}.`;
    }

    const limit = circumferenceDiffLimit.value;
    const diff = circumferenceDiff(sample, index);
    if (limit === null || diff === null || diff <= limit) {
        return undefined;
    }

    return `Circumference difference is ${diff.toFixed(3)}. Maximum allowed is ${limit}.`;
};
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
                <div v-else-if="needsItemCodeValidation" class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                    <span class="font-medium">Validation inactive:</span>
                    select a configured Diaphragm item code to apply the real-time sample checks.
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
                <div v-if="needsItemCodeValidation" class="mb-4 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                    Sample textboxes will validate after a configured Diaphragm item code is selected.
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
                                <td class="px-4 py-3 text-sm text-gray-500">{{ sampleRequirementText(sample) }}</td>
                                <td v-for="(_, index) in sample.sample_values" :key="index" class="px-4 py-3">
                                    <input
                                        v-model="sample.sample_values[index]"
                                        type="text"
                                        class="w-24 rounded-md shadow-sm sm:text-sm text-center"
                                        :class="sampleInputClass(sample, index)"
                                        :disabled="isSampleInputDisabled(sample)"
                                        :aria-invalid="hasSampleInputError(sample, index)"
                                        :title="sampleInputTitle(sample, index)"
                                    />
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

<style scoped>
.sample-input-invalid {
    border-color: rgb(239 68 68) !important;
    background-color: rgb(254 242 242);
    color: rgb(127 29 29);
}

.sample-input-invalid:focus {
    border-color: rgb(239 68 68) !important;
    box-shadow: 0 0 0 1px rgb(239 68 68);
}
</style>
