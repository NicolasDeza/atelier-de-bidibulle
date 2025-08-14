<script setup>
import Form from "./_Form.vue";
const props = defineProps({ categories: Array });
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Nouveau produit</h1>
        <Form :categories="categories" action="/admin/products" method="post" />
    </div>
</template>
