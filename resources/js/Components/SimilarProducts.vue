<script setup>
import { Link } from "@inertiajs/vue3";
import { ref, computed } from "vue";

const props = defineProps({
    products: {
        type: Array,
        required: true,
    },
});

const scrollRef = ref(null);

const showArrows = computed(() => props.products.length > 4);

const scrollLeft = () => {
    scrollRef.value?.scrollBy({ left: -300, behavior: "smooth" });
};
const scrollRight = () => {
    scrollRef.value?.scrollBy({ left: 300, behavior: "smooth" });
};
</script>

<template>
    <section v-if="products.length" class="mt-16">
        <div class="max-w-[1440px] mx-auto px-4">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold">Vous pourriez aussi aimer</h3>

                <!-- Flèches cachées si < 5 produits -->
                <div v-if="showArrows" class="hidden md:flex gap-2">
                    <button
                        aria-label="Faire défiler vers la gauche"
                        @click="scrollLeft"
                        class="bg-white border border-gray-300 rounded-full w-10 h-10 flex items-center justify-center shadow-md hover:bg-gray-100 transition"
                    >
                        ←
                    </button>
                    <button
                        aria-label="Faire défiler vers la droite"
                        @click="scrollRight"
                        class="bg-white border border-gray-300 rounded-full w-10 h-10 flex items-center justify-center shadow-md hover:bg-gray-100 transition"
                    >
                        →
                    </button>
                </div>
            </div>

            <!-- Carousel produits -->
            <div
                ref="scrollRef"
                class="flex overflow-x-auto gap-4 snap-x snap-mandatory scroll-smooth pb-4 px-1 no-scrollbar"
            >
                <div
                    v-for="product in products"
                    :key="product.id"
                    class="w-[85%] md:w-[260px] lg:w-[280px] shrink-0 snap-start bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow"
                >
                    <Link
                        :href="route('products.show', product.slug)"
                        class="block"
                    >
                        <img
                            :src="product.image_url"
                            :alt="product.name"
                            class="w-full h-44 sm:h-48 md:h-52 object-cover"
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

<style scoped>
/* Masquer la scrollbar sur Chrome/Firefox */
.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    scrollbar-width: none;
}
</style>
