<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import SidebarNavLink from '@/Components/Sidebar/SidebarNavLink.vue';
import SidebarNavGroup from '@/Components/Sidebar/SidebarNavGroup.vue';
import SidebarSection from '@/Components/Sidebar/SidebarSection.vue';
import SidebarOverlay from '@/Components/Sidebar/SidebarOverlay.vue';
import { usePermissions } from '@/Composables/usePermissions';

import {
    HomeIcon,
    ClipboardDocumentCheckIcon,
    FireIcon,
    WrenchScrewdriverIcon,
    MagnifyingGlassIcon,
    DocumentTextIcon,
    ClockIcon,
    CubeIcon,
    UsersIcon,
    BuildingOfficeIcon,
    BriefcaseIcon,
    ShieldCheckIcon,
    ListBulletIcon,
    Bars3Icon,
    BellIcon,
    XMarkIcon,
    ChevronDoubleLeftIcon,
    ChevronDoubleRightIcon,
} from '@heroicons/vue/24/outline';

const { canView, canCreate, canImport, canApprove, isAdmin, isSuperAdmin } = usePermissions();
const page = usePage();

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);
const notificationLoading = ref(false);
const notificationSummary = ref({
    pendingCount: 0,
    notifications: [],
    modules: [],
    hasApprovalAccess: false,
});
let approvalChannelName = null;

const closeSidebar = () => {
    sidebarOpen.value = false;
};

const toggleSidebarCollapse = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
};

const handleResize = () => {
    if (window.innerWidth >= 1024) {
        sidebarOpen.value = false;
    }
};

const hasApprovalAccess = computed(() => {
    return notificationSummary.value.hasApprovalAccess
        || canApprove('annealing')
        || canApprove('temperature')
        || canApprove('torque')
        || canApprove('welding');
});

const notificationCount = computed(() => Number(notificationSummary.value.pendingCount || 0));

const notificationCountLabel = computed(() => {
    return notificationCount.value > 99 ? '99+' : notificationCount.value.toString();
});

const latestNotifications = computed(() => notificationSummary.value.notifications || []);

const approvalModuleCounts = computed(() => {
    return (notificationSummary.value.modules || []).filter((module) => module.pendingCount > 0);
});

const updateNotificationSummary = (summary) => {
    notificationSummary.value = {
        pendingCount: Number(summary?.pendingCount || 0),
        notifications: Array.isArray(summary?.notifications) ? summary.notifications : [],
        modules: Array.isArray(summary?.modules) ? summary.modules : [],
        hasApprovalAccess: Boolean(summary?.hasApprovalAccess),
    };
};

const fetchApprovalNotifications = async () => {
    if (!hasApprovalAccess.value) {
        return;
    }

    notificationLoading.value = true;

    try {
        const response = await window.axios.get(route('approval-notifications.summary'));
        updateNotificationSummary(response.data);
    } catch (error) {
        console.error('Unable to load approval notifications', error);
    } finally {
        notificationLoading.value = false;
    }
};

const formatNotificationTime = (value) => {
    if (!value) {
        return '';
    }

    return new Date(value).toLocaleString();
};

const handleVisibilityChange = () => {
    if (document.visibilityState === 'visible') {
        fetchApprovalNotifications();
    }
};

onMounted(() => {
    window.addEventListener('resize', handleResize);

    if (hasApprovalAccess.value) {
        fetchApprovalNotifications();
        document.addEventListener('visibilitychange', handleVisibilityChange);
    }

    const userId = page.props.auth?.user?.id;

    if (hasApprovalAccess.value && window.Echo && userId) {
        approvalChannelName = `approval-notifications.${userId}`;
        window.Echo.private(approvalChannelName)
            .listen('.approval.notifications.changed', (event) => {
                updateNotificationSummary(event.summary);
            });
    }
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
    document.removeEventListener('visibilitychange', handleVisibilityChange);

    if (approvalChannelName && window.Echo) {
        window.Echo.leave(approvalChannelName);
    }
});

const sidebarWidth = computed(() => {
    return sidebarCollapsed.value ? 'w-16' : 'w-64';
});

const mainMargin = computed(() => {
    return sidebarCollapsed.value ? 'lg:ml-16' : 'lg:ml-64';
});
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Mobile sidebar overlay -->
        <SidebarOverlay :show="sidebarOpen" @close="closeSidebar" />

        <!-- Mobile sidebar -->
        <transition
            enter-active-class="transition-transform duration-300 ease-out"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition-transform duration-200 ease-in"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <div
                v-show="sidebarOpen"
                class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl lg:hidden flex flex-col"
            >
                <!-- Mobile sidebar header -->
                <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200">
                    <Link :href="route('dashboard')" class="flex items-center">
                        <ApplicationLogo class="h-8 w-auto text-indigo-600" />
                        <span class="ml-2 text-lg font-semibold text-gray-900">ICRS</span>
                    </Link>
                    <button
                        @click="closeSidebar"
                        class="p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <XMarkIcon class="h-6 w-6" />
                    </button>
                </div>

                <!-- Mobile sidebar content -->
                <nav class="flex-1 px-2 py-4 space-y-6 overflow-y-auto">
                    <!-- Main -->
                    <SidebarSection>
                        <SidebarNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                            :icon="HomeIcon"
                        >
                            Dashboard
                        </SidebarNavLink>
                    </SidebarSection>

                    <!-- Inspection Records -->
                    <SidebarSection title="Inspection Records">
                        <!-- Annealing Checks -->
                        <SidebarNavGroup
                            v-if="canView('annealing')"
                            title="Annealing Checks"
                            :icon="ClipboardDocumentCheckIcon"
                            :active="route().current('annealing-checks.*')"
                            :default-open="route().current('annealing-checks.*')"
                        >
                            <SidebarNavLink
                                :href="route('annealing-checks.index')"
                                :active="route().current('annealing-checks.index')"
                                :nested="true"
                            >
                                All Checks
                            </SidebarNavLink>
                            <SidebarNavLink
                                v-if="canCreate('annealing')"
                                :href="route('annealing-checks.create')"
                                :active="route().current('annealing-checks.create')"
                                :nested="true"
                            >
                                Add New
                            </SidebarNavLink>
                            <SidebarNavLink
                                v-if="canImport('annealing')"
                                :href="route('annealing-checks.import.form')"
                                :active="route().current('annealing-checks.import.form')"
                                :nested="true"
                            >
                                Import
                            </SidebarNavLink>
                            <SidebarNavLink
                                v-if="canApprove('annealing')"
                                :href="route('annealing-checks.approval')"
                                :active="route().current('annealing-checks.approval')"
                                :nested="true"
                            >
                                Pending Approvals
                            </SidebarNavLink>
                        </SidebarNavGroup>

                        <SidebarNavLink
                            v-if="canView('temperature')"
                            :href="route('temp-records.index')"
                            :active="route().current('temp-records.*') && !route().current('temp-records.approval')"
                            :icon="FireIcon"
                        >
                            Temperature Records
                        </SidebarNavLink>
                        <SidebarNavLink
                            v-if="canApprove('temperature')"
                            :href="route('temp-records.approval')"
                            :active="route().current('temp-records.approval')"
                            :icon="FireIcon"
                        >
                            Temperature Approvals
                        </SidebarNavLink>

                        <SidebarNavLink
                            v-if="canView('torque')"
                            :href="route('torque-records.index')"
                            :active="route().current('torque-records.*') && !route().current('torque-records.approval')"
                            :icon="WrenchScrewdriverIcon"
                        >
                            Torque Records
                        </SidebarNavLink>
                        <SidebarNavLink
                            v-if="canApprove('torque')"
                            :href="route('torque-records.approval')"
                            :active="route().current('torque-records.approval')"
                            :icon="WrenchScrewdriverIcon"
                        >
                            Torque Approvals
                        </SidebarNavLink>

                        <SidebarNavLink
                            v-if="canView('magnetism')"
                            :href="route('magnetism-checksheet.index')"
                            :active="route().current('magnetism-checksheet.*')"
                            :icon="MagnifyingGlassIcon"
                        >
                            Magnetism Checksheet
                        </SidebarNavLink>

                        <!-- Welding Checksheet -->
                        <SidebarNavGroup
                            v-if="canView('welding')"
                            title="Welding Checksheet"
                            :icon="DocumentTextIcon"
                            :active="route().current('welding-checksheets.*')"
                            :default-open="route().current('welding-checksheets.*')"
                        >
                            <SidebarNavLink
                                :href="route('welding-checksheets.index')"
                                :active="route().current('welding-checksheets.index')"
                                :nested="true"
                            >
                                All Checksheets
                            </SidebarNavLink>
                            <SidebarNavLink
                                v-if="canCreate('welding')"
                                :href="route('welding-checksheets.create')"
                                :active="route().current('welding-checksheets.create')"
                                :nested="true"
                            >
                                Add New
                            </SidebarNavLink>
                            <SidebarNavLink
                                v-if="canImport('welding')"
                                :href="route('welding-checksheets.import.form')"
                                :active="route().current('welding-checksheets.import.form')"
                                :nested="true"
                            >
                                Import
                            </SidebarNavLink>
                            <SidebarNavLink
                                v-if="canApprove('welding')"
                                :href="route('welding-checksheets.approval')"
                                :active="route().current('welding-checksheets.approval')"
                                :nested="true"
                            >
                                Pending Approvals
                            </SidebarNavLink>
                        </SidebarNavGroup>

                        <SidebarNavLink
                            v-if="canView('material')"
                            :href="route('material-monitoring-checksheets.index')"
                            :active="route().current('material-monitoring-checksheets.*')"
                            :icon="CubeIcon"
                        >
                            Material Monitoring
                        </SidebarNavLink>
                    </SidebarSection>

                    <!-- System -->
                    <SidebarSection title="System">
                        <SidebarNavLink
                            v-if="canView('modification')"
                            :href="route('modification-logs.index')"
                            :active="route().current('modification-logs.*')"
                            :icon="ClockIcon"
                        >
                            Modification Logs
                        </SidebarNavLink>
                    </SidebarSection>

                    <!-- Administration -->
                    <SidebarSection v-if="isAdmin" title="Administration">
                        <SidebarNavLink
                            :href="route('users.index')"
                            :active="route().current('users.*')"
                            :icon="UsersIcon"
                        >
                            User Management
                        </SidebarNavLink>

                        <SidebarNavGroup
                            v-if="isSuperAdmin"
                            title="Settings"
                            :icon="ShieldCheckIcon"
                            :active="route().current('admin.*')"
                            :default-open="route().current('admin.*')"
                        >
                            <SidebarNavLink
                                :href="route('admin.departments.index')"
                                :active="route().current('admin.departments.*')"
                                :icon="BuildingOfficeIcon"
                                :nested="true"
                            >
                                Departments
                            </SidebarNavLink>
                            <SidebarNavLink
                                :href="route('admin.positions.index')"
                                :active="route().current('admin.positions.*')"
                                :icon="BriefcaseIcon"
                                :nested="true"
                            >
                                Positions
                            </SidebarNavLink>
                            <SidebarNavLink
                                :href="route('admin.roles.index')"
                                :active="route().current('admin.roles.*')"
                                :icon="ShieldCheckIcon"
                                :nested="true"
                            >
                                Roles
                            </SidebarNavLink>
                        </SidebarNavGroup>

                        <SidebarNavLink
                            :href="route('activity-logs.index')"
                            :active="route().current('activity-logs.*')"
                            :icon="ListBulletIcon"
                        >
                            Activity Logs
                        </SidebarNavLink>
                    </SidebarSection>
                </nav>
            </div>
        </transition>

        <!-- Desktop sidebar -->
        <div
            :class="[
                'hidden lg:fixed lg:inset-y-0 lg:flex lg:flex-col bg-white border-r border-gray-200 transition-all duration-300',
                sidebarWidth
            ]"
        >
            <!-- Desktop sidebar header -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-gray-200">
                <Link :href="route('dashboard')" class="flex items-center overflow-hidden">
                    <ApplicationLogo class="h-8 w-8 flex-shrink-0 text-indigo-600" />
                    <transition
                        enter-active-class="transition-opacity duration-200"
                        enter-from-class="opacity-0"
                        enter-to-class="opacity-100"
                        leave-active-class="transition-opacity duration-150"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <span v-show="!sidebarCollapsed" class="ml-2 text-lg font-semibold text-gray-900 whitespace-nowrap">
                            ICRS
                        </span>
                    </transition>
                </Link>
                <button
                    @click="toggleSidebarCollapse"
                    class="p-1.5 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    :title="sidebarCollapsed ? 'Expand sidebar' : 'Collapse sidebar'"
                >
                    <ChevronDoubleLeftIcon v-if="!sidebarCollapsed" class="h-5 w-5" />
                    <ChevronDoubleRightIcon v-else class="h-5 w-5" />
                </button>
            </div>

            <!-- Desktop sidebar content -->
            <nav class="flex-1 px-2 py-4 space-y-6 overflow-y-auto overflow-x-hidden">
                <!-- Main -->
                <SidebarSection :collapsed="sidebarCollapsed">
                    <SidebarNavLink
                        :href="route('dashboard')"
                        :active="route().current('dashboard')"
                        :icon="HomeIcon"
                        :collapsed="sidebarCollapsed"
                    >
                        Dashboard
                    </SidebarNavLink>
                </SidebarSection>

                <!-- Inspection Records -->
                <SidebarSection title="Inspection Records" :collapsed="sidebarCollapsed">
                    <!-- Annealing Checks -->
                    <SidebarNavGroup
                        v-if="canView('annealing')"
                        title="Annealing Checks"
                        :icon="ClipboardDocumentCheckIcon"
                        :active="route().current('annealing-checks.*')"
                        :default-open="route().current('annealing-checks.*')"
                        :collapsed="sidebarCollapsed"
                    >
                        <SidebarNavLink
                            :href="route('annealing-checks.index')"
                            :active="route().current('annealing-checks.index')"
                            :nested="true"
                        >
                            All Checks
                        </SidebarNavLink>
                        <SidebarNavLink
                            v-if="canCreate('annealing')"
                            :href="route('annealing-checks.create')"
                            :active="route().current('annealing-checks.create')"
                            :nested="true"
                        >
                            Add New
                        </SidebarNavLink>
                        <SidebarNavLink
                            v-if="canImport('annealing')"
                            :href="route('annealing-checks.import.form')"
                            :active="route().current('annealing-checks.import.form')"
                            :nested="true"
                        >
                            Import
                        </SidebarNavLink>
                        <SidebarNavLink
                            v-if="canApprove('annealing')"
                            :href="route('annealing-checks.approval')"
                            :active="route().current('annealing-checks.approval')"
                            :nested="true"
                        >
                            Pending Approvals
                        </SidebarNavLink>
                    </SidebarNavGroup>

                    <SidebarNavLink
                        v-if="canView('temperature')"
                        :href="route('temp-records.index')"
                        :active="route().current('temp-records.*') && !route().current('temp-records.approval')"
                        :icon="FireIcon"
                        :collapsed="sidebarCollapsed"
                    >
                        Temperature Records
                    </SidebarNavLink>
                    <SidebarNavLink
                        v-if="canApprove('temperature')"
                        :href="route('temp-records.approval')"
                        :active="route().current('temp-records.approval')"
                        :icon="FireIcon"
                        :collapsed="sidebarCollapsed"
                    >
                        Temperature Approvals
                    </SidebarNavLink>

                    <SidebarNavLink
                        v-if="canView('torque')"
                        :href="route('torque-records.index')"
                        :active="route().current('torque-records.*') && !route().current('torque-records.approval')"
                        :icon="WrenchScrewdriverIcon"
                        :collapsed="sidebarCollapsed"
                    >
                        Torque Records
                    </SidebarNavLink>
                    <SidebarNavLink
                        v-if="canApprove('torque')"
                        :href="route('torque-records.approval')"
                        :active="route().current('torque-records.approval')"
                        :icon="WrenchScrewdriverIcon"
                        :collapsed="sidebarCollapsed"
                    >
                        Torque Approvals
                    </SidebarNavLink>

                    <SidebarNavLink
                        v-if="canView('magnetism')"
                        :href="route('magnetism-checksheet.index')"
                        :active="route().current('magnetism-checksheet.*')"
                        :icon="MagnifyingGlassIcon"
                        :collapsed="sidebarCollapsed"
                    >
                        Magnetism Checksheet
                    </SidebarNavLink>

                    <!-- Welding Checksheet -->
                    <SidebarNavGroup
                        v-if="canView('welding')"
                        title="Welding Checksheet"
                        :icon="DocumentTextIcon"
                        :active="route().current('welding-checksheets.*')"
                        :default-open="route().current('welding-checksheets.*')"
                        :collapsed="sidebarCollapsed"
                    >
                        <SidebarNavLink
                            :href="route('welding-checksheets.index')"
                            :active="route().current('welding-checksheets.index')"
                            :nested="true"
                        >
                            All Checksheets
                        </SidebarNavLink>
                        <SidebarNavLink
                            v-if="canCreate('welding')"
                            :href="route('welding-checksheets.create')"
                            :active="route().current('welding-checksheets.create')"
                            :nested="true"
                        >
                            Add New
                        </SidebarNavLink>
                        <SidebarNavLink
                            v-if="canImport('welding')"
                            :href="route('welding-checksheets.import.form')"
                            :active="route().current('welding-checksheets.import.form')"
                            :nested="true"
                        >
                            Import
                        </SidebarNavLink>
                        <SidebarNavLink
                            v-if="canApprove('welding')"
                            :href="route('welding-checksheets.approval')"
                            :active="route().current('welding-checksheets.approval')"
                            :nested="true"
                        >
                            Pending Approvals
                        </SidebarNavLink>
                    </SidebarNavGroup>

                    <SidebarNavLink
                        v-if="canView('material')"
                        :href="route('material-monitoring-checksheets.index')"
                        :active="route().current('material-monitoring-checksheets.*')"
                        :icon="CubeIcon"
                        :collapsed="sidebarCollapsed"
                    >
                        Material Monitoring
                    </SidebarNavLink>
                </SidebarSection>

                <!-- System -->
                <SidebarSection title="System" :collapsed="sidebarCollapsed">
                    <SidebarNavLink
                        v-if="canView('modification')"
                        :href="route('modification-logs.index')"
                        :active="route().current('modification-logs.*')"
                        :icon="ClockIcon"
                        :collapsed="sidebarCollapsed"
                    >
                        Modification Logs
                    </SidebarNavLink>
                </SidebarSection>

                <!-- Administration -->
                <SidebarSection v-if="isAdmin" title="Administration" :collapsed="sidebarCollapsed">
                    <SidebarNavLink
                        :href="route('users.index')"
                        :active="route().current('users.*')"
                        :icon="UsersIcon"
                        :collapsed="sidebarCollapsed"
                    >
                        User Management
                    </SidebarNavLink>

                    <SidebarNavGroup
                        v-if="isSuperAdmin"
                        title="Settings"
                        :icon="ShieldCheckIcon"
                        :active="route().current('admin.*')"
                        :default-open="route().current('admin.*')"
                        :collapsed="sidebarCollapsed"
                    >
                        <SidebarNavLink
                            :href="route('admin.departments.index')"
                            :active="route().current('admin.departments.*')"
                            :icon="BuildingOfficeIcon"
                            :nested="true"
                        >
                            Departments
                        </SidebarNavLink>
                        <SidebarNavLink
                            :href="route('admin.positions.index')"
                            :active="route().current('admin.positions.*')"
                            :icon="BriefcaseIcon"
                            :nested="true"
                        >
                            Positions
                        </SidebarNavLink>
                        <SidebarNavLink
                            :href="route('admin.roles.index')"
                            :active="route().current('admin.roles.*')"
                            :icon="ShieldCheckIcon"
                            :nested="true"
                        >
                            Roles
                        </SidebarNavLink>
                    </SidebarNavGroup>

                    <SidebarNavLink
                        :href="route('activity-logs.index')"
                        :active="route().current('activity-logs.*')"
                        :icon="ListBulletIcon"
                        :collapsed="sidebarCollapsed"
                    >
                        Activity Logs
                    </SidebarNavLink>
                </SidebarSection>
            </nav>
        </div>

        <!-- Main content area -->
        <div :class="['flex flex-col min-h-screen transition-all duration-300', mainMargin]">
            <!-- Top bar -->
            <header class="sticky top-0 z-30 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
                    <!-- Mobile menu button -->
                    <button
                        @click="sidebarOpen = true"
                        class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <Bars3Icon class="h-6 w-6" />
                    </button>

                    <!-- Page header slot or title -->
                    <div class="flex min-w-0 flex-1 items-center lg:ml-0 ml-4">
                        <div class="w-full min-w-0">
                            <slot name="header">
                                <h1 class="text-lg font-semibold text-gray-900">
                                    <!-- Default empty, pages can override -->
                                </h1>
                            </slot>
                        </div>
                    </div>

                    <!-- User dropdown -->
                    <div class="ml-4 flex shrink-0 items-center">
                        <Dropdown align="right" width="notification">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="flex items-center max-w-xs text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    <span class="sr-only">Open user menu</span>
                                    <div class="flex items-center">
                                        <div class="relative h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-indigo-600">
                                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                                            </span>
                                            <span
                                                v-if="hasApprovalAccess && notificationCount > 0"
                                                class="absolute -right-1.5 -top-1.5 inline-flex min-w-[1.25rem] items-center justify-center rounded-full bg-red-600 px-1.5 py-0.5 text-[10px] font-semibold leading-none text-white ring-2 ring-white"
                                            >
                                                {{ notificationCountLabel }}
                                            </span>
                                        </div>
                                        <span class="hidden sm:block ml-3 text-sm font-medium text-gray-700">
                                            {{ $page.props.auth.user.name }}
                                        </span>
                                        <svg class="hidden sm:block ml-2 h-4 w-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </template>

                            <template #content>
                                <div v-if="hasApprovalAccess" class="border-b border-gray-100">
                                    <div class="flex items-center justify-between px-4 py-3">
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900">Approval notifications</p>
                                            <p class="text-xs text-gray-500">{{ notificationCount }} pending item(s)</p>
                                        </div>
                                        <BellIcon class="h-5 w-5 text-gray-400" />
                                    </div>

                                    <div v-if="notificationLoading" class="px-4 py-6 text-center text-sm text-gray-500">
                                        Loading approvals...
                                    </div>

                                    <div v-else-if="latestNotifications.length" class="max-h-72 overflow-y-auto">
                                        <Link
                                            v-for="notification in latestNotifications"
                                            :key="notification.id"
                                            :href="route(notification.routeName)"
                                            class="block px-4 py-3 text-left hover:bg-gray-50"
                                        >
                                            <div class="flex items-start gap-3">
                                                <div class="mt-0.5 flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-indigo-50">
                                                    <BellIcon class="h-4 w-4 text-indigo-600" />
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="truncate text-xs font-medium uppercase text-indigo-600">
                                                        {{ notification.moduleLabel }}
                                                    </p>
                                                    <p class="mt-0.5 text-sm text-gray-900">
                                                        {{ notification.message }}
                                                    </p>
                                                    <p class="mt-1 text-xs text-gray-500">
                                                        {{ formatNotificationTime(notification.createdAt) }}
                                                    </p>
                                                </div>
                                            </div>
                                        </Link>
                                    </div>

                                    <div v-else class="px-4 py-6 text-center">
                                        <p class="text-sm font-medium text-gray-900">No pending approval notifications</p>
                                        <p class="mt-1 text-xs text-gray-500">New records that need your approval will appear here.</p>
                                    </div>

                                    <div v-if="approvalModuleCounts.length" class="border-t border-gray-100 px-4 py-3">
                                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                                            <Link
                                                v-for="approvalModule in approvalModuleCounts"
                                                :key="approvalModule.module"
                                                :href="route(approvalModule.routeName)"
                                                class="rounded-md border border-gray-200 px-3 py-2 hover:border-indigo-300 hover:bg-indigo-50"
                                            >
                                                <p class="truncate text-xs font-medium text-gray-700">{{ approvalModule.label }}</p>
                                                <p class="mt-0.5 text-sm font-semibold text-gray-900">{{ approvalModule.pendingCount }} pending</p>
                                            </Link>
                                        </div>
                                    </div>

                                    <Link
                                        :href="route('approvals.index')"
                                        class="block border-t border-gray-100 px-4 py-3 text-sm font-medium text-indigo-600 hover:bg-indigo-50"
                                    >
                                        View all pending approvals
                                    </Link>
                                </div>

                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="text-sm font-medium text-gray-900">{{ $page.props.auth.user.name }}</p>
                                    <p class="text-xs text-gray-500 truncate">{{ $page.props.auth.user.email }}</p>
                                </div>
                                <DropdownLink :href="route('profile.edit')">
                                    Profile
                                </DropdownLink>
                                <DropdownLink :href="route('logout')" method="post" as="button">
                                    Log Out
                                </DropdownLink>
                            </template>
                        </Dropdown>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1">
                <div class="py-6">
                    <div class=" mx-auto px-4 sm:px-6 lg:px-8">
                        <div
                            v-if="$page.props.errors?.duplicate"
                            class="mb-6 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-amber-900 shadow-sm"
                            role="alert"
                        >
                            <p class="font-semibold">Duplicate record detected</p>
                            <p class="mt-1 text-sm">{{ $page.props.errors.duplicate }}</p>
                        </div>
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
