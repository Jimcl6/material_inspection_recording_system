<template>
  <div class="container py-4">
    <a href="/torque-records" class="btn btn-link p-0 mb-3">← Back to Torque Records</a>
    <div class="card">
      <div class="card-header d-flex align-items-center justify-content-between">
        <strong>Torque Record #{{ record.id }}</strong>
        <a class="btn btn-sm btn-outline-secondary" :href="`/torque-records/${record.id}/edit`">Edit</a>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-12 col-md-6">
            <h6 class="mb-2">Driver Info</h6>
            <dl class="row mb-0">
              <dt class="col-sm-5">Driver Model</dt>
              <dd class="col-sm-7">{{ record.driver_model }}</dd>
              <dt class="col-sm-5">Driver Type</dt>
              <dd class="col-sm-7">{{ record.driver_type }}</dd>
            </dl>
          </div>
          <div class="col-12 col-md-6">
            <h6 class="mb-2">Assignment</h6>
            <dl class="row mb-0">
              <dt class="col-sm-5">Line Assigned</dt>
              <dd class="col-sm-7">{{ record.line_assigned }}</dd>
              <dt class="col-sm-5">Control No</dt>
              <dd class="col-sm-7">{{ record.control_no }}</dd>
              <dt class="col-sm-5">Process Assigned</dt>
              <dd class="col-sm-7">{{ record.process_assigned }}</dd>
            </dl>
          </div>
          <div class="col-12">
            <h6 class="mb-2">Torque Record</h6>
            <div class="row">
              <div class="col-12 col-lg-6">
                <dl class="row mb-0">
                  <dt class="col-sm-5">Time (AM)</dt>
                  <dd class="col-sm-7">{{ record.time_am }}</dd>
                  <dt class="col-sm-5">Torque (AM)</dt>
                  <dd class="col-sm-7">{{ record.torque_am }}</dd>
                </dl>
              </div>
              <div class="col-12 col-lg-6">
                <dl class="row mb-0">
                  <dt class="col-sm-5">Time (PM)</dt>
                  <dd class="col-sm-7">{{ record.time_pm }}</dd>
                  <dt class="col-sm-5">Torque (PM)</dt>
                  <dd class="col-sm-7">{{ record.torque_pm }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({ record: Object })
</script>
