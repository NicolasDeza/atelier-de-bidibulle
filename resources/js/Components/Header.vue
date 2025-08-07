<script setup>
import { ref } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";

const page = usePage();
const mobileMenuOpen = ref(false);
const mobileCategoriesOpen = ref(false);

const logout = () => {
    router.post(route("logout"));
};
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
                <img
                    src="/TFE-Logo-Noir.png"
                    alt="Logo"
                    class="h-14 w-14 object-contain"
                />
                <span class="text-2xl font-bold">Atelier De Bidibulle</span>
            </div>

            <!-- Recherche (desktop) -->
            <div class="hidden md:block w-full max-w-[18rem] mx-4 relative">
                <input
                    type="text"
                    placeholder="Rechercher"
                    class="w-full pl-4 pr-10 py-2 text-sm border border-gray-300 rounded-full shadow-sm outline-none focus:border-gray-400"
                />
                <span
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-300"
                >
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
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
                <button
                    class="hidden md:inline text-black hover:text-gray-600 transition"
                >
                    <i class="fa-solid fa-shopping-cart text-lg"></i>
                </button>

                <!-- Icône user -->
                <Link
                    v-if="page.props.auth.user"
                    href="/dashboard"
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
                    type="text"
                    placeholder="Rechercher"
                    class="w-full pl-4 pr-10 py-2 text-sm border border-gray-300 rounded-full shadow-sm outline-none"
                />
                <span
                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-300"
                >
                    <i class="fa-solid fa-magnifying-glass"></i>
                </span>
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
                                    <a
                                        href="#"
                                        class="block px-4 py-2 hover:bg-gray-100"
                                        >Cadeaux de naissance</a
                                    >
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        class="block px-4 py-2 hover:bg-gray-100"
                                        >Anniversaire</a
                                    >
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        class="block px-4 py-2 hover:bg-gray-100"
                                        >Personnalisable</a
                                    >
                                </li>
                                <li>
                                    <a
                                        href="#"
                                        class="block px-4 py-2 hover:bg-gray-100"
                                        >Décoration Maison</a
                                    >
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <a
                            href="#"
                            class="pb-1 border-b-2 border-transparent hover:border-black transition"
                            >Contact</a
                        >
                    </li>
                    <li>
                        <a
                            href="#"
                            class="pb-1 border-b-2 border-transparent hover:border-black transition"
                            >À propos</a
                        >
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
                <li v-if="page.props.auth.user">
                    <Link
                        :href="route('wishlist.index')"
                        class="flex items-center gap-2"
                    >
                        <i class="fa-solid fa-heart text-black"></i>
                        <span>Mes Favoris</span>
                    </Link>
                </li>
                <li>
                    <button class="flex items-center gap-2">
                        <i class="fa-solid fa-shopping-cart text-black"></i>
                        <span>Panier</span>
                    </button>
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
                                <a href="#" class="block py-1"
                                    >Cadeaux de naissance</a
                                >
                            </li>
                            <li>
                                <a href="#" class="block py-1">Anniversaire</a>
                            </li>
                            <li>
                                <a href="#" class="block py-1"
                                    >Personnalisable</a
                                >
                            </li>
                            <li>
                                <a href="#" class="block py-1"
                                    >Décoration Maison</a
                                >
                            </li>
                        </ul>
                    </div>
                </li>
                <li><a href="#" class="block">Contact</a></li>
                <li><a href="#" class="block">À propos</a></li>
            </ul>

            <!-- Auth mobile -->
            <div class="flex gap-4 mt-6">
                <template v-if="page.props.auth.user">
                    <Link
                        href="/dashboard"
                        class="text-sm px-4 py-2 rounded border border-black"
                        >Mon compte</Link
                    >
                    <button
                        @click="logout"
                        class="border border-black px-4 py-2 text-sm rounded"
                    >
                        Déconnexion
                    </button>
                </template>
                <template v-else>
                    <Link
                        :href="route('register')"
                        class="bg-bidibordeaux text-white px-4 py-2 text-sm rounded"
                        >S'inscrire</Link
                    >
                    <Link
                        :href="route('login')"
                        class="border border-black px-4 py-2 text-sm rounded"
                        >Connexion</Link
                    >
                </template>
            </div>
        </div>
    </header>
</template>
