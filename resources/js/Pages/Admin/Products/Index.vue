<script setup>
import { Link, usePage, router } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import Pagination from "@/Components/Pagination.vue";
import { Dialog, DialogPanel, TransitionRoot } from "@headlessui/vue";
import {
    PencilSquareIcon,
    TrashIcon,
    PlusIcon,
    MagnifyingGlassIcon,
    XMarkIcon,
    ChevronDownIcon,
} from "@heroicons/vue/24/outline";

const props = defineProps({
    products: Object,
    categories: { type: Array, default: () => [] },
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
        { preserveScroll: true, preserveState: true, replace: true }
    );
});

// Modale de confirmation suppression
const confirmOpen = ref(false);
const toDelete = ref(null);
function askDelete(product) {
    toDelete.value = product;
    confirmOpen.value = true;
}
function confirmDelete() {
    if (!toDelete.value) return;
    router.delete(`/admin/products/${toDelete.value.id}`, {
        preserveScroll: true,
        onFinish: () => (confirmOpen.value = false),
    });
}

let t;
watch([search, category_id], () => {
    clearTimeout(t);
    t = setTimeout(() => {
        router.get(
            "/admin/products",
            { search: search.value, category_id: category_id.value },
            { preserveScroll: true, preserveState: true, replace: true }
        );
    }, 300); // 300ms
});
</script>

<template>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex items-center justify-between mb-4">
                <!-- Bouton retour -->
                <Link
                    :href="route('profile.show')"
                    class="inline-flex items-center gap-2 h-9 px-4 border border-gray-300 rounded-md bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 hover:border-gray-400 active:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 text-gray-500"
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
                    Retour au profil
                </Link>
            </div>

            <!-- Header -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Produits</h1>
                <Link
                    href="/admin/products/create"
                    class="inline-flex h-9 items-center justify-center rounded-md bg-indigo-600 px-4 text-sm font-medium text-white transition hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    <PlusIcon class="mr-2 h-4 w-4" />
                    Nouveau
                </Link>
            </div>

            <!-- Flash -->
            <div
                v-if="flash?.success"
                class="rounded border border-green-200 bg-green-50 px-4 py-3 text-green-700"
            >
                {{ flash.success }}
            </div>

            <!-- Filtres (sans bouton réinitialiser) -->
            <div
                class="rounded-lg bg-white p-4 shadow flex flex-col sm:flex-row sm:items-center gap-3"
            >
                <!-- Search -->
                <div class="relative w-full sm:max-w-xs">
                    <MagnifyingGlassIcon
                        class="pointer-events-none absolute left-2.5 top-2.5 h-5 w-5 text-gray-400"
                    />
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Rechercher par nom ou slug…"
                        class="w-full rounded-lg border pl-9 pr-9 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <button
                        v-if="search"
                        type="button"
                        @click="search = ''"
                        class="absolute right-2.5 top-2.5 inline-flex h-5 w-5 items-center justify-center rounded hover:bg-gray-100"
                        title="Effacer"
                    >
                        <XMarkIcon class="h-5 w-5 text-gray-400" />
                    </button>
                </div>

                <!-- Catégorie -->
                <div class="relative w-full sm:max-w-xs">
                    <select
                        v-model="category_id"
                        class="appearance-none w-full rounded-lg border pr-9 pl-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
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
                    <ChevronDownIcon
                        class="pointer-events-none absolute right-2.5 top-2.5 h-5 w-5 text-gray-400"
                    />
                </div>
            </div>

            <!-- Tableau responsive -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <!-- Version mobile (cartes) -->
                <div class="sm:hidden">
                    <div
                        v-for="p in products.data"
                        :key="p.id"
                        class="border-b border-gray-200 p-4"
                    >
                        <div class="flex items-start space-x-3">
                            <!-- Image -->
                            <img
                                v-if="p.image_url"
                                :src="p.image_url"
                                class="h-16 w-16 rounded object-cover ring-1 ring-gray-200 flex-shrink-0"
                                alt="Image produit"
                            />
                            <div
                                v-else
                                class="h-16 w-16 rounded bg-gray-100 flex items-center justify-center text-gray-400 text-xs flex-shrink-0"
                            >
                                —
                            </div>

                            <!-- Contenu -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h3
                                            class="font-medium text-gray-900 truncate"
                                        >
                                            {{ p.name }}
                                        </h3>
                                        <p
                                            class="text-sm text-gray-500 truncate"
                                        >
                                            {{ p.slug }}
                                        </p>
                                        <p
                                            class="text-sm font-medium text-gray-900 mt-1"
                                        >
                                            {{ Number(p.price).toFixed(2) }}€
                                        </p>
                                    </div>

                                    <!-- Actions mobile -->
                                    <div
                                        class="flex items-center space-x-2 ml-2"
                                    >
                                        <Link
                                            :href="`/admin/products/${p.id}/edit`"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50"
                                            title="Modifier"
                                        >
                                            <PencilSquareIcon class="h-4 w-4" />
                                        </Link>
                                        <button
                                            @click="askDelete(p)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-red-600 hover:bg-red-50"
                                            title="Supprimer"
                                        >
                                            <TrashIcon class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>

                                <!-- Badges mobile -->
                                <div
                                    class="flex items-center justify-between mt-2"
                                >
                                    <span
                                        :class="[
                                            'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium',
                                            p.stock > 0
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-red-100 text-red-700',
                                        ]"
                                    >
                                        {{
                                            p.stock > 0
                                                ? `Stock: ${p.stock}`
                                                : "Rupture"
                                        }}
                                    </span>
                                    <span class="text-xs text-gray-500">
                                        {{
                                            p.category?.name ?? "Sans catégorie"
                                        }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-if="products.data.length === 0"
                        class="p-8 text-center text-gray-500"
                    >
                        Aucun produit.
                    </div>
                </div>

                <!-- Version desktop (tableau) -->
                <div class="hidden sm:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left">
                            <tr>
                                <th class="px-4 py-3 font-medium text-gray-700">
                                    Image
                                </th>
                                <th class="px-4 py-3 font-medium text-gray-700">
                                    Nom
                                </th>
                                <th class="px-4 py-3 font-medium text-gray-700">
                                    Prix
                                </th>
                                <th class="px-4 py-3 font-medium text-gray-700">
                                    Stock
                                </th>
                                <th class="px-4 py-3 font-medium text-gray-700">
                                    Catégorie
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
                                v-for="p in products.data"
                                :key="p.id"
                                class="border-t last:border-b"
                            >
                                <td class="px-4 py-3">
                                    <img
                                        v-if="p.image_url"
                                        :src="p.image_url"
                                        class="h-12 w-12 rounded object-cover ring-1 ring-gray-200"
                                        alt="Image produit"
                                    />
                                    <div
                                        v-else
                                        class="h-12 w-12 rounded bg-gray-100 flex items-center justify-center text-gray-400 text-xs"
                                    >
                                        —
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900">
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
                                        :class="[
                                            'inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium',
                                            p.stock > 0
                                                ? 'bg-green-100 text-green-700'
                                                : 'bg-red-100 text-red-700',
                                        ]"
                                    >
                                        {{ p.stock > 0 ? p.stock : "Rupture" }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    {{ p.category?.name ?? "—" }}
                                </td>

                                <!-- Actions directes (icônes) -->
                                <td class="px-4 py-3">
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <Link
                                            :href="`/admin/products/${p.id}/edit`"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                            title="Modifier"
                                        >
                                            <PencilSquareIcon class="h-4 w-4" />
                                            <span class="sr-only"
                                                >Modifier</span
                                            >
                                        </Link>

                                        <button
                                            @click="askDelete(p)"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-md text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                                            title="Supprimer"
                                        >
                                            <TrashIcon class="h-4 w-4" />
                                            <span class="sr-only"
                                                >Supprimer</span
                                            >
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="products.data.length === 0">
                                <td
                                    colspan="6"
                                    class="px-4 py-10 text-center text-gray-500"
                                >
                                    Aucun produit.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="border-t p-4">
                    <Pagination :links="products.links" />
                </div>
            </div>
        </div>
    </div>

    <!-- Modale confirmation suppression -->
    <TransitionRoot :show="confirmOpen" as="template">
        <Dialog @close="confirmOpen = false" class="relative z-50">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black/30" aria-hidden="true" />

            <!-- Container centré -->
            <div class="fixed inset-0 flex items-center justify-center p-4">
                <DialogPanel
                    class="mx-auto max-w-md rounded-lg bg-white p-6 shadow-xl"
                >
                    <h3 class="text-lg font-semibold text-gray-900">
                        Supprimer le produit
                    </h3>
                    <p class="mt-2 text-sm text-gray-600">
                        Confirmer la suppression de «
                        {{ toDelete?.name }} » ?
                    </p>

                    <div class="mt-5 flex justify-end gap-2">
                        <button
                            class="inline-flex h-9 items-center justify-center rounded-md border border-gray-300 bg-white px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            @click="confirmOpen = false"
                        >
                            Annuler
                        </button>
                        <button
                            class="inline-flex h-9 items-center justify-center rounded-md bg-red-600 px-4 text-sm font-medium text-white hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                            @click="confirmDelete"
                        >
                            Supprimer
                        </button>
                    </div>
                </DialogPanel>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
