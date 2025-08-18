<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, watch, computed } from "vue";
import InputField from "@/Components/Form/InputField.vue";
import {
    Listbox,
    ListboxButton,
    ListboxOptions,
    ListboxOption,
} from "@headlessui/vue";
import { ChevronUpDownIcon } from "@heroicons/vue/24/outline";

const props = defineProps({
    product: { type: Object, default: null },
    action: { type: String, required: true },
    method: { type: String, default: "post" }, // 'post' | 'put'
    categories: { type: Array, default: () => [] },
});

const isCreate = computed(() => props.method === "post");

// ⚠️ en création, price/stock = '' (pas 0) pour laisser Laravel dire "requis"
const form = useForm({
    name: props.product?.name ?? "",
    slug: props.product?.slug ?? "",
    description: props.product?.description ?? "",
    price: props.product ? props.product.price : "",
    stock: props.product ? props.product.stock : "",
    image: null,
    category_id: props.product?.category_id ?? null,
});

// Preview image
const imagePreview = ref(props.product?.image_url ?? null);

// Catégorie (Listbox)
const selectedCategory = ref(
    props.categories.find((c) => c.id === form.category_id) ?? null
);
watch(selectedCategory, (val) => {
    form.category_id = val?.id ?? null;
});

// Slug auto (sans écraser un slug saisi à la main)
const slugify = (s) =>
    s
        ?.toString()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, "-")
        .replace(/(^-|-$)+/g, "") || "";

watch(
    () => form.name,
    (n, oldN) => {
        const prevDerived = slugify(oldN ?? "");
        if (!form.slug || form.slug === prevDerived) {
            form.slug = slugify(n);
        }
    }
);

// Upload image
const handleImageUpload = (event) => {
    const file = event.target.files?.[0];
    if (file) {
        // 🔥 SÉCURITÉ : Validation côté client
        const allowedTypes = [
            "image/jpeg",
            "image/png",
            "image/gif",
            "image/webp",
        ];
        if (!allowedTypes.includes(file.type)) {
            form.setError(
                "image",
                "Type de fichier non autorisé. Utilisez JPG, PNG, GIF ou WEBP."
            );
            return;
        }

        if (file.size > 2 * 1024 * 1024) {
            // 2MB
            form.setError("image", "Le fichier ne doit pas dépasser 2 Mo.");
            return;
        }

        form.clearErrors("image");
        form.image = file;
        const reader = new FileReader();
        reader.onload = (e) => (imagePreview.value = e.target?.result);
        reader.readAsDataURL(file);
    }
};

// Coercition nombres
const toNumber = (v) => {
    if (v === "" || v === null || typeof v === "number") return v;
    const n = Number(String(v).replace(",", "."));
    return isNaN(n) ? v : n;
};
const toInt = (v) => {
    if (v === "" || v === null || typeof v === "number") return v;
    const n = parseInt(String(v), 10);
    return isNaN(n) ? v : n;
};

// Helper erreurs
const getError = (f) => form.errors?.[f] ?? null;

// Submit
function submit() {
    // petites gardes front pour retour immédiat (la vraie validation reste côté Laravel)
    if (!form.category_id) {
        form.setError("category_id", "La catégorie est requise.");
        return;
    }
    if (!form.description || !String(form.description).trim()) {
        form.setError("description", "La description est requise.");
        return;
    }
    if (isCreate.value && !form.image) {
        form.setError("image", "L’image est requise à la création.");
        return;
    }

    const resetTransform = () => form.transform((d) => d);
    const opts = {
        preserveScroll: true,
        forceFormData: true,
        onFinish: resetTransform,
    };

    if (isCreate.value) {
        form.transform((data) => ({
            ...data,
            price: toNumber(data.price),
            stock: toInt(data.stock),
        }));
        form.post(props.action, opts);
    } else {
        // POST + spoof PUT (multipart PUT est capricieux en PHP)
        form.transform((data) => ({
            ...data,
            price: toNumber(data.price),
            stock: toInt(data.stock),
            _method: "PUT",
        }));
        form.post(props.action, opts);
    }
}
</script>

<template>
    <div
        class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 py-8 px-4"
    >
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6"
            >
                <h1 class="text-2xl font-bold text-slate-900">
                    {{
                        method === "post"
                            ? "Créer un produit"
                            : "Modifier le produit"
                    }}
                </h1>
                <p class="text-slate-600 mt-1">
                    {{
                        method === "post"
                            ? "Ajoutez un nouveau produit à votre catalogue"
                            : "Modifiez les informations du produit"
                    }}
                </p>
            </div>

            <!-- Form -->
            <div
                class="bg-white rounded-xl shadow-sm border border-slate-200 p-6"
            >
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Grid -->
                    <div class="grid gap-6 md:grid-cols-2">
                        <InputField
                            v-model="form.name"
                            label="Nom du produit"
                            :error="form.errors.name"
                            placeholder="Ex: Mug personnalisé"
                            :required="true"
                        />

                        <InputField
                            v-model="form.slug"
                            label="Slug"
                            placeholder="Généré automatiquement"
                            :error="form.errors.slug"
                        />

                        <InputField
                            v-model="form.price"
                            label="Prix (€)"
                            type="number"
                            step="0.01"
                            :min="0"
                            :error="form.errors.price"
                            placeholder="0.00"
                            :required="true"
                        />

                        <InputField
                            v-model="form.stock"
                            label="Stock"
                            type="number"
                            :min="0"
                            :error="form.errors.stock"
                            placeholder="0"
                            :required="true"
                        />

                        <!-- Catégorie -->
                        <div class="space-y-2">
                            <label class="text-sm font-medium text-slate-700">
                                Catégorie <span class="text-red-500">*</span>
                            </label>
                            <Listbox v-model="selectedCategory">
                                <div class="relative">
                                    <ListboxButton
                                        class="relative w-full cursor-pointer bg-white border rounded-lg py-2 pl-3 pr-10 text-left text-sm focus:outline-none"
                                        :class="[
                                            getError('category_id')
                                                ? 'border-red-500 ring-2 ring-red-100'
                                                : 'border-slate-300 focus:ring-2 focus:ring-indigo-500',
                                        ]"
                                    >
                                        <span
                                            class="block truncate text-slate-900"
                                        >
                                            {{
                                                selectedCategory?.name ??
                                                "Sélectionner une catégorie"
                                            }}
                                        </span>
                                        <span
                                            class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3"
                                        >
                                            <ChevronUpDownIcon
                                                class="h-4 w-4 text-slate-400"
                                            />
                                        </span>
                                    </ListboxButton>

                                    <ListboxOptions
                                        class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-lg bg-white border border-slate-200 shadow-lg text-sm"
                                    >
                                        <ListboxOption
                                            :value="null"
                                            class="cursor-pointer px-3 py-2 hover:bg-indigo-50"
                                        >
                                            — Aucune catégorie
                                        </ListboxOption>
                                        <ListboxOption
                                            v-for="c in categories"
                                            :key="c.id"
                                            :value="c"
                                            class="cursor-pointer px-3 py-2 hover:bg-indigo-50"
                                        >
                                            {{ c.name }}
                                        </ListboxOption>
                                    </ListboxOptions>
                                </div>
                            </Listbox>
                            <p
                                v-if="getError('category_id')"
                                class="text-xs text-red-600"
                            >
                                {{ getError("category_id") }}
                            </p>
                        </div>

                        <!-- Image (compact) -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-sm font-medium text-slate-700">
                                Image
                                <span v-if="isCreate" class="text-red-500"
                                    >*</span
                                >
                            </label>

                            <div class="flex items-center gap-3">
                                <input
                                    id="image-upload"
                                    type="file"
                                    accept="image/*"
                                    class="hidden"
                                    @change="handleImageUpload"
                                />
                                <label
                                    for="image-upload"
                                    class="inline-flex items-center justify-center h-9 px-3 rounded-md border border-slate-300 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50 cursor-pointer"
                                    :class="
                                        getError('image')
                                            ? 'border-red-500'
                                            : ''
                                    "
                                >
                                    Parcourir…
                                </label>

                                <!-- Nom du fichier / état -->
                                <span
                                    class="text-sm text-slate-600 truncate max-w-[240px]"
                                >
                                    {{
                                        form.image?.name
                                            ? form.image.name
                                            : imagePreview
                                            ? "Image sélectionnée"
                                            : !isCreate && product?.image
                                            ? "Image actuelle"
                                            : "Aucun fichier sélectionné"
                                    }}
                                </span>

                                <!-- Preview locale si un nouveau fichier a été choisi -->
                                <img
                                    v-if="imagePreview"
                                    :src="imagePreview"
                                    alt="Preview"
                                    class="h-10 w-10 object-cover rounded border"
                                />

                                <!-- Preview de l'image existante (édition, aucun nouveau fichier) -->
                                <img
                                    v-else-if="!isCreate && product?.image"
                                    :src="product.image_url"
                                    alt="Image actuelle"
                                    class="h-10 w-10 object-cover rounded border"
                                />
                            </div>

                            <p
                                v-if="!isCreate"
                                class="mt-1 text-xs text-slate-500"
                            >
                                Laissez vide pour conserver l’image actuelle.
                            </p>
                            <p
                                v-if="getError('image')"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ getError("image") }}
                            </p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700">
                            Description <span class="text-red-500">*</span>
                        </label>
                        <textarea
                            v-model="form.description"
                            rows="4"
                            :aria-invalid="!!getError('description')"
                            class="w-full px-3 py-2 bg-white border rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 resize-none"
                            :class="
                                getError('description')
                                    ? 'border-red-500 focus:ring-red-500'
                                    : 'border-slate-300 focus:ring-indigo-500'
                            "
                            placeholder="Décrivez votre produit..."
                        ></textarea>
                        <p
                            v-if="getError('description')"
                            class="text-xs text-red-600"
                        >
                            {{ getError("description") }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div
                        class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between pt-6 border-t border-slate-200 gap-3"
                    >
                        <a
                            href="/admin/products"
                            class="inline-flex items-center justify-center h-10 px-4 py-2 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 hover:border-slate-400 active:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-500 transition-all duration-150"
                        >
                            ← Retour
                        </a>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="inline-flex items-center justify-center h-10 px-6 py-2 bg-indigo-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-indigo-700 hover:shadow-md active:bg-indigo-800 active:scale-95 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg
                                v-if="form.processing"
                                class="animate-spin -ml-1 mr-3 h-4 w-4 text-white"
                                fill="none"
                                viewBox="0 0 24 24"
                            >
                                <circle
                                    class="opacity-25"
                                    cx="12"
                                    cy="12"
                                    r="10"
                                    stroke="currentColor"
                                    stroke-width="4"
                                />
                                <path
                                    class="opacity-75"
                                    fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                />
                            </svg>
                            {{
                                form.processing
                                    ? "Enregistrement..."
                                    : "Enregistrer"
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
