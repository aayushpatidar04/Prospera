<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Create from './Create.vue';
import Edit from './Edit.vue';
import Delete from './Delete.vue';
import { Head } from '@inertiajs/vue3';
import { VueGoodTable } from 'vue-good-table-next';
import 'vue-good-table-next/dist/vue-good-table-next.css';
import Publish from './Publish.vue';
import Unpublish from './Unpublish.vue';


defineProps({
    blogs: Array
});

const columns = [
    { label: 'Title', field: 'title' },
    { label: 'Slug', field: 'slug' },
    { label: 'Published', field: 'published' },
    { label: 'Actions', field: 'actions', sortable: false }
];

</script>

<template>

    <Head title="Blogs" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Blogs
            </h2>
        </template>
        <div class="container p-4 mx-auto">
            <Create />

            <VueGoodTable :columns="columns" :rows="blogs" :search-options="{ enabled: true }"
                :pagination-options="{ enabled: true, perPage: 10 }">
                <template #table-row="props">
                    <span v-if="props.column.field === 'actions'">
                        <Edit :blog="props.row" />
                        &nbsp;
                        <Delete :id="props.row.id" />
                        &nbsp;
                        <Publish v-if="!props.row.published" :id="props.row.id" />
                        <Unpublish v-else :id="props.row.id" />
                    </span> 
                    <span v-else>
                        {{ props.formattedRow[props.column.field] }}
                    </span>

                </template>

            </VueGoodTable>

        </div>
    </AuthenticatedLayout>
</template>

<style>
[id^="vgt-select-rpp-"]{
    background-image: none !important;
}
</style>