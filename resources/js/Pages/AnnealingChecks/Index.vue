<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { ref } from 'vue';

type Filters = {
    search?: string;
    [key: string]: any;
};

type AnnealingCheck = {
    id: number;
    item_code: string;
    receiving_date: string;
    supplier_lot_number: string;
    quantity: number;
    annealing_date: string;
    machine_number: string;
    created_at: string;
    updated_at: string;
};

type AnnealingChecksResponse = {
    data: AnnealingCheck[];
    links: any[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
};

interface Props {
    annealingChecks: AnnealingChecksResponse;
    filters: Filters;
}

const props = withDefaults(defineProps<Props>(), {
    annealingChecks: () => ({
        data: [],
        links: [],
        current_page: 1,
        last_page: 1,
        per_page: 10,
        total: 0,
        from: 0,
        to: 0
    }),
    filters: () => ({
        search: ''
    })
});

const search = ref<string>(props.filters.search || '');
const checkToDelete = ref<AnnealingCheck | null>(null);
const showDeleteModal = ref<boolean>(false);

const deleteCheck = (): void => {
    if (!checkToDelete.value) return;
    
    router.delete(route('annealing-checks.destroy', checkToDelete.value.id), {
        onSuccess: (): void => {
            showDeleteModal.value = false;
            checkToDelete.value = null;
        },
        preserveScroll: true,
        onError: (error: Error): void => {
            console.error('Error deleting check:', error);
            showDeleteModal.value = false;
            checkToDelete.value = null;
            
            // Show error toast or notification
            window.dispatchEvent(new CustomEvent('toast', {
                detail: {
                    type: 'error',
                    message: 'Failed to delete the annealing check. Please try again.'
                }
            }));
        }
    });
};

const confirmDelete = (check: AnnealingCheck): void => {
    checkToDelete.value = check;
    showDeleteModal.value = true;
};

const searchChecks = (): void => {
    router.get(route('annealing-checks.index'), { search: search.value }, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    });
};

const resetSearch = (): void => {
    search.value = '';
    searchChecks();
};

const formatDate = (dateString: string): string => {
    if (!dateString) return '';
    try {
        const options: Intl.DateTimeFormatOptions = { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        };
        return new Date(dateString).toLocaleDateString(undefined, options);
    } catch (error) {
        console.error('Error formatting date:', error);
        return 'Invalid date';
    }
};
</script>

<template>
    <Head title="Annealing Checks" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Annealing Checks
                </h2>
                <div class="space-x-2">
                    <Link 
                        :href="route('annealing-checks.import.form')" 
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Import
                    </Link>
                    <Link 
                        :href="route('annealing-checks.create')" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Check
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-between items-center mb-6">
                            <div class="w-1/3">
                                <input
                                    v-model="search"
                                    type="search"
                                    placeholder="Search by item code, lot #, or machine #"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                />
                            </div>
                            <Link 
                                :href="route('annealing-checks.create')" 
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                            >
                                Add New Check
                            </Link>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Item Code
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Supplier Lot #
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Annealing Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Machine #
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Actions
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="check in annealingChecks.data" :key="check.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ check.item_code }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ check.supplier_lot_number || 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ formatDate(check.annealing_date) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ check.machine_number || 'N/A' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Completed
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <Link 
                                                    :href="route('annealing-checks.show', check.id)" 
                                                    class="p-1 text-indigo-600 hover:text-indigo-900 rounded-full hover:bg-indigo-50"
                                                    title="View"
                                                >
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                    </svg>
                                                </Link>
                                                <Link 
                                                    :href="route('annealing-checks.edit', check.id)" 
                                                    class="p-1 text-blue-600 hover:text-blue-900 rounded-full hover:bg-blue-50"
                                                    title="Edit"
                                                >
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                    </svg>
                                                </Link>
                                                <button 
                                                    @click="confirmDelete(check)" 
                                                    class="p-1 text-red-600 hover:text-red-900 rounded-full hover:bg-red-50"
                                                    title="Delete"
                                                >
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr v-if="!annealingChecks.data.length">
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                            No annealing checks found.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="mt-4" v-if="annealingChecks.links.length > 3">
                            <div class="flex justify-between items-center">
                                <div class="text-sm text-gray-700">
                                    Showing <span class="font-medium">{{ annealingChecks.from }}</span> to 
                                    <span class="font-medium">{{ annealingChecks.to }}</span> of 
                                    <span class="font-medium">{{ annealingChecks.total }}</span> results
                                </div>
                                <div class="flex space-x-1">
                                    <template v-for="(link, index) in annealingChecks.links" :key="index">
                                        <Link 
                                            v-if="link.url"
                                            :href="link.url"
                                            v-html="link.label"
                                            class="px-4 py-2 border rounded-md text-sm font-medium"
                                            :class="{
                                                'bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                                                'bg-white border-gray-300 text-gray-500 hover:bg-gray-50': !link.active && link.url,
                                                'text-gray-300': !link.url
                                            }"
                                        />
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="fixed inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showDeleteModal = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Delete Annealing Check
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to delete the annealing check for item code <span class="font-medium">{{ checkToDelete?.item_code }}</span>? This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button 
                            type="button" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                            @click="deleteCheck"
                        >
                            Delete
                        </button>
                        <button 
                            type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                            @click="showDeleteModal = false"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
