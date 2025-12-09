<template>
  <div class="modal fade" tabindex="-1" ref="modalEl">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">New Batch</h5>
          <button type="button" class="btn-close" aria-label="Close" @click="close"></button>
        </div>
        <div class="modal-body">
          <form id="newBatchForm" class="row g-3" @submit.prevent="submit">
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
              <div class="text-danger small" v-if="form.errors && Object.keys(form.errors).length">
                <div v-for="(msg, key) in form.errors" :key="key">{{ msg }}</div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <div class="form-check me-auto">
            <input class="form-check-input" type="checkbox" id="openCheckpoint" v-model="openCheckpointAfter" />
            <label class="form-check-label" for="openCheckpoint">Open Checkpoint modal after create</label>
          </div>
          <button type="button" class="btn btn-secondary" @click="close">Cancel</button>
          <button type="submit" form="newBatchForm" class="btn btn-primary" :disabled="form.processing">Create</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Modal } from 'bootstrap'

const props = defineProps({
  modelValue: { type: Boolean, default: false },
})
const emit = defineEmits(['update:modelValue', 'created'])

const modalEl = ref(null)
let bsModal = null

onMounted(() => {
  if (modalEl.value) {
    bsModal = new Modal(modalEl.value, { backdrop: 'static', keyboard: false })
    modalEl.value.addEventListener('hidden.bs.modal', () => {
      emit('update:modelValue', false)
    })
    if (props.modelValue) nextTick(() => bsModal.show())
  }
})

onBeforeUnmount(() => {
  if (bsModal) { bsModal.hide(); bsModal.dispose(); bsModal = null }
})

watch(() => props.modelValue, async (val) => {
  if (!bsModal && modalEl.value) {
    bsModal = new Modal(modalEl.value, { backdrop: 'static', keyboard: false })
  }
  if (val) {
    await nextTick()
    bsModal?.show()
    focusFirst()
    setTodayIfEmpty()
    if (!form.LetterCode) fetchNextLetter()
  } else {
    bsModal?.hide()
  }
})

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

const openCheckpointAfter = ref(true)

function setTodayIfEmpty() {
  if (!form.ProductionDate) {
    form.ProductionDate = new Date().toISOString().slice(0,10)
  }
}

async function fetchNextLetter() {
  if (!form.ProductionDate) return
  try {
    const res = await fetch(`/batches/next-letter?date=${encodeURIComponent(form.ProductionDate)}`)
    const data = await res.json()
    if (!form.LetterCode) form.LetterCode = data.letter || ''
  } catch (e) {}
}

watch(() => form.ProductionDate, () => {
  if (!form.LetterCode) fetchNextLetter()
})

function focusFirst() {
  setTimeout(() => {
    const el = modalEl.value?.querySelector('input,select,textarea,button')
    el?.focus()
  }, 0)
}

function resetAll() {
  form.reset()
}

function close() {
  emit('update:modelValue', false)
}

function submit() {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
  const payload = { ...form, stay: 1 }
  form.transform(() => ({ ...payload, _token: csrf }))
      .post('/batches', {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          emit('created', { openCheckpoint: openCheckpointAfter.value })
          resetAll()
          close()
        },
      })
}
</script>
