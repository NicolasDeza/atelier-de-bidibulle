<script setup>
import { Head, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("password.update"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Réinitialiser le mot de passe" />

    <section
        class="min-h-screen flex items-center justify-center py-16 px-4 bg-gray-50"
    >
        <div class="w-full max-w-md bg-white shadow-md rounded-xl p-8">
            <h1 class="text-2xl font-bold mb-6 text-gray-900 text-center">
                Réinitialiser le mot de passe
            </h1>

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

                <div>
                    <InputLabel for="password" value="Nouveau mot de passe" />
                    <TextInput
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full"
                        required
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
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

                <div class="flex justify-end">
                    <PrimaryButton
                        class="bg-bidibordeaux hover:bg-rose-800 active:bg-rose-900 transition-colors"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                    >
                        Réinitialiser
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </section>
</template>
