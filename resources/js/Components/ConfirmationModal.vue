<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';

const emit = defineEmits(['close', 'confirm']);

const show = ref(false);

const close = () => {
    show.value = false;
    emit('close');
};

const confirm = () => {
    close();
    emit('confirm');
};

// Close on ESC key
const handleKeydown = (e) => {
    if (e.key === 'Escape' && show.value) {
        close();
    }
};

onMounted(() => {
    document.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    document.removeEventListener('keydown', handleKeydown);
});

// Expose open method
const open = () => {
    show.value = true;
};

defineExpose({
    open,
    close
});
</script>

<template>
    <TransitionRoot appear :show="show" as="template">
        <Dialog as="div" @close="close" class="relative z-50">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/25" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-md transform overflow-hidden rounded-2xl bg-white p-6 text-left align-middle shadow-xl transition-all"
                        >
                            <DialogTitle
                                as="h3"
                                class="text-lg font-medium leading-6 text-gray-900"
                            >
                                <slot name="title">Confirm Action</slot>
                            </DialogTitle>

                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    <slot>Are you sure you want to perform this action?</slot>
                                </p>
                            </div>

                            <div class="mt-4 flex justify-end space-x-2">
                                <SecondaryButton @click="close">
                                    Cancel
                                </SecondaryButton>
                                <DangerButton @click="confirm">
                                    <slot name="confirm-button">Confirm</slot>
                                </DangerButton>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>