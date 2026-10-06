<script setup>
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import MahasiswaLayout from '@/Layouts/dashboard/MahasiswaLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import PhoneInput from '@/Components/dashboard/PhoneInput.vue';

const props = defineProps({
	userData: {
		type: Object,
		default: () => null,
	},
	isDefaultPassword: {
		type: Boolean,
		default: false,
	},
	isEmailEmpty: {
		type: Boolean,
		default: false,
	},
	isLocked: {
		type: Boolean,
		default: false,
	},
	hasPhoto: {
		type: Boolean,
		default: false,
	},
});

const page = usePage();

// Toast State
const toast = ref({
	show: false,
	type: 'success',
	title: '',
	message: '',
});

const showToast = (type, title, message) => {
	toast.value = {
		show: true,
		type,
		title,
		message,
	};
};

// Check flash messages
watch(
	() => page.props.flash,
	(newFlash) => {
		if (newFlash?.success) {
			showToast('success', 'Berhasil', newFlash.success);
		}
		if (newFlash?.error) {
			showToast('error', 'Terjadi Kesalahan', newFlash.error);
		}
		if (newFlash?.warning) {
			showToast('warning', 'Peringatan Keamanan', newFlash.warning);
		}
	},
	{ immediate: true, deep: true }
);

const currentUser = computed(() => props.userData || page.props.auth?.user || {});
const isDefaultPass = computed(() => props.isDefaultPassword ?? page.props.auth?.user?.is_default_password ?? false);
const isEmailEmp = computed(() => props.isEmailEmpty ?? page.props.auth?.user?.is_email_empty ?? false);
const isLockedAccount = computed(() => props.isLocked ?? page.props.auth?.user?.is_locked ?? (isDefaultPass.value || isEmailEmp.value));

const isLoading = ref(true);
onMounted(() => {
	setTimeout(() => {
		isLoading.value = false;
	}, 200);
});

// 1. State Data Diri
const formPersonal = ref({
	name: currentUser.value.name || '',
	nim: currentUser.value.nim || currentUser.value.NIM || '',
	angkatan: currentUser.value.angkatan || '',
	photoPreview: currentUser.value.profile_picture || null,
	photo: null,
});

const fileInputRef = ref(null);

const handlePhotoUpload = (e) => {
	const file = e.target.files?.[0];
	if (file) {
		if (file.size > 10 * 1024 * 1024) {
			showToast('error', 'Ukuran Terlalu Besar', 'Maksimal ukuran foto adalah 10MB.');
			return;
		}
		formPersonal.value.photo = file;
		formPersonal.value.photoPreview = URL.createObjectURL(file);
	}
};

const handleDrop = (e) => {
	const file = e.dataTransfer?.files?.[0];
	if (file) {
		if (file.size > 10 * 1024 * 1024) {
			showToast('error', 'Ukuran Terlalu Besar', 'Maksimal ukuran foto adalah 10MB.');
			return;
		}
		formPersonal.value.photo = file;
		formPersonal.value.photoPreview = URL.createObjectURL(file);
	}
};

const triggerFileInput = () => {
	fileInputRef.value?.click();
};

const previewingImage = ref(null);

const openImagePreview = (img) => {
	previewingImage.value = img;
};

const closeImagePreview = () => {
	previewingImage.value = null;
};

const handleKeyDown = (e) => {
	if (e.key === 'Escape' && previewingImage.value) {
		closeImagePreview();
	}
};

onMounted(() => {
	document.addEventListener('keydown', handleKeyDown);
});

onBeforeUnmount(() => {
	document.removeEventListener('keydown', handleKeyDown);
});

// 2. State Data Akun
const savedAccountData = ref({
	email: null,
	phone: null,
});

const formAccount = ref({
	email: currentUser.value.email || '',
	phone: currentUser.value.phone || '',
});

const isEditingEmail = ref(false);
const isEditingPhone = ref(false);
const emailInputRef = ref(null);
const phoneInputRef = ref(null);
const accountErrors = ref({});
const isSavingAccount = ref(false);

watch(
	() => currentUser.value,
	(val) => {
		if (val) {
			formPersonal.value.name = val.name || '';
			formPersonal.value.nim = val.nim || val.NIM || '';
			formPersonal.value.angkatan = val.angkatan || '';
			formPersonal.value.photoPreview = val.profile_picture || null;

			if (!isEditingEmail.value) {
				formAccount.value.email = savedAccountData.value.email ?? (val.email && val.email !== '-' ? val.email : '');
			}
			if (!isEditingPhone.value) {
				formAccount.value.phone = savedAccountData.value.phone ?? (val.phone && val.phone !== '-' ? val.phone : '');
			}
		}
	},
	{ immediate: true, deep: true }
);

const hasInitialEmail = computed(() => {
	const email = savedAccountData.value.email ?? currentUser.value?.email;
	return !!email && email !== '-';
});
const hasInitialPhone = computed(() => {
	const phone = savedAccountData.value.phone ?? currentUser.value?.phone;
	return !!phone && phone !== '-';
});

const isEmailLocked = computed(() => hasInitialEmail.value && !isEditingEmail.value);
const isPhoneLocked = computed(() => hasInitialPhone.value && !isEditingPhone.value);

const cancelEditEmail = () => {
	const current = currentUser.value || {};
	const origEmail = savedAccountData.value.email ?? (current.email && current.email !== '-' ? current.email : '');
	formAccount.value.email = origEmail;
	isEditingEmail.value = false;
	if (accountErrors.value?.email) {
		delete accountErrors.value.email;
	}
	emailInputRef.value?.blur();
};

const cancelEditPhone = () => {
	const current = currentUser.value || {};
	const origPhone = savedAccountData.value.phone ?? (current.phone && current.phone !== '-' ? current.phone : '');
	formAccount.value.phone = origPhone;
	isEditingPhone.value = false;
	if (accountErrors.value?.phone) {
		delete accountErrors.value.phone;
	}
	phoneInputRef.value?.blur();
};

const toggleEditEmail = () => {
	if (isEditingEmail.value) {
		cancelEditEmail();
	} else {
		if (isEditingPhone.value) cancelEditPhone();
		isEditingEmail.value = true;
		nextTick(() => {
			emailInputRef.value?.focus();
		});
	}
};

const toggleEditPhone = () => {
	if (isEditingPhone.value) {
		cancelEditPhone();
	} else {
		if (isEditingEmail.value) cancelEditEmail();
		isEditingPhone.value = true;
		nextTick(() => {
			phoneInputRef.value?.focus();
		});
	}
};

// Clear error secara otomatis saat user mengetik atau mengubah field akun
watch(() => formAccount.value.email, () => {
	if (accountErrors.value?.email) {
		delete accountErrors.value.email;
	}
});

watch(() => formAccount.value.phone, () => {
	if (accountErrors.value?.phone) {
		delete accountErrors.value.phone;
	}
});

// 3. State Ganti Password
const formPassword = ref({
	currentPassword: '',
	newPassword: '',
	confirmPassword: '',
});

// Clear error secara otomatis saat user mengetik password
watch(() => formPassword.value.currentPassword, () => {
	if (passwordErrors.value?.currentPassword) {
		delete passwordErrors.value.currentPassword;
	}
});

watch(() => formPassword.value.newPassword, () => {
	if (passwordErrors.value?.newPassword) {
		delete passwordErrors.value.newPassword;
	}
});

watch(() => formPassword.value.confirmPassword, () => {
	if (passwordErrors.value?.confirmPassword) {
		delete passwordErrors.value.confirmPassword;
	}
});

const showCurrentPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const passwordErrors = ref({});
const isSavingPassword = ref(false);

// State periksa apakah ada perubahan data akun dibanding data semula
const isAccountChanged = computed(() => {
	const current = currentUser.value || {};
	const origEmail = (savedAccountData.value.email ?? (current.email && current.email !== '-' ? current.email : '')).trim();
	const origPhone = (savedAccountData.value.phone ?? (current.phone && current.phone !== '-' ? current.phone : '')).trim();

	const newEmail = (formAccount.value.email || '').trim();
	const newPhone = (formAccount.value.phone || '').trim();

	const normOrigPhone = origPhone.replace(/[\s-]/g, '');
	const normNewPhone = newPhone.replace(/[\s-]/g, '');

	return newEmail !== origEmail || normNewPhone !== normOrigPhone;
});

// State periksa apakah form password ada isinya
const isPasswordChanged = computed(() => {
	return (
		!!(formPassword.value.currentPassword || '').trim() ||
		!!(formPassword.value.newPassword || '').trim() ||
		!!(formPassword.value.confirmPassword || '').trim()
	);
});

const isPersonalChanged = computed(() => {
	return !!formPersonal.value.photo;
});

// Save Handlers
const isSavingPersonal = ref(false);
const savePersonalData = () => {
	if (!isPersonalChanged.value || isSavingPersonal.value) return;
	isSavingPersonal.value = true;

	setTimeout(() => {
		showToast('success', 'Berhasil Disimpan', 'Foto profil berhasil diperbarui.');
		formPersonal.value.photo = null;
		isSavingPersonal.value = false;
	}, 400);
};

const saveAccountData = () => {
	if (!isAccountChanged.value || isSavingAccount.value) return;
	accountErrors.value = {};
	if (!formAccount.value.email.trim()) {
		accountErrors.value.email = 'Email wajib diisi.';
		showToast('error', 'Validasi Gagal', 'Email tidak boleh kosong.');
		return;
	}

	isSavingAccount.value = true;
	setTimeout(() => {
		savedAccountData.value.email = formAccount.value.email.trim();
		savedAccountData.value.phone = formAccount.value.phone.trim();
		isEditingEmail.value = false;
		isEditingPhone.value = false;
		showToast('success', 'Berhasil Disimpan', 'Data akun berhasil diperbarui.');
		isSavingAccount.value = false;
	}, 400);
};

const savePassword = () => {
	if (!isPasswordChanged.value || isSavingPassword.value) return;
	passwordErrors.value = {};

	if (!formPassword.value.currentPassword) {
		passwordErrors.value.currentPassword = 'Password saat ini wajib diisi.';
		showToast('error', 'Validasi Gagal', 'Password saat ini wajib diisi.');
		return;
	}
	if (!formPassword.value.newPassword) {
		passwordErrors.value.newPassword = 'Password baru wajib diisi.';
		showToast('error', 'Validasi Gagal', 'Password baru wajib diisi.');
		return;
	}
	if (formPassword.value.newPassword.length < 8) {
		passwordErrors.value.newPassword = 'Password baru minimal 8 karakter.';
		showToast('error', 'Validasi Gagal', 'Password baru minimal 8 karakter.');
		return;
	}
	if (formPassword.value.newPassword !== formPassword.value.confirmPassword) {
		passwordErrors.value.confirmPassword = 'Konfirmasi password baru tidak cocok.';
		showToast('error', 'Validasi Gagal', 'Konfirmasi password baru tidak cocok.');
		return;
	}
	if (formPassword.value.newPassword === formPersonal.value.nim) {
		passwordErrors.value.newPassword = 'Password baru tidak boleh sama dengan NIM.';
		showToast('error', 'Validasi Gagal', 'Password baru tidak boleh sama dengan password default (NIM).');
		return;
	}

	isSavingPassword.value = true;
	setTimeout(() => {
		formPassword.value = {
			currentPassword: '',
			newPassword: '',
			confirmPassword: '',
		};
		showToast('success', 'Password Diperbarui', 'Password berhasil diubah!');
		isSavingPassword.value = false;
	}, 400);
};
</script>

<template>
	<Head title="Profile Mahasiswa" />

	<MahasiswaLayout>
		<section class="mx-auto w-full max-w-[1520px] px-4 py-6 font-poppins sm:px-6 sm:py-8 lg:px-8">
			<div class="space-y-6">
				<!-- Header Section -->
				<div class="space-y-1">
					<h1 class="text-[34px] sm:text-[42px] font-extrabold text-[#112340] leading-none tracking-tight">
						Profile
					</h1>
					<p class="font-inter text-[14px] text-[#4d6786] font-normal mt-2">
						Yuk Lengkapi profile akun Talenta kamu!
					</p>
				</div>

				<!-- Security Warning Banner -->
				<div
					v-if="isLockedAccount"
					class="flex items-start gap-3.5 rounded-[14px] border border-amber-300 bg-amber-50/90 p-4 sm:p-5 shadow-xs"
				>
					<div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
						<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286zm0 13.036h.008v.008H12v-.008z" />
						</svg>
					</div>
					<div class="space-y-1">
						<h3 class="font-poppins text-[15px] font-bold text-amber-900">
							<template v-if="isDefaultPass && isEmailEmp">
								Perhatian: Lengkapi Email & Ubah Password Default Anda
							</template>
							<template v-else-if="isEmailEmp">
								Perhatian: Lengkapi Email Aktif Anda
							</template>
							<template v-else>
								Perhatian: Ubah Password Default Anda (NIM)
							</template>
						</h3>
						<p class="font-inter text-[13px] leading-relaxed text-amber-800">
							Demi keamanan akun, akses menu <strong>Dashboard</strong> dan <strong>Aktivitas</strong> masih terkunci.
							Harap <span v-if="isEmailEmp">masukkan <strong>Email aktif</strong> Anda</span><span v-if="isEmailEmp && isDefaultPass"> serta </span><span v-if="isDefaultPass">perbarui <strong>Password baru</strong></span> pada formulir di bawah ini agar seluruh menu terbuka penuh.
						</p>
					</div>
				</div>

				<!-- Main Profile Form Grid -->
				<div class="grid grid-cols-1 items-start gap-6 lg:grid-cols-12">
					<!-- LEFT COLUMN: Data Diri (Card 1) -->
					<div class="rounded-[14px] bg-white p-6 shadow-sm ring-1 ring-[#d6e0ee] lg:col-span-4">
						<form @submit.prevent="savePersonalData" class="space-y-5">
							<div>
								<h2 class="font-poppins text-[19px] font-bold text-[#112340]">
									Data diri
								</h2>

								<div class="mt-4 space-y-4">
									<!-- Foto Diri -->
									<div>
										<div class="flex items-center justify-between">
											<label class="block font-poppins text-[13px] font-semibold text-[#112340]">
												Foto diri
											</label>
										</div>
										<p class="mt-0.5 font-inter text-[12px] text-[#7188a3]">
											Masukan foto terbaikmu berupa jpg/png/jpeg (MAX 10mb)
										</p>

										<!-- State 0: Loading Skeleton -->
										<div
											v-if="isLoading"
											class="mt-3 flex h-[200px] sm:h-[220px] w-full items-center justify-center overflow-hidden rounded-[12px] border border-[#d6e0ee] bg-[#fafcff] p-3 text-center transition-all animate-pulse"
										>
											<div class="h-full w-auto aspect-[3/4] rounded-[10px] bg-slate-200"></div>
										</div>

										<!-- State 1: Memiliki Foto Profil -->
										<div
											v-else-if="formPersonal.photoPreview"
											class="mt-3 flex h-[200px] sm:h-[220px] w-full items-center justify-center overflow-hidden rounded-[12px] border border-[#d6e0ee] bg-[#fafcff] p-3 text-center transition-all"
										>
											<div class="group relative flex h-full w-auto items-center justify-center overflow-hidden rounded-[10px] border border-[#d6e0ee] bg-slate-100 shadow-xs aspect-[3/4]">
												<img
													:src="formPersonal.photoPreview"
													alt="Foto Profil"
													class="h-full w-full object-cover object-top"
												/>

												<!-- Zoom & Edit Action Buttons -->
												<div class="absolute inset-0 bg-black/40 flex items-center justify-center gap-2.5 opacity-100 lg:opacity-0 lg:group-hover:opacity-100 transition-opacity">
													<button
														type="button"
														@click.stop="openImagePreview(formPersonal.photoPreview)"
														class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-900/80 text-white shadow-md backdrop-blur-xs transition hover:bg-slate-900 hover:scale-110 active:scale-95 focus:outline-none cursor-pointer"
														title="Lihat Foto Ukuran Penuh"
													>
														<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
															<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
														</svg>
													</button>
													<button
														type="button"
														@click.stop="triggerFileInput"
														class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-500 text-white shadow-md backdrop-blur-xs transition hover:bg-amber-600 hover:scale-110 active:scale-95 focus:outline-none cursor-pointer"
														title="Ganti Foto"
													>
														<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
															<path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125" />
														</svg>
													</button>
												</div>
											</div>
										</div>

										<!-- State 2: Belum Memiliki Foto Profil / Upload Container -->
										<div
											v-else
											class="mt-3 flex h-[200px] sm:h-[220px] w-full flex-col items-center justify-center rounded-[12px] border-2 border-dashed border-[#d6e0ee] bg-[#f8fafc] p-4 text-center transition-all hover:border-[#183669] hover:bg-[#f0f4f9] group cursor-pointer"
											@click="triggerFileInput"
											@dragover.prevent
											@drop.prevent="handleDrop"
										>
											<input
												type="file"
												ref="fileInputRef"
												@change="handlePhotoUpload"
												accept="image/jpeg,image/png,image/jpg"
												class="hidden"
											/>
											<div class="flex flex-col items-center justify-center pointer-events-none">
												<svg class="h-10 w-10 text-[#8c9eb5] mb-1.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
													<path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
												</svg>
												<p class="font-inter text-[12px] text-[#7188a3] font-medium">
													Upload gambar atau seret gambar ke form ini
												</p>
												<span class="mt-1 text-[10px] font-inter text-[#8c9eb5]">
													Format JPG, PNG, JPEG • Maksimal 10MB • Rasio 3:4
												</span>
												<button
													type="button"
													class="mt-2.5 rounded-[8px] border border-[#a6b7cb] bg-white px-5 py-1.5 font-inter text-[12px] font-semibold text-[#5a718d] shadow-xs"
												>
													Upload
												</button>
											</div>
										</div>
									</div>

									<!-- Nama -->
									<div>
										<label class="block font-poppins text-[13px] font-semibold text-[#112340]">
											Nama
										</label>
										<div v-if="isLoading" class="mt-1 flex h-[38px] items-center border-b border-[#d6e0ee]">
											<div class="h-4 w-44 rounded bg-slate-200 animate-pulse"></div>
										</div>
										<div v-else class="mt-1 flex h-[38px] items-center border-b border-[#d6e0ee] font-inter text-[14px] font-medium text-[#112340] select-none cursor-default">
											{{ formPersonal.name || '-' }}
										</div>
									</div>

									<!-- NIM -->
									<div>
										<label class="block font-poppins text-[13px] font-semibold text-[#112340]">
											NIM
										</label>
										<div v-if="isLoading" class="mt-1 flex h-[38px] items-center border-b border-[#d6e0ee]">
											<div class="h-4 w-32 rounded bg-slate-200 animate-pulse"></div>
										</div>
										<div v-else class="mt-1 flex h-[38px] items-center border-b border-[#d6e0ee] font-inter text-[14px] font-medium text-[#112340] select-none cursor-default">
											{{ formPersonal.nim || '-' }}
										</div>
									</div>

									<!-- Angkatan -->
									<div>
										<label class="block font-poppins text-[13px] font-semibold text-[#112340]">
											Angkatan
										</label>
										<div v-if="isLoading" class="mt-1 flex h-[38px] items-center border-b border-[#d6e0ee]">
											<div class="h-4 w-24 rounded bg-slate-200 animate-pulse"></div>
										</div>
										<div v-else class="mt-1 flex h-[38px] items-center border-b border-[#d6e0ee] font-inter text-[14px] font-medium text-[#112340] select-none cursor-default">
											{{ formPersonal.angkatan || '-' }}
										</div>
									</div>
								</div>
							</div>

							<!-- Submit Button Data Diri -->
							<div class="flex justify-end pt-2">
								<button
									type="submit"
									:disabled="!isPersonalChanged || isSavingPersonal"
									:class="[
										'inline-flex items-center justify-center gap-2 rounded-[8px] px-6 py-2.5 font-poppins text-[14px] font-semibold transition duration-150',
										!isPersonalChanged || isSavingPersonal
											? 'cursor-not-allowed bg-[#f0f4f9] text-[#8c9eb5] border-[1.5px] border-[#d6e0ee] shadow-none'
											: 'cursor-pointer bg-[#183669] text-white border-[1.5px] border-[#183669] shadow-sm hover:bg-[#122b54] hover:border-[#122b54] active:scale-[0.98]'
									]"
									:title="!isPersonalChanged ? 'Pilih foto profil baru untuk disimpan' : 'Simpan foto profil'"
								>
									<svg v-if="isSavingPersonal" class="h-4 w-4 animate-spin text-[#8c9eb5]" fill="none" viewBox="0 0 24 24">
										<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
										<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
									</svg>
									<svg
										v-else
										class="h-4 w-4 shrink-0 translate-y-[0.5px]"
										viewBox="0 0 16 16"
										fill="currentColor"
										aria-hidden="true"
									>
										<path d="M13.3333 16V8.88889H2.66667V16H0.888889C0.653141 16 0.427048 15.9064 0.260349 15.7397C0.0936505 15.573 0 15.3469 0 15.1111V0.888889C0 0.653141 0.0936505 0.427048 0.260349 0.260349C0.427048 0.0936505 0.653141 0 0.888889 0H12.4444L16 3.55556V15.1111C16 15.3469 15.9064 15.573 15.7397 15.7397C15.573 15.9064 15.3469 16 15.1111 16H13.3333ZM11.5556 16H4.44444V10.6667H11.5556V16Z" />
									</svg>
									<span class="leading-none pt-[0.5px]">{{ isSavingPersonal ? 'Menyimpan...' : 'Simpan' }}</span>
								</button>
							</div>
						</form>
					</div>

					<!-- RIGHT COLUMN: Data Akun (Card 2) & Ganti Password (Card 3) -->
					<div class="w-full space-y-6 lg:col-span-8">
						<!-- Card 2: Data Akun -->
						<div class="rounded-[14px] bg-white p-6 shadow-sm ring-1 ring-[#d6e0ee]">
							<h2 class="font-poppins text-[19px] font-bold text-[#112340]">
								Data Akun
							</h2>

							<form @submit.prevent="saveAccountData" class="mt-5 space-y-5">
								<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
									<!-- Email Field -->
									<div class="flex flex-col">
										<label class="block font-poppins text-[13px] font-semibold text-[#112340]">
											Email<span class="text-red-500">*</span>
										</label>
										<p class="mt-0.5 min-h-[18px] font-inter text-[12px] text-[#7188a3]">
											Masukkan email aktif yang kamu gunakan
										</p>
										<div v-if="isLoading" class="relative mt-2 h-[46px] w-full rounded-[10px] bg-slate-100 border border-[#d6e0ee] animate-pulse"></div>
										<div v-else class="relative mt-2">
											<input
												ref="emailInputRef"
												v-model="formAccount.email"
												type="email"
												:readonly="isEmailLocked"
												:tabindex="isEmailLocked ? -1 : 0"
												@focus="isEmailLocked && $event.target.blur()"
												placeholder="contoh: nama@email.com"
												:class="[
													'custom-input h-[46px] w-full rounded-[10px] pl-3.5 font-inter text-[14px] text-[#112340] placeholder-[#a8bed4] transition-all duration-150',
													hasInitialEmail || isEditingEmail ? 'pr-11' : 'pr-3.5',
													accountErrors.email ? 'border-red-400 bg-red-50/20' : ''
												]"
											/>
											<!-- Edit Pencil Button inside Email Input -->
											<button
												type="button"
												@mousedown.prevent
												@click="toggleEditEmail"
												class="absolute inset-y-0 right-0 z-10 flex items-center pr-3 focus:outline-none cursor-pointer"
												:title="isEditingEmail ? 'Batalkan perubahan email' : 'Edit Email'"
											>
												<span class="flex h-7 w-7 items-center justify-center rounded-[7px] text-[#183669] transition-colors hover:bg-[#dbe4ef] active:bg-[#ccd9e7]">
													<svg
														v-if="isEditingEmail"
														class="h-4 w-4"
														fill="none"
														viewBox="0 0 24 24"
														stroke="currentColor"
														stroke-width="2.5"
													>
														<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
													</svg>
													<svg
														v-else
														class="h-4 w-4"
														viewBox="0 0 19 19"
														fill="currentColor"
													>
														<path d="M4.92119 4.92074C4.92119 5.18177 4.81749 5.43211 4.63291 5.61669C4.44833 5.80126 4.19798 5.90496 3.93695 5.90496H2.95271C2.69168 5.90496 2.44133 6.00865 2.25675 6.19323C2.07217 6.3778 1.96847 6.62814 1.96847 6.88917V15.7471C1.96847 16.0082 2.07217 16.2585 2.25675 16.4431C2.44133 16.6277 2.69168 16.7313 2.95271 16.7313H11.8108C12.0719 16.7313 12.3222 16.6277 12.5068 16.4431C12.6914 16.2585 12.7951 16.0082 12.7951 15.7471V14.7629C12.7951 14.5019 12.8988 14.2515 13.0834 14.067C13.2679 13.8824 13.5183 13.7787 13.7793 13.7787C14.0404 13.7787 14.2907 13.8824 14.4753 14.067C14.6599 14.2515 14.7636 14.5019 14.7636 14.7629V15.7471C14.7636 16.5302 14.4525 17.2812 13.8987 17.835C13.345 18.3887 12.594 18.6998 11.8108 18.6998H2.95271C2.1696 18.6998 1.41857 18.3887 0.864829 17.835C0.311088 17.2812 0 16.5302 0 15.7471V6.88917C0 6.10608 0.311088 5.35506 0.864829 4.80134C1.41857 4.24761 2.1696 3.93652 2.95271 3.93652H3.93695C4.19798 3.93652 4.44833 4.04022 4.63291 4.22479C4.81749 4.40937 4.92119 4.65971 4.92119 4.92074Z" />
														<path d="M11.413 2.96342L15.7358 7.2861L9.55481 13.4896C9.4634 13.5813 9.3548 13.6541 9.23522 13.7037C9.11564 13.7534 8.98744 13.779 8.85797 13.779H5.90526C5.64422 13.779 5.39388 13.6753 5.2093 13.4907C5.02472 13.3061 4.92102 13.0558 4.92102 12.7948V9.84211C4.92105 9.71264 4.94662 9.58444 4.99628 9.46487C5.04593 9.34529 5.11869 9.23669 5.21039 9.14528L11.413 2.96342ZM17.8067 0.893608C18.3495 1.43606 18.6678 2.16332 18.6979 2.93014C18.728 3.69697 18.4677 4.44693 17.9691 5.03027L17.8076 5.20743L17.1256 5.89048L12.8077 1.57272L13.4918 0.893608C14.064 0.32144 14.84 0 15.6492 0C16.4584 0 17.2345 0.32144 17.8067 0.893608Z" />
													</svg>
												</span>
											</button>
										</div>
										<p v-if="accountErrors.email" class="mt-1 font-inter text-[11px] font-medium text-red-500">
											{{ accountErrors.email }}
										</p>
									</div>

									<!-- Nomor Handphone Field -->
									<div class="flex flex-col">
										<label class="block font-poppins text-[13px] font-semibold text-[#112340]">
											Nomor Handphone
										</label>
										<p class="mt-0.5 min-h-[18px] font-inter text-[12px] text-[#7188a3]">
											Masukkan nomor handphone aktif yang kamu gunakan
										</p>
										<div v-if="isLoading" class="relative mt-2 h-[46px] w-full rounded-[10px] bg-slate-100 border border-[#d6e0ee] animate-pulse"></div>
										<div v-else class="relative mt-2">
											<PhoneInput
												ref="phoneInputRef"
												v-model="formAccount.phone"
												:readonly="isPhoneLocked"
												placeholder="XXX-XXXX-XXXX"
												:has-error="!!accountErrors.phone"
											>
												<!-- Edit Pencil Button inside Phone Input -->
												<template #append>
													<button
														type="button"
														@mousedown.prevent
														@click="toggleEditPhone"
														class="flex items-center focus:outline-none cursor-pointer"
														:title="isEditingPhone ? 'Batalkan perubahan nomor' : 'Edit Nomor Handphone'"
													>
														<span class="flex h-7 w-7 items-center justify-center rounded-[7px] text-[#183669] transition-colors hover:bg-[#dbe4ef] active:bg-[#ccd9e7]">
															<svg
																v-if="isEditingPhone"
																class="h-4 w-4"
																fill="none"
																viewBox="0 0 24 24"
																stroke="currentColor"
																stroke-width="2.5"
															>
																<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
															</svg>
															<svg
																v-else
																class="h-4 w-4"
																viewBox="0 0 19 19"
																fill="currentColor"
															>
																<path d="M4.92119 4.92074C4.92119 5.18177 4.81749 5.43211 4.63291 5.61669C4.44833 5.80126 4.19798 5.90496 3.93695 5.90496H2.95271C2.69168 5.90496 2.44133 6.00865 2.25675 6.19323C2.07217 6.3778 1.96847 6.62814 1.96847 6.88917V15.7471C1.96847 16.0082 2.07217 16.2585 2.25675 16.4431C2.44133 16.6277 2.69168 16.7313 2.95271 16.7313H11.8108C12.0719 16.7313 12.3222 16.6277 12.5068 16.4431C12.6914 16.2585 12.7951 16.0082 12.7951 15.7471V14.7629C12.7951 14.5019 12.8988 14.2515 13.0834 14.067C13.2679 13.8824 13.5183 13.7787 13.7793 13.7787C14.0404 13.7787 14.2907 13.8824 14.4753 14.067C14.6599 14.2515 14.7636 14.5019 14.7636 14.7629V15.7471C14.7636 16.5302 14.4525 17.2812 13.8987 17.835C13.345 18.3887 12.594 18.6998 11.8108 18.6998H2.95271C2.1696 18.6998 1.41857 18.3887 0.864829 17.835C0.311088 17.2812 0 16.5302 0 15.7471V6.88917C0 6.10608 0.311088 5.35506 0.864829 4.80134C1.41857 4.24761 2.1696 3.93652 2.95271 3.93652H3.93695C4.19798 3.93652 4.44833 4.04022 4.63291 4.22479C4.81749 4.40937 4.92119 4.65971 4.92119 4.92074Z" />
																<path d="M11.413 2.96342L15.7358 7.2861L9.55481 13.4896C9.4634 13.5813 9.3548 13.6541 9.23522 13.7037C9.11564 13.7534 8.98744 13.779 8.85797 13.779H5.90526C5.64422 13.779 5.39388 13.6753 5.2093 13.4907C5.02472 13.3061 4.92102 13.0558 4.92102 12.7948V9.84211C4.92105 9.71264 4.94662 9.58444 4.99628 9.46487C5.04593 9.34529 5.11869 9.23669 5.21039 9.14528L11.413 2.96342ZM17.8067 0.893608C18.3495 1.43606 18.6678 2.16332 18.6979 2.93014C18.728 3.69697 18.4677 4.44693 17.9691 5.03027L17.8076 5.20743L17.1256 5.89048L12.8077 1.57272L13.4918 0.893608C14.064 0.32144 14.84 0 15.6492 0C16.4584 0 17.2345 0.32144 17.8067 0.893608Z" />
															</svg>
														</span>
													</button>
												</template>
											</PhoneInput>
										</div>
										<p v-if="accountErrors.phone" class="mt-1 font-inter text-[11px] font-medium text-red-500">
											{{ accountErrors.phone }}
										</p>
									</div>
								</div>

								<!-- Submit Button Data Akun -->
								<div class="flex justify-end pt-3">
									<button
										type="submit"
										:disabled="!isAccountChanged || isSavingAccount"
										:class="[
											'inline-flex items-center justify-center gap-2 rounded-[8px] px-6 py-2.5 font-poppins text-[14px] font-semibold transition duration-150',
											!isAccountChanged || isSavingAccount
												? 'cursor-not-allowed bg-[#f0f4f9] text-[#8c9eb5] border-[1.5px] border-[#d6e0ee] shadow-none'
												: 'cursor-pointer bg-[#183669] text-white border-[1.5px] border-[#183669] shadow-sm hover:bg-[#122b54] hover:border-[#122b54] active:scale-[0.98]'
										]"
										:title="!isAccountChanged ? 'Tidak ada perubahan data akun untuk disimpan' : 'Simpan perubahan akun'"
									>
										<svg v-if="isSavingAccount" class="h-4 w-4 animate-spin text-[#8c9eb5]" fill="none" viewBox="0 0 24 24">
											<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
											<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
										</svg>
										<svg
											v-else
											class="h-4 w-4 shrink-0 translate-y-[0.5px]"
											viewBox="0 0 16 16"
											fill="currentColor"
											aria-hidden="true"
										>
											<path d="M13.3333 16V8.88889H2.66667V16H0.888889C0.653141 16 0.427048 15.9064 0.260349 15.7397C0.0936505 15.573 0 15.3469 0 15.1111V0.888889C0 0.653141 0.0936505 0.427048 0.260349 0.260349C0.427048 0.0936505 0.653141 0 0.888889 0H12.4444L16 3.55556V15.1111C16 15.3469 15.9064 15.573 15.7397 15.7397C15.573 15.9064 15.3469 16 15.1111 16H13.3333ZM11.5556 16H4.44444V10.6667H11.5556V16Z" />
										</svg>
										<span class="leading-none pt-[0.5px]">{{ isSavingAccount ? 'Menyimpan...' : 'Simpan' }}</span>
									</button>
								</div>
							</form>
						</div>

						<!-- Card 3: Ganti Password -->
						<div class="rounded-[14px] bg-white p-6 shadow-sm ring-1 ring-[#d6e0ee]">
							<h2 class="font-poppins text-[19px] font-bold text-[#112340]">
								Ganti Password
							</h2>

							<form @submit.prevent="savePassword" class="mt-5 space-y-5">
								<div class="grid grid-cols-1 gap-6 md:grid-cols-2">
									<!-- Kolom Kiri: Password Saat Ini -->
									<div class="flex flex-col">
										<label class="block font-poppins text-[13px] font-semibold text-[#112340]">
											Password<span class="text-red-500">*</span>
										</label>
										<p class="mt-0.5 min-h-[18px] font-inter text-[12px] text-[#7188a3]">
											Masukkan password yang kamu gunakan sekarang
										</p>
										<div class="relative mt-2">
											<input
												v-model="formPassword.currentPassword"
												:type="showCurrentPassword ? 'text' : 'password'"
												placeholder="Masukkan password saat ini"
												:class="[
													'custom-input h-[46px] w-full rounded-[10px] pl-3.5 pr-10 font-inter text-[14px] text-[#112340] placeholder-[#a8bed4] transition-all duration-150',
													passwordErrors.currentPassword || passwordErrors.current_password ? 'border-red-400 bg-red-50/20' : ''
												]"
											/>
											<button
												type="button"
												@click="showCurrentPassword = !showCurrentPassword"
												class="absolute inset-y-0 right-0 flex items-center pr-3 focus:outline-none cursor-pointer"
												:title="showCurrentPassword ? 'Sembunyikan password' : 'Lihat password'"
											>
												<img
													v-if="showCurrentPassword"
													src="/assets/icons/shown.svg"
													alt="Lihat password"
													class="h-4 w-4 object-contain opacity-70 transition hover:opacity-100"
												/>
												<img
													v-else
													src="/assets/icons/hidden.svg"
													alt="Sembunyikan password"
													class="h-4 w-4 object-contain opacity-70 transition hover:opacity-100"
												/>
											</button>
										</div>
										<p v-if="passwordErrors.current_password || passwordErrors.currentPassword" class="mt-1 font-inter text-[11px] font-medium text-red-500">
											{{ passwordErrors.current_password || passwordErrors.currentPassword }}
										</p>
									</div>

									<!-- Kolom Kanan: Password Baru -->
									<div class="flex flex-col">
										<label class="block font-poppins text-[13px] font-semibold text-[#112340]">
											Password Baru<span class="text-red-500">*</span>
										</label>
										<p class="mt-0.5 min-h-[18px] font-inter text-[12px] text-[#7188a3]">
											Masukkan password baru kamu
										</p>
										<div class="relative mt-2">
											<input
												v-model="formPassword.newPassword"
												:type="showNewPassword ? 'text' : 'password'"
												placeholder="Minimal 8 karakter"
												:class="[
													'custom-input h-[46px] w-full rounded-[10px] pl-3.5 pr-10 font-inter text-[14px] text-[#112340] placeholder-[#a8bed4] transition-all duration-150',
													passwordErrors.new_password || passwordErrors.newPassword ? 'border-red-400 bg-red-50/20' : ''
												]"
											/>
											<button
												type="button"
												@click="showNewPassword = !showNewPassword"
												class="absolute inset-y-0 right-0 flex items-center pr-3 focus:outline-none cursor-pointer"
												:title="showNewPassword ? 'Sembunyikan password' : 'Lihat password'"
											>
												<img
													v-if="showNewPassword"
													src="/assets/icons/shown.svg"
													alt="Lihat password"
													class="h-4 w-4 object-contain opacity-70 transition hover:opacity-100"
												/>
												<img
													v-else
													src="/assets/icons/hidden.svg"
													alt="Sembunyikan password"
													class="h-4 w-4 object-contain opacity-70 transition hover:opacity-100"
												/>
											</button>
										</div>
										<p v-if="passwordErrors.new_password || passwordErrors.newPassword" class="mt-1 font-inter text-[11px] font-medium text-red-500">
											{{ passwordErrors.new_password || passwordErrors.newPassword }}
										</p>
									</div>

									<!-- Kolom Kanan Baris 2: Konfirmasi Password Baru -->
									<div class="flex flex-col md:col-start-2">
										<label class="block font-poppins text-[13px] font-semibold text-[#112340]">
											Konfirmasi Password Baru<span class="text-red-500">*</span>
										</label>
										<p class="mt-0.5 min-h-[18px] font-inter text-[12px] text-[#7188a3]">
											Masukkan ulang password baru kamu
										</p>
										<div class="relative mt-2">
											<input
												v-model="formPassword.confirmPassword"
												:type="showConfirmPassword ? 'text' : 'password'"
												placeholder="Ulangi password baru"
												:class="[
													'custom-input h-[46px] w-full rounded-[10px] pl-3.5 pr-10 font-inter text-[14px] text-[#112340] placeholder-[#a8bed4] transition-all duration-150',
													passwordErrors.confirmPassword || passwordErrors.new_password_confirmation ? 'border-red-400 bg-red-50/20' : ''
												]"
											/>
											<button
												type="button"
												@click="showConfirmPassword = !showConfirmPassword"
												class="absolute inset-y-0 right-0 flex items-center pr-3 focus:outline-none cursor-pointer"
												:title="showConfirmPassword ? 'Sembunyikan password' : 'Lihat password'"
											>
												<img
													v-if="showConfirmPassword"
													src="/assets/icons/shown.svg"
													alt="Lihat password"
													class="h-4 w-4 object-contain opacity-70 transition hover:opacity-100"
												/>
												<img
													v-else
													src="/assets/icons/hidden.svg"
													alt="Sembunyikan password"
													class="h-4 w-4 object-contain opacity-70 transition hover:opacity-100"
												/>
											</button>
										</div>
										<p v-if="passwordErrors.confirmPassword || passwordErrors.new_password_confirmation" class="mt-1 font-inter text-[11px] font-medium text-red-500">
											{{ passwordErrors.confirmPassword || passwordErrors.new_password_confirmation }}
										</p>
									</div>
								</div>

								<!-- Submit Button Ganti Password -->
								<div class="flex justify-end pt-3">
									<button
										type="submit"
										:disabled="!isPasswordChanged || isSavingPassword"
										:class="[
											'inline-flex items-center justify-center gap-2 rounded-[8px] px-6 py-2.5 font-poppins text-[14px] font-semibold transition duration-150',
											!isPasswordChanged || isSavingPassword
												? 'cursor-not-allowed bg-[#f0f4f9] text-[#8c9eb5] border-[1.5px] border-[#d6e0ee] shadow-none'
												: 'cursor-pointer bg-[#183669] text-white border-[1.5px] border-[#183669] shadow-sm hover:bg-[#122b54] hover:border-[#122b54] active:scale-[0.98]'
										]"
										:title="!isPasswordChanged ? 'Silakan isi form password terlebih dahulu' : 'Simpan password baru'"
									>
										<svg v-if="isSavingPassword" class="h-4 w-4 animate-spin text-[#8c9eb5]" fill="none" viewBox="0 0 24 24">
											<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
											<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
										</svg>
										<svg
											v-else
											class="h-4 w-4 shrink-0 translate-y-[0.5px]"
											viewBox="0 0 16 16"
											fill="currentColor"
											aria-hidden="true"
										>
											<path d="M13.3333 16V8.88889H2.66667V16H0.888889C0.653141 16 0.427048 15.9064 0.260349 15.7397C0.0936505 15.573 0 15.3469 0 15.1111V0.888889C0 0.653141 0.0936505 0.427048 0.260349 0.260349C0.427048 0.0936505 0.653141 0 0.888889 0H12.4444L16 3.55556V15.1111C16 15.3469 15.9064 15.573 15.7397 15.7397C15.573 15.9064 15.3469 16 15.1111 16H13.3333ZM11.5556 16H4.44444V10.6667H11.5556V16Z" />
										</svg>
										<span class="leading-none pt-[0.5px]">{{ isSavingPassword ? 'Menyimpan...' : 'Simpan' }}</span>
									</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>

			<!-- Lightbox Image Modal Preview -->
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
						v-if="previewingImage"
						class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/80 backdrop-blur-md p-4 transition-all"
						@click="closeImagePreview"
					>
						<Transition
							enter-active-class="ease-out duration-200"
							enter-from-class="opacity-0 scale-95"
							enter-to-class="opacity-100 scale-100"
							leave-active-class="ease-in duration-150"
							leave-from-class="opacity-100 scale-100"
							leave-to-class="opacity-0 scale-95"
						>
							<div
								v-if="previewingImage"
								class="relative flex items-center justify-center bg-transparent"
								@click.stop
							>
								<button
									type="button"
									@click="closeImagePreview"
									class="absolute top-3.5 right-3.5 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-md transition hover:scale-105 active:scale-95 focus:outline-none cursor-pointer"
									title="Tutup Preview"
								>
									<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
									</svg>
								</button>
								<img
									:src="previewingImage"
									alt="Zoomed Preview"
									class="max-h-[82vh] max-w-[88vw] w-auto h-auto min-w-[280px] sm:min-w-[460px] rounded-xl object-contain shadow-2xl"
								/>
							</div>
						</Transition>
					</div>
				</Transition>
			</Teleport>

			<!-- Toast Notification -->
			<ToastNotification
				:show="toast.show"
				:type="toast.type"
				:title="toast.title"
				:message="toast.message"
				@close="toast.show = false"
			/>
		</section>
	</MahasiswaLayout>
</template>

<style scoped>
/* Reset total dari plugin @tailwindcss/forms dan default browser outline */
.custom-input {
	border: 1px solid #d6e0ee !important;
	outline: none !important;
	box-shadow: none !important;
	--tw-ring-shadow: none !important;
	--tw-ring-offset-shadow: none !important;
	--tw-ring-color: transparent !important;
	--tw-ring-offset-color: transparent !important;
	background-color: #ffffff;
}

/* Hanya aktif hover ketika field TIDAK sedang fokus/aktif */
.custom-input:not(:focus):not(:focus-within):not([readonly]):not(:disabled):hover {
	border-color: #a6b7cb !important;
	background-color: #fafcff !important;
}

/* Ketika fokus / aktif, border tetap #183669 dan background tetap putih bersih, tidak ada efek hover */
.custom-input:focus,
.custom-input:focus-visible,
.custom-input:focus-within,
.custom-input:active,
.custom-input:focus:hover,
.custom-input:focus-within:hover {
	border-color: #183669 !important;
	outline: none !important;
	box-shadow: none !important;
	--tw-ring-shadow: none !important;
	--tw-ring-offset-shadow: none !important;
	--tw-ring-color: transparent !important;
	--tw-ring-offset-color: transparent !important;
	background-color: #ffffff !important;
}

.custom-input[readonly],
.custom-input:disabled {
	border-color: #d6e0ee !important;
	background-color: #f0f4f9 !important;
	color: #112340 !important;
	cursor: not-allowed !important;
	user-select: none !important;
	box-shadow: none !important;
	outline: none !important;
	--tw-ring-shadow: none !important;
	--tw-ring-color: transparent !important;
}

.custom-input[readonly]:hover,
.custom-input[readonly]:focus,
.custom-input[readonly]:active {
	border-color: #d6e0ee !important;
	background-color: #f0f4f9 !important;
	box-shadow: none !important;
	outline: none !important;
}
</style>