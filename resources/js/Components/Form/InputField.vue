<script setup>
defineOptions({ inheritAttrs: false });

defineProps({
    label: String,
    required: Boolean,
    error: String,
    placeholder: String,
    type: { type: String, default: "text" },
    modelValue: [String, Number],
});

defineEmits(["update:modelValue"]);
</script>

<template>
    <div class="space-y-1">
        <label class="text-sm font-medium text-slate-700 flex items-center">
            {{ label }}
            <span v-if="required" class="text-red-500 ml-1">*</span>
        </label>

        <input
            :value="modelValue"
            @input="
                $emit(
                    'update:modelValue',
                    $event.target.type === 'number'
                        ? Number($event.target.value)
                        : $event.target.value
                )
            "
            :type="type"
            :placeholder="placeholder"
            v-bind="$attrs"
            class="w-full px-3 py-2 bg-white rounded-lg text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-colors border"
            :class="
                error
                    ? 'border-red-500 focus:ring-red-500'
                    : 'border-slate-300 focus:border-transparent'
            "
        />

        <p v-if="error" class="text-xs text-red-600">{{ error }}</p>
    </div>
</template>
