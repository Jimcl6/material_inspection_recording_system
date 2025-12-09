<template>
  <div class="container py-4">
    <a href="/" class="btn btn-link p-0 mb-3">← Back to Batches</a>

    <div class="card">
      <div class="card-header"><strong>New Batch</strong></div>
      <div class="card-body">
        <form class="row g-3" @submit.prevent="submit">
          <div class="col-12 col-md-3">
            <label class="form-label">Production Date</label>
            <input v-model="form.ProductionDate" type="date" class="form-control" required />
          </div>
          <div class="col-12 col-md-2">
            <label class="form-label">Letter Code</label>
            <input v-model="form.LetterCode" type="text" maxlength="5" class="form-control" placeholder="auto" />
            <div class="form-text">Auto A..Z per date</div>
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">QR Code</label>
            <input v-model="form.QRCode" type="text" maxlength="20" class="form-control" required />
          </div>
          <div class="col-12 col-md-4">
            <label class="form-label">Material Lot Number</label>
            <input v-model="form.MaterialLotNumber" type="text" maxlength="50" class="form-control" required />
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Produce Qty</label>
            <input v-model.number="form.ProduceQty" type="number" min="0" class="form-control" required />
          </div>
          <div class="col-6 col-md-3">
            <label class="form-label">Total Qty</label>
            <input v-model.number="form.TotalQty" type="number" min="0" class="form-control" required />
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">Job Number</label>
            <input v-model="form.JobNumber" type="text" maxlength="50" class="form-control" required />
          </div>
          <div class="col-12">
            <label class="form-label">Remarks</label>
            <textarea v-model="form.Remarks" rows="3" class="form-control"></textarea>
          </div>

          <div class="col-12">
            <hr class="my-2" />
            <h6 class="mb-2">Optional: First Checkpoint and Samples</h6>
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">Checkpoint #</label>
            <input v-model.number="cp.CheckpointNumber" type="number" min="1" class="form-control" />
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">Inspector (First)</label>
            <input v-model="cp.InspectorName_First" type="text" class="form-control" />
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">Judgement (First)</label>
            <input v-model="cp.Judgement_First" type="text" class="form-control" />
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">Inspector (Last)</label>
            <input v-model="cp.InspectorName_Last" type="text" class="form-control" />
          </div>
          <div class="col-12 col-md-3">
            <label class="form-label">Judgement (Last)</label>
            <input v-model="cp.Judgement_Last" type="text" class="form-control" />
          </div>

          <div class="col-12 col-lg-6">
            <label class="form-label d-flex align-items-center justify-content-between">Samples (FIRST)
              <button class="btn btn-sm btn-outline-secondary" type="button" @click="addFirst">Add</button>
            </label>
            <div v-for="(v, i) in samplesFirst" :key="'f'+i" class="input-group mb-2">
              <span class="input-group-text" style="width:70px">#{{ i+1 }}</span>
              <input v-model="samplesFirst[i]" type="text" class="form-control" />
              <button class="btn btn-outline-danger" type="button" @click="removeFirst(i)">Del</button>
            </div>
          </div>
          <div class="col-12 col-lg-6">
            <label class="form-label d-flex align-items-center justify-content-between">Samples (LAST)
              <button class="btn btn-sm btn-outline-secondary" type="button" @click="addLast">Add</button>
            </label>
            <div v-for="(v, i) in samplesLast" :key="'l'+i" class="input-group mb-2">
              <span class="input-group-text" style="width:70px">#{{ i+1 }}</span>
              <input v-model="samplesLast[i]" type="text" class="form-control" />
              <button class="btn btn-outline-danger" type="button" @click="removeLast(i)">Del</button>
            </div>
          </div>
          <div class="col-12 d-flex gap-2">
            <button class="btn btn-primary" :disabled="form.processing">Create</button>
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
import { useForm } from '@inertiajs/vue3'
import { watch, ref, reactive } from 'vue'

const form = useForm({
  ProductionDate: '',
  LetterCode: '',
  QRCode: '',
  MaterialLotNumber: '',
  ProduceQty: 0,
  JobNumber: '',
  TotalQty: 0,
  Remarks: '',
})

// Default date to today on first load
if (!form.ProductionDate) {
  form.ProductionDate = new Date().toISOString().slice(0,10)
}

async function fetchNextLetter() {
  if (!form.ProductionDate) return
  try {
    const res = await fetch(`/batches/next-letter?date=${encodeURIComponent(form.ProductionDate)}`)
    const data = await res.json()
    if (!form.LetterCode) form.LetterCode = data.letter || ''
  } catch (e) {}
}

// Auto-fill letter when date changes IF user hasn't typed a letter
watch(() => form.ProductionDate, () => {
  if (!form.LetterCode) fetchNextLetter()
})

// Also try once on initial render
fetchNextLetter()

// Local state for checkpoint and samples within the same setup scope
const cp = reactive({
  CheckpointNumber: 1,
  InspectorName_First: '',
  Judgement_First: '',
  InspectorName_Last: '',
  Judgement_Last: '',
})
const samplesFirst = ref([])
const samplesLast = ref([])
function addFirst() { samplesFirst.value.push('') }
function removeFirst(i) { samplesFirst.value.splice(i,1) }
function addLast() { samplesLast.value.push('') }
function removeLast(i) { samplesLast.value.splice(i,1) }

function submit() {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
  const payload = {
    ...form,
    checkpoint: {
      ...cp,
      samples_first: samplesFirst.value,
      samples_last: samplesLast.value,
    },
  }
  form.transform(() => ({ ...payload, _token: csrf }))
      .post('/batches')
}
</script>
