<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { inject, ref } from 'vue';

const props = defineProps({ id: Number })

const confirmingDeletion = ref(false);
const alertRef = inject('alertRef')
const form = useForm({})

const confirmDeletion = () => {
    confirmingDeletion.value = true;
};

const closeModal = () => {
    confirmingDeletion.value = false;
};

const deleteRecommendation = () => {
  form.delete(route('recommendations.destroy', props.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal()
      alertRef.value.showAlert('Recommendation deleted successfully!', 'success');
    },
    onError: () => {
      alertRef.value.showAlert('Failed to delete recommendation!', 'error');
    }
  })
}
</script>

<template>
    <DangerButton @click="confirmDeletion">Delete</DangerButton>

    <Modal :show="confirmingDeletion" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Are you sure you want to delete your recommendation?
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Once your recommendation is deleted, all of its resources and data
                will be permanently deleted. Please enter your password to
                confirm you would like to permanently delete your recommendation.
            </p>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal">
                    Cancel
                </SecondaryButton>
                &nbsp;
                <DangerButton @click="deleteRecommendation">Confirm Delete</DangerButton>
            </div>
        </div>
    </Modal>
</template>
