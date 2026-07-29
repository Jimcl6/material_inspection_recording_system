<script setup lang="ts">
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import {
    ArrowRightIcon,
    ClipboardDocumentCheckIcon,
    FireIcon,
    WrenchScrewdriverIcon,
    CubeIcon,
    DocumentTextIcon,
    MagnifyingGlassIcon,
} from '@heroicons/vue/24/outline';
import { usePermissions } from '@/Composables/usePermissions';
import { useTabletRecentRoute } from '@/Composables/useTabletRecentRoute';

const props = defineProps<{
    dashboardSummary: Record<string, any>;
    pendingApprovalsCount?: number | null;
}>();

const page = usePage();
const { canCreate, canView } = usePermissions();
const { recentRoute } = useTabletRecentRoute();

const modules = [
    { key: 'annealing', label: 'Annealing', index: 'annealing-checks.index', create: 'annealing-checks.create', icon: FireIcon, color: 'text-orange-700 bg-orange-50 border-orange-200' },
    { key: 'temperature', label: 'Temperature', index: 'temp-records.index', create: 'temp-records.create', icon: ClipboardDocumentCheckIcon, color: 'text-emerald-700 bg-emerald-50 border-emerald-200' },
    { key: 'torque', label: 'Torque', index: 'torque-records.index', create: 'torque-records.create', icon: WrenchScrewdriverIcon, color: 'text-amber-700 bg-amber-50 border-amber-200' },
    { key: 'welding', label: 'Welding', index: 'welding-checksheets.index', create: 'welding-checksheets.create', icon: DocumentTextIcon, color: 'text-blue-700 bg-blue-50 border-blue-200' },
    { key: 'magnetism', label: 'Magnetism', index: 'magnetism-checksheet.index', create: 'magnetism-checksheet.create', icon: MagnifyingGlassIcon, color: 'text-purple-700 bg-purple-50 border-purple-200' },
    { key: 'material', label: 'Material', index: 'material-monitoring-checksheets.index', create: 'material-monitoring-checksheets.create', icon: CubeIcon, color: 'text-rose-700 bg-rose-50 border-rose-200' },
];

const visibleModules = computed(() => modules.filter((module) => canView(module.key)));
const creatableModules = computed(() => modules.filter((module) => canCreate(module.key)));
const userName = computed(() => String(page.props.auth?.user?.name || 'Operator').split(' ')[0]);
</script>

<template>
    <!-- eslint-disable vue/valid-v-for -- Known parser false positive for keyed Vue template loops. -->
    <section class="mx-auto max-w-5xl space-y-5">
        <div>
            <p class="text-sm font-medium text-indigo-600">Tablet workspace</p>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">Good day, {{ userName }}</h1>
            <p class="mt-1 text-sm text-gray-600">Start a check, resume recent work, or review today’s status.</p>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Today</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">{{ props.dashboardSummary.todayTotal ?? 0 }}</p>
            </div>
            <Link
                v-if="props.pendingApprovalsCount !== null"
                :href="route('approvals.index')"
                class="rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
            >
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Pending</p>
                <p class="mt-2 text-2xl font-bold text-amber-900">{{ props.pendingApprovalsCount ?? 0 }}</p>
            </Link>
            <div v-else class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">This month</p>
                <p class="mt-2 text-2xl font-bold text-gray-900">{{ props.dashboardSummary.currentMonthTotal }}</p>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-red-700">Rejected</p>
                <p class="mt-2 text-2xl font-bold text-red-900">{{ props.dashboardSummary.approvals?.rejected ?? 0 }}</p>
            </div>
        </div>

        <Link
            v-if="recentRoute"
            :href="recentRoute.href"
            class="flex min-h-14 items-center justify-between rounded-xl bg-indigo-600 px-5 py-4 font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
            <span>{{ recentRoute.label }}</span>
            <ArrowRightIcon class="h-5 w-5" />
        </Link>

        <div v-if="creatableModules.length" class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="text-base font-semibold text-gray-900">New inspection</h2>
            <div class="mt-3 grid grid-cols-2 gap-3 landscape:grid-cols-3">
                <Link
                    v-for="module in creatableModules"
                    :key="module.key"
                    :href="route(module.create)"
                    class="flex min-h-20 items-center gap-3 rounded-xl border p-4 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    :class="module.color"
                >
                    <component :is="module.icon" class="h-6 w-6 shrink-0" />
                    {{ module.label }}
                </Link>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <h2 class="text-base font-semibold text-gray-900">Browse records</h2>
            <div class="mt-2 divide-y divide-gray-100">
                <Link
                    v-for="module in visibleModules"
                    :key="module.key"
                    :href="route(module.index)"
                    class="flex min-h-12 items-center justify-between py-3 text-sm font-medium text-gray-800 hover:text-indigo-700"
                >
                    {{ module.label }}
                    <ArrowRightIcon class="h-4 w-4 text-gray-400" />
                </Link>
            </div>
        </div>
    </section>
    <!-- eslint-enable vue/valid-v-for -->
</template>
