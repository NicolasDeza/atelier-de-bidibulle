<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    order: Object,
    shipping: Object,
    address: Object,
});
const euros = (cts) => ((cts || 0) / 100).toFixed(2);
</script>

<template>
    <div class="max-w-2xl mx-auto p-8 space-y-8">
        <!--  En-tête avec icône check -->
        <div class="flex flex-col items-center text-center space-y-4">
            <div
                class="flex items-center justify-center w-16 h-16 rounded-full bg-green-100"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-8 w-8 text-green-600"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M5 13l4 4L19 7"
                    />
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">
                Merci pour votre commande !
            </h1>
            <p class="text-gray-600">Votre paiement a bien été confirmé.</p>
            <p class="text-sm text-gray-500">
                Un e-mail de confirmation vous a été envoyé.
            </p>
        </div>

        <!--  Bloc récap commande -->
        <div class="bg-white shadow rounded-xl p-6 space-y-4">
            <div class="flex justify-between items-center">
                <span class="font-semibold text-gray-800">Commande :</span>
                <span class="text-gray-600">#{{ order?.uuid }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="font-semibold text-gray-800">Total payé :</span>
                <span class="text-gray-900 font-bold">
                    {{ order?.total_price }} {{ order?.currency }}
                </span>
            </div>
        </div>

        <!--  Livraison -->
        <div class="bg-gray-50 rounded-xl p-6 space-y-2">
            <div class="font-medium text-gray-800">Livraison</div>
            <div class="text-gray-600">Mode : {{ shipping?.label || "—" }}</div>
            <div class="text-gray-600">
                Frais : {{ euros(shipping?.amount_total) }} €
            </div>
        </div>

        <!--  Adresse -->
        <div class="bg-gray-50 rounded-xl p-6 space-y-2">
            <div class="font-medium text-gray-800">Adresse de livraison</div>
            <div v-if="address" class="text-gray-600">
                <div>{{ address.name }}</div>
                <div>
                    {{ address.line1 }}
                    <span v-if="address.line2">({{ address.line2 }})</span>
                </div>
                <div>{{ address.postal_code }} {{ address.city }}</div>
                <div>{{ address.country }}</div>
            </div>
            <div v-else class="text-gray-500 italic">
                Adresse non disponible.
            </div>
        </div>

        <!-- Boutons -->
        <div class="flex flex-col sm:flex-row gap-4 pt-4">
            <Link
                :href="route('home')"
                class="flex-1 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg text-center font-semibold transition-colors"
            >
                Retour à l'accueil
            </Link>

            <Link
                :href="route('products.index')"
                class="flex-1 bg-white border border-gray-300 hover:bg-gray-50 text-gray-800 px-6 py-3 rounded-lg text-center font-medium transition-colors"
            >
                Continuer mes achats
            </Link>
        </div>
    </div>
</template>
