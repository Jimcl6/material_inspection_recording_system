<template>
  <div class="modal fade" tabindex="-1" ref="modalEl">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Checkpoint</h5>
          <button type="button" class="btn-close" aria-label="Close" @click="close"></button>
        </div>
        <div class="modal-body">
          <form id="checkpointForm" class="row g-3" @submit.prevent="submit">
            <div class="col-12 col-md-3">
              <label class="form-label">Checkpoint #</label>
              <input v-model.number="form.CheckpointNumber" type="number" min="1" class="form-control" required />
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label">Inspector (First)</label>
              <input v-model="form.InspectorName_First" type="text" class="form-control" />
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label">Judgement (First)</label>
              <input v-model="form.Judgement_First" type="text" class="form-control" />
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label">Inspector (Last)</label>
              <input v-model="form.InspectorName_Last" type="text" class="form-control" />
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label">Judgement (Last)</label>
              <input v-model="form.Judgement_Last" type="text" class="form-control" />
            </div>

            <div class="col-12">
              <hr class="my-2" />
              <h6 class="mb-0">Samples</h6>
            </div>

            <div class="col-12 col-lg-6">
              <label class="form-label d-flex align-items-center justify-content-between">FIRST
                <button class="btn btn-sm btn-outline-secondary" type="button" @click="addFirst">Add</button>
              </label>
              <div v-for="(v, i) in form.samples_first" :key="'f'+i" class="input-group mb-2">
                <span class="input-group-text" style="width:70px">#{{ i+1 }}</span>
                <input v-model="form.samples_first[i]" type="text" class="form-control" />
                <button class="btn btn-outline-danger" type="button" @click="removeFirst(i)">Del</button>
              </div>
            </div>
            <div class="col-12 col-lg-6">
              <label class="form-label d-flex align-items-center justify-content-between">LAST
                <button class="btn btn-sm btn-outline-secondary" type="button" @click="addLast">Add</button>
              </label>
              <div v-for="(v, i) in form.samples_last" :key="'l'+i" class="input-group mb-2">
                <span class="input-group-text" style="width:70px">#{{ i+1 }}</span>
                <input v-model="form.samples_last[i]" type="text" class="form-control" />
                <button class="btn btn-outline-danger" type="button" @click="removeLast(i)">Del</button>
              </div>
            </div>

            <div class="col-12">
              <div class="text-danger small" v-if="form.errors && Object.keys(form.errors).length">
                <div v-for="(msg, key) in form.errors" :key="key">{{ msg }}</div>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" @click="close">Cancel</button>
          <button type="submit" form="checkpointForm" class="btn btn-primary" :disabled="form.processing || !batchId">Save</button>
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
  batchId: { type: [Number, String], required: false },
})
const emit = defineEmits(['update:modelValue', 'saved'])

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
  } else {
    bsModal?.hide()
  }
})

const form = useForm({
  CheckpointNumber: 1,
  InspectorName_First: '',
  Judgement_First: '',
  InspectorName_Last: '',
  Judgement_Last: '',
  samples_first: [],
  samples_last: [],
})

function addFirst() { form.samples_first.push('') }
function removeFirst(i) { form.samples_first.splice(i,1) }
function addLast() { form.samples_last.push('') }
function removeLast(i) { form.samples_last.splice(i,1) }

function focusFirst() {
  setTimeout(() => {
    const el = modalEl.value?.querySelector('input,select,textarea,button')
    el?.focus()
  }, 0)
}

function resetAll() {
  form.reset()
  form.CheckpointNumber = 1
  form.samples_first = []
  form.samples_last = []
}

function close() {
  emit('update:modelValue', false)
}

function submit() {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
  if (!props.batchId) return
  form.transform((data) => ({ ...data, _token: csrf, stay: 1 }))
      .post(`/batches/${props.batchId}/checkpoints`, {
        preserveScroll: true,
        onSuccess: () => {
          emit('saved')
          resetAll()
          close()
        },
      })
}
</script>
