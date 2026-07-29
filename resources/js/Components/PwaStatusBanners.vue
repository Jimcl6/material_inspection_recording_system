<script setup lang="ts">
import {
    ArrowDownTrayIcon,
    ArrowPathIcon,
    ExclamationTriangleIcon,
    WifiIcon,
} from '@heroicons/vue/24/outline';
import { usePwa } from '@/Composables/usePwa';

defineProps<{
    showInstallPromotion?: boolean;
}>();

const {
    isOnline,
    installState,
    canInstall,
    showManualInstallHelp,
    updateAvailable,
    isApplyingUpdate,
    registrationError,
    promptInstall,
    applyUpdate,
} = usePwa();

const install = (): void => {
    void promptInstall();
};

const update = (): void => {
    void applyUpdate();
};
</script>

<template>
    <div
        v-if="
            !isOnline
            || updateAvailable
            || registrationError
            || (
                showInstallPromotion
                && (
                    canInstall
                    || installState === 'insecure'
                    || installState === 'unsupported'
                    || showManualInstallHelp
                )
            )
        "
        class="border-b border-gray-200 bg-white"
    >
        <div class="mx-auto space-y-3 px-4 py-3 sm:px-6 lg:px-8">
            <div
                v-if="!isOnline"
                class="flex items-start gap-3 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-amber-950"
                role="alert"
            >
                <WifiIcon class="mt-0.5 h-5 w-5 flex-none" />
                <div>
                    <p class="text-sm font-semibold">MIRS is offline</p>
                    <p class="mt-0.5 text-sm">
                        Viewing and submitting inspection records requires a network connection.
                    </p>
                </div>
            </div>

            <div
                v-if="updateAvailable"
                class="flex flex-col gap-3 rounded-lg border border-sky-300 bg-sky-50 px-4 py-3 text-sky-950 sm:flex-row sm:items-center sm:justify-between"
                role="status"
            >
                <div class="flex items-start gap-3">
                    <ArrowPathIcon class="mt-0.5 h-5 w-5 flex-none" />
                    <div>
                        <p class="text-sm font-semibold">A MIRS update is ready</p>
                        <p class="mt-0.5 text-sm">
                            Finish any form you are editing, then reload to use the latest version.
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    class="inline-flex min-h-10 items-center justify-center rounded-md bg-sky-700 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-800 focus:outline-none focus:ring-2 focus:ring-sky-600 focus:ring-offset-2 disabled:cursor-wait disabled:opacity-70"
                    :disabled="isApplyingUpdate"
                    @click="update"
                >
                    {{ isApplyingUpdate ? 'Updating...' : 'Update now' }}
                </button>
            </div>

            <div
                v-if="registrationError"
                class="flex items-start gap-3 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-red-950"
                role="alert"
            >
                <ExclamationTriangleIcon class="mt-0.5 h-5 w-5 flex-none" />
                <div>
                    <p class="text-sm font-semibold">App installation is unavailable</p>
                    <p class="mt-0.5 text-sm">
                        Continue in the browser and ask an administrator to verify the PWA deployment.
                    </p>
                </div>
            </div>

            <div
                v-if="showInstallPromotion && canInstall"
                class="flex flex-col gap-3 rounded-lg border border-teal-300 bg-teal-50 px-4 py-3 text-teal-950 sm:flex-row sm:items-center sm:justify-between"
                role="status"
            >
                <div class="flex items-start gap-3">
                    <ArrowDownTrayIcon class="mt-0.5 h-5 w-5 flex-none" />
                    <div>
                        <p class="text-sm font-semibold">Install MIRS on this tablet</p>
                        <p class="mt-0.5 text-sm">
                            Launch MIRS from its own icon without Chrome's address bar or toolbar.
                        </p>
                    </div>
                </div>
                <button
                    type="button"
                    class="inline-flex min-h-10 items-center justify-center rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-600 focus:ring-offset-2"
                    @click="install"
                >
                    Install MIRS
                </button>
            </div>

            <div
                v-else-if="showInstallPromotion && installState === 'insecure'"
                class="flex items-start gap-3 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-amber-950"
                role="status"
            >
                <ExclamationTriangleIcon class="mt-0.5 h-5 w-5 flex-none" />
                <div>
                    <p class="text-sm font-semibold">Secure connection required for installation</p>
                    <p class="mt-0.5 text-sm">
                        Open the production HTTPS address to install MIRS as an Android app.
                    </p>
                </div>
            </div>

            <div
                v-else-if="showInstallPromotion && installState === 'unsupported'"
                class="flex items-start gap-3 rounded-lg border border-gray-300 bg-gray-50 px-4 py-3 text-gray-800"
                role="status"
            >
                <ExclamationTriangleIcon class="mt-0.5 h-5 w-5 flex-none" />
                <div>
                    <p class="text-sm font-semibold">This browser cannot install MIRS</p>
                    <p class="mt-0.5 text-sm">
                        Open the secure MIRS address in the latest Google Chrome for Android.
                    </p>
                </div>
            </div>

            <div
                v-else-if="showInstallPromotion && showManualInstallHelp"
                class="flex items-start gap-3 rounded-lg border border-gray-300 bg-gray-50 px-4 py-3 text-gray-800"
                role="status"
            >
                <ArrowDownTrayIcon class="mt-0.5 h-5 w-5 flex-none" />
                <div>
                    <p class="text-sm font-semibold">Install from Chrome</p>
                    <p class="mt-0.5 text-sm">
                        Open Chrome's menu, choose Add to home screen, then choose Install rather than Create shortcut.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
