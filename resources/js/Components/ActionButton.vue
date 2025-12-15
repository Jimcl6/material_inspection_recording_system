<template>
  <div class="bg-white overflow-hidden shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
      <div class="text-center">
        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full" 
             :class="[
               variant === 'primary' ? 'bg-indigo-100' : 'bg-gray-100',
               variant === 'primary' ? 'text-indigo-600' : 'text-gray-600'
             ]">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="icon" />
          </svg>
        </div>
        <h3 class="mt-4 text-sm font-medium text-gray-900">
          {{ title }}
        </h3>
      </div>
    </div>
    <div class="bg-gray-50 px-5 py-3 text-center">
      <Link :href="to" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
        Go to {{ title }}
      </Link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps({
  title: {
    type: String,
    required: true
  },
  icon: {
    type: String,
    required: true
  },
  to: {
    type: String,
    required: true
  },
  variant: {
    type: String,
    default: 'secondary'
  }
});
</script>