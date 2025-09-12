<script setup>
import { ref, computed } from "vue";
import { usePage, router, Link } from "@inertiajs/vue3";

const page = usePage();

const props = defineProps({
    product: Object,
    visibleReviews: Array,
    hasMoreReviews: Boolean,
    canShowLess: Boolean,
    showReviewForm: Boolean,
    newReview: String,
    newRating: String,
    hasUserReview: Boolean,
});

const emit = defineEmits([
    "ask-delete-review",
    "show-more",
    "show-less",
    "toggle-form",
    "submit-review",
    "update:newRating",
    "update:newReview",
]);

// Helper pour afficher les étoiles
const getStars = (rating) => "★".repeat(rating) + "☆".repeat(5 - rating);
</script>

<template>
    <!-- Section avis produit -->
    <section class="py-12 md:py-20">
        <div class="max-w-4xl mx-auto px-6">
            <h2 class="text-2xl font-bold mb-8 text-center">
                Avis sur ce produit
            </h2>

            <!-- Résumé des notes -->
            <div
                v-if="product.reviews_count > 0"
                class="border border-gray-200 rounded-xl p-6 mb-8"
            >
                <div class="flex items-center justify-center gap-6">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-gray-900">
                            {{ product.average_rating }}
                        </div>
                        <div class="text-yellow-400 text-xl mb-1">
                            {{ getStars(Math.round(product.average_rating)) }}
                        </div>
                        <div class="text-sm text-gray-600">
                            {{ product.reviews_count }} avis
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des avis -->
            <div v-if="product.reviews.length" class="space-y-6">
                <div
                    v-for="review in visibleReviews"
                    :key="review.id"
                    class="border border-gray-200 rounded-xl p-6 hover:border-gray-300 transition-colors"
                >
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 bg-bidibordeaux rounded-full flex items-center justify-center text-white font-semibold"
                            >
                                {{ review.user.name.charAt(0).toUpperCase() }}
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">
                                    {{ review.user.name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{
                                        new Intl.DateTimeFormat("fr-BE").format(
                                            new Date(review.created_at)
                                        )
                                    }}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-yellow-400">
                                {{ getStars(review.rating) }}
                            </span>
                            <button
                                v-if="
                                    page.props.auth?.user?.id === review.user.id
                                "
                                @click="$emit('ask-delete-review', review)"
                                class="text-gray-400 hover:text-red-600 transition-colors p-1"
                                title="Supprimer cet avis"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke-width="1.5"
                                    stroke="currentColor"
                                    class="w-4 h-4"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <p class="text-gray-700 leading-relaxed">
                        {{ review.comment }}
                    </p>
                </div>

                <!-- Boutons Voir plus / Voir moins -->
                <div class="text-center pt-6">
                    <button
                        v-if="hasMoreReviews"
                        @click="$emit('show-more')"
                        class="px-6 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 transition mr-3"
                    >
                        Voir plus d'avis
                    </button>
                    <button
                        v-if="canShowLess"
                        @click="$emit('show-less')"
                        class="px-6 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                    >
                        Voir moins
                    </button>
                </div>
            </div>

            <!-- Aucun avis -->
            <div v-else class="text-center py-16">
                <h3 class="text-xl font-medium text-gray-900 mb-2">
                    Aucun avis pour le moment
                </h3>
                <p class="text-gray-500">
                    Soyez le premier à partager votre expérience avec ce produit
                </p>
            </div>

            <!-- Formulaire ajout d'avis -->
            <div
                v-if="page.props.auth?.user"
                class="mt-12 pt-8 border-t border-gray-200"
            >
                <!-- Si déjà un avis → message -->
                <div
                    v-if="hasUserReview"
                    class="text-center py-6 text-gray-600"
                >
                    Vous avez déjà partagé votre avis sur ce produit.<br />
                    Merci pour votre contribution !
                </div>

                <!-- Sinon on affiche le bouton + formulaire -->
                <template v-else>
                    <button
                        @click="$emit('toggle-form')"
                        class="px-6 py-3 bg-bidibordeaux text-white rounded-lg hover:bg-rose-800 transition font-medium"
                    >
                        {{ showReviewForm ? "Annuler" : "Laisser un avis" }}
                    </button>

                    <div
                        v-if="showReviewForm"
                        class="border border-gray-200 rounded-xl p-6 mt-6"
                    >
                        <h3 class="text-lg font-semibold mb-6 text-gray-900">
                            Votre avis
                        </h3>

                        <div class="mb-6">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Note
                            </label>
                            <select
                                :value="newRating"
                                @input="
                                    $emit(
                                        'update:newRating',
                                        $event.target.value
                                    )
                                "
                                class="border border-gray-300 rounded-lg px-3 py-2 w-full focus:ring-2 focus:ring-bidibordeaux focus:border-bidibordeaux"
                            >
                                <option disabled value="">
                                    Choisir une note
                                </option>
                                <option v-for="n in 5" :key="n" :value="n">
                                    {{ n }} ★
                                </option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                            >
                                Commentaire
                            </label>
                            <textarea
                                :value="newReview"
                                @input="
                                    $emit(
                                        'update:newReview',
                                        $event.target.value
                                    )
                                "
                                rows="4"
                                placeholder="Partagez votre expérience avec ce produit..."
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-bidibordeaux focus:border-bidibordeaux"
                            />
                        </div>

                        <button
                            @click="$emit('submit-review')"
                            class="bg-bidibordeaux hover:bg-rose-800 text-white font-medium px-6 py-3 rounded-lg transition"
                        >
                            Publier mon avis
                        </button>
                    </div>
                </template>
            </div>

            <!-- Message si non connecté -->
            <div v-else class="mt-12 pt-8 border-t border-gray-200">
                <div class="text-center py-12">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">
                        Partagez votre avis
                    </h3>
                    <p class="text-gray-600 mb-6">
                        Connectez-vous pour laisser un commentaire sur ce
                        produit
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 justify-center">
                        <Link
                            :href="route('login')"
                            class="px-6 py-2 bg-bidibordeaux text-white rounded-lg hover:bg-rose-800 transition font-medium"
                        >
                            Se connecter
                        </Link>
                        <Link
                            :href="route('register')"
                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition font-medium"
                        >
                            Créer un compte
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
