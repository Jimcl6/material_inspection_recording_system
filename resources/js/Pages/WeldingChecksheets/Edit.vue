<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ChecksheetForm from './Partials/ChecksheetForm.vue';
import { route } from 'ziggy-js';

defineProps<{
    users: any[];
    types: any[];
    checksheet: any;
}>();
</script>

<template>
    <Head title="Edit Welding Checksheet" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Welding Checksheet</h2>
                <Link :href="route('welding-checksheets.index')" class=" mx-3 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    &larr; Back to List
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <ChecksheetForm :users="users" :types="types" :checksheet="checksheet" />
            </div>
        </div>
    </AppLayout>
</template>
