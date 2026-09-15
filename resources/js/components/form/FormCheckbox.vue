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
				class="mt-2 size-18 md:size-20 shrink-0 accent-brand border border-brand"
				@change="$emit('update:modelValue', $event.target.checked)">
			<label :for="id" class="font-bold cursor-pointer">
				<slot />
			</label>
		</div>
		<template v-if="error">
			<div :id="`${id}-error`" role="alert" class="mt-6 font-bold text-brand text-[16px] md:text-[18px] lg:text-[20px]">
				{{ error }}
			</div>
		</template>
	</div>
</template>
