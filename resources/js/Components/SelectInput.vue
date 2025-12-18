<template>
    <div>
        <label v-if="label" class="block text-sm font-medium text-gray-700">
            {{ label }}
        </label>
        <select
            :id="id"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            v-bind="$attrs"
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
            :class="{ 'border-red-300 text-red-900 placeholder-red-300': error }"
        >
            <option v-if="showEmptyOption" value="" selected disabled>
                {{ placeholder || 'Select an option' }}
            </option>
            <option
                v-for="(option, index) in options"
                :key="index"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>
        <p v-if="error" class="mt-2 text-sm text-red-600">
            {{ error }}
        </p>
    </div>
</template>

<script setup>
const props = defineProps({
    modelValue: {
        type: [String, Number, Boolean, Object],
        default: '',
    },
    options: {
        type: Array,
        required: true,
        validator: (value) => {
            return value.every(option => 'value' in option && 'label' in option);
        }
    },
    id: {
        type: String,
        default: () => `select-${Math.random().toString(36).substr(2, 9)}`
    },
    label: {
        type: String,
        default: ''
    },
    error: {
        type: String,
        default: ''
    },
    placeholder: {
        type: String,
        default: ''
    },
    showEmptyOption: {
        type: Boolean,
        default: true
    }
});

defineEmits(['update:modelValue']);
</script>