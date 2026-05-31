<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ChecksheetForm from './Partials/ChecksheetForm.vue';
import { route } from 'ziggy-js';

defineProps<{
    users: any[];
    types: any[];
}>();
</script>

<template>
    <Head title="Create Welding Checksheet" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Welding Checksheet</h2>
                <Link :href="route('welding-checksheets.index')" class="text-gray-600 hover:text-gray-800">
                    &larr; Back to List
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <ChecksheetForm :users="users" :types="types" />
            </div>
        </div>
    </AppLayout>
</template>
