<script setup>
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { usePermissions } from '@/Composables/usePermissions';

const showingNavigationDropdown = ref(false);
const showAnnealingDropdown = ref(false);
const showDiaphragmWeldingDropdown = ref(false);
const showAdminDropdown = ref(false);

const { canView, canCreate, canImport, canApprove, isAdmin, isSuperAdmin } = usePermissions();
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <ApplicationLogo class="block h-9 w-auto" />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                    Dashboard
                                </NavLink>
                                
                                <!-- Annealing Checks Dropdown -->
                                <div v-if="canView('annealing')" class="relative" @mouseenter="showAnnealingDropdown = true" @mouseleave="showAnnealingDropdown = false">
                                    <button class="flex items-center text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                                            :class="{ 'bg-gray-100': route().current('annealing-checks.*') }">
                                        Annealing Checks
                                        <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div v-show="showAnnealingDropdown" class="absolute z-10 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                        <div class="py-1">
                                            <NavLink :href="route('annealing-checks.index')" :active="route().current('annealing-checks.index')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                All Checks
                                            </NavLink>
                                            <NavLink v-if="canCreate('annealing')" :href="route('annealing-checks.create')" :active="route().current('annealing-checks.create')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Add New
                                            </NavLink>
                                            <NavLink v-if="canImport('annealing')" :href="route('annealing-checks.import.form')" :active="route().current('annealing-checks.import.form')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Import
                                            </NavLink>
                                            <NavLink v-if="canApprove('annealing')" 
                                                    :href="route('annealing-checks.approval')" 
                                                    :active="route().current('annealing-checks.approval')" 
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-t border-gray-100">
                                                Pending Approvals
                                            </NavLink>
                                        </div>
                                    </div>
                                </div>
                                
                                <NavLink v-if="canView('temperature')" :href="route('temp-records.index')" :active="route().current('temp-records.*')">
                                    Temperature Records
                                </NavLink>
                                <NavLink v-if="canView('torque')" :href="route('torque-records.index')" :active="route().current('torque-records.*')">
                                    Torque Records
                                </NavLink>
                                <NavLink v-if="canView('magnetism')" :href="route('magnetism-checksheet.index')" :active="route().current('magnetism-checksheet.*')">
                                    Magnetism Checksheet
                                </NavLink>
                                
                                <!-- Diaphragm Welding Dropdown -->
                                <div v-if="canView('diaphragm')" class="relative" @mouseenter="showDiaphragmWeldingDropdown = true" @mouseleave="showDiaphragmWeldingDropdown = false">
                                    <button class="flex items-center text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                                            :class="{ 'bg-gray-100': route().current('diaphragm-welding.*') }">
                                        Diaphragm Welding
                                        <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div v-show="showDiaphragmWeldingDropdown" class="absolute z-10 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                        <div class="py-1">
                                            <NavLink :href="route('diaphragm-welding.index')" :active="route().current('diaphragm-welding.index')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                All Checksheets
                                            </NavLink>
                                            <NavLink v-if="canCreate('diaphragm')" :href="route('diaphragm-welding.create')" :active="route().current('diaphragm-welding.create')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Add New
                                            </NavLink>
                                            <NavLink v-if="canImport('diaphragm')" :href="route('diaphragm-welding.import.form')" :active="route().current('diaphragm-welding.import.form')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Import
                                            </NavLink>
                                            <NavLink v-if="canApprove('diaphragm')" 
                                                    :href="route('diaphragm-welding.approval')" 
                                                    :active="route().current('diaphragm-welding.approval')" 
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-t border-gray-100">
                                                Pending Approvals
                                            </NavLink>
                                        </div>
                                    </div>
                                </div>
                                
                                <NavLink v-if="canView('modification')" :href="route('modification-logs.index')" :active="route().current('modification-logs.*')">
                                    Modification Logs
                                </NavLink>
                                <NavLink v-if="canView('material')" :href="route('material-monitoring-checksheets.index')" :active="route().current('material-monitoring-checksheets.*')">
                                    Mtrl Monitoring Checksheet
                                </NavLink>
                                <NavLink v-if="isAdmin" :href="route('users.index')" :active="route().current('users.*')">
                                    User Management
                                </NavLink>
                                
                                <!-- Admin Dropdown (for admin and super_admin) -->
                                <div v-if="isAdmin" 
                                     class="relative" @mouseenter="showAdminDropdown = true" @mouseleave="showAdminDropdown = false">
                                    <button class="flex items-center text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
                                            :class="{ 'bg-gray-100': route().current('admin.*') || route().current('activity-logs.*') }">
                                        Admin
                                        <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <div v-show="showAdminDropdown" class="absolute z-10 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                                        <div class="py-1">
                                            <NavLink v-if="isSuperAdmin"
                                                    :href="route('admin.departments.index')" 
                                                    :active="route().current('admin.departments.*')" 
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Departments
                                            </NavLink>
                                            <NavLink v-if="isSuperAdmin"
                                                    :href="route('admin.positions.index')" 
                                                    :active="route().current('admin.positions.*')" 
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Positions
                                            </NavLink>
                                            <NavLink v-if="isSuperAdmin"
                                                    :href="route('admin.roles.index')" 
                                                    :active="route().current('admin.roles.*')" 
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                                Roles
                                            </NavLink>
                                            <NavLink :href="route('activity-logs.index')" 
                                                    :active="route().current('activity-logs.*')" 
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 border-t border-gray-100">
                                                Activity Logs
                                            </NavLink>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Settings Dropdown -->
                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <div class="ml-3 relative">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="ml-2 -mr-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink :href="route('profile.edit')"> Profile </DropdownLink>
                                        <DropdownLink :href="route('logout')" method="post" as="button">
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex': !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex': showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden"
                >
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                        
                        <!-- Annealing Section (Mobile) -->
                        <template v-if="canView('annealing')">
                            <ResponsiveNavLink
                                :href="route('annealing-checks.index')"
                                :active="route().current('annealing-checks.index')"
                            >
                                Annealing Checks
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="canCreate('annealing')"
                                :href="route('annealing-checks.create')"
                                :active="route().current('annealing-checks.create')"
                                class="pl-8"
                            >
                                Add New
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="canImport('annealing')"
                                :href="route('annealing-checks.import.form')"
                                :active="route().current('annealing-checks.import.form')"
                                class="pl-8"
                            >
                                Import
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="canApprove('annealing')"
                                :href="route('annealing-checks.approval')"
                                :active="route().current('annealing-checks.approval')"
                                class="pl-8"
                            >
                                Pending Approvals
                            </ResponsiveNavLink>
                        </template>
                        
                        <ResponsiveNavLink
                            v-if="canView('temperature')"
                            :href="route('temp-records.index')"
                            :active="route().current('temp-records.*')"
                        >
                            Temperature Records
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="canView('torque')"
                            :href="route('torque-records.index')"
                            :active="route().current('torque-records.*')"
                        >
                            Torque Records
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="canView('magnetism')"
                            :href="route('magnetism-checksheet.index')"
                            :active="route().current('magnetism-checksheet.*')"
                        >
                            Magnetism Checksheet
                        </ResponsiveNavLink>
                        
                        <!-- Diaphragm Welding Section (Mobile) -->
                        <div v-if="canView('diaphragm')" class="border-t border-gray-200 pt-2 mt-2">
                            <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Diaphragm Welding</div>
                            <ResponsiveNavLink
                                :href="route('diaphragm-welding.index')"
                                :active="route().current('diaphragm-welding.index')"
                            >
                                All Checksheets
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="canCreate('diaphragm')"
                                :href="route('diaphragm-welding.create')"
                                :active="route().current('diaphragm-welding.create')"
                            >
                                Add New
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="canImport('diaphragm')"
                                :href="route('diaphragm-welding.import.form')"
                                :active="route().current('diaphragm-welding.import.form')"
                            >
                                Import
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="canApprove('diaphragm')"
                                :href="route('diaphragm-welding.approval')"
                                :active="route().current('diaphragm-welding.approval')"
                            >
                                Pending Approvals
                            </ResponsiveNavLink>
                        </div>
                        
                        <ResponsiveNavLink
                            v-if="canView('modification')"
                            :href="route('modification-logs.index')"
                            :active="route().current('modification-logs.*')"
                        >
                            Modification Logs
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="canView('material')"
                            :href="route('material-monitoring-checksheets.index')"
                            :active="route().current('material-monitoring-checksheets.*')"
                        >
                            Material Parts
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            v-if="isAdmin"
                            :href="route('users.index')"
                            :active="route().current('users.*')"
                        >
                            User Management
                        </ResponsiveNavLink>
                        
                        <!-- Admin Section (Mobile) -->
                        <div v-if="isAdmin" class="border-t border-gray-200 pt-2 mt-2">
                            <div class="px-4 py-2 text-xs font-semibold text-gray-400 uppercase">Admin</div>
                            <ResponsiveNavLink
                                v-if="isSuperAdmin"
                                :href="route('admin.departments.index')"
                                :active="route().current('admin.departments.*')"
                            >
                                Departments
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="isSuperAdmin"
                                :href="route('admin.positions.index')"
                                :active="route().current('admin.positions.*')"
                            >
                                Positions
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                v-if="isSuperAdmin"
                                :href="route('admin.roles.index')"
                                :active="route().current('admin.roles.*')"
                            >
                                Roles
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('activity-logs.index')"
                                :active="route().current('activity-logs.*')"
                            >
                                Activity Logs
                            </ResponsiveNavLink>
                        </div>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')"> Profile </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
