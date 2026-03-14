<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { debounce } from 'lodash';
import {
    MagnifyingGlassIcon,
    FunnelIcon,
    UserPlusIcon,
    PencilIcon,
    EyeIcon,
    TrashIcon,
    QrCodeIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
} from '@heroicons/vue/24/outline';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    users: Object,
    filters: Object,
    roles: Array,
    departments: Array,
    positions: Array,
});

const search = ref(props.filters.search || '');
const selectedRole = ref(props.filters.role || '');
const selectedDepartment = ref(props.filters.department || '');
const selectedStatus = ref(props.filters.status || '');
const selectedEmploymentStatus = ref(props.filters.employment_status || '');
const selectedUsers = ref([]);
const showBulkActions = ref(false);

watch([search, selectedRole, selectedDepartment, selectedStatus, selectedEmploymentStatus], debounce(() => {
    router.get(
        route('users.index'),
        {
            search: search.value,
            role: selectedRole.value,
            department: selectedDepartment.value,
            status: selectedStatus.value,
            employment_status: selectedEmploymentStatus.value,
        },
        { preserveState: true }
    );
}, 300));

const selectAll = computed({
    get: () => selectedUsers.value.length === props.users.data.length,
    set: (value) => {
        if (value) {
            selectedUsers.value = props.users.data.map(user => user.id);
        } else {
            selectedUsers.value = [];
        }
    },
});

watch(selectedUsers, (value) => {
    showBulkActions.value = value.length > 0;
});

const getStatusBadge = (status) => {
    const badges = {
        active: { bg: 'bg-green-100', text: 'text-green-800', icon: CheckCircleIcon },
        inactive: { bg: 'bg-gray-100', text: 'text-gray-800', icon: XCircleIcon },
        suspended: { bg: 'bg-red-100', text: 'text-red-800', icon: XCircleIcon },
    };
    return badges[status] || badges.inactive;
};

const getEmploymentBadge = (status) => {
    const badges = {
        regular: { bg: 'bg-blue-100', text: 'text-blue-800' },
        contractual: { bg: 'bg-yellow-100', text: 'text-yellow-800' },
        probationary: { bg: 'bg-purple-100', text: 'text-purple-800' },
    };
    return badges[status] || badges.regular;
};

const deleteUser = (user) => {
    if (confirm(`Are you sure you want to deactivate ${user.name}?`)) {
        router.delete(route('users.destroy', user.id), {
            onSuccess: () => {
                toast.success('User deactivated successfully');
            },
            onError: () => {
                toast.error('Failed to deactivate user');
            },
        });
    }
};

const performBulkAction = (action) => {
    if (!selectedUsers.value.length) return;

    const actionText = action === 'activate' ? 'activate' : action === 'deactivate' ? 'deactivate' : 'delete';
    if (confirm(`Are you sure you want to ${actionText} selected users?`)) {
        router.post(
            route('users.bulk-action'),
            {
                action,
                user_ids: selectedUsers.value,
            },
            {
                onSuccess: () => {
                    toast.success(`Users ${actionText}d successfully`);
                    selectedUsers.value = [];
                    showBulkActions.value = false;
                },
                onError: () => {
                    toast.error(`Failed to ${actionText} users`);
                },
            }
        );
    }
};
</script>

<template>
    <Head title="User Management" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    User Management
                </h2>
                <Link
                    :href="route('users.create')"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    <UserPlusIcon class="w-4 h-4 mr-2" />
                    Add User
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex items-center mb-4">
                            <FunnelIcon class="w-5 h-5 text-gray-500 mr-2" />
                            <h3 class="text-lg font-medium text-gray-900">Filters</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                                <div class="relative">
                                    <input
                                        v-model="search"
                                        type="text"
                                        placeholder="Search users..."
                                        class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                    />
                                    <MagnifyingGlassIcon class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" />
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                                <select
                                    v-model="selectedRole"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">All Roles</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.id">
                                        {{ role.name }}
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Department</label>
                                <select
                                    v-model="selectedDepartment"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">All Departments</option>
                                    <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                        {{ dept.name }}
                                    </option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                <select
                                    v-model="selectedStatus"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="suspended">Suspended</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Employment</label>
                                <select
                                    v-model="selectedEmploymentStatus"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                >
                                    <option value="">All Types</option>
                                    <option value="regular">Regular</option>
                                    <option value="contractual">Contractual</option>
                                    <option value="probationary">Probationary</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bulk Actions -->
                <div
                    v-if="showBulkActions"
                    class="bg-blue-50 border border-blue-200 rounded-md p-4 mb-6"
                >
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-blue-800">
                            {{ selectedUsers.length }} user(s) selected
                        </span>
                        <div class="space-x-2">
                            <button
                                @click="performBulkAction('activate')"
                                class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700"
                            >
                                Activate
                            </button>
                            <button
                                @click="performBulkAction('deactivate')"
                                class="px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-700"
                            >
                                Deactivate
                            </button>
                            <button
                                @click="performBulkAction('delete')"
                                class="px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-700"
                            >
                                Delete
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left">
                                        <input
                                            type="checkbox"
                                            v-model="selectAll"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        User
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Role
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Department
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Position
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Employment
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        QR Code
                                    </th>
                                    <th class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="user in users.data" :key="user.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <input
                                            type="checkbox"
                                            :value="user.id"
                                            v-model="selectedUsers"
                                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ user.name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ user.email }}
                                            </div>
                                            <div class="text-xs text-gray-400">
                                                ID: {{ user.employee_id }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">
                                            {{ user.role?.name || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">
                                            {{ user.department?.name || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-900">
                                            {{ user.position?.name || '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                getStatusBadge(user.status).bg,
                                                getStatusBadge(user.status).text,
                                            ]"
                                        >
                                            <component
                                                :is="getStatusBadge(user.status).icon"
                                                class="w-4 h-4 mr-1"
                                            />
                                            {{ user.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            v-if="user.qr_code && user.qr_code.employment_status"
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                getEmploymentBadge(user.qr_code.employment_status).bg,
                                                getEmploymentBadge(user.qr_code.employment_status).text,
                                            ]"
                                        >
                                            {{ user.qr_code.employment_status }}
                                        </span>
                                        <span v-else class="text-sm text-gray-400">-</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <QrCodeIcon
                                            v-if="user.qr_code"
                                            class="w-5 h-5 text-green-500"
                                            title="QR Code Generated"
                                        />
                                        <XCircleIcon
                                            v-else
                                            class="w-5 h-5 text-gray-400"
                                            title="No QR Code"
                                        />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            <Link
                                                :href="route('users.show', user.id)"
                                                class="text-indigo-600 hover:text-indigo-900"
                                                title="View"
                                            >
                                                <EyeIcon class="w-5 h-5" />
                                            </Link>
                                            <Link
                                                :href="route('users.edit', user.id)"
                                                class="text-blue-600 hover:text-blue-900"
                                                title="Edit"
                                            >
                                                <PencilIcon class="w-5 h-5" />
                                            </Link>
                                            <button
                                                @click="deleteUser(user)"
                                                class="text-red-600 hover:text-red-900"
                                                title="Deactivate"
                                            >
                                                <TrashIcon class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link
                                v-if="users.prev_page_url"
                                :href="users.prev_page_url"
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Previous
                            </Link>
                            <span v-else class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-gray-100 cursor-not-allowed">
                                Previous
                            </span>
                            <Link
                                v-if="users.next_page_url"
                                :href="users.next_page_url"
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Next
                            </Link>
                            <span v-else class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-300 bg-gray-100 cursor-not-allowed">
                                Next
                            </span>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing
                                    <span class="font-medium">{{ users.from }}</span>
                                    to
                                    <span class="font-medium">{{ users.to }}</span>
                                    of
                                    <span class="font-medium">{{ users.total }}</span>
                                    results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                    <!-- Pagination links will be rendered by Inertia -->
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
