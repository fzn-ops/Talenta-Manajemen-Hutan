<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import DatePicker from '@/Components/dashboard/DatePicker.vue';
import ImagePreviewModal from '@/Components/dashboard/ImagePreviewModal.vue';

const props = defineProps({
	show: {
		type: Boolean,
		default: false,
	},
	isEditMode: {
		type: Boolean,
		default: false,
	},
	initialData: {
		type: Object,
		default: null,
	},
});

const emit = defineEmits(['close', 'submit']);

// Form State
const form = ref({
	id: null,
	posisi: '',
	instansi: '',
	lokasi: '',
	deskripsi: '',
	kualifikasi: '',
	tautan: '',
	deadline: '',
	logo: null,
});

// Errors State
const errors = ref({
	posisi: '',
	instansi: '',
	lokasi: '',
	deskripsi: '',
	kualifikasi: '',
	tautan: '',
	deadline: '',
	logo: '',
});

// Logo Upload & Lightbox State
const logoInput = ref(null);
const logoPreview = ref(null);
const isDragging = ref(false);
const showImageModal = ref(false);

const openLogoModal = () => {
	if (logoPreview.value) {
		showImageModal.value = true;
	}
};

const removeLogo = () => {
	form.value.logo = null;
	logoPreview.value = null;
	if (logoInput.value) {
		logoInput.value.value = '';
	}
};

// Backdrop & ESC key tracking
const isBackdropClick = ref(false);

const handleClose = () => {
	showImageModal.value = false;
	resetForm();
	emit('close');
};

const handleBackdropMouseDown = (e) => {
	isBackdropClick.value = e.target === e.currentTarget;
};

const handleBackdropMouseUp = (e) => {
	if (isBackdropClick.value && e.target === e.currentTarget) {
		handleClose();
	}
	isBackdropClick.value = false;
};

const handleKeyDown = (e) => {
	if (e.key === 'Escape' && props.show) {
		if (showImageModal.value) {
			showImageModal.value = false;
		} else {
			handleClose();
		}
	}
};

onMounted(() => {
	document.addEventListener('keydown', handleKeyDown);
});

onBeforeUnmount(() => {
	document.removeEventListener('keydown', handleKeyDown);
});

// Textarea Heights & Resizing State (Limit min & max)
const MIN_DESKRIPSI_HEIGHT = 95;
const MAX_DESKRIPSI_HEIGHT = 450; // Limit diperluas agar nyaman untuk deskripsi pekerjaan

const MIN_KUALIFIKASI_HEIGHT = 90;
const MAX_KUALIFIKASI_HEIGHT = 320;

const MIN_LOKASI_HEIGHT = 90;
const MAX_LOKASI_HEIGHT = 320;

const deskripsiHeight = ref(95);
const kualifikasiHeight = ref(95);
const lokasiHeight = ref(95);
const deskripsiRef = ref(null);
const kualifikasiRef = ref(null);
const lokasiRef = ref(null);
const isResizing = ref(false);

const startResize = (field, e) => {
	if (e.type === 'mousedown') {
		e.preventDefault();
	}
	e.stopPropagation();
	isResizing.value = true;
	if (typeof document !== 'undefined') {
		document.body.style.userSelect = 'none';
	}

	const isDeskripsi = field === 'deskripsi';
	const isLokasi = field === 'lokasi';
	const targetRef = isDeskripsi ? deskripsiRef.value : (isLokasi ? lokasiRef.value : kualifikasiRef.value);
	const startY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
	const startHeight = targetRef ? targetRef.offsetHeight : (isDeskripsi ? deskripsiHeight.value : (isLokasi ? lokasiHeight.value : kualifikasiHeight.value));
	const minH = isDeskripsi ? MIN_DESKRIPSI_HEIGHT : (isLokasi ? MIN_LOKASI_HEIGHT : MIN_KUALIFIKASI_HEIGHT);
	const maxH = isDeskripsi ? MAX_DESKRIPSI_HEIGHT : (isLokasi ? MAX_LOKASI_HEIGHT : MAX_KUALIFIKASI_HEIGHT);

	const onMouseMove = (moveEvent) => {
		if (!isResizing.value) return;
		if (moveEvent.cancelable && moveEvent.type.includes('touch')) {
			moveEvent.preventDefault();
		}
		const clientY = moveEvent.type.includes('touch') ? moveEvent.touches[0].clientY : moveEvent.clientY;
		const newHeight = Math.max(minH, Math.min(maxH, startHeight + (clientY - startY)));
		if (isDeskripsi) {
			deskripsiHeight.value = newHeight;
		} else if (isLokasi) {
			lokasiHeight.value = newHeight;
		} else {
			kualifikasiHeight.value = newHeight;
		}
	};

	const onMouseUp = (upEvent) => {
		upEvent?.stopPropagation();
		isResizing.value = false;
		if (typeof document !== 'undefined') {
			document.body.style.userSelect = '';
		}
		window.removeEventListener('mousemove', onMouseMove);
		window.removeEventListener('mouseup', onMouseUp);
		window.removeEventListener('touchmove', onMouseMove);
		window.removeEventListener('touchend', onMouseUp);
	};

	window.addEventListener('mousemove', onMouseMove);
	window.addEventListener('mouseup', onMouseUp);
	window.addEventListener('touchmove', onMouseMove, { passive: false });
	window.addEventListener('touchend', onMouseUp);
};

const resetErrors = () => {
	errors.value = {
		posisi: '',
		instansi: '',
		lokasi: '',
		deskripsi: '',
		kualifikasi: '',
		tautan: '',
		deadline: '',
		logo: '',
	};
};

const resetForm = () => {
	form.value = {
		id: null,
		posisi: '',
		instansi: '',
		lokasi: '',
		deskripsi: '',
		kualifikasi: '',
		tautan: '',
		deadline: '',
		logo: null,
	};
	logoPreview.value = null;
	deskripsiHeight.value = 95;
	kualifikasiHeight.value = 95;
	lokasiHeight.value = 95;
	if (logoInput.value) {
		logoInput.value.value = '';
	}
	resetErrors();
};

// Sync form with props
watch(
	() => props.show,
	(isOpen) => {
		if (isOpen) {
			if (props.initialData) {
				form.value = {
					id: props.initialData.id ?? null,
					posisi: props.initialData.posisi ?? '',
					instansi: props.initialData.instansi ?? '',
					lokasi: props.initialData.lokasi ?? '',
					deskripsi: props.initialData.deskripsi ?? '',
					kualifikasi: props.initialData.kualifikasi ?? '',
					tautan: props.initialData.tautan ?? '',
					deadline: props.initialData.deadline ?? '',
					logo: props.initialData.logo ?? null,
				};
				if (props.initialData.logo && typeof props.initialData.logo === 'object') {
					logoPreview.value = URL.createObjectURL(props.initialData.logo);
				} else if (props.initialData.logo) {
					const img = props.initialData.logo;
					logoPreview.value = (img.startsWith('http') || img.startsWith('/'))
						? img
						: `https://picsum.photos/seed/${img}/800/600`;
				} else {
					logoPreview.value = null;
				}
			} else {
				resetForm();
			}
			resetErrors();
		} else {
			resetForm();
		}
	},
	{ immediate: true }
);

const triggerLogoUpload = () => {
	logoInput.value?.click();
};

const processLogoFile = (file) => {
	if (file) {
		if (file.size > 10 * 1024 * 1024) {
			errors.value.logo = 'Ukuran gambar maksimal 10MB!';
			return;
		}
		form.value.logo = file;
		logoPreview.value = URL.createObjectURL(file);
		errors.value.logo = '';
	}
};

const handleLogoUpload = (event) => {
	const file = event.target.files?.[0];
	processLogoFile(file);
};

const handleLogoDrop = (event) => {
	isDragging.value = false;
	const file = event.dataTransfer.files?.[0];
	processLogoFile(file);
};

const validateForm = () => {
	resetErrors();
	let isValid = true;

	if (!form.value.posisi || !form.value.posisi.trim()) {
		errors.value.posisi = 'Posisi wajib diisi!';
		isValid = false;
	}
	if (!form.value.instansi || !form.value.instansi.trim()) {
		errors.value.instansi = 'Nama instansi wajib diisi!';
		isValid = false;
	}
	if (!form.value.lokasi || !form.value.lokasi.trim()) {
		errors.value.lokasi = 'Lokasi wajib diisi!';
		isValid = false;
	}
	if (!form.value.deskripsi || !form.value.deskripsi.trim()) {
		errors.value.deskripsi = 'Deskripsi wajib diisi!';
		isValid = false;
	}
	if (!form.value.kualifikasi || !form.value.kualifikasi.trim()) {
		errors.value.kualifikasi = 'Kualifikasi wajib diisi!';
		isValid = false;
	}
	if (!form.value.tautan || !form.value.tautan.trim()) {
		errors.value.tautan = 'Tautan registrasi wajib diisi!';
		isValid = false;
	}
	if (!form.value.deadline) {
		errors.value.deadline = 'Deadline wajib dipilih!';
		isValid = false;
	}
	if (!form.value.logo) {
		errors.value.logo = 'Logo instansi wajib diunggah!';
		isValid = false;
	}

	return isValid;
};

const handleSubmit = () => {
	if (!validateForm()) return;
	emit('submit', { ...form.value });
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
				class="fixed inset-0 z-50 overflow-y-auto overscroll-contain bg-slate-900/40 backdrop-blur-xs p-3 sm:p-6 flex justify-center items-start min-h-screen"
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
						class="relative w-full max-w-4xl max-h-[92vh] flex flex-col my-auto transform rounded-[16px] sm:rounded-[20px] bg-white p-4 sm:p-7 shadow-2xl font-poppins border border-[#e2e8f0] overflow-hidden"
					>
						<!-- Modal Title -->
						<div class="mb-4 sm:mb-6 shrink-0">
							<h2 class="text-[18px] sm:text-[22px] font-bold text-[#183669] tracking-tight">
								{{ isEditMode ? 'Edit Karir' : 'Form Tambah Karir' }}
							</h2>
						</div>

						<form @submit.prevent="handleSubmit" novalidate class="flex flex-col flex-1 min-h-0">
							<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6 overflow-y-auto pr-1">
								<!-- Kolom Kiri: Informasi Inti & Persyaratan -->
								<div class="flex flex-col gap-3.5 sm:gap-4">
									<!-- Posisi -->
									<div>
										<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
											Posisi<span class="text-red-500">*</span>
										</label>
										<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
											Masukkan posisi karir yang dibuka yah!
										</p>
										<input
											type="text"
											v-model="form.posisi"
											@input="errors.posisi = ''"
											placeholder="Contoh: Frontend Developer"
											class="mt-1 sm:mt-1.5 h-[42px] sm:h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0"
											:class="errors.posisi ? 'border-red-400 focus:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
										/>
										<p v-if="errors.posisi" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
											<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
											</svg>
											<span>{{ errors.posisi }}</span>
										</p>
									</div>

									<!-- Deskripsi -->
									<div>
										<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
											Deskripsi<span class="text-red-500">*</span>
										</label>
										<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
											Jelaskan gambaran pekerjaan ini yah!
										</p>
										<div
											class="mt-1 sm:mt-1.5 relative w-full rounded-[10px] border transition-colors duration-150 overflow-hidden"
											:class="errors.deskripsi ? 'border-red-400 focus-within:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus-within:border-[#183669] focus-within:bg-white'"
										>
											<textarea
												ref="deskripsiRef"
												v-model="form.deskripsi"
												@input="errors.deskripsi = ''"
												placeholder="Deskripsi kegiatan atau pekerjaan..."
												:style="{ height: `${deskripsiHeight}px`, minHeight: '95px', maxHeight: '450px' }"
												class="w-full bg-transparent px-3.5 pt-2.5 pb-2 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] focus:outline-none resize-none overflow-y-auto block border-0 ring-0 focus:ring-0 focus:border-0 shadow-none"
											></textarea>
											<!-- Bottom Full-Width Resize Handle -->
											<div
												@mousedown="startResize('deskripsi', $event)"
												@touchstart.passive="startResize('deskripsi', $event)"
												class="group/handle flex h-[18px] w-full cursor-ns-resize items-center justify-center bg-[#f8fafc] hover:bg-[#e8edf5] active:bg-[#dbe4f2] border-t border-[#edf2f7] transition-colors select-none"
												style="touch-action: none;"
												title="Tarik ke bawah untuk memanjangkan kotak deskripsi"
											>
												<div class="h-[4px] w-12 rounded-full bg-[#cbd5e1] group-hover/handle:bg-[#183669]/60 group-hover/handle:w-16 group-active/handle:bg-[#183669] transition-all duration-200"></div>
											</div>
										</div>
										<p v-if="errors.deskripsi" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
											<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
											</svg>
											<span>{{ errors.deskripsi }}</span>
										</p>
									</div>

									<!-- Kualifikasi -->
									<div>
										<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
											Kualifikasi<span class="text-red-500">*</span>
										</label>
										<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
											Sebutkan kualifikasi yang dibutuhkan!
										</p>
										<div
											class="mt-1 sm:mt-1.5 relative w-full rounded-[10px] border transition-colors duration-150 overflow-hidden"
											:class="errors.kualifikasi ? 'border-red-400 focus-within:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus-within:border-[#183669] focus-within:bg-white'"
										>
											<textarea
												ref="kualifikasiRef"
												v-model="form.kualifikasi"
												@input="errors.kualifikasi = ''"
												placeholder="S1 Semester 7, menguasai skill..."
												:style="{ height: `${kualifikasiHeight}px`, minHeight: '90px', maxHeight: '320px' }"
												class="w-full bg-transparent px-3.5 pt-2.5 pb-2 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] focus:outline-none resize-none overflow-y-auto block border-0 ring-0 focus:ring-0 focus:border-0 shadow-none"
											></textarea>
											<!-- Bottom Full-Width Resize Handle -->
											<div
												@mousedown="startResize('kualifikasi', $event)"
												@touchstart.passive="startResize('kualifikasi', $event)"
												class="group/handle flex h-[18px] w-full cursor-ns-resize items-center justify-center bg-[#f8fafc] hover:bg-[#e8edf5] active:bg-[#dbe4f2] border-t border-[#edf2f7] transition-colors select-none"
												style="touch-action: none;"
												title="Tarik ke bawah untuk memanjangkan kotak kualifikasi"
											>
												<div class="h-[4px] w-12 rounded-full bg-[#cbd5e1] group-hover/handle:bg-[#183669]/60 group-hover/handle:w-16 group-active/handle:bg-[#183669] transition-all duration-200"></div>
											</div>
										</div>
										<p v-if="errors.kualifikasi" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
											<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
											</svg>
											<span>{{ errors.kualifikasi }}</span>
										</p>
									</div>
								</div>

								<!-- Kolom Kanan: Detail Instansi, Lokasi & Registrasi -->
								<div class="flex flex-col gap-3.5 sm:gap-4">
									<!-- Nama Instansi -->
									<div>
										<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
											Nama Instansi<span class="text-red-500">*</span>
										</label>
										<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
											Masukkan nama instansi / perusahaan di sini!
										</p>
										<input
											type="text"
											v-model="form.instansi"
											@input="errors.instansi = ''"
											placeholder="Contoh: PT Teknologi Bangsa"
											class="mt-1 sm:mt-1.5 h-[42px] sm:h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0"
											:class="errors.instansi ? 'border-red-400 focus-border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
										/>
										<p v-if="errors.instansi" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
											<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
											</svg>
											<span>{{ errors.instansi }}</span>
										</p>
									</div>

									<!-- Lokasi -->
									<div>
										<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
											Lokasi<span class="text-red-500">*</span>
										</label>
										<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
											Sebutkan lokasi penempatan pekerjaan ini!
										</p>
										<div
											class="mt-1 sm:mt-1.5 relative w-full rounded-[10px] border transition-colors duration-150 overflow-hidden"
											:class="errors.lokasi ? 'border-red-400 focus-within:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus-within:border-[#183669] focus-within:bg-white'"
										>
											<textarea
												ref="lokasiRef"
												v-model="form.lokasi"
												@input="errors.lokasi = ''"
												placeholder="Contoh: Jakarta Selatan / Hybrid / Remote..."
												:style="{ height: `${lokasiHeight}px`, minHeight: '90px', maxHeight: '320px' }"
												class="w-full bg-transparent px-3.5 pt-2.5 pb-2 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] focus:outline-none resize-none overflow-y-auto block border-0 ring-0 focus:ring-0 focus:border-0 shadow-none"
											></textarea>
											<!-- Bottom Full-Width Resize Handle -->
											<div
												@mousedown="startResize('lokasi', $event)"
												@touchstart.passive="startResize('lokasi', $event)"
												class="group/handle flex h-[18px] w-full cursor-ns-resize items-center justify-center bg-[#f8fafc] hover:bg-[#e8edf5] active:bg-[#dbe4f2] border-t border-[#edf2f7] transition-colors select-none"
												style="touch-action: none;"
												title="Tarik ke bawah untuk memanjangkan kotak lokasi"
											>
												<div class="h-[4px] w-12 rounded-full bg-[#cbd5e1] group-hover/handle:bg-[#183669]/60 group-hover/handle:w-16 group-active/handle:bg-[#183669] transition-all duration-200"></div>
											</div>
										</div>
										<p v-if="errors.lokasi" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
											<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
											</svg>
											<span>{{ errors.lokasi }}</span>
										</p>
									</div>

									<!-- Grid 2 Kolom untuk Tautan & Deadline -->
									<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
										<!-- Tautan Registrasi -->
										<div>
											<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
												Tautan Registrasi<span class="text-red-500">*</span>
											</label>
											<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
												Masukkan tautan pendaftaran!
											</p>
											<input
												type="text"
												v-model="form.tautan"
												@input="errors.tautan = ''"
												placeholder="https://..."
												class="mt-1 sm:mt-1.5 h-[42px] sm:h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0"
												:class="errors.tautan ? 'border-red-400 focus:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
											/>
											<p v-if="errors.tautan" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
												<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
													<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
												</svg>
												<span>{{ errors.tautan }}</span>
											</p>
										</div>

										<!-- Deadline (DatePicker) -->
										<div>
											<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
												Deadline<span class="text-red-500">*</span>
											</label>
											<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
												Batas waktu pendaftaran!
											</p>
											<div class="mt-1 sm:mt-1.5">
												<DatePicker
													v-model="form.deadline"
													:has-error="!!errors.deadline"
													placeholder="Pilih deadline"
													@update:modelValue="errors.deadline = ''"
												/>
											</div>
											<p v-if="errors.deadline" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
												<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
													<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
												</svg>
												<span>{{ errors.deadline }}</span>
											</p>
										</div>
									</div>

									<!-- Logo Upload -->
									<div>
										<div class="flex items-center justify-between">
											<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
												Logo Instansi<span class="text-red-500">*</span>
											</label>
											<span v-if="logoPreview" class="font-inter text-[11px] sm:text-[12px] font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-[#183669] border border-slate-200">
												1/1
											</span>
										</div>
										<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5 mb-1.5">
											Masukkan logo instansi berupa JPG/PNG! (MAX 10MB, Rasio 1:1)
										</p>

										<input
											type="file"
											ref="logoInput"
											accept="image/png, image/jpeg, image/jpg"
											class="hidden"
											@change="handleLogoUpload"
										/>

										<div
											@dragover.prevent="isDragging = true"
											@dragleave.prevent="isDragging = false"
											@drop.prevent="handleLogoDrop"
											:class="[
												'mt-1.5 flex flex-col items-center justify-center rounded-[12px] border-2 border-dashed p-3 text-center transition-colors min-h-[110px]',
												errors.logo
													? 'border-red-400 bg-red-50/20'
													: isDragging
													? 'border-[#183669] bg-[#183669]/5'
													: 'border-[#183669]/30 bg-[#fafcff] hover:border-[#183669]/60'
											]"
										>
											<!-- State 1: No logo uploaded yet -->
											<div v-if="!logoPreview" class="flex flex-col items-center justify-center py-1.5">
												<svg class="h-8 w-8 text-[#8c9eb5]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
													<path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
												</svg>
												<p class="mt-1 font-inter text-[11.5px] text-[#7188a3]">
													Upload logo atau seret gambar ke form ini
												</p>
												<button
													type="button"
													@click="triggerLogoUpload"
													class="mt-2 rounded-[8px] border border-[#a6b7cb] bg-white px-4 py-0.5 font-inter text-[11.5px] font-semibold text-[#5a718d] transition hover:bg-slate-50 shadow-xs cursor-pointer"
												>
													Upload
												</button>
											</div>

											<!-- State 2: Logo uploaded (Square 1:1) -->
											<div v-else class="w-full flex items-center justify-center py-0.5">
												<div
													class="relative aspect-square w-full max-w-[95px] sm:max-w-[105px] rounded-[10px] border border-[#d6e0ee] bg-white overflow-hidden shadow-xs group"
												>
													<img :src="logoPreview" alt="Logo Instansi" class="h-full w-full object-contain p-2" />
													
													<!-- Action Overlay with Preview, Edit, and Delete -->
													<div class="absolute inset-0 bg-black/40 flex flex-col justify-between p-1.5 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 sm:group-focus-within:opacity-100 transition-opacity">
														<!-- Top Row: Delete Button -->
														<div class="flex justify-end w-full">
															<button
																type="button"
																@click.stop.prevent="removeLogo"
																class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600/90 text-white shadow-sm hover:bg-red-700 hover:scale-110 active:scale-95 transition cursor-pointer backdrop-blur-xs"
																title="Hapus Logo"
															>
																<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
																	<path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
																</svg>
															</button>
														</div>

														<!-- Bottom Row: Preview & Edit Buttons -->
														<div class="flex items-center justify-center gap-1.5 w-full pb-0.5">
															<!-- Preview / Zoom Button -->
															<button
																type="button"
																@click.stop.prevent="openLogoModal"
																class="flex h-6 w-6 items-center justify-center rounded-full bg-black/60 text-white shadow-sm hover:bg-black/85 hover:scale-110 active:scale-95 transition cursor-pointer backdrop-blur-xs"
																title="Lihat Logo Penuh"
															>
																<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
																	<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
																</svg>
															</button>

															<!-- Edit / Replace Button -->
															<button
																type="button"
																@click.stop.prevent="triggerLogoUpload"
																class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-500/90 text-white shadow-sm hover:bg-amber-600 hover:scale-110 active:scale-95 transition cursor-pointer backdrop-blur-xs"
																title="Ganti Logo"
															>
																<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
																	<path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
																</svg>
															</button>
														</div>
													</div>
												</div>
											</div>
										</div>
										<p v-if="errors.logo" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
											<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
											</svg>
											<span>{{ errors.logo }}</span>
										</p>
									</div>
								</div>
							</div>

							<!-- Tombol Aksi Form (Center di Mobile & Kanan di Desktop) -->
							<div class="mt-5 sm:mt-6 shrink-0 flex flex-row items-center justify-center sm:justify-end gap-3 sm:gap-4 pt-3 sm:pt-4 border-t border-slate-100">
								<button
									type="button"
									@click="handleClose"
									class="h-[42px] sm:h-[44px] min-w-[120px] sm:min-w-[140px] px-5 sm:px-6 rounded-[10px] border border-[#d6e0ee] bg-white font-poppins text-[13px] sm:text-[14px] font-bold text-[#183669] transition hover:border-[#183669] hover:bg-slate-50 focus:border-[#183669] focus:outline-none active:scale-98 cursor-pointer select-none"
								>
									Kembali
								</button>
								<button
									type="submit"
									class="h-[42px] sm:h-[44px] min-w-[120px] sm:min-w-[140px] px-5 sm:px-6 rounded-[10px] bg-[#183669] font-poppins text-[13px] sm:text-[14px] font-bold text-white shadow-sm transition hover:bg-[#122b54] active:scale-98 focus:outline-none cursor-pointer select-none whitespace-nowrap"
								>
									Simpan
								</button>
							</div>
						</form>
					</div>
				</Transition>
			</div>
		</Transition>
	</Teleport>

	<!-- MODAL GAMBAR (LIGHTBOX) TERPUSAT -->
	<ImagePreviewModal
		:show="showImageModal"
		:images="logoPreview"
		label="Logo"
		@close="showImageModal = false"
	/>
</template>
