<script setup>
import ProductCard from "@/Components/ProductCard.vue";
import Pagination from "@/Components/Pagination.vue";
import CategoryFilter from "@/Components/CategoryFilter.vue";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import { computed } from "vue";
import { useNavigation } from "@/Composables/useNavigation";
import { Head } from "@inertiajs/vue3";

const props = defineProps({
    products: Object,
    categories: Array,
    currentCategory: {
        type: String,
        default: "all",
    },
    categoryName: String,
});

// Utiliser le composable useNavigation
const { goToAllProducts } = useNavigation();

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
    <Head title="Produits">
        <meta
            name="description"
            content="Découvrez tous les produits de l’Atelier de Bidibule : créations personnalisées faites main pour naissances, cadeaux uniques et décorations."
        />
    </Head>

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
                    type="button"
                    @click="goToAllProducts"
                    class="mt-4 px-6 py-2 bg-bidibordeaux text-white rounded-lg hover:bg-rose-800 active:bg-rose-900 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bidibordeaux/40"
                >
                    Voir tous les produits
                </button>
            </div>
        </section>
    </PublicLayout>
</template>
