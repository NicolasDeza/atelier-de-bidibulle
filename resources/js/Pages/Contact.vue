<script setup>
import PublicLayout from "@/Layouts/PublicLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const form = useForm({
    name: "",
    email: "",
    message: "",
    // honeypot anti-spam
    website: "",
});

const sent = ref(false);

const submitForm = () => {
    sent.value = false;
    form.post(route("contact.send"), {
        onSuccess: () => {
            sent.value = true;
            form.reset("name", "email", "message");
        },
        onError: () => {
            sent.value = false;
        },
    });
};
</script>

<template>
    <Head title="Contact">
        <meta
            name="description"
            content="Contactez l'Atelier de Bidibulle pour toute question ou demande d'information sur nos créations artisanales."
        />
        <meta name="robots" content="index, follow" />
    </Head>

    <PublicLayout>
        <div class="max-w-[1440px] mx-auto py-12 px-6">
            <!-- En-tête -->
            <div class="text-center mb-12">
                <h1 class="text-3xl font-bold text-gray-900 mb-4">
                    Contactez-nous
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Une question sur nos créations ? Besoin d'un produit
                    personnalisé ? N'hésitez pas à nous écrire, nous vous
                    répondrons dans les plus brefs délais.
                </p>
            </div>

            <div class="grid lg:grid-cols-3 gap-12">
                <!-- Informations de contact -->
                <div class="lg:col-span-1">
                    <div class="bg-gray-50 rounded-2xl p-8 h-fit">
                        <h2 class="text-xl font-semibold mb-6 text-gray-900">
                            Nos coordonnées
                        </h2>

                        <div class="space-y-6">
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-bidibordeaux rounded-lg flex items-center justify-center"
                                    >
                                        <i
                                            class="fas fa-envelope text-white text-sm"
                                        ></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">
                                        Email
                                    </h3>
                                    <p class="text-gray-600">
                                        contact@atelier-bidibulle.com
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-bidibordeaux rounded-lg flex items-center justify-center"
                                    >
                                        <i
                                            class="fas fa-clock text-white text-sm"
                                        ></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">
                                        Délai de réponse
                                    </h3>
                                    <p class="text-gray-600">
                                        Sous 24-48h en moyenne
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0">
                                    <div
                                        class="w-10 h-10 bg-bidibordeaux rounded-lg flex items-center justify-center"
                                    >
                                        <i
                                            class="fas fa-heart text-white text-sm"
                                        ></i>
                                    </div>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900">
                                        Créations sur mesure
                                    </h3>
                                    <p class="text-gray-600">
                                        Nous adorons les projets personnalisés !
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Réseaux sociaux -->
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h3 class="font-medium text-gray-900 mb-4">
                                Suivez-nous
                            </h3>
                            <div class="flex space-x-4">
                                <a
                                    href="#"
                                    class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center hover:bg-bidibordeaux hover:text-white transition-colors"
                                >
                                    <i class="fab fa-facebook-f text-sm"></i>
                                </a>
                                <a
                                    href="#"
                                    class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center hover:bg-bidibordeaux hover:text-white transition-colors"
                                >
                                    <i class="fab fa-instagram text-sm"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulaire -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-2xl shadow-lg p-8">
                        <!-- Message de succès -->
                        <div
                            v-if="sent"
                            class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg"
                        >
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i
                                        class="fas fa-check-circle text-green-600"
                                    ></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-green-800 font-medium">
                                        Message envoyé avec succès !
                                    </p>
                                    <p class="text-green-700 text-sm">
                                        Nous vous répondrons dans les plus brefs
                                        délais.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <form @submit.prevent="submitForm" class="space-y-6">
                            <!-- Honeypot (caché) -->
                            <input
                                v-model="form.website"
                                type="text"
                                name="website"
                                class="hidden"
                                tabindex="-1"
                                autocomplete="off"
                            />

                            <!-- Nom et Email sur la même ligne -->
                            <div class="grid md:grid-cols-2 gap-6">
                                <div>
                                    <InputLabel
                                        for="name"
                                        value="Nom complet"
                                        class="text-gray-700 font-medium"
                                    />
                                    <TextInput
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bidibordeaux focus:ring-bidibordeaux focus:ring-opacity-50 transition-colors"
                                        placeholder="Votre nom"
                                        required
                                    />
                                    <InputError
                                        :message="form.errors.name"
                                        class="mt-1"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        for="email"
                                        value="Adresse email"
                                        class="text-gray-700 font-medium"
                                    />
                                    <TextInput
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bidibordeaux focus:ring-bidibordeaux focus:ring-opacity-50 transition-colors"
                                        placeholder="votre@email.com"
                                        required
                                    />
                                    <InputError
                                        :message="form.errors.email"
                                        class="mt-1"
                                    />
                                </div>
                            </div>

                            <!-- Message -->
                            <div>
                                <InputLabel
                                    for="message"
                                    value="Votre message"
                                    class="text-gray-700 font-medium"
                                />
                                <textarea
                                    id="message"
                                    v-model="form.message"
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-bidibordeaux focus:ring-bidibordeaux focus:ring-opacity-50 transition-colors resize-none"
                                    rows="6"
                                    placeholder="Décrivez votre demande, projet ou question..."
                                    required
                                />
                                <InputError
                                    :message="form.errors.message"
                                    class="mt-1"
                                />
                            </div>

                            <!-- Informations supplémentaires -->
                            <div
                                class="bg-blue-50 border border-blue-200 rounded-lg p-4"
                            >
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i
                                            class="fas fa-info-circle text-blue-600"
                                        ></i>
                                    </div>
                                    <div class="ml-3">
                                        <h3
                                            class="text-sm font-medium text-blue-800"
                                        >
                                            Projets personnalisés
                                        </h3>
                                        <div class="mt-1 text-sm text-blue-700">
                                            <p>
                                                N'hésitez pas à nous décrire vos
                                                idées de créations sur mesure,
                                                nous adorons relever de nouveaux
                                                défis créatifs !
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Bouton d'envoi -->
                            <div class="flex justify-end">
                                <PrimaryButton
                                    :disabled="form.processing"
                                    class="px-8 py-3 bg-bidibordeaux hover:bg-rose-800 focus:bg-rose-800 active:bg-rose-900 focus:ring-rose-500 transition-colors"
                                >
                                    <span
                                        v-if="form.processing"
                                        class="flex items-center"
                                        aria-live="polite"
                                    >
                                        <svg
                                            class="animate-spin -ml-1 mr-3 h-4 w-4 text-white"
                                            xmlns="http://www.w3.org/2000/svg"
                                            fill="none"
                                            viewBox="0 0 24 24"
                                            role="status"
                                            aria-hidden="true"
                                        >
                                            <circle
                                                class="opacity-25"
                                                cx="12"
                                                cy="12"
                                                r="10"
                                                stroke="currentColor"
                                                stroke-width="4"
                                            ></circle>
                                            <path
                                                class="opacity-75"
                                                fill="currentColor"
                                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                                            ></path>
                                        </svg>
                                        Envoi en cours...
                                    </span>
                                    <span v-else class="flex items-center">
                                        <i
                                            class="fas fa-paper-plane mr-2"
                                            aria-hidden="true"
                                        ></i>
                                        Envoyer le message
                                    </span>
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
