<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Checkbox from "@/Components/Checkbox.vue";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    terms: false,
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Inscription" />

    <section
        class="min-h-screen flex items-center justify-center py-4 px-3 bg-gray-50"
    >
        <div
            class="w-full max-w-4xl bg-white shadow-md rounded-xl overflow-hidden grid grid-cols-1 lg:grid-cols-2"
        >
            <!-- Colonne gauche (image ou couleur) -->
            <div class="hidden lg:block bg-bidibordeaux"></div>

            <!-- Colonne droite (formulaire) -->
            <div class="p-4 sm:p-6 lg:p-8">
                <h1
                    class="text-lg sm:text-xl lg:text-2xl font-bold mb-4 sm:mb-6 text-gray-900"
                >
                    Créer un compte
                </h1>

                <form @submit.prevent="submit" class="space-y-4 sm:space-y-5">
                    <div>
                        <InputLabel for="name" value="Nom" />
                        <TextInput
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="mt-1 block w-full"
                            required
                            autofocus
                            autocomplete="name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel for="email" value="Email" />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full"
                            required
                            autocomplete="username"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel for="password" value="Mot de passe" />
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full"
                            required
                            autocomplete="new-password"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password"
                        />
                    </div>

                    <div>
                        <InputLabel
                            for="password_confirmation"
                            value="Confirmer le mot de passe"
                        />
                        <TextInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            class="mt-1 block w-full"
                            required
                            autocomplete="new-password"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password_confirmation"
                        />
                    </div>

                    <div
                        v-if="
                            $page.props.jetstream
                                .hasTermsAndPrivacyPolicyFeature
                        "
                        class="pt-2"
                    >
                        <div class="flex items-start">
                            <Checkbox
                                id="terms"
                                v-model:checked="form.terms"
                                name="terms"
                                required
                                class="mt-1"
                            />
                            <label
                                for="terms"
                                class="ml-3 text-sm text-gray-600 leading-relaxed"
                            >
                                J'accepte les
                                <a
                                    target="_blank"
                                    :href="route('terms.show')"
                                    class="underline hover:text-gray-900"
                                >
                                    conditions d'utilisation
                                </a>
                                et la
                                <a
                                    target="_blank"
                                    :href="route('policy.show')"
                                    class="underline hover:text-gray-900"
                                >
                                    politique de confidentialité </a
                                >.
                            </label>
                        </div>
                        <InputError class="mt-2" :message="form.errors.terms" />
                    </div>

                    <!-- Boutons avec plus d'espacement -->
                    <div class="pt-4 sm:pt-6 space-y-3 sm:space-y-4">
                        <PrimaryButton
                            class="w-full bg-bidibordeaux hover:bg-rose-800 active:bg-rose-900 transition-colors justify-center text-sm sm:text-base"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            S'inscrire
                        </PrimaryButton>

                        <div class="text-center">
                            <Link
                                :href="route('login')"
                                class="text-xs sm:text-sm text-gray-600 hover:text-gray-900 underline"
                            >
                                Déjà inscrit ? Se connecter
                            </Link>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>
