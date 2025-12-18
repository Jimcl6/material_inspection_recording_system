<script setup>
defineProps({
    class: {
        type: String,
        default: '',
    },
});

</script>

<template>
    <div :class="['bg-white overflow-hidden shadow-sm sm:rounded-lg', cardClass]">
        <div v-if="$slots.header" class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <slot name="header" />
        </div>
        <div class="p-6">
            <slot />
        </div>
    </div>
</template>