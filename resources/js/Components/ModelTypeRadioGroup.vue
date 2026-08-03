<script setup>
import { computed } from 'vue'

const props = defineProps({
    id: { type: String, required: true },
    label: { type: String, required: true },
    modelValue: { type: String, default: '' },
    options: { type: Array, required: true },
    error: { type: String, default: '' },
    required: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue'])
const isLegacyValue = computed(() => props.modelValue && !props.options.includes(props.modelValue))
</script>

<template>
    <!-- eslint-disable vue/valid-v-for -- Known parser false positive for keyed Vue template loops. -->
    <fieldset>
        <legend class="block text-sm font-medium text-gray-700">
            {{ label }}<span v-if="required"> *</span>
        </legend>
        <div class="mt-2 grid grid-cols-2 gap-3">
            <label
                v-for="(option, index) in options"
                :key="index"
                class="flex min-h-12 cursor-pointer items-center gap-3 rounded-lg border px-4 py-3 text-sm font-medium transition"
                :class="modelValue === option
                    ? 'border-blue-600 bg-blue-50 text-blue-800 ring-1 ring-blue-600'
                    : 'border-gray-300 bg-white text-gray-700 hover:border-blue-400'"
            >
                <input
                    :id="`${id}-${option.toLowerCase()}`"
                    type="radio"
                    :name="id"
                    :value="option"
                    :checked="modelValue === option"
                    :required="required && !isLegacyValue"
                    class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500"
                    @change="emit('update:modelValue', option)"
                />
                <span>{{ option }}</span>
            </label>
        </div>
        <div v-if="isLegacyValue" class="mt-3 rounded-md border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800">
            Current legacy value: <strong>{{ modelValue }}</strong>. You may leave it unchanged, or select one of the standard values above.
        </div>
        <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>
    </fieldset>
    <!-- eslint-enable vue/valid-v-for -->
</template>
