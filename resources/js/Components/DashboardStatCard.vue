<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const stats = [
    { name: 'Total Inspections', value: '12,345', change: '+12%', changeType: 'increase' },
    { name: 'Passed', value: '8,456', change: '+5%', changeType: 'increase' },
    { name: 'Failed', value: '2,345', change: '-2%', changeType: 'decrease' },
    { name: 'Pending Review', value: '1,544', change: '+18%', changeType: 'increase' },
];

const recentActivity = ref([
    { id: 1, type: 'inspection', action: 'Completed', user: 'John Doe', time: '2 hours ago' },
    { id: 2, type: 'user', action: 'Logged in', user: 'Jane Smith', time: '3 hours ago' },
    { id: 3, type: 'inspection', action: 'Started', user: 'Robert Johnson', time: '5 hours ago' },
]);
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                    <div v-for="stat in stats" :key="stat.name" 
                         class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <span class="text-2xl font-bold text-gray-900">{{ stat.value }}</span>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            {{ stat.name }}
                                        </dt>
                                        <dd>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ stat.change }}
                                            </div>
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">
                            Recent Activity
                        </h3>
                    </div>
                    <div class="bg-white shadow overflow-hidden sm:rounded-b-lg">
                        <ul role="list" class="divide-y divide-gray-200">
                            <li v-for="activity in recentActivity" :key="activity.id" class="px-6 py-4">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate">
                                            {{ activity.user }}
                                        </p>
                                        <p class="text-sm text-gray-500 truncate">
                                            {{ activity.action }}
                                        </p>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ activity.time }}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>