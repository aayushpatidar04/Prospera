<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SuccessButton from '@/Components/SuccessButton.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { inject, ref } from 'vue';

const props = defineProps({ id: Number })
const alertRef = inject('alertRef')

const sendAlertModal = ref(false);
const form = useForm({
    title: '',
    body: '',
})

const sendAlert = () => {
    sendAlertModal.value = true;
};

const submitForm = () => {
    form.post(route('recommendations.send-alert', props.id), {
        preserveScroll: true,
        onSuccess: () => {
            sendAlertModal.value = false;
            form.clearErrors();
            form.reset({
                title: '',
                body: ''
            })

            alertRef.value.showAlert('Alert sent successfully!', 'success');
        },
        onError: () => {
            alertRef.value.showAlert('Failed to send alert!', 'error');
        },
        onFinish: () => {
            form.reset({
                title: '',
                body: ''
            })
        },
    })
}

const closeModal = () => {
    sendAlertModal.value = false;
    form.clearErrors();
    form.reset({
        title: '',
        body: ''
    })

};
</script>

<template>
    <PrimaryButton @click="sendAlert">
        Send Alert
    </PrimaryButton>

    <Modal :show="sendAlertModal" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Send Alert
            </h2>

            <div>
                <InputLabel for="title" value="Title" />
                <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full"
                    placeholder="Title" />
                <InputError :message="form.errors.title" class="mt-2" />
            </div>

            <!-- Analyst Notes (full width) -->
            <div class="mt-6">
                <InputLabel for="body" value="Message" />
                <textarea id="body" v-model="form.body"
                    class="mt-1 block w-full border-gray-300 rounded" placeholder="Alert Message"></textarea>
                <InputError :message="form.errors.body" class="mt-2" />
            </div>



            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="closeModal">
                    Cancel
                </SecondaryButton>

                <SuccessButton class="ms-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing"
                    @click="submitForm">
                    Send
                </SuccessButton>
            </div>
        </div>
    </Modal>
</template>