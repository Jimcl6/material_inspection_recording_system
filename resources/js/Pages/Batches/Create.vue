<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { watch, ref, reactive } from 'vue';

// Import route helper
declare function route(name: string, params?: any): string;

const form = useForm({
  ProductionDate: '',
  LetterCode: '',
  QRCode: '',
  MaterialLotNumber: '',
  ProduceQty: 0,
  JobNumber: '',
  TotalQty: 0,
  Remarks: '',
});

// Default date to today on first load
if (!form.ProductionDate) {
  form.ProductionDate = new Date().toISOString().slice(0,10);
}

async function fetchNextLetter() {
  if (!form.ProductionDate) return;
  try {
    const res = await fetch(`/production-batches/next-letter?date=${encodeURIComponent(form.ProductionDate)}`);
    const data = await res.json();
    if (!form.LetterCode) form.LetterCode = data.letter || '';
  } catch (e) {}
}

// Auto-fill letter when date changes IF user hasn't typed a letter
watch(() => form.ProductionDate, () => {
  if (!form.LetterCode) fetchNextLetter();
});

// Also try once on initial render
fetchNextLetter();

// Local state for checkpoint and samples within the same setup scope
const cp = reactive({
  CheckpointNumber: 1,
  InspectorName_First: '',
  Judgement_First: '',
  InspectorName_Last: '',
  Judgement_Last: '',
});

const samplesFirst = ref<string[]>([]);
const samplesLast = ref<string[]>([]);

const addFirst = () => { samplesFirst.value.push(''); };
const removeFirst = (i: number) => { samplesFirst.value.splice(i, 1); };
const addLast = () => { samplesLast.value.push(''); };
const removeLast = (i: number) => { samplesLast.value.splice(i, 1); };

const submit = () => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const payload = {
    ...form,
    checkpoint: {
      ...cp,
      samples_first: samplesFirst.value,
      samples_last: samplesLast.value,
    },
  };
  form.transform(() => ({ ...payload, _token: csrf }))
      .post('/production-batches');
};
</script>

<template>
    <Head title="Create Production Batch" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create Production Batch
                </h2>
                <Link 
                    :href="route('production-batches.index')" 
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Batches
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Basic Information Section -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Production Date</label>
                                        <input
                                            v-model="form.ProductionDate"
                                            type="date"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Letter Code</label>
                                        <input
                                            disable
                                            v-model="form.LetterCode"
                                            type="text"
                                            maxlength="5"
                                            placeholder="auto"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                        <p class="mt-1 text-sm text-gray-500">Auto A..Z per date</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">QR Code</label>
                                        <input
                                            v-model="form.QRCode"
                                            type="text"
                                            maxlength="20"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Material Lot Number</label>
                                        <input
                                            v-model="form.MaterialLotNumber"
                                            type="text"
                                            maxlength="50"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            required
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Quantity Information Section -->
                            <div>
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Quantity Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Produce Qty</label>
                                        <input
                                            v-model.number="form.ProduceQty"
                                            type="number"
                                            min="0"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Total Qty</label>
                                        <input
                                            v-model.number="form.TotalQty"
                                            type="number"
                                            min="0"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            required
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Job Number</label>
                                        <input
                                            v-model="form.JobNumber"
                                            type="text"
                                            maxlength="50"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                            required
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Remarks Section -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                                <textarea
                                    v-model="form.Remarks"
                                    rows="3"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                ></textarea>
                            </div>

                            <!-- Optional Checkpoint Section -->
                            <div class="border-t pt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Optional: First Checkpoint and Samples</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Checkpoint #</label>
                                        <input
                                            v-model.number="cp.CheckpointNumber"
                                            type="number"
                                            min="1"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Inspector (First)</label>
                                        <input
                                            v-model="cp.InspectorName_First"
                                            type="text"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Judgement (First)</label>
                                        <input
                                            v-model="cp.Judgement_First"
                                            type="text"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Inspector (Last)</label>
                                        <input
                                            v-model="cp.InspectorName_Last"
                                            type="text"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Judgement (Last)</label>
                                        <input
                                            v-model="cp.Judgement_Last"
                                            type="text"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                    </div>
                                </div>

                                <!-- Samples Section -->
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    <div>
                                        <div class="flex justify-between items-center mb-2">
                                            <label class="block text-sm font-medium text-gray-700">Samples (FIRST)</label>
                                            <button
                                                type="button"
                                                @click="addFirst"
                                                class="inline-flex items-center px-3 py-1 bg-indigo-600 border border-transparent rounded-md text-xs font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            >
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                                Add
                                            </button>
                                        </div>
                                        <div class="space-y-2">
                                            <div v-for="(sample, i) in samplesFirst" :key="'f' + i" class="flex items-center space-x-2">
                                                <span class="inline-flex items-center justify-center w-12 h-10 rounded-md border border-gray-300 bg-gray-50 text-sm font-medium text-gray-700">
                                                    #{{ i + 1 }}
                                                </span>
                                                <input
                                                    v-model="samplesFirst[i]"
                                                    type="text"
                                                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                />
                                                <button
                                                    type="button"
                                                    @click="removeFirst(i)"
                                                    class="inline-flex items-center p-2 border border-transparent rounded-md text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="flex justify-between items-center mb-2">
                                            <label class="block text-sm font-medium text-gray-700">Samples (LAST)</label>
                                            <button
                                                type="button"
                                                @click="addLast"
                                                class="inline-flex items-center px-3 py-1 bg-indigo-600 border border-transparent rounded-md text-xs font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                            >
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                                Add
                                            </button>
                                        </div>
                                        <div class="space-y-2">
                                            <div v-for="(sample, i) in samplesLast" :key="'l' + i" class="flex items-center space-x-2">
                                                <span class="inline-flex items-center justify-center w-12 h-10 rounded-md border border-gray-300 bg-gray-50 text-sm font-medium text-gray-700">
                                                    #{{ i + 1 }}
                                                </span>
                                                <input
                                                    v-model="samplesLast[i]"
                                                    type="text"
                                                    class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                />
                                                <button
                                                    type="button"
                                                    @click="removeLast(i)"
                                                    class="inline-flex items-center p-2 border border-transparent rounded-md text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex justify-between items-center pt-6 border-t">
                                <div class="text-sm text-red-600" v-if="form.errors && Object.keys(form.errors).length">
                                    <div v-for="(msg, key) in form.errors" :key="key" class="mb-1">
                                        {{ msg }}
                                    </div>
                                </div>
                                <div class="flex space-x-3">
                                    <Link
                                        :href="route('production-batches.index')"
                                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        Cancel
                                    </Link>
                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                        :disabled="form.processing"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Create Batch
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
