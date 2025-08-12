$
<script setup>
import { ref } from "vue";
import axios from "axios";

const props = defineProps({
    order: Object, // envoyé depuis Laravel avec les infos commande (euros)
});

const loading = ref(false);
const errorMessage = ref("");

const fmt = (v) => Number(v ?? 0).toFixed(2) + " €";

const payWithStripe = async () => {
    loading.value = true;
    errorMessage.value = "";

    try {
        const { data } = await axios.post(
            route("checkout.payment.session", { order: props.order.uuid })
        );
        if (data.url) {
            window.location.href = data.url; // Redirection Stripe Checkout
        } else {
            errorMessage.value = "Impossible d'obtenir le lien de paiement.";
            loading.value = false;
        }
    } catch (e) {
        console.error(e);
        errorMessage.value = "Erreur lors de la création de la session Stripe.";
        loading.value = false;
    }
};
</script>

<template>
    <div class="max-w-3xl mx-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Paiement</h1>

        <!-- Récapitulatif -->
        <div class="border rounded-lg p-4 mb-6 bg-white shadow-sm">
            <h2 class="text-lg font-semibold mb-3">Récapitulatif commande</h2>

            <ul v-if="order.items?.length" class="divide-y">
                <li
                    v-for="item in order.items"
                    :key="item.id"
                    class="flex justify-between py-2"
                >
                    <span>{{ item.name }} × {{ item.quantity }}</span>
                    <span>{{ fmt(item.unit_price) }}</span>
                </li>
            </ul>

            <div class="flex justify-between font-semibold mt-4">
                <span>Livraison</span>
                <span>{{ fmt(order.shipping_total) }}</span>
            </div>
            <div class="flex justify-between font-bold text-lg mt-2">
                <span>Total</span>
                <span>{{ fmt(order.total_price) }}</span>
            </div>
        </div>

        <!-- Erreur -->
        <p v-if="errorMessage" class="text-red-600 mb-4">{{ errorMessage }}</p>

        <!-- Bouton -->
        <button
            @click="payWithStripe"
            :disabled="loading"
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg w-full font-semibold disabled:opacity-50"
        >
            <span v-if="loading">Redirection vers Stripe…</span>
            <span v-else>Payer avec Stripe</span>
        </button>
    </div>
</template>
