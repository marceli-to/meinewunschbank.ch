<script setup>
defineProps({
	// The photo group is labelled by a plain element, not a <legend>.
	as: { type: String, default: 'legend' },
	required: { type: Boolean, default: false },
});
</script>

<template>
	<component :is="as" class="w-full font-bold text-[18px] md:text-[24px] lg:text-[28px] mb-4 md:mb-6 lg:mb-8">
		<slot />
		<template v-if="required">
			<span aria-hidden="true"> *</span>
		</template>
	</component>
</template>
