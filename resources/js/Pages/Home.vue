<script setup>
import { Link, Head } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";

const props = defineProps({
    featuredProducts: Array,
});
</script>

<template>
    <Head title="Accueil">
        <meta
            name="description"
            content="Atelier de Bidibule — créations personnalisées faites main (naissance, cadeaux, déco). Paiement sécurisé, livraison rapide en Belgique/UE."
        />
    </Head>

    <PublicLayout>
        <!-- Bannière pleine largeur -->
        <section
            aria-label="Bannière d'accueil de l'atelier Bidibulle"
            class="relative bg-[url('/images/TFE-Banniere-Craft.jpg')] 0 bg-cover bg-no-repeat bg-center h-[320px] md:h-[600px] flex items-center justify-center"
        >
            <div
                class="bg-white/70 backdrop-blur-sm px-6 py-4 md:py-8 rounded-lg text-center max-w-md w-full mx-8"
            >
                <h1
                    class="text-2xl md:text-4xl font-extrabold font-serif leading-tight"
                >
                    Atelier<br />De<br />Bidibulle
                </h1>
                <p class="mt-3 text-sm md:text-lg text-black">
                    Transforme chaque instant en souvenir
                </p>
                <Link
                    :href="route('products.index')"
                    class="mt-4 md:mt-8 inline-block bg-bidibordeaux hover:bg-rose-800 text-white font-bold px-4 py-2 md:px-6 md:py-4 text-sm rounded"
                >
                    DÉCOUVREZ
                </Link>
            </div>
        </section>

        <!-- Section 2 localisation -->
        <section
            class="py-12 md:py-20 px-2 md:px-0 text-center flex flex-col items-center gap-6"
        >
            <h2 class="uppercase font-bold text-xl md:text-2xl">
                Fais main, avec amour, pour vous
            </h2>
            <p>
                Une collection pensée pour apporter chaleur et personnalité à
                votre quotidien.
            </p>
            <Link
                href="/contact"
                class="w-fit inline-flex items-center border border-black rounded-full px-4 py-2 mt-6 text-sm hover:bg-black hover:text-white transition"
            >
                <i class="fas fa-map-marker-alt mr-2"></i>
                Notre localisation
            </Link>
        </section>

        <!-- Section 4 produits en vedette -->
        <section class="pb-20 white">
            <div
                class="bg-bidibordeaux text-white py-5 uppercase font-bold text-center mb-8"
            >
                DÉCOUVREZ NOS COUPS DE CŒUR
            </div>

            <div class="max-w-[1440px] mx-auto px-4">
                <div
                    class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8"
                >
                    <Link
                        v-for="product in props.featuredProducts"
                        :key="product.id"
                        :href="route('products.show', product.slug)"
                        class="text-center flex flex-col h-full rounded-lg hover:shadow-lg transition"
                    >
                        <img
                            :src="product.image_url"
                            :alt="product.name"
                            class="w-full max-w-[400px] md:max-w-[500px] h-[150px] md:h-[200px] object-cover rounded-lg shadow mx-auto"
                        />
                        <h3 class="mt-4 font-semibold">{{ product.name }}</h3>
                        <p class="text-sm text-gray-600 min-h-[70px] mt-2">
                            {{ product.description }}
                        </p>
                        <p class="mt-auto mb-2 font-semibold text-black">
                            {{ product.price }} €
                            <span
                                v-if="product.old_price"
                                class="text-sm line-through text-red-600"
                            >
                                {{ product.old_price }} €
                            </span>
                        </p>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Étapes explicatives -->
        <section class="bg-beige py-12 md:py-20 px-4 md:px-8">
            <div class="max-w-[1440px] mx-auto text-center">
                <h2 class="text-2xl md:text-3xl font-bold mb-14">
                    Comment ça marche ?
                </h2>
                <div class="grid md:grid-cols-3 gap-8">
                    <div
                        class="bg-white p-6 rounded-xl shadow flex flex-col items-center text-center"
                    >
                        <div class="text-4xl mb-4">🛍️</div>
                        <h3 class="text-xl font-semibold mb-2">
                            1. Choisissez
                        </h3>
                        <p class="text-gray-600">
                            Parcourez notre catalogue de créations artisanales
                            et trouvez le produit parfait pour l’occasion.
                        </p>
                    </div>

                    <div
                        class="bg-white p-6 rounded-xl shadow flex flex-col items-center text-center"
                    >
                        <div class="text-4xl mb-4">🎨</div>
                        <h3 class="text-xl font-semibold mb-2">
                            2. Personnalisez
                        </h3>
                        <p class="text-gray-600">
                            Ajoutez votre touche personnelle : prénoms,
                            couleurs, messages… chaque détail compte.
                        </p>
                    </div>

                    <div
                        class="bg-white p-6 rounded-xl shadow flex flex-col items-center text-center"
                    >
                        <div class="text-4xl mb-4">📦</div>
                        <h3 class="text-xl font-semibold mb-2">3. Recevez</h3>
                        <p class="text-gray-600">
                            Votre commande est préparée avec soin et livrée
                            rapidement à votre porte.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Avis Google -->
        <section class="bg-white py-12 md:py-20 text-center">
            <div class="max-w-[1440px] mx-auto px-4 md:px-8">
                <h2 class="text-2xl md:text-3xl font-bold mb-4">
                    Ce que nos clients disent de nous
                </h2>

                <div class="flex items-center justify-center mb-3">
                    <img
                        src="/icons/google-svgrepo-com.svg"
                        alt="Google"
                        class="w-10 h-10 mr-2"
                    />
                    <span class="text-2xl font-semibold">4.8 / 5</span>
                    <span class="ml-2 text-yellow-500 text-2xl">★★★★★</span>
                </div>

                <p class="text-sm text-gray-500 mb-8 italic">
                    *Avis présentés à titre d’exemple, en attendant vos
                    témoignages authentiques !*
                </p>

                <div class="grid md:grid-cols-3 gap-8 mt-16">
                    <div
                        class="bg-gray-50 p-6 rounded-lg shadow flex flex-col h-full justify-between"
                    >
                        <div>
                            <p class="italic">
                                « Magnifique travail, personnalisation parfaite,
                                livraison rapide ! »
                            </p>
                        </div>
                        <div class="mt-4">
                            <div class="text-yellow-500 text-lg">★★★★★</div>
                            <p class="mt-2 font-semibold">— Marie D.</p>
                        </div>
                    </div>

                    <div
                        class="bg-gray-50 p-6 rounded-lg shadow flex flex-col h-full justify-between"
                    >
                        <div>
                            <p class="italic">
                                « Un cadeau unique qui a fait très plaisir.
                                Merci pour votre professionnalisme ! »
                            </p>
                        </div>
                        <div class="mt-4">
                            <div class="text-yellow-500 text-lg">★★★★★</div>
                            <p class="mt-2 font-semibold">— Julien L.</p>
                        </div>
                    </div>

                    <div
                        class="bg-gray-50 p-6 rounded-lg shadow flex flex-col h-full justify-between"
                    >
                        <div>
                            <p class="italic">
                                « Je recommande à 100% : fait main, beaux et de
                                qualité. »
                            </p>
                        </div>
                        <div class="mt-4">
                            <div class="text-yellow-500 text-lg">★★★★★</div>
                            <p class="mt-2 font-semibold">— Sarah P.</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8">
                    <a
                        href="https://www.google.com/search?q=atelier+bidibulle"
                        target="_blank"
                        class="text-gray-400 underline"
                    >
                        Voir tous nos avis Google
                    </a>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
