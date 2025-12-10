<template>
  <div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h1 class="h3 m-0">Production Batches</h1>
      <button class="btn btn-primary" @click="showNew = true">New Batch</button>
    </div>

    <div class="card mb-3">
      <div class="card-body">
        <form class="row g-2 align-items-end" @submit.prevent="applyFilters">
          <div class="col-12 col-md-3">
            <label class="form-label">Search</label>
            <input v-model="filters.q" type="text" class="form-control" placeholder="QR / Lot / Job #" />
          </div>
          <div class="col-12 col-md-2">
            <label class="form-label">QR Code</label>
            <input v-model="filters.qr" type="text" class="form-control" />
          </div>
          <div class="col-12 col-md-2">
            <label class="form-label">Lot</label>
            <input v-model="filters.lot" type="text" class="form-control" />
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Date From</label>
            <input v-model="filters.date_from" type="date" class="form-control" />
          </div>
          <div class="col-6 col-md-2">
            <label class="form-label">Date To</label>
            <input v-model="filters.date_to" type="date" class="form-control" />
          </div>
          <div class="col-12 col-md-1 d-grid">
            <button class="btn btn-outline-primary">Filter</button>
          </div>
        </form>
      </div>
    </div>

    <div class="card">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-hover mb-0">
            <thead>
              <tr>
                <th>ID</th>
                <th>Date</th>
                <th>Letter</th>
                <th>QR Code</th>
                <th>Lot</th>
                <th>Produce Qty</th>
                <th>Job #</th>
                <th>Total Qty</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="b in batches.data" :key="b.BatchID">
                <td>{{ b.BatchID }}</td>
                <td>{{ b.ProductionDate }}</td>
                <td>{{ b.LetterCode }}</td>
                <td>{{ b.QRCode }}</td>
                <td>{{ b.MaterialLotNumber }}</td>
                <td>{{ b.ProduceQty }}</td>
                <td>{{ b.JobNumber }}</td>
                <td>{{ b.TotalQty }}</td>
                <td class="text-end">
                  <div class="dropdown dropup d-inline-block position-static">
                    <button
                      class="btn btn-sm btn-outline-secondary dropdown-toggle"
                      type="button"
                      data-bs-toggle="dropdown"
                      data-bs-display="static"
                      data-bs-boundary="viewport"
                      aria-expanded="false">
                      Actions
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                        <a class="dropdown-item" :href="`batches/${b.BatchID}`">View</a>
                      </li>
                      <li>
                        <button class="dropdown-item" @click.prevent="openEdit(b)">Edit</button>
                      </li>
                      <li>
                        <button class="dropdown-item" @click.prevent="openCheckpoint(b.BatchID)">Add Checkpoint</button>
                      </li>
                      <li><hr class="dropdown-divider" /></li>
                      <li>
                        <button class="dropdown-item text-danger" @click.prevent="destroyBatch(b.BatchID)">Delete</button>
                      </li>
                    </ul>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="card-footer">
        <nav v-if="batches.links && batches.links.length">
          <ul class="pagination mb-0">
            <li v-for="(l, idx) in batches.links" :key="idx" class="page-item" :class="{ active: l.active, disabled: !l.url }">
              <a class="page-link" :href="l.url || '#'" v-html="l.label"></a>
            </li>
          </ul>
        </nav>
      </div>
    </div>
    <NewBatchModal v-model="showNew" @created="onCreated" />
    <CheckpointModal v-model="showCheckpoint" :batch-id="newBatchId" @saved="refreshList" />
    <EditBatchModal v-model="showEdit" :batch="selectedBatch" @updated="refreshList" />
  </div>
</template>

<script setup>
const props = defineProps({
  batches: Object,
  filters: Object,
});

import { reactive, ref, onMounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import NewBatchModal from './NewBatchModal.vue'
import CheckpointModal from './CheckpointModal.vue'
import EditBatchModal from './EditBatchModal.vue'

const filters = reactive({
  q: props.filters?.q || '',
  qr: props.filters?.qr || '',
  lot: props.filters?.lot || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
})

const showNew = ref(false)
const showCheckpoint = ref(false)
const newBatchId = ref(null)
let shouldOpenCheckpoint = false
const page = usePage()
const showEdit = ref(false)
const selectedBatch = ref(null)

function applyFilters() {
  router.get(window.location.pathname, { ...filters }, { preserveState: true, replace: true })
}

function destroyBatch(id) {
  if (confirm('Delete this batch?')) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    router.post(`/batches/${id}`, { _method: 'delete', _token: csrf }, { preserveScroll: true })
  }
}

function onCreated(payload = {}) {
  shouldOpenCheckpoint = !!payload.openCheckpoint
  router.get(window.location.pathname, { ...filters }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
    onSuccess: () => {
      const id = page?.props?.flash?.new_batch_id
      if (id) {
        newBatchId.value = id
        if (shouldOpenCheckpoint) showCheckpoint.value = true
      }
    }
  })
}

function openCheckpoint(batchId) {
  newBatchId.value = batchId
  showCheckpoint.value = true
}

function refreshList() {
  router.get(window.location.pathname, { ...filters }, { preserveState: true, preserveScroll: true, replace: true })
}

function openEdit(batch) {
  selectedBatch.value = { ...batch }
  showEdit.value = true
}

onMounted(() => {
  const flashed = page?.props?.flash
  if (flashed?.edit_batch && flashed?.edit_batch_id) {
    selectedBatch.value = flashed.edit_batch
    showEdit.value = true
  }
})
</script>
