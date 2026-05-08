<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
        default: false,
    },
    icon: {
        type: [Object, Function],
        default: null,
    },
    collapsed: {
        type: Boolean,
        default: false,
    },
    nested: {
        type: Boolean,
        default: false,
    },
});

const classes = computed(() => {
    const base = 'group flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150';
    const activeClasses = 'bg-indigo-50 text-indigo-700 border-l-4 border-indigo-500 -ml-px';
    const inactiveClasses = 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 border-l-4 border-transparent -ml-px';
    const nestedPadding = props.nested ? 'pl-11' : '';
    
    return [
        base,
        props.active ? activeClasses : inactiveClasses,
        nestedPadding,
    ].join(' ');
});

const iconClasses = computed(() => {
    const base = 'flex-shrink-0 h-5 w-5 transition-colors duration-150';
    return props.active 
        ? `${base} text-indigo-500` 
        : `${base} text-gray-400 group-hover:text-gray-500`;
});
</script>

<template>
    <Link :href="href" :class="classes" :title="collapsed ? $slots.default?.[0]?.children : undefined">
        <component 
            v-if="icon" 
            :is="icon" 
            :class="iconClasses"
            aria-hidden="true"
        />
        <span 
            :class="[
                'transition-opacity duration-200',
                icon ? 'ml-3' : '',
                collapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : ''
            ]"
        >
            <slot />
        </span>
    </Link>
</template>
