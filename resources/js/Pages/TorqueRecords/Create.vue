<template>
  <div class="container py-4">
    <a href="/torque-records" class="btn btn-link p-0 mb-3">← Back to Torque Records</a>

    <div class="card">
      <div class="card-header"><strong>New Torque Record</strong></div>
      <div class="card-body">
        <form class="row g-3" @submit.prevent="submit">
          <!-- Groupbox 1: Model Series -->
          <div class="container my-1">
            <div class="row">
              <fieldset class="col-6">
                <div class="row g-3">
                  <div class="col-12 col-md-12">
                    <input v-model="form.model_series" type="text" class="form-control col-12" placeholder="Model Series:" required />
                  </div>
                </div>
              </fieldset>
              <fieldset class="col-6">
                <div class="row g-3">
                  <div class="col-12 col-md-12">
                    <input ref="dateEl" v-model="form.date" type="text" class="form-control col-12" placeholder="Date: DD/MM/YYYY" />
                  </div>
                </div>
              </fieldset>
            </div>
          </div>
          <!-- Groupbox 2: Date -->
          
          <!-- Groupbox 3: Driver Details -->
          <div class="container my-1">
            <div class="card">
              <div class="card-header">
                <strong>Driver Details</strong>
              </div>
              <div class="card-body">
                <div class="row g-3">
                  <div class="col-12 col-md-4">
                    <label class="form-label">Solder Model</label>
                    <input v-model="form.driver_model" type="text" maxlength="100" class="form-control" />
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
                    <label class="form-label">Screw Type</label>
                    <input v-model="form.screw_type" type="text" maxlength="50" class="form-control" />
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
                    <select v-model="form.driver_type" class="form-select" required>
                      <option value="" disabled>Select...</option>
                      <option v-for="et in equipmentTypes" :key="et" :value="et">{{ et }}</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Groupbox 4: Temp Record -->
          <div class="container my-1">
            <div class="card">
              <div class="card-header">
                <strong>Torque Record</strong>
              </div>
              <div class="card-body">
                <fieldset class="col-12">
                  <div class="row g-3">
                    <!-- Left side: AM pair aligned left -->
                    <div class="col-12 col-lg-6">
                      <div class="row g-2">
                        <div class="col-6">
                          <label class="form-label">Time (AM)</label>
                          <input v-model="form.time_am" type="time" class="form-control" />
                        </div>
                        <div class="col-6">
                          <label class="form-label">Temp (AM)</label>
                          <input v-model="form.torque_am" type="text" maxlength="20" class="form-control" />
                        </div>
                      </div>
                    </div>
                    <!-- Left-most for PM on its half -->
                    <div class="col-12 col-lg-6">
                      <div class="row g-2">
                        <div class="col-6">
                          <label class="form-label">Time (PM)</label>
                          <input v-model="form.time_pm" type="time" class="form-control" />
                        </div>
                        <div class="col-6">
                          <label class="form-label">Temp (PM)</label>
                          <input v-model="form.torque_pm" type="text" maxlength="20" class="form-control" />
                        </div>
                      </div>
                    </div>
                  </div>
                </fieldset>
              </div>
            </div>
          </div>

          <!-- Groupbox 5: Remarks -->
          <div class="container my-1">
            <div class="card">             
              <div class="card-header"><strong>Remarks</strong></div>
              <div class="card-body">
                <fieldset class="col-12">
                  <div class="row g-3">
                    <div class="col-12">
                      <!-- <label class="form-label">Remarks</label> -->
                      <textarea v-model="form.col_remarks" rows="3" class="form-control" maxlength="100"></textarea>
                    </div>
                  </div>
                </fieldset>
              </div>
            </div>
          </div>

          <!-- Groupbox 6: Checked By -->
          <div class="container my-1">
            <div class="row">
              <div class="col-6">
                <div class="card">
                  <div class="card-header">
                    <strong class="fs-6">Checked By:</strong>
                  </div>
                  <div class="card-body">
                    <div class="row g-3">
                      <div class="col-12 col-md-12">
                        <input v-model="form.checked_by" type="text" class="form-control col-12" placeholder="Checked By:" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-6 d-flex justify-content-end">
                <div class="card border-0">
                  <div class="card-header bg-white border-0"></div>
                  <div class="card-body">
                    <button class="btn btn-outline-primary btn-lg" :disabled="form.processing">Save</button>
                    <div class="text-danger small" v-if="form.errors && Object.keys(form.errors).length">
                      <div v-for="(msg, key) in form.errors" :key="key">{{ msg }}</div>
                    </div>
                  </div>
                </div> 
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  driver_model: '',
  driver_type: '',
  line_assigned: '',
  control_no: '',
  process_assigned: '',
  time_am: '',
  torque_am: '',
  time_pm: '',
  torque_pm: '',
})

function submit() {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
  form.transform((data) => ({ ...data, _token: csrf }))
      .post('/torque-records')
}
</script>
