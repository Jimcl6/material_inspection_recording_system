<script setup>
import { reactive, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    filters: {
        type: Object,
        default: () => ({})
    },
    filterConfig: {
        type: Array,
        required: true,
        // Each item: { type: 'text'|'select'|'date', key: String, label: String, placeholder?: String, options?: Array<{value, label}> }
    },
    routeName: {
        type: String,
        required: true
    },
    debounceMs: {
        type: Number,
        default: 300
    }
});

const filterValues = reactive({});

// Initialize filter values from props
props.filterConfig.forEach((config) => {
    filterValues[config.key] = props.filters?.[config.key] ?? '';
});

const hasActiveFilters = computed(() => {
    return Object.values(filterValues).some((v) => v !== '' && v !== null && v !== undefined);
});

let debounceTimeout = null;

const applyFilters = () => {
    const query = {};
    Object.entries(filterValues).forEach(([key, value]) => {
        if (value !== '' && value !== null && value !== undefined) {
            query[key] = value;
        }
    });
    router.get(route(props.routeName), query, {
        preserveState: true,
        replace: true,
        preserveScroll: true,
    });
};

const debouncedApply = () => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        applyFilters();
    }, props.debounceMs);
};

const resetFilters = () => {
    props.filterConfig.forEach((config) => {
        filterValues[config.key] = '';
    });
    applyFilters();
};

// Watch text inputs for debounced auto-apply
props.filterConfig.forEach((config) => {
    if (config.type === 'text') {
        watch(() => filterValues[config.key], () => {
            debouncedApply();
        });
    }
});
</script>

<template>
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900">Filters</h3>
                </div>
                <button
                    v-if="hasActiveFilters"
                    @click="resetFilters"
                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition ease-in-out duration-150"
                >
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Clear Filters
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div v-for="config in filterConfig" :key="config.key">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ config.label }}</label>

                    <!-- Text Input -->
                    <div v-if="config.type === 'text'" class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input
                            v-model="filterValues[config.key]"
                            type="text"
                            :placeholder="config.placeholder || 'Search...'"
                            class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500"
                        />
                    </div>

                    <!-- Select Input -->
                    <select
                        v-else-if="config.type === 'select'"
                        v-model="filterValues[config.key]"
                        @change="applyFilters"
                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    >
                        <option value="">{{ config.placeholder || 'All' }}</option>
                        <option
                            v-for="opt in config.options"
                            :key="opt.value"
                            :value="opt.value"
                        >
                            {{ opt.label }}
                        </option>
                    </select>

                    <!-- Date Input -->
                    <input
                        v-else-if="config.type === 'date'"
                        v-model="filterValues[config.key]"
                        type="date"
                        @change="applyFilters"
                        class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
