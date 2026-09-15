<script setup>
import IconCheck from '@/components/icons/IconCheck.vue';

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
			<span class="relative mt-2 block shrink-0">
				<input
					:id="id"
					type="checkbox"
					:checked="modelValue"
					:aria-invalid="error ? 'true' : null"
					:aria-describedby="error ? `${id}-error` : null"
					class="peer block size-20 md:size-22 lg:size-24 appearance-none border border-brand bg-white cursor-pointer outline-none focus:ring-2 focus:ring-brand/40"
					@change="$emit('update:modelValue', $event.target.checked)">
				<IconCheck class="pointer-events-none absolute inset-0 hidden p-3 text-brand peer-checked:block" />
			</span>
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
