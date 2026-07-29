import { computed, readonly, ref, shallowRef } from 'vue';
import { registerSW } from 'virtual:pwa-register';

type InstallChoice = {
    outcome: 'accepted' | 'dismissed';
    platform: string;
};

interface BeforeInstallPromptEvent extends Event {
    platforms: string[];
    userChoice: Promise<InstallChoice>;
    prompt: () => Promise<void>;
}

export type PwaInstallState =
    | 'installed'
    | 'installable'
    | 'browser'
    | 'insecure'
    | 'unsupported';

export type PwaInstallOutcome = 'accepted' | 'dismissed' | 'unavailable';

const UPDATE_INTERVAL_MS = 60 * 60 * 1000;
const INSTALL_HELP_DELAY_MS = 4000;

const deferredInstallPrompt = shallowRef<BeforeInstallPromptEvent | null>(null);
const isStandalone = ref(false);
const installedThisSession = ref(false);
const isOnline = ref(true);
const installDetectionSettled = ref(false);
const updateAvailable = ref(false);
const isApplyingUpdate = ref(false);
const registrationError = ref<string | null>(null);

let initialized = false;
let serviceWorkerRegistration: ServiceWorkerRegistration | undefined;
let updateServiceWorker: ((reloadPage?: boolean) => Promise<void>) | undefined;

const serviceWorkersSupported =
    typeof navigator !== 'undefined' && 'serviceWorker' in navigator;
const isSecure =
    typeof window !== 'undefined' && window.isSecureContext;

const installState = computed<PwaInstallState>(() => {
    if (isStandalone.value || installedThisSession.value) {
        return 'installed';
    }

    if (!isSecure) {
        return 'insecure';
    }

    if (!serviceWorkersSupported) {
        return 'unsupported';
    }

    if (deferredInstallPrompt.value) {
        return 'installable';
    }

    return 'browser';
});

const canInstall = computed(
    () => installState.value === 'installable',
);

const showManualInstallHelp = computed(
    () =>
        installDetectionSettled.value
        && installState.value === 'browser',
);

const refreshDisplayMode = (): void => {
    isStandalone.value =
        window.matchMedia('(display-mode: standalone)').matches
        || window.matchMedia('(display-mode: fullscreen)').matches;
};

const checkForUpdates = async (): Promise<void> => {
    if (
        !serviceWorkerRegistration
        || !isOnline.value
        || document.visibilityState !== 'visible'
    ) {
        return;
    }

    try {
        await serviceWorkerRegistration.update();
    } catch {
        // A temporary update-check failure must not interrupt inspection work.
    }
};

const promptInstall = async (): Promise<PwaInstallOutcome> => {
    const installPrompt = deferredInstallPrompt.value;

    if (!installPrompt || isStandalone.value) {
        return 'unavailable';
    }

    deferredInstallPrompt.value = null;

    try {
        await installPrompt.prompt();
        const choice = await installPrompt.userChoice;

        if (choice.outcome === 'accepted') {
            installedThisSession.value = true;
        }

        return choice.outcome;
    } catch {
        return 'unavailable';
    }
};

const applyUpdate = async (): Promise<void> => {
    if (!updateAvailable.value || !updateServiceWorker) {
        return;
    }

    isApplyingUpdate.value = true;

    try {
        await updateServiceWorker(true);
    } finally {
        window.setTimeout(() => {
            isApplyingUpdate.value = false;
        }, 5000);
    }
};

export const initializePwa = (): void => {
    if (initialized || typeof window === 'undefined') {
        return;
    }

    initialized = true;
    isOnline.value = navigator.onLine;
    refreshDisplayMode();

    const displayMode = window.matchMedia('(display-mode: standalone)');

    displayMode.addEventListener('change', refreshDisplayMode);
    window.addEventListener('online', () => {
        isOnline.value = true;
        void checkForUpdates();
    });
    window.addEventListener('offline', () => {
        isOnline.value = false;
    });
    window.addEventListener('beforeinstallprompt', (event) => {
        event.preventDefault();
        deferredInstallPrompt.value =
            event as BeforeInstallPromptEvent;
        installDetectionSettled.value = true;
    });
    window.addEventListener('appinstalled', () => {
        installedThisSession.value = true;
        deferredInstallPrompt.value = null;
    });
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            refreshDisplayMode();
            void checkForUpdates();
        }
    });

    window.setTimeout(() => {
        installDetectionSettled.value = true;
    }, INSTALL_HELP_DELAY_MS);

    if (!isSecure || !serviceWorkersSupported) {
        return;
    }

    updateServiceWorker = registerSW({
        immediate: true,
        onNeedRefresh: () => {
            updateAvailable.value = true;
        },
        onRegisteredSW: (_swUrl, registration) => {
            serviceWorkerRegistration = registration;
            void checkForUpdates();
        },
        onRegisterError: (error) => {
            registrationError.value =
                error instanceof Error
                    ? error.message
                    : 'The application service worker could not be registered.';
        },
    });

    window.setInterval(() => {
        void checkForUpdates();
    }, UPDATE_INTERVAL_MS);
};

export const usePwa = () => ({
    isStandalone: readonly(isStandalone),
    isOnline: readonly(isOnline),
    installState,
    canInstall,
    showManualInstallHelp,
    updateAvailable: readonly(updateAvailable),
    isApplyingUpdate: readonly(isApplyingUpdate),
    registrationError: readonly(registrationError),
    promptInstall,
    applyUpdate,
});
