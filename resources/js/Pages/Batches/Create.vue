<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { watch, ref, reactive } from 'vue';

// Import route helper
declare function route(name: string, params?: any): string;

// Checkpoint position labels
const CHECKPOINT_LABELS = ['Front 1', 'Front 2', 'Back 1', 'Back 2'];

const form = useForm({
  ProductionDate: '',
  LetterCode: '',
  QRCode: '',
  MaterialLotNumber: '',
  ProduceQty: 0,
  JobNumber: '',
  TotalQty: 0,
  Remarks: '',
  ItemName: '',
  ItemCode: '',
});

// Default date to today on first load
if (!form.ProductionDate) {
  form.ProductionDate = new Date().toISOString().slice(0,10);
}

async function fetchNextLetter() {
  if (!form.ProductionDate) return;
  try {
    const res = await fetch(`/magnetism-checksheet/next-letter?date=${encodeURIComponent(form.ProductionDate)}`);
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

// Inspector names (shared across all checkpoints per phase)
const inspectorFirst = ref('');
const inspectorLast = ref('');

// Fixed 4 checkpoints with 5 samples each for First and Last inspection
interface CheckpointData {
  CheckpointNumber: number;
  label: string;
  Judgement_First: string;
  Judgement_Last: string;
  samples_first: string[];
  samples_last: string[];
}

const checkpoints = reactive<CheckpointData[]>([
  { CheckpointNumber: 1, label: 'Front 1', Judgement_First: '', Judgement_Last: '', samples_first: ['', '', '', '', ''], samples_last: ['', '', '', '', ''] },
  { CheckpointNumber: 2, label: 'Front 2', Judgement_First: '', Judgement_Last: '', samples_first: ['', '', '', '', ''], samples_last: ['', '', '', '', ''] },
  { CheckpointNumber: 3, label: 'Back 1', Judgement_First: '', Judgement_Last: '', samples_first: ['', '', '', '', ''], samples_last: ['', '', '', '', ''] },
  { CheckpointNumber: 4, label: 'Back 2', Judgement_First: '', Judgement_Last: '', samples_first: ['', '', '', '', ''], samples_last: ['', '', '', '', ''] },
]);

const submit = () => {
  const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  
  // Build checkpoints array with inspector names
  const checkpointsPayload = checkpoints.map(cp => ({
    CheckpointNumber: cp.CheckpointNumber,
    InspectorName_First: inspectorFirst.value,
    Judgement_First: cp.Judgement_First,
    InspectorName_Last: inspectorLast.value,
    Judgement_Last: cp.Judgement_Last,
    samples_first: cp.samples_first,
    samples_last: cp.samples_last,
  }));

  const payload = {
    ...form.data(),
    checkpoints: checkpointsPayload,
  };
  
  form.transform(() => ({ ...payload, _token: csrf }))
      .post('/magnetism-checksheet');
};
</script>

<template>
    <Head title="Create Magnetism Checksheet" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create Magnetism Checksheet
                </h2>
                <Link 
                    :href="route('magnetism-checksheet.index')" 
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Checksheets
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto">
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
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
                                        <input
                                            v-model="form.ItemName"
                                            type="text"
                                            maxlength="50"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Item Code</label>
                                        <input
                                            v-model="form.ItemCode"
                                            type="text"
                                            maxlength="50"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
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

                            <!-- Inspection Samples Grid Section -->
                            <div class="border-t pt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Inspection Samples</h3>
                                
                                <!-- Inspector Names -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Inspector (First Inspection)</label>
                                        <input
                                            v-model="inspectorFirst"
                                            type="text"
                                            placeholder="Enter inspector name"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Inspector (Last Inspection)</label>
                                        <input
                                            v-model="inspectorLast"
                                            type="text"
                                            placeholder="Enter inspector name"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                        />
                                    </div>
                                </div>

                                <!-- Samples Grid -->
                                <div class="overflow-x-auto">
                                    <table class="min-w-full border border-gray-200 rounded-lg">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th rowspan="2" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b border-r border-gray-200 align-middle">
                                                    Checkpoint
                                                </th>
                                                <th colspan="6" class="px-3 py-2 text-center text-xs font-medium text-blue-600 uppercase tracking-wider border-b border-r border-gray-200 bg-blue-50">
                                                    First Inspection (N=5)
                                                </th>
                                                <th colspan="6" class="px-3 py-2 text-center text-xs font-medium text-green-600 uppercase tracking-wider border-b border-gray-200 bg-green-50">
                                                    Last Inspection (N=5)
                                                </th>
                                            </tr>
                                            <tr>
                                                <th v-for="n in 5" :key="'fh'+n" class="px-2 py-1 text-center text-xs font-medium text-gray-500 border-b border-r border-gray-200 bg-blue-50 w-16">
                                                    {{ n }}
                                                </th>
                                                <th class="px-2 py-1 text-center text-xs font-medium text-gray-500 border-b border-r border-gray-200 bg-blue-50 w-20">
                                                    Judge
                                                </th>
                                                <th v-for="n in 5" :key="'lh'+n" class="px-2 py-1 text-center text-xs font-medium text-gray-500 border-b border-r border-gray-200 bg-green-50 w-16">
                                                    {{ n }}
                                                </th>
                                                <th class="px-2 py-1 text-center text-xs font-medium text-gray-500 border-b border-gray-200 bg-green-50 w-20">
                                                    Judge
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            <tr v-for="cp in checkpoints" :key="cp.CheckpointNumber" class="hover:bg-gray-50">
                                                <td class="px-3 py-2 whitespace-nowrap text-sm font-medium text-gray-900 border-r border-gray-200">
                                                    {{ cp.CheckpointNumber }} - {{ cp.label }}
                                                </td>
                                                <!-- First Inspection Samples -->
                                                <td v-for="(_, i) in cp.samples_first" :key="'f'+cp.CheckpointNumber+'-'+i" class="px-1 py-1 border-r border-gray-200">
                                                    <input
                                                        v-model="cp.samples_first[i]"
                                                        type="text"
                                                        class="w-14 text-center text-sm rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                        placeholder="-"
                                                    />
                                                </td>
                                                <!-- First Inspection Judgement -->
                                                <td class="px-1 py-1 border-r border-gray-200">
                                                    <input
                                                        v-model="cp.Judgement_First"
                                                        type="text"
                                                        class="w-16 text-center text-sm rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                        placeholder="OK"
                                                    />
                                                </td>
                                                <!-- Last Inspection Samples -->
                                                <td v-for="(_, i) in cp.samples_last" :key="'l'+cp.CheckpointNumber+'-'+i" class="px-1 py-1 border-r border-gray-200">
                                                    <input
                                                        v-model="cp.samples_last[i]"
                                                        type="text"
                                                        class="w-14 text-center text-sm rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                        placeholder="-"
                                                    />
                                                </td>
                                                <!-- Last Inspection Judgement -->
                                                <td class="px-1 py-1">
                                                    <input
                                                        v-model="cp.Judgement_Last"
                                                        type="text"
                                                        class="w-16 text-center text-sm rounded border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                                        placeholder="OK"
                                                    />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">Enter measurement values for each sample position. Leave empty if not applicable.</p>
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
                                        :href="route('magnetism-checksheet.index')"
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
