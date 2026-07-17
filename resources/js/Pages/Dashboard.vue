<template>
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Overview -->
                <!-- <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div v-for="stat in stats" :key="stat.name" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full" :class="stat.bgColorClass + ' bg-opacity-20'">
                                    <component :is="stat.icon" class="h-6 w-6" :class="stat.textColorClass" />
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">{{ stat.name }}</p>
                                    <p class="text-2xl font-semibold text-gray-900">{{ stat.value }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> -->

                <div v-if="approvalModules.length > 0" class="mb-6">
                    <Link
                        :href="route('approvals.index')"
                        class="flex flex-col gap-3 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-red-900 transition hover:border-red-300 hover:bg-red-100 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0a3 3 0 01-6 0m6 0H9" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-semibold">Pending Approvals</p>
                                <p class="text-sm text-red-700">{{ pendingApprovalsCount }} item(s) waiting for review</p>
                            </div>
                        </div>
                        <span class="text-sm font-medium text-red-700">
                            Open approvals <span aria-hidden="true">&rarr;</span>
                        </span>
                    </Link>
                </div>

                <!-- Main Modules Grid -->
                <div
                    v-if="hasVisibleDashboardModules"
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8"
                >
                    <!-- Annealing Checks -->
                    <div v-if="canView('annealing')" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-blue-500">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-blue-100">
                                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Annealing Checks</h3>
                                    <p class="text-sm text-gray-500">Manage and track annealing processes</p>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <Link :href="route('annealing-checks.index')" class="text-sm font-medium text-blue-600 hover:text-blue-500">
                                    View All <span aria-hidden="true">&rarr;</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Temperature Records -->
                    <div v-if="canView('temperature')" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-green-500">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-green-100">
                                    <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Temperature Records</h3>
                                    <p class="text-sm text-gray-500">Track and analyze temperature data</p>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <Link :href="route('temp-records.index')" class="text-sm font-medium text-green-600 hover:text-green-500">
                                    View All <span aria-hidden="true">&rarr;</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Torque Records -->
                    <div v-if="canView('torque')" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-yellow-500">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-yellow-100">
                                    <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37a1.724 1.724 0 002.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Torque Records</h3>
                                    <p class="text-sm text-gray-500">Manage torque measurement data</p>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <Link :href="route('torque-records.index')" class="text-sm font-medium text-yellow-600 hover:text-yellow-500">
                                    View All <span aria-hidden="true">&rarr;</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Magnetism Checksheet -->
                    <div v-if="canView('magnetism')" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-purple-500">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-purple-100">
                                    <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Magnetism Checksheet</h3>
                                    <p class="text-sm text-gray-500">Track and manage magnetism checksheets</p>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <Link :href="route('magnetism-checksheet.index')" class="text-sm font-medium text-purple-600 hover:text-purple-500">
                                    View All <span aria-hidden="true">&rarr;</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Modification Logs -->
                    <div v-if="canView('modification')" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-red-500">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-red-100">
                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Modification Logs</h3>
                                    <p class="text-sm text-gray-500">View system modification history</p>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <Link :href="route('modification-logs.index')" class="text-sm font-medium text-red-600 hover:text-red-500">
                                    View All <span aria-hidden="true">&rarr;</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Material Monitoring Checksheet -->
                    <div v-if="canView('material')" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-orange-500">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-orange-100">
                                    <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Material Monitoring Checksheet</h3>
                                    <p class="text-sm text-gray-500">View welding checksheets</p>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <Link :href="route('material-monitoring-checksheets.index')" class="text-sm font-medium text-orange-600 hover:text-red-500">
                                    View All <span aria-hidden="true">&rarr;</span>
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Welding Checksheet -->
                    <div v-if="canView('welding')" class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-orange-500">
                        <div class="p-6">
                            <div class="flex items-center">
                                <div class="p-3 rounded-full bg-orange-100">
                                    <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-medium text-gray-900">Welding Checksheet</h3>
                                    <p class="text-sm text-gray-500">View material monitor checksheets</p>
                                </div>
                            </div>
                            <div class="mt-4 flex justify-end">
                                <Link :href="route('welding-checksheets.index')" class="text-sm font-medium text-orange-600 hover:text-red-500">
                                    View All <span aria-hidden="true">&rarr;</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity Section -->
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Today's Activity</h3>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                        <div v-if="recentActivity.length > 0" class="divide-y divide-gray-200">
                            <div v-for="(activity, index) in recentActivity" :key="index" class="p-6 hover:bg-gray-50 transition-colors">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-indigo-600 font-medium">{{ activity.user_initials }}</span>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <div class="flex items-center">
                                            <p class="text-sm font-medium text-gray-900">{{ activity.user_name }}</p>
                                            <p class="ml-2 text-sm text-gray-500">{{ activity.action }}</p>
                                            <p class="ml-auto text-sm text-gray-500">{{ activity.time_ago }}</p>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500">{{ activity.details }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="p-6 text-center text-gray-500">
                            No activity today.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed, defineProps } from 'vue';
import { Link } from '@inertiajs/vue3';
// import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePermissions } from '@/Composables/usePermissions';

const dashboardModuleKeys = [
    'annealing',
    'temperature',
    'torque',
    'magnetism',
    'modification',
    'material',
    'welding',
];

const { canView } = usePermissions();
const hasVisibleDashboardModules = computed(() => dashboardModuleKeys.some((module) => canView(module)));

const props = defineProps({
    stats: {
        type: Array,
        required: true,
    },
    recentActivity: {
        type: Array,
        required: true,
    },
    pendingApprovalsCount: {
        type: Number,
        default: null,
    },
    approvalModules: {
        type: Array,
        default: () => [],
    },
});
</script>
