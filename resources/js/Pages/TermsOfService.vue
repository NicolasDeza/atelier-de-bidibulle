<script setup>
import { Head, Link } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
</script>

<template>
    <Head title="Conditions générales de vente">
        <meta
            name="description"
            content="Consultez les Conditions Générales de Vente de l'Atelier de Bidibulle : commandes, paiements, livraisons, retours, garanties et respect du droit belge."
        />
        <meta name="robots" content="noindex, follow" />
    </Head>

    <PublicLayout>
        <div class="max-w-[900px] py-12 px-10">
            <h1 class="text-3xl font-bold mb-2">
                Conditions générales de vente
            </h1>
            <p class="text-sm text-gray-600 mb-8">
                Dernière mise à jour : [JJ/MM/AAAA]
            </p>

            <!-- 1. Identification du vendeur -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">
                    1. Identification du vendeur
                </h2>
                <p class="mb-4">
                    Le présent site <strong>L'Atelier de Bidibulle</strong> est
                    exploité par :
                </p>
                <ul class="list-disc ml-6 space-y-1">
                    <li>
                        Entreprise : <strong>[Nom / raison sociale]</strong>
                    </li>
                    <li>
                        Numéro BCE / TVA : <strong>[BE0xxx.xxx.xxx]</strong>
                    </li>
                    <li>
                        Adresse :
                        <strong>[Rue, n°, CP, Ville, Belgique]</strong>
                    </li>
                    <li>
                        Email : <strong>contact@atelier-bidibulle.com</strong>
                    </li>
                    <li>Téléphone : <strong>[numéro]</strong></li>
                    <li>
                        Hébergeur :
                        <strong>[Nom + adresse de l'hébergeur]</strong>
                    </li>
                </ul>
            </section>

            <!-- 2. Objet -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">2. Objet</h2>
                <p>
                    Les présentes CGV définissent les droits et obligations
                    contractuels applicables à toute commande passée sur le Site
                    entre le vendeur et le client consommateur au sens du Code
                    de droit économique belge.
                </p>
            </section>

            <!-- 3. Produits -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">3. Produits</h2>
                <ul class="list-disc ml-6 space-y-2">
                    <li>
                        Les produits proposés sont décrits avec la plus grande
                        exactitude possible.
                    </li>
                    <li>Les photographies sont fournies à titre indicatif.</li>
                    <li>
                        <strong>Important :</strong> Les articles personnalisés
                        ou fabriqués sur demande ne peuvent être ni repris ni
                        échangés.
                    </li>
                </ul>
            </section>

            <!-- 4. Prix -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">4. Prix</h2>
                <p>
                    Les prix sont indiqués en euros (€), toutes taxes comprises
                    (TTC). Les frais de livraison sont précisés avant validation
                    de la commande et s'ajoutent au prix des produits.
                </p>
            </section>

            <!-- 5. Commandes -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">5. Commandes</h2>
                <div class="space-y-3">
                    <p>
                        Le client valide sa commande via le processus sécurisé
                        du site.
                    </p>
                    <p>
                        La commande n'est considérée comme ferme et définitive
                        qu'après réception du paiement intégral par le vendeur.
                    </p>
                    <p>
                        Une confirmation de commande est envoyée par email au
                        client.
                    </p>
                </div>
            </section>

            <!-- 6. Paiement -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">6. Paiement</h2>
                <div class="space-y-3">
                    <p>
                        Le paiement est effectué en ligne de manière sécurisée
                        via notre prestataire <strong>Stripe</strong>.
                    </p>
                    <p>
                        Moyens de paiement acceptés : Carte bancaire, PayPal,
                        Bancontact.
                    </p>
                    <div class="bg-blue-50 p-4 rounded">
                        <p class="text-sm">
                            <strong>Sécurité :</strong> Le vendeur n'a jamais
                            accès aux données bancaires du client, qui sont
                            traitées directement par Stripe.
                        </p>
                    </div>
                </div>
            </section>

            <!-- 7. Livraison -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">7. Livraison</h2>
                <div class="space-y-3">
                    <p>
                        <strong>Zone de livraison :</strong> Belgique et Union
                        européenne (selon disponibilité).
                    </p>
                    <p>
                        <strong>Délais :</strong> 24 à 48h de préparation + 2 à
                        4 jours ouvrés d'expédition via Bpost.
                    </p>
                    <p>
                        En cas de retard de livraison supérieur à 14 jours, le
                        client peut demander l'annulation de la commande et le
                        remboursement.
                    </p>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    Plus de détails :
                    <Link :href="route('shipping.returns')" class="underline">
                        Livraison & Retours
                    </Link>
                </p>
            </section>

            <!-- 8. Droit de rétractation -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">
                    8. Droit de rétractation et retours
                </h2>
                <div class="space-y-3">
                    <p>
                        Conformément au droit belge et européen, le client
                        dispose de
                        <strong>14 jours calendrier</strong> à compter de la
                        réception pour exercer son droit de rétractation.
                    </p>
                    <div class="bg-amber-50 p-4 rounded">
                        <h3 class="font-semibold mb-2">Exceptions :</h3>
                        <p class="text-sm">
                            Les produits personnalisés ou fabriqués sur mesure
                            ne peuvent faire l'objet d'une rétractation.
                        </p>
                    </div>
                    <p>
                        Les frais de retour sont à la charge du client, sauf en
                        cas de défaut du produit.
                    </p>
                </div>
            </section>

            <!-- 9. Garanties légales -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">9. Garanties légales</h2>
                <div class="space-y-3">
                    <p>
                        Les produits bénéficient de la garantie légale de
                        conformité pendant <strong>2 ans</strong> à compter de
                        la livraison.
                    </p>
                    <p>
                        En cas de défaut de conformité, le client doit informer
                        le vendeur dans un délai raisonnable avec preuves à
                        l'appui (photos, description du défaut).
                    </p>
                </div>
            </section>

            <!-- 10. Responsabilité -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">10. Responsabilité</h2>
                <p>
                    Le vendeur ne peut être tenu responsable des dommages
                    résultant d'une mauvaise utilisation du produit ou d'un cas
                    de force majeure (catastrophe naturelle, grève, etc.).
                </p>
            </section>

            <!-- 11. Protection des données -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">
                    11. Protection des données (RGPD)
                </h2>
                <p>
                    Les données personnelles collectées servent uniquement à la
                    gestion des commandes et au service client. Pour plus
                    d'informations, consultez notre
                    <Link :href="route('privacy.policy')" class="underline">
                        Politique de confidentialité </Link
                    >.
                </p>
            </section>

            <!-- 12. Droit applicable -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">
                    12. Droit applicable et litiges
                </h2>
                <div class="space-y-3">
                    <p>
                        Les présentes CGV sont régies par le
                        <strong>droit belge</strong>.
                    </p>
                    <p>
                        En cas de litige, compétence des tribunaux de
                        l'arrondissement judiciaire du siège social du vendeur,
                        sauf dispositions impératives protectrices du
                        consommateur.
                    </p>
                    <div class="bg-gray-100 p-4 rounded">
                        <p class="text-sm">
                            <strong>Médiation :</strong> En cas de litige, une
                            solution amiable sera recherchée avant tout recours
                            judiciaire.
                        </p>
                    </div>
                </div>
            </section>

            <!-- 13. Modifications -->
            <section>
                <h2 class="text-xl font-semibold mb-2">
                    13. Modifications des CGV
                </h2>
                <p>
                    Ces conditions générales peuvent être modifiées à tout
                    moment. La version en vigueur au moment de la commande
                    s'applique. Les modifications importantes seront notifiées
                    aux clients.
                </p>
            </section>
        </div>
    </PublicLayout>
</template>
