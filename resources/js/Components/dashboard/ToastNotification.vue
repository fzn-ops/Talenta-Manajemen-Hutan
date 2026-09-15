<script setup>
import { computed } from 'vue';

const props = defineProps({
	show: {
		type: Boolean,
		default: false,
	},
	type: {
		type: String,
		default: 'success', // 'success' | 'error' | 'info'
	},
	title: {
		type: String,
		default: '',
	},
	message: {
		type: String,
		default: '',
	},
});

defineEmits(['close']);

const bgColor = computed(() => {
	switch (props.type) {
		case 'success':
			return 'bg-[#416f65] text-white';
		case 'error':
			return 'bg-[#e11d48] text-white';
		default:
			return 'bg-[#17334F] text-white';
	}
});
</script>

<template>
	<Transition
		enter-active-class="transform ease-out duration-300 transition"
		enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
		enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
		leave-active-class="transition ease-in duration-100"
		leave-from-class="opacity-100"
		leave-to-class="opacity-0"
	>
		<div
			v-if="show"
			class="fixed bottom-5 right-5 z-50 flex max-w-md items-center gap-3 rounded-xl p-4 shadow-xl font-poppins"
			:class="bgColor"
		>
			<svg v-if="type === 'success'" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
				<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
			</svg>
			<svg v-else-if="type === 'error'" class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
				<path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
			</svg>
			<svg v-else class="h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
				<path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
			</svg>

			<div class="flex-1">
				<h4 v-if="title" class="text-sm font-bold leading-tight">{{ title }}</h4>
				<p class="text-xs font-normal opacity-90 mt-0.5 leading-normal">{{ message }}</p>
			</div>

			<button
				type="button"
				class="ml-2 inline-flex shrink-0 rounded-md p-1 text-white/80 hover:text-white focus:outline-none cursor-pointer"
				@click="$emit('close')"
			>
				<svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
					<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
				</svg>
			</button>
		</div>
	</Transition>
</template>
