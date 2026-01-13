<script setup>
// import { Link } from '@inertiajs/vue3';

const props = defineProps({
    materialPart: {
        type: Object,
        required: true
    }
});

const confirmDelete = () => {
    if (confirm(`Are you sure you want to delete this material part?\n\nMaterial Type: ${props.materialPart.material_type}\nLot Number: ${props.materialPart.main_lot_number}\nItem Block: ${props.materialPart.item_block_code}`)) {
        // Use Inertia to submit delete form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = route('material-monitoring-checksheets.destroy', props.materialPart.id);
        
        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            form.appendChild(csrfInput);
        }
        
        // Add method override for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
};
</script>

<template>
    <button
        @click="confirmDelete"
        class="text-red-600 hover:text-red-900"
        title="Delete"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
        </svg>
    </button>
</template>
