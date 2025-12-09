<template>
  <div class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div>
        <a :href="`/batches/${batch.BatchID}`" class="btn btn-link p-0">← Back to Batch</a>
        <h1 class="h4 mt-2 mb-0">Checkpoint #{{ checkpoint.CheckpointNumber }}</h1>
        <div class="text-muted small">Batch #{{ batch.BatchID }} • {{ batch.ProductionDate }} • {{ batch.LetterCode }} • QR: {{ batch.QRCode }}</div>
      </div>
    </div>

    <div class="row g-3">
      <div class="col-12">
        <div class="card">
          <div class="card-header"><strong>Checkpoint Details</strong></div>
          <div class="card-body">
            <form class="row g-2" @submit.prevent="updateCheckpoint">
              <div class="col-12 col-md-3">
                <label class="form-label">Checkpoint #</label>
                <input v-model.number="cpForm.CheckpointNumber" type="number" min="1" class="form-control" required />
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label">Inspector (First)</label>
                <input v-model="cpForm.InspectorName_First" type="text" class="form-control" />
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label">Judgement (First)</label>
                <input v-model="cpForm.Judgement_First" type="text" class="form-control" />
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label">Inspector (Last)</label>
                <input v-model="cpForm.InspectorName_Last" type="text" class="form-control" />
              </div>
              <div class="col-12 col-md-3">
                <label class="form-label">Judgement (Last)</label>
                <input v-model="cpForm.Judgement_Last" type="text" class="form-control" />
              </div>
              <div class="col-12">
                <button class="btn btn-primary" :disabled="cpForm.processing">Save Details</button>
                <span class="text-success ms-2" v-if="$page.props.flash?.success">{{ $page.props.flash.success }}</span>
              </div>
              <div class="col-12">
                <div class="text-danger small" v-if="cpForm.errors && Object.keys(cpForm.errors).length">
                  <div v-for="(msg, key) in cpForm.errors" :key="key">{{ msg }}</div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex align-items-center justify-content-between">
            <strong>Samples</strong>
          </div>
          <div class="card-body">
            <form class="row g-2 align-items-end mb-3" @submit.prevent="addSample">
              <div class="col-12 col-md-2">
                <label class="form-label">Phase</label>
                <select v-model="addForm.Phase" class="form-select" required>
                  <option value="FIRST">FIRST</option>
                  <option value="LAST">LAST</option>
                </select>
              </div>
              <div class="col-12 col-md-2">
                <label class="form-label">Order</label>
                <input v-model.number="addForm.SampleOrder" type="number" min="1" class="form-control" required />
              </div>
              <div class="col-12 col-md-4">
                <label class="form-label">Value</label>
                <input v-model="addForm.Value" type="text" class="form-control" maxlength="50" required />
              </div>
              <div class="col-12 col-md-2 d-grid">
                <button class="btn btn-success" :disabled="addForm.processing">Add Sample</button>
              </div>
              <div class="col-12">
                <div class="text-danger small" v-if="addForm.errors && Object.keys(addForm.errors).length">
                  <div v-for="(msg, key) in addForm.errors" :key="key">{{ msg }}</div>
                </div>
              </div>
            </form>

            <div class="row g-3">
              <div class="col-12 col-lg-6">
                <h6>FIRST</h6>
                <div class="table-responsive">
                  <table class="table table-sm align-middle">
                    <thead>
                      <tr>
                        <th>Order</th>
                        <th>Value</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="s in samplesFirst" :key="s.SampleID">
                        <td style="width: 90px;">
                          <input v-model.number="editRows[s.SampleID].SampleOrder" type="number" min="1" class="form-control form-control-sm" />
                        </td>
                        <td>
                          <input v-model="editRows[s.SampleID].Value" type="text" maxlength="50" class="form-control form-control-sm" />
                        </td>
                        <td class="text-end" style="width: 160px;">
                          <button class="btn btn-sm btn-outline-primary me-2" @click="saveSample(s.SampleID)">Save</button>
                          <button class="btn btn-sm btn-outline-danger" @click="deleteSample(s.SampleID)">Delete</button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
              <div class="col-12 col-lg-6">
                <h6>LAST</h6>
                <div class="table-responsive">
                  <table class="table table-sm align-middle">
                    <thead>
                      <tr>
                        <th>Order</th>
                        <th>Value</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="s in samplesLast" :key="s.SampleID">
                        <td style="width: 90px;">
                          <input v-model.number="editRows[s.SampleID].SampleOrder" type="number" min="1" class="form-control form-control-sm" />
                        </td>
                        <td>
                          <input v-model="editRows[s.SampleID].Value" type="text" maxlength="50" class="form-control form-control-sm" />
                        </td>
                        <td class="text-end" style="width: 160px;">
                          <button class="btn btn-sm btn-outline-primary me-2" @click="saveSample(s.SampleID)">Save</button>
                          <button class="btn btn-sm btn-outline-danger" @click="deleteSample(s.SampleID)">Delete</button>
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
    </div>
  </div>
</template>

<script setup>
import { computed, reactive } from 'vue'
import { useForm, router } from '@inertiajs/vue3'

const props = defineProps({
  batch: Object,
  checkpoint: Object,
  samples: Array,
})

const cpForm = useForm({
  CheckpointNumber: props.checkpoint.CheckpointNumber,
  InspectorName_First: props.checkpoint.InspectorName_First || '',
  Judgement_First: props.checkpoint.Judgement_First || '',
  InspectorName_Last: props.checkpoint.InspectorName_Last || '',
  Judgement_Last: props.checkpoint.Judgement_Last || '',
})

function updateCheckpoint() {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
  cpForm.transform((data) => ({ ...data, _method: 'put', _token: csrf }))
       .post(`/checkpoints/${props.checkpoint.CheckpointID}`)
}

const addForm = useForm({
  Phase: 'FIRST',
  SampleOrder: '',
  Value: '',
})

function addSample() {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
  addForm.transform((data) => ({ ...data, _token: csrf }))
        .post(`/checkpoints/${props.checkpoint.CheckpointID}/samples`, {
    preserveScroll: true,
    onSuccess: () => addForm.reset('SampleOrder', 'Value'),
  })
}

const editRows = reactive({})
props.samples.forEach(s => {
  editRows[s.SampleID] = { SampleOrder: s.SampleOrder, Value: s.Value }
})

const samplesFirst = computed(() => props.samples.filter(s => s.Phase === 'FIRST'))
const samplesLast = computed(() => props.samples.filter(s => s.Phase === 'LAST'))

function saveSample(id) {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
  const payload = { ...editRows[id], _method: 'put', _token: csrf }
  router.post(`/samples/${id}`, payload, { preserveScroll: true })
}

function deleteSample(id) {
  if (confirm('Delete this sample?')) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    router.post(`/samples/${id}`, { _method: 'delete', _token: csrf }, { preserveScroll: true })
  }
}
</script>
