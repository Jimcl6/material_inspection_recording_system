<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTableFilters from '@/Components/DataTableFilters.vue';
import DeleteButton from '@/Components/DeleteButton.vue';

const props = defineProps({
    materialParts: {
        type: Object,
        default: () => ({ data: [], links: [], meta: { from: 0, to: 0, total: 0 } })
    },
    materialTypes: {
        type: Object,
        default: () => ({})
    },
    filters: {
        type: Object,
        default: () => ({})
    }
});

const materialTypeOptions = computed(() => {
    if (!props.materialTypes) return [];
    return Object.entries(props.materialTypes).map(([key, value]) => ({
        value: key,
        label: value,
    }));
});

const filterConfig = computed(() => [
    {
        type: 'text',
        key: 'search',
        label: 'Search',
        placeholder: 'Search by lot number...',
    },
    {
        type: 'date',
        key: 'date_from',
        label: 'Date From',
    },
    {
        type: 'date',
        key: 'date_to',
        label: 'Date To',
    },
    {
        type: 'select',
        key: 'material_type',
        label: 'Material Type',
        placeholder: 'All Types',
        options: materialTypeOptions.value,
    },
]);

const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getSubLotCount = (materialPart) => {
    if (!materialPart.sub_lot_numbers) {
        return 0;
    }
    // Handle old array format (sub_lots key) and new keyed object format
    if (materialPart.sub_lot_numbers.sub_lots) {
        return materialPart.sub_lot_numbers.sub_lots.length;
    }
    return Object.keys(materialPart.sub_lot_numbers).filter(
        key => materialPart.sub_lot_numbers[key] && materialPart.sub_lot_numbers[key].trim()
    ).length;
};
</script>

<template>
    <Head title="Material Monitoring Checksheets" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Material Monitoring Checksheets
                </h2>
                <Link
                    :href="route('material-monitoring-checksheets.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Create New
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <DataTableFilters
                    :filters="filters"
                    :filter-config="filterConfig"
                    route-name="material-monitoring-checksheets.index"
                />

                <!-- Results Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                Results ({{ materialParts.data?.length || 0 }})
                            </h3>
                            <div class="text-sm text-gray-500">
                                Showing {{ materialParts.meta?.from || 0 }} to {{ materialParts.meta?.to || 0 }} of {{ materialParts.meta?.total || 0 }}
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Material Type
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Item Block Code
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Letter Code
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Main Lot Number
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Sub Lots
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Quantity
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Operator
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="materialPart in materialParts.data" :key="materialPart.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatDate(materialPart.date) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ materialTypes[materialPart.material_type] || materialPart.material_type }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ materialPart.item_block_code }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                {{ materialPart.letter_code }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ materialPart.main_lot_number }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ getSubLotCount(materialPart) }} sub-lots
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ materialPart.produced_qty?.toLocaleString() || 0 }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ materialPart.operator || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <Link
                                                    :href="route('material-monitoring-checksheets.show', materialPart.id)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                    title="View"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </Link>
                                                <Link
                                                    :href="route('material-monitoring-checksheets.edit', materialPart.id)"
                                                    class="text-blue-600 hover:text-blue-900"
                                                    title="Edit"
                                                >
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </Link>
                                                <DeleteButton :material-part="materialPart" />
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!materialParts.data || materialParts.data.length === 0">
                                        <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No material parts found. Click "Create New" to add your first material part.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6" v-if="materialParts.links && materialParts.links.length > 3">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700">
                                    Showing {{ materialParts.meta?.from || 0 }} to {{ materialParts.meta?.to || 0 }} of {{ materialParts.meta?.total || 0 }} results
                                </div>
                                <div class="flex space-x-1">
                                    <template v-for="(link, key) in materialParts.links" :key="key">
                                        <Link
                                            v-if="link.url"
                                            :href="link.url"
                                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium rounded-md"
                                            :class="{
                                                'bg-indigo-600 border-indigo-600 text-white': link.active,
                                                'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active
                                            }"
                                        >
                                            <span v-html="link.label"></span>
                                        </Link>
                                        <span
                                            v-else
                                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white"
                                        >
                                            <span v-html="link.label"></span>
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
