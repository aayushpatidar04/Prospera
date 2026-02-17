<script setup>

import SuccessButton from '@/Components/SuccessButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { inject, ref, watch } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import WarningButton from '@/Components/WarningButton.vue';



const editBlogModal = ref(false);
const alertRef = inject('alertRef');

const data = defineProps({
    blog: Object

})
let form = useForm({
    title: data.blog?.title || '',
    content: data.blog?.content || '',
    image: null,
});

const editBlog = () => {
    form.title = data.blog?.title || '';
    form.content = data.blog?.content || '';
    form.image = null;
    editBlogModal.value = true;
};

const closeModal = () => {
    editBlogModal.value = false;
    form.clearErrors();
    form.reset({
        title: '',
        content: '',
        image: null,
    })

}

const submitForm = () => {
    form.post(route('blogs.edit', { id: data.blog.id }), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            editBlogModal.value = false;
            form.clearErrors();
            form.reset({
                title: '',
                content: '',
                image: null,
            })

            alertRef.value.showAlert('Blog updated successfully!', 'success');
        },
        onError: () => {
            alertRef.value.showAlert('Validation failed. Please check your inputs.', 'error');
        },
        onFinish: () => {
            form.reset({
                title: '',
                content: '',
                image: null,
            })

        },
    })
}
</script>

<template>
    <WarningButton @click="editBlog">
        Edit
    </WarningButton>

    <Modal :show="editBlogModal" @close="closeModal" :key="data.blog.id">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Edit Blog
            </h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <InputLabel for="title" value="Title" />
                    <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full"
                        placeholder="Enter Title" required />
                    <InputError :message="form.errors.title" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="image" value="Image" />
                    <input type="file" accept="image/*" @change="e => form.image = e.target.files[0]" class="block w-full text-sm text-gray-500 
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-full file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100" />
                    <InputError :message="form.errors.image" class="mt-2" />
                </div>
            </div>

            <div class="mt-6">
                <InputLabel for="content" value="Content" />
                <QuillEditor v-model:content="form.content" contentType="html" theme="snow"
                    placeholder="Write your blog content here..." style="height:300px" />

                <InputError :message="form.errors.content" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal">
                    Cancel
                </SecondaryButton>

                <SuccessButton class="ms-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                    @click="submitForm">
                    Update
                </SuccessButton>
            </div>
        </div>
    </Modal>
</template>