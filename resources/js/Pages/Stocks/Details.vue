<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import * as PusherPushNotifications from "@pusher/push-notifications-web";
import { ref, onMounted, onUnmounted } from 'vue'
import { Chart } from 'chart.js/auto'
import axios from 'axios'

const beamsClient = new PusherPushNotifications.Client({
    instanceId: "becc7936-b48d-4ce6-8c1f-eb84f793b4fc",
});

const data = defineProps({
    ltp: Object,
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

const ltp = ref(data.ltp)




const charts = [
    { label: 'Intraday Price Movement', endpoint: `/intraday-price-movement/${ltp.value.symbol}` },
    { label: 'Daily NAV Trend', endpoint: `/daily-nav-trend/${ltp.value.symbol}` }
]

let chartInstances = []   // ✅ store chart objects
let intervalId = null     // ✅ store interval reference

async function fetchAndUpdateChart(i) {
    const res = await axios.get(charts[i].endpoint)
    const data = res.data

    let labels
    if (i === 0) {
        labels = data.map(row =>
            new Date(row.timestamp).toLocaleTimeString('en-IN', {
                hour: '2-digit',
                minute: '2-digit'
            })
        )
    } else {
        labels = data.map(row =>
            new Date(row.date).toLocaleDateString('en-GB', {
                day: 'numeric',
                month: 'short',
                year: '2-digit'
            })
        )
    }

    const prices = data.map(row => row.lastPrice)

    if (!chartInstances[i]) {
        // ✅ first time: create chart
        chartInstances[i] = new Chart(document.getElementById(`chart-${i}`), {
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
                    x: { title: { display: false }, ticks: { display: false }, grid: { drawTicks: false } },
                    y: { title: { display: false, text: 'Price (₹)' } }
                }
            }
        })
    } else {
        // ✅ subsequent calls: update chart
        chartInstances[i].data.labels = labels
        chartInstances[i].data.datasets[0].data = prices
        chartInstances[i].update()
    }
}

onMounted(() => {
    // initial load
    for (let i = 0; i < charts.length; i++) {
        fetchAndUpdateChart(i)
    }

    // refresh every 15 seconds
    intervalId = setInterval(() => {
        for (let i = 0; i < charts.length; i++) {
            fetchAndUpdateChart(i)
        }
    }, 15000)
})

onUnmounted(() => {
    clearInterval(intervalId)
})

function useBroadcast(channelName, eventName, triggerUrl, stateRef) {
    const interval = setInterval(async () => {
        const now = new Date();
        if (now.getHours() > 15 || (now.getHours() === 15 && now.getMinutes() >= 45)) {
            clearInterval(interval); return;
        }
        await axios.get(triggerUrl)
    }, 10000)

    window.Echo.channel(channelName)
        .listen(eventName, (e) => {
            stateRef.value = e.data
        })

    onUnmounted(() => clearInterval(interval))
}

onMounted(() => {
    useBroadcast('update-stock', '.update-stock', `/trigger-stock-event/${ltp.value.symbol}`, ltp)
})


</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                {{ ltp.symbol }}
            </h2>
        </template>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 mx-auto mb-10 max-w-7xl sm:px-6 lg:px-8 mt-10">
            <div class="bg-white shadow rounded p-6 flex items-center justify-between">
                <!-- Left side: Title -->
                <div>
                    <h2 class="text-xl font-bold">{{ ltp.symbol }}</h2>
                    <p class="text-gray-500 text-sm">{{ formatDate(ltp.timestamp) }}</p>
                    <p class="text-gray-500">Previous Close: <b>₹ {{ ltp.previousClose }}</b></p>
                </div>

                <!-- Right side: Value + Change -->
                <div class="text-right">
                    <p class="text-3xl font-bold">{{ ltp.lastPrice }}</p>
                    <p :class="ltp.change >= 0 ? 'text-green-600' : 'text-red-600'"
                        class="text-lg font-semibold flex items-center justify-end">
                        <!-- Arrow -->
                        <span v-if="ltp.change >= 0">▲</span>
                        <span v-else>▼</span>
                        {{ ltp.change }} ({{ ltp.pchange }}%)
                    </p>
                </div>
            </div>
            <div class="bg-white shadow rounded p-6 flex items-center justify-between">
                <!-- Left side: Title -->
                <div>
                    <p class="text-gray-500">Traded Value: </p>
                    <p class="text-gray-500">Traded Volume: </p>
                    <p class="text-gray-500">Market Cap: </p>
                </div>

                <!-- Right side: Value + Change -->
                <div class="text-right">
                    <p class="text-gray-500"><b>{{ ltp.totalTradedValue }}</b></p>
                    <p class="text-gray-500"><b>{{ ltp.totalTradedVolume }}</b></p>
                    <p class="text-gray-500"><b>{{ ltp.totalMarketCap }}</b></p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-1 md:grid-cols-2 gap-6 mx-auto max-w-7xl sm:px-6 lg:px-8 pb-10">
            <div v-for="(dataset, index) in charts" :key="index" class="bg-white shadow rounded p-4">
                <canvas :id="`chart-${index}`"></canvas>
            </div>
        </div>


    </AuthenticatedLayout>
</template>
