<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline';

type Fact = { label: string; value: unknown };
type DetailItem = { label: string; value: unknown };
type DetailSection = { title: string; items: DetailItem[] };

const props = defineProps<{
    records: any[];
    titleFor: (record: any) => unknown;
    subtitleFor?: (record: any) => unknown;
    factsFor: (record: any) => Fact[];
    detailsFor?: (record: any) => DetailSection[];
    showRouteName: string;
    notes: string;
    processing?: boolean;
}>();

const emit = defineEmits<{
    approve: [record: any];
    reject: [record: any];
    bulk: [];
    'update:notes': [value: string];
}>();

const currentIndex = ref(0);
const currentRecord = computed(() => props.records[currentIndex.value] ?? null);

watch(() => props.records.length, (length) => {
    currentIndex.value = Math.min(currentIndex.value, Math.max(0, length - 1));
});

const displayValue = (value: unknown): string =>
    value === null || value === undefined || value === '' ? 'N/A' : String(value);
</script>

<template>
    <!-- eslint-disable vue/valid-v-for -- Known parser false positive for keyed Vue template loops. -->
    <div>
        <div class="mb-4 flex items-center justify-between gap-3">
            <p class="text-sm font-medium text-gray-600">
                {{ records.length ? `${currentIndex + 1} of ${records.length}` : 'No pending records' }}
            </p>
            <button
                type="button"
                class="inline-flex min-h-11 items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50"
                @click="emit('bulk')"
            >
                Bulk mode
            </button>
        </div>

        <article v-if="currentRecord" class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">Pending review</p>
            <h3 class="mt-1 text-xl font-bold text-gray-900">{{ displayValue(titleFor(currentRecord)) }}</h3>
            <p v-if="subtitleFor" class="mt-1 text-sm text-gray-600">{{ displayValue(subtitleFor(currentRecord)) }}</p>

            <dl class="mt-5 grid grid-cols-2 gap-4">
                <div v-for="fact in factsFor(currentRecord)" :key="fact.label">
                    <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ fact.label }}</dt>
                    <dd class="mt-1 text-sm font-semibold text-gray-900">{{ displayValue(fact.value) }}</dd>
                </div>
            </dl>

            <div v-if="detailsFor" class="mt-5 space-y-4 border-t border-gray-200 pt-4">
                <section v-for="section in detailsFor(currentRecord)" :key="section.title">
                    <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ section.title }}</h4>
                    <dl class="mt-2 space-y-2">
                        <div v-for="item in section.items" :key="`${section.title}-${item.label}`" class="grid grid-cols-2 gap-3 text-sm">
                            <dt class="text-gray-500">{{ item.label }}</dt>
                            <dd class="break-words font-semibold text-gray-900">{{ displayValue(item.value) }}</dd>
                        </div>
                    </dl>
                </section>
            </div>

            <Link
                :href="route(showRouteName, currentRecord.id)"
                class="mt-5 inline-flex min-h-11 items-center text-sm font-semibold text-indigo-700"
            >
                View complete record
            </Link>

            <label class="mt-4 block text-sm font-medium text-gray-700" for="tablet-approval-notes">
                Approval notes
            </label>
            <textarea
                id="tablet-approval-notes"
                :value="notes"
                rows="3"
                class="mt-1 block w-full rounded-lg border-gray-300 text-base shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Optional for approval; explain a rejection"
                @input="emit('update:notes', ($event.target as HTMLTextAreaElement).value)"
            />

            <div class="mt-5 grid grid-cols-2 gap-3">
                <button
                    type="button"
                    :disabled="processing"
                    class="min-h-12 rounded-lg bg-red-600 px-4 py-3 text-sm font-bold text-white hover:bg-red-700 disabled:opacity-50"
                    @click="emit('reject', currentRecord)"
                >
                    Reject
                </button>
                <button
                    type="button"
                    :disabled="processing"
                    class="min-h-12 rounded-lg bg-green-600 px-4 py-3 text-sm font-bold text-white hover:bg-green-700 disabled:opacity-50"
                    @click="emit('approve', currentRecord)"
                >
                    Approve
                </button>
            </div>
        </article>

        <div v-else class="rounded-xl border border-gray-200 bg-white px-6 py-12 text-center text-sm text-gray-500">
            No pending approvals at this time.
        </div>

        <div v-if="records.length > 1" class="mt-4 flex justify-between">
            <button
                type="button"
                :disabled="currentIndex === 0"
                class="inline-flex min-h-11 items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 disabled:opacity-40"
                @click="currentIndex--"
            >
                <ChevronLeftIcon class="h-5 w-5" /> Previous
            </button>
            <button
                type="button"
                :disabled="currentIndex >= records.length - 1"
                class="inline-flex min-h-11 items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 disabled:opacity-40"
                @click="currentIndex++"
            >
                Next <ChevronRightIcon class="h-5 w-5" />
            </button>
        </div>
    </div>
    <!-- eslint-enable vue/valid-v-for -->
</template>
