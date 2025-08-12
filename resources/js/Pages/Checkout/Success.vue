<script setup>
const props = defineProps({ order: Object });
</script>

<template>
    <div class="max-w-2xl mx-auto p-8 text-center">
        <h1 class="text-2xl font-bold mb-4">Paiement réussi 🎉</h1>
        <p class="mb-2">Commande #{{ props.order.id }}</p>
        <p class="text-lg font-semibold">
            Total : {{ (props.order.total_price / 100).toFixed(2) }}
            {{ props.order.currency }}
        </p>

        <div class="mt-6">
            <a href="/" class="text-indigo-600 underline">Retour à l’accueil</a>
        </div>
    </div>
</template>
