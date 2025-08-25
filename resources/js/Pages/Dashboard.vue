<script setup>
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    orders: Array,
});
</script>

<template>
    <div class="max-w-7xl mx-auto py-10 px-6 space-y-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-6">
            Tableau de bord Admin
        </h1>

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

        <!-- Dernières commandes -->
        <div class="mt-10">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">
                Dernières commandes
            </h2>
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-100 text-gray-700">
                        <tr>
                            <th class="px-4 py-2">UUID</th>
                            <th class="px-4 py-2">Statut</th>
                            <th class="px-4 py-2">Montant</th>
                            <th class="px-4 py-2">Articles</th>
                            <th class="px-4 py-2">Payée le</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="order in orders"
                            :key="order.id"
                            class="border-t hover:bg-gray-50"
                        >
                            <td class="px-4 py-2 font-mono text-xs">
                                {{ order.uuid }}
                            </td>
                            <td class="px-4 py-2">
                                <span
                                    :class="{
                                        'text-green-600 font-medium':
                                            order.payment_status === 'paid',
                                        'text-yellow-600 font-medium':
                                            order.payment_status ===
                                            'processing',
                                        'text-red-600 font-medium':
                                            order.payment_status === 'failed',
                                    }"
                                >
                                    {{ order.payment_status }}
                                </span>
                            </td>
                            <td class="px-4 py-2">
                                {{ order.total_price.toFixed(2) }} €
                            </td>
                            <td class="px-4 py-2">{{ order.items_count }}</td>
                            <td class="px-4 py-2">{{ order.paid_at }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
