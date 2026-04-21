<template>
    <div v-if="modelValue" class="fixed inset-0 overflow-y-auto z-50">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="close"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="mb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                        {{ batch ? 'Edit Batch' : 'Add New Batch' }}
                    </h3>
                </div>

                <form @submit.prevent="submit">
                    <div class="space-y-4">
                        <div>
                            <label for="production_date" class="block text-sm font-medium text-gray-700">Production Date <span class="text-red-500">*</span></label>
                            <input
                                id="production_date"
                                v-model="form.production_date"
                                type="date"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                :class="{ 'border-red-500': form.errors.production_date }"
                                @change="fetchNextLetter"
                            />
                            <p v-if="form.errors.production_date" class="mt-1 text-sm text-red-600">{{ form.errors.production_date }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="letter_code" class="block text-sm font-medium text-gray-700">Letter Code <span class="text-red-500">*</span></label>
                                <div class="mt-1 flex">
                                    <input
                                        id="letter_code"
                                        v-model="form.letter_code"
                                        type="text"
                                        maxlength="1"
                                        class="block w-full rounded-l-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 uppercase"
                                        :class="{ 'border-red-500': form.errors.letter_code }"
                                    />
                                    <button
                                        type="button"
                                        @click="fetchNextLetter"
                                        class="inline-flex items-center px-3 rounded-r-md border border-l-0 border-gray-300 bg-gray-50 text-gray-500 text-sm hover:bg-gray-100"
                                        title="Auto-assign next letter"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                        </svg>
                                    </button>
                                </div>
                                <p v-if="form.errors.letter_code" class="mt-1 text-sm text-red-600">{{ form.errors.letter_code }}</p>
                            </div>

                            <div>
                                <label for="produce_qty" class="block text-sm font-medium text-gray-700">Produce Qty <span class="text-red-500">*</span></label>
                                <input
                                    id="produce_qty"
                                    v-model.number="form.produce_qty"
                                    type="number"
                                    min="1"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                    :class="{ 'border-red-500': form.errors.produce_qty }"
                                />
                                <p v-if="form.errors.produce_qty" class="mt-1 text-sm text-red-600">{{ form.errors.produce_qty }}</p>
                            </div>
                        </div>

                        <div>
                            <label for="material_lot_number" class="block text-sm font-medium text-gray-700">Material Lot Number <span class="text-red-500">*</span></label>
                            <input
                                id="material_lot_number"
                                v-model="form.material_lot_number"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                :class="{ 'border-red-500': form.errors.material_lot_number }"
                                placeholder="e.g., 11172515A-208P"
                                @blur="fetchLetterForLot"
                            />
                            <p v-if="isExistingLot" class="mt-1 text-sm text-blue-600">Using existing letter for this lot</p>
                            <p v-if="form.errors.material_lot_number" class="mt-1 text-sm text-red-600">{{ form.errors.material_lot_number }}</p>
                        </div>

                        <div>
                            <label for="qr_code" class="block text-sm font-medium text-gray-700">QR Code <span class="text-red-500">*</span></label>
                            <input
                                id="qr_code"
                                v-model="form.qr_code"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                :class="{ 'border-red-500': form.errors.qr_code }"
                                placeholder="e.g., 20251124P"
                            />
                            <p v-if="form.errors.qr_code" class="mt-1 text-sm text-red-600">{{ form.errors.qr_code }}</p>
                        </div>

                        <div>
                            <label for="job_number" class="block text-sm font-medium text-gray-700">Job Number <span class="text-red-500">*</span></label>
                            <input
                                id="job_number"
                                v-model="form.job_number"
                                type="text"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                                :class="{ 'border-red-500': form.errors.job_number }"
                                placeholder="e.g., 3J73799745-0"
                            />
                            <p v-if="form.errors.job_number" class="mt-1 text-sm text-red-600">{{ form.errors.job_number }}</p>
                        </div>

                        <div>
                            <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                            <textarea
                                id="remarks"
                                v-model="form.remarks"
                                rows="2"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            ></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button
                            type="button"
                            @click="close"
                            class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-400"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 disabled:opacity-50"
                        >
                            <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ batch ? 'Update' : 'Add' }} Batch
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';

declare function route(name: string, params?: any): string;

const isExistingLot = ref(false);

interface Batch {
    id: number;
    letter_code: string;
    material_lot_number: string;
    qr_code: string;
    produce_qty: number;
    job_number: string;
    remarks: string | null;
}

interface Props {
    modelValue: boolean;
    checksheetId: number;
    batch?: Batch | null;
    selectedDate?: string;
}

const props = withDefaults(defineProps<Props>(), {
    batch: null,
    selectedDate: '',
});

const emit = defineEmits<{
    (e: 'update:modelValue', value: boolean): void;
    (e: 'saved'): void;
}>();

const form = useForm({
    production_date: props.selectedDate || new Date().toISOString().split('T')[0],
    letter_code: '',
    material_lot_number: '',
    qr_code: '',
    produce_qty: 1,
    job_number: '',
    remarks: '',
});

watch(() => props.modelValue, (isOpen) => {
    if (isOpen) {
        if (props.batch) {
            // Editing existing batch
            form.production_date = props.selectedDate || '';
            form.letter_code = props.batch.letter_code;
            form.material_lot_number = props.batch.material_lot_number;
            form.qr_code = props.batch.qr_code;
            form.produce_qty = props.batch.produce_qty;
            form.job_number = props.batch.job_number;
            form.remarks = props.batch.remarks || '';
        } else {
            // New batch
            form.reset();
            form.production_date = props.selectedDate || new Date().toISOString().split('T')[0];
            fetchNextLetter();
        }
    }
});

const fetchNextLetter = async () => {
    if (props.batch) return;
    
    try {
        const response = await axios.get(route('magnetism-checksheet.next-letter', props.checksheetId));
        if (response.data.letter) {
            form.letter_code = response.data.letter;
            isExistingLot.value = false;
        }
    } catch (error) {
        console.error('Failed to fetch next letter:', error);
    }
};

const fetchLetterForLot = async () => {
    if (!form.material_lot_number || props.batch) return;
    
    try {
        const response = await axios.get(route('magnetism-checksheet.letter-for-lot', props.checksheetId), {
            params: { material_lot_number: form.material_lot_number }
        });
        if (response.data.letter) {
            form.letter_code = response.data.letter;
            isExistingLot.value = response.data.is_existing_lot;
        }
    } catch (error) {
        console.error('Failed to fetch letter for lot:', error);
    }
};

const close = () => {
    emit('update:modelValue', false);
    form.reset();
    form.clearErrors();
    isExistingLot.value = false;
};

const submit = () => {
    if (props.batch) {
        // Update existing batch
        form.put(route('magnetism-checksheet.batches.update', {
            magnetism_checksheet: props.checksheetId,
            batch: props.batch.id,
        }), {
            preserveScroll: true,
            onSuccess: () => {
                emit('saved');
                close();
            },
        });
    } else {
        // Create new batch
        form.post(route('magnetism-checksheet.batches.store', props.checksheetId), {
            preserveScroll: true,
            onSuccess: () => {
                emit('saved');
                close();
            },
        });
    }
};
</script>
