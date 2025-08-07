<script setup>
import { Link } from "@inertiajs/vue3";

defineProps({
    links: Array,
});

function formatLabel(label) {
    if (label.includes("Previous")) return "&laquo; Précédent";
    if (label.includes("Next")) return "Suivant &raquo;";
    return label;
}
</script>

<template>
    <div
        v-if="links.length > 3"
        class="flex flex-wrap gap-2 mt-8 justify-center"
    >
        <template v-for="(link, key) in links" :key="key">
            <!-- Si le lien a une URL valide -->
            <Link
                v-if="link.url"
                :href="link.url"
                v-html="formatLabel(link.label)"
                :class="[
                    'px-3 py-1 border rounded transition',
                    link.active ? 'bg-black text-white' : 'hover:bg-gray-100',
                ]"
            />

            <!-- Si le lien n'a PAS d'URL -->
            <span
                v-else
                v-html="formatLabel(link.label)"
                class="px-3 py-1 border rounded text-gray-400 cursor-not-allowed"
            />
        </template>
    </div>
</template>
