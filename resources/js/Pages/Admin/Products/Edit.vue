<script setup>
import Form from "./_Form.vue";
const props = defineProps({ product: Object, categories: Array });
</script>

<template>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4 text-center">
            Modifier : {{ product.name }}
        </h1>
        <Form
            :product="product"
            :categories="categories"
            :action="`/admin/products/${product.id}`"
            method="put"
        />
    </div>
</template>
