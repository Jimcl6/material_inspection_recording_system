<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ClipboardDocumentListIcon,
    HomeIcon,
    PlusIcon,
    ShieldCheckIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { route } from 'ziggy-js';
import { usePermissions } from '@/Composables/usePermissions';

type ModuleLink = {
    module: string;
    label: string;
    indexRoute: string;
    createRoute: string;
};

const modules: ModuleLink[] = [
    {
        module: 'annealing',
        label: 'Annealing Checks',
        indexRoute: 'annealing-checks.index',
        createRoute: 'annealing-checks.create',
    },
    {
        module: 'temperature',
        label: 'Temperature Records',
        indexRoute: 'temp-records.index',
        createRoute: 'temp-records.create',
    },
    {
        module: 'torque',
        label: 'Torque Records',
        indexRoute: 'torque-records.index',
        createRoute: 'torque-records.create',
    },
    {
        module: 'magnetism',
        label: 'Magnetism Checksheet',
        indexRoute: 'magnetism-checksheet.index',
        createRoute: 'magnetism-checksheet.create',
    },
    {
        module: 'welding',
        label: 'Welding Checksheet',
        indexRoute: 'welding-checksheets.index',
        createRoute: 'welding-checksheets.create',
    },
    {
        module: 'material',
        label: 'Material Monitoring',
        indexRoute: 'material-monitoring-checksheets.index',
        createRoute: 'material-monitoring-checksheets.create',
    },
];

const page = usePage();
const { canView, canCreate, canApprove } = usePermissions();
const sheet = ref<'records' | 'create' | null>(null);

const viewableModules = computed(() =>
    modules.filter(({ module }) => canView(module)),
);
const creatableModules = computed(() =>
    modules.filter(({ module }) => canCreate(module)),
);
const hasApprovalAccess = computed(() =>
    ['annealing', 'temperature', 'torque', 'welding'].some((module) =>
        canApprove(module),
    ),
);

const currentRoute = computed(() => {
    void page.url;

    return route().current() || '';
});
const isRecordRoute = computed(() =>
    viewableModules.value.some(({ indexRoute }) =>
        currentRoute.value.startsWith(indexRoute.replace('.index', '.')),
    ),
);

const closeSheet = (): void => {
    sheet.value = null;
};
</script>

<template>
    <!-- eslint-disable vue/valid-v-for -- Known parser false positive for keyed Vue template loops. -->
    <div>
        <div
            v-if="sheet"
            class="fixed inset-0 z-50 flex items-end bg-gray-900/50"
            @click.self="closeSheet"
        >
            <section class="w-full rounded-t-2xl bg-white px-6 pb-8 pt-5 shadow-2xl">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ sheet === 'records' ? 'Open records' : 'Create new record' }}
                        </h2>
                        <p class="text-sm text-gray-500">Choose an operational module.</p>
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-11 w-11 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        aria-label="Close module chooser"
                        @click="closeSheet"
                    >
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <Link
                        v-for="moduleLink in sheet === 'records' ? viewableModules : creatableModules"
                        :key="moduleLink.module"
                        :href="route(sheet === 'records' ? moduleLink.indexRoute : moduleLink.createRoute)"
                        class="flex min-h-16 items-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold text-gray-800 hover:border-indigo-300 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        @click="closeSheet"
                    >
                        {{ moduleLink.label }}
                    </Link>
                </div>
            </section>
        </div>

        <nav
            class="fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 bg-white px-4 pb-[max(0.75rem,env(safe-area-inset-bottom))] pt-2 shadow-[0_-8px_24px_rgba(15,23,42,0.08)]"
            aria-label="Tablet primary navigation"
        >
            <div class="mx-auto grid max-w-3xl grid-cols-4 gap-2">
                <Link
                    :href="route('dashboard')"
                    class="flex min-h-14 flex-col items-center justify-center rounded-xl px-2 py-1 text-xs font-medium"
                    :class="currentRoute === 'dashboard' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50'"
                >
                    <HomeIcon class="h-6 w-6" />
                    <span>Home</span>
                </Link>
                <button
                    type="button"
                    class="flex min-h-14 flex-col items-center justify-center rounded-xl px-2 py-1 text-xs font-medium"
                    :class="isRecordRoute ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-50'"
                    @click="sheet = 'records'"
                >
                    <ClipboardDocumentListIcon class="h-6 w-6" />
                    <span>Records</span>
                </button>
                <button
                    type="button"
                    class="flex min-h-14 flex-col items-center justify-center rounded-xl px-2 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50"
                    @click="sheet = 'create'"
                >
                    <PlusIcon class="h-6 w-6" />
                    <span>New</span>
                </button>
                <Link
                    v-if="hasApprovalAccess"
                    :href="route('approvals.index')"
                    class="flex min-h-14 flex-col items-center justify-center rounded-xl px-2 py-1 text-xs font-medium"
                    :class="currentRoute.includes('approval') || currentRoute === 'approvals.index'
                        ? 'bg-indigo-50 text-indigo-700'
                        : 'text-gray-600 hover:bg-gray-50'"
                >
                    <ShieldCheckIcon class="h-6 w-6" />
                    <span>Approvals</span>
                </Link>
                <span
                    v-else
                    class="flex min-h-14 flex-col items-center justify-center rounded-xl px-2 py-1 text-xs font-medium text-gray-300"
                    aria-hidden="true"
                >
                    <ShieldCheckIcon class="h-6 w-6" />
                    <span>Approvals</span>
                </span>
            </div>
        </nav>
    </div>
    <!-- eslint-enable vue/valid-v-for -->
</template>
