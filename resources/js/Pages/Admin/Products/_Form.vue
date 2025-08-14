<script setup>
import { useForm } from "@inertiajs/vue3";

const props = defineProps({
    product: { type: Object, default: null },
    action: { type: String, required: true },
    method: { type: String, default: "post" },
    categories: { type: Array, default: () => [] },
});

const form = useForm({
    name: props.product?.name ?? "",
    slug: props.product?.slug ?? "",
    description: props.product?.description ?? "",
    price: props.product?.price ?? 0,
    discount_type: props.product?.discount_type ?? null,
    discount_value: props.product?.discount_value ?? null,
    stock: props.product?.stock ?? 0,
    image: props.product?.image ?? "",
    image_large: props.product?.image_large ?? "",
    category_id: props.product?.category_id ?? null,
});
</script>

<template>
    <form
        :action="action"
        method="post"
        @submit.prevent="
            method === 'post' ? form.post(action) : form.put(action)
        "
        class="space-y-4 max-w-2xl"
    >
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm">Nom</label>
                <input
                    v-model="form.name"
                    class="w-full border rounded p-2"
                    required
                />
            </div>
            <div>
                <label class="block text-sm">Slug (auto si vide)</label>
                <input v-model="form.slug" class="w-full border rounded p-2" />
            </div>
            <div>
                <label class="block text-sm">Prix (€)</label>
                <input
                    v-model.number="form.price"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full border rounded p-2"
                    required
                />
            </div>
            <div>
                <label class="block text-sm">Stock</label>
                <input
                    v-model.number="form.stock"
                    type="number"
                    min="0"
                    class="w-full border rounded p-2"
                    required
                />
            </div>
            <div>
                <label class="block text-sm">Catégorie</label>
                <select
                    v-model="form.category_id"
                    class="w-full border rounded p-2"
                >
                    <option :value="null">—</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">
                        {{ c.name }}
                    </option>
                </select>
            </div>
            <div>
                <label class="block text-sm">Type de remise</label>
                <select
                    v-model="form.discount_type"
                    class="w-full border rounded p-2"
                >
                    <option :value="null">—</option>
                    <option value="fixed">Montant fixe</option>
                    <option value="percent">Pourcentage</option>
                </select>
            </div>
            <div>
                <label class="block text-sm">Valeur de remise</label>
                <input
                    v-model.number="form.discount_value"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full border rounded p-2"
                />
            </div>
            <div>
                <label class="block text-sm">Image (nom de fichier)</label>
                <input
                    v-model="form.image"
                    class="w-full border rounded p-2"
                    placeholder="ex: TFE-exemple-3.jpg"
                />
            </div>
            <div>
                <label class="block text-sm"
                    >Image large (nom de fichier)</label
                >
                <input
                    v-model="form.image_large"
                    class="w-full border rounded p-2"
                    placeholder="ex: TFE-exemple-3-xl.jpg"
                />
            </div>
        </div>

        <div>
            <label class="block text-sm">Description</label>
            <textarea
                v-model="form.description"
                rows="5"
                class="w-full border rounded p-2"
            ></textarea>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 rounded bg-black text-white">
                Enregistrer
            </button>
            <a href="/admin/products" class="px-4 py-2 rounded border"
                >Annuler</a
            >
        </div>
    </form>
</template>
