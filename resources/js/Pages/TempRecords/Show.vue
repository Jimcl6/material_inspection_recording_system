<template>
  <div class="container py-4">
    <a href="/temp-records" class="btn btn-link p-0 mb-3">← Back to Temperature Records</a>
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <strong>Temperature Record #{{ record.id }}</strong>
        <a class="btn btn-sm btn-outline-secondary" :href="`/temp-records/${record.id}/edit`">Edit</a>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <h6 class="mb-2">Model Series</h6>
            <dl class="row mb-0">
              <dt class="col-sm-5">Model Series</dt>
              <dd class="col-sm-7">{{ record.model_series }}</dd>
            </dl>
          </div>
          <div class="col-12 col-md-6">
            <h6 class="mb-2">Date</h6>
            <dl class="row mb-0">
              <dt class="col-sm-5">Date</dt>
              <dd class="col-sm-7">{{ record.date }}</dd>
            </dl>
          </div>

          <div class="col-12">
            <h6 class="mb-2">Driver Details</h6>
            <dl class="row mb-0">
              <dt class="col-sm-4">Solder Model</dt>
              <dd class="col-sm-8">{{ record.solder_model }}</dd>
              <dt class="col-sm-4">Line Assigned</dt>
              <dd class="col-sm-8">{{ record.line_assigned }}</dd>
              <dt class="col-sm-4">Control No</dt>
              <dd class="col-sm-8">{{ record.control_no }}</dd>
              <dt class="col-sm-4">Process Assigned</dt>
              <dd class="col-sm-8">{{ record.process_assigned }}</dd>
              <dt class="col-sm-4">Person in Charge</dt>
              <dd class="col-sm-8">{{ record.person_in_charge }}</dd>
              <dt class="col-sm-4">Equipment Type</dt>
              <dd class="col-sm-8">{{ record.equipment_type }}</dd>
            </dl>
          </div>

          <div class="col-12">
            <h6 class="mb-2">Temperature Record</h6>
            <div class="row">
              <div class="col-12 col-lg-6">
                <dl class="row mb-0">
                  <dt class="col-sm-5">Time (AM)</dt>
                  <dd class="col-sm-7">{{ record.time_am }}</dd>
                  <dt class="col-sm-5">Temp (AM)</dt>
                  <dd class="col-sm-7">{{ record.temp_am }}</dd>
                </dl>
              </div>
              <div class="col-12 col-lg-6">
                <dl class="row mb-0">
                  <dt class="col-sm-5">Time (PM)</dt>
                  <dd class="col-sm-7">{{ record.time_pm }}</dd>
                  <dt class="col-sm-5">Temp (PM)</dt>
                  <dd class="col-sm-7">{{ record.temp_pm }}</dd>
                </dl>
              </div>
            </div>
          </div>

          <div class="col-12">
            <h6 class="mb-2">Remarks</h6>
            <p class="mb-0">{{ record.col_remarks }}</p>
          </div>

          <div class="col-12">
            <h6 class="mb-2">Checked By</h6>
            <p class="mb-0">{{ record.checked_by }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({ record: Object })
</script>
