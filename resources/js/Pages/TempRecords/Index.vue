<template>
  <Head title="Temperature Records" />

  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Temperature Records
        </h2>
        <div class="flex space-x-2">
          <Link 
            :href="route('temperature-records.import.form')" 
            class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
            </svg>
            Import
          </Link>
          <Link 
            :href="route('temperature-records.create')" 
            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            New Record
          </Link>
        </div>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <!-- Search and Filter -->
            <div class="flex justify-between items-center mb-6">
              <div class="w-1/3">
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <input
                    v-model="search"
                    type="text"
                    class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    placeholder="Search records..."
                    @keyup.enter="searchRecords"
                  />
                  <button
                    v-if="search"
                    @click="resetSearch"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                    type="button"
                  >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                  </button>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <button
                  @click="exportRecords"
                  class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                  </svg>
                  Export
                </button>
              </div>
            </div>

            <!-- Records Table -->
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Date
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Model Series
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Equipment
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      In Charge
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      AM Temp
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      PM Temp
                    </th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="record in records.data" :key="record.id" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDate(record.date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-gray-900">{{ record.model_series }}</div>
                      <div class="text-sm text-gray-500">{{ record.solder_model }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ record.equipment_type }}</div>
                      <div class="text-sm text-gray-500">Line {{ record.line_assigned }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ record.person_in_charge }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getTempClass(record.temp_am)">
                          {{ record.temp_am }}°C
                        </span>
                        <span class="ml-2 text-xs text-gray-500">{{ record.time_am }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getTempClass(record.temp_pm)">
                          {{ record.temp_pm }}°C
                        </span>
                        <span class="ml-2 text-xs text-gray-500">{{ record.time_pm }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <div class="flex items-center justify-end space-x-2">
                        <Link 
                          :href="route('temperature-records.show', record.id)" 
                          class="p-1 text-indigo-600 hover:text-indigo-900 rounded-full hover:bg-indigo-50"
                          title="View"
                        >
                          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                          </svg>
                        </Link>
                        <Link 
                          :href="route('temperature-records.edit', record.id)" 
                          class="p-1 text-yellow-600 hover:text-yellow-900 rounded-full hover:bg-yellow-50"
                          title="Edit"
                        >
                          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </Link>
                        <button 
                          @click="confirmDelete(record)" 
                          class="p-1 text-red-600 hover:text-red-900 rounded-full hover:bg-red-50"
                          title="Delete"
                        >
                          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="!records.data.length">
                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                      No temperature records found.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4" v-if="records.links && records.links.length > 3">
              <div class="flex justify-between items-center">
                <div class="text-sm text-gray-700">
                  Showing <span class="font-medium">{{ records.from || 0 }}</span> to 
                  <span class="font-medium">{{ records.to || 0 }}</span> of 
                  <span class="font-medium">{{ records.total || 0 }}</span> results
                </div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                  <template v-for="(link, index) in records.links" :key="index">
                    <span 
                      v-if="!link.url"
                      v-html="link.label"
                      class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
                    ></span>
                    <Link
                      v-else
                      :href="link.url"
                      v-html="link.label"
                      class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium"
                      :class="{
                        'z-10 bg-indigo-50 border-indigo-500 text-indigo-600': link.active,
                        'hover:bg-gray-50 text-gray-700': !link.active
                      }"
                    ></Link>
                  </template>
                </nav>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal :show="showDeleteModal" @close="showDeleteModal = false">
      <template #title>
        Delete Record
      </template>
      <template #content>
        Are you sure you want to delete this temperature record? This action cannot be undone.
      </template>
      <template #footer>
        <SecondaryButton @click="showDeleteModal = false">
          Cancel
        </SecondaryButton>
        <DangerButton 
          class="ml-2" 
          :class="{ 'opacity-25': deleteProcessing }" 
          :disabled="deleteProcessing"
          @click="deleteRecord"
        >
          <span v-if="deleteProcessing">Deleting...</span>
          <span v-else>Delete Record</span>
        </DangerButton>
      </template>
    </ConfirmationModal>
  </AppLayout>
</template>

<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
  records: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({
      search: ''
    })
  }
});

const search = ref(props.filters.search || '');
const showDeleteModal = ref(false);
const recordToDelete = ref(null);
const deleteProcessing = ref(false);

const searchRecords = () => {
  router.get(route('temperature-records.index'), { search: search.value }, {
    preserveState: true,
    replace: true,
    preserveScroll: true
  });
};

const resetSearch = () => {
  search.value = '';
  searchRecords();
};

const confirmDelete = (record) => {
  recordToDelete.value = record;
  showDeleteModal.value = true;
};

const deleteRecord = () => {
  if (!recordToDelete.value) return;
  
  deleteProcessing.value = true;
  
  router.delete(route('temperature-records.destroy', recordToDelete.value.id), {
    onSuccess: () => {
      showDeleteModal.value = false;
      recordToDelete.value = null;
      deleteProcessing.value = false;
    },
    onError: () => {
      showDeleteModal.value = false;
      deleteProcessing.value = false;
    },
    preserveScroll: true
  });
};

const exportRecords = () => {
  window.location.href = route('temperature-records.export');
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

const getTempClass = (temp) => {
  const tempValue = parseFloat(temp) || 0;
  if (tempValue < 20) return 'bg-blue-100 text-blue-800';
  if (tempValue > 30) return 'bg-red-100 text-red-800';
  return 'bg-green-100 text-green-800';
};

// Watch for changes in the search input with debounce
let searchTimeout = null;
watch(search, (newValue) => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    searchRecords();
  }, 300);
});
</script>
