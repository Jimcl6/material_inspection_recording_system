<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>
    </template>

    <div class="py-6">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 gap-5 mb-8 sm:grid-cols-2 lg:grid-cols-4">
          <DashboardStatCard 
            title="Total Batches" 
            :value="stats.totalBatches" 
            icon="cube"
            variant="primary"
          />
          <DashboardStatCard 
            title="Inspections Today" 
            :value="stats.inspectionsToday"
            icon="clipboard-document-check"
            variant="success"
          />
          <DashboardStatCard 
            title="Pending Actions" 
            :value="stats.pendingActions"
            icon="exclamation-triangle"
            variant="warning"
          />
          <DashboardStatCard 
            title="Completed This Week" 
            :value="stats.completedThisWeek"
            icon="check-circle"
            variant="info"
          />
        </div>

        <!-- Quick Actions -->
        <div class="mb-8">
          <h3 class="mb-4 text-lg font-medium text-gray-900">Quick Actions</h3>
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
            <ActionButton
              title="New Batch"
              icon="plus-circle"
              :href="route('batches.create')"
              variant="primary"
            />
            <ActionButton
              title="View All Batches"
              icon="list-bullet"
              :href="route('batches.index')"
              variant="secondary"
            />
            <ActionButton
              title="Generate Report"
              icon="document-chart-bar"
              @click="generateReport"
              variant="success"
            />
            <ActionButton
              title="Settings"
              icon="cog-6-tooth"
              @click="openSettings"
              variant="outline"
            />
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="p-6 bg-white rounded-lg shadow">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Recent Activity</h3>
            <a :href="route('activity.index')" class="text-sm text-blue-600 hover:underline">
              View All
            </a>
          </div>
          <div class="overflow-hidden border border-gray-200 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Activity</th>
                  <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Batch</th>
                  <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">User</th>
                  <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Time</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(activity, index) in recentActivity" :key="index">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <component
                        :is="activityIcons[activity.type]"
                        class="w-5 h-5 mr-2"
                        :class="activityColors[activity.type]"
                      />
                      <span>{{ activity.description }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <a :href="activity.batchUrl" class="text-blue-600 hover:underline">
                      {{ activity.batch }}
                    </a>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ activity.user }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">{{ formatTimeAgo(activity.time) }}</td>
                </tr>
                <tr v-if="recentActivity.length === 0">
                  <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                    No recent activity to show
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DashboardStatCard from '@/Components/DashboardStatCard.vue';
import ActionButton from '@/Components/ActionButton.vue';
import { 
  CubeIcon, 
  ClipboardDocumentCheckIcon, 
  ExclamationTriangleIcon, 
  CheckCircleIcon 
} from '@heroicons/vue/24/outline';

// Mock data - replace with actual API calls
const stats = ref({
  totalBatches: 127,
  inspectionsToday: 8,
  pendingActions: 5,
  completedThisWeek: 42
});

const recentActivity = ref([
  {
    type: 'inspection',
    description: 'Completed inspection',
    batch: 'B-2023-001',
    batchUrl: '/batches/1',
    user: 'John Doe',
    time: new Date(Date.now() - 1000 * 60 * 15) // 15 minutes ago
  },
  {
    type: 'batch',
    description: 'New batch created',
    batch: 'B-2023-128',
    batchUrl: '/batches/128',
    user: 'Jane Smith',
    time: new Date(Date.now() - 1000 * 60 * 60) // 1 hour ago
  },
  {
    type: 'sample',
    description: 'Sample collected',
    batch: 'B-2023-120',
    batchUrl: '/batches/120',
    user: 'Mike Johnson',
    time: new Date(Date.now() - 1000 * 60 * 60 * 3) // 3 hours ago
  }
]);

const activityIcons = {
  inspection: ClipboardDocumentCheckIcon,
  batch: CubeIcon,
  sample: CheckCircleIcon,
  alert: ExclamationTriangleIcon
};

const activityColors = {
  inspection: 'text-green-500',
  batch: 'text-blue-500',
  sample: 'text-indigo-500',
  alert: 'text-yellow-500'
};

const generateReport = () => {
  // TODO: Implement report generation
  alert('Report generation will be implemented here');
};

const openSettings = () => {
  // TODO: Implement settings modal
  alert('Settings modal will open here');
};

const formatTimeAgo = (date) => {
  const seconds = Math.floor((new Date() - date) / 1000);
  let interval = Math.floor(seconds / 31536000);
  
  if (interval > 1) return `${interval} years ago`;
  if (interval === 1) return '1 year ago';
  
  interval = Math.floor(seconds / 2592000);
  if (interval > 1) return `${interval} months ago`;
  if (interval === 1) return '1 month ago';
  
  interval = Math.floor(seconds / 86400);
  if (interval > 1) return `${interval} days ago`;
  if (interval === 1) return '1 day ago';
  
  interval = Math.floor(seconds / 3600);
  if (interval > 1) return `${interval} hours ago`;
  if (interval === 1) return '1 hour ago';
  
  interval = Math.floor(seconds / 60);
  if (interval > 1) return `${interval} minutes ago`;
  if (interval === 1) return '1 minute ago';
  
  return 'Just now';
};
</script>