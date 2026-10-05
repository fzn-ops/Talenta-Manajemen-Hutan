<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
	show: {
		type: Boolean,
		default: false,
	},
	data: {
		type: Object,
		default: () => ({}),
	},
});

const emit = defineEmits(['close', 'approve', 'reject']);

// Height resize state for deskripsi
const deskripsiHeight = ref(150);
const isResizing = ref(false);

const startResize = (e) => {
	if (e.type === 'mousedown') {
		e.preventDefault();
	}
	e.stopPropagation();
	isResizing.value = true;
	const startY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
	const startHeight = deskripsiHeight.value;

	const onMouseMove = (moveEvent) => {
		if (!isResizing.value) return;
		const clientY = moveEvent.type.includes('touch') ? moveEvent.touches[0].clientY : moveEvent.clientY;
		const newHeight = Math.max(90, Math.min(450, startHeight + (clientY - startY)));
		deskripsiHeight.value = newHeight;
	};

	const onMouseUp = (upEvent) => {
		upEvent.stopPropagation();
		isResizing.value = false;
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

// Lightbox state
const showImageModal = ref(false);
const selectedImage = ref('');

const imageList = computed(() => {
	if (!props.data?.gambar) return [];
	if (Array.isArray(props.data.gambar)) {
		return props.data.gambar.slice(0, 3);
	}
	if (typeof props.data.gambar === 'string') {
		const arr = props.data.gambar.split(',').map(s => s.trim()).filter(Boolean);
		return arr.slice(0, 3);
	}
	return [props.data.gambar];
});

const getImageSrc = (gambar) => {
	if (gambar && typeof gambar === 'object' && gambar.url) {
		return gambar.url;
	}
	if (gambar && typeof gambar === 'object' && gambar.file) {
		return URL.createObjectURL(gambar.file);
	}
	if (gambar && typeof gambar === 'string' && (gambar.startsWith('http') || gambar.startsWith('/'))) {
		return gambar;
	}
	return `https://picsum.photos/seed/${(typeof gambar === 'string' ? gambar : 'dummy')}/800/600`;
};

const openImage = (gambar) => {
	if (document.activeElement instanceof HTMLElement) {
		document.activeElement.blur();
	}
	selectedImage.value = getImageSrc(gambar);
	showImageModal.value = true;
};

// Backdrop & Keyboard Handlers
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

const handleClose = () => {
	if (document.activeElement instanceof HTMLElement) {
		document.activeElement.blur();
	}
	emit('close');
};

const handleKeyDown = (e) => {
	if (e.key === 'Escape' && props.show) {
		if (document.activeElement instanceof HTMLElement) {
			document.activeElement.blur();
		}
		if (showImageModal.value) {
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();
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
						<!-- Modal Header -->
						<div class="mb-4 sm:mb-6">
							<h2 class="text-[18px] sm:text-[22px] font-bold text-[#183669] tracking-tight">
								Persetujuan Aktivitas
							</h2>
						</div>

						<div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
							<!-- Kolom Kiri -->
							<div class="flex flex-col gap-3.5 sm:gap-4">
								<!-- Judul Aktivitas -->
								<div>
									<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
										Judul Aktivitas
									</label>
									<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">Judul kegiatan yang diajukan</p>
									<input
										type="text"
										readonly
										:value="data?.judul || '-'"
										class="mt-1 sm:mt-1.5 h-[42px] sm:h-[44px] w-full rounded-[10px] border border-[#d6e0ee] bg-[#f8fafc] px-3.5 font-inter text-[13px] sm:text-[14px] text-[#1e3456] outline-none ring-0 focus:outline-none focus:ring-0 focus:border-[#d6e0ee] focus-visible:outline-none focus-visible:ring-0 cursor-default select-text transition-none"
										style="outline: none !important; box-shadow: none !important;"
									/>
								</div>

								<!-- Deadline -->
								<div>
									<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
										Deadline
									</label>
									<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5">Batas waktu pelaksanaan aktivitas</p>
									<input
										type="text"
										readonly
										:value="data?.deadline || '-'"
										class="mt-1 sm:mt-1.5 h-[42px] sm:h-[44px] w-full rounded-[10px] border border-[#d6e0ee] bg-[#f8fafc] px-3.5 font-inter text-[13px] sm:text-[14px] text-[#1e3456] outline-none ring-0 focus:outline-none focus:ring-0 focus:border-[#d6e0ee] focus-visible:outline-none focus-visible:ring-0 cursor-default select-text transition-none"
										style="outline: none !important; box-shadow: none !important;"
									/>
								</div>

								<!-- Kategori -->
								<div>
									<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
										Kategori
									</label>
									<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5 mb-1.5">Kategori roadmap aktivitas</p>
									<div class="flex flex-wrap items-center gap-1.5 min-h-[42px] sm:min-h-[44px] p-2.5 rounded-[10px] border border-[#d6e0ee] bg-[#f8fafc]">
										<template v-if="data?.kategori && (Array.isArray(data.kategori) ? data.kategori.length > 0 : String(data.kategori).trim().length > 0)">
											<span
												v-for="(cat, catIdx) in (Array.isArray(data.kategori) ? data.kategori : data.kategori.split(',').map(s => s.trim()))"
												:key="catIdx"
												:class="[
													'inline-flex items-center justify-center rounded-full px-2.5 py-0.5 font-inter text-[11px] font-semibold border',
													cat === 'Profesional' || cat === 'Professional' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' :
													cat === 'Bisnis' ? 'bg-amber-50 text-amber-700 border-amber-200' :
													cat === 'Birokrat' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
													cat === 'Akademisi' ? 'bg-purple-50 text-purple-700 border-purple-200' :
													'bg-slate-100 text-slate-700 border-slate-200'
												]"
											>
												{{ cat }}
											</span>
										</template>
										<span v-else class="font-inter text-[13px] text-[#7188a3] italic">-</span>
									</div>
								</div>

								<!-- Deskripsi Kegiatan (Tanpa Double Border, Dengan Drag Height UI) -->
								<div class="flex-1 flex flex-col">
									<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
										Deskripsi Kegiatan
									</label>
									<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5 mb-1.5">
										Rincian lengkap aktivitas yang diajukan
									</p>
									<div class="relative flex-1 flex flex-col rounded-[10px] border border-[#d6e0ee] bg-[#f8fafc] overflow-hidden">
										<textarea
											readonly
											:value="data?.deskripsi || '-'"
											:style="{ height: `${deskripsiHeight}px` }"
											class="w-full resize-none border-0 border-none bg-transparent p-3.5 pb-6 font-inter text-[13px] sm:text-[14px] leading-relaxed text-[#1e3456] outline-none ring-0 focus:outline-none focus:ring-0 focus:border-transparent focus-visible:outline-none focus-visible:ring-0 overflow-y-auto cursor-default select-text shadow-none"
											style="border: none !important; outline: none !important; box-shadow: none !important;"
										></textarea>

										<!-- Bottom Full-Width Resize Handle UI -->
										<div
											@mousedown="startResize"
											@touchstart.passive="startResize"
											class="absolute bottom-0 left-0 flex h-3.5 w-full cursor-ns-resize items-center justify-center bg-[#fafcff] hover:bg-[#e8eef8] border-t border-[#d6e0ee] transition select-none rounded-b-[9px]"
											style="touch-action: none;"
											title="Tarik ke bawah untuk memanjangkan kotak deskripsi"
										>
											<div class="h-1 w-12 rounded-full bg-[#cbd5e1]"></div>
										</div>
									</div>
								</div>
							</div>

							<!-- Kolom Kanan -->
							<div class="flex flex-col gap-3.5 sm:gap-4">
								<!-- Gambar Aktivitas -->
								<div>
									<div class="flex items-center justify-between">
										<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
											Gambar Aktivitas
										</label>
										<span class="font-inter text-[11px] sm:text-[12px] font-semibold px-2.5 py-0.5 rounded-full bg-slate-100 text-[#183669] border border-slate-200">
											{{ imageList.length }}/3
										</span>
									</div>
									<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5 mb-1.5">
										Dokumentasi / poster kegiatan (Rasio 3:4, Maks. 3 Gambar)
									</p>

									<!-- Image Box Container -->
									<div
										class="mt-1.5 flex flex-col items-center justify-center rounded-[12px] border-2 border-dashed border-[#183669]/30 bg-[#fafcff] p-3.5 text-center min-h-[145px]"
									>
										<!-- State: Images present -->
										<div v-if="imageList.length > 0" class="w-full">
											<div class="flex flex-wrap items-center justify-center gap-2.5 sm:gap-3 w-full">
												<!-- Image Items in 3:4 Portrait Ratio -->
												<div
													v-for="(gbr, idx) in imageList"
													:key="idx"
													class="relative aspect-[3/4] w-full max-w-[95px] sm:max-w-[110px] rounded-[10px] border border-[#d6e0ee] bg-white overflow-hidden shadow-xs group"
												>
													<img :src="getImageSrc(gbr)" class="h-full w-full object-cover" />
													
													<!-- Action Overlay with Preview Button Only -->
													<div class="absolute inset-0 bg-black/40 flex items-center justify-center p-1.5 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 sm:group-focus-within:opacity-100 transition-opacity">
														<!-- Preview / Zoom Button -->
														<button
															type="button"
															@click.stop.prevent="e => { if (e.currentTarget) e.currentTarget.blur(); openImage(gbr); }"
															class="flex h-7 w-7 items-center justify-center rounded-full bg-black/60 text-white shadow-sm hover:bg-black/85 hover:scale-110 active:scale-95 transition cursor-pointer backdrop-blur-xs outline-none focus:outline-none focus:ring-0 focus-visible:outline-none focus-visible:ring-0"
															style="outline: none !important; box-shadow: none !important;"
															title="Lihat Gambar Penuh"
														>
															<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
																<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607zM10.5 7.5v6m3-3h-6" />
															</svg>
														</button>
													</div>
												</div>
											</div>
										</div>

										<!-- State: Empty -->
										<div v-else class="flex flex-col items-center justify-center py-4">
											<p class="font-inter text-[13px] text-[#7188a3] italic">- Tidak ada gambar aktivitas -</p>
										</div>
									</div>
								</div>

								<!-- Catatan Penolakan Sebelumnya -->
								<div class="flex-1 flex flex-col">
									<label class="block text-[12.5px] sm:text-[13px] font-semibold text-[#183669]">
										Catatan Penolakan Sebelumnya
									</label>
									<p class="font-inter text-[10.5px] sm:text-[11px] text-[#7188a3] mt-0.5 mb-1.5">
										Berikut catatan penolakan jika submisi ini pernah ditolak
									</p>
									<div
										class="flex-1 min-h-[90px] rounded-[10px] p-3.5 font-inter text-[13px] sm:text-[14px] leading-relaxed border select-text"
										:class="data?.status === 'Ditolak' && (data?.alasan_tolak || true)
											? 'border-red-200 bg-red-50/70 text-red-700'
											: 'border-[#d6e0ee] bg-[#f8fafc] text-[#7188a3] italic'"
									>
										{{ (data?.status === 'Ditolak') ? (data?.alasan_tolak || 'Data tidak lengkap atau format gambar kurang jelas.') : '- Tidak ada catatan penolakan sebelumnya -' }}
									</div>
								</div>
							</div>
						</div>

						<!-- Tombol Aksi Form (Center di Mobile & Kanan di Desktop) -->
						<div class="mt-6 sm:mt-8 flex flex-row items-center justify-center sm:justify-end gap-3 sm:gap-4 pt-3 sm:pt-4 border-t border-slate-100">
							<button
								type="button"
								@click="handleClose"
								class="h-[42px] sm:h-[44px] min-w-[100px] sm:min-w-[120px] px-5 sm:px-6 rounded-[10px] border border-[#d6e0ee] bg-white font-poppins text-[13px] sm:text-[14px] font-bold text-[#183669] transition hover:border-[#183669] hover:bg-slate-50 focus:border-[#183669] focus:outline-none focus:ring-0 active:scale-98 cursor-pointer select-none outline-none"
								style="outline: none !important; box-shadow: none !important;"
							>
								Kembali
							</button>
							<button
								type="button"
								@click="$emit('reject', data)"
								class="h-[42px] sm:h-[44px] min-w-[100px] sm:min-w-[120px] px-5 sm:px-6 rounded-[10px] bg-[#ef4444] font-poppins text-[13px] sm:text-[14px] font-bold text-white shadow-sm transition hover:bg-red-600 active:scale-98 focus:outline-none focus:ring-0 cursor-pointer select-none whitespace-nowrap outline-none"
								style="outline: none !important; box-shadow: none !important;"
							>
								Tolak
							</button>
							<button
								type="button"
								@click="$emit('approve', data)"
								class="h-[42px] sm:h-[44px] min-w-[100px] sm:min-w-[120px] px-5 sm:px-6 rounded-[10px] bg-[#10b981] font-poppins text-[13px] sm:text-[14px] font-bold text-white shadow-sm transition hover:bg-[#059669] active:scale-98 focus:outline-none focus:ring-0 cursor-pointer select-none whitespace-nowrap outline-none"
								style="outline: none !important; box-shadow: none !important;"
							>
								Setujui
							</button>
						</div>
					</div>
				</Transition>
			</div>
		</Transition>
	</Teleport>

	<!-- Lightbox Modal untuk Bukti Gambar -->
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
						class="relative flex items-center justify-center bg-transparent"
						@click.stop
					>
						<button
							type="button"
							@click="showImageModal = false"
							class="absolute top-3.5 right-3.5 z-20 flex h-9 w-9 items-center justify-center rounded-full bg-black/60 text-white hover:bg-black/85 backdrop-blur-xs shadow-md transition hover:scale-105 active:scale-95 focus:outline-none focus:ring-0 cursor-pointer outline-none"
							style="outline: none !important; box-shadow: none !important;"
							title="Tutup Preview"
						>
							<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
							</svg>
						</button>
						<img
							:src="selectedImage"
							alt="Preview Gambar"
							class="max-h-[85vh] max-w-[90vw] w-auto h-auto min-w-[280px] sm:min-w-[480px] rounded-xl object-contain shadow-2xl"
						/>
					</div>
				</Transition>
			</div>
		</Transition>
	</Teleport>
</template>
