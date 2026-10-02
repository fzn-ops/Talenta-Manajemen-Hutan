<script setup>
defineEmits(['click']);

defineProps({
	label: {
		type: String,
		default: 'Lihat detail',
	},
	disabled: {
		type: Boolean,
		default: false,
	},
});
</script>

<template>
	<button
		type="button"
		:aria-label="label"
		:title="label"
		:disabled="disabled"
		:class="[
			'inline-flex items-center justify-center rounded-[6px] transition-all duration-150 focus:outline-none',
			disabled
				? 'cursor-not-allowed opacity-50 shadow-none'
				: 'hover:brightness-75 active:brightness-60 cursor-pointer'
		]"
		@click="!disabled && $emit('click')"
	>
		<img src="/assets/icons/button-preview.svg" alt="" class="h-7 w-7 rounded-[5px]" aria-hidden="true" />
	</button>
</template>
