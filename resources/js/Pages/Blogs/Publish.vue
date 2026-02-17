<script setup>
import SuccessButton from '@/Components/SuccessButton.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useForm } from '@inertiajs/vue3';
import { inject, ref } from 'vue';

const props = defineProps({ id: Number })

const confirmingPublish = ref(false);
const alertRef = inject('alertRef')
const form = useForm({
    status: 'publish'
})

const confirmPublish = () => {
    confirmingPublish.value = true;
};

const closeModal = () => {
    confirmingPublish.value = false;
};

const PublishBlog = () => {
  form.patch(route('blogs.publish', props.id), {
    preserveScroll: true,
    onSuccess: () => {
      closeModal()
      alertRef.value.showAlert('Blog publication status updated successfully!', 'success');
    },
    onError: () => {
      alertRef.value.showAlert('Failed to delete blog!', 'error');
    }
  })
}
</script>

<template>
    <SuccessButton @click="confirmPublish">Publish</SuccessButton>

    <Modal :show="confirmingPublish" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Are you sure you want to publish your blog?
            </h2>

            <p class="mt-1 text-sm text-gray-600">
                Once your blog is published, it will be visible to all users. Please confirm you would like to publish your blog.
            </p>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal">
                    Cancel
                </SecondaryButton>
                &nbsp;
                <SuccessButton @click="PublishBlog">Confirm Publish</SuccessButton>
            </div>
        </div>
    </Modal>
</template>
