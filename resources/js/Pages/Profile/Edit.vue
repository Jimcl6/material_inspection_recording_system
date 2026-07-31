<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    status: String,
    mustChangePassword: Boolean,
});
</script>

<template>
    <Head title="Profile" />

    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Profile</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div
                    v-if="status"
                    class="rounded-md border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900"
                >
                    {{ status }}
                </div>

                <div
                    v-if="!mustChangePassword"
                    class="p-4 sm:p-8 bg-white shadow sm:rounded-lg"
                >
                    <UpdateProfileInformationForm
                        class="max-w-xl"
                    />
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <UpdatePasswordForm
                        :must-change-password="mustChangePassword"
                        class="max-w-xl"
                    />
                </div>

                <div
                    v-if="!mustChangePassword"
                    class="p-4 sm:p-8 bg-white shadow sm:rounded-lg"
                >
                    <DeleteUserForm class="max-w-xl" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
