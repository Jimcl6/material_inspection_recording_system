<template>
  <div class="container py-4">
    

    <div class="card">
      <div class="card-header"><div class="row d-flex-justify-content-around">
        <div class="col-6"><strong>Edit Temperature Record #{{ record.id }}</strong></div>
        <div class="col-6 d-flex justify-content-end">
          <a href="/temp-records" class="btn btn-danger btn-sm">Back / Cancel</a>
          <button class="btn btn-outline-primary btn-sm" :disabled="form.processing">Save</button>
            <div class="text-danger small" v-if="form.errors && Object.keys(form.errors).length">
              <div v-for="(msg, key) in form.errors" :key="key">{{ msg }}</div>
            </div>
        </div>
      </div></div>
      <div class="card-body">
        <form class="row g-3" @submit.prevent="submit">
          <!-- Groupbox 1: Model Series -->
          <fieldset class="col-12">
            <div class="card">
              <div class="card-header"><legend class="fs-6"><strong>Model Series</strong></legend></div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-6 col-md-6">
                    <label class="form-label">Model Series</label>
                    <input v-model="form.model_series" type="text" maxlength="100" class="form-control" required />
                  </div>
                  <div class="col-6 col-md-6">
                    <label class="form-label">Date/Time</label>
                    <input ref="dateEl" v-model="form.date" type="text" class="form-control" placeholder="DD/MM/YYYY" />
                  </div>
                </div>
              </div>
            </div>
          </fieldset>

          <!-- Groupbox 3: Driver Details -->
          <fieldset class="col-12">
            <div class="card">
              <div class="card-header"><legend class="fs-6"><strong>Driver Details</strong></legend></div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12 col-md-4">
                    <label class="form-label">Solder Model</label>
                    <input v-model="form.solder_model" type="text" maxlength="100" class="form-control" />
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label">Line Assigned</label>
                    <input v-model="form.line_assigned" type="text" maxlength="100" class="form-control" />
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label">Control No</label>
                    <input v-model="form.control_no" type="text" maxlength="50" class="form-control" />
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label">Process Assigned</label>
                    <input v-model="form.process_assigned" type="text" maxlength="100" class="form-control" />
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label">Person in Charge</label>
                    <input v-model="form.person_in_charge" type="text" maxlength="100" class="form-control" />
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label">Equipment Type</label>
                    <select v-model="form.equipment_type" class="form-select" required>
                      <option value="" disabled>Select...</option>
                      <option v-for="et in equipmentTypes" :key="et" :value="et">{{ et }}</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </fieldset>

          <!-- Groupbox 4: Temp Record -->
          <fieldset class="col-12">
            <div class="card">
              <div class="card-header"><legend class="fs-6"><strong>Temperature Record</strong></legend></div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12 col-lg-6">
                    <div class="row g-2">
                      <div class="col-6">
                        <label class="form-label">Time (AM)</label>
                        <input v-model="form.time_am" type="time" class="form-control" />
                      </div>
                      <div class="col-6">
                        <label class="form-label">Temp (AM)</label>
                        <input v-model="form.temp_am" type="text" maxlength="20" class="form-control" />
                      </div>
                    </div>
                  </div>
                  <div class="col-12 col-lg-6">
                    <div class="row g-2">
                      <div class="col-6">
                        <label class="form-label">Time (PM)</label>
                        <input v-model="form.time_pm" type="time" class="form-control" />
                      </div>
                      <div class="col-6">
                        <label class="form-label">Temp (PM)</label>
                        <input v-model="form.temp_pm" type="text" maxlength="20" class="form-control" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </fieldset>

          <!-- Groupbox 5: Remarks -->
          <fieldset class="col-12">
            <div class="card">
              <div class="card-header">
                <div class="row">
                  <div class="col-6"><legend class="fs-6"><strong>Remarks</strong></legend></div>
                  <div class="col-6"><legend class="fs-6"><strong>Checked By</strong></legend></div>
                </div>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-6">
                    <textarea v-model="form.col_remarks" rows="1" class="form-control" maxlength="100" placeholder="Remarks:"></textarea>
                  </div>
                  <div class="col-6">
                    <input v-model="form.checked_by" type="text" maxlength="100" class="form-control" />
                  </div>
                </div>
              </div>
            </div>
          </fieldset>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({ record: Object, equipmentTypes: Array })
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
  checked_by: props.record?.checked_by || '',
})

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

function submit() {
  const csrf = document.querySelector('meta[name=\"csrf-token\"]')?.getAttribute('content') || ''
  form.transform((data) => ({ ...data, _token: csrf }))
      .put(`/temp-records/${props.record.id}`)
}
</script>
