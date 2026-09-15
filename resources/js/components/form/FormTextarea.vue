<script setup>
defineProps({
	id: { type: String, required: true },
	labelledby: { type: String, default: null },
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
			:aria-labelledby="labelledby"
			:value="modelValue"
			:placeholder="placeholder"
			:aria-invalid="error ? 'true' : null"
			:aria-describedby="error ? `${id}-error` : null"
			class="w-full border border-brand p-12 md:p-14 lg:p-18 bg-white text-[18px] md:text-[20px] lg:text-[22px] placeholder:text-slate outline-none focus:ring-2 focus:ring-brand/40"
			@input="$emit('update:modelValue', $event.target.value)"></textarea>
		<template v-if="error">
			<p :id="`${id}-error`" role="alert" class="mt-6 text-xxs text-error">{{ error }}</p>
		</template>
	</div>
</template>
