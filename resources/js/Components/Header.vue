<script setup>
import { ref, watch, nextTick } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import axios from "axios";
import { useNavigation } from "@/Composables/useNavigation";

const page = usePage();
const mobileMenuOpen = ref(false);
const mobileCategoriesOpen = ref(false);
const searchQuery = ref("");
const searchQueryMobile = ref("");
const suggestions = ref([]);
const suggestionsMobile = ref([]);
const showSuggestions = ref(false);
const showSuggestionsMobile = ref(false);
const searchTimeout = ref(null);

const logout = () => {
    router.post(route("logout"));
};

const fetchSuggestions = async (query, isMobile = false) => {
    if (query.length < 2) {
        if (isMobile) {
            suggestionsMobile.value = [];
            showSuggestionsMobile.value = false;
        } else {
            suggestions.value = [];
            showSuggestions.value = false;
        }
        return;
    }

    try {
        const response = await axios.get("/search/suggestions", {
            params: { q: query },
        });

        if (isMobile) {
            suggestionsMobile.value = response.data;
            showSuggestionsMobile.value = true; // Toujours afficher le dropdown
        } else {
            suggestions.value = response.data;
            showSuggestions.value = true; // Toujours afficher le dropdown
        }
    } catch (error) {
        console.error("Erreur lors de la recherche:", error);
    }
};

const debouncedSearch = (query, isMobile = false) => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }

    searchTimeout.value = window.setTimeout(() => {
        fetchSuggestions(query, isMobile);
    }, 300);
};

const delayedHideSuggestions = () => {
    window.setTimeout(() => {
        hideSuggestions();
    }, 200);
};

watch(searchQuery, (newQuery) => {
    debouncedSearch(newQuery, false);
});

watch(searchQueryMobile, (newQuery) => {
    debouncedSearch(newQuery, true);
});

const handleSearch = (query) => {
    hideSuggestions();
    if (query.trim()) {
        router.get(route("products.index"), { search: query.trim() });
    } else {
        router.get(route("products.index"));
    }
};

const handleSearchDesktop = () => {
    handleSearch(searchQuery.value);
};

const handleSearchMobile = () => {
    handleSearch(searchQueryMobile.value);
    mobileMenuOpen.value = false;
};

const handleKeydown = (event, isMobile = false) => {
    if (event.key === "Enter") {
        event.preventDefault();
        if (isMobile) {
            handleSearchMobile();
        } else {
            handleSearchDesktop();
        }
    } else if (event.key === "Escape") {
        hideSuggestions();
    }
};

const selectSuggestion = (item, isMobile = false) => {
    if (item.type === "category") {
        router.visit(route("products.index", { category: item.slug }));
    } else {
        router.visit(route("products.show", item.slug));
    }
    hideSuggestions();
    if (isMobile) {
        mobileMenuOpen.value = false;
    }
};

const hideSuggestions = () => {
    showSuggestions.value = false;
    showSuggestionsMobile.value = false;
};

const handleFocus = (isMobile = false) => {
    const query = isMobile ? searchQueryMobile.value : searchQuery.value;
    if (query.length >= 2) {
        if (isMobile) {
            showSuggestionsMobile.value = suggestionsMobile.value.length > 0;
        } else {
            showSuggestions.value = suggestions.value.length > 0;
        }
    }
};

const { goToAllProducts } = useNavigation();
</script>

<template>
    <header class="sticky top-0 z-50 bg-white">
        <!-- Bandeau promo -->
        <div class="bg-black text-sm py-1">
            <p class="text-white text-center">
                Livraison gratuite à partir de 50€ d'achat !
            </p>
        </div>

        <!-- Ligne 1 : Logo, recherche, icônes ou burger -->
        <div class="flex items-center justify-between px-10 py-4 border-b">
            <!-- Logo -->
            <div class="flex items-center gap-6">
                <Link :href="route('home')" class="flex items-center gap-6">
                    <img
                        src="/TFE-Logo-Noir.png"
                        alt="Logo Atelier De Bidibulle"
                        class="h-14 w-14 object-contain"
                    />
                    <span class="text-2xl font-bold">Atelier De Bidibulle</span>
                </Link>
            </div>

            <!-- Recherche (desktop) -->
            <div class="hidden md:block w-full max-w-[18rem] mx-4 relative">
                <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Rechercher un produit..."
                    @keydown="handleKeydown($event, false)"
                    @focus="handleFocus(false)"
                    @blur="delayedHideSuggestions"
                    class="w-full pl-4 pr-10 py-2 text-sm border border-gray-300 rounded-full shadow-sm outline-none focus:border-gray-400"
                />
                <button
                    @click="handleSearchDesktop"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>

                <!-- Suggestions dropdown -->
                <div
                    v-if="showSuggestions"
                    class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50 max-h-80 overflow-y-auto"
                >
                    <!-- Résultats de recherche -->
                    <div v-if="suggestions.length > 0">
                        <div
                            v-for="item in suggestions"
                            :key="`${item.type}-${item.id}`"
                            @click="selectSuggestion(item, false)"
                            class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                        >
                            <!-- Affichage pour les catégories -->
                            <div
                                v-if="item.type === 'category'"
                                class="flex items-center w-full"
                            >
                                <div
                                    class="w-12 h-12 bg-bidibordeaux rounded flex items-center justify-center mr-3"
                                >
                                    <i
                                        class="fa-solid fa-folder text-white"
                                    ></i>
                                </div>
                                <div class="flex-1">
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ item.name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        Catégorie •
                                        {{ item.products_count }} produit(s)
                                    </p>
                                </div>
                            </div>

                            <!-- Affichage pour les produits -->
                            <template v-else>
                                <img
                                    :src="item.image_url"
                                    :alt="item.name"
                                    class="w-12 h-12 object-cover rounded mr-3"
                                />
                                <div class="flex-1">
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ item.name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ item.category }}
                                    </p>
                                    <p
                                        class="text-sm font-semibold text-bidibordeaux"
                                    >
                                        {{ item.price }}€
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Aucun résultat trouvé -->
                    <div
                        v-else-if="searchQuery.length >= 2"
                        class="p-4 text-center"
                    >
                        <p class="text-gray-500 text-sm mb-3">
                            Aucun résultat trouvé
                        </p>
                        <button
                            type="button"
                            @click="goToAllProducts($event)"
                            class="bg-bidibordeaux text-white px-4 py-2 rounded-md text-sm hover:bg-rose-800 active:bg-rose-900 transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-bidibordeaux/40"
                        >
                            Voir tous les produits
                        </button>
                    </div>
                </div>
            </div>

            <!-- Icônes & burger -->
            <div class="flex items-center space-x-4">
                <!-- Icône favoris -->
                <Link
                    v-if="page.props.auth.user"
                    :href="route('wishlist.index')"
                    class="hidden md:inline text-black hover:text-gray-600 transition"
                >
                    <i class="fa-solid fa-heart text-lg"></i>
                </Link>

                <!-- Icône panier -->
                <Link
                    :href="route('cart.index')"
                    class="hidden md:inline text-black hover:text-gray-600 transition"
                >
                    <i class="fa-solid fa-shopping-cart text-lg"></i>
                </Link>

                <!-- Icône user -->
                <Link
                    v-if="page.props.auth.user"
                    :href="route('profile.show')"
                    class="hidden md:inline text-black hover:text-gray-600 transition"
                >
                    <i class="fa-solid fa-user text-lg"></i>
                </Link>
                <Link
                    v-else
                    :href="route('login')"
                    class="hidden md:inline text-black hover:text-gray-600 transition"
                >
                    <i class="fa-solid fa-user text-lg"></i>
                </Link>

                <!-- Burger mobile -->
                <button
                    class="md:hidden"
                    @click="mobileMenuOpen = !mobileMenuOpen"
                >
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Recherche mobile -->
        <div class="block md:hidden px-8 pt-2 pb-4 border-b">
            <div class="relative">
                <input
                    v-model="searchQueryMobile"
                    type="text"
                    placeholder="Rechercher un produit..."
                    @keydown="handleKeydown($event, true)"
                    @focus="handleFocus(true)"
                    @blur="delayedHideSuggestions"
                    class="w-full pl-4 pr-10 py-2 text-sm border border-gray-300 rounded-full shadow-sm outline-none"
                />
                <button
                    @click="handleSearchMobile"
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>

                <!-- Suggestions dropdown mobile -->
                <div
                    v-if="showSuggestionsMobile"
                    class="absolute top-full left-0 right-0 mt-1 bg-white border border-gray-300 rounded-lg shadow-lg z-50 max-h-60 overflow-y-auto"
                >
                    <!-- Résultats de recherche mobile -->
                    <div v-if="suggestionsMobile.length > 0">
                        <div
                            v-for="item in suggestionsMobile"
                            :key="`${item.type}-${item.id}`"
                            @click="selectSuggestion(item, true)"
                            class="flex items-center p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0"
                        >
                            <!-- Affichage pour les catégories -->
                            <div
                                v-if="item.type === 'category'"
                                class="flex items-center w-full"
                            >
                                <div
                                    class="w-10 h-10 bg-bidibordeaux rounded flex items-center justify-center mr-3"
                                >
                                    <i
                                        class="fa-solid fa-folder text-white text-sm"
                                    ></i>
                                </div>
                                <div class="flex-1">
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ item.name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ item.products_count }} produit(s)
                                    </p>
                                </div>
                            </div>

                            <!-- Affichage pour les produits -->
                            <template v-else>
                                <img
                                    :src="item.image_url"
                                    :alt="item.name"
                                    class="w-10 h-10 object-cover rounded mr-3"
                                />
                                <div class="flex-1">
                                    <p
                                        class="text-sm font-medium text-gray-900"
                                    >
                                        {{ item.name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ item.category }}
                                    </p>
                                    <p
                                        class="text-sm font-semibold text-bidibordeaux"
                                    >
                                        {{ item.price }}€
                                    </p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Aucun résultat trouvé mobile -->
                    <div
                        v-else-if="searchQueryMobile.length >= 2"
                        class="p-4 text-center"
                    >
                        <p class="text-gray-500 text-sm mb-3">
                            Aucun résultat trouvé
                        </p>
                        <button
                            @click="goToAllProducts"
                            class="bg-bidibordeaux text-white px-4 py-2 rounded-md text-sm hover:bg-opacity-90 transition-colors"
                        >
                            Voir tous les produits
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Menu principal desktop -->
        <nav class="bg-white px-10 py-4 hidden md:block z-50">
            <div class="flex items-center justify-between">
                <ul class="flex space-x-8 font-medium text-base">
                    <li>
                        <Link
                            :href="route('home')"
                            class="pb-1 border-b-2 border-transparent hover:border-black transition"
                        >
                            Accueil
                        </Link>
                    </li>
                    <li class="relative group">
                        <Link
                            :href="route('products.index')"
                            class="pb-1 border-b-2 border-transparent hover:border-black transition inline-flex items-center gap-1"
                        >
                            Produits
                            <i class="fa-solid fa-chevron-down text-sm"></i>
                        </Link>
                        <!-- Sous-catégories -->
                        <div class="absolute left-0 top-full pt-2">
                            <ul
                                class="hidden group-hover:flex flex-col bg-white shadow-lg z-10 min-w-[200px] border border-gray-300 rounded-md"
                            >
                                <li>
                                    <Link
                                        :href="
                                            route('products.index', {
                                                category: 'cadeaux-naissance',
                                            })
                                        "
                                        class="block px-4 py-2 hover:bg-gray-100"
                                        >Cadeaux de naissance</Link
                                    >
                                </li>
                                <li>
                                    <Link
                                        :href="
                                            route('products.index', {
                                                category: 'anniversaire',
                                            })
                                        "
                                        class="block px-4 py-2 hover:bg-gray-100"
                                        >Anniversaire</Link
                                    >
                                </li>
                                <li>
                                    <Link
                                        :href="
                                            route('products.index', {
                                                category: 'personnalisable',
                                            })
                                        "
                                        class="block px-4 py-2 hover:bg-gray-100"
                                        >Personnalisable</Link
                                    >
                                </li>
                                <li>
                                    <Link
                                        :href="
                                            route('products.index', {
                                                category: 'decoration-maison',
                                            })
                                        "
                                        class="block px-4 py-2 hover:bg-gray-100"
                                        >Décoration Maison</Link
                                    >
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <Link
                            :href="route('contact')"
                            class="pb-1 border-b-2 border-transparent hover:border-black transition"
                        >
                            Contact
                        </Link>
                    </li>
                    <li>
                        <Link
                            :href="route('about')"
                            class="pb-1 border-b-2 border-transparent hover:border-black transition"
                        >
                            À propos
                        </Link>
                    </li>
                </ul>

                <!-- Auth boutons -->
                <div class="flex space-x-2" v-if="page.props.auth.user">
                    <button
                        @click="logout"
                        class="border border-black text-sm px-4 py-2 rounded hover:bg-gray-100 transition"
                    >
                        Déconnexion
                    </button>
                </div>
                <div class="flex space-x-2" v-else>
                    <Link
                        :href="route('register')"
                        class="bg-bidibordeaux text-white text-sm px-4 py-2 rounded hover:bg-rose-800 transition"
                    >
                        S'inscrire
                    </Link>
                    <Link
                        :href="route('login')"
                        class="border border-black text-sm px-4 py-2 rounded hover:bg-gray-100 transition"
                    >
                        Connexion
                    </Link>
                </div>
            </div>
        </nav>

        <!-- Menu mobile -->
        <div
            v-if="mobileMenuOpen"
            class="fixed inset-x-0 top-[32px] bottom-0 bg-white z-50 px-6 py-6 flex flex-col gap-6 shadow-lg"
        >
            <div class="flex justify-between items-start">
                <div class="flex items-center gap-6">
                    <img
                        src="/TFE-Logo-Noir.png"
                        alt="Logo"
                        class="h-14 w-14 object-contain"
                    />
                    <span class="text-[18px] font-bold"
                        >Atelier De Bidibulle</span
                    >
                </div>
                <button @click="mobileMenuOpen = false">
                    <i class="fa-solid fa-xmark text-2xl"></i>
                </button>
            </div>

            <!-- Liens mobile -->
            <ul class="space-y-3 text-sm">
                <li>
                    <Link :href="route('home')" class="block">Accueil</Link>
                </li>
                <li>
                    <div>
                        <button
                            class="flex items-center justify-between w-full"
                            @click="
                                mobileCategoriesOpen = !mobileCategoriesOpen
                            "
                        >
                            <span>Produits</span>
                            <i
                                :class="[
                                    'fa-solid fa-chevron-down transition-transform',
                                    { 'rotate-180': mobileCategoriesOpen },
                                ]"
                            ></i>
                        </button>
                        <ul
                            v-if="mobileCategoriesOpen"
                            class="pl-4 mt-2 space-y-2"
                        >
                            <li>
                                <Link
                                    :href="route('products.index')"
                                    class="block py-1 font-semibold text-bidibordeaux"
                                    >Tous les produits</Link
                                >
                            </li>
                            <li>
                                <Link
                                    :href="
                                        route('products.index', {
                                            category: 'cadeaux-naissance',
                                        })
                                    "
                                    class="block py-1"
                                    >Cadeaux de naissance</Link
                                >
                            </li>
                            <li>
                                <Link
                                    :href="
                                        route('products.index', {
                                            category: 'anniversaire',
                                        })
                                    "
                                    class="block py-1"
                                    >Anniversaire</Link
                                >
                            </li>
                            <li>
                                <Link
                                    :href="
                                        route('products.index', {
                                            category: 'personnalisable',
                                        })
                                    "
                                    class="block py-1"
                                    >Personnalisable</Link
                                >
                            </li>
                            <li>
                                <Link
                                    :href="
                                        route('products.index', {
                                            category: 'decoration-maison',
                                        })
                                    "
                                    class="block py-1"
                                    >Décoration Maison</Link
                                >
                            </li>
                        </ul>
                    </div>
                </li>
                <li>
                    <Link :href="route('contact')" class="block">Contact</Link>
                </li>
                <li>
                    <Link :href="route('about')" class="block">À propos</Link>
                </li>

                <!-- Icônes côte à côte -->
                <li class="flex items-center gap-6 py-2">
                    <!-- Icône Panier -->
                    <Link :href="route('cart.index')" class="flex items-center">
                        <i
                            class="fa-solid fa-shopping-cart text-black text-2xl"
                        ></i>
                    </Link>

                    <!-- Icône Favoris (si connecté) -->
                    <Link
                        v-if="page.props.auth.user"
                        :href="route('wishlist.index')"
                        class="flex items-center gap-2"
                    >
                        <i class="fa-solid fa-heart text-black text-2xl"></i>
                        <span>Mes Favoris</span>
                    </Link>

                    <!-- Icône Profil -->
                    <Link
                        v-if="page.props.auth.user"
                        :href="route('profile.show')"
                        class="flex items-center"
                    >
                        <i class="fa-solid fa-user text-black text-2xl"></i>
                    </Link>
                    <Link
                        v-else
                        :href="route('login')"
                        class="flex items-center"
                    >
                        <i class="fa-solid fa-user text-black text-2xl"></i>
                    </Link>
                </li>
            </ul>

            <!-- Auth mobile -->
            <div class="flex gap-4 mt-6">
                <template v-if="page.props.auth.user">
                    <button
                        @click="logout"
                        class="flex-1 border border-black px-4 py-2 text-sm rounded text-center"
                    >
                        Déconnexion
                    </button>
                </template>
                <template v-else>
                    <Link
                        :href="route('register')"
                        class="flex-1 bg-bidibordeaux text-white px-4 py-2 text-sm rounded text-center"
                        >S'inscrire</Link
                    >
                    <Link
                        :href="route('login')"
                        class="flex-1 border border-black px-4 py-2 text-sm rounded text-center"
                        >Connexion</Link
                    >
                </template>
            </div>
        </div>
    </header>
</template>
