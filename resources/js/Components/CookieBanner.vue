<!-- resources/js/Components/CookieBanner.vue -->
<script setup>
import { ref, onMounted } from "vue";
import { Link } from "@inertiajs/vue3";

const visible = ref(false);

onMounted(() => {
    // Affiche le bandeau uniquement si pas encore accepté
    visible.value = !document.cookie.includes("cookie_consent=accepted");
});

function acceptCookies() {
    const d = new Date();
    d.setFullYear(d.getFullYear() + 1); // 1 an
    document.cookie =
        "cookie_consent=accepted; path=/; expires=" + d.toUTCString();
    visible.value = false;
}
</script>

<template>
    <div
        v-if="visible"
        class="fixed bottom-0 inset-x-0 z-50 bg-gray-900 text-white px-6 py-4 text-sm"
    >
        <div
            class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center gap-4 justify-between"
        >
            <p class="text-center sm:text-left">
                Ce site utilise uniquement des cookies nécessaires à son
                fonctionnement (panier, session, paiement). Aucun cookie
                publicitaire n’est utilisé. Consultez notre
                <Link :href="route('privacy.policy')" class="underline"
                    >politique de confidentialité</Link
                >.
            </p>
            <button
                @click="acceptCookies"
                class="bg-white text-gray-900 font-semibold px-4 py-2 rounded hover:bg-gray-200 transition"
            >
                Accepter
            </button>
        </div>
    </div>
</template>
