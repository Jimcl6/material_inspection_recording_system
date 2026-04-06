<script setup>
import { ref, watch, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { debounce } from 'lodash';

const props = defineProps({
    activities: Object,
    filters: Object,
    users: Array,
    modules: Object,
    types: Object,
});

const search = ref(props.filters?.search || '');
const userId = ref(props.filters?.user_id || '');
const module = ref(props.filters?.module || '');
const type = ref(props.filters?.type || '');
const dateFrom = ref(props.filters?.date_from || '');
const dateTo = ref(props.filters?.date_to || '');

const selectedIds = ref([]);
const expandedRows = ref([]);

const applyFilters = debounce(() => {
    router.get(route('activity-logs.index'), {
        search: search.value || undefined,
        user_id: userId.value || undefined,
        module: module.value || undefined,
        type: type.value || undefined,
        date_from: dateFrom.value || undefined,
        date_to: dateTo.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch([search, userId, module, type, dateFrom, dateTo], applyFilters);

const clearFilters = () => {
    search.value = '';
    userId.value = '';
    module.value = '';
    type.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    router.get(route('activity-logs.index'));
};

const toggleRow = (id) => {
    const index = expandedRows.value.indexOf(id);
    if (index === -1) {
        expandedRows.value.push(id);
    } else {
        expandedRows.value.splice(index, 1);
    }
};

const isExpanded = (id) => expandedRows.value.includes(id);

const toggleSelect = (id) => {
    const index = selectedIds.value.indexOf(id);
    if (index === -1) {
        selectedIds.value.push(id);
    } else {
        selectedIds.value.splice(index, 1);
    }
};

const selectAll = computed({
    get: () => {
        return props.activities.data.length > 0 && 
               props.activities.data.every(a => selectedIds.value.includes(a.id));
    },
    set: (value) => {
        if (value) {
            selectedIds.value = props.activities.data.map(a => a.id);
        } else {
            selectedIds.value = [];
        }
    }
});

const deleteActivity = (activity) => {
    if (confirm('Are you sure you want to delete this activity log?')) {
        router.delete(route('activity-logs.destroy', activity.id), {
            preserveScroll: true,
        });
    }
};

const bulkDelete = () => {
    if (selectedIds.value.length === 0) {
        alert('Please select at least one activity to delete.');
        return;
    }
    if (confirm(`Are you sure you want to delete ${selectedIds.value.length} activity logs?`)) {
        router.post(route('activity-logs.bulk-destroy'), {
            activity_ids: selectedIds.value,
        }, {
            preserveScroll: true,
            onSuccess: () => {
                selectedIds.value = [];
            },
        });
    }
};

const getTypeColor = (type) => {
    const colors = {
        create: 'bg-green-100 text-green-800',
        update: 'bg-blue-100 text-blue-800',
        delete: 'bg-red-100 text-red-800',
        login: 'bg-purple-100 text-purple-800',
        logout: 'bg-gray-100 text-gray-800',
        approve: 'bg-emerald-100 text-emerald-800',
        reject: 'bg-orange-100 text-orange-800',
        import: 'bg-cyan-100 text-cyan-800',
        export: 'bg-indigo-100 text-indigo-800',
    };
    return colors[type] || 'bg-gray-100 text-gray-800';
};

const formatProperties = (properties) => {
    if (!properties) return null;
    return JSON.stringify(properties, null, 2);
};
</script>

<template>
    <AppLayout>
        <Head title="Activity Logs" />

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-semibold text-gray-900">Activity Logs</h1>
                        <p class="mt-1 text-sm text-gray-600">View and manage system activity logs</p>
                    </div>
                    <button
                        v-if="selectedIds.length > 0"
                        @click="bulkDelete"
                        class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete Selected ({{ selectedIds.length }})
                    </button>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow mb-6 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Search description..."
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">User</label>
                            <select
                                v-model="userId"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            >
                                <option value="">All Users</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Module</label>
                            <select
                                v-model="module"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            >
                                <option value="">All Modules</option>
                                <option v-for="(label, key) in modules" :key="key" :value="key">
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                            <select
                                v-model="type"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            >
                                <option value="">All Actions</option>
                                <option v-for="(label, key) in types" :key="key" :value="key">
                                    {{ label }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                            <input
                                v-model="dateFrom"
                                type="date"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                            <input
                                v-model="dateTo"
                                type="date"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                            />
                        </div>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <button
                            @click="clearFilters"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900"
                        >
                            Clear Filters
                        </button>
                    </div>
                </div>

                <!-- Table -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left">
                                    <input
                                        type="checkbox"
                                        v-model="selectAll"
                                        class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    />
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Module</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template v-for="activity in activities.data" :key="activity.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <input
                                            type="checkbox"
                                            :checked="selectedIds.includes(activity.id)"
                                            @change="toggleSelect(activity.id)"
                                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                        />
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div v-if="activity.user" class="text-sm">
                                            <div class="font-medium text-gray-900">{{ activity.user.name }}</div>
                                            <div class="text-gray-500 text-xs">{{ activity.user.email }}</div>
                                        </div>
                                        <span v-else class="text-gray-400 text-sm">System</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span
                                            :class="['px-2 py-1 text-xs font-semibold rounded-full capitalize', getTypeColor(activity.type)]"
                                        >
                                            {{ activity.type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 capitalize">
                                        {{ activity.module || '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-900 max-w-md truncate">
                                        {{ activity.description }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                        <div>{{ activity.time_ago }}</div>
                                        <div class="text-xs">{{ activity.created_at }}</div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        <button
                                            @click="toggleRow(activity.id)"
                                            class="text-blue-600 hover:text-blue-900 mr-3"
                                        >
                                            {{ isExpanded(activity.id) ? 'Collapse' : 'Expand' }}
                                        </button>
                                        <button
                                            @click="deleteActivity(activity)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                                <!-- Expanded Row -->
                                <tr v-if="isExpanded(activity.id)" class="bg-gray-50">
                                    <td colspan="7" class="px-4 py-4">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <h4 class="font-medium text-gray-900 mb-2">Details</h4>
                                                <dl class="space-y-1">
                                                    <div class="flex">
                                                        <dt class="w-24 text-gray-500">IP Address:</dt>
                                                        <dd class="text-gray-900">{{ activity.ip_address || 'N/A' }}</dd>
                                                    </div>
                                                    <div class="flex">
                                                        <dt class="w-24 text-gray-500">Created:</dt>
                                                        <dd class="text-gray-900">{{ activity.created_at }}</dd>
                                                    </div>
                                                </dl>
                                            </div>
                                            <div v-if="activity.properties">
                                                <h4 class="font-medium text-gray-900 mb-2">Properties</h4>
                                                <pre class="bg-gray-100 p-2 rounded text-xs overflow-x-auto max-h-40">{{ formatProperties(activity.properties) }}</pre>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            <tr v-if="activities.data.length === 0">
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    No activity logs found.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div v-if="activities.links && activities.links.length > 3" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        <nav class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ activities.from }} to {{ activities.to }} of {{ activities.total }} results
                            </div>
                            <div class="flex space-x-1">
                                <Link
                                    v-for="link in activities.links"
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    :class="[
                                        'px-3 py-2 text-sm rounded-md',
                                        link.active ? 'bg-blue-600 text-white' : 'text-gray-700 hover:bg-gray-100',
                                        !link.url ? 'opacity-50 cursor-not-allowed' : ''
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
