<script setup>
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
	show: {
		type: Boolean,
		default: false,
	},
	type: {
		type: String,
		default: 'success', // 'success', 'error', 'info', 'warning'
	},
	title: {
		type: String,
		default: '',
	},
	message: {
		type: String,
		default: '',
	},
	duration: {
		type: Number,
		default: 4000, // 4 seconds
	},
});

const emit = defineEmits(['close']);

let timer = null;

const startTimer = () => {
	if (timer) clearTimeout(timer);
	if (props.duration > 0 && props.show) {
		timer = setTimeout(() => {
			emit('close');
		}, props.duration);
	}
};

watch(
	() => props.show,
	(val) => {
		if (val) {
			startTimer();
		} else if (timer) {
			clearTimeout(timer);
		}
	}
);

onMounted(() => {
	if (props.show) {
		startTimer();
	}
});

const config = computed(() => {
	switch (props.type) {
		case 'error':
			return {
				bg: 'bg-red-50',
				border: 'border-red-200',
				text: 'text-red-800',
				iconBg: 'bg-red-100 text-red-600',
				title: props.title || 'Terjadi Kesalahan',
			};
		case 'warning':
			return {
				bg: 'bg-amber-50',
				border: 'border-amber-200',
				text: 'text-amber-800',
				iconBg: 'bg-amber-100 text-amber-600',
				title: props.title || 'Peringatan',
			};
		case 'info':
			return {
				bg: 'bg-blue-50',
				border: 'border-blue-200',
				text: 'text-blue-800',
				iconBg: 'bg-blue-100 text-blue-600',
				title: props.title || 'Informasi',
			};
		case 'success':
		default:
			return {
				bg: 'bg-[#f0fdf4]',
				border: 'border-[#bbf7d0]',
				text: 'text-[#166534]',
				iconBg: 'bg-[#dcfce7] text-[#15803d]',
				title: props.title || 'Berhasil',
			};
	}
});
</script>

<template>
	<Teleport to="body">
		<Transition
			enter-active-class="transform ease-out duration-300 transition"
			enter-from-class="-translate-y-3 opacity-0 sm:translate-y-0 sm:translate-x-4"
			enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
			leave-active-class="transition ease-in duration-200"
			leave-from-class="opacity-100"
			leave-to-class="opacity-0"
		>
			<div
				v-if="show"
				class="pointer-events-none fixed top-4 inset-x-4 z-50 flex flex-col items-center sm:inset-x-auto sm:top-5 sm:right-5 sm:w-full sm:max-w-sm sm:items-end font-poppins"
			>
				<div
					:class="[
						'pointer-events-auto flex w-full items-center gap-3.5 rounded-[12px] border p-4 shadow-xl shadow-slate-900/10 backdrop-blur-sm transition-all',
						config.bg,
						config.border,
					]"
					role="alert"
				>
					<!-- Icon -->
					<div :class="['flex h-8 w-8 shrink-0 items-center justify-center rounded-full', config.iconBg]">
						<!-- Success Check Icon -->
						<svg v-if="type === 'success'" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
						</svg>
						<!-- Error Alert Icon -->
						<svg v-else-if="type === 'error'" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
						</svg>
						<!-- Info Icon -->
						<svg v-else class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
						</svg>
					</div>

					<!-- Content -->
					<div class="flex-1 min-w-0">
						<p :class="['text-[14px] font-bold leading-tight', config.text]">
							{{ config.title }}
						</p>
						<p v-if="message" :class="['mt-1 font-inter text-[13px] leading-snug opacity-90', config.text]">
							{{ message }}
						</p>
					</div>

					<!-- Close Button -->
					<button
						type="button"
						@click="$emit('close')"
						:class="['shrink-0 rounded-md p-1 opacity-70 transition hover:opacity-100 focus:outline-none', config.text]"
					>
						<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
						</svg>
					</button>
				</div>
			</div>
		</Transition>
	</Teleport>
</template>