<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import PwaStatusBanners from '@/Components/PwaStatusBanners.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head>
        <title>Login - Material Inspection System</title>
        <meta name="csrf-token" :content="$page.props.csrf_token">
    </Head>

    <div class="pwa-app-shell min-h-screen bg-gray-100">
        <PwaStatusBanners />

        <div class="flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0">
            <div>
                <Link href="/">
                    <ApplicationLogo class="w-20 h-20 fill-current text-gray-500" />
                </Link>
            </div>

            <div
                class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
            >
                <slot />
            </div>
        </div>
    </div>
</template>
