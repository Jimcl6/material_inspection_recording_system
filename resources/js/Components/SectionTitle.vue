<script setup>
defineProps({
    as: {
        type: String,
        default: 'h3',
    },
});

// Remove the defineSlots line as it's not needed
// Slots are automatically available in the template
</script>

<template>
    <component :is="as" class="text-lg font-medium text-gray-900">
        <slot />
    </component>
</template>