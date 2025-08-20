<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import Checkbox from "@/Components/Checkbox.vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? "on" : "",
    })).post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Connexion" />

    <section
        class="min-h-screen flex items-center justify-center py-16 px-4 bg-gray-50"
    >
        <div
            class="w-full max-w-4xl bg-white shadow-md rounded-xl overflow-hidden grid grid-cols-1 md:grid-cols-2"
        >
            <!-- Colonne gauche (image ou couleur unie) -->
            <div class="hidden md:block bg-bidibordeaux"></div>
            <!-- 👉 plus tard tu peux remplacer par une image :
            <div class="hidden md:block bg-[url('/images/auth-bg.jpg')] bg-cover bg-center"></div> -->

            <!-- Colonne droite (formulaire) -->
            <div class="p-8">
                <h1 class="text-2xl font-bold mb-6 text-gray-900">Connexion</h1>

                <div
                    v-if="status"
                    class="mb-4 font-medium text-sm text-green-600"
                >
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Email -->
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

                    <!-- Mot de passe -->
                    <div>
                        <InputLabel for="password" value="Mot de passe" />
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full"
                            required
                            autocomplete="current-password"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password"
                        />
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center">
                        <Checkbox
                            v-model:checked="form.remember"
                            name="remember"
                        />
                        <span class="ml-2 text-sm text-gray-600">
                            Se souvenir de moi
                        </span>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between mt-6">
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="underline text-sm text-gray-600 hover:text-gray-900"
                        >
                            Mot de passe oublié ?
                        </Link>

                        <PrimaryButton
                            class="bg-bidibordeaux hover:bg-rose-800 active:bg-rose-900 transition-colors"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            Se connecter
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </section>
</template>
