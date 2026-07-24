<script setup lang="ts">
type ApprovalStats = {
    approved: number;
    pending: number;
    rejected: number;
};

type ModuleStats = {
    totalRecords: number;
    currentMonthTotal: number;
    previousMonthTotal: number;
    trendPercentage: number | null;
    approvals: ApprovalStats | null;
};

defineProps<{
    stats: ModuleStats;
}>();

const formatTrend = (value: number | null): string => {
    if (value === null) {
        return 'N/A';
    }

    return `${value > 0 ? '+' : ''}${value}%`;
};
</script>

<template>
    <div class="mt-5 border-t border-gray-100 pt-4">
        <dl class="grid grid-cols-4 gap-2 text-center">
            <div>
                <dt class="text-xs text-gray-500">Total</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ stats.totalRecords }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">This month</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ stats.currentMonthTotal }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">Previous</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ stats.previousMonthTotal }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-500">Trend</dt>
                <dd
                    class="mt-1 text-sm font-semibold"
                    :class="stats.trendPercentage === null
                        ? 'text-gray-500'
                        : stats.trendPercentage >= 0 ? 'text-green-600' : 'text-red-600'"
                >
                    {{ formatTrend(stats.trendPercentage) }}
                </dd>
            </div>
        </dl>
        <p class="mt-3 min-h-5 text-center text-xs text-gray-500">
            <template v-if="stats.approvals">
                Approved {{ stats.approvals.approved }}
                <span aria-hidden="true">&middot;</span>
                Pending {{ stats.approvals.pending }}
                <span aria-hidden="true">&middot;</span>
                Rejected {{ stats.approvals.rejected }}
            </template>
            <template v-else>No approval workflow</template>
        </p>
    </div>
</template>
