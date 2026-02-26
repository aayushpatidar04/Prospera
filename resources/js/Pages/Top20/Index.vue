<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue'

const data = defineProps({
    'data': Object
})

const sector = ref('NIFTY')
const category = ref('gainer')
const stocks = ref(data.data)
const timestamp = ref(data.data[0].created_at)

let lastSector = sector.value
let lastCategory = category.value

const fetchData = () => {
    router.get('/top-stocks', { sector: sector.value, category: category.value }, {
        preserveState: true,
        onSuccess: (page) => {
            stocks.value = page.props.data
            timestamp.value = page.props.data[0].created_at
        }
    })
}

let intervalId = null

onMounted(() => {
    intervalId = setInterval(() => {
        if (sector.value === lastSector && category.value === lastCategory) {
            fetchData()
        } else {
            lastSector = sector.value
            lastCategory = category.value
        }
    }, 60000) // 60,000 ms = 1 minute
})

onUnmounted(() => {
    clearInterval(intervalId)
})

const formatDate = (ts) => {
    if (!ts) return '';
    const date = new Date(ts);

    return date.toLocaleString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true,
    });
}
</script>

<template>

    <Head title="Top 20 Gainers / Loosers" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Top 20 Gainers - Loosers
            </h2>
        </template>

        <div class="p-6">
            <!-- Filter Section -->
            <div class="flex space-x-4 mb-6">
                <select v-model="sector" @change="fetchData" class="border rounded pe-10 py-2">
                    <option value="NIFTY">NIFTY 50</option>
                    <option value="BANKNIFTY">BANK NIFTY</option>
                    <option value="NIFTYNEXT50">NIFTY NEXT 50</option>
                    <option value="SecGtr20">Securities > Rs 20</option>
                    <option value="SecLwr20">Securities < Rs 20</option>
                    <option value="FOSec">F&O Securities</option>
                    <option value="allSec">All Securities</option>
                </select>

                <select v-model="category" @change="fetchData" class="border rounded pe-10 py-2">
                    <option value="gainer">Gainer</option>
                    <option value="looser">Loser</option>
                </select>
            </div>

            <div class="py-3">
                <p>Updated At: {{ formatDate(timestamp) }}</p>
            </div>

            <!-- Stocks Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div v-for="stock in stocks" :key="stock.symbol" class="bg-white shadow rounded-lg p-4 flex flex-col">
                    <div class="flex justify-between items-center mb-2">
                        <h2 class="text-lg font-semibold">{{ stock.symbol }}</h2>
                        <span class="text-sm text-gray-500">{{ stock.series }}</span>
                    </div>
                    <p class="text-xl font-bold" :class="category === 'gainer' ? 'text-green-600' : 'text-red-600'">
                        ₹{{ stock.ltp }}
                    </p>
                    <div class="flex justify-between">
                        <div>
                            <p class="text-sm text-gray-600">Prev: ₹{{ stock.prev_price }}</p>
                            <p class="text-sm text-gray-600">Change: <span
                                    :class="category === 'gainer' ? 'text-green-600' : 'text-red-600'">{{
                                    stock.net_price
                                    }}%</span></p>
                            <p class="text-sm text-gray-600">Volume: {{ stock.trade_quantity }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">Open: ₹{{ stock.open_price }}</p>
                            <p class="text-sm text-gray-600">High: ₹{{ stock.high_price }}</p>
                            <p class="text-sm text-gray-600">Low: ₹{{ stock.low_price }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>