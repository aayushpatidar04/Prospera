<script setup>
import { ref } from 'vue'

const visible = ref(false)
const message = ref('')
const type = ref('success')

function showAlert(msg, alertType = 'success') {
    message.value = msg
    type.value = alertType
    visible.value = true

    setTimeout(() => {
        visible.value = false
    }, 5000) // auto-hide after 5s
}

// expose function so parent/layout can call it
defineExpose({ showAlert })
</script>

<template>
    <transition name="fade">
        <div v-if="visible" class="fixed bottom-4 right-4 z-[1000] px-4 py-3 rounded shadow-lg" :class="type === 'success'
            ? 'bg-green-100 border border-green-400 text-green-700'
            : 'bg-red-100 border border-red-400 text-red-700'">
            {{ message }}
        </div>
    </transition>
</template>

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
