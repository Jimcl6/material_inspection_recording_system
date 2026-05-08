<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
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
    XMarkIcon,
    ChevronDoubleLeftIcon,
    ChevronDoubleRightIcon,
} from '@heroicons/vue/24/outline';

const { canView, canCreate, canImport, canApprove, isAdmin, isSuperAdmin } = usePermissions();

const sidebarOpen = ref(false);
const sidebarCollapsed = ref(false);

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

onMounted(() => {
    window.addEventListener('resize', handleResize);
});

onUnmounted(() => {
    window.removeEventListener('resize', handleResize);
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
                        <span class="ml-2 text-lg font-semibold text-gray-900">MIRS</span>
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
                            :active="route().current('temp-records.*')"
                            :icon="FireIcon"
                        >
                            Temperature Records
                        </SidebarNavLink>

                        <SidebarNavLink 
                            v-if="canView('torque')"
                            :href="route('torque-records.index')" 
                            :active="route().current('torque-records.*')"
                            :icon="WrenchScrewdriverIcon"
                        >
                            Torque Records
                        </SidebarNavLink>

                        <SidebarNavLink 
                            v-if="canView('magnetism')"
                            :href="route('magnetism-checksheet.index')" 
                            :active="route().current('magnetism-checksheet.*')"
                            :icon="MagnifyingGlassIcon"
                        >
                            Magnetism Checksheet
                        </SidebarNavLink>

                        <!-- Diaphragm Welding -->
                        <SidebarNavGroup 
                            v-if="canView('diaphragm')"
                            title="Diaphragm Welding"
                            :icon="DocumentTextIcon"
                            :active="route().current('diaphragm-welding.*')"
                            :default-open="route().current('diaphragm-welding.*')"
                        >
                            <SidebarNavLink 
                                :href="route('diaphragm-welding.index')" 
                                :active="route().current('diaphragm-welding.index')"
                                :nested="true"
                            >
                                All Checksheets
                            </SidebarNavLink>
                            <SidebarNavLink 
                                v-if="canCreate('diaphragm')"
                                :href="route('diaphragm-welding.create')" 
                                :active="route().current('diaphragm-welding.create')"
                                :nested="true"
                            >
                                Add New
                            </SidebarNavLink>
                            <SidebarNavLink 
                                v-if="canImport('diaphragm')"
                                :href="route('diaphragm-welding.import.form')" 
                                :active="route().current('diaphragm-welding.import.form')"
                                :nested="true"
                            >
                                Import
                            </SidebarNavLink>
                            <SidebarNavLink 
                                v-if="canApprove('diaphragm')"
                                :href="route('diaphragm-welding.approval')" 
                                :active="route().current('diaphragm-welding.approval')"
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
                            MIRS
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
                        :active="route().current('temp-records.*')"
                        :icon="FireIcon"
                        :collapsed="sidebarCollapsed"
                    >
                        Temperature Records
                    </SidebarNavLink>

                    <SidebarNavLink 
                        v-if="canView('torque')"
                        :href="route('torque-records.index')" 
                        :active="route().current('torque-records.*')"
                        :icon="WrenchScrewdriverIcon"
                        :collapsed="sidebarCollapsed"
                    >
                        Torque Records
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

                    <!-- Diaphragm Welding -->
                    <SidebarNavGroup 
                        v-if="canView('diaphragm')"
                        title="Diaphragm Welding"
                        :icon="DocumentTextIcon"
                        :active="route().current('diaphragm-welding.*')"
                        :default-open="route().current('diaphragm-welding.*')"
                        :collapsed="sidebarCollapsed"
                    >
                        <SidebarNavLink 
                            :href="route('diaphragm-welding.index')" 
                            :active="route().current('diaphragm-welding.index')"
                            :nested="true"
                        >
                            All Checksheets
                        </SidebarNavLink>
                        <SidebarNavLink 
                            v-if="canCreate('diaphragm')"
                            :href="route('diaphragm-welding.create')" 
                            :active="route().current('diaphragm-welding.create')"
                            :nested="true"
                        >
                            Add New
                        </SidebarNavLink>
                        <SidebarNavLink 
                            v-if="canImport('diaphragm')"
                            :href="route('diaphragm-welding.import.form')" 
                            :active="route().current('diaphragm-welding.import.form')"
                            :nested="true"
                        >
                            Import
                        </SidebarNavLink>
                        <SidebarNavLink 
                            v-if="canApprove('diaphragm')"
                            :href="route('diaphragm-welding.approval')" 
                            :active="route().current('diaphragm-welding.approval')"
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
                    <div class="flex-1 flex items-center lg:ml-0 ml-4">
                        <slot name="header">
                            <h1 class="text-lg font-semibold text-gray-900">
                                <!-- Default empty, pages can override -->
                            </h1>
                        </slot>
                    </div>

                    <!-- User dropdown -->
                    <div class="flex items-center">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="flex items-center max-w-xs text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    <span class="sr-only">Open user menu</span>
                                    <div class="flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center">
                                            <span class="text-sm font-medium text-indigo-600">
                                                {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
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
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <slot />
                    </div>
                </div>
            </main>
        </div>
    </div>
</template>
