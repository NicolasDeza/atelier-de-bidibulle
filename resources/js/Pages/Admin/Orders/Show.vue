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
    <div class="max-w-4xl mx-auto p-6 space-y-6">
        <div class="flex items-center gap-4">
            <Link
                :href="route('admin.orders.index', { token })"
                class="text-blue-600 hover:underline"
            >
                ← Retour aux commandes
            </Link>
            <h1 class="text-2xl font-bold">Commande {{ order.uuid }}</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Détails commande -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Détails</h2>
                <div class="space-y-2 text-sm">
                    <div>
                        <strong>Client:</strong> {{ order.customer_email }}
                    </div>
                    <div>
                        <strong>Total:</strong> {{ euros(order.total_price) }}
                        {{ order.currency }}
                    </div>
                    <div>
                        <strong>Statut paiement:</strong>
                        {{ order.payment_status }}
                    </div>
                    <div>
                        <strong>Payée le:</strong> {{ order.paid_at || "—" }}
                    </div>
                    <div>
                        <strong>Livraison:</strong>
                        {{ order.shipping_method_label || "—" }}
                    </div>
                </div>
            </div>

            <!-- Suivi -->
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold mb-4">Suivi</h2>
                <form @submit.prevent="submitTracking" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1"
                            >Numéro de suivi</label
                        >
                        <input
                            v-model="form.tracking_number"
                            type="text"
                            class="w-full rounded border-gray-300"
                            placeholder="Entrez le numéro de suivi"
                        />
                    </div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
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

        <!-- Articles -->
        <div class="bg-white rounded-xl shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left">Produit</th>
                        <th class="px-4 py-2 text-left">Quantité</th>
                        <th class="px-4 py-2 text-left">Prix unitaire</th>
                        <th class="px-4 py-2 text-left">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="item in order.order_products" :key="item.id">
                        <td class="px-4 py-2">{{ item.product.name }}</td>
                        <td class="px-4 py-2">{{ item.quantity }}</td>
                        <td class="px-4 py-2">
                            {{ euros(item.price) }} {{ order.currency }}
                        </td>
                        <td class="px-4 py-2">
                            {{ euros(item.price * item.quantity) }}
                            {{ order.currency }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
