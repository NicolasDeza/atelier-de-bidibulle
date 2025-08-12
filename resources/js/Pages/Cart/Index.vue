<script setup>
import { Link, router } from "@inertiajs/vue3";
import { computed } from "vue";
import PublicLayout from "@/Layouts/PublicLayout.vue";

const props = defineProps({
    cartItems: { type: Array, default: () => [] },
    total: { type: Number, default: 0 },
    isAuthenticated: { type: Boolean, default: false },
});

// --- TVA (les prix sont déjà TVAC)
const TVA_RATE = 0.21;
const subTotalHT = computed(() => {
    const ht = props.total / (1 + TVA_RATE);
    return ht.toFixed(2);
});
const tvaAmount = computed(() => {
    const ht = props.total / (1 + TVA_RATE);
    const tva = props.total - ht;
    return tva.toFixed(2);
});

// --- Y a-t-il un problème de stock dans le panier ?
const hasStockIssue = computed(() =>
    props.cartItems.some(
        (i) => !i.is_available || i.stock === 0 || i.quantity > i.stock
    )
);

// Mise à jour de la quantité (client-side guard + requête)
const updateQuantity = (item, newQuantity) => {
    if (newQuantity < 1) return;

    // Ne pas dépasser le stock côté front (UX)
    if (typeof item.stock === "number" && newQuantity > item.stock) {
        newQuantity = item.stock;
        alert(`Stock insuffisant (max ${item.stock}).`);
    }

    if (props.isAuthenticated) {
        router.put(
            route("cart.update", item.id),
            { quantity: newQuantity },
            { preserveScroll: true }
        );
    } else {
        router.put(
            route("cart.session.update", item.key),
            { quantity: newQuantity },
            { preserveScroll: true }
        );
    }
};

// Supprimer un produit
const removeItem = (item) => {
    if (!confirm("Voulez-vous retirer ce produit du panier ?")) return;

    if (props.isAuthenticated) {
        router.delete(route("cart.remove", item.id), { preserveScroll: true });
    } else {
        router.delete(route("cart.session.remove", item.key), {
            preserveScroll: true,
        });
    }
};

// Vider le panier
const clearCart = () => {
    if (!confirm("Voulez-vous vider le panier ?")) return;

    router.delete(route("cart.clear"), { preserveScroll: true });
};
</script>

<template>
    <PublicLayout>
        <div class="max-w-[1440px] mx-auto px-4 md:px-8 py-8">
            <h1 class="text-3xl font-bold mb-8">Mon panier</h1>

            <!-- Panier vide -->
            <div v-if="cartItems.length === 0" class="text-center py-16">
                <p class="text-gray-500 text-lg mb-6">Votre panier est vide</p>
                <Link
                    :href="route('products.index')"
                    class="bg-bidibordeaux hover:bg-rose-800 text-white px-6 py-3 rounded font-semibold"
                >
                    Continuer vos achats
                </Link>
            </div>

            <!-- Panier avec articles -->
            <div v-else class="grid lg:grid-cols-3 gap-8">
                <!-- Liste des articles -->
                <div class="lg:col-span-2 space-y-4">
                    <div
                        v-for="item in cartItems"
                        :key="item.id || item.key"
                        class="grid grid-cols-[auto_1fr_auto] items-start gap-4 p-4 bg-white rounded-lg shadow border"
                    >
                        <!-- Image -->
                        <img
                            :src="item.image_url"
                            :alt="item.name"
                            class="w-24 h-24 object-cover rounded"
                        />

                        <!-- Contenu -->
                        <div class="min-w-0">
                            <h3 class="font-semibold text-lg">
                                {{ item.name }}
                            </h3>
                            <p class="text-gray-600">
                                {{ Number(item.price).toFixed(2) }}€
                            </p>

                            <p
                                v-if="item.customization"
                                class="text-sm text-gray-500 italic"
                            >
                                Personnalisation : {{ item.customization }}
                            </p>

                            <!-- État de stock -->
                            <p
                                v-if="item.stock === 0"
                                class="text-red-600 text-sm font-semibold mt-1"
                            >
                                Rupture de stock
                            </p>
                            <p
                                v-else-if="item.stock <= 3"
                                class="text-amber-600 text-sm mt-1"
                            >
                                Plus que {{ item.stock }} en stock
                            </p>
                            <p v-else class="text-gray-500 text-xs mt-1">
                                Stock disponible
                            </p>

                            <!-- Actions -->
                            <div class="flex items-center gap-4 mt-3">
                                <div class="flex items-center border rounded">
                                    <button
                                        @click="
                                            updateQuantity(
                                                item,
                                                item.quantity - 1
                                            )
                                        "
                                        class="px-3 py-1 hover:bg-gray-100"
                                        aria-label="Diminuer la quantité"
                                    >
                                        -
                                    </button>
                                    <span class="px-4">{{
                                        item.quantity
                                    }}</span>
                                    <button
                                        @click="
                                            updateQuantity(
                                                item,
                                                item.quantity + 1
                                            )
                                        "
                                        :disabled="
                                            typeof item.stock === 'number' &&
                                            item.quantity >= item.stock
                                        "
                                        class="px-3 py-1 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                        aria-label="Augmenter la quantité"
                                    >
                                        +
                                    </button>
                                </div>

                                <button
                                    @click="removeItem(item)"
                                    class="text-red-600 hover:text-red-800 p-2 rounded hover:bg-red-50 transition-colors"
                                    title="Supprimer du panier"
                                    aria-label="Supprimer du panier"
                                >
                                    <!-- icône poubelle -->
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke-width="1.5"
                                        stroke="currentColor"
                                        class="w-5 h-5"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Prix total (toujours à droite) -->
                        <div class="text-right whitespace-nowrap self-center">
                            <p class="font-semibold text-lg">
                                {{ Number(item.subtotal).toFixed(2) }}€
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Résumé de la commande -->
                <div class="bg-gray-50 p-6 rounded-lg h-fit">
                    <h2 class="font-semibold text-xl mb-4">Résumé</h2>

                    <div class="space-y-2 mb-4">
                        <!-- Sous-total HT -->
                        <div class="flex justify-between">
                            <span>Sous-total (HT)</span>
                            <span>{{ subTotalHT }}€</span>
                        </div>

                        <!-- TVA 21% -->
                        <div class="flex justify-between">
                            <span>TVA (21 %)</span>
                            <span>{{ tvaAmount }}€</span>
                        </div>

                        <!-- Livraison -->
                        <div class="flex justify-between">
                            <span>Livraison</span>
                            <span>Calculé à l'étape suivante</span>
                        </div>

                        <hr />

                        <!-- Total TTC -->
                        <div class="flex justify-between font-semibold text-lg">
                            <span>Total TTC</span>
                            <span>{{ Number(total).toFixed(2) }}€</span>
                        </div>
                    </div>

                    <button
                        :disabled="hasStockIssue"
                        @click.prevent="$inertia.post(route('cart.checkout'))"
                        class="w-full bg-bidibordeaux hover:bg-rose-800 text-white py-3 rounded font-semibold mb-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        aria-label="Passer à l’adresse et à la livraison"
                    >
                        Procéder au paiement
                    </button>
                    <p v-if="hasStockIssue" class="text-sm text-red-600 mb-2">
                        Un ou plusieurs articles ne sont plus disponibles en
                        quantité suffisante.
                    </p>

                    <button
                        @click="clearCart"
                        class="w-full border border-gray-300 py-2 rounded hover:bg-gray-100 text-sm"
                        aria-label="Vider complètement le panier"
                    >
                        Vider le panier
                    </button>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
