<script setup>
import { computed, ref } from 'vue';

const props = defineProps({
    modelValue: { type: File, default: null },
    error: { type: String, default: null },
});

const emit = defineEmits(['update:modelValue']);

const input = ref(null);
const preview = ref(null);

const filename = computed(() => props.modelValue?.name ?? null);

function pick() {
    input.value.click();
}

function onChange(event) {
    const file = event.target.files?.[0] ?? null;

    if (preview.value) {
        URL.revokeObjectURL(preview.value);
        preview.value = null;
    }

    if (file) {
        preview.value = URL.createObjectURL(file);
    }

    emit('update:modelValue', file);
}

function clear() {
    if (preview.value) {
        URL.revokeObjectURL(preview.value);
        preview.value = null;
    }

    input.value.value = '';
    emit('update:modelValue', null);
}
</script>

<template>
    <div>
        <div class="flex items-center justify-between gap-16 md:gap-24 border-b border-brand pb-8">
            <p id="photo-status" aria-live="polite" class="text-xxs md:text-xs">
                <template v-if="filename">{{ filename }}</template>
                <template v-else>JPG, PNG oder WEBP, max. 12 MB</template>
            </p>
            <div class="flex items-center gap-8 md:gap-12 shrink-0">
                <button
                    v-if="filename"
                    type="button"
                    aria-label="Foto entfernen"
                    class="btn cursor-pointer text-xxs underline underline-offset-2 hover:no-underline"
                    @click="clear">
                    entfernen
                </button>
                <button
                    type="button"
                    aria-describedby="photo-status"
                    class="btn inline-flex w-auto font-bold leading-none px-16 py-10 lg:px-20 lg:py-12 rounded-full border border-brand text-brand cursor-pointer hover:bg-brand hover:text-white transition-colors"
                    @click="pick">
                    Foto hochladen
                </button>
            </div>
        </div>

        <input
            ref="input"
            type="file"
            tabindex="-1"
            aria-hidden="true"
            accept="image/jpeg,image/png,image/webp,image/heic"
            class="sr-only"
            @change="onChange">

        <img
            v-if="preview"
            :src="preview"
            alt=""
            class="mt-16 md:mt-20 w-160 md:w-200 h-auto object-cover">

        <p v-if="error" role="alert" class="mt-6 text-xxs text-error">{{ error }}</p>
    </div>
</template>
