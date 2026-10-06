<script setup>
import MahasiswaLayout from '@/Layouts/dashboard/MahasiswaLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch, onMounted, onUnmounted } from 'vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import RoadmapMaterialNavigation from '@/Components/dashboard/mahasiswa/RoadmapMaterialNavigation.vue';

const props = defineProps({
	roadmapId: {
		type: [Number, String],
		default: 1,
	},
});

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

// Roadmaps Data Store
const roadmapsData = {
	1: {
		id: 1,
		category: 'Profesional',
		title: 'Persiapan Karir Software Engineer & Fullstack Developer',
		subTitle: 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
	},
	2: {
		id: 2,
		category: 'Bisnis',
		title: 'Inkubasi Bisnis & Rintisan Startup Berbasis Hasil Hutan',
		subTitle: 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
	},
	3: {
		id: 3,
		category: 'Birokrasi',
		title: 'Jalur Masuk ASN & Karir Birokrasi Lingkungan Hidup',
		subTitle: 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
	},
	4: {
		id: 4,
		category: 'Akademisi',
		title: 'Persiapan Studi Lanjut S2/S3 & Publikasi Ilmiah Kehutanan',
		subTitle: 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
	},
	5: {
		id: 5,
		category: 'Profesional',
		title: 'Sertifikasi Konsultan Lingkungan & AMDAL Profesional',
		subTitle: 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
	},
	6: {
		id: 6,
		category: 'Bisnis',
		title: 'Pengembangan Usaha Ekowisata & Agroforestry Berkelanjutan',
		subTitle: 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
	},
};

const currentRoadmap = computed(() => {
	return roadmapsData[props.roadmapId] || {
		id: props.roadmapId,
		category: 'Roadmap',
		title: `Roadmap ${props.roadmapId}`,
		subTitle: 'Yuk Pelajari Roadmap Yang Telah Kamu Ikuti!',
	};
});

// Progress Tracking
const completedTasks = ref(10);
const totalTasks = ref(40);
const progressPercentage = computed(() => {
	return Math.min(100, Math.round((completedTasks.value / totalTasks.value) * 100));
});
const isCompleted = computed(() => completedTasks.value >= totalTasks.value);

const handleDownloadCertificate = () => {
	if (!isCompleted.value) {
		showToast('info', 'Sertifikat Terkunci', `Selesaikan semua ${totalTasks.value} task untuk membuka dan mengunduh sertifikat.`);
	} else {
		showToast('success', 'Mengunduh Sertifikat', 'Sertifikat Anda sedang diunduh.');
	}
};

// Months Data Structure with Materials & Tasks
const months = ref([
	{
		id: 1,
		name: 'Bulan 1',
		items: [
			{
				id: 101,
				type: 'Materi',
				title: 'Materi 1',
				topic: 'Cara Mendapatkan Return Usaha 100% dalam 1 Bulan',
				isCompleted: true,
				cards: [
					{
						id: 1,
						type: 'image',
						content: 'https://picsum.photos/seed/stonks/1000/500',
					},
					{
						id: 2,
						type: 'text',
						content: `<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc ac varius massa, eu pulvinar neque. Mauris viverra lectus et massa porttitor molestie. Curabitur pharetra, mauris eget bibendum convallis, quam lorem iaculis magna, in pulvinar nisi risus accumsan sem. Mauris condimentum ex et nisi facilisis vehicula. Etiam finibus vulputate dolor, a malesuada leo ultrices quis. Praesent nec porta dolor. Proin bibendum urna est, at cursus lorem condimentum ut. Aenean et dolor magna. Duis ac purus nisi. Pellentesque vulputate sapien et lorem volutpat, eget egestas orci faucibus. Praesent maximus, ex a luctus volutpat, dolor purus ultricies nibh, et facilisis magna nisi et lacus. Nullam ac tincidunt purus.</p><p>Duis nisl nulla, ultricies consequat risus vitae, ullamcorper varius metus. Vestibulum in mollis ante. Nullam ut libero enim. Vestibulum rutrum justo ex. In a ex elit. In sed elit in nibh pellentesque facilisis. Nullam a elit ipsum. Nullam tortor magna, suscipit nec consectetur at, pellentesque non nibh. Proin ut arcu aliquam, ornare risus at, viverra sem. Nulla semper posuere lacus eget tincidunt. Integer tortor justo, blandit at interdum in, varius ac justo. Phasellus eleifend a massa suscipit hendrerit. Donec quis felis est. Sed non dui sem. Sed faucibus, purus eget ornare tempus, nibh metus porta eros, semper feugiat metus risus ac mi.</p>`,
					},
				],
			},
			{
				id: 102,
				type: 'Tugas',
				title: 'Tugas 1',
				topic: 'Analisis Kelayakan Finansial dan Pasar Produk Hutan',
				status: 'diterima',
				isCompleted: true,
				cards: [
					{
						id: 3,
						type: 'task_submission',
						content: 'Ikuti Kegiatan Terkait Analisis Kelayakan Finansial Produk Hutan',
					},
				],
				activities: [
					{
						id: 1,
						expanded: false,
						notes: 'Telah mengikuti workshop valuasi kelayakan finansial UMKM kehutanan.',
						fileName: 'laporan_analisis_kelayakan.pdf',
					},
				],
			},
			{
				id: 103,
				type: 'Materi',
				title: 'Materi 2',
				topic: 'Strategi Pemasaran Digital & Branding Komoditas Kehutanan',
				isCompleted: false,
				cards: [
					{
						id: 4,
						type: 'text',
						content: `<p>Membangun positioning brand yang kuat di era digital memerlukan pemahaman terhadap target audiens dan storytelling produk ramah lingkungan. Pelajari optimasi kanal media sosial dan konten edukasi untuk menarik calon pembeli.</p>`,
					},
				],
			},
			{
				id: 104,
				type: 'Tugas',
				title: 'Tugas 2',
				topic: 'Pembuatan Pitch Deck Bisnis Agroforestry',
				status: 'menunggu',
				isCompleted: false,
				cards: [
					{
						id: 5,
						type: 'task_submission',
						content: 'Ikuti Kegiatan Terkait Pembuatan Pitch Deck Bisnis Agroforestry',
					},
				],
				activities: [
					{
						id: 2,
						expanded: false,
						notes: 'Pitch deck 12 slide bisnis agroforestry madu hutan lestari.',
						fileName: 'pitch_deck_agroforestry_v1.pdf',
					},
				],
			},
			{
				id: 105,
				type: 'Materi',
				title: 'Materi 3',
				topic: 'Manajemen Operasional & Rantai Pasok Hasil Hutan',
				isCompleted: false,
				cards: [
					{
						id: 6,
						type: 'text',
						content: `<p>Memahami rantai pasok hulu ke hilir untuk komoditas hasil hutan bukan kayu (HHBK), termasuk standardisasi penanganan pascapanen, pengemasan, dan jalur distribusi logistik.</p>`,
					},
				],
			},
			{
				id: 106,
				type: 'Tugas',
				title: 'Tugas 3',
				topic: 'Simulasi Perencanaan Rantai Pasok Berkelanjutan',
				status: 'belum',
				isCompleted: false,
				cards: [
					{
						id: 7,
						type: 'task_submission',
						content: 'Ikuti Kegiatan Terkait Simulasi Rantai Pasok Berkelanjutan',
					},
				],
				activities: [],
			},
		],
	},
	{
		id: 2,
		name: 'Bulan 2',
		items: [
			{
				id: 201,
				type: 'Materi',
				title: 'Materi 1',
				topic: 'Pengembangan Model Bisnis Ekowisata Berbasis Komunitas',
				isCompleted: false,
				cards: [
					{
						id: 8,
						type: 'text',
						content: `<p>Pelajari tahapan merancang paket eduwisata dan ekowisata, pelibatan masyarakat lokal, serta sistem bagi hasil yang adil dan berkelanjutan di kawasan hutan kemasyarakatan.</p>`,
					},
				],
			},
			{
				id: 202,
				type: 'Tugas',
				title: 'Tugas 1',
				topic: 'Rancangan Program Eduwisata Hutan Pendidikan',
				isCompleted: false,
				cards: [
					{
						id: 9,
						type: 'task_submission',
						content: 'Ikuti Kegiatan Terkait Rancangan Program Eduwisata',
					},
				],
				activities: [],
			},
		],
	},
]);

// Active Tab & Selection State
const activeMonthId = ref(1);
const selectedItemId = ref(101);
const isMobileNavOpen = ref(false);

const currentMonth = computed(() => {
	return months.value.find((m) => m.id === activeMonthId.value) || months.value[0];
});

const currentItem = computed(() => {
	if (!currentMonth.value || !currentMonth.value.items.length) return null;
	return currentMonth.value.items.find((i) => i.id === selectedItemId.value) || currentMonth.value.items[0];
});

// Toggle Selesai status (Only for Materi)
const toggleItemCompleted = () => {
	if (currentItem.value && currentItem.value.type === 'Materi') {
		currentItem.value.isCompleted = !currentItem.value.isCompleted;
		if (currentItem.value.isCompleted) {
			showToast('success', 'Materi Selesai', `"${currentItem.value.title}" ditandai telah selesai dipelajari.`);
		} else {
			showToast('info', 'Status Diperbarui', `"${currentItem.value.title}" ditandai belum selesai.`);
		}
	}
};

// Switch active month tab
const selectMonth = (monthId) => {
	activeMonthId.value = monthId;
	const m = months.value.find((item) => item.id === monthId);
	if (m && m.items.length > 0) {
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

// Student Task Activities Handlers
const handleAddActivity = async () => {
	if (!currentItem.value) return;
	if (!currentItem.value.activities) {
		currentItem.value.activities = [];
	}
	currentItem.value.activities.push({
		id: Date.now(),
		notes: '',
		fileName: '',
		fileData: null,
		expanded: true,
	});

	await nextTick();
	const mainEl = document.querySelector('main');
	if (mainEl) {
		mainEl.scrollTo({ top: mainEl.scrollHeight, behavior: 'smooth' });
	}
};

const handleRemoveActivity = (index) => {
	if (currentItem.value && currentItem.value.activities) {
		currentItem.value.activities.splice(index, 1);
		if (currentItem.value.activities.length === 0) {
			currentItem.value.isCompleted = false;
		}
		showToast('info', 'Dihapus', 'Draft kegiatan berhasil dihapus.');
	}
};

const handleActivityFileChange = (e, activity) => {
	const file = e.target.files?.[0];
	if (!file) return;

	if (file.size > 20 * 1024 * 1024) {
		showToast('error', 'File Terlalu Besar', 'Maksimal ukuran file adalah 20MB.');
		return;
	}

	activity.fileName = file.name;
	showToast('success', 'File Dipilih', `File "${file.name}" berhasil dipilih.`);
};

const handleSubmitActivity = (index) => {
	const activity = currentItem.value?.activities?.[index];
	if (!activity) return;

	if (!activity.notes && !activity.fileName) {
		showToast('error', 'Gagal Mengirim', 'Mohon isi catatan atau unggah file bukti kegiatan.');
		return;
	}

	if (currentItem.value) {
		currentItem.value.isCompleted = true;
	}
	showToast('success', 'Berhasil Dikirim', 'Bukti kegiatan berhasil disubmit.');
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
				<Link href="/mahasiswa/roadmap" class="hover:underline text-[#183669] transition-colors">
					Daftar Roadmap
				</Link>
				<span class="text-[#8ca1b9]">/</span>
				<span class="text-[#7188a3] font-medium">...</span>
			</nav>

			<!-- Page Header Section with Progress Bar & Download Certificate -->
			<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
				<!-- Title & Subtitle -->
				<div class="space-y-1">
					<h1 class="text-[28px] sm:text-[34px] lg:text-[38px] font-extrabold leading-tight text-[#17334F] tracking-tight">
						{{ currentRoadmap.title }}
					</h1>
					<p class="font-inter text-[13px] sm:text-[14px] font-medium text-[#64748b]">
						{{ currentRoadmap.subTitle }}
					</p>
				</div>

				<!-- Progress & Download Certificate Widget (Clean & Integrated) -->
				<div class="flex flex-col sm:flex-row lg:flex-col items-start lg:items-end gap-2.5 shrink-0">
					<!-- Progress Bar -->
					<div class="w-full sm:w-60 lg:w-64 space-y-1.5">
						<div class="flex items-center justify-between text-xs font-semibold text-[#475569]">
							<span>{{ completedTasks }}/{{ totalTasks }} Task</span>
							<span class="text-[#183669] font-bold">{{ progressPercentage }}%</span>
						</div>
						<div class="h-2 w-full rounded-full bg-[#e2e8f0] overflow-hidden">
							<div
								class="h-full rounded-full bg-[#416f65] transition-all duration-300"
								:style="{ width: `${progressPercentage}%` }"
							></div>
						</div>
					</div>

					<!-- Download Certificate Button -->
					<button
						type="button"
						@click="handleDownloadCertificate"
						class="flex items-center justify-center gap-2 rounded-[10px] bg-[#183669] px-5 py-2 text-[13px] font-semibold text-white transition hover:bg-[#122b54] active:scale-95 cursor-pointer shadow-xs"
					>
						<span>Unduh Sertifikat</span>
						<svg class="h-3.5 w-3.5 shrink-0 text-white/90" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
						</svg>
					</button>
				</div>
			</div>

			<!-- Folder Tab & Body Container -->
			<div class="mt-2 w-full max-w-full overflow-visible">
				<!-- Folder Header Tabs Row (Tabs Only, No Add/Delete) -->
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
				</div>

				<!-- Main White Card Body (Top right corner dynamic; bottom corners 10px rounded) -->
				<div
					:class="[
						'border border-[#d6e0ee] bg-white p-5 sm:p-7 lg:p-9 shadow-xs font-poppins relative z-0 min-h-[400px]',
						isTopRightRounded ? 'rounded-tr-[10px]' : 'rounded-tr-none',
						'rounded-b-[10px] rounded-tl-none'
					]"
				>
					<div class="flex flex-col md:flex-row gap-6 lg:gap-8 items-start">
						<!-- ================= LEFT COLUMN: CONTENT & MEDIA (70%) ================= -->
						<div class="w-full md:w-[62%] lg:w-[68%] xl:w-[70%] space-y-5">
							<!-- Topic Title -->
							<div class="flex items-center justify-between gap-4">
								<h2 class="flex-1 min-w-0 font-poppins text-[22px] sm:text-[26px] font-extrabold text-[#17334F] leading-tight break-words">
									{{ currentItem?.topic || 'Belum ada topik' }}
								</h2>
							</div>

							<!-- Media & Content Display (Read Only) -->
							<div v-if="currentItem?.cards && currentItem.cards.length > 0" class="flex flex-col gap-6 mt-6">
								<div v-for="card in currentItem.cards" :key="card.id" class="w-full">
									<!-- IMAGE -->
									<div v-if="card.type === 'image' && card.content" class="w-full aspect-video rounded-[10px] overflow-hidden border border-[#d6e0ee] shadow-sm bg-slate-50">
										<img :src="card.content" class="w-full h-full object-cover" />
									</div>

									<!-- PDF -->
									<div v-else-if="card.type === 'pdf' && card.fileName" class="w-full flex flex-col rounded-[10px] border border-[#d6e0ee] shadow-sm bg-white overflow-hidden">
										<!-- Header: Icon, Name, Download -->
										<div class="flex items-center justify-between bg-[#fafcff] px-4 py-3 border-b border-[#d6e0ee]">
											<div class="flex items-center gap-3 min-w-0">
												<svg class="h-7 w-7 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
													<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
												</svg>
												<div class="flex-1 min-w-0">
													<p class="font-poppins font-bold text-[#183669] truncate text-[13px] sm:text-[14px]">
														{{ card.fileName }}
													</p>
												</div>
											</div>
											<a
												:href="getPdfUrl(card.content)"
												target="_blank"
												:download="card.fileName"
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
										class="font-inter text-[13px] sm:text-[14px] leading-relaxed text-[#334155] text-justify prose prose-sm sm:prose-base max-w-none prose-p:my-2 prose-h1:text-[22px] prose-h2:text-[18px] prose-h3:text-[16px]"
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

							<!-- Completion Action Box at Bottom (Only for Materi) -->
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
									@click="toggleItemCompleted"
									class="flex items-center gap-2 rounded-[8px] px-6 py-2.5 font-poppins text-[13px] font-bold text-white transition active:scale-95 cursor-pointer shadow-sm"
									:class="currentItem?.isCompleted ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-[#183669] hover:bg-[#122b54]'"
								>
									<svg v-if="currentItem?.isCompleted" class="h-4 w-4 stroke-[3]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
									<span>{{ currentItem?.isCompleted ? 'Selesai' : 'Tandai Selesai' }}</span>
								</button>
							</div>

							<!-- Task Activities Submission UI (Only for Tugas) -->
							<div v-if="currentItem?.type === 'Tugas'" class="mt-8 border-t border-[#d6e0ee] pt-6">
								<div class="flex items-center justify-between mb-5">
									<h3 class="font-poppins text-lg font-bold text-[#17334F]">
										{{ getTaskInstructionText(currentItem) }}
									</h3>
									<button
										type="button"
										@click="handleAddActivity"
										class="rounded-[6px] bg-[#183669] px-5 py-2 text-[13px] font-semibold text-white hover:bg-[#122b54] active:scale-95 transition shadow-sm cursor-pointer"
									>
										Tambah
									</button>
								</div>

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
											<h4 class="font-poppins text-[15px] font-bold text-[#17334F]">
												Kegiatan {{ index + 1 }}
											</h4>
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

										<!-- Card Body -->
										<div v-show="activity.expanded" class="p-5 flex flex-col gap-4 bg-white">
											<p class="font-inter text-[13px] text-[#334155] leading-relaxed">
												Masukkan bukti dan output yang kamu dapatkan dari kegiatan ini dalam bentuk PDF atau ketikkan pada kolom dibawah ini
											</p>

											<textarea
												v-model="activity.notes"
												rows="4"
												placeholder="Ketikkan apa yang kamu dapatkan disini:"
												class="w-full rounded-[8px] border border-[#d6e0ee] bg-white p-3 font-inter text-[13px] text-[#334155] placeholder-[#94a3b8] focus:border-[#183669] focus:ring-1 focus:ring-[#183669] outline-none transition-colors resize-y"
											></textarea>

											<div class="mt-2 flex flex-col gap-3">
												<p class="font-inter text-[12px] text-[#64748b]">
													Kamu juga bisa upload bukti atau rangkuman kegiatan yang disertai dokumentasi pada kolom dibawah ini
												</p>
												<div class="flex items-center gap-3 bg-[#fafcff] p-3 rounded-[8px] border border-dashed border-[#d6e0ee]">
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
														{{ activity.fileName || 'No File Chosen' }}
													</span>
												</div>
											</div>

											<!-- Actions -->
											<div class="mt-2 flex items-center justify-end gap-3 pt-4 border-t border-[#f1f5f9]">
												<button
													type="button"
													@click="handleRemoveActivity(index)"
													class="flex h-10 w-10 items-center justify-center rounded-[6px] bg-[#ff6b6b] text-white hover:bg-[#fa5252] transition shadow-sm cursor-pointer"
													title="Hapus Kegiatan"
												>
													<svg class="h-[18px] w-[18px]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
														<path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
													</svg>
												</button>
												<button
													type="button"
													@click="handleSubmitActivity(index)"
													class="rounded-[6px] bg-[#183669] px-7 py-2.5 text-[13px] font-bold text-white hover:bg-[#122b54] active:scale-95 transition shadow-sm cursor-pointer"
												>
													Kirim
												</button>
											</div>
										</div>
									</div>

									<!-- Empty State for Activities -->
									<div
										v-if="!currentItem.activities || currentItem.activities.length === 0"
										class="flex flex-col items-center justify-center p-8 bg-[#fafcff] border border-dashed border-[#d6e0ee] rounded-[10px]"
									>
										<p class="font-inter text-[13px] text-[#64748b] text-center">
											Belum ada kegiatan yang ditambahkan. Klik <strong class="text-[#183669]">Tambah</strong> untuk mulai menambahkan aktivitas yang telah kamu ikuti.
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
					{{ currentItem?.title || 'Belum ada' }}
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
