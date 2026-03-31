<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
    submittedAt: {
        type: String,
        default: null,
    },
    approvedAt: {
        type: String,
        default: null,
    },
});

const statusClass = computed(() => {
    switch (props.status) {
        case 'approved':
            return 'bg-green-100 text-green-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
});

const statusText = computed(() => {
    return props.status ? props.status.charAt(0).toUpperCase() + props.status.slice(1) : 'Unknown';
});

const formattedSubmittedAt = computed(() => {
    if (!props.submittedAt) return null;
    return new Date(props.submittedAt).toLocaleString();
});

const formattedApprovedAt = computed(() => {
    if (!props.approvedAt) return null;
    return new Date(props.approvedAt).toLocaleString();
});
</script>

<template>
    <div class="flex items-center space-x-2">
        <span :class="statusClass" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full">
            {{ statusText }}
        </span>
        
        <div v-if="status === 'pending' && submittedAt" class="text-xs text-gray-500">
            Submitted {{ formattedSubmittedAt }}
        </div>
        
        <div v-else-if="status === 'approved' && approvedAt" class="text-xs text-gray-500">
            Approved {{ formattedApprovedAt }}
        </div>
        
        <div v-else-if="status === 'rejected' && approvedAt" class="text-xs text-gray-500">
            Rejected {{ formattedApprovedAt }}
        </div>
    </div>
</template>
