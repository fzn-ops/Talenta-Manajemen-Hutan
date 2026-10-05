<script setup>
const emit = defineEmits(['click']);

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

const handleClick = (e) => {
	if (e && e.currentTarget && typeof e.currentTarget.blur === 'function') {
		e.currentTarget.blur();
	}
	emit('click');
};
</script>

<template>
	<button
		type="button"
		:aria-label="label"
		:disabled="disabled"
		:class="[
			'inline-flex h-[28px] w-[28px] shrink-0 items-center justify-center rounded-[5px] transition-colors outline-none focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0 edit-table-btn',
			disabled
				? 'cursor-not-allowed bg-slate-200 opacity-50 shadow-none'
				: 'bg-[#ffd56a] text-[#f4a300] active:bg-[#ffcc54]'
		]"
		style="outline: none !important; box-shadow: none !important;"
		@click="!disabled && handleClick($event)"
	>
		<img src="/assets/icons/edit.svg" alt="" class="h-[18px] w-[18px] object-contain" aria-hidden="true" />
	</button>
</template>

<style scoped>
@media (hover: hover) and (pointer: fine) {
	.edit-table-btn:not(:disabled):hover {
		background-color: #ffcc54;
	}
}
</style>
