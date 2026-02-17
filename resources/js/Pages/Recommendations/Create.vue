<script setup>
import SuccessButton from '@/Components/SuccessButton.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import { inject, ref } from 'vue';

const addRecommendationModal = ref(false);
const alertRef = inject('alertRef')

const form = useForm({
    stock_name: '',
    exchange: '',
    recommendation_type: 'buy',
    entry_price: '',
    target_price: '',
    stop_loss: '',
    duration: 'intraday',
    risk_level: 'low',
    analyst_notes: ''
})


const addRecommendation = () => {
    addRecommendationModal.value = true;
};

const submitForm = () => {
    form.post(route('recommendations.store'), {
        preserveScroll: true,
        onSuccess: () => {
            addRecommendationModal.value = false;
            form.clearErrors();
            form.reset({
                stock_name: '',
                exchange: '',
                recommendation_type: '',
                entry_price: '',
                target_price: '',
                stop_loss: '',
                duration: '',
                risk_level: '',
                analyst_notes: ''
            })

            alertRef.value.showAlert('Recommendation added successfully!', 'success');
        },
        onError: () => {
            alertRef.value.showAlert('Validation failed. Please check your inputs.', 'error');
        },
        onFinish: () => {
            form.reset()
        },
    })
}

const closeModal = () => {
    addRecommendationModal.value = false;
    form.clearErrors();
    form.reset({
        stock_name: '',
        exchange: '',
        recommendation_type: '',
        entry_price: '',
        target_price: '',
        stop_loss: '',
        duration: '',
        risk_level: '',
        analyst_notes: ''
    })

};
</script>


<template>
    <section class="space-y-6 mb-3">
        <PrimaryButton @click="addRecommendation">
            Add Recommendation
        </PrimaryButton>
    </section>
    <Modal :show="addRecommendationModal" @close="closeModal">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900">
                Add Stock Recommendation
            </h2>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Stock Name -->
                <div>
                    <InputLabel for="stock_name" value="Stock Name" />
                    <TextInput id="stock_name" v-model="form.stock_name" type="text" class="mt-1 block w-full"
                        placeholder="Stock Name" />
                    <InputError :message="form.errors.stock_name" class="mt-2" />
                </div>

                <!-- Exchange -->
                <div>
                    <InputLabel for="exchange" value="Exchange" />
                    <TextInput id="exchange" v-model="form.exchange" type="text" class="mt-1 block w-full"
                        placeholder="Exchange (e.g. NSE, BSE)" />
                    <InputError :message="form.errors.exchange" class="mt-2" />
                </div>

                <!-- Recommendation Type -->
                <div>
                    <InputLabel for="recommendation_type" value="Recommendation Type" />
                    <select id="recommendation_type" v-model="form.recommendation_type"
                        class="mt-1 block w-full border-gray-300 rounded">
                        <option value="buy">Buy</option>
                        <option value="sell">Sell</option>
                        <option value="hold">Hold</option>
                    </select>
                    <InputError :message="form.errors.recommendation_type" class="mt-2" />
                </div>

                <!-- Entry Price -->
                <div>
                    <InputLabel for="entry_price" value="Entry Price" />
                    <TextInput id="entry_price" v-model="form.entry_price" type="number" step="0.01"
                        class="mt-1 block w-full" placeholder="Entry Price" />
                    <InputError :message="form.errors.entry_price" class="mt-2" />
                </div>

                <!-- Target Price -->
                <div>
                    <InputLabel for="target_price" value="Target Price" />
                    <TextInput id="target_price" v-model="form.target_price" type="number" step="0.01"
                        class="mt-1 block w-full" placeholder="Target Price" />
                    <InputError :message="form.errors.target_price" class="mt-2" />
                </div>

                <!-- Stop Loss -->
                <div>
                    <InputLabel for="stop_loss" value="Stop Loss" />
                    <TextInput id="stop_loss" v-model="form.stop_loss" type="number" step="0.01"
                        class="mt-1 block w-full" placeholder="Stop Loss" />
                    <InputError :message="form.errors.stop_loss" class="mt-2" />
                </div>

                <!-- Duration -->
                <div>
                    <InputLabel for="duration" value="Duration" />
                    <select id="duration" v-model="form.duration" class="mt-1 block w-full border-gray-300 rounded">
                        <option value="intraday">Intraday</option>
                        <option value="short-term">Short-term</option>
                        <option value="long-term">Long-term</option>
                    </select>
                    <InputError :message="form.errors.duration" class="mt-2" />
                </div>

                <!-- Risk Level -->
                <div>
                    <InputLabel for="risk_level" value="Risk Level" />
                    <select id="risk_level" v-model="form.risk_level" class="mt-1 block w-full border-gray-300 rounded">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                    <InputError :message="form.errors.risk_level" class="mt-2" />
                </div>
            </div>

            <!-- Analyst Notes (full width) -->
            <div class="mt-6">
                <InputLabel for="analyst_notes" value="Analyst Notes" />
                <textarea id="analyst_notes" v-model="form.analyst_notes"
                    class="mt-1 block w-full border-gray-300 rounded" placeholder="Notes"></textarea>
                <InputError :message="form.errors.analyst_notes" class="mt-2" />
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
