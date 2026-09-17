<script setup>
import { computed } from 'vue';

const props = defineProps({
	modelValue: {
		type: String,
		default: '',
	},
	placeholder: {
		type: String,
		default: '812-3456-7890',
	},
	disabled: {
		type: Boolean,
		default: false,
	},
	heightClass: {
		type: String,
		default: 'h-[42px]',
	},
});

const emit = defineEmits(['update:modelValue']);

// Helper to strip non-digits except dashes and handle +62
const cleanInput = (val) => {
	if (!val) return '';
	let clean = val.replace(/^\+62\s*/, '').replace(/^62\s*/, '').replace(/^0/, '');
	return clean;
};

const internalValue = computed({
	get: () => cleanInput(props.modelValue),
	set: (val) => {
		const raw = val ? val.trim() : '';
		if (!raw) {
			emit('update:modelValue', '');
		} else {
			emit('update:modelValue', `+62 ${raw}`);
		}
	},
});
</script>

<template>
	<div class="relative flex items-center w-full rounded-[10px] border border-[#d6e0ee] bg-white transition-colors duration-150 hover:border-[#a6b7cb] hover:bg-[#fafcff] focus-within:border-[#183669] focus-within:bg-white">
		<!-- Country Code Prefix -->
		<div class="flex items-center gap-1 pl-3.5 pr-2.5 text-[#183669] font-inter text-[14px] font-semibold select-none border-r border-[#d6e0ee]">
			<span class="text-xs">🇮🇩</span>
			<span>+62</span>
		</div>

		<!-- Input Phone Number -->
		<input
			v-model="internalValue"
			type="tel"
			:placeholder="placeholder"
			:disabled="disabled"
			:class="[
				heightClass,
				'w-full bg-transparent px-3 font-inter text-[14px] text-[#1e3456] placeholder-[#a6b7cb] focus:outline-none focus:ring-0 disabled:cursor-not-allowed disabled:opacity-60'
			]"
		/>
	</div>
</template>
