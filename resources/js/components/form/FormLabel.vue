<script setup>
defineProps({
    for: { type: String, default: null },
    required: { type: Boolean, default: false },
});
</script>

<template>
    <label :for="$props.for" class="block font-sans-bold mb-8 md:mb-10">
        <slot />
        <span v-if="required" aria-hidden="true"> *</span>
    </label>
</template>
