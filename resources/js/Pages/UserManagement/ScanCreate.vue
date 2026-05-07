<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useToast } from 'vue-toastification';
import * as Html5Qrcode from 'html5-qrcode';

const toast = useToast();

const props = defineProps({
    roles: Array,
    departments: Array,
    positions: Array,
});

// Scanner state
const scanner = ref(null);
const isScanning = ref(false);
const manualInput = ref('');
const isProcessing = ref(false);

// Form state
const showForm = ref(false);
const scannedData = ref(null);

// Duplicate user modal
const showDuplicateModal = ref(false);
const existingUser = ref(null);

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

const startScanner = () => {
    isScanning.value = true;
    
    scanner.value = new Html5Qrcode.Html5QrcodeScanner(
        'qr-reader',
        {
            fps: 10,
            qrbox: { width: 250, height: 250 },
            rememberLastUsedCamera: true,
            supportedScanTypes: [Html5Qrcode.Html5QrcodeScanner.SCAN_TYPE_CAMERA],
        },
        false
    );

    scanner.value.render(
        (decodedText) => {
            handleScanSuccess(decodedText);
        },
        (errorMessage) => {
            console.warn(errorMessage);
        }
    );
};

const stopScanner = () => {
    if (scanner.value) {
        scanner.value.clear();
        scanner.value = null;
    }
    isScanning.value = false;
};

const handleScanSuccess = async (decodedText) => {
    if (isProcessing.value) return;
    
    isProcessing.value = true;
    stopScanner();

    try {
        const response = await fetch(route('users.parse-badge'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ qr_data: decodedText }),
        });

        const data = await response.json();

        if (response.status === 409 && data.exists) {
            // Duplicate user found
            existingUser.value = data.existing_user;
            showDuplicateModal.value = true;
            toast.warning('User with this Employee ID already exists');
        } else if (data.success) {
            // Pre-fill the form
            scannedData.value = data.data;
            form.name = data.data.name;
            form.employee_id = data.data.employee_id;
            form.employment_status = data.data.employment_status;
            showForm.value = true;
            toast.success('QR code scanned successfully! Please complete the form.');
        } else {
            toast.error(data.message || 'Invalid QR code format');
            startScanner();
        }
    } catch (error) {
        console.error('Scan error:', error);
        toast.error('Failed to process QR code');
        startScanner();
    } finally {
        isProcessing.value = false;
    }
};

const handleManualInput = async () => {
    if (!manualInput.value.trim()) {
        toast.error('Please enter QR data');
        return;
    }

    await handleScanSuccess(manualInput.value.trim());
    manualInput.value = '';
};

const resetScan = () => {
    showForm.value = false;
    scannedData.value = null;
    form.reset();
    startScanner();
};

const closeDuplicateModal = () => {
    showDuplicateModal.value = false;
    existingUser.value = null;
    startScanner();
};

const editExistingUser = () => {
    if (existingUser.value) {
        router.visit(route('users.edit', existingUser.value.id));
    }
};

const submit = () => {
    form.post(route('users.store'), {
        onSuccess: () => {
            toast.success('User created successfully');
            router.visit(route('users.index'));
        },
        onError: (errors) => {
            toast.error('Please check the form for errors');
        },
    });
};

const watchDepartmentChange = () => {
    form.position_id = '';
};

onMounted(() => {
    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
        startScanner();
    } else {
        toast.error('Camera not available on this device');
    }
});

onUnmounted(() => {
    stopScanner();
});
</script>

<template>
    <Head title="Scan to Create User" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Scan to Create User
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
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Scanner Section -->
                <div v-if="!showForm" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Scan Employee Badge QR Code
                        </h3>
                        <p class="text-sm text-gray-500 mb-4">
                            Scan the QR code on the employee's nameplate badge to pre-fill the user creation form.
                            <br />
                            <span class="font-medium">Expected format:</span> Employee ID , Full Name , Employment Status
                        </p>
                        
                        <div class="flex justify-center mb-6">
                            <div id="qr-reader" class="w-full max-w-md"></div>
                        </div>

                        <!-- Manual Input -->
                        <div class="mt-6 border-t pt-6">
                            <h4 class="text-md font-medium text-gray-900 mb-3">
                                Manual QR Code Input
                            </h4>
                            <div class="flex space-x-2">
                                <input
                                    v-model="manualInput"
                                    type="text"
                                    placeholder="e.g., 25-431 , JOHN DOE , REGULAR"
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                    @keyup.enter="handleManualInput"
                                    :disabled="isProcessing"
                                />
                                <button
                                    @click="handleManualInput"
                                    :disabled="isProcessing"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
                                    <span v-if="isProcessing">Processing...</span>
                                    <span v-else>Process</span>
                                </button>
                            </div>
                        </div>

                        <!-- Controls -->
                        <div class="mt-6 flex justify-center space-x-4">
                            <button
                                v-if="!isScanning"
                                @click="startScanner"
                                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
                            >
                                Start Scanner
                            </button>
                            <button
                                v-if="isScanning"
                                @click="stopScanner"
                                class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                            >
                                Stop Scanner
                            </button>
                            <Link
                                :href="route('users.create')"
                                class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
                            >
                                Manual Entry Instead
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- User Creation Form (shown after successful scan) -->
                <div v-if="showForm" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <!-- Scanned Data Summary -->
                    <div class="p-4 bg-green-50 border-b border-green-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-sm font-medium text-green-800">
                                    QR Code Scanned: {{ scannedData?.employee_id }} - {{ scannedData?.name }}
                                </span>
                            </div>
                            <button
                                @click="resetScan"
                                class="text-sm text-green-700 hover:text-green-900 underline"
                            >
                                Scan Another
                            </button>
                        </div>
                    </div>

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
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 bg-green-50"
                                        :class="{ 'border-red-500': form.errors.name }"
                                    />
                                    <p class="mt-1 text-xs text-green-600">Pre-filled from QR code</p>
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
                                        readonly
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-green-50 cursor-not-allowed"
                                        :class="{ 'border-red-500': form.errors.employee_id }"
                                    />
                                    <p class="mt-1 text-xs text-green-600">Pre-filled from QR code (read-only)</p>
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
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 bg-green-50"
                                        :class="{ 'border-red-500': form.errors.employment_status }"
                                    >
                                        <option value="regular">Regular</option>
                                        <option value="contractual">Contractual</option>
                                        <option value="probationary">Probationary</option>
                                    </select>
                                    <p class="mt-1 text-xs text-green-600">Pre-filled from QR code</p>
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
                            <button
                                type="button"
                                @click="resetScan"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Cancel
                            </button>
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

        <!-- Duplicate User Modal -->
        <div v-if="showDuplicateModal" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeDuplicateModal"></div>

                <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg">
                    <div class="flex items-center justify-center w-12 h-12 mx-auto bg-yellow-100 rounded-full mb-4">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>

                    <h3 class="text-lg font-medium text-center text-gray-900 mb-2">
                        User Already Exists
                    </h3>

                    <p class="text-sm text-gray-500 text-center mb-4">
                        An account with this Employee ID already exists in the system.
                    </p>

                    <div v-if="existingUser" class="bg-gray-50 rounded-md p-4 mb-4">
                        <p class="text-sm"><strong>Name:</strong> {{ existingUser.name }}</p>
                        <p class="text-sm"><strong>Employee ID:</strong> {{ existingUser.employee_id }}</p>
                        <p class="text-sm"><strong>Email:</strong> {{ existingUser.email }}</p>
                        <p class="text-sm"><strong>Status:</strong> 
                            <span :class="{
                                'text-green-600': existingUser.status === 'active',
                                'text-red-600': existingUser.status === 'inactive',
                                'text-yellow-600': existingUser.status === 'suspended'
                            }">
                                {{ existingUser.status }}
                            </span>
                        </p>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button
                            @click="closeDuplicateModal"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Scan Another
                        </button>
                        <button
                            @click="editExistingUser"
                            class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            Edit Existing User
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
#qr-reader {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    overflow: hidden;
}

#qr-reader__dashboard_section_csr {
    display: none;
}

#qr-reader__dashboard_section_csr button {
    display: none;
}
</style>
