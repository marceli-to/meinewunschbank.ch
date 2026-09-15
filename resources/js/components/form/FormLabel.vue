<script setup>
defineProps({
	for: { type: String, default: null },
	required: { type: Boolean, default: false },
});
</script>

<template>
	<label :for="$props.for" class="block font-bold mb-4 lg:mb-8">
		<slot />
		<!-- On one line: split, the space before the asterisk is lost. -->
		<template v-if="required">
			<span aria-hidden="true"> *</span>
		</template>
	</label>
</template>
