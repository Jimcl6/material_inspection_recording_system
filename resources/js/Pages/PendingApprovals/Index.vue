<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    approvalModules: {
        type: Array,
        default: () => [],
    },
    pendingApprovalsCount: {
        type: Number,
        default: 0,
    },
});

const formatDate = (value) => {
    if (!value) {
        return 'N/A';
    }

    return new Date(value).toLocaleDateString();
};
</script>

<template>
    <Head title="Pending Approvals" />

    <AppLayout>
        <template #header>
            <div class="flex flex-col gap-1">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Pending Approvals
                </h2>
                <p class="text-sm text-gray-500">
                    {{ pendingApprovalsCount }} item(s) waiting across approval modules
                </p>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div
                    v-if="approvalModules.length === 0"
                    class="rounded-md border border-gray-200 bg-white px-6 py-10 text-center shadow-sm"
                >
                    <h3 class="text-base font-medium text-gray-900">No approval modules available</h3>
                    <p class="mt-1 text-sm text-gray-500">You do not have approval access for any module.</p>
                </div>

                <section
                    v-for="approvalModule in approvalModules"
                    :key="approvalModule.module"
                    class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm"
                >
                    <div class="flex flex-col gap-3 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">{{ approvalModule.label }}</h3>
                            <p class="text-sm text-gray-500">{{ approvalModule.pendingCount }} pending item(s)</p>
                        </div>
                        <Link
                            :href="route(approvalModule.routeName)"
                            class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                        >
                            Review All
                        </Link>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Record</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Submitted By</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="record in approvalModule.records" :key="record.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-gray-900">{{ record.title }}</p>
                                        <p class="text-sm text-gray-500">{{ record.subtitle || 'No additional details' }}</p>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ formatDate(record.date) }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ record.submittedBy || 'System' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                        <Link
                                            :href="route(record.showRouteName, record.showRouteParams)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            View
                                        </Link>
                                    </td>
                                </tr>
                                <tr v-if="approvalModule.records.length === 0">
                                    <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                                        No pending records in this module.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
