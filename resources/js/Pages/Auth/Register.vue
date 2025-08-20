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
        class="min-h-screen flex items-center justify-center py-16 px-4 bg-gray-50"
    >
        <div
            class="w-full max-w-4xl bg-white shadow-md rounded-xl overflow-hidden grid grid-cols-1 md:grid-cols-2"
        >
            <!-- Colonne gauche (image ou couleur) -->
            <div class="hidden md:block bg-bidibordeaux"></div>
            <!-- 👉 plus tard tu peux remplacer par une image :
            <div class="hidden md:block bg-[url('/images/auth-bg.jpg')] bg-cover bg-center"></div> -->

            <!-- Colonne droite (formulaire) -->
            <div class="p-8">
                <h1 class="text-2xl font-bold mb-6 text-gray-900">
                    Créer un compte
                </h1>

                <form @submit.prevent="submit" class="space-y-4">
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
                        class="flex items-center"
                    >
                        <Checkbox
                            id="terms"
                            v-model:checked="form.terms"
                            name="terms"
                            required
                        />
                        <label for="terms" class="ml-2 text-sm text-gray-600">
                            J’accepte les
                            <a
                                target="_blank"
                                :href="route('terms.show')"
                                class="underline hover:text-gray-900"
                            >
                                conditions d’utilisation
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
                        <InputError class="mt-2" :message="form.errors.terms" />
                    </div>

                    <div class="flex items-center justify-between mt-6">
                        <Link
                            :href="route('login')"
                            class="underline text-sm text-gray-600 hover:text-gray-900"
                        >
                            Déjà inscrit ?
                        </Link>

                        <PrimaryButton
                            class="bg-bidibordeaux hover:bg-rose-800 active:bg-rose-900 transition-colors"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            S’inscrire
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>
