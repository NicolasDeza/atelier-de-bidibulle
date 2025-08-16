<script setup>
import { Link, useForm } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import {
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
    TransitionRoot,
} from "@headlessui/vue";

const props = defineProps({
    product: { type: Object, default: null },
    action: { type: String, required: true },
    method: { type: String, default: "post" }, // 'post' | 'put'
    categories: { type: Array, default: () => [] }, // [{id, name}]
});

// --- formulaire
const form = useForm({
    name: props.product?.name ?? "",
    slug: props.product?.slug ?? "",
    description: props.product?.description ?? "",
    price: props.product?.price ?? 0,
    discount_type: props.product?.discount_type ?? null, // 'fixed' | 'percent' | null
    discount_value: props.product?.discount_value ?? null,
    stock: props.product?.stock ?? 0,
    image: props.product?.image ?? "",
    image_large: props.product?.image_large ?? "",
    category_id: props.product?.category_id ?? null,
});

// --- Catégorie (Listbox)
const selectedCategory = ref(
    props.categories.find((c) => c.id === form.category_id) ?? null
);
watch(selectedCategory, (val) => {
    form.category_id = val?.id ?? null;
});

// --- Type de remise (Listbox)
const discountOptions = [
    { value: null, label: "—" },
    { value: "fixed", label: "Montant fixe (€)" },
    { value: "percent", label: "Pourcentage (%)" },
];
const selectedDiscount = ref(
    discountOptions.find((o) => o.value === form.discount_type) ??
        discountOptions[0]
);
watch(selectedDiscount, (opt) => {
    form.discount_type = opt.value;
    if (opt.value === null) form.discount_value = null;
});

// --- Auto-slug quand on tape le nom (si le slug est vide ou dérivé)
const slugify = (s) =>
    s
        .toString()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/(^-|-$)+/g, "");

watch(
    () => form.name,
    (n) => {
        if (
            !props.product?.slug ||
            form.slug === "" ||
            form.slug === slugify(props.product?.name ?? "")
        ) {
            form.slug = slugify(n);
        }
    }
);

// --- Helpers
const canEditDiscountValue = computed(
    () => selectedDiscount.value?.value !== null
);
const imagePreview = computed(() =>
    form.image
        ? `/images/produits/${form.image}`
        : props.product?.image
        ? `/images/produits/${props.product.image}`
        : null
);

// --- submit
function submit() {
    if (props.method === "post") {
        form.post(props.action, { preserveScroll: true });
    } else {
        form.put(props.action, { preserveScroll: true });
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="mx-auto w-full max-w-3xl space-y-6">
        <!-- Titre -->
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold">Informations du produit</h2>
            <div class="text-sm text-gray-500">
                <span class="text-red-500">*</span> champs obligatoires
            </div>
        </div>

        <!-- Grid -->
        <div class="grid gap-4 sm:grid-cols-2">
            <!-- Nom -->
            <div>
                <label class="mb-1 block text-sm font-medium"
                    >Nom <span class="text-red-500">*</span></label
                >
                <input
                    v-model="form.name"
                    class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    required
                />
                <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">
                    {{ form.errors.name }}
                </p>
            </div>

            <!-- Slug -->
            <div>
                <label class="mb-1 block text-sm font-medium"
                    >Slug (auto si vide)</label
                >
                <input
                    v-model="form.slug"
                    class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
                <p v-if="form.errors.slug" class="mt-1 text-xs text-red-600">
                    {{ form.errors.slug }}
                </p>
            </div>

            <!-- Prix -->
            <div>
                <label class="mb-1 block text-sm font-medium"
                    >Prix (€) <span class="text-red-500">*</span></label
                >
                <input
                    v-model.number="form.price"
                    type="number"
                    step="0.01"
                    min="0"
                    class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    required
                />
                <p v-if="form.errors.price" class="mt-1 text-xs text-red-600">
                    {{ form.errors.price }}
                </p>
            </div>

            <!-- Stock -->
            <div>
                <label class="mb-1 block text-sm font-medium"
                    >Stock <span class="text-red-500">*</span></label
                >
                <input
                    v-model.number="form.stock"
                    type="number"
                    min="0"
                    class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    required
                />
                <p v-if="form.errors.stock" class="mt-1 text-xs text-red-600">
                    {{ form.errors.stock }}
                </p>
            </div>

            <!-- Catégorie (Listbox) -->
            <div class="sm:col-span-1">
                <label class="mb-1 block text-sm font-medium">Catégorie</label>
                <Listbox v-model="selectedCategory">
                    <div class="relative">
                        <ListboxButton
                            class="relative w-full cursor-default rounded-lg border bg-white py-2 pl-3 pr-10 text-left text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <span class="block truncate">
                                {{ selectedCategory?.name ?? "—" }}
                            </span>
                            <span
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"
                            >
                                ▾
                            </span>
                        </ListboxButton>

                        <TransitionRoot
                            leave="transition ease-in duration-100"
                            leave-from="opacity-100"
                            leave-to="opacity-0"
                        >
                            <ListboxOptions
                                class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md border bg-white py-1 text-sm shadow-lg focus:outline-none"
                            >
                                <ListboxOption
                                    :value="null"
                                    v-slot="{ active, selected }"
                                    class="cursor-default select-none px-3 py-2"
                                >
                                    <div
                                        :class="[
                                            active ? 'bg-gray-100' : '',
                                            'rounded',
                                        ]"
                                    >
                                        — Aucune
                                    </div>
                                </ListboxOption>

                                <ListboxOption
                                    v-for="c in categories"
                                    :key="c.id"
                                    :value="c"
                                    v-slot="{ active }"
                                    class="cursor-default select-none px-3 py-2"
                                >
                                    <div
                                        :class="[
                                            active ? 'bg-gray-100' : '',
                                            'rounded',
                                        ]"
                                    >
                                        {{ c.name }}
                                    </div>
                                </ListboxOption>
                            </ListboxOptions>
                        </TransitionRoot>
                    </div>
                </Listbox>
                <p
                    v-if="form.errors.category_id"
                    class="mt-1 text-xs text-red-600"
                >
                    {{ form.errors.category_id }}
                </p>
            </div>

            <!-- Type de remise (Listbox) -->
            <div class="sm:col-span-1">
                <label class="mb-1 block text-sm font-medium"
                    >Type de remise</label
                >
                <Listbox v-model="selectedDiscount">
                    <div class="relative">
                        <ListboxButton
                            class="relative w-full cursor-default rounded-lg border bg-white py-2 pl-3 pr-10 text-left text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        >
                            <span class="block truncate">{{
                                selectedDiscount.label
                            }}</span>
                            <span
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"
                            >
                                ▾
                            </span>
                        </ListboxButton>

                        <TransitionRoot
                            leave="transition ease-in duration-100"
                            leave-from="opacity-100"
                            leave-to="opacity-0"
                        >
                            <ListboxOptions
                                class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md border bg-white py-1 text-sm shadow-lg focus:outline-none"
                            >
                                <ListboxOption
                                    v-for="opt in discountOptions"
                                    :key="String(opt.value)"
                                    :value="opt"
                                    v-slot="{ active }"
                                    class="cursor-default select-none px-3 py-2"
                                >
                                    <div
                                        :class="[
                                            active ? 'bg-gray-100' : '',
                                            'rounded',
                                        ]"
                                    >
                                        {{ opt.label }}
                                    </div>
                                </ListboxOption>
                            </ListboxOptions>
                        </TransitionRoot>
                    </div>
                </Listbox>
                <p
                    v-if="form.errors.discount_type"
                    class="mt-1 text-xs text-red-600"
                >
                    {{ form.errors.discount_type }}
                </p>
            </div>

            <!-- Valeur de remise -->
            <div class="sm:col-span-1">
                <label class="mb-1 block text-sm font-medium"
                    >Valeur de remise</label
                >
                <input
                    v-model.number="form.discount_value"
                    type="number"
                    step="0.01"
                    min="0"
                    :disabled="!canEditDiscountValue"
                    class="w-full rounded-lg border px-3 py-2 text-sm disabled:cursor-not-allowed disabled:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    :placeholder="
                        selectedDiscount.value === 'percent'
                            ? 'ex: 10 (pour 10%)'
                            : 'ex: 5.00'
                    "
                />
                <p
                    v-if="form.errors.discount_value"
                    class="mt-1 text-xs text-red-600"
                >
                    {{ form.errors.discount_value }}
                </p>
            </div>

            <!-- Image (nom de fichier) -->
            <div class="sm:col-span-1">
                <label class="mb-1 block text-sm font-medium"
                    >Image (nom de fichier)</label
                >
                <input
                    v-model="form.image"
                    class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="ex: TFE-exemple-3.jpg"
                />
                <p v-if="form.errors.image" class="mt-1 text-xs text-red-600">
                    {{ form.errors.image }}
                </p>
                <div v-if="imagePreview" class="mt-2">
                    <img
                        :src="imagePreview"
                        alt=""
                        class="h-24 w-24 rounded object-cover ring-1 ring-gray-200"
                    />
                </div>
            </div>

            <!-- Image large (optionnel) -->
            <div class="sm:col-span-1">
                <label class="mb-1 block text-sm font-medium"
                    >Image large (nom de fichier)</label
                >
                <input
                    v-model="form.image_large"
                    class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    placeholder="ex: TFE-exemple-3-xl.jpg"
                />
                <p
                    v-if="form.errors.image_large"
                    class="mt-1 text-xs text-red-600"
                >
                    {{ form.errors.image_large }}
                </p>
            </div>

            <!-- Description (col-span) -->
            <div class="sm:col-span-2">
                <label class="mb-1 block text-sm font-medium"
                    >Description</label
                >
                <textarea
                    v-model="form.description"
                    rows="5"
                    class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                ></textarea>
                <p
                    v-if="form.errors.description"
                    class="mt-1 text-xs text-red-600"
                >
                    {{ form.errors.description }}
                </p>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2">
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex h-10 items-center justify-center rounded-md bg-indigo-600 px-5 text-sm font-medium text-white transition hover:bg-indigo-500 disabled:opacity-50"
            >
                {{ form.processing ? "Enregistrement…" : "Enregistrer" }}
            </button>
            <Link
                href="/admin/products"
                class="inline-flex h-10 items-center justify-center rounded-md border border-gray-300 bg-white px-5 text-sm font-medium text-gray-700 hover:bg-gray-50"
            >
                Annuler
            </Link>
        </div>
    </form>
</template>
