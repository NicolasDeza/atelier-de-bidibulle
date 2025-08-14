<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import DeleteUserForm from "@/Pages/Profile/Partials/DeleteUserForm.vue";
import LogoutOtherBrowserSessionsForm from "@/Pages/Profile/Partials/LogoutOtherBrowserSessionsForm.vue";
import SectionBorder from "@/Components/SectionBorder.vue";
import TwoFactorAuthenticationForm from "@/Pages/Profile/Partials/TwoFactorAuthenticationForm.vue";
import UpdatePasswordForm from "@/Pages/Profile/Partials/UpdatePasswordForm.vue";
import UpdateProfileInformationForm from "@/Pages/Profile/Partials/UpdateProfileInformationForm.vue";
import { Link } from "@inertiajs/vue3";

defineProps({
    confirmsTwoFactorAuthentication: Boolean,
    sessions: Array,
});
</script>

<template>
    <AppLayout title="Profile">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Profile
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <div v-if="$page.props.jetstream.canUpdateProfileInformation">
                    <UpdateProfileInformationForm
                        :user="$page.props.auth.user"
                    />
                    <SectionBorder />
                </div>

                <div v-if="$page.props.jetstream.canUpdatePassword">
                    <UpdatePasswordForm class="mt-10 sm:mt-0" />
                    <SectionBorder />
                </div>

                <div
                    v-if="
                        $page.props.jetstream.canManageTwoFactorAuthentication
                    "
                >
                    <TwoFactorAuthenticationForm
                        :requires-confirmation="confirmsTwoFactorAuthentication"
                        class="mt-10 sm:mt-0"
                    />
                    <SectionBorder />
                </div>

                <LogoutOtherBrowserSessionsForm
                    :sessions="sessions"
                    class="mt-10 sm:mt-0"
                />

                <template
                    v-if="$page.props.jetstream.hasAccountDeletionFeatures"
                >
                    <SectionBorder />
                    <DeleteUserForm class="mt-10 sm:mt-0" />
                </template>

                <!-- Séparateur avant la partie Admin -->
                <SectionBorder />

                <!-- ADMINISTRATION -->
                <div
                    v-if="$page.props.auth?.user?.is_admin"
                    class="mt-10 sm:mt-0"
                >
                    <div class="md:grid md:grid-cols-3 md:gap-6">
                        <!-- Colonne gauche -->
                        <div class="px-4 sm:px-0">
                            <h3 class="text-lg font-medium text-gray-900">
                                Administration
                            </h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Accédez à l’interface d’administration pour
                                gérer le catalogue. Vous pourrez créer, modifier
                                et supprimer des produits.
                            </p>
                        </div>

                        <!-- Colonne droite -->
                        <div class="mt-5 md:col-span-2 md:mt-0">
                            <div class="bg-white shadow sm:rounded-lg">
                                <div class="px-4 py-5 sm:p-6">
                                    <h4
                                        class="text-base font-medium text-gray-900 mb-2"
                                    >
                                        Gestion des produits
                                    </h4>
                                    <p class="text-sm text-gray-600 mb-4">
                                        Ajouter un nouveau produit, mettre à
                                        jour le prix, le stock, la catégorie, et
                                        définir les images (nom de fichier)
                                        présentes dans
                                        <code
                                            class="px-1 py-0.5 bg-gray-100 rounded"
                                            >/public/images/produits/</code
                                        >.
                                    </p>
                                    <Link
                                        href="/admin/products"
                                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                    >
                                        Ouvrir l’admin
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /ADMINISTRATION -->
                <!-- /ADMIN -->
            </div>
        </div>
    </AppLayout>
</template>
