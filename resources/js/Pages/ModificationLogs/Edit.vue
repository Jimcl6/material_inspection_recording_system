<template>
  <div class="container py-4">
    <a :href="`/modification-logs/${log.id}`" class="btn btn-link p-0 mb-3">← Back to Log</a>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Modification Log #{{ log.id }}</h5>
      </div>
      <div class="card-body">
        <form class="row g-3" @submit.prevent="submit">
          <!-- Group 1: Basic Info -->
          <fieldset class="col-12">
            <legend class="fs-6">Basic Information</legend>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label">Production Date/Time</label>
                <input ref="dateEl" v-model="form.prod_date" type="text" class="form-control" placeholder="DD/MM/YYYY HH:MM" required />
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Model Code</label>
                <input v-model="form.model_code" type="text" maxlength="100" class="form-control" required />
              </div>
              <div class="col-12">
                <label class="form-label">Item for Modification</label>
                <input v-model="form.item_for_modification" type="text" maxlength="255" class="form-control" required />
              </div>
              <div class="col-12">
                <label class="form-label">Nature of Change</label>
                <textarea v-model="form.nature_of_change" rows="2" class="form-control"></textarea>
              </div>
            </div>
          </fieldset>

          <!-- Group 2: Details -->
          <fieldset class="col-12">
            <legend class="fs-6">Modification Details</legend>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label">From</label>
                <input v-model="form.col_from" type="text" maxlength="255" class="form-control" />
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">To</label>
                <input v-model="form.col_to" type="text" maxlength="255" class="form-control" />
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Material Lot No</label>
                <input v-model="form.material_lot_no" type="text" maxlength="255" class="form-control" />
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Job No / Transfer Order</label>
                <input v-model="form.job_no_transfer_order" type="text" maxlength="255" class="form-control" />
              </div>
            </div>
          </fieldset>

          <!-- Group 3: Serial Numbers -->
          <fieldset class="col-12">
            <legend class="fs-6">Serial Numbers</legend>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label">Start Serial</label>
                <input v-model="form.start_serial" type="text" maxlength="255" class="form-control" />
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">End Serial</label>
                <input v-model="form.end_serial" type="text" maxlength="255" class="form-control" />
              </div>
            </div>
          </fieldset>

          <!-- Group 4: Personnel -->
          <fieldset class="col-12">
            <legend class="fs-6">Personnel</legend>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label">Recorded By</label>
                <input v-model="form.recorded_by" type="text" maxlength="255" class="form-control" required />
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Source of Info</label>
                <input v-model="form.source_of_info" type="text" maxlength="255" class="form-control" />
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Approved By</label>
                <input v-model="form.approved_by" type="text" maxlength="255" class="form-control" />
              </div>
            </div>
          </fieldset>

          <!-- Group 5: Additional Info -->
          <fieldset class="col-12">
            <legend class="fs-6">Additional Information</legend>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label">4M</label>
                <input v-model="form.col_4m" type="text" maxlength="50" class="form-control" />
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label">Line</label>
                <input v-model="form.col_line" type="text" maxlength="50" class="form-control" />
              </div>
              <div class="col-12">
                <label class="form-label">Remarks</label>
                <textarea v-model="form.col_remarks" rows="2" class="form-control"></textarea>
              </div>
            </div>
          </fieldset>

          <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary" :disabled="form.processing">Update</button>
            <button type="button" class="btn btn-outline-danger" @click="confirmDelete" :disabled="form.processing">
              Delete
            </button>
            <div v-if="Object.keys(form.errors).length > 0" class="text-danger small">
              <div v-for="(msg, key) in form.errors" :key="key">{{ msg }}</div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
// import { confirmDialog } from '@/Composables/useConfirmDialog'

const props = defineProps({
  log: {
    type: Object,
    required: true
  }
})

const form = useForm({
  prod_date: props.log.prod_date,
  col_4m: props.log.col_4m || '',
  col_line: props.log.col_line || '',
  model_code: props.log.model_code,
  item_for_modification: props.log.item_for_modification,
  nature_of_change: props.log.nature_of_change || '',
  col_from: props.log.col_from || '',
  col_to: props.log.col_to || '',
  material_lot_no: props.log.material_lot_no || '',
  start_serial: props.log.start_serial || '',
  end_serial: props.log.end_serial || '',
  recorded_by: props.log.recorded_by,
  source_of_info: props.log.source_of_info || '',
  approved_by: props.log.approved_by || '',
  job_no_transfer_order: props.log.job_no_transfer_order || '',
  col_remarks: props.log.col_remarks || ''
})

function submit() {
  const csrf = document.querySelector('meta[name="csrf-token"]')
    ? document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    : ''
  form.transform((data) => ({ ...data, _token: csrf, _method: 'PUT' }))
    .post(`/modification-logs/${props.log.id}`)
}

async function confirmDelete() {
  if (confirm('Are you sure you want to delete this modification log? This action cannot be undone.')) {
    router.delete(`/modification-logs/${props.log.id}`, {
      onSuccess: () => {
        router.visit('/modification-logs')
      },
      onError: (errors) => {
        alert('Failed to delete the log. Please try again.')
        console.error('Delete error:', errors)
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
