<template>
  <div class="container py-4">
    <a href="/modification-logs" class="btn btn-link p-0 mb-3">← Back to Modification Logs</a>

    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Modification Log #{{ log.id }}</h5>
        <div class="btn-group">
          <a :href="`/modification-logs/${log.id}/edit`" class="btn btn-sm btn-outline-primary">
            Edit
          </a>
        </div>
      </div>
      <div class="card-body">
        <div class="row g-4">
          <!-- Basic Information -->
          <div class="col-12">
            <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label text-muted small mb-1">Production Date/Time</label>
                <div class="fw-normal">{{ formatDateTime(log.prod_date) }}</div>
              </div>
              <div class="col-12 col-md-6">
                <label class="form-label text-muted small mb-1">Model Code</label>
                <div class="fw-normal">{{ log.model_code }}</div>
              </div>
              <div class="col-12">
                <label class="form-label text-muted small mb-1">Item for Modification</label>
                <div class="fw-normal">{{ log.item_for_modification }}</div>
              </div>
              <div class="col-12" v-if="log.nature_of_change">
                <label class="form-label text-muted small mb-1">Nature of Change</label>
                <div class="fw-normal text-pre-wrap">{{ log.nature_of_change }}</div>
              </div>
            </div>
          </div>

          <!-- Modification Details -->
          <div class="col-12">
            <h6 class="border-bottom pb-2 mb-3">Modification Details</h6>
            <div class="row g-3">
              <div class="col-12 col-md-6" v-if="log.col_from">
                <label class="form-label text-muted small mb-1">From</label>
                <div class="fw-normal">{{ log.col_from }}</div>
              </div>
              <div class="col-12 col-md-6" v-if="log.col_to">
                <label class="form-label text-muted small mb-1">To</label>
                <div class="fw-normal">{{ log.col_to }}</div>
              </div>
              <div class="col-12 col-md-6" v-if="log.material_lot_no">
                <label class="form-label text-muted small mb-1">Material Lot No</label>
                <div class="fw-normal">{{ log.material_lot_no }}</div>
              </div>
              <div class="col-12 col-md-6" v-if="log.job_no_transfer_order">
                <label class="form-label text-muted small mb-1">Job No / Transfer Order</label>
                <div class="fw-normal">{{ log.job_no_transfer_order }}</div>
              </div>
            </div>
          </div>

          <!-- Serial Numbers -->
          <div class="col-12" v-if="log.start_serial || log.end_serial">
            <h6 class="border-bottom pb-2 mb-3">Serial Numbers</h6>
            <div class="row g-3">
              <div class="col-12 col-md-6" v-if="log.start_serial">
                <label class="form-label text-muted small mb-1">Start Serial</label>
                <div class="fw-normal">{{ log.start_serial }}</div>
              </div>
              <div class="col-12 col-md-6" v-if="log.end_serial">
                <label class="form-label text-muted small mb-1">End Serial</label>
                <div class="fw-normal">{{ log.end_serial }}</div>
              </div>
            </div>
          </div>

          <!-- Personnel -->
          <div class="col-12">
            <h6 class="border-bottom pb-2 mb-3">Personnel</h6>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label class="form-label text-muted small mb-1">Recorded By</label>
                <div class="fw-normal">{{ log.recorded_by }}</div>
              </div>
              <div class="col-12 col-md-6" v-if="log.source_of_info">
                <label class="form-label text-muted small mb-1">Source of Info</label>
                <div class="fw-normal">{{ log.source_of_info }}</div>
              </div>
              <div class="col-12 col-md-6" v-if="log.approved_by">
                <label class="form-label text-muted small mb-1">Approved By</label>
                <div class="fw-normal">{{ log.approved_by }}</div>
              </div>
            </div>
          </div>

          <!-- Additional Information -->
          <div class="col-12" v-if="log.col_4m || log.col_line || log.col_remarks">
            <h6 class="border-bottom pb-2 mb-3">Additional Information</h6>
            <div class="row g-3">
              <div class="col-12 col-md-6" v-if="log.col_4m">
                <label class="form-label text-muted small mb-1">4M</label>
                <div class="fw-normal">{{ log.col_4m }}</div>
              </div>
              <div class="col-12 col-md-6" v-if="log.col_line">
                <label class="form-label text-muted small mb-1">Line</label>
                <div class="fw-normal">{{ log.col_line }}</div>
              </div>
              <div class="col-12" v-if="log.col_remarks">
                <label class="form-label text-muted small mb-1">Remarks</label>
                <div class="fw-normal text-pre-wrap">{{ log.col_remarks }}</div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-4 pt-3 border-top d-flex justify-content-between">
          <div class="text-muted small">
            Created at: {{ formatDateTime(log.created_at) }}
            <span v-if="log.updated_at !== log.created_at" class="ms-3">
              Updated at: {{ formatDateTime(log.updated_at) }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { format } from 'date-fns'

const props = defineProps({
  log: {
    type: Object,
    required: true
  }
})

function formatDateTime(dateTime) {
  if (!dateTime) return 'N/A'
  try {
    // First try parsing as ISO string
    const date = new Date(dateTime)
    if (isNaN(date.getTime())) {
      // If not a valid date, try parsing as custom format (d/m/Y H:i)
      const [datePart, timePart] = dateTime.split(' ')
      const [day, month, year] = datePart.split('/')
      const formattedDate = `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}${timePart ? 'T' + timePart : ''}`
      return format(new Date(formattedDate), 'dd/MM/yyyy HH:mm')
    }
    return format(date, 'dd/MM/yyyy HH:mm')
  } catch (e) {
    console.error('Error formatting date:', e)
    return dateTime // Return original if can't parse
  }
}
</script>

<style scoped>
.text-pre-wrap {
  white-space: pre-wrap;
  word-break: break-word;
}
</style>
