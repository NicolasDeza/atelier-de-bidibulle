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
        class="flex flex-wrap gap-1 sm:gap-2 mt-8 justify-center max-w-full overflow-x-auto"
    >
        <template v-for="(link, key) in links" :key="key">
            <Link
                v-if="link.url"
                :href="link.url"
                v-html="formatLabel(link.label)"
                :class="[
                    'px-2 sm:px-3 py-1 border rounded transition text-sm sm:text-base',
                    link.active ? 'bg-black text-white' : 'hover:bg-gray-100',
                ]"
            />
            <span
                v-else
                v-html="formatLabel(link.label)"
                class="px-2 sm:px-3 py-1 border rounded text-gray-400 cursor-not-allowed text-sm sm:text-base"
            />
        </template>
    </div>
</template>
