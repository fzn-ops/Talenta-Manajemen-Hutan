<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

// Props
const props = defineProps({
	currentPage: {
		type: Number,
		required: true,
	},
	totalPages: {
		type: Number,
		default: null,
	},
	totalItems: {
		type: Number,
		default: null,
	},
	rowsPerPage: {
		type: Number,
		required: true,
	},
	rowsOptions: {
		type: Array,
		default: () => [5, 10, 20, 50],
	},
	disabled: {
		type: Boolean,
		default: false,
	},
});

// Emits
const emit = defineEmits(['update:currentPage', 'update:rowsPerPage']);

// Menghitung total halaman secara akurat (baik dari totalPages langsung atau dari totalItems / rowsPerPage)
const computedTotalPages = computed(() => {
	if (props.totalPages !== null && props.totalPages !== undefined) {
		return Math.max(1, Number(props.totalPages) || 1);
	}
	if (props.totalItems !== null && props.totalItems !== undefined) {
		const count = Math.ceil(Number(props.totalItems) / (props.rowsPerPage || 10));
		return Math.max(1, count);
	}
	return 1;
});

// Internal page input for direct jump
const pageInput = ref(props.currentPage);

watch(
	() => props.currentPage,
	(val) => {
		pageInput.value = val;
	},
);

const handlePageInput = () => {
	if (props.disabled) return;
	let page = parseInt(pageInput.value, 10);
	if (isNaN(page) || page < 1) page = 1;
	if (page > computedTotalPages.value) page = computedTotalPages.value;
	pageInput.value = page;
	emit('update:currentPage', page);
};

const goToPage = (page) => {
	if (props.disabled) return;
	if (typeof page === 'number' && page >= 1 && page <= computedTotalPages.value) {
		emit('update:currentPage', page);
	}
};

const prevPage = () => {
	if (props.disabled) return;
	if (props.currentPage > 1) {
		emit('update:currentPage', props.currentPage - 1);
	}
};

const nextPage = () => {
	if (props.disabled) return;
	if (props.currentPage < computedTotalPages.value) {
		emit('update:currentPage', props.currentPage + 1);
	}
};

// Visible page window - hanya menampilkan jumlah tombol sesuai total halaman yang ada
const visiblePages = computed(() => {
	const total = computedTotalPages.value;
	const current = props.currentPage;

	if (total <= 7) {
		return Array.from({ length: total }, (_, i) => i + 1);
	}
	if (current <= 4) {
		return [1, 2, 3, 4, 5, '...', total];
	}
	if (current >= total - 3) {
		return [1, '...', total - 4, total - 3, total - 2, total - 1, total];
	}
	return [1, '...', current - 1, current, current + 1, '...', total];
});

// Rows per page dropup
const isRowsDropdownOpen = ref(false);

const setRowsPerPage = (r) => {
	if (props.disabled) return;
	isRowsDropdownOpen.value = false;
	emit('update:rowsPerPage', r);
};

// Close dropup when clicking outside
const closeDropup = () => {
	isRowsDropdownOpen.value = false;
};

onMounted(() => document.addEventListener('click', closeDropup));
onBeforeUnmount(() => document.removeEventListener('click', closeDropup));
</script>

<template>
	<div class="flex flex-col gap-4 py-2 font-inter text-[13px] text-[#4d6786] sm:flex-row sm:items-center sm:justify-between">
		<!-- Left: Page [input] of N | Rows per page [dropup] -->
		<div class="flex items-center gap-2">
			<span>Page</span>
			<input
				type="number"
				min="1"
				:max="computedTotalPages"
				:disabled="disabled"
				v-model.number="pageInput"
				@keydown.enter="handlePageInput"
				@blur="handlePageInput"
				class="h-8 w-11 rounded-[6px] border border-[#d6e0ee] bg-white p-0 text-center font-inter text-[13px] font-medium text-[#173a63] focus:border-[#183669] focus:outline-none focus:ring-1 focus:ring-[#183669] disabled:cursor-not-allowed disabled:bg-[#f0f4f9] disabled:border-[#d6e0ee] disabled:opacity-60"
			/>
			<span>of {{ computedTotalPages }}</span>

			<span class="mx-1.5 text-[#cbd6e2]">|</span>

			<span>Rows per page</span>

			<!-- Rows Per Page Dropup -->
			<div class="relative" @click.stop>
				<button
					type="button"
					:disabled="disabled"
					@click="!disabled && (isRowsDropdownOpen = !isRowsDropdownOpen)"
					class="flex h-8 min-w-[56px] items-center justify-between gap-2 rounded-[6px] border border-[#d6e0ee] bg-white px-2.5 font-inter text-[13px] font-medium text-[#173a63] transition hover:border-[#183669] focus:border-[#183669] focus:outline-none disabled:cursor-not-allowed disabled:bg-[#f0f4f9] disabled:border-[#d6e0ee] disabled:opacity-60 disabled:hover:border-[#d6e0ee]"
					:class="{ 'border-[#183669] ring-1 ring-[#183669]/20': isRowsDropdownOpen }"
				>
					<span>{{ rowsPerPage }}</span>
					<svg
						:class="['h-3.5 w-3.5 text-[#8ca1b9] transition-transform duration-200', isRowsDropdownOpen ? 'rotate-180 text-[#183669]' : '']"
						fill="none"
						stroke="currentColor"
						stroke-width="2"
						viewBox="0 0 24 24"
					>
						<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
					</svg>
				</button>

				<!-- Dropup Popover -->
				<div
					v-if="isRowsDropdownOpen && !disabled"
					class="absolute bottom-full left-0 z-30 mb-1.5 w-20 rounded-[8px] border border-[#d6e0ee] bg-white p-1 shadow-xl font-inter space-y-0.5"
				>
					<button
						v-for="r in rowsOptions"
						:key="r"
						type="button"
						@click="setRowsPerPage(r)"
						:class="[
							'w-full rounded-[4px] px-2 py-1 text-center text-xs transition-colors',
							rowsPerPage === r
								? 'bg-[#183669] font-bold text-white'
								: 'text-[#435b76] hover:bg-slate-100'
						]"
					>
						{{ r }}
					</button>
				</div>
			</div>
		</div>

		<!-- Right: << < [page buttons] > >> -->
		<div class="flex items-center gap-1">
			<!-- First Page (<<) -->
			<button
				type="button"
				@click="goToPage(1)"
				:disabled="disabled || currentPage === 1"
				class="flex h-8 w-8 items-center justify-center rounded-[6px] border border-[#d6e0ee] bg-white text-[#4d6786] transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
				aria-label="First Page"
			>
				<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" d="M18.75 19.5l-7.5-7.5 7.5-7.5m-6 15L5.25 12l7.5-7.5" />
				</svg>
			</button>

			<!-- Prev Page (<) -->
			<button
				type="button"
				@click="prevPage"
				:disabled="disabled || currentPage === 1"
				class="flex h-8 w-8 items-center justify-center rounded-[6px] border border-[#d6e0ee] bg-white text-[#4d6786] transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
				aria-label="Previous Page"
			>
				<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
				</svg>
			</button>

			<!-- Page Numbers -->
			<template v-for="(p, index) in visiblePages" :key="index">
				<button
					v-if="p !== '...'"
					type="button"
					:disabled="disabled"
					@click="goToPage(p)"
					:class="[
						'flex h-8 min-w-[32px] items-center justify-center rounded-[6px] px-1 text-[13px] transition-colors',
						p === currentPage
							? 'border border-[#cbd8e8] bg-[#f0f4f9] font-bold text-[#183669]'
							: 'border border-transparent text-[#4d6786] hover:bg-slate-100',
						disabled ? 'cursor-not-allowed opacity-50 hover:bg-transparent' : ''
					]"
				>
					{{ p }}
				</button>
				<span v-else class="flex h-8 w-7 items-center justify-center text-[#8ca1b9]">...</span>
			</template>

			<!-- Next Page (>) -->
			<button
				type="button"
				@click="nextPage"
				:disabled="disabled || currentPage >= computedTotalPages"
				class="flex h-8 w-8 items-center justify-center rounded-[6px] border border-[#d6e0ee] bg-white text-[#4d6786] transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
				aria-label="Next Page"
			>
				<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
				</svg>
			</button>

			<!-- Last Page (>>) -->
			<button
				type="button"
				@click="goToPage(computedTotalPages)"
				:disabled="disabled || currentPage >= computedTotalPages"
				class="flex h-8 w-8 items-center justify-center rounded-[6px] border border-[#d6e0ee] bg-white text-[#4d6786] transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40"
				aria-label="Last Page"
			>
				<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 4.5l7.5 7.5-7.5 7.5m6-15l7.5 7.5-7.5 7.5" />
				</svg>
			</button>
		</div>
	</div>
</template>
