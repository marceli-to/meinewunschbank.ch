<script setup>
import { computed, ref } from 'vue';
import Button from '../Button.vue';
import FormLegend from './FormLegend.vue';
import IconTrash from '../icons/IconTrash.vue';

const props = defineProps({
	modelValue: { type: File, default: null },
	label: { type: String, required: true },
	required: { type: Boolean, default: false },
	error: { type: String, default: null },
});

const emit = defineEmits(['update:modelValue']);

const input = ref(null);
const preview = ref(null);

const filename = computed(() => props.modelValue?.name ?? null);

// The hint has done its job once a usable file is in place.
const showHint = computed(() => ! preview.value || Boolean(props.error));

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
	<div class="relative">
		<div class="flex items-end gap-16 md:gap-24 mb-24 md:mb-28 lg:mb-32">
			<FormLegend as="div" id="photo-heading" :required="required" class="grow border-b border-brand pb-8">{{ label }}</FormLegend>
			<Button
				variant="secondary"
				:aria-describedby="showHint ? 'photo-status' : null"
				class="shrink-0 mb-4 md:mb-6 lg:mb-8 translate-y-1/2"
				@click="pick">
				Foto hochladen
			</Button>
		</div>

		<template v-if="showHint">
		  <div id="photo-status" class="text-xxs md:text-xs absolute top-48 lg:top-60 left-0">
			JPG, PNG oder WEBP, max. 12 MB
		  </div>
		</template>

		<p aria-live="polite" class="sr-only">{{ filename ? `${filename} ausgewählt` : '' }}</p>

		<input
			ref="input"
			type="file"
			tabindex="-1"
			aria-hidden="true"
			accept="image/jpeg,image/png,image/webp,image/heic"
			class="sr-only"
			@change="onChange">

		<template v-if="preview">
			<div class="mt-16 md:mt-20">
				<div class="w-max border border-brand bg-white p-12 md:p-14 lg:p-18">
					<img
						:src="preview"
						alt=""
						class="block w-220 md:w-280 h-auto object-cover">
				</div>
				<button
					type="button"
					aria-label="Foto entfernen"
					class="btn group mt-8 md:mt-10 lg:mt-12 inline-flex items-center gap-6 cursor-pointer text-xxs"
					@click="clear">
					<IconTrash class="size-18 md:size-20 lg:size-22 shrink-0" />
					<span class="underline underline-offset-2 group-hover:no-underline">entfernen</span>
				</button>
			</div>
		</template>

		<template v-if="error">
			<p role="alert" class="mt-6 text-xxs text-error">{{ error }}</p>
		</template>
	</div>
</template>
