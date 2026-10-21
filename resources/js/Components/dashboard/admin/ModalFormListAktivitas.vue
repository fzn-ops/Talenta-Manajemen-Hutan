<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import DatePicker from '@/Components/dashboard/DatePicker.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue';
import RichTextEditor from '@/Components/dashboard/RichTextEditor.vue';

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
		default: () => ({
			id: null,
			judul: '',
			deskripsi: '',
			deadline: '',
			kategori: [],
			gambar: [],
		}),
	},
});

const emit = defineEmits(['close', 'submit']);

const listKategori = ['Profesional', 'Bisnis', 'Akademisi', 'Birokrat'];

const form = ref({
	id: null,
	judul: '',
	deskripsi: '',
	deadline: '',
	kategori: [],
	gambar: [],
});

const formError = ref('');
const errors = ref({});

// Image state
const fileInput = ref(null);
const replaceFileInput = ref(null);
const isDragging = ref(false);
const showDeleteImageModal = ref(false);
const imageIndexToDelete = ref(null);
const imageIndexToReplace = ref(null);

// Lightbox state
const showImageModal = ref(false);
const selectedImage = ref('');
const activeImageIndex = ref(0);
const isHoveringNav = ref(false);

// Backdrop tracking
const isBackdropClick = ref(false);

const syncFormData = () => {
	formError.value = '';
	errors.value = {};
	form.value = {
		id: props.initialData?.id || null,
		judul: props.initialData?.judul || '',
		deskripsi: props.initialData?.deskripsi || '',
		deadline: props.initialData?.deadline || '',
		kategori: props.initialData?.kategori ? [...props.initialData.kategori] : [],
		gambar: props.initialData?.gambar ? [...props.initialData.gambar] : [],
	};
};

watch(
	() => props.show,
	(isOpen) => {
		if (isOpen) {
			syncFormData();
		}
	},
	{ immediate: true }
);

const handleClose = () => {
	formError.value = '';
	errors.value = {};
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
	if (showImageModal.value) {
		if (e.key === 'Escape') {
			showImageModal.value = false;
			isHoveringNav.value = false;
		} else if (e.key === 'ArrowLeft') {
			prevImage();
		} else if (e.key === 'ArrowRight') {
			nextImage();
		}
		return;
	}
	if (e.key === 'Escape' && props.show) {
		if (showDeleteImageModal.value) {
			showDeleteImageModal.value = false;
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

// ==========================================
// LOGIC UPLOAD & PREVIEW GAMBAR
// ==========================================
const triggerFileInput = () => {
	fileInput.value?.click();
};

const triggerReplaceImage = (index) => {
	imageIndexToReplace.value = index;
	replaceFileInput.value?.click();
};

const processFiles = (files) => {
	if (!files || !files.length) return;

	const sisaSlot = 3 - form.value.gambar.length;
	const filesToAdd = files.slice(0, sisaSlot);

	filesToAdd.forEach((file) => {
		if (file.size > 10 * 1024 * 1024) {
			return;
		}

		const previewUrl = URL.createObjectURL(file);
		form.value.gambar.push({
			file: file,
			url: previewUrl,
			isNew: true,
		});
	});

	if (form.value.gambar.length > 0) {
		errors.value.gambar = '';
	}
};

const handleFileUpload = (event) => {
	const files = Array.from(event.target.files);
	processFiles(files);
	event.target.value = '';
};

const handleReplaceImage = (event) => {
    const file = event.target.files?.[0];
    if (!file) return;

    if (file.size > 10 * 1024 * 1024) {
        event.target.value = '';
        alert("Ukuran gambar maksimal 10MB ya wok!"); // Opsional, biar user tau
        return;
    }

    if (imageIndexToReplace.value !== null && imageIndexToReplace.value >= 0) {
        const old = form.value.gambar[imageIndexToReplace.value];
        
        // CEK GAMBAR LAMA SEBELUM DITIMPA
        if (old && typeof old === 'object') {
            if (old.isNew && old.url) {
                // Kalau gambar baru, cukup bersihin cache memory
                URL.revokeObjectURL(old.url);
            } else if (!old.isNew && old.id) {
                // NAH INI DIA! Kalau yang di-replace gambar dari DB, catat ID-nya ke keranjang hapus!
                deletedImageIds.value.push(old.id);
            }
        }

        const previewUrl = URL.createObjectURL(file);
        
        // Baru deh lu timpa posisinya sama gambar baru
        form.value.gambar[imageIndexToReplace.value] = {
            file: file,
            url: previewUrl,
            isNew: true,
        };
        errors.value.gambar = '';
    }
    
    event.target.value = '';
    imageIndexToReplace.value = null;
};

const handleImageDrop = (event) => {
	isDragging.value = false;
	const files = Array.from(event.dataTransfer?.files || []);
	processFiles(files);
};

const openDeleteImageModal = (index) => {
	imageIndexToDelete.value = index;
	showDeleteImageModal.value = true;
};

// Pastikan variabel ini udah lu deklarasiin di bagian atas (bareng ref yang lain)
const deletedImageIds = ref([]); 

const executeRemoveImage = () => {
    if (imageIndexToDelete.value !== null) {
        // Ambil gambar yang mau dihapus dari array
        const removed = form.value.gambar.splice(imageIndexToDelete.value, 1)[0];
        
        // CCTV 1: LIHAT APA ISI GAMBAR YANG DIHAPUS
        console.log("CCTV 1 - GAMBAR YANG DIHAPUS:", removed);

        if (removed) {
            if (removed.isNew) {
                // Bersihkan memory kalau gambar baru
                URL.revokeObjectURL(removed.url);
                console.log("Ini gambar baru, cache dibersihkan.");
            } else if (removed.id) {
                // NAH! Kalau ada ID-nya, masukin keranjang!
                deletedImageIds.value.push(removed.id);
                console.log("CCTV 2 - ID BERHASIL MASUK KERANJANG! Isi sekarang:", deletedImageIds.value);
            } else {
                console.warn("BAHAYA! Gambar lama tapi gak punya ID! Cek fungsi Edit lu.");
            }
        }
    }
    showDeleteImageModal.value = false;
    imageIndexToDelete.value = null;
};

const getImageSrc = (gbr) => {
	if (typeof gbr === 'object' && gbr.url) {
		return gbr.url;
	}
	if (typeof gbr === 'string') {
		if (gbr.startsWith('http://') || gbr.startsWith('https://') || gbr.startsWith('blob:') || gbr.startsWith('data:')) return gbr;
		if (gbr.startsWith('/storage/')) return gbr;
		if (gbr.startsWith('storage/')) return '/' + gbr;
		if (gbr.startsWith('/')) return gbr;
		return '/storage/' + gbr;
	}
	return `https://picsum.photos/seed/${gbr}/300/400`;
};

const getImageItemSrc = (item) => {
	if (!item) return '';
	if (typeof item === 'object' && item.url) return item.url;
	if (typeof item === 'object' && item.file) return URL.createObjectURL(item.file);
	if (typeof item === 'string') return getImageSrc(item);
	return `https://picsum.photos/seed/talenta/800/600`;
};

const openImage = (target, index = 0) => {
	if (typeof target === 'number') {
		index = target;
	} else if (typeof index === 'number' && index >= 0) {
		// keep index
	} else {
		index = form.gambar.indexOf(target);
	}
	activeImageIndex.value = index >= 0 && index < form.gambar.length ? index : 0;
	selectedImage.value = getImageItemSrc(form.gambar[activeImageIndex.value] || target);
	showImageModal.value = true;
};

const prevImage = () => {
	if (form.gambar.length <= 1) return;
	activeImageIndex.value = (activeImageIndex.value - 1 + form.gambar.length) % form.gambar.length;
	selectedImage.value = getImageItemSrc(form.gambar[activeImageIndex.value]);
};

const nextImage = () => {
	if (form.gambar.length <= 1) return;
	activeImageIndex.value = (activeImageIndex.value + 1) % form.gambar.length;
	selectedImage.value = getImageItemSrc(form.gambar[activeImageIndex.value]);
};

// Touch swipe support on mobile
const touchStartX = ref(0);
const touchEndX = ref(0);

const handleTouchStart = (e) => {
	if (!e.changedTouches || e.changedTouches.length === 0) return;
	touchStartX.value = e.changedTouches[0].clientX;
};

const handleTouchEnd = (e) => {
	if (!e.changedTouches || e.changedTouches.length === 0) return;
	touchEndX.value = e.changedTouches[0].clientX;
	const diff = touchEndX.value - touchStartX.value;
	if (Math.abs(diff) > 40 && form.gambar.length > 1) {
		if (diff > 0) {
			prevImage();
		} else {
			nextImage();
		}
	}
};

// ==========================================
// SUBMIT FORM VALIDATION
// ==========================================
const stripHtml = (html) => {
	if (!html) return '';
	const tmp = typeof document !== 'undefined' ? document.createElement('div') : null;
	if (!tmp) return html.replace(/<[^>]*>?/gm, '');
	tmp.innerHTML = html;
	return tmp.textContent || tmp.innerText || '';
};

const handleSubmit = () => {
	formError.value = '';
	errors.value = {};

	const inputJudul = (form.value.judul || '').trim();
	const inputDeskripsi = (form.value.deskripsi || '').trim();
	const textDeskripsi = stripHtml(inputDeskripsi).trim();
	const inputDeadline = form.value.deadline;

	if (!inputJudul) {
		errors.value.judul = 'Judul aktivitas wajib diisi.';
	}
	if (!inputDeskripsi || (!textDeskripsi && !inputDeskripsi.includes('<img'))) {
		errors.value.deskripsi = 'Deskripsi aktivitas wajib diisi.';
	}
	if (!inputDeadline) {
		errors.value.deadline = 'Deadline batas registrasi aktivitas wajib dipilih.';
	}
	if (!form.value.gambar || form.value.gambar.length === 0) {
		errors.value.gambar = 'Minimal 1 gambar pendukung wajib diunggah.';
	}
	if (!form.value.kategori || form.value.kategori.length === 0) {
		errors.value.kategori = 'Pilih minimal 1 kategori aktivitas.';
	}

	if (Object.keys(errors.value).length > 0) {
		return;
	}

	emit('submit', {
		id: form.value.id,
		judul: inputJudul,
		deskripsi: inputDeskripsi,
		deadline: inputDeadline,
		kategori: [...form.value.kategori],
		gambar: [...form.value.gambar],
		deleted_images: deletedImageIds.value,
	});
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
						class="relative w-full max-w-4xl my-auto transform rounded-[16px] sm:rounded-[20px] bg-white p-4 sm:p-7 shadow-2xl font-poppins border border-[#e2e8f0] overflow-visible"
					>
						<!-- Modal Title -->
						<div class="mb-4 sm:mb-6">
							<h2 class="text-[18px] sm:text-[22px] font-bold text-[#183669] tracking-tight">
								{{ isEditing ? 'Edit Aktivitas' : 'Form Tambah Aktivitas' }}
							</h2>
						</div>

						<!-- Error Alert Box -->
						<div v-if="formError" class="mb-4 rounded-[10px] bg-red-50 p-2.5 sm:p-3 font-inter text-[12px] sm:text-[13px] text-red-600 border border-red-200">
							{{ formError }}
						</div>

						<form @submit.prevent="handleSubmit" novalidate>
							<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
								<!-- Kolom Kiri -->
								<div class="flex flex-col gap-3.5 sm:gap-4">
									<!-- Judul Aktivitas -->
									<div>
										<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
											Judul Aktivitas<span class="text-red-500">*</span>
										</label>
										<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
											Masukkan judul aktivitas yang kamu ajukan di sini yah!
										</p>
										<input
											v-model="form.judul"
											type="text"
											placeholder="Pelatihan manajer KDMP"
											@input="errors.judul = ''"
											class="mt-1 sm:mt-1.5 h-[42px] sm:h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0"
											:class="errors.judul ? 'border-red-400 focus:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
										/>
										<p v-if="errors.judul" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
											<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
											</svg>
											<span>{{ errors.judul }}</span>
										</p>
									</div>

									<!-- Deskripsi (RichTextEditor) -->
									<div>
										<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
											Deskripsi<span class="text-red-500">*</span>
										</label>
										<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5 mb-1 sm:mb-1.5">
											Jelaskan gambaran kegiatan ini yah!
										</p>
										<RichTextEditor
											v-model="form.deskripsi"
											placeholder="Pelatihan manajer KDMP"
											min-height="115px"
											:has-error="!!errors.deskripsi"
											@update:modelValue="errors.deskripsi = ''"
										/>
										<p v-if="errors.deskripsi" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
											<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
											</svg>
											<span>{{ errors.deskripsi }}</span>
										</p>
									</div>

									<!-- Deadline -->
									<div>
										<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
											Deadline<span class="text-red-500">*</span>
										</label>
										<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
											Masukkan tanggal batas registrasi dari aktivitas kamu yah!
										</p>
										<div class="mt-1 sm:mt-1.5">
											<DatePicker
												v-model="form.deadline"
												:has-error="!!errors.deadline"
												placeholder="Pilih tanggal deadline"
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

								<!-- Kolom Kanan -->
								<div class="flex flex-col gap-4">
									<!-- Image Upload Box Container -->
									<div>
										<div class="flex items-center justify-between">
											<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
												Gambar<span class="text-red-500">*</span>
											</label>
											<span class="font-inter text-[11px] sm:text-[12px] font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-[#183669] border border-slate-200">
												{{ form.gambar.length }}/3
											</span>
										</div>
										<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5 mb-1.5">
											Masukkan gambar pendukung berupa jpg/png/jpeg! (MAX 10MB, 3 Gambar)
										</p>

										<!-- Input File Tersembunyi (Tambah Gambar) -->
										<input
											type="file"
											ref="fileInput"
											accept="image/png, image/jpeg, image/jpg"
											multiple
											class="hidden"
											@change="handleFileUpload"
										/>

										<!-- Input File Tersembunyi (Ganti / Edit Gambar Spesifik) -->
										<input
											type="file"
											ref="replaceFileInput"
											accept="image/png, image/jpeg, image/jpg"
											class="hidden"
											@change="handleReplaceImage"
										/>

										<!-- Image Upload Box Container -->
										<div
											@dragover.prevent="isDragging = true"
											@dragleave.prevent="isDragging = false"
											@drop.prevent="handleImageDrop"
											:class="[
												'mt-1.5 flex flex-col items-center justify-center rounded-[12px] border-2 border-dashed p-3.5 text-center transition-colors min-h-[145px]',
												errors.gambar
													? 'border-red-400 bg-red-50/20'
													: isDragging
													? 'border-[#183669] bg-[#183669]/5'
													: 'border-[#183669]/30 bg-[#fafcff] hover:border-[#183669]/60'
											]"
										>
											<!-- State 1: No images uploaded yet -->
											<div v-if="form.gambar.length === 0" class="flex flex-col items-center justify-center py-2.5">
												<svg class="h-9 w-9 text-[#8c9eb5]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
													<path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
												</svg>
												<p class="mt-1.5 font-inter text-[12px] text-[#7188a3]">
													Upload gambar atau seret gambar ke form ini
												</p>
												<p class="font-inter text-[10px] text-[#94a3b8] mt-0.5">
													(JPG/PNG, Rasio Disarankan 3:4)
												</p>
												<button
													type="button"
													@click="triggerFileInput"
													class="mt-2.5 rounded-[8px] border border-[#a6b7cb] bg-white px-5 py-1 font-inter text-[12px] font-semibold text-[#5a718d] transition hover:bg-slate-50 shadow-xs cursor-pointer"
												>
													Upload
												</button>
											</div>

											<!-- State 2: Images uploaded -->
											<div v-else class="w-full">
												<div class="grid grid-cols-3 gap-2.5 sm:gap-3 w-full items-center justify-items-center">
													<!-- Image Items in 3:4 Portrait Ratio -->
													<div
														v-for="(gbr, idx) in form.gambar"
														:key="idx"
														class="relative aspect-[3/4] w-full max-w-[95px] sm:max-w-[110px] rounded-[10px] border border-[#d6e0ee] bg-white overflow-hidden shadow-xs group"
													>
														<img :src="getImageSrc(gbr)" class="h-full w-full object-cover" />
														
														<!-- Action Overlay with Preview, Edit, and Delete -->
														<div class="absolute inset-0 bg-black/40 flex flex-col justify-between p-1.5 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 sm:group-focus-within:opacity-100 transition-opacity">
															<!-- Top Row: Delete Button -->
															<div class="flex justify-end w-full">
																<button
																	type="button"
																	@click.stop.prevent="openDeleteImageModal(idx)"
																	class="flex h-6 w-6 items-center justify-center rounded-full bg-red-600/90 text-white shadow-sm hover:bg-red-700 hover:scale-110 active:scale-95 transition cursor-pointer backdrop-blur-xs"
																	title="Hapus Gambar"
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
																	@click.stop.prevent="openImage(gbr, idx)"
																	class="flex h-6 w-6 items-center justify-center rounded-full bg-black/60 text-white shadow-sm hover:bg-black/85 hover:scale-110 active:scale-95 transition cursor-pointer backdrop-blur-xs"
																	title="Lihat Gambar Penuh"
																>
																	<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
																		<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
																	</svg>
																</button>

																<!-- Edit / Replace Button -->
																<button
																	type="button"
																	@click.stop.prevent="triggerReplaceImage(idx)"
																	class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-500/90 text-white shadow-sm hover:bg-amber-600 hover:scale-110 active:scale-95 transition cursor-pointer backdrop-blur-xs"
																	title="Ganti Gambar"
																>
																	<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
																		<path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
																	</svg>
																</button>
															</div>
														</div>
													</div>

													<!-- Slot Tambah Gambar jika belum 3 -->
													<div
														v-if="form.gambar.length < 3"
														@click="triggerFileInput"
														class="aspect-[3/4] w-full max-w-[95px] sm:max-w-[110px] rounded-[10px] border-2 border-dashed border-[#a6b7cb] hover:border-[#183669] hover:bg-slate-50/80 flex flex-col items-center justify-center cursor-pointer transition text-[#5a718d] group select-none"
														title="Tambah Gambar Lainnya"
													>
														<svg class="h-6 w-6 text-[#8c9eb5] group-hover:text-[#183669] transition-colors" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
															<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
														</svg>
														<span class="mt-1 font-inter text-[10.5px] sm:text-[11px] font-medium text-[#7188a3] group-hover:text-[#183669]">
															Tambah
														</span>
													</div>
												</div>
											</div>
										</div>
										<p v-if="errors.gambar" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
											<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
											</svg>
											<span>{{ errors.gambar }}</span>
										</p>
									</div>

									<!-- Kategori Checkbox -->
									<div>
										<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
											Kategori<span class="text-red-500">*</span>
										</label>
										<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
											Masukkan kemungkinan kategori dari aktivitas kamu yah!
										</p>
										<div class="grid grid-cols-2 gap-2 sm:gap-2.5 mt-2">
											<label
												v-for="kat in listKategori"
												:key="kat"
												class="flex items-center gap-2 p-2 rounded-[8px] border transition cursor-pointer select-none"
												:class="form.kategori.includes(kat) ? 'border-[#183669] bg-[#183669]/5' : (errors.kategori ? 'border-red-300 hover:border-red-400 bg-red-50/10' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff]')"
											>
												<input
													type="checkbox"
													:value="kat"
													v-model="form.kategori"
													@change="errors.kategori = ''"
													class="h-4 w-4 rounded border-[#d6e0ee] text-[#183669] focus:ring-0 focus:outline-none cursor-pointer"
												/>
												<span class="font-inter text-[12.5px] sm:text-[13px] font-medium text-[#1e3456]">{{ kat }}</span>
											</label>
										</div>
										<p v-if="errors.kategori" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
											<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
											</svg>
											<span>{{ errors.kategori }}</span>
										</p>
									</div>
								</div>
							</div>

							<!-- Tombol Aksi Form (Center di Mobile & Kanan di Desktop) -->
							<div class="mt-6 sm:mt-8 flex flex-row items-center justify-center sm:justify-end gap-3 sm:gap-4 pt-3 sm:pt-4 border-t border-slate-100">
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

	<!-- Modal Delete Confirmation for Image -->
	<DeleteModal
		:show="showDeleteImageModal"
		title="Hapus Gambar?"
		:message="`Apakah Anda yakin ingin menghapus foto lampiran ke-${imageIndexToDelete !== null ? imageIndexToDelete + 1 : ''} dari form aktivitas?`"
		@close="showDeleteImageModal = false"
		@confirm="executeRemoveImage"
	>
		<template #confirm-text>Hapus Gambar</template>
	</DeleteModal>

	<!-- MODAL GAMBAR (LIGHTBOX) -->
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
				v-if="showImageModal"
				class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/80 backdrop-blur-md p-4 transition-all"
				@click="showImageModal = false"
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
						v-if="showImageModal"
						class="relative flex flex-col items-center justify-center bg-transparent max-w-full max-h-full touch-pan-y select-none"
						@click.stop
						@touchstart="handleTouchStart"
						@touchend="handleTouchEnd"
						@mouseenter="isHoveringNav = true"
						@mouseleave="isHoveringNav = false"
					>
						<!-- Close button -->
						<button
							type="button"
							@click="showImageModal = false"
							class="absolute top-3.5 right-3.5 z-30 flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-md transition hover:scale-105 active:scale-95 focus:outline-none cursor-pointer"
							title="Tutup Preview"
						>
							<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
							</svg>
						</button>

						<!-- Prev button (slide in from left on image hover) -->
						<button
							v-if="form.gambar.length > 1"
							type="button"
							@click="prevImage"
							:class="[
								'absolute left-3.5 top-1/2 -translate-y-1/2 z-30 hidden sm:flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-lg transition-all duration-300 ease-out hover:scale-110 active:scale-95 focus:outline-none cursor-pointer',
								isHoveringNav ? 'opacity-100 translate-x-0 pointer-events-auto' : 'opacity-0 -translate-x-6 pointer-events-none'
							]"
							title="Gambar Sebelumnya"
						>
							<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
							</svg>
						</button>

						<!-- Next button (slide in from right on image hover) -->
						<button
							v-if="form.gambar.length > 1"
							type="button"
							@click="nextImage"
							:class="[
								'absolute right-3.5 top-1/2 -translate-y-1/2 z-30 hidden sm:flex h-10 w-10 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-lg transition-all duration-300 ease-out hover:scale-110 active:scale-95 focus:outline-none cursor-pointer',
								isHoveringNav ? 'opacity-100 translate-x-0 pointer-events-auto' : 'opacity-0 translate-x-6 pointer-events-none'
							]"
							title="Gambar Berikutnya"
						>
							<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
							</svg>
						</button>

						<!-- Image -->
						<img
							:src="selectedImage"
							alt="Preview Gambar"
							class="max-h-[85vh] max-w-[90vw] w-auto h-auto min-w-[280px] sm:min-w-[480px] rounded-xl object-contain shadow-2xl"
						/>

						<!-- Bottom Indicator (slide in from bottom on image hover on PC; always visible on mobile) -->
						<div
							v-if="form.gambar.length > 1"
							:class="[
								'absolute bottom-4 left-1/2 -translate-x-1/2 z-30 flex items-center gap-2 rounded-full bg-black/70 px-3.5 py-1.5 backdrop-blur-sm text-xs font-medium text-white shadow-lg transition-all duration-300 ease-out',
								isHoveringNav ? 'opacity-100 translate-y-0 pointer-events-auto' : 'opacity-100 sm:opacity-0 sm:translate-y-6 sm:pointer-events-none'
							]"
						>
							<span>Gambar {{ activeImageIndex + 1 }} dari {{ form.gambar.length }}</span>
							<div class="flex items-center gap-1.5 ml-1">
								<button
									v-for="(_, dotIdx) in form.gambar"
									:key="dotIdx"
									type="button"
									@click="activeImageIndex = dotIdx; selectedImage = getImageItemSrc(form.gambar[dotIdx])"
									class="h-2 rounded-full transition-all cursor-pointer"
									:class="activeImageIndex === dotIdx ? 'w-4 bg-white' : 'w-2 bg-white/40 hover:bg-white/70'"
									:title="'Buka Gambar ' + (dotIdx + 1)"
								/>
							</div>
						</div>
					</div>
				</Transition>
			</div>
		</Transition>
	</Teleport>
</template>
