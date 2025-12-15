<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <DashboardStatCard 
                    :stats="stats"
                    :recent-activity="recentActivity"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { defineProps } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DashboardStatCard from '@/Components/DashboardStatCard.vue';

const props = defineProps({
    stats: {
        type: Array,
        required: true
    },
    recentActivity: {
        type: Array,
        required: true
    }
});
</script>