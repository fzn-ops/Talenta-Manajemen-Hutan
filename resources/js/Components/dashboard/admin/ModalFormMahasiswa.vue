<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import PhoneInput from '@/Components/dashboard/PhoneInput.vue';

const props = defineProps({
	show: {
		type: Boolean,
		default: false,
	},
	isEditing: {
		type: Boolean,
		default: false,
	},
	initialData: {
		type: Object,
		default: () => ({ nim: '', name: '', angkatan: '', talent_mapping: '', username: '', email: '', phone: '', password: '' }),
	},
	editingId: {
		type: [Number, String, null],
		default: null,
	},
	existingStudents: {
		type: Array,
		default: () => [],
	},
	loading: {
		type: Boolean,
		default: false,
	},
});

const emit = defineEmits(['close', 'submit']);

const talentMappingOptions = ['Profesional', 'Bisnis', 'Birokrat', 'Akademisi'];

const form = ref({
	nim: '',
	name: '',
	angkatan: '',
	talent_mapping: '',
	username: '',
	password: '',
	email: '',
	phone: '',
});

const formError = ref('');
const errors = ref({});
const showPassword = ref(false);
const isPasswordManuallyEdited = ref(false);
const isTalentDropdownOpen = ref(false);
const talentDropdownRef = ref(null);

const selectTalentMapping = (option) => {
	form.value.talent_mapping = option;
	isTalentDropdownOpen.value = false;
	if (errors.value.talent_mapping) {
		delete errors.value.talent_mapping;
	}
};

const handleDocumentClick = (e) => {
	if (talentDropdownRef.value && !talentDropdownRef.value.contains(e.target)) {
		isTalentDropdownOpen.value = false;
	}
};

// Auto-fill password with NIM if not manually modified
watch(
	() => form.value.nim,
	(newNim) => {
		if (!props.isEditing && !isPasswordManuallyEdited.value) {
			form.value.password = newNim;
		}
	}
);

watch(
	() => props.show,
	(isOpen) => {
		if (isOpen) {
			formError.value = '';
			errors.value = {};
			showPassword.value = false;
			isPasswordManuallyEdited.value = false;

			isTalentDropdownOpen.value = false;
			const currentNim = props.initialData?.nim !== '-' ? (props.initialData?.nim || '') : '';
			const rawAngkatan = props.initialData?.angkatan;
			const angkatanStr = (rawAngkatan !== null && rawAngkatan !== undefined && rawAngkatan !== '-') 
				? String(rawAngkatan) 
				: '';

			form.value = {
				nim: currentNim ? String(currentNim) : '',
				name: props.initialData?.name || props.initialData?.nama || '',
				angkatan: angkatanStr,
				talent_mapping: props.initialData?.talent_mapping !== '-' ? (props.initialData?.talent_mapping || '') : '',
				password: props.isEditing ? '' : (currentNim ? String(currentNim) : ''),
				email: props.initialData?.email !== '-' ? (props.initialData?.email || '') : '',
				phone: props.initialData?.phone !== '-' && props.initialData?.phone 
					? props.initialData.phone 
					: (props.initialData?.no_handphone !== '-' ? (props.initialData?.no_handphone || '') : ''),
			};
		}
	},
	{ immediate: true }
);

const handleClose = () => {
	formError.value = '';
	errors.value = {};
	isTalentDropdownOpen.value = false;
	emit('close');
};

const handleAngkatanInput = (e) => {
	form.value.angkatan = e.target.value.replace(/\D/g, '');
	if (errors.value.angkatan) {
		delete errors.value.angkatan;
	}
};

const handleSubmit = () => {
	formError.value = '';
	errors.value = {};
	const inputNim = String(form.value.nim || '').trim();
	const inputName = String(form.value.name || '').trim();
	const inputAngkatan = String(form.value.angkatan || '').trim();
	const inputTalentMapping = String(form.value.talent_mapping || '').trim();
	const inputPassword = String(form.value.password || '').trim();
	const inputEmail = String(form.value.email || '').trim();
	const inputPhone = String(form.value.phone || '').trim();

	if (!inputNim) {
		errors.value.nim = 'NIM wajib diisi.';
	}
	if (!inputName) {
		errors.value.name = 'Nama mahasiswa wajib diisi.';
	}
	if (!inputAngkatan) {
		errors.value.angkatan = 'Angkatan wajib diisi.';
	}
	if (!inputTalentMapping) {
		errors.value.talent_mapping = 'Talent mapping wajib dipilih.';
	}

	let finalPassword = null;
	if (!props.isEditing) {
		finalPassword = inputPassword || inputNim;
		if (!finalPassword) {
			errors.value.password = 'Password wajib diisi.';
		} else if (finalPassword.length < 6) {
			errors.value.password = 'Password minimal 6 karakter.';
		}
	} else {
		if (inputPassword) {
			if (inputPassword.length < 6) {
				errors.value.password = 'Password minimal 6 karakter.';
			} else {
				finalPassword = inputPassword;
			}
		}
	}

	if (Object.keys(errors.value).length > 0) {
		return;
	}

	// Validate duplicate NIM against existingStudents
	let isNimDuplicate = false;
	const originalNim = String(props.initialData?.nim || '').trim().toLowerCase();

	if (props.isEditing && inputNim.toLowerCase() === originalNim) {
		isNimDuplicate = false;
	} else {
		isNimDuplicate = props.existingStudents.some((s) => {
			if (props.isEditing && (s.id == props.editingId || String(s.id) === String(props.editingId))) return false;
			return String(s.nim || '').toLowerCase().trim() === inputNim.toLowerCase();
		});
	}

	if (isNimDuplicate) {
		formError.value = `NIM "${inputNim}" sudah terdaftar. NIM tidak boleh duplikat.`;
		return;
	}

	emit('submit', {
		nim: inputNim,
		name: inputName,
		angkatan: inputAngkatan || null,
		talent_mapping: inputTalentMapping || null,
		password: finalPassword,
		email: inputEmail ? inputEmail : null,
		phone: inputPhone ? inputPhone : null,
		no_handphone: inputPhone ? inputPhone : null,
	});

	handleClose();
};

const handleKeyDown = (e) => {
	if (e.key === 'Escape' && props.show) {
		handleClose();
	}
};

onMounted(() => {
	document.addEventListener('keydown', handleKeyDown);
	document.addEventListener('click', handleDocumentClick);
});

onBeforeUnmount(() => {
	document.removeEventListener('keydown', handleKeyDown);
	document.removeEventListener('click', handleDocumentClick);
});

const isBackdropClick = ref(false);

const handleBackdropMouseDown = (e) => {
	isBackdropClick.value = e.target === e.currentTarget;
};

const handleBackdropMouseUp = (e) => {
	if (isBackdropClick.value && e.target === e.currentTarget) {
		handleClose();
	}
	isBackdropClick.value = false;
};
</script>

<template>
	<Teleport to="body">
		<Transition
			enter-active-class="ease-out duration-200"
			enter-from-class="opacity-0"
			enter-to-class="opacity-100"
			leave-active-class="ease-in duration-150"
			leave-from-class="opacity-100"
			leave-to-class="opacity-0"
		>
			<div
				v-if="show"
				class="fixed inset-0 z-50 overflow-y-auto overscroll-contain bg-slate-900/40 backdrop-blur-xs p-3 sm:p-6 flex justify-center items-start sm:items-center min-h-screen"
				@mousedown="handleBackdropMouseDown"
				@mouseup="handleBackdropMouseUp"
			>
				<Transition
					enter-active-class="ease-out duration-200"
					enter-from-class="opacity-0 scale-95 translate-y-2"
					enter-to-class="opacity-100 scale-100 translate-y-0"
					leave-active-class="ease-in duration-150"
					leave-from-class="opacity-100 scale-100 translate-y-0"
					leave-to-class="opacity-0 scale-95 translate-y-2"
				>
					<div
						v-if="show"
						class="relative w-full max-w-[620px] my-auto transform rounded-[18px] sm:rounded-[22px] bg-white p-5 sm:p-7 shadow-2xl font-poppins border border-[#e2e8f0] overflow-visible"
					>
						<!-- Close Button (Top-Right) -->
						<button
							type="button"
							@click="handleClose"
							class="absolute top-4 right-4 sm:top-5 sm:right-5 p-1.5 rounded-full text-[#7188a3] hover:text-[#183669] hover:bg-slate-100 transition-colors cursor-pointer focus:outline-none"
							title="Tutup Modal"
						>
							<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
							</svg>
						</button>

						<!-- Modal Header -->
						<div class="text-center px-4 sm:px-6">
							<h2 class="text-[19px] sm:text-[22px] font-bold text-[#183669] tracking-tight">
								{{ isEditing ? 'Form Edit Mahasiswa' : 'Form Tambah Mahasiswa' }}
							</h2>
							<p class="font-inter text-[12px] sm:text-[13px] text-[#7188a3] mt-1">
								{{ isEditing ? 'Perbarui informasi data mahasiswa di bawah ini' : 'Lengkapi data mahasiswa baru di bawah ini' }}
							</p>
						</div>

						<!-- Error Alert Box -->
						<div v-if="formError" class="mt-3 sm:mt-4 rounded-[10px] bg-red-50 p-2.5 sm:p-3 font-inter text-[12px] sm:text-[13px] text-red-600 border border-red-200">
							{{ formError }}
						</div>

						<form @submit.prevent="handleSubmit" novalidate class="mt-4 sm:mt-5 space-y-3.5 sm:space-y-4">
							<!-- Row 1: NIM & Nama Mahasiswa (2 Kolom di Desktop) -->
							<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
								<!-- 1. NIM (Wajib) -->
								<div class="flex flex-col">
									<div class="min-h-[38px] flex flex-col justify-end">
										<label class="block text-[13px] font-semibold text-[#183669] leading-tight">
											NIM<span class="text-red-500 font-semibold ml-0.5">*</span>
										</label>
										<p class="font-inter text-[11px] text-[#7188a3] mt-0.5 truncate leading-normal">
											Nomor Induk Mahasiswa
										</p>
									</div>
									<input
										v-model="form.nim"
										type="text"
										placeholder="Contoh: J0403231075"
										@input="errors.nim = ''"
										class="mt-1.5 h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[13.5px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0"
										:class="errors.nim ? 'border-red-400 focus:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
									/>
									<p v-if="errors.nim" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
										<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
										</svg>
										<span>{{ errors.nim }}</span>
									</p>
								</div>

								<!-- 2. Nama Mahasiswa (Wajib) -->
								<div class="flex flex-col">
									<div class="min-h-[38px] flex flex-col justify-end">
										<label class="block text-[13px] font-semibold text-[#183669] leading-tight">
											Nama Mahasiswa<span class="text-red-500 font-semibold ml-0.5">*</span>
										</label>
										<p class="font-inter text-[11px] text-[#7188a3] mt-0.5 truncate leading-normal">
											Nama lengkap mahasiswa
										</p>
									</div>
									<input
										v-model="form.name"
										type="text"
										placeholder="Contoh: John Doe"
										@input="errors.name = ''"
										class="mt-1.5 h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[13.5px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0"
										:class="errors.name ? 'border-red-400 focus:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
									/>
									<p v-if="errors.name" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
										<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
										</svg>
										<span>{{ errors.name }}</span>
									</p>
								</div>
							</div>

							<!-- Row 2: Angkatan & Talent Mapping (2 Kolom di Desktop) -->
							<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
								<!-- 3. Angkatan (Wajib, Hanya Angka) -->
								<div class="flex flex-col">
									<div class="min-h-[38px] flex flex-col justify-end">
										<label class="block text-[13px] font-semibold text-[#183669] leading-tight">
											Angkatan<span class="text-red-500 font-semibold ml-0.5">*</span>
										</label>
										<p class="font-inter text-[11px] text-[#7188a3] mt-0.5 truncate leading-normal">
											Nomor angkatan (contoh: 60)
										</p>
									</div>
									<input
										v-model="form.angkatan"
										type="text"
										inputmode="numeric"
										placeholder="Contoh: 60"
										maxlength="4"
										@input="handleAngkatanInput"
										class="mt-1.5 h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[13.5px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0"
										:class="errors.angkatan ? 'border-red-400 focus:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
									/>
									<p v-if="errors.angkatan" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
										<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
										</svg>
										<span>{{ errors.angkatan }}</span>
									</p>
								</div>

								<!-- 4. Talent Mapping (Custom Dropdown UI) -->
								<div ref="talentDropdownRef" class="relative flex flex-col">
									<div class="min-h-[38px] flex flex-col justify-end">
										<label class="block text-[13px] font-semibold text-[#183669] leading-tight">
											Talent Mapping<span class="text-red-500 font-semibold ml-0.5">*</span>
										</label>
										<p class="font-inter text-[11px] text-[#7188a3] mt-0.5 truncate leading-normal">
											Pilihan talent mapping mahasiswa
										</p>
									</div>
									<div class="relative mt-1.5">
										<button
											type="button"
											@click="isTalentDropdownOpen = !isTalentDropdownOpen"
											class="h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[13.5px] sm:text-[14px] transition-colors duration-150 flex items-center justify-between cursor-pointer focus:outline-none focus:ring-0 select-none text-left"
											:class="[
												errors.talent_mapping
													? 'border-red-400 bg-red-50/20 text-[#1e3456]'
													: isTalentDropdownOpen
													? 'border-[#183669] bg-white ring-1 ring-[#183669]/10'
													: 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff]'
											]"
										>
											<span :class="form.talent_mapping ? 'text-[#1e3456] font-medium' : 'text-[#94a3b8]'">
												{{ form.talent_mapping || 'Pilih Talent Mapping' }}
											</span>
											<svg
												:class="['h-4 w-4 text-[#183669] transition-transform duration-150 shrink-0', isTalentDropdownOpen ? 'rotate-180' : '']"
												fill="none"
												stroke="currentColor"
												stroke-width="2"
												viewBox="0 0 24 24"
											>
												<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
											</svg>
										</button>

										<!-- Dropdown Popover List -->
										<Transition
											enter-active-class="transition duration-150 ease-out"
											enter-from-class="transform scale-95 opacity-0 -translate-y-1"
											enter-to-class="transform scale-100 opacity-100 translate-y-0"
											leave-active-class="transition duration-100 ease-in"
											leave-from-class="transform scale-100 opacity-100 translate-y-0"
											leave-to-class="transform scale-95 opacity-0 -translate-y-1"
										>
											<div
												v-if="isTalentDropdownOpen"
												class="absolute left-0 top-full mt-1.5 z-50 w-full rounded-[12px] border border-[#d6e0ee] bg-white p-1.5 shadow-2xl ring-1 ring-black/5"
											>
												<button
													v-for="option in talentMappingOptions"
													:key="option"
													type="button"
													@click="selectTalentMapping(option)"
													:class="[
														'flex w-full items-center justify-between px-3 py-2 rounded-[8px] text-left font-inter text-[13px] transition-colors cursor-pointer',
														form.talent_mapping === option
															? 'bg-[#183669]/10 font-semibold text-[#183669]'
															: 'text-[#2c4363] hover:bg-[#f1f5f9]'
													]"
												>
													<span>{{ option }}</span>
													<svg
														v-if="form.talent_mapping === option"
														class="h-4 w-4 text-[#183669] shrink-0"
														fill="none"
														stroke="currentColor"
														stroke-width="2.5"
														viewBox="0 0 24 24"
													>
														<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
													</svg>
												</button>
											</div>
										</Transition>
									</div>
									<p v-if="errors.talent_mapping" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
										<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
										</svg>
										<span>{{ errors.talent_mapping }}</span>
									</p>
								</div>
							</div>

							<!-- Row 3: Email & Nomor Handphone (2 Kolom di Desktop) -->
							<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
								<!-- 5. Email (Opsional) -->
								<div class="flex flex-col">
									<div class="min-h-[38px] flex flex-col justify-end">
										<label class="block text-[13px] font-semibold text-[#183669] leading-tight">
											Email
										</label>
										<p class="font-inter text-[11px] text-[#7188a3] mt-0.5 truncate leading-normal">
											Alamat email aktif (opsional)
										</p>
									</div>
									<input
										v-model="form.email"
										type="email"
										placeholder="example@apps.ipb.ac.id"
										class="mt-1.5 h-[44px] w-full rounded-[10px] border border-[#d6e0ee] bg-white px-3.5 font-inter text-[13.5px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white focus:outline-none focus:ring-0"
									/>
								</div>

								<!-- 6. Nomor Handphone (Opsional) -->
								<div class="flex flex-col">
									<div class="min-h-[38px] flex flex-col justify-end">
										<label class="block text-[13px] font-semibold text-[#183669] leading-tight">
											Nomor Handphone
										</label>
										<p class="font-inter text-[11px] text-[#7188a3] mt-0.5 truncate leading-normal">
											Nomor WhatsApp/HP (opsional)
										</p>
									</div>
									<PhoneInput
										v-model="form.phone"
										height-class="h-[44px]"
										placement="top"
										class="mt-1.5"
										placeholder="812-3456-7890"
									/>
								</div>
							</div>

							<!-- Row 4: Password (Kredensial Akun - Full Width) -->
							<div class="flex flex-col">
								<div class="flex items-center justify-between">
									<label class="block text-[13px] font-semibold text-[#183669] leading-tight">
										Password<span v-if="!isEditing" class="text-red-500 font-semibold ml-0.5">*</span>
									</label>
									<span v-if="isEditing" class="font-inter text-[11px] font-medium text-[#7188a3]">
										(Opsional)
									</span>
								</div>
								<p class="font-inter text-[11px] text-[#7188a3] mt-0.5 truncate leading-normal">
									{{ isEditing ? 'Password baru jika ingin diubah (min. 6 karakter)' : 'Default sesuai NIM (dapat diubah sebelum disimpan)' }}
								</p>
								<div class="relative mt-1.5">
									<input
										v-model="form.password"
										:type="showPassword ? 'text' : 'password'"
										:placeholder="isEditing ? 'Masukkan password baru (opsional)' : 'Default sesuai NIM'"
										@input="isPasswordManuallyEdited = true; errors.password = ''"
										class="h-[44px] w-full rounded-[10px] border bg-white pl-3.5 pr-11 font-inter text-[13.5px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0"
										:class="errors.password ? 'border-red-400 focus:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
									/>
									<button
										type="button"
										@click="showPassword = !showPassword"
										class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-[#183669] hover:opacity-75 focus:outline-none cursor-pointer"
										:title="showPassword ? 'Sembunyikan Password' : 'Lihat Password'"
									>
										<img
											:src="showPassword ? '/assets/icons/shown.svg' : '/assets/icons/hidden.svg'"
											:alt="showPassword ? 'Sembunyikan Password' : 'Lihat Password'"
											class="h-4 w-4 object-contain"
										/>
									</button>
								</div>
								<p v-if="errors.password" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
									<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
									</svg>
									<span>{{ errors.password }}</span>
								</p>
							</div>

							<!-- Action Buttons -->
							<div class="mt-6 sm:mt-7 flex flex-col-reverse sm:flex-row items-center justify-end gap-2.5 sm:gap-3 pt-3 sm:pt-4 border-t border-slate-100">
								<button
									type="button"
									:disabled="loading"
									@click="handleClose"
									class="h-[42px] sm:h-[44px] w-full sm:w-auto sm:min-w-[120px] px-5 rounded-[10px] border border-[#d6e0ee] bg-white font-poppins text-[13px] sm:text-[14px] font-bold text-[#183669] transition hover:border-[#183669] hover:bg-slate-50 focus:border-[#183669] focus:outline-none active:scale-98 cursor-pointer select-none disabled:opacity-50 disabled:cursor-not-allowed"
								>
									Batal
								</button>
								<button
									type="submit"
									:disabled="loading"
									class="inline-flex items-center justify-center gap-2 h-[42px] sm:h-[44px] w-full sm:w-auto sm:min-w-[170px] px-6 rounded-[10px] bg-[#183669] font-poppins text-[13px] sm:text-[14px] font-bold text-white shadow-sm transition hover:bg-[#122b54] active:scale-98 focus:outline-none cursor-pointer select-none whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed"
								>
									<svg v-if="loading" class="h-4 w-4 animate-spin text-white" viewBox="0 0 24 24" fill="none">
										<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
										<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
									</svg>
									<span>{{ isEditing ? 'Simpan Perubahan' : 'Tambah Mahasiswa' }}</span>
								</button>
							</div>
						</form>
					</div>
				</Transition>
			</div>
		</Transition>
	</Teleport>
</template>
