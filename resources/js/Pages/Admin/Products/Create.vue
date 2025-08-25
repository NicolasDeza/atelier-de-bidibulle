<script setup>
import Form from "./_Form.vue";
const props = defineProps({ categories: Array });
</script>

<template>
    <div class="p-4 sm:p-6 min-h-screen">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-xl sm:text-2xl font-bold mb-4 text-center">
                Nouveau produit
            </h1>
            <div class="bg-white rounded-lg shadow p-4 sm:p-6">
                <Form
                    :categories="categories"
                    action="/admin/products"
                    method="post"
                />
            </div>
        </div>
    </div>
</template>
