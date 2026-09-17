<script setup>
import { computed, h, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
	modelValue: {
		type: [String, Number],
		default: '',
	},
	placeholder: {
		type: String,
		default: '',
	},
	readonly: {
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
	heightClass: {
		type: String,
		default: 'h-[46px]',
	},
	placement: {
		type: String,
		default: 'auto', // 'auto' | 'top' | 'bottom'
	},
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

// Daftar kode negara umum dengan nama (Indonesia default)
const countries = [
	{ code: 'ID', name: 'Indonesia', dial_code: '+62', placeholder: '812-3456-7890' },
	{ code: 'MY', name: 'Malaysia', dial_code: '+60', placeholder: '12-345-6789' },
	{ code: 'SG', name: 'Singapura (Singapore)', dial_code: '+65', placeholder: '8123-4567' },
	{ code: 'BN', name: 'Brunei Darussalam', dial_code: '+673', placeholder: '812-3456' },
	{ code: 'TH', name: 'Thailand', dial_code: '+66', placeholder: '81-234-5678' },
	{ code: 'PH', name: 'Filipina (Philippines)', dial_code: '+63', placeholder: '912-345-6789' },
	{ code: 'VN', name: 'Vietnam', dial_code: '+84', placeholder: '91-234-5678' },
	{ code: 'AU', name: 'Australia', dial_code: '+61', placeholder: '412-345-678' },
	{ code: 'JP', name: 'Jepang (Japan)', dial_code: '+81', placeholder: '90-1234-5678' },
	{ code: 'KR', name: 'Korea Selatan (South Korea)', dial_code: '+82', placeholder: '10-1234-5678' },
	{ code: 'SA', name: 'Arab Saudi (Saudi Arabia)', dial_code: '+966', placeholder: '50-123-4567' },
	{ code: 'GB', name: 'Inggris (United Kingdom)', dial_code: '+44', placeholder: '7123-456789' },
	{ code: 'US', name: 'Amerika Serikat (USA)', dial_code: '+1', placeholder: '202-555-0123' },
	{ code: 'DE', name: 'Jerman (Germany)', dial_code: '+49', placeholder: '151-23456789' },
	{ code: 'NL', name: 'Belanda (Netherlands)', dial_code: '+31', placeholder: '6-12345678' },
	{ code: 'CN', name: 'Tiongkok (China)', dial_code: '+86', placeholder: '138-0013-8000' },
	{ code: 'TW', name: 'Taiwan', dial_code: '+886', placeholder: '912-345-678' },
	{ code: 'IN', name: 'India', dial_code: '+91', placeholder: '98765-43210' },
	{ code: 'TR', name: 'Turki (Turkey)', dial_code: '+90', placeholder: '532-123-4567' },
	{ code: 'EG', name: 'Mesir (Egypt)', dial_code: '+20', placeholder: '10-1234-5678' },
];

// SVG Flags renderer agar tampil tajam dan berwarna di semua OS (termasuk Windows)
const flagSvgMap = {
	ID: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('path', { fill: '#E70011', d: 'M0 0h640v240H0z' }),
			h('path', { fill: '#FFFFFF', d: 'M0 240h640v240H0z' }),
		]),
	MY: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('path', { fill: '#CC0000', d: 'M0 0h640v480H0z' }),
			h('path', { stroke: '#fff', 'stroke-width': '34', d: 'M0 51h640M0 119h640M0 187h640M0 255h640M0 323h640M0 391h640M0 459h640' }),
			h('rect', { width: '320', height: '272', fill: '#000066' }),
			h('circle', { cx: '160', cy: '136', r: '80', fill: '#FFCC00' }),
			h('circle', { cx: '180', cy: '136', r: '68', fill: '#000066' }),
		]),
	SG: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('path', { fill: '#ED2939', d: 'M0 0h640v240H0z' }),
			h('path', { fill: '#FFFFFF', d: 'M0 240h640v240H0z' }),
			h('circle', { cx: '150', cy: '120', r: '60', fill: '#FFFFFF' }),
			h('circle', { cx: '168', cy: '120', r: '60', fill: '#ED2939' }),
		]),
	BN: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#F7E017' }),
			h('polygon', { points: '0,0 640,360 640,480 0,120', fill: '#FFFFFF' }),
			h('polygon', { points: '0,60 640,420 640,480 0,120', fill: '#000000' }),
		]),
	TH: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#A51931' }),
			h('rect', { y: '80', width: '640', height: '320', fill: '#F4F5F8' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#2D2A4A' }),
		]),
	PH: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '240', fill: '#0038A8' }),
			h('rect', { y: '240', width: '640', height: '240', fill: '#CE1126' }),
			h('polygon', { points: '0,0 280,240 0,480', fill: '#FFFFFF' }),
		]),
	VN: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#DA251D' }),
			h('polygon', { points: '320,120 355,230 470,230 378,298 412,410 320,342 228,410 262,298 170,230 285,230', fill: '#FFFF00' }),
		]),
	AU: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#00008B' }),
			h('rect', { width: '320', height: '240', fill: '#012169' }),
			h('path', { stroke: '#fff', 'stroke-width': '40', d: 'M0 0l320 240M320 0L0 240' }),
			h('path', { stroke: '#C8102E', 'stroke-width': '24', d: 'M0 0l320 240M320 0L0 240' }),
			h('path', { stroke: '#fff', 'stroke-width': '60', d: 'M160 0v240M0 120h320' }),
			h('path', { stroke: '#C8102E', 'stroke-width': '36', d: 'M160 0v240M0 120h320' }),
		]),
	JP: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#FFFFFF' }),
			h('circle', { cx: '320', cy: '240', r: '144', fill: '#BC002D' }),
		]),
	KR: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#FFFFFF' }),
			h('circle', { cx: '320', cy: '240', r: '100', fill: '#CD2E3A' }),
			h('path', { d: 'M220 240a100 100 0 0 0 200 0c0 55-45 100-100 100s-100-45-100-100z', fill: '#0047A0' }),
		]),
	SA: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#006C35' }),
			h('rect', { x: '180', y: '330', width: '280', height: '20', rx: '10', fill: '#FFFFFF' }),
			h('text', { x: '50%', y: '50%', 'text-anchor': 'middle', fill: '#fff', 'font-size': '110', 'font-weight': 'bold', 'font-family': 'sans-serif' }, 'SA'),
		]),
	GB: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#012169' }),
			h('path', { stroke: '#fff', 'stroke-width': '80', d: 'M0 0l640 480M640 0L0 480' }),
			h('path', { stroke: '#C8102E', 'stroke-width': '48', d: 'M0 0l640 480M640 0L0 480' }),
			h('path', { stroke: '#fff', 'stroke-width': '120', d: 'M320 0v480M0 240h640' }),
			h('path', { stroke: '#C8102E', 'stroke-width': '72', d: 'M320 0v480M0 240h640' }),
		]),
	US: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#B22234' }),
			h('path', { stroke: '#fff', 'stroke-width': '37', d: 'M0 55h640M0 129h640M0 203h640M0 277h640M0 351h640M0 425h640' }),
			h('rect', { width: '260', height: '260', fill: '#3C3B6E' }),
		]),
	DE: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#000000' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#DD0000' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#FFCE00' }),
		]),
	NL: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#AE1C28' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#FFFFFF' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#21468B' }),
		]),
	CN: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#DE2910' }),
			h('circle', { cx: '110', cy: '110', r: '40', fill: '#FFDE00' }),
		]),
	TW: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#FE0000' }),
			h('rect', { width: '320', height: '240', fill: '#000095' }),
			h('circle', { cx: '160', cy: '120', r: '50', fill: '#FFFFFF' }),
		]),
	IN: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#FF9933' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#FFFFFF' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#138808' }),
			h('circle', { cx: '320', cy: '240', r: '50', fill: '#000080' }),
		]),
	TR: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '480', fill: '#E30A17' }),
			h('circle', { cx: '280', cy: '240', r: '120', fill: '#FFFFFF' }),
			h('circle', { cx: '310', cy: '240', r: '96', fill: '#E30A17' }),
		]),
	EG: () =>
		h('svg', { viewBox: '0 0 640 480', class: 'h-3.5 w-5 rounded-[2px] shadow-xs shrink-0 border border-slate-200' }, [
			h('rect', { width: '640', height: '160', fill: '#CE1126' }),
			h('rect', { y: '160', width: '640', height: '160', fill: '#FFFFFF' }),
			h('rect', { y: '320', width: '640', height: '160', fill: '#000000' }),
			h('circle', { cx: '320', cy: '240', r: '35', fill: '#C09300' }),
		]),
};

const getFlagComponent = (code) => {
	return flagSvgMap[code] || flagSvgMap.ID;
};

const selectedCountry = ref(countries[0]); // Default: Indonesia (+62)
const nationalNumber = ref('');
const isDropdownOpen = ref(false);
const isDropUp = ref(false);
const searchQuery = ref('');
const dropdownRef = ref(null);
const searchInputRef = ref(null);
const phoneInputRef = ref(null);

// Format nomor telepon otomatis dengan pemisah: 3 digit - 4 digit - sisa digit
const formatPhoneNumber = (digits) => {
	if (!digits) return '';
	if (digits.length <= 3) {
		return digits;
	}
	if (digits.length <= 7) {
		return `${digits.slice(0, 3)}-${digits.slice(3)}`;
	}
	return `${digits.slice(0, 3)}-${digits.slice(3, 7)}-${digits.slice(7, 13)}`;
};

// Parse input string saat pertama kali load atau saat prop modelValue berubah
const parsePhoneNumber = (val) => {
	if (!val || val === '-') {
		nationalNumber.value = '';
		return;
	}

	const str = String(val).trim();

	// Cek apakah string diawali kode negara (+XX)
	let matchedCountry = null;
	const sorted = [...countries].sort((a, b) => b.dial_code.length - a.dial_code.length);
	for (const c of sorted) {
		if (str.startsWith(c.dial_code)) {
			matchedCountry = c;
			break;
		}
	}

	let digits = '';
	if (matchedCountry) {
		selectedCountry.value = matchedCountry;
		digits = str.slice(matchedCountry.dial_code.length).replace(/\D/g, '');
	} else if (str.startsWith('0')) {
		selectedCountry.value = countries[0];
		digits = str.replace(/\D/g, '').replace(/^0+/, '');
	} else if (str.startsWith('62')) {
		selectedCountry.value = countries[0];
		digits = str.slice(2).replace(/\D/g, '');
	} else {
		selectedCountry.value = countries[0];
		digits = str.replace(/\D/g, '');
	}

	if (digits.startsWith('0')) {
		digits = digits.replace(/^0+/, '');
	}
	if (digits.length > 13) {
		digits = digits.slice(0, 13);
	}

	nationalNumber.value = formatPhoneNumber(digits);
};

watch(
	() => props.modelValue,
	(newVal) => {
		const currentFull = nationalNumber.value ? `${selectedCountry.value.dial_code} ${nationalNumber.value}` : '';
		if (String(newVal).replace(/[\s-]/g, '') !== currentFull.replace(/[\s-]/g, '')) {
			parsePhoneNumber(newVal);
		}
	},
	{ immediate: true }
);

// Filter pencarian negara
const filteredCountries = computed(() => {
	const q = searchQuery.value.toLowerCase().trim();
	if (!q) return countries;
	return countries.filter(
		(c) =>
			c.name.toLowerCase().includes(q) ||
			c.dial_code.includes(q) ||
			c.code.toLowerCase().includes(q)
	);
});

// Hitung posisi buka dropdown (ke atas jika ruang bawah tidak cukup atau jika placement='top')
const calculatePlacement = () => {
	if (props.placement === 'top') {
		isDropUp.value = true;
		return;
	}
	if (props.placement === 'bottom') {
		isDropUp.value = false;
		return;
	}

	// Mode auto: cek sisa ruang dari elemen ke batas bawah modal / container terdekat
	if (dropdownRef.value) {
		const rect = dropdownRef.value.getBoundingClientRect();
		let containerBottom = window.innerHeight;

		// Cari ancestor yang memiliki scroll/modal wrapper
		let parent = dropdownRef.value.parentElement;
		while (parent && parent !== document.body) {
			const style = window.getComputedStyle(parent);
			const overflowY = style.overflowY || style.overflow;
			if (overflowY === 'auto' || overflowY === 'scroll' || overflowY === 'hidden') {
				const parentRect = parent.getBoundingClientRect();
				containerBottom = Math.min(containerBottom, parentRect.bottom);
				break;
			}
			parent = parent.parentElement;
		}

		const spaceBelow = containerBottom - rect.bottom;
		const dropdownHeight = 270;

		// Jika ruang di bawah kurang dari 270px, otomatis buka ke atas
		isDropUp.value = spaceBelow < dropdownHeight;
	}
};

const toggleDropdown = () => {
	if (props.readonly || props.disabled) return;
	const willOpen = !isDropdownOpen.value;
	if (willOpen) {
		calculatePlacement();
	}
	isDropdownOpen.value = willOpen;
	if (isDropdownOpen.value) {
		searchQuery.value = '';
		nextTick(() => {
			searchInputRef.value?.focus();
		});
	}
};

const selectCountry = (c) => {
	selectedCountry.value = c;
	isDropdownOpen.value = false;
	searchQuery.value = '';
	emitValue();
	nextTick(() => {
		phoneInputRef.value?.focus();
	});
};

// Hanya izinkan angka saat tombol ditekan (angka 0-9)
const onKeyPress = (e) => {
	if (props.readonly || props.disabled) return;
	const char = String.fromCharCode(e.which || e.keyCode);
	if (!/^[0-9]$/.test(char)) {
		e.preventDefault();
	}
};

// Handle tombol Backspace agar nyaman saat melewati tanda minus '-'
const onKeyDown = (e) => {
	if (props.readonly || props.disabled) return;

	if (e.key === 'Backspace') {
		const input = e.target;
		const selStart = input.selectionStart;
		const selEnd = input.selectionEnd;

		// Jika kursor tunggal tepat setelah karakter '-'
		if (selStart === selEnd && selStart > 0 && input.value[selStart - 1] === '-') {
			e.preventDefault();
			const val = input.value;
			const beforeMinus = val.slice(0, selStart - 2);
			const afterMinus = val.slice(selStart);
			let digits = (beforeMinus + afterMinus).replace(/\D/g, '');
			if (digits.startsWith('0')) digits = digits.replace(/^0+/, '');
			const formatted = formatPhoneNumber(digits);
			nationalNumber.value = formatted;
			if (phoneInputRef.value) {
				phoneInputRef.value.value = formatted;
			}
			emitValue();
			nextTick(() => {
				const newPos = Math.max(0, selStart - 2);
				input.setSelectionRange(newPos, newPos);
			});
		}
	}
};

// Saat user paste teks
const onPaste = (e) => {
	if (props.readonly || props.disabled) return;
	e.preventDefault();
	const pasted = (e.clipboardData || window.clipboardData)?.getData('text') || '';
	if (!pasted) return;

	const trimmed = pasted.trim();
	let matched = null;
	const sorted = [...countries].sort((a, b) => b.dial_code.length - a.dial_code.length);
	for (const c of sorted) {
		if (trimmed.startsWith(c.dial_code)) {
			matched = c;
			break;
		}
	}

	let digits = '';
	if (matched) {
		selectedCountry.value = matched;
		digits = trimmed.slice(matched.dial_code.length).replace(/\D/g, '');
	} else {
		digits = trimmed.replace(/\D/g, '');
		if (digits.startsWith('62')) {
			selectedCountry.value = countries[0];
			digits = digits.slice(2);
		}
	}

	if (digits.startsWith('0')) {
		digits = digits.replace(/^0+/, '');
	}
	if (digits.length > 13) {
		digits = digits.slice(0, 13);
	}

	const formatted = formatPhoneNumber(digits);
	nationalNumber.value = formatted;
	if (phoneInputRef.value) {
		phoneInputRef.value.value = formatted;
	}
	emitValue();
};

// Saat user mengetik
const onInput = (e) => {
	let val = e.target.value;
	let digits = val.replace(/\D/g, '');

	// Hapus angka 0 di awal jika diketik
	if (digits.startsWith('0')) {
		digits = digits.replace(/^0+/, '');
	}

	// Maksimal 13 digit
	if (digits.length > 13) {
		digits = digits.slice(0, 13);
	}

	const formatted = formatPhoneNumber(digits);
	nationalNumber.value = formatted;
	if (phoneInputRef.value) {
		phoneInputRef.value.value = formatted;
	}
	emitValue();
};

const emitValue = () => {
	if (!nationalNumber.value) {
		emit('update:modelValue', '');
		return;
	}
	// Format tersimpan: '+62 895-6228-15861'
	const formatted = `${selectedCountry.value.dial_code} ${nationalNumber.value}`;
	emit('update:modelValue', formatted);
};

const onBlur = (e) => {
	emit('blur', e);
};

const onFocus = (e) => {
	emit('focus', e);
};

// Tutup dropdown jika klik di luar
const handleClickOutside = (e) => {
	if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
		isDropdownOpen.value = false;
	}
};

onMounted(() => {
	document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
	document.removeEventListener('click', handleClickOutside);
});

defineExpose({
	focus: () => phoneInputRef.value?.focus(),
	blur: () => phoneInputRef.value?.blur(),
});
</script>

<template>
	<div
		ref="dropdownRef"
		:class="[
			'phone-container relative flex w-full items-center rounded-[10px] transition-colors duration-150',
			heightClass,
			hasError
				? 'border-[1.5px] border-red-400 bg-red-50/20'
				: readonly || disabled
				? 'border-[1.5px] border-[#d6e0ee] bg-[#f0f4f9] cursor-not-allowed select-none'
				: 'border-[1.5px] border-[#d6e0ee] bg-white hover:border-[#183669] focus-within:!border-[#183669]'
		]"
	>
		<!-- Tombol Pemilih Kode Negara (National Calling Code) -->
		<button
			type="button"
			@mousedown.prevent
			@click.stop="toggleDropdown"
			:disabled="readonly || disabled"
			:tabindex="readonly || disabled ? -1 : 0"
			class="flex h-full shrink-0 items-center gap-2 pl-3.5 pr-2.5 font-inter text-[14px] font-medium text-[#183669] transition hover:bg-slate-100/60 rounded-l-[9px] focus:outline-none disabled:cursor-not-allowed disabled:hover:bg-transparent cursor-pointer"
			:title="`Pilih kode negara (${selectedCountry.name} ${selectedCountry.dial_code})`"
		>
			<!-- Real SVG Flag Icon -->
			<span class="inline-flex items-center shrink-0">
				<component :is="getFlagComponent(selectedCountry.code)" />
			</span>
			<span class="select-none tracking-tight leading-none text-[#183669] font-medium">{{ selectedCountry.dial_code }}</span>
			<svg
				:class="[
					'h-3.5 w-3.5 text-[#7188a3] transition-transform duration-150 shrink-0',
					isDropUp ? (isDropdownOpen ? 'text-[#183669]' : 'rotate-180') : (isDropdownOpen ? 'rotate-180 text-[#183669]' : '')
				]"
				fill="none"
				stroke="currentColor"
				stroke-width="2.2"
				viewBox="0 0 24 24"
			>
				<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
			</svg>
		</button>

		<!-- Garis Pemisah Antara Kode Negara dan Input -->
		<div class="h-5 w-[1px] bg-[#d6e0ee] shrink-0" aria-hidden="true"></div>

		<!-- Input Angka Handphone (Tanpa border dalam, tanpa ring biru tailwind, sejajar sempurna) -->
		<input
			ref="phoneInputRef"
			type="tel"
			inputmode="numeric"
			:value="nationalNumber"
			:readonly="readonly"
			:disabled="disabled"
			:placeholder="placeholder || selectedCountry.placeholder"
			@keypress="onKeyPress"
			@keydown="onKeyDown"
			@paste="onPaste"
			@input="onInput"
			@blur="onBlur"
			@focus="onFocus"
			:class="[
				'phone-raw-input h-full flex-1 border-0 bg-transparent pl-3 font-inter text-[14px] text-[#173a63] placeholder-[#a8bed4] leading-normal',
				$slots.append ? 'pr-11' : 'pr-3',
				readonly || disabled ? 'cursor-not-allowed select-none' : ''
			]"
		/>

		<!-- Slot Tombol Aksi Tambahan (Edit/Gembok: Menyatu di dalam container tanpa pemisah) -->
		<div v-if="$slots.append" class="absolute inset-y-0 right-0 z-10 flex items-center pr-2.5 pointer-events-auto">
			<slot name="append"></slot>
		</div>

		<!-- Popover Dropdown Pilihan Negara (Auto Drop-Up / Drop-Down) -->
		<Transition
			enter-active-class="transition duration-150 ease-out"
			:enter-from-class="isDropUp ? 'transform scale-95 opacity-0 translate-y-1' : 'transform scale-95 opacity-0 -translate-y-1'"
			enter-to-class="transform scale-100 opacity-100 translate-y-0"
			leave-active-class="transition duration-100 ease-in"
			leave-from-class="transform scale-100 opacity-100 translate-y-0"
			:leave-to-class="isDropUp ? 'transform scale-95 opacity-0 translate-y-1' : 'transform scale-95 opacity-0 -translate-y-1'"
		>
			<div
				v-if="isDropdownOpen"
				:class="[
					'absolute left-0 z-50 w-[280px] sm:w-[320px] rounded-[12px] border border-[#d6e0ee] bg-white p-2 shadow-2xl ring-1 ring-black/10',
					isDropUp ? 'bottom-full mb-2' : 'top-full mt-2'
				]"
			>
				<!-- Pencarian Negara -->
				<div class="relative mb-2">
					<span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-[#7188a3]">
						<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
						</svg>
					</span>
					<input
						ref="searchInputRef"
						v-model="searchQuery"
						type="text"
						placeholder="Cari negara atau kode..."
						class="h-[34px] w-full rounded-[8px] border border-[#d6e0ee] bg-[#f8fafc] pl-8 pr-2.5 font-inter text-[12px] text-[#173a63] placeholder-[#8c9eb5] transition focus:border-[#183669] focus:bg-white focus:outline-none"
					/>
				</div>

				<!-- Daftar Negara -->
				<div class="max-h-[220px] overflow-y-auto divide-y divide-slate-50 pr-0.5">
					<button
						v-for="country in filteredCountries"
						:key="country.code"
						type="button"
						@click.stop="selectCountry(country)"
						:class="[
							'flex w-full items-center justify-between px-2.5 py-2 rounded-[8px] text-left font-inter transition-colors text-[13px] cursor-pointer',
							selectedCountry.code === country.code
								? 'bg-[#183669]/10 font-semibold text-[#183669]'
								: 'text-[#2c4363] hover:bg-[#f1f5f9]'
						]"
					>
						<span class="flex items-center gap-2.5 truncate">
							<component :is="getFlagComponent(country.code)" />
							<span class="truncate">{{ country.name }}</span>
						</span>
						<span class="font-mono text-[12px] text-[#7188a3] ml-2 shrink-0 font-medium">
							{{ country.dial_code }}
						</span>
					</button>

					<div
						v-if="filteredCountries.length === 0"
						class="py-4 text-center font-inter text-[12px] text-[#7188a3]"
					>
						Negara tidak ditemukan
					</div>
				</div>
			</div>
		</Transition>
	</div>
</template>

<style scoped>
/* Reset total dari @tailwindcss/forms pada input nomor */
.phone-raw-input {
	border: 0 !important;
	border-width: 0 !important;
	border-style: none !important;
	outline: none !important;
	box-shadow: none !important;
	--tw-ring-shadow: none !important;
	--tw-ring-offset-shadow: none !important;
	--tw-ring-color: transparent !important;
	--tw-ring-offset-color: transparent !important;
	background-color: transparent !important;
	border-radius: 0 !important;
	appearance: none !important;
	-webkit-appearance: none !important;
}

.phone-raw-input:focus,
.phone-raw-input:focus-visible,
.phone-raw-input:focus-within,
.phone-raw-input:active {
	border: 0 !important;
	border-width: 0 !important;
	border-style: none !important;
	outline: none !important;
	box-shadow: none !important;
	--tw-ring-shadow: none !important;
	--tw-ring-offset-shadow: none !important;
	--tw-ring-color: transparent !important;
	--tw-ring-offset-color: transparent !important;
	border-color: transparent !important;
}

.phone-container:focus-within {
	box-shadow: none !important;
	outline: none !important;
	--tw-ring-shadow: none !important;
	--tw-ring-color: transparent !important;
}
</style>
