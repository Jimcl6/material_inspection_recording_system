<template>
    <div>
        <h2 class="text-2xl font-semibold text-gray-900">Dashboard Overview</h2>
        
        <!-- Stats Grid -->
        <div class="mt-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <div v-for="stat in stats" :key="stat.name" 
                 class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <dt class="text-sm font-medium text-gray-500 truncate">
                        {{ stat.name }}
                    </dt>
                    <dd class="mt-1 text-3xl font-semibold text-gray-900">
                        {{ stat.value }}
                    </dd>
                    <div class="mt-2">
                        <span :class="[
                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                            stat.changeType === 'increase' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        ]">
                            {{ stat.change }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="mt-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Activity</h3>
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
                <ul class="divide-y divide-gray-200">
                    <li v-for="activity in recentActivity" :key="activity.id" class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="min-w-0 flex-1 flex items-center">
                                <div class="flex-shrink-0">
                                    <span v-if="activity.type === 'inspection'" 
                                          class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                    <span v-else 
                                          class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">
                                        {{ activity.user }}
                                        <span class="text-gray-500">{{ activity.action }}</span>
                                    </p>
                                    <p class="text-sm text-gray-500">{{ activity.time }}</p>
                                </div>
                            </div>
                            <div v-if="activity.type === 'inspection'">
                                <Link 
                                    v-if="$page.props.ziggy.routes['inspections.show']"
                                    :href="route('inspections.show', activity.id)"
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                                >
                                    View
                                    <span class="sr-only">, {{ activity.action }}</span>
                                </Link>
                                <span v-else class="text-sm text-gray-500">
                                    View
                                </span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: {
        type: Array,
        default: () => [
            { name: 'Total Inspections', value: '12,345', change: '+12%', changeType: 'increase' },
            { name: 'Passed', value: '8,456', change: '+5%', changeType: 'increase' },
            { name: 'Failed', value: '2,345', change: '-2%', changeType: 'decrease' },
            { name: 'Pending Review', value: '1,544', change: '+18%', changeType: 'increase' },
        ]
    },
    recentActivity: {
        type: Array,
        default: () => [
            { id: 1, type: 'inspection', action: 'Completed', user: 'John Doe', time: '2 hours ago' },
            { id: 2, type: 'user', action: 'Logged in', user: 'Jane Smith', time: '3 hours ago' },
            { id: 3, type: 'inspection', action: 'Started', user: 'Robert Johnson', time: '5 hours ago' },
        ]
    }
});
</script>