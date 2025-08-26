<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    orders: Array,
});
</script>

<template>
    <div
        class="max-w-7xl mx-auto py-6 sm:py-10 px-4 sm:px-6 space-y-6 sm:space-y-8"
    >
        <!-- Header avec bouton retour -->
        <div
            class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
        >
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                Tableau de bord Admin
            </h1>

            <Link
                :href="route('profile.show')"
                class="inline-flex items-center gap-2 h-9 px-3 sm:px-4 border border-gray-300 rounded-md bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 w-fit"
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
                <span class="hidden sm:inline">Retour au profil</span>
                <span class="sm:hidden">Retour</span>
            </Link>
        </div>

        <!-- Cartes de navigation -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <Link
                href="/admin/products"
                class="bg-white shadow-md hover:shadow-lg rounded-xl p-6 flex flex-col justify-between transition"
            >
                <h2 class="text-xl font-semibold text-gray-800">Produits</h2>
                <p class="text-gray-500 text-sm">
                    Gérer le catalogue (ajout, édition, suppression).
                </p>
            </Link>

            <Link
                href="/admin/orders"
                class="bg-white shadow-md hover:shadow-lg rounded-xl p-6 flex flex-col justify-between transition"
            >
                <h2 class="text-xl font-semibold text-gray-800">Commandes</h2>
                <p class="text-gray-500 text-sm">
                    Voir et gérer les commandes clients.
                </p>
            </Link>

            <div
                class="bg-white shadow-md rounded-xl p-6 flex flex-col justify-between opacity-60"
            >
                <h2 class="text-xl font-semibold text-gray-800">
                    Utilisateurs
                </h2>
                <p class="text-gray-500 text-sm">
                    (Optionnel) Gestion des comptes clients.
                </p>
            </div>
        </div>

        <!-- Dernières commandes responsive -->
        <div class="mt-8 sm:mt-10">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4">
                Dernières commandes
            </h2>

            <!-- Version mobile (cartes) -->
            <div class="sm:hidden space-y-3">
                <div
                    v-for="order in orders"
                    :key="order.id"
                    class="bg-white shadow rounded-lg p-4"
                >
                    <div class="space-y-2">
                        <div class="flex justify-between items-start">
                            <div class="font-mono text-xs text-gray-500">
                                {{ order.uuid }}
                            </div>
                            <span
                                :class="{
                                    'text-green-600 font-medium bg-green-100 px-2 py-0.5 rounded text-xs':
                                        order.payment_status === 'paid',
                                    'text-yellow-600 font-medium bg-yellow-100 px-2 py-0.5 rounded text-xs':
                                        order.payment_status === 'processing',
                                    'text-red-600 font-medium bg-red-100 px-2 py-0.5 rounded text-xs':
                                        order.payment_status === 'failed',
                                }"
                            >
                                {{ order.payment_status }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-sm">
                            <div>
                                <span class="text-gray-500">Montant:</span>
                                <span class="font-medium ml-1"
                                    >{{ order.total_price.toFixed(2) }} €</span
                                >
                            </div>
                            <div>
                                <span class="text-gray-500">Articles:</span>
                                <span class="font-medium ml-1">{{
                                    order.items_count
                                }}</span>
                            </div>
                        </div>

                        <div class="text-sm">
                            <span class="text-gray-500">Payée le:</span>
                            <span class="ml-1">{{ order.paid_at || "—" }}</span>
                        </div>
                    </div>
                </div>

                <div
                    v-if="orders.length === 0"
                    class="bg-white shadow rounded-lg p-8 text-center text-gray-500"
                >
                    Aucune commande récente
                </div>
            </div>

            <!-- Version desktop (tableau) -->
            <div
                class="hidden sm:block bg-white shadow rounded-lg overflow-hidden"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-100 text-gray-700">
                            <tr>
                                <th class="px-4 py-3 font-medium">UUID</th>
                                <th class="px-4 py-3 font-medium">Statut</th>
                                <th class="px-4 py-3 font-medium">Montant</th>
                                <th class="px-4 py-3 font-medium">Articles</th>
                                <th class="px-4 py-3 font-medium">Payée le</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="order in orders"
                                :key="order.id"
                                class="border-t hover:bg-gray-50"
                            >
                                <td class="px-4 py-3 font-mono text-xs">
                                    {{ order.uuid }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        :class="{
                                            'text-green-600 font-medium':
                                                order.payment_status === 'paid',
                                            'text-yellow-600 font-medium':
                                                order.payment_status ===
                                                'processing',
                                            'text-red-600 font-medium':
                                                order.payment_status ===
                                                'failed',
                                        }"
                                    >
                                        {{ order.payment_status }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-medium">
                                    {{ order.total_price.toFixed(2) }} €
                                </td>
                                <td class="px-4 py-3">
                                    {{ order.items_count }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ order.paid_at || "—" }}
                                </td>
                            </tr>

                            <tr v-if="orders.length === 0">
                                <td
                                    colspan="5"
                                    class="px-4 py-8 text-center text-gray-500"
                                >
                                    Aucune commande récente
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
