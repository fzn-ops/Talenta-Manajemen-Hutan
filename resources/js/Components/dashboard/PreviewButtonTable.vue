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
			'inline-flex h-[32px] w-[32px] shrink-0 items-center justify-center rounded-[8px] transition-all duration-150',
			disabled
				? 'cursor-not-allowed bg-slate-200 opacity-50 shadow-none'
				: 'hover:brightness-90 active:scale-95 cursor-pointer'
		]"
		@click="!disabled && $emit('click')"
	>
		<img src="/assets/icons/button-preview.svg" alt="" class="h-[32px] w-[32px] rounded-[8px] object-contain" aria-hidden="true" />
	</button>
</template>
