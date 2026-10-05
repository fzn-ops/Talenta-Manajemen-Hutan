<script setup>
const emit = defineEmits(['click']);

defineProps({
	label: {
		type: String,
		default: 'Hapus data',
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
			'inline-flex h-[28px] w-[28px] shrink-0 items-center justify-center rounded-[5px] transition-colors outline-none focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0 delete-table-btn',
			disabled
				? 'cursor-not-allowed bg-slate-200 opacity-50 shadow-none'
				: 'bg-[#ff9ca1] text-[#ff2f35] active:bg-[#ff888e]'
		]"
		style="outline: none !important; box-shadow: none !important;"
		@click="!disabled && handleClick($event)"
	>
		<img src="/assets/icons/delete.svg" alt="" class="h-[18px] w-[18px] object-contain" aria-hidden="true" />
	</button>
</template>

<style scoped>
@media (hover: hover) and (pointer: fine) {
	.delete-table-btn:not(:disabled):hover {
		background-color: #ff888e;
	}
}
</style>
