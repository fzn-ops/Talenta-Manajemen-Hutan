<script setup>
import { ref, watch } from 'vue';
import DatePicker from '@/Components/dashboard/DatePicker.vue';
import RichTextEditor from '@/Components/dashboard/RichTextEditor.vue';

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
	judul: '',
	tanggal: '',
	isi: '',
	gambar: null,
});

// Errors State
const errors = ref({
	judul: '',
	tanggal: '',
	isi: '',
	gambar: '',
});

// Image Upload State
const imageInput = ref(null);
const imagePreview = ref(null);
const isDragging = ref(false);

const resetErrors = () => {
	errors.value = {
		judul: '',
		tanggal: '',
		isi: '',
		gambar: '',
	};
};

const resetForm = () => {
	form.value = {
		id: null,
		judul: '',
		tanggal: '',
		isi: '',
		gambar: null,
	};
	imagePreview.value = null;
	if (imageInput.value) {
		imageInput.value.value = '';
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
					judul: props.initialData.judul ?? '',
					tanggal: props.initialData.tanggal ?? '',
					isi: props.initialData.isi ?? '',
					gambar: props.initialData.gambar ?? null,
				};
				if (props.initialData.gambar && typeof props.initialData.gambar === 'object') {
					imagePreview.value = URL.createObjectURL(props.initialData.gambar);
				} else if (props.initialData.gambar) {
					const img = props.initialData.gambar;
					imagePreview.value = (img.startsWith('http') || img.startsWith('/'))
						? img
						: `https://picsum.photos/seed/${img}/800/600`;
				} else {
					imagePreview.value = null;
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

const triggerImageUpload = () => {
	imageInput.value?.click();
};

const processImageFile = (file) => {
	if (file) {
		if (file.size > 10 * 1024 * 1024) {
			errors.value.gambar = 'Ukuran gambar maksimal 10MB!';
			return;
		}
		form.value.gambar = file;
		imagePreview.value = URL.createObjectURL(file);
		errors.value.gambar = '';
	}
};

const handleImageUpload = (event) => {
	const file = event.target.files?.[0];
	processImageFile(file);
};

const handleImageDrop = (event) => {
	isDragging.value = false;
	const file = event.dataTransfer.files?.[0];
	processImageFile(file);
};

// Helper to check if rich text HTML is actually empty
const isContentEmpty = (html) => {
	if (!html) return true;
	const stripped = html.replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
	return stripped.length === 0 && !html.includes('<img');
};

const validateForm = () => {
	resetErrors();
	let isValid = true;

	if (!form.value.judul || !form.value.judul.trim()) {
		errors.value.judul = 'Judul berita wajib diisi!';
		isValid = false;
	}
	if (!form.value.tanggal) {
		errors.value.tanggal = 'Tanggal terbit wajib dipilih!';
		isValid = false;
	}
	if (!form.value.isi || isContentEmpty(form.value.isi)) {
		errors.value.isi = 'Isi berita tidak boleh kosong!';
		isValid = false;
	}
	if (!form.value.gambar) {
		errors.value.gambar = 'Thumbnail berita wajib diupload!';
		isValid = false;
	}

	return isValid;
};

const handleClose = () => {
	resetForm();
	emit('close');
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
				@click.self="handleClose"
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
								{{ isEditMode ? 'Edit Berita' : 'Form Tambah Berita' }}
							</h2>
						</div>

						<form @submit.prevent="handleSubmit" novalidate>
							<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
								<!-- Kolom Kiri -->
								<div class="flex flex-col gap-3.5 sm:gap-4">
									<!-- Judul Berita -->
									<div>
										<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
											Judul Berita<span class="text-red-500">*</span>
										</label>
										<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
											Masukkan judul berita yang menarik perhatian!
										</p>
										<input
											type="text"
											v-model="form.judul"
											@input="errors.judul = ''"
											placeholder="Contoh: Seminar Nasional Manhut 2026"
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

									<!-- Tanggal Terbit (DatePicker) -->
									<div>
										<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
											Tanggal Terbit<span class="text-red-500">*</span>
										</label>
										<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">
											Pilih tanggal perilisan berita.
										</p>
										<div class="mt-1 sm:mt-1.5">
											<DatePicker
												v-model="form.tanggal"
												:has-error="!!errors.tanggal"
												placeholder="Pilih tanggal terbit"
												@update:modelValue="errors.tanggal = ''"
											/>
										</div>
										<p v-if="errors.tanggal" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
											<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
											</svg>
											<span>{{ errors.tanggal }}</span>
										</p>
									</div>

									<!-- Isi Berita (RichTextEditor) -->
									<div class="flex-1 flex flex-col">
										<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
											Isi Berita<span class="text-red-500">*</span>
										</label>
										<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5 mb-1 sm:mb-1.5">
											Tuliskan konten berita secara lengkap di sini.
										</p>
										<RichTextEditor
											v-model="form.isi"
											placeholder="Tulis isi berita..."
											min-height="140px"
											:has-error="!!errors.isi"
											@update:modelValue="errors.isi = ''"
										/>
										<p v-if="errors.isi" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
											<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
											</svg>
											<span>{{ errors.isi }}</span>
										</p>
									</div>
								</div>

								<!-- Kolom Kanan -->
								<div class="flex flex-col gap-3.5 sm:gap-4">
									<!-- Thumbnail Upload -->
									<div class="flex-1 flex flex-col">
										<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
											Gambar / Thumbnail<span class="text-red-500">*</span>
										</label>
										<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5 mb-1.5">
											Upload thumbnail berita (JPG/PNG, MAX 10MB)
										</p>

										<input
											type="file"
											ref="imageInput"
											accept="image/png, image/jpeg, image/jpg"
											class="hidden"
											@change="handleImageUpload"
										/>

										<div
											@click="triggerImageUpload"
											@dragover.prevent="isDragging = true"
											@dragleave.prevent="isDragging = false"
											@drop.prevent="handleImageDrop"
											:class="[
												'relative flex flex-1 min-h-[200px] sm:min-h-[240px] w-full flex-col items-center justify-center rounded-[12px] border-2 border-dashed p-3.5 text-center transition-colors cursor-pointer group select-none',
												errors.gambar ? 'border-red-400 bg-red-50/20' : (isDragging ? 'border-[#183669] bg-[#183669]/5' : 'border-[#183669]/30 bg-[#fafcff] hover:border-[#183669]/60')
											]"
										>
											<template v-if="imagePreview">
												<div class="relative h-full w-full min-h-[170px] rounded-[10px] overflow-hidden border border-[#d6e0ee] bg-white p-2 flex items-center justify-center">
													<img :src="imagePreview" alt="Preview" class="max-h-[220px] w-full object-contain" />
													<div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity rounded-[10px]">
														<span class="rounded-[8px] bg-white px-3 py-1.5 text-[12px] font-bold text-[#183669] shadow-md">Ganti Gambar</span>
													</div>
												</div>
											</template>
											<template v-else>
												<svg class="h-10 w-10 text-[#8c9eb5] group-hover:text-[#183669] transition-colors" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
													<path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
												</svg>
												<p class="mt-2 font-inter text-[12px] text-[#7188a3]">
													Upload gambar atau seret file ke form ini
												</p>
												<button type="button" class="mt-2.5 rounded-[8px] border border-[#a6b7cb] bg-white px-5 py-1.5 font-inter text-[12px] font-semibold text-[#5a718d] transition hover:bg-slate-50 shadow-xs cursor-pointer">
													Upload
												</button>
											</template>
										</div>
										<p v-if="errors.gambar" class="mt-1 flex items-center gap-1 font-inter text-[11px] font-medium text-red-500">
											<svg class="h-3.5 w-3.5 shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
											</svg>
											<span>{{ errors.gambar }}</span>
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
</template>
