<script setup>
defineProps({
    id: { type: String, required: true },
    modelValue: { type: [String, Number], default: '' },
    type: { type: String, default: 'text' },
    error: { type: String, default: null },
    placeholder: { type: String, default: null },
    autocomplete: { type: String, default: null },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div>
        <input
            :id="id"
            :type="type"
            :value="modelValue"
            :placeholder="placeholder"
            :autocomplete="autocomplete"
            :aria-invalid="error ? 'true' : null"
            :aria-describedby="error ? `${id}-error` : null"
            class="w-full border border-accent px-12 py-10 md:px-16 md:py-12 bg-white outline-none focus:ring-2 focus:ring-accent/40"
            @input="$emit('update:modelValue', $event.target.value)">
        <p v-if="error" :id="`${id}-error`" class="mt-6 text-xxs text-error">{{ error }}</p>
    </div>
</template>
