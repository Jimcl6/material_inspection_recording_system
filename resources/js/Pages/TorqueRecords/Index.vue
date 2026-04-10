<template>
  <Head title="Torque Records" />

  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Torque Records
        </h2>
        <div class="flex space-x-2">
          <Link 
            v-if="canCreate('torque')"
            :href="route('torque-records.create')" 
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
        <DataTableFilters
            :filters="filters"
            :filter-config="filterConfig"
            route-name="torque-records.index"
        />

        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
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
                      Driver Details
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      In Charge
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      AM Reading
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      PM Reading
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
                      <div class="text-sm text-gray-500">{{ record.control_no }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900">{{ record.driver_model }}</div>
                      <div class="text-sm text-gray-500">Type: {{ record.driver_type }}</div>
                      <div class="text-sm text-gray-500">Line {{ record.line_assigned }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ record.person_in_charge }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getTorqueClass(record.torque_am)">
                          {{ record.torque_am }} N·m
                        </span>
                        <span class="ml-2 text-xs text-gray-500">{{ record.time_am }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getTorqueClass(record.torque_pm)">
                          {{ record.torque_pm }} N·m
                        </span>
                        <span class="ml-2 text-xs text-gray-500">{{ record.time_pm }}</span>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                      <div class="flex items-center justify-end space-x-2">
                        <Link 
                          :href="route('torque-records.show', record.id)" 
                          class="p-1 text-indigo-600 hover:text-indigo-900 rounded-full hover:bg-indigo-50"
                          title="View"
                        >
                          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                          </svg>
                        </Link>
                        <Link 
                          v-if="canUpdate('torque')"
                          :href="route('torque-records.edit', record.id)" 
                          class="p-1 text-blue-600 hover:text-blue-900 rounded-full hover:bg-blue-50"
                          title="Edit"
                        >
                          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </Link>
                        <button 
                          v-if="canDelete('torque')"
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
                      No torque records found.
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
        Are you sure you want to delete this torque record? This action cannot be undone.
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
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTableFilters from '@/Components/DataTableFilters.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { usePermissions } from '@/Composables/usePermissions';

const { canCreate, canUpdate, canDelete } = usePermissions();

const props = defineProps({
  records: {
    type: Object,
    required: true
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  driverModels: {
    type: Array,
    default: () => []
  },
  lineOptions: {
    type: Array,
    default: () => []
  }
});

const filterConfig = computed(() => [
  {
    type: 'text',
    key: 'search',
    label: 'Search',
    placeholder: 'Search by model, driver, screw type...',
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
    key: 'driver_model',
    label: 'Driver Model',
    placeholder: 'All Drivers',
    options: props.driverModels.map((d) => ({ value: d, label: d })),
  },
  {
    type: 'select',
    key: 'line_assigned',
    label: 'Line',
    placeholder: 'All Lines',
    options: props.lineOptions.map((l) => ({ value: l, label: l })),
  },
]);

const showDeleteModal = ref(false);
const recordToDelete = ref(null);
const deleteProcessing = ref(false);

const confirmDelete = (record) => {
  recordToDelete.value = record;
  showDeleteModal.value = true;
};

const deleteRecord = () => {
  if (!recordToDelete.value) return;
  
  deleteProcessing.value = true;
  
  router.delete(route('torque-records.destroy', recordToDelete.value.id), {
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
  window.location.href = route('torque-records.export');
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

const getTorqueClass = (torque) => {
  const torqueValue = parseFloat(torque) || 0;
  // Adjust these thresholds based on your torque requirements
  if (torqueValue < 5) return 'bg-blue-100 text-blue-800';
  if (torqueValue > 15) return 'bg-red-100 text-red-800';
  return 'bg-green-100 text-green-800';
};
</script>