<script setup>
import ProductCard from "@/Components/ProductCard.vue";
import Pagination from "@/Components/Pagination.vue";
import CategoryFilter from "@/Components/CategoryFilter.vue";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import { computed } from "vue";

const props = defineProps({
    products: Object,
    categories: Array,
    currentCategory: {
        type: String,
        default: "all",
    },
    categoryName: String,
});

const pageTitle = computed(() => {
    if (props.categoryName) {
        return `Catégorie : ${props.categoryName}`;
    }
    return props.currentCategory === "all"
        ? "Notre Catalogue"
        : "Produits filtrés";
});
</script>

<template>
    <PublicLayout>
        <section class="max-w-[1440px] mx-auto p-8 mb-12">
            <h1 class="text-2xl font-bold mb-6">{{ pageTitle }}</h1>

            <!-- Composant de filtre par catégorie -->
            <CategoryFilter
                :categories="categories"
                :currentCategory="currentCategory"
                :totalProducts="products.total"
                className="mb-8"
            />

            <!-- Résultats -->
            <div v-if="products.data.length > 0">
                <p class="text-gray-600 mb-4">
                    {{ products.total }} produit{{
                        products.total > 1 ? "s" : ""
                    }}
                    trouvé{{ products.total > 1 ? "s" : "" }}
                </p>

                <div
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
                >
                    <ProductCard
                        v-for="product in products.data"
                        :key="product.id"
                        :product="product"
                    />
                </div>

                <Pagination :links="products.links" />
            </div>

            <!-- Message si aucun produit trouvé -->
            <div v-else class="text-center py-12">
                <p class="text-gray-500 text-lg">
                    Aucun produit trouvé dans cette catégorie.
                </p>
                <button
                    @click="$inertia.visit(route('products.index'))"
                    class="mt-4 px-6 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
                >
                    Voir tous les produits
                </button>
            </div>
        </section>
    </PublicLayout>
</template>
