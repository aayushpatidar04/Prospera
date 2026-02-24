<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import * as PusherPushNotifications from "@pusher/push-notifications-web";
import { ref, onMounted, onUnmounted } from 'vue'
import { Chart } from 'chart.js/auto'
import Echo from 'laravel-echo'
import axios from 'axios'

const beamsClient = new PusherPushNotifications.Client({
    instanceId: "becc7936-b48d-4ce6-8c1f-eb84f793b4fc",
});

const data = defineProps({
    tradedStocks: Object,
    filters: Object
});

beamsClient.start()
    .then(() => beamsClient.addDeviceInterest('hello'))
    .then(() => console.log('Successfully registered and subscribed!'))
    .catch(console.error);

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

const searchTerm = ref(data.filters.search || '')
const timestamp = ref(formatDate(data.tradedStocks.data?.[0]?.timestamp))
const tradedStocks = ref(data.tradedStocks)

onMounted(() => {
    const interval = setInterval(async () => {
        await axios.get('/trigger-traded-stocks-event', {
            params: {
                page: tradedStocks.value.current_page,
                search: searchTerm.value
            }
        })
    }, 10000)

    window.Echo.channel('traded-stocks')
        .listen('.traded-stocks.updated', (e) => {
            // e.data contains the payload you broadcasted
            tradedStocks.value = e.data.tradedStocks
            timestamp.value = e.timestamp
        })

    onUnmounted(() => clearInterval(interval))
})


const searchStocks = () => {
    router.get('/dashboard', { search: searchTerm.value }, { preserveState: true, onSuccess: (page) => { tradedStocks.value = page.props.tradedStocks } })
}

const charts = [
    { label: 'SilverBees', endpoint: '/silverbees-performance' },
    { label: 'TataSilver', endpoint: '/tata-silver' }
]

onMounted(async () => {
    for (let i = 0; i < charts.length; i++) {
        const res = await axios.get(charts[i].endpoint)
        const data = res.data

        const labels = data.map(row =>
            new Date(row.timestamp).toLocaleTimeString('en-IN', {
                hour: '2-digit',
                minute: '2-digit'
            })
        )
        const prices = data.map(row => row.lastPrice)

        new Chart(document.getElementById(`chart-${i}`), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: charts[i].label,
                    data: prices,
                    borderColor: '#4F46E5',
                    backgroundColor: 'rgba(79,70,229,0.2)',
                    tension: 0.3,
                    borderWidth: 1,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHitRadius: 10
                }]
            },
            options: {
                responsive: true,
                interaction: { mode: 'nearest', intersect: false },
                plugins: { tooltip: { enabled: true } },
                scales: {
                    x: { title: { display: false, text: 'Time' }, ticks: { display: false }, grid: { drawTicks: false } },
                    y: { title: { display: false, text: 'Price (₹)' } }
                }
            }
        })
    }
})

function useBroadcast(channelName, eventName, triggerUrl, stateRef) {
  const interval = setInterval(async () => {
    await axios.get(triggerUrl)
  }, 4000)

  window.Echo.channel(channelName)
    .listen(eventName, (e) => {
      stateRef.value = e
    })

  onUnmounted(() => clearInterval(interval))
}


const nifty = ref({ price: null, change: null, percent: null, lastupd: null })
const niftybank = ref({ price: null, change: null, percent: null, lastupd: null })

onMounted(() => {
  useBroadcast('update-nifty50', '.update-nifty50', '/trigger-nifty50-event', nifty)
  useBroadcast('update-niftybank', '.update-niftybank', '/trigger-niftybank-event', niftybank)
})


</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        You're logged in!
                    </div>
                </div>
            </div>
        </div>


        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mx-auto mb-10 max-w-7xl sm:px-6 lg:px-8">
            <div class="bg-white shadow rounded p-6 flex items-center justify-between">
                <!-- Left side: Title -->
                <div>
                    <h2 class="text-xl font-bold">NIFTY 50</h2>
                    <p class="text-gray-500 text-sm">Last Updated: {{ nifty.lastupd }}</p>
                </div>

                <!-- Right side: Value + Change -->
                <div class="text-right">
                    <p class="text-3xl font-bold">{{ nifty.price }}</p>
                    <p :class="nifty.change >= 0 ? 'text-green-600' : 'text-red-600'"
                        class="text-lg font-semibold flex items-center justify-end">
                        <!-- Arrow -->
                        <span v-if="nifty.change >= 0">▲</span>
                        <span v-else>▼</span>
                        {{ nifty.change }} ({{ nifty.percent }}%)
                    </p>
                </div>
            </div>
            <div class="bg-white shadow rounded p-6 flex items-center justify-between">
                <!-- Left side: Title -->
                <div>
                    <h2 class="text-xl font-bold">NIFTY BANK</h2>
                    <p class="text-gray-500 text-sm">Last Updated: {{ niftybank.lastupd }}</p>
                </div>

                <!-- Right side: Value + Change -->
                <div class="text-right">
                    <p class="text-3xl font-bold">{{ niftybank.price }}</p>
                    <p :class="niftybank.change >= 0 ? 'text-green-600' : 'text-red-600'"
                        class="text-lg font-semibold flex items-center justify-end">
                        <!-- Arrow -->
                        <span v-if="niftybank.change >= 0">▲</span>
                        <span v-else>▼</span>
                        {{ niftybank.change }} ({{ niftybank.percent }}%)
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 gap-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div v-for="(dataset, index) in charts" :key="index" class="bg-white shadow rounded p-4">
                <h3 class="text-lg font-semibold mb-2">{{ dataset.label }}</h3>
                <canvas :id="`chart-${index}`"></canvas>
            </div>
        </div>

        <div class="my-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h2 class="text-xl font-semibold leading-tight text-gray-800 mb-4">Traded Stocks</h2>
                        <div class="mb-4 text-sm text-gray-600">
                            Updated at: {{ timestamp }}
                        </div>
                        <div>
                            <!-- Search bar -->
                            <div class="mb-4">
                                <input v-model="searchTerm" @input="searchStocks" type="text"
                                    placeholder="Search stocks..." class="border rounded px-3 py-2 w-full" />
                            </div>
                            <!-- Info on top -->
                            <div class="mb-4 text-sm text-gray-600">
                                Showing {{ tradedStocks.from }}–{{ tradedStocks.to }} of {{ tradedStocks.total }}
                                stocks
                            </div>

                            <!-- Grid of cards -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                                <div v-for="stock in tradedStocks.data" :key="stock.identifier"
                                    class="bg-white shadow rounded p-4">
                                    <h3 class="text-lg font-semibold">{{ stock.symbol }}</h3>
                                    <p class="text-gray-600">Last Price: ₹ {{ stock.lastPrice }}</p>

                                    <p :class="{
                                        'text-green-600 font-bold': stock.pchange > 0,
                                        'text-red-600 font-bold': stock.pchange < 0,
                                        'text-gray-600': stock.pchange === 0
                                    }">
                                        {{ stock.pchange }} %
                                    </p>

                                    <p class="text-gray-700">Change: <span :class="{
                                        'text-green-600 font-bold': stock.change > 0,
                                        'text-red-600 font-bold': stock.change < 0,
                                        'text-gray-600': stock.change === 0
                                    }">{{ stock.change }}</span></p>
                                    <p class="text-gray-500">Previous Close: ₹ {{ stock.previousClose }}</p>
                                </div>
                            </div>

                            <!-- Pagination links -->
                            <div class="flex justify-between items-center mt-6">
                                <Link v-if="tradedStocks.prev_page_url" :href="tradedStocks.prev_page_url"
                                    class="px-4 py-2 bg-gray-200 rounded">
                                    Previous
                                </Link>

                                <span class="text-sm text-gray-600">
                                    Page {{ tradedStocks.current_page }} of {{ tradedStocks.last_page }}
                                </span>

                                <Link v-if="tradedStocks.next_page_url" :href="tradedStocks.next_page_url"
                                    class="px-4 py-2 bg-gray-200 rounded">
                                    Next
                                </Link>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
