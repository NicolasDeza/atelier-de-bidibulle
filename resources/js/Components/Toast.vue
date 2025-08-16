<script setup>
import { onMounted, ref } from "vue";
const props = defineProps({
    message: { type: String, required: true },
    type: { type: String, default: "success" }, // success | error | info
    duration: { type: Number, default: 3000 }, // ms
});
const emit = defineEmits(["hidden"]);
const show = ref(true);
function close() {
    show.value = false;
    setTimeout(() => emit("hidden"), 150);
}
onMounted(() => setTimeout(close, props.duration));
</script>

<template>
    <transition name="fade">
        <div v-if="show" class="fixed bottom-4 right-4 z-50">
            <div
                :class="[
                    'rounded-md px-4 py-3 shadow text-sm',
                    type === 'success'
                        ? 'bg-green-600 text-white'
                        : type === 'error'
                        ? 'bg-red-600 text-white'
                        : 'bg-gray-800 text-white',
                ]"
            >
                {{ message }}
            </div>
        </div>
    </transition>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.15s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
