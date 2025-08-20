<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

defineProps({
    status: String,
});

const form = useForm({
    email: "",
});

const submit = () => {
    form.post(route("password.email"));
};
</script>

<template>
    <Head title="Mot de passe oublié" />

    <section
        class="min-h-screen flex items-center justify-center bg-gray-50 px-4"
    >
        <div class="w-full max-w-md bg-white shadow-md rounded-xl p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                Réinitialiser le mot de passe
            </h1>
            <p class="text-sm text-gray-600 mb-6">
                Entrez votre adresse e-mail et nous vous enverrons un lien pour
                réinitialiser votre mot de passe.
            </p>

            <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                {{ status }}
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="email" value="Email" />
                    <TextInput
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="mt-1 block w-full"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <PrimaryButton
                    class="w-full bg-bidibordeaux hover:bg-rose-800 active:bg-rose-900 transition-colors"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Envoyer le lien
                </PrimaryButton>
            </form>
        </div>
    </section>
</template>
