<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/dashboard/AdminLayout.vue';
import ToastNotification from '@/Components/dashboard/ToastNotification.vue';
import Modal from '@/Components/dashboard/Modal.vue';

// Chart.js registration
import { Pie } from 'vue-chartjs';
import {
	Chart as ChartJS,
	ArcElement,
	Tooltip,
	Legend,
} from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
	participantDistribution: {
		type: Array,
		default: () => [
			{ category: 'Professional', count: 510, color: '#7c72ff' },
			{ category: 'Bisnis', count: 270, color: '#ff8b85' },
			{ category: 'Birokrat', count: 120, color: '#38bdf8' },
			{ category: 'Akademisi', count: 100, color: '#fb923c' },
		],
	},
	pengajuanAktivitas: {
		type: Array,
		default: () => [
			{ id: 1, judul: 'Lorem Ipsum dolor sit amet...', kategori: 'Profesional, Bisnis, Birokrat, Akad..', deadline: '10 Juli 2029', file: '123.jpg', status: 'Menunggu' },
			{ id: 2, judul: 'Lorem Ipsum dolor sit amet...', kategori: 'Profesional, Bisnis, Birokrat, Akad..', deadline: '10 Juli 2029', file: '123.jpg', status: 'Menunggu' },
			{ id: 3, judul: 'Lorem Ipsum dolor sit amet...', kategori: 'Profesional, Bisnis, Birokrat, Akad..', deadline: '10 Juli 2029', file: '123.jpg', status: 'Menunggu' },
			{ id: 4, judul: 'Lorem Ipsum dolor sit amet...', kategori: 'Profesional, Bisnis, Birokrat, Akad..', deadline: '10 Juli 2029', file: '123.jpg', status: 'Menunggu' },
			{ id: 5, judul: 'Lorem Ipsum dolor sit amet...', kategori: 'Profesional, Bisnis, Birokrat, Akad..', deadline: '10 Juli 2029', file: '123.jpg', status: 'Menunggu' },
		],
	},
	pendaftaranAktivitas: {
		type: Array,
		default: () => [
			{ id: 1, namaTim: 'Tim Biasa Aja', namaPeserta: 'Fauzan Fuadiansyah, Farhan Hakim, Rintan Aruf...', file: '123.jpeg', status: 'Menunggu' },
			{ id: 2, namaTim: 'Tim Biasa Aja', namaPeserta: 'Fauzan Fuadiansyah, Farhan Hakim, Rintan Aruf...', file: '123.jpeg', status: 'Menunggu' },
			{ id: 3, namaTim: 'Tim Biasa Aja', namaPeserta: 'Fauzan Fuadiansyah, Farhan Hakim, Rintan Aruf...', file: '123.jpeg', status: 'Menunggu' },
			{ id: 4, namaTim: 'Tim Biasa Aja', namaPeserta: 'Fauzan Fuadiansyah, Farhan Hakim, Rintan Aruf...', file: '123.jpeg', status: 'Menunggu' },
			{ id: 5, namaTim: 'Tim Biasa Aja', namaPeserta: 'Fauzan Fuadiansyah, Farhan Hakim, Rintan Aruf...', file: '123.jpeg', status: 'Menunggu' },
		],
	},
	hasilAktivitas: {
		type: Array,
		default: () => [
			{ id: 1, namaMahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenisRoadmap: 'Professional', namaAktivitas: 'Hackaton V2.0 Tahun 2026', file: '123.jpg', status: 'Menunggu' },
			{ id: 2, namaMahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenisRoadmap: 'Professional', namaAktivitas: 'Hackaton V2.0 Tahun 2026', file: '123.jpg', status: 'Menunggu' },
			{ id: 3, namaMahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenisRoadmap: 'Professional', namaAktivitas: 'Hackaton V2.0 Tahun 2026', file: '123.jpg', status: 'Menunggu' },
			{ id: 4, namaMahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenisRoadmap: 'Professional', namaAktivitas: 'Hackaton V2.0 Tahun 2026', file: '123.jpg', status: 'Menunggu' },
			{ id: 5, namaMahasiswa: 'Fauzan Fuadiansyah', nim: 'J0403231085', jenisRoadmap: 'Professional', namaAktivitas: 'Hackaton V2.0 Tahun 2026', file: '123.jpg', status: 'Menunggu' },
		],
	},
});

const isLoading = ref(true);
const isDesktop = ref(false);

const updateScreenSize = () => {
	if (typeof window !== 'undefined') {
		isDesktop.value = window.innerWidth >= 1024;
	}
};

onMounted(() => {
	setTimeout(() => {
		isLoading.value = false;
	}, 250);
	updateScreenSize();
	window.addEventListener('resize', updateScreenSize, { passive: true });
});

onUnmounted(() => {
	if (typeof window !== 'undefined') {
		window.removeEventListener('resize', updateScreenSize);
	}
});

// Toast State
const toast = ref({
	show: false,
	type: 'success',
	title: '',
	message: '',
});

const showToast = (type, title, message) => {
	toast.value = { show: true, type, title, message };
	setTimeout(() => {
		toast.value.show = false;
	}, 3500);
};

// Modal Preview State
const isPreviewModalOpen = ref(false);
const previewItem = ref(null);

const handlePreview = (item) => {
	previewItem.value = item;
	isPreviewModalOpen.value = true;
};

const handleApprove = (item) => {
	showToast('success', 'Berhasil Disetujui', `Aktivitas ${item.judul || item.namaAktivitas || item.namaTim || ''} berhasil disetujui.`);
};

const handleReject = (item) => {
	showToast('error', 'Aktivitas Ditolak', `Aktivitas ${item.judul || item.namaAktivitas || item.namaTim || ''} telah ditolak.`);
};

// Participant Statistics
const participantStats = computed(() => props.participantDistribution);
const totalParticipants = computed(() => {
	return participantStats.value.reduce((acc, curr) => acc + curr.count, 0);
});

// Mobile distribution interactive fluid slider state
const activeSlideIndex = ref(0);
const isDragging = ref(false);
const dragStartX = ref(0);
const dragStartY = ref(0);
const dragOffset = ref(0);
let isHorizontalDrag = null;

const startDrag = (clientX, clientY) => {
	if (isDesktop.value) return;
	isDragging.value = true;
	dragStartX.value = clientX;
	dragStartY.value = clientY;
	dragOffset.value = 0;
	isHorizontalDrag = null;
};

const moveDrag = (clientX, clientY, e) => {
	if (!isDragging.value || isDesktop.value) return;
	const deltaX = clientX - dragStartX.value;
	const deltaY = clientY - dragStartY.value;

	if (isHorizontalDrag === null && (Math.abs(deltaX) > 6 || Math.abs(deltaY) > 6)) {
		isHorizontalDrag = Math.abs(deltaX) > Math.abs(deltaY);
	}

	if (isHorizontalDrag === false) {
		isDragging.value = false;
		dragOffset.value = 0;
		return;
	}

	if (isHorizontalDrag === true) {
		if (e && e.cancelable && e.preventDefault) {
			e.preventDefault();
		}
		// Rubber-band resistance at boundary edges for satisfying tactile feel
		if ((activeSlideIndex.value === 0 && deltaX > 0) || (activeSlideIndex.value === 1 && deltaX < 0)) {
			dragOffset.value = deltaX * 0.28;
		} else {
			dragOffset.value = deltaX;
		}
	}
};

const endDrag = () => {
	if (!isDragging.value) return;
	isDragging.value = false;
	const threshold = 40;
	if (dragOffset.value < -threshold && activeSlideIndex.value < 1) {
		activeSlideIndex.value = 1;
	} else if (dragOffset.value > threshold && activeSlideIndex.value > 0) {
		activeSlideIndex.value = 0;
	}
	dragOffset.value = 0;
	isHorizontalDrag = null;
};

// Touch Handlers
const handleTouchStart = (e) => {
	if (e.touches && e.touches.length === 1) {
		startDrag(e.touches[0].clientX, e.touches[0].clientY);
	}
};
const handleTouchMove = (e) => {
	if (e.touches && e.touches.length === 1) {
		moveDrag(e.touches[0].clientX, e.touches[0].clientY, e);
	}
};
const handleTouchEnd = () => {
	endDrag();
};

// Mouse Handlers (Enables natural drag in desktop responsive preview)
const handleMouseDown = (e) => {
	if (e.button !== 0) return;
	startDrag(e.clientX, e.clientY);
};
const handleMouseMove = (e) => {
	moveDrag(e.clientX, e.clientY, e);
};
const handleMouseUp = () => {
	endDrag();
};
const handleMouseLeave = () => {
	if (isDragging.value) {
		endDrag();
	}
};

const goToSlide = (index) => {
	activeSlideIndex.value = index;
	dragOffset.value = 0;
};

const mobileTrackStyle = computed(() => {
	if (isDesktop.value) {
		return {};
	}
	if (isDragging.value) {
		return {
			transform: `translateX(calc(-${activeSlideIndex.value * 100}% + ${dragOffset.value}px))`,
		};
	}
	return {
		transform: `translateX(-${activeSlideIndex.value * 100}%)`,
	};
});

// Chart Data Setup
const pieChartData = computed(() => ({
	labels: participantStats.value.map((s) => s.category),
	datasets: [
		{
			data: participantStats.value.map((s) => s.count),
			backgroundColor: participantStats.value.map((s) => s.color),
			borderColor: '#ffffff',
			borderWidth: 2,
			hoverOffset: 6,
		},
	],
}));

const pieChartOptions = {
	responsive: true,
	maintainAspectRatio: false,
	interaction: {
		mode: 'nearest',
		intersect: true,
	},
	layout: {
		padding: 10,
	},
	plugins: {
		legend: {
			display: false,
		},
		tooltip: {
			callbacks: {
				label: (ctx) => {
					const val = ctx.raw || 0;
					const pct = totalParticipants.value ? Math.round((val / totalParticipants.value) * 100) : 0;
					return ` ${ctx.label}: ${val} (${pct}%)`;
				},
			},
			backgroundColor: '#17334F',
			titleFont: { family: 'Poppins', size: 12, weight: 'bold' },
			bodyFont: { family: 'Poppins', size: 12 },
			cornerRadius: 8,
			padding: 10,
		},
		datalabels: {
			color: '#ffffff',
			font: {
				family: 'Poppins',
				weight: 'bold',
				size: 13,
			},
			formatter: (value) => {
				const pct = totalParticipants.value ? Math.round((value / totalParticipants.value) * 100) : 0;
				return `${pct}%`;
			},
		},
	},
};

// Column Definitions
const columnsPengajuan = [
	{ key: 'id', label: 'No', sortable: false, width: 'w-[60px]' },
	{ key: 'judul', label: 'Judul', sortable: true, width: 'w-[260px]' },
	{ key: 'kategori', label: 'Kategori', sortable: true, width: 'w-[230px]' },
	{ key: 'deadline', label: 'Deadline', sortable: true, width: 'w-[130px]' },
	{ key: 'file', label: 'Gambar', sortable: true, width: 'w-[120px]' },
	{ key: 'status', label: 'Status', sortable: true, width: 'w-[140px]' },
	{ key: 'aksi', label: 'Aksi', sortable: false, width: 'w-[140px]' },
];

const columnsPendaftaran = [
	{ key: 'id', label: 'No', sortable: false, width: 'w-[60px]' },
	{ key: 'namaTim', label: 'Nama Tim', sortable: true, width: 'w-[230px]' },
	{ key: 'namaPeserta', label: 'Nama Peserta', sortable: true, width: 'w-[330px]' },
	{ key: 'file', label: 'Bukti Registrasi', sortable: true, width: 'w-[190px]' },
	{ key: 'status', label: 'Status', sortable: true, width: 'w-[130px]' },
	{ key: 'aksi', label: 'Aksi', sortable: false, width: 'w-[140px]' },
];

const columnsHasil = [
	{ key: 'id', label: 'No', sortable: false, width: 'w-[60px]' },
	{ key: 'namaMahasiswa', label: 'Nama Mahasiswa', sortable: true, width: 'w-[190px]' },
	{ key: 'nim', label: 'NIM', sortable: true, width: 'w-[110px]' },
	{ key: 'jenisRoadmap', label: 'Jenis Roadmap', sortable: true, width: 'w-[150px]' },
	{ key: 'namaAktivitas', label: 'Nama Aktivitas', sortable: true, width: 'w-[200px]' },
	{ key: 'file', label: 'Bukti', sortable: true, width: 'w-[100px]' },
	{ key: 'status', label: 'Status', sortable: true, width: 'w-[130px]' },
	{ key: 'aksi', label: 'Aksi', sortable: false, width: 'w-[140px]' },
];

// Sort State for Table 1 (Pengajuan)
const sortKey1 = ref('id');
const sortDirection1 = ref('asc');
const toggleSort1 = (key) => {
	if (sortKey1.value === key) {
		sortDirection1.value = sortDirection1.value === 'asc' ? 'desc' : 'asc';
	} else {
		sortKey1.value = key;
		sortDirection1.value = 'asc';
	}
};
const sortedPengajuan = computed(() => {
	const data = props.pengajuanAktivitas || [];
	return [...data].sort((a, b) => {
		let left = a[sortKey1.value] ?? '';
		let right = b[sortKey1.value] ?? '';
		if (typeof left === 'number' && typeof right === 'number') {
			return sortDirection1.value === 'asc' ? left - right : right - left;
		}
		left = String(left).toLowerCase();
		right = String(right).toLowerCase();
		if (left === right) return 0;
		return sortDirection1.value === 'asc' ? (left > right ? 1 : -1) : (left < right ? 1 : -1);
	});
});

// Sort State for Table 2 (Pendaftaran)
const sortKey2 = ref('id');
const sortDirection2 = ref('asc');
const toggleSort2 = (key) => {
	if (sortKey2.value === key) {
		sortDirection2.value = sortDirection2.value === 'asc' ? 'desc' : 'asc';
	} else {
		sortKey2.value = key;
		sortDirection2.value = 'asc';
	}
};
const sortedPendaftaran = computed(() => {
	return [...props.pendaftaranAktivitas].sort((a, b) => {
		let left = a[sortKey2.value] ?? '';
		let right = b[sortKey2.value] ?? '';
		if (typeof left === 'number' && typeof right === 'number') {
			return sortDirection2.value === 'asc' ? left - right : right - left;
		}
		left = String(left).toLowerCase();
		right = String(right).toLowerCase();
		if (left === right) return 0;
		return sortDirection2.value === 'asc' ? (left > right ? 1 : -1) : (left < right ? 1 : -1);
	});
});

// Sort State for Table 3 (Hasil)
const sortKey3 = ref('id');
const sortDirection3 = ref('asc');
const toggleSort3 = (key) => {
	if (sortKey3.value === key) {
		sortDirection3.value = sortDirection3.value === 'asc' ? 'desc' : 'asc';
	} else {
		sortKey3.value = key;
		sortDirection3.value = 'asc';
	}
};
const sortedHasil = computed(() => {
	return [...props.hasilAktivitas].sort((a, b) => {
		let left = a[sortKey3.value] ?? '';
		let right = b[sortKey3.value] ?? '';
		if (typeof left === 'number' && typeof right === 'number') {
			return sortDirection3.value === 'asc' ? left - right : right - left;
		}
		left = String(left).toLowerCase();
		right = String(right).toLowerCase();
		if (left === right) return 0;
		return sortDirection3.value === 'asc' ? (left > right ? 1 : -1) : (left < right ? 1 : -1);
	});
});
</script>

<template>
	<Head title="Dashboard Admin" />

	<AdminLayout>
		<div class="mx-auto w-full max-w-[1520px] px-4 py-6 font-poppins sm:px-6 sm:py-8 lg:px-8 space-y-10 sm:space-y-12 lg:space-y-14 pb-16">
			<!-- Header Section -->
			<div class="space-y-1">
				<h1 class="text-[28px] sm:text-[34px] lg:text-[40px] font-extrabold leading-tight text-[#17334F] tracking-tight">
					Selamat Datang Admin!
				</h1>
				<p class="font-inter text-[13px] sm:text-[14px] font-medium text-[#64748b]">
					Lihat seberapa banyak mahasiswa yang mengikuti aktivitas Talenta!
				</p>
			</div>

			<!-- Distribution Cards Section -->
			<div class="w-full">
				<!-- Outer overflow container: isolates cards on mobile with touch drag, visible on desktop so borders/shadows are never clipped -->
				<div class="w-full overflow-hidden lg:overflow-visible select-none lg:select-auto p-1 lg:p-0">
					<div
						class="flex lg:grid lg:grid-cols-2 lg:gap-6 will-change-transform lg:!transform-none lg:!cursor-default"
						:class="isDragging ? 'transition-none cursor-grabbing' : 'transition-transform duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] cursor-grab'"
						:style="mobileTrackStyle"
						@touchstart="handleTouchStart"
						@touchmove="handleTouchMove"
						@touchend="handleTouchEnd"
						@mousedown="handleMouseDown"
						@mousemove="handleMouseMove"
						@mouseup="handleMouseUp"
						@mouseleave="handleMouseLeave"
					>
						<!-- Left Card Slide: Pie Chart -->
						<div class="w-full shrink-0 lg:shrink px-1 lg:px-0">
							<div class="w-full rounded-2xl bg-white p-5 sm:p-6 shadow-xs border border-[#e2e8f0] flex flex-col items-center justify-between min-h-[380px]">
								<h2 class="text-center font-poppins text-[14px] sm:text-[16px] font-bold text-[#17334F] leading-snug px-2">
									Distribusi Peserta Berdasarkan Kategori Talenta
								</h2>

								<div class="relative w-full max-w-[240px] sm:max-w-[260px] h-[210px] sm:h-[250px] my-2 flex items-center justify-center">
									<div v-if="isLoading" class="h-44 w-44 rounded-full bg-slate-100 animate-pulse"></div>
									<Pie
										v-else
										:data="pieChartData"
										:options="pieChartOptions"
										:plugins="[ChartDataLabels]"
										aria-label="Grafik Distribusi Peserta"
									/>
								</div>

								<!-- Legend Badges Below Chart -->
								<div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 text-xs font-semibold text-[#475569] pt-2">
									<div v-for="item in participantStats" :key="item.category" class="flex items-center gap-1.5">
										<span class="h-3 w-3 rounded-full shrink-0" :style="{ backgroundColor: item.color }"></span>
										<span>{{ item.category }}</span>
									</div>
								</div>
							</div>
						</div>

						<!-- Right Card Slide: Real Numbers Table -->
						<div class="w-full shrink-0 lg:shrink px-1 lg:px-0">
							<div class="w-full rounded-2xl bg-white p-5 sm:p-6 shadow-xs border border-[#e2e8f0] flex flex-col justify-between min-h-[380px]">
								<h2 class="text-center font-poppins text-[14px] sm:text-[16px] font-bold text-[#17334F] leading-snug px-2">
									Distribusi Peserta Berdasarkan Kategori Talenta<br />
									<span class="text-[12px] sm:text-[13px] font-medium text-[#64748b]">(Dalam Angka riil)</span>
								</h2>

								<div class="mt-4 flex-1 flex flex-col justify-center px-2 sm:px-10">
									<div class="flex items-center justify-between py-2.5 border-b-2 border-[#17334F]/20 font-bold text-sm sm:text-[15px] text-[#17334F]">
										<span>Kategori</span>
										<span>Jumlah</span>
									</div>
									<div class="divide-y divide-[#f1f5f9] text-sm sm:text-[14px] text-[#334155] font-medium">
										<div v-for="item in participantStats" :key="item.category" class="flex items-center justify-between py-2.5 sm:py-3">
											<span>{{ item.category }}</span>
											<span class="font-semibold text-[#17334F]">{{ item.count }}</span>
										</div>
									</div>
									<div class="flex items-center justify-between py-3.5 border-t-2 border-[#17334F]/20 font-bold text-sm sm:text-[15px] text-[#17334F]">
										<span>Total</span>
										<span>{{ totalParticipants }}</span>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<!-- Dots Pagination Indicator (Mobile only) -->
				<div class="flex items-center justify-center gap-2 lg:hidden mt-4">
					<button
						type="button"
						@click="goToSlide(0)"
						class="h-2 rounded-full transition-all duration-300 cursor-pointer"
						:class="activeSlideIndex === 0 ? 'w-6 bg-[#416f65]' : 'w-2 bg-slate-300 hover:bg-slate-400'"
						aria-label="Slide 1: Grafik"
					></button>
					<button
						type="button"
						@click="goToSlide(1)"
						class="h-2 rounded-full transition-all duration-300 cursor-pointer"
						:class="activeSlideIndex === 1 ? 'w-6 bg-[#416f65]' : 'w-2 bg-slate-300 hover:bg-slate-400'"
						aria-label="Slide 2: Angka Riil"
					></button>
				</div>
			</div>

			<!-- Table 1: Pengajuan Aktivitas Mahasiswa -->
			<div>
				<div class="mb-4 flex items-center justify-between gap-3">
					<h2 class="text-[16px] sm:text-[18px] md:text-[20px] font-bold leading-tight sm:leading-none text-[#17334F]">
						Pengajuan Aktivitas
					</h2>
					<Link
						href="/admin/aktivitas/persetujuan"
						class="font-inter text-xs sm:text-sm font-semibold text-[#17334F] hover:text-[#416f65] flex items-center gap-1 transition-colors shrink-0"
					>
						<span>Selengkapnya</span>
						<svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
							<path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
						</svg>
					</Link>
				</div>

				<div class="overflow-x-auto rounded-[12px] bg-white shadow-sm ring-1 ring-[#d6e0ee]">
					<table class="w-full min-w-[1080px] table-fixed">
						<thead class="bg-[#416f65] font-inter text-[15px] font-semibold text-white whitespace-nowrap">
							<tr class="h-[52px]">
								<th
									v-for="col in columnsPengajuan"
									:key="col.key"
									class="px-4 py-3 text-center whitespace-nowrap"
									:class="col.width"
								>
									<div
										class="flex items-center justify-center gap-1.5 whitespace-nowrap"
										:class="col.sortable ? 'cursor-pointer select-none hover:text-white/85 transition-colors' : ''"
										@click="col.sortable ? toggleSort1(col.key) : null"
									>
										<span v-if="col.sortable" class="w-3.5 shrink-0" aria-hidden="true"></span>
										<span>{{ col.label }}</span>
										<svg
											v-if="col.sortable"
											class="h-3.5 w-3.5 transition-transform duration-200 shrink-0"
											:class="[
												sortKey1 === col.key ? 'text-white' : 'text-white/60',
												sortKey1 === col.key && sortDirection1 === 'asc' ? 'rotate-180' : ''
											]"
											fill="none"
											viewBox="0 0 24 24"
											stroke="currentColor"
											stroke-width="2.2"
											stroke-linecap="round"
											stroke-linejoin="round"
										>
											<path d="M12 4.5v15m0 0l-5-5m5 5l5-5" />
										</svg>
									</div>
								</th>
							</tr>
						</thead>
						<tbody class="divide-y divide-[#d6e0ee] font-inter text-[15px] text-[#435b76]">
							<tr
								v-for="(item, idx) in sortedPengajuan"
								:key="`pengajuan-${item.id}-${idx}`"
								class="h-[52px] hover:bg-[#f7f9fd] transition-colors"
							>
								<td class="px-4 py-3 text-center font-medium w-[60px]">{{ idx + 1 }}</td>
								<td class="px-4 py-3 text-left font-medium text-[#2f4b6e] truncate w-[260px]" :title="item.judul">{{ item.judul }}</td>
								<td class="px-4 py-3 text-left truncate w-[230px]" :title="item.kategori">{{ item.kategori }}</td>
								<td class="whitespace-nowrap px-4 py-3 text-center w-[130px]">{{ item.deadline }}</td>
								<td class="px-4 py-3 text-center w-[120px]">
									<button
										type="button"
										class="text-[#416f65] hover:underline font-medium cursor-pointer"
										@click="handlePreview(item)"
									>
										{{ item.file }}
									</button>
								</td>
								<td class="px-4 py-3 text-center w-[140px]">
									<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
										<svg class="h-3.5 w-3.5 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
											<circle cx="12" cy="12" r="9" />
											<polyline points="12 7 12 12 15 15" />
										</svg>
										<span>{{ item.status }}</span>
									</span>
								</td>
								<td class="px-4 py-3 text-center w-[140px]">
									<div class="flex items-center justify-center gap-2">
										<button
											type="button"
											@click="handleApprove(item)"
											title="Setujui"
											class="inline-flex items-center justify-center rounded-[6px] transition-all duration-150 hover:brightness-75 active:brightness-60 focus:outline-none cursor-pointer"
										>
											<img src="/assets/icons/button-setuju.svg" alt="Setuju" class="h-7 w-7 rounded-[5px]" />
										</button>
										<button
											type="button"
											@click="handleReject(item)"
											title="Tolak"
											class="inline-flex items-center justify-center rounded-[6px] transition-all duration-150 hover:brightness-75 active:brightness-60 focus:outline-none cursor-pointer"
										>
											<img src="/assets/icons/button-tolak.svg" alt="Tolak" class="h-7 w-7 rounded-[5px]" />
										</button>
										<button
											type="button"
											@click="handlePreview(item)"
											title="Lihat Detail"
											class="inline-flex items-center justify-center rounded-[6px] transition-all duration-150 hover:brightness-75 active:brightness-60 focus:outline-none cursor-pointer"
										>
											<img src="/assets/icons/button-preview.svg" alt="Preview" class="h-7 w-7 rounded-[5px]" />
										</button>
									</div>
								</td>
							</tr>
							<tr v-if="sortedPengajuan.length === 0">
								<td colspan="7" class="py-8 text-center text-[#64748b]">Belum ada data pengajuan aktivitas.</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<!-- Table 2: Pendaftaran Aktivitas Mahasiswa -->
			<div>
				<div class="mb-4 flex items-center justify-between gap-3">
					<h2 class="text-[16px] sm:text-[18px] md:text-[20px] font-bold leading-tight sm:leading-none text-[#17334F]">
						Pendaftaran Aktivitas Mahasiswa
					</h2>
					<Link
						href="/admin/aktivitas/bukti-pendaftaran"
						class="font-inter text-xs sm:text-sm font-semibold text-[#17334F] hover:text-[#416f65] flex items-center gap-1 transition-colors shrink-0"
					>
						<span>Selengkapnya</span>
						<svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
							<path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
						</svg>
					</Link>
				</div>

				<div class="overflow-x-auto rounded-[12px] bg-white shadow-sm ring-1 ring-[#d6e0ee]">
					<table class="w-full min-w-[1080px] table-fixed">
						<thead class="bg-[#416f65] font-inter text-[15px] font-semibold text-white whitespace-nowrap">
							<tr class="h-[52px]">
								<th
									v-for="col in columnsPendaftaran"
									:key="col.key"
									class="px-4 py-3 text-center whitespace-nowrap"
									:class="col.width"
								>
									<div
										class="flex items-center justify-center gap-1.5 whitespace-nowrap"
										:class="col.sortable ? 'cursor-pointer select-none hover:text-white/85 transition-colors' : ''"
										@click="col.sortable ? toggleSort2(col.key) : null"
									>
										<span v-if="col.sortable" class="w-3.5 shrink-0" aria-hidden="true"></span>
										<span>{{ col.label }}</span>
										<svg
											v-if="col.sortable"
											class="h-3.5 w-3.5 transition-transform duration-200 shrink-0"
											:class="[
												sortKey2 === col.key ? 'text-white' : 'text-white/60',
												sortKey2 === col.key && sortDirection2 === 'asc' ? 'rotate-180' : ''
											]"
											fill="none"
											viewBox="0 0 24 24"
											stroke="currentColor"
											stroke-width="2.2"
											stroke-linecap="round"
											stroke-linejoin="round"
										>
											<path d="M12 4.5v15m0 0l-5-5m5 5l5-5" />
										</svg>
									</div>
								</th>
							</tr>
						</thead>
						<tbody class="divide-y divide-[#d6e0ee] font-inter text-[15px] text-[#435b76]">
							<tr
								v-for="(item, idx) in sortedPendaftaran"
								:key="`pendaftaran-${item.id}-${idx}`"
								class="h-[52px] hover:bg-[#f7f9fd] transition-colors"
							>
								<td class="px-4 py-3 text-center font-medium w-[60px]">{{ idx + 1 }}</td>
								<td class="px-4 py-3 text-left font-medium text-[#2f4b6e] truncate w-[230px]" :title="item.namaTim">{{ item.namaTim }}</td>
								<td class="px-4 py-3 text-left truncate w-[330px]" :title="item.namaPeserta">{{ item.namaPeserta }}</td>
								<td class="px-4 py-3 text-center w-[190px]">
									<button
										type="button"
										class="text-[#416f65] hover:underline font-medium cursor-pointer"
										@click="handlePreview(item)"
									>
										{{ item.file }}
									</button>
								</td>
								<td class="px-4 py-3 text-center w-[130px]">
									<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
										<svg class="h-3.5 w-3.5 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
											<circle cx="12" cy="12" r="9" />
											<polyline points="12 7 12 12 15 15" />
										</svg>
										<span>{{ item.status }}</span>
									</span>
								</td>
								<td class="px-4 py-3 text-center w-[140px]">
									<div class="flex items-center justify-center gap-2">
										<button
											type="button"
											@click="handleApprove(item)"
											title="Setujui"
											class="inline-flex items-center justify-center rounded-[6px] transition-all duration-150 hover:brightness-75 active:brightness-60 focus:outline-none cursor-pointer"
										>
											<img src="/assets/icons/button-setuju.svg" alt="Setuju" class="h-7 w-7 rounded-[5px]" />
										</button>
										<button
											type="button"
											@click="handleReject(item)"
											title="Tolak"
											class="inline-flex items-center justify-center rounded-[6px] transition-all duration-150 hover:brightness-75 active:brightness-60 focus:outline-none cursor-pointer"
										>
											<img src="/assets/icons/button-tolak.svg" alt="Tolak" class="h-7 w-7 rounded-[5px]" />
										</button>
										<button
											type="button"
											@click="handlePreview(item)"
											title="Lihat Detail"
											class="inline-flex items-center justify-center rounded-[6px] transition-all duration-150 hover:brightness-75 active:brightness-60 focus:outline-none cursor-pointer"
										>
											<img src="/assets/icons/button-preview.svg" alt="Preview" class="h-7 w-7 rounded-[5px]" />
										</button>
									</div>
								</td>
							</tr>
							<tr v-if="sortedPendaftaran.length === 0">
								<td colspan="6" class="py-8 text-center text-[#64748b]">Belum ada data pendaftaran aktivitas.</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			<!-- Table 3: Hasil Aktivitas Mahasiswa -->
			<div>
				<div class="mb-4 flex items-center justify-between gap-3">
					<h2 class="text-[16px] sm:text-[18px] md:text-[20px] font-bold leading-tight sm:leading-none text-[#17334F]">
						Hasil Aktivitas Mahasiswa
					</h2>
					<Link
						href="/admin/aktivitas/hasil"
						class="font-inter text-xs sm:text-sm font-semibold text-[#17334F] hover:text-[#416f65] flex items-center gap-1 transition-colors shrink-0"
					>
						<span>Selengkapnya</span>
						<svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
							<path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
						</svg>
					</Link>
				</div>

				<div class="overflow-x-auto rounded-[12px] bg-white shadow-sm ring-1 ring-[#d6e0ee]">
					<table class="w-full min-w-[1080px] table-fixed">
						<thead class="bg-[#416f65] font-inter text-[15px] font-semibold text-white whitespace-nowrap">
							<tr class="h-[52px]">
								<th
									v-for="col in columnsHasil"
									:key="col.key"
									class="px-4 py-3 text-center whitespace-nowrap"
									:class="col.width"
								>
									<div
										class="flex items-center justify-center gap-1.5 whitespace-nowrap"
										:class="col.sortable ? 'cursor-pointer select-none hover:text-white/85 transition-colors' : ''"
										@click="col.sortable ? toggleSort3(col.key) : null"
									>
										<span v-if="col.sortable" class="w-3.5 shrink-0" aria-hidden="true"></span>
										<span>{{ col.label }}</span>
										<svg
											v-if="col.sortable"
											class="h-3.5 w-3.5 transition-transform duration-200 shrink-0"
											:class="[
												sortKey3 === col.key ? 'text-white' : 'text-white/60',
												sortKey3 === col.key && sortDirection3 === 'asc' ? 'rotate-180' : ''
											]"
											fill="none"
											viewBox="0 0 24 24"
											stroke="currentColor"
											stroke-width="2.2"
											stroke-linecap="round"
											stroke-linejoin="round"
										>
											<path d="M12 4.5v15m0 0l-5-5m5 5l5-5" />
										</svg>
									</div>
								</th>
							</tr>
						</thead>
						<tbody class="divide-y divide-[#d6e0ee] font-inter text-[15px] text-[#435b76]">
							<tr
								v-for="(item, idx) in sortedHasil"
								:key="`hasil-${item.id}-${idx}`"
								class="h-[52px] hover:bg-[#f7f9fd] transition-colors"
							>
								<td class="px-4 py-3 text-center font-medium w-[60px]">{{ idx + 1 }}</td>
								<td class="px-4 py-3 text-left font-medium text-[#2f4b6e] truncate w-[190px]" :title="item.namaMahasiswa">{{ item.namaMahasiswa }}</td>
								<td class="px-4 py-3 text-center font-mono text-[#64748b] w-[110px]">{{ item.nim }}</td>
								<td class="px-4 py-3 text-center w-[150px]">{{ item.jenisRoadmap }}</td>
								<td class="px-4 py-3 text-left truncate w-[200px]" :title="item.namaAktivitas">{{ item.namaAktivitas }}</td>
								<td class="px-4 py-3 text-center w-[100px]">
									<button
										type="button"
										class="text-[#416f65] hover:underline font-medium cursor-pointer"
										@click="handlePreview(item)"
									>
										{{ item.file }}
									</button>
								</td>
								<td class="px-4 py-3 text-center w-[130px]">
									<span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
										<svg class="h-3.5 w-3.5 text-gray-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
											<circle cx="12" cy="12" r="9" />
											<polyline points="12 7 12 12 15 15" />
										</svg>
										<span>{{ item.status }}</span>
									</span>
								</td>
								<td class="px-4 py-3 text-center w-[140px]">
									<div class="flex items-center justify-center gap-2">
										<button
											type="button"
											@click="handleApprove(item)"
											title="Setujui"
											class="inline-flex items-center justify-center rounded-[6px] transition-all duration-150 hover:brightness-75 active:brightness-60 focus:outline-none cursor-pointer"
										>
											<img src="/assets/icons/button-setuju.svg" alt="Setuju" class="h-7 w-7 rounded-[5px]" />
										</button>
										<button
											type="button"
											@click="handleReject(item)"
											title="Tolak"
											class="inline-flex items-center justify-center rounded-[6px] transition-all duration-150 hover:brightness-75 active:brightness-60 focus:outline-none cursor-pointer"
										>
											<img src="/assets/icons/button-tolak.svg" alt="Tolak" class="h-7 w-7 rounded-[5px]" />
										</button>
										<button
											type="button"
											@click="handlePreview(item)"
											title="Lihat Detail"
											class="inline-flex items-center justify-center rounded-[6px] transition-all duration-150 hover:brightness-75 active:brightness-60 focus:outline-none cursor-pointer"
										>
											<img src="/assets/icons/button-preview.svg" alt="Preview" class="h-7 w-7 rounded-[5px]" />
										</button>
									</div>
								</td>
							</tr>
							<tr v-if="sortedHasil.length === 0">
								<td colspan="8" class="py-8 text-center text-[#64748b]">Belum ada data hasil aktivitas.</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<!-- PREVIEW FILE MODAL -->
		<Modal :show="isPreviewModalOpen" @close="isPreviewModalOpen = false" max-width="lg">
			<div class="p-6 font-poppins">
				<div class="flex items-center justify-between border-b border-gray-100 pb-3">
					<h3 class="text-base font-bold text-[#17334F]">Preview Dokumen / Bukti</h3>
					<button
						type="button"
						class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 focus:outline-none cursor-pointer"
						@click="isPreviewModalOpen = false"
					>
						<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
						</svg>
					</button>
				</div>

				<div class="my-6 flex flex-col items-center justify-center rounded-xl bg-gray-50 border border-dashed border-gray-200 p-8 text-center">
					<svg class="h-16 w-16 text-[#416f65]/70 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
						<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
					</svg>
					<p class="text-sm font-semibold text-[#17334F]">{{ previewItem?.file || 'Dokumen.jpg' }}</p>
					<p class="text-xs text-gray-500 mt-1">{{ previewItem?.judul || previewItem?.namaAktivitas || previewItem?.namaTim || 'Detail Aktivitas' }}</p>
				</div>

				<div class="flex justify-end gap-2.5">
					<button
						type="button"
						class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 cursor-pointer"
						@click="isPreviewModalOpen = false"
					>
						Tutup
					</button>
					<button
						type="button"
						class="rounded-lg bg-[#416f65] px-4 py-2 text-sm font-semibold text-white hover:bg-[#345c53] cursor-pointer shadow-sm"
						@click="isPreviewModalOpen = false; showToast('info', 'Unduh File', 'File sedang diunduh...')"
					>
						Unduh File
					</button>
				</div>
			</div>
		</Modal>

		<!-- TOAST NOTIFICATION -->
		<ToastNotification
			:show="toast.show"
			:type="toast.type"
			:title="toast.title"
			:message="toast.message"
			@close="toast.show = false"
		/>
	</AdminLayout>
</template>
