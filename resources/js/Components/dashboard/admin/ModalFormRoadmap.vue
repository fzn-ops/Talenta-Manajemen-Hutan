<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';

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
		default: () => ({ title: '', category: '', thumbnail: '' }),
	},
	editingId: {
		type: [Number, String, null],
		default: null,
	},
});

const emit = defineEmits(['close', 'submit']);

const categoryOptions = ['Profesional', 'Bisnis', 'Birokrasi', 'Akademisi'];

const form = ref({
	title: '',
	category: '',
	thumbnail: '',
});

const formError = ref('');
const errors = ref({});
const fileInputRef = ref(null);
const thumbnailPreview = ref('');
const isDragging = ref(false);
const previewingImage = ref(null);
const isCategoryDropdownOpen = ref(false);
const categoryDropdownRef = ref(null);

const openImagePreview = (imgUrl) => {
	previewingImage.value = imgUrl;
};
const closeImagePreview = () => {
	previewingImage.value = null;
};

const selectCategory = (cat) => {
	form.value.category = cat;
	errors.value.category = '';
	isCategoryDropdownOpen.value = false;
};

const handleDocumentClick = (e) => {
	if (categoryDropdownRef.value && !categoryDropdownRef.value.contains(e.target)) {
		isCategoryDropdownOpen.value = false;
	}
};

const triggerFileInput = () => {
	fileInputRef.value?.click();
};

const handleFileChange = (e) => {
	const file = e.target.files?.[0];
	if (!file) return;
	processFile(file);
	e.target.value = '';
};

const handleDrop = (e) => {
	isDragging.value = false;
	const file = e.dataTransfer.files?.[0];
	if (!file) return;
	processFile(file);
};

const processFile = (file) => {
	if (!file.type.match(/^image\/(jpeg|png|jpg)$/i)) {
		errors.value.thumbnail = 'Harap upload file gambar yang valid (PNG, JPG, JPEG).';
		return;
	}

	if (file.size > 5 * 1024 * 1024) {
		errors.value.thumbnail = 'Ukuran gambar tidak boleh melebihi 5MB.';
		return;
	}

	errors.value.thumbnail = '';

	const reader = new FileReader();
	reader.onload = (event) => {
		thumbnailPreview.value = event.target.result;
		form.value.thumbnail = event.target.result;
	};
	reader.readAsDataURL(file);
};

const removeThumbnail = () => {
	thumbnailPreview.value = '';
	form.value.thumbnail = '';
	if (fileInputRef.value) {
		fileInputRef.value.value = '';
	}
};

watch(
	() => props.show,
	(isOpen) => {
		if (isOpen) {
			formError.value = '';
			errors.value = {};
			isCategoryDropdownOpen.value = false;
			thumbnailPreview.value = props.initialData?.thumbnail || '';
			form.value = {
				title: props.initialData?.title || '',
				category: props.initialData?.category || '',
				thumbnail: props.initialData?.thumbnail || '',
			};
			if (fileInputRef.value) {
				fileInputRef.value.value = '';
			}
		}
	},
	{ immediate: true }
);

const handleClose = () => {
	formError.value = '';
	errors.value = {};
	isCategoryDropdownOpen.value = false;
	emit('close');
};

const handleSubmit = () => {
	formError.value = '';
	errors.value = {};

	const inputTitle = form.value.title.trim();
	const inputCategory = form.value.category.trim();

	if (!inputTitle) {
		errors.value.title = 'Nama Roadmap wajib diisi.';
	}
	if (!inputCategory) {
		errors.value.category = 'Kategori wajib dipilih.';
	}
	if (!form.value.thumbnail) {
		errors.value.thumbnail = 'Thumbnail cover roadmap wajib diunggah.';
	}

	if (Object.keys(errors.value).length > 0) {
		return;
	}

	emit('submit', {
		title: inputTitle,
		category: inputCategory,
		thumbnail: form.value.thumbnail,
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
						class="relative w-full max-w-[560px] my-auto transform rounded-[16px] sm:rounded-[20px] bg-white p-5 sm:p-8 shadow-2xl font-poppins border border-[#e2e8f0] overflow-visible"
					>
						<!-- Modal Header -->
						<div class="text-center">
							<h2 class="text-[18px] sm:text-[22px] font-bold text-[#183669] tracking-tight">
								{{ isEditing ? 'Form Edit Roadmap' : 'Form Tambah Roadmap' }}
							</h2>
						</div>

						<!-- Error Alert Box -->
						<div v-if="formError" class="mt-3 sm:mt-4 rounded-[10px] bg-red-50 p-2.5 sm:p-3 font-inter text-[12px] sm:text-[13px] text-red-600 border border-red-200">
							{{ formError }}
						</div>

						<form @submit.prevent="handleSubmit" novalidate class="mt-4 sm:mt-6 space-y-3 sm:space-y-4">
							<!-- 1. Nama Roadmap -->
							<div>
								<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
									Nama Roadmap<span class="text-red-500">*</span>
								</label>
								<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">Masukkan nama lengkap atau judul roadmap</p>
								<input
									v-model="form.title"
									type="text"
									placeholder="Contoh: Persiapan Karir Software Engineer"
									@input="errors.title = ''"
									class="mt-1 sm:mt-1.5 h-[42px] sm:h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[13px] sm:text-[14px] text-[#1e3456] placeholder-[#94a3b8] transition-colors duration-150 focus:outline-none focus:ring-0"
									:class="errors.title ? 'border-red-400 focus:border-red-500 bg-red-50/20' : 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff] focus:border-[#183669] focus:bg-white'"
								/>
								<p v-if="errors.title" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
									<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
									</svg>
									<span>{{ errors.title }}</span>
								</p>
							</div>

							<!-- 2. Kategori Roadmap (Custom Dropdown UI) -->
							<div ref="categoryDropdownRef" class="relative">
								<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
									Kategori<span class="text-red-500">*</span>
								</label>
								<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">Pilih kategori hasil talent mapping</p>
								<div class="relative mt-1 sm:mt-1.5">
									<button
										type="button"
										@click="isCategoryDropdownOpen = !isCategoryDropdownOpen"
										class="h-[42px] sm:h-[44px] w-full rounded-[10px] border bg-white px-3.5 font-inter text-[13px] sm:text-[14px] transition-colors duration-150 flex items-center justify-between cursor-pointer focus:outline-none focus:ring-0 select-none text-left"
										:class="[
											errors.category
												? 'border-red-400 bg-red-50/20'
												: (isCategoryDropdownOpen
													? 'border-[#183669] bg-white'
													: 'border-[#d6e0ee] hover:border-[#a6b7cb] hover:bg-[#fafcff]')
										]"
									>
										<span :class="form.category ? 'text-[#1e3456] font-medium' : 'text-[#94a3b8]'">
											{{ form.category || 'Pilih Kategori' }}
										</span>
										<svg
											:class="['h-4 w-4 text-[#183669] transition-transform duration-150 shrink-0', isCategoryDropdownOpen ? 'rotate-180' : '']"
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
											v-if="isCategoryDropdownOpen"
											class="absolute left-0 top-full mt-1.5 z-50 w-full rounded-[12px] border border-[#d6e0ee] bg-white p-1.5 shadow-2xl ring-1 ring-black/5"
										>
											<button
												v-for="cat in categoryOptions"
												:key="cat"
												type="button"
												@click="selectCategory(cat)"
												:class="[
													'flex w-full items-center justify-between px-3 py-2 rounded-[8px] text-left font-inter text-[13px] transition-colors cursor-pointer',
													form.category === cat
														? 'bg-[#183669]/10 font-semibold text-[#183669]'
														: 'text-[#2c4363] hover:bg-[#f1f5f9]'
												]"
											>
												<span>{{ cat }}</span>
												<svg
													v-if="form.category === cat"
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
								<p v-if="errors.category" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
									<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
									</svg>
									<span>{{ errors.category }}</span>
								</p>
							</div>

							<!-- 3. Thumbnail Cover Roadmap -->
							<div>
								<div class="flex items-center justify-between">
									<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
										Thumbnail Cover<span class="text-red-500">*</span>
									</label>
								</div>
								<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
									Pilih gambar sebagai Thumbnail utama (Disarankan rasio 16:9, cth: 1280 &times; 720 px)
								</p>

								<!-- Image Upload Box Container (Identical to ModalFormAktivitasDosen) -->
								<div
									@dragover.prevent="isDragging = true"
									@dragleave.prevent="isDragging = false"
									@drop.prevent="handleDrop"
									:class="[
										'mt-1 sm:mt-1.5 flex flex-col items-center justify-center rounded-[12px] border-2 border-dashed p-3.5 text-center transition-colors min-h-[145px]',
										errors.thumbnail
											? 'border-red-400 bg-red-50/20'
											: isDragging
											? 'border-[#183669] bg-[#183669]/5'
											: 'border-[#183669]/30 bg-[#fafcff] hover:border-[#183669]/60'
									]"
								>
									<!-- State 1: No images uploaded yet -->
									<div v-if="!form.thumbnail" class="flex flex-col items-center justify-center py-2.5">
										<svg class="h-9 w-9 text-[#8c9eb5]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
										</svg>
										<p class="mt-1.5 font-inter text-[12px] text-[#7188a3]">
											Upload gambar atau seret gambar ke form ini
										</p>
										<p class="mt-1 font-inter text-[11px] text-[#8ca1b9]">
											(MAX 5MB, JPG/JPEG/PNG &bull; Disarankan 1280 &times; 720 px)
										</p>
										<button
											type="button"
											@click="triggerFileInput"
											class="mt-2 rounded-[8px] border border-[#a6b7cb] bg-white px-5 py-1 font-inter text-[12px] font-semibold text-[#5a718d] transition hover:bg-slate-50 shadow-xs cursor-pointer"
										>
											Upload
										</button>
									</div>

									<!-- State 2: Image uploaded (Preview rendered INSIDE the box) -->
									<div v-else class="w-full flex justify-center">
										<div class="group relative w-full max-w-[280px] sm:max-w-[320px] aspect-video overflow-hidden rounded-[8px] border border-[#d6e0ee] transition-all shadow-xs bg-slate-100">
											<img :src="thumbnailPreview || form.thumbnail" alt="Preview Thumbnail" class="h-full w-full object-cover" />

											<!-- Delete Button -->
											<button
												type="button"
												@click.stop="removeThumbnail"
												class="absolute right-1.5 top-1.5 z-20 flex h-6 w-6 items-center justify-center rounded-full bg-red-600/90 hover:bg-red-600 text-white shadow-md backdrop-blur-xs transition hover:scale-110 active:scale-95 focus:outline-none cursor-pointer"
												title="Hapus Gambar"
											>
												<svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
													<path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
												</svg>
											</button>

											<!-- Zoom / Preview Button -->
											<button
												type="button"
												@click.stop="openImagePreview(thumbnailPreview || form.thumbnail)"
												class="absolute right-1.5 bottom-1.5 z-20 flex h-6 w-6 items-center justify-center rounded-full bg-black/60 hover:bg-black/85 text-white shadow-md backdrop-blur-xs transition hover:scale-110 active:scale-95 focus:outline-none cursor-pointer"
												title="Lihat Ukuran Penuh"
											>
												<svg class="h-3 w-3 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
													<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
												</svg>
											</button>
										</div>
									</div>

									<input
										ref="fileInputRef"
										type="file"
										accept="image/png, image/jpeg, image/jpg"
										class="hidden"
										@change="handleFileChange"
									/>
								</div>

								<!-- Validation Error Message -->
								<p v-if="errors.thumbnail" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
									<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
										<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
									</svg>
									<span>{{ errors.thumbnail }}</span>
								</p>
							</div>

							<!-- Action Buttons -->
							<div class="mt-6 sm:mt-8 flex flex-row items-center justify-center gap-2.5 sm:gap-4 pt-1 sm:pt-3">
								<button
									type="button"
									@click="handleClose"
									class="h-[42px] sm:h-[44px] flex-1 sm:flex-initial sm:min-w-[130px] px-4 sm:px-6 rounded-[10px] border border-[#d6e0ee] bg-white font-poppins text-[13px] sm:text-[14px] font-bold text-[#183669] transition hover:border-[#183669] hover:bg-slate-50 focus:border-[#183669] focus:outline-none active:scale-98 cursor-pointer select-none"
								>
									Kembali
								</button>
								<button
									type="submit"
									class="h-[42px] sm:h-[44px] flex-1 sm:flex-initial sm:min-w-[170px] px-4 sm:px-6 rounded-[10px] bg-[#183669] font-poppins text-[13px] sm:text-[14px] font-bold text-white shadow-sm transition hover:bg-[#122b54] active:scale-98 focus:outline-none cursor-pointer select-none whitespace-nowrap"
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

	<!-- Lightbox Image Modal Preview (z-[100] above all modals) -->
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
</template>
