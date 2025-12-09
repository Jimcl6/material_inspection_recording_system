<template>
  <div class="container py-4">
    <a href="/" class="btn btn-link p-0 mb-3">← Back to Batches</a>

    <div class="row g-3">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <h1 class="h4">Batch #{{ batch.BatchID }}</h1>
            <div class="row mt-3">
              <div class="col-md-6">
                <dl class="row mb-0">
                  <dt class="col-sm-5">Production Date</dt>
                  <dd class="col-sm-7">{{ batch.ProductionDate }}</dd>
                  <dt class="col-sm-5">Letter Code</dt>
                  <dd class="col-sm-7">{{ batch.LetterCode }}</dd>
                  <dt class="col-sm-5">QR Code</dt>
                  <dd class="col-sm-7">{{ batch.QRCode }}</dd>
                  <dt class="col-sm-5">Lot Number</dt>
                  <dd class="col-sm-7">{{ batch.MaterialLotNumber }}</dd>
                </dl>
              </div>
              <div class="col-md-6">
                <dl class="row mb-0">
                  <dt class="col-sm-5">Produce Qty</dt>
                  <dd class="col-sm-7">{{ batch.ProduceQty }}</dd>
                  <dt class="col-sm-5">Job Number</dt>
                  <dd class="col-sm-7">{{ batch.JobNumber }}</dd>
                  <dt class="col-sm-5">Total Qty</dt>
                  <dd class="col-sm-7">{{ batch.TotalQty }}</dd>
                  <dt class="col-sm-5">Remarks</dt>
                  <dd class="col-sm-7">{{ batch.Remarks }}</dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <h2 class="h5 m-0">Checkpoints</h2>
          </div>
          <div class="card-body">
            <form class="row g-2 align-items-end mb-3" @submit.prevent="submitAdd">
              <div class="col-12 col-md-2">
                <label class="form-label">Checkpoint #</label>
                <input v-model="form.CheckpointNumber" type="number" min="1" class="form-control" required />
              </div>
              <div class="col-12 col-md-2">
                <label class="form-label">Inspector (First)</label>
                <input v-model="form.InspectorName_First" type="text" class="form-control" />
              </div>
              <div class="col-12 col-md-2">
                <label class="form-label">Judgement (First)</label>
                <input v-model="form.Judgement_First" type="text" class="form-control" />
              </div>
              <div class="col-12 col-md-2">
                <label class="form-label">Inspector (Last)</label>
                <input v-model="form.InspectorName_Last" type="text" class="form-control" />
              </div>
              <div class="col-12 col-md-2">
                <label class="form-label">Judgement (Last)</label>
                <input v-model="form.Judgement_Last" type="text" class="form-control" />
              </div>
              <div class="col-12 col-md-2 d-grid">
                <button class="btn btn-primary" :disabled="form.processing">Add</button>
              </div>
              <div class="col-12">
                <div class="text-danger small" v-if="form.errors && Object.keys(form.errors).length">
                  <div v-for="(msg, key) in form.errors" :key="key">{{ msg }}</div>
                </div>
              </div>
            </form>

            <div class="table-responsive">
              <table class="table table-striped table-hover mb-0">
                <thead>
                  <tr>
                    <th>ID</th>
                    <th>#</th>
                    <th>Inspector (First)</th>
                    <th>Judgement (First)</th>
                    <th>Inspector (Last)</th>
                    <th>Judgement (Last)</th>
                    <th>Samples</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="c in checkpoints" :key="c.CheckpointID">
                    <td>{{ c.CheckpointID }}</td>
                    <td>{{ c.CheckpointNumber }}</td>
                    <td>{{ c.InspectorName_First }}</td>
                    <td>{{ c.Judgement_First }}</td>
                    <td>{{ c.InspectorName_Last }}</td>
                    <td>{{ c.Judgement_Last }}</td>
                    <td>{{ c.samples_count }}</td>
                    <td class="text-end">
                      <a class="btn btn-sm btn-outline-primary me-2" :href="`/checkpoints/${c.CheckpointID}/edit`">Manage</a>
                      <button class="btn btn-sm btn-outline-danger" @click.prevent="destroyCheckpoint(c.CheckpointID)">Delete</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  batch: Object,
  checkpoints: Array,
});

import { useForm, router } from '@inertiajs/vue3'
const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''

const form = useForm({
  CheckpointNumber: '',
  InspectorName_First: '',
  Judgement_First: '',
  InspectorName_Last: '',
  Judgement_Last: '',
})

function submitAdd() {
  form.transform((data) => ({ ...data, _token: csrf }))
      .post(`/batches/${props.batch.BatchID}/checkpoints`, {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
}

function destroyCheckpoint(id) {
  if (confirm('Delete this checkpoint?')) {
    router.post(`/checkpoints/${id}`, { _method: 'delete', _token: csrf }, { preserveScroll: true })
  }
}
</script>
