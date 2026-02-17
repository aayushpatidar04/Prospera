<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { inject, ref } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';



const addBlogModal = ref(false);
const alertRef = inject('alertRef');

const form = useForm({
    title: '',
    content: '',
    image: null,
});

const addBlog = () => {
    addBlogModal.value = true;
};

const closeModal = () => {
    addBlogModal.value = false;
    form.clearErrors();
    form.reset({
        title: '',
        content: '',
        image: null,
    })
}

const submitForm = () => {
    form.post(route('blogs.store'), {
        forceFormData: true,
        preserveScroll: true,
        onSuccess: () => {
            addBlogModal.value = false;
            form.clearErrors();
            form.reset({
                title: '',
                content: '',
                image: null,
            })
            alertRef.value.showAlert('Blog added successfully!', 'success');
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
    <section class="space-y-6 mb-3">
        <PrimaryButton @click="addBlog">
            Add Blog
        </PrimaryButton>
    </section>
    <Modal :show="addBlogModal" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Add New Blog
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
                <!-- <QuillEditor v-model:content="form.content" contentType="html" theme="snow"
                    placeholder="Write your blog content here..." style="height:300px" /> -->
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
                    Submit
                </SuccessButton>
            </div>
        </div>
    </Modal>
</template>