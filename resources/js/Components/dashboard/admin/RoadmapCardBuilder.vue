<script setup>
import { ref, computed, watch } from 'vue';
import RichTextEditor from '@/Components/dashboard/RichTextEditor.vue';

const props = defineProps({
	modelValue: {
		type: Array,
		default: () => [],
	},
});

const emit = defineEmits(['update:modelValue', 'error']);

// Helper to reliably render base64 PDF in iframe/object by converting to Blob URL
const blobUrlCache = new Map();
const getPdfUrl = (dataUrl) => {
	if (!dataUrl) return '';
	if (dataUrl.startsWith('http') || dataUrl.startsWith('/')) return dataUrl;
	
	if (dataUrl.startsWith('data:application/pdf;base64,')) {
		if (blobUrlCache.has(dataUrl)) {
			return blobUrlCache.get(dataUrl);
		}
		try {
			const parts = dataUrl.split(',');
			const bstr = atob(parts[1]);
			let n = bstr.length;
			const u8arr = new Uint8Array(n);
			while(n--) {
				u8arr[n] = bstr.charCodeAt(n);
			}
			const blob = new Blob([u8arr], { type: 'application/pdf' });
			const url = URL.createObjectURL(blob);
			blobUrlCache.set(dataUrl, url);
			return url;
		} catch (e) {
			console.error('Failed to convert base64 to Blob URL', e);
			return dataUrl;
		}
	}
	return dataUrl;
};

// Sync with v-model
const cards = computed({
	get: () => props.modelValue,
	set: (val) => emit('update:modelValue', val),
});

watch(cards, (newCards) => {
	newCards.forEach(c => {
		if (c.hasError) {
			let isEmpty = false;
			if (c.type === 'text') isEmpty = !c.content || c.content.trim() === '' || c.content === '<p></p>';
			else if (c.type === 'image' || c.type === 'pdf') isEmpty = !c.content;
			else if (c.type === 'video') isEmpty = !c.content || c.content.trim() === '';
			
			if (!isEmpty) c.hasError = false;
		}
	});
}, { deep: true });

const availableTypes = [
	{ type: 'image', label: 'Gambar', icon: 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z' },
	{ type: 'text', label: 'Teks Deskripsi', icon: 'M4 6h16M4 12h16M4 18h7' },
	{ type: 'pdf', label: 'File PDF', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
	{ type: 'video', label: 'Video Embed', icon: 'M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z' },
	{ type: 'task_submission', label: 'Instruksi Pengumpulan Tugas', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01' },
];

const hasCardType = (type) => cards.value.some((c) => c.type === type);

const addCard = (type) => {
	if (hasCardType(type)) return;
	cards.value = [...cards.value, {
		id: Date.now() + Math.random().toString(36).substring(2, 7),
		type,
		content: '',
		file: null,
		fileName: '',
	}];
};

const removeCard = (id) => {
	cards.value = cards.value.filter((c) => c.id !== id);
};

// Drag and drop mechanics
const dragIndex = ref(-1);
const dragEnabledIndex = ref(null);

// --- Desktop HTML5 Drag ---
const onDragStart = (e, index) => {
	dragIndex.value = index;
	e.dataTransfer.effectAllowed = 'move';
	// Make it look seamless
	e.target.style.opacity = '0.5';
};

const onDragEnter = (e, index) => {
	if (dragIndex.value === index) return;
	const newCards = [...cards.value];
	const draggedItem = newCards[dragIndex.value];
	newCards.splice(dragIndex.value, 1);
	newCards.splice(index, 0, draggedItem);
	dragIndex.value = index;
	cards.value = newCards;
};

const onDragEnd = (e) => {
	dragIndex.value = -1;
	e.target.style.opacity = '1';
};

// --- Mobile Touch Drag ---
const touchState = ref({
	active: false,
	index: -1,
	startY: 0,
	el: null,
	clone: null,
});

const onTouchStart = (e, index) => {
	const touch = e.touches[0];
	touchState.value = {
		active: true,
		index,
		startY: touch.clientY,
	};
	
	const cardEl = e.currentTarget.closest('.card-wrapper');
	if (cardEl) {
		const rect = cardEl.getBoundingClientRect();
		const clone = cardEl.cloneNode(true);
		clone.style.position = 'fixed';
		clone.style.top = `${rect.top}px`;
		clone.style.left = `${rect.left}px`;
		clone.style.width = `${rect.width}px`;
		clone.style.height = `${rect.height}px`;
		clone.style.opacity = '0.9';
		clone.style.zIndex = '9999';
		clone.style.pointerEvents = 'none'; // so we can elementFromPoint through it
		clone.style.boxShadow = '0 15px 30px rgba(24,54,105,0.15)';
		clone.style.transition = 'none';
		clone.style.transform = 'scale(1.02)';
		document.body.appendChild(clone);
		
		touchState.value.clone = clone;
		touchState.value.el = cardEl;
		cardEl.style.opacity = '0.2';
	}
	document.body.style.overflow = 'hidden';
};

const onTouchMove = (e) => {
	if (!touchState.value.active) return;
	// e.preventDefault(); // Removed because 'touch-none' CSS class handles scroll prevention
	const touch = e.touches[0];
	const deltaY = touch.clientY - touchState.value.startY;
	
	if (touchState.value.clone) {
		touchState.value.clone.style.transform = `translateY(${deltaY}px) scale(1.02)`;
	}
	
	const target = document.elementFromPoint(touch.clientX, touch.clientY);
	const targetCard = target?.closest('.card-wrapper');
	
	if (targetCard && targetCard !== touchState.value.el) {
		const targetIndexStr = targetCard.getAttribute('data-index');
		if (targetIndexStr !== null) {
			const targetIndex = parseInt(targetIndexStr, 10);
			if (targetIndex !== touchState.value.index) {
				const newCards = [...cards.value];
				const [movedItem] = newCards.splice(touchState.value.index, 1);
				newCards.splice(targetIndex, 0, movedItem);
				cards.value = newCards;
				touchState.value.index = targetIndex;
			}
		}
	}
};

const onTouchEnd = () => {
	if (!touchState.value.active) return;
	touchState.value.active = false;
	
	if (touchState.value.clone) {
		touchState.value.clone.remove();
		touchState.value.clone = null;
	}
	if (touchState.value.el) {
		touchState.value.el.style.opacity = '1';
		touchState.value.el = null;
	}
	
	document.body.style.overflow = '';
};


const moveUp = (index) => {
	if (index === 0) return;
	const newCards = [...cards.value];
	const temp = newCards[index - 1];
	newCards[index - 1] = newCards[index];
	newCards[index] = temp;
	cards.value = newCards;
};

const moveDown = (index) => {
	if (index === cards.value.length - 1) return;
	const newCards = [...cards.value];
	const temp = newCards[index + 1];
	newCards[index + 1] = newCards[index];
	newCards[index] = temp;
	cards.value = newCards;
};

// File Upload Handlers
const triggerFileInput = (id) => {
	const el = document.getElementById(`file-input-${id}`);
	if (el) el.click();
};

const removeFile = (card) => {
	const updated = [...cards.value];
	const index = updated.findIndex((c) => c.id === card.id);
	if (index !== -1) {
		updated[index].content = '';
		updated[index].file = null;
		updated[index].fileName = '';
		cards.value = updated;
	}
};

const handleFileDrop = (e, card) => {
	const file = e.dataTransfer.files?.[0];
	if (!file) return;
	processFile(file, card);
};

const handleFileChange = (e, card) => {
	const file = e.target.files?.[0];
	if (!file) return;
	processFile(file, card);
	e.target.value = '';
};

const processFile = (file, card) => {
	if (card.type === 'pdf') {
		if (file.type !== 'application/pdf') {
			emit('error', 'Hanya bisa upload file PDF.');
			return;
		}
		if (file.size > 50 * 1024 * 1024) {
			emit('error', 'Maksimal ukuran file PDF adalah 50MB.');
			return;
		}
	} else if (card.type === 'image') {
		if (!file.type.match(/^image\/(jpeg|png|jpg)$/i)) {
			emit('error', 'Harap upload file gambar yang valid (PNG, JPG, JPEG).');
			return;
		}
	}

	const reader = new FileReader();
	reader.onload = (event) => {
		const updated = [...cards.value];
		const index = updated.findIndex((c) => c.id === card.id);
		if (index !== -1) {
			updated[index].content = event.target.result;
			updated[index].file = { name: file.name, size: file.size, type: file.type };
			updated[index].fileName = file.name;
			cards.value = updated;
		}
	};
	reader.readAsDataURL(file);
};

// Video Embed Helper
const getVideoEmbedUrl = (url) => {
	if (!url) return null;
	try {
		if (url.includes('youtube.com/watch')) {
			const urlObj = new window.URL(url);
			const v = urlObj.searchParams.get('v');
			return v ? `https://www.youtube.com/embed/${v}` : null;
		}
		if (url.includes('youtube.com/shorts/')) {
			const id = url.split('youtube.com/shorts/')[1].split('?')[0];
			return id ? `https://www.youtube.com/embed/${id}` : null;
		}
		if (url.includes('youtu.be/')) {
			const id = url.split('youtu.be/')[1].split('?')[0];
			return id ? `https://www.youtube.com/embed/${id}` : null;
		}
	} catch (e) {
		return null;
	}
	return null;
};
</script>

<template>
	<div class="space-y-4">
		<!-- Cards List -->
		<div class="space-y-4 relative">
			<div
				v-for="(card, index) in cards"
				:key="card.id"
				:data-index="index"
				:class="[
					'card-wrapper group relative rounded-[10px] bg-white transition-colors shadow-sm focus-within:border-[#183669] border',
					card.hasError ? 'border-red-500 ring-1 ring-red-500' : 'border-[#d6e0ee]'
				]"
				:draggable="dragEnabledIndex === index"
				@dragstart="onDragStart($event, index)"
				@dragenter.prevent="onDragEnter($event, index)"
				@dragover.prevent
				@dragend="onDragEnd"
			>
				<!-- Card Header (Drag Handle & Title) -->
				<div class="flex items-center justify-between border-b border-[#d6e0ee] bg-[#fafcff] px-4 py-2 rounded-t-[10px]">
					<div class="flex items-center gap-2">
						<div 
							class="cursor-grab active:cursor-grabbing p-1.5 -ml-1.5 rounded hover:bg-[#e2e8f0] transition-colors touch-none"
							@mouseenter="dragEnabledIndex = index"
							@mouseleave="dragEnabledIndex = null"
							@touchstart.passive="onTouchStart($event, index)"
							@touchmove.passive="onTouchMove"
							@touchend.passive="onTouchEnd"
							@touchcancel.passive="onTouchEnd"
						>
							<svg class="h-5 w-5 text-[#8c9eb5] hover:text-[#183669]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
							</svg>
						</div>
						<span class="text-sm font-bold text-[#183669] capitalize">
							{{ availableTypes.find(t => t.type === card.type)?.label }}
						</span>
					</div>
					<div class="flex items-center gap-2">
						<!-- Mobile Up/Down Arrows -->
						<button type="button" @click.stop="moveUp(index)" :disabled="index === 0" class="sm:hidden p-1 text-[#8c9eb5] disabled:opacity-30">
							<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7" /></svg>
						</button>
						<button type="button" @click.stop="moveDown(index)" :disabled="index === cards.length - 1" class="sm:hidden p-1 text-[#8c9eb5] disabled:opacity-30">
							<svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" /></svg>
						</button>

						<!-- Delete Button -->
						<button
							v-if="!card.isFixed"
							type="button"
							@click.stop="removeCard(card.id)"
							class="flex h-7 w-7 items-center justify-center rounded-[6px] bg-red-50 text-red-500 transition hover:bg-red-100"
							title="Hapus Card"
						>
							<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
							</svg>
						</button>
					</div>
				</div>

				<!-- Card Body -->
				<div class="p-4 cursor-auto">
					<!-- TEXT CARD -->
					<div v-if="card.type === 'text'">
						<RichTextEditor v-model="card.content" placeholder="Tuliskan materi di sini..." min-height="200px" />
					</div>

					<!-- IMAGE & PDF UPLOAD CARD -->
					<div v-else-if="card.type === 'image' || card.type === 'pdf'">
						<div
							@dragover.prevent
							@drop.prevent="handleFileDrop($event, card)"
							class="flex flex-col items-center justify-center rounded-[12px] border-2 border-dashed border-[#183669]/30 bg-[#fafcff] p-5 text-center transition-colors hover:border-[#183669]/60 min-h-[160px]"
						>
							<div v-if="!card.content" class="flex flex-col items-center">
								<svg class="h-10 w-10 text-[#8c9eb5]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-1.41-8.775 5.25 5.25 0 0110.233-2.33 3 3 0 013.758 3.848A3.752 3.752 0 0118 19.5H6.75z" />
								</svg>
								<p class="mt-2 font-inter text-[13px] text-[#7188a3]">
									Upload {{ card.type === 'pdf' ? 'file PDF' : 'gambar' }} atau seret ke form ini
								</p>
								<p class="mt-1 font-inter text-[11px] text-[#8ca1b9]">
									{{ card.type === 'pdf' ? '(MAX 50MB)' : '(JPG/PNG, Rasio Disarankan 16:9)' }}
								</p>
								<button
									type="button"
									@click="triggerFileInput(card.id)"
									class="mt-3 rounded-[8px] border border-[#a6b7cb] bg-white px-5 py-1.5 font-inter text-[12px] font-semibold text-[#5a718d] shadow-xs hover:bg-slate-50"
								>
									Upload
								</button>
							</div>
							<div v-else class="w-full relative flex flex-col items-center">
								<!-- Preview Image -->
								<div v-if="card.type === 'image'" class="w-full aspect-video rounded-[8px] overflow-hidden border border-[#d6e0ee] shadow-sm bg-slate-50">
									<img :src="card.content" class="w-full h-full object-cover" />
								</div>
								<!-- Preview PDF -->
								<div v-else class="w-full flex flex-col rounded-[10px] border border-[#d6e0ee] shadow-sm bg-white overflow-hidden">
									<!-- Header: Icon, Name -->
									<div class="flex items-center justify-between bg-[#fafcff] px-4 py-3 border-b border-[#d6e0ee]">
										<div class="flex items-center gap-3 min-w-0">
											<svg class="h-7 w-7 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
												<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
											</svg>
											<div class="flex-1 min-w-0">
												<p class="font-poppins font-bold text-[#183669] truncate text-[13px] sm:text-[14px]">{{ card.fileName }}</p>
												<p class="font-inter text-[11px] text-[#7188a3] mt-0.5">Preview PDF</p>
											</div>
										</div>
									</div>
									<!-- Browser Native PDF Viewer -->
									<div class="w-full h-[400px] sm:h-[500px] bg-slate-50 relative">
										<object :data="getPdfUrl(card.content)" type="application/pdf" class="w-full h-full">
											<iframe :src="getPdfUrl(card.content)" class="w-full h-full border-none" title="PDF Viewer Preview">
												<p class="p-4 text-[13px] text-center text-slate-500">Browser Anda tidak mendukung preview PDF.</p>
											</iframe>
										</object>
									</div>
								</div>
								
								<button
									type="button"
									@click.stop="removeFile(card)"
									class="mt-3 text-xs font-semibold text-red-500 hover:underline"
								>
									Ganti / Hapus File
								</button>
							</div>

							<input
								:id="`file-input-${card.id}`"
								type="file"
								:accept="card.type === 'pdf' ? 'application/pdf' : 'image/png, image/jpeg, image/jpg'"
								class="hidden"
								@change="handleFileChange($event, card)"
							/>
						</div>
					</div>

					<!-- VIDEO CARD -->
					<div v-else-if="card.type === 'video'">
						<label class="block text-[12.5px] font-semibold text-[#183669] mb-1">Link Embed Video (YouTube)</label>
						<input
							v-model="card.content"
							type="text"
							placeholder="Contoh: https://www.youtube.com/watch?v=..."
							class="w-full rounded-[8px] border border-[#d6e0ee] bg-white px-3 py-2.5 font-inter text-[13px] text-[#1e3456] placeholder-[#94a3b8] focus:border-[#183669] focus:outline-none focus:ring-0"
						/>
						<div v-if="card.content" class="mt-3 aspect-video w-full min-h-[250px] sm:min-h-0 rounded-[8px] overflow-hidden border border-[#d6e0ee] bg-black">
                            <iframe 
                                v-if="getVideoEmbedUrl(card.content)"
                                :src="getVideoEmbedUrl(card.content)" 
                                class="w-full h-full"
                                frameborder="0" 
								allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen>
                            </iframe>
                            <div v-else class="flex h-full flex-col items-center justify-center bg-slate-100 text-[13px] font-semibold text-slate-500 text-center px-4">
								<svg class="h-10 w-10 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
								Preview video belum didukung untuk tautan ini.<br>
								Gunakan tautan YouTube yang valid.
							</div>
						</div>
					</div>

					<!-- TASK SUBMISSION CARD (Admin Config) -->
					<div v-else-if="card.type === 'task_submission'">
						<label class="block text-[12.5px] font-semibold text-[#183669] mb-1">Judul / Instruksi Pengumpulan Tugas</label>
						<input
							type="text"
							v-model="card.content"
							placeholder="Contoh: Ikuti Kegiatan Terkait Analisis Kelayakan Finansial..."
							class="w-full rounded-[8px] border border-[#d6e0ee] bg-white px-3 py-2.5 font-inter text-[13px] text-[#334155] placeholder-[#94a3b8] focus:border-[#183669] focus:ring-1 focus:ring-[#183669] outline-none transition-colors"
						/>
						<div class="mt-2 flex items-start gap-2 bg-[#f0f4f9] p-3 rounded-[6px]">
							<svg class="h-5 w-5 text-[#8ca1b9] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
							</svg>
							<p class="font-inter text-[12px] text-[#5a718d] leading-relaxed">
								Card ini tidak dapat dihapus dan akan memunculkan formulir otomatis (tombol Tambah Kegiatan, form teks, & upload bukti) bagi mahasiswa saat mereka melihat halaman tugas ini.
							</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
