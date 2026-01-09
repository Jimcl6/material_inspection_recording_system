<template>
  <Head title="Edit Modification Log" />

  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Edit Modification Log #{{ log.id }}
        </h2>
        <Link
          :href="route('modification-logs.index')"
          class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          Back to Modification Logs
        </Link>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <form @submit.prevent="submit" class="space-y-8">
              <!-- Basic Information Section -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">
                  Basic Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Production Date/Time <span class="text-red-500">*</span>
                    </label>
                    <input
                      ref="dateInputEl"
                      v-model="form.prod_date"
                      type="text"
                      placeholder="DD/MM/YYYY HH:MM"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                      required
                    />
                    <div v-if="form.errors.prod_date" class="mt-1 text-sm text-red-600">
                      {{ form.errors.prod_date }}
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Model Code <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.model_code"
                      type="text"
                      maxlength="100"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                      required
                    />
                    <div v-if="form.errors.model_code" class="mt-1 text-sm text-red-600">
                      {{ form.errors.model_code }}
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Item for Modification <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.item_for_modification"
                      type="text"
                      maxlength="255"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                      required
                    />
                    <div v-if="form.errors.item_for_modification" class="mt-1 text-sm text-red-600">
                      {{ form.errors.item_for_modification }}
                    </div>
                  </div>
                </div>
                <div class="mt-6">
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nature of Change
                  </label>
                  <textarea
                    v-model="form.nature_of_change"
                    rows="3"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  ></textarea>
                  <div v-if="form.errors.nature_of_change" class="mt-1 text-sm text-red-600">
                    {{ form.errors.nature_of_change }}
                  </div>
                </div>
              </div>

              <!-- Modification Details Section -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">
                  Modification Details
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      From
                    </label>
                    <input
                      v-model="form.col_from"
                      type="text"
                      maxlength="255"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    />
                    <div v-if="form.errors.col_from" class="mt-1 text-sm text-red-600">
                      {{ form.errors.col_from }}
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      To
                    </label>
                    <input
                      v-model="form.col_to"
                      type="text"
                      maxlength="255"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    />
                    <div v-if="form.errors.col_to" class="mt-1 text-sm text-red-600">
                      {{ form.errors.col_to }}
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Material Lot No
                    </label>
                    <input
                      v-model="form.material_lot_no"
                      type="text"
                      maxlength="255"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    />
                    <div v-if="form.errors.material_lot_no" class="mt-1 text-sm text-red-600">
                      {{ form.errors.material_lot_no }}
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Job No / Transfer Order
                    </label>
                    <input
                      v-model="form.job_no_transfer_order"
                      type="text"
                      maxlength="255"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    />
                    <div v-if="form.errors.job_no_transfer_order" class="mt-1 text-sm text-red-600">
                      {{ form.errors.job_no_transfer_order }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Serial Numbers Section -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">
                  Serial Numbers
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Start Serial
                    </label>
                    <input
                      v-model="form.start_serial"
                      type="text"
                      maxlength="255"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    />
                    <div v-if="form.errors.start_serial" class="mt-1 text-sm text-red-600">
                      {{ form.errors.start_serial }}
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      End Serial
                    </label>
                    <input
                      v-model="form.end_serial"
                      type="text"
                      maxlength="255"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    />
                    <div v-if="form.errors.end_serial" class="mt-1 text-sm text-red-600">
                      {{ form.errors.end_serial }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Personnel Section -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">
                  Personnel
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Recorded By <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.recorded_by"
                      type="text"
                      maxlength="255"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                      required
                    />
                    <div v-if="form.errors.recorded_by" class="mt-1 text-sm text-red-600">
                      {{ form.errors.recorded_by }}
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Source of Info
                    </label>
                    <input
                      v-model="form.source_of_info"
                      type="text"
                      maxlength="255"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    />
                    <div v-if="form.errors.source_of_info" class="mt-1 text-sm text-red-600">
                      {{ form.errors.source_of_info }}
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Approved By
                    </label>
                    <input
                      v-model="form.approved_by"
                      type="text"
                      maxlength="255"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    />
                    <div v-if="form.errors.approved_by" class="mt-1 text-sm text-red-600">
                      {{ form.errors.approved_by }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Additional Information Section -->
              <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">
                  Additional Information
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      4M
                    </label>
                    <input
                      v-model="form.col_4m"
                      type="text"
                      maxlength="50"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    />
                    <div v-if="form.errors.col_4m" class="mt-1 text-sm text-red-600">
                      {{ form.errors.col_4m }}
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      Line
                    </label>
                    <input
                      v-model="form.col_line"
                      type="text"
                      maxlength="50"
                      class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    />
                    <div v-if="form.errors.col_line" class="mt-1 text-sm text-red-600">
                      {{ form.errors.col_line }}
                    </div>
                  </div>
                </div>
                <div class="mt-6">
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    Remarks
                  </label>
                  <textarea
                    v-model="form.col_remarks"
                    rows="3"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  ></textarea>
                  <div v-if="form.errors.col_remarks" class="mt-1 text-sm text-red-600">
                    {{ form.errors.col_remarks }}
                  </div>
                </div>
              </div>

              <!-- Form Actions -->
              <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                <div class="text-sm text-gray-500">
                  <span class="text-red-500">*</span> Required fields
                </div>
                <div class="flex space-x-3">
                  <Link
                    :href="route('modification-logs.index')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                  >
                    Cancel
                  </Link>
                  <button
                    type="submit"
                    :disabled="form.processing"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
                  >
                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Update
                  </button>
                  <button
                    type="button"
                    @click="confirmDelete"
                    :disabled="form.processing"
                    class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-800 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

// Import route helper
declare function route(name: string, params?: any): string;

interface ModificationLog {
  id: number;
  prod_date: string;
  col_4m?: string;
  col_line?: string;
  model_code: string;
  item_for_modification: string;
  nature_of_change?: string;
  col_from?: string;
  col_to?: string;
  material_lot_no?: string;
  start_serial?: string;
  end_serial?: string;
  recorded_by: string;
  source_of_info?: string;
  approved_by?: string;
  job_no_transfer_order?: string;
  col_remarks?: string;
}

const props = defineProps<{
  log: ModificationLog;
}>();

const form = useForm({
  prod_date: props.log.prod_date || '',
  col_4m: props.log.col_4m || '',
  col_line: props.log.col_line || '',
  model_code: props.log.model_code || '',
  item_for_modification: props.log.item_for_modification || '',
  nature_of_change: props.log.nature_of_change || '',
  col_from: props.log.col_from || '',
  col_to: props.log.col_to || '',
  material_lot_no: props.log.material_lot_no || '',
  start_serial: props.log.start_serial || '',
  end_serial: props.log.end_serial || '',
  recorded_by: props.log.recorded_by || '',
  source_of_info: props.log.source_of_info || '',
  approved_by: props.log.approved_by || '',
  job_no_transfer_order: props.log.job_no_transfer_order || '',
  col_remarks: props.log.col_remarks || '',
});

const submit = () => {
  form.put(route('modification-logs.update', props.log.id), {
    onSuccess: () => {
      window.dispatchEvent(new CustomEvent('toast', {
        detail: {
          type: 'success',
          message: 'Modification log updated successfully!'
        }
      }));
    },
    onError: () => {
      window.dispatchEvent(new CustomEvent('toast', {
        detail: {
          type: 'error',
          message: 'Failed to update modification log. Please try again.'
        }
      }));
    }
  });
};

const confirmDelete = () => {
  if (confirm(`Are you sure you want to delete this modification log? This action cannot be undone.`)) {
    useForm({}).delete(route('modification-logs.destroy', props.log.id), {
      onSuccess: () => {
        window.dispatchEvent(new CustomEvent('toast', {
          detail: {
            type: 'success',
            message: 'Modification log deleted successfully!'
          }
        }));
        router.visit(route('modification-logs.index'));
      },
      onError: () => {
        window.dispatchEvent(new CustomEvent('toast', {
          detail: {
            type: 'error',
            message: 'Failed to delete modification log. Please try again.'
          }
        }));
      }
    })
  }
}

const dateEl = ref(null)
onMounted(() => {
  if (window.flatpickr && dateEl.value) {
    window.flatpickr(dateEl.value, {
      enableTime: true,
      dateFormat: 'd/m/Y H:i',
      time_24hr: true,
      allowInput: true,
    })
  }
})
</script>
