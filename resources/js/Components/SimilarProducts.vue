<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    products: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <section v-if="products.length" class="mt-20">
        <div class="max-w-screen-xl mx-auto px-4">
            <h3 class="text-2xl font-bold mb-8 text-center">
                Vous pourriez aussi aimer
            </h3>

            <div
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8"
            >
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow"
                >
                    <Link
                        :href="route('products.show', product.slug)"
                        class="block"
                    >
                        <img
                            :src="product.image_url"
                            :alt="product.name"
                            class="w-full h-52 object-cover"
                        />
                        <div class="p-4">
                            <h4
                                class="text-base font-semibold mb-2 text-gray-800"
                            >
                                {{ product.name }}
                            </h4>
                            <div class="text-bidibordeaux font-bold text-lg">
                                {{ product.price }} €
                                <span
                                    v-if="product.old_price"
                                    class="line-through text-sm text-gray-400 ml-2"
                                >
                                    {{ product.old_price }} €
                                </span>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </section>
</template>
