<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    status: String,
});
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div class="mb-4 text-sm text-gray-600">
            Password reset emails are not available in this system. Please contact an administrator or super administrator
            and ask them to reset your password from User Management.
        </div>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <div class="flex items-center justify-end mt-4">
            <Link :href="route('login')">
                <PrimaryButton>Back to Login</PrimaryButton>
            </Link>
        </div>
    </GuestLayout>
</template>
