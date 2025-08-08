<script setup>
import { Link, router, usePage } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import SimilarProducts from "@/Components/SimilarProducts.vue";

const page = usePage();
const props = defineProps({
    product: Object,
    similarProducts: Array,
});

const newReview = ref("");
const newRating = ref("");
const showReviewForm = ref(false);

//  Etoiles
const getStars = (rating) => {
    return "★".repeat(rating) + "☆".repeat(5 - rating);
};

// Avis du formulaire visibles

const visibleReviewsCount = ref(3);
const visibleReviews = computed(() =>
    props.product.reviews.slice(0, visibleReviewsCount.value)
);
const hasMoreReviews = computed(
    () => props.product.reviews.length > visibleReviewsCount.value
);
const canShowLess = computed(
    () => visibleReviewsCount.value > 3 && props.product.reviews.length > 3
);

const showMoreReviews = () => {
    visibleReviewsCount.value += 3;
};
const showLessReviews = () => {
    visibleReviewsCount.value = 3;
};

const submitReview = () => {
    if (!newReview.value || !newRating.value) {
        alert("Veuillez écrire un avis et donner une note.");
        return;
    }

    router.post(
        route("reviews.store"),
        {
            product_id: props.product.id,
            rating: newRating.value,
            comment: newReview.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                alert("Merci pour votre avis !");
                newReview.value = "";
                newRating.value = "";
                showReviewForm.value = false;
            },
            onError: () => {
                alert("Erreur lors de l'envoi de l'avis.");
            },
        }
    );
};
const deleteReview = (id) => {
    if (!confirm("Voulez-vous vraiment supprimer cet avis ?")) return;

    router.delete(route("reviews.destroy", id), {
        preserveScroll: true,
        onSuccess: () => alert("Avis supprimé avec succès"),
    });
};

// Ajout au panier
const customization = ref("");
const quantity = ref(1);

const increment = () => {
    quantity.value++;
};
const decrement = () => {
    if (quantity.value > 1) {
        quantity.value--;
    }
};

// Fonction pour ajouter au panier
const addToCart = () => {
    router.post(
        route("cart.add"),
        {
            product_id: props.product.id,
            quantity: quantity.value,
            customization: customization.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                customization.value = ""; // Réinitialiser le champ de personnalisation
                alert("Produit ajouté au panier !");
            },
            onError: (errors) => {
                console.error("Erreur ajout panier:", errors);
                alert("Impossible d'ajouter au panier");
            },
        }
    );
};

// Bouton favoris
const isFavorite = ref(!!props.product.is_favorite);

const toggleFavorite = () => {
    router.post(
        route("wishlist.toggle", props.product.id),
        {},
        {
            preserveScroll: true,
            onSuccess: () => {
                const flashData = page.props.flash?.favoriteToggled;
                if (flashData) {
                    isFavorite.value = flashData.status === "added";
                } else {
                    // Fallback simple si y'a pas de flash data
                    isFavorite.value = !isFavorite.value;
                }
            },
            onError: (errors) => {
                console.error("Erreur favoris:", errors);
                alert("Une erreur est survenue");
            },
        }
    );
};
</script>

<template>
    <PublicLayout>
        <section class="max-w-[1440px] mx-auto px-4 md:px-8 py-8">
            <!-- Fil d'Ariane -->
            <nav class="text-sm text-gray-500 mb-8">
                <Link href="/" class="hover:underline">Accueil</Link>
                <span class="mx-2">›</span>
                <Link :href="route('products.index')" class="hover:underline"
                    >Produits</Link
                >
                <span class="mx-2">›</span>
                <span class="text-black">{{ product.name }}</span>
            </nav>

            <!-- Contenu principal -->
            <div class="grid md:grid-cols-2 gap-10">
                <!-- Image produit -->
                <div>
                    <img
                        :src="product.image_url"
                        :alt="product.name"
                        class="w-full rounded-lg object-cover"
                    />
                </div>

                <!-- Infos produit -->
                <div class="flex flex-col">
                    <!-- Note -->
                    <div
                        v-if="props.product.reviews_count > 0"
                        class="flex items-center mb-2"
                    >
                        <div class="text-yellow-400 mr-2 text-lg">
                            {{
                                getStars(
                                    Math.round(props.product.average_rating)
                                )
                            }}
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ props.product.average_rating }} sur 5 ({{
                                props.product.reviews_count
                            }}
                            avis)
                        </div>
                    </div>

                    <p v-else class="text-sm text-gray-500 mb-2">
                        Aucun avis pour ce produit
                    </p>

                    <!-- Nom produit -->
                    <h1 class="text-2xl font-bold mb-4">
                        {{ product.name }}
                    </h1>

                    <!-- Description -->
                    <p class="text-gray-700 mb-6">
                        {{ product.description }}
                    </p>

                    <!-- Prix -->
                    <p class="text-2xl font-bold mb-6">
                        {{ Number(product.price).toFixed(2) }}€
                        <span
                            v-if="product.old_price"
                            class="text-red-600 text-base line-through ml-2"
                        >
                            {{ Number(product.old_price).toFixed(2) }}€
                        </span>
                    </p>

                    <!-- Sélecteur quantité et favoris -->
                    <div class="flex items-center gap-4 mb-6">
                        <div
                            class="flex items-center border border-gray-300 rounded"
                        >
                            <button
                                @click="decrement"
                                class="px-3 py-1 hover:bg-gray-200"
                                aria-label="Diminuer la quantité"
                            >
                                -
                            </button>
                            <span class="px-4">{{ quantity }}</span>
                            <button
                                @click="increment"
                                class="px-3 py-1 hover:bg-gray-200"
                                aria-label="Augmenter la quantité"
                            >
                                +
                            </button>
                        </div>
                        <button
                            @click="toggleFavorite"
                            :class="[
                                'h-9 w-9 flex items-center justify-center border transition rounded-md',
                                isFavorite
                                    ? 'border-red-500 text-red-500'
                                    : 'border-gray-300 text-gray-400 hover:text-red-400 hover:border-red-400',
                            ]"
                            :aria-label="
                                isFavorite
                                    ? 'Retirer des favoris'
                                    : 'Ajouter aux favoris'
                            "
                        >
                            <span class="text-xl leading-none">
                                {{ isFavorite ? "♥" : "♡" }}
                            </span>
                        </button>
                    </div>
                    <!-- Champ personnalisation -->
                    <div class="mb-6">
                        <label
                            class="block mb-2 text-sm font-medium text-gray-700"
                        >
                            Personnalisation (facultatif) :
                        </label>
                        <input
                            v-model="customization"
                            type="text"
                            placeholder="Ex : Mamy d'amour"
                            class="w-full border border-gray-300 rounded px-3 py-2"
                        />
                    </div>
                    <!-- Bouton panier -->
                    <button
                        @click="addToCart"
                        class="bg-bidibordeaux hover:bg-rose-800 text-white font-bold py-3 px-6 rounded"
                    >
                        Ajouter au panier
                    </button>

                    <!-- Catégorie -->
                    <p class="mt-6 text-sm text-gray-500">
                        <span class="uppercase font-bold">Catégorie :</span>
                        {{ product.category?.name }}
                    </p>

                    <!-- Retour catalogue -->
                    <Link
                        :href="route('products.index')"
                        class="mt-8 inline-block text-sm text-gray-500 underline"
                    >
                        ← Retour au catalogue
                    </Link>
                </div>
            </div>
        </section>

        <!-- Section avis produit -->
        <section class="mt-16 max-w-[1440px] mx-auto px-4 md:px-8">
            <h2 class="text-2xl font-bold mb-6 text-center">
                Avis sur ce produit
            </h2>

            <!-- Liste des avis -->
            <div v-if="props.product.reviews.length" class="space-y-6">
                <div
                    v-for="review in visibleReviews"
                    :key="review.id"
                    class="bg-white shadow-md rounded-lg p-4 border border-gray-100"
                >
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center">
                            <span class="text-yellow-400 text-lg mr-2">
                                {{ getStars(review.rating) }}
                            </span>
                            <span class="text-sm font-medium text-gray-700">
                                {{ review.user.name }}
                            </span>
                        </div>

                        <button
                            v-if="page.props.auth?.user?.id === review.user.id"
                            @click="deleteReview(review.id)"
                            class="text-red-500 text-xs hover:underline"
                            aria-label="Supprimer cet avis"
                        >
                            Supprimer
                        </button>
                    </div>

                    <span class="text-xs text-gray-400">
                        {{ new Date(review.created_at).toLocaleDateString() }}
                    </span>

                    <p class="text-gray-700 leading-relaxed mt-2">
                        {{ review.comment }}
                    </p>
                </div>

                <!-- Boutons Voir plus / Voir moins -->
                <div class="text-center">
                    <button
                        v-if="hasMoreReviews"
                        @click="showMoreReviews"
                        class="mt-4 px-6 py-2 bg-bidibordeaux text-white rounded hover:bg-rose-800 transition"
                    >
                        Voir plus d'avis
                    </button>
                    <button
                        v-if="canShowLess"
                        @click="showLessReviews"
                        class="mt-4 ml-2 px-6 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition"
                    >
                        Voir moins
                    </button>
                </div>
            </div>

            <!-- Aucun avis -->
            <p v-else class="text-gray-500 text-center mb-6">
                Aucun avis pour le moment.
            </p>

            <!-- Formulaire ajout d'avis -->
            <div v-if="page.props.auth?.user" class="mt-10">
                <button
                    @click="showReviewForm = !showReviewForm"
                    class="mb-4 px-4 py-2 bg-bidibordeaux text-white rounded hover:bg-rose-800 transition"
                    :aria-label="
                        showReviewForm
                            ? 'Annuler le formulaire d’avis'
                            : 'Ouvrir le formulaire pour laisser un avis'
                    "
                >
                    {{ showReviewForm ? "Annuler" : "Laisser un avis" }}
                </button>

                <div
                    v-if="showReviewForm"
                    class="bg-gray-50 p-5 rounded-lg shadow-inner border"
                >
                    <h3 class="text-lg font-semibold mb-3">Laissez un avis</h3>

                    <textarea
                        v-model="newReview"
                        rows="3"
                        placeholder="Votre avis..."
                        class="w-full border border-gray-300 rounded px-3 py-2 mb-3 focus:ring focus:ring-bidibordeaux focus:border-bidibordeaux"
                    ></textarea>

                    <select
                        v-model="newRating"
                        class="border border-gray-300 rounded px-2 py-2 mb-3 w-full focus:ring focus:ring-bidibordeaux"
                    >
                        <option disabled value="">Sélectionnez une note</option>
                        <option v-for="n in 5" :key="n" :value="n">
                            {{ n }} ★
                        </option>
                    </select>

                    <button
                        @click="submitReview"
                        class="bg-bidibordeaux hover:bg-rose-800 text-white font-bold px-5 py-2 rounded w-full"
                    >
                        Envoyer mon avis
                    </button>
                </div>
            </div>

            <!-- Message si on est pas connecté -->
            <p v-else class="text-center text-gray-500 mt-6">
                Connectez-vous pour laisser un avis.
            </p>
        </section>

        <section class="mt-20 mb-20 max-w-[1440px] mx-auto px-4 md:px-8">
            <SimilarProducts :products="props.similarProducts" />
        </section>
    </PublicLayout>
</template>
