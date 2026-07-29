import { usePage } from '@inertiajs/vue3';
import { onMounted, readonly, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import { useTabletMode } from '@/Composables/useTabletMode';

type RecentRoute = {
    href: string;
    label: string;
};

const STORAGE_KEY = 'mirs.tablet.recent-route';
const recentRoute = ref<RecentRoute | null>(null);

const MODULE_LABELS: Record<string, string> = {
    'annealing-checks': 'Annealing check',
    'temp-records': 'Temperature record',
    'torque-records': 'Torque record',
    'welding-checksheets': 'Welding checksheet',
    'magnetism-checksheet': 'Magnetism checksheet',
    'material-monitoring-checksheets': 'Material monitoring checksheet',
};

const loadRecentRoute = (): void => {
    if (typeof window === 'undefined') {
        return;
    }

    try {
        const stored = window.localStorage.getItem(STORAGE_KEY);
        recentRoute.value = stored ? JSON.parse(stored) : null;
    } catch {
        recentRoute.value = null;
    }
};

export function useTabletRecentRoute() {
    const page = usePage();
    const { isTabletMode, currentRouteName } = useTabletMode();

    const rememberCurrentRoute = (): void => {
        if (!isTabletMode.value || typeof window === 'undefined') {
            return;
        }

        const routeName = currentRouteName.value;
        const moduleKey = Object.keys(MODULE_LABELS).find((key) => routeName.startsWith(`${key}.`));
        const action = routeName.split('.').at(-1);

        if (!moduleKey || !['create', 'edit', 'show'].includes(action || '')) {
            return;
        }

        const label = action === 'create'
            ? `Continue new ${MODULE_LABELS[moduleKey]}`
            : `Continue ${MODULE_LABELS[moduleKey]}`;
        const value = { href: page.url, label };

        recentRoute.value = value;
        window.localStorage.setItem(STORAGE_KEY, JSON.stringify(value));
    };

    onMounted(() => {
        loadRecentRoute();
        rememberCurrentRoute();
    });
    watch(() => page.url, rememberCurrentRoute);

    return {
        recentRoute: readonly(recentRoute),
    };
}
