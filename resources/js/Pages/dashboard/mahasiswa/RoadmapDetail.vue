<script setup>
import MahasiswaLayout from '@/Layouts/dashboard/MahasiswaLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch, onMounted, onUnmounted } from 'vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import RoadmapMaterialNavigation from '@/Components/dashboard/mahasiswa/RoadmapMaterialNavigation.vue';
import SelectParticipatedActivityModal from '@/Components/dashboard/mahasiswa/SelectParticipatedActivityModal.vue';
import DeleteModal from '@/Components/dashboard/DeleteModal.vue';

const props = defineProps({
	roadmapId: {
		type: [Number, String],
		default: 1,
	},
	roadmap: {
		type: Object,
		default: null,
	},
	months: {
		type: Array,
		default: () => [],
	},
	completedTasks: {
		type: Number,
		default: 0,
	},
	totalTasks: {
		type: Number,
		default: 0,
	},
	eligibleActivities: {
		type: Array,
		default: () => [],
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

const closeToast = () => {
	toast.value.show = false;
};

// Flash Session Watcher
watch(
	() => page.props?.flash,
	(flash) => {
		if (flash?.success) {
			showToast('success', 'Berhasil', flash.success);
		}
		if (flash?.error) {
			showToast('error', 'Gagal', flash.error);
		}
	},
	{ immediate: true, deep: true }
);

// Roadmap Info
const currentRoadmap = computed(() => {
	if (props.roadmap) {
		return {
			id: props.roadmap.id || props.roadmapId,
			category: props.roadmap.category || props.roadmap.kategori || 'Profesional',
			kategori: props.roadmap.kategori || props.roadmap.category || 'Profesional',
			title: props.roadmap.title || props.roadmap.nama_roadmap || 'Roadmap',
			subTitle: props.roadmap.subTitle || 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
		};
	}
	return {
		id: props.roadmapId,
		category: 'Profesional',
		kategori: 'Profesional',
		title: 'Roadmap',
		subTitle: 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
	};
});

// Progress Tracking (Disinkronkan dengan backend Pencapaian)
const completedTasks = ref(props.completedTasks || 0);
const totalTasks = ref(props.totalTasks || 0);

watch(
	() => props.completedTasks,
	(val) => {
		completedTasks.value = val ?? 0;
	},
	{ immediate: true }
);

watch(
	() => props.totalTasks,
	(val) => {
		totalTasks.value = val ?? 0;
	},
	{ immediate: true }
);

const progressPercentage = computed(() => {
	if (totalTasks.value <= 0) return 0;
	return Math.min(100, Math.round((completedTasks.value / totalTasks.value) * 100));
});

const isCompleted = computed(() => completedTasks.value >= totalTasks.value && totalTasks.value > 0);

const handleDownloadCertificate = () => {
	if (!isCompleted.value) {
		showToast(
			'info',
			'Sertifikat Terkunci',
			`Selesaikan semua ${totalTasks.value} task/materi untuk membuka dan mengunduh sertifikat.`
		);
	} else {
		showToast('success', 'Mengunduh Sertifikat', 'Sertifikat Anda sedang diproses dan diunduh.');
	}
};

// Months Data Structure with Materials & Tasks
const months = ref([]);

const initMonthsData = () => {
	if (props.months && props.months.length > 0) {
		months.value = JSON.parse(JSON.stringify(props.months));
	} else {
		months.value = [];
	}
};

watch(
	() => props.months,
	() => {
		initMonthsData();
	},
	{ immediate: true, deep: true }
);

// Active Tab & Selection State
const activeMonthId = ref(null);
const selectedItemId = ref(null);
const isMobileNavOpen = ref(false);

// Initialize active month and selected item
watch(
	months,
	(newMonths) => {
		if (newMonths && newMonths.length > 0) {
			const currentMonthExists = newMonths.some((m) => m.id === activeMonthId.value);
			if (!currentMonthExists) {
				activeMonthId.value = newMonths[0].id;
			}
			const curM = newMonths.find((m) => m.id === activeMonthId.value) || newMonths[0];
			if (curM && curM.items && curM.items.length > 0) {
				const curItemExists = curM.items.some((i) => i.id === selectedItemId.value);
				if (!curItemExists) {
					selectedItemId.value = curM.items[0].id;
				}
			} else {
				selectedItemId.value = null;
			}
		} else {
			activeMonthId.value = null;
			selectedItemId.value = null;
		}
	},
	{ immediate: true }
);

const currentMonth = computed(() => {
	if (!months.value.length) return null;
	return months.value.find((m) => m.id === activeMonthId.value) || months.value[0];
});

const currentItem = computed(() => {
	if (!currentMonth.value || !currentMonth.value.items || !currentMonth.value.items.length) return null;
	return currentMonth.value.items.find((i) => i.id === selectedItemId.value) || currentMonth.value.items[0];
});

// Toggle Selesai status (Only for Materi - Terhubung ke Backend Pencapaian)
const isTogglingMateri = ref(false);
const toggleItemCompleted = () => {
	if (!currentItem.value || currentItem.value.type !== 'Materi' || isTogglingMateri.value) return;

	isTogglingMateri.value = true;
	router.post(
		route('mahasiswa.roadmap.materi.toggle', currentItem.value.id),
		{},
		{
			preserveScroll: true,
			onFinish: () => {
				isTogglingMateri.value = false;
			},
		}
	);
};

// Switch active month tab
const selectMonth = (monthId) => {
	activeMonthId.value = monthId;
	const m = months.value.find((item) => item.id === monthId);
	if (m && m.items && m.items.length > 0) {
		selectedItemId.value = m.items[0].id;
	} else {
		selectedItemId.value = null;
	}
};

// Switch active item
const selectItem = (itemId) => {
	selectedItemId.value = itemId;
	isMobileNavOpen.value = false;
};

// --- Dynamic Top-Right Corner Logic ---
const tabsContainerRef = ref(null);
const isTopRightRounded = ref(true);
let tabsResizeObserver = null;

const checkTopRightCorner = () => {
	if (!tabsContainerRef.value) return;
	const container = tabsContainerRef.value;
	const children = container.children;
	if (children.length === 0) return;

	const lastChild = children[children.length - 1];
	const totalWidth = lastChild.offsetLeft + lastChild.offsetWidth;

	isTopRightRounded.value = totalWidth < container.clientWidth - 5;
};

onMounted(() => {
	if (tabsContainerRef.value) {
		tabsResizeObserver = new ResizeObserver(() => checkTopRightCorner());
		tabsResizeObserver.observe(tabsContainerRef.value);
	}
	setTimeout(() => checkTopRightCorner(), 100);
});

onUnmounted(() => {
	if (tabsResizeObserver && tabsContainerRef.value) {
		tabsResizeObserver.unobserve(tabsContainerRef.value);
	}
});

// =========================================================================
// MODAL PEMILIHAN KEGIATAN MAHASISWA & SUBMISSION TUGAS
// =========================================================================
const isSelectActivityModalOpen = ref(false);

const handleOpenAddActivityModal = () => {
	if (!currentItem.value || currentItem.value.type !== 'Tugas') return;
	isSelectActivityModalOpen.value = true;
};

const handleActivitySelected = async (selectedActivity) => {
	if (!currentItem.value) return;
	if (!currentItem.value.activities) {
		currentItem.value.activities = [];
	}

	// Tambahkan draft kartu kegiatan
	currentItem.value.activities.push({
		id: 'draft_' + Date.now(),
		id_hasil: null,
		id_pendaftaran_aktivitas: selectedActivity.id,
		judul_kegiatan: selectedActivity.judul,
		notes: '',
		fileName: '',
		file: null,
		mediaUrl: '',
		status: 'draft',
		catatan_penolakan: '',
		isSubmitted: false,
		expanded: true,
	});

	showToast(
		'success',
		'Kegiatan Dipilih',
		`Kegiatan "${selectedActivity.judul}" berhasil dimasukkan. Lengkapi catatan/bukti dan klik Kirim.`
	);

	await nextTick();
	const mainEl = document.querySelector('main');
	if (mainEl) {
		mainEl.scrollTo({ top: mainEl.scrollHeight, behavior: 'smooth' });
	}
};

// Delete Activity Modal State
const showDeleteActivityModal = ref(false);
const activityToDelete = ref(null);
const isDeletingActivity = ref(false);

const openDeleteActivityModal = (activity, index) => {
	activityToDelete.value = { activity, index };
	showDeleteActivityModal.value = true;
};

const executeDeleteActivity = () => {
	if (!activityToDelete.value) return;
	const { activity, index } = activityToDelete.value;

	// Jika draft (belum tersimpan di backend), hapus langsung dari memori lokal
	if (!activity.isSubmitted || !activity.id_hasil) {
		currentItem.value.activities.splice(index, 1);
		showDeleteActivityModal.value = false;
		activityToDelete.value = null;
		showToast('info', 'Dihapus', 'Draft kegiatan berhasil dihapus.');
		return;
	}

	// Jika sudah disubmit ke backend, panggil endpoint delete
	isDeletingActivity.value = true;
	router.delete(route('mahasiswa.roadmap.hasil.destroy', activity.id_hasil), {
		preserveScroll: true,
		onSuccess: () => {
			isDeletingActivity.value = false;
			showDeleteActivityModal.value = false;
			activityToDelete.value = null;
			showToast('success', 'Dihapus', 'Kegiatan berhasil dihapus dari tugas.');
		},
		onError: () => {
			isDeletingActivity.value = false;
			showToast('error', 'Gagal', 'Terjadi kesalahan saat menghapus kegiatan.');
		},
	});
};

const handleActivityFileChange = (e, activity) => {
	const file = e.target.files?.[0];
	if (!file) return;

	if (file.size > 20 * 1024 * 1024) {
		showToast('error', 'File Terlalu Besar', 'Maksimal ukuran file adalah 20MB.');
		return;
	}

	activity.file = file;
	activity.fileName = file.name;
	showToast('success', 'File Dipilih', `File "${file.name}" berhasil dipilih.`);
};

const isSubmittingTask = ref(false);
const handleSubmitActivity = (index) => {
	const activity = currentItem.value?.activities?.[index];
	if (!activity) return;

	if (!activity.notes && !activity.file && !activity.fileName) {
		showToast('error', 'Gagal Mengirim', 'Mohon isi catatan atau unggah file bukti kegiatan.');
		return;
	}

	isSubmittingTask.value = true;
	const formData = new FormData();
	formData.append('id_pendaftaran_aktivitas', activity.id_pendaftaran_aktivitas);
	formData.append('notes', activity.notes || '');
	if (activity.file) {
		formData.append('media', activity.file);
	}

	router.post(route('mahasiswa.roadmap.materi.hasil', currentItem.value.id), formData, {
		forceFormData: true,
		preserveScroll: true,
		onSuccess: () => {
			isSubmittingTask.value = false;
			showToast('success', 'Berhasil Dikirim', 'Bukti kegiatan berhasil disubmit untuk diverifikasi admin.');
		},
		onError: (errors) => {
			isSubmittingTask.value = false;
			const firstErr = Object.values(errors)[0] || 'Gagal mengirim bukti kegiatan.';
			showToast('error', 'Gagal Mengirim', firstErr);
		},
	});
};

const getTaskInstructionText = (item) => {
	if (!item || item.type !== 'Tugas') return '';
	if (item.cards) {
		const tCard = item.cards.find((c) => c.type === 'task_submission');
		if (tCard && tCard.content) return tCard.content;
	}
	return `Ikuti Kegiatan Terkait ${item.topic || 'Tugas'}`;
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
	} catch {
		return null;
	}
	return null;
};

// Open external links in rich text safely in a new tab
const handleRichTextClick = (e) => {
	const link = e.target.closest('a');
	if (!link) return;
	const href = link.getAttribute('href');
	if (href && (href.startsWith('http://') || href.startsWith('https://') || href.startsWith('//'))) {
		e.preventDefault();
		window.open(href, '_blank', 'noopener,noreferrer');
	}
};

// PDF URL Helper
const getPdfUrl = (content) => {
	if (!content) return '';
	if (typeof content === 'string' && (content.startsWith('http') || content.startsWith('blob:') || content.startsWith('data:'))) {
		return content;
	}
	return content;
};
</script>

<template>
	<Head :title="`Detail ${currentRoadmap.title} - Mahasiswa`" />

	<MahasiswaLayout>
		<div class="flex flex-col gap-6 min-h-[calc(100vh-80px)] mx-auto w-full max-w-[1520px] px-4 pt-8 sm:pt-[52px] pb-24 sm:pb-20 font-poppins sm:px-6 lg:px-8">
			<!-- Top Breadcrumb -->
			<nav class="flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-[#183669]">
				<Link href="/mahasiswa/roadmap" :preserve-state="false" class="hover:underline text-[#183669] transition-colors">
					Daftar Roadmap
				</Link>
				<span class="text-[#8ca1b9]">/</span>
				<span class="text-[#7188a3] font-medium truncate max-w-[250px] sm:max-w-md">
					{{ currentRoadmap.title }}
				</span>
			</nav>

			<!-- Page Header Section with Progress Bar & Download Certificate -->
			<div class="space-y-4 sm:space-y-5">
				<!-- Title & Subtitle -->
				<div class="space-y-1 sm:space-y-1.5">
					<h1 class="text-[26px] sm:text-[32px] lg:text-[36px] font-extrabold leading-tight text-[#17334F] tracking-tight">
						{{ currentRoadmap.title }}
					</h1>
					<p class="font-inter text-[13px] sm:text-[14px] font-medium text-[#64748b]">
						{{ currentRoadmap.subTitle }}
					</p>
				</div>

				<!-- Progress & Download Certificate Row (Berdasarkan data Pencapaian) -->
				<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 sm:gap-6 lg:gap-8 w-full pt-0.5">
					<!-- Progress Bar (Mentok sampai Tombol Unduh Sertifikat di Desktop) -->
					<div class="w-full sm:flex-1 sm:min-w-0 space-y-1.5 sm:space-y-2">
						<div class="flex items-center justify-between text-xs sm:text-[13px] font-semibold text-[#475569]">
							<span>{{ completedTasks }}/{{ totalTasks }} Task</span>
							<span class="text-[#183669] font-bold">{{ progressPercentage }}%</span>
						</div>
						<div class="h-2 sm:h-2.5 lg:h-3 w-full rounded-full bg-[#e2e8f0] overflow-hidden">
							<div
								class="h-full rounded-full bg-[#416f65] transition-all duration-300"
								:style="{ width: `${progressPercentage}%` }"
							></div>
						</div>
					</div>

					<!-- Download Certificate Button -->
					<div class="flex justify-end shrink-0">
						<button
							type="button"
							@click="handleDownloadCertificate"
							class="flex items-center justify-center gap-2 rounded-[10px] bg-[#183669] px-5 py-2.5 text-[13px] font-semibold text-white transition hover:bg-[#122b54] active:scale-95 cursor-pointer shadow-xs"
						>
							<span>Unduh Sertifikat</span>
							<svg class="h-3.5 w-3.5 shrink-0 text-white/90" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
							</svg>
						</button>
					</div>
				</div>
			</div>

			<!-- Folder Tab & Body Container -->
			<div class="mt-2 w-full max-w-full overflow-visible">
				<!-- Folder Header Tabs Row -->
				<div
					ref="tabsContainerRef"
					class="flex items-end select-none relative z-10 -mb-[1px] overflow-x-auto scrollbar-hide w-full"
					style="scrollbar-width: none; -ms-overflow-style: none;"
				>
					<!-- Month Tab Buttons -->
					<button
						v-for="(m, index) in months"
						:key="m.id"
						type="button"
						@click="selectMonth(m.id)"
						:style="{ borderTopLeftRadius: '10px', borderTopRightRadius: '10px' }"
						:class="[
							'relative shrink-0 flex items-center justify-center gap-2.5 px-5 sm:px-6 h-[48px] rounded-t-[10px] font-poppins text-[13px] sm:text-[14px] font-bold transition-all cursor-pointer border-t border-r border-[#d6e0ee]',
							index === 0 ? 'border-l' : '-ml-[1px] border-l',
							activeMonthId === m.id
								? 'bg-white text-[#183669] border-b border-b-transparent z-20'
								: 'bg-[#f1f5f9] text-[#64748b] hover:bg-[#e2e8f0] hover:text-[#183669] border-b border-b-[#d6e0ee] z-10'
						]"
					>
						<span class="whitespace-nowrap">{{ m.name }}</span>

						<!-- Active Blue Underline Indicator -->
						<span
							v-if="activeMonthId === m.id"
							class="absolute -bottom-[1px] -left-[1px] -right-[1px] h-[3px] bg-[#183669] pointer-events-none"
						></span>
					</button>

					<div v-if="months.length === 0" class="px-4 py-3 text-sm text-slate-400 font-medium">
						Belum ada bulan pembelajaran.
					</div>
				</div>

				<!-- Main White Card Body (Top right corner dynamic; bottom corners 10px rounded) -->
				<div
					:class="[
						'border border-[#d6e0ee] bg-white p-5 sm:p-7 lg:p-9 shadow-xs font-poppins relative z-0 min-h-[400px]',
						isTopRightRounded ? 'rounded-tr-[10px]' : 'rounded-tr-none',
						'rounded-b-[10px] rounded-tl-none'
					]"
				>
					<div v-if="currentItem" class="flex flex-col md:flex-row gap-6 lg:gap-8 items-start">
						<!-- ================= LEFT COLUMN: CONTENT & MEDIA (70%) ================= -->
						<div class="w-full md:w-[62%] lg:w-[68%] xl:w-[70%] space-y-5">
							<!-- Topic Title -->
							<div class="flex items-center justify-between gap-4">
								<h2 class="flex-1 min-w-0 font-poppins text-[22px] sm:text-[26px] font-extrabold text-[#17334F] leading-tight break-words">
									{{ currentItem?.topic || currentItem?.judul || 'Belum ada topik' }}
								</h2>
							</div>

							<!-- Media & Content Display (Read Only) -->
							<div v-if="currentItem?.cards && currentItem.cards.length > 0" class="flex flex-col gap-6 sm:gap-7 mt-6">
								<div v-for="card in currentItem.cards" :key="card.id" class="w-full">
									<!-- IMAGE -->
									<div v-if="card.type === 'image' && card.content" class="w-full aspect-video rounded-[10px] overflow-hidden border border-[#d6e0ee] shadow-sm bg-slate-50">
										<img :src="card.content" class="w-full h-full object-cover" />
									</div>

									<!-- PDF -->
									<div v-else-if="card.type === 'pdf' && card.content" class="w-full flex flex-col rounded-[10px] border border-[#d6e0ee] shadow-sm bg-white overflow-hidden">
										<!-- Header: Icon, Name, Download -->
										<div class="flex items-center justify-between bg-[#fafcff] px-4 py-3 border-b border-[#d6e0ee]">
											<div class="flex items-center gap-3 min-w-0">
												<svg class="h-7 w-7 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
													<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
												</svg>
												<div class="flex-1 min-w-0">
													<p class="font-poppins font-bold text-[#183669] truncate text-[13px] sm:text-[14px]">
														{{ card.fileName || 'Dokumen Materi.pdf' }}
													</p>
												</div>
											</div>
											<a
												:href="getPdfUrl(card.content)"
												target="_blank"
												:download="card.fileName || 'materi.pdf'"
												class="shrink-0 rounded-[6px] bg-[#183669] px-3 py-1.5 sm:px-4 sm:py-2 text-[11px] sm:text-[12px] font-bold text-white hover:bg-[#122b54] transition shadow-sm whitespace-nowrap flex items-center gap-1.5"
											>
												<svg class="h-3.5 w-3.5 sm:h-4 sm:w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
													<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
												</svg>
												Unduh
											</a>
										</div>
										<!-- Browser Native PDF Viewer -->
										<div class="w-full h-[450px] sm:h-[550px] md:h-[650px] bg-slate-50 relative">
											<object :data="getPdfUrl(card.content)" type="application/pdf" class="w-full h-full">
												<iframe :src="getPdfUrl(card.content)" class="w-full h-full border-none" title="PDF Viewer">
													<p class="p-4 text-[13px] text-center text-slate-500">
														Browser Anda tidak mendukung preview PDF. Silakan klik tombol Unduh.
													</p>
												</iframe>
											</object>
										</div>
									</div>

									<!-- TEXT -->
									<div
										v-else-if="card.type === 'text'"
										class="w-full rendered-rich-text text-left font-inter text-[13.5px] sm:text-[14.5px] text-[#334155] leading-relaxed break-words"
										@click="handleRichTextClick"
										v-html="card.content"
									></div>

									<!-- VIDEO -->
									<div
										v-else-if="card.type === 'video' && card.content"
										class="aspect-video w-full min-h-[250px] sm:min-h-0 rounded-[10px] overflow-hidden border border-[#d6e0ee] bg-black shadow-sm"
									>
										<iframe
											v-if="getVideoEmbedUrl(card.content)"
											:src="getVideoEmbedUrl(card.content)"
											class="w-full h-full"
											frameborder="0"
											allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
											allowfullscreen
										></iframe>
										<div v-else class="flex h-full flex-col items-center justify-center bg-slate-100 text-[13px] font-semibold text-slate-500 text-center px-4">
											<svg class="h-10 w-10 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
											</svg>
											Preview video belum didukung untuk tautan ini.<br />
											Gunakan tautan YouTube yang valid.
										</div>
									</div>
								</div>
							</div>

							<!-- Fallback Simple Display -->
							<div v-else-if="currentItem?.description" class="font-inter text-[13px] sm:text-[14px] leading-relaxed text-[#334155] whitespace-pre-wrap text-justify mt-6">
								{{ currentItem.description }}
							</div>

							<!-- Completion Action Box at Bottom (Only for Materi - Terhubung ke Pencapaian) -->
							<div
								v-if="currentItem?.type === 'Materi'"
								class="mt-8 pt-6 border-t border-[#d6e0ee] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
							>
								<div class="flex items-center gap-2">
									<span
										v-if="currentItem?.isCompleted"
										class="inline-flex items-center gap-1.5 text-[13px] font-semibold text-emerald-700 bg-emerald-50 px-3.5 py-1.5 rounded-full border border-emerald-200"
									>
										<svg class="h-4 w-4 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
										Materi ini telah Anda selesaikan
									</span>
									<span v-else class="text-[13px] text-[#64748b] font-medium">
										Tandai materi ini setelah selesai membaca atau menonton.
									</span>
								</div>
								<button
									type="button"
									:disabled="isTogglingMateri"
									@click="toggleItemCompleted"
									class="flex items-center gap-2 rounded-[8px] px-6 py-2.5 font-poppins text-[13px] font-bold text-white transition active:scale-95 cursor-pointer shadow-sm disabled:opacity-60"
									:class="currentItem?.isCompleted ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-[#183669] hover:bg-[#122b54]'"
								>
									<svg v-if="currentItem?.isCompleted" class="h-4 w-4 stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
									<span>{{ currentItem?.isCompleted ? 'Selesai' : 'Tandai Selesai' }}</span>
								</button>
							</div>

							<!-- ========================================================================= -->
							<!-- TASK ACTIVITIES SUBMISSION UI (Only for Tugas - DENGAN POP UP PILIH KEGIATAN) -->
							<!-- ========================================================================= -->
							<div v-if="currentItem?.type === 'Tugas'" class="mt-8 border-t border-[#d6e0ee] pt-6">
								<div class="flex items-center justify-between mb-5">
									<div>
										<h3 class="font-poppins text-lg font-bold text-[#17334F]">
											{{ getTaskInstructionText(currentItem) }}
										</h3>
										<p class="text-xs text-[#64748b] mt-0.5 font-inter">
											Pilih kegiatan yang telah diikuti dengan kategori yang sesuai untuk memenuhi tugas ini.
										</p>
									</div>
									<button
										type="button"
										@click="handleOpenAddActivityModal"
										class="rounded-[8px] bg-[#183669] px-5 py-2 text-[13px] font-bold text-white hover:bg-[#122b54] active:scale-95 transition shadow-sm cursor-pointer shrink-0"
									>
										Tambah
									</button>
								</div>

								<!-- Activities Cards List -->
								<div class="flex flex-col gap-4">
									<div
										v-for="(activity, index) in (currentItem.activities || [])"
										:key="activity.id"
										class="rounded-[10px] border border-[#d6e0ee] bg-white overflow-hidden shadow-sm"
									>
										<!-- Card Header -->
										<div
											class="flex items-center justify-between bg-[#fafcff] px-5 py-3.5 border-b border-[#d6e0ee] cursor-pointer hover:bg-[#f4f7fb] transition select-none"
											@click="activity.expanded = !activity.expanded"
										>
											<div class="flex items-center gap-3 min-w-0">
												<h4 class="font-poppins text-[15px] font-bold text-[#17334F] truncate">
													Kegiatan {{ index + 1 }}: {{ activity.judul_kegiatan || 'Aktivitas yang Diikuti' }}
												</h4>
											</div>

											<div class="flex items-center gap-2.5 shrink-0">
												<!-- Status Badge -->
												<span
													v-if="activity.status === 'disetujui'"
													class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-0.5 text-xs font-bold text-emerald-700 border border-emerald-200"
												>
													<svg class="h-3.5 w-3.5 stroke-[2.5]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
													Disetujui
												</span>
												<span
													v-else-if="activity.status === 'menunggu'"
													class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs font-bold text-amber-700 border border-amber-200"
												>
													<svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
													Menunggu Verifikasi
												</span>
												<span
													v-else-if="activity.status === 'ditolak'"
													class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-bold text-red-700 border border-red-200"
												>
													Ditolak
												</span>
												<span
													v-else
													class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-600 border border-slate-200"
												>
													Draft
												</span>

												<svg
													class="h-5 w-5 text-[#17334F] transition-transform"
													:class="{ 'rotate-180': !activity.expanded }"
													fill="none"
													stroke="currentColor"
													viewBox="0 0 24 24"
												>
													<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
												</svg>
											</div>
										</div>

										<!-- Card Body -->
										<div v-show="activity.expanded" class="p-5 flex flex-col gap-4 bg-white">
											<!-- Rejection Alert Box -->
											<div
												v-if="activity.status === 'ditolak' && activity.catatan_penolakan"
												class="p-3.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs sm:text-[13px] space-y-1"
											>
												<p class="font-bold flex items-center gap-1.5">
													<svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
														<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
													</svg>
													Catatan Penolakan dari Admin:
												</p>
												<p class="text-red-700 pl-5.5">
													{{ activity.catatan_penolakan }}
												</p>
											</div>

											<p class="font-inter text-[13px] text-[#334155] leading-relaxed">
												Masukkan bukti dan output yang kamu dapatkan dari kegiatan <strong class="text-[#17334F]">"{{ activity.judul_kegiatan }}"</strong> dalam bentuk PDF/gambar atau ketikkan pada kolom di bawah ini:
											</p>

											<!-- Form input (bisa diedit jika belum disubmit atau jika ditolak) -->
											<textarea
												v-if="!activity.isSubmitted"
												v-model="activity.notes"
												rows="4"
												placeholder="Ketikkan apa yang kamu dapatkan dari kegiatan ini disini..."
												class="w-full rounded-[8px] border border-[#d6e0ee] bg-white p-3 font-inter text-[13px] text-[#334155] placeholder-[#94a3b8] focus:border-[#183669] focus:ring-1 focus:ring-[#183669] outline-none transition-colors resize-y"
											></textarea>
											<div v-else class="p-3.5 bg-slate-50 rounded-[8px] border border-[#e2e8f0] text-[13px] text-[#334155] leading-relaxed whitespace-pre-wrap font-inter">
												{{ activity.notes || 'Tidak ada catatan tertulis.' }}
											</div>

											<div class="mt-1 flex flex-col gap-3">
												<p class="font-inter text-[12px] text-[#64748b]">
													Bukti atau rangkuman kegiatan yang disertai dokumentasi:
												</p>

												<!-- File Input for Draft -->
												<div
													v-if="!activity.isSubmitted"
													class="flex items-center gap-3 bg-[#fafcff] p-3 rounded-[8px] border border-dashed border-[#d6e0ee]"
												>
													<label class="shrink-0 rounded-[6px] bg-[#183669] px-4 py-2 text-[12px] font-semibold text-white hover:bg-[#122b54] transition shadow-sm cursor-pointer">
														<span>Pilih File</span>
														<input
															type="file"
															accept=".pdf,.png,.jpg,.jpeg,.doc,.docx"
															class="hidden"
															@change="handleActivityFileChange($event, activity)"
														/>
													</label>
													<span class="font-inter text-[12px] text-[#64748b] truncate">
														{{ activity.fileName || 'Belum ada file yang dipilih (Maks 20MB)' }}
													</span>
												</div>

												<!-- Existing File Display for Submitted Item -->
												<div
													v-else-if="activity.mediaUrl || activity.fileName"
													class="flex items-center justify-between bg-[#fafcff] p-3 rounded-[8px] border border-[#d6e0ee]"
												>
													<div class="flex items-center gap-2.5 min-w-0">
														<svg class="h-6 w-6 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
															<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
														</svg>
														<span class="font-poppins text-xs sm:text-[13px] font-bold text-[#183669] truncate">
															{{ activity.fileName || 'Bukti Kegiatan' }}
														</span>
													</div>
													<a
														:href="activity.mediaUrl"
														target="_blank"
														download
														class="shrink-0 rounded-[6px] bg-[#183669] px-3.5 py-1.5 text-xs font-bold text-white hover:bg-[#122b54] transition shadow-xs flex items-center gap-1.5"
													>
														<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
															<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
														</svg>
														Unduh Bukti
													</a>
												</div>
											</div>

											<!-- Actions Row -->
											<div class="mt-2 flex items-center justify-end gap-3 pt-4 border-t border-[#f1f5f9]">
												<!-- Tombol Hapus (Tersedia untuk draft, atau status menunggu/ditolak) -->
												<button
													v-if="!activity.isSubmitted || activity.status === 'menunggu' || activity.status === 'ditolak'"
													type="button"
													@click="openDeleteActivityModal(activity, index)"
													class="flex h-10 w-10 items-center justify-center rounded-[8px] bg-[#ff6b6b] text-white hover:bg-[#fa5252] transition shadow-xs cursor-pointer"
													:title="activity.isSubmitted ? 'Hapus Pengajuan Ini' : 'Batal / Hapus Draft'"
												>
													<svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
														<path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
													</svg>
												</button>

												<!-- Tombol Kirim (Hanya untuk yang belum disubmit) -->
												<button
													v-if="!activity.isSubmitted"
													type="button"
													:disabled="isSubmittingTask"
													@click="handleSubmitActivity(index)"
													class="rounded-[8px] bg-[#183669] px-7 py-2.5 text-[13px] font-bold text-white hover:bg-[#122b54] active:scale-95 transition shadow-sm cursor-pointer disabled:opacity-60"
												>
													{{ isSubmittingTask ? 'Mengirim...' : 'Kirim' }}
												</button>
											</div>
										</div>
									</div>

									<!-- Empty State for Activities -->
									<div
										v-if="!currentItem.activities || currentItem.activities.length === 0"
										class="flex flex-col items-center justify-center p-8 bg-[#fafcff] border border-dashed border-[#d6e0ee] rounded-[10px]"
									>
										<p class="font-inter text-[13px] text-[#64748b] text-center max-w-md">
											Belum ada kegiatan yang dimasukkan. Klik <strong class="text-[#183669] cursor-pointer hover:underline" @click="handleOpenAddActivityModal">Tambah</strong> untuk memilih aktivitas yang telah kamu ikuti dengan kategori <strong class="text-[#183669]">{{ currentRoadmap.category }}</strong>.
										</p>
									</div>
								</div>
							</div>
						</div>

						<!-- ================= RIGHT COLUMN: MATERI & TUGAS LIST (30%) (DESKTOP / TABLET) ================= -->
						<div class="hidden md:block w-full md:w-[38%] lg:w-[32%] xl:w-[30%] sticky top-6 self-start space-y-3">
							<RoadmapMaterialNavigation
								:items="currentMonth?.items || []"
								v-model:selectedItemId="selectedItemId"
							/>
						</div>
					</div>

					<div v-else class="py-16 text-center text-slate-500 font-medium">
						Tidak ada materi atau tugas yang tersedia pada bulan ini.
					</div>
				</div>
			</div>
		</div>

		<!-- ================= MOBILE BOTTOM NAVIGATION BAR ================= -->
		<div
			v-if="currentMonth"
			class="md:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t border-[#d6e0ee] shadow-[0_-8px_20px_-3px_rgba(0,0,0,0.08)] p-3 px-4 flex items-center justify-between"
		>
			<div class="flex flex-col mr-3 flex-1 min-w-0">
				<span class="text-[10px] font-bold text-[#8ca1b9] uppercase tracking-wider mb-0.5">Materi Saat Ini</span>
				<span class="text-[13px] font-extrabold text-[#17334F] line-clamp-1 truncate">
					{{ currentItem?.title || currentItem?.topik || 'Belum ada' }}
				</span>
			</div>
			<button
				type="button"
				@click="isMobileNavOpen = true"
				class="flex shrink-0 items-center gap-2 bg-[#183669] text-white px-5 py-2.5 rounded-full text-[12px] font-bold active:scale-95 transition hover:bg-[#122b54] cursor-pointer shadow-sm"
			>
				<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
				</svg>
				Daftar Materi
			</button>
		</div>

		<!-- ================= MODAL: MOBILE NAVIGATION DRAWER ================= -->
		<Teleport to="body">
			<!-- Overlay with animation -->
			<Transition
				enter-active-class="ease-out duration-300"
				enter-from-class="opacity-0"
				enter-to-class="opacity-100"
				leave-active-class="ease-in duration-200"
				leave-from-class="opacity-100"
				leave-to-class="opacity-0"
			>
				<div
					v-if="isMobileNavOpen"
					class="fixed inset-0 z-[60] bg-[#102653]/35 backdrop-blur-xs cursor-pointer md:hidden"
					aria-hidden="true"
					@click="isMobileNavOpen = false"
				></div>
			</Transition>

			<!-- Drawer Panel sliding from right -->
			<Transition
				enter-active-class="transform transition ease-out duration-300"
				enter-from-class="translate-x-full"
				enter-to-class="translate-x-0"
				leave-active-class="transform transition ease-in duration-200"
				leave-from-class="translate-x-0"
				leave-to-class="translate-x-full"
			>
				<div
					v-if="isMobileNavOpen"
					class="fixed top-0 right-0 z-[61] h-full w-[85%] max-w-[350px] bg-white shadow-2xl flex flex-col md:hidden font-poppins"
				>
					<!-- Drawer Header -->
					<div class="flex items-center justify-between p-4 sm:p-5 bg-white border-b border-[#d6e0ee] shrink-0">
						<div>
							<h3 class="font-extrabold text-[#17334F] text-[17px] sm:text-[18px]">Daftar Materi</h3>
							<p class="text-[12px] text-[#64748b] mt-0.5">Navigasi bulan ini</p>
						</div>
						<button
							type="button"
							@click="isMobileNavOpen = false"
							class="p-2 rounded-[8px] hover:bg-slate-100 text-[#64748b] transition active:bg-slate-200 cursor-pointer"
						>
							<svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
							</svg>
						</button>
					</div>
					<!-- Drawer Body -->
					<div class="flex-1 overflow-y-auto p-4 sm:p-5 bg-[#fafcff] space-y-2">
						<RoadmapMaterialNavigation
							:items="currentMonth?.items || []"
							v-model:selectedItemId="selectedItemId"
							@update:selectedItemId="isMobileNavOpen = false"
						/>
					</div>
				</div>
			</Transition>
		</Teleport>

		<!-- ================= MODAL: PILIH KEGIATAN YANG TELAH DIIKUTI ================= -->
		<SelectParticipatedActivityModal
			:show="isSelectActivityModalOpen"
			:eligible-activities="props.eligibleActivities"
			:roadmap-category="currentRoadmap.category"
			@close="isSelectActivityModalOpen = false"
			@select="handleActivitySelected"
		/>

		<!-- ================= MODAL: KONFIRMASI HAPUS KEGIATAN ================= -->
		<DeleteModal
			:show="showDeleteActivityModal"
			:is-loading="isDeletingActivity"
			@close="showDeleteActivityModal = false"
			@confirm="executeDeleteActivity"
		>
			<template #title>
				Hapus Kegiatan dari Tugas?
			</template>
			<template #message>
				Apakah Anda yakin ingin menghapus kegiatan <span class="font-bold text-[#17334F]">"{{ activityToDelete?.activity?.judul_kegiatan || 'ini' }}"</span> dari tugas ini? Tindakan ini tidak dapat dibatalkan.
			</template>
			<template #confirm-text>
				Hapus Kegiatan
			</template>
		</DeleteModal>

		<!-- Toast Notification -->
		<ToastNotification
			:show="toast.show"
			:type="toast.type"
			:title="toast.title"
			:message="toast.message"
			@close="closeToast"
		/>
	</MahasiswaLayout>
</template>
