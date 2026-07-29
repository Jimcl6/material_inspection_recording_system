<script setup lang="ts">
import { ChevronDownIcon } from '@heroicons/vue/24/outline';
import { useSingleExpandedRow } from '@/Composables/useSingleExpandedRow';
import RecordDetailPanel from '@/Components/RecordDetailPanel.vue';

type Fact = {
    label?: string;
    value: unknown;
};

type DetailItem = {
    label: string;
    value: unknown;
};

type DetailSection = {
    title: string;
    items: DetailItem[];
};

const props = withDefaults(defineProps<{
    records: any[];
    titleFor: (record: any) => unknown;
    factsFor: (record: any) => Fact[];
    statusFor?: (record: any) => string | null | undefined;
    sectionsFor: (record: any) => DetailSection[];
    emptyMessage?: string;
    recordKey?: string;
}>(), {
    statusFor: undefined,
    emptyMessage: 'No records found.',
    recordKey: 'id',
});

const { toggleExpanded, isExpanded } = useSingleExpandedRow();

const displayValue = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return 'N/A';
    }

    return String(value);
};

const statusClasses = (status: string): string => {
    switch (status.toLowerCase()) {
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-amber-100 text-amber-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-700';
    }
};

const recordStatus = (record: any): string | null => {
    const value = props.statusFor?.(record);

    return value ? String(value) : null;
};
</script>

<template>
    <!-- eslint-disable vue/valid-v-for -- Known parser false positive for keyed Vue template loops. -->
    <div class="divide-y divide-gray-200 rounded-xl border border-gray-200 bg-white shadow-sm">
        <article
            v-for="record in props.records"
            :key="record[recordKey]"
            class="px-4 py-4"
            :class="{ 'bg-indigo-50/40': isExpanded(record[recordKey]) }"
        >
            <div class="flex items-start gap-3">
                <button
                    type="button"
                    class="flex min-w-0 flex-1 items-start gap-3 rounded-lg text-left focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    :aria-expanded="isExpanded(record[recordKey])"
                    @click="toggleExpanded(record[recordKey])"
                >
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-base font-semibold text-gray-900">
                            {{ displayValue(titleFor(record)) }}
                        </span>
                        <span class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-sm text-gray-600">
                            <span v-for="(fact, index) in factsFor(record)" :key="`${record[recordKey]}-${index}`">
                                <span v-if="fact.label" class="text-gray-500">{{ fact.label }}: </span>
                                <span class="font-medium text-gray-800">{{ displayValue(fact.value) }}</span>
                            </span>
                        </span>
                    </span>
                    <span
                        v-if="recordStatus(record)"
                        class="inline-flex shrink-0 rounded-full px-2.5 py-1 text-xs font-semibold"
                        :class="statusClasses(recordStatus(record)!)"
                    >
                        {{ recordStatus(record) }}
                    </span>
                    <ChevronDownIcon
                        class="mt-1 h-5 w-5 shrink-0 text-gray-500 transition-transform"
                        :class="{ 'rotate-180': isExpanded(record[recordKey]) }"
                    />
                </button>
            </div>

            <div v-if="isExpanded(record[recordKey])" class="mt-4 border-t border-gray-200 pt-4">
                <RecordDetailPanel :sections="sectionsFor(record)" />
                <div class="mt-4 flex flex-wrap justify-end gap-3">
                    <slot name="actions" :record="record" />
                </div>
            </div>
        </article>

        <div v-if="!props.records.length" class="px-6 py-12 text-center text-sm text-gray-500">
            {{ emptyMessage }}
        </div>
    </div>
    <!-- eslint-enable vue/valid-v-for -->
</template>
