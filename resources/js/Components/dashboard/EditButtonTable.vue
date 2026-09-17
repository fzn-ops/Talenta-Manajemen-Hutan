<script setup>
defineEmits(['click']);

defineProps({
	label: {
		type: String,
		default: 'Edit data',
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
		:disabled="disabled"
		:class="[
			'inline-flex h-[32px] w-[32px] shrink-0 items-center justify-center rounded-[8px] transition-colors',
			disabled
				? 'cursor-not-allowed bg-slate-200 opacity-50 shadow-none'
				: 'bg-[#ffd56a] text-[#f4a300] hover:bg-[#ffcc54]'
		]"
		@click="!disabled && $emit('click')"
	>
		<img src="/assets/icons/edit.svg" alt="" class="h-[18px] w-[18px] object-contain" aria-hidden="true" />
	</button>
</template>
