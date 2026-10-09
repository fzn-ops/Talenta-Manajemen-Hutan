<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

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

// Daftar lengkap negara (Indonesia default, disusul negara-negara Asia dan internasional)
const countries = [
	// Asia Tenggara (ASEAN)
	{ code: 'ID', name: 'Indonesia', dial_code: '+62', placeholder: '812-3456-7890' },
	{ code: 'MY', name: 'Malaysia', dial_code: '+60', placeholder: '12-345-6789' },
	{ code: 'SG', name: 'Singapura (Singapore)', dial_code: '+65', placeholder: '8123-4567' },
	{ code: 'BN', name: 'Brunei Darussalam', dial_code: '+673', placeholder: '812-3456' },
	{ code: 'TH', name: 'Thailand', dial_code: '+66', placeholder: '81-234-5678' },
	{ code: 'PH', name: 'Filipina (Philippines)', dial_code: '+63', placeholder: '912-345-6789' },
	{ code: 'VN', name: 'Vietnam', dial_code: '+84', placeholder: '91-234-5678' },
	{ code: 'KH', name: 'Kamboja (Cambodia)', dial_code: '+855', placeholder: '12-345-678' },
	{ code: 'LA', name: 'Laos', dial_code: '+856', placeholder: '20-2345-6789' },
	{ code: 'MM', name: 'Myanmar', dial_code: '+95', placeholder: '9-234-5678' },
	{ code: 'TL', name: 'Timor-Leste', dial_code: '+670', placeholder: '7712-3456' },

	// Asia Selatan
	{ code: 'BD', name: 'Bangladesh', dial_code: '+880', placeholder: '1712-345678' },
	{ code: 'IN', name: 'India', dial_code: '+91', placeholder: '98765-43210' },
	{ code: 'PK', name: 'Pakistan', dial_code: '+92', placeholder: '301-2345678' },
	{ code: 'LK', name: 'Sri Lanka', dial_code: '+94', placeholder: '71-234-5678' },
	{ code: 'NP', name: 'Nepal', dial_code: '+977', placeholder: '984-1234567' },
	{ code: 'MV', name: 'Maladewa (Maldives)', dial_code: '+960', placeholder: '771-2345' },
	{ code: 'AF', name: 'Afghanistan', dial_code: '+93', placeholder: '70-123-4567' },

	// Asia Timur
	{ code: 'JP', name: 'Jepang (Japan)', dial_code: '+81', placeholder: '90-1234-5678' },
	{ code: 'KR', name: 'Korea Selatan (South Korea)', dial_code: '+82', placeholder: '10-1234-5678' },
	{ code: 'CN', name: 'Tiongkok (China)', dial_code: '+86', placeholder: '138-0013-8000' },
	{ code: 'TW', name: 'Taiwan', dial_code: '+886', placeholder: '912-345-678' },
	{ code: 'HK', name: 'Hong Kong', dial_code: '+852', placeholder: '9123-4567' },
	{ code: 'MO', name: 'Makau (Macau)', dial_code: '+853', placeholder: '6123-4567' },
	{ code: 'MN', name: 'Mongolia', dial_code: '+976', placeholder: '8812-3456' },

	// Timur Tengah & Asia Barat
	{ code: 'SA', name: 'Arab Saudi (Saudi Arabia)', dial_code: '+966', placeholder: '50-123-4567' },
	{ code: 'AE', name: 'Uni Emirat Arab (UAE)', dial_code: '+971', placeholder: '50-123-4567' },
	{ code: 'QA', name: 'Qatar', dial_code: '+974', placeholder: '3312-3456' },
	{ code: 'KW', name: 'Kuwait', dial_code: '+965', placeholder: '9123-4567' },
	{ code: 'BH', name: 'Bahrain', dial_code: '+973', placeholder: '3912-3456' },
	{ code: 'OM', name: 'Oman', dial_code: '+968', placeholder: '9123-4567' },
	{ code: 'YE', name: 'Yaman (Yemen)', dial_code: '+967', placeholder: '71-234-5678' },
	{ code: 'JO', name: 'Yordania (Jordan)', dial_code: '+962', placeholder: '7-9012-3456' },
	{ code: 'LB', name: 'Lebanon', dial_code: '+961', placeholder: '70-123-456' },
	{ code: 'IQ', name: 'Irak (Iraq)', dial_code: '+964', placeholder: '790-123-4567' },
	{ code: 'IR', name: 'Iran', dial_code: '+98', placeholder: '912-345-6789' },
	{ code: 'PS', name: 'Palestina (Palestine)', dial_code: '+970', placeholder: '59-912-3456' },
	{ code: 'SY', name: 'Suriah (Syria)', dial_code: '+963', placeholder: '944-123-456' },
	{ code: 'TR', name: 'Turki (Turkey)', dial_code: '+90', placeholder: '532-123-4567' },

	// Asia Tengah
	{ code: 'UZ', name: 'Uzbekistan', dial_code: '+998', placeholder: '90-123-4567' },
	{ code: 'KZ', name: 'Kazakhstan', dial_code: '+7', placeholder: '701-234-5678' },

	// Negara Internasional Populer
	{ code: 'AU', name: 'Australia', dial_code: '+61', placeholder: '412-345-678' },
	{ code: 'GB', name: 'Inggris (United Kingdom)', dial_code: '+44', placeholder: '7123-456789' },
	{ code: 'US', name: 'Amerika Serikat (USA)', dial_code: '+1', placeholder: '202-555-0123' },
	{ code: 'DE', name: 'Jerman (Germany)', dial_code: '+49', placeholder: '151-23456789' },
	{ code: 'NL', name: 'Belanda (Netherlands)', dial_code: '+31', placeholder: '6-12345678' },
	{ code: 'EG', name: 'Mesir (Egypt)', dial_code: '+20', placeholder: '10-1234-5678' },
	{ code: 'FR', name: 'Prancis (France)', dial_code: '+33', placeholder: '6-12-34-56-78' },
	{ code: 'IT', name: 'Italia (Italy)', dial_code: '+39', placeholder: '312-345-6789' },
	{ code: 'ES', name: 'Spanyol (Spain)', dial_code: '+34', placeholder: '612-34-56-78' },
	{ code: 'CA', name: 'Kanada (Canada)', dial_code: '+1', placeholder: '416-555-0123' },
	{ code: 'RU', name: 'Rusia (Russia)', dial_code: '+7', placeholder: '912-345-67-89' },
	{ code: 'BR', name: 'Brasil (Brazil)', dial_code: '+55', placeholder: '11-91234-5678' },
	{ code: 'ZA', name: 'Afrika Selatan (South Africa)', dial_code: '+27', placeholder: '82-123-4567' },
];

const getFlagUrl = (code) => {
	return `https://flagcdn.com/w40/${code.toLowerCase()}.png`;
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
	const clean = digits.replace(/\D/g, '');
	if (clean.length <= 3) return clean;
	if (clean.length <= 7) return `${clean.slice(0, 3)}-${clean.slice(3)}`;
	if (clean.length <= 11) return `${clean.slice(0, 3)}-${clean.slice(3, 7)}-${clean.slice(7)}`;
	return `${clean.slice(0, 3)}-${clean.slice(3, 7)}-${clean.slice(7, 11)}-${clean.slice(11, 15)}`;
};

// Parse modelValue saat inisialisasi atau berubah dari parent
const parseIncomingValue = (val) => {
	if (!val) {
		nationalNumber.value = '';
		return;
	}

	const strVal = String(val).trim();

	// Cek apakah diawali kode dial negara terdaftar (misal: +62 812..., +60...)
	let matched = null;
	const sorted = [...countries].sort((a, b) => b.dial_code.length - a.dial_code.length);
	for (const c of sorted) {
		if (strVal.startsWith(c.dial_code)) {
			matched = c;
			const remaining = strVal.slice(c.dial_code.length).trim();
			selectedCountry.value = c;
			nationalNumber.value = formatPhoneNumber(remaining);
			return;
		}
	}

	// Jika tidak ada dial_code di depan (misal: "08123456789" atau "8123456789")
	let clean = strVal.replace(/\D/g, '');
	if (clean.startsWith('0')) {
		clean = clean.slice(1);
	}
	nationalNumber.value = formatPhoneNumber(clean);
};

watch(
	() => props.modelValue,
	(newVal) => {
		const currentFull = selectedCountry.value.dial_code + ' ' + nationalNumber.value;
		if (newVal !== currentFull && newVal !== nationalNumber.value) {
			parseIncomingValue(newVal);
		}
	},
	{ immediate: true }
);

// Filter negara pada dropdown pencarian
const filteredCountries = computed(() => {
	const q = searchQuery.value.trim().toLowerCase();
	if (!q) return countries;
	return countries.filter((c) =>
		c.name.toLowerCase().includes(q) ||
		c.dial_code.toLowerCase().includes(q) ||
		c.code.toLowerCase().includes(q)
	);
});

// Buka/Tutup dropdown dengan kalkulasi posisi otomatis
const toggleDropdown = () => {
	if (props.readonly || props.disabled) return;
	isDropdownOpen.value = !isDropdownOpen.value;
	if (isDropdownOpen.value) {
		searchQuery.value = '';
		if (props.placement === 'top') {
			isDropUp.value = true;
		} else if (props.placement === 'bottom') {
			isDropUp.value = false;
		} else if (dropdownRef.value) {
			const rect = dropdownRef.value.getBoundingClientRect();
			const spaceBelow = window.innerHeight - rect.bottom;
			isDropUp.value = spaceBelow < 260 && rect.top > 260;
		}
		nextTick(() => {
			searchInputRef.value?.focus();
		});
	}
};

const selectCountry = (country) => {
	selectedCountry.value = country;
	isDropdownOpen.value = false;
	emitValue();
	nextTick(() => {
		phoneInputRef.value?.focus();
	});
};

// Input Handlers
const onInput = (e) => {
	const raw = e.target.value;
	let cleanDigits = raw.replace(/\D/g, '');

	// Hilangkan awalan '0' jika user terbiasa mengetik format lokal '08xx'
	if (cleanDigits.startsWith('0')) {
		cleanDigits = cleanDigits.slice(1);
	}

	const formatted = formatPhoneNumber(cleanDigits);
	nationalNumber.value = formatted;
	e.target.value = formatted;
	emitValue();
};

const onKeyPress = (e) => {
	if (!/[\d]/.test(e.key)) {
		e.preventDefault();
	}
};

const onKeyDown = (e) => {
	if (e.key === 'Backspace') {
		const input = e.target;
		const start = input.selectionStart;
		const end = input.selectionEnd;
		if (start === end && start > 0) {
			const charBefore = nationalNumber.value[start - 1];
			if (charBefore === '-') {
				e.preventDefault();
				const clean = (
					nationalNumber.value.slice(0, start - 2) +
					nationalNumber.value.slice(start)
				).replace(/\D/g, '');
				const formatted = formatPhoneNumber(clean);
				nationalNumber.value = formatted;
				input.value = formatted;
				emitValue();
				nextTick(() => {
					const newPos = Math.max(0, start - 2);
					input.setSelectionRange(newPos, newPos);
				});
			}
		}
	}
};

const onPaste = (e) => {
	e.preventDefault();
	const text = e.clipboardData?.getData('text') || '';
	let digits = text.replace(/\D/g, '');

	for (const c of countries) {
		const pureDial = c.dial_code.replace(/\D/g, '');
		if (digits.startsWith(pureDial)) {
			selectedCountry.value = c;
			digits = digits.slice(pureDial.length);
			break;
		}
	}

	if (digits.startsWith('0')) {
		digits = digits.slice(1);
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
	// Format tersimpan: '+62 812-3456-7890'
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
				? 'border border-red-400 bg-red-50/20 hover:border-red-400 focus-within:!border-red-500'
				: readonly || disabled
				? 'border border-[#d6e0ee] bg-[#f0f4f9] cursor-not-allowed select-none'
				: 'border border-[#d6e0ee] bg-white hover:border-[#a6b7cb] hover:bg-[#fafcff] focus-within:!border-[#183669] focus-within:!bg-white'
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
			<!-- CDN Flag Icon Image -->
			<span class="inline-flex items-center shrink-0">
				<img
					:src="getFlagUrl(selectedCountry.code)"
					:alt="selectedCountry.name"
					class="h-3.5 w-5 rounded-[2px] object-cover shadow-xs border border-slate-200"
					loading="lazy"
				/>
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

		<!-- Input Angka Handphone -->
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

		<!-- Slot Tombol Aksi Tambahan (Edit/Pencil Icon) -->
		<div v-if="$slots.append" class="absolute inset-y-0 right-0 z-10 flex items-center pr-2.5 pointer-events-auto">
			<slot name="append"></slot>
		</div>

		<!-- Popover Dropdown Pilihan Negara -->
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
						class="phone-search-input h-[34px] w-full rounded-[8px] border border-[#d6e0ee] bg-[#f8fafc] pl-8 pr-2.5 font-inter text-[12px] text-[#173a63] placeholder-[#8c9eb5] transition focus:border-[#183669] focus:bg-white focus:outline-none"
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
							<img
								:src="getFlagUrl(country.code)"
								:alt="country.name"
								class="h-3.5 w-5 rounded-[2px] object-cover shadow-xs border border-slate-200 shrink-0"
								loading="lazy"
							/>
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
/* Reset total dari @tailwindcss/forms pada search input */
.phone-search-input {
	outline: none !important;
	box-shadow: none !important;
	--tw-ring-shadow: none !important;
	--tw-ring-offset-shadow: none !important;
	--tw-ring-color: transparent !important;
	--tw-ring-offset-color: transparent !important;
}

.phone-search-input:focus,
.phone-search-input:focus-visible,
.phone-search-input:active {
	outline: none !important;
	box-shadow: none !important;
	--tw-ring-shadow: none !important;
	--tw-ring-offset-shadow: none !important;
	--tw-ring-color: transparent !important;
	--tw-ring-offset-color: transparent !important;
	border-color: #183669 !important;
}

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
