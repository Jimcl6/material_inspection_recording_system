<script setup lang="ts">
type DetailItem = {
    label: string;
    value: unknown;
};

type DetailSection = {
    title: string;
    items: DetailItem[];
};

defineProps<{
    sections: DetailSection[];
}>();

const displayValue = (value: unknown): string => {
    if (value === null || value === undefined || value === '') {
        return 'N/A';
    }

    return String(value);
};
</script>

<template>
    <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
            <section v-for="section in sections" :key="section.title">
                <h4 class="text-xs font-semibold uppercase text-gray-500">
                    {{ section.title }}
                </h4>
                <dl class="mt-3 space-y-2">
                    <div
                        v-for="item in section.items"
                        :key="`${section.title}-${item.label}`"
                        class="grid grid-cols-3 gap-3 text-sm"
                    >
                        <dt class="text-gray-500">
                            {{ item.label }}
                        </dt>
                        <dd class="col-span-2 break-words font-medium text-gray-900">
                            {{ displayValue(item.value) }}
                        </dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>
</template>
