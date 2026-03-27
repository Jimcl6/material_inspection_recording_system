<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import DataTableFilters from '@/Components/DataTableFilters.vue';
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

const filterConfig = computed(() => [
    {
        type: 'text',
        key: 'search',
        label: 'Search',
        placeholder: 'Search users...',
    },
    {
        type: 'select',
        key: 'role',
        label: 'Role',
        placeholder: 'All Roles',
        options: (props.roles || []).map((r) => ({ value: r.id, label: r.name })),
    },
    {
        type: 'select',
        key: 'department',
        label: 'Department',
        placeholder: 'All Departments',
        options: (props.departments || []).map((d) => ({ value: d.id, label: d.name })),
    },
    {
        type: 'select',
        key: 'status',
        label: 'Status',
        placeholder: 'All Status',
        options: [
            { value: 'active', label: 'Active' },
            { value: 'inactive', label: 'Inactive' },
            { value: 'suspended', label: 'Suspended' },
        ],
    },
    {
        type: 'select',
        key: 'employment_status',
        label: 'Employment',
        placeholder: 'All Types',
        options: [
            { value: 'regular', label: 'Regular' },
            { value: 'contractual', label: 'Contractual' },
            { value: 'probationary', label: 'Probationary' },
        ],
    },
]);

const selectedUsers = ref([]);
const showBulkActions = ref(false);

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
                <DataTableFilters
                    :filters="filters"
                    :filter-config="filterConfig"
                    route-name="users.index"
                />

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
