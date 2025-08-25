<script setup>
import { Link } from "@inertiajs/vue3";
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
    <div class="max-w-6xl mx-auto p-6 space-y-6">
        <h1 class="text-2xl font-bold">Commandes</h1>

        <form method="get" class="flex gap-3">
            <input type="hidden" name="token" :value="token" />
            <input
                name="q"
                :value="filters?.q || ''"
                placeholder="Rechercher (UUID, email)"
                class="flex-1 rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500"
            />
            <select
                name="scope"
                :value="filters?.scope || 'to-ship'"
                class="rounded border-gray-300 focus:border-blue-500 focus:ring-blue-500"
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
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 rounded"
            >
                Filtrer
            </button>
        </form>

        <div class="bg-white rounded-xl shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Payée le</th>
                        <th class="px-4 py-2 text-left">Commande</th>
                        <th class="px-4 py-2 text-left">Client</th>
                        <th class="px-4 py-2 text-left">Articles</th>
                        <th class="px-4 py-2 text-left">Total</th>
                        <th class="px-4 py-2 text-left">Livraison</th>
                        <th class="px-4 py-2 text-left">Statut</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="o in orders.data" :key="o.id">
                        <td class="px-4 py-2">{{ o.paid_at ?? "—" }}</td>
                        <td class="px-4 py-2 font-mono text-xs">
                            {{ o.uuid }}
                        </td>
                        <td class="px-4 py-2">
                            <div class="text-gray-600">
                                {{ o.customer_email }}
                            </div>
                        </td>
                        <td class="px-4 py-2">
                            {{ o.order_products_sum_quantity }}
                        </td>
                        <td class="px-4 py-2 font-semibold">
                            {{ euros(o.total_price) }} {{ o.currency }}
                        </td>
                        <td class="px-4 py-2">
                            {{ o.shipping_method_label ?? "—" }}
                        </td>
                        <td class="px-4 py-2">
                            <span
                                v-if="o.shipping_status === 'shipped'"
                                class="inline-flex rounded bg-green-100 text-green-700 px-2 py-0.5 text-xs"
                                >Expédiée</span
                            >
                            <span
                                v-else-if="o.payment_status === 'paid'"
                                class="inline-flex rounded bg-amber-100 text-amber-700 px-2 py-0.5 text-xs"
                                >À préparer</span
                            >
                            <span
                                v-else
                                class="inline-flex rounded bg-gray-100 text-gray-600 px-2 py-0.5 text-xs"
                                >{{ o.payment_status }}</span
                            >
                        </td>
                        <td class="px-4 py-2 text-right">
                            <Link
                                :href="
                                    route('admin.orders.show', {
                                        order: o.uuid,
                                        token,
                                    })
                                "
                                class="text-blue-600 hover:underline"
                            >
                                Ouvrir
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex items-center gap-2 text-sm">
            <Link
                v-for="link in orders.links"
                :key="link.url ?? link.label"
                :href="link.url ? `${link.url}&token=${token}` : '#'"
                v-html="link.label"
                :class="[
                    'px-3 py-1 rounded',
                    link.active
                        ? 'bg-blue-600 text-white'
                        : 'bg-gray-100 text-gray-700',
                    !link.url && 'pointer-events-none opacity-50',
                ]"
            />
        </div>
    </div>
</template>
