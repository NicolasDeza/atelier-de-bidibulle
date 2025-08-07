<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    product: Object,
});
</script>

<template>
    <Link
        :href="route('products.show', product.slug)"
        class="block w-full overflow-hidden hover:shadow-lg transition"
    >
        <img
            :src="product.image_url"
            :alt="product.name"
            class="w-full aspect-[4/5] object-cover max-h-64"
        />
        <div class="p-2 md:p-4">
            <h3 class="font-semibold text-sm md:text-lg mb-1 md:mb-2">
                {{ product.name }}
            </h3>
            <p class="text-gray-600 text-sm md:text-base mb-1">
                {{ product.price }} €
            </p>
        </div>
    </Link>
</template>
