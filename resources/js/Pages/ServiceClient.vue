<script setup>
import { Head, Link } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
</script>

<template>
    <Head title="Service client" />

    <PublicLayout>
        <div class="mx-auto max-w-[1440px] py-10 px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 mb-6">
                Service client
            </h1>

            <!-- Contact -->
            <section class="mb-10 grid gap-4 md:grid-cols-2">
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-2">Nous contacter</h2>
                    <p class="text-gray-600 mb-3">
                        Une question ? Écrivez-nous, nous répondons sous 24–48h
                        (du lundi au vendredi).
                    </p>
                    <p class="text-gray-800">
                        📧
                        <a
                            href="mailto:contact@atelier-de-bidibule.be"
                            class="underline hover:no-underline"
                            >contact@atelier-de-bidibule.be</a
                        >
                    </p>
                </div>

                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h2 class="text-xl font-semibold mb-2">
                        Livraison & retours
                    </h2>
                    <p class="text-gray-600">
                        Détails des délais, frais et procédures de retour.
                    </p>
                    <div class="mt-4 flex flex-wrap gap-3">
                        <Link href="/livraison-retours" class="hover:underline">
                            Livraison et retours
                        </Link>
                        <span class="text-gray-300">·</span>
                        <Link
                            href="/conditions-generales"
                            class="hover:underline"
                        >
                            Conditions générales de vente
                        </Link>
                    </div>
                </div>
            </section>

            <!-- FAQ -->
            <section>
                <h2 class="text-xl font-semibold mb-4">Questions fréquentes</h2>

                <div class="space-y-3">
                    <details class="group bg-white rounded-lg shadow-sm p-5">
                        <summary
                            class="cursor-pointer list-none flex items-center justify-between"
                        >
                            <span class="font-medium"
                                >Quels sont les délais de livraison ?</span
                            >
                            <span class="transition group-open:rotate-180"
                                >⌄</span
                            >
                        </summary>
                        <p class="mt-3 text-gray-600">
                            Belgique : 3–5 jours ouvrés. International : 5–7
                            jours. Les produits personnalisés peuvent nécessiter
                            un délai de préparation supplémentaire.
                        </p>
                    </details>

                    <details class="group bg-white rounded-lg shadow-sm p-5">
                        <summary
                            class="cursor-pointer list-none flex items-center justify-between"
                        >
                            <span class="font-medium"
                                >Comment suivre ma commande ?</span
                            >
                            <span class="transition group-open:rotate-180"
                                >⌄</span
                            >
                        </summary>
                        <p class="mt-3 text-gray-600">
                            Un e-mail de confirmation avec le lien de suivi vous
                            est envoyé dès l’expédition. Vérifiez vos spams si
                            besoin.
                        </p>
                    </details>

                    <details class="group bg-white rounded-lg shadow-sm p-5">
                        <summary
                            class="cursor-pointer list-none flex items-center justify-between"
                        >
                            <span class="font-medium"
                                >Puis-je retourner un article ?</span
                            >
                            <span class="transition group-open:rotate-180"
                                >⌄</span
                            >
                        </summary>
                        <p class="mt-3 text-gray-600">
                            Oui, sous 14 jours après réception (hors articles
                            personnalisés sauf défaut). Consultez la page
                            <Link
                                href="/livraison-retours"
                                class="hover:underline"
                                >Livraison et retours</Link
                            >
                            pour la procédure.
                        </p>
                    </details>

                    <details class="group bg-white rounded-lg shadow-sm p-5">
                        <summary
                            class="cursor-pointer list-none flex items-center justify-between"
                        >
                            <span class="font-medium"
                                >Quels sont les moyens de paiement ?</span
                            >
                            <span class="transition group-open:rotate-180"
                                >⌄</span
                            >
                        </summary>
                        <p class="mt-3 text-gray-600">
                            Cartes (Visa, Mastercard), Bancontact, PayPal, et
                            paiement sécurisé via Stripe.
                        </p>
                    </details>
                </div>
            </section>

            <!-- Liens utiles -->
            <section class="mt-10">
                <h2 class="text-xl font-semibold mb-3">Liens utiles</h2>
                <ul class="list-disc pl-5 space-y-1 text-gray-700">
                    <li>
                        <Link href="/contact" class="hover:underline"
                            >Contact</Link
                        >
                    </li>
                </ul>
            </section>
        </div></PublicLayout
    >
</template>
