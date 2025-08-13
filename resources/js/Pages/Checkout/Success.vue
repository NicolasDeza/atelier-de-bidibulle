<script setup>
const props = defineProps({
    order: Object,
    shipping: Object,
    address: Object,
});
const euros = (cts) => ((cts || 0) / 100).toFixed(2);
</script>

<template>
    <div class="max-w-xl mx-auto p-6 space-y-4">
        <h1 class="text-2xl font-semibold">Merci pour votre commande 🎉</h1>
        <div>Commande #{{ order?.uuid }}</div>

        <div class="bg-white p-4 rounded shadow">
            <div class="font-medium mb-2">Livraison</div>
            <div>Mode : {{ shipping?.label || "—" }}</div>
            <div>Frais : {{ euros(shipping?.amount_total) }} €</div>
        </div>

        <div class="bg-white p-4 rounded shadow">
            <div class="font-medium mb-2">Adresse</div>
            <div v-if="address">
                <div>{{ address.name }}</div>
                <div>
                    {{ address.line1 }}
                    <span v-if="address.line2">({{ address.line2 }})</span>
                </div>
                <div>{{ address.postal_code }} {{ address.city }}</div>
                <div>{{ address.country }}</div>
            </div>
            <div v-else>Adresse non disponible.</div>
        </div>
    </div>
</template>
