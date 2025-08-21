<script setup>
import Header from "@/Components/Header.vue";
import Footer from "@/Components/Footer.vue";
import { usePage } from "@inertiajs/vue3";
import { ref, watch } from "vue";
import Toast from "@/Components/Toast.vue";
import CookieBanner from "@/Components/CookieBanner.vue";

const page = usePage();
const toast = ref({ msg: null, type: "success" });

// affiche automatiquement flash.success / flash.error
watch(
    () => page.props.flash,
    (f) => {
        if (f?.success) toast.value = { msg: f.success, type: "success" };
        else if (f?.error) toast.value = { msg: f.error, type: "error" };
    },
    { immediate: true }
);
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <Header />

        <!-- Contenu principal en full width -->
        <main class="flex-grow">
            <slot />
        </main>

        <Footer />

        <Toast
            v-if="toast.msg"
            :message="toast.msg"
            :type="toast.type"
            @hidden="toast.msg = null"
        />
        <CookieBanner />
    </div>
</template>
