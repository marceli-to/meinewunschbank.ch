<script setup>
defineProps({
    id: { type: String, required: true },
    modelValue: { type: Boolean, default: false },
    error: { type: String, default: null },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <div>
        <div class="flex items-start gap-12 md:gap-16">
            <input
                :id="id"
                type="checkbox"
                :checked="modelValue"
                :aria-invalid="error ? 'true' : null"
                :aria-describedby="error ? `${id}-error` : null"
                class="mt-2 size-18 md:size-20 shrink-0 accent-accent border border-accent"
                @change="$emit('update:modelValue', $event.target.checked)">
            <label :for="id" class="font-sans-bold cursor-pointer">
                <slot />
            </label>
        </div>
        <p v-if="error" :id="`${id}-error`" class="mt-6 text-xxs text-error">{{ error }}</p>
    </div>
</template>
