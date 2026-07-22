<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import QRCode from 'qrcode';
import { useToast } from 'vue-toastification';
import AppLayout from '@/Layouts/AppLayout.vue';
import {
    ArrowLeftIcon,
    ArrowPathIcon,
    MagnifyingGlassIcon,
    PrinterIcon,
    QrCodeIcon,
} from '@heroicons/vue/24/outline';

const props = defineProps({
    users: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const toast = useToast();
const search = ref(props.filters.search || '');
const selectedIds = ref([]);
const qrImages = ref({});
const isRendering = ref(false);

const eligibleUsers = computed(() => props.users.filter((user) => user.can_reissue));
const selectedUsers = computed(() => props.users.filter((user) => selectedIds.value.includes(user.id)));
const allEligibleSelected = computed({
    get: () => eligibleUsers.value.length > 0 && selectedIds.value.length === eligibleUsers.value.length,
    set: (checked) => {
        selectedIds.value = checked ? eligibleUsers.value.map((user) => user.id) : [];
    },
});

let renderSequence = 0;
const renderSelectedBadges = async () => {
    const sequence = ++renderSequence;
    isRendering.value = true;
    const images = {};

    await Promise.all(selectedUsers.value.map(async (user) => {
        if (!user.qr_data) return;

        try {
            images[user.id] = await QRCode.toDataURL(user.qr_data, {
                width: 360,
                margin: 1,
                errorCorrectionLevel: 'M',
                color: { dark: '#111827', light: '#ffffff' },
            });
        } catch {
            images[user.id] = null;
        }
    }));

    if (sequence === renderSequence) {
        qrImages.value = images;
        isRendering.value = false;
    }
};

watch(selectedIds, renderSelectedBadges, { deep: true });

const applySearch = () => {
    router.get(route('users.badges'), { search: search.value || undefined }, {
        preserveState: true,
        replace: true,
    });
};

const reissueSelected = () => {
    if (!selectedIds.value.length) return;
    if (!window.confirm(`Reissue ${selectedIds.value.length} selected badge(s)? Previously printed copies will stop working after an APP_KEY rotation.`)) return;

    router.post(route('users.badges.reissue'), { user_ids: selectedIds.value }, {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Selected badges reissued. Review the preview, then print.');
            renderSelectedBadges();
        },
        onError: () => toast.error('Badge reissue was not completed.'),
    });
};

const printSelected = async () => {
    if (!selectedIds.value.length) return;
    await renderSelectedBadges();

    if (Object.values(qrImages.value).some((image) => !image)) {
        toast.error('At least one badge could not be rendered locally. Nothing was printed.');
        return;
    }

    window.print();
};
</script>

<template>
    <Head title="QR Badge Reissue & Printing" />

    <AppLayout>
        <template #header>
            <div class="flex flex-wrap items-center justify-between gap-3 no-print">
                <div>
                    <h2 class="text-xl font-semibold leading-tight text-gray-800">QR Badge Reissue & Printing</h2>
                    <p class="mt-1 text-sm text-gray-500">Active users only. QR images are generated locally in this browser.</p>
                </div>
                <Link :href="route('users.index')" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                    <ArrowLeftIcon class="mr-2 h-4 w-4" />
                    Back to users
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <section class="no-print rounded-lg bg-white p-6 shadow-sm">
                    <form class="flex flex-col gap-3 sm:flex-row" @submit.prevent="applySearch">
                        <label class="relative flex-1">
                            <span class="sr-only">Search active users</span>
                            <MagnifyingGlassIcon class="pointer-events-none absolute left-3 top-2.5 h-5 w-5 text-gray-400" />
                            <input v-model="search" type="search" placeholder="Search by name or employee ID" class="w-full rounded-md border-gray-300 pl-10 focus:border-indigo-500 focus:ring-indigo-500" />
                        </label>
                        <button type="submit" class="rounded-md bg-gray-800 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-700">Filter</button>
                    </form>

                    <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 pt-5">
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                            <input v-model="allEligibleSelected" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                            Select all printable badges ({{ eligibleUsers.length }})
                        </label>
                        <div class="flex flex-wrap gap-2">
                            <button :disabled="!selectedIds.length" type="button" class="inline-flex items-center rounded-md bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 disabled:cursor-not-allowed disabled:opacity-40" @click="reissueSelected">
                                <ArrowPathIcon class="mr-2 h-4 w-4" />
                                Reissue selected
                            </button>
                            <button :disabled="!selectedIds.length || isRendering" type="button" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-40" @click="printSelected">
                                <PrinterIcon class="mr-2 h-4 w-4" />
                                Print selected
                            </button>
                        </div>
                    </div>

                    <div class="mt-5 overflow-x-auto rounded-md border border-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="w-12 px-4 py-3"></th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Active user</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Department</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Badge state</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white">
                                <tr v-for="user in users" :key="user.id">
                                    <td class="px-4 py-3">
                                        <input v-model="selectedIds" :value="user.id" :disabled="!user.can_reissue" type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 disabled:opacity-30" />
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900">{{ user.name }}</p>
                                        <p class="text-sm text-gray-500">{{ user.employee_id }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ user.department || 'Unassigned' }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span v-if="user.can_reissue" class="inline-flex items-center text-emerald-700"><QrCodeIcon class="mr-1 h-4 w-4" />Ready</span>
                                        <span v-else class="text-gray-500">No active stored badge</span>
                                    </td>
                                </tr>
                                <tr v-if="!users.length">
                                    <td colspan="4" class="px-4 py-10 text-center text-sm text-gray-500">No active users matched the filter.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="badge-sheet mt-8">
                    <div v-if="!selectedUsers.length" class="no-print rounded-lg border-2 border-dashed border-gray-300 p-12 text-center text-gray-500">
                        Select at least one active badge to preview it here.
                    </div>
                    <article v-for="user in selectedUsers" :key="user.id" class="badge-card">
                        <div class="badge-heading">
                            <p class="badge-system">MIRS</p>
                            <p class="badge-label">EMPLOYEE ACCESS BADGE</p>
                        </div>
                        <img v-if="qrImages[user.id]" :src="qrImages[user.id]" alt="Encrypted employee QR badge" class="badge-qr" />
                        <div v-else class="badge-qr-placeholder">Rendering…</div>
                        <p class="badge-name">{{ user.name }}</p>
                        <p class="badge-employee">{{ user.employee_id }}</p>
                        <p class="badge-meta">{{ user.department || 'Department not assigned' }} · {{ user.employment_status || 'Status unavailable' }}</p>
                    </article>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.badge-sheet {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1rem;
}

.badge-card {
    break-inside: avoid;
    min-height: 430px;
    border: 1px solid #d1d5db;
    border-radius: 0.75rem;
    background: #fff;
    padding: 1.25rem;
    text-align: center;
    box-shadow: 0 1px 3px rgb(0 0 0 / 0.08);
}

.badge-heading { margin-bottom: 0.75rem; }
.badge-system { font-size: 1.4rem; font-weight: 800; letter-spacing: 0.12em; color: #1e3a8a; }
.badge-label { margin-top: 0.15rem; font-size: 0.7rem; font-weight: 700; letter-spacing: 0.14em; color: #6b7280; }
.badge-qr, .badge-qr-placeholder { width: 230px; height: 230px; margin: 0 auto; }
.badge-qr-placeholder { display: grid; place-items: center; background: #f3f4f6; color: #6b7280; }
.badge-name { margin-top: 0.85rem; font-size: 1.1rem; font-weight: 700; color: #111827; }
.badge-employee { margin-top: 0.1rem; font-size: 0.95rem; font-weight: 600; color: #1d4ed8; }
.badge-meta { margin-top: 0.4rem; font-size: 0.75rem; color: #4b5563; }

@media print {
    :global(body) { background: #fff !important; }
    :global(.no-print), .no-print { display: none !important; }
    .badge-sheet { grid-template-columns: repeat(2, 86mm); justify-content: center; gap: 6mm; margin: 0; }
    .badge-card { width: 86mm; min-height: 54mm; padding: 5mm; border-radius: 2mm; box-shadow: none; page-break-inside: avoid; }
    .badge-qr { width: 34mm; height: 34mm; }
    .badge-qr-placeholder { display: none; }
}
</style>
