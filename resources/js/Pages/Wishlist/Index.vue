<script setup>
import { Link } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";

const props = defineProps({
    favorites: Array,
});
</script>

<template>
    <PublicLayout>
        <section class="max-w-[1440px] mx-auto px-4 py-8">
            <h1 class="text-2xl font-bold mb-6">Mes Favoris</h1>

            <div
                v-if="favorites.length"
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
            >
                <Link
                    v-for="product in favorites"
                    :key="product.id"
                    :href="route('products.show', product.slug)"
                    class="border p-4 rounded-lg shadow hover:shadow-lg transition"
                >
                    <img
                        :src="product.image_url"
                        :alt="product.name"
                        class="w-full h-40 object-cover rounded mb-3"
                    />
                    <h2 class="font-semibold">{{ product.name }}</h2>
                    <p class="text-sm text-gray-600">
                        {{ product.category?.name }}
                    </p>
                    <p class="mt-2 font-bold">{{ product.price }} €</p>
                </Link>
            </div>

            <p v-else class="text-gray-500">
                Vous n’avez encore aucun produit en favoris.
            </p>
        </section>
    </PublicLayout>
</template>
