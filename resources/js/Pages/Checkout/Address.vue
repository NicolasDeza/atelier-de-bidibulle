<script setup>
import { useForm } from "@inertiajs/vue3";
import { computed } from "vue";

const props = defineProps({
    order: Object,
    address: Object,
    methods: Array,
    countries: Array,
    guest: Boolean,
    errors: Object,
    items: Array,
    subtotal: Number,
});

const form = useForm({
    customer_email: props.order?.customer_email ?? "",
    full_name: props.address?.full_name ?? "",
    address_line_1: props.address?.address_line_1 ?? "",
    address_line_2: props.address?.address_line_2 ?? "",
    postal_code: props.address?.postal_code ?? "",
    country_id: props.address?.country_id ?? props.countries?.[0]?.id ?? null,
    city_name: props.address?.city_name ?? "",
    phone_number: props.address?.phone_number ?? "",
    shipping_method_id:
        props.order?.shipping_method_id ?? props.methods?.[0]?.id ?? null,
});

const selectedMethod = computed(() =>
    props.methods.find((m) => m.id === Number(form.shipping_method_id))
);

const shipping = computed(() =>
    Number(selectedMethod.value?.effective_price ?? 0)
);
const total = computed(() => Number(props.subtotal ?? 0) + shipping.value);
const fmt = (n) => Number(n || 0).toFixed(2);

const save = () =>
    form.post(route("checkout.address.update", props.order.id), {
        preserveScroll: true,
    });
</script>

<template>
    <div class="max-w-3xl mx-auto p-6 space-y-6">
        <h1 class="text-2xl font-semibold">Adresse & Livraison</h1>

        <!-- Email invité -->
        <section v-if="guest" class="bg-white rounded-xl p-4 shadow">
            <label class="text-sm">Email</label>
            <input
                v-model="form.customer_email"
                type="email"
                class="w-full border rounded-lg p-2"
            />
            <p v-if="errors.customer_email" class="text-sm text-red-600 mt-1">
                {{ errors.customer_email }}
            </p>
        </section>

        <!-- Adresse -->
        <section class="bg-white rounded-xl p-4 shadow space-y-4">
            <h2 class="font-medium">Adresse de livraison</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input
                    v-model="form.full_name"
                    placeholder="Nom complet"
                    class="border p-2 rounded"
                />
                <input
                    v-model="form.phone_number"
                    placeholder="Téléphone"
                    class="border p-2 rounded"
                />

                <input
                    v-model="form.address_line_1"
                    placeholder="Adresse"
                    class="border p-2 rounded md:col-span-2"
                />
                <input
                    v-model="form.address_line_2"
                    placeholder="Complément"
                    class="border p-2 rounded md:col-span-2"
                />

                <input
                    v-model="form.postal_code"
                    placeholder="Code postal"
                    class="border p-2 rounded"
                />
                <input
                    v-model="form.city_name"
                    placeholder="Ville"
                    class="border p-2 rounded"
                />

                <select v-model="form.country_id" class="border p-2 rounded">
                    <option v-for="c in countries" :key="c.id" :value="c.id">
                        {{ c.name }}
                    </option>
                </select>
            </div>

            <div class="text-sm text-red-600 space-y-1">
                <p v-if="errors.full_name">{{ errors.full_name }}</p>
                <p v-if="errors.phone_number">{{ errors.phone_number }}</p>
                <p v-if="errors.address_line_1">{{ errors.address_line_1 }}</p>
                <p v-if="errors.postal_code">{{ errors.postal_code }}</p>
                <p v-if="errors.city_name">{{ errors.city_name }}</p>
                <p v-if="errors.country_id">{{ errors.country_id }}</p>
            </div>
        </section>

        <!-- Mode de livraison -->
        <section class="bg-white rounded-xl p-4 shadow space-y-3">
            <h2 class="font-medium">Mode de livraison</h2>
            <div class="space-y-2">
                <label
                    v-for="m in methods"
                    :key="m.id"
                    class="flex items-center gap-3 p-3 border rounded-lg"
                >
                    <input
                        type="radio"
                        v-model="form.shipping_method_id"
                        :value="m.id"
                    />
                    <div class="flex-1">
                        <div class="font-medium">{{ m.name }}</div>
                        <div class="text-xs text-gray-600">
                            {{ m.code }}
                            <span v-if="m.free_from" class="ml-2"
                                >• Gratuit dès
                                {{ Number(m.free_from).toFixed(2) }} €</span
                            >
                        </div>
                    </div>
                    <div class="font-medium">
                        {{ Number(m.effective_price).toFixed(2) }} €
                    </div>
                </label>
            </div>
            <p
                v-if="errors.shipping_method_id"
                class="text-sm text-red-600 mt-1"
            >
                {{ errors.shipping_method_id }}
            </p>
        </section>

        <!-- Récapitulatif (unique) -->
        <section
            v-if="items?.length"
            class="bg-white rounded-xl p-4 shadow space-y-3"
        >
            <h2 class="font-medium">Récapitulatif</h2>

            <div class="divide-y">
                <div
                    v-for="it in items"
                    :key="it.id"
                    class="flex items-center justify-between py-2"
                >
                    <div class="text-sm">
                        <div class="font-medium">{{ it.name }}</div>
                        <div class="text-gray-600">
                            x{{ it.qty }} · {{ fmt(it.unit) }} €
                        </div>
                    </div>
                    <div class="font-medium">{{ fmt(it.line) }} €</div>
                </div>
            </div>

            <div class="border-t pt-3 space-y-1 text-sm">
                <div class="flex justify-between">
                    <span>Sous-total</span
                    ><span class="font-medium">{{ fmt(subtotal) }} €</span>
                </div>
                <div class="flex justify-between">
                    <span>Livraison</span
                    ><span class="font-medium">{{ fmt(shipping) }} €</span>
                </div>
                <div class="flex justify-between text-base font-semibold pt-1">
                    <span>Total</span><span>{{ fmt(total) }} €</span>
                </div>
            </div>
        </section>

        <!-- Footer actions -->
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Frais de livraison :
                <span class="font-semibold">{{ fmt(shipping) }} €</span>
            </div>
            <button
                @click="save"
                :disabled="form.processing"
                class="px-4 py-2 rounded-lg bg-black text-white disabled:opacity-50"
            >
                Continuer
            </button>
        </div>
    </div>
</template>
