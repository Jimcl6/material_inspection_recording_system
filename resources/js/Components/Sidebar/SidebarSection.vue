<script setup>
defineProps({
    title: {
        type: String,
        default: '',
    },
    collapsed: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div class="space-y-1">
        <h3 
            v-if="title && !collapsed"
            class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider"
        >
            {{ title }}
        </h3>
        <div v-else-if="title && collapsed" class="border-t border-gray-200 my-2" />
        <div class="space-y-1">
            <slot />
        </div>
    </div>
</template>
