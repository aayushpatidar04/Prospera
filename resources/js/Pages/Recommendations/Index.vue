<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Create from './Create.vue';
import Edit from './Edit.vue';
import Delete from './Delete.vue';
import { Link, Head } from '@inertiajs/vue3';
import { VueGoodTable } from 'vue-good-table-next'
import 'vue-good-table-next/dist/vue-good-table-next.css'

defineProps({
    recommendations: Array
});

const columns = [
    { label: 'Stock', field: 'stock_name' },
    { label: 'Type', field: 'recommendation_type' },
    { label: 'Entry', field: 'entry_price' },
    { label: 'Target', field: 'target_price' },
    { label: 'Stop Loss', field: 'stop_loss' },
    { label: 'Actions', field: 'actions', sortable: false }
]

</script>


<template>

    <Head title="Recommendations" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Stock Recommendations
            </h2>
        </template>
        <div class="container p-4 mx-auto">

            <Create />

            <VueGoodTable :columns="columns" :rows="recommendations" :search-options="{ enabled: true }"
                :pagination-options="{ enabled: true, perPage: 10 }">
                <template #table-row="props">
                    <span v-if="props.column.field === 'actions'">
                        <Edit :recommendation="props.row" />
                        &nbsp;
                        <Delete :id="props.row.id" />
                    </span>
                    <span v-else>
                        {{ props.formattedRow[props.column.field] }}
                    </span>
                </template>
            </VueGoodTable>

        </div>
    </AuthenticatedLayout>
</template>


<style scoped>
[id^="vgt-select-rpp-"] {
    appearance: auto !important;
}
</style>