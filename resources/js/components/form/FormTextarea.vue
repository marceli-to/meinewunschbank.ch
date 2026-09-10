<script setup>
defineProps({
    id: { type: String, required: true },
    modelValue: { type: String, default: '' },
    rows: { type: Number, default: 5 },
    error: { type: String, default: null },
    placeholder: { type: String, default: null },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div>
        <textarea
            :id="id"
            :rows="rows"
            :value="modelValue"
            :placeholder="placeholder"
            :aria-invalid="error ? 'true' : null"
            :aria-describedby="error ? `${id}-error` : null"
            class="w-full border border-brand px-12 py-10 md:px-16 md:py-12 bg-white outline-none focus:ring-2 focus:ring-brand/40"
            @input="$emit('update:modelValue', $event.target.value)"></textarea>
        <p v-if="error" :id="`${id}-error`" class="mt-6 text-xxs text-error">{{ error }}</p>
    </div>
</template>
