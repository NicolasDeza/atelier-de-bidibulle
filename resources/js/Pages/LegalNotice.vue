<script setup>
import { Head, Link } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
</script>

<template>
    <Head title="Mentions légales">
        <meta
            name="description"
            content="Mentions légales de L’Atelier de Bidibule : éditeur, coordonnées, numéro d’entreprise (BCE), TVA, hébergeur et responsabilité."
        />
        <!-- En dev/TFE : ok. En prod, supprime si tu veux l’indexation -->
        <meta name="robots" content="noindex, follow" />
    </Head>

    <PublicLayout>
        <div class="max-w-[900px] py-12 px-10">
            <h1 class="text-3xl font-bold mb-2">Mentions légales</h1>
            <p class="text-sm text-gray-600 mb-8">
                Informations légales obligatoires (Belgique / UE)
            </p>

            <!-- ÉDITEUR -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">Éditeur du site</h2>
                <div class="bg-gray-50 p-4 rounded">
                    <ul class="space-y-2">
                        <li>
                            <strong>Dénomination&nbsp;:</strong> L’Atelier de
                            Bidibule
                        </li>
                        <li>
                            <strong>Forme juridique&nbsp;:</strong> [Entreprise
                            individuelle / SRL / …]
                        </li>
                        <li>
                            <strong>Numéro d’entreprise (BCE)&nbsp;:</strong>
                            [BE0xxx.xxx.xxx]
                        </li>
                        <li>
                            <strong>TVA intracommunautaire&nbsp;:</strong>
                            [BE0xxx.xxx.xxx]
                        </li>
                        <li>
                            <strong>Adresse du siège&nbsp;:</strong> [Rue, n°,
                            CP, Ville, Belgique]
                        </li>
                        <li>
                            <strong>Email&nbsp;:</strong>
                            contact@atelier-bidibule.com
                        </li>
                        <li><strong>Téléphone&nbsp;:</strong> [numéro]</li>
                        <li>
                            <strong
                                >Responsable de la publication&nbsp;:</strong
                            >
                            [Nom, fonction]
                        </li>
                    </ul>
                </div>
            </section>

            <!-- HÉBERGEMENT -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">Hébergement</h2>
                <div class="space-y-2">
                    <p>
                        <strong>Hébergeur&nbsp;:</strong> [Nom de l’hébergeur]
                    </p>
                    <p>
                        <strong>Adresse&nbsp;:</strong> [Adresse complète de
                        l’hébergeur]
                    </p>
                    <p>
                        <strong>Site / Téléphone&nbsp;:</strong> [URL / numéro]
                    </p>
                </div>
            </section>

            <!-- LIENS UTILES -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">
                    Documents contractuels
                </h2>
                <ul class="list-disc ml-6">
                    <li>
                        <Link
                            :href="route('terms.conditions')"
                            class="underline"
                            >Conditions générales de vente (CGV)</Link
                        >
                    </li>
                    <li>
                        <Link :href="route('privacy.policy')" class="underline"
                            >Politique de confidentialité (RGPD)</Link
                        >
                    </li>
                </ul>
            </section>

            <!-- PROPRIÉTÉ INTELLECTUELLE -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">
                    Propriété intellectuelle
                </h2>
                <div class="space-y-3">
                    <p>
                        L’ensemble des contenus du site (textes, visuels, logos,
                        créations) est protégé par le droit d’auteur et la
                        législation applicable. Toute reproduction non autorisée
                        est interdite.
                    </p>
                </div>
            </section>

            <!-- RESPONSABILITÉ -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">Responsabilité</h2>
                <div class="space-y-3">
                    <p>
                        L’Atelier de Bidibule met tout en œuvre pour fournir une
                        information fiable et à jour, sans garantie
                        d’exhaustivité. Le site peut contenir des liens externes
                        dont le contenu n’engage pas la responsabilité de
                        l’éditeur.
                    </p>
                </div>
            </section>

            <!-- DROIT APPLICABLE -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">Droit applicable</h2>
                <p>
                    Le présent site est soumis au <strong>droit belge</strong>.
                    En cas de litige, les juridictions belges compétentes seront
                    saisies, sous réserve des dispositions impératives
                    protectrices du consommateur.
                </p>
            </section>

            <!-- CRÉDITS (facultatif) -->
            <section>
                <h2 class="text-xl font-semibold mb-2">Crédits</h2>
                <div class="bg-gray-50 p-4 rounded">
                    <p>
                        <strong>Conception et développement&nbsp;:</strong> [Nom
                        / équipe / école]
                    </p>
                    <p>
                        <strong>Photographies / Icônes&nbsp;:</strong> [Crédits
                        le cas échéant]
                    </p>
                </div>
            </section>
        </div>
    </PublicLayout>
</template>
