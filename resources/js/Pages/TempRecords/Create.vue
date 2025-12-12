<template>
  <div class="container py-4">
    
    <div class="card">
      <div class="card-header">
        <div class="row d-flex justify-content-between">
          <div class="col-6">
            <strong>New Temperature Record</strong>
          </div>
          <div class="col-6">
            <div class="container m-0 d-flex justify-content-end">
              <buton class="btn btn-danger btn-sm"><a href="/temp-records" class="text-decoration-none text-white"> Back / Cancel </a></buton>
              <button class="btn btn-outline-primary btn-sm" :disabled="form.processing">Save</button>
              <div class="text-danger small" v-if="form.errors && Object.keys(form.errors).length">
                <div v-for="(msg, key) in form.errors" :key="key">{{ msg }}</div>
              </div>
            </div>
          </div>
      </div></div>
      <div class="card-body">
        <form class="row g-3" @submit.prevent="submit">
          <!-- Groupbox 1: Model Series -->
          <div class="container my-1">
            <div class="card">
              <div class="card-header"><div class="row">
                <div class="col-6"><strong>Model Series:</strong></div>
                <div class="col-6"><strong>Date:</strong></div>
              </div></div>
              <div class="card-body">
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
                    <input v-model="form.solder_model" type="text" maxlength="100" class="form-control" placeholder="Solder Model"/>
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
          </div>

          <!-- Groupbox 4: Temp Record -->
          <div class="container my-1">
            <div class="card">
              <div class="card-header">
                <strong>Temperature Record</strong>
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
                          <input v-model="form.temp_am" type="text" maxlength="20" class="form-control" />
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
                          <input v-model="form.temp_pm" type="text" maxlength="20" class="form-control" />
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
              <div class="card-header"><div class="row">
                <div class="col-6"><strong>Remarks:</strong></div>
                <div class="col-6"><strong>Checked By:</strong></div>
              </div></div>
              <div class="card-body">
                <fieldset class="col-12">
                  <div class="row g-3">
                    <div class="col-6">
                      <!-- <label class="form-label">Remarks</label> -->
                      <textarea v-model="form.col_remarks" rows="1" class="form-control" maxlength="100"></textarea>
                    </div>
                    <div class="col-6">
                      <input v-model="form.checked_by" type="text" class="form-control col-12" placeholder="Checked By:" />
                    </div>
                  </div>
                </fieldset>
              </div>
            </div>
          </div>

          <!-- Groupbox 6: Checked By -->
          <div class="container my-1">
            <div class="row">
              

              <div class="col-6 d-flex justify-content-end">
                <div class="card border-0">
                  <div class="card-header bg-white border-0"></div>
                  <div class="card-body">
                    
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
import { ref, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({ equipmentTypes: Array })

const form = useForm({
  date: '',
  model_series: '',
  solder_model: '',
  line_assigned: '',
  control_no: '',
  equipment_type: '',
  process_assigned: '',
  person_in_charge: '',
  time_am: '',
  temp_am: '',
  time_pm: '',
  temp_pm: '',
  col_remarks: '',
  checked_by: '',
})

function submit() {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
  form.transform((data) => ({ ...data, _token: csrf }))
      .post('/temp-records')
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
