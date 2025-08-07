<script setup>
import { router } from "@inertiajs/vue3";

const props = defineProps({
    categories: Array,
    currentCategory: {
        type: String,
        default: "all",
    },
    totalProducts: Number,
    className: {
        type: String,
        default: "",
    },
});

const emit = defineEmits(["categoryChanged"]);

const filterByCategory = (categorySlug) => {
    emit("categoryChanged", categorySlug);

    const params = categorySlug === "all" ? {} : { category: categorySlug };
    router.get(route("products.index"), params, {
        preserveState: true,
        preserveScroll: true,
    });
};
</script>

<template>
    <div :class="className">
        <h2 class="text-base font-semibold mb-4">Filtrer par catégorie :</h2>
        <div class="flex flex-wrap gap-2">
            <!-- Bouton "Tous les produits" -->
            <button
                @click="filterByCategory('all')"
                :class="[
                    'px-3 py-1.5 text-xs font-medium border transition-colors duration-200 flex items-center gap-1.5',
                    currentCategory === 'all'
                        ? 'bg-gray-200 text-gray-900 border-gray-400'
                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100 hover:border-black',
                ]"
            >
                <span>Tous les produits</span>
                <span
                    class="text-[10px] bg-white text-gray-600 border border-gray-300 rounded px-2 py-0.5"
                >
                    {{ totalProducts }}
                </span>
            </button>

            <!-- Boutons pour chaque catégorie -->
            <button
                v-for="category in categories"
                :key="category.id"
                @click="filterByCategory(category.slug)"
                :class="[
                    'px-3 py-1.5 text-xs font-medium border transition-colors duration-200 flex items-center gap-1.5',
                    currentCategory === category.slug
                        ? 'bg-gray-200 text-gray-900 border-gray-400'
                        : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100 hover:border-black',
                ]"
            >
                <span>{{ category.name }}</span>
                <span
                    class="text-[10px] bg-white text-gray-600 border border-gray-300 rounded px-2 py-0.5"
                >
                    {{ category.products_count }}
                </span>
            </button>
        </div>
    </div>
</template>
