<template>
    <AuthenticatedLayout>
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

                <!-- Main Modules Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    <!-- Annealing Checks -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-blue-500">
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
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-green-500">
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
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-yellow-500">
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
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-purple-500">
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
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-l-4 border-red-500">
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
                </div>

                <!-- Recent Activity Section -->
                <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
                    </div>
                    <div class="divide-y divide-gray-200">
                        <div v-for="(activity, index) in recentActivity" :key="index" class="p-6 hover:bg-gray-50">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                        <span class="text-gray-600">{{ activity.user_initials }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="flex items-center">
                                        <p class="text-sm font-medium text-gray-900">{{ activity.user_name }}</p>
                                        <p class="ml-2 text-sm text-gray-500">{{ activity.action }}</p>
                                        <p class="ml-auto text-sm text-gray-500">{{ activity.time_ago }}</p>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">{{ activity.details }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-if="recentActivity.length === 0" class="p-6 text-center text-gray-500">
                            No recent activity to display.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { defineProps } from 'vue';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
    stats: {
        type: Array,
        required: true,
        default: () => [
            {
                name: 'Total Annealing Checks',
                value: '0',
                icon: 'svg',
                bgColorClass: 'bg-blue-100',
                textColorClass: 'text-blue-600',
                change: '0%',
                changeType: 'increase'
            },
            {
                name: 'Temperature Records',
                value: '0',
                icon: 'svg',
                bgColorClass: 'bg-green-100',
                textColorClass: 'text-green-600',
                change: '0%',
                changeType: 'increase'
            },
            {
                name: 'Torque Records',
                value: '0',
                icon: 'svg',
                bgColorClass: 'bg-yellow-100',
                textColorClass: 'text-yellow-600',
                change: '0%',
                changeType: 'increase'
            },
            {
                name: 'Production Batches',
                value: '0',
                icon: 'svg',
                bgColorClass: 'bg-purple-100',
                textColorClass: 'text-purple-600',
                change: '0%',
                changeType: 'increase'
            }
        ]
    },
    recentActivity: {
        type: Array,
        required: true,
        default: () => []
    }
});
</script>