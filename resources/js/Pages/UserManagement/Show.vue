<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useToast } from 'vue-toastification';
import QRCode from 'qrcode';
import {
    UserIcon,
    EnvelopeIcon,
    PhoneIcon,
    BuildingOfficeIcon,
    BriefcaseIcon,
    QrCodeIcon,
    CheckCircleIcon,
    XCircleIcon,
    ClockIcon,
    CalendarIcon,
    ShieldCheckIcon,
} from '@heroicons/vue/24/outline';

const toast = useToast();

const props = defineProps({
    user: Object,
    qrStatus: Object,
    qrData: String,
});

const qrCanvas = ref(null);
const qrImageDataUrl = ref(null);

const generateQrCanvas = async () => {
    if (props.qrData && qrCanvas.value) {
        try {
            await QRCode.toCanvas(qrCanvas.value, props.qrData, {
                width: 250,
                margin: 2,
                color: {
                    dark: '#000000',
                    light: '#ffffff',
                },
            });
            qrImageDataUrl.value = qrCanvas.value.toDataURL('image/png');
        } catch (err) {
            console.error('Failed to generate QR code:', err);
        }
    }
};

onMounted(() => {
    generateQrCanvas();
});

watch(() => props.qrData, () => {
    generateQrCanvas();
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

const downloadQrCode = () => {
    if (!qrImageDataUrl.value) return;
    
    const link = document.createElement('a');
    link.href = qrImageDataUrl.value;
    link.download = `${props.user.employee_id}_qr_code.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    toast.success('QR code downloaded');
};

const regenerateQrCode = () => {
    router.post(
        route('users.regenerate-qr', props.user.id),
        {},
        {
            onSuccess: () => {
                toast.success('QR code regenerated successfully');
            },
            onError: () => {
                toast.error('Failed to regenerate QR code');
            },
        }
    );
};
</script>

<template>
    <Head :title="`User Details - ${user.name}`" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    User Details - {{ user.name }}
                </h2>
                <div class="flex space-x-2">
                    <Link
                        :href="route('users.scanner')"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        <QrCodeIcon class="w-4 h-4 mr-2" />
                        QR Scanner
                    </Link>
                    <Link
                        :href="route('users.edit', user.id)"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Edit User
                    </Link>
                    <Link
                        :href="route('users.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Back to Users
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- User Information -->
                    <div class="lg:col-span-2">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-6">User Information</h3>
                                
                                <div class="flex items-center mb-6">
                                    <div class="w-20 h-20 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <UserIcon class="w-12 h-12 text-indigo-600" />
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-xl font-semibold text-gray-900">{{ user.name }}</h4>
                                        <p class="text-sm text-gray-500">{{ user.email }}</p>
                                        <div class="mt-1">
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
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Employee ID
                                        </label>
                                        <div class="flex items-center text-sm text-gray-900">
                                            <ShieldCheckIcon class="w-5 h-5 mr-2 text-gray-400" />
                                            {{ user.employee_id }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Contact Number
                                        </label>
                                        <div class="flex items-center text-sm text-gray-900">
                                            <PhoneIcon class="w-5 h-5 mr-2 text-gray-400" />
                                            {{ user.contact_number || 'N/A' }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Email Address
                                        </label>
                                        <div class="flex items-center text-sm text-gray-900">
                                            <EnvelopeIcon class="w-5 h-5 mr-2 text-gray-400" />
                                            {{ user.email }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Role
                                        </label>
                                        <div class="flex items-center text-sm text-gray-900">
                                            <BriefcaseIcon class="w-5 h-5 mr-2 text-gray-400" />
                                            {{ user.role?.name || 'N/A' }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Department
                                        </label>
                                        <div class="flex items-center text-sm text-gray-900">
                                            <BuildingOfficeIcon class="w-5 h-5 mr-2 text-gray-400" />
                                            {{ user.department?.name || 'N/A' }}
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">
                                            Position
                                        </label>
                                        <div class="flex items-center text-sm text-gray-900">
                                            <BriefcaseIcon class="w-5 h-5 mr-2 text-gray-400" />
                                            {{ user.position?.name || 'N/A' }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Employment Details -->
                                <div v-if="user.qr_code" class="mt-6 pt-6 border-t border-gray-200">
                                    <h4 class="text-md font-medium text-gray-900 mb-4">Employment Details</h4>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Employment Status
                                            </label>
                                            <span
                                                :class="[
                                                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                    getEmploymentBadge(user.qr_code.employment_status).bg,
                                                    getEmploymentBadge(user.qr_code.employment_status).text,
                                                ]"
                                            >
                                                {{ user.qr_code.employment_status }}
                                            </span>
                                        </div>

                                        <div v-if="user.qr_code.hire_date">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Hire Date
                                            </label>
                                            <div class="flex items-center text-sm text-gray-900">
                                                <CalendarIcon class="w-5 h-5 mr-2 text-gray-400" />
                                                {{ new Date(user.qr_code.hire_date).toLocaleDateString() }}
                                            </div>
                                        </div>

                                        <div v-if="user.qr_code.contract_end_date">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Contract End Date
                                            </label>
                                            <div class="flex items-center text-sm text-gray-900">
                                                <CalendarIcon class="w-5 h-5 mr-2 text-gray-400" />
                                                {{ new Date(user.qr_code.contract_end_date).toLocaleDateString() }}
                                            </div>
                                        </div>

                                        <div v-if="qrStatus && user.qr_code">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                                Contract Status
                                            </label>
                                            <div class="text-sm">
                                                <span v-if="qrStatus.is_expired" class="text-red-600 font-medium">
                                                    Expired
                                                </span>
                                                <span v-else-if="qrStatus.is_expiring_soon" class="text-yellow-600 font-medium">
                                                    Expiring in {{ qrStatus.days_until_expiry }} days
                                                </span>
                                                <span v-else class="text-green-600 font-medium">
                                                    Active
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Login History -->
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Login History</h3>
                                
                                <div v-if="user.login_history && user.login_history.length > 0" class="space-y-3">
                                    <div
                                        v-for="login in user.login_history"
                                        :key="login.id"
                                        class="flex items-center justify-between p-3 bg-gray-50 rounded-md"
                                    >
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                <CheckCircleIcon class="w-6 h-6 text-green-600" />
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ new Date(login.login_at).toLocaleString() }}
                                                </p>
                                                <p class="text-xs text-gray-500">
                                                    {{ login.ip_address }} • {{ login.login_type }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p v-if="login.session_duration" class="text-xs text-gray-500">
                                                Duration: {{ login.session_duration }} min
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div v-else class="text-center py-8 text-gray-500">
                                    No login history available
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- QR Code Section -->
                    <div class="lg:col-span-1">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">QR Code</h3>
                                
                                <div v-if="qrData" class="text-center">
                                    <canvas
                                        ref="qrCanvas"
                                        class="mx-auto mb-4 border-2 border-gray-200 rounded-lg"
                                    ></canvas>
                                    
                                    <div class="space-y-2">
                                        <button
                                            @click="downloadQrCode"
                                            class="w-full px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                        >
                                            Download QR Code
                                        </button>
                                        
                                        <button
                                            @click="regenerateQrCode"
                                            class="w-full px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
                                        >
                                            Regenerate QR Code
                                        </button>
                                    </div>
                                </div>
                                
                                <div v-else class="text-center py-8">
                                    <QrCodeIcon class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                                    <p class="text-gray-500 mb-4">No QR Code Generated</p>
                                    <button
                                        @click="regenerateQrCode"
                                        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                    >
                                        Generate QR Code
                                    </button>
                                </div>

                                <!-- QR Status -->
                                <div v-if="qrStatus" class="mt-6 pt-6 border-t border-gray-200">
                                    <h4 class="text-md font-medium text-gray-900 mb-3">QR Code Status</h4>
                                    
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600">Status:</span>
                                            <span :class="qrStatus.is_active ? 'text-green-600' : 'text-red-600'">
                                                {{ qrStatus.is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                        
                                        <div v-if="user.qr_code" class="flex justify-between text-sm">
                                            <span class="text-gray-600">Last Scanned:</span>
                                            <span class="text-gray-900">
                                                {{ user.qr_code.last_scanned_at 
                                                    ? new Date(user.qr_code.last_scanned_at).toLocaleString() 
                                                    : 'Never' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
