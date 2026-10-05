<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
	modelValue: {
		type: String,
		default: '',
	},
	placeholder: {
		type: String,
		default: 'Pilih tanggal',
	},
	required: {
		type: Boolean,
		default: false,
	},
	disabled: {
		type: Boolean,
		default: false,
	},
	hasError: {
		type: Boolean,
		default: false,
	},
	error: {
		type: [Boolean, String],
		default: false,
	},
	placement: {
		type: String,
		default: 'auto', // 'auto' | 'top' | 'bottom'
	},
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const datePickerRef = ref(null);
const yearsContainerRef = ref(null);

// viewMode: 'days' | 'months' | 'years'
const viewMode = ref('days');

const monthNames = [
	'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
	'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

const monthShortNames = [
	'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
	'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'
];

const dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

// Current viewed month and year in calendar
const currentYear = new Date().getFullYear();
const viewYear = ref(currentYear);
const viewMonth = ref(new Date().getMonth()); // 0-indexed

// Year decade range for year view navigation
const yearRangeStart = ref(Math.floor(currentYear / 12) * 12);

// Generate 12 years for the year picker view
const decadeYears = computed(() => {
	const years = [];
	for (let i = 0; i < 12; i++) {
		years.push(yearRangeStart.value + i);
	}
	return years;
});

// Sync view with modelValue when opened or changed
const syncViewWithModel = () => {
	if (props.modelValue) {
		const parts = props.modelValue.split('-');
		if (parts.length === 3) {
			const y = parseInt(parts[0], 10);
			const m = parseInt(parts[1], 10) - 1;
			if (!isNaN(y) && !isNaN(m)) {
				viewYear.value = y;
				viewMonth.value = m;
				yearRangeStart.value = Math.floor(y / 12) * 12;
				return;
			}
		}
	}
	const now = new Date();
	viewYear.value = now.getFullYear();
	viewMonth.value = now.getMonth();
	yearRangeStart.value = Math.floor(now.getFullYear() / 12) * 12;
};

watch(() => props.modelValue, syncViewWithModel, { immediate: true });

const openUpward = ref(false);

const updatePlacement = () => {
	if (!datePickerRef.value) return;
	if (props.placement === 'top') {
		openUpward.value = true;
		return;
	}
	if (props.placement === 'bottom') {
		openUpward.value = false;
		return;
	}
	// 'auto' mode
	const rect = datePickerRef.value.getBoundingClientRect();
	const popoverHeight = 360;
	const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
	const spaceBelow = viewportHeight - rect.bottom;
	const spaceAbove = rect.top;

	if (spaceBelow < popoverHeight && spaceAbove > spaceBelow) {
		openUpward.value = true;
	} else {
		openUpward.value = false;
	}
};

const toggleDropdown = () => {
	if (props.disabled) return;
	isOpen.value = !isOpen.value;
	if (isOpen.value) {
		viewMode.value = 'days';
		syncViewWithModel();
		updatePlacement();
		nextTick(() => {
			updatePlacement();
		});
	}
};

const closeDropdown = () => {
	isOpen.value = false;
	viewMode.value = 'days';
};

const handleWindowEvents = () => {
	if (isOpen.value) {
		updatePlacement();
	}
};

// Outside click listener & resize listener
onMounted(() => {
	document.addEventListener('click', closeDropdown);
	window.addEventListener('resize', handleWindowEvents);
	window.addEventListener('scroll', handleWindowEvents, true);
});

onBeforeUnmount(() => {
	document.removeEventListener('click', closeDropdown);
	window.removeEventListener('resize', handleWindowEvents);
	window.removeEventListener('scroll', handleWindowEvents, true);
});

// Format displayed date: e.g. "21 Januari 2026"
const displayValue = computed(() => {
	if (!props.modelValue) return '';
	const parts = props.modelValue.split('-');
	if (parts.length === 3) {
		const y = parseInt(parts[0], 10);
		const m = parseInt(parts[1], 10) - 1;
		const d = parseInt(parts[2], 10);
		if (!isNaN(y) && !isNaN(m) && !isNaN(d) && monthNames[m]) {
			return `${d} ${monthNames[m]} ${y}`;
		}
	}
	return props.modelValue;
});

// Navigation handlers depending on viewMode
const handlePrev = () => {
	if (viewMode.value === 'days') {
		if (viewMonth.value === 0) {
			viewMonth.value = 11;
			viewYear.value--;
		} else {
			viewMonth.value--;
		}
	} else if (viewMode.value === 'months') {
		viewYear.value--;
	} else if (viewMode.value === 'years') {
		yearRangeStart.value -= 12;
	}
};

const handleNext = () => {
	if (viewMode.value === 'days') {
		if (viewMonth.value === 11) {
			viewMonth.value = 0;
			viewYear.value++;
		} else {
			viewMonth.value++;
		}
	} else if (viewMode.value === 'months') {
		viewYear.value++;
	} else if (viewMode.value === 'years') {
		yearRangeStart.value += 12;
	}
};

const openMonthView = () => {
	viewMode.value = viewMode.value === 'months' ? 'days' : 'months';
};

const openYearView = () => {
	yearRangeStart.value = Math.floor(viewYear.value / 12) * 12;
	viewMode.value = viewMode.value === 'years' ? 'days' : 'years';
};

const selectMonth = (idx) => {
	viewMonth.value = idx;
	viewMode.value = 'days';
};

const selectYear = (y) => {
	viewYear.value = y;
	viewMode.value = 'days';
};

// Helper: pad 2 digits
const pad = (num) => String(num).padStart(2, '0');

// Days generation for current viewed month
const calendarDays = computed(() => {
	const year = Number(viewYear.value);
	const month = Number(viewMonth.value);

	const firstDayIndex = new Date(year, month, 1).getDay(); // 0 = Sun
	const daysInMonth = new Date(year, month + 1, 0).getDate();
	const daysInPrevMonth = new Date(year, month, 0).getDate();

	const days = [];

	// Previous month buffer days
	for (let i = firstDayIndex - 1; i >= 0; i--) {
		const d = daysInPrevMonth - i;
		const prevM = month === 0 ? 11 : month - 1;
		const prevY = month === 0 ? year - 1 : year;
		days.push({
			day: d,
			dateString: `${prevY}-${pad(prevM + 1)}-${pad(d)}`,
			isCurrentMonth: false,
		});
	}

	// Current month days
	const todayStr = new Date().toISOString().split('T')[0];
	for (let i = 1; i <= daysInMonth; i++) {
		const dateStr = `${year}-${pad(month + 1)}-${pad(i)}`;
		days.push({
			day: i,
			dateString: dateStr,
			isCurrentMonth: true,
			isToday: dateStr === todayStr,
			isSelected: props.modelValue === dateStr,
		});
	}

	// Next month buffer days to fill grid (42 slots total for 6 weeks)
	const remaining = 42 - days.length;
	for (let i = 1; i <= remaining; i++) {
		const nextM = month === 11 ? 0 : month + 1;
		const nextY = month === 11 ? year + 1 : year;
		days.push({
			day: i,
			dateString: `${nextY}-${pad(nextM + 1)}-${pad(i)}`,
			isCurrentMonth: false,
		});
	}

	return days;
});

const selectDate = (dayObj) => {
	emit('update:modelValue', dayObj.dateString);
	emit('change', dayObj.dateString);
	isOpen.value = false;
};

const selectToday = () => {
	const today = new Date().toISOString().split('T')[0];
	emit('update:modelValue', today);
	emit('change', today);
	isOpen.value = false;
};

const clearDate = () => {
	emit('update:modelValue', '');
	emit('change', '');
	isOpen.value = false;
};
</script>

<template>
	<div ref="datePickerRef" class="relative w-full" @click.stop>
		<!-- Input Field Trigger -->
		<button
			type="button"
			:disabled="disabled"
			@click="toggleDropdown"
			:class="[
				'flex h-[44px] w-full items-center justify-between rounded-[10px] border bg-white px-3.5 font-inter text-[14px] text-left transition-colors duration-150 focus:outline-none',
				(hasError || error)
					? 'border-red-400 focus:border-red-500 bg-red-50/20'
					: (isOpen ? 'border-[#183669] bg-white' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff]'),
				disabled ? 'cursor-not-allowed bg-slate-50 text-slate-400 opacity-60' : 'cursor-pointer'
			]"
		>
			<span :class="displayValue ? 'text-[#1e3456] font-medium' : 'text-[#a6b7cb]'" class="truncate">
				{{ displayValue || placeholder }}
			</span>

			<!-- Calendar SVG Icon -->
			<svg
				:class="['h-4 w-4 shrink-0 transition-colors ml-2', isOpen ? 'text-[#183669]' : 'text-[#8ca1b9]']"
				fill="none"
				stroke="currentColor"
				stroke-width="2"
				viewBox="0 0 24 24"
			>
				<path
					stroke-linecap="round"
					stroke-linejoin="round"
					d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5"
				/>
			</svg>
		</button>

		<!-- Popover Calendar Container (100% width matching input field without overflow) -->
		<div
			v-if="isOpen"
			:class="[
				'absolute left-0 z-50 w-full rounded-[12px] border border-[#d6e0ee] bg-white p-2.5 sm:p-3 shadow-xl font-inter select-none box-border',
				openUpward ? 'bottom-full mb-1.5' : 'top-full mt-1.5'
			]"
		>
			<!-- Calendar Header Navigation (Interactive Month & Year without arrow icons) -->
			<div class="flex items-center justify-between pb-2 border-b border-[#f0f4f9]">
				<!-- Prev Button -->
				<button
					type="button"
					@click="handlePrev"
					class="flex h-7 w-7 shrink-0 items-center justify-center rounded-[6px] text-[#4d6786] transition hover:bg-[#f0f4f9] hover:text-[#183669]"
					title="Sebelumnya"
				>
					<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
					</svg>
				</button>

				<!-- Center Header: Clickable Month & Year (Hover state without dropdown chevron) -->
				<div class="flex items-center justify-center gap-1">
					<!-- Month Button -->
					<button
						type="button"
						@click="openMonthView"
						:class="[
							'rounded-[6px] px-2 py-0.5 font-poppins text-[13px] font-bold transition',
							viewMode === 'months'
								? 'bg-[#183669] text-white shadow-xs'
								: 'text-[#183669] hover:bg-[#f0f4f9]'
						]"
						title="Pilih Bulan"
					>
						{{ monthNames[viewMonth] }}
					</button>

					<!-- Year Button -->
					<button
						type="button"
						@click="openYearView"
						:class="[
							'rounded-[6px] px-2 py-0.5 font-poppins text-[13px] font-bold transition',
							viewMode === 'years'
								? 'bg-[#183669] text-white shadow-xs'
								: 'text-[#183669] hover:bg-[#f0f4f9]'
						]"
						title="Pilih Tahun"
					>
						<span v-if="viewMode === 'years'">{{ yearRangeStart }} - {{ yearRangeStart + 11 }}</span>
						<span v-else>{{ viewYear }}</span>
					</button>
				</div>

				<!-- Next Button -->
				<button
					type="button"
					@click="handleNext"
					class="flex h-7 w-7 shrink-0 items-center justify-center rounded-[6px] text-[#4d6786] transition hover:bg-[#f0f4f9] hover:text-[#183669]"
					title="Berikutnya"
				>
					<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
					</svg>
				</button>
			</div>

			<!-- ================= VIEW 1: DAYS (Default Calendar View) ================= -->
			<div v-if="viewMode === 'days'">
				<!-- Days of Week Header -->
				<div class="mt-1.5 grid grid-cols-7 text-center">
					<span
						v-for="day in dayNames"
						:key="day"
						class="py-0.5 text-[10.5px] font-bold uppercase text-[#8ca1b9]"
					>
						{{ day }}
					</span>
				</div>

				<!-- Days Grid (7 columns) -->
				<div class="mt-0.5 grid grid-cols-7 gap-0.5 text-center">
					<button
						v-for="(d, idx) in calendarDays"
						:key="idx"
						type="button"
						@click="selectDate(d)"
						:class="[
							'flex h-7 sm:h-7.5 w-full items-center justify-center rounded-[6px] text-[12px] sm:text-[12.5px] transition font-medium',
							!d.isCurrentMonth ? 'text-[#cbd6e2] hover:bg-slate-50' : '',
							d.isCurrentMonth && !d.isSelected ? 'text-[#1e3456] hover:bg-[#f0f4f9] hover:text-[#183669]' : '',
							d.isToday && !d.isSelected ? 'ring-1 ring-[#183669] font-bold text-[#183669]' : '',
							d.isSelected ? 'bg-[#183669] font-bold text-white shadow-xs hover:bg-[#122b54]' : ''
						]"
					>
						{{ d.day }}
					</button>
				</div>
			</div>

			<!-- ================= VIEW 2: MONTHS PICKER (3x4 Grid) ================= -->
			<div v-else-if="viewMode === 'months'" class="py-2">
				<div class="grid grid-cols-3 gap-1.5 text-center">
					<button
						v-for="(mName, idx) in monthShortNames"
						:key="idx"
						type="button"
						@click="selectMonth(idx)"
						:class="[
							'flex h-9 items-center justify-center rounded-[8px] font-poppins text-[13px] font-medium transition',
							viewMonth === idx
								? 'bg-[#183669] font-bold text-white shadow-xs'
								: 'text-[#1e3456] hover:bg-[#f0f4f9] hover:text-[#183669]'
						]"
					>
						{{ mName }}
					</button>
				</div>
			</div>

			<!-- ================= VIEW 3: YEARS PICKER (3x4 Grid) ================= -->
			<div v-else-if="viewMode === 'years'" class="py-2">
				<div class="grid grid-cols-3 gap-1.5 text-center">
					<button
						v-for="y in decadeYears"
						:key="y"
						type="button"
						@click="selectYear(y)"
						:class="[
							'flex h-9 items-center justify-center rounded-[8px] font-poppins text-[13px] font-medium transition',
							viewYear === y
								? 'bg-[#183669] font-bold text-white shadow-xs'
								: 'text-[#1e3456] hover:bg-[#f0f4f9] hover:text-[#183669]'
						]"
					>
						{{ y }}
					</button>
				</div>
			</div>

			<!-- Footer Actions (Clear & Today) -->
			<div class="mt-2 flex items-center justify-between border-t border-[#f0f4f9] pt-1.5 font-inter text-[11.5px]">
				<button
					type="button"
					@click="clearDate"
					class="font-medium text-[#c93b2b] transition hover:text-[#9e2215] hover:underline"
				>
					Hapus
				</button>
				<button
					type="button"
					@click="selectToday"
					class="font-bold text-[#183669] transition hover:underline"
				>
					Hari Ini
				</button>
			</div>
		</div>
	</div>
</template>