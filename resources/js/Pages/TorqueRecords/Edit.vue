<template>
  <div class="container py-4">
    <div class="card">
      <div class="card-header">
        <div class="row d-flex-justify-content-around">
          <div class="col-6"><strong>Edit Torque Record #{{ record.id }}</strong></div>
          <div class="col-6 d-flex justify-content-end gap-2">
            <a href="/torque-records" class="btn btn-danger btn-sm">Back / Cancel</a>
            <button class="btn btn-outline-primary btn-sm" :disabled="form.processing">Save</button>
            <div class="text-danger small" v-if="form.errors && Object.keys(form.errors).length">
              <div v-for="(msg, key) in form.errors" :key="key">{{ msg }}</div>
            </div>
          </div>
        </div>
      </div>
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
                    <label class="form-label">Date</label>
                    <input ref="dateEl" v-model="form.date" type="text" class="form-control" placeholder="DD/MM/YYYY" required />
                  </div>
                </div>
              </div>
            </div>
          </fieldset>

          <!-- Groupbox 2: Driver Details -->
          <fieldset class="col-12">
            <div class="card">
              <div class="card-header"><legend class="fs-6"><strong>Driver Details</strong></legend></div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12 col-md-4">
                    <label class="form-label">Driver Model</label>
                    <input v-model="form.driver_model" type="text" maxlength="100" class="form-control" required />
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label">Driver Type</label>
                    <input v-model="form.driver_type" type="text" maxlength="100" class="form-control" required />
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label">Screw Type</label>
                    <input v-model="form.screw_type" type="text" maxlength="100" class="form-control" required />
                  </div>
                </div>
              </div>
            </div>
          </fieldset>

          <!-- Groupbox 3: Assignment -->
          <fieldset class="col-12">
            <div class="card">
              <div class="card-header"><legend class="fs-6"><strong>Assignment</strong></legend></div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12 col-md-4">
                    <label class="form-label">Line Assigned</label>
                    <input v-model="form.line_assigned" type="text" maxlength="100" class="form-control" required />
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label">Control No</label>
                    <input v-model="form.control_no" type="text" maxlength="50" class="form-control" required />
                  </div>
                  <div class="col-12 col-md-4">
                    <label class="form-label">Process Assigned</label>
                    <input v-model="form.process_assigned" type="text" maxlength="100" class="form-control" required />
                  </div>
                  <div class="col-12 col-md-6">
                    <label class="form-label">Person in Charge</label>
                    <input v-model="form.person_in_charge" type="text" maxlength="100" class="form-control" required />
                  </div>
                </div>
              </div>
            </div>
          </fieldset>

          <!-- Groupbox 4: Torque Record -->
          <fieldset class="col-12">
            <div class="card">
              <div class="card-header"><legend class="fs-6"><strong>Torque Record</strong></legend></div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12 col-lg-6">
                    <div class="row g-2">
                      <div class="col-6">
                        <label class="form-label">Time (AM)</label>
                        <input v-model="form.time_am" type="time" class="form-control" required />
                      </div>
                      <div class="col-6">
                        <label class="form-label">Torque (AM)</label>
                        <input v-model="form.torque_am" type="number" step="0.01" class="form-control" required />
                      </div>
                    </div>
                  </div>
                  <div class="col-12 col-lg-6">
                    <div class="row g-2">
                      <div class="col-6">
                        <label class="form-label">Time (PM)</label>
                        <input v-model="form.time_pm" type="time" class="form-control" required />
                      </div>
                      <div class="col-6">
                        <label class="form-label">Torque (PM)</label>
                        <input v-model="form.torque_pm" type="number" step="0.01" class="form-control" required />
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
                    <input v-model="form.checked_by" type="text" maxlength="100" class="form-control" required />
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

const props = defineProps({ 
  record: Object 
})

const dateEl = ref(null)

const form = useForm({
  date: props.record?.date || '',
  model_series: props.record?.model_series || '',
  driver_model: props.record?.driver_model || '',
  driver_type: props.record?.driver_type || '',
  screw_type: props.record?.screw_type || '',
  line_assigned: props.record?.line_assigned || '',
  control_no: props.record?.control_no || '',
  process_assigned: props.record?.process_assigned || '',
  person_in_charge: props.record?.person_in_charge || '',
  time_am: props.record?.time_am || '',
  torque_am: props.record?.torque_am || '',
  time_pm: props.record?.time_pm || '',
  torque_pm: props.record?.torque_pm || '',
  col_remarks: props.record?.col_remarks || '',
  checked_by: props.record?.checked_by || '',
})

onMounted(() => {
  if (window.flatpickr && dateEl.value) {
    window.flatpickr(dateEl.value, {
      dateFormat: 'd/m/Y',
      allowInput: true,
    })
  }
})

function submit() {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
  form.transform((data) => ({ ...data, _token: csrf }))
      .put(`/torque-records/${props.record.id}`)
}
</script>