<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useToast } from 'vue-toastification';

const toast = useToast();

const props = defineProps({
    roles: Array,
    departments: Array,
    positions: Array,
});

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    employee_id: '',
    role_id: '',
    department_id: '',
    position_id: '',
    contact_number: '',
    employment_status: 'regular',
    hire_date: '',
    contract_end_date: '',
    generate_qr: true,
});

const filteredPositions = computed(() => {
    if (!form.department_id) return props.positions;
    return props.positions.filter(p => p.department_id === form.department_id);
});

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: () => {
            toast.success('User created successfully');
            router.visit(route('users.index'));
        },
        onError: () => {
            toast.error('Please check the form for errors');
        },
    });
};

const watchDepartmentChange = () => {
    form.position_id = '';
};
</script>

<template>
    <Head title="Create User" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Create New User
                </h2>
                <Link
                    :href="route('users.index')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Back to Users
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Basic Information -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Full Name <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{ 'border-red-500': form.errors.name }"
                                    />
                                    <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Email Address <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.email"
                                        type="email"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{ 'border-red-500': form.errors.email }"
                                    />
                                    <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.email }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Employee ID <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.employee_id"
                                        type="text"
                                        required
                                        placeholder="e.g., EMP001"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{ 'border-red-500': form.errors.employee_id }"
                                    />
                                    <p v-if="form.errors.employee_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.employee_id }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Contact Number
                                    </label>
                                    <input
                                        v-model="form.contact_number"
                                        type="tel"
                                        placeholder="e.g., 09123456789"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{ 'border-red-500': form.errors.contact_number }"
                                    />
                                    <p v-if="form.errors.contact_number" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.contact_number }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Password -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Password</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Password <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.password"
                                        type="password"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{ 'border-red-500': form.errors.password }"
                                    />
                                    <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.password }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Confirm Password <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.password_confirmation"
                                        type="password"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- Role & Position -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Role & Position</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Role <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.role_id"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{ 'border-red-500': form.errors.role_id }"
                                    >
                                        <option value="">Select a role</option>
                                        <option v-for="role in roles" :key="role.id" :value="role.id">
                                            {{ role.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.role_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.role_id }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Department
                                    </label>
                                    <select
                                        v-model="form.department_id"
                                        @change="watchDepartmentChange"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{ 'border-red-500': form.errors.department_id }"
                                    >
                                        <option value="">Select a department</option>
                                        <option v-for="dept in departments" :key="dept.id" :value="dept.id">
                                            {{ dept.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.department_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.department_id }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Position
                                    </label>
                                    <select
                                        v-model="form.position_id"
                                        :disabled="!form.department_id"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 disabled:bg-gray-100"
                                        :class="{ 'border-red-500': form.errors.position_id }"
                                    >
                                        <option value="">Select a position</option>
                                        <option v-for="position in filteredPositions" :key="position.id" :value="position.id">
                                            {{ position.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.position_id" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.position_id }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Employment Details -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Employment Details</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Employment Status <span class="text-red-500">*</span>
                                    </label>
                                    <select
                                        v-model="form.employment_status"
                                        required
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{ 'border-red-500': form.errors.employment_status }"
                                    >
                                        <option value="regular">Regular</option>
                                        <option value="contractual">Contractual</option>
                                        <option value="probationary">Probationary</option>
                                    </select>
                                    <p v-if="form.errors.employment_status" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.employment_status }}
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Hire Date
                                    </label>
                                    <input
                                        v-model="form.hire_date"
                                        type="date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{ 'border-red-500': form.errors.hire_date }"
                                    />
                                    <p v-if="form.errors.hire_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.hire_date }}
                                    </p>
                                </div>

                                <div v-if="form.employment_status === 'contractual'">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Contract End Date
                                    </label>
                                    <input
                                        v-model="form.contract_end_date"
                                        type="date"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                        :class="{ 'border-red-500': form.errors.contract_end_date }"
                                    />
                                    <p v-if="form.errors.contract_end_date" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.contract_end_date }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code Generation -->
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-4">QR Code</h3>
                            <div class="flex items-center">
                                <input
                                    v-model="form.generate_qr"
                                    type="checkbox"
                                    id="generate_qr"
                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                />
                                <label for="generate_qr" class="ml-2 text-sm text-gray-700">
                                    Generate QR Code for this user
                                </label>
                            </div>
                            <p class="mt-2 text-sm text-gray-500">
                                QR codes will be used for quick user identification and login
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                            <Link
                                :href="route('users.index')"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                            >
                                <span v-if="form.processing">Creating...</span>
                                <span v-else>Create User</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
