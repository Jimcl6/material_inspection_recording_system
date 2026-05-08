<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

defineEmits(['close']);
</script>

<template>
    <transition
        enter-active-class="transition-opacity duration-300 ease-out"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200 ease-in"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div 
            v-if="show"
            class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
            @click="$emit('close')"
            aria-hidden="true"
        />
    </transition>
</template>
