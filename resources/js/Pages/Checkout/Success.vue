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
    <div class="max-w-xl mx-auto p-6 space-y-4">
        <h1 class="text-2xl font-semibold">Merci pour votre commande 🎉</h1>
        <div>Commande #{{ order?.uuid }}</div>

        <div class="bg-white p-4 rounded shadow">
            <div class="font-medium mb-2">Livraison</div>
            <div>Mode : {{ shipping?.label || "—" }}</div>
            <div>Frais : {{ euros(shipping?.amount_total) }} €</div>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <div class="font-medium mb-2">Adresse</div>
            <div v-if="address">
                <div>{{ address.name }}</div>
                <div>
                    {{ address.line1 }}
                    <span v-if="address.line2">({{ address.line2 }})</span>
                </div>
                <div>{{ address.postal_code }} {{ address.city }}</div>
                <div>{{ address.country }}</div>
            </div>
            <div v-else>Adresse non disponible.</div>
        </div>

        <div class="flex flex-col gap-4 pt-6">
            <Link
                :href="route('home')"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg text-center font-semibold transition-colors"
            >
                Retour à l'accueil
            </Link>

            <Link
                :href="route('products.index')"
                class="bg-gray-100 hover:bg-gray-200 text-gray-800 px-6 py-3 rounded-lg text-center font-medium transition-colors"
            >
                Continuer mes achats
            </Link>
        </div>
    </div>
</template>
