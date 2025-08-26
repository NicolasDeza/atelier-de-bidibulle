<script setup>
import { Link } from "@inertiajs/vue3";
import Pagination from "@/Components/Pagination.vue";

const props = defineProps({
    orders: Object,
    filters: { type: Object, default: () => ({}) },
    token: String,
});

const scopeOptions = [
    { value: "to-ship", label: "À préparer" },
    { value: "shipped", label: "Expédiées" },
    { value: "all", label: "Toutes" },
];
const euros = (n) =>
    Number(n || 0)
        .toFixed(2)
        .replace(".", ",");
</script>

<template>
    <div class="py-4 sm:py-8">
        <div
            class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4 sm:space-y-6"
        >
            <!-- Header avec même style que Products -->
            <div class="flex items-center justify-between">
                <h1 class="text-xl sm:text-2xl font-semibold">Commandes</h1>

                <Link
                    :href="route('profile.show')"
                    class="inline-flex h-9 items-center justify-center rounded-md border border-gray-300 bg-white px-3 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 w-fit"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 mr-1 sm:mr-2"
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

            <!-- Filtres avec même structure que Products -->
            <div
                class="rounded-lg bg-white p-4 shadow flex flex-col sm:flex-row sm:items-center gap-3"
            >
                <form
                    method="get"
                    class="flex flex-col sm:flex-row gap-3 w-full"
                >
                    <input type="hidden" name="token" :value="token" />
                    <input
                        name="q"
                        :value="filters?.q || ''"
                        placeholder="Rechercher (UUID, email)"
                        class="flex-1 rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <select
                        name="scope"
                        :value="filters?.scope || 'to-ship'"
                        class="rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    >
                        <option
                            v-for="o in scopeOptions"
                            :key="o.value"
                            :value="o.value"
                        >
                            {{ o.label }}
                        </option>
                    </select>
                    <button
                        class="inline-flex h-9 items-center justify-center rounded-md bg-indigo-600 px-4 text-sm font-medium text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Filtrer
                    </button>
                </form>
            </div>

            <!-- Tableau responsive -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <!-- Version mobile (cartes) -->
                <div class="sm:hidden">
                    <div
                        v-for="o in orders.data"
                        :key="o.id"
                        class="border-b border-gray-200 p-4"
                    >
                        <div class="space-y-3">
                            <!-- Header carte -->
                            <div class="flex items-start justify-between">
                                <div class="flex-1 min-w-0">
                                    <div
                                        class="font-mono text-xs text-gray-500 truncate"
                                    >
                                        {{ o.uuid }}
                                    </div>
                                    <div
                                        class="font-medium text-gray-900 truncate"
                                    >
                                        {{ o.customer_email }}
                                    </div>
                                </div>
                                <!-- Action mobile -->
                                <Link
                                    :href="
                                        route('admin.orders.show', {
                                            order: o.uuid,
                                            token,
                                        })
                                    "
                                    class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 ml-2"
                                    title="Voir"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                        />
                                    </svg>
                                </Link>
                            </div>

                            <!-- Détails carte -->
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div>
                                    <span class="text-gray-500">Articles:</span>
                                    <span class="font-medium ml-1">{{
                                        o.items_count
                                    }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Total:</span>
                                    <span class="font-semibold ml-1"
                                        >{{ euros(o.total_price) }}
                                        {{ o.currency }}</span
                                    >
                                </div>
                                <div>
                                    <span class="text-gray-500">Payée le:</span>
                                    <span class="ml-1">{{
                                        o.paid_at || "—"
                                    }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500"
                                        >Livraison:</span
                                    >
                                    <span class="ml-1">{{
                                        o.shipping_method_label || "—"
                                    }}</span>
                                </div>
                            </div>

                            <!-- Statut -->
                            <div class="flex justify-end">
                                <span
                                    v-if="o.shipping_status === 'shipped'"
                                    class="inline-flex items-center rounded-md bg-green-100 text-green-700 px-2 py-0.5 text-xs font-medium"
                                    >Expédiée</span
                                >
                                <span
                                    v-else-if="o.payment_status === 'paid'"
                                    class="inline-flex items-center rounded-md bg-amber-100 text-amber-700 px-2 py-0.5 text-xs font-medium"
                                    >À préparer</span
                                >
                                <span
                                    v-else
                                    class="inline-flex items-center rounded-md bg-gray-100 text-gray-600 px-2 py-0.5 text-xs font-medium"
                                    >{{ o.payment_status }}</span
                                >
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="orders.data.length === 0"
                        class="p-8 text-center text-gray-500"
                    >
                        Aucune commande.
                    </div>
                </div>

                <!-- Version desktop (tableau) -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left">
                            <tr>
                                <th class="px-4 py-3 font-medium text-gray-700">
                                    Payée le
                                </th>
                                <th class="px-4 py-3 font-medium text-gray-700">
                                    Commande
                                </th>
                                <th class="px-4 py-3 font-medium text-gray-700">
                                    Client
                                </th>
                                <th class="px-4 py-3 font-medium text-gray-700">
                                    Articles
                                </th>
                                <th class="px-4 py-3 font-medium text-gray-700">
                                    Total
                                </th>
                                <th class="px-4 py-3 font-medium text-gray-700">
                                    Livraison
                                </th>
                                <th class="px-4 py-3 font-medium text-gray-700">
                                    Statut
                                </th>
                                <th
                                    class="px-4 py-3 w-28 text-right font-medium text-gray-700"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="o in orders.data"
                                :key="o.id"
                                class="border-t last:border-b"
                            >
                                <td class="px-4 py-3">
                                    {{ o.paid_at ?? "—" }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-mono text-xs">
                                        {{ o.uuid }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-gray-600">
                                        {{ o.customer_email }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">{{ o.items_count }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-semibold">
                                        {{ euros(o.total_price) }}
                                        {{ o.currency }}
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    {{ o.shipping_method_label ?? "—" }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        v-if="o.shipping_status === 'shipped'"
                                        class="inline-flex items-center rounded-md bg-green-100 text-green-700 px-2 py-0.5 text-xs font-medium"
                                        >Expédiée</span
                                    >
                                    <span
                                        v-else-if="o.payment_status === 'paid'"
                                        class="inline-flex items-center rounded-md bg-amber-100 text-amber-700 px-2 py-0.5 text-xs font-medium"
                                        >À préparer</span
                                    >
                                    <span
                                        v-else
                                        class="inline-flex items-center rounded-md bg-gray-100 text-gray-600 px-2 py-0.5 text-xs font-medium"
                                        >{{ o.payment_status }}</span
                                    >
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end">
                                        <Link
                                            :href="
                                                route('admin.orders.show', {
                                                    order: o.uuid,
                                                    token,
                                                })
                                            "
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            title="Voir la commande"
                                        >
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                />
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                />
                                            </svg>
                                            <span class="sr-only">Voir</span>
                                        </Link>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="orders.data.length === 0">
                                <td
                                    colspan="8"
                                    class="px-4 py-10 text-center text-gray-500"
                                >
                                    Aucune commande.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="border-t p-4">
                    <Pagination :links="orders.links" />
                </div>
            </div>
        </div>
    </div>
</template>
