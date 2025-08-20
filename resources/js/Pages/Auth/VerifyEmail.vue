<script setup>
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    status: String,
});

const form = useForm({});

const submit = () => {
    form.post(route("verification.send"));
};

const verificationLinkSent = computed(
    () => props.status === "verification-link-sent"
);
</script>

<template>
    <Head title="Vérification Email" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div class="mb-4 text-sm text-gray-600">
            Avant de continuer, merci de vérifier votre adresse email en
            cliquant sur le lien que nous venons de vous envoyer.
            <br />
            Si vous n’avez pas reçu l’email, vous pouvez en demander un nouveau
            ci-dessous.
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-4 font-medium text-sm text-green-600"
        >
            Un nouveau lien de vérification a été envoyé à votre adresse email.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Renvoyer l’email
                </PrimaryButton>

                <div class="flex items-center space-x-4">
                    <Link
                        :href="route('profile.show')"
                        class="text-sm text-gray-600 hover:text-gray-900 underline"
                    >
                        Modifier le profil
                    </Link>

                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="text-sm text-gray-600 hover:text-gray-900 underline"
                    >
                        Déconnexion
                    </Link>
                </div>
            </div>
        </form>
    </AuthenticationCard>
</template>
