<script setup>
defineProps({
	variant: { type: String, default: 'primary' },
	type: { type: String, default: 'button' },
});

// A transparent border keeps the primary the same size as the bordered secondary.
const variants = {
	primary: 'border-transparent bg-brand text-white hover:bg-crimson active:bg-garnet',
	secondary: 'border-brand text-brand hover:bg-brand/15 active:bg-brand/20',
};
</script>

<template>
	<button
		:type="type"
		:class="variants[variant]"
		class="btn inline-flex w-auto font-bold text-[16px] lg:text-[18px] leading-none px-16 py-10 md:px-20 md:py-12 lg:px-32 lg:py-14 rounded-full border no-underline cursor-pointer transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
		<slot />
	</button>
</template>
