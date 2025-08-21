<script setup>
import { ref } from "vue";
import { Link, useForm, usePage } from "@inertiajs/vue3";
import Toast from "@/Components/Toast.vue";

const currentYear = ref(new Date().getFullYear());

// Accordéons pour mobile
const openSections = ref({
    atelier: false,
    aide: false,
    ressources: false,
});

const toggleSection = (key) => {
    openSections.value[key] = !openSections.value[key];
};

// Newsletter form - déplacé en dehors de toggleSection
const newsletterForm = useForm({
    email: "",
    website: "", // honeypot
});

const submitNewsletter = () => {
    newsletterForm.post(route("newsletter.store"), {
        preserveScroll: true,
    });
};
</script>

<template>
    <!-- Newsletter -->
    <section class="bg-beige py-12">
        <h2 class="text-center text-2xl font-bold mb-6">
            Abonnez-vous à notre newsletter
        </h2>

        <form
            @submit.prevent="submitNewsletter"
            class="max-w-xl mx-auto flex flex-col sm:flex-row items-center gap-4 px-6"
        >
            <input
                v-model="newsletterForm.email"
                name="email"
                type="email"
                required
                placeholder="Saisissez votre e-mail"
                class="w-full sm:flex-1 px-4 py-2 border border-gray-200 rounded shadow-sm outline-none focus:border-gray-400"
            />
            <!-- Honeypot: doit rester vide -->
            <input
                v-model="newsletterForm.website"
                name="website"
                type="text"
                tabindex="-1"
                autocomplete="off"
                class="hidden"
            />

            <button
                type="submit"
                :disabled="newsletterForm.processing"
                class="w-full sm:w-auto bg-bidibordeaux text-white px-6 py-2 rounded hover:bg-rose-800 transition"
            >
                Abonne-toi
            </button>
        </form>
    </section>

    <!-- Footer -->
    <footer
        aria-label="Pied de page de l'atelier Bidibulle"
        class="bg-white border-t px-6 md:px-10 grid grid-cols-1 md:grid-cols-[2fr_1fr_1fr_1fr_1fr] gap-8 pt-16 mb-8 text-sm text-gray-700"
    >
        <!-- Colonne 1 : logo + texte -->
        <section class="space-y-4">
            <img
                src="/TFE-Logo-Noir.png"
                alt="Logo"
                class="h-14 w-auto mx-auto md:mx-0 mb-6 md:mb-0"
            />
            <p class="max-w-md text-center md:text-left">
                Atelier de Bidibulle, votre destination pour des créations
                artisanales uniques. Chaque produit est fait avec soin pour
                transformer vos moments en souvenirs inoubliables.
            </p>
            <div class="flex justify-center md:justify-start gap-4 text-lg">
                <a href="#" aria-label="Twitter"
                    ><i class="fab fa-twitter"></i
                ></a>
                <a href="#" aria-label="Facebook"
                    ><i class="fab fa-facebook-f"></i
                ></a>
                <a href="#" aria-label="Instagram"
                    ><i class="fab fa-instagram"></i
                ></a>
            </div>
        </section>

        <!-- Accordéon mobile + colonnes normales sur md -->
        <section class="md:border-none border-y border-gray-200 py-4">
            <button
                @click="toggleSection('atelier')"
                class="flex justify-between w-full items-center font-bold uppercase md:hidden"
            >
                L'atelier
                <span class="inline-flex items-center">{{
                    openSections.atelier ? "-" : "+"
                }}</span>
            </button>
            <nav
                aria-labelledby="footer-nav-atelier"
                :class="{ hidden: !openSections.atelier }"
                class="md:block space-y-2"
            >
                <h4 class="hidden md:block font-bold uppercase mb-4 text-black">
                    L'atelier
                </h4>
                <ul class="space-y-2">
                    <li>
                        <a
                            href="#"
                            class="hover:border-b hover:border-black transition"
                            >Accueil</a
                        >
                    </li>
                    <li>
                        <a
                            href="#"
                            class="hover:border-b hover:border-black transition"
                            >À propos</a
                        >
                    </li>
                    <li>
                        <a
                            href="#"
                            class="hover:border-b hover:border-black transition"
                            >Produits</a
                        >
                    </li>
                    <li>
                        <a
                            href="#"
                            class="hover:border-b hover:border-black transition"
                            >Contact</a
                        >
                    </li>
                </ul>
            </nav>
        </section>

        <section class="md:border-none border-y border-gray-200 py-4">
            <button
                @click="toggleSection('aide')"
                class="flex justify-between w-full items-center font-bold uppercase md:hidden"
            >
                Aide
                <span class="inline-flex items-center">{{
                    openSections.aide ? "-" : "+"
                }}</span>
            </button>
            <nav
                aria-labelledby="footer-nav-aide"
                :class="{ hidden: !openSections.aide }"
                class="md:block space-y-2"
            >
                <h4 class="hidden md:block font-bold uppercase mb-4 text-black">
                    Aide
                </h4>
                <ul class="space-y-2">
                    <li>
                        <Link
                            :href="route('shipping.returns')"
                            class="hover:border-b hover:border-black transition"
                        >
                            Livraison et retours
                        </Link>
                    </li>
                    <li>
                        <a
                            href="#"
                            class="hover:border-b hover:border-black transition"
                            >Service client</a
                        >
                    </li>
                    <li>
                        <Link
                            :href="route('terms.conditions')"
                            class="hover:border-b hover:border-black transition"
                        >
                            Conditions générales de vente
                        </Link>
                    </li>
                    <li>
                        <Link
                            :href="route('privacy.policy')"
                            class="hover:border-b hover:border-black transition"
                        >
                            Politique de confidentialité
                        </Link>
                    </li>
                    <li>
                        <Link
                            :href="route('legal.notice')"
                            class="hover:border-b hover:border-black transition"
                        >
                            Mentions légales
                        </Link>
                    </li>
                </ul>
            </nav>
        </section>

        <section class="md:border-none border-y border-gray-200 py-4">
            <button
                @click="toggleSection('ressources')"
                class="flex justify-between w-full items-center font-bold uppercase md:hidden"
            >
                Ressources
                <span class="inline-flex items-center">{{
                    openSections.ressources ? "-" : "+"
                }}</span>
            </button>
            <nav
                aria-labelledby="footer-nav-ressources"
                :class="{ hidden: !openSections.ressources }"
                class="md:block space-y-2"
            >
                <h4 class="hidden md:block font-bold uppercase mb-4 text-black">
                    Ressources
                </h4>
                <ul class="space-y-2">
                    <li>
                        <a
                            href="#"
                            class="hover:border-b hover:border-black transition"
                            >Idées cadeaux</a
                        >
                    </li>
                    <li>
                        <a
                            href="#"
                            class="hover:border-b hover:border-black transition"
                            >Newsletter</a
                        >
                    </li>
                </ul>
            </nav>
        </section>

        <!-- Paiement sécurisé -->
        <section class="text-center md:text-left py-4 md:py-4">
            <h4 class="font-bold uppercase mb-2 text-black">
                Paiement sécurisé
            </h4>
            <p class="mb-2">Moyens de paiement</p>
            <ul class="flex flex-wrap justify-center md:justify-start gap-2">
                <li>
                    <img
                        src="/icons/visa-classic-svgrepo-com.svg"
                        alt="Visa"
                        class="h-8 w-auto"
                    />
                </li>
                <li>
                    <img
                        src="/icons/mastercard-full-svgrepo-com.svg"
                        alt="Mastercard"
                        class="h-8 w-auto"
                    />
                </li>
                <li>
                    <img
                        src="/icons/paypal-svgrepo-com.svg"
                        alt="PayPal"
                        class="h-8 w-auto"
                    />
                </li>
                <li>
                    <img
                        src="/icons/bancontact-svgrepo-com.svg"
                        alt="Bancontact"
                        class="h-8 w-auto"
                    />
                </li>
                <li>
                    <img
                        src="/icons/stripe-svgrepo-com.svg"
                        alt="Stripe"
                        class="h-8 w-auto"
                    />
                </li>
                <li>
                    <img
                        src="/icons/bancontact-payconiq-svgrepo-com.svg"
                        alt="Payconiq"
                        class="h-8 w-auto"
                    />
                </li>
            </ul>
        </section>

        <!-- Copyright -->
        <div
            class="md:col-span-5 border-t md:pt-8 pt-8 text-center text-sm text-gray-500"
        >
            © {{ currentYear }} Atelier De Bidibulle – Tous droits réservés
        </div>
    </footer>
</template>
