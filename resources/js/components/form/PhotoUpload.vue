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
        <div class="flex items-center justify-between gap-16 md:gap-24 border-b border-accent pb-8">
            <p class="text-xxs md:text-xs">
                <template v-if="filename">{{ filename }}</template>
                <template v-else>JPG, PNG oder WEBP, max. 12 MB</template>
            </p>
            <div class="flex items-center gap-8 md:gap-12 shrink-0">
                <button
                    v-if="filename"
                    type="button"
                    class="btn cursor-pointer text-xxs underline underline-offset-2 hover:no-underline"
                    @click="clear">
                    entfernen
                </button>
                <button
                    type="button"
                    class="btn inline-flex w-auto font-bold leading-none px-16 py-10 xl:px-20 xl:py-12 rounded-full border border-accent text-accent cursor-pointer hover:bg-accent hover:text-white transition-colors"
                    @click="pick">
                    Foto hochladen
                </button>
            </div>
        </div>

        <input
            ref="input"
            type="file"
            accept="image/jpeg,image/png,image/webp,image/heic"
            class="sr-only"
            @change="onChange">

        <img
            v-if="preview"
            :src="preview"
            alt=""
            class="mt-16 md:mt-20 w-160 md:w-200 h-auto object-cover">

        <p v-if="error" class="mt-6 text-xxs text-error">{{ error }}</p>
    </div>
</template>
