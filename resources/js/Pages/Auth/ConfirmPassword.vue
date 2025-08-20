<script setup>
import { ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const form = useForm({
    password: "",
});

const passwordInput = ref(null);

const submit = () => {
    form.post(route("password.confirm"), {
        onFinish: () => {
            form.reset();
            passwordInput.value.focus();
        },
    });
};
</script>

<template>
    <Head title="Confirmation du mot de passe" />

    <section
        class="min-h-screen flex items-center justify-center bg-gray-50 px-4"
    >
        <div class="w-full max-w-md bg-white shadow-md rounded-xl p-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">
                Zone sécurisée
            </h1>
            <p class="text-sm text-gray-600 mb-6">
                Avant de continuer, merci de confirmer votre mot de passe pour
                accéder à cette section sécurisée.
            </p>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="password" value="Mot de passe" />
                    <TextInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        required
                        autocomplete="current-password"
                        autofocus
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <PrimaryButton
                    class="w-full bg-bidibordeaux hover:bg-rose-800 active:bg-rose-900 transition-colors"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Confirmer
                </PrimaryButton>
            </form>
        </div>
    </section>
</template>
