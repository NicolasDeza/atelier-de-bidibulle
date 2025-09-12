<script setup>
import { Link, router, usePage, Head } from "@inertiajs/vue3";
import { ref, computed } from "vue";
import PublicLayout from "@/Layouts/PublicLayout.vue";
import SimilarProducts from "@/Components/SimilarProducts.vue";
import Modal from "@/Components/Modal.vue";
import ProductReviews from "@/Components/ProductReviews.vue";

const page = usePage();
const props = defineProps({
    product: Object,
    similarProducts: Array,
});

//
// --- Avis ---
//
const newReview = ref("");
const newRating = ref("");
const showReviewForm = ref(false);

// Avis visibles (pagination locale)
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

// Vérifie si l’utilisateur a déjà laissé un avis
const hasUserReview = computed(() => {
    if (!page.props.auth?.user) return false;
    return props.product.reviews.some(
        (review) => review.user.id === page.props.auth.user.id
    );
});

// Envoi d’un avis
const submitReview = () => {
    if (!newReview.value || !newRating.value) return;

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
                newReview.value = "";
                newRating.value = "";
                showReviewForm.value = false;
            },
        }
    );
};

// Modal de suppression d’avis
const showDeleteModal = ref(false);
const reviewToDelete = ref(null);

const askDeleteReview = (review) => {
    reviewToDelete.value = review;
    showDeleteModal.value = true;
};

const confirmDeleteReview = () => {
    if (!reviewToDelete.value) return;

    router.delete(route("reviews.destroy", reviewToDelete.value.id), {
        preserveScroll: true,
        onFinish: () => {
            showDeleteModal.value = false;
            reviewToDelete.value = null;
        },
    });
};

//
// --- Ajout au panier ---
//
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

// Nettoyage des inputs
const sanitizeInput = (input) => {
    return input
        .trim()
        .replace(/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi, "");
};

const addToCart = () => {
    const cleanCustomization = sanitizeInput(customization.value).substring(
        0,
        100
    );

    const validQuantity = Math.max(
        1,
        Math.min(quantity.value, props.product.stock || 1)
    );

    router.post(
        route("cart.add"),
        {
            product_id: props.product.id,
            quantity: validQuantity,
            customization: cleanCustomization,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                customization.value = "";
            },
            onError: (errors) => {
                console.error("Erreur ajout panier:", errors);
            },
        }
    );
};

//
// --- Favoris ---
//
const isFavorite = ref(!!props.product.is_favorite);

// Helper pour afficher les étoiles
const getStars = (rating) => "★".repeat(rating) + "☆".repeat(5 - rating);

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
                    isFavorite.value = !isFavorite.value;
                }
            },
        }
    );
};
</script>

<template>
    <Head :title="product.name">
        <meta
            name="description"
            :content="`Découvrez ${product.name} — création artisanale personnalisée. Livraison rapide en Belgique/UE, paiement sécurisé.`"
        />
        <meta name="robots" content="index, follow" />

        <!-- Canonical -->
        <link rel="canonical" :href="route('products.show', product.slug)" />

        <!-- Open Graph -->
        <meta property="og:type" content="product" />
        <meta property="og:site_name" content="Atelier de Bidibulle" />
        <meta property="og:title" :content="product.name" />
        <meta
            property="og:description"
            content="Création artisanale personnalisée. Livraison rapide en Belgique/UE."
        />
        <meta
            property="og:url"
            :content="route('products.show', product.slug)"
        />
        <meta
            property="og:image"
            :content="
                product.image_url.startsWith('http')
                    ? product.image_url
                    : `https://atelierdebidibulle.be${product.image_url}`
            "
        />

        <!-- Twitter -->
        <meta name="twitter:card" content="summary_large_image" />
        <meta name="twitter:title" :content="product.name" />
        <meta
            name="twitter:description"
            content="Création artisanale personnalisée. Livraison rapide en Belgique/UE."
        />
        <meta
            name="twitter:image"
            :content="
                product.image_url.startsWith('http')
                    ? product.image_url
                    : `https://atelierdebidibulle.be${product.image_url}`
            "
        />
    </Head>

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
                    <h1 class="text-2xl font-bold mb-2">{{ product.name }}</h1>

                    <!-- Prix + badge stock -->
                    <div class="flex items-center gap-3 mb-4">
                        <p class="text-2xl font-bold">
                            {{ Number(product.price).toFixed(2) }}€
                            <span
                                v-if="product.old_price"
                                class="text-red-600 text-base line-through ml-2"
                            >
                                {{ Number(product.old_price).toFixed(2) }}€
                            </span>
                        </p>

                        <span
                            v-if="product.stock === 0"
                            class="text-sm font-medium text-red-600"
                        >
                            Rupture
                        </span>
                        <span
                            v-else-if="product.stock <= 3"
                            class="text-sm font-medium text-amber-600"
                        >
                            Plus que {{ product.stock }}
                        </span>
                        <span v-else class="text-sm font-medium text-green-600">
                            En stock
                        </span>
                    </div>

                    <!-- Description -->
                    <p class="text-gray-700 mb-4">{{ product.description }}</p>

                    <!-- Quantité, favoris, catégorie  -->
                    <div class="flex items-center flex-wrap gap-3 mb-6">
                        <div
                            class="flex items-center border border-gray-300 rounded"
                        >
                            <button
                                @click="decrement"
                                :disabled="quantity <= 1"
                                class="px-3 py-1 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-transparent"
                                aria-label="Diminuer la quantité"
                            >
                                -
                            </button>
                            <span class="px-4">{{ quantity }}</span>
                            <button
                                @click="increment"
                                :disabled="quantity >= product.stock"
                                class="px-3 py-1 hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-transparent"
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
                            title="Ajouter aux favoris"
                        >
                            <span class="text-xl leading-none">{{
                                isFavorite ? "♥" : "♡"
                            }}</span>
                        </button>

                        <Link
                            v-if="product.category?.name"
                            :href="
                                route('categories.show', product.category.slug)
                            "
                            class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-700 hover:underline"
                        >
                            {{ product.category.name }}
                        </Link>
                    </div>

                    <!-- Personnalisation -->
                    <div class="mb-6">
                        <label
                            class="block mb-2 text-sm font-medium text-gray-700"
                            >Personnalisation (facultatif) :</label
                        >
                        <input
                            v-model="customization"
                            type="text"
                            placeholder="Ex : Mamy d'amour"
                            maxlength="100"
                            class="w-full border border-gray-300 rounded px-3 py-2"
                        />
                    </div>

                    <!-- Bouton panier + message rupture -->
                    <div class="mb-4">
                        <button
                            @click="addToCart"
                            :disabled="product.stock === 0"
                            class="bg-bidibordeaux hover:bg-rose-800 text-white font-bold py-3 px-6 rounded w-full disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Ajouter au panier
                        </button>
                        <p
                            v-if="product.stock === 0"
                            class="text-red-600 font-semibold text-sm mt-2"
                        >
                            Ce produit est actuellement en rupture de stock.
                        </p>
                    </div>

                    <!-- Retour catalogue -->
                    <Link
                        :href="route('products.index')"
                        class="mt-6 inline-block text-sm text-gray-500 underline"
                    >
                        ← Retour au catalogue
                    </Link>
                </div>
            </div>
        </section>
        <!-- Section avis produit -->
        <ProductReviews
            :product="props.product"
            :visible-reviews="visibleReviews"
            :has-more-reviews="hasMoreReviews"
            :can-show-less="canShowLess"
            :show-review-form="showReviewForm"
            :new-review="newReview"
            :new-rating="newRating"
            :has-user-review="hasUserReview"
            @ask-delete-review="askDeleteReview"
            @show-more="showMoreReviews"
            @show-less="showLessReviews"
            @toggle-form="showReviewForm = !showReviewForm"
            @submit-review="submitReview"
            @update:newRating="newRating = $event"
            @update:newReview="newReview = $event"
        />

        <!-- Section produits similaires-->
        <section class="mb-20 max-w-[1440px] mx-auto px-4 md:px-8">
            <SimilarProducts :products="props.similarProducts" />
        </section>

        <!-- Modal suppression avis -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Supprimer l'avis</h3>
                <p class="text-gray-600 mb-6">
                    Voulez-vous vraiment supprimer cet avis ?
                </p>
                <div class="flex justify-end gap-3">
                    <button
                        @click="showDeleteModal = false"
                        class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button
                        @click="confirmDeleteReview"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
                    >
                        Supprimer
                    </button>
                </div>
            </div>
        </Modal>
    </PublicLayout>
</template>
