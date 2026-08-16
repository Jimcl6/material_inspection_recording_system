<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ChecksheetForm from './Partials/ChecksheetForm.vue';
import { route } from 'ziggy-js';
import { computed } from 'vue';

const props = defineProps<{
    users: any[];
    types: any[];
    checksheet?: any;
    formMode?: 'create' | 'duplicate';
    sourceChecksheetId?: number;
    duplicateSequenceMode?: 'next_letter' | 'same_letter_new_run';
    sourceJobNumber?: string | null;
    sourceProdQty?: number | null;
    sourceLetterCode?: string | null;
}>();

const pageTitle = computed(() => props.formMode === 'duplicate' ? 'Duplicate Welding Checksheet' : 'Create Welding Checksheet');
</script>

<template>
    <Head :title="pageTitle" />

    <AppLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ pageTitle }}</h2>
                <Link :href="route('welding-checksheets.index')" class="text-gray-600 hover:text-gray-800">
                    &larr; Back to List
                </Link>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <ChecksheetForm
                    :users="users"
                    :types="types"
                    :checksheet="checksheet"
                    :form-mode="formMode"
                    :source-checksheet-id="sourceChecksheetId"
                    :duplicate-sequence-mode="duplicateSequenceMode"
                    :source-job-number="sourceJobNumber"
                    :source-prod-qty="sourceProdQty"
                    :source-letter-code="sourceLetterCode"
                />
            </div>
        </div>
    </AppLayout>
</template>
