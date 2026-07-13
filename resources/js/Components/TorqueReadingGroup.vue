<script setup>
import { computed } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    period: {
        type: String,
        required: true,
        validator: (value) => ['am', 'pm'].includes(value),
    },
    title: { type: String, required: true },
    time: { type: String, default: '' },
    readings: { type: Array, default: () => [{ torque_value: '' }] },
    errors: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:time', 'update:readings']);
const maximumReadings = 8;

const timeValue = computed({
    get: () => props.time,
    set: (value) => emit('update:time', value),
});

const timeError = computed(() => props.errors[`time_${props.period}`]);
const groupError = computed(() => props.errors[`readings.${props.period}`] || props.errors.readings);
const readingError = (index) => props.errors[`readings.${props.period}.${index}.torque_value`];

const updateReading = (index, value) => {
    const updated = props.readings.map((reading, readingIndex) => (
        readingIndex === index ? { ...reading, torque_value: value } : reading
    ));
    emit('update:readings', updated);
};

const addReading = () => {
    if (props.readings.length < maximumReadings) {
        emit('update:readings', [...props.readings, { torque_value: '' }]);
    }
};

const removeReading = (index) => {
    const updated = props.readings.filter((_, readingIndex) => readingIndex !== index);
    emit('update:readings', updated.length ? updated : [{ torque_value: '' }]);
};
</script>

<template>
    <div class="border border-gray-200 rounded-lg p-5 bg-gray-50">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <h4 class="text-lg font-medium text-gray-900">{{ title }}</h4>
                <p class="mt-1 text-xs text-gray-500">Up to {{ maximumReadings }} torque readings</p>
            </div>
            <span class="rounded-full bg-white px-2.5 py-1 text-xs font-medium text-gray-600 ring-1 ring-gray-200">
                {{ readings.length }} / {{ maximumReadings }}
            </span>
        </div>

        <div class="mb-5">
            <InputLabel :for="`time_${period}`" value="Time" />
            <TextInput
                :id="`time_${period}`"
                v-model="timeValue"
                type="time"
                class="mt-1 block w-full"
                :class="{ 'border-red-500': timeError }"
            />
            <InputError :message="timeError" class="mt-2" />
        </div>

        <div class="space-y-4">
            <div v-for="(reading, index) in readings" :key="index">
                <div class="flex items-center justify-between gap-3">
                    <InputLabel :for="`${period}_torque_${index}`" :value="`Torque Reading ${index + 1}`" />
                    <button
                        v-if="readings.length > 1"
                        type="button"
                        class="text-xs font-medium text-red-600 hover:text-red-800"
                        @click="removeReading(index)"
                    >
                        Remove
                    </button>
                </div>
                <div class="relative mt-1 rounded-md shadow-sm">
                    <TextInput
                        :id="`${period}_torque_${index}`"
                        :model-value="reading.torque_value"
                        type="number"
                        min="0"
                        max="99999999.99"
                        step="0.01"
                        inputmode="decimal"
                        class="block w-full pr-14"
                        :class="{ 'border-red-500': readingError(index) }"
                        placeholder="0.00"
                        @update:model-value="updateReading(index, $event)"
                    />
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">N·m</span>
                    </div>
                </div>
                <InputError :message="readingError(index)" class="mt-2" />
            </div>
        </div>

        <InputError :message="groupError" class="mt-4" />

        <button
            type="button"
            class="mt-5 inline-flex w-full items-center justify-center rounded-md border border-dashed border-indigo-300 bg-white px-4 py-2 text-sm font-medium text-indigo-700 hover:border-indigo-400 hover:bg-indigo-50 disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="readings.length >= maximumReadings"
            @click="addReading"
        >
            <span v-if="readings.length < maximumReadings">+ Add Torque Reading</span>
            <span v-else>Maximum of 8 readings reached</span>
        </button>
    </div>
</template>
