import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, readonly, ref } from 'vue';
import { route } from 'ziggy-js';

export const TABLET_MEDIA_QUERY =
    '(min-width: 600px) and (max-width: 1280px) and (min-height: 600px) and (hover: none) and (pointer: coarse)';

const tabletDevice = ref(false);
let tabletMediaQuery: MediaQueryList | null = null;
let tabletListenerStarted = false;

const OPERATIONAL_ROUTES = new Set([
    'dashboard',
    'approvals.index',
    'annealing-checks.index',
    'annealing-checks.create',
    'annealing-checks.show',
    'annealing-checks.edit',
    'annealing-checks.approval',
    'temp-records.index',
    'temp-records.create',
    'temp-records.show',
    'temp-records.edit',
    'temp-records.approval',
    'torque-records.index',
    'torque-records.create',
    'torque-records.show',
    'torque-records.edit',
    'torque-records.approval',
    'welding-checksheets.index',
    'welding-checksheets.create',
    'welding-checksheets.show',
    'welding-checksheets.edit',
    'welding-checksheets.approval',
    'magnetism-checksheet.index',
    'magnetism-checksheet.create',
    'magnetism-checksheet.show',
    'magnetism-checksheet.edit',
    'material-monitoring-checksheets.index',
    'material-monitoring-checksheets.create',
    'material-monitoring-checksheets.show',
    'material-monitoring-checksheets.edit',
]);

const startTabletListener = (): void => {
    if (tabletListenerStarted || typeof window === 'undefined') {
        return;
    }

    tabletMediaQuery = window.matchMedia(TABLET_MEDIA_QUERY);
    tabletDevice.value = tabletMediaQuery.matches;
    tabletMediaQuery.addEventListener('change', (event) => {
        tabletDevice.value = event.matches;
    });
    tabletListenerStarted = true;
};

export function useTabletMode() {
    const page = usePage();

    onMounted(startTabletListener);

    const currentRouteName = computed(() => {
        void page.url;

        return route().current() || '';
    });

    const isTabletOperationalRoute = computed(() =>
        OPERATIONAL_ROUTES.has(currentRouteName.value),
    );

    const isTabletMode = computed(
        () => tabletDevice.value && isTabletOperationalRoute.value,
    );

    return {
        isTabletDevice: readonly(tabletDevice),
        isTabletOperationalRoute,
        isTabletMode,
        currentRouteName,
    };
}
