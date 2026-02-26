<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';


const data = defineProps({
    'timestamp': String,
    'count': Number,
    'data': Object,
    'type': String,
});

const timestamp = ref(data.timestamp)
const count = ref(data.count)
const stocks = ref(data.data)
const type = ref(data.type)

let lastType = type.value

const fetchData = () => {
    router.get('/52week', { type: type.value }, {
        preserveState: true,
        onSuccess: (page) => {
            stocks.value = page.props.data
            timestamp.value = page.props.timestamp
            count.value = page.props.count
            type.value = page.props.type
        }
    })
}

let intervalId = null

onMounted(() => {
    intervalId = setInterval(() => {
        if (type.value === lastType) {
            fetchData()
        } else {
            lastType = type.value
        }
    }, 60000)
})

onUnmounted(() => {
    clearInterval(intervalId)
})

</script>

<template>

    <Head title="52 Week High - Low" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                52 Week High - Low
            </h2>
        </template>

        <div class="p-6">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div class="text-sm text-gray-500">
                    Last updated: {{ timestamp }} | Count: {{ count }}
                </div>
            </div>

            <!-- Filter -->
            <div class="mb-6">
                <select v-model="type" @change="fetchData" class="border rounded pe-10 py-2">
                    <option value="High">High</option>
                    <option value="Low">Low</option>
                </select>
            </div>

            <!-- Stock Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="stock in stocks" :key="stock.symbol" class="bg-white shadow rounded-lg p-4 flex flex-col">
                    <div class="flex justify-between items-center">
                        <h2 class="text-lg font-semibold">{{ stock.symbol }}</h2>
                        <span class="text-sm text-gray-500">{{ stock.series }}</span>
                    </div>
                    <p class="text-sm text-gray-700">{{ stock.comapnyName }}</p>

                    <div class="mt-2">
                        <p class="text-xl font-bold text-blue-600">₹{{ stock.ltp }}</p>
                        <p class="text-sm text-gray-600">Change: <span :class="stock.change >= 0 ? 'text-green-600' : 'text-red-600'">{{ stock.change }} ({{ stock.pChange.toFixed(2) }}%)</span></p>
                        <p class="text-sm text-gray-600">Prev Close: ₹{{ stock.prevClose }}</p>
                    </div>
                    <div class="mt-2 text-sm text-gray-500">
                        New 52W {{ type }}: {{ stock.new52WHL }} <br>
                        Prev 52W {{ type }}: {{ stock.prev52WHL }} ({{ stock.prevHLDate }})
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>