<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { useToast } from 'vue-toastification';
import * as Html5Qrcode from 'html5-qrcode';

const toast = useToast();

const scanner = ref(null);
const isScanning = ref(false);
const scannedUser = ref(null);
const showResult = ref(false);
const scanHistory = ref([]);

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
            // Handle scan error silently
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
    try {
        // Extract the material code pattern (e.g., "25-431")
        const materialCodeMatch = decodedText.match(/(\d+-\d+)/);
        const materialCode = materialCodeMatch ? materialCodeMatch[1] : null;
        
        const response = await fetch(route('users.scan'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
            body: JSON.stringify({ 
                qr_data: decodedText,
                material_code: materialCode
            }),
        });

        const data = await response.json();

        if (data.success) {
            scannedUser.value = {
                ...data.user,
                material_code: materialCode,
                full_qr_data: decodedText
            };
            showResult.value = true;
            
            // Add to history
            scanHistory.value.unshift({
                ...data.user,
                material_code: materialCode,
                scanned_at: new Date().toLocaleString(),
            });

            // Keep only last 10 scans
            if (scanHistory.value.length > 10) {
                scanHistory.value = scanHistory.value.slice(0, 10);
            }

            if (materialCode) {
                toast.success(`Material Code ${materialCode} - User: ${data.user.name}`);
            } else {
                toast.success(`User identified: ${data.user.name}`);
            }
            
            // Stop scanner after successful scan
            stopScanner();
        } else {
            toast.error(data.message || 'Invalid QR code');
        }
    } catch (error) {
        console.error('Scan error:', error);
        toast.error('Failed to process QR code');
    }
};

const resetScan = () => {
    scannedUser.value = null;
    showResult.value = false;
    startScanner();
};

const manualInput = ref('');

const handleManualInput = async () => {
    if (!manualInput.value.trim()) {
        toast.error('Please enter QR data');
        return;
    }

    await handleScanSuccess(manualInput.value.trim());
    manualInput.value = '';
};

onMounted(() => {
    // Check if camera is available
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
    <Head title="QR Code Scanner" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                QR Code Scanner
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Scanner Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Scan Employee QR Code
                        </h3>
                        
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
                                    placeholder="Paste QR code data here..."
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                                    @keyup.enter="handleManualInput"
                                />
                                <button
                                    @click="handleManualInput"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                >
                                    Process
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
                        </div>
                    </div>
                </div>

                <!-- Scan Result -->
                <div v-if="showResult && scannedUser" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-medium text-gray-900">
                                Scan Result
                            </h3>
                            <button
                                @click="resetScan"
                                class="text-indigo-600 hover:text-indigo-900 text-sm"
                            >
                                Scan Another
                            </button>
                        </div>

                        <div class="bg-green-50 border border-green-200 rounded-md p-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-green-800">
                                        User Identified Successfully
                                    </h3>
                                    <div class="mt-2 text-sm text-green-700">
                                        <p v-if="scannedUser.material_code" class="mb-2">
                                            <strong class="text-lg">{{ scannedUser.material_code }}</strong>
                                            <span class="ml-2 text-xs bg-green-100 text-green-800 px-2 py-1 rounded">Material Code</span>
                                        </p>
                                        <p><strong>Name:</strong> {{ scannedUser.name }}</p>
                                        <p><strong>Employee ID:</strong> {{ scannedUser.employee_id }}</p>
                                        <p><strong>Email:</strong> {{ scannedUser.email }}</p>
                                        <p><strong>Role:</strong> {{ scannedUser.role }}</p>
                                        <p><strong>Department:</strong> {{ scannedUser.department || 'N/A' }}</p>
                                        <p><strong>Position:</strong> {{ scannedUser.position || 'N/A' }}</p>
                                        <p><strong>Employment Status:</strong> {{ scannedUser.employment_status }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scan History -->
                <div v-if="scanHistory.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Recent Scans
                        </h3>
                        
                        <div class="space-y-3">
                            <div
                                v-for="(user, index) in scanHistory"
                                :key="index"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-md"
                            >
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                        <span class="text-indigo-600 font-medium">
                                            {{ user.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ user.name }}
                                            <span v-if="user.material_code" class="ml-2 text-xs bg-indigo-100 text-indigo-800 px-2 py-1 rounded">
                                                {{ user.material_code }}
                                            </span>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ user.employee_id }} • {{ user.role }}
                                        </p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-500">
                                        {{ user.scanned_at }}
                                    </p>
                                </div>
                            </div>
                        </div>
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
