<script setup>
import { ref, computed, watch } from 'vue';
import { ChevronDownIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    icon: {
        type: [Object, Function],
        default: null,
    },
    collapsed: {
        type: Boolean,
        default: false,
    },
    defaultOpen: {
        type: Boolean,
        default: false,
    },
    active: {
        type: Boolean,
        default: false,
    },
});

const isOpen = ref(props.defaultOpen || props.active);

watch(() => props.active, (active) => {
    if (active) {
        isOpen.value = true;
    }
});

const toggle = () => {
    if (!props.collapsed) {
        isOpen.value = !isOpen.value;
    }
};

const buttonClasses = computed(() => {
    const base = 'group w-full flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-150';
    const activeClasses = 'bg-gray-100 text-gray-900';
    const inactiveClasses = 'text-gray-600 hover:bg-gray-50 hover:text-gray-900';
    
    return [base, props.active ? activeClasses : inactiveClasses].join(' ');
});

const iconClasses = computed(() => {
    const base = 'flex-shrink-0 h-5 w-5 transition-colors duration-150';
    return props.active 
        ? `${base} text-gray-500` 
        : `${base} text-gray-400 group-hover:text-gray-500`;
});

const chevronClasses = computed(() => {
    const base = 'ml-auto h-4 w-4 transition-transform duration-200 text-gray-400';
    return isOpen.value ? `${base} rotate-180` : base;
});
</script>

<template>
    <div class="space-y-1">
        <button
            type="button"
            @click="toggle"
            :class="buttonClasses"
            :title="collapsed ? title : undefined"
        >
            <component 
                v-if="icon" 
                :is="icon" 
                :class="iconClasses"
                aria-hidden="true"
            />
            <span 
                :class="[
                    'flex-1 text-left transition-opacity duration-200',
                    icon ? 'ml-3' : '',
                    collapsed ? 'lg:opacity-0 lg:w-0 lg:overflow-hidden' : ''
                ]"
            >
                {{ title }}
            </span>
            <ChevronDownIcon 
                v-if="!collapsed"
                :class="chevronClasses"
                aria-hidden="true"
            />
        </button>
        
        <transition
            enter-active-class="transition-all duration-200 ease-out"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-96"
            leave-active-class="transition-all duration-150 ease-in"
            leave-from-class="opacity-100 max-h-96"
            leave-to-class="opacity-0 max-h-0"
        >
            <div 
                v-show="isOpen && !collapsed" 
                class="space-y-1 overflow-hidden"
            >
                <slot />
            </div>
        </transition>
    </div>
</template>
