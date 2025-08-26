<script setup>
import { Link, useForm, router } from "@inertiajs/vue3";

const props = defineProps({
    order: Object,
    addr: Object,
    token: String,
});

const form = useForm({
    tracking_number: props.order.tracking_number || "",
});

const euros = (n) =>
    Number(n || 0)
        .toFixed(2)
        .replace(".", ",");

const submitTracking = () => {
    form.post(
        route("admin.orders.tracking.update", {
            order: props.order.uuid,
            token: props.token,
        }),
        {
            onSuccess: () => {
                // Rediriger vers la liste après succès
                router.visit(
                    route("admin.orders.index", { token: props.token })
                );
            },
        }
    );
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
                        Commande {{ order.uuid }}
                    </h1>
                </div>
            </div>

            <!-- Card unique responsive -->
            <div class="rounded-lg bg-white shadow p-4 sm:p-6">
                <!-- Détails commande -->
                <div class="mb-6 sm:mb-8">
                    <h2 class="text-lg font-semibold mb-4">
                        Détails de la commande
                    </h2>
                    <div class="space-y-3 text-sm">
                        <!-- Mobile: Stack vertical, Desktop: 2 colonnes -->
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
                                <span class="font-semibold text-gray-900"
                                    >{{ euros(order.total_price) }}
                                    {{ order.currency }}</span
                                >
                            </div>
                            <div
                                class="flex flex-col sm:flex-row sm:justify-between"
                            >
                                <span class="font-medium text-gray-700"
                                    >Statut paiement:</span
                                >
                                <span
                                    class="inline-flex items-center rounded-md bg-green-100 text-green-700 px-2 py-0.5 text-xs font-medium w-fit"
                                    >{{ order.payment_status }}</span
                                >
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

                <!-- Suivi -->
                <div class="border-t pt-4 sm:pt-6">
                    <h2 class="text-lg font-semibold mb-4">
                        Suivi de la commande
                    </h2>
                    <form
                        @submit.prevent="submitTracking"
                        class="w-full max-w-md"
                    >
                        <div class="mb-4">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2"
                                >Numéro de suivi</label
                            >
                            <input
                                v-model="form.tracking_number"
                                type="text"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Entrez le numéro de suivi"
                            />
                        </div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex h-9 items-center justify-center rounded-md bg-indigo-600 px-4 text-sm font-medium text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? "Enregistrement..."
                                    : "Enregistrer"
                            }}
                        </button>
                    </form>
                </div>
            </div>

            <!-- Articles responsive -->
            <div class="rounded-lg bg-white shadow">
                <div class="px-4 sm:px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold">Articles commandés</h2>
                </div>

                <!-- Mobile: Cards -->
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
                            <!-- Personnalisation mobile -->
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
                                <span>
                                    {{ euros(item.price) }} {{ order.currency }}
                                </span>
                            </div>
                            <div
                                class="flex justify-between text-sm font-semibold"
                            >
                                <span>Total:</span>
                                <span>
                                    {{ euros(item.price * item.quantity) }}
                                    {{ order.currency }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Desktop: Table -->
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
                                        <!-- Personnalisation desktop -->
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
    </div>
</template>
