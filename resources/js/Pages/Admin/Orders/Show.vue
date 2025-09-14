<script setup>
import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    order: Object,
    addr: Object,
    token: String,
});

// État pour le modal de confirmation
const showModal = ref(false);

// Fonction appelée quand l'admin veut expédier
const markAsShipped = () => {
    showModal.value = true;
};

// Fonction appelée pour confirmer l'expédition
const confirmShipment = () => {
    showModal.value = false;
    router.post(
        route("admin.orders.tracking.update", {
            order: props.order.uuid,
            token: props.token,
        })
    );
};

// Utilitaire affichage prix
const euros = (n) =>
    Number(n || 0)
        .toFixed(2)
        .replace(".", ",");

// Traduction de bd

const paymentLabel = (status) => {
    switch (status) {
        case "paid":
            return "Payé";
        case "unpaid":
            return "Non payé";
        case "pending":
            return "En attente";
        case "refunded":
            return "Remboursé";
        default:
            return status;
    }
};
</script>

<template>
    <div class="min-h-screen py-4 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl w-full mx-auto space-y-4 sm:space-y-6">
            <!-- Header responsive -->
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
            >
                <div
                    class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4"
                >
                    <Link
                        :href="route('admin.orders.index', { token })"
                        class="inline-flex items-center gap-2 rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 w-fit"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4 text-gray-500"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M15 19l-7-7 7-7"
                            />
                        </svg>
                        Retour
                    </Link>
                    <h1 class="text-xl sm:text-2xl font-semibold break-all">
                        Commande #{{ order.uuid }}
                    </h1>
                </div>
            </div>

            <!-- Card détails commande -->
            <div class="rounded-lg bg-white shadow p-4 sm:p-6">
                <div class="mb-6 sm:mb-8">
                    <h2 class="text-lg font-semibold mb-4">
                        Détails de la commande
                    </h2>
                    <div class="space-y-3 text-sm">
                        <div class="grid grid-cols-1 gap-3">
                            <div
                                class="flex flex-col sm:flex-row sm:justify-between"
                            >
                                <span class="font-medium text-gray-700"
                                    >Client:</span
                                >
                                <span class="text-gray-900 break-all">{{
                                    order.customer_email
                                }}</span>
                            </div>
                            <div
                                class="flex flex-col sm:flex-row sm:justify-between"
                            >
                                <span class="font-medium text-gray-700"
                                    >Total:</span
                                >
                                <span class="font-semibold text-gray-900">
                                    {{ euros(order.total_price) }}
                                    {{ order.currency }}
                                </span>
                            </div>
                            <div
                                class="flex flex-col sm:flex-row sm:justify-between"
                            >
                                <span class="font-medium text-gray-700"
                                    >Statut paiement:</span
                                >
                                <span
                                    class="inline-flex items-center rounded-md bg-green-100 text-green-700 px-2 py-0.5 text-xs font-medium w-fit"
                                >
                                    {{ paymentLabel(order.payment_status) }}
                                </span>
                            </div>
                            <div
                                class="flex flex-col sm:flex-row sm:justify-between"
                            >
                                <span class="font-medium text-gray-700"
                                    >Payée le:</span
                                >
                                <span class="text-gray-900">{{
                                    order.paid_at || "—"
                                }}</span>
                            </div>
                            <div
                                class="flex flex-col sm:flex-row sm:justify-between"
                            >
                                <span class="font-medium text-gray-700"
                                    >Livraison:</span
                                >
                                <span class="text-gray-900">{{
                                    order.shipping_method_label || "—"
                                }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bouton expédition -->
                <div class="border-t pt-4 sm:pt-6">
                    <h2 class="text-lg font-semibold mb-4">
                        Suivi de la commande
                    </h2>
                    <button
                        @click="markAsShipped"
                        class="inline-flex h-9 items-center justify-center rounded-md bg-green-600 px-4 text-sm font-medium text-white transition hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                    >
                        Valider la commande
                    </button>
                </div>
            </div>

            <!-- Articles -->
            <div class="rounded-lg bg-white shadow">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold">Articles commandés</h2>
                </div>

                <!-- Mobile: cartes -->
                <div class="sm:hidden">
                    <div
                        v-for="item in order.order_products"
                        :key="item.id"
                        class="border-b border-gray-200 p-4 last:border-b-0"
                    >
                        <div class="space-y-2">
                            <div class="font-medium text-gray-900">
                                {{ item.product.name }}
                            </div>
                            <div v-if="item.customization" class="text-sm">
                                <span class="text-gray-600"
                                    >Personnalisation :</span
                                >
                                <span class="ml-1 text-blue-600 font-medium">{{
                                    item.customization
                                }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Quantité:</span>
                                <span
                                    class="inline-flex items-center rounded-md bg-gray-100 text-gray-700 px-2 py-0.5 text-xs font-medium"
                                >
                                    {{ item.quantity }}
                                </span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600"
                                    >Prix unitaire:</span
                                >
                                <span
                                    >{{ euros(item.price) }}
                                    {{ order.currency }}</span
                                >
                            </div>
                            <div
                                class="flex justify-between text-sm font-semibold"
                            >
                                <span>Total:</span>
                                <span
                                    >{{ euros(item.price * item.quantity) }}
                                    {{ order.currency }}</span
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop: table -->
                <div class="hidden sm:block">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-left">
                                <tr>
                                    <th
                                        class="px-4 py-3 font-medium text-gray-700"
                                    >
                                        Produit
                                    </th>
                                    <th
                                        class="px-4 py-3 font-medium text-gray-700"
                                    >
                                        Quantité
                                    </th>
                                    <th
                                        class="px-4 py-3 font-medium text-gray-700"
                                    >
                                        Prix unitaire
                                    </th>
                                    <th
                                        class="px-4 py-3 font-medium text-gray-700 text-right"
                                    >
                                        Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="item in order.order_products"
                                    :key="item.id"
                                    class="border-t last:border-b"
                                >
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">
                                            {{ item.product.name }}
                                        </div>
                                        <div
                                            v-if="item.customization"
                                            class="text-sm text-blue-600 mt-1"
                                        >
                                            <span class="text-gray-600"
                                                >Personnalisation :</span
                                            >
                                            {{ item.customization }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="inline-flex items-center rounded-md bg-gray-100 text-gray-700 px-2 py-0.5 text-xs font-medium"
                                        >
                                            {{ item.quantity }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="text-gray-900">
                                            {{ euros(item.price) }}
                                            {{ order.currency }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div
                                            class="font-semibold text-gray-900"
                                        >
                                            {{
                                                euros(
                                                    item.price * item.quantity
                                                )
                                            }}
                                            {{ order.currency }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de confirmation -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="p-6">
                <h2 class="text-lg font-semibold mb-4">
                    Confirmer l’expédition
                </h2>
                <p class="text-gray-700 mb-6">
                    Êtes-vous sûr de vouloir marquer cette commande comme
                    expédiée ?
                </p>

                <div class="flex justify-end gap-3">
                    <button
                        @click="showModal = false"
                        class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-100"
                    >
                        Annuler
                    </button>
                    <button
                        @click="confirmShipment"
                        class="px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-500"
                    >
                        Expédier
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>
