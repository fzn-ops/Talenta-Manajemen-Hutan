<script setup>
import { computed } from 'vue';

const props = defineProps({
	modelValue: {
		type: String,
		default: '',
	},
	placeholder: {
		type: String,
		default: 'Cari data disini...',
	},
	disabled: {
		type: Boolean,
		default: false,
	},
});

const emit = defineEmits(['update:modelValue', 'clear']);

const inputValue = computed({
	get: () => props.modelValue,
	set: (val) => emit('update:modelValue', val),
});

const handleClear = () => {
	emit('update:modelValue', '');
	emit('clear');
};
</script>

<template>
	<div class="group relative flex-1">
		<!-- Search Icon -->
		<div
			:class="[
				'pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 transition-colors duration-200',
				disabled ? 'text-[#8ca1b9]' : 'text-[#183669] group-hover:text-[#183669] group-focus-within:text-[#183669]'
			]"
		>
			<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
			</svg>
		</div>

		<!-- Input Text with Hover & Focus Styles (Border only, distinct hover tone) -->
		<input
			v-model="inputValue"
			type="text"
			:placeholder="placeholder"
			:disabled="disabled"
			class="h-[46px] w-full rounded-[10px] border-2 border-[#d6e0ee] bg-transparent pl-12 pr-10 font-inter text-[14px] text-[#173a63] placeholder-[#8ca1b9] transition-colors duration-200 hover:border-[#8ea9cb] focus:border-[#183669] focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:bg-[#f0f4f9] disabled:border-[#d6e0ee] disabled:hover:border-[#d6e0ee] disabled:opacity-60"
		/>

		<!-- Clear Button ('X') when input has text -->
		<button
			v-if="modelValue && !disabled"
			type="button"
			@click="handleClear"
			title="Hapus pencarian"
			class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-[#8ca1b9] transition-colors hover:text-[#183669] focus:outline-none"
		>
			<span class="flex h-6 w-6 items-center justify-center rounded-full transition hover:bg-[#eef2f8]">
				<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
				</svg>
			</span>
		</button>
	</div>
</template>