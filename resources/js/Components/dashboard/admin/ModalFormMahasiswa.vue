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
		default: () => ({ nim: '', name: '', angkatan: '', username: '', email: '', phone: '', password: '' }),
	},
	editingId: {
		type: [Number, String, null],
		default: null,
	},
	existingStudents: {
		type: Array,
		default: () => [],
	},
});

const emit = defineEmits(['close', 'submit']);

const form = ref({
	nim: '',
	name: '',
	angkatan: '',
	username: '',
	password: '',
	email: '',
	phone: '',
});

const formError = ref('');
const errors = ref({});
const showPassword = ref(false);
const isPasswordManuallyEdited = ref(false);

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

			const currentNim = props.initialData?.nim !== '-' ? (props.initialData?.nim || '') : '';

			form.value = {
				nim: currentNim,
				name: props.initialData?.name || '',
				angkatan: props.initialData?.angkatan !== '-' ? (props.initialData?.angkatan || '') : '',
				username: props.initialData?.username || '',
				password: props.isEditing ? '' : (currentNim || ''),
				email: props.initialData?.email !== '-' ? (props.initialData?.email || '') : '',
				phone: props.initialData?.phone !== '-' ? (props.initialData?.phone || '') : '',
			};
		}
	},
	{ immediate: true }
);

const handleClose = () => {
	formError.value = '';
	errors.value = {};
	emit('close');
};

const handleAngkatanInput = (e) => {
	form.value.angkatan = e.target.value.replace(/\D/g, '');
};

const handleSubmit = () => {
	formError.value = '';
	errors.value = {};
	const inputNim = form.value.nim.trim();
	const inputName = form.value.name.trim();
	const inputAngkatan = form.value.angkatan ? form.value.angkatan.trim() : '';
	const inputUsername = form.value.username ? form.value.username.trim() : '';
	const inputPassword = form.value.password ? form.value.password.trim() : '';

	if (!inputNim) {
		errors.value.nim = 'NIM wajib diisi.';
	}
	if (!inputName) {
		errors.value.name = 'Nama mahasiswa wajib diisi.';
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
	const isNimDuplicate = props.existingStudents.some((s) => {
		if (props.isEditing && s.id === props.editingId) return false;
		return (s.nim || '').toLowerCase().trim() === inputNim.toLowerCase().trim();
	});

	if (isNimDuplicate) {
		formError.value = `NIM "${inputNim}" sudah terdaftar. NIM tidak boleh duplikat.`;
		return;
	}

	// Validate duplicate Username against existingStudents
	if (inputUsername) {
		const isUsernameDuplicate = props.existingStudents.some((s) => {
			if (props.isEditing && s.id === props.editingId) return false;
			return (s.username || '').toLowerCase().trim() === inputUsername.toLowerCase().trim();
		});

		if (isUsernameDuplicate) {
			formError.value = `Username "${inputUsername}" sudah digunakan oleh akun lain.`;
			return;
		}
	}

	emit('submit', {
		nim: inputNim,
		name: inputName,
		angkatan: inputAngkatan || null,
		username: inputUsername || null,
		password: finalPassword,
		email: form.value.email.trim() ? form.value.email.trim() : null,
		phone: form.value.phone.trim() ? form.value.phone.trim() : null,
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
});

onBeforeUnmount(() => {
	document.removeEventListener('keydown', handleKeyDown);
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
				class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-slate-900/40 backdrop-blur-xs p-3.5 sm:p-4"
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
						class="relative w-full max-w-[560px] max-h-[92vh] overflow-y-auto transform rounded-[20px] bg-white p-6 sm:p-8 shadow-2xl font-poppins border border-[#e2e8f0]"
					>
						<!-- Modal Header -->
						<div class="text-center">
							<h2 class="text-[22px] font-bold text-[#183669] tracking-tight">
								{{ isEditing ? 'Form Edit Mahasiswa' : 'Form Tambah Mahasiswa' }}
							</h2>
						</div>

						<!-- Error Alert Box -->
						<div v-if="formError" class="mt-4 rounded-[10px] bg-red-50 p-3 font-inter text-[13px] text-red-600 border border-red-200">
							{{ formError }}
						</div>

						<form @submit.prevent="handleSubmit" novalidate class="mt-6 space-y-4">
							<!-- Row 1: NIM & Nama Mahasiswa (2 Kolom) -->
							<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
								<!-- 1. NIM (Wajib) -->
								<div>
									<label class="block text-[13px] font-semibold text-[#183669]">
										NIM<span class="text-red-500">*</span>
									</label>
									<p class="font-inter text-[11px] text-[#7188a3] mt-0.5">Masukkan nomor NIM Mahasiswa</p>
									<input
										v-model="form.nim"
										type="text"
										placeholder="J0403231075"
										@input="errors.nim = ''"
										class="mt-1.5 h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0"
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
								<div>
									<label class="block text-[13px] font-semibold text-[#183669]">
										Nama Mahasiswa<span class="text-red-500">*</span>
									</label>
									<p class="font-inter text-[11px] text-[#7188a3] mt-0.5">Masukkan nama lengkap Mahasiswa</p>
									<input
										v-model="form.name"
										type="text"
										placeholder="John Doe"
										@input="errors.name = ''"
										class="mt-1.5 h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0"
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

							<!-- Row 2: Angkatan & Password (2 Kolom) -->
							<div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
								<!-- 3. Angkatan (Hanya Angka) -->
								<div>
									<label class="block text-[13px] font-semibold text-[#183669]">
										Angkatan
									</label>
									<p class="font-inter text-[11px] text-[#7188a3] mt-0.5">Nomor angkatan (hanya angka, contoh: 60)</p>
									<input
										v-model="form.angkatan"
										type="text"
										inputmode="numeric"
										placeholder="XX"
										maxlength="4"
										@input="handleAngkatanInput"
										class="mt-1.5 h-[44px] w-full rounded-[10px] border border-[#d6e0ee] bg-white px-3.5 font-inter text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white focus:outline-none focus:ring-0"
									/>
								</div>

								<!-- 4. Password (Kredensial Akun) -->
								<div>
									<div class="flex items-center justify-between">
										<label class="block text-[13px] font-semibold text-[#183669]">
											Password<span v-if="!isEditing" class="text-red-500">*</span>
										</label>
										<span v-if="isEditing" class="font-inter text-[10px] font-medium text-[#7188a3]">
											(Opsional)
										</span>
									</div>
									<p class="font-inter text-[11px] text-[#7188a3] mt-0.5 truncate">
										{{ isEditing ? 'Password baru jika ingin diubah' : 'Default sesuai nilai NIM' }}
									</p>
									<div class="relative mt-1.5">
										<input
											v-model="form.password"
											:type="showPassword ? 'text' : 'password'"
											:placeholder="isEditing ? 'Password baru (opsional)' : 'Default NIM'"
											@input="isPasswordManuallyEdited = true; errors.password = ''"
											class="h-[44px] w-full rounded-[10px] border bg-white pl-3.5 pr-11 font-inter text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0"
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
							</div>

							<!-- Row 3: Username (Full Width) -->
							<div>
								<label class="block text-[13px] font-semibold text-[#183669]">
									Username
								</label>
								<p class="font-inter text-[11px] text-[#7188a3] mt-0.5">Masukkan username akun (opsional)</p>
								<input
									v-model="form.username"
									type="text"
									placeholder="johndoe"
									@input="errors.username = ''"
									class="mt-1.5 h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0"
									:class="errors.username ? 'border-red-400 focus:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
								/>
								<p v-if="errors.username" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
									<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
									</svg>
									<span>{{ errors.username }}</span>
								</p>
							</div>

							<!-- Row 4: Email (Full Width) -->
							<div>
								<label class="block text-[13px] font-semibold text-[#183669]">
									Email
								</label>
								<p class="font-inter text-[11px] text-[#7188a3] mt-0.5">Masukkan email aktif (opsional)</p>
								<input
									v-model="form.email"
									type="email"
									placeholder="example@apps.ipb.ac.id"
									class="mt-1.5 h-[44px] w-full rounded-[10px] border border-[#d6e0ee] bg-white px-3.5 font-inter text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white focus:outline-none focus:ring-0"
								/>
							</div>

							<!-- Row 5: Nomor Handphone (Full Width) -->
							<div>
								<label class="block text-[13px] font-semibold text-[#183669]">
									Nomor Handphone
								</label>
								<p class="font-inter text-[11px] text-[#7188a3] mt-0.5">Masukkan nomor aktif Mahasiswa (opsional)</p>
								<PhoneInput
									v-model="form.phone"
									height-class="h-[44px]"
									placement="top"
									class="mt-1.5"
									placeholder="XXX-XXXX-XXXX"
								/>
							</div>

							<!-- Action Buttons -->
							<div class="mt-8 flex items-center justify-center gap-4 pt-3">
								<button
									type="button"
									@click="handleClose"
									class="h-[44px] min-w-[130px] px-6 rounded-[10px] border border-[#d6e0ee] bg-white font-poppins text-[14px] font-bold text-[#183669] transition hover:border-[#183669] hover:bg-slate-50 focus:border-[#183669] focus:outline-none active:scale-98 cursor-pointer select-none"
								>
									Batal
								</button>
								<button
									type="submit"
									class="h-[44px] min-w-[170px] px-6 rounded-[10px] bg-[#183669] font-poppins text-[14px] font-bold text-white shadow-sm transition hover:bg-[#122b54] active:scale-98 focus:outline-none cursor-pointer select-none"
								>
									{{ isEditing ? 'Simpan Perubahan' : 'Tambah Mahasiswa' }}
								</button>
							</div>
						</form>
					</div>
				</Transition>
			</div>
		</Transition>
	</Teleport>
</template>
