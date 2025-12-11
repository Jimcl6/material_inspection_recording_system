<template>
  <div class="container py-4">
    <a href="/torque-records" class="btn btn-link p-0 mb-3">← Back to Torque Records</a>

    <div class="card">
      <div class="card-header"><strong>Edit Torque Record #{{ record.id }}</strong></div>
      <div class="card-body">
        <form class="row g-3" @submit.prevent="submit">
          <fieldset class="col-12">
            <legend class="fs-6">Driver Info</legend>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label">Driver Model</label>
                <input v-model="form.driver_model" type="text" maxlength="100" class="form-control" />
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Driver Type</label>
                <input v-model="form.driver_type" type="text" maxlength="100" class="form-control" />
              </div>
            </div>
          </fieldset>

          <fieldset class="col-12">
            <legend class="fs-6">Assignment</legend>
            <div class="row g-3">
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
            </div>
          </fieldset>

          <fieldset class="col-12">
            <legend class="fs-6">Torque Record</legend>
            <div class="row g-3">
              <div class="col-12 col-lg-6">
                <div class="row g-2">
                  <div class="col-6">
                    <label class="form-label">Time (AM)</label>
                    <input v-model="form.time_am" type="time" class="form-control" />
                  </div>
                  <div class="col-6">
                    <label class="form-label">Torque (AM)</label>
                    <input v-model="form.torque_am" type="text" maxlength="20" class="form-control" />
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
                    <label class="form-label">Torque (PM)</label>
                    <input v-model="form.torque_pm" type="text" maxlength="20" class="form-control" />
                  </div>
                </div>
              </div>
            </div>
          </fieldset>

          <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary" :disabled="form.processing">Save</button>
            <div class="text-danger small" v-if="form.errors && Object.keys(form.errors).length">
              <div v-for="(msg, key) in form.errors" :key="key">{{ msg }}</div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({ record: Object })
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  driver_model: props.record?.driver_model || '',
  driver_type: props.record?.driver_type || '',
  line_assigned: props.record?.line_assigned || '',
  control_no: props.record?.control_no || '',
  process_assigned: props.record?.process_assigned || '',
  time_am: props.record?.time_am || '',
  torque_am: props.record?.torque_am || '',
  time_pm: props.record?.time_pm || '',
  torque_pm: props.record?.torque_pm || '',
})

function submit() {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
  form.transform((data) => ({ ...data, _token: csrf }))
      .put(`/torque-records/${props.record.id}`)
}
</script>
