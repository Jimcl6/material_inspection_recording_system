<template>
  <Head :title="`Edit Temperature Record #${record?.id || ''}`" />

  <AppLayout>
    <template #header>
      <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Edit Temperature Record #{{ record?.id || '' }}
        </h2>
        <Link 
          :href="route('temp-records.index')" 
          class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:bg-gray-200 active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
        >
          Back to List
        </Link>
      </div>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <form @submit.prevent="submit" class="space-y-6">
              <div v-if="form.errors && Object.keys(form.errors).length > 0" class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                  <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                  </div>
                  <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">
                      There {{ Object.keys(form.errors).length === 1 ? 'is' : 'are' }} {{ Object.keys(form.errors).length }} {{ Object.keys(form.errors).length === 1 ? 'error' : 'errors' }} with your submission
                    </h3>
                    <div class="mt-2 text-sm text-red-700">
                      <ul class="list-disc pl-5 space-y-1">
                        <li v-for="(error, key) in form.errors" :key="key">
                          {{ error }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Model Series & Date -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="model_series" class="block text-sm font-medium text-gray-700">
                    Model Series <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="model_series"
                    v-model="form.model_series"
                    type="text"
                    maxlength="100"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    required
                  />
                </div>
                <div>
                  <label for="date" class="block text-sm font-medium text-gray-700">
                    Date <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="date"
                    ref="dateEl"
                    v-model="form.date"
                    type="text"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    placeholder="DD/MM/YYYY"
                    required
                  />
                </div>
              </div>

              <!-- Solder Details -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="solder_model" class="block text-sm font-medium text-gray-700">
                    Solder Model
                  </label>
                  <input
                    id="solder_model"
                    v-model="form.solder_model"
                    type="text"
                    maxlength="100"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label for="equipment_type" class="block text-sm font-medium text-gray-700">
                    Equipment Type <span class="text-red-500">*</span>
                  </label>
                  <select
                    id="equipment_type"
                    v-model="form.equipment_type"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                    required
                  >
                    <option value="" disabled>Select Equipment Type</option>
                    <option value="Soldering Iron">Soldering Iron</option>
                    <option value="Soldering Pot">Soldering Pot</option>
                  </select>
                </div>
              </div>

              <!-- Assignment Details -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <label for="line_assigned" class="block text-sm font-medium text-gray-700">
                    Line Assigned
                  </label>
                  <input
                    id="line_assigned"
                    v-model="form.line_assigned"
                    type="text"
                    maxlength="100"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label for="control_no" class="block text-sm font-medium text-gray-700">
                    Control No
                  </label>
                  <input
                    id="control_no"
                    v-model="form.control_no"
                    type="text"
                    maxlength="50"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label for="process_assigned" class="block text-sm font-medium text-gray-700">
                    Process Assigned
                  </label>
                  <input
                    id="process_assigned"
                    v-model="form.process_assigned"
                    type="text"
                    maxlength="100"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  />
                </div>
                <div>
                  <label for="person_in_charge" class="block text-sm font-medium text-gray-700">
                    Person in Charge
                  </label>
                  <input
                    id="person_in_charge"
                    v-model="form.person_in_charge"
                    type="text"
                    maxlength="100"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  />
                </div>
              </div>

              <!-- Temperature Readings -->
              <div class="mt-8">
                <div class="flex justify-between items-center mb-4">
                  <h3 class="text-lg font-medium text-gray-900">Temperature Readings</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <!-- AM Reading -->
                  <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-900 mb-4">AM Reading</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label for="time_am" class="block text-sm font-medium text-gray-700">
                          Time (AM)
                        </label>
                        <input
                          id="time_am"
                          v-model="form.time_am"
                          type="time"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                      </div>
                      <div>
                        <label for="temp_am" class="block text-sm font-medium text-gray-700">
                          Temperature (AM)
                        </label>
                        <input
                          id="temp_am"
                          v-model="form.temp_am"
                          type="text"
                          maxlength="20"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                      </div>
                    </div>
                  </div>

                  <!-- PM Reading -->
                  <div class="bg-gray-50 p-4 rounded-lg">
                    <h4 class="text-md font-medium text-gray-900 mb-4">PM Reading</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label for="time_pm" class="block text-sm font-medium text-gray-700">
                          Time (PM)
                        </label>
                        <input
                          id="time_pm"
                          v-model="form.time_pm"
                          type="time"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                      </div>
                      <div>
                        <label for="temp_pm" class="block text-sm font-medium text-gray-700">
                          Temperature (PM)
                        </label>
                        <input
                          id="temp_pm"
                          v-model="form.temp_pm"
                          type="text"
                          maxlength="20"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Remarks & Verification -->
              <div class="mt-6">
                <label for="col_remarks" class="block text-sm font-medium text-gray-700">
                  Remarks
                </label>
                <textarea
                  id="col_remarks"
                  v-model="form.col_remarks"
                  rows="3"
                  maxlength="100"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  placeholder="Enter any remarks here..."
                ></textarea>
              </div>

              <div class="md:w-1/2">
                <label for="checked_by" class="block text-sm font-medium text-gray-700">
                  Checked By <span class="text-red-500">*</span>
                </label>
                <input
                  id="checked_by"
                  v-model="form.checked_by"
                  type="text"
                  maxlength="100"
                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  required
                />
              </div>

              <!-- Form Actions -->
              <div class="flex justify-end pt-6">
                <button
                  type="submit"
                  class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                  :disabled="form.processing"
                >
                  <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  record: {
    type: Object,
    default: null
  },
  equipmentTypes: {
    type: Array,
    default: () => []
  }
})

const dateEl = ref(null)
const form = useForm({
  date: props.record?.date || '',
  model_series: props.record?.model_series || '',
  solder_model: props.record?.solder_model || '',
  line_assigned: props.record?.line_assigned || '',
  control_no: props.record?.control_no || '',
  equipment_type: props.record?.equipment_type || '',
  process_assigned: props.record?.process_assigned || '',
  person_in_charge: props.record?.person_in_charge || '',
  time_am: props.record?.time_am || '',
  temp_am: props.record?.temp_am || '',
  time_pm: props.record?.time_pm || '',
  temp_pm: props.record?.temp_pm || '',
  col_remarks: props.record?.col_remarks || '',
  checked_by: props.record?.checked_by || ''
})

// Watch for record changes and update form
watch(() => props.record, (newRecord) => {
  if (newRecord) {
    form.date = newRecord.date || ''
    form.model_series = newRecord.model_series || ''
    form.solder_model = newRecord.solder_model || ''
    form.line_assigned = newRecord.line_assigned || ''
    form.control_no = newRecord.control_no || ''
    form.equipment_type = newRecord.equipment_type || ''
    form.process_assigned = newRecord.process_assigned || ''
    form.person_in_charge = newRecord.person_in_charge || ''
    form.time_am = newRecord.time_am || ''
    form.temp_am = newRecord.temp_am || ''
    form.time_pm = newRecord.time_pm || ''
    form.temp_pm = newRecord.temp_pm || ''
    form.col_remarks = newRecord.col_remarks || ''
    form.checked_by = newRecord.checked_by || ''
  }
}, { immediate: true })

onMounted(() => {
  if (window.flatpickr && dateEl.value) {
    window.flatpickr(dateEl.value, {
      dateFormat: 'd/m/Y',
      allowInput: true,
      onOpen: function(selectedDates, dateStr, instance) {
        instance.set('maxDate', new Date())
      }
    })
  }
})

function submit() {
  if (!props.record?.id) return
  
  form.put(`/temp-records/${props.record.id}`, {
    preserveScroll: true,
    onSuccess: () => {
      // Handle success if needed
    },
  })
}
</script>