<script setup>
import { Head, Link } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
</script>

<template>
    <Head title="Livraison & Retours">
        <meta
            name="description"
            content="Informations sur les délais de livraison et conditions de retour pour l'Atelier de Bidibulle. Livraison 2-4 jours, retour sous 14 jours."
        />
        <meta name="robots" content="index, follow" />
    </Head>

    <PublicLayout>
        <div class="max-w-[1440px] mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold mb-2">Livraison & Retours</h1>
            <p class="text-sm text-gray-600 mb-8">
                Informations pratiques pour vos commandes
            </p>

            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">Délais de livraison</h2>
                <div class="space-y-3">
                    <p>
                        Les commandes sont préparées sous
                        <strong>24 à 48h ouvrées</strong>
                        puis expédiées via Bpost. Le délai moyen de livraison
                        est de
                        <strong>2 à 4 jours ouvrés</strong>.
                    </p>
                    <p>
                        <strong>Zone de livraison :</strong> Belgique et Union
                        européenne (selon disponibilité).
                    </p>
                </div>
                <div class="bg-blue-50 p-4 rounded mt-4">
                    <p class="text-sm">
                        <strong>Suivi :</strong> Un numéro de suivi vous sera
                        communiqué par email dès l'expédition de votre commande.
                    </p>
                </div>
            </section>

            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">
                    Retours & droit de rétractation
                </h2>
                <div class="space-y-3">
                    <p>
                        Conformément à la législation belge et européenne, vous
                        disposez de
                        <strong>14 jours calendrier</strong> après réception
                        pour exercer votre droit de rétractation.
                    </p>

                    <div class="bg-amber-50 p-4 rounded">
                        <h3 class="font-semibold mb-2">
                            Exception importante :
                        </h3>
                        <p class="text-sm">
                            Les produits personnalisés ou fabriqués sur mesure
                            ne peuvent faire l'objet d'une rétractation.
                        </p>
                    </div>

                    <h3 class="font-semibold mt-6 mb-3">
                        Procédure de retour :
                    </h3>
                    <ol class="list-decimal ml-6 space-y-2">
                        <li>
                            <strong>Contactez-nous :</strong>
                            <a
                                href="mailto:contact@atelierdebidibulle.be"
                                class="underline text-dark-600"
                            >
                                contact@atelierdebidibulle.be
                            </a>
                        </li>
                        <li>
                            <strong>Renvoyez le produit</strong> complet, dans
                            son emballage d'origine et en parfait état.
                        </li>
                        <li>
                            <strong>Remboursement</strong> sous 14 jours après
                            réception et vérification du produit.
                        </li>
                    </ol>

                    <div class="bg-gray-100 p-4 rounded mt-4">
                        <p class="text-sm">
                            <strong>Frais de retour :</strong> À votre charge,
                            sauf en cas de défaut du produit ou d'erreur de
                            notre part.
                        </p>
                    </div>
                </div>
            </section>

            <section>
                <h2 class="text-xl font-semibold mb-2">
                    Informations complémentaires
                </h2>
                <p class="text-sm text-gray-600">
                    Pour plus de détails, consultez nos
                    <Link
                        :href="route('terms.conditions')"
                        class="underline text-dark-600"
                    >
                        Conditions générales de vente </Link
                    >.
                </p>
            </section>
        </div>
    </PublicLayout>
</template>
