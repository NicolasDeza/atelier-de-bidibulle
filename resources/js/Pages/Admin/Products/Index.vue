<script setup>
import { Link, useForm, usePage, router } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue"; // Jetstream

const props = defineProps({
    products: Object, // paginator avec ->with('category')
    categories: { type: Array, default: () => [] }, // si tu veux filtrer
    filters: {
        type: Object,
        default: () => ({ search: "", category_id: null }),
    },
});

const { flash } = usePage().props;

const search = ref(props.filters.search || "");
const category_id = ref(props.filters.category_id || "");

watch([search, category_id], () => {
    router.get(
        "/admin/products",
        { search: search.value, category_id: category_id.value },
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        }
    );
});
</script>

<template>
    <AppLayout title="Gestion des produits">
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-semibold">Produits</h1>
                    <Link
                        href="/admin/products/create"
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700"
                    >
                        + Nouveau
                    </Link>
                </div>

                <div
                    v-if="flash?.success"
                    class="p-3 rounded bg-green-50 text-green-700 border border-green-200"
                >
                    {{ flash.success }}
                </div>

                <!-- Filtres -->
                <div
                    class="bg-white shadow sm:rounded-lg p-4 flex flex-col sm:flex-row gap-3"
                >
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Rechercher par nom ou slug…"
                        class="w-full sm:max-w-xs border rounded-lg px-3 py-2"
                    />
                    <select
                        v-model="category_id"
                        class="w-full sm:max-w-xs border rounded-lg px-3 py-2"
                    >
                        <option value="">Toutes catégories</option>
                        <option
                            v-for="c in categories"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.name }}
                        </option>
                    </select>
                </div>

                <!-- Tableau -->
                <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 text-left">
                                <tr>
                                    <th class="px-4 py-3">Image</th>
                                    <th class="px-4 py-3">Nom</th>
                                    <th class="px-4 py-3">Prix</th>
                                    <th class="px-4 py-3">Stock</th>
                                    <th class="px-4 py-3">Catégorie</th>
                                    <th class="px-4 py-3 w-40">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="p in products.data"
                                    :key="p.id"
                                    class="border-t"
                                >
                                    <td class="px-4 py-3">
                                        <img
                                            v-if="p.image"
                                            :src="`/images/produits/${p.image}`"
                                            class="w-12 h-12 object-cover rounded"
                                        />
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">
                                            {{ p.name }}
                                        </div>
                                        <div class="text-gray-500">
                                            {{ p.slug }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ Number(p.price).toFixed(2) }} €
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            :class="
                                                p.stock > 0
                                                    ? 'text-green-600'
                                                    : 'text-red-600'
                                            "
                                            >{{ p.stock }}</span
                                        >
                                    </td>
                                    <td class="px-4 py-3">
                                        {{ p.category?.name ?? "—" }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-3">
                                            <Link
                                                :href="`/admin/products/${p.id}/edit`"
                                                class="text-blue-600 hover:underline"
                                                >Modifier</Link
                                            >
                                            <Link
                                                as="button"
                                                method="delete"
                                                :href="`/admin/products/${p.id}`"
                                                class="text-red-600 hover:underline"
                                            >
                                                Supprimer
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="products.data.length === 0">
                                    <td
                                        colspan="6"
                                        class="px-4 py-8 text-center text-gray-500"
                                    >
                                        Aucun produit.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="p-4 border-t flex flex-wrap gap-2">
                        <Link
                            v-for="l in products.links"
                            :key="l.url || l.label"
                            :href="l.url || '#'"
                            v-html="l.label"
                            :class="[
                                'px-3 py-1 rounded border',
                                {
                                    'bg-black text-white': l.active,
                                    'opacity-50 pointer-events-none': !l.url,
                                },
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
