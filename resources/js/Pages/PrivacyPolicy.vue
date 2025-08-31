<script setup>
import { Head, Link } from "@inertiajs/vue3";
import PublicLayout from "@/Layouts/PublicLayout.vue";
</script>

<template>
    <Head title="Politique de confidentialité">
        <meta
            name="description"
            content="Découvrez comment l’Atelier de Bidibule collecte, utilise et protège vos données personnelles. Politique RGPD conforme (Belgique/UE)."
        />
    </Head>

    <PublicLayout>
        <div class="max-w-[1440px] mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold mb-2">
                Politique de confidentialité
            </h1>
            <p class="text-sm text-gray-600 mb-8">
                Dernière mise à jour : 27/08/2025
            </p>

            <!-- 1. Responsable de traitement -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">
                    1 Responsable du traitement
                </h2>
                <p>
                    Le responsable du traitement des données personnelles
                    collectées via le site
                    <strong>L’Atelier de Bidibule</strong> (le « Site ») est :
                </p>
                <ul class="list-disc ml-6">
                    <li>
                        Entreprise :
                        <strong>Productions associées ASBL</strong>
                    </li>
                    <li>Numéro BCE / TVA : <strong>BE0896.755.397</strong></li>
                    <li>
                        Adresse :
                        <strong>Rue Coenraerts 72, 1060 Bruxelles</strong>
                    </li>
                    <li>
                        Contact (RGPD) :
                        <strong>contact@atelierdebidibulle.be</strong>
                    </li>
                    <li>
                        Hébergeur :
                        <strong>O2Switch</strong>
                    </li>
                    <li>
                        Adresse :
                        <strong
                            >Chemin des Pardiaux, 63000 Clermont-Ferrand,
                            France</strong
                        >
                    </li>
                    <li>
                        Site web :
                        <strong>https://www.o2switch.fr</strong>
                    </li>
                </ul>
            </section>

            <!-- 2. Données collectées -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">2 Données collectées</h2>
                <ul class="list-disc ml-6 space-y-1">
                    <li>
                        <strong>Identité / contact :</strong> nom, prénom,
                        email, téléphone, adresses de facturation/livraison.
                    </li>
                    <li>
                        <strong>Commande :</strong> produits, quantités,
                        montants, historique d’achats.
                    </li>
                    <li>
                        <strong>Paiement :</strong> traité par
                        <strong>Stripe</strong> (derniers chiffres et statut).
                        Aucune donnée de carte n’est stockée sur le Site.
                    </li>
                    <li>
                        <strong>Formulaire de contact :</strong> nom, adresse
                        email, contenu du message (finalité : répondre à votre
                        demande).
                    </li>
                    <li><strong>Newsletter :</strong> email et préférences.</li>
                    <li>
                        <strong>Navigation / cookies :</strong> uniquement des
                        cookies essentiels (gestion du panier, authentification
                        sécurisée, protection contre la fraude). Aucun cookie
                        publicitaire ou de suivi externe n’est utilisé.
                    </li>
                </ul>
                <p class="mt-4 text-sm bg-gray-100 p-3 rounded">
                    <strong>Important :</strong> nous collectons uniquement les
                    données nécessaires à l’exécution de vos commandes et à
                    l’amélioration du service.
                </p>
            </section>

            <!-- 3. Finalités & bases légales -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">
                    3 Finalités et bases légales (art. 6 RGPD)
                </h2>
                <div class="space-y-4">
                    <div class="border-l-4 border-blue-500 pl-4">
                        <h3 class="font-semibold">Traitement des commandes</h3>
                        <p>
                            <strong>Base :</strong> exécution du contrat (art.
                            6(1)(b))
                        </p>
                        <p>
                            Préparation, expédition, service client,
                            facturation.
                        </p>
                    </div>
                    <div class="border-l-4 border-green-500 pl-4">
                        <h3 class="font-semibold">Obligations légales</h3>
                        <p>
                            <strong>Base :</strong> obligation légale (art.
                            6(1)(c))
                        </p>
                        <p>Conservation comptable et fiscale (Belgique).</p>
                    </div>
                    <div class="border-l-4 border-purple-500 pl-4">
                        <h3 class="font-semibold">Newsletter / prospection</h3>
                        <p>
                            <strong>Base :</strong> consentement (art. 6(1)(a))
                        </p>
                        <p>
                            Vous pouvez retirer votre consentement à tout
                            moment.
                        </p>
                    </div>
                    <div class="border-l-4 border-gray-500 pl-4">
                        <h3 class="font-semibold">
                            Sécurité / prévention fraude
                        </h3>
                        <p>
                            <strong>Base :</strong> intérêt légitime (art.
                            6(1)(f))
                        </p>
                    </div>
                    <div class="border-l-4 border-orange-500 pl-4">
                        <h3 class="font-semibold">
                            Réponse aux demandes via formulaire
                        </h3>
                        <p>
                            <strong>Base :</strong> intérêt légitime (art.
                            6(1)(f)) — nous utilisons vos données uniquement
                            pour traiter et répondre à votre demande de contact.
                        </p>
                    </div>
                </div>
            </section>

            <!-- 4. Cookies -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">4. Cookies</h2>
                <p>
                    Aucun cookie publicitaire ni de suivi d’audience n’est
                    utilisé.
                </p>
                <p>
                    Notre site utilise uniquement des cookies
                    <strong>nécessaires</strong> à son bon fonctionnement
                    (gestion du panier, session utilisateur, sécurité et
                    paiement). Ces cookies ne collectent aucune donnée
                    personnelle à des fins publicitaires.
                </p>
                <p class="mt-2">
                    Vous pouvez gérer ou supprimer les cookies nécessaires via
                    les paramètres de votre navigateur. Toutefois, leur
                    suppression peut affecter certaines fonctionnalités
                    essentielles du site.
                </p>
                <p class="text-sm text-gray-600 mt-2">
                    Voir aussi nos
                    <Link :href="route('terms.conditions')" class="underline">
                        Conditions générales </Link
                    >.
                </p>
            </section>

            <!-- 5. Destinataires / sous-traitants -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">
                    5 Destinataires et sous-traitants
                </h2>
                <p>
                    Accès limité aux personnes habilitées. Prestataires agissant
                    en notre nom (contrats RGPD) :
                </p>
                <ul class="list-disc ml-6 mt-2">
                    <li>
                        <strong>Stripe Payments Europe</strong> : traitement des
                        paiements.
                    </li>
                    <li><strong>Hébergeur</strong> : O2Switch</li>
                    <li>
                        <strong>Brevo</strong>
                        : envoi d’emails.
                    </li>
                    <li><strong>Bpost</strong> : livraison.</li>
                </ul>
            </section>

            <!-- 6. Transferts hors EEE -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">
                    6 Transferts hors EEE
                </h2>
                <p>
                    Si certains prestataires traitent des données hors Espace
                    économique européen, nous veillons à des garanties
                    appropriées (décision d’adéquation, clauses contractuelles
                    types, mesures complémentaires).
                </p>
            </section>

            <!-- 7. Durées de conservation -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">
                    7 Durées de conservation
                </h2>
                <ul class="list-disc ml-6 space-y-1">
                    <li>
                        <strong>Facturation / pièces comptables :</strong>
                        jusqu’à <strong>10 ans</strong> (obligations légales).
                    </li>
                    <li>
                        <strong>Compte client inactif : </strong>
                        <strong>36</strong> mois puis suppression/anonymisation.
                    </li>
                    <li>
                        <strong>Messages de contact :</strong>

                        Messages reçus via le formulaire de contact sont
                        conservés <strong>24 mois</strong> maximum, puis
                        supprimés.
                    </li>
                    <li>
                        <strong>Newsletter :</strong> jusqu’au désabonnement.
                    </li>
                    <li>
                        <strong>Cookies :</strong> selon typologie (cf. §4).
                    </li>
                </ul>
            </section>

            <!-- 8. Vos droits -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">8 Vos droits (RGPD)</h2>
                <p>
                    Droits d’accès, rectification, effacement, limitation,
                    opposition, portabilité (et directives post-mortem le cas
                    échéant).
                </p>
                <p class="mt-2">
                    Exercice des droits :
                    <strong>contact@atelierdebidibulle.be</strong> ou courrier à
                    <strong>Rue Coenraerts 72 1060 Bruxelles</strong>. Délai de
                    réponse : <strong>1 mois</strong> (prolongeable selon
                    complexité). Une preuve d’identité peut être demandée.
                </p>
                <p class="mt-2">
                    Si vous estimez que vos droits ne sont pas respectés, vous
                    pouvez introduire une plainte auprès de l’
                    <strong>Autorité de protection des données (APD)</strong> —
                    Rue de la Presse 35, 1000 Bruxelles —
                    <a
                        href="https://www.autoriteprotectiondonnees.be"
                        class="underline"
                        target="_blank"
                        rel="noopener"
                        >autoriteprotectiondonnees.be</a
                    >.
                </p>
            </section>

            <!-- 9. Sécurité -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">9 Sécurité</h2>
                <p>
                    Mesures techniques et organisationnelles adaptées
                    (HTTPS/TLS, contrôle d’accès, sauvegardes, journalisation).
                    En cas de violation à risque, nous appliquons la procédure
                    de notification RGPD.
                </p>
            </section>

            <!-- 10. Mineurs -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">10 Mineurs</h2>
                <p>
                    Le Site n’est pas destiné aux moins de 16 ans.
                    Parents/tuteurs : contactez-nous si des données ont été
                    transmises sans consentement approprié.
                </p>
            </section>

            <!-- 11. Décisions automatisées -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">
                    11 Décisions automatisées
                </h2>
                <p>
                    Aucune décision produisant des effets juridiques fondée
                    exclusivement sur un traitement automatisé (y compris
                    profilage), au sens de l’article 22 RGPD.
                </p>
            </section>

            <!-- 12. Contact & réclamations -->
            <section class="mb-8">
                <h2 class="text-xl font-semibold mb-2">
                    12 Contact & réclamations
                </h2>
                <p>
                    Pour toute question liée à cette politique ou à vos données
                    :
                    <Link :href="route('contact')" class="underline"
                        >Service client</Link
                    >
                    — <strong>contact@atelierdebidibulle.be</strong>.
                </p>
            </section>

            <!-- 13. Modifications -->
            <section>
                <h2 class="text-xl font-semibold mb-2">
                    13 Modifications de la politique
                </h2>
                <p>
                    Nous pouvons mettre à jour la présente politique pour
                    refléter les évolutions légales ou techniques. La version
                    publiée sur cette page fait foi. En cas de changement
                    substantiel, un avis pourra être publié sur le Site et/ou
                    envoyé par email.
                </p>
            </section>
        </div>
    </PublicLayout>
</template>
